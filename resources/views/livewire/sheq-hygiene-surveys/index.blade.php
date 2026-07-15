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
                                <a href="" data-toggle="modal" data-target="#sheq_hygiene_surveyModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Hygiene Survey</a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="row" style="margin-bottom:10px">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" placeholder="Search..." wire:model.debounce.500ms="search">
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" wire:model="stressor_filter">
                                        <option value="">All Stressors</option>
                                        <option value="noise">Noise</option>
                                        <option value="dust">Dust</option>
                                        <option value="illumination">Illumination</option>
                                        <option value="heat">Heat</option>
                                        <option value="vibration">Vibration</option>
                                        <option value="ergonomics">Ergonomics</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" wire:model="department_filter">
                                        <option value="">All Departments</option>
                                        @foreach ($departments as $department)
                                            <option value="{{$department->id}}">{{$department->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Number</th>
                                    <th class="th-sm">Stressor</th>
                                    <th class="th-sm">Department</th>
                                    <th class="th-sm">Area</th>
                                    <th class="th-sm">Date</th>
                                    <th class="th-sm">Result</th>
                                    <th class="th-sm">Limit</th>
                                    <th class="th-sm">Within Limit?</th>
                                    <th class="th-sm">Next Survey</th>
                                    <th class="th-sm">Status</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if (isset($sheq_hygiene_surveys))
                                <tbody>
                                    @forelse ($sheq_hygiene_surveys as $survey)
                                  <tr>
                                    <td>{{$survey->survey_number}}</td>
                                    <td>{{ucwords($survey->stressor)}}</td>
                                    <td>{{$survey->department->name ?? '-'}}</td>
                                    <td>{{$survey->area}}</td>
                                    <td>{{$survey->survey_date ? \Carbon\Carbon::parse($survey->survey_date)->format('d M Y') : '-'}}</td>
                                    <td>{{$survey->result}}</td>
                                    <td>{{$survey->limit_standard}}</td>
                                    <td>
                                        @if ($survey->exceeds_limit)
                                            <span class="label label-danger">Exceeds Limit</span>
                                        @else
                                            <span class="label label-success">Within Limit</span>
                                        @endif
                                    </td>
                                    <td>{{$survey->next_survey_date ? \Carbon\Carbon::parse($survey->next_survey_date)->format('d M Y') : '-'}}</td>
                                    <td>
                                        @if ($survey->status == 'closed')
                                            <span class="label label-success">Closed</span>
                                        @else
                                            <span class="label label-default">Open</span>
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click="edit({{$survey->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" wire:click.prevent="delete({{$survey->id}})"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="11">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Hygiene Surveys Found ....
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
                                    @if (isset($sheq_hygiene_surveys))
                                        {{ $sheq_hygiene_surveys->links() }}
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="sheq_hygiene_surveyDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Hygiene Survey?</strong> </center>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_hygiene_surveyModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-plus"></i> Add Hygiene Survey <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    @include('livewire.sheq-hygiene-surveys.form')
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_hygiene_surveyEditModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-edit"></i> Edit Hygiene Survey <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <input type="hidden" wire:model="sheq_hygiene_survey_id">
                <div class="modal-body">
                    @include('livewire.sheq-hygiene-surveys.form')
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
