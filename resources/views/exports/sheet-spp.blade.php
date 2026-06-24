<table>

    {{-- 🔥 HEADER UTAMA --}}
    <tr>
        <td colspan="15" style="text-align:center; font-weight:bold; font-size:16px;">
            LAPORAN PEMBAYARAN SPP
        </td>
    </tr>

    <tr>
        <td colspan="15" style="text-align:center; font-weight:bold;">
            {{ $yayasan }}
        </td>
    </tr>

    <tr>
        <td colspan="15" style="text-align:center;">
            {{ $lembaga }}
        </td>
    </tr>

    <tr>
        <td colspan="15" style="text-align:center;">
            Tahun Ajaran: {{ $tahunAjaran }} | 
            Kelas: {{ $kelas }}
        </td>
    </tr>

    <tr><td colspan="15"></td></tr>

    {{-- 🔥 HEADER --}}
    <tr>
        @foreach(['No','Nama','Kelas','Juli','Agustus','September','Oktober','November','Desember','Januari','Februari','Maret','April','Mei','Juni'] as $header)
            <th style="border:1px solid #000; font-weight:bold; background:#eaeaea; text-align:center;">
                {{ $header }}
            </th>
        @endforeach
    </tr>

    {{-- 🔥 DATA --}}
    @foreach($data ?? [] as $i => $siswa)

        @php
            $tagihanSpp = $siswa->tagihans->where('jenisTagihan.nama','SPP');
        @endphp

        <tr>
            <td style="border:1px solid #000;">{{ $i+1 }}</td>

            <td style="border:1px solid #000;">
                {{ $siswa->nama_lengkap }}
            </td>

            <td style="border:1px solid #000;">
                {{ $siswa->kelas->nama ?? 'All' }}
            </td>

            {{-- LOOP BULAN --}}
            @foreach(array_merge(range(7,12), range(1,6)) as $bulan)

                @php
                    $tagihan = $tagihanSpp->first(fn($t) => $t->bulan == $bulan);

                    $nominal = $tagihan->nominal ?? 0;

                    $pembayaran = $tagihan?->pembayarans ?? collect();
                    $dibayar = $pembayaran->sum('nominal');

                    $lunas = $dibayar >= $nominal && $nominal > 0;

                    $tanggal = $pembayaran
                    ->sortByDesc(function ($p) {
                        return $p->tanggal_bayar ?? $p->created_at;
                    })
                    ->map(function ($p) {
                        return $p->tanggal_bayar ?? $p->created_at;
                    })
                    ->first();
                @endphp

                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    background: {{ $lunas ? '#dff5e1' : '#fdecec' }};
                ">

                    @if($tagihan)

                        @if($lunas)
                            <strong style="color:#1a7f37;">
                                Lunas <br>
                                ({{ $tanggal ? \Carbon\Carbon::parse($tanggal)->format('d/m/Y') : '-' }})
                            </strong>
                        @else
                            {{ number_format($nominal) }}
                        @endif

                    @else
                        -
                    @endif

                </td>

            @endforeach

        </tr>

    @endforeach
</table>