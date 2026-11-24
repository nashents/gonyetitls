<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SheqAuditTemplate;

class SheqAuditTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('sheq_audit_templates.index');
    }

    /**
     * Display the specified resource (template builder).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sheq_audit_template = SheqAuditTemplate::findOrFail($id);
        // Everyone gets the read-only checklist; ?mode=build opens the
        // builder, but only for the creator / is_admin / Super Admin.
        $mode = request('mode') === 'build' && $sheq_audit_template->canBeBuiltBy(auth()->user()) ? 'build' : 'view';

        return view('sheq_audit_templates.show',[
            'sheq_audit_template' => $sheq_audit_template,
            'mode' => $mode,
        ]);
    }
}
