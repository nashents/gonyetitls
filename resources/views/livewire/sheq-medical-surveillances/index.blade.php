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
                                <a href="" data-toggle="modal" data-target="#sheq_medical_surveillanceModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Medical Record</a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="row" style="margin-bottom:10px">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" placeholder="Search employee..." wire:model.debounce.500ms="search">
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" wire:model="exam_filter">
                                        <option value="">All Exam Types</option>
                                        <option value="pre_employment">Pre-Employment</option>
                                        <option value="periodic">Periodic</option>
                                        <option value="transfer">Transfer / Re-assignment</option>
                                        <option value="exit">Exit</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" wire:model="due_filter">
                                        <option value="">All Records</option>
                                        <option value="overdue">Overdue For Next Exam</option>
                                    </select>
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Employee</th>
                                    <th class="th-sm">Exam Type</th>
                                    <th class="th-sm">Exam Date</th>
                                    <th class="th-sm">Next Due</th>
                                    <th class="th-sm">Provider</th>
                                    <th class="th-sm">Outcome</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if (isset($sheq_medical_surveillances))
                                <tbody>
                                    @forelse ($sheq_medical_surveillances as $surveillance)
                                  <tr>
                                    <td>{{$surveillance->employee ? $surveillance->employee->name.' '.$surveillance->employee->surname : '-'}}</td>
                                    <td>{{ucwords(str_replace('_',' ',$surveillance->exam_type))}}</td>
                                    <td>{{$surveillance->exam_date ? \Carbon\Carbon::parse($surveillance->exam_date)->format('d M Y') : '-'}}</td>
                                    <td>
                                        {{$surveillance->next_due_date ? \Carbon\Carbon::parse($surveillance->next_due_date)->format('d M Y') : '-'}}
                                        @if ($surveillance->isOverdue())
                                            <span class="label label-danger">Overdue</span>
                                        @endif
                                    </td>
                                    <td>{{$surveillance->provider}}</td>
                                    <td>
                                        @if ($surveillance->outcome == 'fit')
                                            <span class="label label-success">Fit</span>
                                        @elseif ($surveillance->outcome == 'fit_with_restrictions')
                                            <span class="label label-warning">Fit With Restrictions</span>
                                        @elseif ($surveillance->outcome == 'unfit')
                                            <span class="label label-danger">Unfit</span>
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
                                                <li><a href="#" wire:click="edit({{$surveillance->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" wire:click.prevent="delete({{$surveillance->id}})"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="7">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Medical Surveillance Records Found ....
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
                                    @if (isset($sheq_medical_surveillances))
                                        {{ $sheq_medical_surveillances->links() }}
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="sheq_medical_surveillanceDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Medical Surveillance Record?</strong> </center>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_medical_surveillanceModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-plus"></i> Add Medical Surveillance Record <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    @include('livewire.sheq-medical-surveillances.form')
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_medical_surveillanceEditModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-edit"></i> Edit Medical Surveillance Record <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <input type="hidden" wire:model="sheq_medical_surveillance_id">
                <div class="modal-body">
                    @include('livewire.sheq-medical-surveillances.form')
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
