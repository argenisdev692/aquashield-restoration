<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Factories\MortgageCompanyFactory;
use Illuminate\Database\Seeder;

class MortgageCompanySeeder extends Seeder
{
    public function run(): void
    {
        MortgageCompanyFactory::new()->count(10)->create();
    }
}
