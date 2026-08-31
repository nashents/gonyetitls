<?php

namespace App\Http\Controllers;

use App\Services\Freight\CustomsTurnaroundCalculator;
use App\Services\Freight\JobProfitabilityCalculator;
use App\Services\Freight\PortExposureCalculator;
use App\Services\Freight\UnbilledCostsAgingCalculator;
use App\Services\Freight\UninvoicedChargesAgingCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class FreightReportController extends Controller
{
    // ── Freight Job Profitability ────────────────────────────────────

    public function jobProfitability()
    {
        return view('freight.reports.job_profitability');
    }

    public function jobProfitabilityPdf(Request $request)
    {
        $pdf = Pdf::loadView('freight.reports.job_profitability.pdf', $this->buildJobProfitabilityData($request))
            ->setPaper('a4', 'landscape');

        return $pdf->download('freight-job-profitability.pdf');
    }

    public function jobProfitabilityPrint(Request $request)
    {
        return view('freight.reports.job_profitability.print', $this->buildJobProfitabilityData($request));
    }

    private function buildJobProfitabilityData(Request $request): array
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        $from = $request->query('from', Carbon::now()->firstOfYear()->format('Y-m-d'));
        $to = $request->query('to', Carbon::now()->format('Y-m-d'));
        $compact = $request->query('view') !== 'details';

        $calculator = new JobProfitabilityCalculator(
            $from,
            $to,
            $request->query('customer_id') ?: null,
            $request->query('salesperson_id') ?: null,
            $request->query('freight_service_type_id') ?: null,
            $request->query('primary_transport_mode') ?: null,
            $request->query('status') ?: null,
        );

        [$rows, $grandTotals] = $compact ? $calculator->summaryByCustomer() : $calculator->details();

        return [
            'company' => $company,
            'currencyCode' => $company?->currency?->name ?? '',
            'from' => $from,
            'to' => $to,
            'compact' => $compact,
            'rows' => $rows,
            'grand_totals' => $grandTotals,
        ];
    }

    // ── Port & Demurrage/Detention Exposure ──────────────────────────

    public function portExposure()
    {
        return view('freight.reports.port_exposure');
    }

    public function portExposurePdf(Request $request)
    {
        $pdf = Pdf::loadView('freight.reports.port_exposure.pdf', $this->buildPortExposureData($request))
            ->setPaper('a4', 'landscape');

        return $pdf->download('freight-port-exposure.pdf');
    }

    public function portExposurePrint(Request $request)
    {
        return view('freight.reports.port_exposure.print', $this->buildPortExposureData($request));
    }

    private function buildPortExposureData(Request $request): array
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;
        $compact = $request->query('view') !== 'details';

        $calculator = new PortExposureCalculator($request->query('shipping_line_vendor_id') ?: null);
        [$vendorRows, $grandTotals] = $calculator->byShippingLine();

        return [
            'company' => $company,
            'currencyCode' => $company?->currency?->name ?? '',
            'as_of_date' => Carbon::now()->format('Y-m-d'),
            'compact' => $compact,
            'vendor_rows' => $vendorRows,
            'grand_totals' => $grandTotals,
            'status_breakdown' => $calculator->statusBreakdown(),
        ];
    }

    // ── Customs Turnaround Time ──────────────────────────────────────

    public function customsTurnaround()
    {
        return view('freight.reports.customs_turnaround');
    }

    public function customsTurnaroundPdf(Request $request)
    {
        $pdf = Pdf::loadView('freight.reports.customs_turnaround.pdf', $this->buildCustomsTurnaroundData($request))
            ->setPaper('a4', 'landscape');

        return $pdf->download('freight-customs-turnaround.pdf');
    }

    public function customsTurnaroundPrint(Request $request)
    {
        return view('freight.reports.customs_turnaround.print', $this->buildCustomsTurnaroundData($request));
    }

    private function buildCustomsTurnaroundData(Request $request): array
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        $from = $request->query('from', Carbon::now()->firstOfYear()->format('Y-m-d'));
        $to = $request->query('to', Carbon::now()->format('Y-m-d'));
        $compact = $request->query('view') !== 'details';

        $calculator = new CustomsTurnaroundCalculator($from, $to, $request->query('clearing_officer_id') ?: null);
        [$rows, $overall] = $calculator->byClearingOfficer();

        if (!$compact) {
            $rows = $calculator->details();
        }

        return [
            'company' => $company,
            'from' => $from,
            'to' => $to,
            'compact' => $compact,
            'rows' => $rows,
            'overall' => $overall,
        ];
    }

    // ── Unbilled Freight Costs Aging ─────────────────────────────────

    public function unbilledCosts()
    {
        return view('freight.reports.unbilled_costs');
    }

    public function unbilledCostsPdf(Request $request)
    {
        $pdf = Pdf::loadView('freight.reports.unbilled_costs.pdf', $this->buildUnbilledCostsData($request))
            ->setPaper('a4', 'landscape');

        return $pdf->download('freight-unbilled-costs.pdf');
    }

    public function unbilledCostsPrint(Request $request)
    {
        return view('freight.reports.unbilled_costs.print', $this->buildUnbilledCostsData($request));
    }

    private function buildUnbilledCostsData(Request $request): array
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        $asOfDate = $request->query('as_of_date', Carbon::today()->format('Y-m-d'));
        $compact = $request->query('view') !== 'details';

        $calculator = new UnbilledCostsAgingCalculator($asOfDate);
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

    // ── Uninvoiced Freight Charges Aging ─────────────────────────────

    public function uninvoicedCharges()
    {
        return view('freight.reports.uninvoiced_charges');
    }

    public function uninvoicedChargesPdf(Request $request)
    {
        $pdf = Pdf::loadView('freight.reports.uninvoiced_charges.pdf', $this->buildUninvoicedChargesData($request))
            ->setPaper('a4', 'landscape');

        return $pdf->download('freight-uninvoiced-charges.pdf');
    }

    public function uninvoicedChargesPrint(Request $request)
    {
        return view('freight.reports.uninvoiced_charges.print', $this->buildUninvoicedChargesData($request));
    }

    private function buildUninvoicedChargesData(Request $request): array
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        $asOfDate = $request->query('as_of_date', Carbon::today()->format('Y-m-d'));
        $compact = $request->query('view') !== 'details';

        $calculator = new UninvoicedChargesAgingCalculator($asOfDate);
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
}
