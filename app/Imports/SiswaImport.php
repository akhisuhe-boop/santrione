<?php

namespace App\Imports;

use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

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

    /**
     * ID user yang melakukan import. WAJIB dikirim dari luar (lihat
     * SiswaResource) karena SiswaImport sekarang ShouldQueue: job-nya
     * jalan di queue worker, di mana Auth::user() selalu null (tidak
     * ada session HTTP). Model Siswa pakai trait BelongsToTenant yang
     * scope query-nya berdasarkan Auth::user() — kalau tidak di-restore
     * manual, scope tenant tidak diterapkan sama sekali di dalam job,
     * dan updateOrCreate(['nis' => ...]) bisa salah menimpa siswa milik
     * yayasan lain kalau NIS-nya kebetulan sama.
     */
    protected int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
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

    /**
     * PERUBAHAN 29 Agt 2026: kalau kolom tanggal lahir di Excel
     * diformat sebagai TANGGAL beneran (bukan teks), PhpSpreadsheet
     * membaca isinya sebagai angka SERIAL internal Excel (mis. 43745),
     * bukan string "2019-10-07" -- MySQL menolak angka mentah itu
     * disimpan ke kolom date ("Incorrect date value"). Method ini
     * mendeteksi & mengonversi kedua kemungkinan bentuk (angka serial
     * ATAU teks tanggal biasa) jadi format Y-m-d yang valid. Kalau
     * tidak bisa di-parse sama sekali, kembalikan null (skip kolom itu
     * saja untuk baris ini) -- BUKAN melempar error yang mematikan
     * seluruh proses import.
     */
    protected function parseTanggalLahir($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function model(array $row)
    {
        // Restore tenant context: job ini jalan di queue worker (tanpa
        // session HTTP), jadi Auth::user() default-nya null. Baris ini
        // mengembalikan identitas user yang upload untuk 1 kali resolve
        // Auth::user(), supaya global scope tenant di model Siswa
        // (trait BelongsToTenant) aktif seperti biasa.
        Auth::onceUsingId($this->userId);

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
                'nama_lengkap' => $row['nama_lengkap'],
                'jenis_kelamin' => $row['jenis_kelamin'],

                // PERUBAHAN 27 Agt 2026: semua kolom OPSIONAL di bawah
                // ini (tidak ada di rules() -- lihat method rules())
                // sekarang pakai `?? null`, bukan akses langsung
                // $row['xxx']. Sebelumnya, kalau 1 SAJA kolom ini hilang
                // atau nama headernya beda di file Excel yang di-upload,
                // PHP melempar "Undefined array key" dan MEMATIKAN
                // SELURUH job import (bukan cuma skip baris/kolom itu) --
                // ini yang bikin import "sedang diproses" tidak
                // pernah selesai/data tidak pernah masuk. Kolom WAJIB
                // (nis, lembaga_id, kelas_id, nama_lengkap,
                // jenis_kelamin) TIDAK diubah -- itu sudah dijamin ada
                // lewat rules() + SkipsOnFailure sebelum sampai ke sini.
                'rfid' => $row['rfid'] ?? null,
                'nisn' => $row['nisn'] ?? null,
                'nik' => $row['nik'] ?? null,
                'tempat_lahir' => $row['tempat_lahir'] ?? null,
                // PERUBAHAN 29 Agt 2026: pakai parseTanggalLahir(),
                // bukan akses langsung -- lihat komentar method itu.
                'tanggal_lahir' => $this->parseTanggalLahir($row['tanggal_lahir'] ?? null),

                'tinggi_badan' => $row['tinggi_badan'] ?? null,
                'berat_badan' => $row['berat_badan'] ?? null,
                'golongan_darah' => $row['golongan_darah'] ?? null,

                'alamat_jalan' => $row['alamat_jalan'] ?? null,
                'provinsi' => $row['provinsi'] ?? null,
                'kabupaten' => $row['kabupaten'] ?? null,
                'kecamatan' => $row['kecamatan'] ?? null,
                'desa' => $row['desa'] ?? null,
                'kode_pos' => $row['kode_pos'] ?? null,

                'no_kartu_keluarga' => $row['no_kartu_keluarga'] ?? null,

                'nik_ayah' => $row['nik_ayah'] ?? null,
                'nama_ayah' => $row['nama_ayah'] ?? null,
                'status_ayah' => $row['status_ayah'] ?? null,
                'pekerjaan_ayah' => $row['pekerjaan_ayah'] ?? null,
                'pendidikan_ayah' => $row['pendidikan_ayah'] ?? null,
                'penghasilan_ayah' => $row['penghasilan_ayah'] ?? null,
                'wa_ayah' => $row['wa_ayah'] ?? null,

                'nik_ibu' => $row['nik_ibu'] ?? null,
                'nama_ibu' => $row['nama_ibu'] ?? null,
                'status_ibu' => $row['status_ibu'] ?? null,
                'pekerjaan_ibu' => $row['pekerjaan_ibu'] ?? null,
                'pendidikan_ibu' => $row['pendidikan_ibu'] ?? null,
                'penghasilan_ibu' => $row['penghasilan_ibu'] ?? null,
                'wa_ibu' => $row['wa_ibu'] ?? null,

                'nik_wali' => $row['nik_wali'] ?? null,
                'nama_wali' => $row['nama_wali'] ?? null,
                'hubungan_wali' => $row['hubungan_wali'] ?? null,
                'status_wali' => $row['status_wali'] ?? null,
                'pekerjaan_wali' => $row['pekerjaan_wali'] ?? null,
                'pendidikan_wali' => $row['pendidikan_wali'] ?? null,
                'penghasilan_wali' => $row['penghasilan_wali'] ?? null,
                'wa_wali' => $row['wa_wali'] ?? null,

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

    /**
     * PERUBAHAN 29 Agt 2026 -- fitur pengaman baru, ditambahkan
     * setelah insiden: 1 file Excel sempat berisi NIS yang KEBETULAN
     * sama dengan siswa LAIN (orang berbeda, di Lembaga yang sama),
     * dan updateOrCreate(['nis' => ...]) MENIMPA DIAM-DIAM data siswa
     * lama itu dengan data siswa baru yang salah -- tanpa peringatan
     * apa pun, ketahuan belakangan lewat jumlah siswa yang aneh di
     * Dashboard.
     *
     * Method ini (fitur bawaan Laravel Excel, dipanggil otomatis
     * sebelum tiap baris divalidasi) menambahkan 1 pengecekan lagi:
     * kalau NIS di baris ini SUDAH dipakai siswa lain DI LEMBAGA YANG
     * SAMA, dan namanya beda (bukan cuma beda kapitalisasi/spasi),
     * baris itu otomatis DI-SKIP (gagal validasi) -- BUKAN
     * ditimpa. Baris yang di-skip tercatat di $this->failures()
     * (lewat SkipsOnFailure + SkipsFailures yang sudah dipakai),
     * sama seperti validasi lembaga_id/kelas_id yang sudah ada --
     * jadi admin yang upload akan lihat baris mana yang gagal &
     * kenapa, bukan diam-diam ketiban masalah kayak insiden kemarin.
     *
     * SENGAJA discope per lembaga_id (bukan cek ke SEMUA sekolah
     * sekaligus) -- NIS itu wajar kalau kebetulan sama antar sekolah
     * BERBEDA (masing-masing punya penomoran sendiri), itu bukan
     * kesalahan. Yang berbahaya cuma kalau bentrok di lembaga yang
     * SAMA.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();

            if (empty($data['nis']) || empty($data['nama_lengkap']) || empty($data['lembaga_id'])) {
                return;
            }

            $existing = Siswa::withoutGlobalScopes()
                ->where('nis', $data['nis'])
                ->where('lembaga_id', $data['lembaga_id'])
                ->first();

            if ($existing && ! $this->namaMiripSama($existing->nama_lengkap, $data['nama_lengkap'])) {
                $validator->errors()->add(
                    'nis',
                    "NIS {$data['nis']} sudah dipakai siswa lain di lembaga ini (\"{$existing->nama_lengkap}\") -- baris ini di-skip, TIDAK menimpa data yang sudah ada. Cek dulu manual apakah ini orang yang sama atau NIS yang salah ketik."
                );
            }
        });
    }

    /**
     * Normalisasi sederhana (huruf kecil semua + rapikan spasi ganda)
     * supaya beda kapitalisasi atau spasi tipis TIDAK dianggap "orang
     * berbeda" (mis. "budi santoso" vs "Budi Santoso" tetap dianggap
     * sama, tidak perlu di-skip).
     */
    protected function namaMiripSama(string $a, string $b): bool
    {
        $normalisasi = fn (string $s) => preg_replace('/\s+/', ' ', mb_strtolower(trim($s)));

        return $normalisasi($a) === $normalisasi($b);
    }
}
