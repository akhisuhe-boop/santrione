<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JadwalPelajaran;
use App\Models\JurnalMengajar;
use App\Models\Pegawai;
use App\Models\Siswa;
use App\Models\AbsensiMapel;
use Carbon\Carbon;

class GuruJurnalController extends Controller
{
    public function index()
    {
        $guru = Pegawai::findOrFail(session('guru_id'));

        $hari = now()->locale('id')->translatedFormat('l');

        $jadwalHariIni = JadwalPelajaran::with([
        'kelas',
        'mataPelajaran',
        'jamPelajaran',
    ])
    ->where('pegawai_id', $guru->id)
    ->where('hari', $hari)
    ->orderBy('jam_pelajaran_id')
    ->get();

    if ($jadwalHariIni->isEmpty()) {
    
        return redirect()
            ->route('guru.dashboard')
            ->with('warning', 'Hari ini Anda tidak memiliki jadwal mengajar.');
    
    }
    
    $sekarang = Carbon::now()->format('H:i');
    
    $jadwal = $jadwalHariIni->first(function ($item) use ($sekarang) {
    
        return $item->jamPelajaran
            && $sekarang >= $item->jamPelajaran->jam_mulai->format('H:i')
            && $sekarang <= $item->jamPelajaran->jam_selesai->format('H:i');
    
    });
    
    if (!$jadwal) {
    
        $jamPertama = $jadwalHariIni->first()->jamPelajaran->jam_mulai->format('H:i');
    
        $jamTerakhir = $jadwalHariIni->last()->jamPelajaran->jam_selesai->format('H:i');
    
        if ($sekarang < $jamPertama) {
    
            return redirect()
                ->route('guru.dashboard')
                ->with(
                    'warning',
                    "Jurnal belum dapat diisi. Jam mengajar dimulai pukul {$jamPertama}."
                );
    
        }
    
        return redirect()
            ->route('guru.dashboard')
            ->with(
                'warning',
                "Jam mengajar telah berakhir. Jurnal hanya dapat diisi pada jam mengajar ({$jamPertama} - {$jamTerakhir})."
            );
    
    }

        $jurnal = JurnalMengajar::firstOrCreate(

    [
        'jadwal_pelajaran_id' => $jadwal->id,
        'jam_pelajaran_id'    => $jadwal->jam_pelajaran_id,
        'tanggal'             => today(),
    ],

    [
        'pegawai_id'          => $guru->id,
        'pegawai_lembaga_id'  => optional($guru->pegawaiLembagas()->first())->id,
        'kelas_id'            => $jadwal->kelas_id,
        'mata_pelajaran_id'   => $jadwal->mata_pelajaran_id,
        'jam_pelajaran_id'    => $jadwal->jam_pelajaran_id,
        'materi'              => '',
        'status'              => 'draft',
    ]

    );
    
    /*
    |--------------------------------------------------------------------------
    | BUAT ABSENSI SISWA OTOMATIS
    |--------------------------------------------------------------------------
    */
    
    $siswas = Siswa::where('kelas_id', $jadwal->kelas_id)
        ->orderBy('nama_lengkap')
        ->get();
    
    foreach ($siswas as $siswa) {
    
        AbsensiMapel::firstOrCreate(
    
            [
                'jadwal_pelajaran_id' => $jadwal->id,
                'siswa_id'            => $siswa->id,
                'tanggal'             => today(),
            ],
    
            [
                'jurnal_mengajar_id'  => $jurnal->id,
                'status'              => 'Hadir',
            ]
    
        );
    
    }
    
    /*
    |--------------------------------------------------------------------------
    | AMBIL ABSENSI UNTUK DITAMPILKAN
    |--------------------------------------------------------------------------
    */
    
    $absensis = AbsensiMapel::with('siswa')
        ->where('jadwal_pelajaran_id', $jadwal->id)
        ->whereDate('tanggal', today())
        ->orderBy('siswa_id')
        ->get();
    
    return view('guru.jurnal', [
    
        'guru'      => $guru,
        'jadwal'    => $jadwal,
        'jurnal'    => $jurnal,
        'absensis'  => $absensis,
    
    ]);
    }
    
    public function pengganti()
    {
        $yayasan = \App\Models\Yayasan::find(session('active_public_yayasan_id'));

        if (! $yayasan?->hasFeature(\App\Support\FeatureGate::AKADEMIK)) {
            return redirect()
                ->route('guru.dashboard')
                ->with('warning', 'Fitur Guru Pengganti belum aktif untuk yayasan ini. Hubungi admin sekolah untuk upgrade paket.');
        }

        $guru = Pegawai::findOrFail(session('guru_id'));
        $hari = now()->locale('id')->translatedFormat('l');

        $jadwalList = JadwalPelajaran::with(['kelas', 'mataPelajaran', 'jamPelajaran', 'guru'])
            ->where('hari', $hari)
            ->where('pegawai_id', '!=', $guru->id)
            ->get()
            ->filter(function ($j) {
                return !JurnalMengajar::where('jadwal_pelajaran_id', $j->id)
                    ->whereDate('tanggal', today())
                    ->exists();
            })
            ->sortBy(fn ($item) => $item->jamPelajaran?->urutan ?? 999)
            ->values();

        return view('guru.jurnal-pengganti', [
            'guru' => $guru,
            'jadwalList' => $jadwalList,
        ]);
    }

