<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JadwalKegiatanResource\Pages;
use App\Filament\Resources\JadwalKegiatanResource\RelationManagers;
use App\Models\JadwalKegiatan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Carbon\Carbon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class JadwalKegiatanResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = JadwalKegiatan::class;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationGroup = 'Absensi';
    protected static ?string $navigationLabel = 'Jadwal Kegiatan';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Jadwal Kegiatan')
                    ->icon('heroicon-o-calendar')
                    ->description('Silakan isi jadwal kegiatan yang akan dilaksanakan untuk Absensi')
                    ->schema([

                        Forms\Components\Select::make('template_kegiatan_id')
                            ->relationship('template', 'nama_kegiatan')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {

                                $template = \App\Models\TemplateKegiatan::find($state);

                                if ($template) {

                                    $set(
                                        'jam_mulai',
                                        Carbon::parse($template->jam_mulai)->format('H:i')
                                    );

                                    $set(
                                        'jam_selesai',
                                        Carbon::parse($template->jam_selesai)->format('H:i')
                                    );

                                }
                            }),

                        Forms\Components\DatePicker::make('tanggal')
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->required(),

                        Forms\Components\TextInput::make('jam_mulai')
                            ->label('Jam Mulai')
                            ->readOnly()
                            ->dehydrated()
                            ->required(),

                        Forms\Components\TextInput::make('jam_selesai')
                            ->label('Jam Selesai')
                            ->readOnly()
                            ->dehydrated()
                            ->required(),

                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date()
                    ->label('Tanggal')
                    ->formatStateUsing(
                        fn ($state) =>
                        \Carbon\Carbon::parse($state)
                            ->locale('id')
                            ->translatedFormat('d F Y')
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('template.nama_kegiatan')
                    ->label('Nama Kegiatan')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('template.tipe')
                    ->label('Tipe')
                    ->colors([
                        'primary' => 'siswa',
                        'info' => 'guru',
                    ]),

                Tables\Columns\TextColumn::make('jam_mulai')
                    ->time()
                    ->label('Jam Mulai'),

                Tables\Columns\TextColumn::make('jam_selesai')
                    ->time()
                    ->label('Jam Selesai'),

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
            'index' => Pages\ListJadwalKegiatans::route('/'),
            'create' => Pages\CreateJadwalKegiatan::route('/create'),
            'edit' => Pages\EditJadwalKegiatan::route('/{record}/edit'),
        ];
    }
}