<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\API\AttendanceController;
use Carbon\Carbon;

class CreateDailyAttendance extends Command
{
    // The name and signature of the console command.
    protected $signature = 'attendance:create';

    // The console command description.
    protected $description = 'Automatically create attendance records for all employees for the current day';

    // Execute the console command.
    public function handle()
    {
        // Get today's date
        $attendanceDate = Carbon::today()->format('Y-m-d');
        $attendanceDate = "2024-10-06";
        // Call the createAttendance method from the AttendanceController
        $controller = new AttendanceController();
        $request = new \Illuminate\Http\Request();
        $request->merge(['date' => $attendanceDate]);

        // Run the createAttendance method
        $controller->insertAttendance($request);

        $this->info('Attendance records created for ' . $attendanceDate);
    }
}
