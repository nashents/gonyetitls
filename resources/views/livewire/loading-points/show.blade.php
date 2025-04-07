<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Loading Point Details</a></li>
                <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$loading_point->user ? $loading_point->user->name : ""}} {{$loading_point->user ? $loading_point->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Name</th>
                                <td class="w-20 line-height-35">{{$loading_point->name}}</td>
                            </tr>
                          
                                <tr>
                                    <th class="w-10 text-center line-height-35">Contact</th>
                                    <td class="w-20 line-height-35">{{$loading_point->contact_name}}{{$loading_point->surname}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Email</th>
                                    <td class="w-20 line-height-35">{{$loading_point->email}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Phonenumber</th>
                                    <td class="w-20 line-height-35">{{$loading_point->phonenumber}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Coordinates</th>
                                    <td class="w-20 line-height-35">{{$loading_point->lat}} , {{$loading_point->long}}</td>
                                </tr>
                              
                                <tr>
                                    <th class="w-10 text-center line-height-35">Assessment Expires</th>
                                    <td class="w-20 line-height-35">
                                         @if ($loading_point->expiry_date >= now()->toDateTimeString())
                                        <span class="badge bg-success">{{$loading_point->expiry_date}}</span>
                                        @else
                                        <span class="badge bg-danger">{{$loading_point->expiry_date}}</span>        
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Description</th>
                                    <td class="w-20 line-height-35">{{$loading_point->description}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Status</th>
                                    <td class="w-20 line-height-35"><span class="badge bg-{{$loading_point->status == 1 ? "success" : "danger"}}">{{$loading_point->status == 1 ? "Active" : "Inactive"}}</span></td>
                                </tr>
                             
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="documents">
                    @livewire('documents.index', ['id' => $loading_point->id,'category' =>'loading_point'])
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
