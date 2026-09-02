<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\PayrollAdjustment;
use App\Models\PayrollAdjustmentTemplate;
use App\Models\JurnalMengajar;

class PayrollService
{
    /*
    |--------------------------------------------------------------------------
    | GENERATE PAYROLL BULANAN
    |--------------------------------------------------------------------------
    */

    /**
     * @param  ?string  $jenis  'struktural' (jabatan tetap), 'fungsional'
     *                          (jabatan per_jp), atau null (gabungan --
     *                          semua jabatan jadi 1 payroll, perilaku
     *                          lama sebelum payroll bisa dipisah per
     *                          jenis).
     */
    public function generate($bulan, $tahun, ?string $jenis = null)
    {
        /*
        |--------------------------------------------------------------------------
        | AMBIL PEGAWAI AKTIF
        |--------------------------------------------------------------------------
        */

        $pegawais = Pegawai::query()
            ->where('is_active', true)
            ->with('pegawaiLembagas')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | LOOP PEGAWAI
        |--------------------------------------------------------------------------
        */

        foreach ($pegawais as $pegawai) {

            /*
            |--------------------------------------------------------------------------
            | CEK SUDAH ADA PAYROLL?
            |--------------------------------------------------------------------------
            */

            $exists = Payroll::query()
                ->where('pegawai_id', $pegawai->id)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->where('jenis', $jenis)
                ->exists();
            if ($exists) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | SKIP PEGAWAI YANG TIDAK PUNYA JABATAN COCOK BUAT BATCH INI
            |--------------------------------------------------------------------------
            |
            | Kalau digenerate khusus "fungsional", pegawai yang cuma
            | punya jabatan tetap (tidak mengajar sama sekali) tidak
            | usah dibuatkan payroll fungsional kosong Rp 0. Berlaku
            | sebaliknya juga untuk "struktural".
            */

            if ($jenis) {

                $adaJabatanCocok = $pegawai->pegawaiLembagas->contains(function ($j) use ($jenis) {
                    return $jenis === 'struktural'
                        ? $j->metode_penggajian === 'tetap'
                        : $j->metode_penggajian === 'per_jp';
                });

                if (! $adaJabatanCocok) {
                    continue;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | CREATE PAYROLL
            |--------------------------------------------------------------------------
            */

            $payroll = Payroll::create([
                'pegawai_id' => $pegawai->id,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'jenis' => $jenis,
                'subtotal' => 0,
                'bonus' => 0,
                'potongan' => 0,
                'total_gaji' => 0,
                'status' => 'draft',
            ]);

            /*
            |--------------------------------------------------------------------------
            | GENERATE ITEMS
            |--------------------------------------------------------------------------
            */

            $this->generatePayrollItems($payroll);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REGENERATE PAYROLL
    |--------------------------------------------------------------------------
    */

    public function regenerate(Payroll $payroll)
    {
        /*
        |--------------------------------------------------------------------------
        | HANYA STATUS DRAFT
        |--------------------------------------------------------------------------
        */

        if ($payroll->status !== 'draft') {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | HAPUS ITEMS LAMA
        |--------------------------------------------------------------------------
        */

        PayrollItem::query()
            ->where('payroll_id', $payroll->id)
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | RESET TOTAL
        |--------------------------------------------------------------------------
        */

        $payroll->update([

            'subtotal' => 0,
            'total_gaji' => 0,

        ]);

        /*
        |--------------------------------------------------------------------------
        | GENERATE ULANG
        |--------------------------------------------------------------------------
        */

        $this->generatePayrollItems($payroll);
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE PAYROLL ITEMS
    |--------------------------------------------------------------------------
    */

    protected function generatePayrollItems(Payroll $payroll)
    {
        /*
        |--------------------------------------------------------------------------
        | AMBIL PEGAWAI
        |--------------------------------------------------------------------------
        */
        $pegawai = Pegawai::query()
            ->with('pegawaiLembagas')
            ->find($payroll->pegawai_id);
        if (!$pegawai) {
            return;
        }

        $subtotal = 0;

        /*
        |--------------------------------------------------------------------------
        | LOOP JABATAN
        |--------------------------------------------------------------------------
        */

        foreach ($pegawai->pegawaiLembagas as $jabatan) {

            /*
            |--------------------------------------------------------------------------
            | FILTER JENIS PAYROLL (struktural/fungsional)
            |--------------------------------------------------------------------------
            |
            | Payroll bertanda "struktural" cuma proses jabatan tetap;
            | "fungsional" cuma proses jabatan per_jp. Payroll lama
            | (jenis null / "gabungan") proses SEMUA jabatan seperti
            | sebelumnya -- backward compatible, data lama tidak berubah.
            */

            if ($payroll->jenis === 'struktural' && $jabatan->metode_penggajian !== 'tetap') {
                continue;
            }

            if ($payroll->jenis === 'fungsional' && $jabatan->metode_penggajian !== 'per_jp') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | GAJI TETAP
            |--------------------------------------------------------------------------
            */

            if ($jabatan->metode_penggajian === 'tetap') {
                $nominal = $jabatan->nominal_tetap ?? 0;
                if ($nominal <= 0) {
                    continue;
                }
                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'pegawai_lembaga_id' => $jabatan->id,
                    'nama_komponen' =>
                        'Gaji '
                        . $jabatan->jabatan,
                    'jenis' => 'gaji',
                    'qty' => 1,
                    'tarif' => $nominal,
                    'subtotal' => $nominal,
                    'keterangan' => 'Gaji Tetap',
                ]);

                $subtotal += $nominal;
            }

            /*
            |--------------------------------------------------------------------------
            | HONOR PER JP
            |--------------------------------------------------------------------------
            */

            if ($jabatan->metode_penggajian === 'per_jp') {

                /*
                |--------------------------------------------------------------------------
                | TOTAL JP
                |--------------------------------------------------------------------------
                */
                $baseQuery = JurnalMengajar::query()
                ->join(
                    'jadwal_pelajarans',
                    'jadwal_pelajarans.id',
                    '=',
                    'jurnal_mengajars.jadwal_pelajaran_id'
                )
                ->join(
                    'jam_pelajarans',
                    'jam_pelajarans.id',
                    '=',
                    'jadwal_pelajarans.jam_pelajaran_id'
                )
                ->where('jurnal_mengajars.pegawai_lembaga_id', $jabatan->id)
                ->where('jurnal_mengajars.status', 'valid')
                ->whereMonth('jurnal_mengajars.tanggal', $payroll->bulan)
                ->whereYear('jurnal_mengajars.tanggal', $payroll->tahun);

                /*
                |--------------------------------------------------------------------------
                | JP NORMAL (tanpa tarif pengganti manual)
                |--------------------------------------------------------------------------
                */
                $totalJP = (clone $baseQuery)
                    ->whereNull('jurnal_mengajars.pegawai_asli_id')
                    ->sum('jam_pelajarans.durasi_jp');

                if ($totalJP > 0) {
                    $tarif = $jabatan->tarif_per_jp ?? 0;
                    $nominal = $totalJP * $tarif;

                    PayrollItem::create([
                        'payroll_id' => $payroll->id,
                        'pegawai_lembaga_id' => $jabatan->id,
                        'nama_komponen' =>
                            'Honor '
                            . $jabatan->jabatan
                            . ' ('
                            . $totalJP
                            . ' JP)',
                        'jenis' => 'gaji',
                        'qty' => $totalJP,
                        'tarif' => $tarif,
                        'subtotal' => $nominal,
                        'keterangan' => 'Honor Mengajar',
                    ]);
                    $subtotal += $nominal;
                }

                /*
                |--------------------------------------------------------------------------
                | JP PENGGANTI (tarif manual per entri)
                |--------------------------------------------------------------------------
                */
                $jurnalPengganti = (clone $baseQuery)
                    ->whereNotNull('jurnal_mengajars.pegawai_asli_id')
                    ->select(
                        'jurnal_mengajars.id',
                        'jam_pelajarans.durasi_jp',
                        'jurnal_mengajars.tarif_pengganti_per_jp'
                    )
                    ->get();

                foreach ($jurnalPengganti as $jp) {
                    // Kalau tarif pengganti tidak di-set manual oleh admin,
                    // fallback pakai tarif normal guru itu sendiri.
                    $tarifPakai = $jp->tarif_pengganti_per_jp ?? $tarif;
                    $nominalPengganti = $jp->durasi_jp * $tarifPakai;

                    PayrollItem::create([
                        'payroll_id' => $payroll->id,
                        'pegawai_lembaga_id' => $jabatan->id,
                        'nama_komponen' =>
                            'Honor Pengganti '
                            . $jabatan->jabatan
                            . ' ('
                            . $jp->durasi_jp
                            . ' JP)',
                        'jenis' => 'gaji',
                        'qty' => $jp->durasi_jp,
                        'tarif' => $tarifPakai,
                        'subtotal' => $nominalPengganti,
                        'keterangan' => 'Honor Mengajar Pengganti',
                    ]);
                    $subtotal += $nominalPengganti;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | TERAPKAN TUNJANGAN/POTONGAN TETAP (dari template, auto tiap bulan)
        |--------------------------------------------------------------------------
        |
        | Ini yang "cukup sekali setting" (mis. Tunjangan Wali Kelas,
        | Tunjangan Pembina Eskul) — beda dari adjustment dinamis yang
        | diinput manual tiap bulan. firstOrCreate supaya aman dipanggil
        | ulang (mis. saat regenerate) tanpa bikin baris duplikat.
        |
        | HANYA masuk ke payroll struktural (atau payroll lama/gabungan)
        | -- TIDAK ke payroll fungsional, supaya tidak dobel-hitung kalau
        | 1 pegawai punya 2 payroll (struktural + fungsional) di bulan
        | yang sama.
        */

        if ($payroll->jenis !== 'fungsional') {

            $templates = PayrollAdjustmentTemplate::query()
                ->where('pegawai_id', $pegawai->id)
                ->where('is_active', true)
                ->get();

            foreach ($templates as $template) {

                PayrollAdjustment::firstOrCreate(
                    [
                        'payroll_id' => $payroll->id,
                        'source_template_id' => $template->id,
                    ],
                    [
                        'tipe' => $template->tipe,
                        'nama_komponen' => $template->nama_komponen,
                        'qty' => 1,
                        'nominal' => $template->nominal,
                        'subtotal' => $template->nominal,
                        'catatan' => $template->catatan ?: 'Otomatis dari Tunjangan/Potongan Tetap',
                    ]
                );
            }
        }

                /*
        |--------------------------------------------------------------------------
        | HITUNG ADJUSTMENT
        |--------------------------------------------------------------------------
        */
        $tambahan = $payroll->adjustments()
            ->where('tipe', 'tambahan')
            ->sum('subtotal');
        $potongan = $payroll->adjustments()
            ->where('tipe', 'potongan')
            ->sum('subtotal');

        /*
        |--------------------------------------------------------------------------
        | TOTAL GAJI
        |--------------------------------------------------------------------------
        */

        $totalGaji =
            $subtotal
            + $tambahan
            - $potongan;
        /*
        |--------------------------------------------------------------------------
        | UPDATE PAYROLL
        |--------------------------------------------------------------------------
        */

        $payroll->update([
            'subtotal' => $subtotal,
            'bonus' => $tambahan,
            'potongan' => $potongan,
            'total_gaji' => $totalGaji,
        ]);
    }
}
