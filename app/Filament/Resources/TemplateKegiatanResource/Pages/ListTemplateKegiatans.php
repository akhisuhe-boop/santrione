<?php

namespace App\Filament\Resources\TemplateKegiatanResource\Pages;

use App\Filament\Resources\TemplateKegiatanResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

use Carbon\Carbon;
use App\Models\TemplateKegiatan;
use App\Models\JadwalKegiatan;

class ListTemplateKegiatans extends ListRecords
{
    protected static string $resource = TemplateKegiatanResource::class;

    protected function getHeaderActions(): array
{
    return [

        Actions\CreateAction::make(),

        Actions\Action::make('lihatJadwal')
        ->label('Lihat Jadwal')
        ->icon('heroicon-o-eye')
        ->url('/admin/' . \Filament\Facades\Filament::getTenant()?->slug . '/jadwal-kegiatans')
        ->color('info'),

        Actions\Action::make('generateJadwal')
            ->label('Generate Jadwal')
            ->icon('heroicon-o-calendar')
            ->color('success')
            ->form([

                Select::make('bulan')
                    ->label('Bulan')
                    ->options([
                        1=>'Januari',
                        2=>'Februari',
                        3=>'Maret',
                        4=>'April',
                        5=>'Mei',
                        6=>'Juni',
                        7=>'Juli',
                        8=>'Agustus',
                        9=>'September',
                        10=>'Oktober',
                        11=>'November',
                        12=>'Desember',
                    ])
                    ->required(),

                TextInput::make('tahun')
                    ->label('Tahun')
                    ->numeric()
                    ->default(now()->year)
                    ->required(),

            ])

            ->action(function ($data) {

    $templates = \App\Models\TemplateKegiatan::where('aktif',1)->get();

    foreach ($templates as $template) {

        // Cek dulu, apakah template ini sudah ada jadwal di bulan & tahun yang sama
        $exists = \App\Models\JadwalKegiatan::where('template_kegiatan_id', $template->id)
                    ->whereYear('tanggal', $data['tahun'])
                    ->whereMonth('tanggal', $data['bulan'])
                    ->exists();

        if ($exists) {
            // Sudah ada, skip generate
            continue;
        }

        // Template belum ada jadwal di bulan ini, generate
        $tanggal = \Carbon\Carbon::create($data['tahun'],$data['bulan'],1)->startOfMonth();

        while ($tanggal->month == $data['bulan']) {

            if (strtolower($tanggal->locale('id')->dayName) == $template->hari) {

                \App\Models\JadwalKegiatan::create([
                    'template_kegiatan_id' => $template->id,
                    'tanggal' => $tanggal->toDateString(),
                    'jam_mulai' => $template->jam_mulai,
                    'jam_selesai' => $template->jam_selesai,
                ]);

            }

            $tanggal->addDay();
        }

    }

})
    ];
}
}
