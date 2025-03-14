<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppointmentField extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_type_id',
        'user_id',
        'name',
        'type',
        'required',
        'options',
        'order',
        'active'
    ];

    protected $casts = [
        'required' => 'boolean',
        'active' => 'boolean',
        'options' => 'array'
    ];

    public function businessType()
    {
        return $this->belongsTo(BusinessType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fieldValues()
    {
        return $this->hasMany(AppointmentFieldValue::class);
    }
}
