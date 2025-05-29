<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Daftar Pesanan';
    protected static ?string $pluralModelLabel = 'Daftar Pesanan';
    protected static ?string $modelLabel = 'Pesanan';

    public static function form(Form $form): Form
    {
        $products = Product::pluck('name', 'id');

        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->nullable()
                    ->label('Customer')
                    ->relationship('user', 'name')
                    ->preload()
                    ->searchable()
                    ->default(auth('web')->id()),

                Forms\Components\TextInput::make('tracking_number')
                    ->label('Tracking Number')
                    ->default(function () {
                        return strtoupper(uniqid('TRK-'));
                    })
                    ->readOnly()
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'proses' => 'Proses',
                        'dikirim' => 'Dikirim',
                        'selesai' => 'Selesai',
                    ])
                    ->default('selesai')
                    ->required(),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Repeater::make('orderItems')
                            ->relationship()
                            ->label('Item')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->options($products)
                                    ->preload()
                                    ->required()
                                    ->searchable()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $product = Product::find($get('product_id'));
                                        $set('price', $product->price);
                                        $set('sub_total', $get('quantity') * $get('price'));
                                    }),
                                Forms\Components\TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $set('sub_total', $get('quantity') * $get('price'));
                                    })
                                    ->default(1),
                                Forms\Components\TextInput::make('price')
                                    ->required()
                                    ->readOnly(),
                                Forms\Components\TextInput::make('sub_total')
                                    ->readOnly()
                                    ->afterStateHydrated(function (Get $get, Set $set) {
                                        $price = $get('price');
                                        $quantity = $get('quantity');

                                        if ($price && $quantity) {
                                            $set('sub_total', $price * $quantity); // Set the calculated sub_total
                                        }
                                    }),

                            ])
                            ->columns(4)
                            ->addAction(function (Get $get, Set $set) {
                                $total = collect($get('orderItems'))->values()->pluck('sub_total')->sum();
                                $set('total_price', $total);
                            })
                    ]),
                Forms\Components\TextInput::make('total_price')
                    ->required()
                    ->readOnly(),

                Forms\Components\Select::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => 'Cash',
                        'midtrans' => 'Midtrans',
                    ])
                    ->default('cash')
                    ->required(),
                Forms\Components\Repeater::make('transaction')
                    ->relationship()
                    ->schema([
                        Forms\Components\Hidden::make('user_id')
                            ->default(auth('web')->id()),
                        Forms\Components\Select::make('payment_status')
                            ->required()
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'failed' => 'Failed',
                            ])
                            ->default('verified'),
                    ])
                    ->addable(false)
                    ->deletable(false)
                    ->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tracking_number')
                    ->label('Tracking Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.role.name')
                    ->label('Role')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Harga')
                    ->money('Rp.')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->sortable(),
                Tables\Columns\TextColumn::make('orderItems.product.name')
                    ->label('Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('orderItems.quantity')
                    ->label('Jumlah')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction.payment_status')
                    ->label('Status Pembayaran'),
                Tables\Columns\TextColumn::make('user.address')
                    ->label('Alamat Pengiriman')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Transaksi')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
