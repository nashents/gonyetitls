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
                                  </tr>
                                </thead>
                                @if ($debit_notes->count()>0)
                                <tbody>
                                    @foreach ($debit_notes as $debit_note)
                                  <tr>
                                    <td>{{$debit_note->debit_note_number}}</td>
                                    <td>
                                        @if ($debit_note->bill)
                                        {{$debit_note->bill ? $debit_note->bill->bill_number : ""}}
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
