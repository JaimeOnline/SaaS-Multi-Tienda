<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class StoreSettingsController extends Controller
{
    /**
     * Display the store settings form.
     */
    public function edit(Request $request): View
    {
        return view('store.settings', [
            'store' => $request->user()->store,
        ]);
    }

    /**
     * Update the authenticated user's store settings.
     */
    public function update(StoreSettingsRequest $request): RedirectResponse
    {
        $request->user()->store->update($request->validated());

        return Redirect::route('store.settings.edit')
            ->with('status', 'store-settings-updated');
    }
}
