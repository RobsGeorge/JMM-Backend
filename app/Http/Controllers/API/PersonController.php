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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class PersonController extends Controller
{
    protected $workContractDirectory = "work_contract";
    protected $personalPhotoDirectory = "personal_photo";
    protected $personalIDDirectory = "personal_id_photo";
    protected $birthCertificateDirectory = "birth_certificate";

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
            ->select('PersonInformation.*', 'PersonSalary.Salary', 'PersonSalary.VariableSalary', 'PersonTaameenValue.TaameenValue', 'JobsTable.JobName', 'DepartmentsTable.DepartmentName')
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
                'input_person_salary_is_per_day' => 'required',
                'input_date_of_birth_certificate_url' => 'nullable|mimes:pdf,jpeg,png,jpg,gif|max:2048', //MAX 2MB
                'input_personal_id_photo_url' => 'nullable|mimes:pdf,jpeg,png,jpg,gif|max:2048', //MAX 2MB
                'input_personal_photo_url' => 'nullable|mimes:pdf,jpeg,png,jpg,gif|max:2048', //MAX 2MB
                'input_work_contract_photo_url' => 'nullable|mimes:pdf,jpeg,png,jpg,gif|max:2048' //MAX 2MB 
              ]);

            if ($validator->fails())
            {
                return response()->json(['data'=>[], 'message'=>'Validation Failed', 'errors'=>$validator->errors()], 400);
            }

            $birthCertificatePath = "";
            $personalPhotoPath = "";
            $personalIDPhotoPath = "";
            $workContractPath = "";

            if($request->hasFile("input_date_of_birth_certificate_url"))
            {
                $birthCertificatePath = $this->uploadFile($request->file('input_date_of_birth_certificate_url'), $this->birthCertificateDirectory, $thisPersonID);
                
            }
            if($request->hasFile("input_personal_photo_url"))
            {
                $personalPhotoPath = $this->uploadFile($request->file('input_personal_photo_url'), $this->personalPhotoDirectory, $thisPersonID);
                
            }
            if($request->hasFile("input_personal_id_photo_url"))
            {
               $personalIDPhotoPath = $this->uploadFile($request->file('input_personal_id_photo_url'), $this->personalIDDirectory, $thisPersonID);
            }
            if($request->hasFile("input_work_contract_photo_url"))
            {
                $workContractPath = $this->uploadFile($request->file('input_work_contract_photo_url'), $this->workContractDirectory, $thisPersonID);
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
                    'DateOfBirthCertificatePhotoURL' => $birthCertificatePath,
                    'PersonalPhotoURL'      => $personalPhotoPath,
                    'PersonalIDPhotoURL'    => $personalIDPhotoPath,
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
                    'WorkContractPhotoURL'   => $workContractPath,
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
                'VariableSalary' => $request->input_person_variable_salary_value,
                'IsPerDay' => $request->input_person_salary_is_per_day
            ));

        }
        catch(Exception $e)
        {
            DB::rollBack();
            return response()->json(['data'=>[], 'message'=>'Insertion Failed'], 400);
        }

            DB::commit();

            $data = DB::table('PersonInformation')
            ->leftJoin('PersonDepartment', 'PersonDepartment.PersonID', '=', 'PersonInformation.PersonID')
            ->leftJoin('DepartmentsTable', 'PersonDepartment.DepartmentID', '=', 'DepartmentsTable.DepartmentID')
            ->leftJoin('PersonJob', 'PersonJob.PersonID', '=', 'PersonInformation.PersonID')
            ->leftJoin('JobsTable', 'JobsTable.JobID', '=', 'PersonJob.JobID')
            ->leftJoin('PersonSalary', 'PersonSalary.PersonID', '=', 'PersonInformation.PersonID')
            ->leftJoin('PersonTaameenValue', 'PersonTaameenValue.PersonID', '=', 'PersonInformation.PersonID')
            ->select('PersonInformation.*', 'PersonSalary.*', 'PersonTaameenValue.TaameenValue', 'JobsTable.JobName', 'DepartmentsTable.DepartmentName')
            ->where('PersonInformation.IsDeleted','0')->where('PersonInformation.PersonID','=',$thisPersonID)->get();
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
            'input_person_salary_is_per_day' => 'required',
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
                'VariableSalary' => $request->input_person_variable_salary_value,
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

        if($person->isDirty()||$person_department->isDirty()||$person_salary->isDirty()||$person_job->isDirty()||$person_taameen->isDirty()){
            
            $changedAttributes = array();

            if ($person->isDirty())
            {
                $person->save();
                array_merge($changedAttributes, $person->getDirty());
            }

            if ($person_department->isDirty())
            {
                $person_department->save();
                array_merge($changedAttributes, $person_department->getDirty());
            }

            if ($person_salary->isDirty())
            {
                $person_salary->save();
                array_merge($changedAttributes, $person_salary->getDirty());
            }

            if ($person_job->isDirty())
            {
                $person_job->save();
                array_merge($changedAttributes, $person_job->getDirty());
            }

            if ($person_taameen->isDirty())
            {
                $person_taameen->save();
                array_merge($changedAttributes, $person_taameen->getDirty());
            }
        return response()->json(['data'=>[], 'message'=>'Person Updated Successfully', 'changed_attributes' => $changedAttributes], 201);
    }
    return response()->json(['message' => 'No changes detected',], 200);
    }

    private function uploadFile($file, $directory, $userId) //Helper function For File Upload
    {
        // Generate unique file name
        $fileName = $userId . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Create directory if it doesn't exist
        $directoryPath = "uploads//".$directory;
        if (!Storage::exists($directoryPath)) {
            Storage::makeDirectory($directoryPath);
        }

        $file->storeAs($directoryPath, $fileName);

        return $fileName;
        //return Storage::url($filePath);
    }

    public function updateFile(Request $request, $id)
    {
        
        $validator = Validator::make($request->all(),[
            'input_date_of_birth_certificate_url' => 'nullable|mimes:pdf,jpeg,png,jpg,gif|max:2048', //MAX 2MB
            'input_personal_id_photo_url' => 'nullable|mimes:pdf,jpeg,png,jpg,gif|max:2048', //MAX 2MB
            'input_personal_photo_url' => 'nullable|mimes:pdf,jpeg,png,jpg,gif|max:2048', //MAX 2MB
            'input_work_contract_photo_url' => 'nullable|mimes:pdf,jpeg,png,jpg,gif|max:2048' //MAX 2MB 
          ]);

        if ($validator->fails())
        {
            return response()->json(['data'=>[], 'message'=>'Validation Failed', 'errors'=>$validator->errors()], 400);
        }

        $data = [];
        $exists = Person::select('PersonID')->where('PersonID', $id)->where('IsDeleted','0')->exists();
        
        if(!$exists)
            return response()->json(['data'=>$data, 'message'=>'Person not found', 'status'=>400]);

            $person = Person::findOrFail($id);

            $birthCertificatePath = "";
            $personalPhotoPath = "";
            $personalIDPhotoPath = "";
            $workContractPath = "";

            if($request->hasFile("input_date_of_birth_certificate_url"))
            {
                return "0";
                $directory= $this->birthCertificateDirectory;
                $filename = $person->DateOfBirthCertificatePhotoURL;
                $filePath = "uploads//".$directory."//".$filename;
                if ($filePath && Storage::exists($filePath)) {
                    Storage::delete($filePath); // Remove the old file from storage
                }
                $birthCertificatePath = $this->uploadFile($request->file('input_date_of_birth_certificate_url'), $this->birthCertificateDirectory, $id);
                $person->fill(array(
                    'DateOfBirthCertificatePhotoURL' => $birthCertificatePath
                ));
                $person->save();
            }
            if($request->hasFile("input_personal_photo_url"))
            {
                return "1";
                $directory= $this->personalPhotoDirectory;
                $filename = $person->PersonalPhotoURL;
                $filePath = "uploads//".$directory."//".$filename;
                if ($filePath && Storage::exists($filePath)) {
                    Storage::delete($filePath); // Remove the old file from storage
                }
                $personalPhotoPath = $this->uploadFile($request->file('input_personal_photo_url'), $this->personalPhotoDirectory, $id);
                $person->fill(array(
                    'PersonalPhotoURL' => $personalPhotoPath
                ));
                $person->save();
            }
            if($request->hasFile("input_personal_id_photo_url"))
            {
                return "2";
                $directory= $this->personalIDDirectory;
                $filename = $person->PersonalIDPhotoURL;
                $filePath = "uploads//".$directory."//".$filename;
                if ($filePath && Storage::exists($filePath)) {
                    Storage::delete($filePath); // Remove the old file from storage
                }
                $personalIDPhotoPath = $this->uploadFile($request->file('input_personal_id_photo_url'), $this->personalIDDirectory, $id);
                $person->fill(array(
                    'PersonalIDPhotoURL' => $personalIDPhotoPath
                ));
                $person->save();
            }
            if($request->hasFile("input_work_contract_photo_url"))
            {
                return "3";
                $directory= $this->workContractDirectory;
                $filename = $person->WorkContractPhotoURL;
                $filePath = "uploads//".$directory."//".$filename;
                if ($filePath && Storage::exists($filePath)) {
                    Storage::delete($filePath); // Remove the old file from storage
                }
                $workContractPath = $this->uploadFile($request->file('input_work_contract_photo_url'), $this->workContractDirectory, $id);
                $person->fill(array(
                    'WorkContractPhotoURL' => $workContractPath
                ));
                $person->save();
            }
            else
            {
                return response()->json(['message' => 'File not found'], 404);
            }
            return response()->json(['message' => 'File Updated Successfully'], 200);
    }   

    public function getFile($directory, $id)
    {   
        $filename = "";
        $data = [];
        $exists = Person::select('PersonID')->where('PersonID', $id)->where('IsDeleted','0')->exists();
        if(!$exists)
            return response()->json(['data'=>$data, 'message'=>'Person not found', 'status'=>400]);

        if($directory==$this->workContractDirectory||
            $directory==$this->birthCertificateDirectory||
            $directory==$this->personalIDDirectory||
            $directory==$this->personalPhotoDirectory)
        {
            $data = DB::table('PersonInformation')->
                    select("DateOfBirthCertificatePhotoURL", "PersonalPhotoURL", "PersonalIDPhotoURL", "WorkContractPhotoURL")->
                    where("PersonID", "=", $id)->get();

            
            if($directory==$this->workContractDirectory)
                $filename = $data->WorkContractPhotoURL;
            else if($directory==$this->birthCertificateDirectory)
                $filename = $data->DateOfBirthCertificatePhotoURL;
            else if($directory==$this->personalIDDirectory)
                $filename = $data->PersonalIDPhotoURL;
            else if($directory==$this->personalPhotoDirectory)
                $filename = $data->PersonalPhotoURL;
            else
                $filename = "";

            $filePath = "uploads//".$directory."//".$filename;

            // Check if the file exists in storage
            if (Storage::exists($filePath)) {
                // Return the file as a response for download/view
                return Storage::download($filePath);
            }
            return response()->json(['message' => 'File not found'], 404);
        }
        else
        {
            return response()->json(['message' => 'Directory not found'], 404);
        }
    }
}
?>