<?php
namespace App\Http\Controllers;
use App\Models\BuktiSosial;
use App\Models\EkosistemSolusi;
use App\Models\FaqItem;
use App\Models\LandingSetting;
use App\Models\Lead;
use App\Models\MasalahSolusi;
use App\Models\ModulAplikasi;
use App\Models\ModulePrice;
use App\Models\MockupScreenshot;
use App\Models\StudiKasus;
use App\Models\SubscriptionPlan;
use App\Models\Testimoni;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

    /**
     * Submit form popup "Jadwalkan Demo Gratis" di landing page.
     * Berbeda dari alur lama (langsung buka WhatsApp pengunjung dengan
     * pesan template dan BERHARAP mereka benar-benar menekan kirim --
     * kalau tab WA ditutup begitu saja, data pengunjung itu hilang total,
     * tidak pernah tercatat di mana pun) -- sekarang datanya PASTI
     * tersimpan sebagai Lead (masuk ke Platform panel > CRM > Leads)
     * begitu form disubmit, lalu admin dinotifikasi via WA secara
     * otomatis oleh sistem (bukan pengunjung yang harus mengirim WA
     * sendiri).
     */
    public function demoRequest(Request $request)
    {
        $data = $request->validate([
            'nama_pic' => ['required', 'string', 'max:150'],
            'no_hp' => ['required', 'string', 'max:30'],
            'nama_lembaga' => ['nullable', 'string', 'max:150'],
        ], [
            'nama_pic.required' => 'Nama wajib diisi.',
            'no_hp.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        $lead = Lead::create([
            'nama_lembaga' => $data['nama_lembaga'] ?: '-',
            'nama_pic' => $data['nama_pic'],
            'no_hp' => $data['no_hp'],
            'sumber' => 'Demo Gratis (Landing Page)',
            'status' => 'baru',
        ]);

        try {
            NotificationService::sendLeadBaruInternal($lead);
        } catch (\Throwable $e) {
            // Gagal kirim notif WA internal TIDAK boleh menggagalkan
            // penyimpanan lead yang sudah sukses -- cukup dicatat ke log,
            // lead tetap kelihatan di panel CRM walau notifnya gagal.
            Log::error("LandingController::demoRequest: gagal kirim notifikasi lead baru internal untuk lead {$lead->id}: {$e->getMessage()}");
        }

        return response()->json([
            'message' => 'Terima kasih! Tim kami akan segera menghubungi Anda untuk menjadwalkan demo.',
        ]);
    }
}
