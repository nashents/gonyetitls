<div>
    <div class="row mt-30">
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">
            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Corridor Details</a></li>
                <li role="presentation"><a href="#transporters" aria-controls="transporters" role="tab" data-toggle="tab">Transporters</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Name</th>
                                <td class="w-20 line-height-35">{{$corridor->name}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">From</th>
                                <td class="w-20 line-height-35">{{ucfirst(App\Models\Country::find($corridor->from)->name)}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">To</th>
                                <td class="w-20 line-height-35">{{ucfirst(App\Models\Country::find($corridor->to)->name)}}</td>
                            </tr>
                            <hr>
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="transporters">
                    @livewire('corridors.transporters', ['id' => $corridor->id])
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
