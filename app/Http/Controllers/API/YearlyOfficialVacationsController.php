<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PersonAttendance;
use \Illuminate\Http\Response;
use App\Models\YearlyOfficialVacations;

class YearlyOfficialVacationsController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function get(Request $request)
    {

        $id = $request->query('id');
        $year = $request->query('year');
        $month = $request->query('month');
        $day = $request->query('date');

        if($id) 
        {
            //Get Vacation by ID
            $vacation = YearlyOfficialVacations::find($id);
            if(!$vacation)
            {
                return response()->json(['message' => 'Vacation not found'], 200);
            }
            return response()->json(['data' => $vacation, 'message' => 'Vacation Returned Successfully'], 200);
        }

        if($month)
        {
            // Extract the year and month from the input
            [$year, $month] = explode('-', $month);

            // Get all vacations for the specified month
            $vacations = YearlyOfficialVacations::whereMonth('VacationDate', $month)
                ->whereYear('VacationDate', $year)
                ->orderBy('VacationDate', 'desc')
                ->get();
            
            // Check if there are any vacations
            if ($vacations->isEmpty()) {
                return response()->json(['message' => 'لا يوجد أي أجازات رسمية موجودة في هذا الشهر'], 200);
            }

            return response()->json(['data'=>$vacations, 'message'=>'All Vacations Returned Successfully!'], 200);
        }

        if($year)
        {
            //Get All Vacations In Specific Given Year
            $vacations = YearlyOfficialVacations::where('Year',  $year)->orderBy('VacationDate', 'desc')->get();

            // Check if there are any vacations
            if ($vacations->isEmpty()) {
                return response()->json(['message' => 'لا يوجد أي أجازات رسمية موجودة في هذا العام'], 200);
            }

            return response()->json(['data'=>$vacations, 'message'=>'All Vacations Returned Successfully!'], 200);
        }

        $data = YearlyOfficialVacations::orderBy('VacationDate', 'desc')->get();
        return response()->json(['data'=>$data, 'message'=>'All Vacations Returned Successfully!'], 200);
    }

    public function insert(Request $request)
    {
        $validated = $request->validate([
            'vacation_name' => 'required|string',
            'vacation_date' => 'required|date_format:Y-m-d'
        ]);
        

        // Extract the year from the vacation_date
        $vacationDate = new \DateTime($request->input('vacation_date'));
        $year = $vacationDate->format('Y');
        
        $existing = YearlyOfficialVacations::where('VacationDate', $validated['vacation_date'])
                                    ->first();

        if ($existing) 
            return response()->json(['message' => 'لا يمكن ادخال الأجازة لأنه يوجد بالفعل أجازة بنفس التاريخ'], 409);
        
        try{
            $vacation = YearlyOfficialVacations::create([
                'VacationDate' => $validated['vacation_date'],
                'VacationName' => $validated['vacation_name'],
                'Year' => $year
            ]);
            

            if ($vacation->save()) {
                $attendances = PersonAttendance::where('AttendanceDate', $vacationDate)->get();
                
                if($attendances)
                {
                    foreach($attendances as $attendance)
                    {
                        $attendance->IsCompanyOnVacation = 1;
                        $attendance->YearlyVacationID = $vacation->VacationID;
                        $attendance->save();
                    } 
                }
                return response()->json([
                    'data' => $vacation,
                    'message' => 'تم تسجيل الأجازة بنجاح'
                ], 201); 
            }
            else
            {
                return response()->json([
                    'message' => 'فشل في ادخال الأجازة. رجاء المحاولة مرة أخرى',
                ], 500);
            }

        }catch (\Exception $e) {
            return response()->json([
                'message' => 'فشل في ادخال الأجازة. رجاء المحاولة مرة أخرى',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        
        $validated = $request->validate([
            'vacation_name' => 'required|string',
            'vacation_date' => 'required|date_format:Y-m-d'
        ]);
        
        // Extract the year from the vacation_date
        $vacationDate = new \DateTime($request->input('vacation_date'));
        $year = $vacationDate->format('Y');

        $vacation = YearlyOfficialVacations::find($id);
        
        // Check if vacation type exists
        if (!$vacation) {
            return response()->json(['message' => 'Vacation not found'], 200);
        }
    
        // Track changes
        $changes = false;

        // Update the fields if they are provided and different from the existing values
        if (isset($validated['vacation_name']) && $vacation->VacationName !== $validated['vacation_name']) {
            $vacation->VacationName = $validated['vacation_name'];
            $changes = true;
        }

        if (isset($validated['vacation_date']) && $vacation->VacationDate !== $validated['vacation_date']) {
            $vacation->VacationDate = $validated['vacation_date'];
            $changes = true;
        }

        if (isset($year) && $vacation->Year !== $year) {
            $vacation->Year = $year;
            $changes = true;
        }

        // Save the changes to the database
        if ($changes) {
            $existing = YearlyOfficialVacations::where('VacationDate', $vacation->VacationDate)->where('VacationID', '!=', $vacation->VacationID)->first();
            if ($existing) 
                return response()->json(['message' => 'لا يمكن تعديل الأجازة لأنه يوجد بالفعل أجازة بنفس التاريخ'], 409);

            if ($vacation->save()) {
                return response()->json([
                    'message' => 'تم تعديل بيانات الاجازة بنجاح',
                ], 200);
            } else {
                return response()->json(['message' => 'فشل في تعديل البيانات'], 500);
            }
        } else {
            return response()->json(['message' => 'لا يوجد تغييرات'], 200);
        }
    }

    public function delete($id)
    {
        $vacation = YearlyOfficialVacations::find($id);
        
        // Check if vacation type exists
        if (!$vacation) {
            return response()->json(['message' => 'Vacation not found'], 200);
        }

        $attendances = PersonAttendance::where('AttendanceDate', $vacation->VacationDate)->get();
                
        if($attendances)
        {
            foreach($attendances as $attendance)
            {
                $attendance->IsCompanyOnVacation = 0;
                $attendance->YearlyVacationID = null;
                $attendance->save();
            } 
        }

        if($vacation->delete())
        {
            return response()->json(['message' => 'تم إلغاء الأجازة بنجاح'], 200);
        }
    }
}