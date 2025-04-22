<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campaign_id' => \App\Models\Campaign::factory(),
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'phone_number' => $this->faker->e164PhoneNumber,
        ];
    }
}
