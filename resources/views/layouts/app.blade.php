<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.settings.name', 'Laravel') }}</title>
        <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/png">

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA==" crossorigin="anonymous" />

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">
        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
        @livewireStyles

        <!-- Scripts -->
        <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
        <script src="{{ asset('js/app.js') }}" defer></script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @livewire('navigation-dropdown')

            <!-- Page Heading -->
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <div class="mt-5 -mb-5 hidden annoucements" data-id="2">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="col-span-6">
                            <div class="w-full py-3 px-4 overflow-hidden sm:rounded-md flex items-center border bg-indigo-50 border-indigo-500">
                                <div class="text-indigo-500 w-10"> 
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                    </svg>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="text-sm text-gray-600 font-semibold">{{ __('Announcements') }}</div>
                                    <div class="text-md">
                                        {{ __('Receive cool tips on weekly basis on how to get the best out of your TMail6.') }}
                                        <a class="font-semibold underline" href="https://bulkmailerapp.com/lists/rp490cg6vw3c7/subscribe" target="_blank" rel="noopener noreferrer">{{ __('Subscribe here') }}</a>
                                    </div>
                                </div>
                                <div class="close text-indigo-500 w-5 cursor-pointer"> 
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScripts

        <script>
            const id = localStorage.getItem('annoucements');
            if(id) {
                const el = document.querySelector('.annoucements');
                if(el.dataset.id > id) {
                    el.classList.remove('hidden')
                }
            } else {
                document.querySelector('.annoucements').classList.remove('hidden')
            }
            document.querySelector('.annoucements .close') && document.querySelector('.annoucements .close').addEventListener('click', () => {
                localStorage.setItem('annoucements', document.querySelector('.annoucements').dataset.id);
                document.querySelector('.annoucements').remove();
            })
        </script>
    </body>
</html>
