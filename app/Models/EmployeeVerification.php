<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeVerification extends Model
{
    use HasFactory;

    protected $table = 'employee_verifications';

    protected $fillable = [
        'employee_id',
        'is_checked',
        'checked_by',
        'checked_at',
        'notes'
    ];

    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by', 'employee_id');
    }
}