<?php

namespace App\Http\Livewire\HorseModels;

use Livewire\Component;
use App\Models\HorseModel;
use Illuminate\Support\Facades\Session;

class Edit extends Component
{
    public $horse_model_id;
    public $name;

    public $engine_type;
    public $engine_cpl;
    public $gearbox_type;
    public $differential_type;
    public $differential_ratio;
    public $compressor_type;
    public $compressor_size;
    public $universal_j_size;
    public $rear_spring_type;
    public $front_spring_type;
    public $flange_size;
    public $steering_box_type;
    public $cab_type;
    public $air_dryer_system;
    public $fifth_wheel_type;
    public $starter_type;
    public $starter_size;
    public $alternator_type;
    public $alternator_size;
    public $fuel_filtering_type;
    public $king_pin_type;
    public $water_pump_belt_type;
    public $water_pump_belt_size;
    public $fan_belt_type;
    public $fan_belt_size;
    public $engine_mounting_type;
    public $steering_reservoir;
    public $braking_system_type;
    public $clutch_size;
    public $tnak_hrs;
    public $battery_size;

    public function mount($id)
    {
        $horse_model = HorseModel::findOrFail($id);
        $this->horse_model_id = $id;
        $this->name = $horse_model->name;

        foreach (array_keys(config('horse_mechanical_fields')) as $field) {
            $this->$field = $horse_model->$field;
        }
    }

    public function updated($value)
    {
        $this->validateOnly($value);
    }

    protected $rules = [
        'name' => 'required|string|min:2',
    ];

    public function update()
    {
        $horse_model = HorseModel::findOrFail($this->horse_model_id);
        $horse_model->name = $this->name;

        foreach (array_keys(config('horse_mechanical_fields')) as $field) {
            $horse_model->$field = $this->$field;
        }

        $horse_model->update();

        Session::flash('success', 'Horse Model Mechanical Details Updated Successfully');
        return redirect()->route('horse_makes.index');
    }

    public function render()
    {
        return view('livewire.horse-models.edit');
    }
}
