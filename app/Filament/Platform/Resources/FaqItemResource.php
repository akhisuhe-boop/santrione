<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;
use App\Filament\Platform\Resources\FaqItemResource\Pages;
use App\Models\FaqItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class FaqItemResource extends BaseResource
{
    protected static ?string $model = FaqItem::class;
    protected static ?string $navigationGroup = 'Landing Page';
    protected static ?string $navigationLabel = 'FAQ';
    protected static ?string $modelLabel = 'FAQ';
    protected static ?string $pluralModelLabel = 'FAQ';
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canEdit($record = null): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function canDelete($record = null): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Pertanyaan & Jawaban')
                ->schema([
                    Forms\Components\TextInput::make('pertanyaan')->required()->maxLength(255)->columnSpanFull(),
                    Forms\Components\Textarea::make('jawaban')->required()->rows(4)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Tampilan')
                ->schema([
                    Forms\Components\TextInput::make('urutan')->numeric()->default(0),
                    Forms\Components\Toggle::make('is_active')->label('Tampilkan di landing page')->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('urutan')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('pertanyaan')->searchable()->limit(60),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Aktif'),
            ])
            ->defaultSort('urutan')
            ->reorderable('urutan')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFaqItems::route('/'),
            'create' => Pages\CreateFaqItem::route('/create'),
            'edit' => Pages\EditFaqItem::route('/{record}/edit'),
        ];
    }
}
