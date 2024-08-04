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

class MarhalaDeraseyyaController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {
            $marhala = DB::table('Marhala')->get();
            return view("marhala.marhala-index", array('marhala' => $marhala, 'title'=> "الرتب الكشفية"));
        }

        public function create()
        {
            return view("marhala.marhala-create");
        }

        public function insert(Request  $request)
        {
            $lastMarhalaID = DB::table('Marhala')->orderBy('marhala','desc')->first();
            
            if($lastMarhalaID==Null)
                $thisMarhalaID = 1;
            else
                $thisMarhalaID = $lastMarhalaID->MarhalaID + 1;

            DB::table('Marhala')->insert(
                array(
                    'MarhalaID' => $thisMarhalaID,
                    'MarhalaName' => $request -> marhala_name
                )
            );
            return redirect()->route('marhala.index')->with('status',' :تم ادخال بنجاح المرحلة' .$request->marhala_name);
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
            $marhala = DB::table('Marhala')->where('MarhalaID', $id)->first();
            return view("marhala.marhala-edit", array('marhala' => $marhala, 'title'=> "تعديل مرحلة دراسية"));
        }
    
        public function updates(Request $request, $id)
        {
            $marhala = DB::table('Marhala')->where('MarhalaID', $id)->first();

            $affected = DB::table('Marhala')->where('MarhalaID', $id)->update(['MarhalaName' => $request->marhala_name]);

            return redirect()->route('marhala.index')->with('status',' :تم تعديل بنجاح المرحلة' .$request->marhala_name);
        }
    
        public function deletes($id)
        {
            $marhala = DB::table('Marhala')->where('MarhalaID', $id)->first();
            return view("marhala.marhala-delete", array('marhala' => $marhala, 'title'=> "حذف مرحلة دراسية"));
        }

        public function destroy($id)
        {
            $deleted = DB::table('Marhala')->where('MarhalaID',$id)->delete();
            
            return redirect()->route('marhala.index')->with('status','تم الغاء المرحلة بنجاح');
        }
}