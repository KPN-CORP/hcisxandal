<?php

namespace App\Exports;

use App\Models\AndalEmployee;
use App\Models\HcisEmployee;
use App\Models\EmployeeVerification;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeComparisonExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;
    protected $comparisonFields = [
        'company_name' => 'Perusahaan (PT)',
        'group_company' => 'Business Unit',
        'unit' => 'Unit / Divisi',
        'job_level' => 'Level Jabatan',
        'office_area' => 'Lokasi Kantor',
        'designation_name' => 'Designation',
        'bank_name' => 'Bank',
        'bank_account_number' => 'No. Rekening',
        'employee_type' => 'Status Karyawan',
    ];

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = HcisEmployee::query();

        if (!empty($this->filters['business_unit'])) {
            $query->where('group_company', $this->filters['business_unit']);
        }
        if (!empty($this->filters['job_level'])) {
            $query->where('job_level', $this->filters['job_level']);
        }

        $hcisEmployees = $query->orderBy('fullname')->get();

        $employeeIds = $hcisEmployees->pluck('employee_id');
        $andalEmployees = AndalEmployee::whereIn('employee_id', $employeeIds)->get()->keyBy('employee_id');
        $verifications = EmployeeVerification::whereIn('employee_id', $employeeIds)->get()->keyBy('employee_id');

        $filteredData = $hcisEmployees->filter(function ($hcis) use ($andalEmployees, $verifications) {
            $andal = $andalEmployees->get($hcis->employee_id);
            $verifyData = $verifications->get($hcis->employee_id);
            
            $isSynchronized = true;
            if ($andal) {
                foreach (array_keys($this->comparisonFields) as $field) {
                    if (trim((string)$hcis->{$field}) !== trim((string)$andal->{$field})) {
                        $isSynchronized = false;
                        break;
                    }
                }
            } else {
                $isSynchronized = false;
            }

            $isChecked = $verifyData ? $verifyData->is_checked : false;

            if (!empty($this->filters['data_status'])) {
                if ($this->filters['data_status'] === 'sync' && !$isSynchronized) return false;
                if ($this->filters['data_status'] === 'unsync' && $isSynchronized) return false;
            }

            if (!empty($this->filters['check_status'])) {
                if ($this->filters['check_status'] === 'checked' && !$isChecked) return false;
                if ($this->filters['check_status'] === 'pending' && $isChecked) return false;
            }

            return true;
        });

        return $filteredData;
    }

    public function map($hcis): array
    {
        $andal = AndalEmployee::where('employee_id', $hcis->employee_id)->first();
        $verification = EmployeeVerification::where('employee_id', $hcis->employee_id)->first();

        $status = 'Synchronized';
        $mismatchDetails = [];

        if (!$andal) {
            $status = 'Missing in Andal';
            $mismatchDetails[] = "Data Employee tidak ditemukan di database ANDAL.";
        } else {
            foreach ($this->comparisonFields as $field => $label) {
                $valHcis = trim((string)$hcis->{$field});
                $valAndal = trim((string)$andal->{$field});

                if ($valHcis !== $valAndal) {
                    $status = 'Unsynchronized';
                    $mismatchDetails[] = "• $label:\n   Andal: $valAndal\n   HCIS: $valHcis";
                }
            }
        }

        $isCheckedStr = 'Pending';
        $checkedByStr = '-';
        
        if ($verification && $verification->is_checked) {
            $isCheckedStr = 'Checked';
            $checkerUser = User::where('employee_id', $verification->checked_by)->first();
            $checkedByStr = ($checkerUser ? $checkerUser->name : $verification->checked_by) . "\n(" . $verification->checked_at . ")";
        }

        return [
            $hcis->employee_id,
            $hcis->fullname,
            $hcis->group_company,
            $status,
            $isCheckedStr,
            $checkedByStr, 
            implode("\n", $mismatchDetails) 
        ];
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Full Name',
            'Business Unit',
            'Data Status',
            'Verification Status', 
            'Verified By',         
            'Mismatch Details'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('G')->getAlignment()->setWrapText(true); 
        $sheet->getStyle('F')->getAlignment()->setWrapText(true);
        $sheet->getColumnDimension('G')->setWidth(60);
        $sheet->getColumnDimension('F')->setWidth(25);
    }
}