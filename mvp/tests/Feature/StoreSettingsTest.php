<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_store_settings(): void
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
            ->get('/store/settings');

        $response
            ->assertOk()
            ->assertSee('Tienda de prueba')
            ->assertSee('tienda-de-prueba');
    }

    public function test_authenticated_user_can_update_store_settings(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda anterior',
            'slug' => 'tienda-anterior',
            'whatsapp' => '0000000000',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/store/settings', [
                'name' => 'Tienda actualizada',
                'slug' => 'tienda-actualizada',
                'whatsapp' => '584121234567',
                'currency' => 'Bs',
                'delivery_notes' => 'Entregas de lunes a viernes.',
                'is_published' => true,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('store.settings.edit'));

        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'name' => 'Tienda actualizada',
            'slug' => 'tienda-actualizada',
            'whatsapp' => '584121234567',
            'currency' => 'Bs',
            'delivery_notes' => 'Entregas de lunes a viernes.',
            'is_published' => true,
        ]);
    }

    public function test_guest_cannot_access_store_settings(): void
    {
        $response = $this->get('/store/settings');

        $response->assertRedirect('/login');
    }
}
