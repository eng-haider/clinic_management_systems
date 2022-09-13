<?php

namespace Database\Factories;

use App\Models\patients;
use Illuminate\Database\Eloquent\Factories\Factory;

class patientsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = patients::class;

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
