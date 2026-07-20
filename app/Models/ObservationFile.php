<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObservationFile extends Model
{
    protected $table = 'observation_files';
    protected $primaryKey = 'file_id';
    public $timestamps = false; // Only uploaded_at in schema

    protected $fillable = [
        'observation_id',
        'file_path',
        'original_filename',
        'uploaded_at',
    ];

    public function observation()
    {
        return $this->belongsTo(Observation::class, 'observation_id', 'observation_id');
    }
}
