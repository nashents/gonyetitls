<?php

namespace App\Http\Livewire\ExpenseCategories;

use App\Models\Expense;
use Livewire\Component;
use App\Models\AccountType;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{



    public $expense_categories;
    public $description;
    public $name;
    public $account_types;
    public $account_type_id;

    public $expense_id;
    public $user_id;

    public function mount(){
        $this->expense_categories = ExpenseCategory::latest()->get();
        $this->account_types = AccountType::orderBy('name','asc')->get();
    }
    private function resetInputFields(){
        $this->account_type_id = '';
        $this->name = '';
        $this->description = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'account_type_id' => 'required',
        'name' => 'required|unique:expense_categories,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
        $expense_category = new ExpenseCategory;
        $expense_category->user_id = Auth::user()->id;
        $expense_category->name = $this->name;
        $expense_category->account_type_id = $this->account_type_id;
        $expense_category->description = $this->description;
        $expense_category->save();
        $this->dispatchBrowserEvent('hide-expense_categoryModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Expense Category Created Successfully!!"
        ]);
        }
        catch(\Exception $e){
        $this->dispatchBrowserEvent('hide-expense_categoryModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating expense_categories!!"
        ]);
    }

    }

    public function edit($id){
    $expense_category = ExpenseCategory::find($id);
    $this->user_id = $expense_category->user_id;
    $this->name = $expense_category->name;
    $this->account_type_id = $expense_category->account_type_id;
    $this->description = $expense_category->description;
    $this->expense_category_id = $expense_category->id;
    $this->dispatchBrowserEvent('show-expense_categoryEditModal');

    }

    public function update()
    {
        if ($this->expense_category_id) {
            try{
            $expense_category = ExpenseCategory::find($this->expense_category_id);
            $expense_category->update([
                'user_id' => Auth::user()->id,
                'name' => $this->name,
                'account_type_id' => $this->account_type_id,
                'description' => $this->description,
            ]);

            $this->dispatchBrowserEvent('hide-expense_categoryEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Expense Category Updated Successfully!!"
            ]);
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-expense_categoryEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while updating expense_categories!!"
            ]);
        }
        }
    }


    public function render()
    {
        $this->expense_categories = ExpenseCategory::latest()->get();
        return view('livewire.expense-categories.index',[
            'expense_categories' => $this->expense_categories
        ]);
    }
}
