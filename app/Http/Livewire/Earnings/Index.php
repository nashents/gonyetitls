<?php

namespace App\Http\Livewire\Earnings;

use App\Models\Earning;
use Livewire\Component;
use App\Models\Currency;
use App\Services\Payroll\SalaryPayeService;
use Illuminate\Support\Facades\Auth;

/**
 * Payroll earnings (Accommodation, Transport ...). These are what the
 * "Allowances" section of a salary lists; the Allowances module is for
 * Transport / driver trip expenses only.
 */
class Index extends Component
{
    public $earnings;
    public $currencies;
    public $currency_id;
    public $description;
    public $name;
    public $calculate_by = 'currency';
    public $amount;
    public $percentage;
    public $taxable = 1;
    public $status;
    public $earning_id;
    public $user_id;

    public function mount(){
        $this->earnings = Earning::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|min:2|unique:earnings,name,'.($this->earning_id ?: 'NULL').',id,deleted_at,NULL',
            'calculate_by' => 'required|in:currency,percentage',
            'amount' => 'nullable|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'taxable' => 'required|in:0,1',
        ];
    }

    private function resetInputFields(){
        $this->currency_id = '';
        $this->name = '';
        $this->description = '';
        $this->calculate_by = 'currency';
        $this->amount = '';
        $this->percentage = '';
        $this->taxable = 1;
        $this->earning_id = null;
    }

    private function fillEarning(Earning $earning): void
    {
        $earning->name = $this->name;
        $earning->currency_id = $this->currency_id ?: null;
        $earning->description = $this->description;
        $earning->calculate_by = $this->calculate_by;
        // Default amount / % only pre-fills the salary form - it can be changed per salary.
        $earning->amount = $this->calculate_by === 'currency' && $this->amount !== '' ? $this->amount : null;
        $earning->percentage = $this->calculate_by === 'percentage' && $this->percentage !== '' ? $this->percentage : null;
        $earning->taxable = (bool) $this->taxable;
    }

    public function store(){
        $this->earning_id = null;
        $this->validate();

        $earning = new Earning;
        $earning->user_id = Auth::user()->id;
        $this->fillEarning($earning);
        $earning->status = 1;
        $earning->save();

        $this->dispatchBrowserEvent('hide-earningModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Earning Created Successfully!!"
        ]);
    }

    public function edit($id){
        $earning = Earning::find($id);
        $this->name = $earning->name;
        $this->currency_id = $earning->currency_id;
        $this->description = $earning->description;
        $this->calculate_by = $earning->calculate_by ?: 'currency';
        $this->amount = $earning->amount;
        $this->percentage = $earning->percentage;
        $this->taxable = $earning->taxable ? 1 : 0;
        $this->earning_id = $earning->id;
        $this->status = $earning->status;
        $this->resetValidation();
        $this->dispatchBrowserEvent('show-earningEditModal');
    }

    public function update()
    {
        if (!$this->earning_id) {
            return;
        }
        $this->validate();

        $earning = Earning::find($this->earning_id);
        $earning->user_id = Auth::user()->id;
        $this->fillEarning($earning);
        $earning->status = $this->status;
        $taxableChanged = $earning->isDirty('taxable');
        $earning->update();

        // PAYE on salary templates is stored, not live - re-run it on every
        // salary carrying this earning so future payroll runs pick up the
        // new Taxable setting.
        $message = "Earning Updated Successfully!!";
        if ($taxableChanged) {
            $count = (new SalaryPayeService())->recalculateForEarning($earning);
            $message .= " PAYE recalculated on {$count} salar" . ($count === 1 ? 'y' : 'ies') . ".";
        }

        $this->dispatchBrowserEvent('hide-earningEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>$message
        ]);
    }

    public function toggleStatus($id)
    {
        $earning = Earning::find($id);
        if (!$earning) {
            return;
        }
        $earning->status = $earning->status == 1 ? 0 : 1;
        $earning->save();

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Earning ".($earning->status == 1 ? 'Activated' : 'Deactivated')." Successfully!!"
        ]);
    }

    public function render()
    {
        $this->earnings = Earning::with('currency')->withCount('salary_items')->orderBy('name','asc')->get();
        return view('livewire.earnings.index',[
            'earnings'=>   $this->earnings
        ]);
    }
}
