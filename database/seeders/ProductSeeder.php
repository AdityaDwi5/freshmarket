<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Keripik Singkong',
            'product_code' => 'KS-001',
            'description' => 'Keripik singkong gurih, cocok untuk cemilan',
            'price' => 10000,
            'stock' => 50,
            'category_id' => '1',
        ]);

        // Minuman
        Product::create([
            'name' => 'Teh Botol Sosro',
            'product_code' => 'TB-001',
            'description' => 'Teh botol siap minum, menyegarkan',
            'price' => 5000,
            'stock' => 30,
            'category_id' => '2',
        ]);

        // Kebutuhan Rumah Tangga
        Product::create([
            'name' => 'Sabun Cuci Piring',
            'product_code' => 'SCP-001',
            'description' => 'Sabun cuci piring dengan wangi segar',
            'price' => 15000,
            'stock' => 54,
            'category_id' => '3',
        ]);

        // Bumbu Masak
        Product::create([
            'name' => 'Saus Sambal ABC',
            'product_code' => 'SS-001',
            'description' => 'Saus sambal dengan rasa pedas yang pas',
            'price' => 8000,
            'stock' => 20,
            'category_id' => '4',
        ]);

        // Produk Kebersihan
        Product::create([
            'name' => 'Detergen',
            'product_code' => 'DT-001',
            'description' => 'Detergen serbaguna, efektif menghilangkan noda',
            'price' => 25000,
            'stock' => 30,
            'category_id' => 5,
        ]);
    }
}
