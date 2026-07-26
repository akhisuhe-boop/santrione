<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;

use Filament\Forms;
use Filament\Forms\Form;

use Filament\Resources\Resource;

use Filament\Tables;
use Filament\Tables\Table;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

use Illuminate\Support\Facades\Hash;

use Spatie\Permission\Models\Role;

class UserResource extends BaseResource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Master Setting';
    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-users';

    /**
     * User model sengaja TIDAK pakai trait BelongsToTenant (lihat
     * catatan di App\Models\User) — jadi scoping tenant untuk resource
     * ini dilakukan manual di sini. Sebelum ini, resource Pengguna
     * tidak ter-scope SAMA SEKALI: siapapun yang login bisa lihat user
     * dari SEMUA yayasan lain. Ini menutup celah itu.
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->is_platform_admin) {

            $tenant = \Filament\Facades\Filament::getTenant();

            return $tenant
                ? $query->where('yayasan_id', $tenant->id)
                : $query;
        }

        if (empty($user->yayasan_id)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('yayasan_id', $user->yayasan_id);
    }

public static function form(Form $form): Form
{
    return $form
        ->schema([

            Forms\Components\Section::make('Data Pengguna')

                ->description('Informasi akun pengguna dan hak akses')

                ->schema([

                    TextInput::make('name')
                        ->label('Nama')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),

                    TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->revealable()
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(
                            fn (string $operation): bool =>
                            $operation === 'create'
                        )
                        ->dehydrateStateUsing(
                            fn ($state) =>
                            filled($state)
                                ? Hash::make($state)
                                : null
                        ),

                    Select::make('roles')
                        ->label('Role')
                        ->relationship(
                            'roles',
                            'name',
                            fn ($query) => auth()->user()?->is_platform_admin
                                ? $query
                                : $query->where(function ($q) {
                                    $q->whereNull('yayasan_id');

                                    if ($tenant = \Filament\Facades\Filament::getTenant()) {
                                        $q->orWhere('yayasan_id', $tenant->id);
                                    }
                                })
                                // "super_admin" itu nama role SPESIAL bagi
                                // Shield — otomatis bypass SEMUA permission
                                // tanpa terkecuali, apapun isi centangannya.
                                // Jangan sampai tenant biasa bisa assign
                                // role ini ke staff-nya sendiri.
                                ->where('name', '!=', 'super_admin')
                        )
                        ->multiple(false)
                        ->preload()
                        ->searchable()
                        ->required(),

                ])

                ->columns(2)

                ->collapsible(),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i'),

            ])
            ->filters([
                //
            ])
            ->actions([

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

            'index' => Pages\ListUsers::route('/'),

            'create' => Pages\CreateUser::route('/create'),

            'edit' => Pages\EditUser::route('/{record}/edit'),

        ];
    }
}