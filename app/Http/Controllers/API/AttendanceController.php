<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Illuminate\Http\Response;
use Carbon\Carbon;
use App\Models\PersonAttendance;
use App\Models\PersonVacations;
use App\Models\WeekDays;
use App\Models\YearlyOfficialVacations;
use App\Models\Person;
use App\Models\WorkingTimes;
use Session;



class AttendanceController extends Controller
{
    // Get attendance with query parameters (supports filtering by date, month, and employee ID)
    public function getAttendance(Request $request)
    {
        // Extract parameters from request (optional)
        $personId = $request->query('person_id');
        $date = $request->query('date'); // Specific date, optional

        // Query attendance based on parameters
        $query = PersonAttendance::query();

        // If a specific person is requested
        if ($personId) {
            $query->where('PersonID', $personId);
        }

        // If a specific date is requested
        if ($date) {
            $validated = $request->validate([
                'date' => 'date_format:Y-m-d'
            ],
            [
                'date.date_format' => 'The attendance date must be in the format YYYY-MM-DD.',
            ]);
            $query->whereDate('AttendanceDate', $date);

            // Step 1: Check if the attendance for the given date is already in the database
            $existingRecords = PersonAttendance::where('AttendanceDate', $date)->get();

            if ($existingRecords->isEmpty()) {
                // If attendance is not found for this specific date
                return response()->json(['message' => 'لم يتم فتح كشف حضور وانصراف لهذا التاريخ. يجب انتظار اليوم نفسه وسيتم فتحه تلقائياً'], 200);
            }
            else{
                // If attendance is found for this specific date
                if ($this->isWeeklyVacation($date)) {
                    return response()->json([
                        'message' => 'هذا التاريخ هو أجازة أسبوعية لكل الموظفين'
                    ], 200);
                }

                if ($companyVacation = $this->getCompanyVacation($date)) {
                    return response()->json([
                        'message' => ': هذا التاريخ هو أجازة رسمية مدفوعة لكل الموظفين'.$companyVacation->VacationName.''
                    ], 200);
                }
            }

        }
        else if ($request->has('month')) 
        {  // Filter by month if provided

            $validated = $request->validate([
                'month' => 'date_format:Y-m'
            ],
            [
                'month.date_format' => 'The attendance Month must be in the format YYYY-MM.',
            ]);
            $startDate = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $request->month)->endOfMonth();
            $query->whereBetween('AttendanceDate', [$startDate, $endDate]);
        }

        // Fetch the attendance records (Eager load the personInformation and personalVacation)
        $attendances =  $query  ->with(['personInformation:PersonID,FirstName,SecondName,ThirdName,LandlineNumber'])
                                ->select('AttendanceID', 'PersonID', 'AttendanceDate', 'WorkStartTime', 'WorkEndTime', 'IsAbsent', 'IsWeeklyVacation', 'IsCompanyOnVacation', 'IsPersonalVacation')
                                ->orderBy('AttendanceDate', 'asc')
                                ->orderBy('PersonID', 'asc')->get();
                             

