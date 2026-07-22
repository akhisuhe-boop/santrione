<?php

namespace App\Services;

use App\Data\TeachingSession;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class JadwalGeneratorService
{
    /**
     * Hari aktif sekolah.
     */
    protected array $hari = [
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu',
    ];

    /**
     * Session Builder.
     */
    protected SessionBuilder $builder;

    /**
     * Constraint Checker.
     */
    protected ConstraintService $constraint;
    
    /**
     * Hari terakhir setiap mapel pada setiap kelas.
     *
     * key = kelasId-mapelId
     */
    protected array $lastPlacedDay = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->builder = new SessionBuilder();
    }

    /**
     * Generate Jadwal.
     *
     * null = seluruh kelas
     * id   = hanya satu kelas
     */
    public function generate(?int $kelasId = null): bool
    {
        DB::transaction(function () use ($kelasId) {

            /*
            |--------------------------------------------------------------------------
            | Ambil jadwal existing
            |--------------------------------------------------------------------------
            */

            $existingSchedule = JadwalPelajaran::query()
                ->select([
                    'pegawai_id',
                    'kelas_id',
                    'mata_pelajaran_id',
                    'hari',
                    'jam_pelajaran_id',
                ])
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Generate ulang satu kelas
            |--------------------------------------------------------------------------
            */

            if ($kelasId) {

                JadwalPelajaran::where('kelas_id', $kelasId)->delete();

                $existingSchedule = $existingSchedule
                    ->reject(fn ($row) => $row->kelas_id == $kelasId)
                    ->values();
            }

            /*
            |--------------------------------------------------------------------------
            | Generate seluruh kelas
            |--------------------------------------------------------------------------
            */

            if (!$kelasId) {

                JadwalPelajaran::query()->delete();

                $existingSchedule = collect();
            }

            /*
            |--------------------------------------------------------------------------
            | Constraint
            |--------------------------------------------------------------------------
            */

            $this->constraint = new ConstraintService(
                $existingSchedule
            );

            /*
            |--------------------------------------------------------------------------
            | Ambil daftar kelas
            |--------------------------------------------------------------------------
            */

            $kelasList = Kelas::query()
                ->when(
                    $kelasId,
                    fn ($q) => $q->whereKey($kelasId)
                )
                ->orderBy('id')
                ->get();

            if ($kelasList->isEmpty()) {

                throw new RuntimeException(
                    'Data kelas tidak ditemukan.'
                );

            }

            /*
            |--------------------------------------------------------------------------
            | Generate seluruh kelas
            |--------------------------------------------------------------------------
            */

            foreach ($kelasList as $kelas) {

                $this->generateClass(
                    $kelas->id
                );
            
            }
            
            $this->validateResult();
            
            try {
            
                $this->save();
            
            } finally {
            
                $this->constraint->reset();
            
            }
            
            return true;
        });

        return true;
    }

    /**
     * Generate satu kelas.
     */
    protected function generateClass(
        int $kelasId
    ): void {

        $sessions = $this->builder
            ->build($kelasId);

        if ($sessions->isEmpty()) {

            return;

        }

        foreach ($sessions as $session) {

            $this->placeSession(
                $session
            );

        }
    }

    protected function placeSession(
    TeachingSession $session
    ): void {
    
        $key = $session->kelasId . '-' . $session->mataPelajaranId;
    
        $days = $this->hari;
    
        if (isset($this->lastPlacedDay[$key])) {
    
            $lastIndex = array_search(
                $this->lastPlacedDay[$key],
                $days,
                true
            );
    
            if ($lastIndex !== false) {
    
                $days = array_merge(
                    array_slice($days, $lastIndex + 1),
                    array_slice($days, 0, $lastIndex + 1)
                );
            }
        }
    
        $jamIds = $this->constraint
            ->getJamIds()
            ->values();
    
        $offset = $session->pertemuanKe % max(
            1,
            $jamIds->count()
        );
    
        $jamIds = $jamIds
            ->slice($offset)
            ->concat($jamIds->take($offset))
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Hindari 2 pertemuan mapel yang sama, di kelas yang sama,
        | jatuh di hari yang sama dalam 1 minggu.
        |--------------------------------------------------------------------------
        |
        | Tiap TeachingSession sudah membawa jam_pelajaran sebanyak
        | jp_per_pertemuan (mis. 2 JP berurutan = 1 kali pertemuan).
        | Yang TIDAK boleh terjadi adalah pertemuan lain dari mapel
        | yang sama muncul lagi di hari yang sudah dipakai mapel itu
        | minggu ini. Kalau semua hari sudah "terpakai" (kasus kelas
        | dengan sangat sedikit hari aktif dibanding jumlah pertemuan),
        | baru fallback membolehkan hari yang sama supaya generate
        | tidak gagal total.
        */

        $usedDays = $this->constraint
            ->getSchedule()
            ->where('kelas_id', $session->kelasId)
            ->where('mata_pelajaran_id', $session->mataPelajaranId)
            ->pluck('hari')
            ->unique()
            ->all();

        $preferredDays = array_values(
            array_diff($days, $usedDays)
        );

        $dayAttempts = ! empty($preferredDays)
            ? [$preferredDays, $days]
            : [$days];

        $candidates = [];

        foreach ($dayAttempts as $attemptDays) {

            $candidates = $this->collectCandidates(
                $session,
                $attemptDays,
                $jamIds
            );

            if (! empty($candidates)) {
                break;
            }
        }
    
        if (empty($candidates)) {
    
            throw new RuntimeException(
                sprintf(
                    'Tidak ditemukan slot untuk Mapel %d (Pertemuan %d/%d).',
                    $session->mataPelajaranId,
                    $session->pertemuanKe,
                    $session->totalPertemuan
                )
            );
        }
    
        usort(
            $candidates,
            fn ($a, $b) => $b['score'] <=> $a['score']
        );
    
        $best = $candidates[0];
    
        $this->constraint->place(
            $session,
            $best['hari'],
            $best['jp']
        );
    
        $this->lastPlacedDay[$key] = $best['hari'];
    }

    /**
     * Kumpulkan semua kandidat (hari, jam, score) yang valid untuk
     * sebuah session, dibatasi ke daftar $days yang diberikan.
     *
     * Dipisah dari placeSession() supaya bisa dipanggil 2x: pass
     * pertama coba hari yang belum dipakai mapel ini, pass kedua
     * (fallback) coba semua hari kalau pass pertama nihil.
     */
    protected function collectCandidates(
        TeachingSession $session,
        array $days,
        Collection $jamIds
    ): array {

        $candidates = [];

        foreach ($days as $hari) {

            foreach ($jamIds as $jam) {

                if (! $this->constraint->canPlace(
                    $session,
                    $hari,
                    $jam->id
                )) {
                    continue;
                }

                $score = 100;

                $dayCount = $this->constraint
                    ->getSchedule()
                    ->where('kelas_id', $session->kelasId)
                    ->where('hari', $hari)
                    ->count();

                $score -= ($dayCount * 5);

                $jpIndex = $jamIds->search(
                    fn ($item) => $item->id == $jam->id
                );

                if ($jpIndex !== false) {

                    $middle = floor($jamIds->count() / 2);

                    $score -= abs($middle - $jpIndex);
                }

                $sameTeacher = $this->constraint
                    ->getSchedule()
                    ->where('pegawai_id', $session->pegawaiId)
                    ->where('hari', $hari)
                    ->count();

                $score -= ($sameTeacher * 8);

                $sameMapel = $this->constraint
                    ->getSchedule()
                    ->where('kelas_id', $session->kelasId)
                    ->where('mata_pelajaran_id', $session->mataPelajaranId)
                    ->where('hari', $hari)
                    ->count();

                $score -= ($sameMapel * 100);

                $candidates[] = [
                    'hari'  => $hari,
                    'jp'    => $jam->id,
                    'score' => $score,
                ];
            }
        }

        return $candidates;
    }
    
    /**
     * Simpan seluruh hasil generate ke database.
     */
    protected function save(): void
    {
        $rows = $this->constraint
            ->getSchedule()
            ->map(function ($row) {
    
                if ($row instanceof JadwalPelajaran) {
    
                    return [
                        'pegawai_id'        => $row->pegawai_id,
                        'kelas_id'          => $row->kelas_id,
                        'mata_pelajaran_id' => $row->mata_pelajaran_id,
                        'hari'              => $row->hari,
                        'jam_pelajaran_id'  => $row->jam_pelajaran_id,
                        'jam_ke'            => $row->jam_ke,
                        'durasi_jam'        => $row->durasi_jam,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }
    
                return [
                    'pegawai_id'        => $row['pegawai_id'],
                    'kelas_id'          => $row['kelas_id'],
                    'mata_pelajaran_id' => $row['mata_pelajaran_id'],
                    'hari'              => $row['hari'],
                    'jam_pelajaran_id'  => $row['jam_pelajaran_id'],
                    'jam_ke'            => $row['jam_ke'],
                    'durasi_jam'        => $row['durasi_jam'] ?? 1,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            })
            ->values()
            ->all();
    
        if (empty($rows)) {
            return;
        }
    
        JadwalPelajaran::insert($rows);
    }

    /**
     * Generate hanya satu kelas.
     */
    public function generateKelas(int $kelasId): bool
    {
        return $this->generate($kelasId);
    }

    /**
     * Generate seluruh kelas.
     */
    public function generateSemua(): bool
    {
        return $this->generate();
    }

    /**
     * Hapus jadwal satu kelas.
     */
    public function clearKelas(int $kelasId): int
    {
        return JadwalPelajaran::where(
            'kelas_id',
            $kelasId
        )->delete();
    }

    /**
     * Hapus seluruh jadwal.
     */
    public function clearAll(): void
    {
        JadwalPelajaran::query()->delete();
    }

    /**
     * Ambil daftar hari aktif.
     */
    public function getHari(): array
    {
        return $this->hari;
    }

    /**
     * Hitung total jadwal yang berhasil dibuat.
     */
    public function totalGenerated(): int
    {
        return $this->constraint->countGenerated();
    }

    /**
     * Ambil hasil generate yang masih berada di memory.
     */
    public function getGeneratedSchedule(): Collection
    {
        return $this->constraint->getSchedule();
    }
    
        /**
     * Validasi apakah semua session berhasil ditempatkan.
     */
    protected function validateResult(): void
    {
        if ($this->constraint->getSchedule()->isEmpty()) {
            throw new RuntimeException(
                'Generate jadwal gagal. Tidak ada jadwal yang berhasil dibuat.'
            );
        }
    }

    /**
     * Generate dan kembalikan Collection
     * tanpa menyimpan ke database.
     */
    public function preview(?int $kelasId = null): Collection
    {
        DB::transaction(function () use ($kelasId) {

            $existingSchedule = collect();

            $this->constraint = new ConstraintService(
                $existingSchedule
            );

            $kelasList = Kelas::query()
                ->when(
                    $kelasId,
                    fn ($q) => $q->whereKey($kelasId)
                )
                ->orderBy('id')
                ->get();

            foreach ($kelasList as $kelas) {

                $this->generateClass(
                    $kelas->id
                );

            }

            $this->validateResult();
        });

        $result = $this->constraint->getSchedule();

        $this->constraint->reset();
        
        return $result;
    }

    /**
     * Regenerate jadwal satu kelas.
     */
    public function regenerateKelas(
        int $kelasId
    ): bool {

        $this->clearKelas($kelasId);

        return $this->generate($kelasId);
    }

    /**
     * Regenerate seluruh jadwal.
     */
    public function regenerateSemua(): bool
    {
        $this->clearAll();

        return $this->generate();
    }

    /**
     * Informasi hasil generate.
     */
    public function statistics(): array
    {
        return $this->constraint->statistics();
    }

    /**
     * Debug helper.
     */
    public function dump(): array
    {
        return [
            'hari' => $this->hari,
            'constraint' => $this->constraint->dump(),
       ];
    }
}