@extends('layouts.app')

@section('extra-css')
    @if (Auth::user()->employee->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
    @elseif (Auth::user()->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
    @endif
@endsection
@section('title')
    Audit Report | @if (Auth::user()->employee->company)
    {{Auth::user()->employee->company->name}}
    @elseif (Auth::user()->company)
    {{Auth::user()->company->name}}
    @endif
@endsection

@section('body-id')
<body class="top-navbar-fixed">
@endsection

@section('content')

    <div class="main-page">
        <div class="container-fluid">
            <div class="row page-title-div">
              @include('includes.top-message')
            </div>
            <div class="row breadcrumb-div">
                <div class="col-md-6">
                    <ul class="breadcrumb">
                        <li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i> Home</a></li>
                        <li><a href="{{route('sheq_audits.index')}}"><i class="fa fa-clipboard-check"></i> Audits</a></li>
                        <li class="active">Report {{$sheq_audit->audit_number}}</li>
                    </ul>
                </div>
                <div class="col-md-6 text-right">
                    <button onclick="window.print()" class="btn btn-default"><i class="fa fa-print"></i> Print</button>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    <h4>Audit Report: {{$sheq_audit->audit_number}}
                                        @if ($sheq_audit->status == 'planned')
                                            <span class="label label-default">Planned</span>
                                        @elseif ($sheq_audit->status == 'in_progress')
                                            <span class="label label-info">In Progress</span>
                                        @elseif ($sheq_audit->status == 'completed')
                                            <span class="label label-primary">Completed</span>
                                        @else
                                            <span class="label label-success">Closed</span>
                                        @endif
                                    </h4>
                                </div>
                            </div>
                            <div class="panel-body p-20">
                                <div class="row">
                                    <div class="col-md-3"><strong>Checklist:</strong> {{$sheq_audit->template->name ?? '-'}}</div>
                                    <div class="col-md-3"><strong>Standard:</strong> {{$sheq_audit->template->standard ?? '-'}}</div>
                                    <div class="col-md-3"><strong>Department:</strong> {{$sheq_audit->department->name ?? '-'}}</div>
                                    <div class="col-md-3"><strong>Type:</strong> {{ucwords($sheq_audit->audit_type ?? '-')}}</div>
                                </div>
                                <div class="row" style="margin-top:10px">
                                    <div class="col-md-3"><strong>Lead Auditor:</strong> {{$sheq_audit->lead_auditor ? $sheq_audit->lead_auditor->name.' '.$sheq_audit->lead_auditor->surname : '-'}}</div>
                                    <div class="col-md-3"><strong>Auditee:</strong> {{$sheq_audit->auditee ? $sheq_audit->auditee->name.' '.$sheq_audit->auditee->surname : '-'}}</div>
                                    <div class="col-md-3"><strong>Scheduled:</strong> {{$sheq_audit->scheduled_date ? \Carbon\Carbon::parse($sheq_audit->scheduled_date)->format('d M Y') : '-'}}</div>
                                    <div class="col-md-3"><strong>Completed:</strong> {{$sheq_audit->completed_date ? \Carbon\Carbon::parse($sheq_audit->completed_date)->format('d M Y') : '-'}}</div>
                                </div>
                                <hr>

                                <h4>Score Summary</h4>
                                <table class="table table-striped table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Element</th>
                                            <th>Actual Mark</th>
                                            <th>Possible Mark</th>
                                            <th>% Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sheq_audit->template->sections as $section)
                                            @php
                                                $possible = $section->possibleTotal();
                                                $actual = $sheq_audit->sectionActualTotal($section);
                                            @endphp
                                            <tr>
                                                <td>{{$section->code}} {{$section->title}}</td>
                                                <td>{{$actual}}</td>
                                                <td>{{$possible}}</td>
                                                <td>{{$possible > 0 ? round(($actual/$possible)*100,1) : 0}}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>TOTAL</th>
                                            <th>{{$sheq_audit->actualTotal()}}</th>
                                            <th>{{$sheq_audit->possibleTotal()}}</th>
                                            <th>{{$sheq_audit->percentageScore()}}%</th>
                                        </tr>
                                    </tfoot>
                                </table>

                                <div class="row" style="margin-top:10px">
                                    <div class="col-md-4"><strong>Non-Conformities (NC):</strong> <span class="label label-danger">{{$sheq_audit->nonConformityCount()}}</span></div>
                                    <div class="col-md-4"><strong>Opportunities for Improvement (OFI):</strong> <span class="label label-warning">{{$sheq_audit->ofiCount()}}</span></div>
                                    <div class="col-md-4"><strong>Overall Score:</strong> <span class="label label-primary">{{$sheq_audit->percentageScore()}}%</span></div>
                                </div>
                                <hr>

                                <h4>Findings</h4>
                                <table class="table table-striped table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Ref</th>
                                            <th>Requirement</th>
                                            <th>Grading</th>
                                            <th>Findings</th>
                                            <th>Objective Evidence</th>
                                            <th>Marks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($sheq_audit->findings() as $response)
                                            <tr>
                                                <td>{{$response->item->section->code ?? ''}} {{$response->item->code ?? ''}}</td>
                                                <td>{{ \Illuminate\Support\Str::limit($response->item->requirement ?? '', 120) }}</td>
                                                <td>
                                                    @if ($response->grading == 'NC')
                                                        <span class="label label-danger">NC</span>
                                                    @elseif ($response->grading == 'OFI')
                                                        <span class="label label-warning">OFI</span>
                                                    @else
                                                        <span class="label label-success">C</span>
                                                    @endif
                                                </td>
                                                <td>{{$response->findings}}</td>
                                                <td>{{$response->evidence}}</td>
                                                <td>{{$response->actual_mark}} / {{$response->item->possible_mark ?? 0}}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6"><center>No findings recorded.</center></td></tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                <h4>Action Plan</h4>
                                <table class="table table-striped table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Number</th>
                                            <th>Action</th>
                                            <th>Responsible</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($sheq_audit->actions as $action)
                                            <tr>
                                                <td>{{$action->action_number}}</td>
                                                <td>{{$action->title}}</td>
                                                <td>{{$action->employee ? $action->employee->name.' '.$action->employee->surname : '-'}}</td>
                                                <td>{{$action->due_date ? \Carbon\Carbon::parse($action->due_date)->format('d M Y') : '-'}}</td>
                                                <td>{{ucwords(str_replace('_',' ',$action->status))}}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5"><center>No actions raised.</center></td></tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                @if ($sheq_audit->summary)
                                    <h4>Summary</h4>
                                    <p>{{$sheq_audit->summary}}</p>
                                @endif
                                @if ($sheq_audit->recommendations)
                                    <h4>Recommendations</h4>
                                    <p>{{$sheq_audit->recommendations}}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

@endsection
