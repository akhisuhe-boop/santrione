@if(!empty($yayasan?->logo))
    <link rel="icon" type="image/png" href="{{ Storage::disk('r2-public')->url($yayasan->logo) }}">
    <link rel="shortcut icon" href="{{ Storage::disk('r2-public')->url($yayasan->logo) }}">
    <link rel="apple-touch-icon" href="{{ Storage::disk('r2-public')->url($yayasan->logo) }}">
@else
    <link rel="icon" href="{{ asset('favicon.ico') }}">
@endif