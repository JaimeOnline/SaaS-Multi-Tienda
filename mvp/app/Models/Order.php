<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'customer_name',
        'customer_phone',
        'customer_notes',
        'status',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope: pedidos de hoy (según timezone de la tienda).
     */
    public function scopeToday(Builder $query): void
    {
        $query->whereDate('created_at', now()->toDateString());
    }

    /**
     * Scope: pedidos de este mes.
     */
    public function scopeThisMonth(Builder $query): void
    {
        $now = now();
        $query->whereBetween('created_at', [$now->startOfMonth(), $now->endOfMonth()]);
    }

    /**
     * Scope: pedidos pendientes.
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }
}
