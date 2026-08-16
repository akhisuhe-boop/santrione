<?php

namespace App\Http\Controllers;

use App\Models\BuktiSosial;
use App\Models\EkosistemSolusi;
use App\Models\FaqItem;
use App\Models\LandingSetting;
use App\Models\MasalahSolusi;
use App\Models\ModulAplikasi;
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
            'masalahSolusi' => MasalahSolusi::active()->get(),
            'ekosistemSolusi' => EkosistemSolusi::active()->get(),
            'modulAplikasi' => ModulAplikasi::active()->get(),
            'mockupScreenshots' => MockupScreenshot::active()->get(),
            'testimonis' => Testimoni::active()->get(),
            'subscriptionPlans' => SubscriptionPlan::where('is_active', true)->orderBy('urutan')->get(),
            'modulePrices' => ModulePrice::aktif()->orderBy('urutan')->get(),
            'faqItems' => FaqItem::active()->get(),
            'studiKasusList' => StudiKasus::active()->get(),
            // Pop-up social proof - nama diisi manual lewat panel, bukan
            // ditarik otomatis dari data Yayasan asli.
            'buktiSosialList' => BuktiSosial::active()->get(),
        ]);
    }
}
