<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Which accounts a manual Deposit / Withdrawal may post its contra side to.
 *
 * Allowed = account in one of the direction's account types, not flagged
 * is_control and not blocked by name. Control accounts (AR, AP, inventory,
 * recoverables, customer advances...) are owned by their own documents, so a
 * manual entry would put the ledger out of step with the sub-ledger. Cash &
 * Bank is never offered - money between own accounts is a Transfer.
 */
class CashTransactionAccounts
{
    const DEPOSIT = 'deposit';
    const WITHDRAWAL = 'withdrawal';

    // Cash in: the owner/lenders putting money in, other income, refunds of assets
    const DEPOSIT_TYPES = [
        'Other Income',
        'Uncategorized Income',
        'Due to You & Other Business Owners',
        'Business Owner Contribution & Drawing',
        'Loan & Line of Credit',
        'Credit Cards',
        'Other Short-Term Asset',
        'Other Long-Term Asset',
        'Other Short-Term Liability',
        'Other Long-Term Liability',
    ];

    // Cash out: running costs, repayments, drawings, asset purchases, tax/payroll remittances
    const WITHDRAWAL_TYPES = [
        'Operating Expense',
        'Uncategorized Expense',
        'Payment Processing Fee',
        'Due to You & Other Business Owners',
        'Business Owner Contribution & Drawing',
        'Loan & Line of Credit',
        'Credit Cards',
        'Property, Plant, Equipment',
        'Other Short-Term Asset',
        'Other Long-Term Asset',
        'Other Short-Term Liability',
        'Other Long-Term Liability',
        'Sales Taxes',
        'Due for Payroll',
    ];

    // Inside an allowed type but posted only by the system / year-end
    const BLOCKED = [
        'Opening Balance Equity',
        'Retained Earnings/Deficit',
        'Depriciation - Expense',
        'Fuel - Ops',
    ];

    // The one control account a deposit may use - it needs a customer
    const CUSTOMER_DEPOSITS = 'Customer Deposits';

    public function query(string $direction): Builder
    {
        $types = $direction === self::DEPOSIT ? self::DEPOSIT_TYPES : self::WITHDRAWAL_TYPES;

        return Account::query()
            ->active()
            ->where(function ($q) use ($direction, $types) {
                $q->where(function ($q) use ($types) {
                    $q->whereHas('account_type', fn ($t) => $t->whereIn('name', $types))
                      ->where(fn ($c) => $c->whereNull('is_control')->orWhere('is_control', false))
                      ->whereNotIn('name', self::BLOCKED);
                });

                if ($direction === self::DEPOSIT) {
                    $q->orWhere('name', self::CUSTOMER_DEPOSITS);
                }
            });
    }

    public function allows(string $direction, $accountId): bool
    {
        return $accountId && $this->query($direction)->whereKey($accountId)->exists();
    }

    public function requiresCustomer($accountId): bool
    {
        return $accountId && Account::whereKey($accountId)->where('name', self::CUSTOMER_DEPOSITS)->exists();
    }

    // Account types holding only their allowed accounts, for the Category <optgroup>s
    public function grouped(string $direction): Collection
    {
        $accounts = $this->query($direction)->with('currency')->orderBy('name')->get()->groupBy('account_type_id');

        return AccountType::whereIn('id', $accounts->keys())->orderBy('id')->get()
            ->each(fn ($type) => $type->setRelation('accounts', $accounts[$type->id]));
    }
}
