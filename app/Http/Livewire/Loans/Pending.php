<?php

namespace App\Http\Livewire\Loans;

use App\Models\Loan;
use App\Models\User;
use App\Http\Livewire\Concerns\HasStayOnPageAuthorization;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class Pending extends Component
{
    use WithPagination, HasStayOnPageAuthorization;
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
            return $this->redirectOrStay('loans.approved');
        }else {
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Loan Rejected Successfully"
            ]);
            return $this->redirectOrStay('loans.rejected');
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
       
        return view('livewire.loans.pending',[
            'loans' => Loan::where('authorization', 'pending')->orderBy('created_at','desc')->paginate(10)
        ]);
    }
}
