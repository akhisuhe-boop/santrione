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
        // Landing page isinya BISA berubah kapan saja (promo, harga,
        // countdown) -- header ini nyuruh browser & proxy di antaranya
        // JANGAN pernah simpan cache halaman ini, selalu ambil versi
        // terbaru dari server. Tanpa ini, pengunjung yang buka
        // halamannya lagi (lewat tombol back, tab lama, dst) bisa lihat
        // promo/harga yang sudah basi tanpa mereka sadari.
        return response()
            ->view('landing.index', [
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
            ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
