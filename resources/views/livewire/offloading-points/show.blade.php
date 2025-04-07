<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">OffLoading Point Details</a></li>
                <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$offloading_point->user ? $offloading_point->user->name : ""}} {{$offloading_point->user ? $offloading_point->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Name</th>
                                <td class="w-20 line-height-35">{{$offloading_point->name}}</td>
                            </tr>
                          
                                <tr>
                                    <th class="w-10 text-center line-height-35">Contact</th>
                                    <td class="w-20 line-height-35">{{$offloading_point->contact_name}}{{$offloading_point->surname}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Email</th>
                                    <td class="w-20 line-height-35">{{$offloading_point->email}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Phonenumber</th>
                                    <td class="w-20 line-height-35">{{$offloading_point->phonenumber}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Coordinates</th>
                                    <td class="w-20 line-height-35">{{$offloading_point->lat}} , {{$offloading_point->long}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Assessment Expires</th>
                                    <td class="w-20 line-height-35">
                                         @if ($offloading_point->expiry_date >= now()->toDateTimeString())
                                        <span class="badge bg-success">{{$offloading_point->expiry_date}}</span>
                                        @else
                                        <span class="badge bg-danger">{{$offloading_point->expiry_date}}</span>        
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Description</th>
                                    <td class="w-20 line-height-35">{{$offloading_point->description}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Status</th>
                                    <td class="w-20 line-height-35"><span class="badge bg-{{$offloading_point->status == 1 ? "success" : "danger"}}">{{$offloading_point->status == 1 ? "Active" : "Inactive"}}</span></td>
                                </tr>
                             
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="documents">
                    @livewire('documents.index', ['id' => $offloading_point->id,'category' =>'offloading_point'])
                  </div>
             
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                        </div>
                    </div>
                    </div>

            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>
   
</div>
