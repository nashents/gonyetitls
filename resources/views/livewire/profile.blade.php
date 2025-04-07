<div>
    <x-loading/>
    <div class="row mt-30">
        <div class="col-md-3">
            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>Your Profile Picture</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-8 col-md-offset-2">
                            <img src="{{asset('images/uploads/'.Auth::user()->profile)}}" alt="{{Auth::user()->name}} {{Auth::user()->surname}}" class="img-responsive">
                            <div class="text-center">
                                <button type="button" class="btn btn-primary btn-xs btn-labeled mt-10" data-toggle="modal" data-target="#profilePictureModal">Update Profile<span class="btn-label btn-label-right"><i class="fa fa-pencil"></i></span></button>
                            </div>
                        </div>
                    </div>
                    @include('profile_picture')
                </div>
            </div>
            
            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>Account Credentials</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th>Username</th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i>{{$employee->user ? $employee->user->username : ""}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Pin</th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i> {{$pin}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <center><a href="#" data-toggle="modal" data-target="#pinResetModal" class="btn btn-primary">Reset Pin <i class="fa fa-key"></i></a></center>  
                                    </th>
                                    
                                </tr>
                        
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
        <!-- /.col-md-3 -->

        <div class="col-md-9">
            @include('includes.messages')
            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#personal" aria-controls="personal" role="tab" data-toggle="tab">{{ucfirst(Auth::user()->name)}} Details</a></li>
                <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="personal">
                    <form action="{{route('employees.update', Auth::user()->id)}}" method="POST">
                        <input type="hidden" name="_method" value="PUT">
                        {{ csrf_field()}}
                            <div class="form-group">
                                <label for="name">Name</label>
                            <input type="text" class="form-control" name="name" value="{{$employee->name}}" disabled >
                            </div>
                            @if ($employee->middlename)
                            <div class="form-group">
                                <label for="name">Middle Name</label>
                            <input type="text" class="form-control" name="name" value="{{$employee->middle_name}}" disabled >
                            </div>
                            @endif

                            <div class="form-group">
                                <label for="surname">Surname</label>
                            <input type="text" class="form-control"  name="phonenumber" value="{{$employee->surname}}" disabled >
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Gender</label>
                                <input type="text" class="form-control" name="email"  value="{{$employee->gender}}" disabled >
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">DOB</label>
                                <input type="text" class="form-control" name="email"  value="{{$employee->dob}}" disabled >
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Phonenumber</label>
                                <input type="text" class="form-control" name="email"  value="{{$employee->phonenumber}}" disabled >
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">ID Number</label>
                                <input type="text" class="form-control" name="email"  value="{{$employee->idnumber}}" disabled >
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Email</label>
                                <input type="text" class="form-control" name="email"  value="{{$employee->email}}" disabled >
                            </div>
                            <div class="form-group">
                                <label for="address">Country</label>
                                <input type="text" class="form-control"  value="{{$employee->country}}" disabled>
                            </div>
                            <div class="form-group">
                                <label for="address">Province</label>
                                <input type="text" class="form-control"  value="{{$employee->province}}" disabled>
                            </div>
                            <div class="form-group">
                                <label for="address">City/Town</label>
                                <input type="text" class="form-control"  value="{{$employee->city}}" disabled>
                            </div>
                            <div class="form-group">
                                <label for="address">Suburb</label>
                                <input type="text" class="form-control"  value="{{$employee->suburb}}" disabled>
                            </div>
                            <div class="form-group">
                                <label for="address">Street Address</label>
                                <input type="text" class="form-control"  value="{{$employee->street_address}}" disabled>
                            </div>
                            <div class="btn-group" role="group" >
                                <button type="button" class="btn btn-gray btn-wide btn-rounded" onclick="goBack()"><i class="fa fa-arrow-left"></i>Back</button>
                            </div>
                    </form>
                </div>

                <div role="tabpanel" class="tab-pane" id="documents">
                    @livewire('documents.index', ['id' => $employee->id,'category'=>'employee'])
                  </div>

                {{-- <div role="tabpanel" class="tab-pane" id="documents">
                    @if ($employee->documents->count()>0)
                    @foreach ($employee->documents as $document)
                    <blockquote class="blockquote-reverse mt-20">
                        <p><a href="{{asset('myfiles/documents/'.$document->filename)}}"><i class="fa fa-file">{{$document->filename}}</i></a></p>
                        <footer>{{$document->title}}</footer>
                    </blockquote>
                    @endforeach
                    @else

                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                    @endif

                </div> --}}



                <!-- /.section-title -->
            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="pinResetModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you sure you want to reset your pin?</strong> </center> 
                </div>
                <form>
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button wire:click.prevent="resetPassword()"  class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-key"></i>Reset Pin</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

</div>
