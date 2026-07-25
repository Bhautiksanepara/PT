<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationParameter extends Model
{
    protected $table = 'registration_parameters';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'registration_id',
        'parameter_id',
    ];

    public function registration()
    {
        return $this->belongsTo(ProgramRegistration::class, 'registration_id', 'registration_id');
    }

    public function parameter()
    {
        return $this->belongsTo(ProgramParameter::class, 'parameter_id', 'parameter_id');
    }
}
