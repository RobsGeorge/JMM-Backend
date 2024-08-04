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

class UniversityController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {
            $university = DB::table('University')->get();
            return view("university.index", array('university' => $university));
        }

        public function create()
        {
            return view("university.create");
        }

        public function insert(Request  $request)
        {
            $lastUniversityID = DB::table('University')->orderBy('UniversityID','desc')->first();

            if($lastUniversityID==Null)
                $thisUniversityID = 1;
            else
                $thisUniversityID = $lastUniversityID->UniversityID + 1;
            
            DB::table('University')->insert(
                array(
                    'UniversityID' => $thisUniversityID,
                    'UniversityName' => $request -> university_name
                )
            );
            return redirect()->route('university.index');
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
            $university = DB::table('University')->where('UniversityID', $id)->first();
            return view("university.edit", array('university' => $university));
        }
    
        public function updates(Request $request, $id)
        {
            $blood = DB::table('University')->where('UniversityID', $id)->first();

            $affected = DB::table('University')->where('UniversityID', $id)->update(['UniversityName' => $request->university_name]);

            return redirect()->route('university.index');
        }
    
        public function deletes($id)
        {
            $university = DB::table('University')->where('UniversityID', $id)->first();
            return view("university.delete", array('university' => $university));
        }

        public function destroy($id)
        {
            $deleted = DB::table('University')->where('UniversityID',$id)->delete();
            
            return redirect()->route('university.index');
        }
}