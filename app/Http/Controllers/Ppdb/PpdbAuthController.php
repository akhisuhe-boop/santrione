<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Concerns\ResolvesPublicTenant;
use App\Http\Controllers\Controller;
use App\Models\Lembaga;
use App\Models\Ppdb;
use App\Models\TahunAjaran;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PpdbAuthController extends Controller
{
    use ResolvesPublicTenant;

    /**
     * Form Login
     */
    public function login()
    {
        $yayasan = $this->currentYayasan();
        return view('ppdb.auth.login', compact('yayasan'));
    }

    /**
     * Proses Login
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'login'    => ['required'],
            'password' => ['required'],
        ]);

        $login = trim($request->login);
        $yayasanId = $this->currentYayasanId();

        $query = Ppdb::where('nisn', $login);

        if ($yayasanId) {
            $query->whereHas('lembaga', function ($q) use ($yayasanId) {
                $q->where('yayasan_id', $yayasanId);
            });
        }

        $ppdb = $query->first();

        if (! $ppdb) {
            return back()->with('error', 'NISN tidak ditemukan');
        }

        if (! Hash::check($request->password, $ppdb->password)) {
            return back()->with('error', 'Password salah');
        }

        session([
            'ppdb_id'   => $ppdb->id,
            'ppdb_nama' => $ppdb->nama_lengkap,
        ]);

        return redirect()->route('ppdb.dashboard');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->session()->forget(['ppdb_id', 'ppdb_nama']);
        $request->session()->regenerateToken();

        return redirect()->route('role.login');
    }

    /**
     * Form Register
     */
    public function register()
    {
        $yayasan = $this->currentYayasan();
        $yayasanId = $this->currentYayasanId();

        $lembagasQuery = Lembaga::orderBy('nama');

        if ($yayasanId) {
            $lembagasQuery->where('yayasan_id', $yayasanId);
        }

        $lembagas = $lembagasQuery->get();

        return view('ppdb.auth.register', compact(
            'yayasan',
            'lembagas'
        ));
    }

    /**
     * Proses Registrasi
     */
    public function store(Request $request)
    {
        $yayasanId = $this->currentYayasanId();

        // NISN unik per-yayasan, bukan global lintas seluruh platform.
        $lembagaIdsForUniqueCheck = $yayasanId
            ? Lembaga::where('yayasan_id', $yayasanId)->pluck('id')
            : collect();

        $request->validate([
            'lembaga_id' => [
                'required',
                Rule::exists('lembagas', 'id')->where(function ($query) use ($yayasanId) {
                    if ($yayasanId) {
                        $query->where('yayasan_id', $yayasanId);
                    }
                }),
            ],
            'nama_lengkap' => [
                'required',
                'string',
                'max:255',
            ],
            'nisn' => [
                'required',
                'string',
                'max:20',
                Rule::unique((new Ppdb)->getTable(), 'nisn')
                    ->where(function ($query) use ($yayasanId, $lembagaIdsForUniqueCheck) {
                        if ($yayasanId) {
                            $query->whereIn('lembaga_id', $lembagaIdsForUniqueCheck);
                        }
                    }),
            ],
            'wa_ayah' => [
                'required',
                'string',
                'max:20',
            ],
            'asal_sekolah' => [
                'required',
                'string',
                'max:255',
            ],
        ], [
            'lembaga_id.required' => 'Silakan pilih lembaga tujuan.',
            'lembaga_id.exists'   => 'Lembaga yang dipilih tidak valid.',
            'nisn.unique'         => 'NISN sudah terdaftar. Silakan login jika sudah memiliki akun.',
        ]);

        $ppdb = Ppdb::create([
            'lembaga_id'      => $request->lembaga_id,
            'nama_lengkap'    => $request->nama_lengkap,
            'nisn'            => $request->nisn,
            'wa_ayah'         => $request->wa_ayah,
            'asal_sekolah'    => $request->asal_sekolah,
            'status'          => 'draft',
            'password'        => Hash::make($request->nisn),
            'tahun_ajaran_id' => TahunAjaran::aktif()?->id,
        ]);

        // Otomatis buat tagihan Biaya Pendaftaran PPDB, kalau lembaga/yayasan
        // ini sudah punya Jenis Tagihan yang ditandai sebagai
        // "Biaya Pendaftaran PPDB" (tipe_sistem = pendaftaran_ppdb).
        $jenisTagihanPendaftaran = \App\Models\JenisTagihan::where('tipe_sistem', 'pendaftaran_ppdb')->first();

        if ($jenisTagihanPendaftaran) {
            \App\Models\Tagihan::create([
                'ppdb_id'          => $ppdb->id,
                'jenis_tagihan_id' => $jenisTagihanPendaftaran->id,
                'judul'            => $jenisTagihanPendaftaran->nama,
                'nominal'          => $jenisTagihanPendaftaran->default_nominal,
                'nominal_terbayar' => 0,
                'status'           => 'belum',
                'jatuh_tempo'      => now()->addDays(7),
                'tahun_ajaran_id'  => $ppdb->tahun_ajaran_id,
            ]);
        }

        NotificationService::sendPpdbBaru($ppdb);

        return redirect()
            ->route('ppdb.login')
            ->with(
                'success',
                'Pendaftaran berhasil. Silakan login menggunakan NISN dan password awal berupa NISN.'
            );
    }
}
