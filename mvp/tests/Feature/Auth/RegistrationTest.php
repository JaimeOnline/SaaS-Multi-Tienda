<?php

namespace Tests\Feature\Auth;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $this->seed();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('stores', [
            'user_id' => auth()->id(),
            'name' => 'Mi tienda',
            'slug' => 'mi-tienda-' . auth()->id(),
            'whatsapp' => '0000000000',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'store_id' => Store::where('user_id', auth()->id())->value('id'),
            'plan_id' => \App\Models\Plan::where('slug', 'inicial')->value('id'),
            'status' => 'active',
        ]);
    }

    public function test_authenticated_users_can_start_the_entrepreneur_trial(): void
    {
        $this->seed();

        $this->post('/register', [
            'name' => 'Trial User',
            'email' => 'trial@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response = $this->post(route('subscriptions.entrepreneur-trial.start'));

        $response->assertRedirect();

        $this->assertDatabaseHas('subscriptions', [
            'store_id' => Store::where('user_id', auth()->id())->value('id'),
            'plan_id' => \App\Models\Plan::where('slug', 'emprendedor')->value('id'),
            'status' => 'trialing',
            'status_reason' => 'Prueba gratuita de 14 días iniciada manualmente.',
        ]);

        $this->assertNotNull(
            \App\Models\Subscription::where('store_id', Store::where('user_id', auth()->id())->value('id'))
                ->where('status', 'trialing')
                ->first()
                ->expires_at
        );
    }

    public function test_users_cannot_start_a_second_active_entrepreneur_trial(): void
    {
        $this->seed();

        $this->post('/register', [
            'name' => 'Repeated Trial User',
            'email' => 'repeated-trial@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $route = route('subscriptions.entrepreneur-trial.start');

        $this->post($route);
        $this->post($route);

        $storeId = Store::where('user_id', auth()->id())->value('id');

        $this->assertDatabaseCount('subscriptions', 2);

        $this->assertDatabaseHas('subscriptions', [
            'store_id' => $storeId,
            'status' => 'trialing',
        ]);
    }
}
