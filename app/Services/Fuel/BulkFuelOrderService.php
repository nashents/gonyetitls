<?php

namespace App\Services\Fuel;

use App\Mail\PendingNotificationEmails;
use App\Models\Driver;
use App\Models\Expense;
use App\Models\Fuel;
use App\Models\Notification;
use App\Models\Trip;
use App\Models\TripExpense;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Bulk fuel orders: one station + shared details, one row per truck. Each row
 * becomes an ordinary pending Fuel order - created exactly as the single
 * order form does (Fuels\Index::store): trip-linked orders get their
 * "Fuel - COGS" TripExpense and the trip's cost of sales is recalculated, and
 * the "Fuel Order Authorization" (Before) notifications go out per order.
 * Approval is the existing per-order / bulk authorization on the Pending page.
 */
class BulkFuelOrderService
{
    /**
     * @param array $header container_id, date, currency_id, unit_price, exchange_rate, deduct_from, fuel_type
     * @param array $rows   horse_id, employee_id, trip_id, category, mileage, quantity, is_full_tank, fillup, comments
     * @return Collection<int, Fuel>
     */
    public function create(array $header, array $rows, $company): Collection
    {
        $fuels = DB::transaction(function () use ($header, $rows, $company) {
            $fuels = collect();

            foreach ($rows as $row) {
                $fuels->push($this->createOrder($header, $row, $company));
            }

            return $fuels;
        });

        $this->notify($fuels, $company);

        return $fuels;
    }

    private function createOrder(array $header, array $row, $company): Fuel
    {
        $quantity = (float) $row['quantity'];
        $amount = round($quantity * (float) $header['unit_price'], 2);
        $exchangeRate = $header['exchange_rate'] !== null && $header['exchange_rate'] !== '' ? (float) $header['exchange_rate'] : null;
        $trip = !empty($row['trip_id']) ? Trip::find($row['trip_id']) : null;

        $fuel = new Fuel;
        $fuel->user_id = Auth::id();
        $fuel->order_number = $this->orderNumber($company);
        $fuel->employee_id = $row['employee_id'];
        $fuel->deduct_from = $header['deduct_from'] ?? 'quantity';
        $fuel->fuel_type = $header['fuel_type'];
        $fuel->is_full_tank = $row['is_full_tank'];
        $fuel->type = 'Horse';
        $fuel->horse_id = $row['horse_id'];
        $fuel->container_id = $header['container_id'];
        $fuel->date = $header['date'];
        $fuel->unit_price = $header['unit_price'];
        $fuel->quantity = $quantity;
        $fuel->currency_id = $header['currency_id'];
        $fuel->amount = $amount;
        $fuel->exchange_rate = $exchangeRate;
        $fuel->exchange_amount = $exchangeRate ? round($exchangeRate * $amount, 2) : null;
        $fuel->odometer = $row['mileage'];
        $fuel->fillup = $row['fillup'];
        $fuel->comments = $row['comments'] ?? null;
        $fuel->supplied_by_customer = false;

        if ($trip) {
            $fuel->trip_id = $trip->id;
            $fuel->driver_id = $trip->driver_id;
            $fuel->category = $row['category'] ?: 'Self';
        } else {
            // The fuel order document prints the driver from driver_id.
            $fuel->driver_id = Driver::where('employee_id', $row['employee_id'])->value('id');
        }

        $fuel->save();

        if ($trip) {
            $this->addTripExpense($fuel, $trip, $fuel->category);
        }

        return $fuel;
    }

    private function addTripExpense(Fuel $fuel, Trip $trip, string $category): void
    {
        $tripExpense = new TripExpense;
        $tripExpense->user_id = $fuel->user_id;
        $tripExpense->trip_id = $trip->id;
        $tripExpense->fuel_id = $fuel->id;
        $tripExpense->expense_id = Expense::where('name', Expense::FUEL_TOPUP)->value('id');
        $tripExpense->currency_id = $fuel->currency_id;
        $tripExpense->category = $category;
        $tripExpense->amount = $fuel->amount;
        $tripExpense->exchange_rate = $fuel->exchange_rate;
        $tripExpense->exchange_amount = $fuel->exchange_amount;
        $tripExpense->save();

        $this->recalculateTrip($trip);
    }

    // Same figures Fuels\Index::store() writes: cost of sales = the trip's "Self" expenses in company currency.
    private function recalculateTrip(Trip $trip): void
    {
        $companyCurrencyId = optional(optional(Auth::user()->employee)->company)->currency_id;

        $costOfSales = TripExpense::where('trip_id', $trip->id)
            ->where('category', 'Self')
            ->get()
            ->sum(function (TripExpense $expense) use ($companyCurrencyId) {
                $value = $expense->currency_id == $companyCurrencyId ? $expense->amount : $expense->exchange_amount;

                return is_numeric($value) ? (float) $value : 0;
            });

        $trip->refresh();
        $trip->cost_of_sales = $costOfSales;
        $turnover = $trip->turnover;

        if ($costOfSales > 0 && is_numeric($turnover) && $turnover > 0) {
            $trip->net_profit = $turnover - $costOfSales;
            if ($trip->net_profit > 0) {
                $trip->markup_percentage = ($trip->net_profit / $costOfSales) * 100;
                $trip->net_profit_percentage = ($trip->net_profit / $turnover) * 100;
            }
        } else {
            $trip->net_profit_percentage = 100;
            $trip->markup_percentage = 100;
        }

        $trip->update();
    }

    // Sent after commit, and a mail failure never undoes the orders.
    private function notify(Collection $fuels, $company): void
    {
        $notifications = Notification::where('when', 'Before')->where('category', 'Fuel Order Authorization')->where('status', 1)->get();

        foreach ($notifications as $notification) {
            $email = $notification->email ?? optional($notification->employee)->email;
            if (! $email) {
                continue;
            }
            foreach ($fuels as $fuel) {
                try {
                    Mail::to($email)->send(new PendingNotificationEmails($company, $notification, $fuel));
                } catch (\Throwable $e) {
                    Log::warning('Bulk fuel order notification failed', ['fuel_id' => $fuel->id, 'error' => $e->getMessage()]);
                }
            }
        }
    }

    // Same numbering as Fuels\Index::orderNumber(): company initials + FO + next id.
    private function orderNumber($company): string
    {
        $name = optional(Auth::user()->company)->name ?? optional($company)->name ?? '';
        $words = explode(' ', $name);
        $initials = isset($words[1][0]) ? $words[0][0].$words[1][0] : ($words[0][0] ?? '');

        $lastId = Fuel::withTrashed()->max('id') ?? 0;

        return $initials.'FO'.str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
    }
}
