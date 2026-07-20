<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StabilityTest extends Model
{
    protected $table = 'stability_tests';
    protected $primaryKey = 'test_id';
    public $timestamps = false;

    protected $fillable = [
        'batch_id',
        'test_date',
        'result',
        'performed_by',
        'remarks',
    ];

    public function batch()
    {
        return $this->belongsTo(SampleBatch::class, 'batch_id', 'batch_id');
    }

    public function performedByAdmin()
    {
        return $this->belongsTo(AdminUser::class, 'performed_by', 'admin_id');
    }
}
