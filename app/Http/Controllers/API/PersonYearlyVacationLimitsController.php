<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\PersonKhosoomat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Illuminate\Http\Response;
use App\Models\WeekDays;
use Session;
use App\Models\Taameen;
use App\Models\VacationType;
use App\Models\WorkingTimes;
use App\Models\YearlyOfficialVacations;
use App\Models\PersonVacations;
use App\Models\PersonYearlyVacationsLimits;
use Carbon\Carbon;

class PersonYearlyVacationLimits extends Controller
{
    public function get(Request $request)
    {
        $request->validate([
            'limit_id' => 'sometimes|exists:PersonYearlyVacationsLimits,ID',
            'person_id' => 'sometimes|exists:PersonInformation,PersonID',
            'vacation_type_id' => 'sometimes|Integer|exists:VacationTypesTable,VacationTypeID',
            'year' => 'sometimes|integer|min:1900|max:'.date('Y'),
        ]);

        // Start building the query
        $query = PersonYearlyVacationsLimits::query();

        if ($request->has('limit_id')) {
            $limit = $query->find($request->limit_id);
            if (!$limit) {
                return response()->json(['message' => 'Limit not found'], 404);
            }
            return response()->json(['data' => $limit, 'message' => 'Khasm Returned Successfully'], 200);
        }

        

        // Filter by person_id
        if ($request->has('person_id')) {
            $limit = PersonYearlyVacationsLimits::where('PersonID', $request->person_id);
            if(!$limit)
                return response()->json(['message' => 'لا يوجد حد أجازات مسجلة لهذا الموظف'], 404);
            $query->where('PersonID', $request->person_id)->orderBy('Year', 'desc');
        }

        // Filter by person_id
        if ($request->has('vacation_type_id')) {
            $limit = PersonYearlyVacationsLimits::where('VacationTypeID', $request->vacation_type_id);
            if(!$limit)
                return response()->json(['message' => 'لا يوجد حد أجازات مسجلة لهذا النوع من الأجازة'], 404);
            $query->where('VacationTypeID', $request->vacation_type_id)->orderBy('Year', 'desc');
        }

        // Filter by year
        if ($request->has('year')) {
            $query->whereYear('Year', $request->year)->orderBy('Year', 'desc');
        }


        // Check if no query parameters are present
        if (!$request->hasAny(['limit_id', 'person_id', 'vacation_type_id', 'year'])) {
            // If no query parameters, return all records
            $imits = $query->get();
            return response()->json(['data'=>$imits, 'message'=>'All Limits Returned Successfully!'], 200);
        }

        // Get the filtered results
        $limits = $query->get();
    
        if(empty($khosoomat))
            return response()->json(['message'=>'لا يوجد أي معلومات مسجلة'], 404);
        return response()->json(['data'=>$khosoomat, 'message'=>'All Limits Returned Successfully!'], 200);
    }

    public function insert(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|exists:PersonInformation,PersonID',
            'vacation_type_id' => 'required|integer|exists:VacationTypesTable,VacationTypeID',
            'year' => 'required|integer|min:1900|max:'.date('Y'),
            'vacation_limit_per_year' => 'required|integer',
        ]);

        $personId = $validated['person_id'];
        $vacationTypeID = $validated['vacation_type_id'];
        $year = $validated['year'];
        $vacationLimit = $validated['vacation_limit_per_year'];
        
        try{
            $limit = PersonYearlyVacationsLimits::create([
                'PersonID' => $personId,
                'Year' => $year,
                'vacationTypeID' => $vacationTypeID,
                'VacationLimit' => $vacationLimit
            ]);
            

            if ($limit->save()) {
                return response()->json([
                    'data' => $limit,
                    'message' => 'تم التسجيل بنجاح'
                ], 201); 
            }
            else
            {
                return response()->json([
                    'message' => 'فشل في الادخال. رجاء المحاولة مرة أخرى',
                ], 500);
            }

        }catch (\Exception $e) {
            return response()->json([
                'message' => 'فشل في الادخال. رجاء المحاولة مرة أخرى',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'vacation_limit_per_year' => 'required|integer'
        ]);
        
        $vacationLimit = $validated['vacation_limit_per_year'];
        $limit = PersonYearlyVacationsLimits::find($id);
        
        // Check if vacation type exists
        if (!$limit) {
            return response()->json(['message' => 'Limit not found'], 404);
        }
    
        // Track changes
        $changes = false;

        if (isset($vacationLimit) && $limit->VacationLimit !== $vacationLimit) {
            $limit->KhasmValue = $vacationLimit;
            $changes = true;
        }

        // Save the changes to the database
        if ($changes) {
            if ($limit->save()) {
                return response()->json([
                    'message' => 'تم تعديل البيانات بنجاح',
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
        $limit = PersonYearlyVacationsLimits::find($id);
        
        // Check if vacation type exists
        if (!$limit) {
            return response()->json(['message' => 'Limit not found'], 404);
        }

        if($limit->delete())
        {
            return response()->json(['message' => 'تم الالغاء بنجاح'], 200);
        }
    }
}