<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Buku Induk Siswa</title>
    <style>
        /* DomPDF -- CSS terbatas, sengaja pakai layout berbasis
           <table> (bukan flexbox/grid) supaya kompatibel & stabil. */
        body { font-family: sans-serif; font-size: 11.5px; color: #1a1a1a; }

        .halaman-siswa {
            page-break-after: always;
        }
        .halaman-siswa:last-child {
            page-break-after: avoid;
        }

        .header-dokumen {
            text-align: center;
            border-bottom: 2px solid #00A39D;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .header-dokumen h1 {
            font-size: 24px;
            margin: 0 0 2px 0;
            letter-spacing: 1px;
            color: #00524F;
        }
        .nama-yayasan {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #444;
            margin: 0 0 2px 0;
        }
        table.tabel-header-logo { width: 100%; border-collapse: collapse; }
        table.tabel-header-logo td { vertical-align: middle; padding: 0; }
        .kolom-logo { width: 60px; text-align: left; }
        .kolom-logo img { height: 50px; width: auto; max-width: 55px; }
        .kolom-judul { text-align: center; }
        .header-dokumen p {
            margin: 0;
            font-size: 12px;
            color: #444;
        }

        table.tabel-atas { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.tabel-atas td { vertical-align: top; padding: 0; }
        .kotak-foto {
            width: 100px;
            text-align: left;
            vertical-align: top;
            font-size: 9px;
            color: #999;
        }
        .kotak-foto img {
            width: 100px;
            height: 130px;
            display: block;
            border: 1px solid #999;
        }
        .info-ringkas { }
        .info-ringkas .nama-besar { font-size: 16px; font-weight: bold; color: #00524F; margin-bottom: 2px; }
        .info-ringkas .no-induk { font-size: 10.5px; color: #666; margin-bottom: 5px; }
        .info-ringkas table { width: 100%; border-collapse: collapse; }
        .info-ringkas table td { padding: 2.5px 0; font-size: 11.5px; vertical-align: top; }
        .info-ringkas table td.label { width: 78px; color: #555; }
        .info-ringkas table td.label-kanan { width: 92px; color: #555; padding-left: 14px; }
        .info-ringkas table td.titik { width: 8px; color: #555; }
        .info-ringkas table td.nilai { width: 90px; }

        .judul-bagian {
            background: #00A39D;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            padding: 3.5px 8px;
            margin-top: 8px;
            margin-bottom: 0;
            letter-spacing: 0.5px;
        }
        table.tabel-data {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ccc;
            border-top: none;
            margin-bottom: 3px;
        }
        table.tabel-data td {
            padding: 3.5px 8px;
            font-size: 11px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        table.tabel-data td.label {
            width: 32%;
            color: #555;
            background: #fafafa;
        }
        table.tabel-data td.titik {
            width: 12px;
            color: #555;
            background: #fafafa;
        }
        table.tabel-data tr:last-child td { border-bottom: none; }

        .sub-judul-ortu {
            font-size: 10.5px;
            font-weight: bold;
            color: #00524F;
            background: #E6F6F5;
            padding: 3px 8px;
            border-left: 1px solid #ccc;
            border-right: 1px solid #ccc;
        }

        .footer-halaman {
            margin-top: 10px;
            text-align: right;
            font-size: 8.5px;
            color: #999;
        }
    </style>
</head>
<body>

@foreach($siswas as $i => $siswa)
<div class="halaman-siswa">

    <div class="header-dokumen">
        <table class="tabel-header-logo">
            <tr>
                <td class="kolom-logo">
                    @if($siswa->logo_base64)
                        <img src="{{ $siswa->logo_base64 }}" alt="Logo">
                    @endif
                </td>
                <td class="kolom-judul">
                    <p class="nama-yayasan">
                        {{ $lembaga->yayasan->nama ?? $siswa->lembaga?->yayasan?->nama }}
                    </p>
                    <h1>BUKU INDUK SISWA</h1>
                    <p>
                        @if($lembaga)
                            {{ $lembaga->nama }}
                        @elseif($siswa->lembaga)
                            {{ $siswa->lembaga->nama }}
                        @endif
                        @if($kelas) &middot; Kelas {{ $kelas->nama }} @endif
                    </p>
                </td>
                <td class="kolom-logo"></td>
            </tr>
        </table>
    </div>

    <table class="tabel-atas">
        <tr>
            <td class="kotak-foto">
                @if($siswa->foto_base64)
                    <img src="{{ $siswa->foto_base64 }}" alt="Foto" width="100" height="130">
                @else
                    <div style="width: 98px; height: 128px; border: 1px solid #999; text-align: center; padding-top: 55px;">Belum<br>ada foto</div>
                @endif
            </td>
            <td style="width: 20px;">&nbsp;</td>
            <td class="info-ringkas">
                <div class="nama-besar">{{ $siswa->nama_lengkap }}</div>
                <div class="no-induk">No. Urut Induk: {{ $i + 1 }}</div>
                <table>
                    <tr>
                        <td class="label">NIS</td><td class="titik">:</td><td class="nilai">{{ $siswa->nis ?: '-' }}</td>
                        <td class="label label-kanan">Tempat, Tgl Lahir</td><td class="titik">:</td><td>{{ $siswa->tempat_lahir ?: '-' }}, {{ $siswa->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d F Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">NISN</td><td class="titik">:</td><td class="nilai">{{ $siswa->nisn ?: '-' }}</td>
                        <td class="label label-kanan">Kelas</td><td class="titik">:</td><td>{{ optional($siswa->kelas)->nama ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Jenis Kelamin</td><td class="titik">:</td><td class="nilai">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : ($siswa->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</td>
                        <td class="label label-kanan">Status</td><td class="titik">:</td><td>{{ $siswa->status_siswa ?: '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p class="judul-bagian">A. DATA PRIBADI</p>
    <table class="tabel-data">
        <tr><td class="label">NIK</td><td class="titik">:</td><td>{{ $siswa->nik ?: '-' }}</td></tr>
        <tr><td class="label">Golongan Darah</td><td class="titik">:</td><td>{{ $siswa->golongan_darah ?: '-' }}</td></tr>
        <tr><td class="label">Tinggi / Berat Badan</td><td class="titik">:</td><td>{{ $siswa->tinggi_badan ? $siswa->tinggi_badan.' cm' : '-' }} / {{ $siswa->berat_badan ? $siswa->berat_badan.' kg' : '-' }}</td></tr>
        <tr><td class="label">No. Kartu Keluarga</td><td class="titik">:</td><td>{{ $siswa->no_kartu_keluarga ?: '-' }}</td></tr>
    </table>

    <p class="judul-bagian">B. ALAMAT</p>
    <table class="tabel-data">
        <tr><td class="label">Alamat Jalan</td><td class="titik">:</td><td>{{ $siswa->alamat_jalan ?: '-' }}</td></tr>
        <tr><td class="label">RT / RW</td><td class="titik">:</td><td>{{ $siswa->rt ?: '-' }} / {{ $siswa->rw ?: '-' }}</td></tr>
        <tr><td class="label">Desa / Kelurahan</td><td class="titik">:</td><td>{{ $siswa->desa ?: '-' }}</td></tr>
        <tr><td class="label">Kecamatan</td><td class="titik">:</td><td>{{ $siswa->kecamatan ?: '-' }}</td></tr>
        <tr><td class="label">Kabupaten / Kota</td><td class="titik">:</td><td>{{ $siswa->kabupaten ?: '-' }}</td></tr>
        <tr><td class="label">Provinsi</td><td class="titik">:</td><td>{{ $siswa->provinsi ?: '-' }}</td></tr>
        <tr><td class="label">Kode Pos</td><td class="titik">:</td><td>{{ $siswa->kode_pos ?: '-' }}</td></tr>
    </table>

    <p class="judul-bagian">C. DATA ORANG TUA / WALI</p>
    <p class="sub-judul-ortu">Ayah</p>
    <table class="tabel-data">
        <tr><td class="label">NIK</td><td class="titik">:</td><td>{{ $siswa->nik_ayah ?: '-' }}</td></tr>
        <tr><td class="label">Nama</td><td class="titik">:</td><td>{{ $siswa->nama_ayah ?: '-' }}</td></tr>
        <tr><td class="label">Status</td><td class="titik">:</td><td>{{ $siswa->status_ayah ?: '-' }}</td></tr>
        <tr><td class="label">Pekerjaan</td><td class="titik">:</td><td>{{ $siswa->pekerjaan_ayah ?: '-' }}</td></tr>
        <tr><td class="label">Pendidikan</td><td class="titik">:</td><td>{{ $siswa->pendidikan_ayah ?: '-' }}</td></tr>
        <tr><td class="label">Penghasilan</td><td class="titik">:</td><td>{{ $siswa->penghasilan_ayah ?: '-' }}</td></tr>
        <tr><td class="label">No. WA</td><td class="titik">:</td><td>{{ $siswa->wa_ayah ?: '-' }}</td></tr>
    </table>

    <p class="sub-judul-ortu">Ibu</p>
    <table class="tabel-data">
        <tr><td class="label">NIK</td><td class="titik">:</td><td>{{ $siswa->nik_ibu ?: '-' }}</td></tr>
        <tr><td class="label">Nama</td><td class="titik">:</td><td>{{ $siswa->nama_ibu ?: '-' }}</td></tr>
        <tr><td class="label">Status</td><td class="titik">:</td><td>{{ $siswa->status_ibu ?: '-' }}</td></tr>
        <tr><td class="label">Pekerjaan</td><td class="titik">:</td><td>{{ $siswa->pekerjaan_ibu ?: '-' }}</td></tr>
        <tr><td class="label">Pendidikan</td><td class="titik">:</td><td>{{ $siswa->pendidikan_ibu ?: '-' }}</td></tr>
        <tr><td class="label">Penghasilan</td><td class="titik">:</td><td>{{ $siswa->penghasilan_ibu ?: '-' }}</td></tr>
        <tr><td class="label">No. WA</td><td class="titik">:</td><td>{{ $siswa->wa_ibu ?: '-' }}</td></tr>
    </table>

    @if($siswa->nama_wali)
    <p class="sub-judul-ortu">Wali</p>
    <table class="tabel-data">
        <tr><td class="label">NIK</td><td class="titik">:</td><td>{{ $siswa->nik_wali ?: '-' }}</td></tr>
        <tr><td class="label">Nama</td><td class="titik">:</td><td>{{ $siswa->nama_wali ?: '-' }}</td></tr>
        <tr><td class="label">Hubungan</td><td class="titik">:</td><td>{{ $siswa->hubungan_wali ?: '-' }}</td></tr>
        <tr><td class="label">Status</td><td class="titik">:</td><td>{{ $siswa->status_wali ?: '-' }}</td></tr>
        <tr><td class="label">Pekerjaan</td><td class="titik">:</td><td>{{ $siswa->pekerjaan_wali ?: '-' }}</td></tr>
        <tr><td class="label">Pendidikan</td><td class="titik">:</td><td>{{ $siswa->pendidikan_wali ?: '-' }}</td></tr>
        <tr><td class="label">Penghasilan</td><td class="titik">:</td><td>{{ $siswa->penghasilan_wali ?: '-' }}</td></tr>
        <tr><td class="label">No. WA</td><td class="titik">:</td><td>{{ $siswa->wa_wali ?: '-' }}</td></tr>
    </table>
    @endif

    <div class="footer-halaman">
        Dicetak {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} &middot; Halaman {{ $i + 1 }} dari {{ $siswas->count() }}
    </div>

</div>
@endforeach

</body>
</html>
