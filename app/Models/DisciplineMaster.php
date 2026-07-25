<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisciplineMaster extends Model
{
    protected $table = 'discipline_masters';
    public $timestamps = false;

    protected $fillable = [
        'discipline_name',
        'short_code',
        'created_at',
    ];

    public function parameters()
    {
        return $this->hasMany(ParameterMaster::class, 'discipline_id', 'id');
    }

    public function programs()
    {
        return $this->hasMany(PtProgram::class, 'discipline_id', 'id');
    }
}
