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
                                <a href="" data-toggle="modal" data-target="#job_titleModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Job Title</a>
                            </div>

                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                             <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search job titles...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Title
                                    </th>
                                    <th class="th-sm">About
                                    </th>
                                    <th class="th-sm">Qualifications
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($job_titles))
                                <tbody>
                                    @forelse ($job_titles as $job_title)
                                  <tr>
                                    <td>
                                        {{$job_title->title}} <br>
                                        <small>
                                            <strong>Department: </strong>{{$job_title->department ? $job_title->department->name : ""}} <br>
                                            <strong>Grade: </strong> @foreach ($job_title->grades as $grade)
                                                {{$grade->grade_name}} {{ $grade->grade_code}} @if(!$loop->last), @endif
                                            @endforeach
                                            <br>
                                            <strong>Rank: </strong>{{$job_title->rank ? $job_title->rank->name : ""}} <br>
                                        </small>
                                    </td>
                                    <td>
                                        <small><strong>Description: </strong>{{$job_title->description}}</small> <br>
                                        <small><strong>Requirements: </strong>{{$job_title->requirements}}</small> <br>
                                        <small><strong>Duties: </strong>{{$job_title->duties}}</small> <br>
                                        <small><strong>Instructions: </strong>{{$job_title->instructions}}</small>
                                    </td>
                                    <td>
                                        <button class="btn btn-success btn-rounded btn-xs" wire:click.prevent="showQualification({{$job_title->id}})"> <i class="fa fa-plus"></i> {{$job_title->title}} Qualification(s)</button>
                                        @if ($job_title->job_title_qualifications->count() > 0)
                                          <br>   
                                            @foreach ($job_title->job_title_qualifications as $job_title_qualification)
                                                <small>{{$job_title_qualification->qualification ? $job_title_qualification->qualification->name : ""}} <strong>{{ $job_title_qualification->mandatory == True ? "Mandatory, " : "Not Mandatory, "}}</strong>  <strong>Weight: </strong>  {{ $job_title_qualification->weight}} <strong>Min Level: </strong> {{ $job_title_qualification->min_level}}  <strong>Min Score: </strong> {{ $job_title_qualification->min_score}}</small> 
                                                @if(!$loop->last), @endif 
                                                @if ($loop->last)
                                                    <button class="btn btn-primary btn-rounded btn-xs" wire:click.prevent="showEditQualification({{$job_title->id}})"> <i class="fa fa-edit"></i></button>
                                                @endif
                                            @endforeach
                                            <br>
                                             
                                        @endif
                                      
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$job_title->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#job_titleDeleteModal{{ $job_title->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('job_titles.delete')
                                </td>
                                  </tr>
                                   @empty
                                  <tr>
                                    <td colspan="7">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Job Titles Found ....
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
                                    @if (isset($job_titles))
                                        {{ $job_titles->links() }} 
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
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Qualification <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="addQualification()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Qualifications<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="qualification_id.0" class="form-control" required>
                                        <option value="">Select Qualification</option>
                                        @foreach ($qualifications as $qualification)
                                        <option value="{{$qualification->id}}">{{$qualification->name}}</option>
                                        @endforeach
                                    </select>
                                     <small>  <a href="{{ route('qualifications.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Qualification</a></small> <a href="#" wire:click.prevent="refresh('qualifications')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('qualification_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-10" style="padding-top:28px; ">
                                <input type="checkbox" wire:model.debounce.300ms="mandatory.0"   class="line-style" />
                                <label for="one" class="radio-label">Mandatory</label>
                                @error('mandatory.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>   
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Min Level</label>
                                          <input type="number" step="any" class="form-control" wire:model.debounce.300ms="min_level.0" placeholder="Enter Min Level" >
                                        @error('min_level.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Weight</label>
                                          <input type="number" step="any" class="form-control" wire:model.debounce.300ms="weight.0" placeholder="Enter Weight" >
                                        @error('weight.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form">
                                    <div class="form-group">
                                        <label for="description">Min Score</label>
                                          <input type="number" step="any" class="form-control" wire:model.debounce.300ms="min_score.0" placeholder="Enter Min Score" >
                                        @error('min_score.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        @foreach($inputs as $key => $value)
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country">Qualifications<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="qualification_id.{{$value}}" class="form-control" required>
                                            <option value="">Select Qualification</option>
                                            @foreach ($qualifications as $qualification)
                                            <option value="{{$qualification->id}}">{{$qualification->name}}</option>
                                            @endforeach
                                        </select>
                                         <small>  <a href="{{ route('qualifications.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Qualification</a></small> <a href="#" wire:click.prevent="refresh('qualifications')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        @error('qualification_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-10" style="padding-top:28px; ">
                                    <input type="checkbox" wire:model.debounce.300ms="mandatory.{{$value}}"   class="line-style" />
                                    <label for="one" class="radio-label">Mandatory</label>
                                    @error('mandatory.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>   
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Min Level</label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="min_level.{{$value}}" placeholder="Enter Min Level" >
                                            @error('min_level.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Weight</label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="weight.{{$value}}" placeholder="Enter Weight" >
                                            @error('weight.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Min Score</label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="min_score.{{$value}}" placeholder="Enter Min Score" >
                                            @error('min_score.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                 <div class="col-md-1">
                                <div class="form-group" style="padding-top:28px; ">
                                    <label for=""></label>
                                    <button class="btn btn-danger btn-rounded btn-xs"    wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
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
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Edit Qualification(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateQualification()" >
                    <div class="modal-body">
                        @foreach($job_title_qualifications as $key => $value)
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country">Qualifications<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="qualification_id.{{$key}}" class="form-control" required>
                                            <option value="">Select Qualification</option>
                                            @foreach ($qualifications as $qualification)
                                            <option value="{{$qualification->id}}">{{$qualification->name}}</option>
                                            @endforeach
                                        </select>
                                        <small>  <a href="{{ route('qualifications.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Qualification</a></small> <a href="#" wire:click.prevent="refresh('qualifications')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        @error('qualification_id.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-10" style="padding-top:28px; ">
                                    <input type="checkbox" wire:model.debounce.300ms="mandatory.{{$key}}"   class="line-style" />
                                    <label for="one" class="radio-label">Mandatory</label>
                                    @error('mandatory.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>   
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Min Level</label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="min_level.{{$key}}" placeholder="Enter Min Level" >
                                            @error('min_level.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Weight</label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="weight.{{$key}}" placeholder="Enter Weight" >
                                            @error('weight.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form">
                                        <div class="form-group">
                                            <label for="description">Min Score</label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="min_score.{{$key}}" placeholder="Enter Min Score" >
                                            @error('min_score.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                 <div class="col-md-1">
                                <div class="form-group" style="padding-top:28px; ">
                                    <label for=""></label>
                                    <button class="btn btn-danger btn-rounded btn-xs"    wire:click.prevent="removeShow({{ $value->id }})"> <i class="fa fa-times"></i></button>
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
                   <center> <strong>Are you sure you want to delete {{$qualification ? $qualification->name : ""}} Qualification from {{$job_title ? ucfirst($job_title->title) : ""}} Job Title </strong> </center>
                </div>
                <form wire:submit.prevent="removeQualification()" >
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="job_titleModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Job Title <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Title<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="title" placeholder="Enter Job Title"required >
                                    @error('title') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
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
                        
                        </div>
                        <div class="row">
                            <div class="col-md-6">
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
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Grades</label>
                                    <select wire:model.debounce.300ms="grade_id" class="form-control" multiple>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="job_titleEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Job Title <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                     <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Title<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="title" placeholder="Enter Job Title"required >
                                    @error('title') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
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
                        
                        </div>
                        <div class="row">
                            <div class="col-md-6">
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
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Grades</label>
                                    <select wire:model.debounce.300ms="grade_id" class="form-control" multiple>
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

