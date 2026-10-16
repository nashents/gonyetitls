<?php

namespace App\Exports;

use App\Services\ReportFormatter;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * Excel version of any financial report that already has a print/PDF
 * statement partial. The partial is rendered with ReportFormatter in raw
 * mode, so the workbook shows exactly the rows the PDF does, with amounts
 * as real numbers instead of formatted text.
 */
class FinancialReportExport implements FromView, WithTitle, WithEvents, ShouldAutoSize
{
    /**
     * @param string $partial statement partial view, e.g. reports.income_statements.partials.statement
     * @param array  $data    the same data array the PDF view receives
     * @param array  $header  reports.shared._header params (title, asOfDate, from, to, selectedType)
     */
    public function __construct(
        private string $partial,
        private array $data,
        private array $header
    ) {
    }

    public function view(): View
    {
        ReportFormatter::$raw = true;

        try {
            $html = view('reports.shared.excel', $this->data + [
                'partial' => $this->partial,
                'header' => $this->header,
            ])->render();
        } finally {
            ReportFormatter::$raw = false;
        }

        return view('reports.shared.excel_raw', ['html' => $html]);
    }

    public function title(): string
    {
        // Excel sheet names: max 31 chars, none of \ / ? * [ ] :
        return mb_substr(str_replace(['\\', '/', '?', '*', '[', ']', ':'], ' ', $this->header['title'] ?? 'Report'), 0, 31);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();

                foreach ($ws->getRowIterator() as $row) {
                    foreach ($row->getCellIterator() as $cell) {
                        if ($cell->getDataType() === DataType::TYPE_NUMERIC) {
                            $cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00;-#,##0.00');
                        }
                    }
                }

                $ws->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            },
        ];
    }
}
