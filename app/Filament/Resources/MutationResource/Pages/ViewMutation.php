<?php

namespace App\Filament\Resources\MutationResource\Pages;

use App\Filament\Resources\MutationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMutation extends ViewRecord
{
    protected static string $resource = MutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
