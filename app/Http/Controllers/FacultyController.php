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

class FacultyController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {
            $faculty = DB::table('Faculty')->get();
            return view("faculty.index", array('faculty' => $faculty));
        }

        public function create()
        {
            return view("faculty.create");
        }

        public function insert(Request  $request)
        {
            $lastFacultyID = DB::table('Faculty')->orderBy('FacultyID','desc')->first();

            if($lastFacultyID==Null)
                $thisFacultyID = 1;
            else
                $thisFacultyID = $lastFacultyID->FacultyID + 1;

            DB::table('Faculty')->insert(
                array(
                    'FacultyID' => $thisFacultyID,
                    'FacultyName' => $request -> faculty_name
                )
            );
            return redirect()->route('faculty.index');
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
            $faculty = DB::table('Faculty')->where('FacultyID', $id)->first();
            return view("faculty.edit", array('faculty' => $faculty));
        }
    
        public function updates(Request $request, $id)
        {
            $faculty = DB::table('Faculty')->where('FacultyID', $id)->first();

            $affected = DB::table('Faculty')->where('FacultyID', $id)->update(['FacultyName' => $request->faculty_name]);

            return redirect()->route('faculty.index');
        }
    
        public function deletes($id)
        {
            $faculty = DB::table('Faculty')->where('FacultyID', $id)->first();
            return view("faculty.delete", array('faculty' => $faculty));
        }

        public function destroy($id)
        {
            $deleted = DB::table('Faculty')->where('FacultyID',$id)->delete();
            
            return redirect()->route('faculty.index');
        }
}