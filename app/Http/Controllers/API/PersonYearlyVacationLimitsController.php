<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PersonVacations;
use App\Models\PersonYearlyVacationsLimits;

class PersonYearlyVacationLimitsController extends Controller
{
    public function get(Request $request)
    {
        $request->validate([
            'limit_id' => 'sometimes|exists:PersonYearlyVacationLimits,LimitID',
            'person_id' => 'sometimes|exists:PersonInformation,PersonID',
            'vacation_type_id' => 'sometimes|Integer|exists:VacationTypesTable,VacationTypeID',
            'year' => 'sometimes|integer|min:1900',
        ]);

        // Start building the query
        $query = PersonYearlyVacationsLimits::query();

        if ($request->has('limit_id')) {
            $limit = $query->find($request->limit_id);
            if (!$limit) {
                return response()->json(['message' => 'Limit not found'], 200);
            }
            return response()->json(['data' => $limit, 'message' => 'Limit Returned Successfully'], 200);
        }

        

        // Filter by person_id
        if ($request->has('person_id')) {
            $limit = PersonYearlyVacationsLimits::where('PersonID', $request->person_id);
            if(!$limit)
                return response()->json(['message' => 'لا يوجد حد أجازات مسجلة لهذا الموظف'], 200);
            $query->where('PersonID', $request->person_id)->orderBy('Year', 'desc');
        }

        // Filter by person_id
        if ($request->has('vacation_type_id')) {
            $limit = PersonYearlyVacationsLimits::where('VacationTypeID', $request->vacation_type_id);
            if(!$limit)
                return response()->json(['message' => 'لا يوجد حد أجازات مسجلة لهذا النوع من الأجازة'], 200);
            $query->where('VacationTypeID', $request->vacation_type_id)->orderBy('Year', 'desc');
        }

        // Filter by year
        if ($request->has('year')) {
            $query->where('Year', $request->year)->orderBy('Year', 'desc');
        }


        // Get the filtered results
        $limits = $query->get();
        
        if($limits->isEmpty())
            return response()->json(['message'=>'لا يوجد أي معلومات مسجلة'], 200);
        return response()->json(['data'=>$limits, 'message'=>'All Limits Returned Successfully!'], 200);
    }

    public function getRemaining(Request $request)  {

        $validated = $request->validate([
            'person_id' => 'required|exists:PersonInformation,PersonID',
            'vacation_type_id' => 'required|Integer|exists:VacationTypesTable,VacationTypeID',
            'year' => 'required|integer|min:1900',
        ]);
        
        $vacationLimit = PersonYearlyVacationsLimits::select('VacationLimit')->where('PersonID', $validated['person_id'])->where('VacationTypeID', $validated['vacation_type_id'])->where('Year', $validated['year'])->get()->first()->VacationLimit;

        

        if (!$vacationLimit) {
            return response()->json(['message' => 'Limit not found'], 200);
        }

        $vacationsCountFromLimit = PersonVacations::where('PersonID', $validated['person_id'])->where('VacationTypeID', $validated['vacation_type_id'])->whereYear('VacationDate', $validated['year'])->where('IsBeyondLimit', 0)->count();
        
        $vacationsCountBeyondLimit = PersonVacations::where('PersonID', $validated['person_id'])->where('VacationTypeID', $validated['vacation_type_id'])->whereYear('VacationDate', $validated['year'])->where('IsBeyondLimit', 1)->count();
        
        if($vacationsCountFromLimit >= $vacationLimit)
        {
            $remaining = 0;
        }
        else
        {
            $remaining = $vacationLimit - $vacationsCountFromLimit;
        }

        $data = [
            'VacationsFromLimit' => $vacationsCountFromLimit,
            'VacationsBeyondLimit' => $vacationsCountBeyondLimit,
            'Remaining' => $remaining,
        ];
            
        return response()->json(['data' => $data, 'message' => 'Data Returned Successfully'], 200);
    }

    public function insert(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|exists:PersonInformation,PersonID',
            'vacation_type_id' => 'required|integer|exists:VacationTypesTable,VacationTypeID',
            'year' => 'required|integer|min:1900',
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
                'VacationTypeID' => $vacationTypeID,
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
            return response()->json(['message' => 'Limit not found'], 200);
        }
    
        // Track changes
        $changes = false;

        if (isset($vacationLimit) && $limit->VacationLimit !== $vacationLimit) {
            $limit->VacationLimit = $vacationLimit;
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
            return response()->json(['message' => 'Limit not found'], 200);
        }

        if($limit->delete())
        {
            return response()->json(['message' => 'تم الالغاء بنجاح'], 200);
        }
    }
}