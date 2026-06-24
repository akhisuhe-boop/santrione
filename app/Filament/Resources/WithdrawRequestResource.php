<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WithdrawRequestResource\Pages;
use App\Models\WithdrawRequest;
use App\Models\Wallet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\Section;

class WithdrawRequestResource extends Resource
{
    protected static ?string $model = WithdrawRequest::class;
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Penarikan Saldo';
    protected static ?int $navigationSort = 8;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-circle';

    // =========================
    // 🧾 FORM
    // =========================
    public static function form(Form $form): Form
    {
        return $form->schema([

            Section::make('Data Siswa')
                ->schema([

                    Forms\Components\Select::make('wallet_id')
                    ->label('Siswa')
                    ->options(
                        \App\Models\Wallet::with('siswa')
                            ->get()
                            ->pluck('siswa.nama_lengkap', 'id')
                    )
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {

                        $wallet = \App\Models\Wallet::with('siswa')->find($state);

                        // ✅ Set saldo
                        $set('saldo', $wallet?->saldo ?? 0);

                        // ✅ Ambil no HP dari DB (FIX sesuai field kamu)
                        $noHp = $wallet?->siswa?->wa_ayah 
                            ?? $wallet?->siswa?->wa_ibu;

                        // ✅ Set ke form state
                        $set('no_hp', $noHp);
                    }),

                    Forms\Components\Placeholder::make('saldo_view')
                        ->label('Saldo Saat Ini')
                        ->content(fn ($get) => 'Rp ' . number_format($get('saldo') ?? 0, 0, ',', '.'))
                        ->extraAttributes([
                            'class' => 'fi-input block w-full rounded-lg border-gray-300 bg-gray-50 px-3 py-2 text-sm shadow-sm'
                        ]),

                    Forms\Components\Hidden::make('saldo'),

                ])->columns(2),

            Section::make('Detail Penarikan')
                ->schema([

                    Forms\Components\TextInput::make('amount')
                        ->label('Nominal')
                        ->numeric()
                        ->required()
                        ->rule(function (callable $get) {
                            return function ($attribute, $value, $fail) use ($get) {

                                $wallet = Wallet::find($get('wallet_id'));

                                if ($wallet && $value > $wallet->saldo) {
                                    $fail('Nominal tidak boleh melebihi saldo!');
                                }
                            };
                        }),

                    Forms\Components\Select::make('method')
                        ->options([
                            'bank' => 'Bank',
                            'cash' => 'Cash',
                        ])
                        ->required()
                        ->reactive(),

                    Forms\Components\TextInput::make('rekening_tujuan')
                        ->label('Nomor Rekening')
                        ->visible(fn ($get) => $get('method') === 'bank')
                        ->required(fn ($get) => $get('method') === 'bank'),

                    Forms\Components\TextInput::make('nama_pemilik')
                        ->label('Nama Penerima')
                        ->required(),

                    Forms\Components\Placeholder::make('no_hp_view')
                    ->label('No HP Orang Tua')
                    ->content(fn ($get) => $get('no_hp') ?? '-')
                    ->reactive()
                    ->extraAttributes([
                        'class' => 'fi-input block w-full rounded-lg border-gray-300 bg-gray-50 px-3 py-2 text-sm shadow-sm'
                    ]),

                    Forms\Components\Textarea::make('catatan_admin'),

                ])->columns(2),

            // =========================
            // 📊 RIWAYAT
            // =========================
            Section::make('Riwayat Penarikan')
                ->schema([

                    Forms\Components\Placeholder::make('riwayat_withdraw')
                        ->content(function ($get) {

                            $walletId = $get('wallet_id');

                            if (!$walletId) {
                                return 'Pilih siswa terlebih dahulu';
                            }

                            $data = WithdrawRequest::where('wallet_id', $walletId)
                            ->latest()
                            ->take(5)
                            ->get();

                            if ($data->isEmpty()) {
                                return 'Belum ada riwayat penarikan';
                            }

                            $html = '<div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-3 gap-3">';

                        foreach ($data as $item) {
                            $html .= '
                                <div class="border p-3 rounded-lg shadow-sm bg-white">
                                    <div class="flex justify-between items-center mb-1">
                                        <div class="font-semibold text-sm">
                                            Rp ' . number_format($item->amount, 0, ',', '.') . '
                                        </div>
                                        <span style="
                                        padding: 2px 8px;
                                        font-size: 12px;
                                        border-radius: 6px;
                                        ' . (
                                            $item->status === 'failed' || $item->status === 'rejected'
                                                ? 'background-color:#fee2e2; color:#dc2626;' 
                                                : ($item->status === 'approved'
                                                    ? 'background-color:#dcfce7; color:#16a34a;'
                                                    : 'background-color:#fef9c3; color:#ca8a04;'
                                                )
                                        ) . '
                                    ">
                                        ' . ucfirst($item->status) . '
                                    </span>
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        ' . \Carbon\Carbon::parse($item->created_at)->format('d M Y H:i') . '
                                    </div>
                                </div>
                            ';
                        }

                        $html .= '</div>';

                            return new \Illuminate\Support\HtmlString($html);
                        }),

                ])
                ->visible(fn ($get) => $get('wallet_id')),

            // =========================
            // ⚙️ SYSTEM FIELD
            // =========================
            Forms\Components\Hidden::make('status')->default('pending'), // 🔥 ADMIN AUTO APPROVE
            Forms\Components\Hidden::make('requested_by_type')->default('admin'),
            Forms\Components\Hidden::make('requested_by_id')->default(auth()->id()),
        ]);
    }

    // =========================
    // 📋 TABLE
    // =========================
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('wallet.siswa.nama_lengkap')
                    ->label('Siswa')
                    ->searchable(),
                
                TextColumn::make('wallet.siswa.kelas.lembaga.nama')
    ->label('Lembaga')
    ->badge()
    ->color(fn ($record) => match ($record->wallet?->siswa?->kelas?->lembaga_id) {
        1 => 'success',
        2 => 'warning',
        3 => 'primary',
        4 => 'info',
        default => 'gray',
    }),

TextColumn::make('wallet.siswa.kelas.nama')
    ->label('Kelas')
    ->badge()
    ->color(fn ($record) => match ($record->wallet?->siswa?->kelas?->lembaga_id) {
        1 => 'primary',
        2 => 'success',
        3 => 'warning',
        4 => 'info',
        default => 'gray',
    }),

                TextColumn::make('amount')
                    ->label('Nominal Penarikan')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('method'),

                BadgeColumn::make('status')
                ->color(fn ($state) => match ($state) {
                    'approved' => 'success',
                    'pending' => 'warning',
                    'rejected', 'failed' => 'danger',
                    default => 'gray',
                }),

                TextColumn::make('created_at')
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->translatedFormat('d F Y H:i:s')),
            ])
            ->actions([

                // =========================
                // ✅ APPROVE
                // =========================
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {

                        if ($record->status !== 'pending') {
                            return;
                        }

                        app(\App\Services\WalletService::class)
                            ->approveWithdraw($record);
                    }),

                // =========================
                // ❌ REJECT
                // =========================
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'rejected',
                            'processed_by' => auth()->id(),
                            'processed_at' => now(),
                        ]);
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWithdrawRequests::route('/'),
            'create' => Pages\CreateWithdrawRequest::route('/create'),
            'edit' => Pages\EditWithdrawRequest::route('/{record}/edit'),
        ];
    }
}