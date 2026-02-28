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
                                <a href="" data-toggle="modal" data-target="#job_postingModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Job Post</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search job postings...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Post
                                    </th>
                                    <th class="th-sm">Due
                                    </th>
                                    <th class="th-sm">Start 
                                    </th>
                                    <th class="th-sm">Contract 
                                    </th>
                                    <th class="th-sm">Requirements
                                    </th>
                                    <th class="th-sm">Duties
                                    </th>
                                    <th class="th-sm">Instructions
                                    </th>
                                    <th class="th-sm">Candidates(Req)
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($job_postings))
                                <tbody>
                                    @forelse ($job_postings as $job_posting)
                                  <tr>
                                    <td>
                                        {{$job_posting->job_title ? $job_posting->job_title->title : ""}} <br>
                                        <small><strong>Dpt:</strong> {{$job_posting->department ? $job_posting->department->name : ""}}</small> <br>
                                        <small><strong>Rank:</strong> {{$job_posting->rank ? $job_posting->rank->name : ""}}</small> <br>
                                        <small><strong>Grade:</strong> {{$job_posting->grade ? $job_posting->grade->grade_code : ""}} {{$job_posting->grade ? $job_posting->grade->grade_name : ""}}</small> <br>
                                        <small><strong>Description:</strong> {{$job_posting->description}}</small> <br>
                                    </td>
                                    <td>
                                        {{$job_posting->due_date}}
                                    </td>
                                    <td>
                                        {{$job_posting->start_date}}
                                    </td>
                                    <td>
                                        {{$job_posting->contract_type}}
                                        @if ($job_posting->duration)
                                            <br>
                                            {{$job_posting->duration ? $job_posting->duration."Month(s)" : ""}}
                                        @endif
                                    </td>
                                  
                                    <td>
                                        {{$job_posting->requirements}}
                                    </td>
                                    <td>
                                        {{$job_posting->duties}}
                                    </td>
                                    <td>
                                        {{$job_posting->instructions}}
                                    </td>
                                    <td>
                                        {{$job_posting->number_of_candidates}}
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if ($job_posting != Null)
                                                    <li><a href="#"  wire:click="edit({{$job_posting->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#"  wire:click="delete({{$job_posting->id}})" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                      
                                </td>
                                  </tr>
                                @empty
                                  <tr>
                                    <td colspan="10">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Job Posting Found ....
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
                                    @if (isset($job_postings))
                                        {{ $job_postings->links() }} 
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="job_postingModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-60" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Job Post <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="name">Job Title<span class="required" style="color: red">*</span></label>
                                <select  class="form-control" wire:model.debounce.300ms="job_title_id" required>
                                    <option value="">Select Job Title</option>
                                    @foreach ($job_titles as $job_title)
                                        <option value="{{$job_title->id}}">{{$job_title->title}}</option>
                                    @endforeach
                                </select>
                                    <small>  <a href="{{ route('job_titles.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Job Title</a></small> <a href="#" wire:click.prevent="refresh('job_titles')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                @error('job_title_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                                <div class="form">
                                <div class="form-group">
                                    <label for="description">Required #<span class="required" style="color: red">*</span></label>
                                    <input type="number" class="form-control" wire:model.debounce.300ms="number_of_candidates" placeholder="Required number to be employeed" required>
                                    @error('number_of_candidates') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                   
                        <div class="row">
                              <div class="col-md-4">
                                <div class="form-group">
                                    <label for="country">Departments</label>
                                    <select wire:model.debounce.300ms="department_id" class="form-control">
                                        <option value="">Select Department</option>
                                        @foreach ($departments as $department)
                                        <option value="{{$department->id}}">{{$department->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="country">Ranks</label>
                                    <select wire:model.debounce.300ms="rank_id" class="form-control">
                                        <option value="">Select Rank</option>
                                        @foreach ($ranks as $rank)
                                        <option value="{{$rank->id}}">{{$rank->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('rank_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                             <div class="col-md-4">
                                <div class="form-group">
                                    <label for="country">Grades</label>
                                    <select wire:model.debounce.300ms="grade_id" class="form-control" >
                                        <option value="">Select Grade</option>
                                        @foreach ($grades as $grade)
                                        <option value="{{$grade->id}}">{{$grade->grade_name}} {{$grade->grade_code}}</option>
                                        @endforeach
                                    </select>
                                     <small>  <a href="{{ route('grades.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Grade</a></small> <a href="#" wire:click.prevent="refresh('grades')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('grade_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea class="form-control" wire:model.debounce.300ms="description" rows="2" placeholder="Enter Description"></textarea>
                                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Requirements</label>
                                        <textarea class="form-control" wire:model.debounce.300ms="requirements" rows="2" placeholder="Enter Requirements"></textarea>
                                        @error('requirements') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="duties">Duties</label>
                                        <textarea class="form-control" wire:model.debounce.300ms="duties" rows="2" placeholder="Enter Duties"></textarea>
                                        @error('duties') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="instructions">Instructions</label>
                                        <textarea class="form-control" wire:model.debounce.300ms="instructions" rows="2" placeholder="Enter Instructions"></textarea>
                                        @error('instructions') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="name">Due Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="due_date" placeholder="Enter Due Date" required />
                                @error('due_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Start Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="start_date" placeholder="Enter Start Date" required />
                                @error('start_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @if ($contract_type == "Fixed Term")
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="validationSample01">Contract<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="contract_type" required>
                                        <option value="">Select Contract Type</option>
                                        <option value="Full Time">Full Time</option>
                                        <option value="Fixed Term">Fixed Term</option>
                                </select>
                                    @error('contract_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="validationSample01">Duration<span class="required" style="color: red">*</span></label>
                                     <input type="number" class="form-control" wire:model.debounce.300ms="duration" placeholder="# of Month(s) eg 6 for 6 Months" required />
                                    @error('duration') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @else   
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="validationSample01">Contract<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="contract_type" required>
                                        <option value="">Select Contract Type</option>
                                        <option value="Full Time">Full Time</option>
                                        <option value="Fixed Term">Fixed Term</option>
                                </select>
                                    @error('contract_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @endif
                       
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

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="job_postingDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to delete Job Posting</strong> </center>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="job_postingEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Loss Cause Category <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                           <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="name">Job Title<span class="required" style="color: red">*</span></label>
                                <select  class="form-control" wire:model.debounce.300ms="job_title_id" required>
                                    <option value="">Select Job Title</option>
                                    @foreach ($job_titles as $job_title)
                                        <option value="{{$job_title->id}}">{{$job_title->title}}</option>
                                    @endforeach
                                </select>
                                    <small>  <a href="{{ route('job_titles.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Job Title</a></small> <a href="#" wire:click.prevent="refresh('job_titles')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                @error('job_title_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                                <div class="form">
                                <div class="form-group">
                                    <label for="description">Required #<span class="required" style="color: red">*</span></label>
                                    <input type="number" class="form-control" wire:model.debounce.300ms="number_of_candidates" placeholder="Required number to be employeed" required>
                                    @error('number_of_candidates') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                   
                        <div class="row">
                              <div class="col-md-4">
                                <div class="form-group">
                                    <label for="country">Departments</label>
                                    <select wire:model.debounce.300ms="department_id" class="form-control">
                                        <option value="">Select Department</option>
                                        @foreach ($departments as $department)
                                        <option value="{{$department->id}}">{{$department->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="country">Ranks</label>
                                    <select wire:model.debounce.300ms="rank_id" class="form-control">
                                        <option value="">Select Rank</option>
                                        @foreach ($ranks as $rank)
                                        <option value="{{$rank->id}}">{{$rank->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('rank_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                             <div class="col-md-4">
                                <div class="form-group">
                                    <label for="country">Grades</label>
                                    <select wire:model.debounce.300ms="grade_id" class="form-control" >
                                        <option value="">Select Grade</option>
                                        @foreach ($grades as $grade)
                                        <option value="{{$grade->id}}">{{$grade->grade_name}} {{$grade->grade_code}}</option>
                                        @endforeach
                                    </select>
                                     <small>  <a href="{{ route('grades.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Grade</a></small> <a href="#" wire:click.prevent="refresh('grades')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('grade_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea class="form-control" wire:model.debounce.300ms="description" rows="2" placeholder="Enter Description"></textarea>
                                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Requirements</label>
                                        <textarea class="form-control" wire:model.debounce.300ms="requirements" rows="2" placeholder="Enter Requirements"></textarea>
                                        @error('requirements') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="duties">Duties</label>
                                        <textarea class="form-control" wire:model.debounce.300ms="duties" rows="2" placeholder="Enter Duties"></textarea>
                                        @error('duties') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="instructions">Instructions</label>
                                        <textarea class="form-control" wire:model.debounce.300ms="instructions" rows="2" placeholder="Enter Instructions"></textarea>
                                        @error('instructions') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="name">Due Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="due_date" placeholder="Enter Due Date" required />
                                @error('due_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Start Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="start_date" placeholder="Enter Start Date" required />
                                @error('start_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @if ($contract_type == "Fixed Term")
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="validationSample01">Contract<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="contract_type" required>
                                        <option value="">Select Contract Type</option>
                                        <option value="Full Time">Full Time</option>
                                        <option value="Fixed Term">Fixed Term</option>
                                </select>
                                    @error('contract_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="validationSample01">Duration<span class="required" style="color: red">*</span></label>
                                     <input type="number" class="form-control" wire:model.debounce.300ms="duration" placeholder="# of Month(s) eg 6 for 6 Months" required />
                                    @error('duration') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @else   
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="validationSample01">Contract<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="contract_type" required>
                                        <option value="">Select Contract Type</option>
                                        <option value="Full Time">Full Time</option>
                                        <option value="Fixed Term">Fixed Term</option>
                                </select>
                                    @error('contract_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @endif
                       
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



</div>

