<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PtProgram extends Model
{
    protected $table = 'pt_programs';
    protected $primaryKey = 'program_id';

    protected $fillable = [
        'program_code',
        'program_name',
        'discipline',
        'scheme_code',
        'description',
        'program_fee',
        'registration_start_date',
        'registration_end_date',
        'dispatch_date',
        'submission_deadline',
        'report_date',
        'registration_status',
        'program_status',
        'created_by',
    ];

    public function parameters()
    {
        return $this->hasMany(ProgramParameter::class, 'program_id', 'program_id');
    }

    public function registrations()
    {
        return $this->hasMany(ProgramRegistration::class, 'program_id', 'program_id');
    }

    public function creator()
    {
        return $this->belongsTo(AdminUser::class, 'created_by', 'admin_id');
    }
}
