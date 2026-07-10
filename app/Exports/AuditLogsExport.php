<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use OwenIt\Auditing\Models\Audit;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AuditLogsExport implements
    FromQuery,
    ShouldAutoSize,
    WithMapping,
    WithHeadings
{
    use Exportable;

    public $from;
    public $to;
    public $user_id;
    public $event;
    public $auditable_type;
    public $search;

    public function __construct($from = null, $to = null, $user_id = null, $event = null, $auditable_type = null, $search = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->user_id = $user_id;
        $this->event = $event;
        $this->auditable_type = $auditable_type;
        $this->search = $search;
    }

    public function query()
    {
        $search = trim($this->search ?? '');

        return Audit::query()
            ->with('user')
            ->when(filled($this->from) && filled($this->to), function (Builder $q) {
                $q->whereBetween('created_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59']);
            })
            ->when(filled($this->from) && !filled($this->to), function (Builder $q) {
                $q->where('created_at', '>=', $this->from . ' 00:00:00');
            })
            ->when(!filled($this->from) && filled($this->to), function (Builder $q) {
                $q->where('created_at', '<=', $this->to . ' 23:59:59');
            })
            ->when(filled($this->user_id), function (Builder $q) {
                $q->where('user_id', $this->user_id);
            })
            ->when(filled($this->event), function (Builder $q) {
                $q->where('event', $this->event);
            })
            ->when(filled($this->auditable_type), function (Builder $q) {
                $q->where('auditable_type', $this->auditable_type);
            })
            ->when($search !== '', function (Builder $q) use ($search) {
                $q->where(function (Builder $qq) use ($search) {
                    $like = "%{$search}%";
                    $qq->where('event', 'like', $like)
                        ->orWhere('auditable_type', 'like', $like)
                        ->orWhere('auditable_id', 'like', $like)
                        ->orWhere('ip_address', 'like', $like)
                        ->orWhere('url', 'like', $like)
                        ->orWhereHas('user', function (Builder $u) use ($like) {
                            $u->where('name', 'like', $like)
                                ->orWhere('surname', 'like', $like)
                                ->orWhereRaw("CONCAT(name,' ',surname) LIKE ?", [$like]);
                        });
                });
            })
            ->orderByDesc('created_at');
    }

    public function map($row): array
    {
        $userName = $row->user ? trim($row->user->name . ' ' . $row->user->surname) : 'System';

        return [
            $row->id,
            $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '',
            $userName,
            ucfirst($row->event),
            class_basename($row->auditable_type),
            $row->auditable_id,
            $this->formatValues($row->old_values),
            $this->formatValues($row->new_values),
            $row->url,
            $row->ip_address,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Date & Time',
            'User',
            'Event',
            'Model',
            'Record ID',
            'Old Values',
            'New Values',
            'URL',
            'IP Address',
        ];
    }

    protected function formatValues($values): string
    {
        if (empty($values)) {
            return '';
        }

        $parts = [];
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $parts[] = "{$key}: {$value}";
        }

        return implode('; ', $parts);
    }
}
