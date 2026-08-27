<x-filament::page>

    {{-- ===================== --}}
    {{-- FILTER --}}
    {{-- ===================== --}}
    <div class="mb-6">
        {{ $this->form }}
    </div>

    {{-- ===================== --}}
    {{-- SUMMARY --}}
    {{-- ===================== --}}
    @php
        $summary = $this->getSummary();

        $months = [
            '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober',
            '11'=>'November','12'=>'Desember','01'=>'Januari','02'=>'Februari',
            '03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
        ];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    {{-- CARD 1 --}}
    <div style="background:#eff6ff; padding:16px; border-radius:12px; border:1px solid #bfdbfe;">
        <div style="font-size:14px; color:#2563eb;">Total Tagihan</div>
        <div style="font-size:20px; font-weight:bold; color:#1d4ed8;">
            Rp {{ number_format($summary['tagihan'],0,',','.') }}
        </div>
    </div>

    {{-- CARD 2 --}}
    <div style="background:#f0fdf4; padding:16px; border-radius:12px; border:1px solid #bbf7d0;">
        <div style="font-size:14px; color:#166534;">Total Dibayar</div>
        <div style="font-size:20px; font-weight:bold; color:#15803d;">
            Rp {{ number_format($summary['dibayar'],0,',','.') }}
        </div>
    </div>

    {{-- CARD 3 --}}
    <div style="background:#fef2f2; padding:16px; border-radius:12px; border:1px solid #fecaca;">
        <div style="font-size:14px; color:#b91c1c;">Total Tunggakan</div>
        <div style="font-size:20px; font-weight:bold; color:#991b1b;">
            Rp {{ number_format($summary['tunggakan'],0,',','.') }}
        </div>
    </div>

