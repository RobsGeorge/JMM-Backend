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


class PersonController extends Controller
{

    //Given a Certain Qetaa ID -> Fetch all the Data of the Persons realted to this Qetaa
    public function getAllPersons()
    {   
        $data = Person::all();
        return response()->json(['data'=>$data, 'message'=>'Person Returned Successfully!', 'status'=>200]);
    }

    public function getPersonByID($id)
    {   
        $data = [];
        $exists = Person::select('PersonID')->where('PersonID', $id)->exists();
        if(!$exists)
            return response()->json(['data'=>$data, 'message'=>'Person not found', 'status'=>400]);

        $data = Person::select('PersonID')->where('PersonID', $id)->get();
        return response()->json(['data'=>$data, 'message'=>'Person Returned Successfully!', 'status'=>200]);
    }

    public function getAllPersonsIndex()
    {
        $data = Person::select('PersonID', 'FirstName', 'SecondName', 'ThirdName', 'FourthName', 'MobileNumber', 'RaqamQawmy');
        if(empty($data))
            return response()->json(['data'=>$data, 'message'=>'Person not found', 'status'=>200]);
        return response()->json(['data'=>$data, 'message'=>'Data Returned Successfully!', 'status'=>200]);
    }

    public function getAllPersonsIDAndNames()
    {
        $data = Person::select('PersonID', 'FirstName', 'SecondName', 'ThirdName', 'FourthName')->get();
        return response()->json(['data'=>$data, 'message'=>'Data Returned Successfully!', 'status'=>200]);
    }

    public function insertPerson(Request $request)
    {   

        $exists = Person::where('RaqamQawmy', '=', $request->input_raqam_qawmy)->exists();
        if($exists)
            return response()->json(['data'=>[], 'message'=>'Raqam Qawmy already exists', 'status'=>200]);

        $exists = Person::first()->exists();
        if(!$exists)
            $thisPersonID = 1;
        else
        {
            $lastPerson = Person::all()->orderBy('PersonID','desc')->first();
            $lastPersonID = $lastPerson->PersonID;
            $thisPersonID = $lastPersonID->PersonID + 1;
        }
            
              
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        $passString =  implode($pass); //turn the array into a string

        try{
            $validator = Validator::make($request->all(),[
                'input_first_name' => 'required',
                'input_second_name' => 'required',
                'input_third_name' => 'required',
                'input_fourth_name' => 'required',
                'input_religion' => 'required',
                'input_gender'=>'required',
                'input_raqam_qawmy' => 'required|min_digits:14|max_digits:14',
                'input_taameen_number' => 'required',
                'input_date_of_birth' => 'required',
                'input_work_start_date' => 'required',
                'input_street_name' => 'required',
                'input_manteqa' => 'required',
                'input_district' => 'required',
                'input_distric' => 'required',
                'input_mohafza_id' => 'required',
                'input_max_number_of_vacation_days' => 'required',
              ]);

            if ($validator->fails())
            {
                return response()->json(['data'=>[], 'message'=>'Validation Failed', 'errors'=>$validator->errors(), 'status'=>400]);
            }

            $person = Person::create([
                
            ]);

            DB::table('NewUsersInformation')->insert(
                array(
                    'PersonID'              => $thisPersonID,
                    'ShamandoraCode'        => $shamandoraCode,
                    'FirstName'             => $request->first_name,
                    'SecondName'            => $request->second_name,
                    'ThirdName'             => $request->third_name,
                    'FourthName'            => $request->fourth_name,
                    'Gender'                => $request->gender,
                    'DateOfBirth'           => $request->birthdate_input,
                    'RaqamQawmy'            => $request->input_raqam_qawmy,
                    'ScoutJoiningYear'      => $request->joining_year_input,
                    'BloodTypeID'           => $request->blood_type_input,
                    'FacebookProfileURL'    => $request->inputFacebookLink,
                    'InstagramProfileURL'   => $request->inputInstagramLink,
                    'PersonalEmail'         => $request->email_input,
                    'BuildingNumber'        => $request->building_number,
                    'FloorNumber'           => $request->floor_number,
                    'AppartmentNumber'      => $request->appartment_number,
                    'MainStreetName'        => $request->main_street_name,
                    'SubStreetName'         => $request->sub_street_name,
                    'ManteqaID'             => $request->manteqa_id,
                    'DistrictID'            => is_null($request->district_id)?1:$request->district_id,
                    'NearestLandmark'       => $request->nearest_landmark,
                    'SanaMarhalaID'         => $request->sana_marhala_id, 
                    'SpiritualFatherName'   => $request->spiritual_father,
                    'SpiritualFatherChurchName' => $request->spiritual_father_church,
                    'Password'              => $passString, 
                    'PersonPersonalMobileNumber' => $request->personal_phone_number,
                    'FatherMobileNumber'    => $request->father_phone_number,
                    'MotherMobileNumber'    => $request->mother_phone_number,
                    'HomePhoneNumber'       => $request->home_phone_number,
                    'IsOPersonalPhoneNumberHavingWhatsapp' => $request->has_whatsapp,
                    'SchoolName'            => $request->person_school,
                    'SchoolGraduationYear'  => $request->school_grad_year,
                    'QetaaID'               => $request->qetaa_id,
                    'QetaaName'             => $QetaaName,  
                )
            );

        }
        catch(Exception $e)
        {
            //return view('person.entry-error');
            dd($e->getMessage());
            DB::rollBack();
            return view('person.entry-error-repeat-trial');
        }

            DB::commit();

            return redirect()->route('person.entry-questions-liveform', $thisPersonID);
    }
}

?>