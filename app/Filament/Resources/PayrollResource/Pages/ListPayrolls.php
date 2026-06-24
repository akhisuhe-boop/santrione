<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use Filament\Forms;
use Filament\Actions\Action;
use App\Services\PayrollService;
use Filament\Notifications\Notification;
use App\Filament\Resources\PayrollResource;
use Filament\Resources\Pages\ListRecords;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;
    protected function getHeaderActions(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | GENERATE PAYROLL
            |--------------------------------------------------------------------------
            */
            Action::make('generatePayroll')
                ->label('Generate Payroll')
                ->icon('heroicon-o-cpu-chip')
                ->color('primary')
                ->form([
                    Forms\Components\Select::make('bulan')
                        ->options([
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember',
                        ])

                        ->required(),
                    Forms\Components\TextInput::make('tahun')
                        ->numeric()
                        ->default(date('Y'))
                        ->required(),
                ])

                ->action(function (array $data) {
                    app(PayrollService::class)
                        ->generate(
                            $data['bulan'],
                            $data['tahun']
                        );

                    Notification::make()
                        ->title('Payroll berhasil digenerate')
                        ->success()
                        ->send();
                }),

        ];
    }
}