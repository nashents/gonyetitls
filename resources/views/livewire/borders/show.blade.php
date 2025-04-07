<div>
    <div class="row mt-30">
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">
            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Route Details</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                <table class="table table-striped">
                    <tbody class="text-center line-height-35 ">
                        <tr>
                            <th class="w-10 text-center line-height-35">Name</th>
                            <td class="w-20 line-height-35">{{$route->name}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">From</th>
                            <td class="w-20 line-height-35">{{ucfirst(App\Models\Destination::find($route->from)->country->name)}} {{ucfirst(App\Models\Destination::find($route->from)->city)}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">To</th>
                            <td class="w-20 line-height-35">{{ucfirst(App\Models\Destination::find($route->to)->country->name)}} {{ucfirst(App\Models\Destination::find($route->to)->city)}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Rank</th>
                            <td class="w-20 line-height-35">{{$route->rank}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Description</th>
                            <td class="w-20 line-height-35">{{$route->description}}</td>
                        </tr>
                        <hr>
                    </tbody>
                </table>
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
