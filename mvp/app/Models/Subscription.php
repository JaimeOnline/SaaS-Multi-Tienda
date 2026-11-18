<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'store_id',
        'plan_id',
        'status',
        'started_at',
        'expires_at',
        'next_renewal_at',
        'grace_period_started_at',
        'grace_period_ends_at',
        'cancelled_at',
        'status_reason',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'next_renewal_at' => 'datetime',
            'grace_period_started_at' => 'datetime',
            'grace_period_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(SubscriptionReminder::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(SubscriptionHistory::class)->orderBy('changed_at');
    }
}
