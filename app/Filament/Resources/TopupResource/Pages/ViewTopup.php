<?php

namespace App\Filament\Resources\TopupResource\Pages;

use App\Filament\Resources\TopupResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTopup extends ViewRecord
{
    protected static string $resource = TopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
