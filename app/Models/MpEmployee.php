<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class MpEmployee extends Authenticatable
{
    protected $table = 'mp_employees';
    protected $primaryKey = 'employee_id';
    public $timestamps = false;

    protected $fillable = [
        'email',
        'password_hash',
        'first_name',
        'last_name',
        'gender',
        'dept_id',
        'position_id',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
