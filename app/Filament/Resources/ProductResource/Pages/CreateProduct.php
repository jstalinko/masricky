<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use Illuminate\Support\Str;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ProductResource;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = Str::slug($data['name']);
        $data['stock'] = $data['type'] == 'mass' ? 0 : count($data['content']);
        $data['unlimited_stock'] = ($data['type'] == 'mass') ? true : false;
        return $data;
    }
}
