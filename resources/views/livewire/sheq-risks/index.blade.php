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
                                <a href="" data-toggle="modal" data-target="#sheq_riskModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Risk</a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="row" style="margin-bottom:10px">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" placeholder="Search hazards/risks..." wire:model.debounce.500ms="search">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" wire:model="category_filter">
                                        <option value="">All Categories</option>
                                        <option value="safety">Safety</option>
                                        <option value="health">Health</option>
                                        <option value="environment">Environment</option>
                                        <option value="quality">Quality</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" wire:model="department_filter">
                                        <option value="">All Departments</option>
                                        @foreach ($departments as $department)
                                            <option value="{{$department->id}}">{{$department->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" wire:model="top_filter">
                                        <option value="">All Risks</option>
                                        <option value="1">Top Risks Only</option>
                                        <option value="0">Non-Top Risks</option>
                                    </select>
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Category</th>
                                    <th class="th-sm">Hazard / Aspect</th>
                                    <th class="th-sm">Risk / Impact</th>
                                    <th class="th-sm">Department</th>
                                    <th class="th-sm">Rating</th>
                                    <th class="th-sm">Residual</th>
                                    <th class="th-sm">Controls</th>
                                    <th class="th-sm">Top Risk</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if (isset($sheq_risks))
                                <tbody>
                                    @forelse ($sheq_risks as $sheq_risk)
                                  <tr>
                                    <td>{{ucwords($sheq_risk->category)}}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($sheq_risk->hazard, 60) }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($sheq_risk->risk, 60) }}</td>
                                    <td>{{$sheq_risk->department->name ?? '-'}}</td>
                                    <td>
                                        @php $band = $sheq_risk->ratingBand(); @endphp
                                        @if ($band == 'Critical')
                                            <span class="label label-danger">{{$sheq_risk->rating}} - Critical</span>
                                        @elseif ($band == 'High')
                                            <span class="label label-warning">{{$sheq_risk->rating}} - High</span>
                                        @elseif ($band == 'Medium')
                                            <span class="label label-info">{{$sheq_risk->rating}} - Medium</span>
                                        @elseif ($band == 'Low')
                                            <span class="label label-success">{{$sheq_risk->rating}} - Low</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @php $rband = $sheq_risk->residualRatingBand(); @endphp
                                        @if ($rband == 'Critical')
                                            <span class="label label-danger">{{$sheq_risk->residual_rating}}</span>
                                        @elseif ($rband == 'High')
                                            <span class="label label-warning">{{$sheq_risk->residual_rating}}</span>
                                        @elseif ($rband == 'Medium')
                                            <span class="label label-info">{{$sheq_risk->residual_rating}}</span>
                                        @elseif ($rband == 'Low')
                                            <span class="label label-success">{{$sheq_risk->residual_rating}}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        {{$sheq_risk->controls->count()}}
                                        @if ($sheq_risk->controls->where('is_critical',1)->count())
                                            <span class="label label-primary">{{$sheq_risk->controls->where('is_critical',1)->count()}} Critical</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($sheq_risk->is_top_risk)
                                            <span class="label label-danger">Top Risk</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click.prevent="controls({{$sheq_risk->id}})"><i class="fa fa-shield color-primary"></i> Controls</a></li>
                                                <li><a href="#" wire:click="edit({{$sheq_risk->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" wire:click.prevent="delete({{$sheq_risk->id}})"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="9">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Risks Found ....
                                        </div>
                                    </td>
                                  </tr>
                                @endforelse
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                @endif
                              </table>
                                 <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($sheq_risks))
                                        {{ $sheq_risks->links() }}
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="sheq_riskDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Risk and its controls?</strong> </center>
                </div>
                <form wire:submit.prevent="destroy()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_riskModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-plus"></i> Add Risk <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    @include('livewire.sheq-risks.form')
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_riskEditModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-edit"></i> Edit Risk <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <input type="hidden" wire:model="sheq_risk_id">
                <div class="modal-body">
                    @include('livewire.sheq-risks.form')
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_riskControlsModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-75" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-shield-alt"></i> Risk Controls <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <div class="modal-body">
                    <table class="table table-striped table-bordered table-sm" width="100%">
                        <thead>
                            <tr>
                                <th>Control</th>
                                <th>Hierarchy</th>
                                <th>Critical</th>
                                <th>Effectiveness</th>
                                <th>Last Evaluated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($current_controls as $control)
                                <tr>
                                    <td>{{$control->description}}</td>
                                    <td>{{ucwords($control->hierarchy)}}</td>
                                    <td>
                                        @if ($control->is_critical)
                                            <span class="label label-primary">Critical Control</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if ($control->effectiveness == 'effective')
                                            <span class="label label-success">Effective</span>
                                        @elseif ($control->effectiveness == 'partially_effective')
                                            <span class="label label-warning">Partially Effective</span>
                                        @elseif ($control->effectiveness == 'not_effective')
                                            <span class="label label-danger">Not Effective</span>
                                        @else
                                            <span class="label label-default">Not Evaluated</span>
                                        @endif
                                    </td>
                                    <td>{{$control->last_evaluated ? \Carbon\Carbon::parse($control->last_evaluated)->format('d M Y') : '-'}}</td>
                                    <td>
                                        <div class="btn-group btn-group-xs">
                                            <button type="button" class="btn btn-success btn-xs" wire:click="evaluateControl({{$control->id}},'effective')" title="Mark Effective"><i class="fa fa-check"></i></button>
                                            <button type="button" class="btn btn-warning btn-xs" wire:click="evaluateControl({{$control->id}},'partially_effective')" title="Mark Partially Effective"><i class="fa fa-adjust"></i></button>
                                            <button type="button" class="btn btn-danger btn-xs" wire:click="evaluateControl({{$control->id}},'not_effective')" title="Mark Not Effective"><i class="fa fa-times"></i></button>
                                            <button type="button" class="btn btn-default btn-xs" wire:click="deleteControl({{$control->id}})" title="Delete"><i class="fa fa-trash color-danger"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><center>No controls recorded for this risk yet.</center></td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <hr>
                    <h5><strong>Add Control</strong></h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Control Description<span class="required" style="color: red">*</span></label>
                                <textarea class="form-control" wire:model.debounce.300ms="control_description" rows="2"></textarea>
                                @error('control_description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Hierarchy of Control</label>
                                <select class="form-control" wire:model.debounce.300ms="control_hierarchy">
                                    <option value="elimination">Elimination</option>
                                    <option value="substitution">Substitution</option>
                                    <option value="engineering">Engineering</option>
                                    <option value="administrative">Administrative</option>
                                    <option value="ppe">PPE</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Critical Control?</label>
                                <select class="form-control" wire:model.debounce.300ms="control_is_critical">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn bg-success btn-rounded" wire:click="storeControl()"><i class="fa fa-plus"></i> Add Control</button>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
