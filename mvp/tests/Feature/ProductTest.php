<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_only_their_store_products(): void
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

        Product::create([
            'store_id' => $store->id,
            'name' => 'Producto propio',
            'slug' => 'producto-propio',
            'price' => 10,
        ]);

        Product::create([
            'store_id' => $otherStore->id,
            'name' => 'Producto ajeno',
            'slug' => 'producto-ajeno',
            'price' => 20,
        ]);

        $response = $this->actingAs($user)->get(route('products.index'));

        $response
            ->assertOk()
            ->assertSee('Producto propio')
            ->assertDontSee('Producto ajeno');
    }

    public function test_authenticated_user_can_create_a_product_for_their_store(): void
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
            'name' => 'Postres',
            'slug' => 'postres',
        ]);

        $response = $this->actingAs($user)->post(route('products.store'), [
            'name' => 'Torta de chocolate',
            'description' => 'Producto de prueba.',
            'price' => '12.50',
            'category_id' => $category->id,
            'is_available' => true,
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Torta de chocolate',
            'slug' => 'torta-de-chocolate',
            'price' => '12.50',
            'is_available' => true,
        ]);
    }

    public function test_authenticated_user_can_upload_an_image_for_their_store_product(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda de prueba',
            'slug' => 'tienda-de-prueba',
            'whatsapp' => '0000000000',
        ]);

        $image = UploadedFile::fake()->image('producto.jpg');

        $response = $this
            ->actingAs($user)
            ->post(route('products.store'), [
                'name' => 'Producto con imagen',
                'price' => 25,
                'image_path' => $image,
                'is_available' => true,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.index'));

        $product = Product::where('store_id', $store->id)
            ->where('name', 'Producto con imagen')
            ->firstOrFail();

        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_authenticated_user_can_replace_an_image_for_their_store_product(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda de prueba',
            'slug' => 'tienda-de-prueba',
            'whatsapp' => '0000000000',
        ]);

        $oldImagePath = 'products/imagen-anterior.jpg';
        Storage::disk('public')->put($oldImagePath, 'contenido');

        $product = Product::create([
            'store_id' => $store->id,
            'name' => 'Producto con imagen',
            'slug' => 'producto-con-imagen',
            'price' => 25,
            'image_path' => $oldImagePath,
            'is_available' => true,
        ]);

        $newImage = UploadedFile::fake()->image('producto-nuevo.jpg');

        $response = $this
            ->actingAs($user)
            ->patch(route('products.update', $product), [
                'name' => $product->name,
                'price' => $product->price,
                'image_path' => $newImage,
                'is_available' => true,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.index'));

        $product->refresh();

        $this->assertNotSame($oldImagePath, $product->image_path);
        Storage::disk('public')->assertMissing($oldImagePath);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_authenticated_user_can_update_and_delete_their_store_product(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda de prueba',
            'slug' => 'tienda-de-prueba',
            'whatsapp' => '0000000000',
        ]);

        $product = Product::create([
            'store_id' => $store->id,
            'name' => 'Producto anterior',
            'slug' => 'producto-anterior',
            'price' => 10,
            'is_available' => true,
        ]);

        $updateResponse = $this
            ->actingAs($user)
            ->patch(route('products.update', $product), [
                'name' => 'Producto actualizado',
                'price' => 15.50,
                'is_available' => false,
            ]);

        $updateResponse
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'store_id' => $store->id,
            'name' => 'Producto actualizado',
            'slug' => 'producto-actualizado',
            'price' => '15.50',
            'is_available' => false,
        ]);

        $deleteResponse = $this
            ->actingAs($user)
            ->delete(route('products.destroy', $product));

        $deleteResponse
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_deleting_a_product_removes_its_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda de prueba',
            'slug' => 'tienda-de-prueba',
            'whatsapp' => '0000000000',
        ]);

        $imagePath = 'products/producto.jpg';
        Storage::disk('public')->put($imagePath, 'contenido');

        $product = Product::create([
            'store_id' => $store->id,
            'name' => 'Producto con imagen',
            'slug' => 'producto-con-imagen',
            'price' => 25,
            'image_path' => $imagePath,
            'is_available' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('products.destroy', $product));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.index'));

        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_user_cannot_create_product_with_another_stores_category(): void
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

        $category = Category::create([
            'store_id' => $otherStore->id,
            'name' => 'Categoría ajena',
            'slug' => 'categoria-ajena',
        ]);

        $response = $this->actingAs($user)->post(route('products.store'), [
            'name' => 'Producto inválido',
            'price' => 10,
            'category_id' => $category->id,
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('products', [
            'store_id' => $store->id,
            'name' => 'Producto inválido',
        ]);
    }

    public function test_user_cannot_update_or_delete_another_stores_product(): void
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

        $product = Product::create([
            'store_id' => $otherStore->id,
            'name' => 'Producto ajeno',
            'slug' => 'producto-ajeno',
            'price' => 20,
        ]);

        $updateResponse = $this->actingAs($user)->patch(route('products.update', $product), [
            'name' => 'Producto modificado',
            'price' => 30,
        ]);

        $updateResponse->assertNotFound();

        $deleteResponse = $this->actingAs($user)
            ->delete(route('products.destroy', $product));

        $deleteResponse->assertNotFound();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'store_id' => $otherStore->id,
            'name' => 'Producto ajeno',
        ]);
    }

    public function test_product_image_must_have_an_allowed_format(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda de prueba',
            'slug' => 'tienda-de-prueba',
            'whatsapp' => '0000000000',
        ]);

        $invalidImage = UploadedFile::fake()->create(
            'documento.pdf',
            100,
            'application/pdf'
        );

        $response = $this
            ->actingAs($user)
            ->post(route('products.store'), [
                'name' => 'Producto inválido',
                'price' => 10,
                'image_path' => $invalidImage,
            ]);

        $response
            ->assertSessionHasErrors('image_path')
            ->assertRedirect();

        $this->assertDatabaseMissing('products', [
            'name' => 'Producto inválido',
        ]);
    }

    public function test_product_creation_is_blocked_when_the_plan_limit_is_reached(): void
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

        Product::create([
            'store_id' => $store->id,
            'name' => 'Producto uno',
            'slug' => 'producto-uno',
            'price' => 10,
        ]);

        Product::create([
            'store_id' => $store->id,
            'name' => 'Producto dos',
            'slug' => 'producto-dos',
            'price' => 20,
        ]);

        $response = $this->actingAs($user)->post(route('products.store'), [
            'name' => 'Producto tres',
            'price' => 30,
        ]);

        $response
            ->assertSessionHasErrors('name')
            ->assertRedirect();

        $this->assertDatabaseMissing('products', [
            'store_id' => $store->id,
            'name' => 'Producto tres',
        ]);
    }

    public function test_product_gallery_respects_the_plan_image_limit(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda con galería limitada',
            'slug' => 'tienda-galeria-limitada',
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

        $response = $this
            ->actingAs($user)
            ->post(route('products.store'), [
                'name' => 'Producto con demasiadas imágenes',
                'price' => 25,
                'images' => [
                    UploadedFile::fake()->image('imagen-uno.jpg'),
                    UploadedFile::fake()->image('imagen-dos.jpg'),
                ],
            ]);

        $response
            ->assertSessionHasErrors('images')
            ->assertRedirect();

        $this->assertDatabaseMissing('products', [
            'store_id' => $store->id,
            'name' => 'Producto con demasiadas imágenes',
        ]);
    }

    public function test_guest_cannot_access_products(): void
    {
        $response = $this->get(route('products.index'));

        $response->assertRedirect('/login');
    }
}
