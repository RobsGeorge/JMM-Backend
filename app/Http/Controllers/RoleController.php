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

class RoleController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {
            $roles = DB::table('Roles')->get();
            return view("role.index", array('roles' => $roles));
        }

        public function create()
        {
            return view("role.create");
        }

        public function insert(Request  $request)
        {
            $lastRoleID = DB::table('Roles')->orderBy('RoleID','desc')->first();
            
            if($lastRoleID==Null)
                $thisRoleID = 1;
            else
                $thisRoleID = $lastRoleID->RoleID + 1;

            DB::table('Roles')->insert(
                array(
                    'RoleID' => $thisRoleID,
                    'RoleName' => $request -> role_name
                )
            );
            return redirect()->route('role.index');
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
            $role = DB::table('Roles')->where('RoleID', $id)->first();
            return view("role.edit", array('role' => $role));
        }
    
        public function updates(Request $request, $id)
        {
            $role = DB::table('Roles')->where('RoleID', $id)->first();

            $affected = DB::table('Roles')->where('RoleID', $id)->update(['RoleName' => $request->role_name]);

            return redirect()->route('role.index');
        }
    
        public function deletes($id)
        {
            $role = DB::table('Roles')->where('RoleID', $id)->first();
            return view("role.delete", array('role' => $role));
        }

        public function destroy($id)
        {
            $deleted = DB::table('Roles')->where('RoleID',$id)->delete();
            
            return redirect()->route('role.index');
        }
}