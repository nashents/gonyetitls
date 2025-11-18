<?php

namespace App\Http\Livewire\Quotations;

use App\Models\Cargo;
use App\Models\Company;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\Destination;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $quotation_filter;

    private $quotations;
    public $quotation_products;
    public $quotation;
    public $quotation_id;
    public $customers;

    public function mount(){
        $this->resetPage();
        $this->quotation_filter = "created_at";
    }

    public function updatingSearch()
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


    public function render()
    {
        if (isset($this->from) && isset($this->to)) {

            if (isset($this->search)) {

                return view('livewire.quotations.index',[
                    'quotations' => Quotation::query()->with(['customer:id,name','currency'])->whereBetween($this->quotation_filter,[$this->from, $this->to] )
                    ->where('quotation_number','like', '%'.$this->search.'%')
                    ->orWhere('date','like', '%'.$this->search.'%')
                    ->orWhere('expiry','like', '%'.$this->search.'%')
                    ->orWhere('authorization','like', '%'.$this->search.'%')
                    ->orWhereHas('customer', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('currency', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy($this->quotation_filter,'desc')->paginate(10),
                  
                ]);

            }else {

                return view('livewire.quotations.index',[
                    'quotations' => Quotation::query()->with(['customer:id,name','currency'])
                    ->whereBetween($this->quotation_filter,[$this->from, $this->to] )
                    ->orderBy($this->quotation_filter,'desc')->paginate(10),
                  
                ]);
            
            }
           
        }
        elseif (isset($this->search)) {
           
            return view('livewire.quotations.index',[
                'quotations' => Quotation::query()->with(['customer:id,name','currency'])->whereMonth($this->quotation_filter, date('m'))
                ->whereYear($this->quotation_filter, date('Y'))
                ->where('quotation_number','like', '%'.$this->search.'%')
                ->orWhere('date','like', '%'.$this->search.'%')
                ->orWhere('expiry','like', '%'.$this->search.'%')
                ->orWhere('authorization','like', '%'.$this->search.'%')
                ->orWhereHas('customer', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('currency', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy($this->quotation_filter,'desc')->paginate(10),
                   
                  
            ]);

        }
        else {
            return view('livewire.quotations.index',[
                'quotations' => Quotation::query()->with(['customer:id,name','currency'])->whereMonth($this->quotation_filter, date('m'))
                ->whereYear($this->quotation_filter, date('Y'))->orderBy($this->quotation_filter,'desc')->paginate(10),
              
            ]);
          
        }
    }
}
