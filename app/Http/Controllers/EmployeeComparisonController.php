<?php

namespace App\Http\Controllers;

use App\Models\AndalEmployee;
use App\Models\HcisEmployee;
use App\Models\User;
use App\Models\EmployeeVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\EmployeeComparisonExport;
use Maatwebsite\Excel\Facades\Excel; 

class EmployeeComparisonController extends Controller
{
    private $comparisonFields = [
        'group_company'       => 'Business Unit',
        'company_name'        => 'Company Name',
        'unit'                => 'Department',
        'designation_name'    => 'Designation',
        'job_level'           => 'Job Level',
        'office_area'         => 'Office Location',
        'bank_name'           => 'Bank Name',
        'bank_account_number' => 'Account Number',
        'employee_type'       => 'Employee Type',
        'tax_status'          => 'Tax Status',
        'marital_status'      => 'Marital Status',
        'religion'            => 'Religion',
        'ktp'                 => 'Citizenship No.',
    ];

    public function index(Request $request)
    {
        $filterOptions = [
            'businessUnits' => HcisEmployee::select('group_company')->whereNotNull('group_company')->distinct()->orderBy('group_company')->pluck('group_company'),
            'jobLevels' => HcisEmployee::select('job_level')->whereNotNull('job_level')->distinct()->orderBy('job_level')->pluck('job_level'),
        ];

        return view('employees.list', [
            'employees' => [], 
            'pageTitle' => 'Employee Synchronization Status',
            'filterOptions' => $filterOptions,
        ]);
    }

    // public function getDataChunk(Request $request)
    // {
    //     $chunkSize = 500; 
    //     $page = $request->input('page', 1);
        
    //     $query = HcisEmployee::query();

    //     if ($request->filled('business_unit')) $query->where('group_company', $request->business_unit);
    //     if ($request->filled('job_level')) $query->where('job_level', $request->job_level);

    //     $allCandidates = $query->orderBy('fullname', 'asc')->get();

    //     $employeeIds = $allCandidates->pluck('employee_id');
    //     $andalEmployees = AndalEmployee::whereIn('employee_id', $employeeIds)->get()->keyBy('employee_id');
    //     $verifications = EmployeeVerification::whereIn('employee_id', $employeeIds)->get()->keyBy('employee_id');

    //     $filteredCollection = $allCandidates->filter(function($hcis) use ($andalEmployees, $verifications, $request) {
    //         $andal = $andalEmployees->get($hcis->employee_id);
    //         $verifyData = $verifications->get($hcis->employee_id);

    //         $isSynchronized = true;
    //         if ($andal) {
    //             foreach (array_keys($this->comparisonFields) as $field) {
    //                 if (trim((string)$hcis->{$field}) !== trim((string)$andal->{$field})) {
    //                     $isSynchronized = false;
    //                     break;
    //                 }
    //             }
    //         } else {
    //             $isSynchronized = false;
    //         }

    //         $isChecked = $verifyData ? $verifyData->is_checked : false;

    //         $hcis->temp_sync_status = $isSynchronized;
    //         $hcis->temp_is_checked = $isChecked;

    //         if ($request->filled('data_status')) {
    //             if ($request->data_status === 'sync' && !$isSynchronized) return false;
    //             if ($request->data_status === 'unsync' && $isSynchronized) return false;
    //         }

    //         if ($request->filled('check_status')) {
    //             if ($request->check_status === 'checked' && !$isChecked) return false;
    //             if ($request->check_status === 'pending' && $isChecked) return false;
    //         }

            
    //         return true;
    //     });

    //     $totalRecords = $filteredCollection->count();
    //     $pagedData = $filteredCollection->forPage($page, $chunkSize);
    //     $startNo = ($page - 1) * $chunkSize + 1;

    //     $data = [];
    //     $index = 0;
    //     foreach ($pagedData as $hcis) {
    //         $data[] = [
    //             'no' => $startNo + $index,
    //             'employee_id' => $hcis->employee_id,
    //             'fullname' => $hcis->fullname,
    //             'group_company' => $hcis->group_company ?? '-',
    //             'sync_status' => $hcis->temp_sync_status, 
    //             'is_checked' => $hcis->temp_is_checked,
    //             'action_url' => route('employees.detail', ['employeeId' => $hcis->employee_id])
    //         ];
    //         $index++;
    //     }

    //     $hasMore = ($page * $chunkSize) < $totalRecords;

    //     return response()->json([
    //         'data' => $data,
    //         'total' => $totalRecords, 
    //         'has_more' => $hasMore
    //     ]);
    // }

