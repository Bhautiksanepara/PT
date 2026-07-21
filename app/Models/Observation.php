<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Observation extends Model
{
    protected $table = 'observations';
    protected $primaryKey = 'observation_id';

    protected $fillable = [
        'sample_id',
        'registration_id',
        'lab_id',
        'parameter_id',
        'test_method',
        'result_value',
        'unit',
        'uncertainty',
        'remarks',
        'is_locked',
        'submitted_at',
    ];

    public function sample()
    {
        return $this->belongsTo(Sample::class, 'sample_id', 'sample_id');
    }

    public function registration()
    {
        return $this->belongsTo(ProgramRegistration::class, 'registration_id', 'registration_id');
    }

    public function lab()
    {
        return $this->belongsTo(Lab::class, 'lab_id', 'lab_id');
    }

    public function parameter()
    {
        return $this->belongsTo(ProgramParameter::class, 'parameter_id', 'parameter_id');
    }

    public function files()
    {
        return $this->hasMany(ObservationFile::class, 'observation_id', 'observation_id');
    }
}
