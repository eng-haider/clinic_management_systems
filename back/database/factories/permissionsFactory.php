<?php

namespace Database\Factories;

use App\Models\permissions;
use Illuminate\Database\Eloquent\Factories\Factory;

class permissionsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = permissions::class;

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
