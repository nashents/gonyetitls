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
                                                    <option value="created_at">application Created At</option>
                                                    <option value="date">application Date</option>
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
                                    <a href="#" data-toggle="modal" data-target="#applicationModal"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>application Register</a>
                                    <a href="#" wire:click="exportapplicationRegisterExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                    <a href="#" wire:click="exportapplicationRegisterCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                    <a href="#" wire:click="exportapplicationRegisterPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                                </div>
                                <div class="col-md-5" style="float: right; padding-right:2px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search application register...">
                                    </div>
                                </div>
                                <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <tr>
                                        <th class="th-sm">Application#
                                        </th>
                                        <th class="th-sm">Date
                                        </th>
                                        <th class="th-sm">Job Title
                                        </th>
                                        <th class="th-sm">Applicant
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>
                                      </tr>
                                    </thead>
                                    @if (isset($applications))
                                    <tbody>
                                        @forelse  ($applications as $application)
                                       
                                      <tr>
                                        <td>
                                            {{$application->application_number}}
                                        </td>
                                        <td>{{ucfirst($application->created_by ? $application->created_by->name : "")}} {{ucfirst($application->created_by ? $application->created_by->surname : "")}}</td>
                                        <td>{{$application->date}}</td>
                                        <td>
                                            @if ($application->recruitment_candidate)
                                                @php
                                                    $applicant = $application->recruitment_candidate;
                                                @endphp
                                                  {{$applicant->name}}
                                            @endif
                                        </td>
                                        <td><span class="badge bg-{{($application->authorization == 'approved') ? 'success' : (($application->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($application->authorization == 'approved') ? 'approved' : (($application->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                      
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
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-calendar"></i> Mark application <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                    <div class="modal-body">
                       
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Date<span class="required" style="color: red">*</span></label>
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
