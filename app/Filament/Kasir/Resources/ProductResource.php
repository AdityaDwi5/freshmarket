<?php

namespace App\Filament\Kasir\Resources;

use App\Filament\Kasir\Resources\ProductResource\Pages;
use App\Filament\Kasir\Resources\ProductResource\RelationManagers;
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

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Daftar Produk';
    protected static ?string $pluralModelLabel = 'Daftar Produk';
    protected static ?string $modelLabel  = 'Produk';
    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Nama Produk')
                    ->validationMessages([
                        'required' => 'Nama Produk wajib diisi',
                    ]),
                Forms\Components\TextInput::make('product_code')
                    ->nullable()
                    ->label('Kode Produk'),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->label('Deskripsi'),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp.')
                    ->label('Harga Produk')
                    ->validationMessages([
                        'required' => 'Harga Produk wajib diisi',
                        'numeric' => 'Harga Produk harus berupa angka',
                    ]),
                Forms\Components\TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->rule('gte:0')
                    ->validationMessages([
                        'required' => 'Stok wajib diisi',
                        'numeric' => 'Stok harus berupa angka',
                        'gte' => 'Stok tidak boleh kurang dari 0',
                    ])
                    ->label('Stok'),
                Forms\Components\TextInput::make('threshold')
                    ->required()
                    ->numeric()
                    ->rule('gte:0')
                    ->validationMessages([
                        'required' => 'Batas Stok menipis wajib diisi',
                        'numeric' => 'Batas Stok menipis harus berupa angka',
                        'gte' => 'Batas Stok menipis tidak boleh kurang dari 0',
                    ])
                    ->default(10)
                    ->label('Batas Stok menipis'),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->label('Kategori'),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('products')
                    ->validationMessages([
                        'image' => 'File harus berupa gambar',
                    ])
                    ->label('Gambar Produk'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Nama Produk'),
                Tables\Columns\TextColumn::make('product_code')
                    ->searchable()
                    ->label('Kode Produk'),
                Tables\Columns\TextColumn::make('price')
                    ->money('Rp.')
                    ->sortable()
                    ->label('Harga Produk'),
                Tables\Columns\TextColumn::make('reviews.rating')
                    ->sortable()
                    ->label('Rating Produk'),
                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->color(function ($record) {
                        return $record->stock < $record->threshold ? 'danger' : null; // Merah jika stok < threshold
                    })
                    ->badge(function ($record) {
                        return $record->stock < $record->threshold; // Tampilkan badge jika stok < threshold
                    })
                    ->sortable()
                    ->label('Stok'),
                Tables\Columns\TextColumn::make('threshold')
                    ->sortable()
                    ->label('Batas stok menipis'),
                Tables\Columns\TextColumn::make('category.name')
                    ->numeric()
                    ->sortable()
                    ->label('Kategori'),
                Tables\Columns\ImageColumn::make('image')
                    ->disk('public')
                    ->label('Gambar Produk'),
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
                Tables\Filters\Filter::make('Stok Menipis')
                    ->query(function (Builder $query) {
                        $query->whereColumn('stock', '<', 'threshold');
                    }),
                Tables\Filters\Filter::make('Stok Aman')
                    ->query(function (Builder $query) {
                        $query->whereColumn('stock', '>=', 'threshold');
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            RelationManagers\ReviewsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
