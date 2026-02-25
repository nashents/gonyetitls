<div>
    <style>
        .modal-lg {
        max-width: 80%;
    }
    </style>
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
                                
                            </div>
                            <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                                <div class="panel-title">
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    Filter By
                                                </span>
                                                <select wire:model.debounce.300ms="application_filter" class="form-control" aria-label="..." >
                                                    <option value="created_at">Created At</option>
                                                    <option value="date">Date</option>
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-lg-3" >
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    From
                                                </span>
                                                <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-lg-3" >
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    To
                                                </span>
                                                <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <!-- /input-group --> 
                                    </div>
                                </div>

                                <div class="panel-title">
                                    <a href="#" data-toggle="modal" data-target="#applicationModal"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Application</a>
                                    <a href="#" wire:click="exportApplicationsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                    <a href="#" wire:click="exportApplicationsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                    <a href="#" wire:click="exportApplicationsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                                </div>
                                <div class="col-md-5" style="float: right; padding-right:2px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search applications...">
                                    </div>
                                </div>
                                <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                        <th class="th-sm" style="width: 15%">Application#
                                        </th>
                                        <th class="th-sm" style="width: 20%">Job Title
                                        </th>
                                        <th class="th-sm">Candidate
                                        </th>
                                        <th class="th-sm">Checks
                                        </th>
                                        <th class="th-sm">Scores
                                        </th>
                                        <th class="th-sm">Decision
                                        </th>
                                        <th class="th-sm">Status
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>
                                      </tr>
                                    </thead>
                                    @if (isset($applications))
                                    <tbody>
                                        @forelse  ($applications as $application)
                                            @if ($application->recruitment_candidate)
                                                @php
                                                    $candidate = $application->recruitment_candidate;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        {{$application->application_number}} <br>
                                                        <small>
                                                            <strong>AppliedOn: </strong>{{$application->date}} <br>
                                                            <strong>CreatedBy: </strong>{{ucfirst($application->user ? $application->user->name : "")}} {{ucfirst($application->user ? $application->user->surname : "")}} <br>
                                                            <strong>CreatedOn: </strong>{{Carbon\Carbon::parse($application->created_at)->format('Y-m-d')}}
                                                        </small>
                                                    </td>
                                                    <td>
                                                        {{$application?->job_posting?->job_title ? $application?->job_posting?->job_title->title : ""}}
                                                        <br>
                                                        <small>
                                                            <strong>JD: </strong>{{Str::limit($application?->job_posting?->description,100,'...')}}
                                                        </small>
                                                    </td>
                                                    <td>
                                                        {{$candidate->first_name}} {{$candidate->last_name}} <br>
                                                        <small>
                                                            <strong>Phonenumber:</strong> {{$candidate->phone}} <br>
                                                            <strong>Email:</strong> {{$candidate->email}} <br>
                                                            <strong>DOB:</strong> {{$candidate->dob}} <br>
                                                            <strong>ID#:</strong> {{$candidate->national_id}} <br>
                                                            <strong>License#:</strong> {{$candidate->drivers_license_number}} <br>
                                                            <strong>Experience:</strong> {{$candidate->years_experience}} <br>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-success btn-rounded btn-xs" wire:click.prevent="showChecks({{$candidate->id}})"> <i class="fa fa-plus"></i> Checks</button> <br>
                                                        @php
                                                            $i = 1;
                                                        @endphp
                                                        <small>
                                                            @foreach ($candidate->checks as $check)
                                                                {{$i++}}) {{$check->type}} 
                                                                @if ($check->result)
                                                                    <span class="badge bg-warning">{{$check->result}} </span>
                                                                @endif
                                                                @if (!$loop->last), <br> @endif
                                                                @if ($loop->last)
                                                                    <a href="#"  wire:click.prevent="showEditChecks({{$candidate->id}})"> <i class="fa fa-edit"></i></a>
                                                                @endif
                                                            @endforeach
                                                        </small>
                                                        
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-success btn-rounded btn-xs" wire:click.prevent="showScores({{$candidate->id}})"> <i class="fa fa-plus"></i> Stages</button> <br>
                                                        @php
                                                            $i = 1;
                                                        @endphp
                                                        <small>
                                                            @foreach ($candidate->scores as $score)
                                                                {{$i++}}) {{$score->stage}} {{$score->criterion}} {{$score->weight}}
                                                                @if ($score->score_percent)
                                                                    <span class="badge bg-info">{{$score->score_percent}} % </span>
                                                                @endif
                                                                @if (!$loop->last), <br> @endif
                                                                @if ($loop->last)
                                                                    <a href="#"  wire:click.prevent="showEditScores({{$candidate->id}})"> <i class="fa fa-edit"></i></a>
                                                                @endif
                                                            @endforeach
                                                        </small>
                                                        
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-success btn-rounded btn-xs" wire:click.prevent="showDecisions({{$candidate->id}})"> <i class="fa fa-plus"></i> Decision</button> <br>
                                                        @php
                                                            $i = 1;
                                                        @endphp
                                                        <small>
                                                            @foreach ($candidate->decisions as $decision)
                                                                {{$i++}}) {{$decision->stage}} 
                                                                @if ($decision->decision)
                                                                    <span class="badge bg-warning">{{$decision->decision}} </span>
                                                                @endif
                                                                @if (!$loop->last), <br> @endif
                                                                @if ($loop->last)
                                                                    <a href="#"  wire:click.prevent="showEditDecisions({{$candidate->id}})"> <i class="fa fa-edit"></i></a>
                                                                @endif
                                                            @endforeach
                                                        </small>
                                                        
                                                    </td>
                                                    <td>{{$candidate->status}}</td>
                                                    <td class="w-10 line-height-35 table-dropdown">
                                                        <div class="dropdown">
                                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="fa fa-bars"></i>
                                                                <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="{{route('applications.show', $application->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                                @if ($application->user_id == Auth::user()->id && $application->authorization == "pending")
                                                                    <li><a href="#" wire:click.prevent="edit({{$application->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="11">
                                                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                        No Applications Found ....
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
                                        @if (isset($applications))
                                            @if ($applications->count()>0)
                                                {{ $applications->links() }} 
                                            @endif
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="decisionModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Recruitment Decisions <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="addDecisions()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Recruitement Stages<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="stage_name.0" class="form-control" required>
                                        <option value="">Select Option</option>
                                        @foreach ($stages as $stage)
                                        <option value="{{$stage->name}}">{{$stage->name}}</option>
                                        @endforeach
                                    </select>
                                     <small>  <a href="{{ route('recruitment-stages.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Stage</a></small> <a href="#" wire:click.prevent="refresh('stages')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('stage_name.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Decision</label>
                                    <select wire:model.debounce.300ms="decision.0" class="form-control" required>
                                        <option value="">Select Option</option>
                                        <option value="Contracted">Contracted</option>
                                        <option value="Decline">Decline</option>
                                        <option value="Engage">Engage</option>
                                         <option value="Hired">Hired</option>
                                        <option value="Impressed">Impressed</option>
                                        <option value="Unimpressed">Unimpressed</option>
                                        <option value="Road Test">Road Test</option>
                                    </select>
                                    @error('decision.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description">Comments</label>
                            <textarea class="form-control" wire:model.debounce.300ms="comments.0" cols="30" rows="2"></textarea>
                            @error('comments.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        
                        @foreach($inputs as $key => $value)
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country">Recruitement Stages<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="stage_name.{{$value}}" class="form-control" required>
                                            <option value="">Select Option</option>
                                            @foreach ($stages as $stage)
                                            <option value="{{$stage->name}}">{{$stage->name}}</option>
                                            @endforeach
                                        </select>
                                        <small>  <a href="{{ route('recruitment-stages.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Stage</a></small> <a href="#" wire:click.prevent="refresh('stages')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        @error('stage_name.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" >
                                        <label for="one" class="radio-label">Decision</label>
                                        <select wire:model.debounce.300ms="decision.{{$value}}" class="form-control" required>
                                            <option value="">Select Option</option>
                                            <option value="Contracted">Contracted</option>
                                            <option value="Decline">Decline</option>
                                            <option value="Engage">Engage</option>
                                            <option value="Hired">Hired</option>
                                            <option value="Impressed">Impressed</option>
                                            <option value="Unimpressed">Unimpressed</option>
                                            <option value="Road Test">Road Test</option>
                                        </select>
                                        @error('decision.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>   
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description">Comments</label>
                                <textarea class="form-control" wire:model.debounce.300ms="comments.{{$value}}" cols="30" rows="2"></textarea>
                                @error('comments.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        @endforeach
                        <div class="row mt-10">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Decision</button>
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
    
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="decisionEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Recruitment Decision <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateDecisions()" >
                    <div class="modal-body">
                        @foreach ($existing_decisions as $key => $value)
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country">Recruitement Stages<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="current_stage_name.{{$key}}" class="form-control" required>
                                            <option value="">Select Option</option>
                                            @foreach ($stages as $stage)
                                            <option value="{{$stage->name}}">{{$stage->name}}</option>
                                            @endforeach
                                        </select>
                                        <small>  <a href="{{ route('recruitment-stages.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Stage</a></small> <a href="#" wire:click.prevent="refresh('stages')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        @error('current_stage_name.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" >
                                        <label for="one" class="radio-label">Decision</label>
                                        <select wire:model.debounce.300ms="current_decision.{{$key}}" class="form-control" required>
                                            <option value="">Select Option</option>
                                            <option value="Contracted">Contracted</option>
                                            <option value="Decline">Decline</option>
                                            <option value="Engage">Engage</option>
                                            <option value="Hired">Hired</option>
                                            <option value="Impressed">Impressed</option>
                                            <option value="Unimpressed">Unimpressed</option>
                                            <option value="Road Test">Road Test</option>
                                        </select>
                                        @error('current_decision.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>   
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description">Comments</label>
                                <textarea class="form-control" wire:model.debounce.300ms="current_comments.{{$key}}" cols="30" rows="2"></textarea>
                                @error('current_comments.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        @endforeach
                        @foreach($inputs as $key => $value)
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country">Recruitement Stages<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="stage_name.{{$value}}" class="form-control" required>
                                            <option value="">Select Option</option>
                                            @foreach ($stages as $stage)
                                            <option value="{{$stage->name}}">{{$stage->name}}</option>
                                            @endforeach
                                        </select>
                                        <small>  <a href="{{ route('recruitment-stages.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Stage</a></small> <a href="#" wire:click.prevent="refresh('stages')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        @error('stage_name.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" >
                                        <label for="one" class="radio-label">Decision</label>
                                        <select wire:model.debounce.300ms="decision.{{$value}}" class="form-control" required>
                                            <option value="">Select Option</option>
                                            <option value="Contracted">Contracted</option>
                                            <option value="Decline">Decline</option>
                                            <option value="Engage">Engage</option>
                                            <option value="Hired">Hired</option>
                                            <option value="Impressed">Impressed</option>
                                            <option value="Unimpressed">Unimpressed</option>
                                            <option value="Road Test">Road Test</option>
                                        </select>
                                        @error('decision.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>   
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description">Comments</label>
                                <textarea class="form-control" wire:model.debounce.300ms="comments.{{$value}}" cols="30" rows="2"></textarea>
                                @error('comments.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        @endforeach
                        <div class="row mt-10">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Decision</button>
                                </div>
                            </div>
                        </div>
                    </div>    
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="stageModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Recruitment Score <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="addScores()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="country">Recruitement Stages<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="stage_name.0" class="form-control" required>
                                        <option value="">Select Option</option>
                                        @foreach ($stages as $stage)
                                        <option value="{{$stage->name}}">{{$stage->name}}</option>
                                        @endforeach
                                    </select>
                                     <small>  <a href="{{ route('recruitment-stages.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Stage</a></small> <a href="#" wire:click.prevent="refresh('stages')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('stage_name.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Criterion</label>
                                    <select wire:model.debounce.300ms="criterion.0" class="form-control" required>
                                        <option value="">Select Option</option>
                                        @foreach ($criterions as $criterion)
                                            <option value="{{$criterion->name}}">{{$criterion->name}}</option>
                                        @endforeach
                                    </select>
                                    <small>  <a href="{{ route('recruitment-criterions.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Stage</a></small> <a href="#" wire:click.prevent="refresh('stages')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('criterion.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                            <div class="col-md-3">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Criterion Weight</label>
                                       <input type="number" step="any" class="form-control" wire:model.debounce.300ms="weight.0">
                                        @error('weight.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Score</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="score.0" placeholder="Score Percentage">
                                        @error('score.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Comments</label>
                                        <textarea class="form-control" wire:model.debounce.300ms="comments.0" cols="30" rows="2"></textarea>
                                        @error('comments.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        @foreach($inputs as $key => $value)
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="country">Recruitement Stages<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="stage_name.{{$value}}" class="form-control" required>
                                            <option value="">Select Option</option>
                                            @foreach ($stages as $stage)
                                            <option value="{{$stage->name}}">{{$stage->name}}</option>
                                            @endforeach
                                        </select>
                                        <small>  <a href="{{ route('recruitment-stages.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Stage</a></small> <a href="#" wire:click.prevent="refresh('stages')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        @error('stage_name.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" >
                                        <label for="one" class="radio-label">Criterion</label>
                                        <select wire:model.debounce.300ms="criterion.{{$value}}" class="form-control" required>
                                            <option value="">Select Option</option>
                                            @foreach ($criterions as $criterion)
                                                <option value="{{$criterion->name}}">{{$criterion->name}}</option>
                                            @endforeach
                                        </select>
                                        <small>  <a href="{{ route('recruitment-criterions.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Stage</a></small> <a href="#" wire:click.prevent="refresh('stages')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        @error('criterion.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>   
                                </div>
                                <div class="col-md-3">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Criterion Weight</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="weight.{{$value}}">
                                            @error('weight.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Score</label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="score.{{$value}}" placeholder="Score Percentage">
                                            @error('score.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Comments</label>
                                            <textarea class="form-control" wire:model.debounce.300ms="comments.{{$value}}" cols="30" rows="2"></textarea>
                                            @error('comments.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group" style="padding-top:28px; ">
                                        <label for=""></label>
                                        <button class="btn btn-danger btn-rounded btn-xs" wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="row mt-10">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Check</button>
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
    
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="stageEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Recruitment Scores <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateScores()" >
                    <div class="modal-body">
                        @foreach ($existing_scores as $key => $value)
                           <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="country">Recruitement Stages<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="current_stage_name.{{$key}}" class="form-control" required>
                                        <option value="">Select Option</option>
                                        @foreach ($stages as $stage)
                                        <option value="{{$stage->name}}">{{$stage->name}}</option>
                                        @endforeach
                                    </select>
                                     <small>  <a href="{{ route('recruitment-stages.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Stage</a></small> <a href="#" wire:click.prevent="refresh('stages')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('current_stage_name.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Criterion</label>
                                    <select wire:model.debounce.300ms="current_criterion.{{$key}}" class="form-control" required>
                                        <option value="">Select Option</option>
                                        @foreach ($criterions as $criterion)
                                            <option value="{{$criterion->name}}">{{$criterion->name}}</option>
                                        @endforeach
                                    </select>
                                    <small>  <a href="{{ route('recruitment-criterions.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Stage</a></small> <a href="#" wire:click.prevent="refresh('stages')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('current_criterion.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                            <div class="col-md-3">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Criterion Weight</label>
                                       <input type="number" step="any" class="form-control" wire:model.debounce.300ms="current_weight.{{$key}}">
                                        @error('current_weight.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Score</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="current_score.{{$key}}" placeholder="Score Percentage">
                                        @error('current_score.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Comments</label>
                                        <textarea class="form-control" wire:model.debounce.300ms="current_comments.{{$key}}" cols="30" rows="2"></textarea>
                                        @error('current_comments.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @foreach($inputs as $key => $value)
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="country">Recruitement Stages<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="stage_name.{{$value}}" class="form-control" required>
                                            <option value="">Select Option</option>
                                            @foreach ($stages as $stage)
                                            <option value="{{$stage->name}}">{{$stage->name}}</option>
                                            @endforeach
                                        </select>
                                        <small>  <a href="{{ route('recruitment-stages.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Stage</a></small> <a href="#" wire:click.prevent="refresh('stages')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        @error('stage_name.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" >
                                        <label for="one" class="radio-label">Criterion</label>
                                        <select wire:model.debounce.300ms="criterion.{{$value}}" class="form-control" required>
                                            <option value="">Select Option</option>
                                            @foreach ($criterions as $criterion)
                                                <option value="{{$criterion->name}}">{{$criterion->name}}</option>
                                            @endforeach
                                        </select>
                                        <small>  <a href="{{ route('recruitment-criterions.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Stage</a></small> <a href="#" wire:click.prevent="refresh('stages')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        @error('criterion.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>   
                                </div>
                                <div class="col-md-3">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Criterion Weight</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="weight.{{$value}}">
                                            @error('weight.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Score</label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="score.{{$value}}" placeholder="Score Percentage">
                                            @error('score.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Comments</label>
                                            <textarea class="form-control" wire:model.debounce.300ms="comments.{{$value}}" cols="30" rows="2"></textarea>
                                            @error('comments.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group" style="padding-top:28px; ">
                                        <label for=""></label>
                                        <button class="btn btn-danger btn-rounded btn-xs" wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="row mt-10">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Check</button>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="checkModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Recruitment Check <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="addChecks()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Recruitement Checks<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="check_name.0" class="form-control" required>
                                        <option value="">Select Option</option>
                                        @foreach ($checks as $check)
                                        <option value="{{$check->name}}">{{$check->name}}</option>
                                        @endforeach
                                    </select>
                                     <small>  <a href="{{ route('recruitment-checks.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Check</a></small> <a href="#" wire:click.prevent="refresh('checks')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('check_name.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Result</label>
                                    <select wire:model.debounce.300ms="result.0" class="form-control" required>
                                        <option value="">Select Option</option>
                                        <option value="Available">Available</option>
                                        <option value="Fail">Fail</option>
                                        <option value="None">None</option>
                                        <option value="Pass">Pass</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Present">Present</option>
                                    </select>
                                    @error('result.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Comments</label>
                                        <textarea  class="form-control" wire:model.debounce.300ms="comments.0" cols="30" rows="2"></textarea>
                                        @error('comments.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Attach Document</label>
                                          <input type="file" class="form-control" wire:model.debounce.300ms="check_attachment.0" placeholder="Select Attachement" >
                                        @error('check_attachment.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        @foreach($inputs as $key => $value)
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country">Recruitement Checks<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="check_name.{{$value}}" class="form-control" required>
                                            <option value="">Select Option</option>
                                            @foreach ($checks as $check)
                                            <option value="{{$check->name}}">{{$check->name}}</option>
                                            @endforeach
                                        </select>
                                        <small>  <a href="{{ route('recruitment-checks.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Check</a></small> <a href="#" wire:click.prevent="refresh('checks')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        @error('check_name.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" >
                                        <label for="one" class="radio-label">Result</label>
                                        <select wire:model.debounce.300ms="result.{{$value}}" class="form-control" required>
                                            <option value="">Select Option</option>
                                            <option value="Available">Available</option>
                                            <option value="Fail">Fail</option>
                                            <option value="None">None</option>
                                            <option value="Pass">Pass</option>
                                            <option value="Pending">Pending</option>
                                            <option value="Present">Present</option>
                                        </select>
                                        @error('result.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>   
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Comments</label>
                                            <textarea  class="form-control" wire:model.debounce.300ms="comments.{{$value}}" cols="30" rows="2"></textarea>
                                            @error('comments.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Attach Document</label>
                                            <input type="file" class="form-control" wire:model.debounce.300ms="check_attachment.{{$value}}" placeholder="Select Attachement" >
                                            @error('check_attachment.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-1">
                                    <div class="form-group" style="padding-top:28px; ">
                                        <label for=""></label>
                                        <button class="btn btn-danger btn-rounded btn-xs" wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="row mt-10">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Check</button>
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
    
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="checkEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Edit Recruitment Checks <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateChecks()" >
                    <div class="modal-body">
                        @foreach ($existing_checks as $key => $value)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Recruitement Checks<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="current_check_name.{{$key}}" class="form-control" required>
                                        <option value="">Select Option</option>
                                        @foreach ($checks as $check)
                                        <option value="{{$check->name}}">{{$check->name}}</option>
                                        @endforeach
                                    </select>
                                     <small>  <a href="{{ route('recruitment-checks.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Check</a></small> <a href="#" wire:click.prevent="refresh('checks')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('current_check_name.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Result</label>
                                    <select wire:model.debounce.300ms="current_result.{{$key}}" class="form-control" required>
                                        <option value="">Select Option</option>
                                        <option value="Available">Available</option>
                                        <option value="Fail">Fail</option>
                                        <option value="None">None</option>
                                        <option value="Pass">Pass</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Present">Present</option>
                                    </select>
                                    @error('current_result.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Comments</label>
                                        <textarea  class="form-control" wire:model.debounce.300ms="current_comments.{{$key}}" cols="30" rows="2"></textarea>
                                        @error('current_comments.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Attach Document</label>
                                        @if (isset($old_attachment[$key]))
                                            <small>Attachment: <a href="">{{isset($old_attachment[$key]) ? $old_attachment[$key] : ""}}</a></small>
                                        @endif
                                        <input type="file" class="form-control" wire:model.debounce.300ms="current_check_attachment.{{$key}}" placeholder="Select Attachement" >
                                        @error('current_check_attachment.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        

                        @foreach($inputs as $key => $value)
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country">Recruitement Checks<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="check_name.{{$value}}" class="form-control" required>
                                            <option value="">Select Option</option>
                                            @foreach ($checks as $check)
                                            <option value="{{$check->name}}">{{$check->name}}</option>
                                            @endforeach
                                        </select>
                                        <small>  <a href="{{ route('recruitment-checks.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Check</a></small> <a href="#" wire:click.prevent="refresh('checks')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        @error('check_name.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" >
                                        <label for="one" class="radio-label">Result</label>
                                        <select wire:model.debounce.300ms="result.{{$value}}" class="form-control" required>
                                            <option value="">Select Option</option>
                                            <option value="Available">Available</option>
                                            <option value="Fail">Fail</option>
                                            <option value="None">None</option>
                                            <option value="Pass">Pass</option>
                                            <option value="Pending">Pending</option>
                                            <option value="Present">Present</option>
                                        </select>
                                        @error('result.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>   
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Comments</label>
                                            <textarea  class="form-control" wire:model.debounce.300ms="comments.{{$value}}" cols="30" rows="2"></textarea>
                                            @error('comments.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Attach Document</label>
                                            <input type="file" class="form-control" wire:model.debounce.300ms="check_attachment.{{$value}}" placeholder="Select Attachement" >
                                            @error('check_attachment.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-1">
                                    <div class="form-group" style="padding-top:28px; ">
                                        <label for=""></label>
                                        <button class="btn btn-danger btn-rounded btn-xs" wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="row mt-10">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Check</button>
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

 <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="applicationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Application <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Application Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Job Postings<span class="required" style="color: red">*</span></label>
                                   <select class="form-control" wire:model.debounce.300ms="job_posting_id" required>
                                        <option value="">Select Job Posting</option>
                                        @foreach ($job_postings as $job_posting)
                                            <option value="{{$job_posting->id}}">{{$job_posting->job_title ? $job_posting->job_title->title : ""}}</option>
                                        @endforeach
                                   </select>
                                    @error('job_posting_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>   
                        <h5 class="underline mt-10">Applicant Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Applicant Name">
                                    @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Surname</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="surname" placeholder="Enter Applicant Surname">
                                    @error('surname') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                
                        </div> 
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Email</label>
                                    <input type="email" class="form-control" wire:model.debounce.300ms="email" placeholder="Enter Email">
                                    @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Phonenumber</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Phonenumber">
                                    @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                
                        </div> 
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gender">Gender<span class="required" style="color: red">*</span></label>
                                        <div class="col-sm-10">
                                        <div class="radio">
                                            <label>
                                            <input type="radio" wire:model.debounce.300ms="gender" id="optionsRadios1" value="Male" required >
                                            Male
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                            <input type="radio"  wire:model.debounce.300ms="gender" id="optionsRadios2" value="Female" required>
                                            Female
                                            </label>
                                        </div>
                                    </div>
                                    @error('gender') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">DOB</label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="dob" placeholder="Enter DOB">
                                    @error('dob') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                
                        </div> 
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">ID#</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="idnumber" placeholder="Enter ID#">
                                    @error('idnumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">License#</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="license_number" placeholder="Enter License#">
                                    @error('license_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div> 
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Year of experience</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="years_experience" placeholder="Enter years of experience">
                                    @error('years_experience') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                               <div class="form-group">
                                    <label for="name">Source</label>
                                    <select class="form-control" wire:model.debounce.300ms="source">
                                        <option value="">Select Option</option>
                                        <option value="Walk In">Walk In</option>
                                        <option value="Referral">Referral</option>
                                    </select>
                                    @error('source') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div> 
                          <h5 class="underline mt-10">Recruitment Details</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Application Status</label>
                                    <select class="form-control" wire:model.debounce.300ms="status">
                                        <option value="">Select Option</option>
                                        <option value="Applied">Applied</option>
                                        <option value="Screened">Screened</option>
                                        <option value="Road Test">Road Test</option>
                                        <option value="Engaged">Engaged</option>
                                        <option value="Declined">Declined</option>
                                        <option value="Contracted">Contracted</option>
                                        <option value="Hired">Hired</option>
                                    </select>
                                    @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Screening Impression</label>
                                   <select class="form-control" wire:model.debounce.300ms="screening_impression">
                                        <option value="">Select Option</option>
                                        <option value="Impressed">Impressed</option>
                                        <option value="Unimpressed">Unimpressed</option>
                                   </select>
                                    @error('screening_impression') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Next Step</label>
                                   <select class="form-control" wire:model.debounce.300ms="next_step">
                                        <option value="">Select Option</option>
                                        <option value="Road Test">Road Test</option>
                                        <option value="Decline">Decline</option>
                                        <option value="Engage">Engage</option>
                                   </select>
                                    @error('next_step') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                
                        </div> 
                        <div class="form-group">
                            <label for="name">Notes</label>
                            <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="2"></textarea>
                            @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
 


    </div>
