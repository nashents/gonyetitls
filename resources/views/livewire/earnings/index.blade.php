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
                                <a href="#" data-toggle="modal" data-target="#earningModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Earning</a>
                                <small class="text-muted" style="margin-left:10px;">Payroll earnings (e.g. Accommodation, Transport) - listed under "Allowances" on a salary. Driver trip allowances are set up under Allowances.</small>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="earningsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Name</th>
                                    <th class="th-sm">Currency</th>
                                    <th class="th-sm">Default Amount</th>
                                    <th class="th-sm">PAYE Taxable</th>
                                    <th class="th-sm">Description</th>
                                    <th class="th-sm">Status</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if ($earnings->count()>0)
                                <tbody>
                                    @foreach ($earnings as $earning)
                                  <tr wire:key="{{ '2a693f-36-' . ($loop->parent?->index ?? 'r') . '-' . $loop->index . '-' . ($earning->id ?? '') }}">
                                    <td>
                                        {{$earning->name}}
                                        @if ($earning->salary_items_count)
                                            <br><small class="text-muted">On {{ $earning->salary_items_count }} salar{{ $earning->salary_items_count == 1 ? 'y' : 'ies' }}</small>
                                        @endif
                                    </td>
                                    <td>{{$earning->currency ? $earning->currency->name : ""}}</td>
                                    <td>
                                        @if ($earning->calculate_by === 'percentage' && $earning->percentage !== null)
                                            {{ $earning->percentage }}%
                                        @elseif ($earning->amount !== null && $earning->amount !== '')
                                            {{ number_format((float) $earning->amount, 2) }}
                                        @endif
                                    </td>
                                    <td><span class="badge bg-{{ $earning->taxable ? 'success' : 'secondary' }}">{{ $earning->taxable ? 'Yes' : 'No' }}</span></td>
                                    <td>{{$earning->description}}</td>
                                    <td><span class="badge bg-{{$earning->status == 1 ? "success" : "danger"}}">{{$earning->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click.prevent="edit({{$earning->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                @if ($earning->status == 1)
                                                    <li wire:key="earning-deactivate-{{ $earning->id }}"><a href="#" wire:click.prevent="toggleStatus({{ $earning->id }})"><i class="fa fa-toggle-off color-warning"></i> Deactivate</a></li>
                                                @else
                                                    <li wire:key="earning-activate-{{ $earning->id }}"><a href="#" wire:click.prevent="toggleStatus({{ $earning->id }})"><i class="fa fa-toggle-on color-success"></i> Activate</a></li>
                                                @endif
                                                @if ($earning->salary_items_count)
                                                    <li class="disabled"><a href="#" onclick="return false;" title="On {{ $earning->salary_items_count }} salary(ies) - remove it from those salaries, or deactivate it instead"><i class="fa fa-lock"></i> Delete (in use)</a></li>
                                                @else
                                                    <li><a href="#" data-toggle="modal" data-target="#earningDeleteModal{{ $earning->id }}" ><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                        @include('earnings.delete')
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>

    @foreach (['earningModal' => ['store()', 'fas fa-plus', 'Add Earning', 'Save', 'fa fa-save'], 'earningEditModal' => ['update()', 'fas fa-edit', 'Edit Earning', 'Update', 'fa fa-refresh']] as $modalId => [$action, $icon, $title, $button, $buttonIcon])
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="{{ $icon }}"></i> {{ $title }} <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="{{ $action }}" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Eg Accommodation" required />
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-6">
                            <div class="form-group">
                                <label for="currency_id">Currency</label>
                                <select wire:model.debounce.300ms="currency_id" class="form-control">
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                    @endforeach
                                </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="calculate_by">Calculate By<span class="required" style="color: red">*</span></label>
                                <select wire:model="calculate_by" class="form-control" required>
                                    <option value="currency">Fixed Amount</option>
                                    <option value="percentage">Percentage</option>
                                </select>
                                @error('calculate_by') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                @if ($calculate_by === 'percentage')
                                    <label for="percentage">Default Percentage (of basic)</label>
                                    <input type="number" step="any" min="0" max="100" class="form-control" wire:model.debounce.300ms="percentage" placeholder="Eg 10" />
                                    @error('percentage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @else
                                    <label for="amount">Default Amount</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount" placeholder="Eg 150" />
                                    @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @endif
                                <small class="text-muted">Pre-fills the salary form; can be changed per salary.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="taxable">PAYE Taxable<span class="required" style="color: red">*</span></label>
                                <select wire:model.defer="taxable" class="form-control" required>
                                    <option value="1">Yes - included in PAYE</option>
                                    <option value="0">No - part of gross, excluded from PAYE</option>
                                </select>
                                @error('taxable') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @if ($modalId === 'earningEditModal')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="status" class="form-control" required >
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" wire:model.debounce.300ms="description" placeholder="Enter Description" cols="30" rows="3"></textarea>
                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="{{ $buttonIcon }}"></i>{{ $button }}</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>
    @endforeach

</div>
