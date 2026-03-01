<?php

namespace Database\Factories;

use App\Models\AllianceCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AllianceCompany>
 */
class AllianceCompanyFactory extends Factory
{
    protected $model = AllianceCompany::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'alliance_company_name' => $this->faker->company() . ' Alliance',
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('##########'),
            'address' => $this->faker->address(),
            'website' => $this->faker->url(),
            'user_id' => User::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
