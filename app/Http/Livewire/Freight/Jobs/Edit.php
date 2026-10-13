<?php

namespace App\Http\Livewire\Freight\Jobs;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\Destination;
use App\Models\Employee;
use App\Models\FreightJob;
use App\Models\FreightServiceType;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Edits only the job-level "Customer & Service" fields (the same section
 * boundary Create.php uses). Shipment particulars, cargo and parties are
 * managed per-shipment via the Show page's own tabs, not re-exposed here.
 */
class Edit extends Component
{
    public $job;
    public $employee;

    public $customers;
    public $freight_service_types;
    public $currencies;
    public $destinations;
    public $officers;
    public $quotations;

    public $customer_id;
    public $customer_reference;
    public $freight_service_type_id;
    public $salesperson_id;
    public $operations_officer_id;
    public $clearing_officer_id;
    public $quotation_id;
    public $incoterm;
    public $currency_id;
    public $import_export_type;
    public $primary_transport_mode;
    public $from;
    public $to;
    public $status;
    public $notes;

    public const STATUSES = [
        'draft', 'confirmed', 'in_progress', 'customs_clearance', 'port_storage',
        'transport_arranged', 'delivered', 'invoiced', 'closed', 'cancelled',
    ];

    public function mount($jobId)
    {
        $this->job = FreightJob::findOrFail($jobId);
        $this->employee = Auth::user()->employee;

        $this->customers = Customer::orderBy('name', 'asc')->get();
        $this->freight_service_types = FreightServiceType::orderBy('name', 'asc')->get();
        $this->currencies = Currency::orderBy('name', 'asc')->get();
        $this->destinations = Destination::with('country')->orderBy('city', 'asc')->get();
        $this->officers = Employee::where('company_id', $this->employee->company_id)
            ->where('status', 1)
            ->where('archive', 0)
            ->whereNotNull('user_id')
            ->orderBy('name', 'asc')
            ->orderBy('surname', 'asc')
            ->get();
        $this->quotations = Quotation::with('customer')
            ->whereYear('date', date('Y'))
            ->latest()
            ->get();

        $this->customer_id = $this->job->customer_id;
        $this->customer_reference = $this->job->customer_reference;
        $this->freight_service_type_id = $this->job->freight_service_type_id;
        $this->salesperson_id = $this->job->salesperson_id;
        $this->operations_officer_id = $this->job->operations_officer_id;
        $this->clearing_officer_id = $this->job->clearing_officer_id;
        $this->quotation_id = $this->job->quotation_id;
        $this->incoterm = $this->job->incoterm;
        $this->currency_id = $this->job->currency_id;
        $this->import_export_type = $this->job->import_export_type;
        $this->primary_transport_mode = $this->job->primary_transport_mode;
        $this->from = $this->job->from;
        $this->to = $this->job->to;
        $this->status = $this->job->status;
        $this->notes = $this->job->notes;
    }

    protected function rules()
    {
        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'freight_service_type_id' => 'nullable|integer|exists:freight_service_types,id',
            'primary_transport_mode' => 'required|string',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'status' => 'required|string',
        ];
    }

    public function refresh($category)
    {
        if ($category === 'customers') {
            $this->customers = Customer::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Customers Refreshed Successfully!!.']);
        } elseif ($category === 'freight_service_types') {
            $this->freight_service_types = FreightServiceType::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Service Types Refreshed Successfully!!.']);
        } elseif ($category === 'currencies') {
            $this->currencies = Currency::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Currencies Refreshed Successfully!!.']);
        } elseif ($category === 'quotations') {
            $this->quotations = Quotation::with('customer')->whereYear('date', date('Y'))->latest()->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Quotations Refreshed Successfully!!.']);
        } elseif ($category === 'destinations') {
            $this->destinations = Destination::with('country')->orderBy('city', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Destinations Refreshed Successfully!!.']);
        }
    }

    public function update()
    {
        $this->validate();

        $this->job->update([
            'customer_id' => $this->customer_id,
            'customer_reference' => $this->customer_reference,
            'freight_service_type_id' => $this->freight_service_type_id,
            'salesperson_id' => $this->salesperson_id,
            'operations_officer_id' => $this->operations_officer_id,
            'clearing_officer_id' => $this->clearing_officer_id,
            'quotation_id' => $this->quotation_id,
            'incoterm' => $this->incoterm,
            'currency_id' => $this->currency_id,
            'import_export_type' => $this->import_export_type,
            'primary_transport_mode' => $this->primary_transport_mode,
            'from' => $this->from,
            'to' => $this->to,
            'status' => $this->status,
            'notes' => $this->notes,
        ]);

        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Freight Job ' . $this->job->job_number . ' updated successfully!']);

        return redirect()->route('freight.jobs.show', $this->job->id);
    }

    public function render()
    {
        return view('livewire.freight.jobs.edit', [
            'incoterms' => FreightJob::INCOTERMS,
            'statuses' => self::STATUSES,
        ]);
    }
}
