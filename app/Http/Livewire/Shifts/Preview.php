<?php

namespace App\Http\Livewire\Shifts;

use App\Models\Shift;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Preview extends Component
{
    public $filters = [];
    public $from;
    public $to;
    public $shifts;
    public $shift_id;
    public $loading_points;
    public $selectedLoadingPoint;
    public $offloading_points;
    public $selectedOffloadingPoint;
    public $transporters;
    public $selectedTransporter;
    public $horses;
    public $selectedHorse;
    public $vehicles;
    public $selectedVehicle;
    public $drivers;
    public $selectedDriver;
    public $employees;
    public $selectedEmployee;
    public $cargos;
    public $selectedCargo;
    public $selectedCustomer;
    public $type;
    public $company;
  
       public function mount($filters = [])
    {
        // 2. Unpack them into named properties for easier use:
        $this->shift_filter = $filters['shift_filter'] ?? null;
        $this->selectedTransporter = $filters['selectedTransporter'] ?? null;
        $this->selectedCustomer = $filters['selectedCustomer'] ?? null;
        $this->selectedDriver = $filters['driver'] ?? null;
        $this->selectedEmployee = $filters['selectedEmployee'] ?? null;
        $this->selectedHorse = $filters['selectedHorse'] ?? null;
        $this->from = $filters['from'] ?? null;
        $this->to = $filters['to'] ?? null;
        $this->selectedCargo = $filters['selectedCargo'] ?? null;
        $this->selectedVehicle = $filters['selectedVehicle'] ?? null;
        $this->type = $filters['type'] ?? null;
        $this->selectedLoadingPoint = $filters['selectedLoadingPoint'] ?? null;
        $this->selectedOffloadingPoint = $filters['selectedOffloadingPoint'] ?? null;
    }

    public function render()
    {
          $query = Shift::query();

        // Filter by Loading Point
       
        // Filter by Driver
        if (!empty($this->selectedDriver)) {
            $query->where('driver_id', $this->selectedDriver);
        }

        // Filter by Horse
        if (!empty($this->selectedHorse)) {
            $query->where('horse_id', $this->selectedHorse);
        }

        // Filter by Customer
        if (!empty($this->selectedCustomer)) {
            $query->where('customer_id', $this->selectedCustomer);
        }

        // Filter by Date Range
        if (!empty($this->from) && !empty($this->to)) {
            $query->whereBetween($this->shift_filter, [$this->from, $this->to]);
        }else {
           $query->whereMonth($this->shift_filter, date('m'))
           ->whereYear($this->shift_filter, date('Y'));
        }

        // Filter by Shift Time
        if (!empty($this->type)) {
            $query->where('type', $this->type);
        }

        // Final Query Execution
        return view('livewire.shifts.preview', [
            'shifts' => $query
                ->orderBy($this->shift_filter, 'desc')
                ->get(),
        ]);
        
    }
}
