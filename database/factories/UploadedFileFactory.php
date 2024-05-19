<?php

namespace Database\Factories;

use App\Models\Folder;
use App\Models\UploadedFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UploadedFile>
 */
class UploadedFileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UploadedFile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'size' => $this->faker->numberBetween(1000, 10000),
            'path' => $this->faker->word,
            'upload_time' => date('Y-m-d H:i:s'),
            'visibly' => 1,
            'user_id' => User::factory(),
            'folder_id' => Folder::factory()
        ];
    }
}
