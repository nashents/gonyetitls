<!doctype html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Purchase Order</title>
@include('includes.css')

</head>
<body style="font-family: FontAwesome; font-size: 14px;">

    <div class="container">
        <div class="card">
            <div class="card-body">
                <div id="invoice">
                    <div class="invoice overflow-auto">
                        <div style="margin-left: -30px; margin-right:-30px" >
                            <header style="margin-top:-45px; padding-bottom:10px"> 
                                <div class="row"  >
                                    <div class="col" style="margin-top:-15px;">
                                        <a href="#"><img src="{{asset('images/uploads/'.$company->logo)}}" width="200" alt=""></a>
                                    </div>
                                    <div class="col company-details" style="margin-top:-120px;">
                                        <h4 class="name" >
                                            <a target="_blank" href="#" style="color:  {{$company->color ? $company->color : "#000000" }}">
                                        {{$company->name}}
                                        </a>
                                        </h4>
                                        <div>{{$company->street_address}} {{$company->suburb}} <br>
                                            {{$company->city}}, {{$company->country}}</div>
                                        <div>
                                            {{$company->phonenumber}}
                                            @if ($company->second_phonenumber)
                                            | {{$company->second_phonenumber}}
                                            @endif
                                            @if ($company->third_phonenumber)
                                            | {{$company->third_phonenumber}}
                                            @endif
                                        </div>
                                      
                                        
                                        <div>{{$company->email}}</div>
                                        @if ($company->second_email)
                                        <div>{{$company->second_email}}</div>
                                        @endif
                                        @if ($company->third_email)
                                        <div>{{$company->third_email}}</div>
                                        <br>
                                        @endif
                                        <div>
                                          
                                                VAT No.: {{$company->vat_number}}
                                           
                                        </div>
                                        <div>
                                          
                                                TIN.: {{$company->tin_number}}
                                           
                                        </div>
                                    </div>
                                </div>
                              
                            </header>
                            <main>
                                <div class="row contacts" style="margin-bottom: 40px">
                                    <div class="col invoice-to">
                                        <div class="text-gray-light">Request From</div>
                                        <h4 class="to">{{$requisition->department ? $requisition->department->name : ""}}</h4>
                                    </div>
                                 
                                    <div class="col invoice-details"  style="margin-top:-120px;">
                                        <div class="date" style="padding-bottom: 3px"> <strong>Requisition No.:</strong> {{$requisition->requisition_number}}</div>
                                        <div class="date" style="padding-bottom: 3px"><strong>Date:</strong> {{$requisition->date}}</div>
                                        <div class="date" style="padding-bottom: 3px"><strong>Currency:</strong> {{$requisition->currency ? $requisition->currency->name : ""}}</div>
                                    </div>
                                </div>
                                <table class="table table-striped" style="margin-top: 50px;">
        
                                    <tbody>
                                        <tr>
                                            <th class="text-center"><strong>Requisition#</strong></th>
                                            <td class="text-center"> {{$requisition->requisition_number}}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><strong>CreatedBy</strong></th>
                                            <td class="text-center"> {{$requisition->user ? $requisition->user->name : ""}} {{$requisition->user ? $requisition->user->surname : ""}}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><strong>RequestedBy</strong></th>
                                            <td class="text-center"> 
                                                {{$requisition->employee ? $requisition->employee->name : ""}} {{$requisition->employee ? $requisition->employee->surname : ""}}
                                                <br>
                                                {{$requisition->department ? $requisition->department->name : ""}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><strong>Requisition For</strong></th>
                                            <td class="text-center"> {{$requisition->description}}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><strong>Date</strong></th>
                                            <td class="text-center"> {{$requisition->date}}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><strong>Currency</strong></th>
                                            <td class="text-center"> {{$requisition->currency ? $requisition->currency->name : ""}}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><strong>Total</strong></th>
                                            <td class="text-center"> {{$requisition->currency ? $requisition->currency->symbol : ""}}{{number_format($requisition->total,2)}}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><strong>Paid</strong></th>
                                            <td class="text-center"> {{$requisition->currency ? $requisition->currency->symbol : ""}}{{number_format($requisition->paid,2)}}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><strong>Status</strong></th>
                                            <td class="text-center"> <span class="label label-{{($requisition->status == 'Paid') ? 'success' : (($requisition->status == 'Partial') ? 'warning' : 'danger') }}">{{ $requisition->status }}</span></td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><strong>Authorization</strong></th>
                                            <td class="text-center"> <span class="badge bg-{{($requisition->authorization == 'approved') ? 'success' : (($requisition->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($requisition->authorization == 'approved') ? 'approved' : (($requisition->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><strong>Authorized By</strong></th>
                                            @php
                                                $authorized_by = App\Models\User::find($requisition->authorized_by_id);
                                            @endphp
                                            <td class="text-center"> 
                                                @if ($authorized_by)
                                                    {{$authorized_by->name}} {{$authorized_by->surname}}
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
            
                                </table>
                           
                               
                            </main>
                            <center> 
                                <footer style="position:fixed; bottom: 0px; left: 0px; right: 0px; ">
                                  
                                    <strong style="font-size: 18px;">Powered By Gonyeti</strong>    
                                </footer>
                            </center>  
                        </div>
                        <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                        <div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>