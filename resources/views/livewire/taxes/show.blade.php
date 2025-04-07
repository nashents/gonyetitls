<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Cargo Details</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$tax->user ? $tax->user->name : ""}} {{$tax->user ? $tax->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Name</th>
                                <td class="w-20 line-height-35">{{$tax->name}}</td>
                            </tr>
                          
                                <tr>
                                    <th class="w-10 text-center line-height-35">Abbreviation</th>
                                    <td class="w-20 line-height-35">{{$tax->abbreviation}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Tax Number</th>
                                    <td class="w-20 line-height-35">{{$tax->tax_number}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Show tax number on invoices</th>
                                    <td class="w-20 line-height-35">{{$tax->show_tax_number}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Tax Recoverable</th>
                                    <td class="w-20 line-height-35">{{$tax->tax_recoverable}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Compound Tax</th>
                                    <td class="w-20 line-height-35">{{$tax->compound_tax}}</td>
                                </tr>
                             
                                <tr>
                                    <th class="w-10 text-center line-height-35">Description</th>
                                    <td class="w-20 line-height-35">{{$tax->description}}</td>
                                </tr>
                             
                        </tbody>
                    </table>
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
