<?php

namespace Database\Factories;

use App\Enums\GuardType;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'guard' => $this->faker->randomElement(GuardType::values()),
            'display_name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'is_system' => false,
            'status' => true,
            'sort_order' => 0,
        ];
    }

    public function platform(): static
    {
        return $this->state(fn (array $attributes) => [
            'guard' => GuardType::PLATFORM->value,
        ]);
    }

    public function merchant(): static
    {
        return $this->state(fn (array $attributes) => [
            'guard' => GuardType::MERCHANT->value,
        ]);
    }

    public function warehouse(): static
    {
        return $this->state(fn (array $attributes) => [
            'guard' => GuardType::WAREHOUSE->value,
        ]);
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
