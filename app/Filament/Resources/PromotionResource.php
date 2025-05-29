<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionResource\Pages;
use App\Filament\Resources\PromotionResource\RelationManagers;
use App\Models\Promotion;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Closure;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-percent-badge';

    protected static ?string $navigationLabel = 'Daftar Promosi';
    protected static ?string $pluralModelLabel = 'Daftar Promosi';
    protected static ?string $modelLabel = 'Promosi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Produk')
                    ->placeholder('Pilih Produk')
                    ->required()
                    ->searchable()
                    ->options(Product::pluck('name', 'id'))
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $product = Product::find($state);
                        if ($product) {
                            $set('product_price', $product->price);
                        }
                    })
                    ->afterStateHydrated(function ($state, callable $set) {
                        $product = Product::find($state);
                        if ($product) {
                            $set('product_price', $product->price);
                        }
                    }),
                Forms\Components\Placeholder::make('product_details')
                    ->label('Detail Produk')
                    ->content(function ($get) {
                        $productPrice = $get('product_price');
                        if ($productPrice !== null) {
                            return "Harga: Rp{$productPrice}";
                        }
                        return "Silakan pilih produk untuk melihat detailnya.";
                    }),
                Forms\Components\Select::make('type')
                    ->options(
                        [
                            'percentage' => 'Persen',
                            'fixed' => 'Potongan Harga'
                        ]
                    )
                    ->live()
                    ->required()
                    ->label('Tipe Diskon'),
                Forms\Components\TextInput::make('value')
                    ->label('Diskon')
                    ->required()
                    ->numeric()
                    ->rules([
                        fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                            $type = $get('type');
                            $productId = $get('product_id');

                            if ($type === 'percentage') {
                                if ($value > 100) {
                                    $fail('Nilai diskon persentase tidak boleh lebih dari 100.');
                                }
                            } elseif ($type === 'fixed') {
                                $product = Product::find($productId);
                                if ($product && $value > $product->price) {
                                    $fail('Nilai diskon tidak boleh lebih besar atau sama dengan harga produk.');
                                }
                            }
                        },
                    ]),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal Mulai Diskon')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Tanggal Diskon Berakhir')
                    ->required()
                    ->after('start_date')
                    ->validationMessages([
                        'after' => 'Tanggal akhir diskon harus lebih besar dari tanggal mulai diskon.',
                    ]),
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe Diskon'),
                Tables\Columns\TextColumn::make('value')
                    ->label('Nilai Diskon')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->type === 'percentage') {
                            return $state . '%';
                        }
                        return 'Rp. ' . number_format($state, 0, ',', '.');
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable()
                    ->label('Tanggal Mulai'),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->label('Tanggal Selesai'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Aktif'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
