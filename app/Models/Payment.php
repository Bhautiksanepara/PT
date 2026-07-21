<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'payment_id';
    public $timestamps = false;

    protected $fillable = [
        'registration_id',
        'amount',
        'discount_amount',
        'final_amount',
        'payment_method',
        'transaction_id',
        'payment_status',
        'paid_at',
    ];

    public function registration()
    {
        return $this->belongsTo(ProgramRegistration::class, 'registration_id', 'registration_id');
    }
}
