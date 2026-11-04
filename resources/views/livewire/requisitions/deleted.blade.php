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
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">Type</span>
                                        <select wire:model="type_filter" class="form-control">
                                            <option value="">All Requisitions</option>
                                            <option value="po_requisition">PO Requisitions</option>
                                            <option value="payment_requisition">Payment Requisitions</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3" style="float: right">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search deleted requisitions...">
                                    </div>
                                </div>
                            </div>
                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Requisition#</th>
                                    <th class="th-sm">Employee</th>
                                    <th class="th-sm">Items</th>
                                    <th class="th-sm">Narration</th>
                                    <th class="th-sm">Date</th>
                                    <th class="th-sm">Total</th>
                                    <th class="th-sm">Auth</th>
                                    <th class="th-sm">Deleted By</th>
                                    <th class="th-sm">Actions</th>
                                  </tr>
                                </thead>
                                <tbody>
                                    @forelse ($requisitions as $requisition)
                                    @php
                                        $deletedAudit = $requisition->audits->first();
                                    @endphp
                                  <tr wire:key="deleted-requisition-row-{{ $requisition->id }}"
                                        @if($requisition->type == 'po_requisition')
                                            style="background-color:#e8f4fd;border-left:6px solid #17a2b8;"
                                        @else
                                            style="background-color:#eafaf1; border-left:6px solid #28a745;"
                                        @endif>
                                    <td>
                                        {{ucfirst($requisition->requisition_number)}} <br>
                                        <small class="text-muted">
                                            <strong>Type:</strong>
                                            @if ($requisition->type == "po_requisition")
                                                PO Requisition
                                            @else
                                                Payment Requisition
                                            @endif <br>
                                            <strong>CreatedBy:</strong> {{$requisition->user?->name}} {{$requisition->user?->surname}} <br>
                                            <strong>CreatedOn:</strong> {{$requisition->created_at}}
                                        </small>
                                    </td>
                                    <td>
                                        {{$requisition->employee?->name}} {{$requisition->employee?->surname}}
                                        <br>
                                        <small><strong><i>{{$requisition->department?->name}}</i></strong></small>
                                    </td>
                                    <td>
                                        <small>
                                        @foreach ($requisition->requisition_items as $requisition_item)
                                            <strong>{{$loop->iteration}})</strong>
                                            @if ($requisition_item->expense)
                                                {{$requisition_item->expense->name}}
                                            @elseif ($requisition_item->allowance)
                                                {{$requisition_item->allowance->name}}
                                            @elseif ($requisition_item->product)
                                                {{$requisition_item->product->brand?->name}} {{$requisition_item->product->name}}
                                            @endif
                                            {{$requisition_item->qty ? " X (".$requisition_item->qty.")" : ""}}
                                            @if ($requisition_item->amount)
                                                @ {{$requisition_item->currency?->symbol}}{{number_format($requisition_item->amount,2)}}
                                            @endif
                                            <br>
                                        @endforeach
                                        </small>
                                    </td>
                                    <td>
                                        {{ $requisition->subject ? "Subject: ".$requisition->subject : "" }}
                                        @if ($requisition->trip)
                                            <br>Trip: {{ $requisition->trip->trip_number }}
                                        @elseif ($requisition->booking)
                                            <br>Booking: {{ $requisition->booking->booking_number }}
                                        @endif
                                        @if ($requisition->description)
                                            <br><small>{{ $requisition->description }}</small>
                                        @endif
                                    </td>
                                    <td>{{$requisition->date}}</td>
                                    <td>
                                        {{$requisition->currency?->name}} {{$requisition->currency?->symbol}}{{number_format($requisition->total ?? 0,2)}}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{($requisition->authorization == 'approved') ? 'success' : (($requisition->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($requisition->authorization == 'approved') ? 'approved' : (($requisition->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                    </td>
                                    <td>
                                        {{$deletedAudit?->user?->name}} {{$deletedAudit?->user?->surname}}
                                        <br>
                                        <small>{{$requisition->deleted_at}}</small>
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click.prevent="restore({{$requisition->id}})"><i class="fas fa-undo color-success"></i> Restore</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="9">
                                        <div style="text-align:center; color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Deleted Requisitions Found ....
                                        </div>
                                    </td>
                                  </tr>
                                  @endforelse
                                </tbody>
                              </table>
                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    {{ $requisitions->links() }}
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="requisitionRestoreModal" tabindex="-1" role="dialog" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to restore this Requisition?</strong> </center>
                </div>
                <form wire:submit.prevent="update()">
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fas fa-undo"></i> Restore</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
