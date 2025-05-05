<?php

namespace App\Console\Commands;

use Carbon\Carbon;
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

        $fitnesses = Fitness::whereDate('first_reminder_at','<=', Carbon::today())
        ->where('first_reminder_at_status', FALSE)
        ->where('expires_at','>=', now()->toDateTimeString())
        ->where('closed', 0)
        ->orWhereDate('second_reminder_at','<=', Carbon::today())
        ->where('second_reminder_at_status', FALSE)
        ->where('expires_at','>=', now()->toDateTimeString())
        ->where('closed', 0)
        ->orWhereDate('third_reminder_at','<=', Carbon::today())
        ->where('third_reminder_at_status', FALSE)
        ->where('expires_at','>=', now()->toDateTimeString())
        ->where('closed', 0)
        ->get();

        if ($fitnesses) {
            foreach ($fitnesses as $fitness) {
                if ($fitness->user->email) {
                    Mail::to($fitness->user->email)->send(new SendReminderEmails($fitness));
                }

                if ($fitness->first_reminder_at <=  Carbon::today() ) {
                    $fitness->first_reminder_at_status = True;
                   
                }
                if ($fitness->second_reminder_at <=  Carbon::today() ) {
                    $fitness->second_reminder_at_status = True;
                    
                }
                if ($fitness->third_reminder_at <=  Carbon::today() ) {
                    $fitness->third_reminder_at_status = True;
                   
                }
                $fitness->update();
            }
        }
       
    }
}
