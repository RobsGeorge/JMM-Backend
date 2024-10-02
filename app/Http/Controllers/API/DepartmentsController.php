<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Illuminate\Http\Response;
use Session;
use App\Models\Department;

class DepartmentsController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function getAllDepartments()
        {
            $data = DB::table('DepartmentsTable')->get();
            return response()->json(['data'=>$data, 'message'=>'All Departments Returned Successfully!'],200);
        }

        public function getDepartmentByID($id)
        {   
            $data = [];

            $exists = Department::select('DepartmentID')->where('DepartmentID', $id)->exists();
            if(!$exists)
                return response()->json(['data'=>$data, 'message'=>'Department not found'], 404);

            $data = Department::getByID($id);
            return response()->json(['data'=>$data, 'message'=>'Department Returned Successfully!'], 200);
        }

        public function insertDepartment(Request $request)
        {   

            $validator = Validator::make($request->all(),[
                'input_department_name' => 'required'
            ]);
    
            if ($validator->fails())
            {
                return response()->json(['data'=>[], 'message'=>'Validation Failed', 'errors'=>$validator->errors()], 400);
            }
            
            $exists = Department::first()->exists();
            if(!$exists)
                $thisDepartmentID = 1;
            else
            {
                $lastDepartment = new Department();
                $lastDepartment = $lastDepartment->orderBy('DepartmentID','desc')->first();
                $lastDepartmentID = $lastDepartment->DepartmentID;
                $thisDepartmentID = $lastDepartmentID + 1;
            }

            

            $department = new Department();
            $department->fill(
                array(
                    'DepartmentID' => $thisDepartmentID,
                    'DepartmentName' => $request->input_department_name,
                    'DepartmentDescription' => $request->input_department_description,
                )
            );

            $department->save();

            return response()->json(['data'=>$department, 'message'=>'Department Created Successfully!'], 201);

        }

        public function update(Request $request, $id)
        {
            $exists = Department::select('DepartmentID')->where('DepartmentID', $id)->exists();
            if(!$exists)
                return response()->json(['data'=>[], 'message'=>'Department not found'], 404);

            $exists = Department::where('DepartmentName', '=', $request->input_department_name)->where('DepartmentID','!=',$id)->exists();
            if($exists)
                return response()->json(['data'=>[], 'message'=>'Department Name already exists'], 200);

            $validator = Validator::make($request->all(),[
                'input_department_name' => 'required'
            ]);

            if ($validator->fails())
            {
                return response()->json(['data'=>[], 'message'=>'Validation Failed', 'errors'=>$validator->errors()], 400);
            }

            $department = Department::getByID($id);

            $department->fill(
                array(
                    "DepartmentName" => $request->input_department_name,
                    'DepartmentDescription' => $request->input_department_description,
                ));


            if($department->isDirty())
            {
                $department->save();              
                return response()->json(['data'=>[], 'message'=>'Department Updated Successfully', 'changed_attributes' => $department->getChanges()], 201);
            }

            return response()->json(['message' => 'No changes detected',], 200);
        }

        public function delete($id)
        {
            $exists = Department::select('DepartmentID')->where('DepartmentID', $id)->exists();
            if(!$exists)
                return response()->json(['data'=>[], 'message'=>'Department not found'], 404);

                Department::where('DepartmentID', $id)->delete();
            return response()->json(['data'=>[], 'message'=>'Department Deleted Successfully'], 200);
        }
}