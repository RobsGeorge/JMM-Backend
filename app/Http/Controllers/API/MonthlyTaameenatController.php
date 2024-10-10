<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MonthlyTaameenat;

class MonthlyTaameenatController extends Controller
{
    public function get(Request $request)
    {
        $request->validate([
            'taameen_id' => 'sometimes|exists:MonthlyTaameenat,ID',
            'person_id' => 'sometimes|exists:PersonInformation,PersonID',
            'month' => 'sometimes|date_format:Y-m'
        ]);

        // Start building the query
        $query = MonthlyTaameenat::query();

        if ($request->has('taameen_id')) {
            $taameen = $query->find($request->hafez_id);
            if (!$taameen) {
                return response()->json(['message' => 'Hafez not found'], 404); 
            }
            return response()->json(['data' => $taameen, 'message' => 'Hafez Returned Successfully'], 200);
        }

        // Filter by person_id
        if ($request->has('person_id')) {
            $hafez = MonthlyTaameenat::where('PersonID', $request->person_id);
            if(!$hafez)
                return response()->json(['message' => 'لا يوجد حوافز مسجلة لهذا الموظف'], 404);
            $query->where('PersonID', $request->person_id)->orderBy('HafezDate', 'desc');
        }
        
        // Filter by month
        if ($request->has('month')) {
            // Extract the year and month from the input
            [$year, $month] = explode('-', $request->month);
            $query->whereMonth('HafezDate', $month)->orderBy('HafezDate', 'desc');
        }

        // Filter by year
        if ($request->has('year')) {
            $query->whereYear('HafezDate', $request->year)->orderBy('HafezDate', 'desc');
        }


        // Check if no query parameters are present
        if (!$request->hasAny(['hafez_id', 'person_id', 'month', 'year'])) {
            // If no query parameters, return all records
            $hawafez = $query->get();
            return response()->json(['data'=>$hawafez, 'message'=>'All Hawafez Returned Successfully!'], 200);
        }

        // Get the filtered results
        $hawafez = $query->get();
    
        if(empty($khosoomat))
            return response()->json(['message'=>'لا يوجد أي حوافز مسجلة'], 404);
        return response()->json(['data'=>$hawafez, 'message'=>'All Hawafez Returned Successfully!'], 200);
    }

    public function insert(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|integer|exists:PersonInformation,PersonID',
            'month' => 'required|date_format:Y-m',
        ]);

        $hafezDate = $validated['hafez_date'];
        $personId = $validated['person_id'];
        $hafezValue = $validated['hafez_value'];
        $hafezReason = $validated['hafez_reason'];
        
        try{
            $hafez = MonthlyTaameenat::create([
                'PersonID' => $personId,
                'HafezDate' => $hafezDate,
                'HafezValue' => $hafezValue,
                'HafezReaons' => $hafezReason
            ]);
            

            if ($hafez->save()) {
                return response()->json([
                    'data' => $hafez,
                    'message' => 'تم تسجيل الحافز بنجاح'
                ], 201); 
            }
            else
            {
                return response()->json([
                    'message' => 'فشل في ادخال الحافز. رجاء المحاولة مرة أخرى',
                ], 500);
            }

        }catch (\Exception $e) {
            return response()->json([
                'message' => 'فشل في ادخال الحافز. رجاء المحاولة مرة أخرى',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        
        $validated = $request->validate([
            'hafez_date' => 'required|date_format:Y-m-d',
            'hafez_reason' => 'required|text',
            'hafez_value' => 'required|float'
        ]);
        
        $hafezDate = $validated['hafez_date'];
        $hafezReason = $validated['hafez_reason'];
        $hafezValue = $validated['hafez_value'];

        $hafez = MonthlyTaameenat::find($id);
        
        // Check if vacation type exists
        if (!$hafez) {
            return response()->json(['message' => 'Hafez not found'], 404);
        }
    
        // Track changes
        $changes = false;

        if (isset($hafezDate) && $hafez->HafezDate !== $hafezDate) {
            $hafez->HafezDate = $hafezDate;
            $changes = true;
        }

        if (isset($hafezReason) && $hafez->HafezReason !== $hafezReason) {
            $hafez->HafezReason = $hafezReason;
            $changes = true;
        }

        if (isset($hafezValue) && $hafez->HafezValue !== $hafezValue) {
            $hafez->HafezValue = $hafezValue;
            $changes = true;
        }

        // Save the changes to the database
        if ($changes) {
            if ($hafez->save()) {
                return response()->json([
                    'message' => 'تم تعديل بيانات الحافز بنجاح',
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
        $hafez = MonthlyTaameenat::find($id);
        
        // Check if vacation type exists
        if (!$hafez) {
            return response()->json(['message' => 'Hafez not found'], 404);
        }

        if($hafez->delete())
        {
            return response()->json(['message' => 'تم إلغاء الحافز بنجاح'], 200);
        }
    }
}