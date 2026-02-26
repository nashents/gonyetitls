<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Application Details</a></li>
                <li role="presentation"><a href="#qualifications" aria-controls="qualifications" role="tab" data-toggle="tab">Qualifications</a></li>
                <li role="presentation"><a href="#checks" aria-controls="checks" role="tab" data-toggle="tab">Checks</a></li>
                <li role="presentation"><a href="#scores" aria-controls="scores" role="tab" data-toggle="tab">Stage Scores</a></li>
                <li role="presentation"><a href="#decisions" aria-controls="decisions" role="tab" data-toggle="tab">Stage Decisions</a></li>
                <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Application#</th>
                                <td class="w-20 line-height-35">{{$application->application_number}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Applied On</th>
                                <td class="w-20 line-height-35">{{$application->date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$application->user ? $application->user->name : ""}} {{$application->user ? $application->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedOn</th>
                                <td class="w-20 line-height-35">{{$application->created_at}}</td>
                            </tr>
                            
                          
                                <tr>
                                    <th class="w-10 text-center line-height-35">Fullname</th>
                                    <td class="w-20 line-height-35">{{$candidate->first_name}} {{$candidate->last_name}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Gender</th>
                                    <td class="w-20 line-height-35">{{$candidate->gender}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">DOB</th>
                                    <td class="w-20 line-height-35">{{$candidate->dob}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Age</th>
                                    <td class="w-20 line-height-35">{{$candidate->age ? $candidate->age."Year(s)" : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Phonenumber</th>
                                    <td class="w-20 line-height-35">{{$candidate->phone}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Email</th>
                                    <td class="w-20 line-height-35">{{$candidate->email}}</td>
                                </tr>
                              
                                <tr>
                                    <th class="w-10 text-center line-height-35">ID#</th>
                                    <td class="w-20 line-height-35">{{$candidate->national_id}}</td>
                                </tr>
                             
                                <tr>
                                    <th class="w-10 text-center line-height-35">License#</th>
                                    <td class="w-20 line-height-35">{{$candidate->drivers_license_number}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Experience</th>
                                    <td class="w-20 line-height-35">{{$candidate->years_experience ? $candidate->years_experience."Year(s)" : ""}}</td>
                                </tr>
                             
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="qualifications">
                    <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                            <th class="th-sm">Name
                            </th>
                            <th class="th-sm">Level
                            </th>
                            <th class="th-sm">Date Awarded
                            </th>
                            <th class="th-sm">Expiry
                            </th>
                            <th class="th-sm">Attachment
                            </th>
                            <th class="th-sm">Status
                            </th>
                            </tr>
                        </thead>
                        @if (isset($recruitment_qualifications))
                            <tbody>
                                @forelse ($recruitment_qualifications as $recruitment_qualification)
                                <tr>
                                <td>{{$recruitment_qualification->qualification ? $recruitment_qualification->qualification->name : ""}}</td>
                                <td>{{$recruitment_qualification->level}}</td>
                                <td>{{$recruitment_qualification->date_awarded}}</td>
                                <td>{{$recruitment_qualification->expires_at}}</td>
                                <td><a href="{{asset('myfiles/documents/'.$recruitment_qualification->certificate_path)}}" style="color: blue" target="_blank">{{$recruitment_qualification->certificate_path}}</a></td>
                                <td>{{$recruitment_qualification->status}}</td>
                            </tr>
                                @empty
                                    <tr>
                                    <td colspan="11">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Qualifications Found ....
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
                            @if (isset($qualifications))
                                @if ($qualifications->count()>0)
                                    {{ $qualifications->links() }} 
                                @endif
                            @endif 
                        </ul>
                    </nav> 
                </div> 
                <div role="tabpanel" class="tab-pane" id="checks">
                    <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                            <th class="th-sm">Stage
                            </th>
                            <th class="th-sm">Result
                            </th>
                            <th class="th-sm">Attachment
                            </th>
                            <th class="th-sm">Comments
                            </th>
                            </tr>
                        </thead>
                        @if (isset($checks))
                            <tbody>
                                @forelse ($checks as $check)
                                <tr>
                                    <td>{{$check->type}}</td>
                                    <td>{{$check->result}}</td>
                                    <td><a href="{{asset('myfiles/documents/'.$check->attachment_path)}}" style="color: blue" target="_blank">{{$check->attachment_path}}</a></td>
                                    <td>{{$check->comment}}</td>
                                </tr>
                                @empty
                                    <tr>
                                    <td colspan="11">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Checks Found ....
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
                            @if (isset($checks))
                                @if ($checks->count()>0)
                                    {{ $checks->links() }} 
                                @endif
                            @endif 
                        </ul>
                    </nav> 
                </div> 
                <div role="tabpanel" class="tab-pane" id="scores">
                      <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                            <th class="th-sm">Stage
                            </th>
                            <th class="th-sm">Criterion
                            </th>
                            <th class="th-sm">Weight
                            </th>
                            <th class="th-sm">Score
                            </th>
                            <th class="th-sm">Comments
                            </th>
                            </tr>
                        </thead>
                        @if (isset($scores))
                            <tbody>
                                @forelse ($scores as $score)
                                <tr>
                                    <td>{{$score->stage}}</td>
                                    <td>{{$score->criterion}}</td>
                                    <td>{{$score->weight}}</td>
                                    <td>{{$score->score_percent ? $score->score_percent."%" : ""}}</td>
                                    <td>{{$score->comment}}</td>
                                </tr>
                                @empty
                                    <tr>
                                    <td colspan="11">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Scores Found ....
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
                            @if (isset($scores))
                                @if ($scores->count()>0)
                                    {{ $scores->links() }} 
                                @endif
                            @endif 
                        </ul>
                    </nav> 
                </div> 
                <div role="tabpanel" class="tab-pane" id="decisions">
                    <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="th-sm">Stage</th>
                                <th class="th-sm">Decision</th>
                                <th class="th-sm">Comments</th>
                            </tr>
                        </thead>
                        @if (isset($decisions))
                            <tbody>
                                @forelse ($decisions as $decision)
                                    <tr>
                                        <td>{{$decision->stage}}</td>
                                        <td>{{$decision->decision}}</td>
                                        <td>{{$decision->comment}}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Decisions Found ....
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
                            @if (isset($decisions))
                                @if ($decisions->count()>0)
                                    {{ $decisions->links() }} 
                                @endif
                            @endif 
                        </ul>
                    </nav> 
                </div> 
                <div role="tabpanel" class="tab-pane" id="documents">
                  @livewire('documents.index', ['id' => $application->id,'category' => 'application'])
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
