<?php

namespace App\Services\Sage;

use App\Integrations\Contracts\SageDriver;
use App\Models\Account;
use App\Models\Company;
use App\Models\CompanyIntegration;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\Horse;
use App\Models\IntegrationLog;
use App\Models\IntegrationMapping;
use App\Models\Product;
use App\Models\Tax;
use App\Models\Trailer;
use App\Models\Transporter;
use App\Models\Vendor;
use App\Services\Sage\Concerns\ManagesMappings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pull (import) existing Sage records into Gonyeti without duplicating.
 *
 *   Customer   ← CUSTOMER          (match: sage id, then name)
 *   Vendor     ← VENDOR            (match: sage id, then name)
 *   Horse      ← CLASS  H.. or FHH.. (match: custom_ref, then registration = NAME)
 *   Trailer    ← CLASS  T.. or FHT.. (match: custom_ref, then registration = NAME)
 *   Transporter← PROJECT SUBCONTRACTOR (match: custom_ref, then name)
 *
 * Runs inside a queued job (no Auth), so company + creator are passed in.
 */
class SagePullService
{
    use ManagesMappings;

    protected SageDriver $driver;
    protected CompanyIntegration $integration;
    protected int $companyId;
    protected int $creatorId;

    public function __construct(SageDriver $driver, CompanyIntegration $integration, int $companyId, int $creatorId)
    {
        $this->driver      = $driver;
        $this->integration = $integration;
        $this->companyId   = $companyId;
        $this->creatorId   = $creatorId;
    }

    /**
     * @param  array  $options  entity-specific context (products use buy/sell).
     * @return array{created:int,linked:int,skipped:int,failed:int}
     */
    public function pull(string $entity, array $options = []): array
    {
        try {
            $summary = match ($entity) {
                'customer'    => $this->pullCustomers(),
                'vendor'      => $this->pullVendors(),
                'horse'       => $this->pullHorses(),
                'trailer'     => $this->pullTrailers(),
                'transporter' => $this->pullTransporters(),
                'driver'      => $this->pullDrivers(),
                'tax'         => $this->pullTaxes(),
                'product'     => $this->pullProducts($options),
                default       => ['created' => 0, 'linked' => 0, 'skipped' => 0, 'failed' => 0],
            };
        } catch (Throwable $e) {
            Log::error("Sage pull {$entity} failed: " . $e->getMessage());
            $summary = ['created' => 0, 'linked' => 0, 'skipped' => 0, 'failed' => 0, 'error' => $e->getMessage()];
        }

        $this->logSummary($entity, $summary);

        return $summary;
    }

    // ── Customers / Vendors ──────────────────────────────────────

    protected function pullCustomers(): array
    {
        $rows = $this->readAll('CUSTOMER', [
            'CUSTOMERID', 'NAME', 'DISPLAYCONTACT.EMAIL1', 'DISPLAYCONTACT.PHONE1',
            'DISPLAYCONTACT.MAILADDRESS.CITY', 'DISPLAYCONTACT.MAILADDRESS.COUNTRY',
            'DISPLAYCONTACT.MAILADDRESS.ADDRESS1',
        ], '');

        return $this->upsertParty(Customer::class, 'C', 'CUSTOMERID', $rows);
    }

    protected function pullVendors(): array
    {
        $rows = $this->readAll('VENDOR', [
            'VENDORID', 'NAME', 'DISPLAYCONTACT.EMAIL1', 'DISPLAYCONTACT.PHONE1',
            'DISPLAYCONTACT.MAILADDRESS.CITY', 'DISPLAYCONTACT.MAILADDRESS.COUNTRY',
            'DISPLAYCONTACT.MAILADDRESS.ADDRESS1',
        ], '');

        return $this->upsertParty(Vendor::class, 'V', 'VENDORID', $rows);
    }

