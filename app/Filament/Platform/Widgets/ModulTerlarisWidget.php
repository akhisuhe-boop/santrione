<?php

namespace App\Filament\Platform\Widgets;

use App\Models\ModulePrice;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ModulTerlarisWidget extends BaseWidget
{
    protected static ?string $heading = 'Modul Terlaris';

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ModulePrice::query()
                    ->withCount(['lembagaModules' => fn ($q) => $q->where('is_active', true)])
                    ->orderByDesc('lembaga_modules_count')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Modul'),

                Tables\Columns\TextColumn::make('lembaga_modules_count')
                    ->label('Lembaga Pakai')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('dibebankan_ke')
                    ->label('Dibebankan ke')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'wali_murid' ? 'Wali Murid' : 'Sekolah')
                    ->color(fn ($state) => $state === 'wali_murid' ? 'warning' : 'gray'),
            ])
            ->paginated(false);
    }
}
