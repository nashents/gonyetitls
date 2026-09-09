<?php

namespace App\Http\Controllers;

use App\Models\CompanyIntegration;
use App\Http\Requests\StoreCompanyIntegrationRequest;
use App\Http\Requests\UpdateCompanyIntegrationRequest;
use Illuminate\Support\Facades\Auth;

class CompanyIntegrationController extends Controller
{
    public function __construct()
    {
        // Integrations (Cartrack/EzyTrack/FanTracker/Pinpoint credentials
        // etc.) is admin-only — not just hidden from the sidebar/links.
        $this->middleware(function ($request, $next) {
            $user = Auth::user();

            abort_unless($user && ($user->is_admin() || $user->isSuperAdmin()), 403);

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('company_integrations.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCompanyIntegrationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompanyIntegrationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CompanyIntegration  $companyIntegration
     * @return \Illuminate\Http\Response
     */
    public function show(CompanyIntegration $companyIntegration)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CompanyIntegration  $companyIntegration
     * @return \Illuminate\Http\Response
     */
    public function edit(CompanyIntegration $companyIntegration)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCompanyIntegrationRequest  $request
     * @param  \App\Models\CompanyIntegration  $companyIntegration
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCompanyIntegrationRequest $request, CompanyIntegration $companyIntegration)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CompanyIntegration  $companyIntegration
     * @return \Illuminate\Http\Response
     */
    public function destroy(CompanyIntegration $companyIntegration)
    {
        //
    }
}
