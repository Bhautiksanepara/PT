<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralCode extends Model
{
    protected $table = 'referral_codes';
    protected $primaryKey = 'referral_id';
    public $timestamps = false; // Custom created_at and used_at in schema

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'is_client_specific',
        'client_lab_id',
        'is_one_time_use',
        'expiry_date',
        'is_used',
        'used_by_lab_id',
        'used_at',
        'created_by',
        'created_at',
    ];

    public function clientLab()
    {
        return $this->belongsTo(Lab::class, 'client_lab_id', 'lab_id');
    }

    public function usedByLab()
    {
        return $this->belongsTo(Lab::class, 'used_by_lab_id', 'lab_id');
    }

    public function creator()
    {
        return $this->belongsTo(AdminUser::class, 'created_by', 'admin_id');
    }

    /**
     * Check if the referral code is currently valid for a specific lab ID.
     */
    public function isValidForLab(?int $labId = null): array
    {
        // 1. Check if already used and is one-time use
        if ($this->is_one_time_use && $this->is_used) {
            return ['valid' => false, 'message' => 'This referral code has already been used.'];
        }

        // 2. Check Expiry Date
        if ($this->expiry_date && \Carbon\Carbon::parse($this->expiry_date)->isPast()) {
            return ['valid' => false, 'message' => 'This referral code has expired.'];
        }

        // 3. Check Client Specific Restriction
        if ($this->is_client_specific && $this->client_lab_id) {
            if (!$labId || $this->client_lab_id != $labId) {
                return ['valid' => false, 'message' => 'This referral code is exclusive to a specific laboratory.'];
            }
        }

        return ['valid' => true, 'message' => 'Referral code is valid!'];
    }
}
