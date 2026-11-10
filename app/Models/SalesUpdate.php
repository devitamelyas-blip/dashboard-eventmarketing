<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class SalesUpdate extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'event_id',
        'revenue',
        'invoice_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'revenue' => 'decimal:2',
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