    public function isiPengganti(Request $request)
    {
        $yayasan = \App\Models\Yayasan::find(session('active_public_yayasan_id'));

        abort_unless(
            $yayasan?->hasFeature(\App\Support\FeatureGate::AKADEMIK),
            403,
            'Fitur Guru Pengganti belum aktif untuk yayasan ini.'
        );

        $request->validate(['jadwal_id' => ['required']]);

        $guru = Pegawai::findOrFail(session('guru_id'));
        $jadwal = JadwalPelajaran::findOrFail($request->jadwal_id);

        // Tarif honor pengganti diambil dari menu Keuangan > Honor Guru
        // Pengganti (per lembaga), bukan diisi manual di sini. Nilai
        // ini disimpan sebagai snapshot saat jurnal dibuat, supaya
        // kalau tarifnya diganti nanti, perhitungan gaji bulan-bulan
        // sebelumnya tidak ikut berubah.
        $lembaga = $jadwal->kelas?->lembaga;

        $jurnal = JurnalMengajar::firstOrCreate(
            [
                'jadwal_pelajaran_id' => $jadwal->id,
                'jam_pelajaran_id'    => $jadwal->jam_pelajaran_id,
                'tanggal'             => today(),
            ],
            [
                'pegawai_id'          => $guru->id,
                'pegawai_asli_id'     => $jadwal->pegawai_id,
                'pegawai_lembaga_id'  => optional($guru->pegawaiLembagas()->first())->id,
                'kelas_id'            => $jadwal->kelas_id,
                'mata_pelajaran_id'   => $jadwal->mata_pelajaran_id,
                'jam_pelajaran_id'    => $jadwal->jam_pelajaran_id,
                'tarif_pengganti_per_jp' => $lembaga?->tarif_pengganti_per_jp,
                'materi'              => '',
                'status'              => 'draft',
            ]
        );

        $siswas = Siswa::where('kelas_id', $jadwal->kelas_id)
            ->orderBy('nama_lengkap')
            ->get();

        foreach ($siswas as $siswa) {
            AbsensiMapel::firstOrCreate(
                [
                    'jadwal_pelajaran_id' => $jadwal->id,
                    'siswa_id'            => $siswa->id,
                    'tanggal'             => today(),
                ],
                [
                    'jurnal_mengajar_id'  => $jurnal->id,
                    'status'              => 'Hadir',
                ]
            );
        }

        $absensis = AbsensiMapel::with('siswa')
            ->where('jadwal_pelajaran_id', $jadwal->id)
            ->whereDate('tanggal', today())
            ->orderBy('siswa_id')
            ->get();

        return view('guru.jurnal', [
            'guru'      => $guru,
            'jadwal'    => $jadwal,
            'jurnal'    => $jurnal,
            'absensis'  => $absensis,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'jurnal_id' => ['required'],
            'materi' => ['required'],
        ]);
    
        $jurnal = JurnalMengajar::findOrFail($request->jurnal_id);

        // Jurnal yang sudah pernah disimpan (materi terisi) atau sudah
        // divalidasi admin tidak boleh ditimpa lagi lewat form ini --
        // cegah submit ganda yang bikin ambigu di sisi guru.
        if ($jurnal->status === 'valid' || filled($jurnal->materi)) {
            return redirect()
                ->route('guru.dashboard')
                ->with('warning', 'Jurnal ini sudah tersimpan dan tidak bisa diubah lagi.');
        }
    
        // Guru cuma mengisi materi. Status jurnal tetap 'draft' sampai
        // divalidasi admin (lihat aksi "Validasi" di JurnalMengajarResource)
        // — guru tidak boleh memvalidasi jurnalnya sendiri, karena gaji
        // per JP hanya dihitung dari jurnal yang statusnya 'valid'.
        $jurnal->update([
            'materi' => $request->materi,
        ]);

        // Simpan absensi siswa yang dipilih guru -- SEBELUM ini,
        // pilihan absensi terkirim di form tapi tidak pernah dibaca
        // sama sekali di sini, jadi tidak pernah tersimpan.
        foreach ($request->input('absensi', []) as $absensiMapelId => $status) {

            if (! in_array($status, ['Hadir', 'Sakit', 'Izin', 'Alpha'], true)) {
                continue;
            }

            AbsensiMapel::where('id', $absensiMapelId)
                ->where('jurnal_mengajar_id', $jurnal->id)
                ->update(['status' => $status]);
        }
    
        return redirect()
            ->route('guru.dashboard')
            ->with('success', 'Jurnal berhasil disimpan.');
    }
}