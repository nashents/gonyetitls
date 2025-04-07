<?php

namespace App\Http\Livewire\Loans;

use App\Models\Loan;
use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    
    public $loan;
    public $loan_id;
    private $payments;

    public function mount($id){
        $this->loan_id = $id;
        $this->loan = Loan::find($id);
        
    }

    public function render()
    {
        return view('livewire.loans.show',[
            'payments' => Payment::where('loan_id',$this->loan_id)->paginate(10)
        ]);
    }
}
