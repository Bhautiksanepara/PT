<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramParameter extends Model
{
    protected $table = 'program_parameters';
    protected $primaryKey = 'parameter_id';
    public $timestamps = false; // Only created_at in schema

    protected $fillable = [
        'program_id',
        'parameter_name',
        'test_method',
        'unit',
        'created_at',
    ];

    public function program()
    {
        return $this->belongsTo(PtProgram::class, 'program_id', 'program_id');
    }

    public function observations()
    {
        return $this->hasMany(Observation::class, 'parameter_id', 'parameter_id');
    }
}