</div>

    {{-- ===================== --}}
    {{-- A. SPP --}}
    {{-- ===================== --}}
    @php $spp = $this->getSppData(); @endphp

    <div class="bg-white rounded-xl shadow mb-8">
        <div class="p-4 font-bold border-b">
            A. SPP / Bulanan
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border">

                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2">No</th>
                        <th class="p-2">Nama</th>
                        <th class="p-2">Kelas</th>

                        @foreach ($months as $label)
                            <th class="p-2 text-center">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach ($spp as $i => $record)
                        <tr class="border-t">
                            <td class="p-2 text-center">
                                {{ ($spp->currentPage() - 1) * $spp->perPage() + $loop->iteration }}
                            </td>
                            <td class="p-2">
                                {{ $record->nama_lengkap }}
                                @if($record->status_siswa !== 'Aktif')
                                    <br>
                                    <span style="display:inline-flex;align-items:center;gap:4px;color:#d97706;font-size:11px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:12px;height:12px;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                        </svg>
                                        Alumni ({{ $record->status_siswa }})
                                    </span>
                                @endif
                            </td>
                            <td class="p-2">{{ $record->kelas->nama ?? '-' }}</td>

                            @foreach ($months as $num => $label)

                                @php
                                    $tagihans = $record->tagihans
                                        ->where('bulan', $num)
                                        ->filter(function ($t) {
                                            if (! optional($t->jenisTagihan)->is_bulanan) return false;
                                            if ($this->tahun_ajaran_id) {
                                                // Filter tahun ajaran dipilih: cocokkan tunggakan
                                                // (periode_tahun_ajaran_id) ATAU tagihan tahun
                                                // berjalan yang sesuai filter.
                                                return $t->periode_tahun_ajaran_id == $this->tahun_ajaran_id
                                                    || (is_null($t->periode_tahun_ajaran_id) && $t->tahun_ajaran_id == $this->tahun_ajaran_id);
                                            }
                                            // Tanpa filter: hanya tagihan tahun berjalan (bukan
                                            // tunggakan), supaya tidak dobel-hitung dengan bulan
                                            // yang sama dari tahun lain.
                                            return is_null($t->periode_tahun_ajaran_id);
                                        });

                                    $totalTagihan = $tagihans->sum('nominal');
                                    // DIPERBAIKI -- sebelumnya menjumlahkan SEMUA
                                    // pembayarans tanpa filter status (termasuk yang
                                    // masih 'pending' dari gateway DOKU), jadi bulan
                                    // dengan pembayaran pending tampil LUNAS padahal
                                    // belum benar-benar dibayar. Sekarang cuma hitung
                                    // yang statusnya 'sukses'.
                                    $totalBayar = $tagihans->flatMap->pembayarans
                                        ->where('status', 'sukses')
                                        ->sum('nominal');
                                    $lunas = $totalBayar >= $totalTagihan && $totalTagihan > 0;
                                @endphp

                                <td class="p-2 text-center">
                                    @if ($totalTagihan > 0)

                                        @php
                                            $tagihanId = $tagihans->first()?->id;
                                        @endphp

                                        @if ($lunas)
                                            <span
                                                wire:click="lihatRiwayat({{ $tagihanId }})"
                                                style="cursor:pointer;background:#dcfce7;color:#15803d;padding:4px 8px;border-radius:6px;font-size:11px;">
                                                LUNAS
                                            </span>

                                        @elseif ($totalBayar > 0)
                                            <span
                                                wire:click="lihatRiwayat({{ $tagihanId }})"
                                                style="cursor:pointer;background:#fef3c7;color:#92400e;padding:4px 8px;border-radius:6px;font-size:11px;">
                                                CICILAN
                                            </span>

                                        @else
                                            <span
                                                wire:click="lihatRiwayat({{ $tagihanId }})"
                                                style="cursor:pointer;color:#dc2626;">
                                                {{ number_format($totalTagihan,0,',','.') }}
                                            </span>
                                        @endif

                                    @else
                                        -
                                    @endif
                                </td>

                            @endforeach
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>

        {{-- ✅ FIX PAGINATION --}}
        <div class="p-3 flex justify-end">
            {{ $spp->links() }}
        </div>
    </div>

    {{-- ===================== --}}
    {{-- B. UMUM --}}
    {{-- ===================== --}}
    @php 
        $umum = $this->getUmumData(); 
    @endphp

    <div id="table-umum" class="bg-white rounded-xl shadow">
        <div class="p-4 font-bold border-b">
            B. Pembayaran Umum (Non SPP)
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border">

                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 text-left">No</th>
                        <th class="p-2 text-left">Nama</th>
                        <th class="p-2 text-left">Kelas</th>
                        <th class="p-2 text-left">Jenis</th>
                        <th class="p-2 text-left">Total</th>
                        <th class="p-2 text-left">Dibayar</th>
                        <th class="p-2 text-left">Sisa</th>
                        <th class="p-2 text-left">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($umum as $tagihan)

                        @php
                            // DIPERBAIKI -- sama seperti tabel SPP bulanan di atas,
                            // sebelumnya menjumlahkan semua pembayarans tanpa filter
                            // status.
                            $dibayar = optional($tagihan->pembayarans)->where('status', 'sukses')->sum('nominal') ?? 0;
                            $sisa = $tagihan->nominal - $dibayar;
                        @endphp

                        <tr class="border-t">
                            <td class="p-2 text-center">
                                {{ ($umum->currentPage() - 1) * $umum->perPage() + $loop->iteration }}
                            </td>

                            <td class="p-2">
                                {{ optional($tagihan->siswa)->nama_lengkap
                                ?? optional($tagihan->ppdb)->nama_lengkap
                                ?? '-' }}

                                @if(!$tagihan->siswa && $tagihan->ppdb)
                                    <div style="margin-top:2px;">
                                        <span style="display:inline-flex;align-items:center;gap:4px;color:#0284c7;font-size:11px;">
                                            Calon Siswa (PSB)
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td class="p-2">{{ $tagihan->siswa->kelas->nama ?? '-' }}</td>

                            <td class="p-2">
                                {{ $tagihan->jenisTagihan->nama ?? '-' }}
                            </td>

                            <td class="p-2">
                                {{ number_format($tagihan->nominal,0,',','.') }}
                            </td>

                            <td class="p-2">
                                {{ number_format($dibayar,0,',','.') }}
                            </td>

                            <td class="p-2">
                                @if ($sisa <= 0)
                                    <span style="background:#dcfce7;color:#15803d;padding:4px 8px;border-radius:6px;font-size:11px;">
                                        LUNAS
                                    </span>
                                @else
                                    <span style="color:#dc2626;">
                                        {{ number_format($sisa,0,',','.') }}
                                    </span>
                                @endif
                            </td>

                            <td class="p-2">
                                <button
                                    wire:click="lihatRiwayat({{ $tagihan->id }})"
                                    style="background:#00A39D;color:#fff;padding:6px 10px;border-radius:6px;font-size:12px;">
                                    Riwayat
                                </button>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="8" class="p-4 text-center text-gray-500">
                                Tidak ada data
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        {{-- ✅ FIX PAGINATION --}}
        <div class="p-3 flex justify-end">
            {{ $umum->links() }}
        </div>
    </div>

    {{-- ===================== --}}
    {{-- MODAL --}}
    {{-- ===================== --}}
    @if ($showModal && $selectedTagihan)

    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl p-6 max-h-[80vh] overflow-y-auto">

            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-lg">Riwayat Pembayaran</h2>

                <button wire:click="$set('showModal', false)">
                    ✕
                </button>
            </div>

            <div class="overflow-hidden border rounded-xl">
            <table class="w-full text-sm">

                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">Tanggal</th>
                        <th class="p-3 text-left">Jumlah</th>
                        <th class="p-3 text-left">Metode</th>
                        <th class="p-3 text-left">Petugas</th>
                        <th class="p-3 text-left">Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($selectedTagihan->pembayarans as $bayar)

                        @php
                            $tanggal = $bayar->tanggal ?? $bayar->created_at;
                            $status = strtolower($bayar->status ?? 'sukses');
                        @endphp

                        <tr>
                            <td class="p-2">
                                {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}
                            </td>

                            <td class="p-2">
                                Rp {{ number_format($bayar->nominal,0,',','.') }}
                            </td>

                            <td class="p-2">
                                {{ $bayar->metode ?? '-' }}
                            </td>

                            <td class="p-2 text-xs text-gray-600">
                                @if($bayar->diinput_oleh)
                                    Input: {{ $bayar->diinput_oleh }}<br>
                                @endif
                                @if($bayar->diverifikasi_oleh)
                                    Verifikasi: {{ $bayar->diverifikasi_oleh }}
                                @endif
                                @if(!$bayar->diinput_oleh && !$bayar->diverifikasi_oleh)
                                    -
                                @endif
                            </td>

                            <td class="p-2">
                                <span style="background: {{ $status == 'sukses' ? '#dcfce7' : '#fee2e2' }};color: {{ $status == 'sukses' ? '#15803d' : '#b91c1c' }};padding:4px 8px;border-radius:6px;font-size:11px;">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">
                                Belum ada pembayaran
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

        </div>

    </div>

    @endif

</x-filament::page>