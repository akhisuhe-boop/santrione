<?php

namespace App\Services;

use App\Data\TeachingSession;
use App\Models\JamPelajaran;
use Illuminate\Support\Collection;

class ConstraintService
{
    /**
     * Jadwal yang sudah ada di database.
     */
    protected Collection $existingSchedule;

    /**
     * Jadwal hasil generate.
     */
    protected Collection $generatedSchedule;

    /**
     * Daftar JP aktif.
     */
    protected Collection $jamIds;

    public function __construct(Collection $existingSchedule)
    {
        $this->existingSchedule = $existingSchedule;

        $this->generatedSchedule = collect();

        $this->jamIds = JamPelajaran::query()
        ->where('aktif', true)
        ->orderBy('urutan')
        ->get()
        ->values();
    }

    /**
     * Apakah session boleh ditempatkan
     * pada hari dan JP tertentu.
     */
    public function canPlace(
        TeachingSession $session,
        string $hari,
        int $jamPelajaranId
    ): bool {

        $index = $this->jamIds->search(
            fn ($item) => $item->id == $jamPelajaranId
        );

        if ($index === false) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Jangan melewati JP terakhir
        |--------------------------------------------------------------------------
        */

        if (
    ($index + $session->durasi)
    > $this->jamIds->count()
) {
    return false;
}

/*
|--------------------------------------------------------------------------
| Semua slot harus lolos
|--------------------------------------------------------------------------
*/

// Aturan keras: mapel yang sama untuk kelas yang sama TIDAK
        // boleh punya lebih dari 1 pertemuan di hari yang sama --
        // walaupun JP/Pertemuan-nya lebih dari 1 (itu tetap 1
        // pertemuan, boleh berurutan). Ini beda dari session yang
        // SEDANG ditempatkan sekarang (durasi-nya sendiri memang
        // boleh berurutan dalam 1 pertemuan itu).
        if (
            $this->isMapelSudahAdaDiHari(
                $session->kelasId,
                $session->mataPelajaranId,
                $hari
            )
        ) {
            return false;
        }

        for ($i = 0; $i < $session->durasi; $i++) {

            $jam = $this->jamIds[$index + $i];

            $jp = $jam->id;

            if (
                $this->isGuruBentrok(
                    $session->pegawaiId,
                    $hari,
                    $jp
                )
            ) {
                return false;
            }

            if (
                $this->isKelasBentrok(
                    $session->kelasId,
                    $hari,
                    $jp
                )
            ) {
                return false;
            }

        }

        return true;
    }

    /**
     * Cek apakah mapel ini (untuk kelas ini) sudah punya
     * pertemuan lain di hari yang sama.
     */
    protected function isMapelSudahAdaDiHari(
        int $kelasId,
        int $mataPelajaranId,
        string $hari
    ): bool {

        $adaDiExisting = $this->existingSchedule
            ->where('kelas_id', $kelasId)
            ->where('mata_pelajaran_id', $mataPelajaranId)
            ->where('hari', $hari)
            ->isNotEmpty();

        $adaDiGenerated = $this->generatedSchedule
            ->where('kelas_id', $kelasId)
            ->where('mata_pelajaran_id', $mataPelajaranId)
            ->where('hari', $hari)
            ->isNotEmpty();

        return $adaDiExisting || $adaDiGenerated;
    }

    /**
     * Tempatkan session ke memory.
     */
    public function place(
    TeachingSession $session,
    string $hari,
    int $jamPelajaranId
    ): void {
    
        $index = $this->jamIds->search(
            fn ($item) => $item->id == $jamPelajaranId
        );
    
        for ($i = 0; $i < $session->durasi; $i++) {
    
            $jam = $this->jamIds[$index + $i];
    
            $this->generatedSchedule->push([
    
                'pegawai_id'        => $session->pegawaiId,
    
                'kelas_id'          => $session->kelasId,
    
                'mata_pelajaran_id' => $session->mataPelajaranId,
    
                'hari'              => $hari,
    
                'jam_pelajaran_id'  => $jam->id,
    
                'jam_ke'            => $jam->urutan,
    
                'durasi_jam'        => 1,
    
            ]);
        }
    }
    
