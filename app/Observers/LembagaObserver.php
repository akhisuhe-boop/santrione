<?php

namespace App\Observers;

use App\Models\Lembaga;
use App\Models\LembagaModule;
use Illuminate\Support\Facades\Log;

/**
 * DITAMBAHKAN -- skema harga baru: modul diaktifkan/nonaktifkan
 * SEKALIGUS untuk semua Lembaga se-Yayasan lewat Checkout (lihat
 * Checkout::toggleModule()). Observer ini menutup satu celah: kalau
 * Yayasan bikin Lembaga BARU setelah beberapa modul sudah aktif,
 * Lembaga baru itu otomatis disamakan (bukan mulai dari nol modul).
 *
 * SENGAJA berupa Observer kecil, BUKAN mengubah tabel/struktur
 * `lembaga_modules` atau 20+ tempat yang cek modul per-Lembaga --
 * supaya semua kode fitur yang sudah ada (Jurnal Mengajar, Laporan
 * Tahfidz, dst) tetap jalan tanpa disentuh sama sekali.
 */
class LembagaObserver
{
    public function created(Lembaga $lembaga): void
    {
        try {
            $lembagaLain = $lembaga->yayasan?->lembagas()
                ->where('id', '!=', $lembaga->id)
                ->with('modules')
                ->first();

            if (! $lembagaLain) {
                return;
            }

            foreach ($lembagaLain->modules as $lm) {
                if (! $lm->is_active) {
                    continue;
                }

                LembagaModule::create([
                    'lembaga_id' => $lembaga->id,
                    'module_price_id' => $lm->module_price_id,
                    'is_active' => true,
                    'aktif_sejak' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('LembagaObserver: gagal sinkronkan modul ke Lembaga baru', [
                'lembaga_id' => $lembaga->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
