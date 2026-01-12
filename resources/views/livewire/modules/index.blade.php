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

                            <div class="panel-title">
                                <a href="" data-toggle="modal" data-target="#moduleModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Module</a>
                            </div>
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
                                                        <li>
                                                            <a href="#" wire:click.prevent="editGroup({{ $group->id }})">
                                                                <i class="fa fa-edit color-success"></i> Edit Group
                                                            </a>
                                                        </li>

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
                                                            <li>
                                                                <a href="#" wire:click.prevent="editModule({{ $module->id }})">
                                                                    <i class="fa fa-edit color-success"></i> Edit Module
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#" wire:click.prevent="toggleAllsub_modules({{ $module->id }}, true)">
                                                                    <i class="fa fa-check"></i> Enable all sub_modules
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#" wire:click.prevent="toggleAllsub_modules({{ $module->id }}, false)">
                                                                    <i class="fa fa-ban"></i> Disable all sub_modules
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

                                                    <td class="nowrap">
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
                                                    </td>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bank_accountModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="bank_account">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Bank Account(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title">Bank<span class="required" style="color: red">*</span></label>
                            <input type="text" class="form-control"  wire:model.debounce.300ms="name.0" placeholder="Enter Bank Name" required />
                            @error('name.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="file">Account Name<span class="required" style="color: red">*</span></label>
                            <input type="text" class="form-control"  wire:model.debounce.300ms="account_name.0" placeholder="Enter Account Name" required/>
                            @error('account_name.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                   
                   
                </div>
                <div class="row">
                   
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="file">Account Number<span class="required" style="color: red">*</span></label>
                            <input type="text" class="form-control"  wire:model.debounce.300ms="account_number.0" placeholder="Enter Account Number" required/>
                            @error('account_number.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                  
                </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="expiry_date">Account Branch<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="branch.0" placeholder="Enter Branch" required />
                                @error('branch.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="expiry_date">Branch Code</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="branch_code.0" placeholder="Enter Branch Code" />
                                @error('branch_code.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="expiry_date">Swift Code</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="swift_code.0" placeholder="Enter Swift Code" />
                                @error('swift_code.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                       
                        @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Bank<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="name.{{ $value }}" placeholder="Enter Bank Name " required/>
                                    @error('name.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file">Account Name<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="account_name.{{ $value }}" placeholder="Enter Account Name" required/>
                                    @error('account_name.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                         
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file">Account Number<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="account_number.{{ $value }}" placeholder="Enter Account Number" required />
                                    @error('account_number.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                           
                        </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="expiry_date">Account Branch<span class="required" style="color: red">*</span></label>
                                        <input type="text" class="form-control"  wire:model.debounce.300ms="branch.{{ $value }}" placeholder="Enter Branch" required />
                                        @error('branch.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="expiry_date">Branch Code</label>
                                        <input type="text" class="form-control"  wire:model.debounce.300ms="branch_code.{{ $value }}" placeholder="Enter Branch Code" >
                                        @error('branch_code.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="expiry_date">Swift Code</label>
                                        <input type="text" class="form-control"  wire:model.debounce.300ms="swift_code.{{ $value }}" placeholder="Enter Swift Code" >
                                        @error('swift_code.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                            
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Bank Account</button>
                                </div>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bank_accountEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="bank_account">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Bank Account <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Bank<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="name" placeholder="Enter Bank Name" required />
                                    @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file">Account Name<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="account_name" placeholder="Enter Account Name" required/>
                                    @error('account_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                           
                           
                        </div>
                        <div class="row">
                           
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file">Account Number<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="account_number" placeholder="Enter Account Number" required/>
                                    @error('account_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                
                            </div>
                        </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="expiry_date">Account Branch<span class="required" style="color: red">*</span></label>
                                        <input type="text" class="form-control"  wire:model.debounce.300ms="branch" placeholder="Enter Branch" required />
                                        @error('branch.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="expiry_date">Branch Code</label>
                                        <input type="text" class="form-control"  wire:model.debounce.300ms="branch_code" placeholder="Enter Branch Code" />
                                        @error('branch_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="expiry_date">Swift Code</label>
                                        <input type="text" class="form-control"  wire:model.debounce.300ms="swift_code" placeholder="Enter Swift Code" />
                                        @error('swift_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                           
                           
                           
                            <div class="form-group">
                                <label for="title">Status</label>
                                <select class="form-control" wire:model.debounce.300ms="status">
                                    <option value="">Select Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>



</div>

