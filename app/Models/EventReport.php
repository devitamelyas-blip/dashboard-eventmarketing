<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class EventReport extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'event_id',
        'actual_participants',
        'valid_leads',
        'actual_team_cost',
        'attendance_doc_path',
        'documentation_doc_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'actual_participants' => 'integer',
            'valid_leads' => 'integer',
            'actual_team_cost' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
