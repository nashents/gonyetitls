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
                                <a href="#" data-toggle="modal" data-target="#taxModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Tax</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Category
                                    </th>
                                    <th class="th-sm">Account
                                    </th>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Abbreviation
                                    </th>
                                    <th class="th-sm">Description
                                    </th>
                                    <th class="th-sm">Rate
                                    </th>
                                    <th class="th-sm">HSCode
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($taxes))
                                <tbody>
                                    @forelse ($taxes as $tax)
                                  <tr>
                                    <td>{{$tax->category}}</td>
                                    <td>{{$tax->account?->name}}</td>
                                    <td>{{$tax->name}}</td>
                                    <td>{{$tax->abbreviation}}</td>
                                    <td>{{$tax->description}}</td>
                                    <td>{{$tax->rate}}</td>
                                    <td>{{$tax->hs_code}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('taxes.show', $tax->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$tax->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#taxDeleteModal{{ $tax->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('taxes.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="6">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Tax Categories Found ....
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
                                    @if (isset($taxes))
                                        {{ $taxes->links() }} 
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

   
   <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="taxModal" taaccountdex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Tax <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Catgeory<span class="required" style="color: red">*</span></label>
                                <select  class="form-control" wire:model.debounce.300ms="category">
                                        <option value="">Select Category</option>
                                        <option value="VAT">VAT</option>
                                        <option value="PAYE">PAYE</option>
                                </select>
                                @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Accounts<span class="required" style="color: red">*</span></label>
                                <select  class="form-control" wire:model.debounce.300ms="account_id">
                                        <option value="">Select Account</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{$account->id}}">{{$account->name}}</option>
                                        @endforeach
                                </select>
                                @error('account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>               
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="eg Value Added Tax 15%" required>
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="account_number">Abbreviation<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="abbreviation" placeholder="eg VAT 15%" required>
                                @error('abbreviation') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>               
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Rate<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="rate" placeholder="eg 15" required>
                                @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="account_number">HS Code</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="hs_code" placeholder="Enter HS Code">
                                @error('hs_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>               
                    <div class="form-group">
                        <label for="name">Description</label>
                        <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="taxEditModal" taaccountdex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Tax <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
               
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Catgeory<span class="required" style="color: red">*</span></label>
                                <select  class="form-control" wire:model.debounce.300ms="category">
                                        <option value="">Select Category</option>
                                        <option value="VAT">VAT</option>
                                        <option value="PAYE">PAYE</option>
                                </select>
                                @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Accounts<span class="required" style="color: red">*</span></label>
                                <select  class="form-control" wire:model.debounce.300ms="account_id">
                                        <option value="">Select Account</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{$account->id}}">{{$account->name}}</option>
                                        @endforeach
                                </select>
                                @error('account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>               
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="eg Value Added Tax 15%" required>
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="account_number">Abbreviation<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="abbreviation" placeholder="eg VAT 15%" required>
                                @error('abbreviation') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>               
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Rate<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="rate" placeholder="eg 15" required>
                                @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="account_number">HS Code</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="hs_code" placeholder="Enter HS Code">
                                @error('hs_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>               
                    <div class="form-group">
                        <label for="name">Description</label>
                        <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

