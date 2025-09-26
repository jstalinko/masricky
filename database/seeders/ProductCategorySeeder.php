<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $categories = [
            'Software License',
            'E-Book',
            'Game Account',
            'Voucher',
            'Premium Subscription'
        ];

        foreach ($categories as $catName) {
            $category = Category::create([
                'name'        => $catName,
                'description' => $faker->sentence(),
                'slug'        => Str::slug($catName),
                'active'      => true,
            ]);

            // Generate beberapa produk per kategori
            for ($i = 0; $i < 5; $i++) {
                $type = $faker->randomElement(['single', 'mass']);
                $status = $faker->randomElement(['ready', 'sold']);

                if ($type === 'single') {
                    $content = "Email: {$faker->safeEmail()} | Pass: {$faker->password()}";
                    $stock = 1;
                    $unlimited_stock = false;
                } else {
                    $content = "License: " . strtoupper(Str::random(4)) . "_" . strtoupper(Str::random(5)) . "_" . strtoupper(Str::random(4)) . "_" . strtoupper(Str::random(3));
                    $stock = 10;
                    $unlimited_stock = $faker->boolean(20); // 20% kemungkinan unlimited
                }

                Product::create([
                    'category_id'      => $category->id,
                    'name'             => $faker->words(3, true),
                    'slug'             => Str::slug($faker->unique()->words(3, true)),
                    'price'            => $faker->numberBetween(100000, 5000000),
                    'type'             => $type,
                    'content'          => $content,
                    'status'           => $status,
                    'unlimited_stock'  => $unlimited_stock,
                    'stock'            => $stock,
                    'active'           => true,
                ]);
            }
        }
    }
}
