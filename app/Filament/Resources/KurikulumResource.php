<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KurikulumResource\Pages;
use App\Filament\Resources\KurikulumResource\RelationManagers;
use App\Models\Kurikulum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;

class KurikulumResource extends Resource
{
    protected static ?string $model = Kurikulum::class;
    protected static ?string $navigationGroup = 'Akademik';
    protected static ?string $navigationLabel = 'Kurikulum';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-date-range';

    public static function form(Form $form): Form
{
    return $form->schema([

        Section::make('Setup Kurikulum')
            ->schema([

                Select::make('kelas_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'nama')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($set) => $set('jumlah_jam_per_minggu', null)),

                Select::make('pegawai_id')
                    ->label('Guru')
                    ->relationship('pegawai', 'nama')
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($set) => $set('jumlah_jam_per_minggu', null)),

                Select::make('mata_pelajaran_id')
                    ->label('Mata Pelajaran')
                    ->relationship('mataPelajaran', 'nama')
                    ->required()
                    ->rules([
                        function (callable $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {

                                $query = \App\Models\Kurikulum::where('kelas_id', $get('kelas_id'))
                                    ->where('mata_pelajaran_id', $value);

                                // 🔥 penting: ignore data sendiri saat edit
                                if ($get('id')) {
                                    $query->where('id', '!=', $get('id'));
                                }

                                if ($query->exists()) {
                                    $fail('Mapel ini sudah ada di kelas tersebut.');
                                }
                            };
                        },
                    ]),

                TextInput::make('jumlah_jam_per_minggu')
                ->label('Jam per Minggu')
                ->numeric()
                ->minValue(1)
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $get, callable $set) {

                    $kelasId = $get('kelas_id');
                    $pegawaiId = $get('pegawai_id');

                    if (!$kelasId || !$pegawaiId) return;

                    // 🔹 TOTAL JAM PER KELAS
                    $totalKelas = \App\Models\Kurikulum::where('kelas_id', $kelasId)
                        ->when($get('id'), fn ($q) => $q->where('id', '!=', $get('id')))
                        ->sum('jumlah_jam_per_minggu') - (int) ($get('jumlah_jam_per_minggu') ?? 0);

                    $maxKelas = 30;

                    if (($totalKelas + $state) > $maxKelas) {
                        \Filament\Notifications\Notification::make()
                            ->title('Total jam kelas melebihi batas!')
                            ->body("Maksimal $maxKelas JP per minggu")
                            ->danger()
                            ->send();

                        $set('jumlah_jam_per_minggu', null);
                        return;
                    }

                    // 🔹 TOTAL JAM PER GURU (HARD LIMIT)
                    $totalGuru = \App\Models\Kurikulum::where('pegawai_id', $pegawaiId)
                        ->when($get('id'), fn ($q) => $q->where('id', '!=', $get('id')))
                        ->sum('jumlah_jam_per_minggu') - (int) ($get('jumlah_jam_per_minggu') ?? 0);

                    $maxGuru = 40;

                    if (($totalGuru + $state) > $maxGuru) {
                        \Filament\Notifications\Notification::make()
                            ->title('Jam guru overload!')
                            ->body("Maksimal $maxGuru JP per minggu")
                            ->danger()
                            ->send();

                        $set('jumlah_jam_per_minggu', null);
                        return;
                    }

                    // 🔸 WARNING (tidak blok)
                    if (($totalGuru + $state) > 24) {
                        \Filament\Notifications\Notification::make()
                            ->title('Jam guru tinggi')
                            ->body('Disarankan maksimal 24 JP per minggu')
                            ->warning()
                            ->send();
                    }
                }),

            ])
            ->columns(2),
    ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('kelas.nama')->label('Kelas'),
            TextColumn::make('pegawai.nama')->label('Guru')->searchable(),
            TextColumn::make('mataPelajaran.nama')->label('Mapel'),
            TextColumn::make('jumlah_jam_per_minggu')
            ->label('JP/Minggu')
            ->summarize(
                Sum::make()
                    ->label('Total JP')
            ),
            ])
            ->defaultSort('kelas_id')

            ->filters([
                Tables\Filters\SelectFilter::make('kelas_id')
                    ->label('Filter Kelas')
                    ->relationship('kelas', 'nama'),

                Tables\Filters\SelectFilter::make('pegawai_id')
                    ->label('Filter Guru')
                    ->relationship('pegawai', 'nama'),

                Tables\Filters\SelectFilter::make('mata_pelajaran_id')
                    ->label('Filter Mapel')
                    ->relationship('mataPelajaran', 'nama'),
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
            'index' => Pages\ListKurikulums::route('/'),
            'create' => Pages\CreateKurikulum::route('/create'),
            'edit' => Pages\EditKurikulum::route('/{record}/edit'),
        ];
    }
}
