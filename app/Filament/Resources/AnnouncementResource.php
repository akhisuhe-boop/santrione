<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AnnouncementResource extends BaseResource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Pengumuman';
    protected static ?string $pluralModelLabel = 'Pengumuman';
    protected static ?string $navigationGroup = 'Manajemen Sekolah';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Isi Pengumuman')
                ->schema([

                    Forms\Components\TextInput::make('title')
                        ->label('Judul')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\RichEditor::make('content')
                        ->label('Isi Pengumuman')
                        ->required()
                        ->columnSpanFull(),

                ]),

            Forms\Components\Grid::make(2)
                ->schema([

                    Forms\Components\Section::make('Target Pengumuman')
                        ->schema([

                            Forms\Components\Select::make('target_type')
                                ->label('Target')
                                ->required()
                                ->default('all')
                                ->live()
                                ->afterStateUpdated(function ($set) {
                                    $set('target_role', null);
                                    $set('kelas_id', null);
                                })
                                ->options([
                                    'all'   => 'Semua Portal',
                                    'role'  => 'Portal Tertentu',
                                    'kelas' => 'Kelas Tertentu',
                                ])
                                ->helperText('Pilih siapa yang akan menerima pengumuman.'),

                            Forms\Components\Select::make('target_role')
                                ->label('Portal')
                                ->options([
                                    'guru'  => 'Guru',
                                    'wali'  => 'Wali Murid',
                                    'ppdb'  => 'PPDB',
                                ])
                                ->visible(fn ($get) => $get('target_type') === 'role')
                                ->required(fn ($get) => $get('target_type') === 'role'),

                            Forms\Components\Select::make('kelas_id')
                                ->label('Kelas')
                                ->relationship('kelas', 'nama')
                                ->searchable()
                                ->preload()
                                ->visible(fn ($get) => $get('target_type') === 'kelas')
                                ->required(fn ($get) => $get('target_type') === 'kelas'),

                            Forms\Components\Toggle::make('is_pinned')
                                ->label('Pin Pengumuman'),

                            Forms\Components\Toggle::make('send_whatsapp')
                                ->label('Broadcast WhatsApp'),

                        ]),

                    Forms\Components\Section::make('Lampiran')
                        ->schema([

                            Forms\Components\FileUpload::make('attachment')
                                ->label('File Lampiran')
                                ->directory('announcements')
                                ->acceptedFileTypes([
                                    'application/pdf',
                                    'image/jpeg',
                                    'image/png',
                                    'application/msword',
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                ])
                                ->maxSize(5120)
                                ->downloadable()
                                ->openable(),

                        ]),

                ]),

            Forms\Components\Hidden::make('created_by')
                ->default(fn () => auth()->id()),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->defaultSort('is_pinned', 'desc')
            ->defaultSort('created_at', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('target_type')
                ->badge()
                ->color(function ($state, $record) {
            
                    if ($state === 'all') {
                        return 'success';
                    }
            
                    if ($state === 'role') {
                        return 'warning';
                    }
            
                    if ($state === 'kelas') {
                        return 'primary';
                    }
            
                    return 'gray';
                }),
                
                Tables\Columns\IconColumn::make('attachment')
                ->label('File')
                ->boolean(fn ($record) => filled($record->attachment)),

                Tables\Columns\IconColumn::make('is_pinned')
                ->label('Pinned')
                ->boolean()
                ->trueIcon('heroicon-o-bookmark')
                ->falseIcon('heroicon-o-minus'),

                Tables\Columns\IconColumn::make('send_whatsapp')
                ->label('WA')
                ->boolean()
                ->trueColor('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

            ])

            ->actions([

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),

            ])

            ->bulkActions([

                Tables\Actions\DeleteBulkAction::make(),

            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [

            'index' => Pages\ListAnnouncements::route('/'),

            'create' => Pages\CreateAnnouncement::route('/create'),

            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),

        ];
    }
}