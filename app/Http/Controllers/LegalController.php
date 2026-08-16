<?php

namespace App\Http\Controllers;

use App\Models\LandingSetting;

class LegalController extends Controller
{
    public function privasi()
    {
        return view('legal.privasi', [
            'setting' => LandingSetting::current(),
        ]);
    }

    public function syaratKetentuan()
    {
        return view('legal.syarat-ketentuan', [
            'setting' => LandingSetting::current(),
        ]);
    }
}
