<?php

namespace App\Services;

use App\Models\Kurikulum;
use App\Models\JadwalPelajaran;

class CurriculumService
{
    /*
    |--------------------------------------------------------------------------
    | Kurikulum
    |--------------------------------------------------------------------------
    */

    public function getCurriculum(
        int $kelasId,
        int $mapelId
    ): ?Kurikulum {

        return Kurikulum::query()
            ->where('kelas_id', $kelasId)
            ->where('mata_pelajaran_id', $mapelId)
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Target JP
    |--------------------------------------------------------------------------
    */

    public function getTargetJP(
        int $kelasId,
        int $mapelId
    ): int {

        return (int) optional(

            $this->getCurriculum(
                $kelasId,
                $mapelId
            )

        )->jumlah_jam_per_minggu;
    }

    /*
    |--------------------------------------------------------------------------
    | Guru Kurikulum
    |--------------------------------------------------------------------------
    */

    public function getTeacherId(
        int $kelasId,
        int $mapelId
    ): ?int {

        return optional(

            $this->getCurriculum(
                $kelasId,
                $mapelId
            )

        )->pegawai_id;
    }

    /*
    |--------------------------------------------------------------------------
    | JP Terjadwal
    |--------------------------------------------------------------------------
    */

    public function getScheduledJP(
        int $kelasId,
        int $mapelId,
        ?int $ignoreScheduleId = null
    ): int {

        $query = JadwalPelajaran::query()

            ->where('kelas_id', $kelasId)

            ->where('mata_pelajaran_id', $mapelId);

        if ($ignoreScheduleId) {

            $query->whereKeyNot($ignoreScheduleId);

        }

        return $query->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Sisa JP
    |--------------------------------------------------------------------------
    */

    public function getRemainingJP(
        int $kelasId,
        int $mapelId,
        ?int $ignoreScheduleId = null
    ): int {

        return

            $this->getTargetJP(
                $kelasId,
                $mapelId
            )

            -

            $this->getScheduledJP(
                $kelasId,
                $mapelId,
                $ignoreScheduleId
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Progress
    |--------------------------------------------------------------------------
    */

    public function getProgress(
        int $kelasId,
        int $mapelId
    ): array {

        $target = $this->getTargetJP(
            $kelasId,
            $mapelId
        );

        $scheduled = $this->getScheduledJP(
            $kelasId,
            $mapelId
        );

        $remaining = $target - $scheduled;

        return [

            'target' => $target,

            'scheduled' => $scheduled,

            'remaining' => $remaining,

            'percentage' =>

                $target > 0

                    ? round(
                        ($scheduled / $target) * 100
                    )

                    : 0,

            'status' =>

                $scheduled == $target
                    ? 'success'

                    : (

                        $scheduled > $target

                            ? 'danger'

                            : 'warning'
                    ),

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Validasi Guru
    |--------------------------------------------------------------------------
    */

    public function validateTeacher(
        int $kelasId,
        int $mapelId,
        int $pegawaiId
    ): array {

        $teacherId = $this->getTeacherId(
            $kelasId,
            $mapelId
        );

        if (!$teacherId) {

            return [

                'valid' => false,

                'message' => 'Kurikulum belum dibuat.',

            ];
        }

        if ($teacherId != $pegawaiId) {

            return [

                'valid' => false,

                'message' => 'Guru tidak sesuai dengan kurikulum.',

            ];
        }

        return [

            'valid' => true,

            'message' => null,

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Validasi JP
    |--------------------------------------------------------------------------
    */

    public function validateJP(
        int $kelasId,
        int $mapelId,
        ?int $ignoreScheduleId = null
    ): array {

        $remaining = $this->getRemainingJP(
            $kelasId,
            $mapelId,
            $ignoreScheduleId
        );

        if ($remaining <= 0) {

            return [

                'valid' => false,

                'message' => 'JP mata pelajaran sudah memenuhi kurikulum.',

            ];
        }

        return [

            'valid' => true,

            'message' => null,

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Validasi Create
    |--------------------------------------------------------------------------
    */

    public function validateCreate(
        int $kelasId,
        int $mapelId,
        int $pegawaiId
    ): array {

        $teacher = $this->validateTeacher(
            $kelasId,
            $mapelId,
            $pegawaiId
        );

        if (!$teacher['valid']) {

            return $teacher;

        }

        return $this->validateJP(
            $kelasId,
            $mapelId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Validasi Update
    |--------------------------------------------------------------------------
    */

    public function validateUpdate(
    JadwalPelajaran $jadwal,
    array $data
    ): array {
    
        $teacher = $this->validateTeacher(
            $data['kelas_id'],
            $data['mata_pelajaran_id'],
            $data['pegawai_id']
        );
    
        if (! $teacher['valid']) {
            return $teacher;
        }
    
        return $this->validateJP(
            $data['kelas_id'],
            $data['mata_pelajaran_id'],
            $jadwal->id
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function validateDelete(): array
    {
        return [

            'valid' => true,

            'message' => null,

        ];
    }
}