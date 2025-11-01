<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Mail\BestDateMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendBestDateEmails extends Command
{
    protected $signature = 'events:send-best-date';
    protected $description = 'Send best date emails for events created 1 day ago';

    public function handle()
    {
        // Get events created exactly 1 day ago with a best date
        $events = Event::whereNotNull('best_date_id')
            ->whereDate('created_at', Carbon::yesterday())
            ->get();

        foreach ($events as $event) {
            Mail::to($event->creator->email)->send(new BestDateMail($event));
            $this->info("Email sent for event ID: {$event->id}");
        }
    }
}
