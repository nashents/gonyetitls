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
                                <a href="#" data-toggle="modal" data-target="#salary_itemModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Salary Item</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="salary_itemsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Type
                                    </th>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Percentage
                                    </th>
                                    <th class="th-sm">Amount
                                    </th>
                                    <th class="th-sm">Description
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($salary_items->count()>0)
                                <tbody>
                                    @foreach ($salary_items as $salary_item)
                                  <tr>
                                    <td>{{$salary_item->type}}</td>
                                    <td>{{ucfirst($salary_item->name)}}</td>
                                    <td>
                                        @if ($salary_item->percentage)
                                        {{$salary_item->percentage}}%        
                                        @endif
                                    </td>
                                    <td>
                                        @if ($salary_item->amount)
                                             {{number_format($salary_item->amount,2)}}        
                                        @endif
                                    </td>
                                    <td>{{$salary_item->description}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('salary_items.show', $salary_item->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                @if ($salary_item->user_id == Null)
                                                    <li><a href="#"  wire:click="edit({{$salary_item->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#salary_itemDeleteModal{{ $salary_item->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                        @include('salary_items.delete')
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="salary_itemModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Salary Item <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Types<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="type" class="form-control" required >
                                    <option value="">Select Item Type</option>
                                    <option value="Earnings">Earnings</option>
                                    <option value="Deductions">Deductions</option>
                                </select>
                                @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required />
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <h5 class="underline mt-10">Select item calculation.</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Calculate By<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="calculate_by" class="form-control" required >
                                    <option value="">Select Option</option>
                                    <option value="currency">$</option>
                                    <option value="percentage">%</option>
                                </select>
                                @error('calculate_by') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Calculate On<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="calculate_on" class="form-control" required >
                                    <option value="">Select Option</option>
                                    <option value="gross">Gross</option>
                                    <option value="net">Net</option>
                                </select>
                                @error('calculate_on') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            @if ($calculate_by == "percentage")
                                <div class="form-group">
                                    <label for="name">Percentage</label>
                                    <input type="number"  step="any" class="form-control" wire:model.debounce.300ms="percentage" placeholder="Enter Percentage"  />
                                    @error('percentage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            @elseif($calculate_by == "currency")
                            <div class="form-group">
                                <label for="name">Amount</label>
                                <input type="number"  step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount"  />
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Description</label>
                              <textarea class="form-control" wire:model.debounce.300ms="description" placeholder="Enter Description" cols="30" rows="3"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="salary_itemEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit salary_item <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="form-group">
                        <label for="country">Types<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="type" class="form-control" required >
                           <option value="">Select Item Type</option>
                           <option value="Earnings">Earnings</option>
                           <option value="Deductions">Deductions</option>
                       </select>
                        @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required />
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Percentage</label>
                        <input type="number"  step="any" class="form-control" wire:model.debounce.300ms="percentage" placeholder="Enter Percentage"  />
                        @error('percentage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Amount</label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" />
                        @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Description</label>
                      <textarea class="form-control" wire:model.debounce.300ms="description" placeholder="Enter Description" cols="30" rows="5"></textarea>
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

