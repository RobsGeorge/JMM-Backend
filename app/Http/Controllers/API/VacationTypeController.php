<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\VacationType;

class VacationTypeController extends Controller
{
    public function get(Request $request)
    {

        $id = $request->query('id');

        if($id)
        {
            //Get Vacation Type by ID
            $vacationType = VacationType::find($id);
            if(!$vacationType)
            {
                return response()->json(['message' => 'Vacation Type not found'], 404);
            }
            return response()->json(['data' => $vacationType, 'message' => 'Vacation Type Returned Successfully'], 200);
        }

        $data = VacationType::all();
        return response()->json(['data'=>$data, 'message'=>'Vacation Types Returned Successfully!'], 200);
    }

    public function insert(Request $request)
    {
        
        $validated = $request->validate([
            'vacation_type_name' => 'required|string',
            'vacation_type_description' => 'nullable|string', 
        ]);
        
        $existing = VacationType::where('VacationTypeName', $validated['vacation_type_name'])
                                    ->first();

        if ($existing) 
            return response()->json(['message' => 'لا يمكن ادخال نوع الأجازة لأنه يوجد بالفعل نوع أجازة بنفس الاسم'], 409);
        
        try{
            $vacationType = VacationType::create([
                'VacationTypeName' => $validated['vacation_type_name'],
                'VacationTypeDescription' => $validated['vacation_type_description']?? null,
            ]);
            return $vacationType;

            if ($vacationType->save()) {
                return response()->json([
                    'data' => $vacationType,
                    'message' => 'تم تسجيل نوع الأجازة بنجاح'
                ], 201); 
            }
            else
            {
                return response()->json([
                    'message' => 'فشل في ادخال نوع الأجازة. رجاء المحاولة مرة أخرى',
                ], 500);
            }

        }catch (\Exception $e) {
            return response()->json([
                'message' => 'فشل في ادخال نوع الأجازة. رجاء المحاولة مرة أخرى',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'vacation_type_name' => 'required|string',
            'vacation_type_description' => 'nullable|string', 
        ]);
        
        $vacationType = VacationType::find($id);
        
        // Check if vacation type exists
        if (!$vacationType) {
            return response()->json(['message' => 'Vacation Type not found'], 404);
        }
    
        // Track changes
        $changes = false;

        // Update the fields if they are provided and different from the existing values
        if (isset($validated['vacation_type_name']) && $vacationType->VacationTypeName !== $validated['vacation_type_name']) {
            $vacationType->VacationTypeName = $validated['vacation_type_name'];
            $changes = true;
        }

        if (isset($validated['vacation_type_description']) && $vacationType->VacationTypeDescription !== $validated['vacation_type_description']) {
            $vacationType->VacationTypeDescription = $validated['vacation_type_description'];
            $changes = true;
        }

        // Save the changes to the database
        if ($changes) {
            $existing = VacationType::where('VacationTypeName', $vacationType->VacationTypeName)->where('VacationTypeDescription', $vacationType->VacationTypeDescription)->first();
            if ($existing) 
                return response()->json(['message' => 'لا يمكن ادخال نوع الأجازة لأنه يوجد بالفعل نوع أجازة بنفس الاسم والوصف'], 409);

            if ($vacationType->save()) {
                return response()->json([
                    'message' => 'تم تعديل بيانات نوع الاجازة بنجاح',
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
        $vacationType = VacationType::find($id);
        
        // Check if vacation type exists
        if (!$vacationType) {
            return response()->json(['message' => 'Vacation Type not found'], 404);
        }

        if($vacationType->delete())
        {
            return response()->json(['message' => 'تم إلغاء نوع الأجازة بنجاح'], 200);
        }
    }
}