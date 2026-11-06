<?php

namespace Plugins\Nokia\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\Nokia\Models\NokiaRom;

/**
 * @extends Factory<NokiaRom>
 */
class NokiaRomFactory extends Factory
{
    /**
     * The model the factory creates.
     */
    protected $model = NokiaRom::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('(##) #####-####'),
        ];
    }
}
