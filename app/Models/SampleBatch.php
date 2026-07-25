<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SampleBatch extends Model
{
    protected $table = 'sample_batches';
    protected $primaryKey = 'batch_id';
    public $timestamps = false;

    protected $fillable = [
        'program_id',
        'batch_number',
        'material',
        'quantity',
        'prepared_by',
        'verified_by',
        'preparation_date',
        'status',
    ];

    public function program()
    {
        return $this->belongsTo(PtProgram::class, 'program_id', 'program_id');
    }

    public function preparedByAdmin()
    {
        return $this->belongsTo(AdminUser::class, 'prepared_by', 'admin_id');
    }

    public function verifiedByAdmin()
    {
        return $this->belongsTo(AdminUser::class, 'verified_by', 'admin_id');
    }

    public function homogeneityTests()
    {
        return $this->hasMany(HomogeneityTest::class, 'batch_id', 'batch_id');
    }

    public function stabilityTests()
    {
        return $this->hasMany(StabilityTest::class, 'batch_id', 'batch_id');
    }

    public function samples()
    {
        return $this->hasMany(Sample::class, 'batch_id', 'batch_id');
    }

    public function referenceValues()
    {
        return $this->hasMany(BatchParameterReferenceValue::class, 'batch_id', 'batch_id');
    }
}
