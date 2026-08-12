<?php

namespace App\Filament\Platform\Pages;

use App\Models\SubscriptionPayment;
use Filament\Pages\Page;

class LaporanPembayaran extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Laporan Pembayaran';
    protected static ?string $navigationGroup = 'Pembayaran';
    protected static ?int $navigationSort = 20;
    protected static ?string $title = 'Laporan Pembayaran Berhasil per Bulan';

    protected static string $view = 'filament.platform.pages.laporan-pembayaran';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public function getLaporan(): array
    {
        $payments = SubscriptionPayment::where('status', 'berhasil')
            ->with('subscription.yayasan')
            ->get()
            ->filter(fn ($p) => $p->subscription?->yayasan);

        return $payments
            ->groupBy(fn ($p) => $p->created_at->format('Y-m'))
            ->map(function ($grupBulan) {
                $perYayasan = $grupBulan
                    ->groupBy(fn ($p) => $p->subscription->yayasan_id)
                    ->map(function ($grupYayasan) {
                        return [
                            'yayasan_nama' => $grupYayasan->first()->subscription->yayasan->nama,
                            'jumlah_transaksi' => $grupYayasan->count(),
                            'total' => $grupYayasan->sum('jumlah'),
                        ];
                    })
                    ->sortByDesc('total')
                    ->values();

                return [
                    'total_bulan' => $perYayasan->sum('total'),
                    'yayasan' => $perYayasan,
                ];
            })
            ->sortKeysDesc()
            ->toArray();
    }

    public function getTotalKeseluruhan(): int
    {
        return (int) collect($this->getLaporan())->sum('total_bulan');
    }
}
