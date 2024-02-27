<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        navigator.permissions.query({name:'geolocation'})
            .then((result) => {
                    if (result.state === 'granted') {
                        window.watch_position_id = navigator.geolocation.watchPosition((position) => {
                            window.coords = position.coords;
                            const event = new CustomEvent("position-updated");
                            document.dispatchEvent(event);
                        });
                    } else {
                        console.log('Browser location services disabled', navigator);
                        navigator.geolocation.getCurrentPosition(() => {})
                    }
                }, () => {
                    console.log('Browser permissions services unavailable', navigator);
                }
            );
    </script>
</head>
<body class="font-sans antialiased bg-white dark:bg-base-200">

<div class="h-full">
    {{ $slot }}

    <div class="btm-nav bg-[#7dcfeb] dark:bg-base-200 h-[56px]">
        <button class="{{ request()->is('search*') ? 'active' : ''}}" href="/search" wire:navigate>
            <x-heroicon-o-magnifying-glass class="h-5 w-5"/>
            <span class="btm-nav-label">搜尋</span>
        </button>
        <button class="{{ request()->is('favorite-routes*') ? 'active' : ''}}" href="/favorite-routes" wire:navigate>
            <x-heroicon-o-heart class="h-5 w-5"/>
            <span class="btm-nav-label">已收藏路線</span>
        </button>
        <button class="{{ request()->is('favorite-stops*') ? 'active' : ''}}" href="/favorite-stops" wire:navigate>
            <x-heroicon-o-flag class="h-5 w-5"/>
            <span class="btm-nav-label">已收藏車站</span>
        </button>
    </div>
</div>

<div class="toast" wire:offline>
    <div class="alert alert-info">
        <span>This device is currently offline.</span>
    </div>
</div>
</body>
</html>
