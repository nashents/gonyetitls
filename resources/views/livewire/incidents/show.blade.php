
<div>
    <div class="row mt-30">
    <div class="col-md-10 col-md-offset-1">
        <ul class="nav nav-tabs nav-justified" role="tablist">
            <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Incident Details</a></li>
            <li role="presentation"><a href="#contacts" aria-controls="contacts" role="tab" data-toggle="tab">Contacts</a></li>
            <li role="presentation" ><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
            <li role="presentation" ><a href="#injuries" aria-controls="injuries" role="tab" data-toggle="tab">Injuries</a></li>
            <li role="presentation" ><a href="#damages" aria-controls="damages" role="tab" data-toggle="tab">Damages</a></li>
            <li role="presentation" ><a href="#others" aria-controls="others" role="tab" data-toggle="tab">Other Incidents</a></li>
        </ul>
        <div class="tab-content bg-white p-15">
            <div role="tabpanel" class="tab-pane active" id="basic">
                <table class="table table-striped">

                    <tbody class="text-center line-height-35 ">

                        <tr>
                            <th class="w-10 text-center line-height-35">Incident#</th>
                            <td class="w-20 line-height-35">{{ucfirst($incident->incident_number)}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">CreatedBy</th>
                            <td class="w-20 line-height-35">{{ucfirst($incident->user ? $incident->user->name : "")}} {{ucfirst($incident->user ? $incident->user->surname : "")}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Transporter</th>
                            <td class="w-20 line-height-35">{{$incident->transporter ? $incident->transporter->name : ""}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">MOT</th>
                            <td class="w-20 line-height-35">
                                @if (isset($incident->horse))
                                    Horse | {{ucfirst($incident->horse->horse_make ? $incident->horse->horse_make->name : "")}} {{ucfirst($incident->horse->horse_model ? $incident->horse->horse_model->name : "" )}} {{ucfirst($incident->horse ? $incident->horse->registration_number : "")}} {{ucfirst($incident->horse ? "| ".$incident->horse->fleet_number : "")}}
                                    @elseif(isset($incident->vehicle))
                                    Vehicle | {{ucfirst($incident->vehicle->vehicle_make ? $incident->vehicle->vehicle_make->name : "")}} {{ucfirst($incident->vehicle->vehicle_model ? $incident->vehicle->vehicle_model->name : "")}} {{ucfirst($incident->vehicle ? $incident->vehicle->registration_number : "")}} {{ucfirst($incident->vehicle ? "| ".$incident->vehicle->fleet_number : "")}}
                                    @elseif(isset($incident->trailer))
                                    Trailer | {{ucfirst($incident->trailer ? $incident->trailer->make : "")}} {{ucfirst($incident->trailer ? $incident->trailer->model : "")}} {{ucfirst($incident->trailer ? $incident->trailer->registration_number : "")}} {{ucfirst($incident->trailer ? "| ".$incident->trailer->fleet_number : "")}}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Incident Report For</th>
                            <td class="w-20 line-height-35">
                                @if ($incident->employee)
                                {{ucfirst($incident->employee ? $incident->employee->name : "")}} {{ucfirst($incident->employee ? $incident->employee->surname : "")}}
                                @elseif($incident->driver)
                                {{ucfirst($incident->driver->employee ? $incident->driver->employee->name : "")}} {{ucfirst($incident->driver->employee ? $incident->driver->employee->surname : "")}}        
                                @endif
                            </td>
                        </tr>
                        
                            <tr>
                                <th class="w-10 text-center line-height-35">Incident Date</th>
                                <td class="w-20 line-height-35">{{$incident->date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Report Date</th>
                                <td class="w-20 line-height-35">{{$incident->report_date}}</td>
                            </tr>
                           
                           
                            <tr>
                                <th class="w-10 text-center line-height-35">Destination</th>
                                <td class="w-20 line-height-35">{{$incident->destination->country ? $incident->destination->country->name : ""}} {{$incident->destination->city}}</td>
                            </tr>
                       
                            <tr>
                                <th class="w-10 text-center line-height-35">Location</th>
                                <td class="w-20 line-height-35">{{$incident->location}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Cargo</th>
                                <td class="w-20 line-height-35">{{$incident->cargo ? $incident->cargo->name : ""}}</td>
                            </tr>
                            @if ($incident->weight)
                            <tr>
                                <th class="w-10 text-center line-height-35">Weight</th>
                                <td class="w-20 line-height-35">{{$incident->weight."tons"}}</td>
                            </tr>
                            @endif
                            
                            @if ($incident->quantity)
                            <tr>
                                <th class="w-10 text-center line-height-35">Quantity</th>
                                <td class="w-20 line-height-35">{{$incident->quantity}} {{$incident->measurement ? $incident->measurement->name : ""}}</td>
                            </tr> 
                            @endif
                            
                            @if ($incident->litreage)
                            <tr>
                                <th class="w-10 text-center line-height-35">Litreage @ Ambient</th>
                                <td class="w-20 line-height-35">{{$incident->litreage}} {{$incident->measurement ? $incident->measurement->name : ""}}</td>
                            </tr>
                            @endif
                            
                            @if ($incident->litreage_at_20)
                            <tr>
                                <th class="w-10 text-center line-height-35">Litreage @ 20 Degrees</th>
                                <td class="w-20 line-height-35">{{$incident->litreage_at_20}} {{$incident->measurement ? $incident->measurement->name : ""}}</td>
                            </tr>
                            @endif
                            <tr>
                                <th class="w-10 text-center line-height-35">Occupation</th>
                                <td class="w-20 line-height-35">{{$incident->occupation}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Experience</th>
                                <td class="w-20 line-height-35">{{$incident->experience}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Person Controlling Activity at Time Of Occurrence</th>
                                <td class="w-20 line-height-35">{{$incident->controling_activity}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Media Coverage</th>
                                <td class="w-20 line-height-35">{{$incident->media_coverage}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Loss Potential</th>
                                <td class="w-20 line-height-35">{{$incident->loss_potential}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Probability of Occurrence</th>
                                <td class="w-20 line-height-35">{{$incident->occurance}}</td>
                            </tr>
                           
                            <tr>
                                <th class="w-10 text-center line-height-35">Event Description:</th>
                                <td class="w-20 line-height-35">{{$incident->description}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Immediate Causes:</th>
                                <td class="w-20 line-height-35">
                                    @if (isset($incident->immediate_causes))
                                        @if ($incident->immediate_causes->count()>0)
                                            @foreach ($incident->immediate_causes as $cause)
                                            <ol>{{$cause->loss ? $cause->loss->name : ""}}</ol>   
                                            @endforeach
                                        @endif
                                    @endif
                                
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Basic Causes:</th>
                                <td class="w-20 line-height-35">
                                    @php
                                        $basic_causes = App\Models\BasicCause::where('incident_id',$incident->id)->get();
                                    @endphp
                                   @if (isset($basic_causes))
                                        @if ($basic_causes->count()>0)
                                            @foreach ($basic_causes as $cause)
                                            <ol>{{$cause->loss ? $cause->loss->name : ""}}</ol>   
                                            @endforeach
                                        @endif
                                   @endif
                                    
                                    
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Corrective Actions:</th>
                                <td class="w-20 line-height-35">{{$incident->corrections}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Authorities on site?</th>
                                <td class="w-20 line-height-35">{{$incident->authorities}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Where to lodge police report:</th>
                                <td class="w-20 line-height-35">{{$incident->report}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Followup Dates</th>
                                <td class="w-20 line-height-35">
                                    @foreach ($incident->incident_dates as $date)
                                    {{$date->date}} <br>       
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Authorization</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{($incident->authorization == 'approved') ? 'success' : (($incident->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($incident->authorization == 'approved') ? 'approved' : (($incident->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{$incident->status == 1 ? "warning" : "success"}}">{{$incident->status == 1 ? "Open" : "Closed"}}</span></td>
                            </tr>
                    </tbody>
                </table>
               
            </div>
            <div role="tabpanel" class="tab-pane" id="contacts">
                @livewire('contacts.index', ['id' => $incident->id,'category' =>'incident'])
              </div>
              <div role="tabpanel" class="tab-pane" id="documents">
                @livewire('documents.index', ['id' => $incident->id,'category' =>'incident'])
              </div>
              <div role="tabpanel" class="tab-pane" id="injuries">
                @livewire('incidents.injuries', ['id' => $incident->id])
              </div>
              <div role="tabpanel" class="tab-pane" id="damages">
                @livewire('incidents.damages', ['id' => $incident->id])
              </div>
              <div role="tabpanel" class="tab-pane" id="others">
                @livewire('incidents.others', ['id' => $incident->id])
              </div>

            <!-- /.section-title -->
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
