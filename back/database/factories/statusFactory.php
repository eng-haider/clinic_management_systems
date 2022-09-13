<?php

namespace Database\Factories;

use App\Models\status;
use Illuminate\Database\Eloquent\Factories\Factory;

class statusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = status::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
           'name' => $this->faker->name,
        ];
    }
}
