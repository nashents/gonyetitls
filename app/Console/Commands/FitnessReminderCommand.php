<?php

namespace App\Console\Commands;

use App\Mail\SendMail;
use App\Models\Fitness;
use Illuminate\Console\Command;
use App\Mail\SendReminderEmails;
use Illuminate\Support\Facades\Mail;

class FitnessReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending Fitness Reminders to Company Email';

    public function sendEmail($company){

    }
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $fitnesses = Fitness::where('reminder_at','<=', now()->toDateTimeString())
                            ->where('expires_at','>=', now()->toDateTimeString())->get();

        foreach ($fitnesses as $fitness) {
            $company = $fitness->company;
            if (isset($company->email) && isset($fitness->user->email) && ($company->noreply)) {
                Mail::to($company->email)->send(new SendReminderEmails($fitness, $company));
            }
        }
    }
}
