<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reminder;
use App\Events\PostCreated;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckDueReminders extends Command
{
    protected $signature = 'reminders:check-due';
    protected $description = 'Check for due reminders and broadcast';

    public function handle()
    {
        Log::channel('query_log')->info('Checking due reminders');

        $now = Carbon::now();

        $reminders = Reminder::whereDate('date', $now->toDateString())
            ->whereTime('date', '<=', $now->format('H:i:s'))
            ->where('notified', false)
            ->get();

        Log::channel('query_log')->info('Found ' . $reminders->count() . ' due reminders');

        foreach ($reminders as $reminder) {
            event(new PostCreated($reminder, 1));
            $reminder->update(['notified' => true]);
        }

        $this->info("Checked reminders at " . $now->toDateTimeString());
    }
}
