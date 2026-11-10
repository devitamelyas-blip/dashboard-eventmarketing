<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'email_hash',
        'counted',
        'answers',
        'anomaly_flags',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'anomaly_flags' => 'array',
            'counted' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    public static function hashEmail(string $email): string
    {
        return hash('sha256', strtolower(trim($email)) . config('app.key'));
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
