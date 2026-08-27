<?php

namespace App\Http\Livewire\Modules;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\SubModule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{

use WithPagination;
protected $paginationTheme = 'bootstrap';

protected $module_groups;
public $company_id;
public $moduleGroups = [];
public $modules = [];

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
        abort_unless(Auth::user()->is_admin(), 403);

        $this->company_id = $id;
        $this->refreshLists();

    }

    public function toggleGroup($groupId)
    {
        $group = ModuleGroup::findOrFail($groupId);

        $newState = ! (bool) $group->is_active;

        $group->is_active      = $newState;
        $group->is_customized  = true;
        $group->customized_at  = now();
        $group->save();

        // Optional rule (recommended): group off => everything off under it
        if (! $newState) {

            Module::where('module_group_id', $groupId)->update([
                'is_active'     => false,
                'is_customized' => true,
                'customized_at' => now(),
            ]);

            $moduleIds = Module::where('module_group_id', $groupId)->pluck('id');

            SubModule::whereIn('module_id', $moduleIds)->update([
                'is_active'     => false,
                'is_customized' => true,
                'customized_at' => now(),
            ]);
        }
    }

    public function toggleAllGroupItems($groupId, $state)
    {
        $state = (bool) $state;

        $group = ModuleGroup::findOrFail($groupId);

        $group->is_active      = $state;
        $group->is_customized  = true;
        $group->customized_at  = now();
        $group->save();

        Module::where('module_group_id', $groupId)->update([
            'is_active'     => $state,
            'is_customized' => true,
            'customized_at' => now(),
        ]);

        $moduleIds = Module::where('module_group_id', $groupId)->pluck('id');

        SubModule::whereIn('module_id', $moduleIds)->update([
            'is_active'     => $state,
            'is_customized' => true,
            'customized_at' => now(),
        ]);
    }

    public function toggleModule($moduleId)
    {
        $module = Module::findOrFail($moduleId);

        $module->is_active      = ! (bool) $module->is_active;
        $module->is_customized  = true;
        $module->customized_at  = now();
        $module->save();
    }

    public function toggleSubmodule($submoduleId)
    {
        $sub = SubModule::findOrFail($submoduleId);

        $sub->is_active      = ! (bool) $sub->is_active;
        $sub->is_customized  = true;
        $sub->customized_at  = now();
        $sub->save();
    }

    public function toggleAllModuleItems($moduleId, $state)
    {
        $state = (bool) $state;

        $module = Module::findOrFail($moduleId);

        $module->is_active      = $state;
        $module->is_customized  = true;
        $module->customized_at  = now();
        $module->save();

        SubModule::where('module_id', $moduleId)->update([
            'is_active'     => $state,
            'is_customized' => true,
            'customized_at' => now(),
        ]);
    }

    public function deleteModuleWithItems(int $moduleId): void
    {
        DB::transaction(function () use ($moduleId) {

            // Ensure module exists (throws 404 if not)
            $module = Module::findOrFail($moduleId);

            // Delete children first (safe even if no FK cascade)
            SubModule::where('module_id', $moduleId)->delete();

            // Then delete parent
            $module->delete();
        });
    }
    
    public function editGroup($groupId) { /* open group modal */ }
    public function editModule($moduleId) { /* open module modal */ }
    public function editSubmodule($submoduleId) { /* open submodule modal */ }


 

    // Group fields
    public $group_name, $group_slug, $group_icon, $group_sort_order = 0, $group_is_active = 1;
    public $group_visibility_json;

    // Module fields
    public $module_group_id;
    public $module_name, $module_slug, $module_icon, $module_route_name, $module_url, $module_badge_key;
    public $module_sort_order = 0, $module_is_active = 1;
    public $module_visibility_json, $module_route_params_json;

    // Submodule fields
    public $sub_module_id;
    public $sub_name, $sub_slug, $sub_icon, $sub_route_name, $sub_url;
    public $sub_sort_order = 0, $sub_is_active = 1;
    public $sub_visibility_json, $sub_route_params_json;

   

    public function refreshLists()
    {
        $this->moduleGroups = ModuleGroup::orderBy('sort_order')->get();
        $this->modules      = Module::orderBy('sort_order')->get();
    }

    public function resetGroupForm()
    {
        $this->reset([
            'group_name','group_slug','group_icon','group_sort_order','group_is_active','group_visibility_json'
        ]);
        $this->group_sort_order = 0;
        $this->group_is_active = 1;
        $this->resetValidation();
    }

    public function resetModuleForm()
    {
        $this->reset([
            'module_group_id','module_name','module_slug','module_icon','module_route_name','module_url',
            'module_badge_key','module_sort_order','module_is_active','module_visibility_json','module_route_params_json'
        ]);
        $this->module_sort_order = 0;
        $this->module_is_active = 1;
        $this->resetValidation();
    }

    public function resetSubmoduleForm()
    {
        $this->reset([
            'sub_module_id','sub_name','sub_slug','sub_icon','sub_route_name','sub_url',
            'sub_sort_order','sub_is_active','sub_visibility_json','sub_route_params_json'
        ]);
        $this->sub_sort_order = 0;
        $this->sub_is_active = 1;
        $this->resetValidation();
    }

    public function storeGroup()
    {
        $data = $this->validate([
            'group_name'           => 'required|string|max:255',
            'group_slug'           => 'required|string|max:255|unique:module_groups,slug',
            'group_icon'           => 'nullable|string|max:255',
            'group_sort_order'     => 'nullable|integer|min:0',
            'group_is_active'      => 'required|boolean',
            'group_visibility_json'=> 'nullable|json',
        ]);

        ModuleGroup::create([
            'name'       => $data['group_name'],
            'slug'       => $data['group_slug'],
            'icon'       => $data['group_icon'] ?? null,
            'sort_order' => $data['group_sort_order'] ?? 0,
            'is_active'  => (bool)$data['group_is_active'],
            'visibility' => !empty($data['group_visibility_json'])
                ? json_decode($data['group_visibility_json'], true)
                : null,
        ]);

        $this->refreshLists();
        $this->dispatchBrowserEvent('toast', ['type' => 'success', 'message' => 'Module Group added.']);
        $this->dispatchBrowserEvent('close-modal', ['id' => 'moduleGroupModal']);
        $this->resetGroupForm();
    }

    public function storeModule()
    {
        $data = $this->validate([
            'module_group_id'         => 'required|exists:module_groups,id',
            'module_name'             => 'required|string|max:255',
            'module_slug'             => 'required|string|max:255|unique:modules,slug',
            'module_icon'             => 'nullable|string|max:255',
            'module_route_name'       => 'nullable|string|max:255',
            'module_url'              => 'nullable|string|max:255',
            'module_badge_key'        => 'nullable|string|max:255',
            'module_sort_order'       => 'nullable|integer|min:0',
            'module_is_active'        => 'required|boolean',
            'module_visibility_json'  => 'nullable|json',
            'module_route_params_json'=> 'nullable|json',
        ]);

        Module::create([
            'module_group_id' => $data['module_group_id'],
            'name'            => $data['module_name'],
            'slug'            => $data['module_slug'],
            'icon'            => $data['module_icon'] ?? null,
            'route_name'      => $data['module_route_name'] ?? null,
            'url'             => $data['module_url'] ?? null,
            'badge_key'       => $data['module_badge_key'] ?? null,
            'sort_order'      => $data['module_sort_order'] ?? 0,
            'is_active'       => (bool)$data['module_is_active'],
            'visibility'      => !empty($data['module_visibility_json'])
                ? json_decode($data['module_visibility_json'], true)
                : null,
            'route_params'    => !empty($data['module_route_params_json'])
                ? json_decode($data['module_route_params_json'], true)
                : null,
        ]);

        $this->refreshLists();
        $this->dispatchBrowserEvent('toast', ['type' => 'success', 'message' => 'Module added.']);
        $this->dispatchBrowserEvent('close-modal', ['id' => 'moduleModal']);
        $this->resetModuleForm();
    }

    public function storeSubmodule()
    {
        $data = $this->validate([
            'sub_module_id'          => 'required|exists:modules,id',
            'sub_name'               => 'required|string|max:255',
            'sub_slug'               => 'required|string|max:255|unique:submodules,slug',
            'sub_icon'               => 'nullable|string|max:255',
            'sub_route_name'         => 'nullable|string|max:255',
            'sub_url'                => 'nullable|string|max:255',
            'sub_sort_order'         => 'nullable|integer|min:0',
            'sub_is_active'          => 'required|boolean',
            'sub_visibility_json'    => 'nullable|json',
            'sub_route_params_json'  => 'nullable|json',
        ]);

        SubModule::create([
            'module_id'     => $data['sub_module_id'],
            'name'          => $data['sub_name'],
            'slug'          => $data['sub_slug'],
            'icon'          => $data['sub_icon'] ?? null,
            'route_name'    => $data['sub_route_name'] ?? null,
            'url'           => $data['sub_url'] ?? null,
            'sort_order'    => $data['sub_sort_order'] ?? 0,
            'is_active'     => (bool)$data['sub_is_active'],
            'visibility'    => !empty($data['sub_visibility_json'])
                ? json_decode($data['sub_visibility_json'], true)
                : null,
            'route_params'  => !empty($data['sub_route_params_json'])
                ? json_decode($data['sub_route_params_json'], true)
                : null,
        ]);

        $this->refreshLists();
        $this->dispatchBrowserEvent('toast', ['type' => 'success', 'message' => 'Submodule added.']);
        $this->dispatchBrowserEvent('close-modal', ['id' => 'submoduleModal']);
        $this->resetSubmoduleForm();
    }


    public function render()
    {
        return view('livewire.modules.index',[
            'module_groups' => ModuleGroup::with('modules','modules.sub_modules')->paginate(10)
        ]);
    }
    
}
