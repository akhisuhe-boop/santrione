<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon />
    <title>Portal Wali Santri</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <style>
        body{
            font-family:'Plus Jakarta Sans',sans-serif;
            background:#F3F4F6;
        }

        ::-webkit-scrollbar{
            width:0;
        }

        .app-container{
        width:100%;
        max-width:100%;
        margin:0 auto;
        min-height:100dvh;
        background:white;
        position:relative;
        box-shadow:0 0 30px rgba(0,0,0,.08);
        border-top:3px solid #00A39D;
        }

        /* responsive upgrade */
        @media (min-width: 768px){
            .app-container{
                max-width:768px;
            }
        }

        @media (min-width: 1024px){
            .app-container{
                max-width:1024px;
            }
        }

        .content-wrapper{
            padding-bottom: calc(env(safe-area-inset-bottom) + 80px);
        }
    </style>

    @stack('styles')
</head>

<body>

<div class="app-container flex flex-col">

    {{-- HEADER --}}
    @include('wali.layout.partials.wali-header')

    {{-- CONTENT --}}
    <div class="content-wrapper flex-1">
        @yield('content')
    </div>

    {{-- BOTTOM NAV --}}
    @include('wali.layout.partials.wali-bottom-nav')

</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stack('scripts')

</body>
</html>