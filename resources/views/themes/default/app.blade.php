<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @if(isset($page))
    {!! $page->header !!}
    <title>{{ $page->title }} - {{ config('app.settings.name') }}</title>
    @else
    <title>{{ config('app.settings.name') }}</title>
    @endif
    {!! config('app.settings.global.header') !!}
    @if(Illuminate\Support\Facades\Storage::disk('local')->has('public/images/custom-favicon.png'))
    <link rel="icon" href="{{ url('storage/images/custom-favicon.png') }}" type="image/png">
    @else
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    @endif
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA==" crossorigin="anonymous" />
    <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">
    <script src="{{ asset('vendor/Shortcode/Shortcode.js') }}"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    @livewireStyles
    {!! config('app.settings.global.css') !!}
</head>
<body>
    <div class="default-theme">
        <div class="flex flex-wrap">
            <div class="w-full lg:w-1/4 bg-blue-700 py-6 lg:min-h-screen" style="background-color: {{ config('app.settings.colors.primary') }}">
                <div class="flex justify-center p-3 mb-10">
                    <a href="{{ route('home') }}">
                        @if(Illuminate\Support\Facades\Storage::disk('local')->has('public/images/custom-logo.png'))
                        <img class="max-w-logo" src="{{ url('storage/images/custom-logo.png') }}" alt="logo">
                        @else
                        <img class="max-w-logo" src="{{ asset('images/logo.png') }}" alt="logo">
                        @endif
                    </a>
                </div>
                @if(config('app.settings.ads.five'))
                <div class="flex justify-center items-center max-w-full m-4 ads-five">{!! config('app.settings.ads.five') !!}</div>
                @endif
                @livewire('frontend.actions', ['in_app' => isset($page) ? true : false])
                @if(config('app.settings.ads.one'))
                <div class="flex justify-center items-center max-w-full m-4 ads-one">{!! config('app.settings.ads.one') !!}</div>
                @endif
            </div>
            <div class="w-full lg:w-3/4">
                @livewire('frontend.nav')
                <div class="flex flex-col lg:min-h-tm-default">
                    @if(config('app.settings.ads.two'))
                    <div class="flex justify-center items-center max-w-full ads-two">{!! config('app.settings.ads.two') !!}</div>
                    @endif
                    @if(isset($page))
                        @livewire('frontend.page', ['page' => $page])
                    @else 
                        @livewire('frontend.app')
                    @endif
                    @if(config('app.settings.ads.three'))
                    <div class="flex justify-center items-center max-w-full ads-three">{!! config('app.settings.ads.three') !!}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if(config('app.settings.cookie.enable'))
    <div id="cookie" class="hidden fixed w-full bottom-0 left-0 p-4 bg-gray-900 text-white justify-between">
        <div class="py-2">
            {!! __(config('app.settings.cookie.text')) !!}
        </div>
        <div id="cookie_close" class="px-3 py-2 bg-yellow-300 text-gray-900 rounded-md cursor-pointer">
            {{ __('Close') }}
        </div>
    </div>
    @endif

    <!--- Helper Text for Language Translation -->
    <div class="hidden language-helper">
        <div class="error">{{ __('Error') }}</div>
        <div class="success">{{ __('Success') }}</div>
        <div class="copy_text">{{ __('Email ID Copied to Clipboard') }}</div>
    </div>

    @livewireScripts
    @if(!isset($page))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const email = '{{ App\Models\TMail::getEmail(true) }}';
            document.title += ` - ${email}`;
            Livewire.emit('syncEmail', email);
            Livewire.emit('fetchMessages');
        });
    </script>
    @endif
    <script>
        document.addEventListener('stopLoader', () => {
            document.getElementById('refresh').classList.add('pause-spinner');
        });
        setInterval(() => {
            if(document.getElementById('imap-error') === null) {
                document.getElementById('refresh').classList.remove('pause-spinner');
                Livewire.emit('fetchMessages')
            }
        }, {{ config('app.settings.fetch_seconds') }} * 1000);
    </script>
    {!! config('app.settings.global.js') !!}
    {!! config('app.settings.global.footer') !!}
</body>
</html>