    /** Shared customer/vendor upsert (both use the SyncsToSageIntacct columns). */
    protected function upsertParty(string $modelClass, string $letter, string $idField, array $rows): array
    {
        $s = ['created' => 0, 'linked' => 0, 'skipped' => 0, 'failed' => 0];

        foreach ($rows as $row) {
            $sageId = $row[$idField] ?? null;
            $name   = $row['NAME'] ?? null;
            if (! $sageId || ! $name) {
                $s['skipped']++;
                continue;
            }

            try {
                $record = $modelClass::where('sage_intacct_id', $sageId)
                    ->orWhere('custom_ref', $sageId)
                    ->first()
                    ?? $modelClass::where('name', $name)->first();

                $isNew = false;
                if (! $record) {
                    $record             = new $modelClass();
                    $record->name       = $name;
                    $record->company_id = $this->companyId;
                    $record->creator_id = $this->creatorId;
                    $record->{$letter === 'C' ? 'customer_number' : 'vendor_number'} = $this->nextNumber($modelClass, $letter);
                    $record->status     = 1;
                    $isNew              = true;
                }

                // Fill only empty identity fields; never clobber user-entered data.
                $record->email          = $record->email ?: ($row['DISPLAYCONTACT.EMAIL1'] ?? null);
                $record->phonenumber    = $record->phonenumber ?: ($row['DISPLAYCONTACT.PHONE1'] ?? null);
                $record->city           = $record->city ?: ($row['DISPLAYCONTACT.MAILADDRESS.CITY'] ?? null);
                $record->country        = $record->country ?: ($row['DISPLAYCONTACT.MAILADDRESS.COUNTRY'] ?? null);
                $record->street_address = $record->street_address ?: ($row['DISPLAYCONTACT.MAILADDRESS.ADDRESS1'] ?? null);

                $record->sage_intacct_id     = $sageId;
                $record->custom_ref          = $record->custom_ref ?: $sageId;
                $record->sage_sync_status    = 'synced';
                $record->sage_last_synced_at = now();
                $record->saveQuietly();

                $isNew ? $s['created']++ : $s['linked']++;
            } catch (Throwable $e) {
                $s['failed']++;
            }
        }

        return $s;
    }

    // ── Horses / Trailers (Sage Classes) ─────────────────────────

    protected function pullHorses(): array
    {
        $rows = $this->readAll('CLASS', ['CLASSID', 'NAME'], $this->classQuery(config('sageintacct.pull.horse_class_prefixes', ['H', 'FHH'])));

        return $this->upsertFleetClass(Horse::class, 'horse_class', 'H', $rows);
    }

    protected function pullTrailers(): array
    {
        // Exclude transporter classes (TRANS-*) which also start with "T".
        $query = $this->classQuery(config('sageintacct.pull.trailer_class_prefixes', ['T', 'FHT'])) . " AND CLASSID NOT LIKE 'TRANS%'";
        $rows  = $this->readAll('CLASS', ['CLASSID', 'NAME'], $query);

        return $this->upsertFleetClass(Trailer::class, 'trailer_class', 'T', $rows);
    }

    /** Shared horse/trailer upsert (match by registration = class NAME). */
    protected function upsertFleetClass(string $modelClass, string $entityType, string $letter, array $rows): array
    {
        $s        = ['created' => 0, 'linked' => 0, 'skipped' => 0, 'failed' => 0];
        $numField = $modelClass === Horse::class ? 'horse_number' : 'trailer_number';

        foreach ($rows as $row) {
            $classId = $row['CLASSID'] ?? null;
            $reg     = $row['NAME'] ?? null;
            if (! $classId || ! $reg) {
                $s['skipped']++;
                continue;
            }

            try {
                $record = $modelClass::where('custom_ref', $classId)
                    ->orWhere('registration_number', $reg)
                    ->first();

                $isNew = false;
                if (! $record) {
                    $record                      = new $modelClass();
                    $record->registration_number = $reg;
                    $record->user_id             = $this->creatorId;
                    $record->{$numField}         = $this->nextNumber($modelClass, $letter);
                    $isNew                       = true;
                }

                $record->custom_ref = $record->custom_ref ?: $classId;
                $record->saveQuietly();

                $this->linkMapping($entityType, $record, $classId, $reg);

                $isNew ? $s['created']++ : $s['linked']++;
            } catch (Throwable $e) {
                $s['failed']++;
            }
        }

        return $s;
    }

