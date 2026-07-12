<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\JurnalMengajar;

class PayrollService
{
    /*
    |--------------------------------------------------------------------------
    | GENERATE PAYROLL BULANAN
    |--------------------------------------------------------------------------
    */

    public function generate($bulan, $tahun)
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
                ->exists();
            if ($exists) {
                continue;
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
                $totalJP = JurnalMengajar::query()
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
                ->whereMonth('jurnal_mengajars.tanggal', $payroll->bulan)
                ->whereYear('jurnal_mengajars.tanggal', $payroll->tahun)
                ->sum('jam_pelajarans.durasi_jp');
                /*
                |--------------------------------------------------------------------------
                | SKIP JIKA TIDAK ADA JP
                |--------------------------------------------------------------------------
                */
                if ($totalJP <= 0) {
                    continue;
                }
                /*
                |--------------------------------------------------------------------------
                | HITUNG NOMINAL
                |--------------------------------------------------------------------------
                */
                $tarif = $jabatan->tarif_per_jp ?? 0;
                $nominal = $totalJP * $tarif;

                /*
                |--------------------------------------------------------------------------
                | CREATE ITEM
                |--------------------------------------------------------------------------
                */
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