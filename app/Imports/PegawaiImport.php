<?php

namespace App\Imports;

use App\Models\Pegawai;
use App\Models\Lembaga;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PegawaiImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            // SKIP HEADER
            if ($index === 0) {
                continue;
            }

            // SKIP BARIS KOSONG
            if (
                !isset($row[0]) || trim($row[0]) === '' ||
                !isset($row[1]) || trim($row[1]) === ''
            ) {
                continue;
            }

            // VALIDASI JK
            $jk = strtoupper(trim($row[3] ?? ''));

            if (!in_array($jk, ['L', 'P'])) {
                continue;
            }

            $nama = trim($row[0]);
            $niy  = trim($row[1]);

            /*
            |--------------------------------------------------------------------------
            | FOTO OTOMATIS
            |--------------------------------------------------------------------------
            | Simpan foto di:
            | storage/app/public/foto-pegawai/
            | dengan nama:
            | 2007030004.jpg
            | 2007030004.png
            |--------------------------------------------------------------------------
            */
            $fotoPath = null;

            // Ambil semua file di folder foto-pegawai
            $files = Storage::disk('public')->files('foto-pegawai');
            
            foreach ($files as $file) {
            
                $filename = pathinfo($file, PATHINFO_FILENAME);
            
                if (trim((string) $filename) === trim((string) $niy)) {
                    $fotoPath = $file;
                    break;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | FORMAT TANGGAL MASUK
            |--------------------------------------------------------------------------
            */
            $tanggalMasuk = null;

            if (!empty($row[10])) {

                try {

                    // Jika format Excel number (45291)
                    if (is_numeric($row[10])) {

                        $tanggalMasuk = ExcelDate::excelToDateTimeObject(
                            $row[10]
                        )->format('Y-m-d');

                    } else {

                        $tanggalMasuk = Carbon::parse(
                            trim($row[10])
                        )->format('Y-m-d');
                    }

                } catch (\Exception $e) {

                    // Jika tanggal tidak valid
                    $tanggalMasuk = null;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | SIMPAN PEGAWAI
            |--------------------------------------------------------------------------
            */
            $pegawai = Pegawai::updateOrCreate(
                [
                    'niy' => $niy,
                ],
                [
                    'nama'            => $nama,
                    'nik'             => $row[2] ?? null,
                    'jenis_kelamin'   => $jk,
                    'no_hp'           => $row[4] ?? null,
                    'email'           => $row[5] ?? null,
                    'alamat'          => $row[6] ?? null,
                    'pendidikan'      => $row[7] ?? null,
                    'universitas'     => $row[8] ?? null,
                    'golongan'        => $row[9] ?? null,
                    'tanggal_masuk'   => $tanggalMasuk,
                    'is_active'       => true,
                    'foto'            => $fotoPath,
                ]
            );
            // Password default hanya jika masih kosong
            if (empty($pegawai->password)) {
                $pegawai->password = Hash::make($niy);
                $pegawai->save();
            }

            /*
            |--------------------------------------------------------------------------
            | LEMBAGA
            |--------------------------------------------------------------------------
            */
            $lembagaId = $row[11] ?? null;

            if (!$lembagaId) {
                continue;
            }

            $lembaga = Lembaga::find($lembagaId);

            if (!$lembaga) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | PIVOT PEGAWAI LEMBAGA
            |--------------------------------------------------------------------------
            */
            DB::table('pegawai_lembaga')->updateOrInsert(
                [
                    'pegawai_id' => $pegawai->id,
                    'lembaga_id' => $lembaga->id,
                ],
                [
                    'jabatan'    => isset($row[12]) ? trim($row[12]) : null,
                    'status'     => isset($row[13]) ? trim($row[13]) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}