<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class ProductRating extends BaseWidget
{

    public ?Product $record;
    protected function getStats(): array
    {

        $averageRating =$this->record->reviews()->avg('rating');

        return [
            Stat::make('Rating Produk', number_format($averageRating, 1))
                ->description($averageRating == 0 ? 'Belum ada ulasan untuk produk ini' : 'Rata-rata ulasan untuk produk ini')
                ->descriptionIcon('heroicon-o-star')
                ->color($averageRating > 3 ? 'success' : ($averageRating > 0 ? 'warning' : 'danger')),
        ];
    }
}
