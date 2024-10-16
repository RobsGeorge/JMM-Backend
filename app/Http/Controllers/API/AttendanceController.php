<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\PersonAttendance;
use App\Models\PersonVacations;
use App\Models\WeekDays;
use App\Models\YearlyOfficialVacations;
use App\Models\Person;
use App\Models\WorkingTimes;




class AttendanceController extends Controller
{
    public function getAttendance(Request $request)
    {
        // Extract query parameters
        $personId = $request->query('person_id');
        $date = $request->query('date');
        $month = $request->query('month');


        if($personId)
        {
            $exists = Person::select('PersonID')->where('PersonID', $personId)->where('IsDeleted','0')->exists();
            if(!$exists)
                return response()->json(['message'=>'Person not found', 'status'=>200]);
        }

        $existingRecords = PersonAttendance::where('AttendanceDate', $date)->get();
        $personsIDs = Person::where('IsDeleted', 0)->pluck('PersonID')->toArray();

        
        if ($existingRecords->isNotEmpty()) {
            $attendances = PersonAttendance::where('AttendanceDate', $date)->pluck('PersonID')->toArray();

            if(count($existingRecords)!=count($personsIDs))
            {
                $personsIDs = array_diff($personsIDs, $attendances);
            }
        }
        

        // Start a query on the PersonAttendance model
        $query = PersonAttendance::query();

        // Check if both date and month are provided; prioritize date
        if ($date) {
            $validated = $request->validate([
                'date' => 'date_format:Y-m-d'
            ], [
                'date.date_format' => 'The attendance date must be in the format YYYY-MM-DD.',
            ]);

            $exists =  PersonAttendance::select('AttendanceID')->where('AttendanceDate', $date)->exists();
            if(!$exists)
                return response()->json(['message'=>'لا يوجد أي كشوفات حضور او انصراف مسجلة لهذا التاريخ', 'status'=>200]);

            if ($personId) {
                $query->where('PersonID', $personId);
            }

            $query->whereDate('AttendanceDate', $date);
            
            $attendances = $query->with(['personInformation' => function ($query) {
                $query->select('PersonID', 'FirstName', 'SecondName', 'ThirdName', 'LandlineNumber', 'IsDeleted')
                      ->where('IsDeleted', 0);
                }])->select('PersonID', 'AttendanceDate', 'WorkStartTime', 'WorkEndTime', 'IsAbsent', 'IsWeeklyVacation', 'IsCompanyOnVacation', 'IsPersonalVacation')
                ->orderBy('AttendanceDate', 'asc')
                ->orderBy('PersonID', 'asc')
                ->get();
            
            $formattedData = $this->formatAttendanceByDate($attendances, $date);

            if(empty($formattedData))
                return response()->json(['message' => 'لا يوجد أي كشوف حضور او انصراف مسجلة في هذا التاريخ'], 200);

            return response()->json(['AtendanceData' => $formattedData], 200);
        }

        // If only month is provided, fetch attendances for the entire month
        if ($month) {
            $validated = $request->validate([
                'month' => 'date_format:Y-m'
            ], [
                'month.date_format' => 'The attendance month must be in the format YYYY-MM.',
            ]);
            
            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

            if ($personId) {
                $query->where('PersonID', $personId);
            }

            $query->whereBetween('AttendanceDate', [$startDate, $endDate]);

            $attendances = $query->with(['personInformation' => function ($query) {
                $query->select('PersonID', 'FirstName', 'SecondName', 'ThirdName', 'LandlineNumber', 'IsDeleted')
                      ->where('IsDeleted', 0);
                }])->select('PersonID', 'AttendanceDate', 'WorkStartTime', 'WorkEndTime', 'IsAbsent', 'IsWeeklyVacation', 'IsCompanyOnVacation', 'IsPersonalVacation')
                ->orderBy('AttendanceDate', 'asc')
                ->orderBy('PersonID', 'asc')
                ->get();

            
            // Format response for multiple attendances in a month
            $formattedData = $this->formatAttendanceByMonth($attendances, $month);
            return response()->json($formattedData, 200);
        }

        // If only person_id is provided, fetch all attendances for that person
        if ($personId) {
            
            $query->where('PersonID', $personId);

            $attendances = $query->with(['personInformation' => function ($query) {
                $query->select('PersonID', 'FirstName', 'SecondName', 'ThirdName', 'LandlineNumber', 'IsDeleted')
                      ->where('IsDeleted', 0);
                }])->select('PersonID', 'AttendanceDate', 'WorkStartTime', 'WorkEndTime', 'IsAbsent', 'IsWeeklyVacation', 'IsCompanyOnVacation', 'IsPersonalVacation')
                ->orderBy('AttendanceDate', 'asc')
                ->orderBy('PersonID', 'asc')
                ->get();

            $formattedData = $this->formatAttendanceByPerson($attendances);

            return response()->json(['AttendanceData' => $formattedData, 'message' => 'All attendances returned for the person.'], 200);
        }

        // If no parameters are provided, return all attendances for all persons
        $attendances = $query->with(['personInformation' => function ($query) {
            $query->select('PersonID', 'FirstName', 'SecondName', 'ThirdName', 'LandlineNumber', 'IsDeleted')
                  ->where('IsDeleted', 0);
            }])->select('PersonID', 'AttendanceDate', 'WorkStartTime', 'WorkEndTime', 'IsAbsent', 'IsWeeklyVacation', 'IsCompanyOnVacation', 'IsPersonalVacation')
            ->orderBy('AttendanceDate', 'asc')
            ->orderBy('PersonID', 'asc')
            ->get();

        $formattedData = $this->formatAttendanceByPerson($attendances);


        return response()->json(['AttendanceData' => $formattedData, 'message' => 'All attendances returned.'], 200);
    }

