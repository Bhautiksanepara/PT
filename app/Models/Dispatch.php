<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    protected $table = 'dispatches';
    protected $primaryKey = 'dispatch_id';
    public $timestamps = false; // Only created_at in schema

    protected $fillable = [
        'sample_id',
        'dispatch_date',
        'courier_name',
        'tracking_number',
        'dispatched_by',
        'notification_sent',
        'notification_sent_at',
        'created_at',
    ];

    public function sample()
    {
        return $this->belongsTo(Sample::class, 'sample_id', 'sample_id');
    }

    public function dispatchedByAdmin()
    {
        return $this->belongsTo(AdminUser::class, 'dispatched_by', 'admin_id');
    }
}
