<x-filament::page>

    <x-filament::section>
        <form>
            {{ $this->form }}
        </form>
    </x-filament::section>

    {{-- ================= STAT UTAMA ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="text-xs text-slate-500">Total Pendaftar</div>
            <div class="text-3xl font-bold text-slate-900 mt-1">
                {{ $this->totalPendaftar }}
            </div>
        </div>

        @php
            $lulus = collect($this->statusBreakdown)->firstWhere('key', 'lulus')['total'] ?? 0;
            $tidakLulus = collect($this->statusBreakdown)->firstWhere('key', 'tidak_lulus')['total'] ?? 0;
            $aktif = collect($this->statusBreakdown)->firstWhere('key', 'aktif')['total'] ?? 0;
        @endphp

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="text-xs text-slate-500">Lulus Seleksi</div>
            <div class="text-3xl font-bold text-emerald-600 mt-1">
                {{ $lulus }}
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="text-xs text-slate-500">Sudah Jadi Siswa Aktif</div>
            <div class="text-3xl font-bold text-sky-600 mt-1">
                {{ $aktif }}
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="text-xs text-slate-500">Tidak Lulus</div>
            <div class="text-3xl font-bold text-rose-600 mt-1">
                {{ $tidakLulus }}
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">

        {{-- ================= STATUS BREAKDOWN ================= --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-semibold text-slate-900">Pendaftar per Status</h3>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($this->statusBreakdown as $item)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <span class="text-sm text-slate-600">{{ $item['label'] }}</span>
                        <span class="text-sm font-semibold text-slate-900">{{ $item['total'] }}</span>
                    </div>
                @endforeach
            </div>

        </div>

        {{-- ================= PER LEMBAGA ================= --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-semibold text-slate-900">Pendaftar per Lembaga</h3>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($this->perLembaga as $item)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <span class="text-sm text-slate-600">{{ $item['lembaga'] }}</span>
                        <span class="text-sm font-semibold text-slate-900">{{ $item['total'] }}</span>
                    </div>
                @empty
                    <div class="px-5 py-6 text-center text-sm text-slate-400">
                        Belum ada data
                    </div>
                @endforelse
            </div>

        </div>

    </div>

    {{-- ================= PEMBAYARAN ================= --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mt-4">

        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-semibold text-slate-900">Status Pembayaran Pendaftaran</h3>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-slate-100">

            <div class="px-5 py-4 text-center">
                <div class="text-2xl font-bold text-emerald-600">{{ $this->pembayaranBreakdown['lunas'] }}</div>
                <div class="text-xs text-slate-500 mt-1">Lunas</div>
            </div>

            <div class="px-5 py-4 text-center">
                <div class="text-2xl font-bold text-amber-600">{{ $this->pembayaranBreakdown['sebagian'] }}</div>
                <div class="text-xs text-slate-500 mt-1">Sebagian</div>
            </div>

            <div class="px-5 py-4 text-center">
                <div class="text-2xl font-bold text-rose-600">{{ $this->pembayaranBreakdown['belum'] }}</div>
                <div class="text-xs text-slate-500 mt-1">Belum Bayar</div>
            </div>

            <div class="px-5 py-4 text-center">
                <div class="text-2xl font-bold text-slate-400">{{ $this->pembayaranBreakdown['belum_ada_tagihan'] }}</div>
                <div class="text-xs text-slate-500 mt-1">Belum Ada Tagihan</div>
            </div>

        </div>

    </div>

    {{-- ================= PENDAFTAR TERBARU ================= --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mt-4">

        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-semibold text-slate-900">Pendaftar Terbaru</h3>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-500 border-b border-slate-100">
                    <th class="px-5 py-2">Nama</th>
                    <th class="px-5 py-2">Lembaga</th>
                    <th class="px-5 py-2">Status</th>
                    <th class="px-5 py-2">Tanggal Daftar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($this->pendaftarTerbaru as $p)
                    <tr>
                        <td class="px-5 py-3 font-medium text-slate-900">{{ $p->nama_lengkap }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $p->lembaga?->nama ?? '-' }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ ucwords(str_replace('_', ' ', $p->status)) }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $p->created_at->locale('id')->translatedFormat('d M Y, H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-slate-400">
                            Belum ada pendaftar
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

</x-filament::page>
