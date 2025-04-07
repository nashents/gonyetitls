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
                                <a href="#" wire:click="exportDebtorsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportDebtorsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportDebtorsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search trips...">
                                </div>
                            </div>
                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Customer
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">0-30 Days
                                    </th>
                                    <th class="th-sm">31-60 Days
                                    </th>
                                    <th class="th-sm">61-90 Days
                                    </th>
                                    <th class="th-sm">>90 Days
                                    </th>
                                   
                                  </tr>
                                </thead>
                                @if (isset($debtors))
                                <tbody>
                                    @php
                                        $thirtyDaysAgo = \Carbon\Carbon::now()->subDays(30);
                                        $thirtyOneDaysAgo = \Carbon\Carbon::now()->subDays(31);
                                        $sixtyDaysAgo = \Carbon\Carbon::now()->subDays(60);
                                        $sixtyOneDaysAgo = \Carbon\Carbon::now()->subDays(61);
                                        $ninetyDaysAgo = \Carbon\Carbon::now()->subDays(90);
                                    @endphp
                                    @forelse ($debtors as $debtor)
                                        <tr>
                                        <td>{{$debtor->name}}</td>
                                        <td>
                                              @foreach ($currencies as $currency)
                                                @php
                                                    $balance = $debtor->invoices->where('currency_id',$currency->id)->where('balance','!=','')->where('balance','!=', Null)->sum('balance');
                                                @endphp
                                                @if (isset($balance) && $balance > 0)
                                                {{ $currency->name }} {{ $currency->symbol }}{{number_format($balance,2)}} <br>
                                                @endif
                                                @endforeach
                                        </td>
                                        <td>
                                              @foreach ($currencies as $currency)
                                                @php
                                                    $balance = $debtor->invoices->where('currency_id',$currency->id)->where('balance','!=','')->where('balance','!=', Null)->where('created_at', '>=', $thirtyDaysAgo)->sum('balance');
                                                @endphp
                                                @if (isset($balance) && $balance > 0)
                                                {{ $currency->name }} {{ $currency->symbol }}{{number_format($balance,2)}} <br>
                                                @endif
                                                @endforeach
                                        </td>
                                       
                                        <td>
                                            @foreach ($currencies as $currency)
                                                @php
                                                    $balance = $debtor->invoices->where('currency_id',$currency->id)->where('balance','!=','')->where('balance','!=', Null)->whereBetween('created_at', [$sixtyDaysAgo, $thirtyOneDaysAgo])->sum('balance');
                                                @endphp
                                                @if (isset($balance) && $balance > 0)
                                                {{ $currency->name }} {{ $currency->symbol }}{{number_format($balance,2)}} <br>
                                                @endif
                                                @endforeach
                                        </td>
                                       
                                            <td>
                                                @foreach ($currencies as $currency)
                                                  @php
                                                      $balance = $debtor->invoices->where('currency_id',$currency->id)->where('balance','!=','')->where('balance','!=', Null)->whereBetween('created_at', [$ninetyDaysAgo, $sixtyOneDaysAgo])->sum('balance');
                                                  @endphp
                                                  @if (isset($balance) && $balance > 0)
                                                  {{ $currency->name }} {{ $currency->symbol }}{{number_format($balance,2)}} <br>
                                                  @endif
                                                  @endforeach
                                          </td>
                                       
                                            <td>
                                                @foreach ($currencies as $currency)
                                                  @php
                                                      $balance = $debtor->invoices->where('currency_id',$currency->id)->where('balance','!=','')->where('balance','!=', Null)->where('created_at', '<', $ninetyDaysAgo)->sum('balance');
                                                  @endphp
                                                  @if (isset($balance) && $balance > 0)
                                                  {{ $currency->name }} {{ $currency->symbol }}{{number_format($balance,2)}} <br>
                                                  @endif
                                                  @endforeach
                                          </td>
                                       
                                        </tr>
                                  @empty
                                  <tr>
                                    <td colspan="8">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Debtors Found ....
                                        </div>
                                       
                                    </td>
                                  </tr>  
                                @endforelse
                                </tbody>
                                @else
                                <tr>
                                    <td colspan="8">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Debtors Found ....
                                        </div>
                                       
                                    </td>
                                  </tr>  
                                 @endif
                              </table>
                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($debtors))
                                        {{ $debtors->links() }} 
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

