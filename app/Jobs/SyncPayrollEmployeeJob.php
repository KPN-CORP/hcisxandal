<?php

namespace App\Jobs;

use App\Mail\HCISPayrollLogMail;
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

    public int $timeout = 1200; // 20 menit
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
        try {
            $dataPullDate = Carbon::today()->toDateString();

            $response = Http::withHeaders([
                'Authorization' => $this->token,
                'Accept' => 'application/json',
            ])->timeout(60)->get($this->apiUrl);

            if (!$response->successful()) {
                Log::error('Payroll API failed', [
                    'url' => $this->apiUrl,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                try {
                    Mail::to('dali.kewara@kpn-corp.com')->send(new HCISPayrollLogMail([
                        'err_url' => $this->apiUrl,
                        'err_http_status' => $response->status(),
                        'err_response_body' => $response->body(),
                    ]));
                } catch (\Exception $errMail) {
                    Log::error('HCIS Payroll Log E-mail failed to send: ' . $errMail->getMessage());
                }
                return;
            }

            foreach ($response->json() as $item) {
                AndalEmployee::updateOrCreate(
                    [
                        // UNIQUE KEY
                        'nik_andal' => $item['nikPayroll'],
                        'data_pull' => $dataPullDate,
                    ],
                    [
                        'employee_id' => $item['nikDarwinBox'] ?? null,
                        'nik_andal' => trim($item['nikPayroll']),
                        'fullname' => trim($item['namaKaryawan']),
                        'designation_name' => $item['namaJabatan'],
                        'job_level' => $item['golongan'],
                        'company_name' => $item['pt'],
                        'office_area' => $item['lokasiKerja'],
                        'employee_type' => $item['statusKaryawan'],
                        'division' => $item['divisi'],
                        'unit' => $item['department'],
                        'religion' => $item['agama'],
                        'marital_status' => $item['maritalStatus'],
                        'tax_status' => $item['taxStatus'],
                        'ktp' => $item['noKTP'],
                        'bank_name' => $item['namaBank'],
                        'bank_account_number' => $item['nomorRekening'],
                    ]
                );
            }

            Log::info('Payroll sync completed', [
                'date' => $dataPullDate,
                'total' => count($response->json()),
            ]);

            try {
                Mail::to('dali.kewara@kpn-corp.com')->send(new HCISPayrollLogMail([
                    'ok_url' => $this->apiUrl,
                    'ok_pull_date' => $dataPullDate,
                    'ok_total' => count($response->json()),
                ]));
            } catch (\Exception $errMail) {
                Log::error('HCIS Payroll Log E-mail failed to send: ' . $errMail->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Payroll Exception Error', [
                'url' => $this->apiUrl,
                'exception' => $e->getMessage(),
            ]);

            try {
                Mail::to('dali.kewara@kpn-corp.com')->send(new HCISPayrollLogMail([
                    'err_url' => $this->apiUrl,
                    'err_exception' => $e->getMessage(),
                ]));
            } catch (\Exception $errMail) {
                Log::error('HCIS Payroll Log E-mail failed to send: ' . $errMail->getMessage());
            }
        }
    }
}
