<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;
use App\Filament\Platform\Resources\EkosistemSolusiResource\Pages;
use App\Models\EkosistemSolusi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class EkosistemSolusiResource extends BaseResource
{
    protected static ?string $model = EkosistemSolusi::class;
    protected static ?string $navigationGroup = 'Landing Page';
    protected static ?string $navigationLabel = 'Solusi Ekosistem';
    protected static ?string $modelLabel = 'Solusi Ekosistem';
    protected static ?string $pluralModelLabel = 'Solusi Ekosistem';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    // Konten landing page adalah keputusan level platform, bukan per yayasan.
    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canEdit($record = null): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canDelete($record = null): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Kartu Solusi (per Peran)')
                ->description('Contoh: Solusi Pimpinan, Solusi Bendahara, Solusi Akademik & Guru, Solusi Wali Siswa.')
                ->schema([
                    Forms\Components\TextInput::make('judul')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Solusi Pimpinan'),
                    Forms\Components\TextInput::make('icon')
                        ->label('Nama Icon (lucide.dev)')
                        ->required()
                        ->default('sparkles')
                        ->helperText('Cari nama icon di lucide.dev, contoh: bar-chart-3, banknote, book-open, smartphone.'),
                    Forms\Components\Textarea::make('deskripsi')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('tag_text')
                        ->label('Teks Tag (bawah kartu)')
                        ->maxLength(255)
                        ->placeholder('Contoh: Monitoring Executive'),
                ])->columns(2),

            Forms\Components\Section::make('Tampilan')
                ->schema([
                    Forms\Components\TextInput::make('urutan')->numeric()->default(0),
                    Forms\Components\Toggle::make('is_active')->label('Tampilkan di landing page')->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('urutan')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('judul')->searchable(),
                Tables\Columns\TextColumn::make('icon')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Aktif'),
            ])
            ->defaultSort('urutan')
            ->reorderable('urutan')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEkosistemSolusis::route('/'),
            'create' => Pages\CreateEkosistemSolusi::route('/create'),
            'edit' => Pages\EditEkosistemSolusi::route('/{record}/edit'),
        ];
    }
}
