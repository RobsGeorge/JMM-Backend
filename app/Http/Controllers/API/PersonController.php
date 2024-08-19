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
use App\Models\Person;
use App\Models\PersonJob;

use App\Models\PersonDepartment;
use App\Models\PersonSalary;
use App\Models\PersonTaameenValue;


class PersonController extends Controller
{

    public function getAllPersons()
    {   
        $data = Person::all()->where('IsDeleted','0');
        return response()->json(['data'=>$data, 'message'=>'All Persons Returned Successfully!', 'status'=>200]);
    }

    public function getPersonByID($id)
    {   
        $data = [];
        $exists = Person::select('PersonID')->where('PersonID', $id)->where('IsDeleted','0')->exists();
        if(!$exists)
            return response()->json(['data'=>$data, 'message'=>'Person not found', 'status'=>400]);

        $data = DB::table('PersonInformation')
            ->leftJoin('PersonDepartment', 'PersonDepartment.PersonID', '=', 'PersonInformation.PersonID')
            ->leftJoin('DepartmentsTable', 'PersonDepartment.DepartmentID', '=', 'DepartmentsTable.DepartmentID')
            ->leftJoin('PersonJob', 'PersonJob.PersonID', '=', 'PersonInformation.PersonID')
            ->leftJoin('JobsTable', 'JobsTable.JobID', '=', 'PersonJob.JobID')
            ->leftJoin('PersonSalary', 'PersonSalary.PersonID', '=', 'PersonInformation.PersonID')
            ->leftJoin('PersonTaameenValue', 'PersonTaameenValue.PersonID', '=', 'PersonInformation.PersonID')
            ->select('PersonInformation.*', 'PersonSalary.*', 'PersonTaameenValue.TaameenValue', 'JobsTable.JobName', 'DepartmentsTable.DepartmentName')
            ->where('PersonInformation.IsDeleted','0')->where('PersonInformation.PersonID','=',$id)->get();

        return response()->json(['data'=>$data, 'message'=>'Person Returned Successfully!'], 200);
    }

    public function getAllPersonIndex()
    {
        $exists = Person::select('PersonID')->where('IsDeleted','0')->exists();
        if(!$exists)
            return response()->json(['data'=>[], 'message'=>'No Persons Found', 'status'=>400]);

        $data = DB::table('PersonInformation')
            ->leftJoin('PersonDepartment', 'PersonDepartment.PersonID', '=', 'PersonInformation.PersonID')
            ->leftJoin('DepartmentsTable', 'PersonDepartment.DepartmentID', '=', 'DepartmentsTable.DepartmentID')
            ->leftJoin('PersonJob', 'PersonJob.PersonID', '=', 'PersonInformation.PersonID')
            ->leftJoin('JobsTable', 'JobsTable.JobID', '=', 'PersonJob.JobID')
            ->leftJoin('PersonSalary', 'PersonSalary.PersonID', '=', 'PersonInformation.PersonID')
            ->leftJoin('PersonTaameenValue', 'PersonTaameenValue.PersonID', '=', 'PersonInformation.PersonID')
            ->select('PersonInformation.*', 'PersonSalary.Salary', 'PersonTaameenValue.TaameenValue', 'JobsTable.JobName', 'DepartmentsTable.DepartmentName')
            ->where('PersonInformation.IsDeleted','0')->get();
        return response()->json(['data'=>$data, 'message'=>'All Persons Data Returned Successfully!'], 200);
    }

    public function getAllPersonsIDAndNames()
    {
        $data = Person::select('PersonID', 'FirstName', 'SecondName', 'ThirdName', 'FourthName')->get();
        return response()->json(['data'=>$data, 'message'=>'Data Returned Successfully!'], 200);
    }