    // ── Transporters (Sage Projects) ─────────────────────────────

    protected function pullTransporters(): array
    {
        $type = (string) config('sageintacct.pull.transporter_project_type', 'SUBCONTRACTOR');
        $rows = $this->readAll('PROJECT', ['PROJECTID', 'NAME'], "PROJECTTYPE = '{$type}'");

        $s = ['created' => 0, 'linked' => 0, 'skipped' => 0, 'failed' => 0];
        foreach ($rows as $row) {
            $projectId = $row['PROJECTID'] ?? null;
            $name      = $row['NAME'] ?? null;
            if (! $projectId || ! $name) {
                $s['skipped']++;
                continue;
            }

            try {
                $record = Transporter::where('custom_ref', $projectId)
                    ->orWhere('name', $name)
                    ->first();

                $isNew = false;
                if (! $record) {
                    $record                     = new Transporter();
                    $record->name               = $name;
                    $record->company_id         = $this->companyId;
                    $record->creator_id         = $this->creatorId;
                    $record->user_id            = $this->creatorId;
                    $record->transporter_number = $this->nextNumber(Transporter::class, 'T');
                    $record->status             = 1;
                    $record->authorization      = 'approved';
                    $isNew                      = true;
                }

                $record->custom_ref = $record->custom_ref ?: $projectId;
                $record->saveQuietly();

                $this->linkMapping('transporter_project', $record, $projectId, $name);

                $isNew ? $s['created']++ : $s['linked']++;
            } catch (Throwable $e) {
                $s['failed']++;
            }
        }

        return $s;
    }

    // ── Drivers (Sage Employees in the sub-contracting department) ──

    protected function pullDrivers(): array
    {
        $dept = (string) config('sageintacct.project.department_id', 'D2-1');
        $rows = $this->readAll('EMPLOYEE', [
            'EMPLOYEEID', 'PERSONALINFO.FIRSTNAME', 'PERSONALINFO.LASTNAME', 'PERSONALINFO.PRINTAS',
        ], "DEPARTMENTID = '{$dept}'");

        $s = ['created' => 0, 'linked' => 0, 'skipped' => 0, 'failed' => 0];
        foreach ($rows as $row) {
            $empId   = $row['EMPLOYEEID'] ?? null;
            $first   = $row['PERSONALINFO.FIRSTNAME'] ?? null;
            $last    = $row['PERSONALINFO.LASTNAME'] ?? null;
            $printas = $row['PERSONALINFO.PRINTAS'] ?? null;
            if (! $empId) {
                $s['skipped']++;
                continue;
            }
            // Fall back to splitting the display name when first/last are blank.
            if (! $first && ! $last && $printas) {
                $parts = preg_split('/\s+/', trim($printas), 2);
                $first = $parts[0] ?? null;
                $last  = $parts[1] ?? null;
            }

            try {
                // De-dup: custom_ref (Sage id) → employee_number → name+surname (+idnumber).
                $employee = Employee::where('custom_ref', $empId)->first()
                    ?? Employee::where('employee_number', $empId)->first();
                if (! $employee && ($first || $last)) {
                    $employee = Employee::when($first, fn ($q) => $q->where('name', $first))
                        ->when($last, fn ($q) => $q->where('surname', $last))
                        ->first();
                }

                $isNew = false;
                if (! $employee) {
                    $employee                  = new Employee();
                    $employee->company_id      = $this->companyId;
                    $employee->creator_id      = $this->creatorId;
                    $employee->name            = $first ?: 'Driver';
                    $employee->surname         = $last ?: '';
                    $employee->post            = 'Driver';
                    // Gonyeti's own sequence — the Sage id goes in custom_ref, not here.
                    $employee->employee_number = $this->nextNumber(Employee::class, 'E');
                    $employee->status          = 1;
                    $isNew = true;
                }
                $employee->custom_ref = $employee->custom_ref ?: $empId;  // Sage EMPLOYEEID
                $employee->saveQuietly();

                // Ensure exactly one Driver for this Employee (no duplicate drivers).
                $driver = Driver::where('employee_id', $employee->id)->first();
                if (! $driver) {
                    $driver                = new Driver();
                    $driver->employee_id   = $employee->id;
                    $driver->user_id       = $this->creatorId;
                    $driver->driver_number = $this->nextNumber(Driver::class, 'D');
                    $driver->status        = 1;
                }
                $driver->custom_ref = $driver->custom_ref ?: $empId;     // Sage EMPLOYEEID
                $driver->saveQuietly();

                // Mapping is keyed on the Employee (entity_type driver_employee).
                $this->linkMapping('driver_employee', $employee, $empId, trim(($first ?? '') . ' ' . ($last ?? '')));

                $isNew ? $s['created']++ : $s['linked']++;
            } catch (Throwable $e) {
                $s['failed']++;
            }
        }

        return $s;
    }

