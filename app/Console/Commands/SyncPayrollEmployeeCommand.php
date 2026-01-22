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
                'url' => 'http://103.99.25.116/gw_plantation/api/Employee/GetListEmployeeKPN?page=1&limit=150',
                'token' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJVc2VyTmFtZUxvZ2luIjoiQVBJIiwic3ViIjoiQVBJIiwianRpIjoiZjQxZjAwOTMtMzUwNi00MzJiLWFjYjMtMDM5NGE2ZjYxYTQ5IiwiaWF0IjoiMS8xNS8yMDI2IDExOjQ4OjU3IEFNICswMDowMCIsImRiTmFtZSI6IlBMQU5UQVRJT05fUEFZUk9MTCIsIlNlcnZlck5hbWUiOiJWTTEyMC0wMDEtTUFZQVBcXFNRTDIwMjIiLCJJZCI6ImVjMTQ3MWM5LWRjZDYtNGMxZi04ZjZkLWEwNGM3ZDhjNGM0ZSIsIm5iZiI6MTc2ODQ3NzczNywiZXhwIjoxODAwMDEzNzM3LCJpc3MiOiJEZW1vSXNzdWVyIiwiYXVkIjoiRGVtb0F1ZGllbmNlIn0.9PRe7D25st86FzLkh1p5VkjfGHPA5sTnR0xZ_IOWcmQ',
            ],
            [
                'url' => 'http://103.99.25.116/gw_downstream/api/Employee/GetListEmployeeKPN?page=1&limit=100',
                'token' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJVc2VyTmFtZUxvZ2luIjoiQVBJIiwic3ViIjoiQVBJIiwianRpIjoiZTI2MTRlZDctMzc5Mi00YmU0LWI5ZWQtNTdkYTEyZGYxYWRhIiwiaWF0IjoiMS8xNS8yMDI2IDExOjQ3OjIzIEFNICswMDowMCIsImRiTmFtZSI6IkRPV05TVFJFQU1fUEFZUk9MTCIsIlNlcnZlck5hbWUiOiJWTTEyMC0wMDEtTUFZQVBcXFNRTDIwMjIiLCJJZCI6IjIzYTM3MWZjLTQ0NTEtNDI1OC1iMjY3LTYzNzZhMGY0ZjYxZCIsIm5iZiI6MTc2ODQ3NzY0MywiZXhwIjoxODAwMDEzNjQzLCJpc3MiOiJEZW1vSXNzdWVyIiwiYXVkIjoiRGVtb0F1ZGllbmNlIn0.fcqXbjwvLyrum5wApmFSdVIYaB1sY3X6MPi__GqlHPg',
            ],
            [
                'url' => 'http://103.99.25.116/gw_cemindo/api/Employee/GetListEmployeeKPN?page=1&limit=100',
                'token' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJVc2VyTmFtZUxvZ2luIjoiQVBJIiwic3ViIjoiQVBJIiwianRpIjoiMWQxYzk1YTQtYTBkMC00ZTliLWI3YmQtZjY5ODEwOWU2MWJjIiwiaWF0IjoiMS8xNS8yMDI2IDExOjQyOjA3IEFNICswMDowMCIsImRiTmFtZSI6IkNFTUlORE9fUEFZUk9MTCIsIlNlcnZlck5hbWUiOiJWTTEyMC0wMDEtTUFZQVBcXFNRTDIwMjIiLCJJZCI6ImYwOTFmNTUyLTZlZDUtNGRhYS04ODRmLTJjOTg0NGI4YWRkNiIsIm5iZiI6MTc2ODQ3NzMyNywiZXhwIjoxODAwMDEzMzI3LCJpc3MiOiJEZW1vSXNzdWVyIiwiYXVkIjoiRGVtb0F1ZGllbmNlIn0.8VF7hFCwf0y9X-eiPqzNT2kEbXIhIzYc2cRZCARH7Dw',
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