<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabSignupVerification extends Model
{
    protected $fillable = [
        'contact_person',
        'laboratory_name',
        'center_name',
        'email',
        'address',
        'city',
        'state',
        'country',
        'pin_code',
        'mobile_number',
        'otp_hash',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}
