<div class="space-y-4">

    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex justify-between items-center">
            <span class="text-sm text-gray-500">Akses Platform ({{ $hasil['jumlah_siswa'] }} siswa)</span>
            <span class="font-medium">Rp {{ number_format($hasil['akses_platform_sebelum_diskon'], 0, ',', '.') }}</span>
        </div>

        @if ($hasil['diskon_persen'] > 0)
            <div class="flex justify-between items-center text-sm text-success-600 mt-1">
                <span>Diskon volume (Lembaga ke-{{ $hasil['urutan_ke'] }}) — {{ $hasil['diskon_persen'] }}%</span>
                <span>− Rp {{ number_format($hasil['akses_platform_sebelum_diskon'] - $hasil['akses_platform'], 0, ',', '.') }}</span>
            </div>
        @endif
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-sm text-gray-500 mb-2">Modul Aktif</p>

        @forelse ($hasil['modul'] as $modul)
            <div class="flex justify-between items-center py-1">
                <span>{{ $modul['nama'] }}</span>
                <span>
                    @if ($modul['termasuk_paket_full'] ?? false)
                        <span class="text-xs text-gray-400">Termasuk Paket Full</span>
                    @elseif ($modul['harga'] === 0)
                        <span class="text-xs text-warning-600">GRATIS (fee dari wali murid)</span>
                    @else
                        Rp {{ number_format($modul['harga'], 0, ',', '.') }}
                    @endif
                </span>
            </div>
        @empty
            <p class="text-sm text-gray-400">Belum ada modul aktif.</p>
        @endforelse
    </div>

    <div class="flex justify-between items-center rounded-lg bg-primary-50 dark:bg-primary-900/20 p-4">
        <span class="font-semibold">Total Tagihan / Bulan</span>
        <span class="text-lg font-bold text-primary-600">Rp {{ number_format($hasil['subtotal'], 0, ',', '.') }}</span>
    </div>

    <p class="text-xs text-gray-400">
        Estimasi berdasarkan jumlah siswa saat ini — angka final tiap bulan mengikuti snapshot yang dikunci saat invoice diterbitkan (tanggal 1).
    </p>

</div>
