<?php

namespace App\Http\Livewire\Incidents;

use Livewire\Component;
use App\Models\Incident;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;


    private $incidents;
    public $incident;
    public $incident_id;
    public $status;
    public $comments;



    private function resetInputFields(){
        $this->status = '';
        $this->comments = '';
    }

    public function mount(){
        $this->resetPage();
      
      }


    //   public function showincident($id){
    //     $this->incident_id = $id;
    //     $this->incident = Incident::find($id);
    //     $this->status = $this->incident->status;
    //     $this->dispatchBrowserEvent('show-closeTicketModal');
    // }

    // public function closeincident(){

    //     $incident = Incident::find($this->incident_id);
    //     $incident->status = $this->status;
    //     $incident->update();

    //     $ticket = $incident->ticket;
    //     if (isset($ticket)) {
    //         $ticket->closed_by_id = Auth::user()->id;
    //         $ticket->status = $this->status;
    //         $ticket->closed_comments = $this->comments;
    //         $ticket->update();
    //     }
      

    //     $horse = $incident->horse;
    //     if (isset($horse)) {
    //         $horse->service = 0;
    //         $horse->update();
    //     }

    //     $vehicle = $incident->vehicle;
    //     if (isset($vehicle)) {
    //         $vehicle->service = 0;
    //         $vehicle->update();
    //     }
      
    //     $trailer = $incident->trailer;
    //     if (isset($trailer)) {
    //         $trailer->service = 0;
    //         $trailer->update();
    //     }

    //     $this->dispatchBrowserEvent('hide-closeTicketModal');
    //     $this->resetInputFields();
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'success',
    //         'message'=>"incident Closing Decision Created Successfully!!"
    //     ]);
    // }


    public function dateRange(){
 
        // $this->resetPage();
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {


        $departments = Auth::user()->employee->departments;
            foreach($departments as $department){
                $department_names[] = $department->name;
            }
            $roles = Auth::user()->roles;
            foreach($roles as $role){
                $role_names[] = $role->name;
            }
            $ranks = Auth::user()->employee->ranks;
            foreach($ranks as $rank){
                $rank_names[] = $rank->name;
            }
            if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names)) {
                if (isset($this->from) && isset($this->to)) {
                    if (isset($this->search)) {
                        return view('livewire.incidents.index',[
                            'incidents' => Incident::query()->with('customer','trip','horse','trailer','vehicle')->whereBetween('created_at',[$this->from, $this->to] )
                            ->where('incident_number','like', '%'.$this->search.'%')
                            ->orWhereHas('horse', function ($query) {
                                return $query->where('registration_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('horse', function ($query) {
                                return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('customer', function ($query) {
                                return $query->where('ticket_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('trip', function ($query) {
                                return $query->where('inspection_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('vehicle', function ($query) {
                                return $query->where('registration_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('vehicle', function ($query) {
                                return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('trailer', function ($query) {
                                return $query->where('registration_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('trailer', function ($query) {
                                return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                            })->orderBy('incident_number','desc')->paginate(10),
                          
                        ]);
                    }else {
                        return view('livewire.incidents.index',[
                            'incidents' => Incident::query()->with('customer','trip','horse','trailer','vehicle')->whereBetween('created_at',[$this->from, $this->to] )->orderBy('incident_number','desc')->paginate(10),
                          
                        ]);
                    }
                   
                }
                elseif (isset($this->search)) {
                   
                    return view('livewire.incidents.index',[
                        'incidents' => Incident::query()->with('customer','trip','horse','trailer','vehicle')->whereMonth('created_at', date('m'))
                        ->whereYear('created_at', date('Y'))
                        ->where('incident_number','like', '%'.$this->search.'%')
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('customer', function ($query) {
                            return $query->where('ticket_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('trip', function ($query) {
                            return $query->where('inspection_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('vehicle', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('vehicle', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('trailer', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('trailer', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })->orderBy('incident_number','desc')->paginate(10),
                      
                    ]);
                }
                else {
                   
                    return view('livewire.incidents.index',[
                        'incidents' => Incident::query()->with('customer','trip','horse','trailer','vehicle')->whereMonth('created_at', date('m'))
                        ->whereYear('created_at', date('Y'))->orderBy('incident_number','desc')->paginate(10),
                      
                    ]);
                  
                }
            }else {
                if (isset($this->from) && isset($this->to)) {
                    if (isset($this->search)) {
                        return view('livewire.incidents.index',[
                            'incidents' => Incident::query()->with('customer','trip','horse','trailer','vehicle')->whereBetween('created_at',[$this->from, $this->to] )->where('user_id',Auth::user()->id)
                            ->where('incident_number','like', '%'.$this->search.'%')
                            ->orWhereHas('horse', function ($query) {
                                return $query->where('registration_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('horse', function ($query) {
                                return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('customer', function ($query) {
                                return $query->where('ticket_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('trip', function ($query) {
                                return $query->where('inspection_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('vehicle', function ($query) {
                                return $query->where('registration_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('vehicle', function ($query) {
                                return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('trailer', function ($query) {
                                return $query->where('registration_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('trailer', function ($query) {
                                return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                            })->orderBy('incident_number','desc')->paginate(10),
                          
                        ]);
                    }else{
                        return view('livewire.incidents.index',[
                            'incidents' => Incident::query()->with('customer','trip','horse','trailer','vehicle')->whereBetween('created_at',[$this->from, $this->to] )->where('user_id',Auth::user()->id)->orderBy('incident_number','desc')->paginate(10),
                          
                        ]);
                    }
                  
                   
                }
                elseif (isset($this->search)) {
                    return view('livewire.incidents.index',[
                        'incidents' => Incident::query()->with('customer','trip','horse','trailer','vehicle')->whereMonth('created_at', date('m'))
                        ->whereYear('created_at', date('Y'))->where('user_id',Auth::user()->id)
                        ->where('incident_number','like', '%'.$this->search.'%')
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('customer', function ($query) {
                            return $query->where('ticket_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('trip', function ($query) {
                            return $query->where('inspection_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('vehicle', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('vehicle', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('trailer', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('trailer', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })->orderBy('incident_number','desc')->paginate(10),
                      
                    ]);
                }
                else {
                    
                    return view('livewire.incidents.index',[
                        'incidents' => Incident::query()->with('customer','trip','horse','trailer','vehicle')->whereMonth('created_at', date('m'))
                        ->whereYear('created_at', date('Y'))->where('user_id',Auth::user()->id)->orderBy('incident_number','desc')->paginate(10),
                      
                    ]);

                }
    
            }
       

    }
}