        /**
     * Cek bentrok guru.
     */
    protected function isGuruBentrok(
        int $pegawaiId,
        string $hari,
        int $jp
    ): bool {

        return $this->existsInExisting(
            'pegawai_id',
            $pegawaiId,
            $hari,
            $jp
        ) || $this->existsInGenerated(
            'pegawai_id',
            $pegawaiId,
            $hari,
            $jp
        );
    }

    /**
     * Cek bentrok kelas.
     */
    protected function isKelasBentrok(
        int $kelasId,
        string $hari,
        int $jp
    ): bool {

        return $this->existsInExisting(
            'kelas_id',
            $kelasId,
            $hari,
            $jp
        ) || $this->existsInGenerated(
            'kelas_id',
            $kelasId,
            $hari,
            $jp
        );
    }

    /**
     * Cek pada jadwal existing.
     */
    protected function existsInExisting(
        string $field,
        int $value,
        string $hari,
        int $jp
    ): bool {

        return $this->existingSchedule
            ->where($field, $value)
            ->where('hari', $hari)
            ->where('jam_pelajaran_id', $jp)
            ->isNotEmpty();
    }

    /**
     * Cek pada jadwal hasil generate.
     */
    protected function existsInGenerated(
        string $field,
        int $value,
        string $hari,
        int $jp
    ): bool {

        return $this->generatedSchedule
            ->where($field, $value)
            ->where('hari', $hari)
            ->where('jam_pelajaran_id', $jp)
            ->isNotEmpty();
    }

    /**
     * Ambil seluruh jadwal hasil generate.
     *
     * Yang dikembalikan hanya jadwal baru,
     * karena hanya data ini yang akan diinsert
     * oleh JadwalGeneratorService.
     */
    public function getSchedule(): Collection
    {
        return $this->generatedSchedule;
    }

    /**
     * Ambil seluruh jadwal existing.
     */
    public function getExistingSchedule(): Collection
    {
        return $this->existingSchedule;
    }

    /**
     * Ambil seluruh jadwal (existing + generated).
     */
    public function getAllSchedule(): Collection
    {
        return $this->existingSchedule
            ->concat($this->generatedSchedule)
            ->values();
    }

    /**
     * Daftar JP aktif.
     */
    public function getJamIds(): Collection
    {
        return $this->jamIds;
    }
    
        /**
     * Total jadwal hasil generate.
     */
    public function countGenerated(): int
    {
        return $this->generatedSchedule->count();
    }

    /**
     * Total jadwal existing.
     */
    public function countExisting(): int
    {
        return $this->existingSchedule->count();
    }

    /**
     * Total seluruh jadwal.
     */
    public function countAll(): int
    {
        return $this->countExisting() + $this->countGenerated();
    }

    /**
     * Apakah hasil generate masih kosong?
     */
    public function isEmpty(): bool
    {
        return $this->generatedSchedule->isEmpty();
    }

    /**
     * Reset hasil generate.
     *
     * Jadwal existing tetap dipertahankan.
     */
    public function reset(): void
    {
        $this->generatedSchedule = collect();
    }

    /**
     * Tambahkan jadwal manual ke hasil generate.
     *
     * Berguna jika suatu saat ingin
     * melakukan penempatan manual.
     */
    public function push(array $row): void
    {
        $this->generatedSchedule->push($row);
    }

    /**
     * Statistik Constraint.
     */
    public function statistics(): array
    {
        return [
            'existing'  => $this->countExisting(),
            'generated' => $this->countGenerated(),
            'total'     => $this->countAll(),
            'jam'       => $this->jamIds->count(),
        ];
    }

    /**
     * Debug.
     */
    public function dump(): array
    {
        return [
            'existing' => $this->existingSchedule,
            'generated' => $this->generatedSchedule,
            'jam_ids' => $this->jamIds,
        ];
    }
}