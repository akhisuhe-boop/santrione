<?php

namespace App\Http\Controllers;

use App\Services\TenantBillingCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * DITAMBAHKAN -- endpoint TANPA LOGIN buat kalkulator harga interaktif
 * di landing page. Terima angka mentah (jumlah siswa + modul yang
 * dicentang pengunjung), panggil TenantBillingCalculator::
 * hitungEstimasiPublik() -- FUNGSI YANG SAMA PERSIS dipakai Checkout
 * sungguhan -- supaya angka yang tampil di landing page dijamin sama
 * dengan yang muncul nanti di Checkout, bukan rumus duplikat yang
 * bisa perlahan jadi beda.
 */
class LandingCalculatorController extends Controller
{
    public function estimasi(Request $request, TenantBillingCalculator $calculator): JsonResponse
    {
        $data = $request->validate([
            'siswa' => ['required', 'integer', 'min:0', 'max:1000000'],
            'modul' => ['array'],
            'modul.*' => ['string'],
            'paket_full' => ['boolean'],
        ]);

        $hasil = $calculator->hitungEstimasiPublik(
            totalSiswa: (int) $data['siswa'],
            modulKeys: $data['modul'] ?? [],
            paketFull: (bool) ($data['paket_full'] ?? false),
        );

        return response()->json([
            'total_siswa' => $hasil['total_siswa'],
            'rate_per_siswa' => $hasil['rate_per_siswa'],
            'diskon_volume_persen' => $hasil['diskon_volume_persen'],
            'diskon_paket_full_persen' => $hasil['diskon_paket_full_persen'],
            'total_bulanan' => $hasil['total'],
        ]);
    }
}
