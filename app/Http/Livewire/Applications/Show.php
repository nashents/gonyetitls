<?php

namespace App\Http\Livewire\Applications;

use App\Models\RecruitmentCheck;
use App\Models\RecruitmentDecision;
use App\Models\RecruitmentQualification;
use App\Models\RecruitmentScore;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $application;
    public $candidate;
    public $candidate_id;
    protected $recruitment_qualifications;
    protected $checks;
    protected $scores;
    protected $decisions;

    public function mount($application){
        $this->application = $application;
        $this->candidate = $this->application->recruitment_candidate;
        $this->candidate_id = $this->application->recruitment_candidate->id;
    }
    public function render()
    {
        return view('livewire.applications.show',[
            'recruitment_qualifications' => RecruitmentQualification::where('candidate_id',$this->candidate_id)->orderBy('created_at','desc')->paginate(10),
            'scores' => RecruitmentScore::where('candidate_id',$this->candidate_id)->orderBy('created_at','desc')->paginate(10),
            'checks' => RecruitmentCheck::where('candidate_id',$this->candidate_id)->orderBy('created_at','desc')->paginate(10),
            'decisions' => RecruitmentDecision::where('candidate_id',$this->candidate_id)->orderBy('created_at','desc')->paginate(10)
        ]);
    }
}
