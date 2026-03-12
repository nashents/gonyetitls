<?php

namespace App\Console\Commands;

use App\Exports\AttendanceRegisterExport;
use App\Exports\BookingsExport;
use App\Exports\EmployeesLeaveExport;
use App\Exports\ShiftsDailyExport;
use App\Mail\DailyExportMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class SendDailyReports extends Command
{
    protected $signature = 'reports:send-daily 
        {--to=* : Override recipients (repeatable)}
        {--keep : Do not delete temp files (debug)}';

    protected $description = 'Generate multiple Excel reports, email in one message, then cleanup temp files';

    public function handle(): int
    {
        $recipients = $this->option('to');
        if (empty($recipients)) {
            $recipients = config('reports.recipients', []);
        }

        if (empty($recipients)) {
            $this->error('No recipients configured. Set REPORT_EMAIL_RECIPIENTS in .env');
            return self::FAILURE;
        }

        $date = now()->format('Y-m-d');
        $runId = $date . '_' . Str::lower(Str::random(8));
        $tmpDir = "tmp/reports/{$runId}";

        // Ensure directory exists on local disk (storage/app)
        Storage::disk('local')->makeDirectory($tmpDir);
        
        $date = now()->subDay()->toDateString();
        // Define the reports you want to generate
        $reports = [
            [
                'export' => new ShiftsDailyExport(),
                'file'   => "shifts-{$date}.xlsx",
            ],
            [
                'export' => new BookingsExport(),
                'file'   => "garage_bookings-{$date}.xlsx",
            ],
            [
                'export' => new EmployeesLeaveExport(),
                'file'   => "employee_leaves-{$date}.xlsx",
            ],
            [
                
                'export' => new AttendanceRegisterExport(
                    $date,
                    $date,
                    null,
                    null,
                    'date'
                ),
                'file' => "attendance_register-{$date}.xlsx",
            ],
           
        ];

        $attachmentMeta = [];
        $relativePaths = [];

        try {
            // 1) Generate all excel files to temp folder
            foreach ($reports as $r) {
                $relativePath = "{$tmpDir}/{$r['file']}";
                Excel::store($r['export'], $relativePath, 'local'); // writes to storage/app/...

                $relativePaths[] = $relativePath;

                $attachmentMeta[] = [
                    'path' => storage_path('app/' . $relativePath),
                    'as'   => $r['file'],
                    'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ];
            }

            // 2) Send one email with multiple attachments
            $subject = "Daily Reports ({$date})";

            Mail::to($recipients)->send(
                new DailyExportMail($attachmentMeta, $subject)
            );

            $this->info('Sent daily reports to: ' . implode(', ', $recipients));

        } catch (\Throwable $e) {
            $this->error('Failed: ' . $e->getMessage());
            // fall through to cleanup (unless --keep)
            throw $e;

        } finally {
            // 3) Cleanup temp files unless debugging
            if (!$this->option('keep')) {
                // delete files then directory
                foreach ($relativePaths as $rp) {
                    Storage::disk('local')->delete($rp);
                }
                Storage::disk('local')->deleteDirectory($tmpDir);
            } else {
                $this->warn("Kept temp files in storage/app/{$tmpDir}");
            }
        }

        return self::SUCCESS;
    }
}