<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\Customer;
use App\Models\AccountType;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\CashFlowCalculator;
use Illuminate\Support\Facades\Auth;
use App\Services\SalesTaxCalculator;
use App\Services\AgedPayablesCalculator;
use App\Services\BalanceSheetCalculator;
use App\Services\CustomerIncomeCalculator;
use App\Services\VendorPurchaseCalculator;
use App\Services\AccountBalanceCalculator;
use App\Services\IncomeStatementCalculator;
use App\Services\AgedReceivablesCalculator;
use App\Services\TrialBalanceCalculator;
use App\Services\AccountTransactionsCalculator;
use App\Http\Livewire\Reports\IncomeStatements\Index as IncomeStatementReport;
use App\Http\Livewire\Reports\BalanceSheets\Index as BalanceSheetReport;
use App\Http\Livewire\Reports\SalesTax\Index as SalesTaxReport;
use App\Http\Livewire\Reports\IncomeByCustomers\Index as IncomeByCustomerReport;
use App\Http\Livewire\Reports\PurchaseByVendors\Index as PurchaseByVendorReport;

class ReportController extends Controller
{
    public function index(){
        return view('reports.index');
    }
    public function incomeStatement(){
        return view('reports.income_statements.index');
    }

    public function incomeStatementPdf(Request $request)
    {
        $pdf = Pdf::loadView('reports.income_statements.pdf', $this->buildIncomeStatementData($request));

        return $pdf->download('income-statement.pdf');
    }

    public function incomeStatementPrint(Request $request)
    {
        return view('reports.income_statements.print', $this->buildIncomeStatementData($request));
    }

    /**
     * Data shared by the PDF export and the print view, so the two (and
     * the live Livewire report) never calculate the numbers differently.
     */
    private function buildIncomeStatementData(Request $request): array
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        $from = $request->query('from', Carbon::now()->firstOfYear()->format('Y-m-d'));
        $to = $request->query('to', Carbon::today()->format('Y-m-d'));
        $selectedType = $request->query('type', IncomeStatementReport::ACCRUAL);
        $compact = $request->query('view') !== 'details';

        $incomeAccounts = AccountType::where('name', 'Income')->first()?->accounts ?? collect();
        $cogsAccounts = AccountType::where('name', 'Cost Of Goods Sold')->first()?->accounts ?? collect();
        $opexAccounts = AccountType::where('name', 'Operating Expense')->first()?->accounts ?? collect();

        $calculator = new IncomeStatementCalculator(
            $from,
            $to,
            $selectedType === IncomeStatementReport::CASH_BASIS,
            $company?->currency_id
        );

        [$totalIncome, $incomeByAccount] = $calculator->incomeAmounts();
        [$totalCogs, $cogsByAccount] = $calculator->expenseAmounts($cogsAccounts->pluck('id')->all());
        [$totalOpex, $opexByAccount] = $calculator->expenseAmounts($opexAccounts->pluck('id')->all());

        $grossProfit = $totalIncome - $totalCogs;
        $grossProfitPercentage = $totalIncome != 0 ? round(($grossProfit / $totalIncome) * 100, 2) : 0.0;

        $netProfit = $grossProfit - $totalOpex;
        $netProfitPercentage = $totalIncome != 0 ? round(($netProfit / $totalIncome) * 100, 2) : 0.0;

