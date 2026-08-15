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

                            <div class="panel-title">
                                <a href="{{route('debit_notes.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Debit Note</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="debit_notesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">Debit Note#
                                    </th>
                                    <th class="th-sm">Bill#
                                    </th>
                                    <th class="th-sm">Vendor
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Authorization
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($debit_notes->count()>0)
                                <tbody>
                                    @foreach ($debit_notes as $debit_note)
                                  <tr>
                                    <td>{{$debit_note->debit_note_number}}</td>
                                    <td>
                                        @if ($debit_note->bill)
                                        <a href="{{ route('bills.show',$debit_note->bill->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">{{$debit_note->bill ? $debit_note->bill->bill_number : ""}} </a>
                                        @endif
                                    </td>
                                    <td>{{$debit_note->vendor ? $debit_note->vendor->name : "undefined"}}</td>
                                    <td>{{$debit_note->date}}</td>
                                    <td>{{$debit_note->currency ? $debit_note->currency->name : "undefined"}}</td>
                                    <td>
                                        @if ($debit_note->total)
                                             {{$debit_note->currency ? $debit_note->currency->symbol : ""}}{{number_format($debit_note->total,2)}}
                                        @endif
                                    </td>
                                    <td><span class="badge bg-{{($debit_note->authorization == 'approved') ? 'success' : (($debit_note->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($debit_note->authorization == 'approved') ? 'approved' : (($debit_note->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('debit_notes.show',$debit_note->id)}}"  ><i class="fas fa-eye color-default"></i> View</a></li>
                                                @if ($debit_note->authorization == "approved")
                                                <li><a href="{{route('debit_notes.preview',$debit_note->id)}}"  ><i class="fas fa-file-invoice color-primary"></i> Preview</a></li>
                                                @endif
                                                <li><a href="{{route('debit_notes.edit',$debit_note->id)}}"  ><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#debit_noteDeleteModal{{ $debit_note->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('debit_notes.delete')
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
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>
</div>
