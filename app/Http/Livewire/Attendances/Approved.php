<?php

namespace App\Http\Livewire\Attendances;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Attendance;
use Livewire\WithPagination;

class Approved extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    protected $attendances;
    public $attendance;
    public $attendance_id;
    public $authorize;
    public $comments;

    public function updatingSearch()
    {
        $this->resetPage(); // important for pagination
    }

    public function render()
    {
         $search = trim($this->search);

        $attendances = Attendance::query()
            ->where('authorization', 'approved')

            // ✅ date filter on date when from/to provided
            ->when($this->from || $this->to, function ($q) {
                $from = $this->from
                    ? Carbon::parse($this->from)->startOfDay()
                    : null;

                $to = $this->to
                    ? Carbon::parse($this->to)->endOfDay()
                    : null;

                if ($from && $to) {
                    $q->whereBetween('date', [$from, $to]);
                } elseif ($from) {
                    $q->where('date', '>=', $from);
                } else { // only $to
                    $q->where('date', '<=', $to);
                }
            })

            ->when($search !== '', function ($q) use ($search) {

                $q->where(function ($qq) use ($search) {

                    // department name
                    $qq->whereHas('department', function ($d) use ($search) {
                        $d->where('name', 'like', "%{$search}%");
                    })

                    // user name / surname / full name
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(name,' ',surname) LIKE ?", ["%{$search}%"]);
                    })

                    // date/time on created_at (works for "2026-02-05", "14:30", "2026-02-05 14")
                    ->orWhereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("DATE_FORMAT(created_at, '%H:%i') LIKE ?", ["%{$search}%"]);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.attendances.approved', [
            'attendances' => $attendances,
        ]);
    }
}
