<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Student,
            'identifier' => fake()->numerify('##########'),
            'locale' => 'id',
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::SuperAdmin,
            'campus_id' => null,
        ]);
    }

    public function campusAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::CampusAdmin,
        ]);
    }

    public function lecturer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Lecturer,
            'identifier' => fake()->numerify('00##########'), // NIDN format
        ]);
    }

    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Student,
            'identifier' => fake()->numerify('22########'), // NIM format
        ]);
    }

    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Staff,
            'identifier' => fake()->numerify('19##########'), // NIP format
        ]);
    }

    public function auditor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Auditor,
        ]);
    }

    public function industryMentor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::IndustryMentor,
        ]);
    }
}
