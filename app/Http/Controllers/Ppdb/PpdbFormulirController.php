<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Ppdb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PpdbFormulirController extends Controller
{
    /**
     * Pastikan biaya pendaftaran (tipe_sistem = pendaftaran_ppdb) sudah
     * lunas sebelum boleh mengakses Formulir / Upload Berkas. Kalau
     * lembaga ini tidak mengaktifkan Jenis Tagihan pendaftaran sama
     * sekali, akses tetap dibolehkan (tidak ada yang perlu dibayar).
     */
    private function pastikanSudahBayarFormulir(Ppdb $ppdb)
    {
        // Pastikan dulu tagihannya ada (kalau lembaga ini memang pakai
        // fitur ini) -- supaya guard ini tetap benar walau user buka
        // langsung URL Formulir/Berkas tanpa lewat dashboard dulu.
        \App\Models\Tagihan::pastikanTagihanPendaftaranPpdb($ppdb);

        $adaTagihanPendaftaran = \App\Models\Tagihan::where('ppdb_id', $ppdb->id)
            ->whereHas('jenisTagihan', fn ($q) => $q->where('tipe_sistem', 'pendaftaran_ppdb'))
            ->exists();

        if (!$adaTagihanPendaftaran) {
            return null; // tidak ada tagihan pendaftaran → tidak perlu dikunci
        }

        $sudahLunas = \App\Models\Tagihan::where('ppdb_id', $ppdb->id)
            ->whereHas('jenisTagihan', fn ($q) => $q->where('tipe_sistem', 'pendaftaran_ppdb'))
            ->where('status', 'lunas')
            ->exists();

        if (!$sudahLunas) {
            return redirect()
                ->route('ppdb.pembayaran')
                ->with('error', 'Silakan selesaikan pembayaran formulir pendaftaran terlebih dahulu.');
        }

        return null;
    }

    /**
     * Tampilkan Formulir PPDB
     */
    public function index()
    {
        $ppdb = Ppdb::findOrFail(session('ppdb_id'));

        if ($redirect = $this->pastikanSudahBayarFormulir($ppdb)) {
            return $redirect;
        }

        return view('ppdb.formulir', [
            'ppdb' => $ppdb,
            'yayasan' => $ppdb->lembaga?->yayasan ?? \App\Models\Yayasan::first(),
        ]);
    }

    /**
     * Simpan Formulir PPDB
     */
    public function store(Request $request)
    {
        $ppdb = Ppdb::findOrFail(session('ppdb_id'));

        if ($redirect = $this->pastikanSudahBayarFormulir($ppdb)) {
            return $redirect;
        }

        $rules = [

        /*
        |--------------------------------------------------------------------------
        | STEP 1 - DATA PRIBADI
        |--------------------------------------------------------------------------
        */
        
        'foto'              => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        'nama_lengkap'      => 'required|string|max:255',
        'nisn'              => 'required|string|max:30',
        'nik'               => 'required|string|max:30',
        'jenis_kelamin'     => 'required|in:L,P',
        'tempat_lahir'      => 'required|string|max:100',
        'tanggal_lahir'     => 'required|date',
    
        'tinggi_badan'      => 'required|numeric',
        'berat_badan'       => 'required|numeric',
        'golongan_darah'    => 'required|string|max:5',
    
        'asal_sekolah'      => 'required|string|max:255',
    
        /*
        |--------------------------------------------------------------------------
        | STEP 2 - DATA ALAMAT
        |--------------------------------------------------------------------------
        */
    
        'alamat_jalan'      => 'required|string',
        'provinsi'          => 'required|string|max:100',
        'kabupaten'         => 'required|string|max:100',
        'kecamatan'         => 'required|string|max:100',
        'desa'              => 'required|string|max:100',
        'rt'                => 'required|string|max:5',
        'rw'                => 'required|string|max:5',
        'kode_pos'          => 'required|string|max:10',
    
        /*
        |--------------------------------------------------------------------------
        | STEP 3 - DATA ORANG TUA
        |--------------------------------------------------------------------------
        */
    
        'no_kartu_keluarga' => 'required|string|max:30',
    
        'nik_ayah'          => 'required|string|max:30',
        'nama_ayah'         => 'required|string|max:255',
        'status_ayah'       => 'required|string|max:100',
        'pekerjaan_ayah'    => 'required|string|max:100',
        'pendidikan_ayah'   => 'required|string|max:100',
        'penghasilan_ayah'  => 'required|string|max:100',
        'wa_ayah'           => 'required|string|max:20',
    
        'nik_ibu'           => 'required|string|max:30',
        'nama_ibu'          => 'required|string|max:255',
        'status_ibu'        => 'required|string|max:100',
        'pekerjaan_ibu'     => 'required|string|max:100',
        'pendidikan_ibu'    => 'required|string|max:100',
        'penghasilan_ibu'   => 'required|string|max:100',
        'wa_ibu'            => 'required|string|max:20',
    
        /*
        |--------------------------------------------------------------------------
        | STEP 4 - DATA WALI (OPSIONAL)
        |--------------------------------------------------------------------------
        */
    
        'nik_wali'          => 'nullable|string|max:30',
        'nama_wali'         => 'nullable|string|max:255',
        'status_wali'       => 'nullable|string|max:100',
        'hubungan_wali'     => 'nullable|string|max:100',
        'pekerjaan_wali'    => 'nullable|string|max:100',
        'pendidikan_wali'   => 'nullable|string|max:100',
        'penghasilan_wali'  => 'nullable|string|max:100',
        'wa_wali'           => 'nullable|string|max:20',
    
        ];
        
        $validated = $request->validate($rules, [
        
            'foto.image'    => 'Foto harus berupa gambar.',
            'foto.mimes'    => 'Format foto harus JPG, JPEG, PNG atau WEBP.',
            'foto.max'      => 'Ukuran foto maksimal 2 MB.',
            'foto.required' => 'Foto wajib diupload.',
        
        ]);

        /*
        |--------------------------------------------------------------------------
        | Upload Foto (Step 1)
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('foto')) {

            // hapus foto lama
            if ($ppdb->foto) {
                try {
                    \Storage::disk('r2-public')->delete($ppdb->foto);
                } catch (\Throwable $e) {
                    // biarkan, foto lama mungkin masih di disk lokal (data lama)
                }
            }

            $webp = \Intervention\Image\Laravel\Facades\Image::decode(
                    file_get_contents($request->file('foto')->getRealPath())
                )
                ->cover(800, 1000)
                ->encodeUsingFileExtension('webp', quality: 80);

            $filename = 'siswa-photos/' . uniqid() . '.webp';

            \Storage::disk('r2-public')->put($filename, (string) $webp);

            $validated['foto'] = $filename;

        }

        /*
        |--------------------------------------------------------------------------
        | Update Data
        |--------------------------------------------------------------------------
        */

        $validated['status'] = 'upload_berkas';

        $ppdb->update($validated);

        return redirect()
            ->route('ppdb.dashboard')
            ->with('success', 'Formulir berhasil disimpan.');
    }
    
     /**
     * Halaman Upload Berkas
     */
    public function uploadBerkas()
    {
        $ppdb = Ppdb::findOrFail(session('ppdb_id'));

        if ($redirect = $this->pastikanSudahBayarFormulir($ppdb)) {
            return $redirect;
        }

        return view('ppdb.upload-berkas', [
            'ppdb' => $ppdb,
            'yayasan' => $ppdb->lembaga?->yayasan ?? \App\Models\Yayasan::first(),
        ]);
    }

    /**
     * Simpan Berkas PPDB
     */
    public function storeBerkas(Request $request)
    {
        $ppdb = Ppdb::findOrFail(session('ppdb_id'));

        if ($redirect = $this->pastikanSudahBayarFormulir($ppdb)) {
            return $redirect;
        }

        $validated = $request->validate([
            'scan_kk'      => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
            'scan_akta'    => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
            'scan_ijazah'  => 'nullable|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'scan_kk.required'     => 'Kartu Keluarga wajib diupload.',
            'scan_akta.required'   => 'Akta Kelahiran wajib diupload.',

            'scan_kk.mimes'        => 'KK harus berupa JPG, PNG atau PDF.',
            'scan_akta.mimes'      => 'Akta harus berupa JPG, PNG atau PDF.',
            'scan_ijazah.mimes'    => 'Ijazah harus berupa JPG, PNG atau PDF.',

            'scan_kk.max'          => 'Ukuran KK maksimal 2 MB.',
            'scan_akta.max'        => 'Ukuran Akta maksimal 2 MB.',
            'scan_ijazah.max'      => 'Ukuran Ijazah maksimal 2 MB.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Upload KK
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('scan_kk')) {

            if ($ppdb->scan_kk) {
                try {
                    \Storage::disk('r2-private')->delete($ppdb->scan_kk);
                } catch (\Throwable $e) {
                }
            }

            $validated['scan_kk'] = $request
                ->file('scan_kk')
                ->store('ppdb/berkas', 'r2-private');
        }

        /*
        |--------------------------------------------------------------------------
        | Upload Akta
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('scan_akta')) {

            if ($ppdb->scan_akta) {
                try {
                    \Storage::disk('r2-private')->delete($ppdb->scan_akta);
                } catch (\Throwable $e) {
                }
            }

            $validated['scan_akta'] = $request
                ->file('scan_akta')
                ->store('ppdb/berkas', 'r2-private');
        }

        /*
        |--------------------------------------------------------------------------
        | Upload Ijazah
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('scan_ijazah')) {

            if ($ppdb->scan_ijazah) {
                try {
                    \Storage::disk('r2-private')->delete($ppdb->scan_ijazah);
                } catch (\Throwable $e) {
                }
            }

            $validated['scan_ijazah'] = $request
                ->file('scan_ijazah')
                ->store('ppdb/berkas', 'r2-private');
        }

        /*
        |--------------------------------------------------------------------------
        | Update Status PPDB
        |--------------------------------------------------------------------------
        */

        $validated['status'] = 'verifikasi_berkas';

        $ppdb->update($validated);

        return redirect()
            ->route('ppdb.dashboard')
            ->with('success', 'Berkas berhasil dikirim dan sedang menunggu verifikasi panitia.');
    }
}