    // ── Item tax groups (Sage ITEMTAXGROUP → Gonyeti taxes module) ──

    /**
     * Pull Sage's ITEM tax groups (Standard Rate, Exempt, Zero Rate, imports, …)
     * into the Gonyeti taxes module. The group itself carries no rate — the rate
     * lives in TAXDETAIL — so each group's rate is resolved from the best matching
     * tax detail (prefer the purchase/input side, since item tax groups drive
     * purchases). When no confident match exists the rate is left null for the
     * user to complete. De-dup is by tax name (case-insensitive); existing,
     * user-entered fields are never overwritten.
     */
    protected function pullTaxes(): array
    {
        // ITEMTAXGROUP holds only item-type groups; GROUPTYPE isn't a filterable
        // field, so read them all (RECORDNO > 0) rather than filtering on it.
        $groups  = $this->readAll('ITEMTAXGROUP', ['NAME', 'GROUPTYPE'], 'RECORDNO > 0');
        $details = $this->readAll('TAXDETAIL', ['DETAILID', 'DESCRIPTION', 'TAXTYPE', 'VALUE', 'STATUS'], 'RECORDNO > 0');
        $rateFor = $this->taxRateResolver($details);

        // Post to the VAT control account if the chart of accounts has one.
        $accountId = optional(
            Account::where('name', 'Value Added Tax')->orWhere('name', 'like', '%VAT%')->first()
        )->id;

        $s = ['created' => 0, 'linked' => 0, 'skipped' => 0, 'failed' => 0];
        foreach ($groups as $row) {
            $name = trim($row['NAME'] ?? '');
            if ($name === '') {
                $s['skipped']++;
                continue;
            }

            try {
                $tax   = Tax::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
                $isNew = false;
                if (! $tax) {
                    $tax          = new Tax();
                    $tax->name    = $name;
                    $tax->user_id = $this->creatorId;
                    $isNew        = true;
                }

                // Fill only empty fields — never clobber a user-entered rate/category.
                $tax->category     = $tax->category ?: 'Item Tax Group';
                $tax->abbreviation = $tax->abbreviation ?: $this->taxAbbreviation($name);
                $tax->account_id   = $tax->account_id ?: $accountId;
                if ($tax->rate === null || $tax->rate === '') {
                    $tax->rate = $rateFor($name); // may stay null when unresolved
                }
                $tax->description = $tax->description ?: 'Imported from Sage Intacct item tax group.';
                $tax->save();

                $isNew ? $s['created']++ : $s['linked']++;
            } catch (Throwable $e) {
                $s['failed']++;
            }
        }

        return $s;
    }

