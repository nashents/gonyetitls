<?php

namespace App\Http\Livewire\DebitNotes;

use App\Models\Bill;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Vendor;
use App\Models\DebitNote;
use App\Models\BankAccount;
use App\Models\Notification;
use App\Models\DebitNoteItem;
use App\Models\BillExpense;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\PendingNotificationEmails;
use Illuminate\Support\Facades\Session;

class Create extends Component
{

    public $bills;
    public $search;
    protected $queryString = ['search'];
    public $reason;
    public $bill;
    public $debit_note_number;
    public $selectedBill;
    public $vendors;
    public $vendor_id;
    public $company_id;
    public $currencies;
    public $currency_id;
    public $date;
    public $subheading;
    public $memo;
    public $footer;
    public $debit_note_reason;
    public $initials;
    public $tax_rate;
    public $tax_amount;
    public $exchange_rate;
    public $exchange_amount;
    public $subtotal;
    public $total;
    public $user_id;
    public $selectedVendor;
    public $vendor;
    public $company;
    public $billExpenses = [];
    public $rows = [];
    public $bill_attached = 'Yes';

    private function resetInputFields(){
    }

    public function updatedBillAttached($value){
        $this->selectedBill = null;
        $this->bill = null;
        $this->billExpenses = [];
        $this->selectedVendor = null;
        $this->vendor = null;
        $this->currency_id = null;
        $this->rows = [];
        $this->recalculateTotals();
    }

    public function updatedSelectedBill($id){
        if (!is_null($id)) {
            $this->bill = Bill::find($this->selectedBill);
            $this->currency_id = $this->bill->currency_id;
            $this->selectedVendor = $this->bill->vendor_id;
            $this->vendor = Vendor::find($this->bill->vendor_id);
            $this->billExpenses = $this->bill->bill_expenses;
            $this->rows = [];
            $this->recalculateTotals();
        }
    }

    public function updatedSelectedVendor($id){
        if ($this->bill_attached === 'No' && !is_null($id)) {
            $this->vendor = Vendor::find($id);
        }
    }

    public function addFromBillExpense($billExpenseId){
        $billExpense = BillExpense::where('bill_id', $this->selectedBill)->find($billExpenseId);
        if ($billExpense) {
            $this->rows[] = [
                'description' => $billExpense->description ?: $billExpense->account?->name,
                'amount' => $billExpense->subtotal,
                'bill_expense_id' => $billExpense->id,
            ];
            $this->recalculateTotals();
        }
    }

    public function addRow(){
        $this->rows[] = ['description' => '', 'amount' => '', 'bill_expense_id' => null];
    }

    public function removeRow($index){
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
        $this->recalculateTotals();
    }

    private function recalculateTotals(){
        $this->subtotal = collect($this->rows)->sum(fn($row) => (float) ($row['amount'] ?? 0));
        $this->total = $this->subtotal + (float) ($this->tax_amount ?? 0);
    }

    public function debit_noteNumber(){
        $str = Auth::user()->employee->company->name;
        $words = explode(' ', $str);
        if (isset($words[1][0])) {
            $this->initials = $words[0][0].$words[1][0];
        }else {
            $this->initials = $words[0][0];
        }
        $debit_note = DebitNote::orderBy('id','desc')->first();
        if (!$debit_note) {
            $this->number = 1;
            $debit_note_number =  $this->initials .'DN'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $this->number = $debit_note->id + 1;
            $debit_note_number =  $this->initials .'DN'. str_pad($this->number, 5, "0", STR_PAD_LEFT);
        }

        return  $debit_note_number;
    }

    public function mount(){
        $this->debit_note_number = $this->debit_noteNumber();
        $this->currencies = Currency::all();
        $this->bills = Bill::where('authorization','approved')->orderBy('created_at','desc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->company_id = Auth::user()->employee->company->id;
        $this->company = Auth::user()->employee->company;
    }

    public function updated($value){
        $this->validateOnly($value);
        if (Str::startsWith($value, 'rows.') || $value === 'tax_amount') {
            $this->recalculateTotals();
        }
    }

    protected $validationAttributes = [
        'rows.*.description' => 'description',
        'rows.*.amount' => 'amount',
    ];

    protected function rules(){
        $rules = [
            'currency_id' => 'required',
            'debit_note_number' => 'required',
            'date' => 'required',
            'rows' => 'required|array|min:1',
            'rows.*.description' => 'required|string',
            'rows.*.amount' => 'required|numeric',
        ];

        if ($this->bill_attached === 'Yes') {
            $rules['selectedBill'] = 'required';
        } else {
            $rules['selectedVendor'] = 'required';
        }

        return $rules;
    }

    public function store(){
        $this->validate();
        $this->recalculateTotals();

        $debit_note = new DebitNote;
        $debit_note->user_id = Auth::user()->id;
        $debit_note->company_id = Auth::user()->employee->company_id;
        $debit_note->currency_id = $this->currency_id;
        $debit_note->tax_rate = $this->tax_rate;
        $debit_note->tax_amount = $this->tax_amount;
        $debit_note->debit_note_number = $this->debit_note_number;
        $debit_note->date = $this->date;
        $debit_note->memo = $this->memo;
        $debit_note->footer = $this->footer;
        $debit_note->subheading = $this->subheading;
        $debit_note->debit_note_reason = $this->debit_note_reason;
        $debit_note->exchange_rate = $this->exchange_rate;
        $debit_note->subtotal = $this->subtotal;
        $debit_note->total = $this->total;

        if ($this->bill_attached === 'Yes') {
            $debit_note->vendor_id = $this->bill->vendor_id;
            $debit_note->bill_id = $this->selectedBill;
            $debit_note->bill_amount = $this->bill->total;
            $debit_note->bill_balance = $this->bill->total - $this->total;
        } else {
            $debit_note->vendor_id = $this->selectedVendor;
            $debit_note->bill_id = null;
            $debit_note->bill_amount = null;
            $debit_note->bill_balance = null;
        }

        $debit_note->save();

        foreach ($this->rows as $row) {
            $debit_note->debit_note_items()->create([
                'user_id' => Auth::user()->id,
                'description' => $row['description'],
                'bill_expense_id' => $row['bill_expense_id'] ?? null,
                'qty' => 1,
                'amount' => $row['amount'],
                'subtotal' => $row['amount'],
            ]);
        }

        $notifications = Notification::where('when','before')->where('category','Debit Note Authorization')->where('status',1)->get();

        if ($notifications->isNotEmpty()) {
            foreach ($notifications as $notification) {
                if($notification && isset($notification->category)){
                $email = $notification->email ?? $notification->employee->email ?? null;
                if($email){
                    Mail::to($email)->send(new PendingNotificationEmails($this->company, $notification, $debit_note));
                }
                }
            }
        }

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Debit Note Created Successfully!!"
        ]);

        return redirect()->route('debit_notes.index');
    }


    public function render()
    {
        if (isset($this->search)) {
            $this->bills = Bill::query()->with('vendor:id,name')
                                ->where('authorization', 'approved')
                                ->where('bill_number', 'like', '%'.$this->search.'%')
                                ->orWhere('bill_date', 'like', '%'.$this->search.'%')
                                ->orWhereHas('vendor', function ($query) {
                                 return $query->where('name', 'like', '%'.$this->search.'%');
                                })->orderBy('created_at','desc')->get();
        }

        return view('livewire.debit-notes.create');
    }
}
