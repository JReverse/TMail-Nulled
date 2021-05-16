<x-jet-form-section submit="apply">
    <x-slot name="title">
        {{ __('Manual') }}
    </x-slot>

    <x-slot name="description">
        {{ __('To apply a manual update, upload the ZIP file via File Manager or FTP in /public/tmp directory and enter the file name here.') }}
    </x-slot>

    <x-slot name="form">
        @if($error)
        <div class="col-span-6">
            <div class="w-full py-3 px-4 overflow-hidden rounded-md flex items-center border bg-red-50 border-red-500">
                <div class="text-red-500 w-10"> 
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"> 
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path> 
                    </svg> 
                </div>
                <div class="ml-4 flex-1">
                    <div class="text-lg text-gray-600 font-semibold">{{ __('Error') }}</div>
                    <div class="text-sm">{{ $error }}</div>
                </div>
            </div>
        </div>
        @endif
        @if($progress)
        <div class="col-span-6 bg-black px-5 py-4 rounded-md">
            {!! $progress !!}
        </div>
        @else
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="filename" value="{{ __('Enter the File Name') }}" />
            <x-jet-input id="filename" type="text" class="mt-1 block w-full" placeholder="Enter the Filename with extension (eg. Patch-ABC.zip)" wire:model.defer="filename"/>
            <x-jet-input-error for="filename" class="mt-2" />
            <x-jet-label class="mt-2" for="filename" value="{{ __('Make sure the file exist in /public/tmp directory') }}" />
        </div>
        @endif
    </x-slot>
    
    <x-slot name="actions">
        @if(!$progress)
        <x-jet-button>
            {{ __('Apply') }}
        </x-jet-button>
        @endif
    </x-slot>
</x-jet-form-section>