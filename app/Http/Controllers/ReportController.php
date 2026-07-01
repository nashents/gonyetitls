<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\AccountType;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\CashFlowCalculator;
use Illuminate\Support\Facades\Auth;
use App\Services\BalanceSheetCalculator;
use App\Services\IncomeStatementCalculator;
use App\Http\Livewire\Reports\IncomeStatements\Index as IncomeStatementReport;
use App\Http\Livewire\Reports\BalanceSheets\Index as BalanceSheetReport;

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
}
