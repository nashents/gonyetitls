<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\SerializesModels;


class DailyExportMail extends Mailable
{
    use Queueable, SerializesModels;
    public $company;
   
    /**
     * @param array<int, array{path:string, as?:string, mime?:string}> $files
     */
    public function __construct(
        public array $files,
        public string $subjectLine
    ) {
        $this->company = Company::find(2) ?? Company::find(1);
    }

    public function build()
    {
        $mail = $this->subject($this->subjectLine)
            ->view('emails.daily-reports');

        foreach ($this->files as $file) {
            $mail->attach($file['path'], array_filter([
                'as'   => $file['as']  ?? null,
                'mime' => $file['mime'] ?? null,
            ]));
        }

        return $mail;
    }
}