    // Helper function to format multiple attendances by month
    protected function formatAttendanceByMonth($attendances, $month)
    {
        $data = [];
        $daysInMonth = Carbon::createFromFormat('Y-m', $month)->daysInMonth;

        foreach ($attendances->groupBy('PersonID') as $personId => $records) 
        {
            $person = $records->first()->personInformation;
            if(!empty($person))
            {
                $attendanceDays = [];
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $attendanceForDay = $records->firstWhere('AttendanceDate', Carbon::createFromFormat('Y-m-d', $month . '-' . $day)->toDateString());

                    $attendanceDays[] = [
                        'Day' => $day,
                        'WorkStartTime' => optional($attendanceForDay)->WorkStartTime ?? null,
                        'WorkEndTime' => optional($attendanceForDay)->WorkEndTime,
                        'IsAbsent' => optional($attendanceForDay)->IsAbsent ?? null,
                        'IsWeeklyVacation' => optional($attendanceForDay)->IsWeeklyVacation ?? null,
                        'IsCompanyOnVacation' => optional($attendanceForDay)->IsCompanyOnVacation ?? null,
                        'IsPersonalVacation' => optional($attendanceForDay)->IsPersonalVacation ?? null,
                    ];
                }

                $data[] = [
                    'PersonID' => $person->PersonID,
                    'FirstName' => $person->FirstName,
                    'SecondName' => $person->SecondName,
                    'ThirdName' => $person->ThirdName,
                    'LandlineNumber' => $person->LandlineNumber,
                    'attendance' => $attendanceDays,
                ];
            }
        }
        return $data;
    }

    protected function formatAttendanceByDate($attendances, $date)
    {
        $data = [];
        // Group the attendances by PersonID to get each person's record for the given date
        foreach ($attendances->groupBy('PersonID') as $personId => $records) 
        {
            // Get the person's information
            $person = $records->first()->personInformation;

            if(!empty($person))
            {
                // Get the attendance for the specified date (since we're grouping by PersonID, there should be only one record per person for this date)
                $attendance = $records->firstWhere('AttendanceDate', $date);
                // Add each person's attendance to the final array
                $data[] = [
                    'PersonID' => $person->PersonID,
                    'FirstName' => $person->FirstName,
                    'SecondName' => $person->SecondName,
                    'ThirdName' => $person->ThirdName,
                    'LandlineNumber' => $person->LandlineNumber,
                    'AttendanceDate' => $attendance->AttendanceDate,
                    'WorkStartTime' => $attendance->WorkStartTime,
                    'WorkEndTime' => $attendance->WorkEndTime,
                    'IsAbsent' => $attendance->IsAbsent,
                    'IsWeeklyVacation' => $attendance->IsWeeklyVacation,
                    'IsCompanyOnVacation' => $attendance->IsCompanyOnVacation,
                    'IsPersonalVacation' => $attendance->IsPersonalVacation
                ];
            }
        }
        return $data;
    }

    function formatAttendanceByPerson($attendances)
    {
        if ($attendances->isEmpty()) {
            return []; // Return an empty array if no attendances are found
        }

        // Initialize an array to hold formatted attendance data
        $formattedData = [];

        // Group attendances by PersonID
        foreach ($attendances as $attendance) {
            $personId = $attendance->PersonID;
            if(!empty($personId))
            {
                // Initialize a new entry for the person if not already done
                if (!isset($formattedData[$personId])) {
                    $formattedData[$personId] = [
                        'PersonID' => $personId,
                        'FirstName' => $attendance->personInformation->FirstName,
                        'SecondName' => $attendance->personInformation->SecondName,
                        'ThirdName' => $attendance->personInformation->ThirdName,
                        'LandlineNumber' => $attendance->personInformation->LandlineNumber,
                        'attendance' => [], // Initialize the attendance array
                    ];
                }

                // Add the attendance details for this person
                $formattedData[$personId]['attendance'][] = [
                    'AttendanceDate' => $attendance->AttendanceDate, // Get the day of the month
                    'WorkStartTime' => $attendance->WorkStartTime,
                    'WorkEndTime' => $attendance->WorkEndTime,
                    'IsAbsent' => (int) $attendance->IsAbsent,
                    'IsWeeklyVacation' => (int) $attendance->IsWeeklyVacation,
                    'IsCompanyOnVacation' => (int) $attendance->IsCompanyOnVacation,
                    'IsPersonalVacation' => (int) $attendance->IsPersonalVacation,
                ];
            }
        }
        // Return the formatted data as an array of persons
        return array_values($formattedData); // Reset array keys
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

        $date = $validated['date'];

        // Step 1: Check if the attendance for the given date is already in the database
        $existingRecords = PersonAttendance::where('AttendanceDate', $date)->get();
        $personsIDs = Person::where('IsDeleted', 0)->where('WorkStartDate', '<=', $date)->pluck('PersonID')->toArray();

        
        if ($existingRecords->isNotEmpty()) {
            $attendances = PersonAttendance::where('AttendanceDate', $date)->pluck('PersonID')->toArray();

            if(count($existingRecords)!=count($personsIDs))
            {
                $personsIDs = array_diff($personsIDs, $attendances);
                return $personsIDs;
            }
            else
            {
                return response()->json([
                    'message' => 'كشف الحضور والانصراف موجود بالفعل لكل الموظفين'
                ], 200);
            }
        }

        // Step 2: Initialize attendance records array
        $attendanceRecords = [];

        // Step 4: Check if the day is a weekly vacation and insert records
        if ($this->isWeeklyVacation($date)) {
            foreach ($personsIDs as $personID) {
                $attendanceRecords[] = $this->createAttendanceRecord($personID, $date, true, false, false);
            }
            return response()->json([
                'message' => 'تم تسجيل حضور وانصراف اليوم على انه أجازة أسبوعية لكل الموظفين'
            ], 200);
        }

        // Step 5: Check if the day is an official company vacation and insert records
        if ($companyVacation = $this->getCompanyVacation($date)) {
            foreach ($personsIDs as $personID) {
                $this->createAttendanceRecord($personID, $date, false, true, false, $companyVacation->VacationID);
            }
            return response()->json([
                'message' => 'تم تسجيل حضور وانصراف اليوم على انه أجازة رسمية مدفوعة لكل الموظفين',
            ], 200);
        }

        // Step 6: Create records for normal working days
        foreach ($personsIDs as $personID) {
            $this->createAttendanceRecord($personID, $date, false, false, false);

            // Step 7: Check if the employee is on personal vacation for this date
            $this->updateAttendanceWithVacationStatus($personID, $date);
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
            ->whereDate('VacationDate', $date)
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
            return response()->json(['message' => 'لا يوجد كشف مفتوح لهذا الشخص في هذا التاريخ'], 200);
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

    public function deleteAttendance(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);
        $date = $validated['date'];

        $existingRecords = PersonAttendance::where('AttendanceDate', $date)->get();
        
        if ($existingRecords->isNotEmpty()) {
            foreach($existingRecords as $record)
            {
                $record->delete();
            }
            return response()->json(['message' => 'تم حذف البيانات بنجاح'], 200);
        }
        else
        {
            return  response()->json(['message' => 'No attendance records found for the given date.'], 200);
        }
    }


}

?>