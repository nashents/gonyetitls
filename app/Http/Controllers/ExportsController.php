<?php

namespace App\Http\Controllers;

use App\Exports\TripsExport;
use App\Exports\TyresExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use App\Exports\CargosExport;
use App\Exports\HorsesExport;
use App\Exports\BrokersExport;
use App\Exports\DriversExport;
use App\Exports\VendorsExport;
use App\Exports\BookingsExport;
use App\Exports\TrailersExport;
use App\Exports\VehiclesExport;
use App\Exports\CountriesExport;
use App\Exports\CustomersExport;
use App\Exports\EmployeesExport;
use App\Exports\ConsigneesExport;
use App\Exports\ContainersExport;
use App\Exports\AllocationsExport;
use App\Exports\AssignmentsExport;
use App\Exports\DestinationsExport;
use App\Exports\FuelRequestsExport;
use App\Exports\LoadingPointsExport;
use App\Exports\OffloadingPointsExport;

class ExportsController extends Controller
{
    public function exportFuelRequestsCSV(Excel $excel){

        return $excel->download(new FuelRequestsExport, 'fuel_requests.csv', Excel::CSV);
    }
    public function exportFuelRequestsPDF(Excel $excel){

        return $excel->download(new FuelRequestsExport, 'fuel_requests.pdf', Excel::DOMPDF);
    }
    public function exportFuelRequestsExcel(Excel $excel){

        return $excel->download(new FuelRequestsExport, 'fuel_requests.xlsx');
    }
 
    public function exportCountriesCSV(Excel $excel){

        return $excel->download(new CountriesExport, 'countries.csv', Excel::CSV);
    }
    public function exportCountriesPDF(Excel $excel){

        return $excel->download(new CountriesExport, 'countries.pdf', Excel::DOMPDF);
    }
    public function exportCountriesExcel(Excel $excel){

        return $excel->download(new CountriesExport, 'countries.xlsx');
    }

    public function exportCargosCSV(Excel $excel){

        return $excel->download(new CargosExport, 'cargos.csv', Excel::CSV);
    }
    public function exportCargosPDF(Excel $excel){

        return $excel->download(new CargosExport, 'cargos.pdf', Excel::DOMPDF);
    }
    public function exportCargosExcel(Excel $excel){

        return $excel->download(new CargosExport, 'cargos.xlsx');
    }

    public function exportBookingsCSV(Excel $excel){

        return $excel->download(new BookingsExport, 'garage_bookings.csv', Excel::CSV);
    }
    public function exportBookingsPDF(Excel $excel){

        return $excel->download(new BookingsExport, 'garage_bookings.pdf', Excel::DOMPDF);
    }
    public function exportBookingsExcel(Excel $excel){

        return $excel->download(new BookingsExport, 'garage_bookings.xlsx');
    }

    public function exportConsigneesCSV(Excel $excel){

        return $excel->download(new ConsigneesExport, 'consignees.csv', Excel::CSV);
    }
    public function exportConsigneesPDF(Excel $excel){

        return $excel->download(new ConsigneesExport, 'consignees.pdf', Excel::DOMPDF);
    }
    public function exportConsigneesExcel(Excel $excel){

        return $excel->download(new ConsigneesExport, 'consignees.xlsx');
    }
 
    public function exportDestinationsCSV(Excel $excel){

        return $excel->download(new DestinationsExport, 'destinations.csv', Excel::CSV);
    }
    public function exportDestinationsPDF(Excel $excel){

        return $excel->download(new DestinationsExport, 'destinations.pdf', Excel::DOMPDF);
    }
    public function exportDestinationsExcel(Excel $excel){

        return $excel->download(new DestinationsExport, 'destinations.xlsx');
    }
 
    public function exportLoadingPointsCSV(Excel $excel){

        return $excel->download(new LoadingPointsExport, 'loading_points.csv', Excel::CSV);
    }
    public function exportLoadingPointsPDF(Excel $excel){

        return $excel->download(new LoadingPointsExport, 'loading_points.pdf', Excel::DOMPDF);
    }
    public function exportLoadingPointsExcel(Excel $excel){

        return $excel->download(new LoadingPointsExport, 'loading_points.xlsx');
    }

    public function exportOffloadingPointsCSV(Excel $excel){

        return $excel->download(new OffloadingPointsExport, 'offloading_points.csv', Excel::CSV);
    }
    public function exportOffloadingPointsPDF(Excel $excel){

        return $excel->download(new OffloadingPointsExport, 'offloading_points.pdf', Excel::DOMPDF);
    }
    public function exportOffloadingPointsExcel(Excel $excel){

        return $excel->download(new OffloadingPointsExport, 'offloading_points.xlsx');
    }

    public function exportAllocationsCSV(Excel $excel){

        return $excel->download(new AllocationsExport, 'allocations.csv', Excel::CSV);
    }
    public function exportAllocationsPDF(Excel $excel){

        return $excel->download(new AllocationsExport, 'allocations.pdf', Excel::DOMPDF);
    }
    public function exportAllocationsExcel(Excel $excel){

        return $excel->download(new AllocationsExport, 'allocations.xlsx');
    }

    public function exportContainersCSV(Excel $excel){

        return $excel->download(new ContainersExport, 'containers.csv', Excel::CSV);
    }
    public function exportContainersPDF(Excel $excel){

        return $excel->download(new ContainersExport, 'containers.pdf', Excel::DOMPDF);
    }
    public function exportContainersExcel(Excel $excel){

        return $excel->download(new ContainersExport, 'containers.xlsx');
    }

