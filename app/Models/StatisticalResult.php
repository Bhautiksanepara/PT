<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatisticalResult extends Model
{
    protected $table = 'statistical_results';
    protected $primaryKey = 'stat_id';
    public $timestamps = false;

    protected $fillable = [
        'program_id',
        'parameter_id',
        'mean',
        'median',
        'standard_deviation',
        'robust_mean',
        'robust_standard_deviation',
        'assigned_value',
        'calculated_at',
    ];

    public function program()
    {
        return $this->belongsTo(PtProgram::class, 'program_id', 'program_id');
    }

    public function parameter()
    {
        return $this->belongsTo(ProgramParameter::class, 'parameter_id', 'parameter_id');
    }
}
