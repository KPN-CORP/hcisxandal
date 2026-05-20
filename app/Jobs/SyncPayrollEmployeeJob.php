<?php

namespace App\Jobs;

use App\Models\AndalEmployee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncPayrollEmployeeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1200;
    public int $tries = 3;

    protected string $apiUrl;
    protected string $token;

    public function __construct(string $apiUrl, string $token)
    {
        $this->apiUrl = $apiUrl;
        $this->token = $token;
    }

    public function handle(): void
    {
        $dataPullDate = Carbon::today()->toDateString();

        $response = Http::withHeaders([
            'Authorization' => $this->token,
            'Accept' => 'application/json',
        ])->timeout(120)->get($this->apiUrl);

        if (!$response->successful()) {
            Log::error('Payroll API failed', [
                'url' => $this->apiUrl,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return;
        }

        $apiData = $response->json();
        $upsertData = [];

        foreach ($apiData as $item) {
            if (empty(trim($item['nikPayroll']))) {
                continue;
            }

            $upsertData[] = [
                'nik_andal' => trim($item['nikPayroll']),
                'data_pull' => $dataPullDate,
                
                'employee_id' => $item['nikDarwinBox'] ?? null,
                'fullname' => trim($item['namaKaryawan'] ?? ''),
                'designation_name' => $item['namaJabatan'] ?? null,
                'job_level' => $item['golongan'] ?? null,
                'company_name' => $item['pt'] ?? null,
                'office_area' => $item['lokasiKerja'] ?? null,
                'employee_type' => $item['statusKaryawan'] ?? null,
                'division' => $item['divisi'] ?? null, 
                'unit' => $item['department'] ?? null,
                'religion' => $item['agama'] ?? null,   
                'marital_status' => $item['maritalStatus'] ?? null,
                'tax_status' => $item['taxStatus'] ?? null,
                'ktp' => $item['noKTP'] ?? null,
                'bank_name' => $item['namaBank'] ?? null,
                'bank_account_number' => $item['nomorRekening'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $chunks = array_chunk($upsertData, 500);

        foreach ($chunks as $chunk) {
            \App\Models\AndalEmployee::upsert(
                $chunk,
                ['nik_andal', 'data_pull'],
                [
                    'employee_id', 'fullname', 'designation_name', 'job_level', 
                    'company_name', 'office_area', 'employee_type', 'division', 
                    'unit', 'religion', 'marital_status', 'tax_status', 'ktp', 
                    'bank_name', 'bank_account_number', 'updated_at'
                ]
            );
        }

        Log::info('Payroll sync completed', [
            'url' => $this->apiUrl,
            'date' => $dataPullDate,
            'total_from_api' => count($apiData),
            'total_processed' => count($upsertData),
        ]);
    }
}
