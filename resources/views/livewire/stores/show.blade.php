<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Store Details</a></li>
                <li role="presentation"><a href="#inventories" aria-controls="inventories" role="tab" data-toggle="tab">Inventory</a></li>
                <li role="presentation"><a href="#tyres" aria-controls="tyres" role="tab" data-toggle="tab">Tyres</a></li>
                {{-- <li role="presentation"><a href="#assets" aria-controls="assets" role="tab" data-toggle="tab">Assets</a></li> --}}

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$store->user ? $store->user->name : ""}} {{$store->user ? $store->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Name</th>
                                <td class="w-20 line-height-35">{{$store->type}}</td>
                            </tr>
                          
                                <tr>
                                    <th class="w-10 text-center line-height-35">Address</th>
                                    <td class="w-20 line-height-35">{{$store->street_address}} {{$store->suburb}} {{$store->city}} {{$store->country}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Status</th>
                                    <td class="w-20 line-height-35"><span class="badge bg-{{$store->status == 1 ? "success" : "danger"}}">{{$store->status == 1 ? "Active" : "Inactive"}}</span></td>
                                </tr>
                               
                             
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="inventories">
                 @livewire('stores.inventories', ['id' => $store->id])
                </div> 
                <div role="tabpanel" class="tab-pane" id="tyres">
                    @livewire('stores.tyres', ['id' => $store->id])
                </div> 
                {{-- <div role="tabpanel" class="tab-pane" id="assets">
                    @livewire('stores.assets', ['id' => $store->id])
                </div>  --}}
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
