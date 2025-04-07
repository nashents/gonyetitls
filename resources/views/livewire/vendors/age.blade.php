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
                                <a href="#" wire:click="exportVendorsAgeExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportVendorsAgeCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportVendorsAgePDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            </div>
                        </div>
                        
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="vendorsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Vendor
                                    </th>
                                    <th class="th-sm">Start of Contract
                                    </th>
                                    <th class="th-sm">End of Contract
                                    </th>
                                    <th class="th-sm">Contract Duration
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($vendors->count()>0)
                                <tbody>
                                    @foreach ($vendors as $vendor)
                                  <tr>
                                    <td>{{$vendor->name}}</td>
                                    @php
                                        $contract = $vendor->contracts->where('status',1)->first();
                                    @endphp
                                    <td>{{$contract ? $contract->start_date : ""}}</td>
                                    <td>{{$contract ? $contract->end_date : ""}}</td>
                                    <td>
                                        @if (isset($contract))
                                        {{$contract->duration ? $contract->duration." Months" : ""}}
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                {{-- <li><a href="#"  wire:click="edit({{$vendor->id}})" ><i class="fa fa-refresh color-success"></i> Update</a></li> --}}
                                            </ul>
                                        </div>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="vendor_ageModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Update {{$vendor->registration_number ? $vendor->registration_number : ""}} Age <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="country">Year<span class="required" style="color: red">*</span></label>
                      <input type="date" wire:model.debounce.300ms="year" class="form-control" required>
                        @error('year') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
   



</div>

