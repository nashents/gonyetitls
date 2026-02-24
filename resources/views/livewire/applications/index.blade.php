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
                                                    <td></td>
                                                    <td></td>
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
