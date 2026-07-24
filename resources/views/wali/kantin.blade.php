@extends('wali.layout.wali')

@section('content')

<div class="px-4 pb-4 bg-[#F8FAFB] min-h-screen">

    {{-- HEADER --}}
    <div class="-mx-4 mb-3 px-4 pt-2 pb-1">
        <div class="flex items-center gap-3 px-3 py-2">

            <a href="{{ route('wali.dashboard') }}"
               class="w-9 h-9 rounded-xl bg-white border border-slate-100 flex items-center justify-center shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <div>
                <div class="text-xs text-slate-500">Fitur Kantin</div>
                <div class="font-bold text-base text-slate-900">Cashless & Riwayat Belanja</div>
            </div>

        </div>
    </div>

    @if(session('success'))
    <div
        x-data="{ show:true }"
        x-init="setTimeout(() => show=false,4000)"
        x-show="show"
        x-transition
        class="mb-4 rounded-[24px] border border-green-200 bg-green-50 p-4 text-sm text-green-700">
        {{ session('success') }}
    </div>
    @endif

    {{-- SALDO WALLET (kartu premium, konsisten dengan kartu santri di beranda) --}}
    <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#00A39D] via-[#00B4AC] to-[#14C8C0] p-5 text-white shadow-xl mb-4">

        <div class="absolute top-0 right-0 w-40 h-40 rounded-full bg-white/10 -mr-16 -mt-16"></div>
        <div class="absolute bottom-0 left-0 w-28 h-28 rounded-full bg-white/5 -ml-10 -mb-10"></div>

        <div class="relative z-10">

            <div class="flex items-center gap-3 mb-4">

                <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur-sm border border-white/20 flex items-center justify-center">
                    <x-heroicon-o-wallet class="w-5 h-5" />
                </div>

                <div>
                    <div class="text-[11px] text-white/70 uppercase tracking-wide">Saldo Wallet</div>
                    <div class="text-sm font-semibold">{{ $siswa->nama_lengkap }}</div>
                </div>

            </div>

            <div class="text-4xl font-extrabold tracking-tight">
                Rp {{ number_format($siswa->wallet->saldo ?? 0, 0, ',', '.') }}
            </div>

            <div class="mt-3 inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm rounded-full px-3 py-1.5 text-[11px] font-medium">
                <x-heroicon-o-shield-check class="w-3.5 h-3.5" />
                100% Cashless — potong otomatis saat belanja
            </div>

        </div>

    </div>

    {{-- LIMIT HARIAN --}}
    <div class="bg-white rounded-[28px] shadow-sm p-5 mb-4">

        <div class="flex items-center gap-3 mb-4">

            <div class="w-10 h-10 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-500">
                <x-heroicon-o-shield-exclamation class="w-5 h-5" />
            </div>

            <div>
                <div class="font-bold text-base text-slate-900">Limit Belanja Harian</div>
                <div class="text-xs text-slate-500 mt-0.5">Kosongkan kalau tidak mau dibatasi</div>
            </div>

        </div>

        <div class="rounded-2xl bg-slate-50 px-4 py-3 mb-4 text-sm text-slate-600">
            Sudah dipakai hari ini:
            <span class="font-bold text-slate-900">Rp {{ number_format($belanjaHariIni, 0, ',', '.') }}</span>
            @if ($siswa->limit_harian_kantin)
                <span class="text-slate-400">dari limit Rp {{ number_format($siswa->limit_harian_kantin, 0, ',', '.') }}</span>
            @endif
        </div>

        <form method="POST" action="{{ route('wali.kantin.limit') }}" class="flex items-center gap-2">

            @csrf

            <div class="flex-1 relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                <input
                    type="number"
                    name="limit_harian_kantin"
                    value="{{ $siswa->limit_harian_kantin }}"
                    placeholder="Tidak dibatasi"
                    min="0"
                    class="w-full rounded-2xl border border-slate-200 pl-10 pr-4 py-3 text-sm font-medium focus:border-[#00A39D] focus:ring-[#00A39D]">
            </div>

            <button type="submit" class="rounded-2xl bg-[#00A39D] hover:bg-[#008b86] transition text-white text-sm font-semibold px-6 py-3 shadow-sm shadow-[#00A39D]/30">
                Simpan
            </button>

        </form>

    </div>

    {{-- RIWAYAT --}}
    <div class="bg-white rounded-[28px] shadow-sm overflow-hidden">

        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100">

            <div class="w-10 h-10 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500">
                <x-heroicon-o-receipt-percent class="w-5 h-5" />
            </div>

            <div class="font-bold text-base text-slate-900">Riwayat Pembelian</div>

        </div>

        <div class="divide-y divide-slate-100">

            @forelse ($riwayatKantin as $trx)

                <div class="flex items-start gap-3 px-5 py-4">

                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 shrink-0 mt-0.5">
                        <x-heroicon-o-shopping-bag class="w-4.5 h-4.5" />
                    </div>

                    <div class="flex-1 min-w-0">

                        <div class="flex items-center justify-between gap-2">
                            <div class="text-sm font-semibold text-slate-900">
                                {{ $trx->tanggal->locale('id')->translatedFormat('d M Y, H:i') }}
                            </div>
                            <div class="text-sm font-bold text-slate-900 shrink-0">
                                Rp {{ number_format($trx->total, 0, ',', '.') }}
                            </div>
                        </div>

                        <div class="text-xs text-slate-400 mt-0.5 truncate">
                            {{ $trx->items->pluck('nama_produk')->implode(', ') }}
                        </div>

                        <span class="inline-flex items-center gap-1 mt-2 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $trx->metode === 'wallet' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                            {{ ucfirst($trx->metode) }}
                        </span>

                    </div>

                </div>

            @empty

                <div class="px-5 py-12 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 mx-auto mb-3">
                        <x-heroicon-o-shopping-bag class="w-6 h-6" />
                    </div>
                    <div class="text-sm text-slate-400">Belum ada riwayat pembelian kantin.</div>
                </div>

            @endforelse

        </div>

        @if ($riwayatKantin->hasPages())
            <div class="px-5 py-4">
                {{ $riwayatKantin->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
