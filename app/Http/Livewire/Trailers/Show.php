<?php

namespace App\Http\Livewire\Trailers;

use App\Models\Bill;
use App\Models\Fuel;
use App\Models\Booking;
use App\Models\Trailer;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Models\TyreAssignment;
use App\Exports\TrailerFuelExport;
use App\Exports\TrailerBillsExport;
use App\Exports\TrailerBookingExport;
use App\Exports\TrailerTyreAssignmentExport;

class Show extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $trailer;
    public $trailer_id;
    public $documents;
    public $images;
    public $fitnesses;
  
    
    private $trips;

    public function mount($id){
        $this->trailer_id = $id;
        $this->trailer = Trailer::find($id);
        $this->documents = $this->trailer->trailer_documents;
        $this->images = $this->trailer->trailer_images;
        $this->fitnesses = $this->trailer->fitnesses;
    }


    public function exportBookingsCSV(Excel $excel){

        return $excel->download(new TrailerBookingExport($this->trailer_id), 'trailer_garage_bookings.csv', Excel::CSV);
    }
    public function exportBookingsPDF(Excel $excel){

        return $excel->download(new TrailerBookingExport($this->trailer_id), 'trailer_garage_bookings.pdf', Excel::DOMPDF);
    }
    public function exportBookingsExcel(Excel $excel){
        return $excel->download(new TrailerBookingExport($this->trailer_id), 'trailer_garage_bookings.xlsx');
    }

    public function exportBillsCSV(Excel $excel){

        return $excel->download(new TrailerBillsExport($this->trailer_id), 'trailer_bills.csv', Excel::CSV);
    }
    public function exportBillsPDF(Excel $excel){

        return $excel->download(new TrailerBillsExport($this->trailer_id), 'trailer_bills.pdf', Excel::DOMPDF);
    }
    public function exportBillsExcel(Excel $excel){
        return $excel->download(new TrailerBillsExport($this->trailer_id), 'trailer_bills.xlsx');
    }


    public function exportTyreAssignmentsCSV(Excel $excel){

        return $excel->download(new TrailerTyreAssignmentExport($this->trailer_id), 'trailer_assigned_tyres.csv', Excel::CSV);
    }
    public function exportTyreAssignmentsPDF(Excel $excel){

        return $excel->download(new TrailerTyreAssignmentExport($this->trailer_id), 'trailer_assigned_tyres.pdf', Excel::DOMPDF);
    }
    public function exportTyreAssignmentsExcel(Excel $excel){
        return $excel->download(new TrailerTyreAssignmentExport($this->trailer_id), 'trailer_assigned_tyres.xlsx');
    }

    public function getBookingsProperty(){

        return Booking::query()->with('trailer','employee','vendor','service_type')->where('trailer_id',$this->trailer_id)->whereYear('created_at', date('Y'))->orderBy('created_at','desc')->paginate(10);
       
    }
    public function getTyreAssignmentsProperty(){

        return TyreAssignment::query()->with('trailer','user')->where('trailer_id',$this->trailer_id)->whereYear('created_at', date('Y'))->orderBy('created_at','desc')->paginate(10);
       
    }

    public function getBillsProperty(){

        return Bill::query()->with('vendor','container', 'trailer', 'fuel','transporter','invoice','ticket','purchase')->where('trailer_id',$this->trailer_id)->whereYear('created_at', date('Y'))->orderBy('created_at','desc')->paginate(10);
       
    }

    public function render()
    {
      
        return view('livewire.trailers.show',[
            'bookings' =>  $this->bookings,
            'bills' =>  $this->bills,
            'tyre_assignments' =>  $this->tyre_assignments
        ]);
    }
}
