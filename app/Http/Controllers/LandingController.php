<?php

namespace App\Http\Controllers;

use App\Models\FaqItem;
use App\Models\LandingSetting;
use App\Models\ModulePrice;
use App\Models\MockupScreenshot;
use App\Models\StudiKasus;
use App\Models\SubscriptionPlan;
use App\Models\Testimoni;

class LandingController extends Controller
{
    public function index()
    {
        return view('landing.index', [
            'setting' => LandingSetting::current(),
            'testimonis' => Testimoni::active()->get(),
            'subscriptionPlans' => SubscriptionPlan::where('is_active', true)->orderBy('urutan')->get(),
            'modulePrices' => ModulePrice::aktif()->orderBy('urutan')->get(),
            'faqItems' => FaqItem::active()->get(),
            'mockupScreenshots' => MockupScreenshot::active()->get(),
            // Semua manual lewat panel admin -- tidak query live ke data
            // operasional (siswa/pembayaran/dst), sesuai keputusan supaya
            // angka publik selalu dikurasi & disetujui dulu.
            'studiKasusList' => StudiKasus::active()->get(),
        ]);
    }
}
