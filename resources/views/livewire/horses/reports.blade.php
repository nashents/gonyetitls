<div>
    <style>
        .modal-lg {
        max-width: 80%;
    }
    </style>
        <section class="section">
            <x-loading/>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-heading">
                                <div>
                                    @include('includes.messages')
                                </div>
                                <div class="panel-title">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="panel-title">
                                                <h5>Report date range</h5>
                                                <div class="row">
                                                
                                                <div class="col-lg-2">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                  From
                                                  </span>
                                                  <input type="date" wire:model.debounce.300ms="from" class="form-control" aria-label="...">
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>
                                                <div class="col-lg-2">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                  To
                                                  </span>
                                                  <input type="date" wire:model.debounce.300ms="to" class="form-control" aria-label="...">
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>
                                            </div>
                                                <h5>Filter reports by</h5>    
                                                <div class="row">
                                                    <div class="col-md-3">
                                                		<div class="input-group ">
                                                			<span class="input-group-addon">
                                                       Filter By
                                                      </span>
                                                      <select wire:model.debounce.300ms="selectedFilter" class="form-control  " aria-label="..." >
                                                            <option value="">Select Filter</option>
                                                            <option value="revenue">Revenue per truck</option>
                                                            <option value="fuel">Fuel Consumption per truck</option>
                                                      </select>
                                                			
                                                		</div>
                                                		<!-- /input-group -->
                                                	</div>
                                                </div>
                                                <h5>Reports format</h5>
                                                @if (isset($horses))
                                                @if ($horses->count()>0)
                                            <div class="row">
                                                <div class="col-md-4">
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="col-lg-2">
                                                        <div class="btn-group">
                                                            @if (isset($from) && isset($to))
                                                            <a href="{{route('horses.report.preview.range',['selectedFilter' => $selectedFilter, 'from' => $from, 'to' => $to])}}" class="btn btn-default border-primary btn-wide btn-rounded" type="button"  aria-haspopup="true" aria-expanded="true">
                                                                <i class="fas fa-file-invoice"></i> Preview
                                                            </a>
                                                            @else   
                                                            <a href="{{route('horses.report.preview',['selectedFilter' => $selectedFilter])}}" class="btn btn-default border-primary btn-wide btn-rounded" type="button"  aria-haspopup="true" aria-expanded="true">
                                                                <i class="fas fa-file-invoice"></i> Preview
                                                            </a>
                                                            @endif
                                                           
                                                        </div>
                                                        <!-- /input-group -->
                                                    </div>
                                                    <div class="col-lg-2">
                                                       
                                                        <!-- /input-group -->
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                </div>
                                            </div>
                                            @endif
                                            @endif
                                            </div>
                                        </div>
                                  
                                   
                                </div>
                                </div>
                            </div>
                            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                                @if ($selectedFilter == "revenue")
                                <table id="horses_revenueTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <tr>
                                            <th class="th-sm">Transporter
                                            </th>
                                            <th class="th-sm">Horse#
                                            </th>
                                            <th class="th-sm">Make
                                            </th>
                                            <th class="th-sm">Model
                                            </th>
                                            <th class="th-sm">HRN
                                            </th>
                                            <th class="th-sm">Revenue
                                            </th>
                                          </tr>
                                    </thead>
                                  
                                    @if ($horses->count()>0)
                                    <tbody>
                                        @foreach ($horses as $horse)
                                        <tr>
                                            <td>{{ucfirst($horse->transporter ? $horse->transporter->name : "")}}</td>
                                            <td>{{$horse->horse_number}}</td>
                                            <td>{{ucfirst($horse->horse_make ? $horse->horse_make->name : "")}}</td>
                                            <td>{{ucfirst($horse->horse_model ? $horse->horse_model->name : "")}}</td>
                                            <td>{{ucfirst($horse->registration_number)}}</td>
                                            <td> 
                                                @foreach ($currencies as $currency)
                                                    @php
                                                        $revenue = App\Models\Trip::where('horse_id',$horse->id)
                                                                                    ->where('currency_id',$currency->id)->sum('freight');
                                                    @endphp
                                                    @if (isset($revenue) && $revenue > 0)
                                                        {{ $currency->name }} {{ $currency->symbol }}{{number_format($revenue,2)}} <br>
                                                    @endif
                                                @endforeach
                                            </td>
                                          </tr>
                                      @endforeach
                                    </tbody>
                                    @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                    @endif
                                   
                                </table>
                                @elseif ($selectedFilter == "fuel")   
                                <table id="horses_fuelTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <tr>
                                            <th class="th-sm">Transporter
                                            </th>
                                            <th class="th-sm">Horse#
                                            </th>
                                            <th class="th-sm">Make
                                            </th>
                                            <th class="th-sm">Model
                                            </th>
                                            <th class="th-sm">HRN
                                            </th>
                                            <th class="th-sm">Fuel
                                            </th>
                                          </tr>
                                    </thead>
                                   
                                    @if ($horses->count()>0)
                                    <tbody>
                                        @foreach ($horses as $horse)
                                        <tr>
                                            <td>{{ucfirst($horse->transporter ? $horse->transporter->name : "")}}</td>
                                            <td>{{$horse->horse_number}}</td>
                                            <td>{{ucfirst($horse->horse_make ? $horse->horse_make->name : "")}}</td>
                                            <td>{{ucfirst($horse->horse_model ? $horse->horse_model->name : "")}}</td>
                                            <td>{{ucfirst($horse->registration_number)}}</td>
                                            <td>
                                                @if ($horse->fuels->sum('quantity') > 0)
                                                    {{$horse->fuels->sum('quantity')}} Litres 
                                                @endif
                                             </td>
                                          </tr>
                                      @endforeach
                                    </tbody>
                                    @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                    @endif
                                  
                                  </table>  
                                @endif
                                <!-- /.col-md-12 -->
                            </div>
                        </div>
                    </div>


                </div>
                <!-- /.row -->

            </div>
            <!-- /.container-fluid -->
        </section>

          <!-- Modal -->


    </div>
