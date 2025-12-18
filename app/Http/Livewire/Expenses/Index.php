<?php

namespace App\Http\Livewire\Expenses;

use App\Models\Account;
use App\Models\Expense;
use Livewire\Component;
use App\Models\Currency;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Models\PaymentMethod;
use Livewire\WithFileUploads;
use App\Exports\ExpensesExport;
use App\Imports\ExpensesImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

  use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;


    public $accounts;
    public $account_id;
    public $payment_methods;
    public $payment_method_id;
    private $expenses;
    public $status;
    public $name;
    public $amount;
    public $currencies;
    public $currency_id;
    public $frequency;
    public $description;
    public $type;
    public $importFile;

    public $expense_id;
    public $user_id;

    public function mount(){
        $this->resetPage();
        $this->reset(['search']);
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->payment_methods = PaymentMethod::orderBy('name','asc')->get();
        $this->accounts = Account::orderBy('name','asc')->get();
    }

     public function exportExpensesCSV(Excel $excel){

        return $excel->download(new ExpensesExport, 'expenses_'.time().'.csv', Excel::CSV);
    }
    public function exportExpensesPDF(Excel $excel){

        return $excel->download(new ExpensesExport, 'expenses_'.time().'.pdf', Excel::DOMPDF);
    }
    public function exportExpensesExcel(Excel $excel){
        return $excel->download(new ExpensesExport, 'expenses_'.time().'.xlsx');
    }
    private function resetInputFields(){
        $this->account_id = '';
        $this->payment_method_id = '';
        $this->name = '';
        $this->amount = '';
        $this->frequency = '';
        $this->currency_id = '';
        $this->description = '';
        $this->type = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:expenses,name,NULL,id,deleted_at,NULL|string|min:2',
        'type' => 'required',
        'account_id' => 'required',
    ];

    public function importExpenses(){
        
        $file = $this->importFile;
        $import = new ExpensesImport;
        $import->import($file);

        $this->dispatchBrowserEvent('hide-expenseImportModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Expense(s) Imported Successfully!!"
        ]);

        return redirect(request()->header('Referer'));
      
    }

    public function store(){
        
         $this->validate();

        try{

       
        $expense = new Expense;
        $expense->user_id = Auth::user()->id;
        $expense->account_id = $this->account_id;
        $expense->payment_method_id = $this->payment_method_id;
        $expense->name = $this->name;
        if (isset($this->currency_id) && $this->currency_id !="") {
            $expense->currency_id = $this->currency_id;
        }
      
        $expense->amount = $this->amount;
        $expense->frequency = $this->frequency;
        $expense->description = $this->description;
        $expense->type = $this->type;
        $expense->save();
        $this->dispatchBrowserEvent('hide-expenseModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Expense Created Successfully!!"
        ]);
        }
        catch(\Exception $e){
        $this->dispatchBrowserEvent('hide-expenseModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating expenses!!"
        ]);
    }

    }

    public function edit($id){
    $expense = Expense::find($id);
    $this->user_id = $expense->user_id;
    $this->name = $expense->name;
    $this->type = $expense->type;
    $this->amount = $expense->amount;
    $this->payment_method_id = $expense->payment_method_id;
    $this->currency_id = $expense->currency_id;
    $this->frequency = $expense->frequency;
    $this->description = $expense->description;
    $this->account_id = $expense->account_id;
    $this->status = $expense->status;
    $this->expense_id = $expense->id;
    $this->dispatchBrowserEvent('show-expenseEditModal');

    }

    public function update()
    {
        if ($this->expense_id) {
            try{
            $expense = Expense::find($this->expense_id);
            $expense->user_id = Auth::user()->id;
            $expense->account_id = $this->account_id;
            $expense->payment_method_id = $this->payment_method_id;
            $expense->amount = $this->amount;
            if (isset($this->currency_id) && $this->currency_id  != "" ) {
                $expense->currency_id = $this->currency_id;
            }
           
            $expense->name = $this->name;
            $expense->type = $this->type;
            $expense->frequency = $this->frequency;
            $expense->description = $this->description;
            $expense->status = $this->status;
            $expense->update();

            $this->dispatchBrowserEvent('hide-expenseEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Expense Updated Successfully!!"
            ]);
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-expenseEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while updating expenses!!"
            ]);
        }
        }
    }


    public function render()
    {
        if (filled($this->search)) {
            return view('livewire.expenses.index',[
                'expenses' => Expense::query()->with('currency','account')
                ->where('name','like', '%'.$this->search.'%')
                ->orWhere('type','like', '%'.$this->search.'%')
                ->orWhere('amount','like', '%'.$this->search.'%')
                ->orWhere('frequency','like', '%'.$this->search.'%')
                ->orWhere('description','like', '%'.$this->search.'%')
                ->orWhereHas('currency', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('account', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('name','asc')->paginate(10),
            ]);

        }else{
            return view('livewire.expenses.index',[
                'expenses' => Expense::orderBy('name','asc')->paginate(10)
            ]);
        }
    }
}
