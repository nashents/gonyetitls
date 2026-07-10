<?php

namespace App\Http\Controllers;

class AuditLogController extends Controller
{
    public function index()
    {
        return view('audit_logs.index');
    }
}
