<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Pengumuman';
    protected static ?string $pluralModelLabel = 'Pengumuman';
    protected static ?string $navigationGroup = 'Manajemen Sekolah';

    public static function form(Form $form): Form
    {
        return $form->schema([

            // =========================
            // ISI PENGUMUMAN
            // =========================
            Forms\Components\Section::make('Isi Pengumuman')
                ->schema([

                    Forms\Components\TextInput::make('title')
                        ->label('Judul')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\RichEditor::make('content')
                        ->label('Isi Pengumuman')
                        ->required()
                        ->columnSpanFull(),
                ]),

            // =========================
            // TARGET + LAMPIRAN
            // =========================
            Forms\Components\Grid::make(2)
                ->schema([

                    // LAMPIRAN
                    Forms\Components\Section::make('Lampiran')
                        ->columnSpan(1)
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

                    // TARGET KELAS (ONLY)
                    Forms\Components\Section::make('Target Kelas')
                        ->columnSpan(1)
                        ->schema([

                            Forms\Components\Select::make('kelas_id')
                                ->label('Kelas')
                                ->relationship('kelas', 'nama')
                                ->nullable()
                                ->helperText('Jika dikosongkan, berlaku untuk semua kelas.'),

                            Forms\Components\Toggle::make('send_whatsapp')
                                ->label('Broadcast WhatsApp')
                                ->helperText('Kirim pengumuman ke WhatsApp wali/siswa'),
                        ]),
                ]),

            // CREATED BY
            Forms\Components\Hidden::make('created_by')
                ->default(fn () => auth()->id()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('kelas.nama')
                    ->label('Kelas')
                    ->default('Semua Kelas'),

                Tables\Columns\IconColumn::make('is_pinned')
                    ->label('Pinned')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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