    public function insertPerson(Request $request)
    {    
        
        $exists = Person::where('RaqamQawmy', '=', $request->input_raqam_qawmy)->exists();
        if($exists)
            return response()->json(['data'=>[], 'message'=>'Raqam Qawmy already exists'], 200);

        $exists = Person::first()->exists();
        if(!$exists)
            $thisPersonID = 1;
        else
        {
            $lastPerson = new Person();
            $lastPerson = $lastPerson->orderBy('PersonID','desc')->first();
            $lastPersonID = $lastPerson->PersonID;
            $thisPersonID = $lastPersonID + 1;
        }

              
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        $passString =  implode($pass); //turn the array into a string

        
            $validator = Validator::make($request->all(),[
                'input_first_name' => 'required',
                'input_second_name' => 'required',
                'input_third_name' => 'required',
                'input_fourth_name' => 'required',
                'input_religion' => 'required',
                'input_gender'=>'required',
                'input_mobile_number'=>'required|min_digits:11|max_digits:11',
                'input_raqam_qawmy' => 'required|min_digits:14|max_digits:14',
                'input_taameen_number' => 'required',
                'input_date_of_birth' => 'required',
                'input_work_start_date' => 'required',
                'input_street_name' => 'required',
                'input_manteqa' => 'required',
                'input_district' => 'required',
                'input_mohafza_id' => 'required',
                'input_max_number_of_vacation_days' => 'required',
                'input_personal_email' => 'email',
                'input_person_department_id' => 'required',
                'input_person_job_id' => 'required',
                'input_person_taameen_value' => 'required',
                'input_person_salary_value' => 'required',
                'input_person_salary_is_per_day' => 'required' 
              ]);

            if ($validator->fails())
            {
                return response()->json(['data'=>[], 'message'=>'Validation Failed', 'errors'=>$validator->errors()], 400);
            }
        
        try{

            DB::beginTransaction();

            DB::table('PersonInformation')->insert(
                array(
                    'PersonID'              => $thisPersonID,
                    'FirstName'             => $request->input_first_name,
                    'SecondName'            => $request->input_second_name,
                    'ThirdName'             => $request->input_third_name,
                    'FourthName'            => $request->input_fourth_name,
                    'Religion'              => $request->input_religion,
                    'Gender'                => $request->input_gender,
                    'RaqamQawmy'            => $request->input_raqam_qawmy,
                    'TaameenNumber'         => $request->input_taameen_number,
                    'DateOfBirth'           => $request->input_date_of_birth,
                    'WorkStartDate'         => $request->input_work_start_date,
                    'DateOfBirthCertificatePhotoURL' => $request->input_date_of_birth_certificate_url,
                    'PersonalPhotoURL'      => $request->input_personal_photo_url,
                    'PersonalIDPhotoURL'    => $request->input_personal_id_photo_url,
                    'MobileNumber'          => $request->input_mobile_number,
                    'LandlineNumber'        => $request->input_landline,
                    'StreetName'            => $request->input_street_name,
                    'Manteqa'               => $request->input_manteqa,
                    'District'              => $request->input_district,
                    'MohafzaID'             => $request->input_mohafza_id,
                    'MaxNumberOfVacationDays' => $request->input_max_number_of_vacation_days,
                    'MaxValueOfSolfaPerMonth' => $request->input_max_value_of_salaray_for_solfa_per_month,
                    'MaxPercentOfSalaryForSolfaPerMonth' => $request->input_max_percent_of_salary_for_solfa_per_month,
                    'WorkEmail'             => $request->input_work_email,
                    'PersonalEmail'         => $request->input_personal_email,
                    'WorkContractPhotoURL'   =>$request->input_work_contract_photo_url,
                )
            );

            DB::table('PersonDepartment')->insert(array(
                'PersonID' => $thisPersonID,
                'DepartmentID' => $request->input_person_department_id
            ));

            DB::table('PersonJob')->insert(array(
                'PersonID' => $thisPersonID,
                'JobID' => $request->input_person_job_id
            ));

            DB::table('PersonSystemPassword')->insert(array(
                'PersonID' => $thisPersonID,
                'PersonSystemPassword' => $passString
            ));

            DB::table('PersonTaameenValue')->insert(array(
                'PersonID' => $thisPersonID,
                'TaameenValue' => $request->input_person_taameen_value
            ));

            DB::table('PersonSalary')->insert(array(
                'PersonID' => $thisPersonID,
                'Salary' => $request->input_person_salary_value,
                'IsPerDay' => $request->input_person_salary_is_per_day
            ));

        }
        catch(Exception $e)
        {
            DB::rollBack();
            return response()->json(['data'=>[], 'message'=>'Insertion Failed'], 400);
        }

            DB::commit();

            $data = Person::where('PersonID', '=', $thisPersonID)->get();
            return response()->json(['data'=>$data, 'message'=>'Person Inserted Successfully'], 201);
    }

    public function remove($id)
    {
        DB::beginTransaction();
        DB::table('PersonInformation')->where('PersonID', $id)->update(['IsDeleted' => 1]);
        DB::commit();

        return response()->json(['data'=>[], 'message'=>'Person Removed From System Successfully'], 200);
    }

    public function revertPersonRemoval($id)
    {
        $exists = Person::select('PersonID')->where('IsDeleted','1')->where('PersonID','=',$id)->exists();
        if(!$exists)
            return response()->json(['data'=>[], 'message'=>'No Removed Persons Found'], 200);

        DB::beginTransaction();

        DB::table('PersonInformation')->where('PersonID', $id)->update(['IsDeleted' => 0]);
        DB::commit();

        return response()->json(['data'=>[], 'message'=>'Person Returned To System Successfully'], 200);
    }


