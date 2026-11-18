<?php

namespace Tests\Feature;

use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_the_three_commercial_plans(): void
    {
        $this->seed();

        $this->assertDatabaseHas('plans', [
            'slug' => 'inicial',
            'name' => 'Plan Inicial',
            'monthly_price' => '0.00',
            'max_products' => 2,
            'max_categories' => 2,
            'max_images_per_product' => 1,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('plans', [
            'slug' => 'emprendedor',
            'name' => 'Plan Emprendedor',
            'monthly_price' => '4.99',
            'max_products' => 20,
            'max_categories' => 20,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('plans', [
            'slug' => 'profesional',
            'name' => 'Plan Profesional',
            'monthly_price' => '9.99',
            'max_products' => 500,
            'is_active' => true,
        ]);

        $this->assertSame(3, Plan::query()->count());
    }
}
