<div>
    <div id="invoice">
        <x-loading/>
        <div class="toolbar hidden-print">
            <div class="text-end">
                <button type="button" onclick="goBack()" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-arrow-left" style="color:black"></i> Back</button>
                {{-- <a href="#" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-envelope" style="color:red"></i> Send</a> --}}
                @if (isset($selectedFilter))
                <a href="{{route('horses.report.print',['selectedFilter' => $selectedFilter])}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-print" style="color:black"></i> Print</a>
                <a href="{{route('horses.report.pdf',['selectedFilter' => $selectedFilter])}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-file-pdf-o" style="color:red"></i> Export as PDF</a>
                @elseif(isset($selectedFilter) && isset($from) && isset($to))  
                <a href="{{route('horses.report.print.range',['selectedFilter' => $selectedFilter, 'from' => $from, 'to' => $to])}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-print" style="color:black"></i> Print</a>
                <a href="{{route('horses.report.pdf.range',['selectedFilter' => $selectedFilter, 'from' => $from, 'to' => $to])}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-file-pdf-o" style="color:red"></i> Export as PDF</a>
                @endif
            </div>
            <hr>
        </div>
        <div class="invoice overflow-auto">
            <div style="min-width: 600px">
                <header>
                    <div class="row">
                        <div class="col">
                            <a href="javascript:;">
                                            <img src="{{asset('images/uploads/'.$company->logo)}}" width="150" alt="">
                                        </a>
                                       
                        </div>
                        <div class="col company-details">
                            <h4 class="name" >
                                <a target="_blank" href="javascript:;" style="color:  {{Auth::user()->employee->company ? Auth::user()->employee->company->color : Auth::user()->company->color }}">
                            {{$company->name}}
                            </a>
                            </h4>
                            <div>{{$company->street_address}}, {{$company->suburb}}, {{$company->city}} {{$company->country}}</div>
                            <div>{{$company->phonenumber}}
                            </div>
                            <div>{{$company->email}}</div>
                        </div>
                    </div>
                </header>
                @if ($selectedFilter == "revenue")
                <main>
                    <div class="row contacts">
                        <div class="col invoice-to" >
                         
                        </div>
                        <div class="col invoice-details">
                            <h4 class="to" style="margin-bottom: -1px;">Revenue Generated Report</h4> 
                            @if (isset($from) && isset($to))
                            <div class="date" style="padding-bottom: 3px"> <strong>From: </strong>{{ date('F j, Y', strtotime($from)) }}</div>
                            <div class="date" style="padding-bottom: 3px"> <strong>To: </strong>{{ date('F j, Y', strtotime($to)) }}</div>
                            @endif
                        </div>
                    </div> 
                    <table>
                        <thead>
                            <tr>
                                <th class="text-center"><strong>Transporter</strong></th>
                                <th class="text-center"><strong>Horse#</strong></th>
                                <th class="text-center"><strong>Make</strong></th>
                                <th class="text-center"><strong>Model</strong></th>
                                <th class="text-center"><strong>HRN</strong></th>
                                <th class="text-center"><strong>Revenue</strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($horses as $horse)
                                @php
                                    $currencies = App\Models\Currency::all();
                                @endphp
                                 <tr>
                                    <td class="text-center"> {{ $horse->transporter ? $horse->transporter->name : "" }} </td>
                                    <td class="text-center">{{ $horse->horse_number}} </td>
                                    <td class="text-center">{{ $horse->horse_make ? $horse->horse_make->name : ""}}</td>
                                    <td class="text-center">{{ $horse->horse_model ? $horse->horse_model->name : ""}}</td>
                                    <td class="text-center"> {{$horse->registration_number}}</td>
                                    <td class="text-center">
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
                
                    </table>
                   
                </main>
                @elseif ($selectedFilter == "fuel")
                <main>
                    <div class="row contacts">
                        <div class="col invoice-to" >
                           
                        </div>
                        <div class="col invoice-details">
                            <h4 class="to" style="margin-bottom: -1px;">Fuel Usage Report</h4>
                            @if (isset($from) && isset($to))
                            <div class="date" style="padding-bottom: 3px"> <strong>From: </strong>{{ date('F j, Y', strtotime($from)) }}</div>
                            <div class="date" style="padding-bottom: 3px"> <strong>To: </strong>{{ date('F j, Y', strtotime($to)) }}</div>
                            @endif
                            
                            <hr>
                        </div>
                    </div> 
                    <table>
                        <thead>
                            <tr>
                                <th class="text-center"><strong>Transporter</strong></th>
                                <th class="text-center"> <strong>Horse#</strong></th>
                                <th class="text-center"> <strong>Make</strong></th>
                                <th class="text-center"> <strong>Model</strong></th>
                                <th class="text-center"><strong>HRN</strong></th>
                                <th class="text-center"><strong>Fuel</strong></th>
                        </thead>
                        <tbody>
                            @foreach ($horses as $horse)
                         
                             <tr>
                                <td class="text-center"> {{ $horse->transporter ? $horse->transporter->name : "" }} </td>
                                <td class="text-center">{{ $horse->horse_number}} </td>
                                <td class="text-center">{{ $horse->horse_make ? $horse->horse_make->name : ""}}</td>
                                <td class="text-center">{{ $horse->horse_model ? $horse->horse_model->name : ""}}</td>
                                <td class="text-center"> {{$horse->registration_number}}</td>
                                <td class="text-center">{{$horse->fuels->sum('quantity')}} Litres</td>
                            </tr>

                        @endforeach
                        </tbody>
                    </table>
                   
                </main>
                @endif
             {{-- <center> <footer>{{$company->invoice_footer}}</footer></center>   --}}
            </div>
            <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
            <div></div>
        </div>
    </div>
</div>
