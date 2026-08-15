<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudiKasusResource\Pages;
use App\Models\StudiKasus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudiKasusResource extends Resource
{
    protected static ?string $model = StudiKasus::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static ?string $navigationGroup = 'Landing Page';

    protected static ?string $navigationLabel = 'Studi Kasus';

    protected static bool $isScopedToTenant = false;

    public static function canAccess(): bool
    {
        return (bool) (auth()->user()?->is_platform_admin ?? false);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas')
                ->schema([
                    Forms\Components\TextInput::make('nama_lembaga')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Yayasan Tunas Cendekia Madani'),
                    Forms\Components\TextInput::make('badge_text')
                        ->maxLength(255)
                        ->placeholder('Contoh: Studi Kasus'),
                    Forms\Components\FileUpload::make('foto')
                        ->label('Foto (opsional)')
                        ->image()
                        ->disk('r2-public')
                        ->directory('landing/studi-kasus')
                        ->imageEditor(),
                    Forms\Components\Textarea::make('deskripsi')
                        ->label('Narasi Singkat')
                        ->rows(4)
                        ->columnSpanFull()
                        ->helperText('Cerita singkat konteksnya — semua angka & fakta di sini diisi manual oleh Anda, tidak diambil otomatis dari data aplikasi.'),
                ])->columns(2),

            Forms\Components\Section::make('Angka yang Ditonjolkan')
                ->description('Isi manual satu-satu — bebas berapa jumlahnya. Contoh: label "Santri Terkelola", nilai "500+".')
                ->schema([
                    Forms\Components\Repeater::make('stats')
                        ->label('Statistik')
                        ->schema([
                            Forms\Components\TextInput::make('label')->required()->placeholder('Santri Terkelola'),
                            Forms\Components\TextInput::make('value')->required()->placeholder('500+'),
                        ])
                        ->columns(2)
                        ->defaultItems(1)
                        ->reorderable()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Kutipan (opsional)')
                ->schema([
                    Forms\Components\Textarea::make('kutipan')->rows(3)->columnSpanFull(),
                    Forms\Components\TextInput::make('kutipan_nama')->label('Nama Pemberi Kutipan'),
                    Forms\Components\TextInput::make('kutipan_jabatan')->label('Jabatan'),
                ])->columns(2),

            Forms\Components\TextInput::make('urutan')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Tampilkan di landing page')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')->disk('r2-public'),
                Tables\Columns\TextColumn::make('nama_lembaga')->searchable(),
                Tables\Columns\TextColumn::make('urutan')->sortable(),
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
            'index' => Pages\ListStudiKasus::route('/'),
            'create' => Pages\CreateStudiKasus::route('/create'),
            'edit' => Pages\EditStudiKasus::route('/{record}/edit'),
        ];
    }
}
