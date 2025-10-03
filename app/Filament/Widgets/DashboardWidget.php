<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardWidget extends BaseWidget
{

    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', \App\Models\User::count())
                ->description('Jumlah user terdaftar')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary')
                ->icon('heroicon-o-users'),

            Stat::make('Total Products', \App\Models\Product::count())
                ->description('Jumlah produk tersedia')
                ->descriptionIcon('heroicon-o-shopping-bag')
                ->color('success')
                ->icon('heroicon-o-shopping-bag'),
            Stat::make('Total Categories', \App\Models\Category::count())
                ->description('Jumlah kategori')
                ->descriptionIcon('heroicon-o-tag')
                ->color('info')
                ->icon('heroicon-o-tag'),
            Stat::make(
                'Total Revenue',
                'Rp ' . number_format(
                    \App\Models\Order::where('status', 'PAID')->sum('total'),
                    0,
                    ',',
                    '.'
                )
            )
                ->description('Total pendapatan')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),


            Stat::make('PAID ORDERS', \App\Models\Order::where('status', 'PAID')->count())
                ->description('Jumlah order sudah dibayar')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->icon('heroicon-o-check-circle'),
            Stat::make('PENDING ORDERS', \App\Models\Order::where('status', 'PENDING')->count())
                ->description('Jumlah order belum dibayar')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning')
                ->icon('heroicon-o-clock'),
            Stat::make('CANCELLED ORDERS', \App\Models\Order::where('status', 'CANCELLED')->count())
                ->description('Jumlah order dibatalkan')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger')
                ->icon('heroicon-o-x-circle'),
                Stat::make('EXPIRED ORDERS', \App\Models\Order::where('status', 'EXPIRED')->count())
                ->description('Jumlah order dibatalkan')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger')
                ->icon('heroicon-o-x-circle'),
            
            Stat::make('Total Orders', \App\Models\Order::count())
                ->description('Jumlah order masuk')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('warning')
                ->icon('heroicon-o-shopping-cart'),
        ];
    }
}
