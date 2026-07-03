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
                                <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th style="width:12%">Application</th>
            <th style="width:12%">Job Title</th>
            <th style="width:14%">Candidate</th>
            <th>Qualifications</th>
            <th>Checks</th>
            <th>Interviews</th>
            <th>Management Decision</th>
            <th style="width:8%">Status</th>
            <th style="width:6%">Actions</th>
        </tr>
    </thead>

    @if (isset($applications))
    <tbody>
        @forelse ($applications as $application)
            @if ($application->recruitment_candidate)
                @php
                    $candidate = $application->recruitment_candidate;
                    // Lock flow: qualifications -> checks -> scores -> decisions
                    $hasRejectedQualification = $candidate->qualifications->contains(fn ($q) => strtolower((string) $q->status) === 'rejected');
                    $hasQual  = $candidate->qualifications->isNotEmpty() && !$hasRejectedQualification;

                    $checksExist = $candidate->checks->isNotEmpty();
                    $allowedCheckResults = ['present', 'pass', 'available'];
                    $hasBlockingCheck = $candidate->checks->contains(fn ($c) => !in_array(strtolower((string) $c->result), $allowedCheckResults));
                    $hasCheck = $checksExist && !$hasBlockingCheck;

                    $hasScore = $candidate->scores->isNotEmpty();
                @endphp
                <tr>

                    {{-- Application --}}
                    <td>
                        <strong>{{ $application->application_number }}</strong><br>
                        <small class="text-muted">
                            <i class="fa fa-calendar"></i> {{ $application->date }}<br>
                            <i class="fa fa-user"></i> {{ ucfirst($application->user?->name) }} {{ ucfirst($application->user?->surname) }}<br>
                            <i class="fa fa-clock-o"></i> {{ Carbon\Carbon::parse($application->created_at)->format('d M Y') }}
                        </small>
                    </td>

                    {{-- Job Title --}}
                    <td>
                        <strong>{{ $application?->job_posting?->job_title?->title ?? 'N/A' }}</strong>
                        @if ($application?->job_posting?->description)
                            <br><small class="text-muted">{{ Str::limit($application->job_posting->description, 60, '...') }}</small>
                        @endif
                    </td>

                    {{-- Candidate --}}
                    <td>
                        <strong>{{ $candidate->first_name }} {{ $candidate->last_name }}</strong><br>
                        <small class="text-muted">
                            <i class="fa fa-phone"></i> {{ $candidate->phone }}<br>
                            <i class="fa fa-envelope"></i> {{ $candidate->email }}<br>
                            <i class="fa fa-birthday-cake"></i> {{ $candidate->dob?->format('d M Y') }}<br>
                            <i class="fa fa-id-card"></i> {{ $candidate->national_id }}<br>
                            <i class="fa fa-car"></i> {{ $candidate->drivers_license_number ?? 'N/A' }}<br>
                            <i class="fa fa-briefcase"></i> {{ $candidate->years_experience }} yr(s)
                        </small>
                    </td>

                    {{-- Qualifications (step 1, always open) --}}
                    <td>
                        <button class="btn btn-success btn-rounded btn-xs mb-1" wire:click.prevent="showQualifications({{ $candidate->id }})">
                            <i class="fa fa-plus"></i> Add
                        </button><br>
                        <small>
                            @forelse ($candidate->qualifications as $qualification)
                                {{ $loop->iteration }}) {{ $qualification->qualification?->name }} {{ $qualification->level }}
                                @if ($qualification->status)
                                    <span class="badge bg-{{ match(strtolower($qualification->status)) {
                                        'pending'  => 'warning',
                                        'verified' => 'success',
                                        'rejected' => 'danger',
                                        default    => 'secondary'
                                    } }}">{{ $qualification->status }}</span>
                                @endif
                                @if ($loop->last)
                                    &nbsp;<a href="#" wire:click.prevent="showEditQualifications({{ $candidate->id }})"><i class="fa fa-edit"></i></a>
                                @else
                                    <br>
                                @endif
                            @empty
                                <span class="text-muted">None added</span>
                            @endforelse
                        </small>
                    </td>

                    {{-- Checks (step 2, needs qualifications) --}}
                    <td>
                        @if ($hasQual)
                            <button class="btn btn-success btn-rounded btn-xs mb-1" wire:click.prevent="showChecks({{ $candidate->id }})">
                                <i class="fa fa-plus"></i> Add
                            </button>
                        @else
                            <button class="btn btn-default btn-rounded btn-xs mb-1" disabled title="{{ $hasRejectedQualification ? 'Update the rejected qualification to Verified first' : 'Add a qualification first' }}">
                                <i class="fa fa-lock"></i> Locked
                            </button>
                        @endif
                        <br>
                        <small>
                            @forelse ($candidate->checks as $check)
                                {{ $loop->iteration }}) {{ $check->type }}
                                @if ($check->result)
                                    <span class="badge bg-{{ match(strtolower($check->result)) {
                                        'pass', 'present', 'available' => 'success',
                                        'fail'    => 'danger',
                                        'pending' => 'warning',
                                        default   => 'secondary'
                                    } }}">{{ $check->result }}</span>
                                @endif
                                @if ($loop->last)
                                    &nbsp;<a href="#" wire:click.prevent="showEditChecks({{ $candidate->id }})"><i class="fa fa-edit"></i></a>
                                @else
                                    <br>
                                @endif
                            @empty
                                <span class="text-muted">None added</span>
                            @endforelse
                        </small>
                    </td>

                    {{-- Scores (step 3, needs checks) --}}
                    <td>
                        @if ($hasCheck)
                            <button class="btn btn-success btn-rounded btn-xs mb-1" wire:click.prevent="showScores({{ $candidate->id }})">
                                <i class="fa fa-plus"></i> Add
                            </button>
                        @else
                            <button class="btn btn-default btn-rounded btn-xs mb-1" disabled title="{{ $checksExist ? 'All checks must be Present, Pass or Available first' : 'Complete a check first' }}">
                                <i class="fa fa-lock"></i> Locked
                            </button>
                        @endif
                        <br>
                        <small>
                            @forelse ($candidate->scores as $score)
                                {{ $loop->iteration }}) {{ $score->stage }}  @if($score->criterion)
                                                                                    &mdash; {{ $score->criterion }}
                                                                                @endif
                                @if ($score->score_percent)
                                    <span class="badge bg-info">{{ $score->score_percent }}%</span>
                                @endif
                                @if ($loop->last)
                                    &nbsp;<a href="#" wire:click.prevent="showEditScores({{ $candidate->id }})"><i class="fa fa-edit"></i></a>
                                @else
                                    <br>
                                @endif
                            @empty
                                <span class="text-muted">None added</span>
                            @endforelse
                        </small>
                    </td>

                    {{-- Management Decision (step 4, needs scores) --}}
                    <td>
                        @if ($hasScore)
                            <button class="btn btn-success btn-rounded btn-xs mb-1" wire:click.prevent="showDecisions({{ $candidate->id }})">
                                <i class="fa fa-plus"></i> Add
                            </button>
                        @else
                            <button class="btn btn-default btn-rounded btn-xs mb-1" disabled title="Score the candidate first">
                                <i class="fa fa-lock"></i> Locked
                            </button>
                        @endif
                        <br>
                        <small>
                            @forelse ($candidate->decisions as $decision)
                                {{ $loop->iteration }}) {{ $decision->stage }}
                                @if ($decision->decision)
                                    <span class="badge bg-{{ match(strtolower($decision->decision)) {
                                        'engage' => 'success',
                                        'fail'   => 'danger',
                                        default  => 'secondary'
                                    } }}">{{ $decision->decision }}</span>
                                @endif
                                @if ($loop->last)
                                    &nbsp;<a href="#" wire:click.prevent="showEditDecisions({{ $candidate->id }})"><i class="fa fa-edit"></i></a>
                                @else
                                    <br>
                                @endif
                            @empty
                                <span class="text-muted">None added</span>
                            @endforelse
                        </small>
                    </td>

                    {{-- Status --}}
                    <td class="text-center">
                        <span class="badge bg-{{ match($candidate->status) {
                            'Applied'    => 'primary',
                            'Engaged'    => 'info',
                            'Declined'   => 'danger',
                            'Contracted' => 'warning',
                            'Hired'      => 'success',
                            default      => 'dark'
                        } }}">
                            {{ $candidate->status }}
                        </span>
                        @if ($candidate->employee_id)
                            <br><small class="text-success" title="Staff record auto-created"><i class="fa fa-check-circle"></i> Onboarded</small>
                        @endif
                        <br>
                        <a href="#" wire:click.prevent="showUpdateStatus({{ $candidate->id }})" title="Quick update status"><i class="fa fa-refresh"></i> Update</a>
                    </td>

                    {{-- Actions --}}
                    <td class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
                                <i class="fa fa-bars"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a href="{{ route('applications.show', $application->id) }}"><i class="fa fa-eye color-default"></i> View</a></li>
                                <li><a href="#" wire:click="edit({{ $application->id }})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#" wire:click="delete({{ $application->id }})"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                            </ul>
                        </div>
                    </td>

                </tr>
            @endif
        @empty
            <tr>
                <td colspan="9" class="text-center text-muted" style="padding: 30px 0;">
                    <i class="fa fa-folder-open-o" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                    No applications found.
                </td>
            </tr>
        @endforelse
    </tbody>
    @else
        <tbody>
            <tr>
                <td colspan="9" class="text-center" style="padding:40px 0;">
                    <img src="{{ asset('images/nodata.png') }}" alt="No data" style="max-width:200px;">
                </td>
            </tr>
        </tbody>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="qualificationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Applicant Qualifications <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="addQualifications()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Qualifications<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="qualification_id.0" class="form-control" required>
                                        <option value="">Select Option</option>
                                        @foreach ($qualifications as $qualification)
                                            <option value="{{$qualification->id}}">{{$qualification->name}}</option>
                                        @endforeach
                                    </select>
                                     <small>  <a href="{{ route('qualifications.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Qualification</a></small> <a href="#" wire:click.prevent="refresh('qualifications')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('qualification_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Level</label>
                                    <input type="text" wire:model.debounce.300ms="level.0" class="form-control">
                                    @error('level.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Date Awarded</label>
                                    <input type="date" wire:model.debounce.300ms="date_awarded.0" class="form-control">
                                    @error('date_awarded.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Expires At</label>
                                    <input type="date" wire:model.debounce.300ms="expires_at.0" class="form-control">
                                    @error('expires_at.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                           
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Certificate</label>
                                    <input type="file" wire:model.debounce.300ms="certificate_path.0" class="form-control">
                                    @error('certificate_path.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                             <div class="col-md-6">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Status<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="qualification_status.0" class="form-control" required>
                                        <option value="">Select Option</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Verified">Verified</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                    @error('qualification_status.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                        </div>
                        @foreach($inputs as $key => $value)
                             <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Qualifications<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="qualification_id.{{$value}}" class="form-control" required>
                                        <option value="">Select Option</option>
                                        @foreach ($qualifications as $qualification)
                                        <option value="{{$qualification->id}}">{{$qualification->name}}</option>
                                        @endforeach
                                    </select>
                                     <small>  <a href="{{ route('qualifications.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Qualification</a></small> <a href="#" wire:click.prevent="refresh('qualifications')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('qualification_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Level</label>
                                    <input type="text" wire:model.debounce.300ms="level.{{$value}}" class="form-control">
                                    @error('level.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Date Awarded</label>
                                    <input type="date" wire:model.debounce.300ms="date_awarded.{{$value}}" class="form-control">
                                    @error('date_awarded.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Expires At</label>
                                    <input type="date" wire:model.debounce.300ms="expires_at.{{$value}}" class="form-control">
                                    @error('expires_at.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                           
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Certificate</label>
                                    <input type="file" wire:model.debounce.300ms="certificate_path.{{$value}}" class="form-control">
                                    @error('certificate_path.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                             <div class="col-md-5">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Status<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="qualification_status.{{$value}}" class="form-control" required>
                                        <option value="">Select Option</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Verified">Verified</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                    @error('qualification_status.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
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
                                    <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Qualification</button>
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
    
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="qualificationEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Applicant Qualifications <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateQualifications()" >
                    <div class="modal-body">
                        @foreach ($existing_qualifications as $key => $value)
                            <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Qualifications<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="current_qualification_id.{{$key}}" class="form-control" required>
                                        <option value="">Select Option</option>
                                        @foreach ($qualifications as $qualification)
                                        <option value="{{$qualification->id}}">{{$qualification->name}}</option>
                                        @endforeach
                                    </select>
                                     <small>  <a href="{{ route('qualifications.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Qualification</a></small> <a href="#" wire:click.prevent="refresh('qualifications')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('qualification_id.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Level</label>
                                    <input type="text" wire:model.debounce.300ms="current_level.{{$key}}" class="form-control">
                                    @error('current_level.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Date Awarded</label>
                                    <input type="date" wire:model.debounce.300ms="current_date_awarded.{{$key}}" class="form-control">
                                    @error('current_date_awarded.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Expires At</label>
                                    <input type="date" wire:model.debounce.300ms="current_expires_at.{{$key}}" class="form-control">
                                    @error('current_expires_at.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                           
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Certificate</label>
                                  <small>
                                    Selected File:
                                    <a href="{{ asset('myfiles/documents/' . ($old_certificate[$key] ?? '')) }}"
                                    target="_blank" style="color: blue">
                                        {{ $old_certificate[$key] ?? 'No file selected' }}
                                    </a>
                                </small>
                                    <input type="file" wire:model.debounce.300ms="current_certificate_path.{{$key}}" class="form-control">
                                    @error('current_certificate_path.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                             <div class="col-md-5">
                                <div class="form-group" >
                                    <label for="one" class="radio-label">Status<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="current_qualification_status.{{$key}}" class="form-control" required>
                                        <option value="">Select Option</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Verified">Verified</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                    @error('current_qualification_status.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                           <div class="col-md-1">
                                <div class="form-group" style="margin-top: 29px; ">
                                    <a href="#" wire:click.prevent="removeShow({{ $value->id }},'qualifications')" ><i class="fa fa-trash color-danger"></i></a>
                                </div>
                            </div> 
                        </div>
                        @endforeach
                      
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


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="decisionModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Management Decision <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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
                                    <label for="one" class="radio-label">Management Decision</label>
                                    <select wire:model.debounce.300ms="decision.0" class="form-control" required>
                                        <option value="">Select Option</option>
                                        <option value="Engage">Engage</option>
                                        <option value="Fail">Fail</option>
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
                                        <label for="one" class="radio-label">Management Decision</label>
                                        <select wire:model.debounce.300ms="decision.{{$value}}" class="form-control" required>
                                            <option value="">Select Option</option>
                                            <option value="Engage">Engage</option>
                                            <option value="Fail">Fail</option>
                                        </select>
                                        @error('decision.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                           <div class="row">
                                <div class="col-md-10">
                                     <div class="form-group">
                                        <label for="description">Comments</label>
                                        <textarea class="form-control" wire:model.debounce.300ms="comments.{{$value}}" cols="30" rows="2"></textarea>
                                        @error('comments.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Management Decision <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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
                                        <label for="one" class="radio-label">Management Decision</label>
                                        <select wire:model.debounce.300ms="current_decision.{{$key}}" class="form-control" required>
                                            <option value="">Select Option</option>
                                            <option value="Engage">Engage</option>
                                            <option value="Fail">Fail</option>
                                        </select>
                                        @error('current_decision.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>   
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <label for="description">Comments</label>
                                        <textarea class="form-control" wire:model.debounce.300ms="current_comments.{{$key}}" cols="30" rows="2"></textarea>
                                        @error('current_comments.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group" style="margin-top: 29px; ">
                                        <a href="#" wire:click.prevent="removeShow({{ $value->id }},'decisions')" ><i class="fa fa-trash color-danger"></i></a>
                                    </div>
                                </div> 
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
                                        <label for="one" class="radio-label">Management Decision</label>
                                        <select wire:model.debounce.300ms="decision.{{$value}}" class="form-control" required>
                                            <option value="">Select Option</option>
                                            <option value="Engage">Engage</option>
                                            <option value="Fail">Fail</option>
                                        </select>
                                        @error('decision.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>   
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-10">
                                     <div class="form-group">
                                        <label for="description">Comments</label>
                                        <textarea class="form-control" wire:model.debounce.300ms="comments.{{$value}}" cols="30" rows="2"></textarea>
                                        @error('comments.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Interview Scores <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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
                                    <label for="one" class="radio-label">Criterion (Optional)</label>
                                    <select wire:model.debounce.300ms="criterion.0" class="form-control">
                                        <option value="">Select Option</option>
                                        @foreach ($criterions as $criterion)
                                            <option value="{{$criterion->name}}">{{$criterion->name}}</option>
                                        @endforeach
                                    </select>
                                    <small>  <a href="{{ route('recruitment-criterions.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Criterion</a></small> <a href="#" wire:click.prevent="refresh('criterions')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('criterion.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                            <div class="col-md-3">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Criterion Weight (Optional)</label>
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
                                        <label for="description">Score<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="score.0" placeholder="Score Percentage" required>
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
                                        <label for="one" class="radio-label">Criterion (Optional)</label>
                                        <select wire:model.debounce.300ms="criterion.{{$value}}" class="form-control">
                                            <option value="">Select Option</option>
                                            @foreach ($criterions as $criterion)
                                                <option value="{{$criterion->name}}">{{$criterion->name}}</option>
                                            @endforeach
                                        </select>
                                        <small>  <a href="{{ route('recruitment-criterions.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Criterion</a></small> <a href="#" wire:click.prevent="refresh('criterions')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        @error('criterion.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>   
                                </div>
                                <div class="col-md-3">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Criterion Weight (Optional)</label>
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
                                            <label for="description">Score<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="score.{{$value}}" placeholder="Score Percentage" required>
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
                                    <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Score</button>
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
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Interview Scores <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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
                                    <label for="one" class="radio-label">Criterion (Optional)</label>
                                    <select wire:model.debounce.300ms="current_criterion.{{$key}}" class="form-control">
                                        <option value="">Select Option</option>
                                        @foreach ($criterions as $criterion)
                                            <option value="{{$criterion->name}}">{{$criterion->name}}</option>
                                        @endforeach
                                    </select>
                                    <small>  <a href="{{ route('recruitment-criterions.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Stage</a></small> <a href="#" wire:click.prevent="refresh('criterions')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('current_criterion.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                            <div class="col-md-3">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Criterion Weight (Optional)</label>
                                       <input type="number" step="any" class="form-control" wire:model.debounce.300ms="current_weight.{{$key}}">
                                        @error('current_weight.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Score<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="current_score.{{$key}}" placeholder="Score Percentage" required>
                                        @error('current_score.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Comments</label>
                                        <textarea class="form-control" wire:model.debounce.300ms="current_comments.{{$key}}" cols="30" rows="2"></textarea>
                                        @error('current_comments.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group" style="margin-top: 29px; ">
                                    <a href="#" wire:click.prevent="removeShow({{ $value->id }},'scores')" ><i class="fa fa-trash color-danger"></i></a>
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
                                        <label for="one" class="radio-label">Criterion (Optional)</label>
                                        <select wire:model.debounce.300ms="criterion.{{$value}}" class="form-control">
                                            <option value="">Select Option</option>
                                            @foreach ($criterions as $criterion)
                                                <option value="{{$criterion->name}}">{{$criterion->name}}</option>
                                            @endforeach
                                        </select>
                                        <small>  <a href="{{ route('recruitment-criterions.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Stage</a></small> <a href="#" wire:click.prevent="refresh('criterions')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        @error('criterion.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>   
                                </div>
                                <div class="col-md-3">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Criterion Weight (Optional)</label>
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
                                            <label for="description">Score<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="score.{{$value}}" placeholder="Score Percentage" required>
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
                                    <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Score</button>
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
                            <div class="col-md-5">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Comments</label>
                                        <textarea  class="form-control" wire:model.debounce.300ms="current_comments.{{$key}}" cols="30" rows="2"></textarea>
                                        @error('current_comments.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Attach Document</label>
                                        @if (isset($old_attachment[$key]))
                                            <small>Attachment: <a href="{{ asset('myfiles/documents/' . $old_attachment[$key]) }}" target="_blank">{{ $old_attachment[$key] }}</a></small>
                                        @endif
                                        <input type="file" class="form-control" wire:model.debounce.300ms="current_check_attachment.{{$key}}" placeholder="Select Attachement" >
                                        @error('current_check_attachment.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group" style="margin-top: 29px; ">
                                    <a href="#" wire:click.prevent="removeShow({{ $value->id }},'checks')" ><i class="fa fa-trash color-danger"></i></a>
                                </div>
                            </div> 
                        </div>
                        @endforeach
                        

                       
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

     <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to delete this Item</strong> </center>
                </div>
                <form wire:submit.prevent="removeItem()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Remove</button> 
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
                                            <option value="{{$job_posting->id}}">{{$job_posting->job_title ? $job_posting->job_title->title : ""}} Due: {{$job_posting->due_date}} Start: {{$job_posting->start_date}}</option>
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
                                    <label for="name">Name<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Applicant Name" required>
                                    @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Surname<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="surname" placeholder="Enter Applicant Surname" required>
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
                                    <label for="name">Phonenumber<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Phonenumber" required>
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
                                    <label for="name">DOB<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="dob" placeholder="Enter DOB" required>
                                    @error('dob') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                
                        </div> 
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">ID#<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="idnumber" placeholder="Enter ID#" required>
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
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Experience<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="years_experience" placeholder="Enter years of experience" required>
                                    @error('years_experience') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                               <div class="form-group">
                                    <label for="name">Source<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="source" required>
                                        <option value="">Select Option</option>
                                        <option value="Walk In">Walk In</option>
                                        <option value="Referral">Referral</option>
                                    </select>
                                    @error('source') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Application Status<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="status" required>
                                        <option value="">Select Option</option>
                                        <option value="Applied">Applied</option>
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
                                    <label for="name">Notes</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="2"></textarea>
                                    @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
 <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="applicationEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Application <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
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
                                    <label for="name">Name<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Applicant Name" required>
                                    @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Surname<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="surname" placeholder="Enter Applicant Surname" required>
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
                                    <label for="name">Phonenumber<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Phonenumber" required>
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
                                    <label for="name">DOB<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="dob" placeholder="Enter DOB" required>
                                    @error('dob') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                
                        </div> 
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">ID#<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="idnumber" placeholder="Enter ID#" required>
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
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Experience<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="years_experience" placeholder="Enter years of experience" required>
                                    @error('years_experience') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                               <div class="form-group">
                                    <label for="name">Source<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="source" required>
                                        <option value="">Select Option</option>
                                        <option value="Walk In">Walk In</option>
                                        <option value="Referral">Referral</option>
                                    </select>
                                    @error('source') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Application Status<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="status" required>
                                        <option value="">Select Option</option>
                                        <option value="Applied">Applied</option>
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
                                    <label for="name">Notes</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="2"></textarea>
                                    @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
  <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to delete Application</strong> </center>
                </div>
                <form wire:submit.prevent="destroy()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Remove</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-refresh"></i> Update Status <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateStatus()" >
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Application Status<span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="status" required>
                                <option value="">Select Option</option>
                                <option value="Applied">Applied</option>
                                <option value="Engaged">Engaged</option>
                                <option value="Declined">Declined</option>
                                <option value="Contracted">Contracted</option>
                                <option value="Hired">Hired</option>
                            </select>
                            @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
</div>
