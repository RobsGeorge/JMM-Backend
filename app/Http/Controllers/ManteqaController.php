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

class ManteqaController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {
            $manateq = DB::table('Manteqa')->get();
            return view("manteqa.index", array('manateq' => $manateq));
        }

        public function create()
        {
            return view("manteqa.create");
        }

        public function insert(Request  $request)
        {
            $lastManteqaID = DB::table('Manteqa')->orderBy('ManteqaID','desc')->first();
            if($lastManteqaID==Null)
                $thisManteqaID = 1;
            else
                $thisManteqaID = $lastManteqaID->ManteqaID + 1;
            DB::table('Manteqa')->insert(
                array(
                    'ManteqaID' => $thisManteqaID,
                    'ManteqaName' => $request -> manteqa_name
                )
            );
            return redirect()->route('manteqa.index');
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
            $manteqa = DB::table('Manteqa')->where('ManteqaID', $id)->first();
            return view("manteqa.edit", array('manteqa' => $manteqa));
        }
    
        public function updates(Request $request, $id)
        {
            $manteqa = DB::table('Manteqa')->where('ManteqaID', $id)->first();

            $affected = DB::table('Manteqa')->where('ManteqaID', $id)->update(['ManteqaName' => $request->manteqa_name]);

            return redirect()->route('manteqa.index');
        }
    
        public function deletes($id)
        {
            $manteqa = DB::table('Manteqa')->where('ManteqaID', $id)->first();
            return view("manteqa.delete", array('manteqa' => $manteqa, 'title'=> "حذف فصيلة دم"));
        }

        public function destroy($id)
        {
            $deleted = DB::table('Manteqa')->where('ManteqaID',$id)->delete();
            
            return redirect()->route('manteqa.index');
        }
}