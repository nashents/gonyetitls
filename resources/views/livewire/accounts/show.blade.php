<div>
    <div class="row mt-30">
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">
            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Account Details</a></li>
                <li role="presentation"><a href="#transactions" aria-controls="transactions" role="tab" data-toggle="tab">Transactions</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                <table class="table table-striped">
                    <tbody class="text-center line-height-35 ">
                        <tr>
                            <th class="w-10 text-center line-height-35">Account Type</th>
                            <td class="w-20 line-height-35">{{$account->account_type ?  $account->account_type->name : ""}}</td>
                        </tr> 
                        @if ($account->account_number)
                        <tr>
                            <th class="w-10 text-center line-height-35">Account#</th>
                            <td class="w-20 line-height-35">{{$account->account_number}}</td>
                        </tr> 
                        @endif
                        <tr>
                            <th class="w-10 text-center line-height-35">Name</th>
                            <td class="w-20 line-height-35">{{$account->name }}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Currency</th>
                            <td class="w-20 line-height-35">{{$account->currency ? $account->currency->name : ""}}</td>
                        </tr>
                        @if ($account->bank_account)
                        <tr>
                            <th class="w-10 text-center line-height-35">Bank</th>
                            <td class="w-20 line-height-35">
                              
                                {{$account->bank_account->name}} {{$account->bank_account->type}} {{$account->bank_account->account_number}}
                               
                            </td>
                        </tr>
                        @endif
                        @if ($account->customer)
                        <tr>
                            <th class="w-10 text-center line-height-35">Customer</th>
                            <td class="w-20 line-height-35">
                              
                                {{$account->customer->name}}
                               
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <th class="w-10 text-center line-height-35">Balance</th>
                            <td class="w-20 line-height-35">
                                @if ($account->balance)
                                {{$account->currency ? $account->currency->symbol : ""}}{{number_format($account->balance,2)}}
                                @else
                                {{$account->currency ? $account->currency->symbol : ""}}{{number_format(0,2)}}
                                @endif
                              </td>
                        </tr>
                        @if ($account->description)
                        <tr>
                            <th class="w-10 text-center line-height-35">Description</th>
                            <td class="w-20 line-height-35">{{$account->description }}</td>
                        </tr>
                        @endif
                        
                      
                    </tbody>
                </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="transactions">
                    @livewire('accounts.transactions', ['id' => $account->id])
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
    @section('extra-js')
    <script>
    $(document).ready( function () {
        $('#transactionsTable').DataTable();
    } );
    </script>
@endsection
    

</div>
