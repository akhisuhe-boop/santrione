<table>

    {{-- 🔥 JUDUL --}}
    <tr>
        <td colspan="10" style="text-align:center; font-weight:bold; font-size:16px;">
            LAPORAN PEMBAYARAN UMUM
        </td>
    </tr>

    {{-- 🔥 YAYASAN --}}
    <tr>
        <td colspan="10" style="text-align:center; font-weight:bold;">
            {{ $yayasan }}
        </td>
    </tr>

    {{-- 🔥 LEMBAGA --}}
    <tr>
        <td colspan="10" style="text-align:center;">
            {{ $lembaga }}
        </td>
    </tr>

    {{-- 🔥 FILTER INFO --}}
    <tr>
        <td colspan="10" style="text-align:center;">
            Tahun Ajaran: {{ $tahunAjaran }} | 
            Kelas: {{ $kelas }}
        </td>
    </tr>

    <tr><td colspan="10"></td></tr>
    
    {{-- 🔥 HEADER (FIX EXCEL STYLE) --}}
    <tr>
        @foreach(['No','Nama','Kelas','Jenis','Tagihan','Dibayar','Tanggal Bayar','Metode','Sisa','Status'] as $h)
            <th style="border:1px solid #000; font-weight:bold; background:#d9d9d9;">
                {{ $h }}
            </th>
        @endforeach
    </tr>

    {{-- 🔥 DATA --}}
    @foreach($data as $i => $row)

        @php
            $pembayarans = $row->pembayarans;

            $bayar = $pembayarans->sum('nominal');
            $sisa = $row->nominal - $bayar;

            // 🔥 ambil semua tanggal (join jadi 1 cell)
            $tanggalList = $pembayarans->map(function ($p) {
                return $p->tanggal_bayar
                    ? \Carbon\Carbon::parse($p->tanggal_bayar)->format('d-m-Y')
                    : '-';
            })->implode(', ');

            // 🔥 ambil semua metode
            $metodeList = $pembayarans->pluck('metode')->filter()->implode(', ');
        @endphp

        <tr>
            <td style="border:1px solid #000;">{{ $i+1 }}</td>

            <td style="border:1px solid #000;">
                {{ $row->siswa->nama_lengkap ?? $row->ppdb->nama ?? '-' }}
            </td>

            <td style="border:1px solid #000;">
                {{ $row->siswa->kelas->nama ?? '-' }}
            </td>

            <td style="border:1px solid #000;">
                {{ $row->jenisTagihan->nama ?? '-' }}
            </td>

            <td style="border:1px solid #000;">
                {{ number_format($row->nominal) }}
            </td>

            <td style="border:1px solid #000;">
                {{ number_format($bayar) }}
            </td>

            <td style="border:1px solid #000;">
                {{ $tanggalList ?: '-' }}
            </td>

            <td style="border:1px solid #000;">
                {{ $metodeList ?: '-' }}
            </td>

            <td style="border:1px solid #000;">
                {{ number_format($sisa) }}
            </td>

            <td style="border:1px solid #000; text-align:center;">
                {{ $sisa <= 0 ? 'Lunas' : 'Belum Lunas' }}
            </td>
        </tr>
    @endforeach
</table>