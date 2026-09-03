<?php

namespace App\Http\Livewire\Expenses;

use App\Models\Account;
use App\Models\Expense;
use App\Models\Tax;
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
use App\Services\Sage\SageIntegration;

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
    public $taxes;
    public $tax_id;
    public $importFile;

    public $expense_id;
    public $user_id;

    public function mount(){
        $this->resetPage();
        $this->reset(['search']);
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->payment_methods = PaymentMethod::orderBy('name','asc')->get();
        $this->accounts = Account::whereHas('account_type', function ($query) {
            $query->where('name', 'Cost Of Goods Sold');
        })->orderBy('name','asc')->get();
        $this->taxes = Tax::whereHas('account', function ($query) {
            return $query->where('name','Value Added Tax');
        })->orderBy('name','asc')->get();
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
        $this->tax_id = '';
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

    /** Sage integration gate — when active, master data is pull-only (no create/edit/delete). */
    public function getSageEnabledProperty()
    {
        return SageIntegration::enabledForUser();
    }

    /** Generate a product number (company initials + P + zero-padded next id). */
    private function productNumber()
    {
        $company  = Auth::user()->employee?->company ?? Auth::user()->company;
        $name     = $company?->name ?? 'GY';
        $words    = explode(' ', trim($name));
        $initials = strtoupper(($words[0][0] ?? 'G') . ($words[1][0] ?? ''));
        $last     = \App\Models\Product::orderBy('id', 'desc')->first();
        $number   = $last ? $last->id + 1 : 1;

        return $initials . 'P' . str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }

    /** Bulk: create + link a non-inventory billable product for expenses missing one. */
    public function syncMissingProducts()
    {
        $missing = Expense::whereNull('product_id')
            ->orWhereDoesntHave('product')
            ->get();

        $count = 0;
        foreach ($missing as $expense) {
            if ($expense->product_id && $expense->product) {
                continue; // already linked to a live product
            }
            $product = new \App\Models\Product;
            $product->user_id        = Auth::user()->id;
            $product->product_number = $this->productNumber();
            $product->name           = $expense->name;
            $product->type           = 'Non Inventory';
            $product->buy            = 1;
            $product->sell           = 0;
            $product->status         = 1;
            $product->save();

            $expense->product_id = $product->id;
            $expense->saveQuietly();
            $count++;
        }

        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => $count > 0 ? "Created + linked products for {$count} expense(s)." : 'All expenses already have a linked product.',
        ]);
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
        $expense->tax_id = $this->tax_id;
        $expense->item_type = 'Non Inventory';
        $expense->save();

        // Every Gonyeti expense is also a non-inventory billable Product — auto-create
        // it and link (expenses.product_id) so the two stay in step (mirrors Sage,
        // where a non-inventory item is both an item and an expense line).
        $product = new \App\Models\Product;
        $product->user_id        = Auth::user()->id;
        $product->product_number = $this->productNumber();
        $product->name           = $this->name;
        $product->type           = 'Non Inventory';
        $product->buy            = 1; // billable
        $product->sell           = 0;
        $product->status         = 1;
        $product->save();

        $expense->product_id = $product->id;
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
    $this->tax_id = $expense->tax_id;
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
            if ($expense->is_locked && $this->name !== $expense->name) {
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"This expense is a core system expense - its name cannot be changed."
                ]);
                return;
            }
            $expense->user_id = Auth::user()->id;
            $expense->account_id = $this->account_id;
            $expense->payment_method_id = $this->payment_method_id;
            $expense->amount = $this->amount;
            if (isset($this->currency_id) && $this->currency_id  != "" ) {
                $expense->currency_id = $this->currency_id;
            }
           
            $expense->name = $this->name;
            $expense->type = $this->type;
            $expense->tax_id = $this->tax_id;
            $expense->item_type = 'Non Inventory';
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
                'expenses' => Expense::query()->with('currency','account','tax','product')
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
                'expenses' => Expense::with('currency','account','tax','product')->orderBy('name','asc')->paginate(10)
            ]);
        }
    }
}
