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
                                <a href="" data-toggle="modal" data-target="#emailModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Email</a>
                            </div>

                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="emailsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Destination
                                    </th>
                                    <th class="th-sm">Subject
                                    </th>
                                    <th class="th-sm">Body
                                    </th>
                                    <th class="th-sm">Attachment(s)
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Actions
                                    </th>

                                  </tr>
                                </thead>
                                @if ($emails->count()>0)
                                <tbody>
                                    @foreach ($emails as $email)
                                  <tr>
                                    <td>
                                        @if ($email->destination == "Employee")
                                        {{ucfirst($email->destination)}} | {{ucfirst($email->employee ? $email->employee->email : "")}}
                                        @endif
                                        
                                    </td>
                                    <td>{{$email->subject}}</td>
                                    <td>{!!$email->body!!}</td>
                                    <td>
                                        @foreach ($email->attachments as $attachment)
                                        <i class="fa fa-file"></i> <a href="{{asset('my_files/documents/'.$attachment->filename)}}" style="color: blue" >{{$attachment->filename}}</a>
                                        @endforeach
                                    </td>
                                    <td>{{$email->status == "0" ? "Email not sent" : "Email sent"}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                {{-- <li><a href="#" data-toggle="modal" data-target="#emailEditModal" wire:click.prevent="edit({{$email->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li> --}}
                                                <li><a href="#" data-toggle="modal" data-target="#emailDeleteModal{{$email->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('email.delete')
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                            @endif
                              </table>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->


                <!-- /.col-md-6 -->


                <!-- /.col-md-6 -->

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>




        <!-- Modal -->
        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Send  Email <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="send()">
                    <div class="modal-body">
                      <div class="form-group">
                          <label for="title">Send email to<span class="required" style="color: red">*</span></label>
                         <select class="form-control" wire:model.debounce.300ms="selectedDestination" required>
                         <option value="">Select Destination</option>
                         <option value="employees">All Employees</option>
                         <option value="drivers">All Drivers</option>
                         <option value="employee">Employee</option>
                         <option value="driver">Driver</option>
                         <option value="department">Department</option>
                         <option value="branch">Branch</option>
                        </select>
                          @error('selectedDestination') <span class="error" style="color:red">{{ $message }}</span> @enderror

                        </div>
                        @if (!is_null($selectedDestination))
                          @if ($employee)
                            <div class="form-group">
                                <label for="title">Email To Employee<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="selectedEmployee" required>
                                    <option value="" selected>Select Employee</option>
                                    @foreach ($employees as $employee)
                                        @if ($employee->email)
                                        <option value="{{$employee->id}}">{{ucfirst($employee->name)}} {{ucfirst($employee->surname)}} {{ $employee->email ? "<".$employee->email.">" : "" }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('selectedEmployee') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                           @endif
                        @endif
                        @if (!is_null($selectedDestination))
                          @if ($driver)
                            <div class="form-group">
                                <label for="title">Email To Driver<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="selectedEmployee" required>
                                    <option value="" selected>Select Driver</option>
                                    @foreach ($drivers as $driver)
                                    @if ($driver->email)
                                        <option value="{{$driver->id}}">{{ucfirst($driver->employee ? $driver->employee->name : "")}} {{ucfirst($driver->employee ? $driver->employee->surname : "")}} {{$driver->employee->email ? "<".$driver->employee->email.">" : ""}}</option>
                                    @endif
                                       
                                    @endforeach
                                </select>
                                @error('selectedEmployee') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                          @endif
                        @endif
                        @if (!is_null($selectedDestination))
                          @if ($department)
                            <div class="form-group">
                                <label for="title">Email To Department<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="selectedDepartment" required>
                                    <option value="" selected>Select Department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->name }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedDepartment') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                           @endif
                        @endif
                        @if (!is_null($selectedDestination))
                          @if ($branch)
                            <div class="form-group">
                                <label for="title">Email To Branch<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="selectedBranch" required>
                                    <option value="" selected>Select Branch</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{$branch->id}}">{{$branch->name}}</option>
                                    @endforeach
                                </select>
                                @error('selectedBranch') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                           @endif
                        @endif
                        <div class="form-group">
                            <label for="code">Subject<span class="required" style="color: red">*</span></label>
                            <input type="text" wire:model.debounce.300ms="subject" class="form-control" placeholder="Enter Subject" required>
                            @error('subject') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                      <div class="form-group">
                        <label for="description">Body<span class="required" style="color: red">*</span></label>
                        <div wire:ignore>
                        <textarea wire:model.debounce.300ms="body" id="body" name="body"  class="form-control" cols="30" rows="5" placeholder="Type your message..." required></textarea>
                        </div>
                        @error('body') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                      <div class="form-group">
                          <label for="code"> <i class="fa fa-link"></i> Attachment</label>
                          <small>Accepted file types: docx, xls, xlxs, pdf, csv</small>
                          <input type="file" wire:model.debounce.300ms="file" class="form-control">
                          @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                      </div>

                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Send</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
                </div>
            </div>
        </div>

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="emailEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Email <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="update()">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="title">Email To<span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="selectedDestination" required>
                                    <option value="" selected>Select Destination</option>
                                    <option value="all">All Employees</option>
                                    <option value="employees">Choose Employee</option>
                                    <option value="drivers">Choose Driver</option>
                                    <option value="departments">Choose Department</option>
                                    <option value="branches">Choose Branch</option>
                            </select>
                            @error('selectedDestination') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        @if (!is_null($selectedEmployee))
                        <div class="form-group">
                            <label for="title">Email To Employee<span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="selectedEmployee" required>
                                <option value="" selected>Select Employee</option>
                                @foreach ($employees as $employee)
                                    @if ($employee->email)
                                        <option value="{{$employee->id}}">{{ucfirst($employee->name)}} {{ucfirst($employee->surname)}} {{$employee->email ? "<".$employee->email.">" : ""}}</option>
                                    @endif 
                                @endforeach
                            </select>
                            @error('selectedEmployee') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        @endif
                        @if (!is_null($selectedDestination))
                        @if ($driver)
                          <div class="form-group">
                              <label for="title">Email To Driver<span class="required" style="color: red">*</span></label>
                              <select class="form-control" wire:model.debounce.300ms="selectedEmployee" required>
                                  <option value="" selected>Select Driver</option>
                                  @foreach ($drivers as $driver)
                                  @if ($driver->email)
                                      <option value="{{$driver->id}}">{{ucfirst($driver->employee ? $driver->employee->name : "")}} {{ucfirst($driver->employee ? $driver->employee->surname : "")}} {{$driver->employee->email ? "<".$driver->employee->email.">" : ""}}</option>
                                  @endif
                                     
                                  @endforeach
                              </select>
                              @error('selectedEmployee') <span class="error" style="color:red">{{ $message }}</span> @enderror
                          </div>
                        @endif
                      @endif
                        @if (!is_null($selectedDepartment))
                            <div class="form-group">
                                <label for="title">Email To Department<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="selectedDepartment" required>
                                    <option value="" selected>Select Department</option>
                                    @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedDepartment') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        @endif
                        @if (!is_null($selectedBranch))
                            <div class="form-group">
                                <label for="title">Email To Branch<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="selectedBranch" required>
                                    <option value="" selected>Select Branch</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{$branch->id}}">{{$branch->name}}</option>
                                    @endforeach
                                </select>
                                @error('selectedBranch') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        @endif
                            <div class="form-group">
                                <label for="code">Subject<span class="required" style="color: red">*</span></label>
                                <input type="text" wire:model.debounce.300ms="subject" class="form-control" placeholder="Enter Subject" required>
                                @error('subject') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                            <label for="description">Body<span class="required" style="color: red">*</span></label>
                            <textarea wire:model.debounce.300ms="body" id="editor"  class="form-control" cols="30" rows="10" placeholder="Type your message..." required></textarea>
                            @error('body') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="code"> <i class="fa fa-link"></i> Attachment</label>
                                <small>Accepted file types: docx, xls, xlxs, pdf, csv</small>
                                <input type="file" wire:model.debounce.300ms="file" class="form-control" multiple ="multiple" >
                                @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button wire:click.prevent="update()" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
                </div>
            </div>
        </div>

    </div>
    @section('extra-js')
    <script>
        ClassicEditor
            .create(document.querySelector('#body'))
            .then(editor => {
                editor.model.document.on('change:data', () => {
                @this.set('body', editor.getData());
                })
            })
            .catch(error => {
                console.error(error);
            });
    </script>
    @endsection