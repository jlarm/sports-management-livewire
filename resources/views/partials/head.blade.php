@php
    $organization = context('organization');
    $applicationTitle = filled($organization?->name) ? $organization->name : config('app.name', 'Laravel');
    $logoPath = is_string($organization?->logo_path) ? $organization->logo_path : null;
    $faviconUrl = null;

    if ($logoPath) {
        $faviconUrl = str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')
            ? $logoPath
            : \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath);
    }
@endphp

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.$applicationTitle : $applicationTitle }}
</title>

@if ($faviconUrl)
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
@else
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
@endif

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
