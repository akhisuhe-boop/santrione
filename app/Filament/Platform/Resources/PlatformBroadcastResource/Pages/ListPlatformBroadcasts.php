<?php

namespace App\Filament\Platform\Resources\PlatformBroadcastResource\Pages;

use App\Filament\Platform\Resources\PlatformBroadcastResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlatformBroadcasts extends ListRecords
{
    protected static string $resource = PlatformBroadcastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Kirim Broadcast Baru'),
        ];
    }
}
