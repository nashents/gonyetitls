
<div>
    <style>
        /* Quotation = warning, Sales Order = success; border is a deeper shade of the row colour */
        table.quotations-table > tbody > tr.row-quote > td { background-color: #fcf8e3 !important; border-top: 1px solid #f0ad4e !important; border-bottom: 1px solid #f0ad4e !important; }
        table.quotations-table > tbody > tr.row-quote > td:first-child { border-left: 4px solid #f0ad4e !important; }
        table.quotations-table > tbody > tr.row-quote > td:last-child { border-right: 1px solid #f0ad4e !important; }
        table.quotations-table > tbody > tr.row-so > td { background-color: #dff0d8 !important; border-top: 1px solid #5cb85c !important; border-bottom: 1px solid #5cb85c !important; }
        table.quotations-table > tbody > tr.row-so > td:first-child { border-left: 4px solid #5cb85c !important; }
        table.quotations-table > tbody > tr.row-so > td:last-child { border-right: 1px solid #5cb85c !important; }
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
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="panel-title">
                                <div class="row">
                                        <div class="col-lg-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                Show
                                                </span>
                                                <select wire:model="document_type" class="form-control" aria-label="...">
                                                    <option value="all">All (Quotations &amp; Sales Orders)</option>
                                                    <option value="quotation">Quotations Only</option>
                                                    <option value="sales_order">Sales Orders Only</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                Filter By
                                                </span>
                                                <select wire:model.debounce.300ms="quotation_filter" class="form-control" aria-label="..." >
                                                    <option value="created_at">Quotation Created At</option>
                                                    <option value="date">Quotation Date</option>
                                                    <option value="sales_order_date">Sales Order Date</option>
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
                                <a href="#" wire:click.prevent="exportQuotationsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click.prevent="exportQuotationsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click.prevent="exportQuotationsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> 
                            </div>
                           
                            
                            <div class="col-md-3" style="float: right; padding-right:0px; ">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search quotations...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive quotations-table" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">Quotation#
                                    </th>
                                    <th class="th-sm">CreatedBy
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
                                    <th class="th-sm">Add Cost
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($quotations))
                                <tbody>
                                    @forelse ($quotations as $quotation)
                                  <tr class="{{ $quotation->isSalesOrder() ? 'row-so' : 'row-quote' }}" wire:key="{{ '6da1c4-95-'. ($loop->parent?->index ?? 'r') . '-' . $loop->index . '-' . ($quotation->id ?? '') }}">
                                    @php
                                        $expiry = $quotation->expiry;
                                        $now = new DateTime();
                                        $datetime2 = new DateTime($expiry);
                                    @endphp
                                    <td>
                                        <span class="label label-warning">Quote</span> {{$quotation->quotation_number}}
                                        @if ($quotation->isSalesOrder())
                                            <br>
                                            <span class="label label-success">SO</span> {{$quotation->sales_order_number}}
                                            @if ($quotation->sales_order_ref)
                                                <br><small><strong>SO Ref:</strong> {{$quotation->sales_order_ref}}</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td>{{$quotation->user ? $quotation->user->name : ""}} {{$quotation->user ? $quotation->user->surname : ""}}</td>
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
                                        @if ($quotation->additional_costs)
                                            @php
                                                $total_cost = $quotation->additional_costs->sum('total');
                                            @endphp
                                            @if (!empty($total_cost) && isset($total_cost))
                                                {{$quotation->currency ? $quotation->currency->symbol : ""}}{{number_format($total_cost,2)}}
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if ($quotation->total)
                                        {{$quotation->currency ? $quotation->currency->symbol : ""}}{{number_format($quotation->total,2)}}
                                        @endif
                                    </td>
                                      <td><span class="badge bg-{{$quotation->status == True ? "warning" : "success" }}">{{$quotation->status == True ? 'open' : "closed" }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('quotations.show',$quotation->id)}}"  ><i class="fa fa-eye color-default"></i>View</a></li>
                                                @if ($quotation->isSalesOrder())
                                                    <li><a href="{{route('quotations.preview',$quotation->id)}}"  ><i class="fa fa-file-invoice color-warning"></i> Quote Preview</a></li>
                                                    <li><a href="{{route('quotations.preview',['id' => $quotation->id, 'document' => 'sales_order'])}}"  ><i class="fa fa-file-invoice color-success"></i> SO Preview</a></li>
                                                @else
                                                    <li><a href="{{route('quotations.preview',$quotation->id)}}"  ><i class="fa fa-file-invoice color-primary"></i> Preview</a></li>
                                                    <li><a href="#" wire:click.prevent="convertToSalesOrderShow({{$quotation->id}})"  ><i class="fa fa-exchange color-success"></i> Convert to Sales Order</a></li>
                                                @endif
                                                @if ($quotation->status == True)
                                                    <li><a href="#" wire:click.prevent="closeQuoteShow({{$quotation->id}})"  ><i class="fa fa-remove color-active"></i> Close Quote</a></li>
                                                    @if ($quotation->trips_count == 0 && !$quotation->isSalesOrder())
                                                        <li><a href="{{route('quotations.edit',$quotation->id)}}"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                        <li><a href="#" data-toggle="modal" data-target="#quotationDeleteModal{{ $quotation->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                    @endif
                                                @endif
                                                 
                                            </ul>
                                        </div>
                                        @include('quotations.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="12">
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



        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="closeQuoteModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-window-close"></i> Close Quotation<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="closeQuotation()" >
                    <div class="modal-body">
                        <p>Are you sure you want to close quotation {{$quotation?->quotation_number}}</p>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
                </div>
            </div>
        </div>

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="convertQuoteModal" tabindex="-1" role="dialog" aria-labelledby="convertQuoteLabel" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="convertQuoteLabel"><i class="fa fa-exchange"></i> Convert Quotation to Sales Order<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="convertToSalesOrder()" >
                    <div class="modal-body">
                        <p>Convert quotation <strong>{{$convert_quotation_number}}</strong>@if ($convert_customer) for <strong>{{$convert_customer}}</strong>@endif to a sales order.</p>
                        <div class="form-group">
                            <label>Sales Order#</label>
                            <input type="text" class="form-control" value="{{$sales_order_number_preview}}" disabled>
                            <small class="text-muted">Auto generated on conversion.</small>
                        </div>
                        <div class="form-group">
                            <label for="sales_order_ref">Sales Order Reference <small>(optional)</small></label>
                            <input type="text" id="sales_order_ref" class="form-control" wire:model.debounce.300ms="sales_order_ref" placeholder="Enter custom sales order reference">
                            @error('sales_order_ref') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="sales_order_date">Sales Order Date<span class="required" style="color: red">*</span></label>
                            <input type="date" id="sales_order_date" class="form-control" wire:model.defer="sales_order_date" required>
                            @error('sales_order_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" wire:loading.attr="disabled" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-exchange"></i>Convert</button>
                        </div>
                    </div>
                </form>
                </div>
            </div>
        </div>


</div>

