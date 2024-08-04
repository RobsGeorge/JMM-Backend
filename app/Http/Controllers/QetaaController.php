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

class QetaaController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {
            $qetaat = DB::table('Qetaa')->get();
            return view("qetaa.index", array('qetaat' => $qetaat));
        }

        public function create()
        {
            return view("qetaa.create");
        }

        public function insert(Request  $request)
        {
            $lastQetaaID = DB::table('Qetaa')->orderBy('QetaaID','desc')->first();
            
            if($lastQetaaID==Null)
                $thisQetaaID = 1;
            else
                $thisQetaaID = $lastQetaaID->QetaaID + 1;

            DB::table('Qetaa')->insert(
                array(
                    'QetaaID' => $thisQetaaID,
                    'Qetaaname' => $request -> qetaa_name
                )
            );
            return redirect()->route('qetaa.index')->with('status',' :تم ادخال بنجاح الفصيلة' .$request->qetaa_name);
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
            $qetaa = DB::table('Qetaa')->where('QetaaID', $id)->first();
            return view("qetaa.edit", array('qetaa' => $qetaa, 'title'=> "تعديل فصيلة دم"));
        }
    
        public function updates(Request $request, $id)
        {
            $qetaa = DB::table('Qetaa')->where('QetaaID', $id)->first();

            $affected = DB::table('Qetaa')->where('QetaaID', $id)->update(['QetaaName' => $request->qetaa_name]);

            return redirect()->route('qetaa.index')->with('status',' :تم تعديل بنجاح الفصيلة' .$request->qetaa_name);
        }
    
        public function deletes($id)
        {
            $qetaa = DB::table('Qetaa')->where('QetaaID', $id)->first();
            return view("qetaa.delete", array('qetaa' => $qetaa, 'title'=> "حذف فصيلة دم"));
        }

        public function destroy($id)
        {
            $deleted = DB::table('Qetaa')->where('QetaaID',$id)->delete();
            
            return redirect()->route('qetaa.index')->with('status','تم الغاء الفصيلة بنجاح');
        }
}