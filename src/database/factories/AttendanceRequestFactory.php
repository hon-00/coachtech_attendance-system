<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceRequest;

class AttendanceRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = AttendanceRequest::class;

    public function definition()
    {
        return [
            'attendance_id' => null,
            'user_id'       => null,
            'clock_in'      => $this->faker->dateTimeThisMonth(),
            'clock_out'     => $this->faker->dateTimeThisMonth(),
            'breaks'        => [],
            'note'          => $this->faker->sentence(),
            'status'        => AttendanceRequest::STATUS_PENDING,
        ];
    }
}