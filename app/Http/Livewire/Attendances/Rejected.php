<?php

namespace App\Http\Livewire\Attendances;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Attendance;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Rejected extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

 
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    protected $attendances;
    public $attendance;
    public $attendance_id;
    public $authorize;
    public $comments;


    public function mount(){
       
    }

    public function updated($value){
        $this->validateOnly($value);
    }

    private function resetInputFields(){
        $this->authorize = "";
        $this->comments = "";
    }


    protected $rules = [
        'authorize' => 'required',
        'comments' => 'nullable',
    ];

    

     public function authorize($id){
        $attendance = Attendance::find($id);
        $this->attendance_id = $attendance->id;
        $this->attendance = $attendance;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

    public function update(){

        DB::transaction(function () {
        
        $this->validate();

        $attendance = Attendance::find($this->attendance_id);
        $attendance->authorized_by_id = Auth::user()->id;
        $attendance->authorization = $this->authorize;
        $attendance->authorization_date = now();
        $attendance->authorization_comments = $this->comments;
        $attendance->update();

        if ($this->authorize == "approved") {

            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Attendance Register Approved Successfully!!"
            ]);
             return redirect()->route('attendances.approved');

        }

         $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Attendance Register Rejected Successfully!!"
            ]);
             return redirect()->route('attendances.rejected');

       
        
        });
    }


    public function updatingSearch()
    {
        $this->resetPage(); // important for pagination
    }
    
    public function render()
    {
         $search = trim($this->search);

        $attendances = Attendance::query()
            ->where('authorization', 'rejected')

             // ✅ date filter on attendance_date when from/to provided
            ->when($this->from || $this->to, function ($q) {
                $from = $this->from
                    ? Carbon::parse($this->from)->startOfDay()
                    : null;

                $to = $this->to
                    ? Carbon::parse($this->to)->endOfDay()
                    : null;

                if ($from && $to) {
                    $q->whereBetween('attendance_date', [$from, $to]);
                } elseif ($from) {
                    $q->where('attendance_date', '>=', $from);
                } else { // only $to
                    $q->where('attendance_date', '<=', $to);
                }
            })
            
            ->when($search !== '', function ($q) use ($search) {

                $q->where(function ($qq) use ($search) {

                    // department name
                    $qq->whereHas('department', function ($d) use ($search) {
                        $d->where('name', 'like', "%{$search}%");
                    })

                    // user name / surname / full name
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(name,' ',surname) LIKE ?", ["%{$search}%"]);
                    })

                    // date/time on created_at (works for "2026-02-05", "14:30", "2026-02-05 14")
                    ->orWhereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("DATE_FORMAT(created_at, '%H:%i') LIKE ?", ["%{$search}%"]);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.attendances.rejected', [
            'attendances' => $attendances,
        ]);
    }
}
