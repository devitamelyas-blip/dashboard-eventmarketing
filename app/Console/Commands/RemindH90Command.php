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
    protected $signature = 'devon:remind-h90';

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
        }

        $this->info("Selesai. Total {$count} email pengingat H+90 terkirim.");
        return Command::SUCCESS;
    }
}
