<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Illuminate\Http\Response;
use Session;

class DistrictController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {
            $districts = DB::table('Districts')->get();
            return view("district.index", array('districts' => $districts, 'title'=> "فصائل الدم"));
        }

        public function create()
        {
            return view("district.create");
        }

        public function insert(Request  $request)
        {
            $lastDistrictID = DB::table('Districts')->orderBy('DistrictID','desc')->first();

            if($lastDistrictID==Null)
                $thisDistrictID = 1;
            else
                $thisDistrictID = $lastDistrictID->DistrictID + 1;

            DB::table('Districts')->insert(
                array(
                    'DistrictID' => $thisDistrictID,
                    'DistrictName' => $request -> district_name
                )
            );
            return redirect()->route('district.index');
        }
    
        /**
            * Display the specified resource.
            *
            * @param  int  $id
            * @return Response
            */
        public function show($id)
        {
            //
        }
    
        /**
            * Show the form for editing the specified resource.
            *
            * @param  int  $id
            * @return Response
            */
        public function edit($id)
        {
            $district = DB::table('Districts')->where('DistrictID', $id)->first();
            return view("district.edit", array('district' => $district));
        }
    
        public function updates(Request $request, $id)
        {
            $affected = DB::table('Districts')->where('DistrictID', $id)->update(['DistrictName' => $request->district_name]);
            return redirect()->route('district.index');
        }
    
        public function deletes($id)
        {
            $district = DB::table('Districts')->where('DistrictID', $id)->first();
            return view("district.delete", array('district' => $district));
        }

        public function destroy($id)
        {
            $deleted = DB::table('Districts')->where('DistrictID',$id)->delete();
            
            return redirect()->route('district.index');
        }
}