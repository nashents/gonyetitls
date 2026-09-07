<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Expense;

/**
 * Single source of truth for the small set of "core" Expense categories that
 * fuel/trip billing flows look up by hardcoded name (see FuelJournalService,
 * BillJournalService) - used by both ExpenseSeeder (fresh installs) and the
 * "Resolve Default Expenses" action on the Expenses list (repairing drift on
 * an already-running instance, e.g. gonyetitls production 2026-09-07: Fuel
 * Topup's account_id was still pointing at the pre-split "Fuel" account,
 * renamed to Fuel - Ops when Fuel - COGS was introduced, because nothing had
 * re-run the seeder against that account).
 *
 * Unlike a plain updateOrCreate, this restores a soft-deleted row instead of
 * leaving it deleted and silently inserting a second row with the same name.
 */
class DefaultExpenseResolver
{
    /** name => [account name, type] */
    private const DEFINITIONS = [
        'Fuel Topup' => ['Fuel - COGS', 'Direct'],
        'Transporter Payment' => ['Creditor Payment', 'Direct'],
    ];

    /**
     * @return array<int, string> one human-readable line per expense describing what changed
     */
    public function resolve(): array
    {
        $summary = [];

        foreach (self::DEFINITIONS as $name => [$accountName, $type]) {
            $account = Account::where('name', $accountName)->first();

            if (!$account) {
                $summary[] = "{$name}: SKIPPED - no \"{$accountName}\" account found.";
                continue;
            }

            $expense = Expense::withTrashed()->where('name', $name)->first();
            $changes = [];

            if (!$expense) {
                Expense::create([
                    'user_id' => null,
                    'account_id' => $account->id,
                    'name' => $name,
                    'type' => $type,
                    'is_locked' => true,
                ]);
                $summary[] = "{$name}: created.";
                continue;
            }

            if ($expense->trashed()) {
                $expense->restore();
                $changes[] = 'restored';
            }

            if ($expense->account_id !== $account->id) {
                $changes[] = "account {$expense->account?->name} -> {$accountName}";
                $expense->account_id = $account->id;
            }

            if ($expense->type !== $type) {
                $changes[] = "type {$expense->type} -> {$type}";
                $expense->type = $type;
            }

            if (!$expense->is_locked) {
                $changes[] = 'relocked';
                $expense->is_locked = true;
            }

            if ($expense->user_id !== null) {
                $changes[] = 'unlinked from user';
                $expense->user_id = null;
            }

            if ($changes) {
                $expense->save();
                $summary[] = "{$name}: " . implode(', ', $changes) . '.';
            } else {
                $summary[] = "{$name}: already correct.";
            }
        }

        return $summary;
    }
}
