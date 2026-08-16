<?php

namespace App\Filament\Platform\Resources\LeadResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $title = 'Riwayat / Catatan Follow-up';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('isi')
                ->label('Catatan')
                ->required()
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('isi')
            ->columns([
                Tables\Columns\TextColumn::make('isi')->label('Catatan')->wrap(),
                Tables\Columns\TextColumn::make('dibuat_oleh')->label('Oleh'),
                Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y, H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['dibuat_oleh'] = auth()->user()?->name;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
