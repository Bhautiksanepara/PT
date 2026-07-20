<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PtPlan extends Model
{
    protected $table = 'pt_plans';
    protected $primaryKey = 'plan_id';
    public $timestamps = false; // Only created_at in schema

    protected $fillable = [
        'program_id',
        'program_number',
        'material',
        'timeline',
        'assigned_coordinator',
        'sample_quantity',
        'sample_preparation_instructions',
        'created_at',
    ];

    public function program()
    {
        return $this->belongsTo(PtProgram::class, 'program_id', 'program_id');
    }

    public function coordinator()
    {
        return $this->belongsTo(AdminUser::class, 'assigned_coordinator', 'admin_id');
    }
}
