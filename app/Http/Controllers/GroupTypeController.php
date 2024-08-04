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

class GroupTypeController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {
            $groupTypes = DB::table('GroupType')->get();
            return view("group-type.index", array('groupTypes' => $groupTypes));
        }

        public function create()
        {
            return view("group-type.create");
        }

        public function insert(Request  $request)
        {
            $lastGroupTypeID = DB::table('GroupType')->orderBy('GroupTypeID','desc')->first();
            
            if($lastGroupTypeID==Null)
                $thisGroupTypeID = 1;
            else
                $thisGroupTypeID = $lastGroupTypeID->GroupTypeID + 1;

            DB::table('GroupType')->insert(
                array(
                    'GroupTypeID' => $thisGroupTypeID,
                    'GroupTypeName' => $request -> group_type_name
                )
            );
            return redirect()->route('group-type.index');
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
            $groupType = DB::table('GroupType')->where('GroupTypeID', $id)->first();
            return view("group-type.edit", array('groupType' => $groupType));
        }
    
        public function updates(Request $request, $id)
        {
            //$qetaa = DB::table('Qetaa')->where('QetaaID', $id)->first();

            $affected = DB::table('GroupType')->where('GroupTypeID', $id)->update(['GroupTypeName' => $request->group_type_name]);

            return redirect()->route('group-type.index');
        }
    
        public function deletes($id)
        {
            $groupType = DB::table('GroupType')->where('GroupTypeID', $id)->first();
            return view("group-type.delete", array('groupType' => $groupType));
        }

        public function destroy($id)
        {
            $deleted = DB::table('GroupType')->where('GroupTypeID',$id)->delete();
            
            return redirect()->route('group-type.index');
        }
}