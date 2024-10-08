<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\PersonAttendance;
use App\Models\PersonAbsence;
use App\Models\WeekDays;
use App\Models\YearlyOfficialVacations;
use App\Models\PersonSalary;
use App\Models\PersonKhosoomat;



class AbsenceController extends Controller
{
    public function getAbsence(Request $request)
    {
        $absenceId = $request->query('absence_id');
        $personId = $request->query('person_id');
        $absenceDate = $request->query('absence_date');
        $month = $request->query('month');
        $year = $request->query('year');

        if ($absenceId) {
            // Get absence by AbsenceID
            $absence = PersonAbsence::with('personInformation')->find($absenceId);
            if (!$absence) {
                return response()->json(['message' => 'Absence not found'], 404);
            }

            $data = [
                'AbsenceID' => $absence->AbsenceID,
                'PersonID' => $absence->PersonID,
                'FirstName' => $absence->personInformation->FirstName,
                'SecondName' => $absence->personInformation->SecondName,
                'ThirdName' => $absence->personInformation->ThirdName,
                'LandlineNumber' => $absence->personInformation->LandlineNumber,
                'AbsenceDate' => $absence->AbsenceDate,
                'AbsenceReason' => $absence->AbsenceReason
            ];
            return response()->json(['data' => $data], 200);
        }

        if ($personId) {
            if ($absenceDate) {
                // Get all absences for the specified AbsenceDate
                $absences = PersonAbsence::with('personInformation')->where('PersonID', $personId)->where('AbsenceDate', $absenceDate)->orderBy('AbsenceDate', 'desc')->get();
                // Check if there are any absences
                if ($absences->isEmpty()) {
                    return response()->json(['message' => 'لا يوجد أي غيابات موجودة في هذا التاريخ لهذا الموظف'], 404);
                }
        
                $groupedAbsences = $absences->groupBy('PersonID')->map(function ($absencesByPerson) {
                    // Retrieve the first absence to extract the personal information
                    $firstAbsence = $absencesByPerson->first();
                    return [
                        'PersonID' => $firstAbsence->PersonID,
                        'FirstName' => $firstAbsence->personInformation->FirstName,
                        'SecondName' => $firstAbsence->personInformation->SecondName,
                        'ThirdName' => $firstAbsence->personInformation->ThirdName,
                        'LandlineNumber' => $firstAbsence->personInformation->LandlineNumber,
                        'absences' => $absencesByPerson->map(function ($absence) {
                            return [
                                'AbsenceID' => $absence->AbsenceID,
                                'AbsenceDate' => $absence->AbsenceDate,
                                'AbsenceReason' => $absence->AbsenceReason,
                            ];
                        })->toArray(), // Return as array
                    ];
                });
    
                return response()->json(['data' => $groupedAbsences], 200);
            }
            if ($month) {
                // Extract the year and month from the input
                [$year, $month] = explode('-', $month);

                // Get all absences for the specified PersonID and month
                $absences = PersonAbsence::with('personInformation')
                    ->where('PersonID', $personId)
                    ->whereMonth('AbsenceDate', $month)
                    ->whereYear('AbsenceDate', $year)
                    ->orderBy('AbsenceDate', 'desc')
                    ->get();
                
                // Check if there are any absences
                if ($absences->isEmpty()) {
                    return response()->json(['message' => 'لا يوجد أي غيابات موجودة في هذا الشهر لهذا الموظف'], 404);
                }
        
                // Format the data
                $groupedAbsences = $absences->groupBy('PersonID')->map(function ($absencesByPerson) {
                    // Retrieve the first absence to extract the personal information
                    $firstAbsence = $absencesByPerson->first();
                    return [
                        'PersonID' => $firstAbsence->PersonID,
                        'FirstName' => $firstAbsence->personInformation->FirstName,
                        'SecondName' => $firstAbsence->personInformation->SecondName,
                        'ThirdName' => $firstAbsence->personInformation->ThirdName,
                        'LandlineNumber' => $firstAbsence->personInformation->LandlineNumber,
                        'absences' => $absencesByPerson->map(function ($absence) {
                            return [
                                'AbsenceID' => $absence->AbsenceID,
                                'AbsenceDate' => $absence->AbsenceDate,
                                'AbsenceReason' => $absence->AbsenceReason,
                            ];
                        })->toArray(), // Return as array
                    ];
                });
                return response()->json(['data' => $groupedAbsences], 200);
            }
            if($year)
            {
                // Get all absences for the specified PersonID and month
                $absences = PersonAbsence::with('personInformation')
                    ->where('PersonID', $personId)
                    ->whereYear('AbsenceDate', $year)
                    ->orderBy('AbsenceDate', 'desc')
                    ->get();

                // Check if there are any absences
                if ($absences->isEmpty()) {
                    return response()->json(['message' => 'لا يوجد أي غيابات موجودة في هذا العام لهذا الموظف'], 404);
                }
        
                // Format the data
                $groupedAbsences = $absences->groupBy('PersonID')->map(function ($absencesByPerson) {
                    // Retrieve the first absence to extract the personal information
                    $firstAbsence = $absencesByPerson->first();
                    return [
                        'PersonID' => $firstAbsence->PersonID,
                        'FirstName' => $firstAbsence->personInformation->FirstName,
                        'SecondName' => $firstAbsence->personInformation->SecondName,
                        'ThirdName' => $firstAbsence->personInformation->ThirdName,
                        'LandlineNumber' => $firstAbsence->personInformation->LandlineNumber,
                        'absences' => $absencesByPerson->map(function ($absence) {
                            return [
                                'AbsenceID' => $absence->AbsenceID,
                                'AbsenceDate' => $absence->AbsenceDate,
                                'AbsenceReason' => $absence->AbsenceReason,
                            ];
                        })->toArray(), // Return as array
                    ];
                });
                return response()->json(['data' => $groupedAbsences], 200);
            }
            
            // Get all absences for the specified PersonID
            $absences = PersonAbsence::with('personInformation')->where('PersonID', $personId)->orderBy('AbsenceDate', 'desc')->get();
            // Check if there are any absences
            if ($absences->isEmpty()) {
                return response()->json(['message' => 'لا يوجد أي غيابات موجودة لهذا الموظف'], 404);
            }
    
            // Format the data
            $groupedAbsences = $absences->groupBy('PersonID')->map(function ($absencesByPerson) {
                // Retrieve the first absence to extract the personal information
                $firstAbsence = $absencesByPerson->first();
                return [
                    'PersonID' => $firstAbsence->PersonID,
                    'FirstName' => $firstAbsence->personInformation->FirstName,
                    'SecondName' => $firstAbsence->personInformation->SecondName,
                    'ThirdName' => $firstAbsence->personInformation->ThirdName,
                    'LandlineNumber' => $firstAbsence->personInformation->LandlineNumber,
                    'absences' => $absencesByPerson->map(function ($absence) {
                        return [
                            'AbsenceID' => $absence->AbsenceID,
                            'AbsenceDate' => $absence->AbsenceDate,
                            'AbsenceReason' => $absence->AbsenceReason,
                        ];
                    })->toArray(), // Return as array
                ];
            });
            return response()->json(['data' => $groupedAbsences], 200);
        }

        if ($absenceDate) {
            // Get all absences for the specified AbsenceDate
            $absences = PersonAbsence::with('personInformation')->where('AbsenceDate', $absenceDate)->get();
            // Check if there are any absences
            if ($absences->isEmpty()) {
                return response()->json(['message' => 'لا يوجد أي غيابات موجودة في هذا التاريخ'], 404);
            }
    
            $groupedAbsences = $absences->groupBy('PersonID')->map(function ($absencesByPerson) {
                // Retrieve the first absence to extract the personal information
                $firstAbsence = $absencesByPerson->first();
                return [
                    'PersonID' => $firstAbsence->PersonID,
                    'FirstName' => $firstAbsence->personInformation->FirstName,
                    'SecondName' => $firstAbsence->personInformation->SecondName,
                    'ThirdName' => $firstAbsence->personInformation->ThirdName,
                    'LandlineNumber' => $firstAbsence->personInformation->LandlineNumber,
                    'absences' => $absencesByPerson->map(function ($absence) {
                        return [
                            'AbsenceID' => $absence->AbsenceID,
                            'AbsenceDate' => $absence->AbsenceDate,
                            'AbsenceReason' => $absence->AbsenceReason,
                        ];
                    })->toArray(), // Return as array
                ];
            });

            return response()->json(['data' => $groupedAbsences], 200);
        }

        if ($month) {

            // Extract the year and month from the input
            [$year, $month] = explode('-', $month);

            // Get all absences for the specified month
            $absences = PersonAbsence::with('personInformation')
                ->whereMonth('AbsenceDate', $month)
                ->whereYear('AbsenceDate', $year)
                ->orderBy('AbsenceDate', 'desc')
                ->get();

            
            // Check if there are any absences
            if ($absences->isEmpty()) {
                return response()->json(['message' => 'لا يوجد اي غيابات موجودة في هذا الشهر'], 404);
            }
    
            $groupedAbsences = $absences->groupBy('PersonID')->map(function ($absencesByPerson) {
                // Retrieve the first absence to extract the personal information
                $firstAbsence = $absencesByPerson->first();
                return [
                    'PersonID' => $firstAbsence->PersonID,
                    'FirstName' => $firstAbsence->personInformation->FirstName,
                    'SecondName' => $firstAbsence->personInformation->SecondName,
                    'ThirdName' => $firstAbsence->personInformation->ThirdName,
                    'LandlineNumber' => $firstAbsence->personInformation->LandlineNumber,
                    'absences' => $absencesByPerson->map(function ($absence) {
                        return [
                            'AbsenceID' => $absence->AbsenceID,
                            'AbsenceDate' => $absence->AbsenceDate,
                            'AbsenceReason' => $absence->AbsenceReason,
                        ];
                    })->toArray(), // Return as array
                ];
            });

            return response()->json(['data' => $groupedAbsences], 200);
        }

        if ($year) {
            // Get all absences for the specified year
            $absences = PersonAbsence::with('personInformation')->whereYear('AbsenceDate', $year)->get();

            // Check if there are any absences
            if ($absences->isEmpty()) {
                return response()->json(['message' => 'لا يوجد أي غيابات موجودة في هذا العام'], 404);
            }
    
            $groupedAbsences = $absences->groupBy('PersonID')->map(function ($absencesByPerson) {
                // Retrieve the first absence to extract the personal information
                $firstAbsence = $absencesByPerson->first();
                return [
                    'PersonID' => $firstAbsence->PersonID,
                    'FirstName' => $firstAbsence->personInformation->FirstName,
                    'SecondName' => $firstAbsence->personInformation->SecondName,
                    'ThirdName' => $firstAbsence->personInformation->ThirdName,
                    'LandlineNumber' => $firstAbsence->personInformation->LandlineNumber,
                    'absences' => $absencesByPerson->map(function ($absence) {
                        return [
                            'AbsenceID' => $absence->AbsenceID,
                            'AbsenceDate' => $absence->AbsenceDate,
                            'AbsenceReason' => $absence->AbsenceReason,
                        ];
                    })->toArray(), // Return as array
                ];
            });
        
            return response()->json(['data' => $groupedAbsences->values()], 200);
        }
        return response()->json(['message' => 'Please provide either AbsenceID, PersonID, AbsenceDate, month, or year'], 400);
    }

