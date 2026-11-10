<?php

namespace App\Models;

use App\Enums\EventPortfolio;
use App\Enums\EventType;
use App\Enums\Goal;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Event extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'name',
        'type',
        'event_date',
        'branch_id',
        'location',
        'pic_name',
        'pic_user_id',
        'goal',
        'theme_product',
        'target_participants',
        'target_leads',
        'estimated_relevance_pct',
        'estimated_relevance_basis',
        'proposal_cost',
        'team_budget',
        'verification_status',
        'verification_note',
        'director_decision',
        'director_note',
        'decision_stale_flag',
        'survey_token',
        'submitter_email',
    ];

    protected function casts(): array
    {
        return [
            'type' => EventType::class,
            'goal' => Goal::class,
            'verification_status' => VerificationStatus::class,
            'event_date' => 'date',
            'proposal_cost' => 'decimal:2',
            'team_budget' => 'decimal:2',
            'estimated_relevance_pct' => 'decimal:2',
            'target_participants' => 'integer',
            'target_leads' => 'integer',
            'decision_stale_flag' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (empty($event->survey_token)) {
                $event->survey_token = Str::random(40);
            }
            if (!empty($event->code)) {
                $event->code = strtoupper(trim($event->code));
            }
        });

        static::updating(function (Event $event) {
            if (!empty($event->code)) {
                $event->code = strtoupper(trim($event->code));
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function portfolio(): Attribute
    {
        return Attribute::make(
            get: fn () => EventPortfolio::fromCode($this->code)
        );
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function picUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function report(): HasOne
    {
        return $this->hasOne(EventReport::class);
    }

    public function salesUpdate(): HasOne
    {
        return $this->hasOne(SalesUpdate::class);
    }

    public function surveyResponses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function isEligibleForReport(): bool
    {
        return now()->startOfDay()->gte($this->event_date->startOfDay());
    }

    public function isEligibleForSalesUpdate(): bool
    {
        if ($this->goal !== Goal::SALES) {
            return false;
        }

        return now()->startOfDay()->gte($this->event_date->copy()->addDays(90)->startOfDay());
    }

    public function daysUntilSalesUpdate(): int
    {
        if ($this->goal !== Goal::SALES) {
            return 0;
        }

        $targetDate = $this->event_date->copy()->addDays(90)->startOfDay();
        $diff = (int) now()->startOfDay()->diffInDays($targetDate, false);

        return max(0, $diff);
    }

    public function resetVerificationIfModifiedAfterVerification(): void
    {
        if ($this->verification_status === VerificationStatus::TERVERIFIKASI) {
            $this->verification_status = VerificationStatus::BELUM_DIPERIKSA;
            if (!empty($this->director_decision)) {
                $this->decision_stale_flag = true;
            }
            $this->save();
        }
    }
}
