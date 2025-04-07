<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Retread Details</a></li>
                <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$retread->user ? $retread->user->name : ""}} {{$retread->user ? $retread->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Retread#</th>
                                <td class="w-20 line-height-35">{{$retread->retread_number}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Vendor</th>
                                <td class="w-20 line-height-35">{{$retread->vendor ? $retread->vendor->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Expense Account</th>
                                <td class="w-20 line-height-35">{{$retread->account ? $retread->account->name : ""}}</td>
                            </tr>
                          
                          
                            <tr>
                                <th class="w-10 text-center line-height-35">Date</th>
                                <td class="w-20 line-height-35">{{$retread->date}}</td>
                            </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Tyres</th>
                                    <td class="w-20 line-height-35">
                                        @if (isset($retread->retread_tyres))
                                            @foreach ($retread->retread_tyres as $retread_tyre)
                                                @if ($retread_tyre->tyre)
                                                    @php
                                                        $tyre = $retread_tyre->tyre;
                                                    @endphp
                                                    {{$tyre->tyre_number}}  {{$tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}} - {{$tyre->serial_number}} ({{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}})
                                                    <br>
                                                @endif
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                               
                                <tr>
                                    <th class="w-10 text-center line-height-35">Total</th>
                                    <td class="w-20 line-height-35">{{$retread->currency ? $retread->currency->name : ""}} {{$retread->currency ? $retread->currency->symbol : ""}}{{$retread->total ? number_format($retread->total,2) : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Comments</th>
                                    <td class="w-20 line-height-35">{{$retread->description}} </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Status</th>
                                    <td class="w-20 line-height-35"><span class="badge bg-{{$retread->status == 1 ? "warning" : "success"}}">{{$retread->status == 1 ? "Open" : "Closed"}}</span></td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Authorization</th>
                                    <td class="w-20 line-height-35"><span class="badge bg-{{($retread->authorization == 'approved') ? 'success' : (($retread->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($retread->authorization == 'approved') ? 'approved' : (($retread->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                </tr>
                                
                             
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="documents">
                  @livewire('documents.index', ['id' => $retread->id,'category'=>'retread'])
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
