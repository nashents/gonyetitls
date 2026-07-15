<?php

namespace App\Http\Livewire\SheqContractorOnboardings;

use App\Models\SheqContractorOnboarding;
use App\Models\Transporter;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    public $status_filter = '';
    public $file_status_filter = '';

    public $vendors;
    public $transporters;

    public $sheq_contractor_onboarding_id;
    public $contractor_type = 'vendor';
    public $contractor_id;
    public $induction_date;
    public $induction_expiry;
    public $screening_status = 'pending';
    public $sheq_file_status = 'pending';
    public $sheq_score;
    public $last_audit_date;
    public $next_audit_date;
    public $notes;
    public $status = 'active';

    protected $rules = [
        'contractor_type' => 'required',
        'contractor_id' => 'required',
    ];

    public function mount(){
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->transporters = Transporter::orderBy('name','asc')->get();
    }

    private function resetInputFields(){
        $this->contractor_type = "vendor";
        $this->contractor_id = "";
        $this->induction_date = "";
        $this->induction_expiry = "";
        $this->screening_status = "pending";
        $this->sheq_file_status = "pending";
        $this->sheq_score = "";
        $this->last_audit_date = "";
        $this->next_audit_date = "";
        $this->notes = "";
        $this->status = "active";
    }

    private function contractorClass(){
        return $this->contractor_type == 'transporter' ? Transporter::class : Vendor::class;
    }

    public function store(){
        $this->validate();

        $onboarding = new SheqContractorOnboarding;
        $onboarding->user_id = Auth::user()->id;
        $onboarding->contractorable_type = $this->contractorClass();
        $onboarding->contractorable_id = $this->contractor_id;
        $this->fill_fields($onboarding);
        $onboarding->save();

        $this->dispatchBrowserEvent('hide-sheq_contractor_onboardingModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Contractor Onboarding Record Created Successfully!!"
        ]);
    }

    private function fill_fields($onboarding){
        $onboarding->induction_date = $this->induction_date ?: Null;
        $onboarding->induction_expiry = $this->induction_expiry ?: Null;
        $onboarding->screening_status = $this->screening_status;
        $onboarding->sheq_file_status = $this->sheq_file_status;
        $onboarding->sheq_score = $this->sheq_score;
        $onboarding->last_audit_date = $this->last_audit_date ?: Null;
        $onboarding->next_audit_date = $this->next_audit_date ?: Null;
        $onboarding->notes = $this->notes;
        $onboarding->status = $this->status;
    }

    public function edit($id){
        $onboarding = SheqContractorOnboarding::find($id);
        $this->sheq_contractor_onboarding_id = $onboarding->id;
        $this->contractor_type = $onboarding->contractorable_type == Transporter::class ? 'transporter' : 'vendor';
        $this->contractor_id = $onboarding->contractorable_id;
        $this->induction_date = $onboarding->induction_date ? Carbon::parse($onboarding->induction_date)->format('Y-m-d') : Null;
        $this->induction_expiry = $onboarding->induction_expiry ? Carbon::parse($onboarding->induction_expiry)->format('Y-m-d') : Null;
        $this->screening_status = $onboarding->screening_status;
        $this->sheq_file_status = $onboarding->sheq_file_status;
        $this->sheq_score = $onboarding->sheq_score;
        $this->last_audit_date = $onboarding->last_audit_date ? Carbon::parse($onboarding->last_audit_date)->format('Y-m-d') : Null;
        $this->next_audit_date = $onboarding->next_audit_date ? Carbon::parse($onboarding->next_audit_date)->format('Y-m-d') : Null;
        $this->notes = $onboarding->notes;
        $this->status = $onboarding->status;
        $this->dispatchBrowserEvent('show-sheq_contractor_onboardingEditModal');
    }

    public function update(){
        $this->validate();

        $onboarding = SheqContractorOnboarding::find($this->sheq_contractor_onboarding_id);
        $onboarding->contractorable_type = $this->contractorClass();
        $onboarding->contractorable_id = $this->contractor_id;
        $this->fill_fields($onboarding);
        $onboarding->update();

        $this->dispatchBrowserEvent('hide-sheq_contractor_onboardingEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Contractor Onboarding Record Updated Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_contractor_onboarding_id = $id;
        $this->dispatchBrowserEvent('show-sheq_contractor_onboardingDeleteModal');
    }

    public function destroy(){
        $onboarding = SheqContractorOnboarding::find($this->sheq_contractor_onboarding_id);
        if ($onboarding) {
            $onboarding->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_contractor_onboardingDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Contractor Onboarding Record Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqContractorOnboarding::query()->with(['contractorable']);

        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }
        if ($this->file_status_filter) {
            $query->where('sheq_file_status', $this->file_status_filter);
        }

        $sheq_contractor_onboardings = $query->orderBy('created_at','desc')->paginate(10);

        return view('livewire.sheq-contractor-onboardings.index',[
            'sheq_contractor_onboardings' => $sheq_contractor_onboardings
        ]);
    }
}
