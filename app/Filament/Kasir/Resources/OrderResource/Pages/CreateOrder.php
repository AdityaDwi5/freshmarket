<?php

namespace App\Filament\Kasir\Resources\OrderResource\Pages;

use App\Filament\Kasir\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

}
