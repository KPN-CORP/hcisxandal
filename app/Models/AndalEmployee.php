<?php
// File: app/Models/AndalEmployee.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AndalEmployee extends Model
{
    use HasFactory;
    protected $table = 'employees_andal';

    protected $fillable = [
        'employee_id',
        'nik_andal',
        'fullname',
        'gender',
        'email',
        'group_company',
        'designation_name',
        'job_level',
        'company_name',
        'contribution_level_code',
        'work_area_code',
        'office_area',
        'employee_type',
        'division',
        'unit',
        'date_of_birth',
        'place_of_birth',
        'religion',
        'marital_status',
        'tax_status',
        'bpjs_tk',
        'bpjs_ks',
        'ktp',
        'kk',
        'npwp',
        'mother_name',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'data_pull',
    ];
}