    /**
     * Build a resolver that maps an item-tax-group name to a percentage rate by
     * matching it against the tax details (normalised, purchase side preferred).
     */
    protected function taxRateResolver(array $details): \Closure
    {
        $index = [];
        foreach ($details as $d) {
            if (strcasecmp($d['STATUS'] ?? 'active', 'active') !== 0) {
                continue;
            }
            $key = $this->normaliseTaxName($d['DESCRIPTION'] ?? ($d['DETAILID'] ?? ''));
            if ($key === '') {
                continue;
            }
            $isPurchase = strcasecmp($d['TAXTYPE'] ?? '', 'Purchase') === 0;
            // First writer wins, but a purchase detail overrides a sale one.
            if (! isset($index[$key]) || $isPurchase) {
                $index[$key] = $d['VALUE'] ?? null;
            }
        }

        return function (string $groupName) use ($index): ?string {
            $g = $this->normaliseTaxName($groupName);
            if ($g === '') {
                return null;
            }
            if (array_key_exists($g, $index)) {
                return $this->numericRate($index[$g]);
            }
            foreach ($index as $k => $value) {
                if ($k !== '' && (str_contains($k, $g) || str_contains($g, $k))) {
                    return $this->numericRate($value);
                }
            }
            return null;
        };
    }

    /** Normalise a tax name for matching (drop qualifiers/side, keep the core). */
    protected function normaliseTaxName(string $s): string
    {
        $s = mb_strtolower($s);
        $s = preg_replace('/\(.*?\)/', ' ', $s);            // drop parenthetical qualifiers
        $s = preg_replace('/[^a-z0-9]+/', ' ', $s);         // punctuation → space
        $s = preg_replace('/\b(input|output|imported)\b/', ' ', $s); // drop direction words
        return trim(preg_replace('/\s+/', ' ', $s));
    }

