<?php

// use App\Http\Livewire\Shifts\Preview;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\SmsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/','LoginController@login')->name('login');
Route::get('/login','LoginController@login')->name('get-login');
Route::post('/login','LoginController@postLogin')->name('postLogin');
Route::get('/signup','LoginController@signup')->name('signup');
Route::get('/logout', 'LoginController@logout')->name('logout');
Route::get('/forgot-password','LoginController@forgotPassword')->name('forgot-password');
Route::get('/{token}/reset-password/{id}', 'LoginController@resetPassword')->name('reset-password');


Route::group(['middleware' => 'auth'], function(){

Route::get('/shifts/reports/preview', [ShiftController::class, 'preview'])->name('shifts.preview');

//**Human Resources Route**

//Leave Management Routes
Route::get('leaves/my-team','LeaveController@myTeam')->name('leaves.myteam');
Route::get('leave/applications/manage','LeaveController@manage')->name('leaves.manage');
Route::get('leave/applications/approved','LeaveController@approved')->name('leaves.approved');
Route::get('leave/applications/pending','LeaveController@pending')->name('leaves.pending');
Route::get('leave/applications/progress','LeaveController@progress')->name('leaves.progress');
Route::get('leave/applications/rejected','LeaveController@rejected')->name('leaves.rejected');
Route::post('leave/application/{id}/decision','LeaveController@decision')->name('leaves.decision');
Route::get('leave/reports','LeaveController@reports')->name('leaves.reports');

Route::get('accounts/tax','AccountController@tax')->name('accounts.tax');

//Loan Management Routes
Route::get('loans/myloans/','LoanController@myloans')->name('loans.myloans');
Route::get('loan/applications/approved','LoanController@approved')->name('loans.approved');
Route::get('loan/applications/pending','LoanController@pending')->name('loans.pending');
Route::get('loan/applications/rejected','LoanController@rejected')->name('loans.rejected');
Route::post('loan/application/{id}/decision','LoanController@decision')->name('loans.decision');
Route::get('loan/reports','LoanController@reports')->name('loans.reports');

Route::get('accounts/receivable','AccountController@accountsReceivable')->name('accounts.receivable');
Route::get('accounts/payable','AccountController@accountsPayable')->name('accounts.payable');

Route::get('admins/management','AdminController@manage')->name('admins.manage');

//**Fuel Management Routes**

//Fuel Requisition Routes
Route::get('employee/{id}/fuel/requests','FuelRequestController@myRequests')->name('fuel_requests.myrequests');
Route::get('fuel/requests/deleted','FuelRequestController@deleted')->name('fuel_requests.deleted');
Route::get('fuel/requests/approved','FuelRequestController@approved')->name('fuel_requests.approved');
Route::get('fuel/requests/rejected','FuelRequestController@rejected')->name('fuel_requests.rejected');
Route::get('fuel/requests/pending','FuelRequestController@pending')->name('fuel_requests.pending');
Route::get('fuel/requests/management','FuelRequestController@manage')->name('fuel_requests.manage');
Route::get('fuel/requests/reports','FuelRequestController@reports')->name('fuel_requests.reports');

//Purchase Order Routes
Route::get('purchase/orders/deleted','PurchaseController@deleted')->name('purchases.deleted');
Route::get('purchase/orders/approved','PurchaseController@approved')->name('purchases.approved');
Route::get('purchase/orders/rejected','PurchaseController@rejected')->name('purchases.rejected');
Route::get('purchase/orders/pending','PurchaseController@pending')->name('purchases.pending');
Route::get('purchase/orders/management','PurchaseController@manage')->name('purchases.manage');
Route::get('purchase/orders/reports','PurchaseController@reports')->name('purchases.reports');

Route::get('inventory_dispatches/approved','InventoryDispatchController@approved')->name('inventory_dispatches.approved');
Route::get('inventory_dispatches/rejected','InventoryDispatchController@rejected')->name('inventory_dispatches.rejected');
Route::get('inventory_dispatches/pending','InventoryDispatchController@pending')->name('inventory_dispatches.pending');

Route::get('asset_transfers/approved','AssetTransferController@approved')->name('asset_transfers.approved');
Route::get('asset_transfers/rejected','AssetTransferController@rejected')->name('asset_transfers.rejected');
Route::get('asset_transfers/pending','AssetTransferController@pending')->name('asset_transfers.pending');

Route::get('inventory_transfers/approved','InventoryTransferController@approved')->name('inventory_transfers.approved');
Route::get('inventory_transfers/rejected','InventoryTransferController@rejected')->name('inventory_transfers.rejected');
Route::get('inventory_transfers/pending','InventoryTransferController@pending')->name('inventory_transfers.pending');

Route::get('tyre_transfers/approved','TyreTransferController@approved')->name('tyre_transfers.approved');
Route::get('tyre_transfers/rejected','TyreTransferController@rejected')->name('tyre_transfers.rejected');
Route::get('tyre_transfers/pending','TyreTransferController@pending')->name('tyre_transfers.pending');

Route::get('asset_dispatches/approved','AssetDispatchController@approved')->name('asset_dispatches.approved');
Route::get('asset_dispatches/rejected','AssetDispatchController@rejected')->name('asset_dispatches.rejected');
Route::get('asset_dispatches/pending','AssetDispatchController@pending')->name('asset_dispatches.pending');

Route::get('tyre_dispatches/approved','TyreDispatchController@approved')->name('tyre_dispatches.approved');
Route::get('tyre_dispatches/rejected','TyreDispatchController@rejected')->name('tyre_dispatches.rejected');
Route::get('tyre_dispatches/pending','TyreDispatchController@pending')->name('tyre_dispatches.pending');

Route::get('inventory_purchase/orders/deleted','InventoryPurchaseController@deleted')->name('inventory_purchases.deleted');
Route::get('inventory_purchase/orders/approved','InventoryPurchaseController@approved')->name('inventory_purchases.approved');
Route::get('inventory_purchase/orders/rejected','InventoryPurchaseController@rejected')->name('inventory_purchases.rejected');
Route::get('inventory_purchase/orders/pending','InventoryPurchaseController@pending')->name('inventory_purchases.pending');
Route::get('inventory_purchase/orders/management','InventoryPurchaseController@manage')->name('inventory_purchases.manage');
Route::get('inventory_purchase/orders/reports','InventoryPurchaseController@reports')->name('inventory_purchases.reports');

Route::get('tyre_purchase/orders/deleted','TyrePurchaseController@deleted')->name('tyre_purchases.deleted');
Route::get('tyre_purchase/orders/approved','TyrePurchaseController@approved')->name('tyre_purchases.approved');
Route::get('tyre_purchase/orders/rejected','TyrePurchaseController@rejected')->name('tyre_purchases.rejected');
Route::get('tyre_purchase/orders/pending','TyrePurchaseController@pending')->name('tyre_purchases.pending');
Route::get('tyre_purchase/orders/management','TyrePurchaseController@manage')->name('tyre_purchases.manage');
Route::get('tyre_purchase/orders/reports','TyrePurchaseController@reports')->name('tyre_purchases.reports');
Route::get('tyre_purchase/orders/deleted','TyrePurchaseController@deleted')->name('tyre_purchases.deleted');
Route::get('tyre_purchase/orders/approved','TyrePurchaseController@approved')->name('tyre_purchases.approved');
Route::get('tyre_purchase/orders/rejected','TyrePurchaseController@rejected')->name('tyre_purchases.rejected');
Route::get('tyre_purchase/orders/pending','TyrePurchaseController@pending')->name('tyre_purchases.pending');
Route::get('tyre_purchase/orders/management','TyrePurchaseController@manage')->name('tyre_purchases.manage');
Route::get('tyre_purchase/orders/reports','TyrePurchaseController@reports')->name('tyre_purchases.reports');




//Fuel Suppliers Routes
Route::get('fuel/supplier/{id}/top-up','TopUpController@manage')->name('top_ups.manage');
Route::get('top_ups/{id}/fuel/','TopUpController@fuel')->name('top_ups.fuel');

Route::get('fuel/transfers','ContainerController@transfers')->name('transfers.fuel');
Route::get('fuel/containers/deleted','ContainerController@deleted')->name('containers.deleted');
Route::get('fuel/containers/management','ContainerController@manage')->name('containers.manage');
Route::get('fuel/containers/reports','ContainerController@reports')->name('containers.reports');

//Fuel Allocation Routes
Route::get('employee/{id}/fuel/allocations','AllocationController@myAllocations')->name('allocations.myallocations');
Route::get('allocations/deleted','AllocationController@deleted')->name('allocations.deleted');
Route::get('allocations/management','AllocationController@manage')->name('allocations.manage');
Route::get('allocations/reports','AllocationController@reports')->name('allocations.reports');

// Fuel Orders Routes
Route::get('fuel/orders/deleted','FuelController@deleted')->name('fuels.deleted');
Route::get('fuel/orders/pending','FuelController@pending')->name('fuels.pending');
Route::get('fuel/orders/approved','FuelController@approved')->name('fuels.approved');
Route::get('fuel/orders/rejected','FuelController@rejected')->name('fuels.rejected');
Route::get('fuels/{fuel}/preview','FuelController@preview')->name('fuels.preview');
Route::get('fuels/{fuel}/print','FuelController@print')->name('fuels.print');

//**Fleet Management Routes**
//Trailers Routes
Route::get('horses/perfomance/','HorseController@performance')->name('horses.performance');
Route::get('drivers/perfomance/','DriverController@performance')->name('drivers.performance');

Route::get('shifts/reports/','ShiftController@reports')->name('shifts.reports');

Route::get('horses/next-service/mileage','HorseController@mileage')->name('horses.mileage');
Route::get('vehicles/next-service/mileage','VehicleController@mileage')->name('vehicles.mileage');
Route::get('trailers/next-service/mileage','TrailerController@mileage')->name('trailers.mileage');




Route::get('vendors/age/','VendorController@age')->name('vendors.age');
Route::get('horses/age/','HorseController@age')->name('horses.age');
Route::get('customers/age/','CustomerController@age')->name('customers.age');
Route::get('employees/age/','EmployeeController@age')->name('employees.age');
Route::get('drivers/age/','DriverController@age')->name('drivers.age');
Route::get('trailers/age/','TrailerController@age')->name('trailers.age');
Route::get('vehicles/age/','VehicleController@age')->name('vehicles.age');
Route::get('horses/management','HorseController@manage')->name('horses.manage');
Route::get('trailers/management','TrailerController@manage')->name('trailers.manage');
Route::get('vehicles/management','VehicleController@manage')->name('vehicles.manage');
Route::get('assets/management','AssetController@manage')->name('assets.manage');
Route::get('inventories/management','InventoryController@manage')->name('inventories.manage');
Route::get('inventories/assignment','InventoryController@assignment')->name('inventories.assignment');
Route::get('inventories/requisition','InventoryController@requisition')->name('inventories.requisition');
Route::post('/inventories/import','ImportsController@importInventories')->name('inventories.import');
Route::post('/locations/import','ImportsController@importLocations')->name('locations.import');
Route::post('/works/import','ImportsController@importWorks')->name('works.import');
Route::post('/shifts/import','ImportsController@importShifts')->name('shifts.import');

Route::post('/trips/import','ImportsController@importTrips')->name('trips.import');
Route::post('/racks/import','ImportsController@importRacks')->name('racks.import');
Route::post('/bins/import','ImportsController@importBins')->name('bins.import');


Route::get('employees/leave-days','EmployeeController@leave')->name('employees.leaves.index');

Route::get('horses/archived','HorseController@archived')->name('horses.archived');
Route::get('trailers/archived','TrailerController@archived')->name('trailers.archived');
Route::get('vehicles/archived','VehicleController@archived')->name('vehicles.archived');
Route::get('employees/reports','EmployeeController@reports')->name('employees.reports');
Route::get('employees/deleted','EmployeeController@deleted')->name('employees.deleted');
Route::get('employees/archived','EmployeeController@archived')->name('employees.archived');
Route::get('drivers/archived','DriverController@archived')->name('drivers.archived');
Route::get('drivers/deleted','DriverController@deleted')->name('drivers.deleted');
Route::get('drivers/reports','DriverController@reports')->name('drivers.reports');
Route::get('vendors/reports','VendorController@reports')->name('vendors.reports');
Route::get('customers/reports','CustomerController@reports')->name('customers.reports');
Route::get('brokers/reports','BrokerController@reports')->name('brokers.reports');
Route::get('horses/reports','HorseController@reports')->name('horses.reports');
Route::get('vehicles/reports','VehicleController@reports')->name('vehicles.reports');

Route::get('assignments/reports','AssignmentController@reports')->name('assignments.reports');
Route::get('trips/reports','TripController@reports')->name('trips.reports');
Route::get('debtors/report','CustomerController@debtorsReports')->name('debtors.reports');
Route::get('creditors/report','VendorController@creditorsReports')->name('creditors.reports');

Route::get('trips/deleted','TripController@deleted')->name('trips.deleted');
Route::get('trips/{trip}/trip-sheet','TripController@preview')->name('trips.trip_sheet');
Route::get('trips/{trip}/manifest','TripController@manifest')->name('trips.manifest');
Route::get('inspections/{inspection}/preview','InspectionController@preview')->name('inspections.preview');
Route::get('trips/{trip}/print','TripController@print')->name('trips.print');

Route::get('trips/{from?}/{to?}/{trip_filter?}/summary','TripController@rangeSummary')->name('trips.summary.range');
Route::get('trips/{from?}/{to?}/{search?}/{trip_filter?}/summary','TripController@allSummary')->name('trips.summary.all');
Route::get('trips/{search?}/{trip_filter?}/summary','TripController@searchSummary')->name('trips.summary.search');
Route::get('trips/{trip_filter?}/summary','TripController@summary')->name('trips.summary');

Route::get('trips/{from?}/{to?}/{trip_filter?}/summary/print','TripController@rangeSummaryPrint')->name('trips.summary.range.print');
Route::get('trips/{from?}/{to?}/{search?}/{trip_filter?}/summary/print','TripController@allSummaryPrint')->name('trips.summary.all.print');
Route::get('trips/{search?}/{trip_filter?}/summary/print','TripController@searchSummaryPrint')->name('trips.summary.search.print');
Route::get('trips/{trip_filter?}/summary/print','TripController@summaryPrint')->name('trips.summary.print');


Route::get('/bookings/export/excel','ExportsController@exportBookingsExcel')->name('bookings.export.excel');
Route::get('/bookings/export/csv','ExportsController@exportBookingsCSV')->name('bookings.export.csv');
Route::get('/bookings/export/pdf','ExportsController@exportBookingsPDF')->name('bookings.export.pdf');



Route::get('/customers/export/excel','ExportsController@exportCustomersExcel')->name('customers.export.excel');
Route::get('/customers/export/csv','ExportsController@exportCustomersCSV')->name('customers.export.csv');
Route::get('/customers/export/pdf','ExportsController@exportCustomersPDF')->name('customers.export.pdf');
Route::post('/customers/import','ImportsController@importCustomers')->name('customers.import');

Route::get('/consignees/export/excel','ExportsController@exportConsigneesExcel')->name('consignees.export.excel');
Route::get('/consignees/export/csv','ExportsController@exportConsigneesCSV')->name('consignees.export.csv');
Route::get('/consignees/export/pdf','ExportsController@exportConsigneesPDF')->name('consignees.export.pdf');
Route::post('/consignees/import','ImportsController@importConsignees')->name('consignees.import');



Route::get('/cargos/export/excel','ExportsController@exportCargosExcel')->name('cargos.export.excel');
Route::get('/cargos/export/csv','ExportsController@exportCargosCSV')->name('cargos.export.csv');
Route::get('/cargos/export/pdf','ExportsController@exportCargosPDF')->name('cargos.export.pdf');
Route::post('/cargos/import','ImportsController@importCargos')->name('cargos.import');

Route::get('/countries/export/excel','ExportsController@exportCountriesExcel')->name('countries.export.excel');
Route::get('/countries/export/csv','ExportsController@exportCountriesCSV')->name('countries.export.csv');
Route::get('/countries/export/pdf','ExportsController@exportCountriesPDF')->name('countries.export.pdf');
Route::post('/countries/import','ImportsController@importCountries')->name('countries.import');

Route::get('/provinces/export/excel','ExportsController@exportProvincesExcel')->name('provinces.export.excel');
Route::get('/provinces/export/csv','ExportsController@exportProvincesCSV')->name('provinces.export.csv');
Route::get('/provinces/export/pdf','ExportsController@exportProvincesPDF')->name('provinces.export.pdf');
Route::post('/provinces/import','ImportsController@importProvinces')->name('provinces.import');

Route::get('/destinations/export/excel','ExportsController@exportDestinationsExcel')->name('destinations.export.excel');
Route::get('/destinations/export/csv','ExportsController@exportDestinationsCSV')->name('destinations.export.csv');
Route::get('/destinations/export/pdf','ExportsController@exportDestinationsPDF')->name('destinations.export.pdf');
Route::post('/destinations/import','ImportsController@importDestinations')->name('destinations.import');

Route::get('/loading_points/export/excel','ExportsController@exportLoadingPointsExcel')->name('loading_points.export.excel');
Route::get('/loading_points/export/csv','ExportsController@exportLoadingPointsCSV')->name('loading_points.export.csv');
Route::get('/loading_points/export/pdf','ExportsController@exportLoadingPointsPDF')->name('loading_points.export.pdf');
Route::post('/loading_points/import','ImportsController@importLoadingPoints')->name('loading_points.import');

Route::get('/offloading_points/export/excel','ExportsController@exportOffloadingPointsExcel')->name('offloading_points.export.excel');
Route::get('/offloading_points/export/csv','ExportsController@exportOffloadingPointsCSV')->name('offloading_points.export.csv');
Route::get('/offloading_points/export/pdf','ExportsController@exportOffloadingPointsPDF')->name('offloading_points.export.pdf');
Route::post('/offloading_points/import','ImportsController@importOffloadingPoints')->name('offloading_points.import');

Route::get('/transporters/export/excel','ExportsController@exportTransportersExcel')->name('transporters.export.excel');
Route::get('/transporters/export/csv','ExportsController@exportTransportersCSV')->name('transporters.export.csv');
Route::get('/transporters/export/pdf','ExportsController@exportTransportersPDF')->name('transporters.export.pdf');
Route::post('/transporters/import','ImportsController@importTransporters')->name('transporters.import');

Route::get('/agents/export/excel','ExportsController@exportAgentsExcel')->name('agents.export.excel');
Route::get('/agents/export/csv','ExportsController@exportAgentsCSV')->name('agents.export.csv');
Route::get('/agents/export/pdf','ExportsController@exportAgentsPDF')->name('agents.export.pdf');
Route::post('/agents/import','ImportsController@importAgents')->name('agents.import');

Route::get('/companies/{company}/profile','CompanyController@getProfile')->name('company-profile');
Route::get('companies/management','CompanyController@manage')->name('companies.manage');
Route::get('/companies/export/excel','ExportsController@exportCompaniesExcel')->name('companies.export.excel');
Route::get('/companies/export/csv','ExportsController@exportCompaniesCSV')->name('companies.export.csv');
Route::get('/companies/export/pdf','ExportsController@exportCompaniesPDF')->name('companies.export.pdf');
Route::post('/companies/import','ImportsController@importCompanies')->name('companies.import');

Route::get('tyres/orders','TyreController@orders')->name('tyres.orders');
Route::post('/tyres/import','ImportsController@importTyres')->name('tyres.import');
Route::get('retreads/orders','RetreadController@orders')->name('retreads.orders');
Route::get('routes/{route}/trips','RouteController@trips')->name('routes.trips');

//**Import and Export Routes**

//Fuel Allocation Import and Export
Route::get('/allocations/export/excel','ExportsController@exportAllocationsExcel')->name('allocations.export.excel');
Route::get('/allocations/export/csv','ExportsController@exportAllocationsCSV')->name('allocations.export.csv');
Route::get('/allocations/export/pdf','ExportsController@exportAllocationsPDF')->name('allocations.export.pdf');
Route::post('/allocations/import','ImportsController@importAllocations')->name('allocations.import');

//Drivers Import and Export
Route::get('/drivers/export/excel','ExportsController@exportDriversExcel')->name('drivers.export.excel');
Route::get('/drivers/export/csv','ExportsController@exportDriversCSV')->name('drivers.export.csv');
Route::get('/drivers/export/pdf','ExportsController@exportDriversPDF')->name('drivers.export.pdf');
Route::post('/drivers/import','ImportsController@importDrivers')->name('drivers.import');

//Employees Import and Export
Route::get('/employees/export/excel','ExportsController@exportEmployeesExcel')->name('employees.export.excel');
Route::get('/employees/export/csv','ExportsController@exportEmployeesCSV')->name('employees.export.csv');
Route::get('/employees/export/pdf','ExportsController@exportEmployeesPDF')->name('employees.export.pdf');
Route::post('/employees/import','ImportsController@importEmployees')->name('employees.import');
Route::post('/employees/leaves/import','ImportsController@importEmployeesLeave')->name('employees.leaves.import');

//Vehicles Import and Export
Route::get('/vehicles/export/excel','ExportsController@exportVehiclesExcel')->name('vehicles.export.excel');
Route::get('/vehicles/export/csv','ExportsController@exportVehiclesCSV')->name('vehicles.export.csv');
Route::get('/vehicles/export/pdf','ExportsController@exportVehiclesPDF')->name('vehicles.export.pdf');
Route::post('/vehicles/import','ImportsController@importVehicles')->name('vehicles.import');

//Horses Import and Export
Route::get('/horses/export/excel','ExportsController@exportHorsesExcel')->name('horses.export.excel');
Route::get('/horses/export/csv','ExportsController@exportHorsesCSV')->name('horses.export.csv');
Route::get('/horses/export/pdf','ExportsController@exportHorsesPDF')->name('horses.export.pdf');
Route::post('/horses/import','ImportsController@importHorses')->name('horses.import');

//Trailers Import and Export
Route::get('/trailers/export/excel','ExportsController@exportTrailersExcel')->name('trailers.export.excel');
Route::get('/trailers/export/csv','ExportsController@exportTrailersCSV')->name('trailers.export.csv');
Route::get('/trailers/export/pdf','ExportsController@exportTrailersPDF')->name('trailers.export.pdf');
Route::post('/trailers/import','ImportsController@importTrailers')->name('trailers.import');

//Trips Import and Export
Route::get('/trips/export/excel','ExportsController@exportTripsExcel')->name('trips.export.excel');
Route::get('/trips/export/csv','ExportsController@exportTripsCSV')->name('trips.export.csv');
Route::get('/trips/export/pdf','ExportsController@exportTripsPDF')->name('trips.export.pdf');
Route::post('/trips/import','ImportsController@importTrips')->name('trips.import');

//Tyres Import and Export
Route::get('/tyres/export/excel','ExportsController@exportTyresExcel')->name('tyres.export.excel');
Route::get('/tyres/export/csv','ExportsController@exportTyresCSV')->name('tyres.export.csv');
Route::get('/tyres/export/pdf','ExportsController@exportTyresPDF')->name('tyres.export.pdf');
Route::post('/tyres/import','ImportsController@importTyres')->name('tyres.import');

//Fuel Requests Import and Export
Route::get('/fuel_requests/export/excel','ExportsController@exportFuelRequestsExcel')->name('fuel_requests.export.excel');
Route::get('/fuel_requests/export/csv','ExportsController@exportFuelRequestsCSV')->name('fuel_requests.export.csv');
Route::get('/fuel_requests/export/pdf','ExportsController@exportFuelRequestsPDF')->name('fuel_requests.export.pdf');
Route::post('/fuel_requests/import','ImportsController@importFuelRequests')->name('fuel_requests.import');

//Fuel Order Import and Export
Route::get('/fuels/export/excel','ExportsController@exportFuelsExcel')->name('fuels.export.excel');
Route::get('/fuels/export/csv','ExportsController@exportFuelsCSV')->name('fuels.export.csv');
Route::get('/fuels/export/pdf','ExportsController@exportFuelsPDF')->name('fuels.export.pdf');
Route::post('/fuels/import','ImportsController@importFuels')->name('fuels.import');

//Fuel Suppliers Import and Export
Route::get('/containers/export/excel','ExportsController@exportContainersExcel')->name('containers.export.excel');
Route::get('/containers/export/csv','ExportsController@exportContainersCSV')->name('containers.export.csv');
Route::get('/containers/export/pdf','ExportsController@exportContainersPDF')->name('containers.export.pdf');
Route::post('/containers/import','ImportsController@importContainers')->name('containers.import');

//Assignments Import and Export
Route::get('/assignments/export/excel','ExportsController@exportAssignmentsExcel')->name('assignments.export.excel');
Route::get('/assignments/export/csv','ExportsController@exportAssignmentsCSV')->name('assignments.export.csv');
Route::get('/assignments/export/pdf','ExportsController@exportAssignmentsPDF')->name('assignments.export.pdf');
Route::post('/assignments/import','ImportsController@importAssignments')->name('assignments.import');

//Customers Import and Export
Route::get('/customers/export/excel','ExportsController@exportCustomersExcel')->name('customers.export.excel');
Route::get('/customers/export/csv','ExportsController@exportCustomersCSV')->name('customers.export.csv');
Route::get('/customers/export/pdf','ExportsController@exportCustomersPDF')->name('customers.export.pdf');
Route::post('/customers/import','ImportsController@importCustomers')->name('customers.import');

//Customers Import and Export
Route::get('/transporters/export/excel','ExportsController@exportTransportersExcel')->name('transporters.export.excel');
Route::get('/transporters/export/csv','ExportsController@exportTransportersCSV')->name('transporters.export.csv');
Route::get('/transporters/export/pdf','ExportsController@exportTransportersPDF')->name('transporters.export.pdf');
Route::post('/transporters/import','ImportsController@importTransporters')->name('transporters.import');

//Customers Import and Export
Route::get('/leave/applications/export/excel','ExportsController@exportLeavesExcel')->name('leaves.export.excel');
Route::get('/leave/applications/export/csv','ExportsController@exportLeavesCSV')->name('leaves.export.csv');
Route::get('/leave/applications/export/pdf','ExportsController@exportLeavesPDF')->name('leaves.export.pdf');
Route::post('/leave/applications/import','ImportsController@importLeaves')->name('leaves.import');

//Brokers Import and Export
Route::get('/brokers/export/excel','ExportsController@exportBrokersExcel')->name('brokers.export.excel');
Route::get('/brokers/export/csv','ExportsController@exportBrokersCSV')->name('brokers.export.csv');
Route::get('/brokers/export/pdf','ExportsController@exportBrokersPDF')->name('brokers.export.pdf');
Route::post('/brokers/import','ImportsController@importBrokers')->name('brokers.import');

//Vendors Import and Export
Route::get('/vendors/export/excel','ExportsController@exportVendorsExcel')->name('vendors.export.excel');
Route::get('/vendors/export/csv','ExportsController@exportVendorsCSV')->name('vendors.export.csv');
Route::get('/vendors/export/pdf','ExportsController@exportVendorsPDF')->name('vendors.export.pdf');
Route::post('/vendors/import','ImportsController@importVendors')->name('vendors.import');

//Cashflows Import and Export
Route::get('/cashflow/export/excel','ExportsController@exportCashflowsExcel')->name('cashflows.export.excel');
Route::get('/cashflow/export/csv','ExportsController@exportCashflowsCSV')->name('cashflows.export.csv');
Route::get('/cashflow/export/pdf','ExportsController@exportCashflowsPDF')->name('cashflows.export.pdf');
Route::post('/cashflow/import','ImportsController@importCashflows')->name('cashflows.import');

//Fitness Import and Export
Route::get('/fitness/export/excel','ExportsController@exportFitnessExcel')->name('fitness.export.excel');
Route::get('/fitness/export/csv','ExportsController@exportFitnessCSV')->name('fitness.export.csv');
Route::get('/fitness/export/pdf','ExportsController@exportFitnessPDF')->name('fitness.export.pdf');
Route::post('/fitness/import','ImportsController@importFitness')->name('fitness.import');


Route::get('horses/profit-and-loss','HorseController@profitLoss')->name('horses.statement.index');
Route::get('horses/{selectedHorse?}/{from?}/{to?}/profit-loss/preview/','HorseController@profitLossPreview')->name('horses.statement.preview');
Route::get('horses/{selectedHorse?}/{from?}/{to?}/profit-loss/print/','HorseController@profitLossPrint')->name('horses.statement.print');
Route::get('horses/{selectedHorse?}/{from?}/{to?}/profit-loss/pdf/','HorseController@profitLossPdf')->name('horses.statement.pdf');

Route::get('reports/financial-statements','ReportController@index')->name('reports.index');
Route::get('reports/income-statement','ReportController@incomeStatement')->name('reports.income_statement');
Route::get('reports/cashflow','ReportController@incomeStatement')->name('reports.cashflow');
Route::get('reports/balance-sheet','ReportController@balanceSheet')->name('reports.balance_sheet');

Route::get('bookings/delete','BookingController@deleted')->name('bookings.deleted');
Route::get('bookings/authorization/pending','BookingController@pending')->name('bookings.pending');
Route::get('bookings/authorization/approved','BookingController@approved')->name('bookings.approved');
Route::get('bookings/authorization/rejected','BookingController@rejected')->name('bookings.rejected');

Route::get('transfers/authorization/pending','TransferController@pending')->name('transfers.pending');
Route::get('transfers/authorization/approved','TransferController@approved')->name('transfers.approved');
Route::get('transfers/authorization/rejected','TransferController@rejected')->name('transfers.rejected');


Route::get('fuel-top-ups/authorization/pending','TopUpController@pending')->name('top_ups.pending');
Route::get('fuel-top-ups/authorization/approved','TopUpController@approved')->name('top_ups.approved');
Route::get('fuel-top-ups/authorization/rejected','TopUpController@rejected')->name('top_ups.rejected');

Route::get('invoices/customer-statements','InvoiceController@customerStatements')->name('customer_statements.index');
Route::get('bills/vendor-statements','BillController@vendorStatements')->name('vendor_statements.index');

Route::get('invoices/{selectedCustomer?}/{selectedType?}/customer-statements/send-email/','InvoiceController@customerStatementsEmail')->name('customer_statements.email.outstanding');
Route::get('invoices/{selectedCustomer?}/{selectedType?}/customer-statements/pdf/','InvoiceController@customerStatementsPDF')->name('customer_statements.pdf.outstanding');
Route::get('invoices/{selectedCustomer?}/{selectedType?}/customer-statements/print/','InvoiceController@customerStatementsPrint')->name('customer_statements.print.outstanding');
Route::get('invoices/{selectedCustomer?}/{selectedType?}/customer-statements/','InvoiceController@customerStatementsPreview')->name('customer_statements.preview.outstanding');

Route::get('invoices/{selectedCustomer?}/{selectedType?}/{from?}/{to?}/customer-statements/send-email/','InvoiceController@customerStatementsEmail')->name('customer_statements.email.account');
Route::get('invoices/{selectedCustomer?}/{selectedType?}/{from?}/{to?}/customer-statements/pdf/','InvoiceController@customerStatementsPDF')->name('customer_statements.pdf.account');
Route::get('invoices/{selectedCustomer?}/{selectedType?}/{from?}/{to?}/customer-statements/print/','InvoiceController@customerStatementsPrint')->name('customer_statements.print.account');
Route::get('invoices/{selectedCustomer?}/{selectedType?}/{from?}/{to?}/customer-statements/','InvoiceController@customerStatementsPreview')->name('customer_statements.preview.account');

Route::get('bills/{selectedVendor?}/{selectedType?}/vendor-statements/send-email/','BillController@vendorStatementsEmail')->name('vendor_statements.email.outstanding');
Route::get('bills/{selectedVendor?}/{selectedType?}/vendor-statements/pdf/','BillController@vendorStatementsPDF')->name('vendor_statements.pdf.outstanding');
Route::get('bills/{selectedVendor?}/{selectedType?}/vendor-statements/print/','BillController@vendorStatementsPrint')->name('vendor_statements.print.outstanding');
Route::get('bills/{selectedVendor?}/{selectedType?}/vendor-statements/','BillController@vendorStatementsPreview')->name('vendor_statements.preview.outstanding');

Route::get('bills/{selectedVendor?}/{selectedType?}/{from?}/{to?}/vendor-statements/send-email/','BillController@vendorStatementsEmail')->name('vendor_statements.email.account');
Route::get('bills/{selectedVendor?}/{selectedType?}/{from?}/{to?}/vendor-statements/pdf/','BillController@vendorStatementsPDF')->name('vendor_statements.pdf.account');
Route::get('bills/{selectedVendor?}/{selectedType?}/{from?}/{to?}/vendor-statements/print/','BillController@vendorStatementsPrint')->name('vendor_statements.print.account');
Route::get('bills/{selectedVendor?}/{selectedType?}/{from?}/{to?}/vendor-statements/','BillController@vendorStatementsPreview')->name('vendor_statements.preview.account');


Route::get('tickets/{ticket}/job-card','TicketController@jobcard')->name('tickets.jobcard');
Route::get('tickets/{ticket}/preview','TicketController@preview')->name('tickets.preview');
Route::get('tickets/{ticket}/print','TicketController@print')->name('tickets.print');
Route::get('tickets/{ticket}//export-to-pdf','TicketController@generatePDF')->name('tickets.pdf');

Route::get('trips/{driver}/driver/','DriverController@trips')->name('driver.trips');
Route::get('recoveries/{driver}/driver/','DriverController@recoveries')->name('driver.recoveries');
Route::get('inspections/{driver}/driver/','DriverController@inspections')->name('driver.inspections');
Route::get('breakdowns/{driver}/driver/','DriverController@breakdowns')->name('driver.breakdowns');

Route::get('goods-received/tyres/','GoodsReceivedController@tyres')->name('goods_receiveds.tyres');
Route::get('goods-received/assets/','GoodsReceivedController@assets')->name('goods_receiveds.assets');

Route::get('reminders/copy/','ReminderController@copy')->name('reminders.copy');

//Transportation Order Routes
Route::get('order','TransportOrderController@order')->name('order');
Route::get('transportation/order/{trip}/preview','TransportOrderController@preview')->name('transport_orders.preview');
Route::get('transportation/order/{trip}/print','TransportOrderController@print')->name('transport_orders.print');
Route::get('transportation/order/{trip}//export-to-pdf','TransportOrderController@generatePDF')->name('transport_orders.pdf');



Route::get('horses/{horse}/edit','HorseController@edit')->name('horses.edit')->withTrashed();
Route::resource('inspection_schedules','InspectionScheduleController');
Route::resource('maintenance_schedules','MaintenanceScheduleController');
Route::resource('ticket_requests','TicketRequestController');
Route::resource('payment_methods','PaymentMethodController');
Route::resource('grades','GradeController');
Route::resource('clusters','ClusterController');
Route::resource('transport_orders','TransportOrderController');
Route::resource('inspection_schedules','InspectionScheduleController');
Route::resource('problem_categories','ProblemCategoryController');
Route::resource('teams','TeamController');
Route::resource('bins','BinController');
Route::resource('trip_transport_orders','TripTransportOrderController');
Route::resource('claims','ClaimController');
Route::resource('inventory_transfers','InventoryTransferController');
Route::resource('tyre_transfers','TyreTransferController');
Route::resource('goods_receiveds','GoodsReceivedController');
Route::resource('racks','RackController');
Route::resource('waste_types','WasteTypeController');
Route::resource('waste_receptacles','WasteReceptacleController');
Route::resource('dependants','DependantController');
Route::resource('shifts','ShiftController');
Route::resource('rehandlings','RehandlingController');
Route::resource('route_expense','RouteExpenseController');
Route::resource('works','WorkController');
Route::resource('locations','LocationController');
Route::resource('job_types','JobTypeController');
Route::resource('tyre_products','TyreProductController');
Route::get('tickets/{id}/cards','TicketController@cards')->name('tickets.cards');
Route::get('inspections/{id}/my-inspections','InspectionController@myInspections')->name('inspections.my-inspections');
Route::resource('qualifications','QualificationController');
Route::resource('employees','EmployeeController');
Route::resource('training_plans','TrainingPlanController');
Route::resource('waste_collections','WasteCollectionController');
Route::resource('units_of_measure','UnitsOfMeasureController');
Route::resource('waste_disposals','WasteDisposalController');
Route::resource('account_activity','AccountActivityController');
Route::resource('disposes','DisposeController');
Route::resource('transfers','TransferController');
Route::resource('loss_categories','LossCategoryController');
Route::resource('loss_groups','LossGroupController');
Route::resource('notifications','NotificationController');
Route::resource('trainings','TrainingController');
Route::resource('stations','StationController');
Route::resource('training_items','TrainingItemController');
Route::resource('training_departments','TrainingDepartmentController');
Route::resource('training_requirements','TrainingRequirementController');
Route::resource('losses','LossController');
Route::get('audits/{id}/{category}','AuditController@index')->name('audits.index');
Route::get('audits','AuditController@all')->name('audits.all');
Route::resource('admins','AdminController');
Route::resource('quotation_products','QuotationProductController');
Route::resource('rate_cards','RateCardController');
Route::resource('companies','CompanyController');
Route::resource('bookings','BookingController');
Route::resource('invoice_items','InvoiceItemController');
Route::resource('consignees','ConsigneeController');
Route::resource('recoveries','RecoveryController');
Route::resource('trip_destinations','TripDestinationController');
Route::resource('deductions','DeductionController');
Route::resource('allowances','AllowanceController');
Route::resource('earnings','EarningController');
Route::resource('reminders','ReminderController');
Route::resource('logs','LogController');
Route::resource('drivers','DriverController');
Route::resource('vehicles','VehicleController');
Route::resource('vehicle_assignments','VehicleAssignmentController');
Route::resource('bills','BillController');
Route::resource('bill_expenses','BillExpenseController');
Route::resource('gate_passes','GatePassController');
Route::resource('workshop_services','WorkshopServiceController');
Route::resource('visitors','VisitorController');
Route::resource('groups','GroupController');
Route::resource('credit_notes','CreditNoteController');
Route::resource('expense_categories','ExpenseCategoryController');
Route::resource('tickets','TicketController');
Route::resource('taxes','TaxController');
Route::resource('tyre_purchases','TyrePurchaseController');
Route::resource('breakdown_assignments','BreakdownAssignmentController');
Route::resource('breakdowns','BreakdownController');
Route::resource('measurements','MeasurementController');
Route::resource('accounts','AccountController');
Route::resource('invoice_products','InvoiceProductController');
Route::resource('product_services','ProductServiceController');
Route::resource('account_types','AccountTypeController');
Route::resource('payrolls','PayrollController');
Route::resource('trailer_links','TrailerLinkController');
Route::resource('payslips','PayslipController');
Route::resource('salary_items','SalaryItemController');
Route::resource('salaries','SalaryController');
Route::resource('folders','FolderController');
Route::resource('loans','LoanController');
Route::resource('loan_types','LoanTypeController');
Route::resource('compliances','ComplianceController');
Route::resource('checklist_items','ChecklistItemController');
Route::resource('checklists','ChecklistController');
Route::resource('agents','AgentController');
Route::resource('provinces','ProvinceController');
Route::resource('inspections','InspectionController');
Route::resource('inspection_types','InspectionTypeController');
Route::resource('inspection_groups','InspectionGroupController');
Route::resource('horses','HorseController');
Route::resource('trailers','TrailerController');
Route::resource('transporters','TransporterController');
Route::resource('trips','TripController');
Route::resource('reminder_items','ReminderItemController');
Route::resource('requisitions','RequisitionController');
Route::resource('currencies','CurrencyController');
Route::resource('vendors','VendorController');
Route::resource('assets','AssetController');
Route::resource('transactions','TransactionController');
Route::resource('brokers','BrokerController');
Route::resource('customers','CustomerController');
Route::resource('contacts','ContactController');
Route::resource('contracts','ContractController');
Route::resource('payments','PaymentController');
Route::resource('ticket_expenses','TicketExpenseController');
Route::resource('trip_groups','TripGroupController');
Route::resource('routes','RouteController');
Route::resource('job_titles','JobTitleController');
Route::resource('fuel_requests','FuelRequestController');
Route::resource('containers','ContainerController');
Route::resource('top_ups','TopUpController');
Route::resource('allocations','AllocationController');
Route::resource('fuels','FuelController');
Route::resource('rates','RateController');
Route::resource('ticket_inventories','TicketInventoryController');
Route::resource('inventory_dispatches','InventoryDispatchController');
Route::resource('tyre_dispatches','TyreDispatchController');
Route::resource('asset_dispatches','AssetDispatchController');
Route::resource('receipts','ReceiptController');
Route::resource('fitnesses','FitnessController');
Route::resource('tyres','TyreController');
Route::resource('addresses','AddressController');
Route::resource('tyre_details','TyreDetailController');
Route::resource('tyre_assignments','TyreAssignmentController');

Route::resource('retread_tyres','RetreadTyreController');
Route::resource('retreads','RetreadController');
Route::resource('destinations','DestinationController');
Route::resource('countries','CountryController');
Route::resource('cargos','CargoController');
Route::resource('assignments','AssignmentController');
Route::resource('vehicle_types','VehicleTypeController');
Route::resource('horse_types','HorseTypeController');
Route::resource('trailer_types','TrailerTypeController');
Route::resource('trip_types','TripTypeController');
Route::resource('asset_details','AssetDetailController');
Route::resource('asset_documents','AssetDocumentController');
Route::resource('trip_expenses','TripExpenseController');
Route::resource('vendor_types','VendorTypeController');
Route::resource('service_types','ServiceTypeController');
Route::resource('vehicle_groups','VehicleGroupController');
Route::resource('horse_groups','HorseGroupController');
Route::resource('trailer_groups','TrailerGroupController');
Route::resource('assignments','AssignmentController');
Route::resource('notices','NoticeController');
Route::resource('emails','EmailController');
Route::resource('leaves','LeaveController');
Route::resource('leave_types','LeaveTypeController');
Route::resource('departments','DepartmentController');
Route::resource('department_heads','DepartmentHeadController');
Route::resource('branches','BranchController');
Route::resource('loading_points','LoadingPointController');
Route::resource('offloading_points','OffloadingPointController');
Route::resource('products','ProductController');
Route::resource('dispatches','DispatchController');
Route::resource('inventory_products','InventoryProductController');
Route::resource('inventory_requisitions','InventoryRequisitionController');
Route::resource('inventory_assignments','InventoryAssignmentController');
Route::resource('documents','DocumentController');
Route::resource('vehicle_documents','VehicleDocumentController');
Route::resource('recruitment-checks','CheckController');
Route::resource('recruitment-stages','StageController');
Route::resource('recruitment-criterions','CriterionController');
Route::resource('horse_documents','HorseDocumentController');
Route::resource('trip_documents','TripDocumentController');
Route::resource('job_postings','JobPostingController');
Route::resource('applications','ApplicationController');
Route::resource('locations','LocationController');
Route::resource('brands','BrandController');
Route::resource('trip_locations','TripLocationController');
Route::resource('expenses','ExpenseController');
Route::resource('categories','CategoryController');
Route::resource('category_values','CategoryValueController');
Route::resource('stocks','StockController');
Route::resource('invoices','InvoiceController');
Route::resource('sales','SaleController');
Route::resource('attendances','AttendanceController');
Route::resource('quotations','QuotationController');
Route::resource('attributes','AttributeController');
Route::resource('attribute_values','AttributeValueController');
Route::resource('values','ValueController');
Route::resource('tax_brackets','TaxBracketController');
Route::resource('stores','StoreController');
Route::resource('orders','OrderController');
Route::resource('vehicle_makes','VehicleMakeController');
Route::resource('horse_makes','HorseMakeController');
Route::resource('rentals','RentalController');
Route::resource('vehicle_models','VehicleModelController');
Route::resource('modules','ModuleController');
Route::resource('horse_models','HorseModelController');
Route::resource('asset_assignments','AssetAssignmentController');
Route::resource('exchange_rates','ExchangeRateController');
Route::resource('inventories','InventoryController');
Route::resource('purchases','PurchaseController');
Route::resource('inventory_purchases','InventoryPurchaseController');
Route::resource('services','ServiceController');
Route::resource('inventory_assignments','InventoryAssignmentController');
Route::resource('trailer_assignments','TrailerAssignmentController');
Route::resource('purchase_products','PurchaseProductController');
Route::resource('purchase_documents','PurchaseDocumentController');
Route::resource('truck_stops','TruckStopController');
Route::resource('incidents','IncidentController');
Route::resource('bank_accounts','BankAccountController');
Route::resource('incomes','IncomeController');
Route::resource('corridors','CorridorController');
Route::resource('clearing_agents','ClearingAgentController');
Route::resource('borders','BorderController');
Route::resource('inspection_services','InspectionServiceController');
Route::resource('checklist_categories','ChecklistCategoryController');
Route::resource('checklist_sub_categories','ChecklistSubCategoryController');
Route::resource('category_checklists','CategoryChecklistController');

Route::get('checklists/{id}/add','ChecklistController@add')->name('checklists.add');

Route::post('/login/location', [LoginController::class, 'saveLoginLocation'])
    ->name('login.location')
    ->middleware('auth');

Route::get('documents/{id}/{category}/all','DocumentController@documents')->name('documents.all');
Route::get('product_services/{category}/all','ProductServiceController@all')->name('product_services.all');

Route::get('credit_notes/{id}/email','CreditNoteController@email')->name('credit_notes.email');
Route::get('credit_notes/{id}/print','CreditNoteController@print')->name('credit_notes.print');
Route::get('credit_notes/{id}/preview','CreditNoteController@preview')->name('credit_notes.preview');
Route::get('credit_notes/{credit_note}/export-to-pdf','CreditNoteController@generatePdf')->name('credit_notes.pdf');

Route::get('quotations/{id}/email','QuotationController@email')->name('quotations.email');
Route::get('quotations/{id}/print','QuotationController@print')->name('quotations.print');
Route::get('quotations/{id}/preview','QuotationController@preview')->name('quotations.preview');
Route::get('quotations/{quotation}/export-to-pdf','QuotationController@generatePdf')->name('quotations.pdf');
Route::get('quotations/delete','QuotationController@delete')->name('quotations.deleted');

Route::get('invoices/{id}/email','InvoiceController@email')->name('invoices.email');
Route::get('invoices/{id}/print','InvoiceController@print')->name('invoices.print');
Route::get('invoices/{id}/preview','InvoiceController@preview')->name('invoices.preview');
Route::get('invoices/{invoice}/export-to-pdf','InvoiceController@generatePdf')->name('invoices.pdf');

Route::get('purchases/{id}/print','PurchaseController@print')->name('purchases.print');
Route::get('purchases/{id}/preview','PurchaseController@preview')->name('purchases.preview');
Route::get('purchases/{purchase}/export-to-pdf','PurchaseController@generatePdf')->name('purchases.pdf');

Route::get('requisitions/{id}/print','RequisitionController@print')->name('requisitions.print');
Route::get('requisitions/{id}/preview','RequisitionController@preview')->name('requisitions.preview');
Route::get('requisitions/{purchase}/export-to-pdf','RequisitionController@generatePdf')->name('requisitions.pdf');

Route::get('requisitions/authorization/pending','RequisitionController@pending')->name('requisitions.pending');
Route::get('requisitions/authorization/approved','RequisitionController@approved')->name('requisitions.approved');
Route::get('requisitions/authorization/rejected','RequisitionController@rejected')->name('requisitions.rejected');

Route::get('incidents/authorization/pending','IncidentController@pending')->name('incidents.pending');
Route::get('incidents/authorization/approved','IncidentController@approved')->name('incidents.approved');
Route::get('incidents/authorization/rejected','IncidentController@rejected')->name('incidents.rejected');

Route::get('attendances/authorization/pending','AttendanceController@pending')->name('attendances.pending');
Route::get('attendances/authorization/approved','AttendanceController@approved')->name('attendances.approved');
Route::get('attendances/authorization/rejected','AttendanceController@rejected')->name('attendances.rejected');

Route::get('waste-collections/authorization/pending','WasteCollectionController@pending')->name('waste_collections.pending');
Route::get('waste-collections/authorization/approved','WasteCollectionController@approved')->name('waste_collections.approved');
Route::get('waste-collections/authorization/rejected','WasteCollectionController@rejected')->name('waste_collections.rejected');

Route::get('waste-disposals/authorization/pending','WasteDisposalController@pending')->name('waste_disposals.pending');
Route::get('waste-disposals/authorization/approved','WasteDisposalController@approved')->name('waste_disposals.approved');
Route::get('waste-disposals/authorization/rejected','WasteDisposalController@rejected')->name('waste_disposals.rejected');

Route::get('payrolls/authorization/pending','PayrollController@pending')->name('payrolls.pending');
Route::get('payrolls/authorization/approved','PayrollController@approved')->name('payrolls.approved');
Route::get('payrolls/authorization/rejected','PayrollController@rejected')->name('payrolls.rejected');

Route::get('retreads/authorization/pending','RetreadController@pending')->name('retreads.pending');
Route::get('retreads/authorization/approved','RetreadController@approved')->name('retreads.approved');
Route::get('retreads/authorization/rejected','RetreadController@rejected')->name('retreads.rejected');

Route::get('gate_passes/{department?}/authorization/pending','GatePassController@pending')->name('gate_passes.pending');
Route::get('gate_passes/{department?}/authorization/approved','GatePassController@approved')->name('gate_passes.approved');
Route::get('gate_passes/{department?}/authorization/rejected','GatePassController@rejected')->name('gate_passes.rejected');

Route::get('bills/{id}/email','BillController@email')->name('bills.email');
Route::get('bills/{id}/print','BillController@print')->name('bills.print');
Route::get('bills/{id}/preview','BillController@preview')->name('bills.preview');
Route::get('bills/{bill}/export-to-pdf','BillController@generatePdf')->name('bills.pdf');

Route::get('payrolls/salary/payslip/{id}/print','PayrollSalaryController@print')->name('payslips.print');
Route::get('payrolls/salary/payslip/{id}/preview','PayrollSalaryController@preview')->name('payslips.preview');
Route::get('payrolls/salary/payslip/{id}/export-to-pdf','PayrollSalaryController@generatePdf')->name('payslips.pdf');

Route::get('receipts/{id}/email','ReceiptController@email')->name('receipts.email');
Route::get('receipts/{id}/print','ReceiptController@print')->name('receipts.print');
Route::get('receipts/{id}/preview','ReceiptController@preview')->name('receipts.preview');
Route::get('receipts/{receipt}/export-to-pdf','ReceiptController@generatePdf')->name('receipts.pdf');
Route::get('receipts/delete','ReceiptController@delete')->name('receipts.deleted');



Route::get('/vehicles/{vehicle}/service','VehicleController@service')->name('vehicles.service');
Route::get('/vehicles/{vehicle}/activate','VehicleController@activate')->name('vehicles.activate');
Route::get('/vehicles/{vehicle}/deactivate','VehicleController@deactivate')->name('vehicles.deactivate');

Route::get('/horses/{horse}/service','HorseController@service')->name('horses.service');
Route::get('/horses/{horse}/activate','HorseController@activate')->name('horses.activate');
Route::get('/horses/{horse}/deactivate','HorseController@deactivate')->name('horses.deactivate');

Route::get('horses/{selectedFilter?}/report/pdf/','HorseController@horsesReportPDF')->name('horses.report.pdf');
Route::get('horses/{selectedFilter?}/report/print/','HorseController@horsesReportPrint')->name('horses.report.print');
Route::get('horses/{selectedFilter?}/report/','HorseController@horsesReportPreview')->name('horses.report.preview');

Route::get('horses/{selectedFilter?}/{from?}/{to?}/report/pdf/','HorseController@horsesReportPDF')->name('horses.report.pdf.range');
Route::get('horses/{selectedFilter?}/{from?}/{to?}/report/print/','HorseController@horsesReportPrint')->name('horses.report.print.range');
Route::get('horses/{selectedFilter?}/{from?}/{to?}/report/','HorseController@horsesReportPreview')->name('horses.report.preview.range');

Route::get('/trailers/{trailer}/activate','TrailerController@activate')->name('trailers.activate');
Route::get('/trailers/{trailer}/deactivate','TrailerController@deactivate')->name('trailers.deactivate');
Route::get('/trailers/{trailer}/service','TrailerController@service')->name('trailers.service');

Route::get('/drivers/{driver}/activate','DriverController@activate')->name('drivers.activate');
Route::get('/drivers/{driver}/deactivate','DriverController@deactivate')->name('drivers.deactivate');

Route::get('/vehicles/{id}/archive','VehicleController@archive')->name('vehicles.archive');
Route::get('/trailers/{id}/archive','TrailerController@archive')->name('trailers.archive');
Route::get('/horses/{id}/archive','HorseController@archive')->name('horses.archive');
Route::get('/employees/{id}/archive','EmployeeController@archive')->name('employees.archive');
Route::get('/drivers/{id}/archive','DriverController@archive')->name('drivers.archive');
Route::get('/employees/{employee}/activate','EmployeeController@activate')->name('employees.activate');
Route::get('/employees/{employee}/deactivate','EmployeeController@deactivate')->name('employees.deactivate');



Route::get('inventory_products/management','InventoryProductController@manage')->name('inventory_products.manage');
Route::get('products/management','ProductController@manage')->name('products.manage');

Route::get('logs/management','LogController@manage')->name('logs.manage');

Route::get('recoveries/authorization/pending','RecoveryController@pending')->name('recoveries.pending');
Route::get('recoveries/authorization/approved','RecoveryController@approved')->name('recoveries.approved');
Route::get('recoveries/authorization/rejected','RecoveryController@rejected')->name('recoveries.rejected');

Route::get('trips/authorization/pending','TripController@pending')->name('trips.pending');
Route::get('trips/authorization/approved','TripController@approved')->name('trips.approved');
Route::get('trips/authorization/rejected','TripController@rejected')->name('trips.rejected');

Route::get('transport_orders/authorization/pending','TransportOrderController@pending')->name('transport_orders.pending');
Route::get('transport_orders/authorization/approved','TransportOrderController@approved')->name('transport_orders.approved');
Route::get('transport_orders/authorization/rejected','TransportOrderController@rejected')->name('transport_orders.rejected');

Route::get('shifts/authorization/pending','ShiftController@pending')->name('shifts.pending');
Route::get('shifts/authorization/approved','ShiftController@approved')->name('shifts.approved');
Route::get('shifts/authorization/rejected','ShiftController@rejected')->name('shifts.rejected');

Route::get('transporters/deleted','TransporterController@deleted')->name('transporters.deleted');
Route::get('transporters/authorization/pending','TransporterController@pending')->name('transporters.pending');
Route::get('transporters/authorization/approved','TransporterController@approved')->name('transporters.approved');
Route::get('transporters/authorization/rejected','TransporterController@rejected')->name('transporters.rejected');

Route::get('bills/authorization/pending','BillController@pending')->name('bills.pending');
Route::get('bills/authorization/approved','BillController@approved')->name('bills.approved');
Route::get('bills/authorization/rejected','BillController@rejected')->name('bills.rejected');

Route::get('invoices/authorization/pending','InvoiceController@pending')->name('invoices.pending');
Route::get('invoices/authorization/approved','InvoiceController@approved')->name('invoices.approved');
Route::get('invoices/authorization/rejected','InvoiceController@rejected')->name('invoices.rejected');
Route::get('deleted/invoices','InvoiceController@deleted')->name('invoices.deleted');

Route::get('credit_notes/deleted','CreditNoteController@deleted')->name('credit_notes.deleted');
Route::get('credit_notes/authorization/pending','CreditNoteController@pending')->name('credit_notes.pending');
Route::get('credit_notes/authorization/approved','CreditNoteController@approved')->name('credit_notes.approved');
Route::get('credit_notes/authorization/rejected','CreditNoteController@rejected')->name('credit_notes.rejected');

Route::get('/employees/{id}/profile','EmployeeController@getProfile')->name('profile');
Route::post('/employees/{id}/change-password','EmployeeController@changePassword')->name('password.change');
Route::post('/employees/{id}/profile-update','EmployeeController@profile')->name('postProfile');

Route::get('/admin/dashboard','DashboardController@index')->name('dashboard.index');
Route::get('/third-parties/dashboard','DashboardController@thirdParty')->name('dashboard.third_parties');
Route::get('/third-parties/trips','TripController@thirdParty')->name('trips.third_parties');
Route::get('trips/{id}/third-parties/','TripController@thirdPartyShow')->name('trips.third_parties.show');

// Send SMS 
Route::get('/sendUserSms', [SmsController::class, 'sendUserSms'])->name('sendUserSms');
Route::get('/smsBalance', [SmsController::class, 'get_balance'])->name('smsBalance');

});



