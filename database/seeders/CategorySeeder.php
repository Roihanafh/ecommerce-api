<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics',    'description' => 'Gadgets, devices, and electronic products'],
            ['name' => 'Fashion',        'description' => 'Clothing, shoes, and accessories'],
            ['name' => 'Home & Living',  'description' => 'Furniture, decor, and household items'],
            ['name' => 'Sports',         'description' => 'Sports equipment and activewear'],
            ['name' => 'Books',          'description' => 'Books, e-books, and educational materials'],
            ['name' => 'Beauty',         'description' => 'Skincare, makeup, and personal care'],
            ['name' => 'Toys',           'description' => 'Toys and games for children'],
            ['name' => 'Automotive',     'description' => 'Car parts and accessories'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name'        => $category['name'],
                    'description' => $category['description'],
                    'is_active'   => true,
                ]
            );
        }

        // Additional random categories
        Category::factory(10)->create();
    }
}
