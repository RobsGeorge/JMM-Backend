<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PersonKhosoomat;
use App\Models\PersonSalary;

class PersonKhosoomatController extends Controller
{
    public function get(Request $request)
    {
        $request->validate([
            'khasm_id' => 'sometimes|exists:PersonKhosoomat,KhasmID',
            'person_id' => 'sometimes|exists:PersonInformation,PersonID',
            'month' => 'sometimes|date_format:Y-m',
            'year' => 'sometimes|integer|min:1900',
        ]);

        // Start building the query
        $query = PersonKhosoomat::query();

        if ($request->has('khasm_id')) {
            $khasm = $query->find($request->khasm_id);
            
            if (!$khasm) {
                return response()->json(['message' => 'Khasm not found'], 200);
            }

            $salary = PersonSalary::where('PersonID', $khasm->PersonID)->select('Salary', 'VariableSalary')->orderBy('UpdateTimestamp', 'desc')->first();

            $response = array();
            $person = $khasm->person;

            $response['HafezID'] = $khasm->KhasmID;
            $response['PersonID'] = $khasm->PersonID;
            $response['PersonFullName'] = $person->FirstName." ".$person->SecondName." ".$person->ThirdName;
            $response['PersonCode'] = $person->LandlineNumber;
            $response['KhasmDate'] = $khasm->KhasmDate;
            $response['KhasmReason'] = $khasm->KhasmReason;
            $response['KhasmValue'] = $khasm->KhasmValue;
            $response['KhasmFromMainSalary'] = $khasm->KhasmFromMainSalary;
            $response['ValueOfPersonHourFromMainSalary'] = (float)$salary->Salary/(30*8);
            $response['ValueOfPersonDayFromMainSalary'] = (float)$salary->Salary/(30);
            $response['ValueOfPersonHourFromVariableSalary'] = (float)$salary->VariableSalary/(30*8);
            $response['ValueOfPersonDayFromVariableSalary'] = (float)$salary->VariableSalary/(30);

            return response()->json(['data' => $response, 'message' => 'Khasm Returned Successfully'], 200);
        }

        

        // Filter by person_id
        if ($request->has('person_id')) {
            $khasm = PersonKhosoomat::where('PersonID', $request->person_id);
            if(!$khasm)
                return response()->json(['message' => 'لا يوجد خصومات مسجلة لهذا الموظف'], 200);
            $query->where('PersonID', $request->person_id)->orderBy('KhasmDate', 'desc');
        }
        
        // Filter by month
        if ($request->has('month')) {
            // Extract the year and month from the input
            [$year, $month] = explode('-', $request->month);
            $query->whereMonth('KhasmDate', $month)->whereYear('KhasmDate', $year)->orderBy('KhasmDate', 'desc');
        }

        // Filter by year
        if ($request->has('year')) {
            $query->whereYear('KhasmDate', $request->year)->orderBy('KhasmDate', 'desc');
        }


        // Get the filtered results
        $khosoomat = $query->get();

        if($khosoomat->isEmpty())
            return response()->json(['message'=>'لا يوجد أي خصومات مسجلة'], 200);

        $response = array();
        $i=0;
        foreach($khosoomat as $khasm)
        {
            $person = $khasm->person;
            $salary = PersonSalary::where('PersonID', $khasm->PersonID)->select('Salary', 'VariableSalary')->orderBy('UpdateTimestamp', 'desc')->first();
            $response[$i]['KhasmID'] = $khasm->KhasmID;
            $response[$i]['PersonID'] = $khasm->PersonID;
            $response[$i]['PersonFullName'] = $person->FirstName." ".$person->SecondName." ".$person->ThirdName;
            $response[$i]['PersonCode'] = $person->LandlineNumber;
            $response[$i]['KhasmDate'] = $khasm->KhasmDate;
            $response[$i]['KhasmReason'] = $khasm->KhasmReason;
            $response[$i]['KhasmValue'] = $khasm->KhasmValue;
            $response[$i]['KhasmFromMainSalary'] = $khasm->KhasmFromMainSalary;
            $response[$i]['ValueOfPersonHourFromMainSalary'] = (float)$salary->Salary/(30*8);
            $response[$i]['ValueOfPersonDayFromMainSalary'] = (float)$salary->Salary/(30);
            $response[$i]['ValueOfPersonHourFromVariableSalary'] = (float)$salary->VariableSalary/(30*8);
            $response[$i]['ValueOfPersonDayFromVariableSalary'] = (float)$salary->VariableSalary/(30);

            $i++;
        }
        return response()->json(['data'=>$response, 'message'=>'All Khosoomat Returned Successfully!'], 200);
    }

