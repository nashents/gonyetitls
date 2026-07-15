<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SheqAudit;

class SheqAuditController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('sheq_audits.index');
    }

    /**
     * Conduct (execute) the audit.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function conduct($id)
    {
        $sheq_audit = SheqAudit::find($id);
        return view('sheq_audits.conduct',[
            'sheq_audit' => $sheq_audit
        ]);
    }

    /**
     * Display the audit report.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sheq_audit = SheqAudit::with(['template.sections.items','responses','department','lead_auditor','auditee','actions'])->find($id);
        return view('sheq_audits.show',[
            'sheq_audit' => $sheq_audit
        ]);
    }
}
