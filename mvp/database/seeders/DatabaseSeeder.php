<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Plan::firstOrCreate(
            ['slug' => 'inicial'],
            [
                'name' => 'Plan Inicial',
                'monthly_price' => 0,
                'max_products' => 2,
                'max_categories' => 2,
                'max_images_per_product' => 1,
                'is_active' => true,
            ]
        );

        Plan::firstOrCreate(
            ['slug' => 'emprendedor'],
            [
                'name' => 'Plan Emprendedor',
                'monthly_price' => 4.99,
                'max_products' => 20,
                'max_categories' => 20,
                'max_images_per_product' => null,
                'is_active' => true,
            ]
        );

        Plan::firstOrCreate(
            ['slug' => 'profesional'],
            [
                'name' => 'Plan Profesional',
                'monthly_price' => 9.99,
                'max_products' => 500,
                'max_categories' => null,
                'max_images_per_product' => null,
                'is_active' => true,
            ]
        );

        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Administrador Demo',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $store = Store::firstOrCreate(
            ['slug' => 'tienda-demo'],
            [
                'user_id' => $user->id,
                'name' => 'Tienda Demo',
                'whatsapp' => '0000000000',
                'currency' => '$',
                'is_published' => true,
            ]
        );

        $categories = collect([
            ['name' => 'Categoría Demo 1', 'slug' => 'categoria-demo-1'],
            ['name' => 'Categoría Demo 2', 'slug' => 'categoria-demo-2'],
            ['name' => 'Categoría Demo 3', 'slug' => 'categoria-demo-3'],
        ])->map(fn(array $attributes) => Category::firstOrCreate(
            ['store_id' => $store->id, 'slug' => $attributes['slug']],
            ['name' => $attributes['name']]
        ));

        collect([
            ['name' => 'Producto Demo 1', 'slug' => 'producto-demo-1', 'price' => 10.00],
            ['name' => 'Producto Demo 2', 'slug' => 'producto-demo-2', 'price' => 15.00],
            ['name' => 'Producto Demo 3', 'slug' => 'producto-demo-3', 'price' => 20.00],
            ['name' => 'Producto Demo 4', 'slug' => 'producto-demo-4', 'price' => 25.00],
            ['name' => 'Producto Demo 5', 'slug' => 'producto-demo-5', 'price' => 30.00],
            ['name' => 'Producto Demo 6', 'slug' => 'producto-demo-6', 'price' => 35.00],
        ])->each(function (array $attributes) use ($store, $categories): void {
            Product::firstOrCreate(
                ['store_id' => $store->id, 'slug' => $attributes['slug']],
                [
                    'category_id' => $categories->random()->id,
                    'name' => $attributes['name'],
                    'price' => $attributes['price'],
                    'description' => 'Producto de demostración.',
                    'is_available' => true,
                ]
            );
        });
    }
}
