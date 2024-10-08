<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PersonKhosoomat;

class PersonKhosoomatController extends Controller
{
    public function get(Request $request)
    {
        $request->validate([
            'khasm_id' => 'sometimes|exists:PersonKhosoomat,KhasmID',
            'person_id' => 'sometimes|exists:PersonInformation,PersonID',
            'month' => 'sometimes|date_format:Y-m',
            'year' => 'sometimes|integer|min:1900|max:'.date('Y'),
        ]);

        // Start building the query
        $query = PersonKhosoomat::query();

        if ($request->has('khasm_id')) {
            $khasm = $query->find($request->khasm_id);
            if (!$khasm) {
                return response()->json(['message' => 'Khasm not found'], 404);
            }
            return response()->json(['data' => $khasm, 'message' => 'Khasm Returned Successfully'], 200);
        }

        

        // Filter by person_id
        if ($request->has('person_id')) {
            $khasm = PersonKhosoomat::where('PersonID', $request->person_id);
            if(!$khasm)
                return response()->json(['message' => 'لا يوجد خصومات مسجلة لهذا الموظف'], 404);
            $query->where('PersonID', $request->person_id)->orderBy('KhasmDate', 'desc');
        }
        
        // Filter by month
        if ($request->has('month')) {
            // Extract the year and month from the input
            [$year, $month] = explode('-', $request->month);
            $query->whereMonth('KhasmDate', $month)->orderBy('KhasmDate', 'desc');
        }

        // Filter by year
        if ($request->has('year')) {
            $query->whereYear('KhasmDate', $request->year)->orderBy('KhasmDate', 'desc');
        }


        // Check if no query parameters are present
        if (!$request->hasAny(['khasm_id', 'person_id', 'month', 'year'])) {
            // If no query parameters, return all records
            $khosoomat = $query->get();
            return response()->json(['data'=>$khosoomat, 'message'=>'All Khasm Returned Successfully!'], 200);
        }

        // Get the filtered results
        $khosoomat = $query->get();
    
        if(empty($khosoomat))
            return response()->json(['message'=>'لا يوجد أي خصومات مسجلة'], 404);
        return response()->json(['data'=>$khosoomat, 'message'=>'All Khosoomat Returned Successfully!'], 200);
    }

    public function insert(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|integer|exists:PersonInformation,PersonID',
            'khasm_date' => 'required|date_format:Y-m-d',
            'khasm_value' => 'required|float',
            'khasm_reason' => 'sometimes|text'
        ]);

        $khasmDate = $validated['khasm_date'];
        $personId = $validated['person_id'];
        $khasmValue = $validated['khasm_value'];
        $khasmReason = $validated['khasm_reason'];
        
        try{
            $khasm = PersonKhosoomat::create([
                'PersonID' => $personId,
                'KhasmDate' => $khasmDate,
                'KhasmValue' => $khasmValue,
                'KhasmReason' => $khasmReason
            ]);
            

            if ($khasm->save()) {
                return response()->json([
                    'data' => $khasm,
                    'message' => 'تم تسجيل الخصم بنجاح'
                ], 201); 
            }
            else
            {
                return response()->json([
                    'message' => 'فشل في ادخال الخصم. رجاء المحاولة مرة أخرى',
                ], 500);
            }

        }catch (\Exception $e) {
            return response()->json([
                'message' => 'فشل في ادخال الخصم. رجاء المحاولة مرة أخرى',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        
        $validated = $request->validate([
            'khasm_date' => 'required|date_format:Y-m-d',
            'khasm_reason' => 'required|text',
            'khasm_value' => 'required|float'
        ]);
        
        $khasmDate = $validated['khasm_date'];
        $khasmReason = $validated['khasm_reason'];
        $khasmValue = $validated['khasm_value'];

        $khasm = PersonKhosoomat::find($id);
        
        // Check if vacation type exists
        if (!$khasm) {
            return response()->json(['message' => 'Khasm not found'], 404);
        }
    
        // Track changes
        $changes = false;

        if (isset($khasmDate) && $khasm->KhasmDate !== $khasmDate) {
            $khasm->KhasmDate = $khasmDate;
            $changes = true;
        }

        if (isset($khasmReason) && $khasm->KhasmReason !== $khasmReason) {
            $khasm->KhasmReason = $khasmReason;
            $changes = true;
        }

        if (isset($khasmValue) && $khasm->KhasmValue !== $khasmValue) {
            $khasm->KhasmValue = $khasmValue;
            $changes = true;
        }

        // Save the changes to the database
        if ($changes) {
            if ($khasm->save()) {
                return response()->json([
                    'message' => 'تم تعديل بيانات الخصم بنجاح',
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
        $khasm = PersonKhosoomat::find($id);
        
        // Check if vacation type exists
        if (!$khasm) {
            return response()->json(['message' => 'Khasm not found'], 404);
        }

        if($khasm->delete())
        {
            return response()->json(['message' => 'تم إلغاء الخصم بنجاح'], 200);
        }
    }
}