@extends('wali.layout.wali')

@section('content')

<div class="p-4 bg-[#F7F9FC] min-h-screen">

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

    <div class="text-xl font-bold text-slate-900 mb-4">
        Kantin
    </div>

    {{-- SALDO WALLET --}}
    <div class="bg-gradient-to-br from-[#00A39D] to-[#008b86] rounded-[24px] shadow-sm p-5 mb-4 text-white">

        <div class="text-xs text-white/80">Saldo Wallet {{ $siswa->nama_lengkap }}</div>
        <div class="text-3xl font-bold mt-1">
            Rp {{ number_format($siswa->wallet->saldo ?? 0, 0, ',', '.') }}
        </div>
        <div class="text-xs text-white/70 mt-1">
            Belanja kantin di sekolah 100% cashless — potong otomatis dari saldo ini.
        </div>

    </div>

    {{-- LIMIT HARIAN --}}
    <div class="bg-white rounded-[24px] shadow-sm p-5 mb-4">

        <div class="font-semibold text-slate-800 mb-1">Limit Belanja Harian</div>
        <div class="text-xs text-slate-500 mb-4">
            Batasi berapa maksimal {{ $siswa->nama_lengkap }} boleh belanja kantin (via wallet) per hari. Kosongkan kalau tidak mau dibatasi.
        </div>

        <div class="text-sm text-slate-600 mb-3">
            Sudah dipakai hari ini: <span class="font-semibold text-slate-800">Rp {{ number_format($belanjaHariIni, 0, ',', '.') }}</span>
            @if ($siswa->limit_harian_kantin)
                dari limit Rp {{ number_format($siswa->limit_harian_kantin, 0, ',', '.') }}
            @endif
        </div>

        <form method="POST" action="{{ route('wali.kantin.limit') }}" class="flex items-center gap-3">

            @csrf

            <input
                type="number"
                name="limit_harian_kantin"
                value="{{ $siswa->limit_harian_kantin }}"
                placeholder="Kosongkan = tidak dibatasi"
                min="0"
                class="flex-1 rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-[#00A39D] focus:ring-[#00A39D]">

            <button type="submit" class="rounded-2xl bg-[#00A39D] hover:bg-[#008b86] text-white text-sm font-semibold px-5 py-2.5">
                Simpan
            </button>

        </form>

    </div>

    {{-- RIWAYAT --}}
    <div class="bg-white rounded-[24px] shadow-sm overflow-hidden">

        <div class="px-5 py-4 border-b border-slate-100 font-semibold text-slate-800">
            Riwayat Pembelian
        </div>

        <div class="divide-y divide-slate-100">

            @forelse ($riwayatKantin as $trx)

                <div class="px-5 py-4">

                    <div class="flex items-center justify-between">
                        <div class="text-sm font-medium text-slate-800">
                            {{ $trx->tanggal->locale('id')->translatedFormat('d M Y, H:i') }}
                        </div>
                        <div class="text-sm font-semibold text-slate-800">
                            Rp {{ number_format($trx->total, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="text-xs text-slate-400 mt-1">
                        {{ $trx->items->pluck('nama_produk')->implode(', ') }}
                    </div>

                    <div class="text-[11px] mt-1">
                        <span class="rounded-full px-2 py-0.5 {{ $trx->metode === 'wallet' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                            {{ ucfirst($trx->metode) }}
                        </span>
                    </div>

                </div>

            @empty

                <div class="px-5 py-10 text-center text-sm text-slate-400">
                    Belum ada riwayat pembelian kantin.
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
