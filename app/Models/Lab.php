<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
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

    public function registrations()
    {
        return $this->hasMany(ProgramRegistration::class, 'lab_id', 'lab_id');
    }
}
