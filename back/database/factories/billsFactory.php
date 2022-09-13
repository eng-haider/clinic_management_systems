<?php

namespace Database\Factories;

use App\Models\bills;
use Illuminate\Database\Eloquent\Factories\Factory;

class billsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = bills::class;

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
