<?php

namespace App\Http\Controllers;

use App\Imports\BinsImport;
use App\Imports\RacksImport;
use App\Imports\TripsImport;
use App\Imports\TyresImport;
use Illuminate\Http\Request;
use App\Imports\AgentsImport;
use App\Imports\CargosImport;
use App\Imports\HorsesImport;
use App\Imports\BrokersImport;
use App\Imports\DriversImport;
use App\Imports\VendorsImport;
use App\Imports\TrailersImport;
use App\Imports\VehiclesImport;
use App\Exports\CompaniesImport;
use App\Imports\CountriesImport;
use App\Imports\CustomersImport;
use App\Imports\EmployeesImport;
use App\Imports\ProvincesImport;
use App\Imports\ConsigneesImport;
use App\Imports\InventoriesImport;
use App\Imports\DestinationsImport;
use App\Imports\TransportersImport;
use App\Imports\LoadingPointsImport;
use App\Imports\EmployeesLeaveImport;
use App\Imports\OffloadingPointsImport;
use Illuminate\Support\Facades\Session;

class ImportsController extends Controller
{

    
    public function importEmployees(Request $request){
        $this->validate($request,[
            'file' => 'required'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new EmployeesImport;
        $import->import($file);
        Session::flash('success','Employee(s) Imported Successfully!!');
        return redirect()->back();
    }
    public function importRacks(Request $request){
        $this->validate($request,[
            'file' => 'required'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new RacksImport;
        $import->import($file);
        Session::flash('success','Rack(s) Imported Successfully!!');
        return redirect()->back();
    }
    public function importBins(Request $request){
        $this->validate($request,[
            'file' => 'required'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new BinsImport;
        $import->import($file);
        Session::flash('success','Bin(s) Imported Successfully!!');
        return redirect()->back();
    }

    public function importTrailers(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new TrailersImport;
        $import->import($file);
        Session::flash('success','Trailer(s) Imported Successfully!!');
        return redirect()->back();
    }
   
    public function importTrips(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new TripsImport;
        $import->import($file);
        Session::flash('success','Trip(s) Imported Successfully!!');
        return redirect()->back();
    }
   
    public function importEmployeesLeave(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new EmployeesLeaveImport;
        $import->import($file);
        Session::flash('success','Employee Leave Data Imported Successfully!!');
        return redirect()->back();
    }

    public function importConsignees(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new ConsigneesImport;
        $import->import($file);
        Session::flash('success','Consignees Imported Successfully!!');
        return redirect()->back();
    }

    public function importProvinces(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new ProvincesImport;
        $import->import($file);
        Session::flash('success','Province(s) Imported Successfully!!');
        return redirect()->back();
    }

 
 
    public function importTyres(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new TyresImport;
        $import->import($file);
        Session::flash('success','Tyre(s) Imported Successfully!!');
        return redirect()->back();
    }
    public function importCountries(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new CountriesImport;
        $import->import($file);
        Session::flash('success','Countries Imported Successfully!!');
        return redirect()->back();
    }
    public function importCargos(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new CargosImport;
        $import->import($file);
        Session::flash('success','Cargo(s) Imported Successfully!!');
        return redirect()->back();
    }
    public function importDestinations(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new DestinationsImport;
        $import->import($file);
        Session::flash('success','Destination(s) Imported Successfully!!');
        return redirect()->back();
    }
    public function importLoadingPoints(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new LoadingPointsImport;
        $import->import($file);
        Session::flash('success','LoadingsPoint(s) Imported Successfully!!');
        return redirect()->back();
    }
    public function importOffloadingPoints(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new OffloadingPointsImport;
        $import->import($file);
        Session::flash('success','OffloadingsPoint(s) Imported Successfully!!');
        return redirect()->back();
    }

    public function importVehicles(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new VehiclesImport;
        $import->import($file);
        Session::flash('success','Vehicle(s) Imported Successfully!!');
        return redirect()->back();
    }
    public function importCustomers(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new CustomersImport;
        $import->import($file);
        Session::flash('success','Customer(s) Imported Successfully!!');
        return redirect()->back();
    }
    public function importAgents(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new AgentsImport;
        $import->import($file);
        Session::flash('success','Agent(s) Imported Successfully!!');
        return redirect()->back();
    }
    public function importTransporters(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new TransportersImport;
        $import->import($file);
        Session::flash('success','Transporter(s) Imported Successfully!!');
        return redirect()->back();
    }
    public function importHorses(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new HorsesImport;
        $import->import($file);
        Session::flash('success','Horse(s) Imported Successfully!!');
        return redirect()->back();
    }
    public function importDrivers(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new DriversImport;
        $import->import($file);
        Session::flash('success','Driver(s) Imported Successfully!!');
        return redirect()->back();
    }
    public function importVendors(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new VendorsImport;
        $import->import($file);
        Session::flash('success','Vendor(s) Imported Successfully!!');
        return redirect()->back();
    }
    public function importBrokers(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new BrokersImport;
        $import->import($file);
        Session::flash('success','Broker(s) Imported Successfully!!');
        return redirect()->back();
    }
    public function importCompanies(Request $request){
        $this->validate($request,[
            'file' => 'required|file|mimes:xls,xlsx'
        ]);
        $file = $request->file;
        ini_set('max_execution_time', 300);
        $import = new CompaniesImport;
        $import->import($file);
        Session::flash('success','Companies Imported Successfully!!');
        return redirect()->back();
    }
}
