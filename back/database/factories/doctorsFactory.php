<?php

namespace Database\Factories;

use App\Models\doctors;
use Illuminate\Database\Eloquent\Factories\Factory;

class doctorsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = doctors::class;

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
