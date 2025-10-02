<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                   ->label('Category')
                    ->required()
                    ->options(function () {
                        return \App\Models\Category::where('active', true)->pluck('name', 'id');
                    }),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\Select::make('type')
                ->options(['mass' => 'Produk Massal', 'single' => 'Produk Sekali Pakai'])
                    ->required(),
                Forms\Components\RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options(['ready' => 'Ready', 'sold' => 'Sold']),
                Forms\Components\Toggle::make('unlimited_stock')
                    ->required(), 
                Forms\Components\Toggle::make('active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money(currency:'IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        'single' => 'primary',
                        'mass' => 'warning',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        'ready' => 'success',
                        'sold' => 'danger',
                        default => 'secondary',
                    }),
            
                Tables\Columns\ToggleColumn::make('active')
                    ,
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
