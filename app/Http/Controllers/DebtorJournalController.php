<?php

namespace App\Http\Controllers;

class DebtorJournalController extends Controller
{
    public function index()
    {
        return view('debtor_journals.index');
    }
}
