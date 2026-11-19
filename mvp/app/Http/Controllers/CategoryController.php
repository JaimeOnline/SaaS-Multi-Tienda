<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display the categories belonging to the authenticated user's store.
     */
    public function index(Request $request): View
    {
        $store = $request->user()->store;

        $query = $store->categories();

        // Global search
        $query->when($request->filled('search'), function ($q) use ($request) {
            $q->where('name', 'ilike', '%' . $request->string('search') . '%');
        });

        // Sorting
        $sortBy = $request->string('sort_by')->toString();
        $sortDir = in_array($request->string('sort_dir')->toString(), ['asc', 'desc']) ? $request->string('sort_dir')->toString() : 'asc';
        $allowedSorts = ['name', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('name');
        }

        $categories = $query->paginate(25)->withQueryString();

        // Handle AJAX request for datatable
        if ($request->expectsJson()) {
            $data = $categories->getCollection()->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'products_count' => $category->products_count,
                    'actions' => [
                        [
                            'label' => 'Editar',
                            'icon' => 'edit-2',
                            'variant' => 'ghost',
                            'onclick' => "editCategory({$category->id})",
                        ],
                        [
                            'label' => 'Eliminar',
                            'icon' => 'trash-2',
                            'variant' => 'danger',
                            'onclick' => "deleteCategory({$category->id})",
                        ],
                    ],
                ];
            });

            return response()->json(array_merge($categories->toArray(), [
                'data' => $data,
            ]));
        }

        return view('categories.index', compact('categories'));
    }

    /**
     * Store a new category for the authenticated user's store.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $store = $request->user()->store;

        $subscription = $store->subscriptions()
            ->with('plan')
            ->whereIn('status', ['active', 'trialing'])
            ->latest()
            ->first();

        $limit = $subscription?->plan?->max_categories;

        if ($limit !== null && $store->categories()->count() >= $limit) {
            throw ValidationException::withMessages([
                'name' => "Has alcanzado el límite de {$limit} categorías de tu plan actual.",
            ]);
        }

        $store->categories()->create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return Redirect::route('categories.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'Categoría creada',
                'message' => 'La categoría se ha creado correctamente.',
            ]);
    }

    /**
     * Update a category belonging to the authenticated user's store.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        abort_unless($category->store_id === $request->user()->store->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return Redirect::route('categories.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'Categoría actualizada',
                'message' => 'La categoría se ha actualizado correctamente.',
            ]);
    }

    /**
     * Delete a category belonging to the authenticated user's store.
     */
    public function destroy(Request $request, Category $category): RedirectResponse
    {
        abort_unless($category->store_id === $request->user()->store->id, 404);

        $category->delete();

        return Redirect::route('categories.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'Categoría eliminada',
                'message' => 'La categoría se ha eliminado correctamente.',
            ]);
    }

    /**
     * Bulk delete categories.
     */
    public function bulkDestroy(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:categories,id']]);

        $store = $request->user()->store;
        $deletedCount = $store->categories()->whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => "Se eliminaron {$deletedCount} categor" . ($deletedCount !== 1 ? 'ías' : 'ía') . ' correctamente.',
        ]);
    }
}
