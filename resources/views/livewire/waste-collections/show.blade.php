<div>
    <div class="row mt-30">
        <div class="col-md-10 col-md-offset-1">
            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Waste Collection Record</a></li>
                <li role="presentation" ><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">WasteCollection#</th>
                                <td class="w-20 line-height-35">{{$waste_collection->waste_collection_number}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$waste_collection->user ? $waste_collection->user->name : ""}} {{$waste_collection->user ? $waste_collection->user->surname : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedOn</th>
                                <td class="w-20 line-height-35">{{$waste_collection->created_at}}</td>
                            </tr>
                        </tbody>
                    </table>
                    <table  class="table  table-spaymented table-bordered table-sm table-responsive" cellspacing="0" width="100%" style=" width:100%; height:100%;">
                         <caption>Waste Collection Items</caption>
                        <thead>
                            <tr>
                                <th class="th-sm">Waste Type
                                </th>
                                <th class="th-sm">Additional Info
                                </th>
                                <th class="th-sm">Qty
                                </th>
                                <th class="th-sm">Collection
                                </th>
                            </tr>
                        </thead>
                        @if (isset($waste_collection_items))
                            <tbody>
                                @forelse ($waste_collection_items as $waste_collection_item)
                                    <tr>
                                        <td>{{$waste_collection_item->waste_type ? $waste_collection_item->waste_type->name : ""}} {{$waste_collection_item->waste_type ? $waste_collection_item->waste_type->category : ""}} {{$waste_collection_item->waste_type ? $waste_collection_item->waste_type->composition : ""}}</td>
                                        <td>{{$waste_collection_item->description}}</td>
                                        <td>{{$waste_collection_item->qty}} {{$waste_collection_item->unit_of_measure}}</td>
                                        <td>
                                            <small><strong>CollectedBy: </strong> {{$waste_collection_item->employee ? $waste_collection_item->employee->name : ""}} {{$waste_collection_item->employee ? $waste_collection_item->employee->surname : ""}}</small>
                                            <small><strong>CollectedOn: </strong> {{$waste_collection_item->date}}</small>
                                        </td>
                                       
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Collection Items Recorded....
                                            </div>
                                        </td>
                                    </tr>  
                                @endforelse
                            </tbody>
                        @else
                            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                        @endif
                    </table>
                </div>
               <div role="tabpanel" class="tab-pane" id="documents">
                    @livewire('documents.index', ['id' => $waste_collection->id,'category'=>'waste_collection'])
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
    </div>
</div>
