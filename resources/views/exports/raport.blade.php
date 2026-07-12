<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">

    <style>

        @page{
            margin:50px 45px;
        }

        body{
            font-family:sans-serif;
            font-size:12px;
            color:#000;
            padding:10px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th,
        td{
            border:1px solid #000;
            padding:6px;
            vertical-align:top;
        }

        .borderless td{
            border:none;
            padding:2px 4px;
        }

        .title{
            text-align:center;
            font-size:19px;
            font-weight:bold;
        }

        .subtitle{
            text-align:center;
            font-size:17px;
            font-weight:bold;
            margin-bottom:10px;
        }

        .line{
            border-top:1px solid #000;
            margin:10px 0 20px;
        }

        .text-center{
            text-align:center;
        }

        .font-bold{
            font-weight:bold;
        }

        .table-header{
            background:#d1d5db;
        }

        .mb-20{
            margin-bottom:20px;
        }

        .mb-10{
            margin-bottom:10px;
        }

        .signature{
            margin-top:40px;
        }

        .ttd-nama{
            font-weight:bold;
            margin-top:25px;
        }

        .no-border{
            border:none !important;
        }

        .guru{
            font-size:10px;
            margin-top:2px;
        }

    </style>

</head>

<body>

    {{-- HEADER --}}
    <div class="title">
        RAPORT HASIL STUDI SISWA
    </div>

    <div class="subtitle">
        {{ $siswa->kelas->lembaga->nama ?? '-' }}
    </div>

    <div class="line"></div>

    {{-- IDENTITAS --}}
    <table class="borderless mb-20">

        <tr>

            <td width="10%">Nama</td>

            <td width="40%">
                : {{ $siswa->nama_lengkap }}
            </td>

            <td width="10%">Sekolah</td>

            <td width="40%">
                : {{ $siswa->kelas->lembaga->nama ?? '-' }}
            </td>

        </tr>

        <tr>

            <td>NIS</td>

            <td>
                : {{ $siswa->nis ?? '-' }}
            </td>

            <td>Kelas</td>

            <td>
                : {{ $siswa->kelas->nama ?? '-' }}
            </td>

        </tr>

        <tr>

            <td>NISN</td>

            <td>
                : {{ $siswa->nisn ?? '-' }}
            </td>

            <td>Semester</td>

            <td>
                : {{ ucfirst($tahunAjaran->semester ?? '-') }}
            </td>

        </tr>

    </table>

    {{-- AKADEMIK --}}
    <table class="mb-20">

        <tr class="font-bold text-center table-header">

            <td width="6%">No</td>

            <td width="28%">
                Mata Pelajaran
            </td>

            <td width="10%">
                Nilai
            </td>

            <td width="10%">
                Grade
            </td>

            <td width="50%">
                Deskripsi
            </td>

        </tr>

        @forelse ($nilaiAkademik as $item)

            <tr>

                <td class="text-center">
                    {{ $loop->iteration }}
                </td>

                <td>

                    <div class="font-bold">
                        {{ $item['mapel'] }}
                    </div>

                    @if(($item['guru'] ?? '-') !== '-')

                        <div class="guru">

                            Guru :
                            {{ $item['guru'] }}

                        </div>

                    @endif

                </td>

                <td class="text-center">
                    {{ $item['nilai_akhir'] }}
                </td>

                <td class="text-center">
                    {{ $item['grade'] }}
                </td>

                <td>
                    {{ $item['deskripsi'] }}
                </td>

            </tr>

        @empty

            <tr>

                <td
                    colspan="5"
                    class="text-center"
                >
                    Belum ada nilai akademik
                </td>

            </tr>

        @endforelse

    </table>

    {{-- SUMMARY --}}
    <table class="mb-20">

        <tr class="font-bold text-center table-header">

            <td>Total Nilai</td>

            <td>Rata-Rata</td>

            <td>Nilai Tertinggi</td>

            <td>Nilai Terendah</td>

        </tr>

        <tr class="text-center">

            <td>{{ $total }}</td>

            <td>{{ round($rataRata) }}</td>

            <td>{{ $tertinggi }}</td>

            <td>{{ $terendah }}</td>

        </tr>

    </table>

    {{-- NON AKADEMIK --}}
    <table
        class="mb-20"
        style="
            border:none;
            width:100%;
            table-layout:fixed;
        "
    >

        <tr>

            {{-- EKSKUL --}}
            <td
                style="
                    width:50%;
                    padding:0 6px 0 0;
                "
                class="no-border"
            >

                <div class="mb-10 font-bold">
                    Nilai Ekstrakulikuler
                </div>

                <table>

                    <tr class="font-bold text-center table-header">

                        <td width="10%">No</td>

                        <td>Ekskul</td>

                        <td width="20%">
                            Nilai
                        </td>

                        <td width="20%">
                            Grade
                        </td>

                    </tr>

                    @forelse (($nonAkademik?->ekstrakurikulers ?? []) as $item)

                        <tr>

                            <td class="text-center">
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                {{ $item->nama_ekskul }}
                            </td>

                            <td class="text-center">
                                {{ $item->nilai }}
                            </td>

                            <td class="text-center">
                                {{ $item->grade }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="4"
                                class="text-center"
                            >
                                Belum ada data ekstrakurikuler
                            </td>

                        </tr>

                    @endforelse

                </table>

            </td>

            {{-- KEPRIBADIAN --}}
            <td
                style="
                    width:50%;
                    padding:0 0 0 6px;
                "
                class="no-border"
            >

                <div class="mb-10 font-bold">
                    Kepribadian
                </div>

                <table>

                    <tr class="font-bold text-center table-header">

                        <td width="10%">No</td>

                        <td>Aspek</td>

                        <td width="20%">
                            Nilai
                        </td>

                        <td width="20%">
                            Grade
                        </td>

                    </tr>

                    @forelse (($nonAkademik?->kepribadians ?? []) as $item)

                        <tr>

                            <td class="text-center">
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                {{ $item->aspek }}
                            </td>

                            <td class="text-center">
                                {{ $item->nilai }}
                            </td>

                            <td class="text-center">
                                {{ $item->grade }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="4"
                                class="text-center"
                            >
                                Belum ada data kepribadian
                            </td>

                        </tr>

                    @endforelse

                </table>

            </td>

        </tr>

    </table>

    {{-- CATATAN & ABSENSI --}}
    <table
        class="mb-20"
        style="
            border:none;
            width:100%;
            table-layout:fixed;
        "
    >

        <tr>

            {{-- CATATAN --}}
            <td
                style="
                    width:50%;
                    padding:0 6px 0 0;
                "
                class="no-border"
            >

                <div class="mb-10 font-bold">
                    Catatan Wali Kelas
                </div>

                <table
                    style="
                        height:136px;
                    "
                >

                    <tr class="font-bold text-center table-header">

                        <td>
                            Catatan
                        </td>

                    </tr>

                    <tr>

                        <td
                            style="
                                vertical-align:top;
                            "
                        >

                            {{ $nonAkademik?->catatan_wali_kelas ?? '-' }}

                        </td>

                    </tr>

                </table>

            </td>

            {{-- ABSENSI --}}
<td
    style="
        width:50%;
        padding:0 0 0 6px;
    "
    class="no-border"
>

    <div class="mb-10 font-bold">
        Absensi
    </div>

    <table
        style="
            height:136px;
        "
    >

        <tr class="font-bold text-center table-header">

            <td width="10%">
                No
            </td>

            <td>
                Jenis
            </td>

            <td width="20%">
                Jumlah
            </td>

        </tr>

        <tr>

            <td class="text-center">
                1
            </td>

            <td>
                Sakit
            </td>

            <td class="text-center">
                {{ $absensiSummary['sakit'] ?? 0 }}
            </td>

        </tr>

        <tr>

            <td class="text-center">
                2
            </td>

            <td>
                Izin
            </td>

            <td class="text-center">
                {{ $absensiSummary['izin'] ?? 0 }}
            </td>

        </tr>

        <tr>

            <td class="text-center">
                3
            </td>

            <td>
                Alpa
            </td>

            <td class="text-center">
                {{ $absensiSummary['alpha'] ?? 0 }}
            </td>

        </tr>

    </table>

</td>

            </td>

        </tr>

    </table>

    {{-- TTD --}}
<table class="borderless signature">

    <tr class="text-center">

        <td width="33%">
            Wali Murid
        </td>

        <td width="33%">
            Wali Kelas
        </td>

        <td width="33%">
            Kepala Sekolah
        </td>

    </tr>

    <tr>

        <td
            height="55"
            class="no-border text-center"
        >
            &nbsp;
        </td>

        <td
            class="no-border text-center"
        >
            &nbsp;
        </td>

        <td
            class="no-border text-center"
        >
            &nbsp;
        </td>

    </tr>

    <tr>

        <td
            class="no-border text-center"
        >
            (_________________)
        </td>

        <td
            class="
                no-border
                text-center
                ttd-nama
            "
        >

            <strong>
                {{ $siswa->kelas->waliKelas->nama ?? '-' }}
            </strong>

        </td>

        <td
            class="
                no-border
                text-center
                ttd-nama
            "
        >

            <strong>

                {{
                    $siswa->kelas
                        ->lembaga
                        ->kepala_sekolah ?? '-'
                }}

            </strong>

        </td>

    </tr>

</table>

</body>
</html>