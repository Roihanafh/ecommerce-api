<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categoryIds = Category::pluck('id');

        Product::factory(10)->create([
            'category_id' => fn () => $categoryIds->random(),
        ]);
    }
}
