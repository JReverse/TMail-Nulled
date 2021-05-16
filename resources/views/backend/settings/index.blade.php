<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('backend.settings.general')
        </div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('backend.settings.imap')
        </div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('backend.settings.configuration')
        </div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('backend.settings.ads')
        </div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('backend.settings.socials')
        </div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('backend.settings.advance')
        </div>
        @if (config('app.settings.theme') == 'groot')
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                @livewire('backend.settings.theme')
            </div>
        @endif
    </div>
</x-app-layout>
