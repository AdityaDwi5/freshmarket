<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Pesanan'),
            'proses' => Tab::make('Proses')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'proses');
                }),
            'pending' => Tab::make('Belum Dibayar')
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->join('transactions', 'transactions.order_id', '=', 'orders.id')
                        ->where('transactions.payment_status', 'pending')
                        ->select('orders.*');
                }),
            'paid' => Tab::make('Sudah Dibayar')
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->join('transactions', 'transactions.order_id', '=', 'orders.id')
                        ->where('transactions.payment_status', 'verified')
                        ->select('orders.*');
                }),
            'dikirim' => Tab::make('Dikirim')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'dikirim');
                }),
            'selesai' => Tab::make('Selesai')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'selesai');
                }),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
