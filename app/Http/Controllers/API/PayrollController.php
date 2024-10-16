<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Person;
use App\Models\PersonSalary;
use App\Models\PersonAbsence;
use App\Models\PersonAttendance;
use App\Models\PersonHafez;
use App\Models\PersonKhosoomat;
use App\Models\PersonSolfa;
use App\Models\PersonTaameenValue;
use App\Models\PersonVacations;
use App\Models\Taameen;
use App\Models\YearlyOfficialVacations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use LDAP\Result;

class PayrollController extends Controller
{
    public function insertPayrollRecord($personID, $month, $year)
    {
        $numberOfDaysPerMonth = 30;
        //$personID = 35;
        //$month = 10;
        //$year = 2024;
        
        $payroll = Payroll::where('PersonID', $personID)->where('PayrollMonth', $month)->where('PayrollYear', $year)->first();
        if(!empty($payroll))
        {
            return 0;
        }

        $salary = PersonSalary::where('PersonID', $personID)->select('Salary', 'VariableSalary')->orderBy('UpdateTimestamp', 'desc')->first();
        $personAbsentDaysCount = PersonAbsence::where('PersonID', $personID)->whereMonth('AbsenceDate', $month)->whereYear('AbsenceDate', $year)->count();
        $personalVacationsCount = PersonVacations::where('PersonID', $personID)->whereMonth('VacationDate', $month)->whereYear('VacationDate', $year)->count();
        $personOfficialVacationsCount = PersonAttendance::where('PersonID', $personID)->select('IsCompanyOnVacation')->whereMonth('AttendanceDate', $month)->whereYear('AttendanceDate', $year)->where('IsCompanyOnVacation', 1)->count();
        $personWeeklyVacationsCount = PersonAttendance::where('PersonID', $personID)->select('IsWeeklyVacation')->whereMonth('AttendanceDate', $month)->whereYear('AttendanceDate', $year)->where('IsWeeklyVacation', 1)->count();
        $personAttendedDaysCount = $numberOfDaysPerMonth - ($personalVacationsCount + $personOfficialVacationsCount + $personWeeklyVacationsCount + $personAbsentDaysCount);
        $personHawafezValue = PersonHafez::select('HafezValue')->where('PersonID', $personID)->whereMonth('HafezDate', $month)->whereYear('HafezDate', $year)->sum('HafezValue');
        $personKhosoomatValue = PersonKhosoomat::select('KhasmValue')->where('PersonID', $personID)->whereMonth('KhasmDate', $month)->whereYear('KhasmDate', $year)->sum('KhasmValue');
        $personSolafValue = PersonSolfa::select('SolfaValue')->where('PersonID', $personID)->whereMonth('SolfaDate', $month)->whereYear('SolfaDate', $year)->sum('SolfaValue');
        $personTaameenValue = PersonTaameenValue::select('TaameenValue')->where('PersonID', $personID)->orderBy('UpdateTimestamp', 'desc')->first();
        $taameenConstants = Taameen::find(1);
        $taameenFinalvalue = (float)$personTaameenValue->TaameenValue * $taameenConstants->TaameenPersonPercentage/100.0;
        $taxesPercentage = 22.5;
        $taxesValue = (float)$salary->Salary * $taxesPercentage/100.0;

        $totalBeforeTaxesAndTaameen = $salary->Salary  + $salary->VariableSalary + $personHawafezValue -  $personKhosoomatValue - $personSolafValue;
        $totalAfterTaxesAndTaameen = $totalBeforeTaxesAndTaameen - $taameenFinalvalue - $taxesValue;

        $payroll = Payroll::create([
            'PersonID' => $personID,
            'MainSalary' => $salary->Salary,
            'VariableSalary' => $salary->VariableSalary,
            'DayValue' => (float)$salary->Salary/$numberOfDaysPerMonth,
            'NumberOfAbsentDays' => $personAbsentDaysCount,
            'NumberOfAttendedDays' => $personAttendedDaysCount,
            'NumberOfPersonalVacations' => $personalVacationsCount,
            'NumberOfOfficialVacations' => $personOfficialVacationsCount,
            'NumberOfWeeklyVacations' => $personWeeklyVacationsCount,
            'HawafezValue' => $personHawafezValue,
            'KhosoomatValue' => $personKhosoomatValue,
            'SolafValue' => $personSolafValue,
            'TaameenValue' => $personTaameenValue->TaameenValue,
            'TaameenPercentage' => $taameenConstants->TaameenPersonPercentage."%",
            'TaameenFinalValue' => $taameenFinalvalue,
            'TaxesPercentage' => $taxesPercentage."%",
            'TaxesValue' => $taxesValue,
            'PayrollClosingDate' => date('Y-m-d'),
            'PayrollMonth' => (int) date('m'),
            'PayrollYear' => (int) date('Y'),
            'TotalBeforeTaxesAndTaameen' => $totalBeforeTaxesAndTaameen,
            'TotalAfterTaxesAndTaameen' => $totalAfterTaxesAndTaameen,
        ]);

        return $payroll;
    }

