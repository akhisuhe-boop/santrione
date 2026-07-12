@if(!empty($yayasan?->logo))
    <link rel="icon" type="image/png" href="{{ asset('storage/' . $yayasan->logo) }}">
    <link rel="shortcut icon" href="{{ asset('storage/' . $yayasan->logo) }}">
    <link rel="apple-touch-icon" href="{{ asset('storage/' . $yayasan->logo) }}">
@else
    <link rel="icon" href="{{ asset('favicon.ico') }}">
@endif