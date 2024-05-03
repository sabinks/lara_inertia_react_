<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookAppointment extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'dob', 'booking_date_time', 'description', 'status'];

    protected $casts = [
        // 'appointment_date_time' => 'datetime:d/m/Y',
        // 'dob' => 'datetime:d/m/Y',
        'created_at' => 'datetime:d/m/Y',
        'updated_at' => 'datetime:d/m/Y',
    ];
}
