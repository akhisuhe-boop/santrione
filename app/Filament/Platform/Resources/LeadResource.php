<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Resources\BaseResource;
use App\Filament\Platform\Resources\LeadResource\Pages;
use App\Filament\Platform\Resources\LeadResource\RelationManagers\NotesRelationManager;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends BaseResource
{
    protected static ?string $model = Lead::class;
    protected static ?string $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Lead';
    protected static ?string $modelLabel = 'Lead';
    protected static ?string $pluralModelLabel = 'Lead';
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

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
            Forms\Components\Section::make('Data Lead')
                ->schema([
                    Forms\Components\TextInput::make('nama_lembaga')->required()->maxLength(255),
                    Forms\Components\TextInput::make('nama_pic')->label('Nama PIC'),
                    Forms\Components\TextInput::make('email')->email(),
                    Forms\Components\TextInput::make('no_hp')->label('No. HP/WA'),
                    Forms\Components\TextInput::make('sumber')->default('Trial Signup'),
                ])->columns(2),

            Forms\Components\Section::make('Status Follow-up')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options(Lead::STATUSES)
                        ->required()
                        ->default('baru'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_lembaga')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('nama_pic')->label('PIC')->searchable(),
                Tables\Columns\TextColumn::make('no_hp')->label('No. HP')
                    ->url(fn (Lead $record) => $record->no_hp ? 'https://wa.me/'.preg_replace('/\D/', '', $record->no_hp) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Lead::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'baru' => 'warning',
                        'dihubungi' => 'info',
                        'follow_up' => 'warning',
                        'deal' => 'success',
                        'batal' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('sumber')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('notes_count')->label('Catatan')->counts('notes'),
                Tables\Columns\TextColumn::make('created_at')->label('Tanggal Masuk')->dateTime('d M Y, H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(Lead::STATUSES),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            NotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
