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
use App\Models\Job;

class JobsController extends Controller
{
    public function getAllJobs()
    {
        $data = DB::table('JobsTable')->get();
        return response()->json(['data'=>$data, 'message'=>'All Jobs Returned Successfully!'],200);
    }

    public function getJobByID($id)
    {   
        $data = [];

        $exists = Job::select('JobID')->where('JobID', $id)->exists();
        if(!$exists)
            return response()->json(['data'=>$data, 'message'=>'Job not found'], 404);

        $data = Job::getByID($id);
        return response()->json(['data'=>$data, 'message'=>'Job Returned Successfully!'], 200);
    }

    public function insertJob(Request $request)
    {   
        $exists = Job::first()->exists();
        if(!$exists)
            $thisJobID = 1;
        else
        {
            $lastJob = new Job();
            $lastJob = $lastJob->orderBy('JobID','desc')->first();
            $lastJobID = $lastJob->JobID;
            $thisJobID = $lastJobID + 1;
        }

        $validator = Validator::make($request->all(),[
            'input_job_name' => 'required'
        ]);

        if ($validator->fails())
        {
            return response()->json(['data'=>[], 'message'=>'Validation Failed', 'errors'=>$validator->errors()], 400);
        }

        $job = new Job();
        $job->fill(
            array(
                'JobID' => $thisJobID,
                'JobName' => $request->input_job_name,
                'JobDescription' => $request->input_job_description,
            )
        );

        $job->save();

        return response()->json(['data'=>$job, 'message'=>'Job Created Successfully!'], 201);

    }

    public function update(Request $request, $id)
    {
        $exists = Job::select('JobID')->where('JobID', $id)->exists();
        if(!$exists)
            return response()->json(['data'=>[], 'message'=>'Job not found'], 404);

        $exists = Job::where('JobName', '=', $request->input_job_name)->where('JobID','!=',$id)->exists();
        if($exists)
            return response()->json(['data'=>[], 'message'=>'Job Name already exists'], 200);

        $validator = Validator::make($request->all(),[
            'input_job_name' => 'required'
        ]);

        if ($validator->fails())
        {
            return response()->json(['data'=>[], 'message'=>'Validation Failed', 'errors'=>$validator->errors()], 400);
        }

        $job = Job::getByID($id);

        $job->fill(
            array(
                "JobName" => $request->input_job_name,
                'JobDescription' => $request->input_job_description,
            ));


        if($job->isDirty())
        {
            $job->save();              
            return response()->json(['data'=>[], 'message'=>'Job Updated Successfully', 'changed_attributes' => $job->getChanges()], 201);
        }

        return response()->json(['message' => 'No changes detected',], 200);
    }

    public function delete($id)
    {
        $exists = Job::select('JobID')->where('JobID', $id)->exists();
        if(!$exists)
            return response()->json(['data'=>[], 'message'=>'Job not found'], 404);

            Job::where('JobID', $id)->delete();
        return response()->json(['data'=>[], 'message'=>'Job Deleted Successfully'], 200);
    }
}