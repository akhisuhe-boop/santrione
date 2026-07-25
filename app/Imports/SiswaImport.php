<?php

namespace App\Imports;

use App\Models\Siswa;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class SiswaImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, ShouldQueue, WithChunkReading
{
    use SkipsFailures;

    /**
     * Password default di-hash SEKALI saat class ini di-instantiate
     * (bukan di dalam model() yang dipanggil per-baris), karena semua
     * siswa hasil import memang diberi password default yang sama.
     * Ini yang tadinya bikin proses lambat: bcrypt dipanggil ratusan kali
     * untuk hasil yang sebenarnya sama saja.
     */
    protected string $defaultPasswordHash;

    public function __construct()
    {
        $this->defaultPasswordHash = Hash::make('12345678');
    }

    /**
     * Wajib ada karena implements WithChunkReading.
     * File Excel akan dibaca & diproses 100 baris per job,
     * bukan semua baris sekaligus dalam satu request.
     */
    public function chunkSize(): int
    {
        return 100;
    }

    public function model(array $row)
    {
        if (empty($row['nama_lengkap'])) {
            return null;
        }

        // Default foto null
        $fotoPath = null;

        if (!empty($row['nis'])) {
            $extensions = ['jpg', 'jpeg', 'png'];
            foreach ($extensions as $ext) {
                $file = 'foto-siswa/' . $row['nis'] . '.' . $ext;
                if (Storage::disk('public')->exists($file)) {
                    $fotoPath = $file; // simpan path relatif ke storage/public
                    break;
                }
            }
        }

        // updateOrCreate → aman import ulang
        return Siswa::updateOrCreate(
            ['nis' => $row['nis']], // master key
            [
                'lembaga_id' => $row['lembaga_id'],
                'kelas_id' => $row['kelas_id'],
                'rfid' => $row['rfid'],
                'nisn' => $row['nisn'],
                'nik' => $row['nik'],
                'nama_lengkap' => $row['nama_lengkap'],
                'jenis_kelamin' => $row['jenis_kelamin'],
                'tempat_lahir' => $row['tempat_lahir'],
                'tanggal_lahir' => $row['tanggal_lahir'],

                'tinggi_badan' => $row['tinggi_badan'],
                'berat_badan' => $row['berat_badan'],
                'golongan_darah' => $row['golongan_darah'],

                'alamat_jalan' => $row['alamat_jalan'],
                'provinsi' => $row['provinsi'],
                'kabupaten' => $row['kabupaten'],
                'kecamatan' => $row['kecamatan'],
                'desa' => $row['desa'],
                'kode_pos' => $row['kode_pos'],

                'no_kartu_keluarga' => $row['no_kartu_keluarga'],

                'nik_ayah' => $row['nik_ayah'],
                'nama_ayah' => $row['nama_ayah'],
                'status_ayah' => $row['status_ayah'],
                'pekerjaan_ayah' => $row['pekerjaan_ayah'],
                'pendidikan_ayah' => $row['pendidikan_ayah'],
                'penghasilan_ayah' => $row['penghasilan_ayah'],
                'wa_ayah' => $row['wa_ayah'],

                'nik_ibu' => $row['nik_ibu'],
                'nama_ibu' => $row['nama_ibu'],
                'status_ibu' => $row['status_ibu'],
                'pekerjaan_ibu' => $row['pekerjaan_ibu'],
                'pendidikan_ibu' => $row['pendidikan_ibu'],
                'penghasilan_ibu' => $row['penghasilan_ibu'],
                'wa_ibu' => $row['wa_ibu'],

                'nik_wali' => $row['nik_wali'],
                'nama_wali' => $row['nama_wali'],
                'hubungan_wali' => $row['hubungan_wali'],
                'status_wali' => $row['status_wali'],
                'pekerjaan_wali' => $row['pekerjaan_wali'],
                'pendidikan_wali' => $row['pendidikan_wali'],
                'penghasilan_wali' => $row['penghasilan_wali'],
                'wa_wali' => $row['wa_wali'],

                'status_siswa' => $row['status_siswa'] ?? 'Aktif',

                // foto
                'foto' => is_string($fotoPath) ? $fotoPath : null,

                // AUTO AKUN ORTU & siswa
                'password' => $this->defaultPasswordHash,
                'pin' => '123456',
            ]
        );
    }

    public function rules(): array
    {
        return [
            'lembaga_id' => 'required|exists:lembagas,id',
            'kelas_id' => 'required|exists:kelas,id',
            'nis' => 'required',
            'nama_lengkap' => 'required',
            'jenis_kelamin' => 'required|in:L,P',
            'foto' => 'nullable|string',
        ];
    }
}
