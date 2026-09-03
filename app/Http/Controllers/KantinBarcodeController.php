<?php

namespace App\Http\Controllers;

use App\Models\KantinProduk;
use Illuminate\Http\Request;

class KantinBarcodeController extends Controller
{
    public function index(Request $request)
    {
        $ids = explode(',', $request->query('ids', ''));

        $produks = KantinProduk::with('kantin')
            ->whereIn('id', $ids)
            ->orderBy('nama')
            ->get();

        return view('kantin.cetak-barcode', compact('produks'));
    }
}
