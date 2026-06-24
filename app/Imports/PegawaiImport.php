<?php

namespace App\Imports;

use App\Models\Pegawai;
use App\Models\Lembaga;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;

class PegawaiImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
        // 🔥 SKIP HEADER (BARIS PERTAMA)
        if ($index === 0) {
            continue;
        }

        // 🔥 SKIP BARIS KOSONG
        if (
            !isset($row[0]) || trim($row[0]) == '' ||
            !isset($row[1]) || trim($row[1]) == ''
        ) {
            continue;
        }

        // 🔥 VALIDASI JENIS KELAMIN
        $jk = strtoupper(trim($row[3] ?? ''));

        if (!in_array($jk, ['L', 'P'])) {
            continue; // skip kalau bukan L/P
        }

        $nama = trim($row[0]);
        $niy  = trim($row[1]);

        // 🔥 AUTO FOTO
        $fotoPath = null;
        foreach (['jpg', 'jpeg', 'png'] as $ext) {
            $path = "pegawai/{$niy}.{$ext}";
            if (Storage::disk('public')->exists($path)) {
                $fotoPath = $path;
                break;
            }
        }

        // 🔥 SIMPAN PEGAWAI
        $pegawai = Pegawai::updateOrCreate(
            ['niy' => $niy],
            [
                'nama' => $nama,
                'nik' => $row[2] ?? null,
                'jenis_kelamin' => $jk,
                'no_hp' => $row[4] ?? null,
                'email' => $row[5] ?? null,
                'alamat' => $row[6] ?? null,
                'pendidikan' => $row[7] ?? null,
                'universitas' => $row[8] ?? null,
                'golongan' => $row[9] ?? null,
                'tanggal_masuk' => $row[10] ?? null,
                'is_active' => true,
                'foto' => $fotoPath,
            ]
        );

        // 🔥 LEMBAGA
        $lembagaId = $row[11] ?? null;

        if (!$lembagaId) {
            continue;
        }

        $lembaga = Lembaga::find($lembagaId);

        if (!$lembaga) {
            continue;
        }

        // 🔥 PIVOT
        DB::table('pegawai_lembaga')->updateOrInsert(
            [
                'pegawai_id' => $pegawai->id,
                'lembaga_id' => $lembaga->id,
            ],
            [
                'jabatan' => isset($row[12]) ? trim($row[12]) : null,
                'status' => isset($row[13]) ? trim($row[13]) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
    }
}