    public function getDataChunk(Request $request)
    {
        $chunkSize = 500; 
        $page = $request->input('page', 1);
        
        $query = HcisEmployee::query();

        if (method_exists(HcisEmployee::class, 'bootSoftDeletes')) {
            $query = HcisEmployee::withTrashed();
        }

        if ($request->filled('business_unit')) $query->where('group_company', $request->business_unit);
        if ($request->filled('job_level')) $query->where('job_level', $request->job_level);

        if ($request->filled('employee_status')) {
            if ($request->employee_status === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($request->employee_status === 'inactive') {
                $query->whereNotNull('deleted_at');
            }
        }

        $allCandidates = $query->orderBy('fullname', 'asc')->get();

        $employeeIds = $allCandidates->pluck('employee_id');
        $andalEmployees = AndalEmployee::whereIn('employee_id', $employeeIds)->get()->keyBy('employee_id');
        $verifications = EmployeeVerification::whereIn('employee_id', $employeeIds)->get()->keyBy('employee_id');

        $filteredCollection = $allCandidates->filter(function($hcis) use ($andalEmployees, $verifications, $request) {
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

            $hcis->temp_sync_status = $isSynchronized;
            $hcis->temp_is_checked = $isChecked;

            if ($request->filled('data_status')) {
                if ($request->data_status === 'sync' && !$isSynchronized) return false;
                if ($request->data_status === 'unsync' && $isSynchronized) return false;
            }

            return true;
        });

        $totalRecords = $filteredCollection->count();
        $pagedData = $filteredCollection->forPage($page, $chunkSize);
        $startNo = ($page - 1) * $chunkSize + 1;

        $data = [];
        $index = 0;
        foreach ($pagedData as $hcis) {
            $data[] = [
                'no' => $startNo + $index,
                'employee_id' => $hcis->employee_id,
                'fullname' => $hcis->fullname,
                'group_company' => $hcis->group_company ?? '-',
                'sync_status' => $hcis->temp_sync_status, 
                'is_checked' => $hcis->temp_is_checked,
                'action_url' => route('employees.detail', ['employeeId' => $hcis->employee_id])
            ];
            $index++;
        }

        $hasMore = ($page * $chunkSize) < $totalRecords;

        return response()->json([
            'data' => $data,
            'total' => $totalRecords, 
            'has_more' => $hasMore
        ]);
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['business_unit', 'job_level', 'data_status', 'check_status']);
        $timestamp = now()->format('Y-m-d_H-i');
        
        return Excel::download(new EmployeeComparisonExport($filters), "Comparison_Report_{$timestamp}.xlsx");
    }

    public function show($employeeId)
    {
        $hcis = HcisEmployee::where('employee_id', $employeeId)->firstOrFail();
        $andal = AndalEmployee::where('employee_id', $employeeId)->first();
        $verification = EmployeeVerification::where('employee_id', $employeeId)->first();

        if (!$andal) {
            return redirect()->route('employees.list')
                ->with('error', 'Employee with ID '.$employeeId.' not found in ANDAL database.');
        }

        $comparisonData = [];
        foreach ($this->comparisonFields as $field => $description) {
            $hcisValue = $hcis->{$field} ?? '-';
            $andalValue = $andal->{$field} ?? '-';
            $isMatch = trim((string)$hcisValue) === trim((string)$andalValue);

            $comparisonData[] = [
                'description' => $description,
                'hcis_value' => $hcisValue,
                'andal_value' => $andalValue,
                'is_match' => $isMatch,
            ];
        }
        
        $checkerName = '-';
        $checkedAt = null;
        $isChecked = false;

        if ($verification && $verification->is_checked) {
            $isChecked = true;
            $checkedAt = $verification->checked_at;
            $checkerUser = User::where('employee_id', $verification->checked_by)->first();
            $checkerName = $checkerUser ? $checkerUser->name : $verification->checked_by;
        }

        return view('employees.detail', [
            'employee' => $hcis, 
            'andalEmployee' => $andal,
            'comparison' => $comparisonData,
            'isChecked' => $isChecked,
            'checkerName' => $checkerName,
            'checkedAt' => $checkedAt
        ]);
    }

    public function confirm($employeeId)
    {
        $exists = HcisEmployee::where('employee_id', $employeeId)->exists();
        if(!$exists) {
            return redirect()->back()->with('error', 'Employee ID not found.');
        }

        $verification = EmployeeVerification::firstOrNew(['employee_id' => $employeeId]);

        if ($verification->is_checked) {
            $verification->is_checked = false;
            $verification->checked_by = null;
            $verification->checked_at = null;
            $message = 'Verification undone. Status is Pending.';
        } else {
            $verification->is_checked = true;
            $verification->checked_by = Auth::user()->employee_id;
            $verification->checked_at = now();
            $message = 'Employee verified successfully.';
        }

        $verification->save();
        return redirect()->back()->with('success', $message);
    }
}