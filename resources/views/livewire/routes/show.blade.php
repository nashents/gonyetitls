<div>
    <div class="row mt-30">
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">
            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Route Details</a></li>
                <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
                <li role="presentation"><a href="#trips" aria-controls="trips" role="tab" data-toggle="tab">Trips</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                <table class="table table-striped">
                    <tbody class="text-center line-height-35 ">
                        <tr>
                            <th class="w-10 text-center line-height-35">Name</th>
                            <td class="w-20 line-height-35">{{$route->name}}</td>
                        </tr>
                        @php
                            $from = App\Models\Destination::find($route->from);
                            $to = App\Models\Destination::find($route->to);
                        @endphp
                        <tr>
                            <th class="w-10 text-center line-height-35">From</th>
                            <td class="w-20 line-height-35">
                                @if (isset($from))
                                {{$from->country ? $from->country->name : ""}} {{ucfirst($from ? $from->city : "")}}        
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">To</th>
                            <td class="w-20 line-height-35">
                                @if (isset($to))
                                     {{$to->country ? $to->country->name : ""}} {{ucfirst($to ? $to->city : "")}}        
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Distance</th>
                            <td class="w-20 line-height-35">{{$route->distance."Kms"}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Tollgates</th>
                            <td class="w-20 line-height-35">{{$route->tollgates}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Rank</th>
                            <td class="w-20 line-height-35">{{$route->rank}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Description</th>
                            <td class="w-20 line-height-35">{{$route->description}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Assessment Expires</th>
                            <td class="w-20 line-height-35">
                                 @if ($route->expiry_date >= now()->toDateTimeString())
                                <span class="badge bg-success">{{$route->expiry_date}}</span>
                                @else
                                <span class="badge bg-danger">{{$route->expiry_date}}</span>        
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Status</th>
                            <td class="w-20 line-height-35"><span class="badge bg-{{$route->status == 1 ? "success" : "danger"}}">{{$route->status == 1 ? "Active" : "Inactive"}}</span></td>
                        </tr>
                        <hr>
                    </tbody>
                </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="documents">
                    @livewire('documents.index', ['id' => $route->id,'category' =>'route'])
                  </div>
                <div role="tabpanel" class="tab-pane" id="trips">
                    @livewire('routes.trips', ['id' => $route->id])
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
