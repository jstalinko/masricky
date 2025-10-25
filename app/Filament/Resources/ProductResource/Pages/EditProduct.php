<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

   protected function mutateFormDataBeforeSave(array $data): array
{
    $product = $this->record; // ambil instance produk yang sedang diedit

    $availableKeysCount = is_array($product->getAvailableKeys())
        ? count($product->getAvailableKeys())
        : 0;

    $contentCount = isset($data['content']) && is_array($data['content'])
        ? count($data['content'])
        : 0;

    $data['stock'] = ($availableKeysCount + $contentCount);
    $data['unlimited_stock'] = ($data['type'] === 'mass');

    return $data;
}
    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
