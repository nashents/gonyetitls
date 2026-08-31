<?php

namespace App\Http\Livewire\Freight\Imports;

use App\Exports\FreightJobTemplateExport;
use App\Imports\FreightJobsImport as FreightJobsImportClass;
use App\Models\ImportLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class FreightJobsImport extends Component
{
    use WithFileUploads;

    public $file;
    public array $skippedRows = [];
    public ?array $summary = null;

    public function mount()
    {
        abort_unless(Auth::user()->is_admin(), 403);
    }

    protected function rules()
    {
        return [
            'file' => 'required|file|mimes:xls,xlsx',
        ];
    }

    public function downloadTemplate()
    {
        return Excel::download(new FreightJobTemplateExport, 'freight-jobs-import-template.xlsx');
    }

    public function import()
    {
        $this->validate();
        ini_set('max_execution_time', 300);

        $log = ImportLog::create([
            'user_id' => Auth::id(),
            'company_id' => Auth::user()->employee?->company_id,
            'import_type' => 'freight_jobs',
            'original_filename' => $this->file->getClientOriginalName(),
            'status' => 'completed',
            'started_at' => now(),
        ]);

        try {
            $import = new FreightJobsImportClass;
            $import->import($this->file);

            $this->skippedRows = $import->getSkippedRows();
            $this->summary = [
                'rows_created' => $import->rowsCreated,
                'rows_skipped' => count($this->skippedRows),
            ];

            $log->update([
                'rows_processed' => $import->rowsCreated + count($this->skippedRows),
                'rows_created' => $import->rowsCreated,
                'rows_skipped' => count($this->skippedRows),
                'skipped_details' => $this->skippedRows,
                'status' => count($this->skippedRows) ? 'completed_with_errors' : 'completed',
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'completed_at' => now()]);
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'Import failed: ' . $e->getMessage()]);
            return;
        }

        $this->reset('file');
        $this->dispatchBrowserEvent('alert', [
            'type' => count($this->skippedRows) ? 'warning' : 'success',
            'message' => "{$this->summary['rows_created']} freight job(s) imported, {$this->summary['rows_skipped']} row(s) skipped.",
        ]);
    }

    public function render()
    {
        return view('livewire.freight.imports.freight-jobs-import', [
            'recentLogs' => ImportLog::where('import_type', 'freight_jobs')->latest()->limit(10)->get(),
        ]);
    }
}
