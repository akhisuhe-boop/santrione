<?php

namespace App\Filament\Resources\PayrollResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AdjustmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'adjustments';

    protected static ?string $title = 'Adjustment Payroll';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('tipe')
                    ->options([
                        'tambahan' => 'Tambahan',
                        'potongan' => 'Potongan',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('nama_komponen')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('qty')
                    ->numeric()
                    ->default(1)
                    ->required(),

                Forms\Components\TextInput::make('nominal')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Forms\Components\Placeholder::make('subtotal_preview')
                    ->label('Subtotal')
                    ->content(function ($get) {

                        $qty = (int) ($get('qty') ?? 0);
                        $nominal = (int) ($get('nominal') ?? 0);

                        return 'Rp '
                            . number_format(
                                $qty * $nominal,
                                0,
                                ',',
                                '.'
                            );
                    }),

                Forms\Components\Textarea::make('catatan')
                    ->rows(3)
                    ->columnSpanFull(),

            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\BadgeColumn::make('tipe')
                    ->colors([
                        'success' => 'tambahan',
                        'danger' => 'potongan',
                    ]),

                Tables\Columns\TextColumn::make('nama_komponen')
                    ->searchable(),

                Tables\Columns\TextColumn::make('qty'),

                Tables\Columns\TextColumn::make('nominal')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('subtotal')
                    ->money('IDR')
                    ->weight('bold'),

            ])

            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}