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
        $today = Carbon::today();
        $now   = now();

        $fitnesses = Fitness::query()
            ->with(['user:id,email'])              // avoid N+1
            ->where('closed', 0)
            ->where('expires_at', '>=', $now)      // compare as datetime, not string
            ->where(function ($q) use ($today) {
                $q->where(function ($q) use ($today) {
                    $q->whereNotNull('first_reminder_at')
                    ->whereDate('first_reminder_at', '<=', $today)
                    ->where('first_reminder_at_status', false);
                })
                ->orWhere(function ($q) use ($today) {
                    $q->whereNotNull('second_reminder_at')
                    ->whereDate('second_reminder_at', '<=', $today)
                    ->where('second_reminder_at_status', false);
                })
                ->orWhere(function ($q) use ($today) {
                    $q->whereNotNull('third_reminder_at')
                    ->whereDate('third_reminder_at', '<=', $today)
                    ->where('third_reminder_at_status', false);
                });
            })
            ->get();

        if ($fitnesses->isEmpty()) {
            return;
        }

        foreach ($fitnesses as $fitness) {

            $fitness = Fitness::find($fitness->id); // Re-fetch to avoid mutating collection items
            
            // Determine what is due (and still unsent)
            $dueFirst  = $fitness->first_reminder_at  && $fitness->first_reminder_at->lte($today)  && ! $fitness->first_reminder_at_status;
            $dueSecond = $fitness->second_reminder_at && $fitness->second_reminder_at->lte($today) && ! $fitness->second_reminder_at_status;
            $dueThird  = $fitness->third_reminder_at  && $fitness->third_reminder_at->lte($today)  && ! $fitness->third_reminder_at_status;

            // Nothing due? Skip (extra safety)
            if (! ($dueFirst || $dueSecond || $dueThird)) {
                continue;
            }

            // Send email (ideally queue this - see note below)
            $email = $fitness->user?->email;
            if ($email) {
                Mail::to($email)->send(new SendReminderEmails($fitness));
            }

            // Mark only what was actually due/unsent
            if ($dueFirst)  $fitness->first_reminder_at_status  = true;
            if ($dueSecond) $fitness->second_reminder_at_status = true;
            if ($dueThird)  $fitness->third_reminder_at_status  = true;

            $fitness->save();
        }
    }
}
