<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Raport</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 24px 16px;
            color: #0f172a;
        }
        .card {
            max-width: 420px;
            margin: 0 auto;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #00A39D, #14C8C0);
            color: #fff;
            padding: 28px 24px;
            text-align: center;
        }
        .header .check {
            width: 56px;
            height: 56px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 28px;
        }
        .header h1 {
            font-size: 17px;
            margin: 0;
        }
        .body {
            padding: 20px 24px 28px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }
        .row:last-child {
            border-bottom: none;
        }
        .row .label {
            color: #64748b;
        }
        .row .value {
            font-weight: 600;
            text-align: right;
        }
        .footer-note {
            margin-top: 16px;
            font-size: 12px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="card">

        <div class="header">
            <div class="check">✓</div>
            <h1>Raport ini Sah &amp; Terverifikasi</h1>
        </div>

        <div class="body">

            <div class="row">
                <span class="label">Nama Siswa</span>
                <span class="value">{{ $siswa->nama_lengkap }}</span>
            </div>

            <div class="row">
                <span class="label">NIS</span>
                <span class="value">{{ $siswa->nis ?? '-' }}</span>
            </div>

            <div class="row">
                <span class="label">Kelas</span>
                <span class="value">{{ $siswa->kelas->nama ?? '-' }}</span>
            </div>

            <div class="row">
                <span class="label">Sekolah</span>
                <span class="value">{{ $lembaga->nama ?? '-' }}</span>
            </div>

            <div class="row">
                <span class="label">Tahun Ajaran</span>
                <span class="value">{{ $tahunAjaran->nama }} - {{ $tahunAjaran->semester }}</span>
            </div>

            <div class="row">
                <span class="label">Jenis Raport</span>
                <span class="value">{{ strtoupper($jenisPenilaian) }}</span>
            </div>

            <p class="footer-note">
                Dokumen ini diterbitkan resmi oleh sistem {{ $lembaga->nama ?? 'sekolah' }} melalui Qinara Apps.
            </p>

        </div>

    </div>

</body>
</html>
