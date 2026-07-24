<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsappSettingResource\Pages;
use App\Models\WhatsappSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;

class WhatsappSettingResource extends BaseResource
{
    protected static ?string $navigationGroup = 'Master Setting';
    protected static ?string $navigationLabel = 'WhatsApp Gateway';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Pengaturan Gateway')
                    ->schema([

                        Forms\Components\Select::make('lembaga_id')
                            ->label('Lembaga')
                            ->relationship(
                                'lembaga',
                                'nama',
                                modifyQueryUsing: fn ($query) => $query->where(
                                    'yayasan_id',
                                    \Filament\Facades\Filament::getTenant()?->id
                                ),
                            )
                            ->required()
                            ->preload()
                            ->searchable(),

                        Forms\Components\TextInput::make('provider')
                            ->default('xSender')
                            ->readOnly(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktifkan Gateway')
                            ->default(true)

                    ])
                    ->columns(2),



                Forms\Components\Section::make('Konfigurasi API xSender')
                    ->schema([

                        Forms\Components\TextInput::make('api_url')
                            ->default('https://xsender.id/id/send-message')
                            ->readOnly(),

                        Forms\Components\TextInput::make('token')
                            ->label('API Key xSender')
                            ->password()
                            ->revealable()
                            ->required(),

                        Forms\Components\TextInput::make('sender')
                            ->label('Nomor WhatsApp Device')
                            ->placeholder('628xxxxxxxx'),

                        Forms\Components\TextInput::make('no_admin_absensi')
                            ->label('Nomor Admin Absensi')
                            ->tel()

                    ])
                    ->columns(2),



                Forms\Components\Section::make('Test Koneksi WhatsApp')
                    ->schema([

                        Forms\Components\TextInput::make('test_number')
                            ->label('Nomor Test')
                            ->placeholder('628xxxxxxxx'),

                        Forms\Components\Textarea::make('test_message')
                            ->label('Pesan Test')
                            ->default('Ini adalah pesan test dari aplikasi absensi'),


                        Forms\Components\Actions::make([

                            Forms\Components\Actions\Action::make('testWhatsapp')
                                ->label('Kirim Test WhatsApp')
                                ->color('primary')

                                ->action(function ($get) {

                                    $number = $get('test_number');
                                    $message = $get('test_message');
                                    $apiKey = $get('token');
                                    $sender = $get('sender');

                                    if(!$number){
                                        \Filament\Notifications\Notification::make()
                                            ->title('Nomor test belum diisi')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    $number = preg_replace('/^0/', '62', $number);

                                    try{

                                        $response = Http::asForm()->post(
                                            'https://xsender.id/id/send-message',
                                            [
                                                'api_key' => $apiKey,
                                                'sender'  => $sender,
                                                'number'  => $number,
                                                'message' => $message
                                            ]
                                        );

                                        if($response->successful()){

                                            \Filament\Notifications\Notification::make()
                                                ->title('Pesan test berhasil dikirim')
                                                ->success()
                                                ->send();

                                        }else{

                                            \Filament\Notifications\Notification::make()
                                                ->title('Gagal mengirim pesan')
                                                ->danger()
                                                ->send();

                                        }

                                    }catch(\Exception $e){

                                        \Filament\Notifications\Notification::make()
                                            ->title('Koneksi ke xSender gagal')
                                            ->danger()
                                            ->send();

                                    }

                                })

                        ])

                    ])

            ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            Tables\Columns\TextColumn::make('lembaga.nama')
                ->label('Lembaga')
                ->badge(),

            Tables\Columns\TextColumn::make('provider')
                ->label('Provider'),

            Tables\Columns\TextColumn::make('sender')
                ->label('No Device'),

            Tables\Columns\TextColumn::make('no_admin_absensi')
                ->label('Nomor Admin Absensi'),

            Tables\Columns\TextColumn::make('api_url')
                ->label('API URL')
                ->limit(30),

            Tables\Columns\IconColumn::make('is_active')
                ->boolean()
                ->label('Aktif')
            ])
        ->actions([
            Tables\Actions\EditAction::make(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListWhatsappSettings::route('/'),
            'create' => Pages\CreateWhatsappSetting::route('/create'),
            'edit' => Pages\EditWhatsappSetting::route('/{record}/edit'),
        ];
    }
}