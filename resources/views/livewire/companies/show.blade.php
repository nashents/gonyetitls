<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Company Details</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Name</th>
                                <td class="w-20 line-height-35">{{$company->name}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Type</th>
                                <td class="w-20 line-height-35">{{$company->type}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Plan</th>
                                <td class="w-20 line-height-35">{{$company->plan}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35">
                                    @if (App\Models\Currency::find($company->license_currency_id))
                                    {{App\Models\Currency::find($company->license_currency_id)->name}}
                                    @endif
                                 </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">License Fee</th>
                                <td class="w-20 line-height-35">
                                    @if (App\Models\Currency::find($company->license_currency_id))
                                    {{App\Models\Currency::find($company->license_currency_id)->symbol}}{{number_format($company->fee,2)}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Email</th>
                                <td class="w-20 line-height-35">{{$company->email}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Phonenumber</th>
                                <td class="w-20 line-height-35">{{$company->phonenumber}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">No Reply</th>
                                <td class="w-20 line-height-35">{{$company->noreply}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">URL</th>
                                <td class="w-20 line-height-35">{{$company->url}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Country</th>
                                <td class="w-20 line-height-35">{{$company->country}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">City</th>
                                <td class="w-20 line-height-35">{{$company->city}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Suburb</th>
                                <td class="w-20 line-height-35">{{$company->suburb}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Street Address</th>
                                <td class="w-20 line-height-35">{{$company->street_address}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Role</th>
                                <td class="w-20 line-height-35">
                                    @if (isset($company->user->roles))
                                    @foreach ($company->user->roles as $role)
                                        {{ $role->name }}
                                    @endforeach
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{$company->status == 1 ? "success" : "danger"}}">{{$company->status == 1 ? "Active" : "Suspended"}}</span></td>
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
