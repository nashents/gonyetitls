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
                                <a href="#" data-toggle="modal" data-target="#trainingModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Training</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">Training Item
                                    </th>
                                    <th class="th-sm">Duration
                                    </th>
                                    <th class="th-sm">Participation
                                    </th>
                                    <th class="th-sm">Comments
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($trainings))
                                <tbody>
                                    @forelse ($trainings as $training)
                                  <tr>
                                    <td>
                                        @if ($training->employee)
                                            {{$training->employee ? $training->employee->name : ""}}   {{$training->employee ? $training->employee->surname : ""}}
                                        @endif
                                        @if ($training->driver)
                                            {{$training->driver->employee ? $training->driver->employee->name : ""}}    {{$training->driver->employee ? $training->driver->employee->surname : ""}}
                                        @endif
                                    </td>
                                    <td>{{$training->training_item ? $training->training_item->name : ""}}</td>
                                    <td>
                                        @if ($training->day_event == False)
                                            {{$training->from}} - {{$training->to}}
                                        @else
                                            {{$training->date}}
                                        @endif
                                    </td>
                                    <td>{{$training->participation}}</td>
                                    <td>{{$training->comments}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('trainings.show', $training->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$training->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#trainingDeleteModal{{ $training->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('trainings.delete')
                                </td>
                                  </tr>
                                 @empty
                                        <tr>
                                            <td colspan="6">
                                                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                    No Training Records Found ....
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
                                        @if (isset($trainings) && $trainings->count()>0)
                                            {{ $trainings->links() }} 
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

  
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="trainingModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Training Record <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Employees<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="employee_id" class="form-control" required>
                                    <option value="">Select Employee</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                                    @endforeach
                                </select>
                                @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Training Item<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="training_item_id" class="form-control" required>
                                    <option value="">Select Item</option>
                                    @foreach ($training_items as $item)
                                        <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                                 <small><a href="{{ route('training_items.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Training Item</a></small> <a href="#" wire:click.prevent="refresh('training_items')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                @error('training_item_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="day_event"   class="line-style" />
                        <label for="one" class="radio-label">Day Event?</label>
                        @error('day_event') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <div class="row">
                        @if ($day_event == True)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Date<span class="required" style="color: red">*</span></label>
                                    <input type="datetime-local" wire:model.debounce.300ms="date" class="form-control" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @else
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Start Date<span class="required" style="color: red">*</span></label>
                                    <input type="datetime-local" wire:model.debounce.300ms="from" class="form-control" required>
                                    @error('from') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">End Date<span class="required" style="color: red">*</span></label>
                                    <input type="datetime-local" wire:model.debounce.300ms="to" class="form-control" required>
                                    @error('to') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif
                       
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Participation</label>
                                <select wire:model.debounce.300ms="participation" class="form-control">
                                    <option value="">Select Employee</option>
                                    <option value="Present">Present</option>
                                    <option value="Absent">Absent</option>
                                </select>
                                @error('participation') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Comments</label>
                        <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="3"></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="trainingEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Training Record <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                 
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Employees<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="employee_id" class="form-control" required>
                                    <option value="">Select Employee</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                                    @endforeach
                                </select>
                                @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Training Item<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="training_item_id" class="form-control" required>
                                    <option value="">Select Employee</option>
                                    @foreach ($training_items as $item)
                                        <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                                 <small><a href="{{ route('training_items.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Training Item</a></small> <a href="#" wire:click.prevent="refresh('training_items')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                @error('training_item_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                   
                    <div class="row">
                         @if ($day_event == True)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Date<span class="required" style="color: red">*</span></label>
                                    <input type="datetime-local" wire:model.debounce.300ms="date" class="form-control" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @else
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Start Date<span class="required" style="color: red">*</span></label>
                                    <input type="datetime-local" wire:model.debounce.300ms="from" class="form-control" required>
                                    @error('from') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">End Date<span class="required" style="color: red">*</span></label>
                                    <input type="datetime-local" wire:model.debounce.300ms="to" class="form-control" required>
                                    @error('to') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Participation</label>
                                <select wire:model.debounce.300ms="participation" class="form-control">
                                    <option value="">Select Employee</option>
                                    <option value="Present">Present</option>
                                    <option value="Absent">Absent</option>
                                </select>
                                @error('participation') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Comments</label>
                        <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="3"></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

