<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">GRV Details</a></li>
                <li role="presentation"><a href="#items" aria-controls="items" role="tab" data-toggle="tab">Items</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">GRV#</th>
                                <td class="w-20 line-height-35">{{$goods_received->goods_received_number}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$goods_received->user ? $goods_received->user->name : ""}} {{$goods_received->user ? $goods_received->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">ReceivedBy</th>
                                <td class="w-20 line-height-35">{{$goods_received->employee ? $goods_received->employee->name : ""}} {{$goods_received->employee ? $goods_received->employee->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Received On</th>
                                <td class="w-20 line-height-35">{{$goods_received->date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Vendor</th>
                                <td class="w-20 line-height-35">{{$goods_received->vendor ? $goods_received->vendor->name : ""}}</td>
                            </tr>
                          
                            <tr>
                                <th class="w-10 text-center line-height-35">Delivery#</th>
                                <td class="w-20 line-height-35">{{$goods_received->delivery_number}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Delivery Date</th>
                                <td class="w-20 line-height-35">{{$goods_received->delivery_date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Driver Name</th>
                                <td class="w-20 line-height-35">{{$goods_received->driver_name}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Condition</th>
                                <td class="w-20 line-height-35">{{$goods_received->condition}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Comments</th>
                                <td class="w-20 line-height-35">{{$goods_received->comments}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35"> @if ($goods_received->status == 1)
                                        <span class="badge bg-success">Open</span>
                                        @else
                                        <span class="badge bg-danger">Closed</span>        
                                        @endif</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="items">
                  @livewire('goods-receiveds.items', ['id' => $goods_received->id])
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
