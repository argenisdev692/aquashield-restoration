<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Src\Modules\FilesEsx\Infrastructure\Persistence\Eloquent\Models\FileEsxEloquentModel;

/**
 * @extends Factory<FileEsxEloquentModel>
 */
final class FileEsxEloquentModelFactory extends Factory
{
    protected $model = FileEsxEloquentModel::class;

    public function definition(): array
    {
        return [
            'uuid'        => Str::uuid()->toString(),
            'file_name'   => $this->faker->word() . '.pdf',
            'file_path'   => 'uploads/' . $this->faker->uuid() . '.pdf',
            'uploaded_by' => UserEloquentModel::factory(),
        ];
    }
}
