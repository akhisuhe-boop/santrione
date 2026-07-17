<?php

namespace App\Filament\Widgets;

use App\Models\Lembaga;
use Filament\Widgets\Widget;

class SiswaPerLembaga extends Widget
{
    protected static ?int $sort = 1;

    protected static string $view =
        'filament.widgets.siswa-per-lembaga';

    protected int|string|array $columnSpan = 'full';

    /*
    |--------------------------------------------------------------------------
    | VISIBILITY
    |--------------------------------------------------------------------------
    */

    public static function canView(): bool
    {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */

    public function getLembagaData()
    {
        return Lembaga::withCount([
                'siswas' => fn ($q) => $q->where('status_siswa', 'Aktif'),
            ])
            ->orderBy('nama', 'asc')
            ->get();
    }
}