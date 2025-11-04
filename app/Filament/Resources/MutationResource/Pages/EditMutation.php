<?php

namespace App\Filament\Resources\MutationResource\Pages;

use App\Filament\Resources\MutationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMutation extends EditRecord
{
    protected static string $resource = MutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
