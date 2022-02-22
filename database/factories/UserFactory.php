<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->firstName(),
            'fname' => $this->faker->firstName(),
            'sname' => $this->faker->firstName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'group_id' => $this->faker->randomElement(['1', '4']),
            'login_type' => 'student',
            //'title' => $this->faker->randomElement(['доц. д-р', 'гл. ас.', 'проф.', 'инж.', 'маг. инж.']),
            'active' => 1
        ];
    }
}
