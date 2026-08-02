@if(!empty($yayasan?->logo))
    <link rel="icon" type="image/png" href="{{ App\Support\FileUrlResolver::public($yayasan->logo) }}">
    <link rel="shortcut icon" href="{{ App\Support\FileUrlResolver::public($yayasan->logo) }}">
    <link rel="apple-touch-icon" href="{{ App\Support\FileUrlResolver::public($yayasan->logo) }}">
@else
    <link rel="icon" href="{{ asset('favicon.ico') }}">
@endif