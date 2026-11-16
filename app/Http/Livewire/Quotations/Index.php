<?php

namespace App\Http\Livewire\Quotations;

use Livewire\Component;
use App\Models\Quotation;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\QuotationsExport;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;
    use \App\Http\Livewire\Concerns\ValidatesUniqueReference;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $quotation_filter;
    // all | quotation | sales_order
    public $document_type = 'all';

    private $quotations;
    public $quotation_products;
    public $quotation;
    public $quotation_id;
    public $customers;

    // Convert to sales order modal
    public $convert_quotation_id;
    public $convert_quotation_number;
    public $convert_customer;
    public $sales_order_number_preview;
    public $sales_order_ref;
    public $sales_order_date;

    public function mount(){
        $this->resetPage();
        $this->quotation_filter = "created_at";
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedDocumentType()
    {
        $this->resetPage();
    }

    public function updatedFrom()
    {
        $this->resetPage();
    }

    public function updatedTo()
    {
        $this->resetPage();
    }


    public function closeQuoteShow($id){
        $this->quotation = Quotation::find($id);
        $this->dispatchBrowserEvent('show-closeQuoteModal');

    }
    public function closeQuotation(){
        $quotation = Quotation::find($this->quotation->id);
        $quotation->status = 0;
        $quotation->closed_by_id = Auth::user()->id;
        $quotation->save();
        $this->dispatchBrowserEvent('hide-closeQuoteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Quotation Closed Successfully!!"
        ]);
    }

    public function convertToSalesOrderShow($id){
        $quotation = Quotation::with('customer:id,name')->find($id);
        if (!$quotation) {
            return;
        }
        if ($quotation->isSalesOrder()) {
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Quotation already converted to sales order ".$quotation->sales_order_number
            ]);
            return;
        }

        $this->resetErrorBag();
        $this->convert_quotation_id = $quotation->id;
        $this->convert_quotation_number = $quotation->quotation_number;
        $this->convert_customer = $quotation->customer ? $quotation->customer->name : "";
        $this->sales_order_number_preview = $quotation->nextSalesOrderNumber()[0];
        $this->sales_order_ref = null;
        $this->sales_order_date = date('Y-m-d');
        $this->dispatchBrowserEvent('show-convertQuoteModal');
    }

    public function updatedSalesOrderRef(){
        $this->checkUniqueReference('sales_order_ref', Quotation::class, 'sales_order_ref', $this->convert_quotation_id, 'sales order reference');
    }

    public function convertToSalesOrder(){
        $this->validate([
            'convert_quotation_id' => 'required',
            'sales_order_ref' => 'nullable|string|max:191',
            'sales_order_date' => 'required|date',
        ]);
        $this->assertUniqueReference('sales_order_ref', Quotation::class, 'sales_order_ref', $this->sales_order_ref, $this->convert_quotation_id, 'sales order reference');

        $quotation = Quotation::find($this->convert_quotation_id);
        if (!$quotation) {
            $this->dispatchBrowserEvent('hide-convertQuoteModal');
            return;
        }

        $quotation = $quotation->convertToSalesOrder($this->sales_order_ref, $this->sales_order_date, Auth::user()->id);

        $this->reset(['convert_quotation_id', 'convert_quotation_number', 'convert_customer', 'sales_order_number_preview', 'sales_order_ref', 'sales_order_date']);
        $this->dispatchBrowserEvent('hide-convertQuoteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Quotation converted to Sales Order ".$quotation->sales_order_number." Successfully!!"
        ]);
    }

    // Same filters for the table and every export (Quotation::scopeListFilter)
    protected function listFilters(): array
    {
        return [
            'date_column'   => $this->quotation_filter,
            'from'          => $this->from,
            'to'            => $this->to,
            'search'        => $this->search,
            'document_type' => $this->document_type,
        ];
    }

    protected function exportName(): string
    {
        $names = ['quotation' => 'quotations', 'sales_order' => 'sales_orders'];
        return ($names[$this->document_type] ?? 'quotations_and_sales_orders').'_'.time();
    }

    public function exportQuotationsExcel(Excel $excel){
        return $excel->download(new QuotationsExport($this->listFilters()), $this->exportName().'.xlsx');
    }
    public function exportQuotationsCSV(Excel $excel){
        return $excel->download(new QuotationsExport($this->listFilters()), $this->exportName().'.csv', Excel::CSV);
    }
    public function exportQuotationsPDF(Excel $excel){
        return $excel->download(new QuotationsExport($this->listFilters()), $this->exportName().'.pdf', Excel::DOMPDF);
    }


    public function render()
    {
        return view('livewire.quotations.index',[
            'quotations' => Quotation::query()
                ->with(['customer:id,name','currency','user','additional_costs'])
                ->withCount('trips')
                ->listFilter($this->listFilters())
                ->paginate(10),
        ]);
    }
}