        return [
            'company' => $company,
            'currencyCode' => $company?->currency?->name ?? '',
            'from' => $from,
            'to' => $to,
            'selectedType' => $selectedType,
            'compact' => $compact,
            'income_accounts' => $incomeAccounts,
            'cost_of_goods_sold_accounts' => $cogsAccounts,
            'operating_expenses_accounts' => $opexAccounts,
            'income_by_account' => $incomeByAccount,
            'cost_of_goods_sold_by_account' => $cogsByAccount,
            'operating_expenses_by_account' => $opexByAccount,
            'total_income' => $totalIncome,
            'total_cost_of_goods_sold' => $totalCogs,
            'total_operating_expenses' => $totalOpex,
            'gross_profit' => $grossProfit,
            'gross_profit_percentage' => $grossProfitPercentage,
            'net_profit' => $netProfit,
            'net_profit_percentage' => $netProfitPercentage,
        ];
    }
    public function fuelConsumption(){
        return view('reports.fuel_consumption');
    }
    public function cashflow(){
        return view('reports.cashflows.index');
    }

    public function cashflowPdf(Request $request)
    {
        $pdf = Pdf::loadView('reports.cashflows.pdf', $this->buildCashFlowData($request));

        return $pdf->download('cash-flow-statement.pdf');
    }

    public function cashflowPrint(Request $request)
    {
        return view('reports.cashflows.print', $this->buildCashFlowData($request));
    }

    /**
     * Data shared by the Cash Flow PDF export and print view, so they
     * never calculate the numbers differently from each other or from
     * the live Livewire report.
     */
    private function buildCashFlowData(Request $request): array
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        $from = $request->query('from', Carbon::now()->firstOfYear()->format('Y-m-d'));
        $to = $request->query('to', Carbon::today()->format('Y-m-d'));
        $compact = $request->query('view') !== 'details';

        $calculator = new CashFlowCalculator($company->id ?? 0, $from, $to);
        $activities = $calculator->operatingActivities();

        $netOperatingCashFlow = $activities['receipts'] + $activities['payments'];
        $netIncreaseInCash = $netOperatingCashFlow + $activities['other'];

        return [
            'company' => $company,
            'currencyCode' => $company?->currency?->name ?? '',
            'from' => $from,
            'to' => $to,
            'compact' => $compact,
            'cash_received_from_customers' => $activities['receipts'],
            'cash_paid_to_vendors' => $activities['payments'],
            'other_movements' => $activities['other'],
            'net_operating_cash_flow' => $netOperatingCashFlow,
            'net_increase_in_cash' => $netIncreaseInCash,
            'beginning_cash_balance' => $calculator->cashBalanceAsOf($calculator->beginningDate()),
            'ending_cash_balance' => $calculator->cashBalanceAsOf($to),
        ];
    }
    public function balanceSheet(){
        return view('reports.balance_sheets.index');
    }

    public function balanceSheetPdf(Request $request)
    {
        $pdf = Pdf::loadView('reports.balance_sheets.pdf', $this->buildBalanceSheetData($request));

        return $pdf->download('balance-sheet.pdf');
    }

    public function balanceSheetPrint(Request $request)
    {
        return view('reports.balance_sheets.print', $this->buildBalanceSheetData($request));
    }

    /**
     * Data shared by the Balance Sheet PDF export and print view, so they
     * never calculate the numbers differently from each other or from
     * the live Livewire report.
     */
    private function buildBalanceSheetData(Request $request): array
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        $asOfDate = $request->query('as_of_date', Carbon::today()->format('Y-m-d'));
        $compact = $request->query('view') !== 'details';

        $calculator = new BalanceSheetCalculator($company->id ?? 0, $asOfDate);

        [$totalAssets, $assetsItems] = $calculator->groupBalances('Assets');
        [$totalLiabilities, $liabilitiesItems] = $calculator->groupBalances(BalanceSheetReport::LIABILITIES_GROUP, creditNormal: true);

        [$equityTotal, $equityItems] = $calculator->groupBalances('Equity', creditNormal: true);
        $netIncome = $calculator->netIncomeToDate();
        $equityItems[] = ['label' => 'Net Income', 'amount' => $netIncome];
        $totalEquity = $equityTotal + $netIncome;

        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        return [
            'company' => $company,
            'currencyCode' => $company?->currency?->name ?? '',
            'as_of_date' => $asOfDate,
            'compact' => $compact,
            'assets_items' => $assetsItems,
            'total_assets' => $totalAssets,
            'liabilities_items' => $liabilitiesItems,
            'total_liabilities' => $totalLiabilities,
            'equity_items' => $equityItems,
            'net_income' => $netIncome,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,
            'is_balanced' => abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01,
        ];
    }

    public function trialBalance(){
        return view('reports.trial_balance');
    }

    public function fleetSubledger(){
        return view('reports.fleet_subledger');
    }

    public function salesTax(){
        return view('reports.sales_tax.index');
    }

    public function salesTaxPdf(Request $request)
    {
        $pdf = Pdf::loadView('reports.sales_tax.pdf', $this->buildSalesTaxData($request));

        return $pdf->download('sales-tax-report.pdf');
    }

    public function salesTaxPrint(Request $request)
    {
        return view('reports.sales_tax.print', $this->buildSalesTaxData($request));
    }

    /**
     * Data shared by the Sales Tax PDF export and print view, so they
     * never calculate the numbers differently from each other or from
     * the live Livewire report.
     */
    private function buildSalesTaxData(Request $request): array
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        $from = $request->query('from', Carbon::now()->firstOfYear()->format('Y-m-d'));
        $to = $request->query('to', Carbon::today()->format('Y-m-d'));
        $selectedType = $request->query('type', SalesTaxReport::ACCRUAL);
        $compact = $request->query('view') !== 'details';

        $calculator = new SalesTaxCalculator(
            $from,
            $to,
            $selectedType === SalesTaxReport::CASH_BASIS,
            $company?->currency_id
        );

        [$totalTaxableSales, $totalTaxCollected, $outputByTax] = $calculator->outputTax();
        [$totalTaxablePurchases, $totalTaxPaid, $inputByTax] = $calculator->inputTax();

        $labels = $calculator->taxLabels(array_unique(array_merge(array_keys($outputByTax), array_keys($inputByTax))));

        $toRows = function (array $byTax) use ($labels) {
            $rows = [];
            foreach ($byTax as $taxId => $amounts) {
                $rows[] = ['label' => $labels[$taxId] ?? 'No Tax', 'taxable' => $amounts['taxable'], 'tax' => $amounts['tax']];
            }
            return $rows;
        };

        return [
            'company' => $company,
            'currencyCode' => $company?->currency?->name ?? '',
            'from' => $from,
            'to' => $to,
            'selectedType' => $selectedType,
            'compact' => $compact,
            'output_tax_rows' => $toRows($outputByTax),
            'total_taxable_sales' => $totalTaxableSales,
            'total_tax_collected' => $totalTaxCollected,
            'input_tax_rows' => $toRows($inputByTax),
            'total_taxable_purchases' => $totalTaxablePurchases,
            'total_tax_paid' => $totalTaxPaid,
            'net_tax_payable' => $totalTaxCollected - $totalTaxPaid,
        ];
    }

    public function incomeByCustomer(){
        return view('reports.income_by_customers.index');
    }

    public function incomeByCustomerPdf(Request $request)
    {
        $pdf = Pdf::loadView('reports.income_by_customers.pdf', $this->buildIncomeByCustomerData($request));

        return $pdf->download('income-by-customer.pdf');
    }

    public function incomeByCustomerPrint(Request $request)
    {
        return view('reports.income_by_customers.print', $this->buildIncomeByCustomerData($request));
    }

    /**
     * Data shared by the Income by Customer PDF export and print view, so
     * they never calculate the numbers differently from each other or
     * from the live Livewire report.
     */
    private function buildIncomeByCustomerData(Request $request): array
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        $from = $request->query('from', Carbon::now()->firstOfYear()->format('Y-m-d'));
        $to = $request->query('to', Carbon::today()->format('Y-m-d'));
        $selectedType = $request->query('type', IncomeByCustomerReport::ACCRUAL);
        $topTenOnly = $request->query('view') !== 'details';

        $calculator = new CustomerIncomeCalculator(
            $from,
            $to,
            $selectedType === IncomeByCustomerReport::CASH_BASIS,
            $company?->currency_id
        );

        [$totalIncome, $incomeByCustomer] = $calculator->incomeByCustomer();
        $names = Customer::whereIn('id', array_keys($incomeByCustomer))->pluck('name', 'id')->all();

        return [
            'company' => $company,
            'currencyCode' => $company?->currency?->name ?? '',
            'from' => $from,
            'to' => $to,
            'selectedType' => $selectedType,
            'customer_items' => IncomeByCustomerReport::buildItems($incomeByCustomer, $names, $topTenOnly),
            'total_income' => $totalIncome,
        ];
    }

    public function agedReceivables(){
        return view('reports.aged_receivables.index');
    }

    public function agedReceivablesPdf(Request $request)
    {
        $pdf = Pdf::loadView('reports.aged_receivables.pdf', $this->buildAgedReceivablesData($request))
            ->setPaper('a4', 'landscape');

        return $pdf->download('aged-receivables.pdf');
    }

    public function agedReceivablesPrint(Request $request)
    {
        return view('reports.aged_receivables.print', $this->buildAgedReceivablesData($request));
    }

    /**
     * Data shared by the Aged Receivables PDF export and print view, so
     * they never calculate the numbers differently from each other or
     * from the live Livewire report.
     */
    private function buildAgedReceivablesData(Request $request): array
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        $asOfDate = $request->query('as_of_date', Carbon::today()->format('Y-m-d'));
        $compact = $request->query('view') !== 'details';

        $calculator = new AgedReceivablesCalculator($asOfDate, $company?->currency_id);
        [$customerRows, $grandTotals] = $calculator->byCustomer();

        return [
            'company' => $company,
            'currencyCode' => $company?->currency?->name ?? '',
            'as_of_date' => $asOfDate,
            'compact' => $compact,
            'customer_rows' => $customerRows,
            'grand_totals' => $grandTotals,
        ];
    }

    public function purchaseByVendor(){
        return view('reports.purchase_by_vendors.index');
    }

    public function purchaseByVendorPdf(Request $request)
    {
        $pdf = Pdf::loadView('reports.purchase_by_vendors.pdf', $this->buildPurchaseByVendorData($request));

        return $pdf->download('purchase-by-vendor.pdf');
    }

    public function purchaseByVendorPrint(Request $request)
    {
        return view('reports.purchase_by_vendors.print', $this->buildPurchaseByVendorData($request));
    }

    /**
     * Data shared by the Purchase by Vendor PDF export and print view, so
     * they never calculate the numbers differently from each other or
     * from the live Livewire report.
     */
    private function buildPurchaseByVendorData(Request $request): array
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        $from = $request->query('from', Carbon::now()->firstOfYear()->format('Y-m-d'));
        $to = $request->query('to', Carbon::today()->format('Y-m-d'));
        $selectedType = $request->query('type', PurchaseByVendorReport::ACCRUAL);
        $topTenOnly = $request->query('view') !== 'details';

        $calculator = new VendorPurchaseCalculator(
            $from,
            $to,
            $selectedType === PurchaseByVendorReport::CASH_BASIS,
            $company?->currency_id
        );

        [$totalPurchases, $purchasesByVendor] = $calculator->purchasesByVendor();
        $names = Vendor::whereIn('id', array_keys($purchasesByVendor))->pluck('name', 'id')->all();

        return [
            'company' => $company,
            'currencyCode' => $company?->currency?->name ?? '',
            'from' => $from,
            'to' => $to,
            'selectedType' => $selectedType,
            'vendor_items' => PurchaseByVendorReport::buildItems($purchasesByVendor, $names, $topTenOnly),
            'total_purchases' => $totalPurchases,
        ];
    }

    public function agedPayables(){
        return view('reports.aged_payables.index');
    }

    public function agedPayablesPdf(Request $request)
    {
        $pdf = Pdf::loadView('reports.aged_payables.pdf', $this->buildAgedPayablesData($request))
            ->setPaper('a4', 'landscape');

        return $pdf->download('aged-payables.pdf');
    }

    public function agedPayablesPrint(Request $request)
    {
        return view('reports.aged_payables.print', $this->buildAgedPayablesData($request));
    }

    /**
     * Data shared by the Aged Payables PDF export and print view, so they
     * never calculate the numbers differently from each other or from
     * the live Livewire report.
     */
    private function buildAgedPayablesData(Request $request): array
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        $asOfDate = $request->query('as_of_date', Carbon::today()->format('Y-m-d'));
        $compact = $request->query('view') !== 'details';

        $calculator = new AgedPayablesCalculator($asOfDate, $company?->currency_id);
        [$vendorRows, $grandTotals] = $calculator->byVendor();

        return [
            'company' => $company,
            'currencyCode' => $company?->currency?->name ?? '',
            'as_of_date' => $asOfDate,
            'compact' => $compact,
            'vendor_rows' => $vendorRows,
            'grand_totals' => $grandTotals,
        ];
    }

    public function accountBalances(){
        return view('reports.account_balances.index');
    }

    public function accountBalancesPdf(Request $request)
    {
        $pdf = Pdf::loadView('reports.account_balances.pdf', $this->buildAccountBalancesData($request))
            ->setPaper('a4', 'landscape');

        return $pdf->download('account-balances.pdf');
    }

    public function accountBalancesPrint(Request $request)
    {
        return view('reports.account_balances.print', $this->buildAccountBalancesData($request));
    }

    /**
     * Data shared by the Account Balances PDF export and print view, so
     * they never calculate the numbers differently from each other or
     * from the live Livewire report.
     */
    private function buildAccountBalancesData(Request $request): array
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        $from = $request->query('from', Carbon::now()->firstOfYear()->format('Y-m-d'));
        $to = $request->query('to', Carbon::today()->format('Y-m-d'));
        $compact = $request->query('view') !== 'details';

        $calculator = new AccountBalanceCalculator($company->id ?? 0, $from, $to);

        return [
            'company' => $company,
            'currencyCode' => $company?->currency?->name ?? '',
            'from' => $from,
            'to' => $to,
            'compact' => $compact,
            'groups' => $calculator->report(),
        ];
    }

    public function accountTransactions(){
        return view('reports.account_transactions.index');
    }

    public function accountTransactionsPdf(Request $request)
    {
        $pdf = Pdf::loadView('reports.account_transactions.pdf', $this->buildAccountTransactionsData($request))
            ->setPaper('a4', 'landscape');

        return $pdf->download('account-transactions.pdf');
    }

    public function accountTransactionsPrint(Request $request)
    {
        return view('reports.account_transactions.print', $this->buildAccountTransactionsData($request));
    }

    /**
     * Data shared by the Account Transactions PDF export and print view,
     * so they never calculate the numbers differently from each other or
     * from the live Livewire report.
     */
    private function buildAccountTransactionsData(Request $request): array
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        $accountId = (int) $request->query('account_id');
        $from = $request->query('from', Carbon::now()->firstOfYear()->format('Y-m-d'));
        $to = $request->query('to', Carbon::today()->format('Y-m-d'));
        $compact = $request->query('view') !== 'details';

        $account = Account::with('account_type.account_type_group')->find($accountId);
        $groupName = $account?->account_type?->account_type_group?->name;
        $creditNormal = in_array($groupName, AccountBalanceCalculator::CREDIT_NORMAL_GROUPS, true);

        $calculator = new AccountTransactionsCalculator($company->id ?? 0, $accountId, $from, $to, $creditNormal);

        $openingBalance = $calculator->openingBalance();
        $transactions = $calculator->transactions($openingBalance);
        $totalDebit = array_sum(array_column($transactions, 'debit'));
        $totalCredit = array_sum(array_column($transactions, 'credit'));
        $closingBalance = $transactions ? end($transactions)['running_balance'] : $openingBalance;

        return [
            'company' => $company,
            'currencyCode' => $company?->currency?->name ?? '',
            'account_name' => $account?->name ?? 'Account',
            'from' => $from,
            'to' => $to,
            'compact' => $compact,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'transactions' => $transactions,
        ];
    }

    public function trialBalancePdf(Request $request)
    {
        $pdf = Pdf::loadView('reports.trial_balance.pdf', $this->buildTrialBalanceData($request));

        return $pdf->download('trial-balance.pdf');
    }

    public function trialBalancePrint(Request $request)
    {
        return view('reports.trial_balance.print', $this->buildTrialBalanceData($request));
    }

    /**
     * Data shared by the Trial Balance PDF export and print view, so they
     * never calculate the numbers differently from each other or from
     * the live Livewire report.
     */
    private function buildTrialBalanceData(Request $request): array
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        $dateFrom = $request->query('date_from', Carbon::now()->startOfYear()->format('Y-m-d'));
        $dateTo = $request->query('date_to', Carbon::today()->format('Y-m-d'));
        // An empty search field is submitted as "search=" - the global
        // ConvertEmptyStringsToNull middleware turns that into a present-
        // but-null query value, so the query()-with-default form won't
        // catch it (defaults only apply when the key is absent entirely).
        $search = $request->query('search') ?? '';
        $hideZero = $request->query('hide_zero', '1') !== '0';

        $calculator = new TrialBalanceCalculator($company->id ?? 0, $dateFrom, $dateTo, $search, $hideZero);
        $lines = $calculator->lines();
        $totals = $calculator->totals($lines);

        return [
            'company' => $company,
            'currencyCode' => $company?->currency?->name ?? '',
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'grouped_lines' => $lines->groupBy('account_type_group_name'),
            'totals' => $totals,
            'is_balanced' => $calculator->isBalanced($totals),
        ];
    }
}
