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
            'year' => 'sometimes|integer|min:1900',
        ]);

        // Start building the query
        $query = PersonHafez::query();

        if ($request->has('hafez_id')) {
            $hafez = $query->find($request->hafez_id);
            if (!$hafez) {
                return response()->json(['message' => 'Hafez not found'], 200);
            }

            $response = array();
            $person = $hafez->person;

            $response['HafezID'] = $hafez->HafezID;
            $response['PersonID'] = $hafez->PersonID;
            $response['PersonFullName'] = $person->FirstName." ".$person->SecondName." ".$person->ThirdName;
            $response['PersonCode'] = $person->LandlineNumber;
            $response['HafezDate'] = $hafez->HafezDate;
            $response['HafezReason'] = $hafez->HafezReason;
            $response['HafezValue'] = $hafez->HafezValue;

            return response()->json(['data' => $response, 'message' => 'Hafez Returned Successfully'], 200);
        }

        // Filter by person_id
        if ($request->has('person_id')) {
            $hafez = PersonHafez::where('PersonID', $request->person_id);
            if(!$hafez)
                return response()->json(['message' => 'لا يوجد حوافز مسجلة لهذا الموظف'], 200);
            $query->where('PersonID', $request->person_id)->orderBy('HafezDate', 'desc');
        }
        
        // Filter by month
        if ($request->has('month')) {
            // Extract the year and month from the input
            [$year, $month] = explode('-', $request->month);
            $query->whereMonth('HafezDate', $month)->whereYear('HafezDate', $year)->orderBy('HafezDate', 'desc');
        }

        // Filter by year
        if ($request->has('year')) {
            $query->whereYear('HafezDate', $request->year)->orderBy('HafezDate', 'desc');
        }


        // Get the filtered results
        $hawafez = $query->get();


    
        if($hawafez->isEmpty())
            return response()->json(['message'=>'لا يوجد أي حوافز مسجلة'], 200);
        
        $response = array();
        $i=0;
        foreach($hawafez as $hafez)
        {
            $person = $hafez->person;
            
            $response[$i]['HafezID'] = $hafez->HafezID;
            $response[$i]['PersonID'] = $hafez->PersonID;
            $response[$i]['PersonFullName'] = $person->FirstName." ".$person->SecondName." ".$person->ThirdName;
            $response[$i]['PersonCode'] = $person->LandlineNumber;
            $response[$i]['HafezDate'] = $hafez->HafezDate;
            $response[$i]['HafezReason'] = $hafez->HafezReason;
            $response[$i]['HafezValue'] = $hafez->HafezValue;

            $i++;
        }
        
        return response()->json(['data'=>$response, 'message'=>'All Hawafez Returned Successfully!'], 200);
    }

    public function insert(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|integer|exists:PersonInformation,PersonID',
            'hafez_date' => 'required|date_format:Y-m-d',
            'hafez_value' => 'required',
            'hafez_reason' => 'required'
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
                'HafezReason' => $hafezReason
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
            'hafez_reason' => 'required',
            'hafez_value' => 'required'
        ]);
        
        $hafezDate = $validated['hafez_date'];
        $hafezReason = $validated['hafez_reason'];
        $hafezValue = $validated['hafez_value'];

        $hafez = PersonHafez::find($id);
        
        // Check if vacation type exists
        if (!$hafez) {
            return response()->json(['message' => 'Hafez not found'], 200);
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
            return response()->json(['message' => 'Hafez not found'], 200);
        }

        if($hafez->delete())
        {
            return response()->json(['message' => 'تم إلغاء الحافز بنجاح'], 200);
        }
    }
}