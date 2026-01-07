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
                                <a href="" data-toggle="modal" data-target="#customerModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Customer</a>
                                <a href="" data-toggle="modal" data-target="#customersImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                                <a href="#" wire:click="exportCustomersExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportCustomersCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportCustomersPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search customers...">
                                </div>
                              
                            </div>
                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Customer#
                                    </th>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Email
                                    </th>
                                    <th class="th-sm">Phonenumber
                                    </th>
                                    <th class="th-sm">VAT/TIN#
                                    </th>
                                    <th class="th-sm">Address
                                    </th>
                                    <th class="th-sm">Invoice(s) Bal
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($customers))
                                <tbody>
                                    @forelse ($customers as $customer)
                                  <tr>
                                    <td>
                                        {{$customer->customer_number}}
                                        @if ($customer->custom_ref)
                                        <br>
                                            <small>Custom Ref:{{$customer->custom_ref}}</small>
                                        @endif 
                                    </td>
                                    <td>
                                        {{$customer->name}}
                                    </td>
                                    <td>{{$customer->email}}</td>
                                    <td>{{$customer->phonenumber}}</td>
                                    <td>{{$customer->vat_number}}{{$customer->tin_number ? " / ".$customer->tin_number : ""}}</td>
                                    <td>{{$customer->street_address}} {{$customer->suburb}} {{$customer->city}} {{$customer->country}}</td>
                                    <td>
                                        @foreach ($currencies as $currency)
                                            @php
                                                $balance = App\Models\Invoice::where('customer_id',$customer->id)
                                                                             ->where('currency_id',$currency->id)->sum('balance');
                                            @endphp
                                            @if (isset($balance) && $balance > 0)
                                            {{ $currency->name }} {{ $currency->symbol }}{{number_format($balance,2)}} <br>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td><span class="badge bg-{{$customer->status == 1 ? "success" : "danger"}}">{{$customer->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('customers.show', $customer->id) }}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$customer->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#customerDeleteModal{{ $customer->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('customers.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="8">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Customers Found ....
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
                                    @if (isset($customers))
                                        {{ $customers->links() }} 
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="customersImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Customers <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form action="{{route('customers.import')}}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Upload Customer(s) Excel File</label>
                        <input type="file" class="form-control" name="file" placeholder="Upload Customer File" >
                        @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button  onClick="this.form.submit(); this.disabled=true; this.value='Sending…'; "  class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="customerModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Customer <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required />
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Initials</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="initials" placeholder="Enter Quotation/Invoice Initials"/>
                                @error('initials') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Custom Ref</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="custom_ref" placeholder="Enter Custom Reference #"/>
                                @error('custom_ref') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                  
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" wire:model.debounce.300ms="email" placeholder="Enter Email"  />
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="phonenumber">Mobile Number</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Mobile Number"  />
                                @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="worknumber">Landline</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="worknumber" placeholder="Enter Landline" >
                                @error('worknumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                   
             
                    <h5 class="underline mt-30">Contacts Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="contact_name.0" placeholder="Enter Name"  />
                                @error('contact_name.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Surname</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="contact_surname.0" placeholder="Enter Surname"  />
                                @error('contact_surname.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" wire:model.debounce.300ms="contact_email.0" placeholder="Enter Email" />
                                @error('contact_email.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="phonenumber">Phonenumber</label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="contact_phonenumber.0" placeholder="Enter Mobile Number" />
                                @error('contact_phonenumber.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="department">Department</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="department.0" placeholder="Enter Department" />
                                @error('department.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @foreach ($contacts_inputs as $key => $value)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="contact_name.{{ $value }}" placeholder="Enter Name"  />
                                @error('contact_name.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Surname</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="contact_surname.{{ $value }}" placeholder="Enter Surname" />
                                @error('contact_surname.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" wire:model.debounce.300ms="contact_email.{{ $value }}" placeholder="Enter Email"/>
                                @error('contact_email.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="phonenumber">Phonenumber</label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="contact_phonenumber.{{ $value }}" placeholder="Enter Mobile Number"  />
                                @error('contact_phonenumber.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="department">Department<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="department.{{ $value }}" placeholder="Enter Department" required />
                                @error('department.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for=""></label>
                                <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="contactsRemove({{$key}})"> <i class="fa fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="contactsAdd({{$o}})"> <i class="fa fa-plus"></i> Contact</button>
                            </div>
                        </div>
                    </div>
                    <h5 class="underline mt-30">Billing Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Currency</label>
                               <select class="form-control" wire:model.debounce.300ms="currency_id">
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                      
                                    @endforeach
                               </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tin_number">Allowable Loss Percentage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="allowable_loss_percentage" placeholder="Enter Allowable Loss % For Trips" >
                                @error('allowable_loss_percentage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                       
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vat_number">VAT Number</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="vat_number" placeholder="Enter Vat Number" >
                                @error('vat_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tin_number">TIN Number</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="tin_number" placeholder="Enter Tin Number" >
                                @error('tin_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="country" placeholder="Enter Country"  />
                                @error('country') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="city" placeholder="Enter City" />
                                @error('city') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Suburb</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="suburb" placeholder="Enter Suburb"  />
                                @error('suburb') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="street_address">Street Address</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="street_address" placeholder="Enter Street Address" />
                                @error('street_address') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <h5 class="underline mt-n">Upload Files(<i>Company Profile, Tax Clearance</i>)</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="title.0" placeholder="Enter File Title eg Identity Card" />
                                @error('title.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="file">File</label>
                                <input type="file" class="form-control" wire:model.debounce.300ms="file.0"  placeholder="Upload File " />
                                @error('file.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="expires_at">Expiry Date</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="expires_at.0" placeholder="dd/mm/yy" />
                                @error('expires_at.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <!-- /.col-md-6 -->
                    </div>
                    @foreach ($inputs as $key => $value)
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="title.{{$value}}" placeholder="Enter File Title eg Identity Card"/>
                                @error('title.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="file">File</label>
                                <input type="file" class="form-control" wire:model.debounce.300ms="file.{{$value}}"  placeholder="Upload File "/>
                                @error('file.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="file">Expiry Date</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="expires_at.{{$value}}"  placeholder="dd/mm/yy"/>
                                @error('expires_at.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for=""></label>
                                <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                            </div>
                        </div>
                        <!-- /.col-md-6 -->
                    </div>
                    @endforeach
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> File</button>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="customerEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Customer <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required />
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Initials</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="initials" placeholder="Enter Quotation/Invoice Initials"/>
                                @error('initials') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Custom Ref</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="custom_ref" placeholder="Enter Custom Reference #"/>
                                @error('custom_ref') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" wire:model.debounce.300ms="email" placeholder="Enter Email" />
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="phonenumber">Mobile Number</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Mobile Number" />
                                @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="worknumber">Landline</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="worknumber" placeholder="Enter Landline" >
                                @error('worknumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <h5 class="underline mt-30">Billing Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Currency</label>
                               <select class="form-control" wire:model.debounce.300ms="currency_id">
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                      
                                    @endforeach
                               </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tin_number">Allowable Loss Percentage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="allowable_loss_percentage" placeholder="Enter Allowable Loss % For Trips" >
                                @error('allowable_loss_percentage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                       
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vat_number">VAT Number</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="vat_number" placeholder="Enter Vat Number" >
                                @error('vat_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tin_number">TIN Number</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="tin_number" placeholder="Enter Tin Number" >
                                @error('tin_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="country" placeholder="Enter Country" />
                                @error('country') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="city" placeholder="Enter City" />
                                @error('city') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Suburb</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="suburb" placeholder="Enter Suburb"  />
                                @error('suburb') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="street_address">Street Address</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="street_address" placeholder="Enter Street Address"/>
                                @error('street_address') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
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



</div>

