<?php

namespace App\Console\Commands;

use App\Enums\EventType;
use App\Enums\Goal;
use App\Enums\VerificationStatus;
use App\Models\Branch;
use App\Models\Event;
use App\Models\EventReport;
use App\Models\SalesUpdate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportLegacyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devon:import-legacy {file : Path ke file CSV legacy} {--dry-run : Jalankan simulasi tanpa menyimpan ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data historis event (Sheet 01 legacy) dari file CSV';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');
        $isDryRun = (bool) $this->option('dry-run');

        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->error("File [{$filePath}] tidak ditemukan atau tidak dapat dibaca.");
            return Command::FAILURE;
        }

        if ($isDryRun) {
            $this->warn("=== MODE SIMULASI (DRY-RUN) AKTIF — TIDAK ADA DATA YANG DISIMPAN ===");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->error("Gagal membuka file CSV.");
            return Command::FAILURE;
        }

        // Read header
        $rawHeader = fgetcsv($handle, 4096, ',');
        if (!$rawHeader) {
            $this->error("File CSV kosong.");
            fclose($handle);
            return Command::FAILURE;
        }

        $header = array_map(fn ($col) => strtolower(trim($col)), $rawHeader);
        $headerMap = array_flip($header);

        $defaultUser = User::role('admin')->first() ?? User::first();
        if (!$defaultUser) {
            $defaultUser = User::create([
                'name' => 'System Administrator',
                'email' => 'admin@kencana.org',
                'password' => bcrypt('password'),
            ]);
            $defaultUser->assignRole('admin');
        }
        $branches = Branch::all();
        $defaultBranch = $branches->first();

        $lineNum = 1;
        $successCount = 0;
        $corruptCount = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 4096, ',')) !== false) {
                $lineNum++;

                // Skip empty lines
                if (empty(array_filter($row, fn ($val) => trim($val) !== ''))) {
                    continue;
                }

                $getValue = function (string $key) use ($row, $headerMap): ?string {
                    if (!isset($headerMap[$key]) || !isset($row[$headerMap[$key]])) {
                        return null;
                    }
                    $val = trim($row[$headerMap[$key]]);
                    return $val === '' ? null : $val;
                };

                // 1. Validate Code
                $rawCode = $getValue('code');
                if (!$rawCode || $rawCode === '-' || trim($rawCode) === '') {
                    $corruptCount++;
                    $errors[] = "Baris {$lineNum}: Kode event kosong atau '-' tidak valid.";
                    continue;
                }

                $code = strtoupper(trim($rawCode));

                // Check uniqueness in database (or within this run)
                if (Event::where('code', $code)->exists()) {
                    $corruptCount++;
                    $errors[] = "Baris {$lineNum}: Kode event [{$code}] sudah terdaftar di sistem.";
                    continue;
                }

                // 2. Validate Goal
                $rawGoal = strtolower($getValue('goal') ?? '');
                $goal = match ($rawGoal) {
                    'sales', 'penjualan' => Goal::SALES,
                    'awareness', 'brand awareness' => Goal::AWARENESS,
                    'relationship', 'hubungan' => Goal::RELATIONSHIP,
                    default => null,
                };

                if (!$goal) {
                    $corruptCount++;
                    $errors[] = "Baris {$lineNum} [{$code}]: Tujuan event '{$rawGoal}' tidak valid (wajib sales/awareness/relationship).";
                    continue;
                }

                // 3. Validate Date
                $rawDate = $getValue('event_date') ?? $getValue('date');
                if (!$rawDate) {
                    $corruptCount++;
                    $errors[] = "Baris {$lineNum} [{$code}]: Tanggal pelaksanaan event wajib diisi.";
                    continue;
                }

                try {
                    $eventDate = Carbon::parse($rawDate);
                } catch (\Throwable $e) {
                    $corruptCount++;
                    $errors[] = "Baris {$lineNum} [{$code}]: Format tanggal '{$rawDate}' tidak valid.";
                    continue;
                }

                // 4. Resolve Branch
                $rawBranch = $getValue('branch') ?? $getValue('branch_id');
                $matchedBranch = null;
                if ($rawBranch) {
                    $matchedBranch = $branches->first(function ($b) use ($rawBranch) {
                        return strtolower($b->name) === strtolower($rawBranch)
                            || strtolower($b->code) === strtolower($rawBranch)
                            || (string) $b->id === (string) $rawBranch;
                    });
                }
                $branchId = $matchedBranch ? $matchedBranch->id : $defaultBranch->id;

                // 5. Costs (strictly parse numeric, zero vs null)
                $parseDecimal = function (?string $raw): ?float {
                    if ($raw === null || trim($raw) === '') return null;
                    $clean = preg_replace('/[^0-9.]/', '', str_replace(',', '.', $raw));
                    return is_numeric($clean) ? (float) $clean : null;
                };

                $proposalCost = $parseDecimal($getValue('proposal_cost')) ?? 0.0;
                $teamBudget = $parseDecimal($getValue('team_budget')) ?? 0.0;

                // Verification status
                $rawVerification = strtolower($getValue('verification_status') ?? 'terverifikasi');
                $verificationStatus = match ($rawVerification) {
                    'terverifikasi', 'verified' => VerificationStatus::TERVERIFIKASI,
                    'perlu_koreksi', 'revisi' => VerificationStatus::PERLU_KOREKSI,
                    default => VerificationStatus::BELUM_DIPERIKSA,
                };

                // Type
                $rawType = strtolower($getValue('type') ?? 'gathering_internal');
                $type = match ($rawType) {
                    'join_external', 'external' => EventType::JOIN_EXTERNAL,
                    default => EventType::GATHERING_INTERNAL,
                };

                if (!$isDryRun) {
                    $event = Event::create([
                        'code' => $code,
                        'name' => $getValue('name') ?? "Event {$code}",
                        'type' => $type,
                        'event_date' => $eventDate,
                        'branch_id' => $branchId,
                        'location' => $getValue('location') ?? 'Lokasi Event',
                        'pic_name' => $getValue('pic_name') ?? 'PIC Cabang',
                        'pic_user_id' => $defaultUser->id,
                        'submitter_email' => $defaultUser->email,
                        'goal' => $goal,
                        'theme_product' => $getValue('theme_product') ?? 'Produk Kencana',
                        'target_participants' => (int) ($getValue('target_participants') ?? 50),
                        'target_leads' => (int) ($getValue('target_leads') ?? 10),
                        'proposal_cost' => $proposalCost,
                        'team_budget' => $teamBudget,
                        'verification_status' => $verificationStatus,
                        'director_decision' => $getValue('director_decision'),
                        'director_note' => $getValue('director_note'),
                        'survey_token' => Str::random(40),
                        'decision_stale_flag' => false,
                    ]);

                    // Post event report if reported
                    $actualParticipants = $getValue('actual_participants');
                    $validLeads = $getValue('valid_leads');
                    $actualTeamCost = $parseDecimal($getValue('actual_team_cost'));

                    if ($actualParticipants !== null || $validLeads !== null || $actualTeamCost !== null) {
                        EventReport::create([
                            'event_id' => $event->id,
                            'actual_participants' => $actualParticipants !== null ? (int) $actualParticipants : null,
                            'valid_leads' => $validLeads !== null ? (int) $validLeads : null,
                            'actual_team_cost' => $actualTeamCost,
                            'notes' => $getValue('report_notes'),
                        ]);
                    }

                    // Sales update if goal sales and revenue reported
                    if ($goal === Goal::SALES) {
                        $revenue = $parseDecimal($getValue('revenue'));
                        if ($revenue !== null) {
                            SalesUpdate::create([
                                'event_id' => $event->id,
                                'revenue' => $revenue,
                                'notes' => $getValue('sales_notes'),
                            ]);
                        }
                    }
                }

                $successCount++;
            }

            if ($isDryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Terjadi kegagalan saat import: " . $e->getMessage());
            return Command::FAILURE;
        } finally {
            fclose($handle);
        }

        // Summary
        $this->newLine();
        $this->info("=== HASIL IMPORT LEGACY CSV ===");
        $this->line("Total baris valid : <info>{$successCount}</info>");
        $this->line("Total baris korup : <comment>{$corruptCount}</comment>");

        if (!empty($errors)) {
            $this->newLine();
            $this->error("Daftar Kesalahan Baris:");
            foreach (array_slice($errors, 0, 20) as $err) {
                $this->line(" - {$err}");
            }
            if (count($errors) > 20) {
                $this->line(" ... dan " . (count($errors) - 20) . " baris korup lainnya.");
            }
        }

        return Command::SUCCESS;
    }
}
