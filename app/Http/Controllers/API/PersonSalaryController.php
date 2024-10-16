<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PersonSalary;

class PersonSalaryController extends Controller
{
    public function get(Request $request)
    {
        $validated = $request->validate([
            'salary_id' => 'sometimes|exists:PersonHawafez,HafezID',
            'person_id' => 'sometimes|exists:PersonInformation,PersonID'
        ]);

        // Start building the query
        $query = PersonSalary::query();

        if ($request->has('salary_id')) {
            $salary = $query->find($validated['salary_id']);
            if (!$salary) {
                return response()->json(['message' => 'Salary not found'], 200);
            }
            return response()->json(['data' => $salary, 'message' => 'Salary Returned Successfully'], 200);
        }

        // Filter by person_id
        if ($request->has('person_id')) {
            $salary = PersonSalary::where('PersonID', $validated['person_id']);
            if(!$salary)
                return response()->json(['message' => 'لا يوجد مرتبات مسجلة لهذا الموظف'], 200);
            $query->where('PersonID', $validated['person_id'])->orderBy('UpdateTimestamp', 'desc');
        }

        // Get the filtered results
        $salaries = $query->get();
    
        if($salaries->isEmpty())
            return response()->json(['message'=>'لا يوجد أي مرتبات مسجلة'], 200);
        return response()->json(['data'=>$salaries, 'message'=>'All Salaries Returned Successfully!'], 200);
    }

    public function insert(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|integer|exists:PersonInformation,PersonID',
            'salary_value' => 'required',
            'variable_salary_value' => 'required',
            'is_per_day' => 'required|integer'
        ]);

        $personId = $validated['person_id'];
        $salaryValue = $validated['salary_value'];
        $variableSalaryValue = $validated['variable_salary_value'];
        $isPerDay = $validated['is_per_day'];
        
        $salary = PersonSalary::where('PersonID', $validated['person_id']);

        if($salary)
        {
            if ($salary->Salary !== $salaryValue || $salary->VariableSalary !== $variableSalaryValue) {
                $salary->Salary = $salaryValue;
                $salary->VariableSalary = $variableSalaryValue;
                $salary->UpdateTimestamp = time();
                $salary->save();
                return response()->json(['data' => $salary, 'message'=>'تم التعديل بنجاح'], 200);
            }
            else
            {
                return response()->json(['message'=>'بيانات المرتب موجودة بالفعل بنفس القيمة'], 200);
            }
        }
        
        
        try{
            $salary = PersonSalary::create([
                'PersonID' => $personId,
                'Salary' => $salaryValue,
                'VariableSalary' => $variableSalaryValue,
                'IsPerDay' => $isPerDay,
                'updateTimestamp' => time()
            ]);
            

            if ($salary->save()) {
                return response()->json([
                    'data' => $salary,
                    'message' => 'تم التسجيل بنجاح'
                ], 201); 
            }
            else
            {
                return response()->json([
                    'message' => 'فشل في الادخال. رجاء المحاولة مرة أخرى',
                ], 500);
            }

        }catch (\Exception $e) {
            return response()->json([
                'message' => 'فشل في الادخال. رجاء المحاولة مرة أخرى',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'salary_value' => 'required',
            'variable_salary_value' => 'required',
            'is_per_day' => 'required|integer'
        ]);
        
        $salaryValue = $validated['salary_value'];
        $variableSalaryValue = $validated['variable_salary_value'];
        $isPerDay = $validated['is_per_day'];
        
        $salary = PersonSalary::where($id);
        
        // Check if salary exists
        if (!$salary) {
            return response()->json(['message' => 'Salary not found'], 200);
        }
    
        // Track changes
        $changes = false;

        if (isset($salaryValue) && $salary->TaameenValue !== $salaryValue) {
            $salary->Salary = $salaryValue;
            $changes = true;
        }

        if (isset($variableSalaryValue) && $salary->TaameenValue !== $variableSalaryValue) {
            $salary->VariableSalary = $variableSalaryValue;
            $changes = true;
        }

        if (isset($isPerDay) && $salary->TaameenValue !== $isPerDay) {
            $salary->IsPerDay = $isPerDay;
            $changes = true;
        }

        // Save the changes to the database
        if ($changes) {
            if ($salary->save()) {
                return response()->json([
                    'message' => 'تم تعديل البيانات بنجاح',
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
        $salary = PersonSalary::find($id);
        
        // Check if taameen exists
        if (!$salary) {
            return response()->json(['message' => 'Salary not found'], 200);
        }

        if($salary->delete())
        {
            return response()->json(['message' => 'تم الالغاء بنجاح'], 200);
        }
    }
}