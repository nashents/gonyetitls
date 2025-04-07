<div>
    <div class="row mt-30">
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">

            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active">
                <table class="table table-striped">
                    <tbody class="text-center line-height-35 ">
                        <tr>
                            <th class="w-10 text-center line-height-35">Inspection#</th>
                            <td class="w-20 line-height-35">{{$inspection->inspection_number}} </td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Status</th>
                            <td class="w-20 line-height-35"><span class="badge bg-{{$inspection->status == 1 ? "warning" : "success"}}">{{$inspection->status == 1 ? "Open" : "Closed"}}</span></td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Comments</th>
                            <td class="w-20 line-height-35">{{$inspection->comments}} </td>
                        </tr>
                        <hr>
                    </tbody>
                </table>
                <form wire:submit.prevent="save()">
                {{-- @foreach ($inspection_groups as $inspection_group) --}}
                <table id="inspectionsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                    <caption><strong>{{$service_type ? $service_type->name : ""}}</strong></caption>
                    <thead >
                     <tr>
                        <th class="th-sm">Service Type
                        </th>
                        <th class="th-sm">Actions
                        </th>
                      </tr>
                    </thead>

                    <tbody>
                        {{-- @foreach ($inputs as $key => $value) --}}
                        @if (isset($inspection_services))
                        @if ($inspection_services->count()>0)
                       @foreach ($inspection_services as $key => $inspection_service)

                       @if ($inspection_service->inspection_type)
                       <tr>

                        <td>
                            <small><strong>{{$inspection_service->inspection_group ? $inspection_service->inspection_group->name : ""}}</strong></small>  <br>
                            {{$inspection_service->inspection_type->name}}
                            <input type="hidden" wire:model.debounce.300ms="inspection_type_id.{{$inspection_service->inspection_type->id}}" value="{{$inspection_service->inspection_type->id}}">
                        <br>

                         {{-- @if ($status[$key] == "red" || $status[$key] == "yellow") --}}



                         <div class="row">
                            <div  class="col-md-6">
                         <div class="form-group">
                                <label for="exampleInputEmail13">Comments</label>
                               <textarea  wire:model.debounce.300ms="comments.{{$inspection_service->inspection_type->id}}" wire:key="{{ $inspection_service->inspection_type->id }}" class="form-control" cols="15" rows="3"></textarea>
                            </div>
                        </div>
                        </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputEmail13">Cost</label>
                                        <input type="text" wire:model.debounce.300ms="cost.{{$inspection_service->inspection_type->id}}" wire:key="{{ $inspection_service->inspection_type->id }}" class="form-control" >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputEmail13">Hours</label>
                                        <input type="text" wire:model.debounce.300ms="hours.{{$inspection_service->inspection_type->id}}" wire:key="{{ $inspection_service->inspection_type->id }}" class="form-control" >
                                    </div>
                                </div>
                                <!-- /.col-md-6 -->
                            </div>

                        {{-- @endif --}}
                        </td>
                        <td>
                            <div class="mb-10">
                                <input type="radio" wire:model.debounce.300ms="status.{{$inspection_service->inspection_type->id}}" wire:key="{{ $inspection_service->inspection_type->id }}" value="{{$green}}"  class="line-style" required/>
                                <label for="one" class="radio-label">Safe</label>
                            </div>
                            <div class="mb-10">
                                <input type="radio" wire:model.debounce.300ms="status.{{$inspection_service->inspection_type->id}}"wire:key="{{ $inspection_service->inspection_type->id }}" value="{{$yellow}}" class="line-style" required/>
                                <label for="two" class="radio-label">Warning</label>
                            </div>
                            <div class="mb-10">
                                <input type="radio" wire:model.debounce.300ms="status.{{$inspection_service->inspection_type->id}}"wire:key="{{ $inspection_service->inspection_type->id }}"  value="{{$red}}" class="line-style" required/>
                                <label for="three" class="radio-label">Danger</label>
                            </div>
                        </td>

                      </tr>
                       @endif
             


                      {{-- @endforeach --}}
                      @endforeach
                      @endif
                      @endif
                    </tbody>

                  </table>
                  {{-- @endforeach --}}
                  <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                            <button type="submit"  class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Save</button>
                        </div>
                    </div>
                    </div>
                </form>
                </div>
            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>


</div>
