<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create(['name' => 'Makanan Ringan']);
        Category::create(['name' => 'Minuman']);
        Category::create(['name' => 'Kebutuhan Rumah Tangga']);
        Category::create(['name' => 'Bumbu Masak']);
        Category::create(['name' => 'Produk Kebersihan']);
    }
}
