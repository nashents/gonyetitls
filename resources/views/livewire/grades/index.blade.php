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
                                <a href="" data-toggle="modal" data-target="#gradeModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Grade</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search grades...">
                                </div>
                            </div>
                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Grade
                                    </th>
                                    <th class="th-sm">Job Structure
                                    </th>
                                    <th class="th-sm">Compensation
                                    </th>
                                    <th class="th-sm">Promotion
                                    </th>
                                    <th class="th-sm">Benefits
                                    </th>
                                    <th class="th-sm">Effective Date
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($grades))
                                <tbody>
                                    @forelse ($grades as $grade)
                                  <tr>
                                    <td>
                                        <strong>Code: </strong>  {{$grade->grade_code}} <br>
                                        <strong>Name: </strong>{{$grade->grade_name}} <br>
                                        <strong>Level: </strong>{{$grade->grade_level}}
                                     </td>
                                    <td>
                                        <strong>Category:</strong> {{$grade->job_category}} <br>
                                        <strong>Band:</strong> {{$grade->job_band}} <br>
                                        <strong>Job Titles:</strong> 
                                        @foreach ($grade->job_titles as $job_title)
                                            {{$job_title->title}}
                                        @endforeach
                                    </td>
                                    <td>
                                        <strong>Currency: </strong> {{$grade->currency ? $grade->currency->name : ""}} <br>
                                        <strong>Min Salary: </strong> {{number_format($grade->min_salary,2)}} <br>
                                        <strong>Max Salary: </strong> {{number_format($grade->max_salary,2)}} <br>
                                    </td>
                                    <td>
                                        @php
                                            $next_grade = App\Models\Grade::find($grade->next_grade_id);
                                        @endphp
                                        <strong>Next Grade:</strong> {{$next_grade ? $next_grade->name : ""}} <br>
                                        <strong>Promotion Criteria: </strong> {{$grade->promotion_criteria}} <br>
                                        <strong>Max # of years in grade: </strong> {{$grade->max_years_in_grade}} <br>
                                    </td>
                                    <td>
                                        <strong>Total Leave Days: </strong> {{$grade->leave_days}} <br>
                                        <strong>Bonus Eligibility: </strong> {{$grade->bonus_eligibility == True ? "Yes" : "No"}} <br>
                                        <strong>Overtime Eligibility: </strong> {{$grade->overtime_eligibility == True ? "Yes" : "No"}} <br>
                                        <strong>Benefit Package: </strong> {{$grade->benefits_package}}
                                    </td>
                                    <td>{{$grade->effective_date}}</td>
                                    <td><span class="badge bg-{{$grade->status == 1 ? "success" : "danger"}}">{{$grade->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$grade->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#gradeDeleteModal{{ $grade->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('grades.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="8">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Grades Found ....
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
                                    @if (isset($grades))
                                        {{ $grades->links() }} 
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="gradeModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Grade <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Code<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="grade_code" placeholder="Eg A1, B1" required />
                                @error('grade_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="grade_name" placeholder="Eg Senior Manager" required />
                                @error('grade_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Level</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="grade_level" placeholder="Grade Numerical Number" />
                                @error('grade_level') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Currencies</label>
                                <select wire:model.debounce.300ms="currency_id" class="form-control">
                                        <option value="">Select Currency</option>
                                        @foreach ($currencies as $currency)
                                             <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                        @endforeach
                                </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Min Salary</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="min_salary" placeholder="Min Salary eg 500" />
                                @error('min_salary') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Max Salary</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="max_salary" placeholder="Max Salary eg 1500"  />
                                @error('max_salary') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Grade Category</label>
                                <input type="text"  class="form-control" wire:model.debounce.300ms="job_category" placeholder="Eg Admin, Technical, Exec"  />
                                @error('job_category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Grade Band</label>
                                <input type="text"  class="form-control" wire:model.debounce.300ms="job_band" placeholder="Grouping eg Support Staff, Middle Management"  />
                                @error('job_band') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Grade Job Titles<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="job_title_id" class="form-control" required multiple>
                                        <option value="">Select Job Title</option>
                                        @foreach ($job_titles as $job_title)
                                            <option value="{{$job_title->id}}">{{$job_title->title}}</option>
                                        @endforeach
                                </select>
                                <small style="color: green">Please multi select grade job titles</small>
                                @error('job_title_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Next Grade</label>
                                <select wire:model.debounce.300ms="next_grade_id" class="form-control" >
                                        <option value="">Select Grade</option>
                                        @foreach ($grades as $grade)
                                            <option value="{{$grade->id}}">{{$grade->grade_name}} {{$grade->grade_code}}</option>
                                        @endforeach
                                </select>
                                @error('next_grade_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Promotion Criteria</label>
                               <textarea  class="form-control" wire:model.debounce.300ms="promotion_criteria" cols="30" rows="3" placeholder="Requirements eg years of service, performance score"></textarea>
                                @error('promotion_criteria') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Max Years Of Service</label>
                                <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="max_years_in_grade" placeholder="Before promotion or review"  />
                                @error('max_years_in_grade') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                      <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Leave Entitlement</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="leave_days" placeholder="Annual leave entitlement" />
                                @error('leave_days') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-10">
                                <input type="checkbox" wire:model.debounce.300ms="bonus_eligibility"   class="line-style" />
                                <label for="one" class="radio-label">Bonus Eligibility</label>
                                @error('bonus_eligibility') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>    
                        </div>
                        <div class="col-md-3">
                            <div class="mb-10">
                                <input type="checkbox" wire:model.debounce.300ms="overtime_eligibility"   class="line-style" />
                                <label for="one" class="radio-label">Overtime Eligibility</label>
                                @error('overtime_eligibility') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>    
                        </div>
                        <div class="col-md-3">
                           <div class="form-group">
                                <label for="name">Benefits</label>
                               <textarea  class="form-control" wire:model.debounce.300ms="benefits_package" cols="30" rows="3" placeholder="Linked benefits scheme"></textarea>
                                @error('benefits_package') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Effective Date</label>
                                <input type="date"  class="form-control" wire:model.debounce.300ms="effective_date" placeholder="Effective Date" />
                                @error('effective_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Status</label>
                                <select class="form-control" wire:model.debounce.300ms="status" >
                                    <option value="">Select Option</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="gradeEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Grade <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                     <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Code<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="grade_code" placeholder="Eg A1, B1" required />
                                @error('grade_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="grade_name" placeholder="Eg Senior Manager" required />
                                @error('grade_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Level</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="grade_level" placeholder="Grade Numerical Number" />
                                @error('grade_level') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Currencies</label>
                                <select wire:model.debounce.300ms="currency_id" class="form-control">
                                        <option value="">Select Currency</option>
                                        @foreach ($currencies as $currency)
                                             <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                        @endforeach
                                </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Min Salary</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="min_salary" placeholder="Min Salary eg 500" />
                                @error('min_salary') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Max Salary</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="max_salary" placeholder="Max Salary eg 1500"  />
                                @error('max_salary') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Grade Category</label>
                                <input type="text"  class="form-control" wire:model.debounce.300ms="job_category" placeholder="Eg Admin, Technical, Exec"  />
                                @error('job_category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Grade Band</label>
                                <input type="text"  class="form-control" wire:model.debounce.300ms="job_band" placeholder="Grouping eg Support Staff, Middle Management"  />
                                @error('job_band') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Grade Job Titles<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="job_title_id" class="form-control" required multiple>
                                        <option value="">Select Job Title</option>
                                        @foreach ($job_titles as $job_title)
                                            <option value="{{$job_title->id}}">{{$job_title->title}}</option>
                                        @endforeach
                                </select>
                                <small style="color: green">Please multi select grade job titles</small>
                                @error('job_title_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Next Grade</label>
                                <select wire:model.debounce.300ms="next_grade_id" class="form-control" >
                                        <option value="">Select Grade</option>
                                        @foreach ($grades as $grade)
                                            <option value="{{$grade->id}}">{{$grade->grade_name}} {{$grade->grade_code}}</option>
                                        @endforeach
                                </select>
                                @error('next_grade_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Promotion Criteria</label>
                               <textarea  class="form-control" wire:model.debounce.300ms="promotion_criteria" cols="30" rows="3" placeholder="Requirements eg years of service, performance score"></textarea>
                                @error('promotion_criteria') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Max Years Of Service</label>
                                <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="max_years_in_grade" placeholder="Before promotion or review"  />
                                @error('max_years_in_grade') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                      <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Leave Entitlement</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="leave_days" placeholder="Annual leave entitlement" />
                                @error('leave_days') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-10">
                                <input type="checkbox" wire:model.debounce.300ms="bonus_eligibility"   class="line-style" />
                                <label for="one" class="radio-label">Bonus Eligibility</label>
                                @error('bonus_eligibility') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>    
                        </div>
                        <div class="col-md-3">
                            <div class="mb-10">
                                <input type="checkbox" wire:model.debounce.300ms="overtime_eligibility"   class="line-style" />
                                <label for="one" class="radio-label">Overtime Eligibility</label>
                                @error('overtime_eligibility') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>    
                        </div>
                        <div class="col-md-3">
                           <div class="form-group">
                                <label for="name">Benefits</label>
                               <textarea  class="form-control" wire:model.debounce.300ms="benefits_package" cols="30" rows="3" placeholder="Linked benefits scheme"></textarea>
                                @error('benefits_package') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Effective Date</label>
                                <input type="date"  class="form-control" wire:model.debounce.300ms="effective_date" placeholder="Effective Date" />
                                @error('effective_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Status</label>
                                <select class="form-control" wire:model.debounce.300ms="status" >
                                    <option value="">Select Option</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

