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
                                <a href="#" wire:click="exportDriversAgeExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportDriversAgeCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportDriversAgePDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            </div>
                        </div>
                        
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="driversTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Transporter
                                    </th>
                                    <th class="th-sm">Driver
                                    </th>
                                    <th class="th-sm">Date of Employement
                                    </th>
                                    <th class="th-sm">Total Years Employed 
                                    </th>
                                    <th class="th-sm">End of Employement
                                    </th>
                                    <th class="th-sm">Years Worked
                                    </th>
                                 
                                  </tr>
                                </thead>
                                @if ($drivers->count()>0)
                                <tbody>
                                    @foreach ($drivers as $driver)
                                  <tr>
                                    <td>{{$driver->transporter ? $driver->transporter->name : ""}}</td>
                                    <td>{{$driver->employee ? $driver->employee->name : ""}} {{$driver->employee ? $driver->employee->surname : ""}}</td>
                                    <td>{{$driver->employee ? $driver->employee->start_date : ""}}</td>
                                    @php
                                        $pattern = '/^\d{4}-\d{2}-\d{2}$/';
                                        $today = Carbon\Carbon::today();

                                        if (isset($driver->employee)) {
                                            if ((preg_match($pattern, $driver->employee->start_date)) ){
                                            $start_date = Carbon\Carbon::parse($driver->employee->start_date);
                                            $yearsDifference = $start_date->diffInYears($today);
                                            }else {
                                                $yearsDifference = "";
                                            }

                                            if ((preg_match($pattern, $driver->employee->end_date)) ){
                                                $end_date = Carbon\Carbon::parse($driver->employee->end_date);
                                                $yearsOfEmployeementDifference = $start_date->diffInYears($end_date);
                                            }else {
                                                $yearsOfEmployeementDifference = "";
                                            }
                                        }else {
                                            $yearsDifference = "";
                                            $yearsOfEmployeementDifference = "";
                                        }
                                     
                                    @endphp
                                    <td>{{ $yearsDifference ? $yearsDifference." Year(s)" : ""}}</td>
                                    <td>{{$driver->employee ? $driver->employee->end_date : "" }}</td>
                                    <td>{{ $yearsOfEmployeementDifference ? $yearsOfEmployeementDifference." Year(s)" : ""}}</td>
                                   
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="driver_ageModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Update {{$driver->employee ? $driver->employee->name : ""}} Age <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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

