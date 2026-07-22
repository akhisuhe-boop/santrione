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
        $request->validate(['jadwal_id' => ['required']]);

        $guru = Pegawai::findOrFail(session('guru_id'));
        $jadwal = JadwalPelajaran::findOrFail($request->jadwal_id);

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
            'status' => ['required'],
        ]);
    
        $jurnal = JurnalMengajar::findOrFail($request->jurnal_id);
    
        $jurnal->update([
            'materi' => $request->materi,
            'status' => $request->status,
        ]);
    
        return redirect()
            ->route('guru.dashboard')
            ->with('success', 'Jurnal berhasil disimpan.');
    }
}