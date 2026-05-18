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
            // [
            //     'url' => 'http://103.99.25.116/gw_plantation/api/Employee/GetListEmployeeKPN?page=1&limit=150',
            //     'token' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJVc2VyTmFtZUxvZ2luIjoiQVBJIiwic3ViIjoiQVBJIiwianRpIjoiZjQxZjAwOTMtMzUwNi00MzJiLWFjYjMtMDM5NGE2ZjYxYTQ5IiwiaWF0IjoiMS8xNS8yMDI2IDExOjQ4OjU3IEFNICswMDowMCIsImRiTmFtZSI6IlBMQU5UQVRJT05fUEFZUk9MTCIsIlNlcnZlck5hbWUiOiJWTTEyMC0wMDEtTUFZQVBcXFNRTDIwMjIiLCJJZCI6ImVjMTQ3MWM5LWRjZDYtNGMxZi04ZjZkLWEwNGM3ZDhjNGM0ZSIsIm5iZiI6MTc2ODQ3NzczNywiZXhwIjoxODAwMDEzNzM3LCJpc3MiOiJEZW1vSXNzdWVyIiwiYXVkIjoiRGVtb0F1ZGllbmNlIn0.9PRe7D25st86FzLkh1p5VkjfGHPA5sTnR0xZ_IOWcmQ',
            // ],
            // [
            //     'url' => 'http://103.99.25.116/gw_downstream/api/Employee/GetListEmployeeKPN?page=1&limit=100',
            //     'token' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJVc2VyTmFtZUxvZ2luIjoiQVBJIiwic3ViIjoiQVBJIiwianRpIjoiZTI2MTRlZDctMzc5Mi00YmU0LWI5ZWQtNTdkYTEyZGYxYWRhIiwiaWF0IjoiMS8xNS8yMDI2IDExOjQ3OjIzIEFNICswMDowMCIsImRiTmFtZSI6IkRPV05TVFJFQU1fUEFZUk9MTCIsIlNlcnZlck5hbWUiOiJWTTEyMC0wMDEtTUFZQVBcXFNRTDIwMjIiLCJJZCI6IjIzYTM3MWZjLTQ0NTEtNDI1OC1iMjY3LTYzNzZhMGY0ZjYxZCIsIm5iZiI6MTc2ODQ3NzY0MywiZXhwIjoxODAwMDEzNjQzLCJpc3MiOiJEZW1vSXNzdWVyIiwiYXVkIjoiRGVtb0F1ZGllbmNlIn0.fcqXbjwvLyrum5wApmFSdVIYaB1sY3X6MPi__GqlHPg',
            // ],
            // [
            //     'url' => 'http://103.99.25.116/gw_cemindo/api/Employee/GetListEmployeeKPN?page=1&limit=100',
            //     'token' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJVc2VyTmFtZUxvZ2luIjoiQVBJIiwic3ViIjoiQVBJIiwianRpIjoiMWQxYzk1YTQtYTBkMC00ZTliLWI3YmQtZjY5ODEwOWU2MWJjIiwiaWF0IjoiMS8xNS8yMDI2IDExOjQyOjA3IEFNICswMDowMCIsImRiTmFtZSI6IkNFTUlORE9fUEFZUk9MTCIsIlNlcnZlck5hbWUiOiJWTTEyMC0wMDEtTUFZQVBcXFNRTDIwMjIiLCJJZCI6ImYwOTFmNTUyLTZlZDUtNGRhYS04ODRmLTJjOTg0NGI4YWRkNiIsIm5iZiI6MTc2ODQ3NzMyNywiZXhwIjoxODAwMDEzMzI3LCJpc3MiOiJEZW1vSXNzdWVyIiwiYXVkIjoiRGVtb0F1ZGllbmNlIn0.8VF7hFCwf0y9X-eiPqzNT2kEbXIhIzYc2cRZCARH7Dw',
            // ],
            [
                'url' => 'http://172.20.0.143/gw_cemindo/api/Employee/GetListEmployeeKPN?page=1&limit=100',
                'token' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJVc2VyTmFtZUxvZ2luIjoiQVBJIiwic3ViIjoiQVBJIiwianRpIjoiMjdiMDkzNmMtMTNkMi00OWRlLThkNDMtYjRjMDYyMTY1MmUxIiwiaWF0IjoiMi80LzIwMjYgNToyOTo0MCBBTSArMDA6MDAiLCJkYk5hbWUiOiJDRU1JTkRPX1BBWVJPTEwiLCJTZXJ2ZXJOYW1lIjoiMTcyLjIwLjAuMTQzLDE0MzQiLCJJZCI6IjE2ZjZhNTMzLTc2NzAtNGEwZC05NGRiLWZhMTZkYWQ4OTkzOSIsIm5iZiI6MTc3MDE4Mjk4MCwiZXhwIjoxODAxNzE4OTgwLCJpc3MiOiJEZW1vSXNzdWVyIiwiYXVkIjoiRGVtb0F1ZGllbmNlIn0.CAtgbNq0MSgpg2YNRTd7wON5J3tGXsVjt5Of7yQEVno',
            ],
            [
                'url' => 'http://172.20.0.143/gw_downstream/api/Employee/GetListEmployeeKPN?page=1&limit=100',
                'token' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJVc2VyTmFtZUxvZ2luIjoiQVBJIiwic3ViIjoiQVBJIiwianRpIjoiNTk2M2Q5NTgtNmM3MS00MTdiLWIzNzktYWM5Njk4MDQ0M2U5IiwiaWF0IjoiMi80LzIwMjYgNTo0NToyMCBBTSArMDA6MDAiLCJkYk5hbWUiOiJET1dOU1RSRUFNX1BBWVJPTEwiLCJTZXJ2ZXJOYW1lIjoiMTcyLjIwLjAuMTQzLDE0MzQiLCJJZCI6IjNiNGUwN2Y1LTUzMzgtNGE2Ny04YTMyLWI3Y2E1YmVhNjc2NiIsIm5iZiI6MTc3MDE4MzkyMCwiZXhwIjoxODAxNzE5OTIwLCJpc3MiOiJEZW1vSXNzdWVyIiwiYXVkIjoiRGVtb0F1ZGllbmNlIn0.WYDY-uF9DLB9YXbX2ooL1PSnT2-4MFk8VKR33vyJcO8',
            ],
            [
                'url' => 'http://172.20.0.143/gw_plantation/api/Employee/GetListEmployeeKPN?page=1&limit=150',
                'token' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJVc2VyTmFtZUxvZ2luIjoiQVBJIiwic3ViIjoiQVBJIiwianRpIjoiODIxODVjNDItYzM3Yi00ZjhiLThkNzktYjU5ZThkMjA5NGVkIiwiaWF0IjoiMi80LzIwMjYgNTo1MDoyMyBBTSArMDA6MDAiLCJkYk5hbWUiOiJQTEFOVEFUSU9OX1BBWVJPTEwiLCJTZXJ2ZXJOYW1lIjoiMTcyLjIwLjAuMTQzLDE0MzQiLCJJZCI6ImNmYmVkZjE2LWU2NzQtNGE2Yi04YTExLWQwOTgzMzNmNDEwYyIsIm5iZiI6MTc3MDE4NDIyMywiZXhwIjoxODAxNzIwMjIzLCJpc3MiOiJEZW1vSXNzdWVyIiwiYXVkIjoiRGVtb0F1ZGllbmNlIn0.AYis-EZmE7S_7dj0eopgyZ-yBSgEXueCWca9SPFpkQc',
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
