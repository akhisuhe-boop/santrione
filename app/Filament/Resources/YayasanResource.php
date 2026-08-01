<?php

namespace App\Filament\Resources;

use App\Filament\Resources\YayasanResource\Pages;
use App\Filament\Resources\YayasanResource\RelationManagers;
use App\Models\Yayasan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class YayasanResource extends BaseResource
{
    protected static ?string $model = Yayasan::class;
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Yayasan';
    protected static ?string $pluralModelLabel = 'Yayasan';
    protected static ?string $navigationLabel = 'Yayasan';    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
    
                Section::make('Informasi Yayasan')
                    ->description('Informasi dasar yayasan')
                    ->icon('heroicon-o-building-library')
                    ->schema([
    
                        TextInput::make('nama')
                            ->label('Nama Yayasan')
                            ->required()
                            ->maxLength(255),
    
                        TextInput::make('ketua')
                            ->label('Ketua Yayasan')
                            ->maxLength(255),

                        \Filament\Forms\Components\Select::make('status')
                            ->label('Status Langganan')
                            ->options([
                                'trial' => 'Trial',
                                'active' => 'Active',
                                'suspended' => 'Suspended (kunci akses, data aman)',
                                'cancelled' => 'Cancelled (kunci akses, data aman)',
                            ])
                            ->visibleOn('edit')
                            ->disabled(fn () => ! auth()->user()?->is_platform_admin)
                            ->helperText('Ubah manual di sini kalau mau suspend/aktifkan tanpa lewat alur pembayaran. Data TIDAK pernah terhapus lewat perubahan status.'),

                        FileUpload::make('logo')
                            ->label('Logo Yayasan')
                            ->image()
                            ->disk('r2-public')
                            ->directory('yayasan')
                            ->imageEditor(),
    
                    ])
                    ->columns(3),
    
                Section::make('Kontak Yayasan')
                    ->description('Informasi kontak yang akan tampil pada raport, kwitansi dan surat.')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
    
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat')
                            ->rows(3)
                            ->columnSpanFull(),
    
                        TextInput::make('telepon')
                            ->label('Telepon')
                            ->tel()
                            ->maxLength(30),
    
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(100),
    
                        TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->placeholder('https://')
                            ->maxLength(150),
    
                    ])
                    ->columns(3),

                Section::make('Domain Custom')
                    ->description('Kalau diisi, portal publik (Wali/Guru/PPDB) yayasan ini bisa diakses lewat domain sendiri, bukan cuma subdomain default.')
                    ->icon('heroicon-o-globe-alt')
                    ->schema([

                        TextInput::make('domain')
                            ->label('Domain')
                            ->placeholder('contoh: sekolahku.sch.id')
                            ->helperText('Arahkan DNS domain ini ke server aplikasi dulu sebelum diisi di sini.')
                            ->unique(ignoreRecord: true),

                    ])
                    ->columns(1),

                Section::make('Akun Admin Awal')
                    ->description('Opsional — isi kalau mau langsung dibuatkan 1 akun admin panel untuk yayasan ini. Password akan digenerate otomatis dan cuma ditampilkan SEKALI setelah yayasan disimpan (catat baik-baik). Kalau dikosongkan, akun admin bisa dibuat manual belakangan lewat menu Pengguna.')
                    ->icon('heroicon-o-user-plus')
                    ->visibleOn('create')
                    ->schema([

                        TextInput::make('admin_nama')
                            ->label('Nama Admin')
                            ->maxLength(255),

                        TextInput::make('admin_email')
                            ->label('Email Admin')
                            ->email()
                            ->maxLength(150)
                            ->unique('users', 'email'),

                    ])
                    ->columns(2),
    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

            Tables\Columns\ImageColumn::make('logo')
                ->label('Logo')
                ->disk('r2-public')
                ->circular()
                ->size(45),
        
            Tables\Columns\TextColumn::make('nama')
                ->label('Nama Yayasan')
                ->searchable()
                ->sortable(),
        
            Tables\Columns\TextColumn::make('ketua')
                ->label('Ketua')
                ->searchable(),

            Tables\Columns\BadgeColumn::make('status')
                ->label('Status Langganan')
                ->colors([
                    'warning' => 'trial',
                    'success' => 'active',
                    'danger' => fn ($state) => in_array($state, ['suspended', 'cancelled']),
                ])
                ->formatStateUsing(function ($state, $record) {
                    if ($state === 'trial') {
                        return $record->isOnTrial()
                            ? 'Trial (' . $record->trialDaysLeft() . ' hari lagi)'
                            : 'Trial Habis';
                    }

                    return ucfirst($state);
                }),
        
            Tables\Columns\TextColumn::make('telepon')
                ->label('Telepon')
                ->toggleable(),
        
            Tables\Columns\TextColumn::make('website')
                ->label('Website')
                ->toggleable(),
        
            Tables\Columns\TextColumn::make('created_at')
                ->label('Dibuat')
                ->date('d M Y')
                ->toggleable(isToggledHiddenByDefault: true),
        
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
            'index' => Pages\ListYayasans::route('/'),
            'create' => Pages\CreateYayasan::route('/create'),            
            'edit' => Pages\EditYayasan::route('/{record}/edit'),
        ];
    }
}
