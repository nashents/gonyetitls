<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyExportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array<int, array{path: string, as?: string, mime?: string}>
     */
    public array $files;
    public $company;

    public string $subjectLine;

    /**
     * @param array<int, array<string, string>> $files
     */
    public function __construct(array $files, string $subjectLine)
    {
        $this->files       = $files;
        $this->subjectLine = $subjectLine;
         $this->company = Company::find(2) ?? Company::find(1);
    }

    public function build()
    {
        // base mail
        $mail = $this->subject($this->subjectLine)
            ->view('emails.daily-reports'); // put any simple blade here

        // attach all files
        foreach ($this->files as $file) {
            // ignore missing paths just in case
            if (empty($file['path'])) {
                continue;
            }

            $mail->attach($file['path'], [
                'as'   => $file['as']   ?? null,
                'mime' => $file['mime'] ?? null,
            ]);
        }

        return $mail;
    }
}