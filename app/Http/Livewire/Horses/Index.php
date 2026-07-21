<?php

namespace App\Http\Livewire\Horses;

use App\Exports\HorsesExport;
use App\Models\Bill;
use App\Models\Booking;
use App\Models\Checklist;
use App\Models\Currency;
use App\Models\Horse;
use App\Models\Incident;
use App\Models\Inspection;
use App\Models\Mileage;
use App\Models\Trip;
use App\Services\Sage\SageSyncService;
use App\Services\Sage\SageIntegration;
use App\Jobs\Sage\SyncHorseToSageJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    
    private $horses;
    public $currencies;
    public $revenue;
    public $currency_id;
    public $horse_id;
    public $from;
    public $to;

    // Sage sync — selected horse ids for bulk sync.
    public $sageSelected = [];

    public function exportHorsesCSV(Excel $excel){

        return $excel->download(new HorsesExport($this->from, $this->to, $this->search), 'horses_'.time().'.csv', Excel::CSV);
    }
    public function exportHorsesPDF(Excel $excel){

        return $excel->download(new HorsesExport($this->from, $this->to, $this->search), 'horses_'.time().'.pdf', Excel::DOMPDF);
    }
    public function exportHorsesExcel(Excel $excel){
        return $excel->download(new HorsesExport($this->from, $this->to, $this->search), 'horses_'.time().'.xlsx');
    }

    public function mount(){
        $this->from = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->to   = Carbon::now()->format('Y-m-d');
        $this->resetPage();
        $this->currencies = Currency::all();
      }

      public function updatingSearch()
      {
          $this->resetPage();
      }

    public function deactivate($id){
        $horse = Horse::find($id);
        $horse->status = 0 ;
        $horse->update();
        Session::flash('success','Horse successfully deactivated');
        return redirect(route('horses.index'));
    }

    public function activate($id){
        $horse = Horse::find($id);
        $horse->status = 1 ;
        $horse->update();
        Session::flash('success','Horse successfully deactivated');
        return redirect(route('horses.index'));
    }

    public function getKpisProperty(): array
    {
        $horses = Horse::all();
        $totalKm = Trip::whereIn('horse_id', $horses->pluck('id'))
        ->whereBetween('created_at', [$this->from, $this->to])
        ->where('authorization', 'approved')
        ->where('trip_status', '!=', 'Cancelled')
        ->sum('distance') ?: 1;
        $totalHorses = $horses->count() ?: 1;

        $scheduledInspections = Checklist::whereBetween('next_inspection_at', [$this->from, $this->to])->count() ?: 1;
        $completedInspections = Checklist::whereBetween('next_inspection_at', [$this->from, $this->to])->where('status', 'completed')->count();

        $springFailures = Booking::where('type', 'suspension')
            ->whereBetween('created_at', [$this->from, $this->to])
            ->count();

       

        $totalDowntimeMinutes = Booking::whereHas('problem_category', fn($q) => $q->where('name', 'like', '%suspension%'))
            ->whereBetween('created_at', [$this->from, $this->to])
            ->where('status', 0)
            ->whereNotNull('in_date')->whereNotNull('in_time')
            ->whereNotNull('out_of_workshop_date')->whereNotNull('out_of_workshop_time')
            ->whereRaw('TIMESTAMP(out_of_workshop_date, out_of_workshop_time) >= TIMESTAMP(in_date, in_time)')
            ->selectRaw('SUM(TIMESTAMPDIFF(MINUTE, TIMESTAMP(in_date, in_time), TIMESTAMP(out_of_workshop_date, out_of_workshop_time))) AS total_minutes')
            ->value('total_minutes') ?? 0;

       

        $maintenanceCost = Bill::whereNotNull('ticket_id')
            ->whereHas('ticket.booking.problem_category', fn($q) => $q->where('name', 'like', '%suspension%'))
            ->whereBetween('created_at', [$this->from, $this->to])
            ->where('authorization', 'approved')
            ->sum('total');

        return [
            'compliance_rate'   => round(($completedInspections / $scheduledInspections) * 100, 1),
            'failure_rate'      => round(($springFailures / $totalKm) * 100000, 2),
            'avg_downtime'      => round(($totalDowntimeMinutes / 60) / $totalHorses, 1),
            'cost_per_km'       => round($maintenanceCost / $totalKm, 4),
            'safety_incidents'  => Booking::where('is_safety_incident', true)->whereBetween('in_date', [$this->from, $this->to])->count(),
            'in_service_count'  => $horses->where('service', 1)->count(),
        ];
    }

    public function calculateCPK($id){

            $cpk = Null;
            $distance = Null;
            $bills = Bill::where('horse_id',$id)->where('authorization','approved')->whereBetween('created_at', [$this->from, $this->to])->get();

            $expenses = 0.0;

            if (!empty($bills)) {
                foreach ($bills as $bill) {
                    $amount = ($bill->currency_id == Auth::user()->employee->company->currency_id) 
                        ? $bill->total 
                        : $bill->exchange_amount;

                    $expenses += (float) $amount;
                }
            } else {
                $expenses = null;
            }

            $last_mileage = Mileage::where('horse_id',$id)->whereBetween('created_at', [$this->from, $this->to])->orderBy('created_at','desc')->first();
            $first_mileage = Mileage::where('horse_id',$id)->whereBetween('created_at', [$this->from, $this->to])->orderBy('created_at','asc')->first();
            
            if ((isset($last_mileage) && is_numeric($last_mileage)) && (isset($first_mileage) && is_numeric($first_mileage))) {

                if ($last_mileage > $first_mileage) {
                    $distance = $last_mileage - $first_mileage;
                }else{
                    $distance = Null;
                }

               
            }else {
                $distance = Null;
            }
           
            if ((isset($expenses) && is_numeric($expenses)) && (isset($distance) && is_numeric($distance)  )  ) {
                $cpk = $expenses / $distance;
                return $cpk;
            }else{
                return $cpk;
            }
          

           

    }

    /**
     * Whether the acting user's company has an active Sage integration.
     * Drives visibility of all Sage controls; computed once per request.
     */
    public function getSageEnabledProperty()
    {
        return SageIntegration::enabledForUser();
    }

    /**
     * Sync one horse to Sage Intacct (as a Class) inline. Used for both the
     * initial sync and retry — the service is idempotent.
     */
    public function syncToSage($id)
    {
        if (! $this->sageEnabled) {
            return;
        }

        $horse  = Horse::findOrFail($id);
        $result = app(SageSyncService::class)->syncHorse($horse);

        $this->dispatchBrowserEvent('alert', [
            'type'    => ! empty($result['success']) ? 'success' : (! empty($result['skipped']) ? 'warning' : 'error'),
            'message' => ! empty($result['success'])
                ? 'Horse synced to Sage (class ' . ($result['external_id'] ?? '') . ').'
                : 'Sage sync: ' . ($result['error'] ?? 'unknown error'),
        ]);
    }

    /** Retry a failed horse sync (idempotent — never duplicates in Sage). */
    public function retrySync($id)
    {
        $this->syncToSage($id);
    }

    /** Bulk sync the selected horses via queued jobs. */
    public function bulkSyncToSage()
    {
        if (! $this->sageEnabled) {
            return;
        }

        $ids = array_filter($this->sageSelected);

        foreach ($ids as $id) {
            SyncHorseToSageJob::dispatch((int) $id);
        }

        $this->sageSelected = [];

        $this->dispatchBrowserEvent('alert', [
            'type'    => count($ids) ? 'success' : 'warning',
            'message' => count($ids)
                ? count($ids) . ' horse(s) queued for Sage sync.'
                : 'Select at least one horse to sync.',
        ]);
    }

    public function render()
    {
        if (isset($this->search)) {
            return view('livewire.horses.index',[
                'horses' => Horse::with('transporter:id,name','horse_make:id,name','horse_model:id,name')
                ->when($this->sageEnabled, fn ($q) => $q->with('sageMapping'))
                ->where('archive',0)
                ->where('horse_number','like', '%'.$this->search.'%')
                ->orWhere('registration_number','like', '%'.$this->search.'%')
                ->orWhere('fleet_number','like', '%'.$this->search.'%')
                ->orWhereHas('horse_make', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('horse_model', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('transporter', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('registration_number','asc')->paginate(10),
                'kpis' => $this->kpis,
            ]);
        }else{
            return view('livewire.horses.index',[
                'horses' => Horse::with('transporter:id,name','horse_make:id,name','horse_model:id,name')
                ->when($this->sageEnabled, fn ($q) => $q->with('sageMapping'))
                ->where('archive',0)->orderBy('registration_number','asc')->paginate(10),
                'kpis' => $this->kpis,
            ]);
        }
       
    }
}
