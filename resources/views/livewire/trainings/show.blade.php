<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Training Record Details</a></li>
                <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$training->user ? $training->user->name : ""}} {{$training->user ? $training->user->surname : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Participant</th>
                                <td class="w-20 line-height-35">
                                    @if ($training->employee)
                                        {{$training->employee ? $training->employee->name : ""}}   {{$training->employee ? $training->employee->surname : ""}}
                                    @endif
                                    @if ($training->driver)
                                        {{$training->driver->employee ? $training->driver->employee->name : ""}}    {{$training->driver->employee ? $training->driver->employee->surname : ""}}
                                    @endif
                                </td>
                            </tr>
                          
                            <tr>
                                <th class="w-10 text-center line-height-35">Training Item</th>
                                <td class="w-20 line-height-35">
                                    {{$training->training_item ? $training->training_item->name : ""}}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">When</th>
                                <td class="w-20 line-height-35">
                                    @if (!$training->day_event)
                                        {{ \Carbon\Carbon::parse($training->from)->format('d M Y, H:i') }} - 
                                        {{ \Carbon\Carbon::parse($training->to)->format('d M Y, H:i') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($training->date)->format('d M Y') }}
                                    @endif
                                </td>
                            </tr> 
                          
                            <tr>
                                <th class="w-10 text-center line-height-35">Comments</th>
                                <td class="w-20 line-height-35">
                                   {{$training->comments}}
                                </td>
                            </tr> 
                         
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="documents">
                 @livewire('documents.index', ['id' => $training->id, 'category' => "training"])
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
