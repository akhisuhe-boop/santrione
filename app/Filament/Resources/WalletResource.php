<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletResource\Pages;
use App\Filament\Resources\WalletResource\RelationManagers;
use App\Models\Wallet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Lembaga;
use App\Models\Kelas;
use Filament\Support\RawJs;

class WalletResource extends BaseResource
{
    protected static ?string $model = Wallet::class;
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Top Up Saldo';
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-circle';

    public static function canCreate(): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\TextInput::make('siswa.nama_lengkap')
                ->label('Siswa')
                ->disabled()
                ->dehydrated(false),

            Forms\Components\TextInput::make('saldo')
                ->label('Saldo')
                ->numeric()
                ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                ->stripCharacters('.')
                ->prefix('Rp')
                ->required()
                ->helperText('Mengubah saldo di sini akan otomatis tercatat sebagai "Penyesuaian Saldo" di Kas, supaya tetap ada jejaknya -- BUKAN diam-diam berubah tanpa catatan.'),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('siswa.nama_lengkap')
                ->label('Nama Siswa')
                ->searchable()
                ->description(fn ($record) =>
                    $record->siswa && $record->siswa->status_siswa !== 'Aktif'
                        ? new \Illuminate\Support\HtmlString(
                            '<span class="inline-flex items-center gap-1 text-amber-600">'
                            . '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 flex-shrink-0">'
                            . '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />'
                            . '</svg>'
                            . 'Alumni (' . e($record->siswa->status_siswa) . ')'
                            . '</span>'
                        )
                        : null
                ),

            TextColumn::make('siswa.kelas.lembaga.nama')
            ->label('Lembaga')
            ->badge()
            ->color('success')
            ->sortable()
            ->searchable(),

            TextColumn::make('siswa.kelas.nama')
            ->label('Kelas')
            ->searchable(),

            TextColumn::make('saldo')
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

            TextColumn::make('updated_at')
                ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->translatedFormat('d F Y H:i:s'))
            ])
            
            ->filters([
            SelectFilter::make('lembaga')
                ->label('Lembaga')
                ->options(
                    Lembaga::orderBy('nama')
                        ->pluck('nama', 'id')
                )
                ->query(function (Builder $query, array $data) {
                    if (blank($data['value'])) {
                        return;
                    }
        
                    $query->whereHas('siswa.kelas', function (Builder $q) use ($data) {
                        $q->where('lembaga_id', $data['value']);
                    });
                }),
        
            SelectFilter::make('kelas')
                ->label('Kelas')
                ->options(
                    Kelas::orderBy('nama')
                        ->pluck('nama', 'id')
                )
                ->query(function (Builder $query, array $data) {
                    if (blank($data['value'])) {
                        return;
                    }
        
                    $query->whereHas('siswa', function (Builder $q) use ($data) {
                        $q->where('kelas_id', $data['value']);
                    });
                }),
        ])
            
            ->actions([
                Tables\Actions\Action::make('topup')
            ->label('Top Up')
            ->icon('heroicon-o-plus')
            ->color('success')

            ->form([
                Forms\Components\TextInput::make('amount')
                    ->label('Nominal Top Up')
                    ->numeric()
                    ->mask(RawJs::make("\$money(\$input, ',', '.')"))
                    ->stripCharacters('.')
                    ->required(),
            ])

            ->action(function ($record, $data) {

                app(\App\Services\WalletService::class)
                    ->topUp($record, $data['amount']);

            }),
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
            'index' => Pages\ListWallets::route('/'),
            'create' => Pages\CreateWallet::route('/create'),
            'edit' => Pages\EditWallet::route('/{record}/edit'),
        ];
    }
}
