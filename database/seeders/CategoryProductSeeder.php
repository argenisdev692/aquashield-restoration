<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryProduct;
use Ramsey\Uuid\Uuid;

class CategoryProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoriesProducts = [
            'Material Removal',
            'Consumible',
            'Content Movement',
            'Administrative',
            'PPE',
            'Equipment',
            'Products',
            'Services'
        ];

        foreach ($categoriesProducts as $category) {
            CategoryProduct::create([
                'uuid' => Uuid::uuid4()->toString(),
                'category_product_name' => $category,
            ]);
        }
    }
}
