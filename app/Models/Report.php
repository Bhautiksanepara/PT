<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'reports';
    protected $primaryKey = 'report_id';
    public $timestamps = false;

    protected $fillable = [
        'report_type',
        'program_id',
        'registration_id',
        'parameter_id',
        'file_path',
        'qr_code',
        'digital_signature',
        'generated_at',
    ];

    public function registration()
    {
        return $this->belongsTo(ProgramRegistration::class, 'registration_id', 'registration_id');
    }

    public function program()
    {
        return $this->belongsTo(PtProgram::class, 'program_id', 'program_id');
    }
}
