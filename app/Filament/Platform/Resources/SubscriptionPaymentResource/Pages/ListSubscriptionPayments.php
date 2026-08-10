<?php

namespace App\Filament\Platform\Resources\SubscriptionPaymentResource\Pages;

use App\Filament\Platform\Resources\SubscriptionPaymentResource;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptionPayments extends ListRecords
{
    protected static string $resource = SubscriptionPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
