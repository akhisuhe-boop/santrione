<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LembagaResource\Pages;
use App\Filament\Resources\LembagaResource\RelationManagers;
use App\Models\Lembaga;
use App\Models\Yayasan;
use Filament\Forms\Get;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LembagaResource extends Resource
{
    protected static ?string $model = Lembaga::class;
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Lembaga';
    protected static ?string $pluralModelLabel = 'Lembaga';
    protected static ?string $navigationLabel = 'Lembaga';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
    
                Section::make('Informasi Lembaga')
                    ->description('Informasi identitas lembaga')
                    ->icon('heroicon-o-building-office')
                    ->schema([
    
                        TextInput::make('nama')
                            ->label('Nama Lembaga')
                            ->required()
                            ->maxLength(255),
    
                        Select::make('jenis')
                            ->label('Jenis Lembaga')
                            ->options([
                                'tk'  => 'TK',
                                'sd'  => 'SD',
                                'smp' => 'SMP',
                                'sma' => 'SMA',
                            ])
                            ->required(),
    
                        FileUpload::make('logo')
                            ->label('Logo Lembaga')
                            ->image()
                            ->directory('lembaga')
                            ->imageEditor(),
    
                        TextInput::make('npsn')
                            ->label('NPSN')
                            ->maxLength(30),
    
                        TextInput::make('nss')
                            ->label('NSS')
                            ->maxLength(30),
    
                    ])
                    ->columns(3),
    
                Section::make('Manajemen Lembaga')
                    ->description('Informasi kepala sekolah dan bendahara')
                    ->icon('heroicon-o-user-group')
                    ->schema([
    
                        TextInput::make('kepala_sekolah')
                            ->label('Kepala Sekolah'),
    
                        Select::make('bendahara_id')
                            ->label('Bendahara')
                            ->relationship('bendahara', 'nama')
                            ->searchable()
                            ->preload(),
                        
                        Select::make('printer_kwitansi')
                            ->label('Printer Kwitansi')
                            ->options([
                                'thermal58' => 'Thermal 58 mm',
                                'thermal80' => 'Thermal 80 mm',
                                'dotmatrix' => 'Dot Matrix 3 Ply',
                            ])
                            ->default('thermal80')
                            ->native(false)
                            ->helperText('Digunakan saat mencetak kwitansi pembayaran.'),
    
                        Forms\Components\Toggle::make('is_tes')
                            ->label('Menggunakan Tes Masuk?')
                            ->helperText('Jika aktif, calon siswa wajib mengikuti tes sebelum dinyatakan lulus.')
                            ->default(true),
    
                    ])
                    ->columns(3),
    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->size(40),
            
                Tables\Columns\TextColumn::make('nama')
                    ->label('Lembaga')
                    ->searchable()
                    ->sortable(),
            
                Tables\Columns\TextColumn::make('jenis')
                    ->badge()
                    ->color('success'),
            
                Tables\Columns\TextColumn::make('npsn')
                    ->label('NPSN')
                    ->toggleable(),
            
                Tables\Columns\TextColumn::make('kepala_sekolah')
                    ->label('Kepala Sekolah'),
            
                Tables\Columns\TextColumn::make('bendahara.nama')
                    ->label('Bendahara'),
                
                Tables\Columns\BadgeColumn::make('printer_kwitansi')
                    ->label('Printer')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'thermal58' => 'Thermal 58 mm',
                        'thermal80' => 'Thermal 80 mm',
                        'dotmatrix' => 'Dot Matrix',
                        default => '-',
                    })
                    ->colors([
                        'success' => 'thermal80',
                        'warning' => 'thermal58',
                        'primary' => 'dotmatrix',
                    ]),
            
                Tables\Columns\IconColumn::make('is_tes')
                    ->label('Tes')
                    ->boolean(),
            
            ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListLembagas::route('/'),
            'create' => Pages\CreateLembaga::route('/create'),
            'edit' => Pages\EditLembaga::route('/{record}/edit'),
        ];
    }
}