    public function insertAbsence(Request $request)
    {
        
        // Step 1: Validate the request input
        $validated = $request->validate([
            'person_id' => 'required|exists:PersonInformation,PersonID',
            'absence_date' => 'required|date_format:Y-m-d',
            'absence_reason' => 'nullable|string', 
        ]);
        
        // If attendance is found for this specific date
        if ($this->isWeeklyVacation($validated['absence_date'])) {
            return response()->json([
                'message' => 'لا يمكن تسجيل غياب في هذا اليوم لأن هذا التاريخ هو أجازة أسبوعية لكل الموظفين'
            ], 400);
        }
        else if ($companyVacation = $this->getCompanyVacation($validated['absence_date'])) {
            return response()->json([
                'message' => 'لا يمكن تسجيل الغياب لأن هذا التاريخ هو أجازة رسمية مدفوعة بمناسبة: '.$companyVacation->VacationName.''
            ], 400);
        }
        
        $existingAbsence = PersonAbsence::where('PersonID', $validated['person_id'])
                                    ->where('AbsenceDate', $validated['absence_date'])
                                    ->first();

        if ($existingAbsence) 
            return response()->json(['message' => 'لا يمكن ادخال هذا الغياب لأنه بالفعل موجود سجل غياب لهذا الموظف في هذا اليوم'], 409);

        // Step 2: Process the request and insert a new absence record
        try {
            // Create a new absence record
            $absence = PersonAbsence::create([
                'PersonID' => $validated['person_id'],
                'AbsenceDate' => $validated['absence_date'],
                'AbsenceReason' => $validated['absence_reason'] ?? null,
            ]);

            $personSalary =  PersonSalary::where('PersonID', $validated['person_id'])->orderBy('UpdateTimstamp', 'desc')->first();
            $absenceMinusValue = (float)$personSalary->Salary/30; //Get the Person latest saved salary, divide it by 30 to get the value of the minus for the absence

            $khasm = PersonKhosoomat::create([
                'PersonID' => $validated['person_id'],
                'KhasmDate' =>  date("Y-m-d"),
                'KhasmReason' => "غياب يوم ".$validated['absence_date'],
                'KhasmValue' => $absenceMinusValue, //To be calculated later from Salaries
            ]);

            // Fetch the attendance record based on person_id and date
            $attendance = PersonAttendance::where('PersonID', $validated['person_id'])
            ->where('AttendanceDate', $validated['absence_date'])
            ->first();

            // Check if the attendance record exists
            if (!$attendance) {
            return response()->json(['message' => 'تم تسجيل الغياب بنجاح في سجل الغيابات واضافة خصم للموظف ولكن لم يتم تعديله في كشف حضور وانصراف اليوم لعدم وجود كشف متاح لهذا اليوم'], 404);
            }

            $attendance->IsAbsent = 1;
            // Save the changes to the database
            if ($attendance->isDirty()) {
                if ($attendance->save()) {
                    return response()->json([
                        'message' => 'تم تسجيل الغياب بنجاح واضافة خصم للموظف بسبب الغياب واضافة الغياب لكشف حضور وانصراف اليوم',
                        'absence' => $absence,
                        'khasm' => $khasm
                    ], 201); 
                }
            }

            return response()->json([
                'message' => 'تم تسجيل الغياب بنجاح في سجل الغيابات واضافة خصم للموظف ولكن لم يتم تعديله في كشف حضور وانصراف اليوم',
                'absence' => $absence,
                'khasm' => $khasm
            ], 201);

        } catch (\Exception $e) {
            // Step 4: Handle any errors during the creation process
            return response()->json([
                'message' => 'فشل في ادخال الغياب والخصم. رجاء المحاولة مرة أخرى',
                'error' => $e->getMessage()
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function updateAbsence(Request $request, $absenceId)
    {
        // Validate the request (AbsenceReason is optional)
        $validatedData = $request->validate([
            'absence_reason' => 'nullable|string|max:100',
        ]);

        // Find the absence record by AbsenceID
        $absence = PersonAbsence::find($absenceId);
        
        // Check if absence exists
        if (!$absence) {
            return response()->json(['message' => 'Absence not found'], 404);
        }

        // Update the AbsenceReason (if provided)
        $absence->AbsenceReason = $request->input('absence_reason', $absence->AbsenceReason);

        // Save the changes
        $absence->save();

        // Return success response
        return response()->json(['message' => 'تم التعديل بنجاح', 'data' => $absence], 200);
    }

    public function deleteAbsence($absenceId)
    {
        // Find the absence record by AbsenceID
        $absence = PersonAbsence::find($absenceId);

        // Check if absence exists
        if (!$absence) {
            return response()->json(['message' => 'Absence not found'], 404);
        }

        // Get PersonID and AbsenceDate for related updates
        $personId = $absence->PersonID;
        $absenceDate = $absence->AbsenceDate;

        // Begin a transaction to ensure consistency across tables
        DB::transaction(function () use ($personId, $absenceDate, $absence) {
            // Delete the absence record
            $absence->delete();

            // Update the PersonAttendance table (set IsAbsent to 0)
            DB::table('PersonAttendance')
                ->where('PersonID', $personId)
                ->where('AttendanceDate', $absenceDate)
                ->update(['IsAbsent' => 0]);

            // Remove related record from PersonKhosoomat table
            DB::table('PersonKhosoomat')
                ->where('PersonID', $personId)
                ->where('KhasmDate', $absenceDate)
                ->delete();
        });

        // Return success response
        return response()->json(['message' => 'تم حذف الغياب بنجاح'], 200);
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

    protected function formatAttendanceByDate($attendances, $date)
    {
        $data = [];

        // Group the attendances by PersonID to get each person's record for the given date
        foreach ($attendances->groupBy('PersonID') as $personId => $records) {
            // Get the person's information
            $person = $records->first()->personInformation;

            // Get the attendance for the specified date (since we're grouping by PersonID, there should be only one record per person for this date)
            $attendance = $records->firstWhere('AbsenceDate', $date);

            // Add each person's attendance to the final array
            $data[] = [
                'PersonID' => $person->PersonID,
                'FirstName' => $person->FirstName,
                'SecondName' => $person->SecondName,
                'ThirdName' => $person->ThirdName,
                'LandlineNumber' => $person->LandlineNumber,
                'AbsenceDate' => $attendance->AbsenceDate,
                'AbsenceReason' => $attendance->AbsenceReason,
            ];
        }

        return $data;
    }

    function formatAttendanceByPerson($absences)
    {
        if ($absences->isEmpty()) {
            return []; // Return an empty array if no absences are found
        }

        // Initialize an array to hold formatted absence data
        $formattedData = [];

        // Group absences by PersonID
        foreach ($absences as $attendance) {
            $personId = $attendance->PersonID;

            // Initialize a new entry for the person if not already done
            if (!isset($formattedData[$personId])) {
                $formattedData[$personId] = [
                    'PersonID' => $personId,
                    'FirstName' => $attendance->personInformation->FirstName,
                    'SecondName' => $attendance->personInformation->SecondName,
                    'ThirdName' => $attendance->personInformation->ThirdName,
                    'LandlineNumber' => $attendance->personInformation->LandlineNumber,
                    'absence' => [], // Initialize the attendance array
                ];
            }

            // Add the attendance details for this person
            $formattedData[$personId]['attendance'][] = [
                'AbsenceDate' => $attendance->AbsenceDate,
                'AbsenceReason' => $attendance->AbsenceReason,
            ];
        }

        // Return the formatted data as an array of persons
        return array_values($formattedData);
    }

}

?>