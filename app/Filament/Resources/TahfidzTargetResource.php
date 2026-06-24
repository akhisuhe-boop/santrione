<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TahfidzTargetResource\Pages;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahfidzTarget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class TahfidzTargetResource extends Resource
{
    protected static ?string $model = TahfidzTarget::class;
    protected static ?string $navigationGroup = 'Tahfidz';
    protected static ?string $navigationLabel = 'Target Tahfidz';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    public static function canCreate(): bool
    {
        return false;
    }

    // ================= FORM =================
    public static function form(Form $form): Form
    {
        return $form->schema([
        Forms\Components\TextInput::make('target_juz')
        ->label('Target Juz')
        ->numeric()
        ->required(),
            ]);
    }

    // ================= TABLE =================
    public static function table(Table $table): Table
    {
        return $table

            ->columns([

                Tables\Columns\TextColumn::make('siswa.nama_lengkap')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('target_juz')
                    ->label('Target')
                    ->formatStateUsing(fn ($state) => 'Juz ' . $state)
                    ->sortable(),

            ])

            ->headerActions([

                Tables\Actions\Action::make('setTargetMassal')
                    ->label('Set Target Tahfidz')
                    ->icon('heroicon-o-cog-6-tooth')

                    ->form([

                        // 🔥 MODE (WAJIB LIVE)
                        Forms\Components\Select::make('mode')
                            ->label('Mode Setting')
                            ->options([
                                'kelas' => 'Per Kelas',
                                'siswa' => 'Pilih Siswa',
                            ])
                            ->required()
                            ->live(), // 🔥 INI KUNCI

                        // 🔥 CHECKLIST KELAS
                        Forms\Components\CheckboxList::make('kelas_ids')
                            ->label('Pilih Kelas')
                            ->options(Kelas::pluck('nama', 'id')->toArray())
                            ->columns(2)
                            ->reactive()
                            ->visible(fn ($get) => $get('mode') === 'kelas'),

                        // 🔥 CHECKLIST SISWA
                        Forms\Components\CheckboxList::make('siswa_ids')
                            ->label('Pilih Siswa')
                            ->options(Siswa::pluck('nama_lengkap', 'id')->toArray())
                            ->columns(2)
                            ->searchable()
                            ->reactive()
                            ->visible(fn ($get) => $get('mode') === 'siswa'),

                        // TARGET
                        Forms\Components\TextInput::make('target_juz')
                            ->label('Target Juz')
                            ->numeric()
                            ->required(),

                    ])

                    ->action(function ($data) {

                        // MODE KELAS
                        if (($data['mode'] ?? null) === 'kelas' && !empty($data['kelas_ids'])) {

                            $siswas = Siswa::whereIn('kelas_id', $data['kelas_ids'])->get();

                            foreach ($siswas as $siswa) {
                                TahfidzTarget::updateOrCreate(
                                    ['siswa_id' => $siswa->id],
                                    ['target_juz' => $data['target_juz']]
                                );
                            }
                        }

                        // MODE SISWA
                        if (($data['mode'] ?? null) === 'siswa' && !empty($data['siswa_ids'])) {

                            foreach ($data['siswa_ids'] as $id) {
                                TahfidzTarget::updateOrCreate(
                                    ['siswa_id' => $id],
                                    ['target_juz' => $data['target_juz']]
                                );
                            }
                        }

                        Notification::make()
                            ->title('Target berhasil diset')
                            ->success()
                            ->send();
                    })

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

    // ================= PAGES =================
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTahfidzTargets::route('/'),
            'create' => Pages\CreateTahfidzTarget::route('/create'),
            'edit' => Pages\EditTahfidzTarget::route('/{record}/edit'),
        ];
    }
}