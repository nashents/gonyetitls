<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Expense Details</a></li>
                <li role="presentation"><a href="#bills" aria-controls="bills" role="tab" data-toggle="tab">Billed Expenses</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$expense->user ? $expense->user->name : ""}} {{$expense->user ? $expense->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Account Type</th>
                                <td class="w-20 line-height-35">{{$expense->account ? $expense->account->account_type->name : ""}}</td>
                            </tr>
                          
                                <tr>
                                    <th class="w-10 text-center line-height-35">Category</th>
                                    <td class="w-20 line-height-35">{{$expense->account ? $expense->account->name : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Type</th>
                                    <td class="w-20 line-height-35">{{$expense->type}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Name</th>
                                    <td class="w-20 line-height-35">{{$expense->name}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Currency</th>
                                    <td class="w-20 line-height-35">{{$expense->currency ? $expense->currency->name : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Amount</th>
                                    <td class="w-20 line-height-35">
                                        @if ($expense->amount)
                                            {{number_format($expense->amount,2)}}    
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Frequency</th>
                                    <td class="w-20 line-height-35">{{$expense->frequency}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Description</th>
                                    <td class="w-20 line-height-35">{{$expense->description}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Status</th>
                                    <td class="w-20 line-height-35"><span class="badge bg-{{$expense->status == 1 ? "success" : "danger"}}">{{$expense->status == 1 ? "Active" : "Inactive"}}</span></td>
                                </tr>
                             
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="bills">
                  @livewire('expenses.bills', ['id' => $expense->id])
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