    public function update(Request $request, $id)
    {

        $data = [];
        $exists = Person::select('PersonID')->where('PersonID', $id)->where('IsDeleted','0')->exists();
        if(!$exists)
            return response()->json(['data'=>$data, 'message'=>'Person not found', 'status'=>400]);

            
        $personRaqamQawmy = Person::getByID($id)->RaqamQawmy;
        $exists = Person::where('RaqamQawmy', '=', $request->input_raqam_qawmy)->where('RaqamQawmy', '!=', $personRaqamQawmy)->exists();
        if($exists)
            return response()->json(['data'=>[], 'message'=>'Raqam Qawmy already exists'], 200);

        $validator = Validator::make($request->all(),[
            'input_first_name' => 'required',
            'input_second_name' => 'required',
            'input_third_name' => 'required',
            'input_fourth_name' => 'required',
            'input_religion' => 'required',
            'input_gender'=>'required',
            'input_mobile_number'=>'required|min_digits:11|max_digits:11',
            'input_raqam_qawmy' => 'required|min_digits:14|max_digits:14',
            'input_taameen_number' => 'required',
            'input_date_of_birth' => 'required',
            'input_work_start_date' => 'required',
            'input_street_name' => 'required',
            'input_manteqa' => 'required',
            'input_district' => 'required',
            'input_mohafza_id' => 'required',
            'input_max_number_of_vacation_days' => 'required',
            'input_personal_email' => 'email',
            'input_person_department_id' => 'required',
            'input_person_job_id' => 'required',
            'input_person_taameen_value' => 'required',
            'input_person_salary_value' => 'required',
            'input_person_salary_is_per_day' => 'required' 
          ]);

        if ($validator->fails())
        {
            return response()->json(['data'=>[], 'message'=>'Validation Failed', 'errors'=>$validator->errors()], 400);
        }

        $person = Person::findOrFail($id);
        $person_department = PersonDepartment::getByPersonID($id);
        $person_salary = PersonSalary::getByPersonID($id);
        $person_job = PersonJob::getByPersonID($id);
        $person_taameen = PersonTaameenValue::getByPersonID($id);
            
        

        $person->fill(
            array(
                'PersonID'              => $id,
                'FirstName'             => $request->input_first_name,
                'SecondName'            => $request->input_second_name,
                'ThirdName'             => $request->input_third_name,
                'FourthName'            => $request->input_fourth_name,
                'Religion'              => $request->input_religion,
                'Gender'                => $request->input_gender,
                'RaqamQawmy'            => $request->input_raqam_qawmy,
                'TaameenNumber'         => $request->input_taameen_number,
                'DateOfBirth'           => $request->input_date_of_birth,
                'WorkStartDate'         => $request->input_work_start_date,
                'DateOfBirthCertificatePhotoURL' => $request->input_date_of_birth_certificate_url,
                'PersonalPhotoURL'      => $request->input_personal_photo_url,
                'PersonalIDPhotoURL'    => $request->input_personal_id_photo_url,
                'MobileNumber'          => $request->input_mobile_number,
                'LandlineNumber'        => $request->input_landline,
                'StreetName'            => $request->input_street_name,
                'Manteqa'               => $request->input_manteqa,
                'District'              => $request->input_district,
                'MohafzaID'             => $request->input_mohafza_id,
                'MaxNumberOfVacationDays' => $request->input_max_number_of_vacation_days,
                'MaxValueOfSolfaPerMonth' => $request->input_max_value_of_salaray_for_solfa_per_month,
                'MaxPercentOfSalaryForSolfaPerMonth' => $request->input_max_percent_of_salary_for_solfa_per_month,
                'WorkEmail'             => $request->input_work_email,
                'PersonalEmail'         => $request->input_personal_email,
                'WorkContractPhotoURL'   =>$request->input_work_contract_photo_url,
            )
        );

        $person_department->fill(
            array(
                'PersonID'  => $id,
                'DepartmentID' => $request->input_person_department_id
            )
        );

        $person_salary->fill(
            array(
                'PersonID'  => $id,
                'Salary' => $request->input_person_salary_value,
                'IsPerDay' => $request->input_person_salary_is_per_day
            )
        );

        

        $person_job->fill(
            array(
                'PersonID'  => $id,
                'JobID' => $request->input_person_job_id
            )
        );

        $person_taameen->fill(
            array(
                'PersonID'  => $id,
                'TaameenValue' => $request->input_person_taameen_value
            )
        );

        //return $person_taameen;
        

        if($person->isDirty()||$person_department->isDirty()||$person_salary->isDirty()||$person_job->isDirty()||$person_taameen->isDirty()){
            
            $changedAttributes = array();

            if ($person->isDirty())
            {
                $person->save();
                array_merge($changedAttributes, $person->getChanges());
            }

            if ($person_department->isDirty())
            {
                $person_department->save();
                array_merge($changedAttributes, $person_department->getChanges());
            }

            if ($person_salary->isDirty())
            {
                $person_salary->save();
                array_merge($changedAttributes, $person_salary->getChanges());
            }

            if ($person_job->isDirty())
            {
                $person_job->save();
                array_merge($changedAttributes, $person_job->getChanges());
            }

            if ($person_taameen->isDirty())
            {
                $person_taameen->save();
                array_merge($changedAttributes, $person_taameen->getChanges());
            }
        return response()->json(['data'=>[], 'message'=>'Person Updated Successfully', 'changed_attributes' => $changedAttributes], 201);
    }
    return response()->json(['message' => 'No changes detected',], 200);
    }
}
?>