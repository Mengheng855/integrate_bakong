<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Coffee',       'price' => 0.1,  'currency' => 'USD', 'description' => 'Hot brewed coffee',        'image' => '☕'],
            ['name' => 'Fried Rice',   'price' => 0.1,  'currency' => 'USD', 'description' => 'Cambodian fried rice',      'image' => '🍚'],
            ['name' => 'Baguette',     'price' => 0.75,  'currency' => 'USD', 'description' => 'Fresh baked baguette',      'image' => '🥖'],
            ['name' => 'Fruit Shake',  'price' => 2.00,  'currency' => 'USD', 'description' => 'Mixed tropical fruits',     'image' => '🥤'],
            ['name' => 'Noodle Soup',  'price' => 2.50,  'currency' => 'USD', 'description' => 'Traditional noodle soup',   'image' => '🍜'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}