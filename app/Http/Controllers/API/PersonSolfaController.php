<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PersonSolfa;

class PersonSolfaController extends Controller
{
    public function get(Request $request)
    {
        $request->validate([
            'solfa_id' => 'sometimes|exists:PersonSolaf,SolfaID',
            'person_id' => 'sometimes|exists:PersonInformation,PersonID',
            'month' => 'sometimes|date_format:Y-m',
            'year' => 'sometimes|integer|min:1900|max:'.date('Y'),
        ]);

        // Start building the query
        $query = PersonSolfa::query();

        if ($request->has('solfa_id')) {
            $solfa = $query->find($request->solfa_id);
            if (!$solfa) {
                return response()->json(['message' => 'Solfa not found'], 404);
            }
            return response()->json(['data' => $solfa, 'message' => 'Solfa Returned Successfully'], 200);
        }

        // Filter by person_id
        if ($request->has('person_id')) {
            $solfa = PersonSolfa::where('PersonID', $request->person_id);
            if(!$solfa)
                return response()->json(['message' => 'لا يوجد سُلَف مسجلة لهذا الموظف'], 404);
            $query->where('PersonID', $request->person_id)->orderBy('SolfaDate', 'desc');
        }
        
        // Filter by month
        if ($request->has('month')) {
            // Extract the year and month from the input
            [$year, $month] = explode('-', $request->month);
            $query->whereMonth('SolfaDate', $month)->orderBy('SolfaDate', 'desc');
        }

        // Filter by year
        if ($request->has('year')) {
            $query->whereYear('SolfaDate', $request->year)->orderBy('SolfaDate', 'desc');
        }

        // Get the filtered results
        $solaf = $query->get();
    
        if(empty($solaf))
            return response()->json(['message'=>'لا يوجد أي سُلَف مسجلة'], 404);
        return response()->json(['data'=>$solaf, 'message'=>'All Solaf Returned Successfully!'], 200);
    }

    public function insert(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|integer|exists:PersonInformation,PersonID',
            'solfa_date' => 'required|date_format:Y-m-d',
            'solfa_value' => 'required',
            'solfa_reason' => 'sometimes|text'
        ]);

        $solfaDate = $validated['solfa_date'];
        $personId = $validated['person_id'];
        $solfaValue = $validated['solfa_value'];
        $solfaReason = $validated['solfa_reason'];
        
        try{
            $solfa = PersonSolfa::create([
                'PersonID' => $personId,
                'SolfaDate' => $solfaDate,
                'SolfaValue' => $solfaValue,
                'SolfaReason' => $solfaReason
            ]);
            

            if ($solfa->save()) {
                return response()->json([
                    'data' => $solfa,
                    'message' => 'تم تسجيل السلفة بنجاح'
                ], 201); 
            }
            else
            {
                return response()->json([
                    'message' => 'فشل في ادخال السلفة. رجاء المحاولة مرة أخرى',
                ], 500);
            }

        }catch (\Exception $e) {
            return response()->json([
                'message' => 'فشل في ادخال السلفة. رجاء المحاولة مرة أخرى',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        
        $validated = $request->validate([
            'solfa_date' => 'required|date_format:Y-m-d',
            'solfa_reason' => 'required|text',
            'solfa_value' => 'required'
        ]);
        
        $solfaDate = $validated['solfa_date'];
        $solfaReason = $validated['solfa_reason'];
        $solfaValue = $validated['solfa_value'];

        $solfa = PersonSolfa::find($id);
        
        // Check if vacation type exists
        if (!$solfa) {
            return response()->json(['message' => 'Solfa not found'], 404);
        }
    
        // Track changes
        $changes = false;

        if (isset($solfaDate) && $solfa->SolfaDate !== $solfaDate) {
            $solfa->SolfaDate = $solfaDate;
            $changes = true;
        }

        if (isset($solfaReason) && $solfa->SolfaReason !== $solfaReason) {
            $solfa->SolfaReason = $solfaReason;
            $changes = true;
        }

        if (isset($solfaValue) && $solfa->SolfaValue !== $solfaValue) {
            $solfa->SolfaValue = $solfaValue;
            $changes = true;
        }

        // Save the changes to the database
        if ($changes) {
            if ($solfa->save()) {
                return response()->json([
                    'message' => 'تم تعديل بيانات السلفة بنجاح',
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
        $solfa = PersonSolfa::find($id);
        
        // Check if vacation type exists
        if (!$solfa) {
            return response()->json(['message' => 'Solfa not found'], 404);
        }

        if($solfa->delete())
        {
            return response()->json(['message' => 'تم إلغاء السلفة بنجاح'], 200);
        }
    }
}