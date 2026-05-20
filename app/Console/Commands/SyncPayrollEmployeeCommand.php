<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncPayrollEmployeeJob;

class SyncPayrollEmployeeCommand extends Command
{
    protected $signature = 'payroll:sync-employees';
    protected $description = 'Sync employee data from Payroll APIs';

    public function handle(): int
    {
        $apis = [
            [
                'url' => config('services.gw.cemindo.api_base_url') . '/api/Employee/GetListEmployeeKPN',
                'token' => 'Bearer ' . config('services.gw.cemindo.api_access_token'),
            ],
            [
                'url' => config('services.gw.downstream.api_base_url') . '/api/Employee/GetListEmployeeKPN',
                'token' => 'Bearer ' . config('services.gw.downstream.api_access_token'),
            ],
            [
                'url' => config('services.gw.plantation.api_base_url') . '/api/Employee/GetListEmployeeKPN',
                'token' => 'Bearer ' . config('services.gw.plantation.api_access_token'),
            ],
        ];

        foreach ($apis as $api) {
            SyncPayrollEmployeeJob::dispatch(
                $api['url'],
                $api['token']
            );
        }

        $this->info('Payroll sync jobs dispatched successfully.');

        return self::SUCCESS;
    }
}
