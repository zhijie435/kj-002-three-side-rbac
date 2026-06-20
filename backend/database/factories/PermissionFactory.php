<?php

namespace Database\Factories;

use App\Enums\GuardType;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $groups = ['user', 'role', 'permission', 'order', 'product', 'inventory', 'system', 'dashboard'];

        return [
            'name' => $this->faker->unique()->word(),
            'guard' => $this->faker->randomElement(GuardType::values()),
            'display_name' => $this->faker->word(),
            'group' => $this->faker->randomElement($groups),
            'description' => $this->faker->sentence(),
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

    public function group(string $group): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => $group,
        ]);
    }
}
