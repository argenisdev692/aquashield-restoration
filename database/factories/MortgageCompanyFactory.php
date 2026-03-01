<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\MortgageCompanies\Infrastructure\Persistence\Eloquent\Models\MortgageCompanyEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\MortgageCompanies\Infrastructure\Persistence\Eloquent\Models\MortgageCompanyEloquentModel>
 */
class MortgageCompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = MortgageCompanyEloquentModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'mortgage_company_name' => fake()->company() . ' Mortgage',
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'website' => fake()->url(),
            'user_id' => UserEloquentModel::factory(),
        ];
    }
}
