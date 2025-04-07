<div>
    <div class="row mt-30">
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">
            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Truck Stop Details</a></li>
                <li role="presentation"><a href="#contacts" aria-controls="contacts" role="tab" data-toggle="tab">Contacts</a></li>
                <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                <table class="table table-striped">
                    <tbody class="text-center line-height-35 ">
                        <tr>
                            <th class="w-10 text-center line-height-35">Name</th>
                            <td class="w-20 line-height-35">{{$truck_stop->name}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Route</th>
                            <td class="w-20 line-height-35">{{$truck_stop->route ? $truck_stop->route->name : ""}}</td>
                        </tr>
                        
                        <tr>
                            <th class="w-10 text-center line-height-35">Rating</th>
                            <td class="w-20 line-height-35">{{$truck_stop->rating}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Assessment Expires</th>
                            <td class="w-20 line-height-35">
                                @if ($truck_stop->expiry_date >= now()->toDateTimeString())
                                <span class="badge bg-success">{{$truck_stop->expiry_date}}</span>
                                @else
                                <span class="badge bg-danger">{{$truck_stop->expiry_date}}</span>        
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Description</th>
                            <td class="w-20 line-height-35">{{$truck_stop->description}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Status</th>
                            <td class="w-20 line-height-35"><span class="badge bg-{{$truck_stop->status == 1 ? "success" : "danger"}}">{{$truck_stop->status == 1 ? "Active" : "Inactive"}}</span></td>
                        </tr>
                        <hr>
                    </tbody>
                </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="contacts">
                    @livewire('contacts.index', ['id' => $truck_stop->id,'category' =>'truck_stop'])
                  </div>
                <div role="tabpanel" class="tab-pane" id="documents">
                    @livewire('documents.index', ['id' => $truck_stop->id,'category' =>'truck_stop'])
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
