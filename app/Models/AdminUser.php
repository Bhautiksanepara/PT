<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AdminUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'admin_users';
    protected $primaryKey = 'admin_id';

    protected $fillable = [
        'full_name',
        'email',
        'username',
        'password_hash',
        'status',
    ];

    protected $hidden = [
        'password_hash',
    ];

    /**
     * Override default password column name for Laravel Auth
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