    public function exportEmployeesCSV(Excel $excel){

        return $excel->download(new EmployeesExport, 'employees.csv', Excel::CSV);
    }
    public function exportEmployeesPDF(Excel $excel){

        return $excel->download(new EmployeesExport, 'employees.pdf', Excel::DOMPDF);
    }
    public function exportEmployeesExcel(Excel $excel){

        return $excel->download(new EmployeesExport, 'employees.xlsx');
    }

    public function exportVehiclesCSV(Excel $excel){

        return $excel->download(new VehiclesExport, 'vehicles.csv', Excel::CSV);
    }
    public function exportVehiclesPDF(Excel $excel){

        return $excel->download(new VehiclesExport, 'vehicles.pdf', Excel::DOMPDF);
    }
    public function exportVehiclesExcel(Excel $excel){

        return $excel->download(new VehiclesExport, 'vehicles.xlsx');
    }

    public function exportHorsesCSV(Excel $excel){

        return $excel->download(new HorsesExport, 'horses.csv', Excel::CSV);
    }
    public function exportHorsesPDF(Excel $excel){

        return $excel->download(new HorsesExport, 'horses.pdf', Excel::DOMPDF);
    }
    public function exportHorsesExcel(Excel $excel){

        return $excel->download(new HorsesExport, 'horses.xlsx');
    }

    public function exportAssignmentsCSV(Excel $excel){

        return $excel->download(new AssignmentsExport, 'assignments.csv', Excel::CSV);
    }
    public function exportAssignmentsPDF(Excel $excel){

        return $excel->download(new AssignmentsExport, 'assignments.pdf', Excel::DOMPDF);
    }
    public function exportAssignmentsExcel(Excel $excel){

        return $excel->download(new AssignmentsExport, 'assignments.xlsx');
    }

    public function exportCustomersCSV(Excel $excel){

        return $excel->download(new CustomersExport, 'customers.csv', Excel::CSV);
    }
    public function exportCustomersPDF(Excel $excel){

        return $excel->download(new CustomersExport, 'customers.pdf', Excel::DOMPDF);
    }
    public function exportCustomersExcel(Excel $excel){

        return $excel->download(new CustomersExport, 'customers.xlsx');
    }
    public function exportVendorsCSV(Excel $excel){

        return $excel->download(new VendorsExport, 'vendors.csv', Excel::CSV);
    }
    public function exportVendorsPDF(Excel $excel){

        return $excel->download(new VendorsExport, 'vendors.pdf', Excel::DOMPDF);
    }
    public function exportVendorsExcel(Excel $excel){

        return $excel->download(new VendorsExport, 'vendors.xlsx');
    }

    public function exportBrokersCSV(Excel $excel){

        return $excel->download(new BrokersExport, 'brokers.csv', Excel::CSV);
    }
    public function exportBrokersPDF(Excel $excel){

        return $excel->download(new BrokersExport, 'brokers.pdf', Excel::DOMPDF);
    }
    public function exportBrokersExcel(Excel $excel){

        return $excel->download(new BrokersExport, 'brokers.xlsx');
    }

    public function exportLeavesCSV(Excel $excel){

        return $excel->download(new LeavesExport, 'leave_applications.csv', Excel::CSV);
    }
    public function exportLeavesPDF(Excel $excel){

        return $excel->download(new LeavesExport, 'leave_applications.pdf', Excel::DOMPDF);
    }
    public function exportLeavesExcel(Excel $excel){

        return $excel->download(new LeavesExport, 'leave_applications.xlsx');
    }

    public function exportTyresCSV(Excel $excel){

        return $excel->download(new TyresExport, 'tyres.csv', Excel::CSV);
    }
    public function exportTyresPDF(Excel $excel){

        return $excel->download(new TyresExport, 'tyres.pdf', Excel::DOMPDF);
    }
    public function exportTyresExcel(Excel $excel){

        return $excel->download(new TyresExport, 'tyres.xlsx');
    }

    public function exportTripsCSV(Excel $excel){

        return $excel->download(new TripsExport, 'trips.csv', Excel::CSV);
    }
    public function exportTripsPDF(Excel $excel){

        return $excel->download(new TripsExport, 'trips.pdf', Excel::DOMPDF);
    }
    public function exportTripsExcel(Excel $excel){

        return $excel->download(new TripsExport, 'trips.xlsx');
    }

    public function exportTrailersCSV(Excel $excel){

        return $excel->download(new TrailersExport, 'trailers.csv', Excel::CSV);
    }
    public function exportTrailersPDF(Excel $excel){

        return $excel->download(new TrailersExport, 'trailers.pdf', Excel::DOMPDF);
    }
    public function exportTrailersExcel(Excel $excel){

        return $excel->download(new TrailersExport, 'trailers.xlsx');
    }
    public function exportDriversCSV(Excel $excel){

        return $excel->download(new DriversExport, 'drivers.csv', Excel::CSV);
    }
    public function exportDriversPDF(Excel $excel){

        return $excel->download(new DriversExport, 'drivers.pdf', Excel::DOMPDF);
    }
    public function exportDriversExcel(Excel $excel){

        return $excel->download(new DriversExport, 'drivers.xlsx');
    }

    public function exportCompaniesCSV(Excel $excel){

        return $excel->download(new CompaniesExport, 'companies.csv', Excel::CSV);
    }
    public function exportCompaniesPDF(Excel $excel){

        return $excel->download(new CompaniesExport, 'companies.pdf', Excel::DOMPDF);
    }
    public function exportCompaniesExcel(Excel $excel){

        return $excel->download(new CompaniesExport, 'companies.xlsx');
    }

}
