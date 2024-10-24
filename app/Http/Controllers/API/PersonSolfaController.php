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
            'year' => 'sometimes|integer|min:1900',
        ]);

        // Start building the query
        $query = PersonSolfa::query();

        if ($request->has('solfa_id')) {
            $solfa = $query->find($request->solfa_id);
            if (!$solfa) {
                return response()->json(['message' => 'Solfa not found'], 200);
            }
            
            $response = array();
            $person = $solfa->person;

            $response['SolfaID'] = $solfa->SolfaID;
            $response['PersonID'] = $solfa->PersonID;
            $response['PersonFullName'] = $person->FirstName." ".$person->SecondName." ".$person->ThirdName;
            $response['PersonCode'] = $person->LandlineNumber;
            $response['SolfaDate'] = $solfa->SolfaDate;
            $response['SolfaReason'] = $solfa->SolfaReason;
            $response['SolfaValue'] = $solfa->SolfaValue;
            $response['SolfaFromMainSalary'] = $solfa->SolfaFromMainSalary;

            return response()->json(['data' => $response, 'message' => 'Solfa Returned Successfully'], 200);
            
        }

        // Filter by person_id
        if ($request->has('person_id')) {
            $solfa = PersonSolfa::where('PersonID', $request->person_id);
            if(!$solfa)
                return response()->json(['message' => 'لا يوجد سُلَف مسجلة لهذا الموظف'], 200);
            $query->where('PersonID', $request->person_id)->orderBy('SolfaDate', 'desc');
        }
        
        // Filter by month
        if ($request->has('month')) {
            // Extract the year and month from the input
            [$year, $month] = explode('-', $request->month);
            $query->whereMonth('SolfaDate', $month)->whereYear('SolfaDate', $year)->orderBy('SolfaDate', 'desc');
        }

        // Filter by year
        if ($request->has('year')) {
            $query->whereYear('SolfaDate', $request->year)->orderBy('SolfaDate', 'desc');
        }

        // Get the filtered results
        $solaf = $query->get();
    
        if($solaf->isEmpty())
            return response()->json(['message'=>'لا يوجد أي سُلَف مسجلة'], 200);

        $response = array();
        $i=0;
        foreach($solaf as $solfa)
        {
            $person = $solfa->person;
            
            $response[$i]['SolfaID'] = $solfa->SolfaID;
            $response[$i]['PersonID'] = $solfa->PersonID;
            $response[$i]['PersonFullName'] = $person->FirstName." ".$person->SecondName." ".$person->ThirdName;
            $response[$i]['PersonCode'] = $person->LandlineNumber;
            $response[$i]['SolfaDate'] = $solfa->SolfaDate;
            $response[$i]['SolfaReason'] = $solfa->SolfaReason;
            $response[$i]['SolfaValue'] = $solfa->SolfaValue;
            $response[$i]['SolfaFromMainSalary'] = $solfa->SolfaFromMainSalary;

            $i++;
        }
        return response()->json(['data'=>$response, 'message'=>'All Solaf Returned Successfully!'], 200);
    }

    public function insert(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|integer|exists:PersonInformation,PersonID',
            'solfa_date' => 'required|date_format:Y-m-d',
            'solfa_value' => 'required',
            'solfa_reason' => 'required',
            'solfa_from_main_salary' => 'required|integer'
        ]);

        $solfaDate = $validated['solfa_date'];
        $personId = $validated['person_id'];
        $solfaValue = $validated['solfa_value'];
        $solfaReason = $validated['solfa_reason'];
        $isFromMainSalary = $validated['solfa_from_main_salary'];
        
        try{
            $solfa = PersonSolfa::create([
                'PersonID' => $personId,
                'SolfaDate' => $solfaDate,
                'SolfaValue' => $solfaValue,
                'SolfaReason' => $solfaReason,
                'SolfaFromMainSalary' => $isFromMainSalary,
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
            'solfa_reason' => 'required',
            'solfa_value' => 'required',
            'solfa_from_main_salary' => 'required|integer'
        ]);
        
        $solfaDate = $validated['solfa_date'];
        $solfaReason = $validated['solfa_reason'];
        $solfaValue = $validated['solfa_value'];
        $isFromMainSalary = $validated['solfa_from_main_salary'];

        $solfa = PersonSolfa::find($id);
        
        // Check if vacation type exists
        if (!$solfa) {
            return response()->json(['message' => 'Solfa not found'], 200);
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

        if (isset($isFromMainSalary) && $solfa->SolfaFromMainSalary !== $isFromMainSalary)
        {
            $solfa->SolfaFromMainSalary = $isFromMainSalary;
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
            return response()->json(['message' => 'Solfa not found'], 200);
        }

        if($solfa->delete())
        {
            return response()->json(['message' => 'تم إلغاء السلفة بنجاح'], 200);
        }
    }
}