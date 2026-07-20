<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

    public function plan()
    {
        return $this->hasOne(PtPlan::class, 'program_id', 'program_id');
    }

    public function creator()
    {
        return $this->belongsTo(AdminUser::class, 'created_by', 'admin_id');
    }

    /**
     * Compute automated registration window status (Upcoming / Active / Closed)
     */
    public function getComputedRegistrationStatusAttribute()
    {
        // If manually marked closed or completed
        if ($this->registration_status === 'closed' || $this->program_status === 'closed' || $this->program_status === 'completed') {
            return 'closed';
        }

        $today = Carbon::today();
        $startDate = $this->registration_start_date ? Carbon::parse($this->registration_start_date) : null;
        $endDate = $this->registration_end_date ? Carbon::parse($this->registration_end_date) : null;

        if ($startDate && $today->lt($startDate)) {
            return 'upcoming';
        }

        if ($endDate && $today->gt($endDate)) {
            return 'closed';
        }

        return 'active';
    }

    /**
     * Check if registration is currently open for participants
     */
    public function isRegistrationOpen()
    {
        return $this->computed_registration_status === 'active';
    }
}
