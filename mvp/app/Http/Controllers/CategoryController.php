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
        $categories = $request->user()
            ->store
            ->categories()
            ->orderBy('name')
            ->get();

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
            ->with('status', 'category-created');
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
            ->with('status', 'category-updated');
    }

    /**
     * Delete a category belonging to the authenticated user's store.
     */
    public function destroy(Request $request, Category $category): RedirectResponse
    {
        abort_unless($category->store_id === $request->user()->store->id, 404);

        $category->delete();

        return Redirect::route('categories.index')
            ->with('status', 'category-deleted');
    }
}
