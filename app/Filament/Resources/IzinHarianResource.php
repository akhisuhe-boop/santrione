<?php

namespace App\Filament\Resources;

use App\Models\IzinHarian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IzinHarianResource extends Resource
{
    protected static ?string $model = IzinHarian::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Absensi';

    protected static ?string $navigationLabel = 'Pengajuan Izin/Sakit';

    protected static ?string $modelLabel = 'Pengajuan Izin/Sakit';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('tipe')
                    ->label('Tipe')
                    ->options([
                        'siswa' => 'Siswa',
                        'guru' => 'Guru / Pegawai',
                    ])
                    ->required()
                    ->live(),

                Forms\Components\Select::make('siswa_id')
                    ->label('Siswa')
                    ->relationship('siswa', 'nama_lengkap')
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get) => $get('tipe') === 'siswa')
                    ->required(fn ($get) => $get('tipe') === 'siswa'),

                Forms\Components\Select::make('pegawai_id')
                    ->label('Guru / Pegawai')
                    ->relationship('pegawai', 'nama')
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get) => $get('tipe') === 'guru')
                    ->required(fn ($get) => $get('tipe') === 'guru'),

                Forms\Components\Select::make('jenis')
                    ->label('Jenis')
                    ->options([
                        'Izin' => 'Izin',
                        'Sakit' => 'Sakit',
                    ])
                    ->required(),

                Forms\Components\DatePicker::make('tanggal_mulai')
                    ->label('Dari Tanggal')
                    ->native(false)
                    ->required(),

                Forms\Components\DatePicker::make('tanggal_selesai')
                    ->label('Sampai Tanggal')
                    ->native(false)
                    ->required(),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->required()
                    ->columnSpanFull(),

            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->getStateUsing(fn ($record) => $record->tipe === 'siswa'
                        ? ($record->siswa?->nama_lengkap ?? '-')
                        : ($record->pegawai?->nama ?? '-')
                    )
                    ->searchable(query: function ($query, string $search) {
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('siswa', fn ($s) => $s->where('nama_lengkap', 'like', "%{$search}%"))
                              ->orWhereHas('pegawai', fn ($p) => $p->where('nama', 'like', "%{$search}%"));
                        });
                    }),

                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'siswa' ? 'Siswa' : 'Guru/Pegawai')
                    ->color(fn ($state) => $state === 'siswa' ? 'info' : 'warning'),

                Tables\Columns\TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn ($state) => $state === 'Sakit' ? 'sky' : 'amber'),

                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Dari')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Sampai')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(40)
                    ->wrap(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'approved' => 'success',
                        'ditolak' => 'danger',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'ditolak' => 'Ditolak',
                    ]),

                Tables\Filters\SelectFilter::make('tipe')
                    ->options([
                        'siswa' => 'Siswa',
                        'guru' => 'Guru / Pegawai',
                    ]),
            ])
            ->actions([

                Tables\Actions\Action::make('lihatLampiran')
                    ->label('Lampiran')
                    ->icon('heroicon-o-paper-clip')
                    ->color('gray')
                    ->visible(fn ($record) => (bool) $record->lampiran)
                    ->url(fn ($record) => $record->lampiran
                        ? \App\Support\FileUrlResolver::private($record->lampiran)
                        : null
                    )
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status === 'pending'),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (IzinHarian $record) {

                        $record->update([
                            'status' => 'approved',
                            'diproses_oleh' => auth()->id(),
                            'diproses_pada' => now(),
                        ]);

                        $record->terapkanKeAbsensi();

                        \App\Services\NotificationService::sendIzinHarianDiproses($record);
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('catatan_admin')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (IzinHarian $record, array $data) {

                        $record->update([
                            'status' => 'ditolak',
                            'catatan_admin' => $data['catatan_admin'],
                            'diproses_oleh' => auth()->id(),
                            'diproses_pada' => now(),
                        ]);

                        \App\Services\NotificationService::sendIzinHarianDiproses($record);
                    }),

                Tables\Actions\DeleteAction::make(),

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
            'index' => \App\Filament\Resources\IzinHarianResource\Pages\ListIzinHarians::route('/'),
            'create' => \App\Filament\Resources\IzinHarianResource\Pages\CreateIzinHarian::route('/create'),
            'edit' => \App\Filament\Resources\IzinHarianResource\Pages\EditIzinHarian::route('/{record}/edit'),
        ];
    }
}
