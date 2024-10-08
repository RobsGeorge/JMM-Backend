<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Mohafza;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MohafzaController extends Controller
{
        public function getAllMohafzat()
        {
            $data = DB::table('MohafazatTable')->get();
            return response()->json(['data'=>$data, 'message'=>'All Mohafazat Returned Successfully!'],200);
        }

        public function getMohafzaByID($id)
        {   $data = [];

            $exists = Mohafza::select('MohafzaID')->where('MohafzaID', $id)->exists();
            if(!$exists)
                return response()->json(['data'=>$data, 'message'=>'Mohafza not found'], 404);

            $data = Mohafza::getByID($id);
            return response()->json(['data'=>$data, 'message'=>'Mohafza Returned Successfully!'],200);
        }

        public function insertMohafza(Request $request)
        {

            $exists = Mohafza::first()->exists();
            if(!$exists)
                $thisMohafzaID = 1;
            else
            {
                $lastMohafza = new Mohafza();
                $lastMohafza = $lastMohafza->orderBy('MohafzaID','desc')->first();
                $lastMohafzaID = $lastMohafza->MohafzaID;
                $thisMohafzaID = $lastMohafzaID + 1;
            }

            $validator = Validator::make($request->all(),[
                'input_mohafza_name' => 'required'
            ]);
    
            if ($validator->fails())
            {
                return response()->json(['data'=>[], 'message'=>'Validation Failed', 'errors'=>$validator->errors()], 400);
            }

            $mohafza = new Mohafza();
            $mohafza->fill(
                array(
                    'MohafzaID' => $thisMohafzaID,
                    'MohafzaName' => $request->input_mohafza_name,
                )
            );

            $mohafza->save();

            return response()->json(['data'=>$mohafza, 'message'=>'Mohafza Created Successfully!'], 201);

        }

        public function update(Request $request, $id)
        {
            $exists = Mohafza::select('MohafzaID')->where('MohafzaID', $id)->exists();
            if(!$exists)
                return response()->json(['data'=>[], 'message'=>'Mohafza not found'], 404);

            $exists = Mohafza::where('MohafzaName', '=', $request->input_mohafza_name)->where('MohafzaID','!=',$id)->exists();
            if($exists)
                return response()->json(['data'=>[], 'message'=>'Mohafza Name already exists'], 200);

            $validator = Validator::make($request->all(),[
                'input_mohafza_name' => 'required'
            ]);

            if ($validator->fails())
            {
                return response()->json(['data'=>[], 'message'=>'Validation Failed', 'errors'=>$validator->errors()], 400);
            }

            $mohafza = Mohafza::getByID($id);

            $mohafza->fill(
                array(
                    "MohafzaName" => $request->input_mohafza_name,
                ));


            if($mohafza->isDirty())
            {
                $mohafza->save();              
                return response()->json(['data'=>[], 'message'=>'Mohafza Updated Successfully', 'changed_attributes' => $mohafza->getChanges()], 201);
            }

            return response()->json(['message' => 'No changes detected',], 200);
        }

        public function delete($id)
        {
            $exists = Mohafza::select('MohafzaID')->where('MohafzaID', $id)->exists();
            if(!$exists)
                return response()->json(['data'=>[], 'message'=>'Mohafza not found'], 404);

            Mohafza::where('MohafzaID', $id)->delete();
            return response()->json(['data'=>[], 'message'=>'Mohafza Deleted Successfully'], 200);
        }
}