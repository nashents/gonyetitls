<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Add New Driver</h5>
                            </div>
                        </div>
                        <div class="panel-body">

                            <form wire:submit.prevent="store()" class="p-20" enctype="multipart/form-data">
                                <h5 class="underline mt-n">Driver Details</h5>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Transporters<span class="required" style="color: red">*</span></label>
                                       <select wire:model.debounce.300ms="transporter_id" class="form-control" required>
                                           <option value="">Select Transporter</option>
                                           @foreach ($transporters as $transporter)
                                               <option value="{{$transporter->id}}">{{$transporter->name}}</option>
                                           @endforeach
                                       </select>
                                            @error('transporter_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="middlename">Name<span class="required" style="color: red">*</span></label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required />
                                            @error('name') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="middlename">Middle Name</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="middle_name" placeholder="Enter Middle Name" />
                                            @error('middle_name') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="middlename">Surname<span class="required" style="color: red">*</span></label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="surname" placeholder="Enter Surname" required />
                                            @error('surname') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                         <div class="form-group">
                                            <label for="gender">Gender<span class="required" style="color: red">*</span></label>
                                                 <div class="col-sm-10">
                                                    <div class="radio">
                                                        <label>
                                                        <input type="radio" wire:model.debounce.300ms="gender" id="optionsRadios1" value="Male" required/>
                                                        Male
                                                        </label>
                                                    </div>
                                                    <div class="radio">
                                                        <label>
                                                        <input type="radio"  wire:model.debounce.300ms="gender" id="optionsRadios2" value="Female" required/>
                                                        Female
                                                        </label>
                                                    </div>
                                                </div>
                                                @error('gender') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                    </div>
                                    <!-- /.col-md-6 -->

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="contact13">DOB</label>
                                            <input type="date" class="form-control" wire:model.debounce.300ms="dob"  placeholder="Enter DOB "  />
                                            @error('dob') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>
                                  <h5 class="underline mt-30">Select what to use as username</h5>
                                 <div class="mb-10">
                                    <label class="radio-inline">
                                        <input type="radio" wire:model.debounce.300ms="use_email_as_username" value="1" name="optradio" >Work Email
                                     </label>
                                    <label class="radio-inline">
                                        <input type="radio" wire:model.debounce.300ms="use_email_as_username" value="0" name="optradio">Phonenumber
                                    </label>
                                    @error('use_email_as_username') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="email">Work Email</label>
                                            <input type="email" class="form-control" wire:model.debounce.300ms="email" placeholder="Enter Work Email"  />
                                            @error('email') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                     <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="email">Personal Email</label>
                                            <input type="email" class="form-control" wire:model.debounce.300ms="personal_email" placeholder="Enter Personal Email" />
                                            @error('personal_email') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="contact13">Phonenumber</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Phonenumber " />
                                            @error('phonenumber') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">ID Number</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="idnumber" placeholder="Enter ID Number"  />
                                            @error('idnumber') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Passport Number</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="passport_number" placeholder="Enter Passport Number" />
                                            @error('passport_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                     <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="middlename">License Number</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="license_number" placeholder="Enter License Number" />
                                            @error('license_number') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="middlename">Experience</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="experience" placeholder="Enter Driver Experience" />
                                            @error('experience') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->

                                   
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="class">License Class</label>
                                            <select name="" wire:model.debounce.300ms="class" class="form-control" >
                                                <option value="">Select License Class</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>

                                            </select>
                                            @error('class') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                     <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Branch</label>
                                           <select wire:model.debounce.300ms="branch_id" class="form-control"  >
                                               <option value="" selected> Select Branch</option>
                                               @foreach ($branches as $branch)
                                                   <option value="{{$branch->id}}">{{$branch->name}}</option>
                                               @endforeach
                                           </select>
                                           @error('branch_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('branches.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Branch</a></small> <a href="#" wire:click.prevent="refresh('branches')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>
                              
                                <div class="row">
                                   
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Department<span class="required" style="color: red">*</span></label>
                                           <select wire:model.debounce.300ms="selectedDepartment" class="form-control" multiple required>
                                               <option value="" selected > Select Department</option>
                                               @foreach ($departments as $department)
                                                   <option value="{{$department->id}}">{{$department->name}}</option>
                                               @endforeach
                                           </select>
                                           @error('selectedDepartment') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Job Title</label>
                                           <select wire:model.debounce.300ms="job_title" class="form-control" >
                                               <option value="" selected > Select Job Title</option>
                                               @if (!is_null($selectedDepartment))
                                                @foreach ($job_titles as $job_title)
                                                    <option value="{{$job_title->title}}">{{$job_title->title}}</option>
                                                @endforeach
                                               @endif
                                           </select>
                                           @error('job_title') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                            <label for="title">Grades</label>
                                            <select class="form-control" wire:model.debounce.300ms="grade_id" >
                                                <option value="">Select Grade</option>
                                                @foreach ($grades as $grade)
                                                    <option value="{{ $grade->id }}">{{ $grade->grade_code }} {{ $grade->grade_name }}</option>
                                                @endforeach
                                            </select>
                                             <small>  <a href="{{ route('grades.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Grade</a></small> <a href="#" wire:click.prevent="refresh('grades')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                            @error('grade_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Rank<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="rank_id" class="form-control" required>
                                                <option value="" selected>Select Rank</option>
                                                @foreach ($ranks as $rank)
                                                    <option value="{{$rank->id}}">{{$rank->name}}</option>
                                                @endforeach
                                            </select>
                                            @error('rank_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Role<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="role_id" class="form-control" multiple="multiple" required>
                                                <option value="" selected>Select Role</option>
                                                @foreach ($roles as $role)
                                                    <option value="{{$role->id}}">{{$role->name}}</option>
                                                @endforeach
                                            </select>
                                            @error('role_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="start_date">Employment Start Date</label>
                                            <input type="date" class="form-control" wire:model.debounce.300ms="start_date" placeholder="Enter Employee Start Date" />
                                            @error('start_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="start_date">Employement End Date</label>
                                            <input type="date" class="form-control" wire:model.debounce.300ms="end_date" placeholder="Enter Employee End Date" />
                                            @error('end_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="start_date">Leave Accrual Rate / Month</label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="accrual_rate" placeholder="Enter Leave Accrual Rate" />
                                            @error('accrual_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="leave_days">Available Leave Days</label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="leave_days" placeholder="Available Leave Days " />
                                            @error('leave_days') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="start_date">Next Of Kin</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="next_of_kin" placeholder="Enter Next Of Kin" />
                                            @error('next_of_kin') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="relationship">Relationship</label>
                                            <select wire:model.debounce.300ms="relationship" class="form-control">
                                                <option value="">Selected Relationship</option>
                                                <option value="Husband">Husband</option>
                                                <option value="Wife">Wife</option>
                                                <option value="Father">Father</option>
                                                <option value="Mother">Mother</option>
                                                <option value="Daughter">Daughter</option>
                                                <option value="Son">Son</option>
                                                <option value="Brother">Brother</option>
                                                <option value="Sister">Sister</option>
                                                <option value="Nephew">Nephew</option>
                                                <option value="Niece">Niece</option>
                                                <option value="Other">Other</option>
                                            </select>
                                            @error('relationship') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="contact">Contact</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="contact" placeholder="Enter Contact" />
                                            @error('contact') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>

                                    <h5 class="underline mt-10">Driver Bank Details</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="title">Bank</label>
                                            <input type="text" class="form-control"  wire:model.debounce.300ms="bank_name" placeholder="Enter Bank Name"  />
                                            @error('bank_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="file">Account Name</label>
                                            <input type="text" class="form-control"  wire:model.debounce.300ms="account_name" placeholder="Enter Account Name" />
                                            @error('account_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="file">Account Number</label>
                                            <input type="text" class="form-control"  wire:model.debounce.300ms="account_number" placeholder="Enter Account Number" />
                                            @error('account_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="title">Account Currency</label>
                                            <select class="form-control" wire:model.debounce.300ms="bank_currency_id" >
                                                <option value="">Select Currency</option>
                                              @foreach ($currencies as $currency)
                                                  <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                              @endforeach
                                            </select>
                                            @error('bank_currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                   
                                </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="expiry_date">Account Branch</label>
                                                <input type="text" class="form-control"  wire:model.debounce.300ms="bank_branch" placeholder="Enter Branch"  />
                                                @error('bank_branch') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="expiry_date">Branch Code</label>
                                                <input type="text" class="form-control"  wire:model.debounce.300ms="branch_code" placeholder="Enter Branch Code" />
                                                @error('branch_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="expiry_date">Swift Code</label>
                                                <input type="text" class="form-control"  wire:model.debounce.300ms="swift_code" placeholder="Enter Swift Code" />
                                                @error('swift_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <br>

                                <h5 class="underline mt-30">Address Details</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Countries</label>
                                           <select wire:model.debounce.300ms="selectedCountry" class="form-control" >
                                               <option value=""> Select Country</option>
                                               @foreach ($countries as $country)
                                                   <option value="{{$country->id}}">{{$country->name}}</option>
                                               @endforeach
                                           </select>
                                           @error('selectedCountry') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('countries.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Country</a></small> <a href="#" wire:click.prevent="refresh('countries')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="province">Province/States</label>
                                           <select wire:model.debounce.300ms="province_id"  class="form-control" >
                                               <option value="" selected>Select Province</option>
                                               @foreach ($provinces as $province)
                                               <option value="{{ $province->id }}">{{ $province->name }}</option>
                                               @endforeach 
                                           </select>
                                           @error('province_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('provinces.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Provinces</a></small> <a href="#" wire:click.prevent="refresh('provinces')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="city">City</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="city" placeholder="Enter City / Town" />
                                            @error('city') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="suburb">Suburb</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="suburb" placeholder="Enter Suburb"  />
                                            @error('suburb') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->

                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="street_address">Street Address</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="street_address" placeholder="Enter Street Address"  />
                                            @error('street_address') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail13">Reference</label>
                                                <input type="text" class="form-control" wire:model.debounce.300ms="reference" placeholder="Enter Reference Fullname" />
                                                @error('reference') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="contact13">Reference Phonenumber</label>
                                                <input type="text" class="form-control" wire:model.debounce.300ms="reference_phonenumber" placeholder="Enter Reference Phonenumber " />
                                                @error('reference_phonenumber') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <!-- /.col-md-6 -->
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="suburb">Contract Duration</label>
                                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="duration" placeholder="Enter contract duration in months"  />
                                                @error('duration') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <!-- /.col-md-6 -->

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="expiration">Contract Expiration Date</label>
                                                <input type="date" class="form-control" wire:model.debounce.300ms="expiration" placeholder="Enter Contract Expiration Date"  />
                                                @error('expiration') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        </div>
                                    <h5 class="underline mt-n">Upload Files(<i>CV, Identity Card, Offer Letter, Contract</i>)</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="title">Title</label>
                                                <input type="text" class="form-control" wire:model.debounce.300ms="title.0" placeholder="Enter File Title eg Identity Card" />
                                                @error('title.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="file">File</label>
                                                <input type="file" class="form-control" wire:model.debounce.300ms="file.0"  placeholder="Upload File " />
                                                @error('file.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <!-- /.col-md-6 -->
                                    </div>
                                    @foreach ($inputs as $key => $value)
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="title">Title</label>
                                                <input type="text" class="form-control" wire:model.debounce.300ms="title.{{$value}}" placeholder="Enter File Title eg Identity Card" />
                                                @error('title.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="file">File</label>
                                                <input type="file" class="form-control" wire:model.debounce.300ms="file.{{$value}}"  placeholder="Upload File " />
                                                @error('file.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label for=""></label>
                                                <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                            </div>
                                        </div>
                                        <!-- /.col-md-6 -->
                                    </div>
                                    @endforeach
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> File</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                    <div class="col-md-12">
                                        <div class="btn-group pull-right mt-10" >
                                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                            <button type="submit" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Create</button>
                                        </div>
                                    </div>
                                    </div>

                            </form>





                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->


            </div>

        </div>
        <!-- /.container-fluid -->
    </section>


</div>
