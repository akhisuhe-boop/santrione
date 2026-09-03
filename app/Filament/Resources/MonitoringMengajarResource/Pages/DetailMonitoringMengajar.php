<?php

namespace App\Filament\Resources\MonitoringMengajarResource\Pages;

use App\Filament\Resources\MonitoringMengajarResource;
use App\Exports\DetailMonitoringMengajarExport;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;

class DetailMonitoringMengajar extends Page implements HasForms
{
    use InteractsWithForms;
    protected static string $resource =
        MonitoringMengajarResource::class;

    protected static string $view =
        'filament.pages.detail-monitoring-mengajar';

    public $record;

    /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    */

    public $filter = 'minggu';
    public $tanggalMulai;
    public $tanggalSelesai;
    public ?array $data = [];
    public function mount($record)
    {
        $this->record =
            \App\Models\Pegawai::findOrFail($record);

        $this->tanggalMulai =
            now()->startOfWeek()->toDateString();

        $this->tanggalSelesai =
            now()->endOfWeek()->toDateString();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn () => Excel::download(
                    new DetailMonitoringMengajarExport($this->record, $this->tanggalMulai, $this->tanggalSelesai),
                    'detail-mengajar-' . \Illuminate\Support\Str::slug($this->record->nama) . '-' . now()->format('Y-m-d') . '.xlsx'
                )),
        ];
    }

    public function form(Form $form): Form
{
    return $form
        ->schema([

            Forms\Components\Grid::make(4)
                ->schema([

                    Forms\Components\Select::make('filter')

                        ->label('Filter')

                        ->options([

                            'minggu' => 'Minggu Ini',

                            'bulan' => 'Bulan Ini',

                            'semester' => 'Semester Ini',

                            'custom' => 'Custom Range',

                        ])

                        ->live()

                        ->default('minggu')

                        ->afterStateUpdated(function ($state) {

                            $this->filter = $state;

                            $this->updatedFilter();

                        }),

                    Forms\Components\DatePicker::make('tanggalMulai')

                        ->label('Dari Tanggal')

                        ->visible(fn () => $this->filter === 'custom')

                        ->live()

                        ->afterStateUpdated(function ($state) {

                            $this->tanggalMulai = $state;

                        }),

                    Forms\Components\DatePicker::make('tanggalSelesai')

                        ->label('Sampai Tanggal')

                        ->visible(fn () => $this->filter === 'custom')

                        ->live()

                        ->afterStateUpdated(function ($state) {

                            $this->tanggalSelesai = $state;

                        }),

                ])

        ])

        ->statePath('data');
}
    /*
    |--------------------------------------------------------------------------
    | UPDATE FILTER
    |--------------------------------------------------------------------------
    */

    public function updatedFilter()
    {
        if ($this->filter === 'minggu') {

            $this->tanggalMulai =
                now()->startOfWeek()->toDateString();

            $this->tanggalSelesai =
                now()->endOfWeek()->toDateString();

        }

        elseif ($this->filter === 'bulan') {

            $this->tanggalMulai =
                now()->startOfMonth()->toDateString();

            $this->tanggalSelesai =
                now()->endOfMonth()->toDateString();

        }

        elseif ($this->filter === 'semester') {

            if (now()->month <= 6) {

                $this->tanggalMulai =
                    now()->startOfYear()->toDateString();

                $this->tanggalSelesai =
                    now()->month(6)->endOfMonth()->toDateString();

            } else {

                $this->tanggalMulai =
                    now()->month(7)->startOfMonth()->toDateString();

                $this->tanggalSelesai =
                    now()->endOfYear()->toDateString();

            }
        }
    }
}