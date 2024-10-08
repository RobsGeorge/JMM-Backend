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
use App\Models\PersonTaameenValue;
use Carbon\Carbon;

class PersonTaameenValueController extends Controller
{
    public function get(Request $request)
    {
        $request->validate([
            'taameen_id' => 'sometimes|exists:PersonTaameenValue,ID',
            'person_id' => 'sometimes|exists:PersonInformation,PersonID',
        ]);

        $query = PersonTaameenValue::query();

        if ($request->has('taameen_id')) {
            $taameen = $query->find($request->limit_id);
            if (!$taameen) {
                return response()->json(['message' => 'Taameen Data not found'], 404);
            }
            return response()->json(['data' => $taameen, 'message' => 'Taameen Data Returned Successfully'], 200);
        }
        else if($request->has('person_id'))
        {
            $taameen = PersonTaameenValue::where('PersonID', $request->person_id);
            if(!$taameen)
                return response()->json(['message' => 'لا يوجد قيمة تأمينية لهذا الموظف'], 404);
            $query->where('PersonID', $request->person_id)->orderBy('UpdateTimestamp', 'desc')->first();
            $taameen = $query->get();
            return response()->json(['data'=>$taameen, 'message'=>'Taameen Data Returned Successfully!'], 200);
        }

        return response()->json(['message'=>'Bad Request'], 400);
    }

    public function insert(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|exists:PersonInformation,PersonID',
            'taameen_value' => 'required|float',
        ]);

        $taameen = PersonTaameenValue::where('PersonID', $validated['person_id']);

        if($taameen)
        {
            if ($taameen->TaameenValue !== $validated['taameen_value']) {
                $taameen->TaameenValue = $validated['taameen_value'];
                $taameen->UpdateTimestamp = time();
                $taameen->save();
                return response()->json(['data' => $taameen, 'message'=>'تم التعديل بنجاح'], 200);
            }
            else
            {
                return response()->json(['message'=>'بيانات التأمين موجودة بالفعل بنفس القيمة'], 200);
            }
        }
        
        
        try{
            $taameen = PersonTaameenValue::create([
                'PersonID' => $validated['person_id'],
                'TaameenValue' => $validated['taameen_value'],
                'updateTimestamp' => time(),
            ]);
            

            if ($taameen->save()) {
                return response()->json([
                    'data' => $taameen,
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
            'taameen_value' => 'required|float',
        ]);
        
        $taameenValue = $validated['taameen_value'];
        $taameen = PersonTaameenValue::where($id);
        
        // Check if taameen exists
        if (!$taameen) {
            return response()->json(['message' => 'Taameen not found'], 404);
        }
    
        // Track changes
        $changes = false;

        if (isset($taameenValue) && $taameen->TaameenValue !== $taameenValue) {
            $taameen->TaameenValue = $taameenValue;
            $changes = true;
        }

        // Save the changes to the database
        if ($changes) {
            if ($taameen->save()) {
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
        $taameen = PersonTaameenValue::find($id);
        
        // Check if taameen exists
        if (!$taameen) {
            return response()->json(['message' => 'Limit not found'], 404);
        }

        if($taameen->delete())
        {
            return response()->json(['message' => 'تم الالغاء بنجاح'], 200);
        }
    }
}