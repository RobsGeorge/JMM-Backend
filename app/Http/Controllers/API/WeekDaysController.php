<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\WeekDays;

class WeekDaysController extends Controller
{
    public function getWeekDays()
    {
        $data = DB::table('WeekDaysTable')->select('DayID', 'DayNameArabic', 'DayNameEnglish', 'IsWeeklyVacation')->orderBy('DayID','asc')->get();
        
        return response()->json(['data'=>$data, 'message'=>'All Week Days Returned Successfully!'], 200);
    }


    public function updateWeekDays(Request $request)
    {

        $validatedData = $request->validate([                
            'input_day_id' => 'required',
            'input_is_weekly_vacation' => 'required',
            ]);
        
        $weekDays = WeekDays::where('DayID', $request->input_day_id)->first();

        $weekDays->fill(
            array(
                'IsWeeklyVacation' => $request->input_is_weekly_vacation,
            )
        );


        // Save the changes to the database
        if ($weekDays->isDirty()) {
            if ($weekDays->save()) {
                return response()->json([
                    'message' => 'تم تعديل بيانات أيام العمل بنجاح',
                ], 200);
            } else {
                return response()->json(['message' => 'فشل في تعديل البيانات'], 500);
            }
        } 
        else {
            return response()->json(['message' => 'No changed fields.'], 200);
        }
    }
}