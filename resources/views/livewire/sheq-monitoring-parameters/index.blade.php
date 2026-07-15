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
                                <a href="" data-toggle="modal" data-target="#sheq_monitoring_parameterModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Monitoring Parameter</a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Parameter</th>
                                    <th class="th-sm">Category</th>
                                    <th class="th-sm">Unit</th>
                                    <th class="th-sm">Limit</th>
                                    <th class="th-sm">Frequency</th>
                                    <th class="th-sm">Readings</th>
                                    <th class="th-sm">Status</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if (isset($sheq_monitoring_parameters))
                                <tbody>
                                    @forelse ($sheq_monitoring_parameters as $parameter)
                                  <tr>
                                    <td>{{$parameter->name}}</td>
                                    <td>{{ucwords($parameter->category)}}</td>
                                    <td>{{$parameter->unit}}</td>
                                    <td>
                                        @if ($parameter->limit_value)
                                            {{$parameter->limit_type == 'min' ? 'Min' : 'Max'}} {{$parameter->limit_value}} {{$parameter->unit}}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ucwords(str_replace('_',' ',$parameter->frequency))}}</td>
                                    <td>{{$parameter->readings_count}}</td>
                                    <td>
                                        @if ($parameter->is_active)
                                            <span class="label label-success">Active</span>
                                        @else
                                            <span class="label label-default">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click="edit({{$parameter->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" wire:click.prevent="delete({{$parameter->id}})"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="8">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Monitoring Parameters Found .... (e.g. Water Consumption, Electricity, Diesel Usage, Effluent pH, Paper Usage)
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
                                    @if (isset($sheq_monitoring_parameters))
                                        {{ $sheq_monitoring_parameters->links() }}
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="sheq_monitoring_parameterDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Monitoring Parameter?</strong> </center>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_monitoring_parameterModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-plus"></i> Add Monitoring Parameter <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    @include('livewire.sheq-monitoring-parameters.form')
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_monitoring_parameterEditModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-edit"></i> Edit Monitoring Parameter <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <input type="hidden" wire:model="sheq_monitoring_parameter_id">
                <div class="modal-body">
                    @include('livewire.sheq-monitoring-parameters.form')
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

</div>
