<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrderWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full'; // biar lebar penuh

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->latest() // order by created_at desc
                    ->with('product') // eager load relasi product
                    ->limit(5) // tampilkan 5 terbaru
            )
            ->columns([
                Tables\Columns\TextColumn::make('payment_id')
                    ->label('Payment ID')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('invoice')
                    ->label('Invoice')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('idr', locale: 'id') // auto format ke Rupiah
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'PAID',
                        'warning' => 'PENDING',
                        'danger'  => 'FAILED',
                    ]),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ]);
    }
}
