<?php

namespace App\Http\Livewire\Routes;

use App\Models\Route;
use App\Models\Expense;
use Livewire\Component;
use App\Models\Currency;
use App\Models\RouteExpense;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Expenses extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];

    private $route_expenses;
    public $route_expense_id;
    public $route_id;
    public $expenses;
    public $expense_id;
    public $currencies;
    public $currency_id;
    public $amount;
    public $category;
    public $status;

    
    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    public function mount($id){
        $this->route_id = $id;
        $this->route = Route::find($id);
         $this->expenses = Expense::whereHas('account', function($q){
            $q->where('name', 'Trip Expense');
        })->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
    }

        public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'expense_id' => 'required',
        'route_id' => 'required',
    ];

    private function resetInputFields(){
        $this->route_id = Null;
        $this->expense_id = Null;
        $this->currency_id = Null;
        $this->amount = Null;
        $this->route_expense_id = Null;
        $this->category = Null;
       
    }

    public function store(){

        if ($this->expense_id) {
            foreach ($this->expense_id as $key => $value) {
                $route_expense = new RouteExpense;
                $route_expense->route_id = $this->route_id;
                if (isset($this->expense_id[$key])) {
                   $route_expense->expense_id = $this->expense_id[$key];
                }
                if (isset($this->currency_id[$key])) {
                    $route_expense->currency_id = $this->currency_id[$key];
                }
                if (isset($this->amount[$key])) {
                      $route_expense->amount = $this->amount[$key];
                }
                if (isset($this->category[$key])) {
                   $route_expense->category = $this->category[$key];
                }
                $route_expense->status = 1;
                $route_expense->save();
            }
        }

        $this->dispatchBrowserEvent('hide-expenseModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Expense(s) Added to Route Successfully!!"
        ]);

        redirect(request()->header('Referer'));
    }

     public function edit($id){
        $this->route_expense_id = $id;
        $route_expense = RouteExpense::find($id);
        $this->currency_id = $route_expense->currency_id;
        $this->expense_id = $route_expense->expense_id;
        $this->amount = $route_expense->amount;
        $this->category = $route_expense->category;
        $this->status = $route_expense->status;
        $this->dispatchBrowserEvent('show-expenseEditModal');
      
    }

    public function update(){

        $route_expense = RouteExpense::find($this->route_expense_id);
        $route_expense->route_id = $this->route_id;
        $route_expense->expense_id = $this->expense_id;
        $route_expense->currency_id = $this->currency_id;
        $route_expense->amount = $this->amount;
        $route_expense->category = $this->category;
        $route_expense->status = $this->status;
        $route_expense->update();

        $this->dispatchBrowserEvent('hide-expenseEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Route Expense Update Successfully!!"
        ]);

        redirect(request()->header('Referer'));
    }

    public function removeExpense($id){
        $this->route_expense_id = $id;
        $this->dispatchBrowserEvent('show-expenseDeleteModal');
      
    }
    public function delete(){
        $route_expense = RouteExpense::find($this->route_expense_id);
        if ($route_expense) {
            $route_expense->delete();
        }
         $this->route_expenses = RouteExpense::where('route_id',$this->route_id)->paginate(10);
        $this->dispatchBrowserEvent('hide-expenseDeleteModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Route Expense Deleted Successfully!!"
        ]);

        redirect(request()->header('Referer'));
    }

    public function render()
    {
        if (filled($this->search)) {
           return view('livewire.routes.expenses',[
                'route_expenses' => RouteExpense::query()->with('currency','route','expense')
                ->where('route_id',$this->route_id)
                ->where('amount','like', '%'.$this->search.'%')
                ->orWhereHas('expense', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('currency', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->paginate(10),
            ]);
        }else{
            return view('livewire.routes.expenses',[
            'route_expenses' => RouteExpense::where('route_id',$this->route_id)->paginate(10)
            ]);
        }
       
    }
}
