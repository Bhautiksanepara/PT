<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $table = 'notifications_log';
    protected $primaryKey = 'notification_id';
    public $timestamps = false; // Only sent_at in schema

    protected $fillable = [
        'lab_id',
        'notification_type',
        'subject',
        'message',
        'sent_at',
    ];

    public function lab()
    {
        return $this->belongsTo(Lab::class, 'lab_id', 'lab_id');
    }
}
