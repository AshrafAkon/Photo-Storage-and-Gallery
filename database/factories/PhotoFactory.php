<?php

namespace Database\Factories;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Photo>
 */
class PhotoFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Photo::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'title' => $this->faker->name(),
            'file_name' => Photo::random_name(),
            // 'dhash' => $photo->dhash,
            // 'sha256' => $photo->sha256,
            'user_id' => 1,
            // 'height' => $photo->height,
            // 'width' => $photo->width,
            'size' => 100000,
        ];
    }
}
