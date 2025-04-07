
<div>
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
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="panel-title">
                                <div class="row">
                                        <div class="col-lg-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                Filter By
                                                </span>
                                                <select wire:model.debounce.300ms="quotation_filter" class="form-control" aria-label="..." >
                                                    <option value="created_at">Quotation Created At</option>
                                                    <option value="date">Quoation Date</option>
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                             
                                        <div class="col-lg-2" style="margin-right: 7px; margin-left:-15px;">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                        From
                                        </span>
                                        <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-lg-2" style="margin-left: 7px">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                        To
                                        </span>
                                        <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                </div>
                            </div>
                            <div class="panel-title" style="margin-top:10px; margin-left:-1px">
                                <a href="{{route('quotations.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Quotation</a>
                                <a href="#" wire:click="exportQuotationsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportQuotationsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportQuotationsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> 
                            </div>
                           
                            
                            <div class="col-md-3" style="float: right; padding-right:0px; ">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search quotations...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">Quotation#
                                    </th>
                                    <th class="th-sm">Customer
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Expiry
                                    </th>
                                    <th class="th-sm">Ccy
                                    </th>
                                    <th class="th-sm">Subtotal
                                    </th>
                                    <th class="th-sm">Tax Amt
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($quotations))
                                <tbody>
                                    @forelse ($quotations as $quotation)
                                  <tr>
                                    @php
                                        $expiry = $quotation->expiry;
                                        $now = new DateTime();
                                        $datetime2 = new DateTime($expiry);
                                    @endphp
                                    <td>{{$quotation->quotation_number}}</td>
                                    <td>{{$quotation->customer ? $quotation->customer->name : ""}}</td>
                                    <td>
                                       {{$quotation->date}}
                                    </td>
                                    <td><span class="label label-{{$now < $expiry ? 'success' : 'danger' }}">{{$quotation->expiry}}</span></td>
                                    <td>{{$quotation->currency ? $quotation->currency->name : ""}}</td>  
                                    <td>
                                        @if ($quotation->subtotal)
                                        {{$quotation->currency ? $quotation->currency->symbol : ""}}{{number_format($quotation->subtotal,2)}}
                                        @endif
                                    </td>
                                    <td>
                                        {{$quotation->currency ? $quotation->currency->name : ""}} {{$quotation->currency ? $quotation->currency->symbol : ""}}{{number_format($quotation->tax_amount ? $quotation->tax_amount : 0, 2)}}
                                      </td>
                                    <td>
                                        @if ($quotation->total)
                                        {{$quotation->currency ? $quotation->currency->symbol : ""}}{{number_format($quotation->total,2)}}
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('quotations.show',$quotation->id)}}"  ><i class="fa fa-eye color-default"></i>View</a></li>
                                                <li><a href="{{route('quotations.preview',$quotation->id)}}"  ><i class="fa fa-file-invoice color-primary"></i> Preview</a></li>
                                                @if ($quotation->trips->count()>0)

                                                @else   
                                                <li><a href="{{route('quotations.edit',$quotation->id)}}" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#quotationDeleteModal{{ $quotation->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                        @include('quotations.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="9">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Quotations Found ....
                                        </div>
                                       
                                    </td>
                                  </tr>  
                                @endforelse
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>

                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($quotations))
                                        {{ $quotations->links() }} 
                                    @endif 
                                </ul>
                            </nav>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>






</div>

