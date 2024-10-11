<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PayrollController extends Controller
{
    public function insertPayrollRecord($personID, $month, $year)
    {
        $payroll = Payroll::where('PersonID', $personID)->where('Month', $month)->where('Year', $year)->first()->get();
        if(!$payroll)
        {
            return null;
        }

        $mainSalary = PersonSalary::find($personID)->select()

        $payroll = Payroll::create([
            'PersonID' => $personID,
            'MainSalary' => $personID,
            'VariableSalary' => $personID,
            'DayValue' => $personID,
            'NumberOfAbsentDays' => $personID,
            'NumberOfAttendedDays' => $personID,
            'NumberOfPersonalVacations' => $personID,
            'NumberOfOfficialvacations' => $personID,
            'NumberOfWeeklyVacations' => $personID,
            'HawafezValue' => $personID,
            'KhosoomatValue' => $personID,
            'SolafValue' => $personID,
            'TaameenValue' => $personID,
            'TaameenPercentage' => $personID,
            'TaameenFinalValue' => $personID,
            'TaxesPercentage' => $personID,
            'TaxesValue' => $personID,
            'PayrollClosingDate' => $personID,
            'PayrollMonth' => $personID,
            'PayrollYear' => $personID,
            'TotalBeforeTaxesAndTaameen' => $personID,
            'TotalAfterTaxesAndTaameen' => $personID,
        ]);
    }

    public function closePayroll()
    {

    }

    public function updatePayrollRecord()
    {

    }

    public function getPayroll(Request $request)
    {
        //Payroll Can be fetched for all employees by certain month
        //or Fetcehd for a certain employee by certain month
        //Month is a required parameter

        $validator = $request->validator(
            [
                'month' => 'required|date_format:Y-m',
                'person_id' => 'sometimes|integer|exists:PersonInformation,PersonID'
            ]
        );

        [$year, $month] = explode('-', $validator['month']);

        if($request->has(['person_id']))
        {
            $personID = $validator['person_id'];
            $payroll = Payroll::where('PersonID', $personID)->where('Month', $month)->where('Year', $year)->first()->get();
            if(!$payroll)
            {
                return response()->json(['message' => 'لا يوجد سجلات قبض مرتب لهذا الموظف في هذا الشهر'], 200);
            }

            return response()->json(['data'=>$payroll, 'message' => 'Payroll Returned Successfully'], 200);
        }
        else
        {
            $payroll = Payroll::where('Month', $month)->where('Year', $year)->first()->get();
            if(!$payroll)
            {
                return response()->json(['message' => 'لا يوجد سجلات قبض مرتبات في هذا الشهر'], 200);
            }

            return response()->json(['data'=>$payroll, 'message' => 'Payroll Returned Successfully'], 200);
        }
    }   

    public function deletePayroll()
    {

    }
}