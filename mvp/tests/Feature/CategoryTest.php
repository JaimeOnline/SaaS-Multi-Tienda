<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_only_their_store_categories(): void
    {
        $user = User::factory()->create();
        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda propia',
            'slug' => 'tienda-propia',
            'whatsapp' => '0000000000',
        ]);

        $otherUser = User::factory()->create();
        $otherStore = Store::create([
            'user_id' => $otherUser->id,
            'name' => 'Otra tienda',
            'slug' => 'otra-tienda',
            'whatsapp' => '0000000000',
        ]);

        Category::create([
            'store_id' => $store->id,
            'name' => 'Categoría propia',
            'slug' => 'categoria-propia',
        ]);

        Category::create([
            'store_id' => $otherStore->id,
            'name' => 'Categoría ajena',
            'slug' => 'categoria-ajena',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('categories.index'));

        $response
            ->assertOk()
            ->assertSee('Categoría propia')
            ->assertDontSee('Categoría ajena');
    }

    public function test_authenticated_user_can_create_a_category_for_their_store(): void
    {
        $user = User::factory()->create();

        Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda de prueba',
            'slug' => 'tienda-de-prueba',
            'whatsapp' => '0000000000',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('categories.store'), [
                'name' => 'Postres',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'store_id' => $user->store->id,
            'name' => 'Postres',
            'slug' => 'postres',
        ]);
    }

    public function test_authenticated_user_can_update_their_store_category(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda de prueba',
            'slug' => 'tienda-de-prueba',
            'whatsapp' => '0000000000',
        ]);

        $category = Category::create([
            'store_id' => $store->id,
            'name' => 'Nombre anterior',
            'slug' => 'nombre-anterior',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('categories.update', $category), [
                'name' => 'Nombre actualizado',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'store_id' => $store->id,
            'name' => 'Nombre actualizado',
            'slug' => 'nombre-actualizado',
        ]);
    }

    public function test_authenticated_user_can_delete_their_store_category(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda de prueba',
            'slug' => 'tienda-de-prueba',
            'whatsapp' => '0000000000',
        ]);

        $category = Category::create([
            'store_id' => $store->id,
            'name' => 'Categoría eliminable',
            'slug' => 'categoria-eliminable',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('categories.destroy', $category));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_user_cannot_update_another_stores_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda propia',
            'slug' => 'tienda-propia',
            'whatsapp' => '0000000000',
        ]);

        $otherStore = Store::create([
            'user_id' => $otherUser->id,
            'name' => 'Otra tienda',
            'slug' => 'otra-tienda',
            'whatsapp' => '0000000000',
        ]);

        $category = Category::create([
            'store_id' => $otherStore->id,
            'name' => 'Categoría ajena',
            'slug' => 'categoria-ajena',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('categories.update', $category), [
                'name' => 'Categoría modificada',
            ]);

        $response->assertNotFound();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Categoría ajena',
        ]);
    }

    public function test_user_cannot_delete_another_stores_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda propia',
            'slug' => 'tienda-propia',
            'whatsapp' => '0000000000',
        ]);

        $otherStore = Store::create([
            'user_id' => $otherUser->id,
            'name' => 'Otra tienda',
            'slug' => 'otra-tienda',
            'whatsapp' => '0000000000',
        ]);

        $category = Category::create([
            'store_id' => $otherStore->id,
            'name' => 'Categoría ajena',
            'slug' => 'categoria-ajena',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('categories.destroy', $category));

        $response->assertNotFound();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_category_creation_is_blocked_when_the_plan_limit_is_reached(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda limitada',
            'slug' => 'tienda-limitada',
            'whatsapp' => '0000000000',
        ]);

        $plan = Plan::create([
            'name' => 'Plan Inicial',
            'slug' => 'inicial',
            'monthly_price' => 0,
            'max_products' => 2,
            'max_categories' => 2,
            'max_images_per_product' => 1,
            'is_active' => true,
        ]);

        Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => now(),
        ]);

        Category::create([
            'store_id' => $store->id,
            'name' => 'Categoría uno',
            'slug' => 'categoria-uno',
        ]);

        Category::create([
            'store_id' => $store->id,
            'name' => 'Categoría dos',
            'slug' => 'categoria-dos',
        ]);

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Categoría tres',
        ]);

        $response
            ->assertSessionHasErrors('name')
            ->assertRedirect();

        $this->assertDatabaseMissing('categories', [
            'store_id' => $store->id,
            'name' => 'Categoría tres',
        ]);
    }

    public function test_guest_cannot_access_categories(): void
    {
        $response = $this->get(route('categories.index'));

        $response->assertRedirect('/login');
    }
}
