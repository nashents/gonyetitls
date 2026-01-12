<?php

namespace App\Http\Livewire\Modules;

use App\Models\Module;
use Livewire\Component;
use App\Models\SubModule;
use App\Models\ModuleGroup;
use Livewire\WithPagination;

class Index extends Component
{

use WithPagination;
protected $paginationTheme = 'bootstrap';

protected $module_groups;
public $company_id;

public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    public function mount($id = null){
        $this->company_id = $id;
        
    }

    public function toggleGroup($groupId)
{
    $group = ModuleGroup::findOrFail($groupId);

    $newState = ! (bool) $group->is_active;
    $group->is_active = $newState;
    $group->save();

    // Optional rule (recommended): group off => everything off under it
    if (!$newState) {
        Module::where('module_group_id', $groupId)->update(['is_active' => false]);

        $moduleIds = Module::where('module_group_id', $groupId)->pluck('id');
        SubModule::whereIn('module_id', $moduleIds)->update(['is_active' => false]);
    }
    }

    public function toggleAllGroupItems($groupId, $state)
    {
        $state = (bool) $state;

        $group = ModuleGroup::findOrFail($groupId);
        $group->is_active = $state;
        $group->save();

        Module::where('module_group_id', $groupId)->update(['is_active' => $state]);

        $moduleIds = Module::where('module_group_id', $groupId)->pluck('id');
        SubModule::whereIn('module_id', $moduleIds)->update(['is_active' => $state]);
    }

    public function toggleModule($moduleId)
    {
        $module = Module::findOrFail($moduleId);
        $module->is_active = ! $module->is_active;
        $module->save();
    }

    public function toggleSubmodule($submoduleId)
    {
        $sub = SubModule::findOrFail($submoduleId);
        $sub->is_active = ! $sub->is_active;
        $sub->save();
    }

    public function toggleAllSubmodules($moduleId, $state)
    {
        SubModule::where('module_id', $moduleId)->update(['is_active' => (bool) $state]);
    }

    public function editGroup($groupId) { /* open group modal */ }
    public function editModule($moduleId) { /* open module modal */ }
    public function editSubmodule($submoduleId) { /* open submodule modal */ }



    public function render()
    {
        return view('livewire.modules.index',[
            'module_groups' => ModuleGroup::with('modules','modules.sub_modules')->paginate(10)
        ]);
    }
    
}
