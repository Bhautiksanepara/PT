<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardAlertRead extends Model
{
    protected $table = 'dashboard_alert_reads';
    public $timestamps = false;

    protected $fillable = [
        'lab_id',
        'program_id',
        'alert_type',
        'read_at',
    ];
}
