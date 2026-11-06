<?php

namespace App\Services;

use App\Models\Cargo;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerCargoLossAllowance;
use App\Models\Transporter;
use Illuminate\Support\Facades\Auth;

/**
 * Loss terms for a trip.
 *
 *  Allowable loss % (customer x cargo; stored as a PERCENT, 0.3 = 0.003):
 *      Customer + Cargo agreement (customer_cargo_loss_allowances)
 *      -> Customer, all cargos (customers.allowable_loss_percentage)
 *      -> Cargo default (cargos.allowable_loss_percentage)
 *      -> Business Settings default (companies.allowable_loss_percentage)
 *
 *  Chargeable loss rate (market rate per cargo, by who carried it):
 *      Cargo own-fleet / subcontractor rate
 *      -> Business Settings own-fleet / subcontractor default
 *  Own fleet = the default transporter; every other transporter is a
 *  subcontractor and they all share one rate.
 *
 * Each resolver returns ['value' => ?float, 'source' => ?string].
 */
class LossRateService
{
    /** Per-request memo of customer+cargo agreements: "customer|cargo" => pct. */
    private ?array $agreements = null;

    public function allowance(?Customer $customer, ?Cargo $cargo, ?Company $company): array
    {
        if ($customer && $cargo) {
            // Table screens preload every agreement; a single trip looks up its own row.
            $pct = is_array($this->agreements)
                ? ($this->agreements[$customer->id . '|' . $cargo->id] ?? null)
                : CustomerCargoLossAllowance::where('customer_id', $customer->id)->where('cargo_id', $cargo->id)->value('allowable_loss_percentage');
            if (is_numeric($pct)) {
                return ['value' => (float) $pct, 'source' => 'Customer + Cargo'];
            }
        }

        return $this->first([
            ['Customer (all cargos)', $customer?->allowable_loss_percentage],
            ['Cargo default', $cargo?->allowable_loss_percentage],
            ['Business default', $company?->allowable_loss_percentage],
        ]);
    }

    public function rate(?Cargo $cargo, ?Transporter $transporter, ?Company $company): array
    {
        $own = $this->isOwnFleet($transporter);
        $field = $own ? 'own_fleet_chargeable_loss_rate' : 'subcontractor_chargeable_loss_rate';
        $who = $own ? 'Own Fleet' : 'Subcontractor';

        return $this->first([
            ["Cargo {$who} rate", $cargo?->$field],
            ["{$who} default", $company?->$field],
        ]);
    }

    public function isOwnFleet(?Transporter $transporter): bool
    {
        return $transporter && ($transporter->default || $transporter->fleet_type === Transporter::FLEET_OWN);
    }

    /** The company's default (own fleet) transporter name, for labels. */
    public function ownFleetName(): string
    {
        return Transporter::where('default', true)->value('name') ?: 'Own Fleet';
    }

    public function currentCompany(): ?Company
    {
        $user = Auth::user();

        return $user?->employee?->company ?? $user?->company;
    }

    /** Customers the Loss Rates screen / export covers (the company's + unassigned). */
    public function customersQuery(?Company $company)
    {
        return Customer::query()
            ->when($company, fn ($q) => $q->where(fn ($w) => $w->where('company_id', $company->id)->orWhereNull('company_id')))
            ->orderBy('name')->orderBy('id');
    }

    /** Saves (or clears, when blank) one customer + cargo agreement. */
    public function setAgreement(int $customerId, int $cargoId, $pct): void
    {
        if (is_numeric($pct)) {
            CustomerCargoLossAllowance::updateOrCreate(
                ['customer_id' => $customerId, 'cargo_id' => $cargoId],
                ['allowable_loss_percentage' => $pct, 'user_id' => Auth::id()]
            );
        } else {
            CustomerCargoLossAllowance::where('customer_id', $customerId)->where('cargo_id', $cargoId)->get()->each->delete();
        }
        $this->agreements = null;
    }

    public function agreements(): array
    {
        return $this->agreements ??= CustomerCargoLossAllowance::get(['customer_id', 'cargo_id', 'allowable_loss_percentage'])
            ->mapWithKeys(fn ($a) => [$a->customer_id . '|' . $a->cargo_id => (float) $a->allowable_loss_percentage])
            ->all();
    }

    /** "0.3% (0.003)" - the % as stored plus the fraction people quote. */
    public static function formatPct($pct): string
    {
        if (! is_numeric($pct)) {
            return '';
        }
        $fraction = rtrim(rtrim(number_format($pct / 100, 8, '.', ''), '0'), '.');

        return ($pct + 0) . '% (' . $fraction . ')';
    }

    private function first(array $levels): array
    {
        foreach ($levels as [$label, $value]) {
            if (is_numeric($value)) {
                return ['value' => (float) $value, 'source' => $label];
            }
        }

        return ['value' => null, 'source' => null];
    }
}
