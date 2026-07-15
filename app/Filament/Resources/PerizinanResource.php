<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerizinanResource\Pages;
use App\Models\Perizinan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

use Carbon\Carbon;

class PerizinanResource extends BaseResource
{
    protected static ?string $model = Perizinan::class;
    protected static ?string $navigationGroup = 'Perizinan';
    protected static ?string $navigationLabel = 'Perizinan Siswa';
    protected static ?int $navigationSort = 8;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Form Perizinan')
                ->columns(4)
                ->schema([

                    Select::make('siswa_id')
                        ->label('Siswa')
                        ->relationship('siswa', 'nama_lengkap')
                        ->searchable()
                        ->required(),

                    Select::make('tipe')
                        ->label('Tipe Izin')
                        ->options([
                            'keluar' => 'Izin Keluar',
                            'pulang' => 'Izin Pulang',
                        ])
                        ->required(),

                    Textarea::make('keperluan')
                        ->label('Keperluan')
                        ->rows(1)
                        ->required(),

                    DatePicker::make('tanggal_mulai')
                        ->label('Tanggal Izin')
                        ->required(),

                    DatePicker::make('tanggal_selesai')
                        ->label('Batas Kembali')
                        ->required()
                        ->helperText('Otomatis batas jam 17:00')
                        ->suffix('17:00'),

                    TextInput::make('penjemput')
                        ->label('Penjemput')
                        ->required(),

                    Select::make('hubungan')
                        ->label('Hubungan')
                        ->options([
                            'ayah' => 'Ayah',
                            'ibu' => 'Ibu',
                            'kakek' => 'Kakek',
                            'nenek' => 'Nenek',
                            'paman' => 'Paman',
                            'bibi' => 'Bibi',
                            'kakak' => 'Kakak',
                            'adik' => 'Adik',
                            'wali' => 'Wali',
                            'lainnya' => 'Lainnya',
                        ])
                        ->searchable()
                        ->placeholder('Pilih hubungan'),

                    TextInput::make('no_wa')
                        ->label('No WA')
                        ->tel()
                        ->required(),

                    // 🔒 hidden system
                    DateTimePicker::make('waktu_keluar')->hidden(),
                    DateTimePicker::make('waktu_kembali')->hidden(),

                    Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'ditolak' => 'Ditolak',
                        'dijemput' => 'Dijemput',
                        'selesai' => 'Selesai',
                    ])
                    ->default('pending')
                    ->required()
                    ->visibleOn('edit') // hanya admin saat edit
                ])
        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
    TextColumn::make('siswa.nama_lengkap')
        ->label('Nama')
        ->searchable(),

    BadgeColumn::make('tipe')
        ->label('Tipe'),

    TextColumn::make('keperluan')
        ->limit(25)
        ->tooltip(fn ($record) => $record->keperluan),

    TextColumn::make('tanggal_mulai')
        ->label('Tanggal Izin')
        ->date(),

    TextColumn::make('waktu_keluar')
        ->label('Keluar Real')
        ->dateTime('d M H:i')
        ->placeholder('-'),

    TextColumn::make('tanggal_selesai')
        ->label('Batas Kembali')
        ->formatStateUsing(fn ($state) =>
            $state ? \Carbon\Carbon::parse($state)->format('d M Y H:i') : '-'
        ),

    TextColumn::make('waktu_kembali')
        ->label('Kembali Real')
        ->dateTime('d M H:i')
        ->placeholder('-'),

    TextColumn::make('penjemput')
        ->default('-'),

    BadgeColumn::make('status')
        ->colors([
            'gray' => 'pending',
            'success' => 'approved',
            'warning' => 'dijemput',
            'primary' => 'selesai',
            'danger' => 'ditolak',
        ])
        ->icons([
            'heroicon-o-clock' => 'pending',
            'heroicon-o-check-circle' => 'approved',
            'heroicon-o-exclamation-circle' => 'dijemput',
            'heroicon-o-x-circle' => 'ditolak',
            'heroicon-o-flag' => 'selesai',
        ]),

    BadgeColumn::make('keterangan_waktu')
        ->label('Keterangan')
        ->colors([
            'success' => 'tepat_waktu',
            'warning' => 'terlambat',
            'danger' => 'sangat_terlambat',
        ]),
])

        ->filters([

            // 🔥 BELUM KEMBALI
            Tables\Filters\Filter::make('belum_kembali')
                ->label('Belum Kembali')
                ->query(fn ($query) => $query->where('status', 'dijemput')),

            // 🔥 TERLAMBAT
            Tables\Filters\Filter::make('terlambat')
                ->label('Terlambat')
                ->query(fn ($query) =>
                    $query->whereIn('keterangan_waktu', ['terlambat', 'sangat_terlambat'])
                ),

            // 🔥 HARI INI
            Tables\Filters\Filter::make('hari_ini')
                ->label('Hari Ini')
                ->query(fn ($query) =>
                    $query->whereDate('tanggal_mulai', now()->toDateString())
                ),

        ])

        ->actions([
        Tables\Actions\EditAction::make(),

        // =========================
        // APPROVE (DARI PENDING)
        // =========================
        Tables\Actions\Action::make('approve')
            ->label('Approve')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn ($record) => $record->status === 'pending')
            ->action(function ($record) {
                $record->update([
                    'status' => 'approved',
                ]);

                \App\Services\NotificationService::sendPerizinanApproved($record);
            })
            ->requiresConfirmation(),

        // =========================
        // REJECT (DARI PENDING)
        // =========================
        Tables\Actions\Action::make('reject')
            ->label('Tolak')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn ($record) => $record->status === 'pending')
            ->form([
                \Filament\Forms\Components\Textarea::make('alasan')
                    ->label('Alasan Penolakan')
                    ->required(),
            ])
            ->action(function ($record, array $data) {
                $record->update([
                    'status' => 'ditolak',
                ]);

                \App\Services\NotificationService::sendPerizinanDitolak(
                    $record,
                    $data['alasan']
                );
            })
            ->requiresConfirmation(),

        // =========================
        // DIJEMPUT
        // =========================
        Tables\Actions\Action::make('dijemput')
            ->label('Dijemput')
            ->icon('heroicon-o-arrow-right-circle')
            ->color('warning')
            ->visible(fn ($record) => $record->status === 'approved')
            ->action(function ($record) {
                $record->update([
                    'status' => 'dijemput',
                    'waktu_keluar' => now(),
                ]);

                \App\Services\NotificationService::sendPerizinanDijemput($record);
            })
            ->requiresConfirmation(),

        // =========================
        // KEMBALI
        // =========================
        Tables\Actions\Action::make('kembali')
            ->label('Kembali')
            ->icon('heroicon-o-arrow-left-circle')
            ->color('success')
            ->visible(fn ($record) => $record->status === 'dijemput')
            ->action(function ($record) {

                $waktuKembali = now();
                $jadwal = \Carbon\Carbon::parse($record->tanggal_selesai);

                if ($waktuKembali->lte($jadwal)) {
                    $keterangan = 'tepat_waktu';
                } elseif ($waktuKembali->diffInHours($jadwal) <= 3) {
                    $keterangan = 'terlambat';
                } else {
                    $keterangan = 'sangat_terlambat';
                }

                $record->update([
                    'waktu_kembali' => $waktuKembali,
                    'keterangan_waktu' => $keterangan,
                    'status' => 'selesai',
                ]);

                \App\Services\NotificationService::sendPerizinanKembali($record);
            })
            ->requiresConfirmation(),
    ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
}

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPerizinans::route('/'),
            'create' => Pages\CreatePerizinan::route('/create'),
            'edit' => Pages\EditPerizinan::route('/{record}/edit'),
        ];
    }
}