<div>
    @section('extra-css')
        <style>
            .chip { margin: 2px 4px 2px 0; display:inline-block; }
            .row-muted { opacity: .85; }
            .indent { padding-left: 28px; }
            .tiny { font-size: 12px; }
            .nowrap { white-space: nowrap; }
            .toggle-cell { width: 90px; text-align: center; vertical-align: middle; }
            .action-cell { width: 140px; }
        </style>
    @endsection
    @push('scripts')
        <script>
            window.addEventListener('close-modal', (e) => {
                const id = e.detail.id;
                if (id) $('#' + id).modal('hide');
            });

            // Optional toast hook (use your own toast lib)
            window.addEventListener('toast', (e) => {
                // console.log(e.detail);
            });
        </script>
    @endpush
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>

                            {{-- <div class="panel-title">
                                <div class="mb-2">
                                    <button class="btn btn-default" data-toggle="modal" data-target="#moduleGroupModal" wire:click="resetGroupForm">
                                        <i class="fa fa-plus-square-o"></i> Add Module Group
                                    </button>

                                    <button class="btn btn-default" data-toggle="modal" data-target="#moduleModal" wire:click="resetModuleForm">
                                        <i class="fa fa-plus-square-o"></i> Add Module
                                    </button>

                                    <button class="btn btn-default" data-toggle="modal" data-target="#submoduleModal" wire:click="resetSubmoduleForm">
                                        <i class="fa fa-plus-square-o"></i> Add Submodule
                                    </button>
                                </div>
                            </div> --}}
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-sm w-100">
                                    <thead>
                                        <tr>
                                            <th style="width: 22%">Group</th>
                                            <th style="width: 26%">Module</th>
                                            <th>Submodule</th>
                                            <th class="toggle-cell">Show</th>
                                            <th class="action-cell">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($module_groups as $group)

                                        @php
                                            $modules = $group->modules ?? collect();
                                            $modulesCount = $modules->count();
                                            $sub_modulesCount = $modules->sum(fn($m) => $m->sub_modules->count());
                                            $activeModulesCount = $modules->where('is_active', true)->count();
                                            $activesub_modulesCount = $modules->flatMap->sub_modules->where('is_active', true)->count();

                                            // If you store is_active on group:
                                            $groupIsActive = (bool) ($group->is_active ?? true);
                                        @endphp

                                        {{-- GROUP ROW (now includes toggle + actions) --}}
                                        <tr class="table-active">
                                            <td colspan="3">
                                                <span class="badge badge-primary chip">GROUP</span>

                                                @if($groupIsActive)
                                                    <span class="badge badge-success chip">Active</span>
                                                @else
                                                    <span class="badge badge-danger chip">Hidden</span>
                                                @endif

                                                <strong>{{ $group->name }}</strong>

                                                <span class="badge badge-dark chip">Modules: {{ $modulesCount }}</span>
                                                <span class="badge badge-info chip">sub_modules: {{ $sub_modulesCount }}</span>

                                                <span class="badge badge-success chip">Active M: {{ $activeModulesCount }}</span>
                                                <span class="badge badge-success chip">Active S: {{ $activesub_modulesCount }}</span>

                                                @if(!empty($group->slug ?? null))
                                                    <span class="badge badge-secondary chip tiny">{{ $group->slug }}</span>
                                                @endif
                                            </td>

                                            <td class="toggle-cell">
                                                <input type="checkbox"
                                                    wire:change="toggleGroup({{ $group->id }})"
                                                    {{ $groupIsActive ? 'checked' : '' }}>
                                            </td>

                                            <td class="nowrap">
                                                <div class="dropdown">
                                                    <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
                                                        <i class="fa fa-bars"></i> <span class="caret"></span>
                                                    </button>

                                                    <ul class="dropdown-menu">
                                                        {{-- <li>
                                                            <a href="#" wire:click.prevent="editGroup({{ $group->id }})">
                                                                <i class="fa fa-edit color-success"></i> Edit Group
                                                            </a>
                                                        </li> --}}

                                                        <li>
                                                            <a href="#" wire:click.prevent="toggleAllGroupItems({{ $group->id }}, true)">
                                                                <i class="fa fa-check"></i> Enable Group (Modules + sub_modules)
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#" wire:click.prevent="toggleAllGroupItems({{ $group->id }}, false)">
                                                                <i class="fa fa-ban"></i> Disable Group (Modules + sub_modules)
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- MODULE + SUBMODULE ROWS --}}
                                        @foreach($group->modules as $module)
                                            @php
                                                $moduleSubCount = $module->sub_modules->count();
                                                $moduleActiveSubCount = $module->sub_modules->where('is_active', true)->count();
                                            @endphp

                                            <tr>
                                                <td class="row-muted"></td>

                                                <td>
                                                    <span class="badge badge-dark chip">MODULE</span>

                                                    @if($module->is_active)
                                                        <span class="badge badge-success chip">Active</span>
                                                    @else
                                                        <span class="badge badge-danger chip">Hidden</span>
                                                    @endif

                                                    <strong>{{ $module->name }}</strong>

                                                    <div class="tiny text-muted">
                                                        <span class="badge badge-secondary chip">{{ $module->slug }}</span>

                                                        @if(!empty($module->route_name))
                                                            <span class="badge badge-info chip">{{ $module->route_name }}</span>
                                                        @endif

                                                        <span class="badge badge-primary chip">Sub: {{ $moduleSubCount }}</span>
                                                        <span class="badge badge-success chip">Active Sub: {{ $moduleActiveSubCount }}</span>
                                                    </div>
                                                </td>

                                                <td class="text-muted">—</td>

                                                <td class="toggle-cell">
                                                    <input type="checkbox"
                                                        wire:change="toggleModule({{ $module->id }})"
                                                        {{ $module->is_active ? 'checked' : '' }}>
                                                </td>

                                                <td class="nowrap">
                                                    <div class="dropdown">
                                                        <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
                                                            <i class="fa fa-bars"></i> <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            {{-- <li>
                                                                <a href="#" wire:click.prevent="editModule({{ $module->id }})">
                                                                    <i class="fa fa-edit color-success"></i> Edit Module
                                                                </a>
                                                            </li> --}}
                                                            <li>
                                                                <a href="#" wire:click.prevent="toggleAllModuleItems({{ $module->id }}, true)">
                                                                    <i class="fa fa-check"></i> Enable Module (Sub Modules)
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#" wire:click.prevent="toggleAllModuleItems({{ $module->id }}, false)">
                                                                    <i class="fa fa-ban"></i> Disable Module (Sub Modules)
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#" wire:click.prevent="deleteModuleWithItems({{ $module->id }}, false)">
                                                                    <i class="fa fa-trash"></i> Delete Module (Sub Modules)
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>

                                            @foreach($module->sub_modules as $submodule)
                                                <tr>
                                                    <td></td>

                                                    <td class="indent text-muted">
                                                        <i class="fa fa-level-down"></i>
                                                        <span class="badge badge-secondary chip tiny">of {{ $module->slug }}</span>
                                                    </td>

                                                    <td>
                                                        <span class="badge badge-warning chip">SUB</span>

                                                        @if($submodule->is_active)
                                                            <span class="badge badge-success chip">Active</span>
                                                        @else
                                                            <span class="badge badge-danger chip">Hidden</span>
                                                        @endif

                                                        <strong>{{ $submodule->name }}</strong>

                                                        <div class="tiny text-muted">
                                                            <span class="badge badge-secondary chip">{{ $submodule->slug }}</span>

                                                            @if(!empty($submodule->route_name))
                                                                <span class="badge badge-info chip">{{ $submodule->route_name }}</span>
                                                            @endif

                                                            @if(!empty($submodule->badge_key))
                                                                <span class="badge badge-primary chip">{{ $submodule->badge_key }}</span>
                                                            @endif
                                                        </div>
                                                    </td>

                                                    <td class="toggle-cell">
                                                        <input type="checkbox"
                                                            wire:change="toggleSubmodule({{ $submodule->id }})"
                                                            {{ $submodule->is_active ? 'checked' : '' }}>
                                                    </td>

                                                    {{-- <td class="nowrap">
                                                        <div class="dropdown">
                                                            <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
                                                                <i class="fa fa-bars"></i> <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <a href="#" wire:click.prevent="editSubmodule({{ $submodule->id }})">
                                                                        <i class="fa fa-edit color-success"></i> Edit Submodule
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </td> --}}
                                                </tr>
                                            @endforeach
                                        @endforeach

                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                          
                             <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($module_groups))
                                        {{ $module_groups->links() }} 
                                    @endif 
                                </ul>
                            </nav>    

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>


    {{-- =========================
    1) MODULE GROUP MODAL
    ========================= --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false"
        class="modal" id="moduleGroupModal" tabindex="-1" role="dialog" aria-labelledby="moduleGroupLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title" id="moduleGroupLabel">
                        <i class="fa fa-plus"></i> Add Module Group
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </h4>
                </div>

                <form wire:submit.prevent="storeGroup">
                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name <span class="required" style="color:red">*</span></label>
                                    <input type="text" class="form-control"
                                        wire:model.debounce.300ms="group_name"
                                        placeholder="e.g. Human Resource" required>
                                    @error('group_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Slug <span class="required" style="color:red">*</span></label>
                                    <input type="text" class="form-control"
                                        wire:model.debounce.300ms="group_slug"
                                        placeholder="e.g. human-resource" required>
                                    @error('group_slug') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Icon</label>
                                    <input type="text" class="form-control"
                                        wire:model.debounce.300ms="group_icon"
                                        placeholder="e.g. fas fa-users">
                                    @error('group_icon') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Sort Order</label>
                                    <input type="number" class="form-control"
                                        wire:model.debounce.300ms="group_sort_order"
                                        placeholder="0" min="0">
                                    @error('group_sort_order') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Active?</label>
                                    <select class="form-control" wire:model="group_is_active">
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                    @error('group_is_active') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Visibility (JSON)</label>
                                    <textarea class="form-control" rows="4"
                                            wire:model.debounce.300ms="group_visibility_json"
                                            placeholder='e.g. {"roles":["Super Admin"],"departments":["Security"]}'></textarea>
                                    @error('group_visibility_json') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal">
                                <i class="fa fa-times"></i> Close
                            </button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded">
                                <i class="fa fa-save"></i> Save
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>


    {{-- =========================
        2) MODULE MODAL
    ========================= --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false"
        class="modal" id="moduleModal" tabindex="-1" role="dialog" aria-labelledby="moduleLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title" id="moduleLabel">
                        <i class="fa fa-plus"></i> Add Module
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </h4>
                </div>

                <form wire:submit.prevent="storeModule">
                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Module Group <span class="required" style="color:red">*</span></label>
                                    <select class="form-control" wire:model="module_group_id" required>
                                        <option value="">-- select group --</option>
                                        @foreach($moduleGroups  as $g)
                                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('module_group_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name <span class="required" style="color:red">*</span></label>
                                    <input type="text" class="form-control"
                                        wire:model.debounce.300ms="module_name"
                                        placeholder="e.g. Fleet Management" required>
                                    @error('module_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Slug <span class="required" style="color:red">*</span></label>
                                    <input type="text" class="form-control"
                                        wire:model.debounce.300ms="module_slug"
                                        placeholder="e.g. fleet-management" required>
                                    @error('module_slug') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Icon</label>
                                    <input type="text" class="form-control"
                                        wire:model.debounce.300ms="module_icon"
                                        placeholder="e.g. fas fa-truck">
                                    @error('module_icon') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Badge Key</label>
                                    <input type="text" class="form-control"
                                        wire:model.debounce.300ms="module_badge_key"
                                        placeholder="e.g. gate_passes_pending_count">
                                    @error('module_badge_key') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Route Name</label>
                                    <input type="text" class="form-control"
                                        wire:model.debounce.300ms="module_route_name"
                                        placeholder="e.g. trips.index">
                                    @error('module_route_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>URL (optional)</label>
                                    <input type="text" class="form-control"
                                        wire:model.debounce.300ms="module_url"
                                        placeholder="e.g. /trips">
                                    @error('module_url') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sort Order</label>
                                    <input type="number" class="form-control"
                                        wire:model.debounce.300ms="module_sort_order"
                                        placeholder="0" min="0">
                                    @error('module_sort_order') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Active?</label>
                                    <select class="form-control" wire:model="module_is_active">
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                    @error('module_is_active') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Route Params (JSON)</label>
                                    <textarea class="form-control" rows="3"
                                            wire:model.debounce.300ms="module_route_params_json"
                                            placeholder='e.g. {"department":"security"}'></textarea>
                                    @error('module_route_params_json') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Visibility (JSON)</label>
                                    <textarea class="form-control" rows="4"
                                            wire:model.debounce.300ms="module_visibility_json"
                                            placeholder='e.g. {"roles":["Super Admin"],"permissions":["trips.view"]}'></textarea>
                                    @error('module_visibility_json') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal">
                                <i class="fa fa-times"></i> Close
                            </button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded">
                                <i class="fa fa-save"></i> Save
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>


    {{-- =========================
        3) SUBMODULE MODAL
    ========================= --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false"
        class="modal" id="submoduleModal" tabindex="-1" role="dialog" aria-labelledby="submoduleLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title" id="submoduleLabel">
                        <i class="fa fa-plus"></i> Add Submodule
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </h4>
                </div>

                <form wire:submit.prevent="storeSubmodule">
                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Module <span class="required" style="color:red">*</span></label>
                                    <select class="form-control" wire:model="sub_module_id" required>
                                        <option value="">-- select module --</option>
                                        @foreach($modules as $m)
                                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('sub_module_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name <span class="required" style="color:red">*</span></label>
                                    <input type="text" class="form-control"
                                        wire:model.debounce.300ms="sub_name"
                                        placeholder="e.g. Manage Trips" required>
                                    @error('sub_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Slug <span class="required" style="color:red">*</span></label>
                                    <input type="text" class="form-control"
                                        wire:model.debounce.300ms="sub_slug"
                                        placeholder="e.g. manage-trips" required>
                                    @error('sub_slug') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Icon</label>
                                    <input type="text" class="form-control"
                                        wire:model.debounce.300ms="sub_icon"
                                        placeholder="e.g. fas fa-list">
                                    @error('sub_icon') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Route Name</label>
                                    <input type="text" class="form-control"
                                        wire:model.debounce.300ms="sub_route_name"
                                        placeholder="e.g. trips.index">
                                    @error('sub_route_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>URL (optional)</label>
                                    <input type="text" class="form-control"
                                        wire:model.debounce.300ms="sub_url"
                                        placeholder="e.g. /trips">
                                    @error('sub_url') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Sort Order</label>
                                    <input type="number" class="form-control"
                                        wire:model.debounce.300ms="sub_sort_order"
                                        placeholder="0" min="0">
                                    @error('sub_sort_order') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Active?</label>
                                    <select class="form-control" wire:model="sub_is_active">
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                    @error('sub_is_active') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Route Params (JSON)</label>
                                    <textarea class="form-control" rows="3"
                                            wire:model.debounce.300ms="sub_route_params_json"
                                            placeholder='e.g. {"department":"security"}'></textarea>
                                    @error('sub_route_params_json') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Visibility (JSON)</label>
                                    <textarea class="form-control" rows="4"
                                            wire:model.debounce.300ms="sub_visibility_json"
                                            placeholder='e.g. {"roles":["Super Admin"],"permissions":["trips.view"]}'></textarea>
                                    @error('sub_visibility_json') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal">
                                <i class="fa fa-times"></i> Close
                            </button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded">
                                <i class="fa fa-save"></i> Save
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

