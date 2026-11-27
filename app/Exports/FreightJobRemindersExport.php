<?php

namespace App\Exports;

use App\Models\FreightJobReminder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

/**
 * Freight > Job Reminders export (Excel / CSV / PDF): logo at A2, headings
 * from A7. Takes the page's filters (FreightJobReminder::scopeListFilter) and
 * the signed-in user's visibility; no user (data backup) = everything.
 */
class FreightJobRemindersExport implements FromQuery, ShouldAutoSize, WithMapping, WithHeadings, WithEvents, WithDrawings, WithCustomStartCell
{
    use Exportable;

    public function __construct(private array $filters = ['status' => 'all'])
    {
    }

    public function query()
    {
        return FreightJobReminder::query()
            ->with(['freight_job.customer:id,name', 'user:id,name,surname', 'completer:id,name,surname'])
            ->visibleTo(Auth::user())
            ->listFilter($this->filters);
    }

    public function map($reminder): array
    {
        $job = $reminder->freight_job;
        $days = $reminder->daysLeft();
        $name = fn ($u) => $u ? trim($u->name . ' ' . $u->surname) : null;

        return [
            $job?->job_number,
            $job?->customer_label,
            $job?->customer_reference,
            $reminder->title,
            $reminder->due_at?->format('d M Y H:i'),
            $reminder->isDone() ? 'Done' : ($days < 0 ? 'Overdue (' . abs($days) . 'd)' : ($days === 0 ? 'Due today' : 'Open')),
            $reminder->remindLabel() . ($reminder->daily_when_overdue ? ', daily when overdue' : ''),
            $reminder->staffUsers()->map($name)->filter()->implode(', '),
            $reminder->notify_client ? 'Yes' : 'No',
            $reminder->extra_emails,
            $reminder->notes,
            $name($reminder->user),
            $reminder->created_at?->format('d M Y'),
            $reminder->last_sent_at?->format('d M Y H:i'),
            $reminder->completed_at?->format('d M Y'),
            $name($reminder->completer),
        ];
    }

    public function headings(): array
    {
        return [
            'Job #', 'Customer', 'Customer Ref', 'Reminder', 'Due', 'Status', 'Emails', 'Staff',
            'Client Emailed', 'Other Emails', 'Notes', 'Set By', 'Set On', 'Last Sent', 'Done On', 'Done By',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A7:P7')->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ],
                ]);
            },
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $company = Auth::user()?->employee?->company;

        if ($company) {
            $drawing->setName($company->name);
            $drawing->setDescription($company->name . ' Logo');
        }
        $logo = $company ? public_path('/images/uploads/' . $company->logo) : null;
        $drawing->setPath($logo && file_exists($logo) ? $logo : public_path('/images/uploads/logo.png'));
        $drawing->setHeight(90);
        $drawing->setCoordinates('A2');

        return $drawing;
    }

    public function startCell(): string
    {
        return 'A7';
    }
}
