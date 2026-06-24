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

class WalletResource extends Resource
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
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('siswa.nama_lengkap')
                ->label('Nama Siswa')
                ->searchable(),

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
                //
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
