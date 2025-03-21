<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contact_id',
        'title',
        'start',
        'end',
        'status',
        'is_attended'
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'is_attended' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function fieldValues()
    {
        return $this->hasMany(AppointmentFieldValue::class);
    }
}
