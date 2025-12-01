<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    const STATUS_NEW = 0;
    const STATUS_WORKING = 1;
    const STATUS_BREAK = 2;
    const STATUS_LEAVE = 3;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'status'
    ];

    protected $casts = [
        'clock_in'  => 'datetime',
        'clock_out' => 'datetime',
        'work_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakLogs()
    {
        return $this->hasMany(BreakLog::class);
    }

    public function getTotalBreakSecondsAttribute()
    {
        return $this->breakLogs
                    ->filter(fn($log) => $log->break_end)
                    ->sum(function($log){
                        return $log->break_end->diffInSeconds($log->break_start);
                    });
    }

    public function getFormattedBreakTotalAttribute()
    {
        $seconds = $this->total_break_seconds;

        if ($seconds <= 0) {
            return null;
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return sprintf('%d:%02d', $hours, $minutes);
    }

    public function getWorkMinutesAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        $breakMinutes = intdiv($this->total_break_seconds, 60);

        return $this->clock_out->diffInMinutes($this->clock_in) - $breakMinutes;
    }

    public function getFormattedWorkTotalAttribute()
    {
        $minutes = $this->work_minutes;

        if ($minutes <= 0) {
            return null;
        }

        $hours = intdiv($minutes, 60);
        $mins  = $minutes % 60;

        return sprintf('%d:%02d', $hours, $mins);
    }

}