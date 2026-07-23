<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>Langganan - {{ $yayasan->nama }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon />

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] } } }
        }
    </script>

</head>

<body class="font-sans bg-slate-100 min-h-screen p-4 sm:p-8">

<div class="max-w-3xl mx-auto space-y-6">

    <div>
        <a href="/admin/{{ $yayasan->slug }}" class="text-sm text-teal-600 font-medium">&larr; Kembali ke Panel</a>
        <h1 class="text-2xl font-bold text-slate-900 mt-2">Langganan</h1>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- STATUS SEKARANG --}}
    <div class="bg-white rounded-3xl shadow-sm p-6">

        <div class="text-sm text-slate-500 mb-1">Status Sekarang</div>

        @if ($yayasan->status === 'trial')

            <div class="text-xl font-bold {{ $yayasan->isOnTrial() ? 'text-amber-600' : 'text-red-600' }}">
                {{ $yayasan->isOnTrial() ? 'Masa Trial — ' . $yayasan->trialDaysLeft() . ' hari lagi' : 'Masa Trial Habis' }}
            </div>

        @elseif ($yayasan->status === 'active')

            <div class="text-xl font-bold text-emerald-600">Aktif</div>

        @else

            <div class="text-xl font-bold text-red-600">Nonaktif ({{ $yayasan->status }})</div>

        @endif

    </div>

    {{-- PILIH PAKET --}}
    <div class="bg-white rounded-3xl shadow-sm p-6">

        <h2 class="text-lg font-bold text-slate-900 mb-4">Pilih Paket</h2>

        <div class="space-y-4">

            @forelse ($plans as $plan)

                <div class="rounded-2xl border border-slate-200 p-5">

                    <div class="flex items-center justify-between">
                        <div class="font-semibold text-slate-800">{{ $plan->nama }}</div>
                        <div class="text-teal-600 font-bold">Rp {{ number_format($plan->harga_bulanan, 0, ',', '.') }} / bulan</div>
                    </div>

                    @if ($plan->deskripsi)
                        <p class="text-sm text-slate-500 mt-1">{{ $plan->deskripsi }}</p>
                    @endif

                    <div class="mt-4 flex flex-col sm:flex-row gap-3">

                        @if ($duitkuEnabled)
                            <form method="POST" action="{{ route('subscription.pay-duitku', $plan) }}">
                                @csrf
                                <button type="submit" class="rounded-xl bg-teal-600 hover:bg-teal-700 transition text-white text-sm font-semibold px-4 py-2.5 w-full sm:w-auto">
                                    Bayar Otomatis (VA / QRIS / E-Wallet)
                                </button>
                            </form>
                        @endif

                        @if ($midtransEnabled)
                            <form method="POST" action="{{ route('subscription.pay-midtrans', $plan) }}">
                                @csrf
                                <button type="submit" class="rounded-xl bg-slate-700 hover:bg-slate-800 text-white text-sm font-semibold px-4 py-2.5 w-full sm:w-auto">
                                    Bayar via Midtrans
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('subscription.pay-manual', $plan) }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-2 items-start">
                            @csrf
                            <input type="file" name="bukti_transfer" required
                                class="text-xs border border-slate-300 rounded-xl px-3 py-2">
                            <button type="submit" class="rounded-xl border border-teal-600 text-teal-600 hover:bg-teal-50 text-sm font-semibold px-4 py-2.5 w-full sm:w-auto">
                                Upload Bukti Transfer Manual
                            </button>
                        </form>

                    </div>

                </div>

            @empty

                <p class="text-sm text-slate-500">Belum ada paket tersedia — hubungi admin platform.</p>

            @endforelse

        </div>

        @if (($bank['nomor_rekening'] ?? null))
            <div class="mt-5 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                Transfer manual ke: <strong>{{ $bank['bank'] }} {{ $bank['nomor_rekening'] }}</strong> a.n. {{ $bank['atas_nama'] }}, lalu upload bukti transfernya di atas.
            </div>
        @endif

    </div>

    {{-- RIWAYAT --}}
    @if ($subscriptions->isNotEmpty())

        <div class="bg-white rounded-3xl shadow-sm p-6">

            <h2 class="text-lg font-bold text-slate-900 mb-4">Riwayat Langganan</h2>

            <div class="space-y-3">

                @foreach ($subscriptions as $sub)

                    <div class="flex items-center justify-between text-sm border-b border-slate-100 pb-3 last:border-0 last:pb-0">

                        <div>
                            <div class="font-medium text-slate-800">{{ $sub->plan->nama ?? '-' }}</div>
                            <div class="text-slate-400 text-xs">{{ $sub->created_at->locale('id')->translatedFormat('d M Y') }}</div>
                        </div>

                        <span class="rounded-full px-3 py-1 text-xs font-semibold
                            {{ match($sub->status) {
                                'active' => 'bg-emerald-100 text-emerald-700',
                                'pending' => 'bg-amber-100 text-amber-700',
                                default => 'bg-slate-100 text-slate-600',
                            } }}">
                            {{ ucfirst($sub->status) }}
                        </span>

                    </div>

                @endforeach

            </div>

        </div>

    @endif

</div>

</body>

</html>
