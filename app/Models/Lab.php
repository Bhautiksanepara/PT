<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Lab extends Authenticatable
{
    use Notifiable;

    protected $table = 'labs';
    protected $primaryKey = 'lab_id';

    protected $fillable = [
        'laboratory_name',
        'nabl_certificate_number',
        'laboratory_type',
        'gst_number',
        'address',
        'city',
        'state',
        'country',
        'pin_code',
        'contact_person',
        'designation',
        'email',
        'mobile_number',
        'username',
        'password_hash',
        'status',
    ];

    protected $hidden = [
        'password_hash',
    ];

    /**
     * Override default password column name for Laravel Auth.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function registrations()
    {
        return $this->hasMany(ProgramRegistration::class, 'lab_id', 'lab_id');
    }
}
