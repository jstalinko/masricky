<?php

namespace App\Filament\Resources\MutationResource\Pages;

use App\Filament\Resources\MutationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMutation extends CreateRecord
{
    protected static string $resource = MutationResource::class;
}
