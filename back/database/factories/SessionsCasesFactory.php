<?php

namespace Database\Factories;

use App\Models\SessionsCases;
use Illuminate\Database\Eloquent\Factories\Factory;

class SessionsCasesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SessionsCases::class;

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
