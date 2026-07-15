<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JadwalPelajaranResource\Pages;
use App\Models\JadwalPelajaran;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;

class JadwalPelajaranResource extends BaseResource
{
    protected static ?string $model = JadwalPelajaran::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Akademik';

    protected static ?string $navigationLabel = 'Jadwal Pelajaran';

    protected static ?string $modelLabel = 'Jadwal Pelajaran';

    protected static ?string $pluralModelLabel = 'Jadwal Pelajaran';

    protected static ?int $navigationSort = 4;

    /**
     * Resource ini menggunakan Custom Page,
     * sehingga Form Filament tidak digunakan.
     */
    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    /**
     * Resource ini menggunakan Custom Grid,
     * sehingga Table Filament tidak digunakan.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [

            'index' => Pages\ListJadwalPelajarans::route('/'),

        ];
    }

    /**
     * Semua operasi Create/Edit/Delete
     * dilakukan melalui Custom Grid.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}