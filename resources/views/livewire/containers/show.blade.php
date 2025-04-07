<div>
    <div class="row mt-30">

    <div class="col-md-10 col-md-offset-1">

        <ul class="nav nav-tabs nav-justified" role="tablist">
            <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Fuel Station Details</a></li>
            <li role="presentation"><a href="#topups" aria-controls="topups" role="tab" data-toggle="tab">Fuel Top Ups</a></li>
        </ul>
        <!-- /.row -->
        <div class="tab-content bg-white p-15">

            <div role="tabpanel" class="tab-pane active" id="basic">
                <table class="table table-striped">

                    <tbody class="text-center line-height-35 ">

                        <tr>
                            <th class="w-10 text-center line-height-35">Fueling Station Number</th>
                            <td class="w-20 line-height-35"> {{$container->container_number}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Created By</th>
                            <td class="w-20 line-height-35">{{ucfirst($container->user ? $container->user->name : "")}} {{ucfirst($container->user ? $container->user->surname : "")}}</td>
                        </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Name</th>
                                <td class="w-20 line-height-35">{{ucfirst($container->name)}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Email</th>
                                <td class="w-20 line-height-35">{{$container->email}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Phonenumber</th>
                                <td class="w-20 line-height-35">{{$container->phonenumber}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Address</th>
                                <td class="w-20 line-height-35">{{$container->address}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Fuel Type</th>
                                <td class="w-20 line-height-35">{{$container->fuel_type}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Purchase Type</th>
                                <td class="w-20 line-height-35">{{$container->purchase_type}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35">{{$container->currency ? $container->currency->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Capacity</th>
                                <td class="w-20 line-height-35">
                                    @if ($container->capacity)
                                    {{$container->capacity}}Litres        
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Balance</th>
                                <td class="w-20 line-height-35"> {{$container->balance}}Litres</td>
                            </tr>
                    </tbody>
                </table>
               
            </div>
            <div role="tabpanel" class="tab-pane" id="topups">
                @livewire('containers.top-ups', ['id' => $container->id])
              </div> 

              <div class="row">
                <div class="col-md-12">
                    <div class="btn-group pull-right mt-10" >
                       <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                    </div>
                </div>
                </div>




            <!-- /.section-title -->
        </div>
    </div>
 </div>
 
</div>