        // Return the modified attendance records
        return response()->json(['data'=>$attendances, 'message'=>'Attendance Returned Successfully'], 200);
    }


    public function insertAttendance(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d'
        ],
        [
            'date.required' => 'The attendance date is required.',
            'date.date_format' => 'The attendance date must be in the format YYYY-MM-DD.',
        ]);

        $date = $request->date;

        // Step 1: Check if the attendance for the given date is already in the database
        $existingRecords = PersonAttendance::where('AttendanceDate', $date)->get();

        if ($existingRecords->isNotEmpty()) {
            // If attendance is already found for this date, return the message
            return response()->json(['message' => 'كشف الحضور والانصراف موجود بالفعل لهذا التاريخ'], 200);
        }

        // Step 2: Initialize attendance records array
        $attendanceRecords = [];

        // Step 3: Fetch all employees
        $employees = Person::all()->where('IsDeleted','=',0); // Fetch all employees

        // Step 4: Check if the day is a weekly vacation and insert records
        if ($this->isWeeklyVacation($date)) {
            foreach ($employees as $employee) {
                $attendanceRecords[] = $this->createAttendanceRecord($employee->PersonID, $date, true, false, false);
            }
            return response()->json([
                'message' => 'تم تسجيل حضور وانصراف اليوم على انه أجازة أسبوعية لكل الموظفين'
            ], 200);
        }

        // Step 5: Check if the day is an official company vacation and insert records
        if ($companyVacation = $this->getCompanyVacation($date)) {
            foreach ($employees as $employee) {
                $this->createAttendanceRecord($employee->PersonID, $date, false, true, false, $companyVacation->VacationID);
            }
            return response()->json([
                'message' => 'تم تسجيل حضور وانصراف اليوم على انه أجازة رسمية مدفوعة لكل الموظفين',
            ], 200);
        }

        // Step 6: Create records for normal working days
        foreach ($employees as $employee) {
            $this->createAttendanceRecord($employee->PersonID, $date, false, false, false);

            // Step 7: Check if the employee is on personal vacation for this date
            $this->updateAttendanceWithVacationStatus($employee->PersonID, $date);
        }

        // Step 8: Return success response
        return response()->json([
            'message' => 'تم انشاء سجل حضور وانصراف اليوم بنجاح!',
        ], 200);
    }

    private function isWeeklyVacation($date)
    {
        $dayOfWeek = Carbon::parse($date)->format('l'); // Get day of the week (e.g., 'Saturday')
        $weekDay = WeekDays::where('DayNameEnglish', $dayOfWeek)->first();

        return $weekDay && $weekDay->IsWeeklyVacation;
    }

    private function getCompanyVacation($date)
    {
        return YearlyOfficialVacations::where('VacationDate', $date)->first();
    }

    private function createAttendanceRecord($personId, $date, $isWeeklyVacation = false, $isCompanyVacation = false, $isPersonalVacation = false, $companyVacationID = null, $personalVacationID = null)
    {
        $attendance = new PersonAttendance();
        $attendance->PersonID = $personId;
        $attendance->AttendanceDate = $date;
        $attendance->IsWeeklyVacation = $isWeeklyVacation;
        $attendance->IsCompanyOnVacation = $isCompanyVacation;
        $attendance->YearlyVacationID = $companyVacationID;
        $attendance->IsPersonalVacation = $isPersonalVacation;
        $attendance->PersonalVacationID = $personalVacationID;
        $attendance->IsAbsent = 0;
        
        // Set default working times if not on vacation
        if (!$isWeeklyVacation && !$isCompanyVacation && !$isPersonalVacation) {
            $workingTimes = $this->getWorkingTimes();
            /*
            $attendance->WorkStartTime = $workingTimes['start'];
            $attendance->WorkEndTime = $workingTimes['end'];
            */
            $attendance->WorkStartTime = Null;
            $attendance->WorkEndTime = Null;
        }

        $attendance->save();

        return $attendance;
    }

    private function getWorkingTimes()
    {
        $workingTimes = WorkingTimes::first(); // Assuming there's only one working time setting

        return [
            'start' => $workingTimes->StartTime,
            'end' => $workingTimes->EndTime,
        ];
    }

    private function updateAttendanceWithVacationStatus($personId, $date)
    {
        // Check if the person has a vacation on the given date
        $vacation = PersonVacations::where('PersonID', $personId)
            ->whereDate('VacationStartDate', '<=', $date)
            ->whereDate('VacationEndDate', '>=', $date)
            ->first();
        
        if ($vacation) {
            // Update attendance record with personal vacation information
            PersonAttendance::where('PersonID', $personId)
                ->where('AttendanceDate', $date)
                ->update([
                    'IsPersonalVacation' => 1,
                    'PersonalVacationID' => $vacation->PersonVacationID
                ]);
        }
    }



    public function updateAttendance(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'person_id' => 'required|integer|exists:PersonInformation,PersonID',
            'date' => 'required|date_format:Y-m-d',
            'WorkStartTime' => 'nullable|date_format:H:i',
            'WorkEndTime' => 'nullable|date_format:H:i',
            'IsAbsent' => 'nullable|integer',
        ]);

        // Fetch the attendance record based on person_id and date
        $attendance = PersonAttendance::where('PersonID', $validatedData['person_id'])
                                    ->where('AttendanceDate', $validatedData['date'])
                                    ->first();
        
        // Check if the attendance record exists
        if (!$attendance) {
            return response()->json(['message' => 'Attendance record not found.'], 404);
        }

        // Track changes
        $changes = [];

        // Update the fields if they are provided and different from the existing values
        if (isset($validatedData['WorkStartTime']) && $attendance->WorkStartTime !== $validatedData['WorkStartTime']) {
            $attendance->WorkStartTime = $validatedData['WorkStartTime'];
            $changes['WorkStartTime'] = $validatedData['WorkStartTime'];
        }

        if (isset($validatedData['WorkEndTime']) && $attendance->WorkEndTime !== $validatedData['WorkEndTime']) {
            $attendance->WorkEndTime = $validatedData['WorkEndTime'];
            $changes['WorkEndTime'] = $validatedData['WorkEndTime'];
        }

        if (isset($validatedData['IsAbsent']) && $attendance->IsAbsent !== $validatedData['IsAbsent']) {
            $attendance->IsAbsent = $validatedData['IsAbsent'];
            $changes['IsAbsent'] = $validatedData['IsAbsent'];
        }

        
        // Save the changes to the database
        if ($attendance->isDirty()) {
            if ($attendance->save()) {
                return response()->json([
                    'message' => 'تم تعديل بيانات الحضور والانصراف بنجاح',
                    'changed_fields' => $changes
                ], 200);
            } else {
                return response()->json(['message' => 'فشل في تعديل البيانات'], 500);
            }
        } else {
            return response()->json(['message' => 'No changed fields.'], 200);
        }
    }


}

?>