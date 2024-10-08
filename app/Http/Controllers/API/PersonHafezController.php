<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PersonHafez;

class PersonHafezController extends Controller
{
    public function get(Request $request)
    {
        $request->validate([
            'hafez_id' => 'sometimes|exists:PersonHawafez,HafezID',
            'person_id' => 'sometimes|exists:PersonInformation,PersonID',
            'month' => 'sometimes|date_format:Y-m',
            'year' => 'sometimes|integer|min:1900|max:'.date('Y'),
        ]);

        // Start building the query
        $query = PersonHafez::query();

        if ($request->has('hafez_id')) {
            $hafez = $query->find($request->hafez_id);
            if (!$hafez) {
                return response()->json(['message' => 'Hafez not found'], 404);
            }
            return response()->json(['data' => $hafez, 'message' => 'Hafez Returned Successfully'], 200);
        }

        // Filter by person_id
        if ($request->has('person_id')) {
            $hafez = PersonHafez::where('PersonID', $request->person_id);
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
            'hafez_date' => 'required|date_format:Y-m-d',
            'hafez_value' => 'required|float',
            'hafez_reason' => 'sometimes|text'
        ]);

        $hafezDate = $validated['hafez_date'];
        $personId = $validated['person_id'];
        $hafezValue = $validated['hafez_value'];
        $hafezReason = $validated['hafez_reason'];
        
        try{
            $hafez = PersonHafez::create([
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

        $hafez = PersonHafez::find($id);
        
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
        $hafez = PersonHafez::find($id);
        
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