    protected function numericRate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        return number_format((float) $value, 2, '.', '');
    }

    /** A short uppercase abbreviation from a tax group name (e.g. "SR", "ZR"). */
    protected function taxAbbreviation(string $name): string
    {
        $words = preg_split('/\s+/', trim($name)) ?: [];
        $acr   = '';
        foreach ($words as $w) {
            if ($w !== '') {
                $acr .= mb_strtoupper(mb_substr($w, 0, 1));
            }
        }
        // Ensure at least two characters for readability.
        return mb_strlen($acr) >= 2 ? $acr : mb_strtoupper(mb_substr($name, 0, 3));
    }

    // ── Products (Sage ITEM → Gonyeti products & services) ────────

    /**
     * Pull Sage ITEMs into the Gonyeti products module. De-dup: existing Sage
     * link (mapping), then product name; otherwise create a sparse product.
     * ITEMTYPE → product type, and the item's tax group is matched to a Gonyeti
     * Tax (by name, from the item-tax-group pull) → tax_id. Existing, user-entered
     * fields are never overwritten. Items land as sell+buy so they appear in the
     * invoice/bill product pickers.
     */
    protected function pullProducts(array $options = []): array
    {
        // Buy/sell context from the calling screen: products & bills → buy;
        // invoices → sell; "all" → both. Default to both when unspecified.
        $buy  = ! empty($options['buy']);
        $sell = ! empty($options['sell']);
        if (! $buy && ! $sell) {
            $buy = $sell = true;
        }

        $rows = $this->readAll('ITEM', ['ITEMID', 'NAME', 'ITEMTYPE', 'TAXGROUP.NAME', 'GLGROUP', 'STATUS'], 'RECORDNO > 0');

        // Tax lookup by lower-cased name (mirrors the Sage item tax group names).
        $taxByName = Tax::all()->keyBy(fn ($t) => mb_strtolower((string) $t->name));

        $s = ['created' => 0, 'linked' => 0, 'skipped' => 0, 'failed' => 0];
        foreach ($rows as $row) {
            $itemId = $row['ITEMID'] ?? null;
            $name   = trim($row['NAME'] ?? '');
            if (! $itemId || $name === '') {
                $s['skipped']++;
                continue;
            }

            try {
                $mapping = IntegrationMapping::where([
                    'company_integration_id' => $this->integration->id,
                    'entity_type'            => 'product_item',
                    'external_id'            => $itemId,
                ])->first();

                $product = ($mapping ? Product::find($mapping->local_id) : null)
                    ?? Product::where('name', $name)->first();

                $isNew = false;
                if (! $product) {
                    $product                 = new Product();
                    $product->name           = $name;
                    $product->user_id        = $this->creatorId;
                    $product->product_number = $this->nextNumber(Product::class, 'P');
                    $product->status         = 1;
                    $isNew = true;
                }

                // Buy/sell: turn ON the flags for this context; never turn a flag
                // off (a product listed for both must keep both).
                if ($buy) {
                    $product->buy = 1;
                }
                if ($sell) {
                    $product->sell = 1;
                }

                // Fill type + tax + GL group only when empty — never clobber edits.
                $product->type = $product->type ?: \App\Services\Sage\Mappers\SageProductItemMapper::gonyetiType($row['ITEMTYPE'] ?? null);
                $product->gl_group = $product->gl_group ?: (trim($row['GLGROUP'] ?? '') ?: null);
                if (empty($product->tax_id)) {
                    $grp = mb_strtolower(trim($row['TAXGROUP.NAME'] ?? ''));
                    if ($grp !== '' && isset($taxByName[$grp])) {
                        $product->tax_id = $taxByName[$grp]->id;
                    }
                }
                $product->saveQuietly();

                $this->linkMapping('product_item', $product, $itemId, $name);

                $isNew ? $s['created']++ : $s['linked']++;
            } catch (Throwable $e) {
                $s['failed']++;
            }
        }

        return $s;
    }

    // ── Helpers ──────────────────────────────────────────────────

    /** Read every record for a query, paging via readMore. */
    protected function readAll(string $object, array $fields, string $query): array
    {
        $pageSize = (int) config('sageintacct.pull.page_size', 1000);

        $res = $this->driver->readByQuery($object, $fields, $query, $pageSize);
        if (empty($res['success'])) {
            throw new \RuntimeException($res['error'] ?? "readByQuery {$object} failed");
        }

        $rows      = $res['data'] ?? [];
        $resultId  = $res['resultId'] ?? null;
        $remaining = (int) ($res['remaining'] ?? 0);

        while ($remaining > 0 && $resultId) {
            $more = $this->driver->readMore($resultId);
            if (empty($more['success'])) {
                break;
            }
            $rows      = array_merge($rows, $more['data'] ?? []);
            $resultId  = $more['resultId'] ?? null;
            $remaining = (int) ($more['remaining'] ?? 0);
        }

        return $rows;
    }

    /** Build a "CLASSID LIKE 'H%' OR CLASSID LIKE 'FHH%'" clause from prefixes. */
    protected function classQuery(array $prefixes): string
    {
        $clauses = array_map(fn ($p) => "CLASSID LIKE '" . str_replace("'", '', $p) . "%'", $prefixes);

        return '(' . implode(' OR ', $clauses) . ')';
    }

    /** Company initials + letter + zero-padded next id (mirrors the Import numbering). */
    protected function nextNumber(string $modelClass, string $letter): string
    {
        $n = ((int) $modelClass::max('id')) + 1;

        return $this->companyInitials() . $letter . str_pad((string) $n, 5, '0', STR_PAD_LEFT);
    }

    protected function companyInitials(): string
    {
        $name  = optional(Company::find($this->companyId))->name ?: 'GY';
        $words = explode(' ', trim($name));
        $init  = ($words[0][0] ?? 'G') . ($words[1][0] ?? '');

        return strtoupper($init);
    }

    /** Record the Sage link in integration_mappings (fleet entities). */
    protected function linkMapping(string $entityType, Model $model, string $externalId, ?string $ref): void
    {
        $mapping = $this->mappingFor($this->integration, $entityType, $model);
        $mapping->local_model     = get_class($model);
        $mapping->local_reference = $ref;
        $mapping->markSynced($externalId, $ref);
    }

    protected function logSummary(string $entity, array $summary): void
    {
        IntegrationLog::create([
            'company_integration_id' => $this->integration->id,
            'direction'              => 'inbound',
            'action'                 => 'pull',
            'status'                 => empty($summary['error']) ? 'ok' : 'fail',
            'message'                => $summary['error'] ?? null,
            'meta'                   => array_merge(['entity' => $entity], $summary),
        ]);
    }
}
