<?php

namespace App\Data;

class TeachingSession
{
    public function __construct(
        public int $kelasId,
        public int $pegawaiId,
        public int $mataPelajaranId,
        public int $durasi,
        public int $pertemuanKe,
        public int $totalPertemuan,
    ) {}
}