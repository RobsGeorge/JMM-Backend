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

class SanaMarhalaDeraseyyaController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {
            $sana = DB::table('SanaMarhala')->get();

            return view("sana-marhala.index", array('sana' => $sana));
        }

        public function create()
        {
            return view("sana-marhala.create");
        }

        public function insert(Request  $request)
        {
            $lastSanaMarhalaID = DB::table('SanaMarhala')->orderBy('SanaMarhalaID','desc')->first();
            
            if($lastSanaMarhalaID==Null)
                $thisSanaMarhalaID = 1;
            else
                $thisSanaMarhalaID = $lastSanaMarhalaID->SanaMarhalaID + 1;

            DB::table('SanaMarhala')->insert(
                array(
                    'SanaMarhalaID' => $thisSanaMarhalaID,
                    'SanaID' => 0,
                    'MarhalaID' => 6,
                    'SanaMarhalaName' => $request -> sana_marhala_name
                )
            );
            return redirect()->route('sana-marhala.index');
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
            $sana = DB::table('SanaMarhala')->where('SanaMarhalaID', $id)->first();
            return view("sana-marhala.edit", array('sana' => $sana));
        }
    
        public function updates(Request $request, $id)
        {
            $sana = DB::table('SanaMarhala')->where('SanaMarhalaID', $id)->first();

            $affected = DB::table('SanaMarhala')->where('SanaMarhalaID', $id)->update(['SanaMarhalaName' => $request->sana_marhala_name]);

            return redirect()->route('sana-marhala.index');
        }
    
        public function deletes($id)
        {
            $sana = DB::table('SanaMarhala')->where('SanaMarhalaID', $id)->first();
            return view("sana-marhala.delete", array('sana' => $sana));
        }

        public function destroy($id)
        {
            $deleted = DB::table('SanaMarhala')->where('SanaMarhalaID',$id)->delete();
            
            return redirect()->route('sana-marhala.index');
        }
}