    public function updatePayrollRecord()
    {

    }

    public function getPayrollRecord(Request $request)
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




    ///////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////

    public function closeMonthPayroll(Request $request)
    {
        $validated = $request->validator(
            [
                'month' => 'required|date_format:Y-m',
            ]
        );
    }

    public function closePersonPayroll(Request $request)
    {
        $validated = $request->validator(
            [
                'month' => 'required|date_format:Y-m',
                'person_id' => 'required|exists:PersonInformation,PersonID',
            ]
        );
    }

    public function getClosedMonthPayroll(Request $request)
    {
        $validated = $request->validator(
            [
                'month' => 'required|date_format:Y-m',
            ]
        );

        [$year, $month] = explode('-', $validated['month']);

        $payroll = Payroll::where('PayrollMonth', $month)->where('PayrollYear', $year)->get();
        if(empty($payroll))
        {
            return response()->json(['message' => 'لا يوجد أي قبض مغلق لهذا الشهر'], 200);
        }

        return response()->json(['data'=>$payroll, 'message' => 'Payroll Returned Successfully'], 200); 
    }

    public function getMonthPayroll(Request $request)
    {
        $validated = $request->validator(
            [
                'month' => 'required|date_format:Y-m',
            ]
        );
    }

    public function getPersonDetailedPayroll(Request $request)
    {
        $numberOfDaysPerMonth = 30;

        $validated = $request->validator(
            [
                'month' => 'required|date_format:Y-m',
                'person_id' => 'required|exists:PersonInformation,PersonID',
            ]
        );

        $personID = $validated['person_id'];
        [$year, $month] = explode('-', $validated['month']);

        $payroll = Payroll::where('PersonID', $personID)->where('PayrollMonth', $month)->where('PayrollYear', $year)->get();

        if($payroll)
        {
            return response()->json(['data'=>$payroll, 'message' => 'Payroll Returned Successfully'], 200);
        }

        $salary = PersonSalary::where('PersonID', $personID)->select('Salary', 'VariableSalary')->orderBy('UpdateTimestamp', 'desc')->first();
        $personAbsentDaysCount = PersonAbsence::where('PersonID', $personID)->whereMonth('AbsenceDate', $month)->whereYear('AbsenceDate', $year)->count();
        $personalVacationsCount = PersonVacations::where('PersonID', $personID)->whereMonth('VacationDate', $month)->whereYear('VacationDate', $year)->count();
        $personOfficialVacationsCount = PersonAttendance::where('PersonID', $personID)->select('IsCompanyOnVacation')->whereMonth('AttendanceDate', $month)->whereYear('AttendanceDate', $year)->where('IsCompanyOnVacation', 1)->count();
        $personWeeklyVacationsCount = PersonAttendance::where('PersonID', $personID)->select('IsWeeklyVacation')->whereMonth('AttendanceDate', $month)->whereYear('AttendanceDate', $year)->where('IsWeeklyVacation', 1)->count();
        $personAttendedDaysCount = $numberOfDaysPerMonth - ($personalVacationsCount + $personOfficialVacationsCount + $personWeeklyVacationsCount + $personAbsentDaysCount);
        $personHawafezValue = PersonHafez::select('HafezValue')->where('PersonID', $personID)->whereMonth('HafezDate', $month)->whereYear('HafezDate', $year)->sum('HafezValue');
        $personKhosoomatValue = PersonKhosoomat::select('KhasmValue')->where('PersonID', $personID)->whereMonth('KhasmDate', $month)->whereYear('KhasmDate', $year)->sum('KhasmValue');
        $personSolafValue = PersonSolfa::select('SolfaValue')->where('PersonID', $personID)->whereMonth('SolfaDate', $month)->whereYear('SolfaDate', $year)->sum('SolfaValue');
        //$personTaameenValue = PersonTaameenValue::select('TaameenValue')->where('PersonID', $personID)->orderBy('UpdateTimestamp', 'desc')->first();
        $taameenConstants = Taameen::find(1);
        $taameenFinalvalue = (float)$salary->Salary * $taameenConstants->TaameenPersonPercentage/100.0;
    }

    public function getPersonPayroll(Request $request)
    {
        $validated = $request->validator(
            [
                'month' => 'required|date_format:Y-m',
                'person_id' => 'required|exists:PersonInformation,PersonID',
            ]
        );
    }

    public function deleteMonthPayroll(Request $request)
    {
        $validated = $request->validator(
            [
                'month' => 'required|date_format:Y-m',
            ]
        );
    }
    public function deletePersonPayroll(Request $request)
    {
        $validated = $request->validator(
            [
                'month' => 'required|date_format:Y-m',
                'person_id' => 'required|exists:PersonInformation,PersonID',
            ]
        );
    }


}