<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sample extends Model
{
    protected $table = 'samples';
    protected $primaryKey = 'sample_id';
    public $timestamps = false; // Only created_at in schema

    protected $fillable = [
        'sample_code',
        'program_id',
        'registration_id',
        'batch_id',
        'qr_code',
        'status',
        'created_at',
    ];

    public function program()
    {
        return $this->belongsTo(PtProgram::class, 'program_id', 'program_id');
    }

    public function registration()
    {
        return $this->belongsTo(ProgramRegistration::class, 'registration_id', 'registration_id');
    }

    public function batch()
    {
        return $this->belongsTo(SampleBatch::class, 'batch_id', 'batch_id');
    }

    public function dispatches()
    {
        return $this->hasMany(Dispatch::class, 'sample_id', 'sample_id');
    }

    public function dispatch()
    {
        return $this->hasOne(Dispatch::class, 'sample_id', 'sample_id');
    }
}
