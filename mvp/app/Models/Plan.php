<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'monthly_price',
        'max_products',
        'max_categories',
        'max_images_per_product',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'max_products' => 'integer',
            'max_categories' => 'integer',
            'max_images_per_product' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the subscriptions using the plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
