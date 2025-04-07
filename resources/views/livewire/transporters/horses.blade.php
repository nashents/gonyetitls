<div>
    <div class="panel-heading">
        <div>
            @include('includes.messages')
        </div>
        <div class="panel-title">
            <a href="{{route('horses.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Horse</a>
          
        </div>
    </div>
    <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">

        <table id="horsesTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <tr>
                <th class="th-sm">Horse#
                </th>
                <th class="th-sm">Fleet#
                </th>
                <th class="th-sm">Transporter
                </th>
                <th class="th-sm">Make
                </th>
                <th class="th-sm">HRN
                </th>
                <th class="th-sm">Revenue
                </th>
                <th class="th-sm">Mileage
                </th>
                <th class="th-sm"><i class="fa fa-arrow-right" aria-hidden="true"></i> Service
                </th>
                <th class="th-sm">Fitness
                </th>
                <th class="th-sm">Availability
                </th>
               
                <th class="th-sm">Actions
                </th>
              </tr>
            </thead>
            @if ($horses->count()>0)
            <tbody>
                @foreach ($horses as $horse)
              <tr>
               
                <td>{{$horse->horse_number}}</td>
                <td>{{$horse->fleet_number}}</td>
                <td>{{$horse->transporter ? $horse->transporter->name : ""}}</td>
                <td>{{ucfirst($horse->horse_make ? $horse->horse_make->name : "")}} {{ucfirst($horse->horse_model ? $horse->horse_model->name : "")}}</td>
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
                <td>{{$horse->mileage ? $horse->mileage."Kms" : ""}}</td>
                <td>{{$horse->next_service ? $horse->next_service."Kms" : ""}}</td>
                  <td><span class="badge bg-{{$horse->service == 0 ? "success" : "danger"}}">{{$horse->service == 0 ? "Fit for use" : "In Service"}}</span></td>
                <td><span class="badge bg-{{$horse->status == 1 ? "success" : "danger"}}">{{$horse->status == 1 ? "Available" : "Unavailable"}}</span></td>
              
              
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="{{route('horses.show', $horse->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                        </ul>
                    </div>
                    @include('horses.delete')

            </td>
              </tr>
              @endforeach
            </tbody>
            @else
            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
            @endif

          </table>

        <!-- /.col-md-12 -->
    </div>
</div>
