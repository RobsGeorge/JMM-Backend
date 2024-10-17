<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Http\Response;
use App\Models\WeekDays;
use App\Models\YearlyOfficialVacations;
use App\Models\PersonVacations;
use App\Models\PersonAttendance;
use Carbon\Carbon;

class PersonVacationsController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function get(Request $request)
    {

        // Validate the incoming request query parameters
        $request->validate([
            'vacation_id' => 'sometimes|exists:PersonVacations,PersonVacationID',
            'person_id' => 'sometimes|exists:PersonInformation,PersonID', // Assuming you have a persons table
            'month' => 'sometimes|date_format:Y-m',
            'year' => 'sometimes|integer|min:1900',
            'vacation_type_id' => 'sometimes|exists:VacationTypesTable,VacationTypeID', // Assuming you have a vacation_types table
        ]);

        //return $request;
        // Start building the query
        $query = PersonVacations::query();

        if ($request->has('vacation_id')) {
            $vacation = $query->find($request->vacation_id);
            if (!$vacation) {
                return response()->json(['message' => 'Vacation not found'], 200);
            }
            $person = $vacation->personInformation;
            $vacationType = $vacation->vacationType;

            $response['PersonVacationID'] = $vacation->PersonVacationID;
            $response['PersonID'] = $vacation->PersonID;
            $response['VacationDate'] = $vacation->VacationDate;
            $response['PersonFullName'] = $person->FirstName." ".$person->SecondName." ".$person->ThirdName;
            $response['PersonCode'] = $person->LandlineNumber;
            $response['VacationTypeID'] = $vacation->VacationTypeID;
            $response['VacationTypeName'] = $vacationType->VacationTypeName;

            return response()->json(['data' => $response, 'message' => 'Vacation Returned Successfully'], 200);
        }

        

        // Filter by person_id
        if ($request->has('person_id')) {
            $vacation = PersonVacations::where('PersonID', $request->person_id);
            if(!$vacation)
                return response()->json(['message' => 'لا يوجد أجازات مسجلة لهذا الموظف'], 200);
            $query->where('PersonID', $request->person_id)->orderBy('VacationDate', 'desc');
        }
        
        // Filter by month
        if ($request->has('month')) {
            // Extract the year and month from the input
            [$year, $month] = explode('-', $request->month);
            $query->whereMonth('VacationDate', $month)->whereYear('VacationDate', $year)->orderBy('VacationDate', 'desc');
        }

        // Filter by year
        if ($request->has('year')) {
            $query->whereYear('VacationDate', $request->year)->orderBy('VacationDate', 'desc');
        }

        // Filter by vacation_type_id
        if ($request->has('vacation_type_id')) {
            $query->where('VacationTypeID', $request->vacation_type_id)->orderBy('VacationDate', 'desc');
        }


        // Get the filtered results
        $vacations = $query->get();

        if($vacations->isEmpty()){
            return response()->json(['message'=>'لا يوجد أي أجازات مسجلة'], 200);
        }
        
        $response = array();
        $i=0;
        foreach($vacations as $vacation)
        {
            $person = $vacation->personInformation;
            $vacationType = $vacation->vacationType;
            
            $response[$i]['PersonVacationID'] = $vacation->PersonVacationID;
            $response[$i]['PersonID'] = $vacation->PersonID;
            $response[$i]['VacationDate'] = $vacation->VacationDate;
            $response[$i]['PersonFullName'] = $person->FirstName." ".$person->SecondName." ".$person->ThirdName;
            $response[$i]['PersonCode'] = $person->LandlineNumber;
            $response[$i]['VacationTypeID'] = $vacation->VacationTypeID;
            $response[$i]['VacationTypeName'] = $vacationType->VacationTypeName;

            $i++;
        }

        return response()->json(['data'=>$response, 'message'=>'All Vacations Returned Successfully!'], 200);
    }

    public function insert(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|integer',
            'vacation_date' => 'required|date_format:Y-m-d',
            'vacation_type_id' => 'required|integer|exists:VacationTypesTable,VacationTypeID'
        ]);

        $vacationDate = $validated['vacation_date'];

        //Check if the day is a weekly vacation
        if ($this->isWeeklyVacation($vacationDate)) {
            return response()->json([
                'message' => 'لا يمكن تسجيل أجازة في هذا اليوم حيث أن هذا اليوم هو أجازة أسبوعية'
            ], 200);
        }

        //Check if the day is an official company vacation
        if ($this->getCompanyVacation($vacationDate)) {
            return response()->json([
                'message' => 'لا يمكن تسجيل أجازة في هذا اليوم حيث أن هذا اليوم هو أجازة رسمية'
            ], 200);
        }

        $vacationDateObj = Carbon::createFromFormat('Y-m-d', $vacationDate);
        $today = Carbon::today();

        if($today->greaterThan($vacationDateObj))
        {
            return response()->json(['message' => 'لا يمكن تسجيل الأجازة حيث أن تاريخ اليوم أكبر من تاريخ الأجازة المطلوب تسجيلها '], 400);
        }

        $personId = $validated['person_id'];
        $vacationTypeId = $validated['vacation_type_id'];
        
        
        $existing = PersonVacations::where('VacationDate', $vacationDate)->where('PersonID', $personId)->first();

        if ($existing) 
            return response()->json(['message' => 'لا يمكن ادخال الأجازة لأنه يوجد بالفعل أجازة بنفس التاريخ لنفس الموظف'], 409);
        
        try{
            $vacation = PersonVacations::create([
                'PersonID' => $personId,
                'VacationDate' => $vacationDate,
                'VacationTypeID' => $vacationTypeId
            ]);
            

            if ($vacation->save()) {
                $attendance = PersonAttendance::where('PersonID', $personId)->where('AttendanceDate', $vacationDate)->first();
                if($attendance)
                {
                    $attendance->IsPersonalVacation = 1;
                    $attendance->PersonalVacationID = $vacation->PersonVacationID;
                    $attendance->save();
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
            'vacation_date' => 'required|date_format:Y-m-d',
            'vacation_type_id' => 'required|integer|exists:VacationTypesTable,VacationTypeID'
        ]);
        
        $vacationDate = $validated['vacation_date'];

        //Check if the day is a weekly vacation
        if ($this->isWeeklyVacation($vacationDate)) {
            return response()->json([
                'message' => 'لا يمكن تعديل الأجازة في هذا اليوم حيث أن هذا اليوم هو أجازة أسبوعية'
            ], 200);
        }

        //Check if the day is an official company vacation
        if ($this->getCompanyVacation($vacationDate)) {
            return response()->json([
                'message' => 'لا يمكن تعديل الأجازة في هذا اليوم حيث أن هذا اليوم هو أجازة رسمية'
            ], 200);
        }

        $vacationDateObj = Carbon::createFromFormat('Y-m-d', $vacationDate);
        $today = Carbon::today();

        if($today->greaterThan($vacationDateObj))
        {
            return response()->json(['message' => 'لا يمكن تعديل الأجازة حيث أن تاريخ اليوم أكبر من تاريخ الأجازة المطلوب تعديله '], 400);
        }

        $vacation = PersonVacations::find($id);
        
        // Check if vacation type exists
        if (!$vacation) {
            return response()->json(['message' => 'Vacation not found'], 200);
        }

        $personId = $vacation->PersonID;
        $vacationTypeId = $validated['vacation_type_id'];
        
        
        $existing = PersonVacations::where('VacationDate', $vacationDate)->where('PersonID', $personId)->where('PersonVacationID', '!=', $id)->first();

        if ($existing) 
            return response()->json(['message' => 'لا يمكن تعديل الأجازة لأنه يوجد بالفعل أجازة بنفس التاريخ لنفس الموظف'], 409);
    
        // Track changes
        $changes = false;

        if (isset($vacationDate) && $vacation->VacationDate !== $vacationDate) {
            $attendance = PersonAttendance::where('PersonID', $personId)->where('AttendanceDate', $vacation->VacationDate)->first();
            if($attendance)
            {
                $attendance->IsPersonalVacation = 0;
                $attendance->PersonalVacationID = null;
                $attendance->save();
            }
            
            $vacation->VacationDate = $vacationDate;
            $changes = true;

            $attendance = PersonAttendance::where('PersonID', $personId)->where('AttendanceDate', $vacationDate)->first();
            if($attendance)
            {
                $attendance->IsPersonalVacation = 1;
                $attendance->PersonalVacationID = $vacation->PersonVacationID;
                $attendance->save();
            }
        }

        if (isset($vacationTypeId) && $vacation->VacationTypeID !== $vacationTypeId) {
            $vacation->VacationTypeID = $vacationTypeId;
            $changes = true;
        }

        // Save the changes to the database
        if ($changes) {
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
        $vacation = PersonVacations::find($id);
        
        // Check if vacation type exists
        if (!$vacation) {
            return response()->json(['message' => 'Vacation not found'], 200);
        }

        $vacationDate = Carbon::createFromFormat('Y-m-d', $vacation->VacationDate);
        $today = Carbon::today();

        if($today->greaterThan($vacationDate))
        {
            return response()->json(['message' => 'لا يمكن إلغاء الأجازة حيث أن تاريخ اليوم أكبر من تاريخ الأجازة المطلوب الغاءها '], 400);
        }

        if($vacation->delete())
        {
            $attendance = PersonAttendance::where('PersonID', $vacation->PersonID)->where('AttendanceDate', $vacation->VacationDate)->first();
            if($attendance)
            {
                $attendance->IsPersonalVacation = 0;
                $attendance->PersonalVacationID = null;
                $attendance->save();
            }
            return response()->json(['message' => 'تم إلغاء الأجازة بنجاح'], 200);
        }
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
}