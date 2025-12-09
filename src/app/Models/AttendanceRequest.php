<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttendanceRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING  = 4;
    const STATUS_APPROVED = 5;
    const STATUS_REJECTED = 6;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'clock_in',
        'clock_out',
        'breaks',
        'note',
        'status',
    ];

    protected $casts = [
        'clock_in' => 'datetime:H:i',
        'clock_out' => 'datetime:H:i',
        'breaks' => 'array',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING  => '承認待ち',
            self::STATUS_APPROVED => '承認済み',
            self::STATUS_REJECTED => '否認',
            default               => '不明',
        };
    }
}