<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\WorkingTimes;

class WorkingTimesController extends Controller
{
    public function getWorkingTimes()
    {
        $data = DB::table('WorkingTimesTable')->select('StartTime', 'EndTime')->where('ID', 1)->get();
        
        return response()->json(['data'=>$data, 'message'=>'Working Times Returned Successfully!'], 200);
    }


    public function updateWorkingTimes(Request $request)
    {

        $validatedData = $request->validate([                
            'input_start_time' => 'date_format:H:i',
            'input_end_time' => 'date_format:H:i',
            ]);
        
        // Fetch the working times record
        $workingTimes = WorkingTimes::find(1);

        if (!$workingTimes) {
            return response()->json(['message' => 'Working times not found.'], 200);
        }
    
        // Track changes
        $changes = false;

        // Update the fields if they are provided and different from the existing values
        if (isset($validatedData['input_start_time']) && $workingTimes->StartTime !== $validatedData['input_start_time']) {
            $workingTimes->StartTime = $validatedData['input_start_time'];
            $changes = true;
        }

        if (isset($validatedData['input_end_time']) && $workingTimes->EndTime !== $validatedData['input_end_time']) {
            $workingTimes->EndTime = $validatedData['input_end_time'];
            $changes = true;
        }

        // Save the changes to the database
        if ($changes) {
            if ($workingTimes->save()) {
                return response()->json([
                    'message' => 'تم تعديل بيانات مواعيد العمل بنجاح',
                ], 200);
            } else {
                return response()->json(['message' => 'فشل في تعديل البيانات'], 500);
            }
        } else {
            return response()->json(['message' => 'No changed fields.'], 200);
        }
    }
}