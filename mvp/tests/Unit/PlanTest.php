<?php

namespace Tests\Unit;

use App\Models\Plan;
use PHPUnit\Framework\TestCase;

class PlanTest extends TestCase
{
    public function test_plan_defines_expected_fillable_attributes(): void
    {
        $plan = new Plan();

        $this->assertSame([
            'name',
            'slug',
            'monthly_price',
            'max_products',
            'max_categories',
            'max_images_per_product',
            'is_active',
        ], $plan->getFillable());
    }

    public function test_plan_defines_expected_casts(): void
    {
        $plan = new Plan();

        $this->assertSame('decimal:2', $plan->getCasts()['monthly_price']);
        $this->assertSame('integer', $plan->getCasts()['max_products']);
        $this->assertSame('integer', $plan->getCasts()['max_categories']);
        $this->assertSame('integer', $plan->getCasts()['max_images_per_product']);
        $this->assertSame('boolean', $plan->getCasts()['is_active']);
    }
}
