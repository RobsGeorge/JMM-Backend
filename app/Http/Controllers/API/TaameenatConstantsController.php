<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Illuminate\Http\Response;
use Session;
use App\Models\Taameen;

class TaameenatConstantsController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {
            $data = DB::table('TaameenTable')->select('TaameenMinValue', 'TaameenMaxValue', 'TaameenPersonPercentage', 'TaameenCorporatePercentage')->orderBy('ID','desc')->first();
            
            return response()->json(['data'=>$data, 'message'=>'All Taameen Data Returned Successfully!', 'status'=>200]);
        }

    
        public function update(Request $request)
        {

            $validator = Validator::make($request->all(),[
                'input_taameen_min_value' => 'required',
                'input_taameen_max_value' => 'required',
                'input_taameen_person_percentage' => 'required',
                'input_taameen_corporate_percentage' => 'required',
              ]);
    
            if ($validator->fails())
            {
                return response()->json(['data'=>[], 'message'=>'Validation Failed', 'errors'=>$validator->errors()], 400);
            }
    
            $taameen = Taameen::findOrFail(1);

            $taameen->fill(
                array(
                    'TaameenMinValue' => $request->input_taameen_min_value,
                    'TaameenMaxValue' => $request->input_taameen_max_value,
                    'TaameenPersonPercentage' => $request->input_taameen_person_percentage,
                    'TaameenCorporatePercentage' => $request->input_taameen_corporate_percentage,
                )
            );

            if($taameen->isDirty()){
                $taameen->UpdateTimestamp = now();
                $changedAttributes = $taameen->getDirty();
                $taameen->save();

                return response()->json(
                    [
                        'message'=>'Fixed Taameen Data Updated Successfully',
                        'changed_attributes' => $changedAttributes],
                        201);
            }

            return response()->json(
                [
                    'message' => 'No changes detected'],
                    200);
        }
}