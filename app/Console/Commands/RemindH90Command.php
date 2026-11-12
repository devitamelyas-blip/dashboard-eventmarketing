<?php

namespace App\Console\Commands;

use App\Enums\Goal;
use App\Mail\SalesUpdateReminderMail;
use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class RemindH90Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:remind-h90';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim pengingat email batas waktu H+90 untuk event Sales yang belum diisi omzetnya';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $events = Event::query()
            ->where('goal', Goal::SALES)
            ->where('event_date', '<=', now()->subDays(90))
            ->whereDoesntHave('salesUpdate', function ($q) {
                $q->whereNotNull('revenue');
            })
            ->with(['branch', 'picUser'])
            ->get();

        $count = 0;
        foreach ($events as $event) {
            $email = $event->submitter_email ?? $event->picUser?->email;
            if ($email) {
                try {
                    Mail::to($email)->send(new SalesUpdateReminderMail($event));
                    $count++;
                    $this->info("Pengingat H+90 terkirim ke [{$email}] untuk event {$event->code}");
                } catch (\Throwable $e) {
                    $this->error("Gagal mengirim ke [{$email}]: " . $e->getMessage());
                }
            }

            if ($event->picUser) {
                try {
                    $event->picUser->notify(new \App\Notifications\EventActivityNotification(
                        title: 'Batas Waktu H+90 Jatuh Tempo',
                        message: "Event Sales [{$event->code}] {$event->name} telah melewati batas waktu 90 hari. Mohon laporkan omzet aktual dan bukti invoice.",
                        url: route('events.sales.edit', $event->id),
                        type: 'warning'
                    ));
                } catch (\Throwable $e) {
                    // silently proceed
                }
            }
        }

        $this->info("Selesai. Total {$count} email pengingat H+90 terkirim.");
        return Command::SUCCESS;
    }
}
