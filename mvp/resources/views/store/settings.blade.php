<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-brand-600">Administración de la tienda</p>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Configuración de la tienda') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-5 shadow-sm sm:p-8">

                <form method="POST" action="{{ route('store.settings.update') }}" class="space-y-7">
                    @csrf
                    @method('PATCH')

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <x-input-label for="name" :value="__('Nombre de la tienda')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                            :value="old('name', $store->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <x-input-label for="slug" :value="__('Identificador público')" />
                        <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full"
                            :value="old('slug', $store->slug)" required />
                        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <x-input-label for="whatsapp" :value="__('WhatsApp')" />
                        <x-text-input id="whatsapp" name="whatsapp" type="text" class="mt-1 block w-full"
                            :value="old('whatsapp', $store->whatsapp)" required />
                        <x-input-error :messages="$errors->get('whatsapp')" class="mt-2" />
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <x-input-label for="currency" :value="__('Moneda')" />
                        <x-text-input id="currency" name="currency" type="text" class="mt-1 block w-full"
                            :value="old('currency', $store->currency)" required />
                        <x-input-error :messages="$errors->get('currency')" class="mt-2" />
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <x-input-label for="delivery_notes" :value="__('Indicaciones de entrega')" />
                        <textarea id="delivery_notes" name="delivery_notes"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('delivery_notes', $store->delivery_notes) }}</textarea>
                        <x-input-error :messages="$errors->get('delivery_notes')" class="mt-2" />
                    </div>

                    <label class="flex items-center gap-3 rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                        <input type="hidden" name="is_published" value="0">
                        <input type="checkbox" name="is_published" value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            @checked(old('is_published', $store->is_published))>
                        <span class="ms-2 text-sm text-gray-600">
                            {{ __('Publicar tienda') }}
                        </span>
                    </label>

                    <div class="flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center">
                        <x-primary-button>{{ __('Guardar cambios') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
