<?php

namespace App\Console\Commands;

use App\Models\Respondent;
use Illuminate\Console\Command;

class AnonymizeRespondentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devon:anonymize-respondents {--days=365 : Batas retensi usia data dalam hari}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menyamarkan data pribadi responden survei (PII) demi kepatuhan retensi privasi tanpa merusak KPI';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Memproses penyusutan dan penyamaran identitas responden lebih lama dari {$days} hari (sebelum {$cutoffDate->toDateString()})...");

        $query = Respondent::where('created_at', '<', $cutoffDate)
            ->where(function ($q) {
                $q->whereNotNull('name')
                  ->orWhereNotNull('phone')
                  ->orWhere('organization', '!=', 'Anonim');
            });

        $total = $query->count();

        if ($total === 0) {
            $this->info("Tidak ada data responden yang memenuhi kriteria retensi.");
            return Command::SUCCESS;
        }

        $query->update([
            'name' => null,
            'phone' => null,
            'organization' => 'Anonim',
        ]);

        $this->info("Berhasil menyamarkan {$total} data identitas responden.");
        $this->line("Catatan: Hash survei (email_hash) dan agregat jawaban KPI tetap utuh untuk historis evaluasi.");

        return Command::SUCCESS;
    }
}
