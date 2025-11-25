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
            return '0:00';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return sprintf('%d:%02d', $hours, $minutes);
    }

}