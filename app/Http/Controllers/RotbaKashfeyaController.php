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

class RotbaKashfeyaController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {
            $rotab = DB::table('RotbaInformation')->get();
            return view("rotab.rotab-index", array('rotab' => $rotab, 'title'=> "الرتب الكشفية"));
        }

        public function create()
        {
            return view("rotab.rotab-create");
        }

        public function insert(Request  $request)
        {
            $lastRotbaID = DB::table('RotbaInformation')->orderBy('RotbaID','desc')->first();
            
            if($lastRotbaID==Null)
                $thisRotbaID = 1;
            else
                $thisRotbaID = $lastRotbaID->RotbaID + 1;


            DB::table('RotbaInformation')->insert(
                array(
                    'RotbaID' => $thisRotbaID,
                    'RotbaName' => $request -> rotba_name
                )
            );
            return redirect()->route('rotab.index')->with('status',' :تم ادخال بنجاح الرتبة' .$request->rotba_name);
            
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
            $rotab = DB::table('RotbaInformation')->where('RotbaID', $id)->first();
            //print_r($rotab->RotbaID);
            return view("rotab.rotab-edit", array('rotab' => $rotab, 'title'=> "تعديل رتبة كشفية"));
        }
    
        public function updates(Request $request, $id)
        {
            $rotab = DB::table('RotbaInformation')->where('RotbaID', $id)->first();

            $affected = DB::table('RotbaInformation')->where('RotbaID', $id)->update(['RotbaName' => $request->rotba_name]);

            return redirect()->route('rotab.index')->with('status',' :تم تعديل بنجاح الرتبة' .$request->rotba_name);
        }
    
        public function deletes($id)
        {
            $rotab = DB::table('RotbaInformation')->where('RotbaID', $id)->first();
            return view("rotab.rotab-delete", array('rotab' => $rotab, 'title'=> "حذف رتبة كشفية"));
        }

        public function destroy($id)
        {
            $deleted = DB::table('RotbaInformation')->where('RotbaID',$id)->delete();

            return redirect()->route('rotab.index')->with('status','تم الغاء الرتبة بنجاح');
        }
}