<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
{
    return [
        'code' => $this->faker->unique()->randomNumber(6),
        'product_name' => $this->faker->word(),
        'imported_t' => now(),
        'status' => 'published',
    ];
}

}
