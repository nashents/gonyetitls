<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        <a href="" data-toggle="modal" data-target="#contactModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Contact</a>
        <br>
        <br>
        <table id="contactsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Name
                </th>
                <th class="th-sm">Surname
                </th>
                <th class="th-sm">Email
                </th>
                <th class="th-sm">Phonenumber
                </th>
                <th class="th-sm">Department
                </th>
                <th class="th-sm">Actions
                </th>
              </tr>
            </thead>
            <tbody>
                @if (isset($contacts))
                @if ($contacts->count() > 0)
                @foreach ($contacts as $contact)
              <tr>
                <td>{{$contact->name}}</td>
                <td>{{$contact->surname}}</td>
                <td>{{$contact->email}}</td>
                <td>{{$contact->phonenumber}}</td>
                <td>{{$contact->department}}</td>
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#" wire:click="edit({{$contact->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                            <li><a href="#" data-toggle="modal" data-target="#contactDeleteModal{{$contact->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                        </ul>
                    </div>
                    @include('contacts.delete')

            </td>
            </tr>
              @endforeach
              @else
              <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
              @endif
              @else
              {{-- <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt=""> --}}
              @endif
            </tbody>
          </table>
    {{-- </blockquote> --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="contactModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="contact">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add contact(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="name.0" placeholder="Enter Name" required>
                                @error('name.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Surname</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="surname.0" placeholder="Enter Surname" >
                                @error('surname.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Email</label>
                                <input type="email" class="form-control"  wire:model.debounce.300ms="email.0" placeholder="Enter Email" >
                                @error('email.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Phonenumber<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control"  wire:model.debounce.300ms="phonenumber.0" placeholder="Enter Phonenumber" required>
                                @error('phonenumber.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Department<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="department.0" placeholder="Enter Department" required>
                                @error('department.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                        @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Name<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="name.{{$value}}" placeholder="Enter Name" required>
                                    @error('name.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file">Surname</label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="surname.{{$value}}" placeholder="Enter Surname" >
                                    @error('surname.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Email</label>
                                    <input type="email" class="form-control"  wire:model.debounce.300ms="email.{{$value}}" placeholder="Enter Email" >
                                    @error('email.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="file">Phonenumber<span class="required" style="color: red">*</span></label>
                                    <input type="number" class="form-control"  wire:model.debounce.300ms="phonenumber.{{$value}}" placeholder="Enter Phonenumber" required>
                                    @error('phonenumber.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="title">Department<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="department.{{ $value }}" placeholder="Enter Department" required>
                                    @error('department.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Contact</button>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="contactEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="contact">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Contact(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="name" placeholder="Enter Name" required>
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="surname">Surname</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="surname" placeholder="Enter Surname" >
                                @error('surname') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Email</label>
                                <input type="email" class="form-control"  wire:model.debounce.300ms="email" placeholder="Enter Email" >
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="surname">Phonenumber<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control"  wire:model.debounce.300ms="phonenumber" placeholder="Enter Phonenumber" required>
                                @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Department<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="department" placeholder="Enter Department" required>
                                @error('department') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
   
    @section('extra-js')
    <script>
    $(document).ready( function () {
        $('#contactsTable').DataTable();
    } );
    </script>
 
@endsection

</div>
