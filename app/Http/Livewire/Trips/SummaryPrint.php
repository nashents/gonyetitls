<?php

namespace App\Http\Livewire\Trips;

use App\Models\Trip;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SummaryPrint extends Component
{
    

    public $trips;
    public $search;
    public $company;
    public $from;
    public $to;
    public $trip_filter;

    public function mount( $from, $to, $search, $company, $trip_filter){
        $this->search = $search;
        $this->company = $company;
        $this->from = $from;
        $this->to = $to;
        $this->trip_filter = $trip_filter;


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
            if (!is_null($this->from) && !is_null($this->to)) {
                if (!is_null($this->search)) {
                  $this->trips = Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                  'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereBetween($this->trip_filter,[$this->from, $this->to] )
                  ->where('trip_number','like', '%'.$this->search.'%')
                  ->orWhere('trip_status','like', '%'.$this->search.'%')
                  ->orWhere('authorization','like', '%'.$this->search.'%')
                  ->orWhereHas('horse', function ($query) {
                      return $query->where('registration_number', 'like', '%'.$this->search.'%');
                  })
                  ->orWhereHas('customer', function ($query) {
                      return $query->where('name', 'like', '%'.$this->search.'%');
                  })
                  ->orWhereHas('delivery_note', function ($query) {
                      return $query->where('offloaded_date', 'like', '%'.$this->search.'%');
                  })
                  ->orWhereHas('horse', function ($query) {
                      return $query->where('registration_number', 'like', '%'.$this->search.'%');
                  })
                  ->orWhereHas('horse', function ($query) {
                      return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                  })
                  ->orWhereHas('user.employee', function ($query) {
                      return $query->where('name', 'like', '%'.$this->search.'%');
                  })
                  ->orWhereHas('transporter', function ($query) {
                      return $query->where('name', 'like', '%'.$this->search.'%');
                  })
                  ->orWhereHas('loading_point', function ($query) {
                      return $query->where('name', 'like', '%'.$this->search.'%');
                  })
                  ->orWhereHas('offloading_point', function ($query) {
                      return $query->where('name', 'like', '%'.$this->search.'%');
                  })
                  ->orderBy('trip_number','desc')->get();
                }else {
                   $this->trips = Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                   'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereBetween($this->trip_filter,[$this->from, $this->to] )->orderBy('trip_number','desc')->get();
                }
               
            }
            elseif (!is_null($this->search)) {
               
              $this->trips = Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
              'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereMonth('created_at', date('m'))
              ->whereYear('created_at', date('Y'))
              ->where('trip_number','like', '%'.$this->search.'%')
              ->orWhere('trip_status','like', '%'.$this->search.'%')
              ->orWhere('authorization','like', '%'.$this->search.'%')
              ->orWhereHas('horse', function ($query) {
                  return $query->where('registration_number', 'like', '%'.$this->search.'%');
              })
              ->orWhereHas('customer', function ($query) {
                  return $query->where('name', 'like', '%'.$this->search.'%');
              })
              ->orWhereHas('delivery_note', function ($query) {
                  return $query->where('offloaded_date', 'like', '%'.$this->search.'%');
              })
              ->orWhereHas('user.employee', function ($query) {
                  return $query->where('name', 'like', '%'.$this->search.'%');
              })
              ->orWhereHas('transporter', function ($query) {
                  return $query->where('name', 'like', '%'.$this->search.'%');
              })
              ->orWhereHas('horse', function ($query) {
                  return $query->where('registration_number', 'like', '%'.$this->search.'%');
              })
              ->orWhereHas('horse', function ($query) {
                  return $query->where('fleet_number', 'like', '%'.$this->search.'%');
              })
              ->orWhereHas('loading_point', function ($query) {
                  return $query->where('name', 'like', '%'.$this->search.'%');
              })
              ->orWhereHas('offloading_point', function ($query) {
                  return $query->where('name', 'like', '%'.$this->search.'%');
              })
              ->orderBy('trip_number','desc')->get();
            }
            else {

               $this->trips = Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
               'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereMonth($this->trip_filter, date('m'))
               ->whereYear($this->trip_filter, date('Y'))->orderBy('trip_number','desc')->get();
              
            }
        }else {
            if (!is_null($this->from) && !is_null($this->to)) {
                if (!is_null($this->search)) {
                   $this->trips = Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                   'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereBetween($this->trip_filter,[$this->from, $this->to] )->where('user_id',Auth::user()->id)
                   ->where('trip_number','like', '%'.$this->search.'%')
                   ->orWhere('trip_status','like', '%'.$this->search.'%')
                   ->orWhere('authorization','like', '%'.$this->search.'%')
                   ->orWhereHas('horse', function ($query) {
                       return $query->where('registration_number', 'like', '%'.$this->search.'%');
                   })
                   ->orWhereHas('customer', function ($query) {
                       return $query->where('name', 'like', '%'.$this->search.'%');
                   })
                   ->orWhereHas('delivery_note', function ($query) {
                       return $query->where('offloaded_date', 'like', '%'.$this->search.'%');
                   })
                   ->orWhereHas('user.employee', function ($query) {
                       return $query->where('name', 'like', '%'.$this->search.'%');
                   })
                   ->orWhereHas('transporter', function ($query) {
                       return $query->where('name', 'like', '%'.$this->search.'%');
                   })
                   ->orWhereHas('loading_point', function ($query) {
                       return $query->where('name', 'like', '%'.$this->search.'%');
                   })
                   ->orWhereHas('horse', function ($query) {
                       return $query->where('registration_number', 'like', '%'.$this->search.'%');
                   })
                   ->orWhereHas('horse', function ($query) {
                       return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                   })
                   ->orWhereHas('offloading_point', function ($query) {
                       return $query->where('name', 'like', '%'.$this->search.'%');
                   })
                   ->orderBy('trip_number','desc')->get();
                }else{
                   $this->trips = Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                        'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereBetween($this->trip_filter,[$this->from, $this->to] )->where('user_id',Auth::user()->id)->orderBy('trip_number','desc')->get();
                }
              
               
            }
            elseif (!is_null($this->search)) {

               $this->trips = Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
               'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereMonth($this->trip_filter, date('m'))
               ->whereYear($this->trip_filter, date('Y'))->where('trip_number','like', '%'.$this->search.'%')->where('user_id',Auth::user()->id)
               ->where('trip_number','like', '%'.$this->search.'%')
               ->orWhere('trip_status','like', '%'.$this->search.'%')
               ->orWhere('authorization','like', '%'.$this->search.'%')
               ->orWhereHas('horse', function ($query) {
                   return $query->where('registration_number', 'like', '%'.$this->search.'%');
               })
               ->orWhereHas('customer', function ($query) {
                   return $query->where('name', 'like', '%'.$this->search.'%');
               })
               ->orWhereHas('delivery_note', function ($query) {
                   return $query->where('offloaded_date', 'like', '%'.$this->search.'%');
               })
               ->orWhereHas('user.employee', function ($query) {
                   return $query->where('name', 'like', '%'.$this->search.'%');
               })
               ->orWhereHas('transporter', function ($query) {
                   return $query->where('name', 'like', '%'.$this->search.'%');
               })
               ->orWhereHas('horse', function ($query) {
                   return $query->where('registration_number', 'like', '%'.$this->search.'%');
               })
               ->orWhereHas('horse', function ($query) {
                   return $query->where('fleet_number', 'like', '%'.$this->search.'%');
               })
               ->orWhereHas('loading_point', function ($query) {
                   return $query->where('name', 'like', '%'.$this->search.'%');
               })
               ->orWhereHas('offloading_point', function ($query) {
                   return $query->where('name', 'like', '%'.$this->search.'%');
               })
               ->orderBy('trip_number','desc')->get();
            }
            else {
                
               $this->trips = Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
               'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereMonth($this->trip_filter, date('m'))
               ->whereYear($this->trip_filter, date('Y'))->where('user_id',Auth::user()->id)->orderBy('trip_number','desc')->get();

            }

        }

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
            if (!is_null($this->from) && !is_null($this->to)) {
                if (!is_null($this->search)) {
                    return view('livewire.trips.summary-print',[
                        'trips' => Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                        'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereBetween($this->trip_filter,[$this->from, $this->to] )
                        ->where('trip_number','like', '%'.$this->search.'%')
                        ->orWhere('trip_status','like', '%'.$this->search.'%')
                        ->orWhere('authorization','like', '%'.$this->search.'%')
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('customer', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('delivery_note', function ($query) {
                            return $query->where('offloaded_date', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('user.employee', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('transporter', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('loading_point', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('offloading_point', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orderBy('trip_number','desc')->get(),
                        'trip_filter' => $this->trip_filter
                    ]);
                }else {
                    return view('livewire.trips.summary-print',[
                        'trips' => Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                        'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereBetween($this->trip_filter,[$this->from, $this->to] )->orderBy('trip_number','desc')->get(),
                        'trip_filter' => $this->trip_filter
                    ]);
                }
               
            }
            elseif (!is_null($this->search)) {
               
                return view('livewire.trips.summary-print',[
                    'trips' => Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                    'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereMonth('created_at', date('m'))
                    ->whereYear('created_at', date('Y'))
                    ->where('trip_number','like', '%'.$this->search.'%')
                    ->orWhere('trip_status','like', '%'.$this->search.'%')
                    ->orWhere('authorization','like', '%'.$this->search.'%')
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('customer', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('delivery_note', function ($query) {
                        return $query->where('offloaded_date', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('user.employee', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('transporter', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('loading_point', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('offloading_point', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy('trip_number','desc')->get(),
                    'trip_filter' => $this->trip_filter
                ]);
            }
            else {

                return view('livewire.trips.summary-print',[
                    'trips' => Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                    'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereMonth($this->trip_filter, date('m'))
                    ->whereYear($this->trip_filter, date('Y'))->orderBy('trip_number','desc')->get(),
                    'trip_filter' => $this->trip_filter
                ]);
              
            }
        }else {
            if (!is_null($this->from) && !is_null($this->to)) {
                if (!is_null($this->search)) {
                    return view('livewire.trips.summary-print',[
                        'trips' => Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                        'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereBetween($this->trip_filter,[$this->from, $this->to] )->where('user_id',Auth::user()->id)
                        ->where('trip_number','like', '%'.$this->search.'%')
                        ->orWhere('trip_status','like', '%'.$this->search.'%')
                        ->orWhere('authorization','like', '%'.$this->search.'%')
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('customer', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('delivery_note', function ($query) {
                            return $query->where('offloaded_date', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('user.employee', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('transporter', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('loading_point', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('offloading_point', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orderBy('trip_number','desc')->get(),
                        'trip_filter' => $this->trip_filter
                    ]);
                }else{
                    return view('livewire.trips.summary-print',[
                        'trips' => Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                        'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereBetween($this->trip_filter,[$this->from, $this->to] )->where('user_id',Auth::user()->id)->orderBy('trip_number','desc')->get(),
                        'trip_filter' => $this->trip_filter
                    ]);
                }
              
               
            }
            elseif (!is_null($this->search)) {
                return view('livewire.trips.summary-print',[
                    'trips' => Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                    'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereMonth($this->trip_filter, date('m'))
                    ->whereYear($this->trip_filter, date('Y'))->where('trip_number','like', '%'.$this->search.'%')->where('user_id',Auth::user()->id)
                    ->where('trip_number','like', '%'.$this->search.'%')
                    ->orWhere('trip_status','like', '%'.$this->search.'%')
                    ->orWhere('authorization','like', '%'.$this->search.'%')
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('customer', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('delivery_note', function ($query) {
                        return $query->where('offloaded_date', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('user.employee', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('transporter', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('loading_point', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('offloading_point', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy('trip_number','desc')->get(),
                    'trip_filter' => $this->trip_filter
                ]);
            }
            else {
                
                return view('livewire.trips.summary-print',[
                    'trips' => Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                    'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereMonth($this->trip_filter, date('m'))
                    ->whereYear($this->trip_filter, date('Y'))->where('user_id',Auth::user()->id)->orderBy('trip_number','desc')->get(),
                    'trip_filter' => $this->trip_filter
                ]);

            }

        }
   
    }
}
