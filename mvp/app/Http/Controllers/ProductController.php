<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display the products belonging to the authenticated user's store.
     */
    public function index(Request $request): View
    {
        $store = $request->user()->store;

        $products = $store->products()
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'ilike', '%' . $request->string('search') . '%');
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->integer('category_id'));
            })
            ->orderBy('name')
            ->get();

        $categories = $store->categories()
            ->orderBy('name')
            ->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Store a new product for the authenticated user's store.
     */
    public function store(ProductRequest $request): RedirectResponse
    {
        $store = $request->user()->store;
        $validated = $request->validated();

        $this->ensureProductLimit($store);

        $this->ensureCategoryBelongsToStore($validated['category_id'] ?? null, $store->id);

        $this->ensureImageLimitForNewProduct(
            $store,
            (int) $request->hasFile('image_path') + count($request->file('images', []))
        );

        if ($request->hasFile('image_path')) {
            $validated['image_path'] = $this->processImage(
                $request->file('image_path'),
                'products'
            );
        }

        $product = $store->products()->create([
            ...$validated,
            'slug' => Str::slug($validated['name']),
            'is_available' => $validated['is_available'] ?? false,
        ]);

        foreach ($request->file('images', []) as $index => $image) {
            $product->images()->create([
                'path' => $this->processImage($image, 'products/gallery'),
                'sort_order' => $index,
            ]);
        }

        return Redirect::route('products.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'Producto creado',
                'message' => "«{$product->name}» ya está en tu catálogo.",
            ]);
    }

    /**
     * Update a product belonging to the authenticated user's store.
     */
    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $store = $request->user()->store;

        abort_unless($product->store_id === $store->id, 404);

        $validated = $request->validated();

        $this->ensureCategoryBelongsToStore($validated['category_id'] ?? null, $store->id);

        $this->ensureImageLimit(
            $store,
            $product,
            count($request->file('images', []))
        );

        $oldImagePath = $product->image_path;

        if ($request->hasFile('image_path')) {
            $validated['image_path'] = $this->processImage(
                $request->file('image_path'),
                'products'
            );
        }

        $product->update([
            ...$validated,
            'slug' => Str::slug($validated['name']),
            'is_available' => $validated['is_available'] ?? false,
        ]);

        if ($request->hasFile('image_path') && $oldImagePath !== null) {
            Storage::disk('public')->delete($oldImagePath);
        }

        $currentImageCount = $product->images()->count();

        foreach ($request->file('images', []) as $index => $image) {
            $product->images()->create([
                'path' => $this->processImage($image, 'products/gallery'),
                'sort_order' => $currentImageCount + $index,
            ]);
        }

        return Redirect::route('products.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'Producto actualizado',
                'message' => "«{$product->name}» se ha guardado correctamente.",
            ]);
    }

    /**
     * Delete a product belonging to the authenticated user's store.
     */
    public function destroy(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->store_id === $request->user()->store->id, 404);

        if ($product->image_path !== null) {
            Storage::disk('public')->delete($product->image_path);
        }

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete();

        return Redirect::route('products.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'Producto eliminado',
                'message' => 'El producto se ha eliminado correctamente.',
            ]);
    }

    private function processImage(UploadedFile $image, string $directory): string
    {
        $manager = new ImageManager(new Driver());

        $encodedImage = $manager
            ->decodePath($image->getRealPath())
            ->scaleDown(width: 1600, height: 1600)
            ->encode(new WebpEncoder(quality: 85));

        $path = $directory . '/' . Str::uuid() . '.webp';

        Storage::disk('public')->put($path, (string) $encodedImage);

        return $path;
    }

    private function ensureProductLimit($store): void
    {
        $subscription = $store->subscriptions()
            ->with('plan')
            ->whereIn('status', ['active', 'trialing'])
            ->latest()
            ->first();

        $limit = $subscription?->plan?->max_products;

        if ($limit !== null && $store->products()->count() >= $limit) {
            throw ValidationException::withMessages([
                'name' => "Has alcanzado el límite de {$limit} productos de tu plan actual.",
            ]);
        }
    }

    private function ensureImageLimitForNewProduct($store, int $newImagesCount): void
    {
        $subscription = $store->subscriptions()
            ->with('plan')
            ->whereIn('status', ['active', 'trialing'])
            ->latest()
            ->first();

        $limit = $subscription?->plan?->max_images_per_product;

        if ($limit !== null && $newImagesCount > $limit) {
            throw ValidationException::withMessages([
                'images' => "Has alcanzado el límite de {$limit} imágenes por producto de tu plan actual.",
            ]);
        }
    }

    private function ensureImageLimit($store, Product $product, int $newImagesCount): void
    {
        $subscription = $store->subscriptions()
            ->with('plan')
            ->whereIn('status', ['active', 'trialing'])
            ->latest()
            ->first();

        $limit = $subscription?->plan?->max_images_per_product;

        if ($limit !== null && $product->images()->count() + $newImagesCount > $limit) {
            throw ValidationException::withMessages([
                'images' => "Has alcanzado el límite de {$limit} imágenes por producto de tu plan actual.",
            ]);
        }
    }

    private function ensureCategoryBelongsToStore(?int $categoryId, int $storeId): void
    {
        if ($categoryId === null) {
            return;
        }

        abort_unless(
            $this->categoryBelongsToStore($categoryId, $storeId),
            422,
            'La categoría seleccionada no pertenece a la tienda autenticada.'
        );
    }

    private function categoryBelongsToStore(int $categoryId, int $storeId): bool
    {
        return \App\Models\Category::query()
            ->whereKey($categoryId)
            ->where('store_id', $storeId)
            ->exists();
    }
}
