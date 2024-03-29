<?php

namespace Database\Factories;

use App\Models\permission_role;
use Illuminate\Database\Eloquent\Factories\Factory;

class permission_roleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = permission_role::class;

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
