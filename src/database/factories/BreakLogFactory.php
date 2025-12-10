<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BreakLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $start = $this->faker->time('H:i:s', '12:00:00');
        $end = $this->faker->time('H:i:s', '13:00:00');

        return [
            'attendance_id' => \App\Models\Attendance::factory(),
            'break_start' => $start,
            'break_end' => $end,
        ];
    }
}
