<div>
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
                                <a href="#" data-toggle="modal" data-target="#budgetModal" class="btn btn-default">
                                    <i class="fa fa-plus-square-o"></i> Add Budget
                                </a>
                            </div>

                        </div>

                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">

                            <table id="budgetsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">

                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        <th>Name</th>
                                        <th>Period</th>
                                        <th>Value</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach ($budgets as $budget)

                                        <tr>

                                            <td>{{ $budget->module }}</td>

                                            <td>{{ $budget->name }}</td>

                                            <td>{{ $budget->period }}</td>

                                            <td>
                                                {{ $budget->currency ? $budget->currency->name : '' }}
                                                {{ $budget->currency ? $budget->currency->symbol : '' }}
                                                {{ number_format($budget->value,2) }}
                                            </td>

                                            <td>
                                                <span class="badge bg-{{ $budget->status == 1 ? 'success' : 'danger' }}">
                                                    {{ $budget->status == 1 ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>

                                            <td class="w-10 line-height-35 table-dropdown">

                                                <div class="dropdown">

                                                    <button class="btn btn-default dropdown-toggle"
                                                            type="button"
                                                            data-toggle="dropdown">
                                                        <i class="fa fa-bars"></i>
                                                        <span class="caret"></span>
                                                    </button>

                                                    <ul class="dropdown-menu">

                                                        <li>
                                                            <a href="#" wire:click.prevent="edit({{ $budget->id }})">
                                                                <i class="fa fa-edit color-success"></i> Edit
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <a href="#"
                                                               data-toggle="modal"
                                                               data-target="#budgetDeleteModal{{ $budget->id }}">
                                                                <i class="fa fa-trash color-danger"></i> Delete
                                                            </a>
                                                        </li>

                                                    </ul>

                                                </div>

                                            

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>
            </div>

        </div>
    </section>





    <!-- CREATE MODAL -->

    <div wire:ignore.self
         data-backdrop="static"
         data-keyboard="false"
         class="modal"
         id="budgetModal"
         tabindex="-1"
         role="dialog"
         data-backdrop-color="blue">

        <div class="modal-dialog mw-100 w-50" role="budget">

            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title">
                        <i class="fa fa-plus"></i> Add Budget
                    </h4>

                    <button type="button"
                            class="close"
                            data-dismiss="modal">
                        <span aria-hidden="true">×</span>
                    </button>

                </div>

                <form wire:submit.prevent="store()">

                    <div class="modal-body">

                        <!-- FIRST ENTRY -->

                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">

                                    <label>
                                        Module
                                        <span class="required" style="color:red">*</span>
                                    </label>

                                    <select class="form-control"
                                            wire:model.debounce.300ms="module.0"
                                            required>

                                        <option value="">Select Module</option>
                                        <option value="Shifts">Shifts</option>
                                        <option value="Trips">Trips</option>

                                    </select>

                                    @error('module.0')
                                        <span class="error" style="color:red">{{ $message }}</span>
                                    @enderror

                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">

                                    <label>
                                        Budget Name
                                        <span class="required" style="color:red">*</span>
                                    </label>

                                    <input type="text"
                                           class="form-control"
                                           wire:model.debounce.300ms="name.0"
                                           placeholder="Enter Budget Name"
                                           required>

                                    @error('name.0')
                                        <span class="error" style="color:red">{{ $message }}</span>
                                    @enderror

                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">

                                    <label>
                                        Period
                                        <span class="required" style="color:red">*</span>
                                    </label>

                                    <select class="form-control"
                                            wire:model.debounce.300ms="period.0"
                                            required>

                                        <option value="">Select Period</option>
                                        <option value="Daily">Daily</option>
                                        <option value="Weekly">Weekly</option>
                                        <option value="Monthly">Monthly</option>
                                        <option value="Quarterly">Quarterly</option>
                                        <option value="Yearly">Yearly</option>

                                    </select>

                                    @error('period.0')
                                        <span class="error" style="color:red">{{ $message }}</span>
                                    @enderror

                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">

                                    <label>Currency</label>

                                    <select class="form-control"
                                            wire:model.debounce.300ms="currency_id.0">

                                        <option value="">Select Currency</option>

                                        @foreach ($currencies as $currency)

                                            <option value="{{ $currency->id }}">
                                                {{ $currency->name }}
                                                ({{ $currency->symbol }})
                                                {{ $currency->fullname }}
                                            </option>

                                        @endforeach

                                    </select>

                                    @error('currency_id.0')
                                        <span class="error" style="color:red">{{ $message }}</span>
                                    @enderror

                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">

                                    <label>
                                        Budget Value
                                        <span class="required" style="color:red">*</span>
                                    </label>

                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           class="form-control"
                                           wire:model.debounce.300ms="value.0"
                                           placeholder="Enter Budget Value"
                                           required>

                                    @error('value.0')
                                        <span class="error" style="color:red">{{ $message }}</span>
                                    @enderror

                                </div>
                            </div>

                        </div>





                        <!-- DYNAMIC INPUTS -->

                        @foreach ($inputs as $key => $valueIndex)

                            <hr>

                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">

                                        <label>
                                            Module
                                            <span class="required" style="color:red">*</span>
                                        </label>

                                        <select class="form-control"
                                                wire:model.debounce.300ms="module.{{ $valueIndex }}"
                                                required>

                                            <option value="">Select Module</option>
                                            <option value="Shifts">Shifts</option>
                                            <option value="Trips">Trips</option>

                                        </select>

                                        @error('module.'.$valueIndex)
                                            <span class="error" style="color:red">{{ $message }}</span>
                                        @enderror

                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">

                                        <label>
                                            Budget Name
                                            <span class="required" style="color:red">*</span>
                                        </label>

                                        <input type="text"
                                               class="form-control"
                                               wire:model.debounce.300ms="name.{{ $valueIndex }}"
                                               placeholder="Enter Budget Name"
                                               required>

                                        @error('name.'.$valueIndex)
                                            <span class="error" style="color:red">{{ $message }}</span>
                                        @enderror

                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">

                                        <label>
                                            Period
                                            <span class="required" style="color:red">*</span>
                                        </label>

                                        <select class="form-control"
                                                wire:model.debounce.300ms="period.{{ $valueIndex }}"
                                                required>

                                            <option value="">Select Period</option>
                                            <option value="Daily">Daily</option>
                                            <option value="Weekly">Weekly</option>
                                            <option value="Monthly">Monthly</option>
                                            <option value="Quarterly">Quarterly</option>
                                            <option value="Yearly">Yearly</option>

                                        </select>

                                        @error('period.'.$valueIndex)
                                            <span class="error" style="color:red">{{ $message }}</span>
                                        @enderror

                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">

                                        <label>Currency</label>

                                        <select class="form-control"
                                                wire:model.debounce.300ms="currency_id.{{ $valueIndex }}">

                                            <option value="">Select Currency</option>

                                            @foreach ($currencies as $currency)

                                                <option value="{{ $currency->id }}">
                                                    {{ $currency->name }}
                                                    ({{ $currency->symbol }})
                                                </option>

                                            @endforeach

                                        </select>

                                        @error('currency_id.'.$valueIndex)
                                            <span class="error" style="color:red">{{ $message }}</span>
                                        @enderror

                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">

                                        <label>
                                            Budget Value
                                            <span class="required" style="color:red">*</span>
                                        </label>

                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               class="form-control"
                                               wire:model.debounce.300ms="value.{{ $valueIndex }}"
                                               placeholder="Enter Budget Value"
                                               required>

                                        @error('value.'.$valueIndex)
                                            <span class="error" style="color:red">{{ $message }}</span>
                                        @enderror

                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">

                                        <button class="btn btn-danger btn-rounded xs"
                                                style="margin-top:23px"
                                                wire:click.prevent="remove({{ $key }})">

                                            <i class="fa fa-times"></i>

                                        </button>

                                    </div>
                                </div>

                            </div>

                        @endforeach





                        <div class="row">

                            <div class="col-md-12">

                                <div class="form-group">

                                    <button class="btn btn-success btn-rounded"
                                            style="float:right"
                                            wire:click.prevent="add({{ $i }})">

                                        <i class="fa fa-plus"></i> Budget

                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <div class="btn-group" role="group">

                            <button type="button"
                                    class="btn btn-gray btn-wide btn-rounded"
                                    data-dismiss="modal">

                                <i class="fa fa-times"></i> Close

                            </button>

                            <button type="submit"
                                    class="btn bg-success btn-wide btn-rounded">

                                <i class="fa fa-save"></i> Save

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>






    <!-- EDIT MODAL -->

    <div wire:ignore.self
         data-backdrop="static"
         data-keyboard="false"
         class="modal"
         id="budgetEditModal"
         tabindex="-1"
         role="dialog"
         data-backdrop-color="blue">

        <div class="modal-dialog mw-100 w-50" role="budget">

            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title">
                        <i class="fa fa-edit"></i> Edit Budget
                    </h4>

                    <button type="button"
                            class="close"
                            data-dismiss="modal">
                        <span aria-hidden="true">×</span>
                    </button>

                </div>

                <form wire:submit.prevent="update()">

                    <div class="modal-body">

                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">

                                    <label>Module</label>

                                    <select class="form-control"
                                            wire:model.debounce.300ms="module">

                                        <option value="">Select Module</option>
                                        <option value="Shifts">Shifts</option>
                                        <option value="Trips">Trips</option>

                                    </select>

                                    @error('module')
                                        <span class="error" style="color:red">{{ $message }}</span>
                                    @enderror

                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">

                                    <label>Budget Name</label>

                                    <input type="text"
                                           class="form-control"
                                           wire:model.debounce.300ms="name"
                                           placeholder="Enter Budget Name">

                                    @error('name')
                                        <span class="error" style="color:red">{{ $message }}</span>
                                    @enderror

                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">

                                    <label>Period</label>

                                    <select class="form-control"
                                            wire:model.debounce.300ms="period">

                                        <option value="">Select Period</option>
                                        <option value="Daily">Daily</option>
                                        <option value="Weekly">Weekly</option>
                                        <option value="Monthly">Monthly</option>
                                        <option value="Quarterly">Quarterly</option>
                                        <option value="Yearly">Yearly</option>

                                    </select>

                                    @error('period')
                                        <span class="error" style="color:red">{{ $message }}</span>
                                    @enderror

                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">

                                    <label>Currency</label>

                                    <select class="form-control"
                                            wire:model.debounce.300ms="currency_id">

                                        <option value="">Select Currency</option>

                                        @foreach ($currencies as $currency)

                                            <option value="{{ $currency->id }}">
                                                {{ $currency->name }}
                                                ({{ $currency->symbol }})
                                            </option>

                                        @endforeach

                                    </select>

                                    @error('currency_id')
                                        <span class="error" style="color:red">{{ $message }}</span>
                                    @enderror

                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">

                                    <label>Budget Value</label>

                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           class="form-control"
                                           wire:model.debounce.300ms="value">

                                    @error('value')
                                        <span class="error" style="color:red">{{ $message }}</span>
                                    @enderror

                                </div>
                            </div>

                        </div>

                        <div class="form-group">

                            <label>Status</label>

                            <select class="form-control"
                                    wire:model.debounce.300ms="status">

                                <option value="">Select Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>

                            </select>

                            @error('status')
                                <span class="error" style="color:red">{{ $message }}</span>
                            @enderror

                        </div>

                    </div>

                    <div class="modal-footer">

                        <div class="btn-group" role="group">

                            <button type="button"
                                    class="btn btn-gray btn-wide btn-rounded"
                                    data-dismiss="modal">

                                <i class="fa fa-times"></i> Close

                            </button>

                            <button type="submit"
                                    class="btn bg-success btn-wide btn-rounded">

                                <i class="fa fa-save"></i> Update

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>