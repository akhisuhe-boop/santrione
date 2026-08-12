<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>Daftar - Qinara App</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon />

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>

</head>

<body class="font-sans bg-slate-100 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-lg">

    <div class="bg-white rounded-3xl shadow-xl p-6 sm:p-8">

        {{-- HEADER --}}
        <div class="text-center mb-6">

            <img
                src="{{ asset('images/qinara-apps-logo.png') }}"
                alt="Qinara Apps"
                class="w-48 md:w-56 object-contain mx-auto mb-4"
            >

            <p class="text-slate-500 text-sm">
                Daftar Yayasan Baru
            </p>

            <h1 class="text-2xl font-bold text-teal-600">
                Coba Gratis {{ $trialDays }} Hari
            </h1>

            <p class="text-slate-500 text-sm mt-1">
                Tidak perlu kartu kredit. Langsung aktif setelah daftar.
            </p>

        </div>

        @if ($errors->any())

            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>

        @endif

        <form method="POST" action="{{ route('public.daftar.store') }}" class="space-y-4">

            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Nama Yayasan / Sekolah
                </label>
                <input
                    type="text"
                    name="nama_yayasan"
                    value="{{ old('nama_yayasan') }}"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Nama Anda
                    </label>
                    <input
                        type="text"
                        name="nama_admin"
                        value="{{ old('nama_admin') }}"
                        required
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        No. HP / WhatsApp
                    </label>
                    <input
                        type="text"
                        name="no_hp"
                        value="{{ old('no_hp') }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                </div>

            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Email
                </label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                <p class="text-xs text-slate-400 mt-1">Dipakai untuk login ke panel admin.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Password
                    </label>
                    <input
                        type="password"
                        name="password"
                        required
                        minlength="8"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Ulangi Password
                    </label>
                    <input
                        type="password"
                        name="password_confirmation"
                        required
                        minlength="8"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                </div>

            </div>

            <button
                type="submit"
                class="w-full rounded-xl bg-teal-600 hover:bg-teal-700 transition text-white font-semibold py-3 text-sm">
                Daftar & Mulai Trial
            </button>

        </form>

        <p class="text-center text-xs text-slate-400 mt-5">
            Sudah punya akun? <a href="/admin/login" class="text-teal-600 font-medium">Masuk di sini</a>
        </p>

    </div>

</div>

</body>

</html>
