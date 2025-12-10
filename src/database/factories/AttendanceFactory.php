<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $clockIn = $this->faker->time('H:i:s', '09:00:00');
        $clockOut = $this->faker->time('H:i:s', '18:00:00');
        $breaks = rand(0, 60);

        return [
            'user_id' => \App\Models\User::factory(),
            'work_date' => $this->faker->date(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => rand(1, 3),
        ];
    }
}
