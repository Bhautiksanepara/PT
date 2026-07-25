<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchParameterReferenceValue extends Model
{
    protected $table = 'batch_parameter_reference_values';
    public $timestamps = false;

    protected $fillable = [
        'batch_id',
        'program_id',
        'parameter_id',
        'replicate_number',
        'reference_value',
        'created_at',
    ];

    public function batch()
    {
        return $this->belongsTo(SampleBatch::class, 'batch_id', 'batch_id');
    }

    public function program()
    {
        return $this->belongsTo(PtProgram::class, 'program_id', 'program_id');
    }

    public function parameter()
    {
        return $this->belongsTo(ProgramParameter::class, 'parameter_id', 'parameter_id');
    }
}
