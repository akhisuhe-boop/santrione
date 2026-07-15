<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplateKegiatanResource\Pages;
use App\Filament\Resources\TemplateKegiatanResource\RelationManagers;
use App\Models\TemplateKegiatan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Carbon\Carbon;
use App\Models\JadwalKegiatan;

class TemplateKegiatanResource extends BaseResource
{
    protected static ?string $model = TemplateKegiatan::class;
    protected static ?string $navigationGroup = 'Absensi';
    protected static ?string $navigationLabel = 'Template Kegiatan';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Template Kegiatan')
                ->description('Silakan isi template kegiatan yang akan digunakan untuk membuat jadwal kegiatan')
                ->schema([

                    Forms\Components\TextInput::make('nama_kegiatan')
                        ->label('Nama Kegiatan')
                        ->required(),

                    Forms\Components\Select::make('tipe')
                        ->options([
                            'siswa' => 'Siswa',
                            'guru' => 'Guru',
                        ])
                        ->required(),

                    Forms\Components\Select::make('hari')
                        ->label('Hari')
                        ->options([
                            'senin' => 'Senin',
                            'selasa' => 'Selasa',
                            'rabu' => 'Rabu',
                            'kamis' => 'Kamis',
                            'jumat' => 'Jumat',
                            'sabtu' => 'Sabtu',
                            'minggu' => 'Minggu',
                        ])
                        ->required(),

                    Forms\Components\TimePicker::make('jam_mulai')
                        ->label('Jam Mulai')
                        ->seconds(true)
                        ->displayFormat('H:i')
                        ->format('H:i:s')
                        ->native(false)
                        ->required(),

                    Forms\Components\TimePicker::make('jam_selesai')
                        ->label('Jam Selesai')
                        ->seconds(true)
                        ->displayFormat('H:i')
                        ->format('H:i:s')
                        ->native(false)
                        ->required(),

                    Forms\Components\TextInput::make('toleransi_telat')
                        ->label('Toleransi Telat (Menit)')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    Forms\Components\Toggle::make('aktif')
                        ->label('Aktif')
                        ->default(true),

                ])
                ->columns(2)
        ]);
}

    public static function table(Table $table): Table
    {
        return $table                
            ->columns([
            Tables\Columns\TextColumn::make('hari')
                ->label('Hari'),

            Tables\Columns\TextColumn::make('nama_kegiatan')
                ->label('Nama Kegiatan')
                ->searchable(),

            Tables\Columns\BadgeColumn::make('tipe')
                ->label('Tipe')
                ->colors([
                    'primary' => 'siswa',
                    'info' => 'guru',
                ]),
            
            Tables\Columns\TextColumn::make('jam_mulai')
                ->label('Jam Mulai')
                ->time('H:i'),

            Tables\Columns\TextColumn::make('jam_selesai')
                ->label('Jam Selesai')
                ->time('H:i'),

            Tables\Columns\TextColumn::make('toleransi_telat')
                ->label('Toleransi Telat')
                ->suffix(' menit'),

            Tables\Columns\IconColumn::make('aktif')
                ->label('Aktif')
                ->boolean(),
            ])

            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTemplateKegiatans::route('/'),
            'create' => Pages\CreateTemplateKegiatan::route('/create'),
            'edit' => Pages\EditTemplateKegiatan::route('/{record}/edit'),
        ];
    }
}
