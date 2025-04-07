<?php

namespace App\Http\Livewire\Loans;

use App\Models\Loan;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class Rejected extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    
    private $loans;
    public $loan_id;
    public $trip_id;
    public $authorize;
    public $comments;
    public $loan;

    public function mount(){
       

    }
    public function authorize($id){
        $loan = Loan::find($id);
        $this->loan_id = $loan->id;
        $this->loan = $loan;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

      public function update(){
      try{
            $loan = Loan::find($this->loan_id);
            $loan->authorized_by_id = Auth::user()->id;
            $loan->authorization = $this->authorize;
            $loan->reason = $this->comments;
            $loan->update();

        if ($this->authorize == "approved") {
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Loan Approved Successfully"
            ]);
            return redirect()->route('loans.approved');
        }else {
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Loan Rejected Successfully"
            ]);
            return redirect()->route('loans.rejected');
        }
}
catch(\Exception $e){
    $this->dispatchBrowserEvent('hide-authorizationModal');
    $this->dispatchBrowserEvent('alert',[
        'type'=>'error',
        'message'=>"Something went wrong while trying to authorize loan!!"
    ]);
    }

      }
    public function render()
    {
      
        return view('livewire.loans.rejected',[
            'loans' => Loan::where('authorization', 'rejected')->latest()->paginate(10)
        ]);
    }
}