    public function insert(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|integer|exists:PersonInformation,PersonID',
            'khasm_date' => 'required|date_format:Y-m-d',
            'khasm_value' => 'required',
            'khasm_reason' => 'required',
            'khasm_from_main_salary' => 'required|integer'
        ]);

        $khasmDate = $validated['khasm_date'];
        $personId = $validated['person_id'];
        $khasmValue = $validated['khasm_value'];
        $khasmReason = $validated['khasm_reason'];
        $isFromMainSalary = $validated['khasm_from_main_salary'];
        
        try{
            $khasm = PersonKhosoomat::create([
                'PersonID' => $personId,
                'KhasmDate' => $khasmDate,
                'KhasmValue' => $khasmValue,
                'KhasmReason' => $khasmReason,
                'KhasmFromMainSalary' => $isFromMainSalary
            ]);
            

            if ($khasm->save()) {
                return response()->json([
                    'data' => $khasm,
                    'message' => 'تم تسجيل الخصم بنجاح'
                ], 201); 
            }
            else
            {
                return response()->json([
                    'message' => 'فشل في ادخال الخصم. رجاء المحاولة مرة أخرى',
                ], 500);
            }

        }catch (\Exception $e) {
            return response()->json([
                'message' => 'فشل في ادخال الخصم. رجاء المحاولة مرة أخرى',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        
        $validated = $request->validate([
            'khasm_date' => 'required|date_format:Y-m-d',
            'khasm_reason' => 'required',
            'khasm_value' => 'required',
            'khasm_from_main_salary' => 'required|integer'
        ]);
        
        $khasmDate = $validated['khasm_date'];
        $khasmReason = $validated['khasm_reason'];
        $khasmValue = $validated['khasm_value'];

        $khasm = PersonKhosoomat::find($id);
        
        // Check if vacation type exists
        if (!$khasm) {
            return response()->json(['message' => 'Khasm not found'], 200);
        }
    
        // Track changes
        $changes = false;

        if (isset($khasmDate) && $khasm->KhasmDate !== $khasmDate) {
            $khasm->KhasmDate = $khasmDate;
            $changes = true;
        }

        if (isset($khasmReason) && $khasm->KhasmReason !== $khasmReason) {
            $khasm->KhasmReason = $khasmReason;
            $changes = true;
        }

        if (isset($khasmValue) && $khasm->KhasmValue !== $khasmValue) {
            $khasm->KhasmValue = $khasmValue;
            $changes = true;
        }

        if (isset($isFromMainSalary) && $khasm->KhasmFromMainSalary !== $isFromMainSalary)
        {
            $khasm->KhasmFromMainSalary = $isFromMainSalary;
            $changes = true;
        }

        // Save the changes to the database
        if ($changes) {
            if ($khasm->save()) {
                return response()->json([
                    'message' => 'تم تعديل بيانات الخصم بنجاح',
                ], 200);
            } else {
                return response()->json(['message' => 'فشل في تعديل البيانات'], 500);
            }
        } else {
            return response()->json(['message' => 'لا يوجد تغييرات'], 200);
        }
    }

    public function delete($id)
    {
        $khasm = PersonKhosoomat::find($id);
        
        // Check if vacation type exists
        if (!$khasm) {
            return response()->json(['message' => 'Khasm not found'], 200);
        }

        if($khasm->delete())
        {
            return response()->json(['message' => 'تم إلغاء الخصم بنجاح'], 200);
        }
    }
}