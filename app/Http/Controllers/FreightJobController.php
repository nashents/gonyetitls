<?php

namespace App\Http\Controllers;

use App\Models\FreightJob;
use Illuminate\Support\Facades\Auth;

class FreightJobController extends Controller
{
    private function resolveCompany()
    {
        return Auth::user()->employee->company ?? Auth::user()->company ?? null;
    }
    public function index()
    {
        return view('freight.jobs.index');
    }

    public function pending()
    {
        return view('freight.jobs.index', ['authorization' => 'pending']);
    }

    public function approved()
    {
        return view('freight.jobs.index', ['authorization' => 'approved']);
    }

    public function rejected()
    {
        return view('freight.jobs.index', ['authorization' => 'rejected']);
    }

    public function create()
    {
        return view('freight.jobs.create');
    }

    public function show(FreightJob $job)
    {
        return view('freight.jobs.show')->with([
            'job' => $job,
        ]);
    }

    public function edit(FreightJob $job)
    {
        return view('freight.jobs.edit')->with([
            'job' => $job,
        ]);
    }

    public function sheet(FreightJob $job)
    {
        $job->load([
            'user', 'customer', 'freight_service_type', 'fromDestination.country', 'toDestination.country',
            'currency', 'quotation',
            'shipments.loading_point',
            'shipments.offloading_point',
            'shipments.containers.shipping_line',
            'shipments.cargo_items',
            'shipments.parties',
        ]);

        $salesperson = \App\Models\User::find($job->salesperson_id);
        $operationsOfficer = \App\Models\User::find($job->operations_officer_id);
        $clearingOfficer = \App\Models\User::find($job->clearing_officer_id);

        return view('freight.jobs.sheet')->with([
            'job' => $job,
            'company' => $this->resolveCompany(),
            'salesperson' => $salesperson,
            'operationsOfficer' => $operationsOfficer,
            'clearingOfficer' => $clearingOfficer,
        ]);
    }

    public function billOfLading(FreightJob $job)
    {
        $job->load([
            'user', 'customer', 'fromDestination.country', 'toDestination.country',
            'shipments.containers.shipping_line',
            'shipments.cargo_items',
            'shipments.parties',
            'shipments.transport_documents',
        ]);

        $shipment = $job->shipments->sortByDesc('id')->first();

        $billOfLadingDocument = $shipment?->transport_documents
            ->whereIn('document_type', ['master_bill_of_lading', 'house_bill_of_lading', 'sea_waybill'])
            ->first();

        return view('freight.jobs.bill-of-lading')->with([
            'job' => $job,
            'shipment' => $shipment,
            'document' => $billOfLadingDocument,
            'company' => $this->resolveCompany(),
        ]);
    }
}
