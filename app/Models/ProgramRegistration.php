<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramRegistration extends Model
{
    protected $table = 'program_registrations';
    protected $primaryKey = 'registration_id';
    public $timestamps = false; // Only registered_at in schema

    protected $fillable = [
        'registration_number',
        'program_id',
        'lab_id',
        'sample_quantity',
        'shipping_address',
        'billing_address',
        'referral_id',
        'discount_applied',
        'status',
        'registered_at',
    ];

    public function lab()
    {
        return $this->belongsTo(Lab::class, 'lab_id', 'lab_id');
    }

    public function program()
    {
        return $this->belongsTo(PtProgram::class, 'program_id', 'program_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'registration_id', 'registration_id');
    }

    public function sample()
    {
        return $this->hasOne(Sample::class, 'registration_id', 'registration_id');
    }

    public function observations()
    {
        return $this->hasMany(Observation::class, 'registration_id', 'registration_id');
    }

    public function registeredParameters()
    {
        return $this->belongsToMany(
            ProgramParameter::class,
            'registration_parameters',
            'registration_id',
            'parameter_id',
            'registration_id',
            'parameter_id'
        );
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'registration_id', 'registration_id');
    }
}
