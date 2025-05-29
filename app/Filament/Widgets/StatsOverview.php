<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget\Chart;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $customerCount = User::whereHas('role', function($query) {
            $query->where('name', 'customer');
        })->count();

        // Transaksi hari ini
        $today = Carbon::today();
        $transactionsToday = Order::whereDate('created_at', $today)->get();
        $transactionCountToday = $transactionsToday->count();
        $totalSalesToday = $transactionsToday->sum('total_price');
        $totalDiscountToday = $transactionsToday->sum(function ($order) {
            return $order->orderItems->sum('discount');
        });

        return [
            Stat::make('Customer', $customerCount)
                ->icon('heroicon-o-users')
                ->color('primary'),
            Stat::make('Transaksi Hari Ini', $transactionCountToday)
                ->icon('heroicon-o-credit-card')
                ->color('success'),
            Stat::make('Total Penjualan Hari Ini', 'Rp ' . number_format($totalSalesToday, 0, ',', '.'))
                ->icon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Diskon Hari Ini', 'Rp ' . number_format($totalDiscountToday, 0, ',', '.'))
                ->icon('heroicon-o-percent-badge')
                ->color('warning'),
        ];
    }
}
