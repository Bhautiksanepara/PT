<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParameterMaster extends Model
{
    protected $table = 'parameter_masters';
    public $timestamps = false;

    protected $fillable = [
        'discipline_id',
        'parameter_name',
        'test_method',
        'unit',
        'created_at',
    ];

    public function discipline()
    {
        return $this->belongsTo(DisciplineMaster::class, 'discipline_id', 'id');
    }
}
