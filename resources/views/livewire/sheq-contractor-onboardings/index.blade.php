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
                                <a href="" data-toggle="modal" data-target="#sheq_contractor_onboardingModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Contractor Onboarding</a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="row" style="margin-bottom:10px">
                                <div class="col-md-6">
                                    <select class="form-control" wire:model="status_filter">
                                        <option value="">All Statuses</option>
                                        <option value="active">Active</option>
                                        <option value="suspended">Suspended</option>
                                        <option value="terminated">Terminated</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="form-control" wire:model="file_status_filter">
                                        <option value="">All SHEQ File Statuses</option>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="conditional">Conditionally Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Contractor</th>
                                    <th class="th-sm">Type</th>
                                    <th class="th-sm">Induction</th>
                                    <th class="th-sm">Screening</th>
                                    <th class="th-sm">SHEQ File</th>
                                    <th class="th-sm">SHEQ Score</th>
                                    <th class="th-sm">Last Audit</th>
                                    <th class="th-sm">Next Audit</th>
                                    <th class="th-sm">Status</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if (isset($sheq_contractor_onboardings))
                                <tbody>
                                    @forelse ($sheq_contractor_onboardings as $onboarding)
                                  <tr>
                                    <td>{{$onboarding->contractorName()}}</td>
                                    <td>{{ $onboarding->contractorable_type == 'App\Models\Transporter' ? 'Transporter' : 'Vendor / Supplier' }}</td>
                                    <td>
                                        {{$onboarding->induction_date ? \Carbon\Carbon::parse($onboarding->induction_date)->format('d M Y') : '-'}}
                                        @if ($onboarding->isInductionExpired())
                                            <span class="label label-danger">Expired</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($onboarding->screening_status == 'passed')
                                            <span class="label label-success">Passed</span>
                                        @elseif ($onboarding->screening_status == 'failed')
                                            <span class="label label-danger">Failed</span>
                                        @else
                                            <span class="label label-default">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($onboarding->sheq_file_status == 'approved')
                                            <span class="label label-success">Approved</span>
                                        @elseif ($onboarding->sheq_file_status == 'conditional')
                                            <span class="label label-warning">Conditional</span>
                                        @elseif ($onboarding->sheq_file_status == 'rejected')
                                            <span class="label label-danger">Rejected</span>
                                        @else
                                            <span class="label label-default">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{$onboarding->sheq_score ?? '-'}}</td>
                                    <td>{{$onboarding->last_audit_date ? \Carbon\Carbon::parse($onboarding->last_audit_date)->format('d M Y') : '-'}}</td>
                                    <td>{{$onboarding->next_audit_date ? \Carbon\Carbon::parse($onboarding->next_audit_date)->format('d M Y') : '-'}}</td>
                                    <td>
                                        @if ($onboarding->status == 'active')
                                            <span class="label label-success">Active</span>
                                        @elseif ($onboarding->status == 'suspended')
                                            <span class="label label-danger">Suspended</span>
                                        @else
                                            <span class="label label-default">{{ucwords($onboarding->status)}}</span>
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click="edit({{$onboarding->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" wire:click.prevent="delete({{$onboarding->id}})"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="10">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Contractor Onboarding Records Found ....
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
                                    @if (isset($sheq_contractor_onboardings))
                                        {{ $sheq_contractor_onboardings->links() }}
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="sheq_contractor_onboardingDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Onboarding Record?</strong> </center>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_contractor_onboardingModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-plus"></i> Add Contractor Onboarding <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    @include('livewire.sheq-contractor-onboardings.form')
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_contractor_onboardingEditModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-edit"></i> Edit Contractor Onboarding <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <input type="hidden" wire:model="sheq_contractor_onboarding_id">
                <div class="modal-body">
                    @include('livewire.sheq-contractor-onboardings.form')
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
