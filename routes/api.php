<?php

use App\Http\Controllers\API\AbsenceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PersonController;
use App\Http\Controllers\API\MohafzaController;
use App\Http\Controllers\API\DepartmentsController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\ClosedMonthsController;
use App\Http\Controllers\API\JobsController;
use App\Http\Controllers\API\PersonHafezController;
use App\Http\Controllers\API\WeekDaysController;
use App\Http\Controllers\API\WorkingTimesController;
use App\Http\Controllers\API\VacationTypeController;
use App\Http\Controllers\API\YearlyOfficialVacationsController;
use App\Http\Controllers\API\PersonVacationsController;
use App\Http\Controllers\API\TaameenatConstantsController;
use App\Http\Controllers\API\PersonKhosoomatController;
use App\Http\Controllers\API\PersonSalaryController;
use App\Http\Controllers\API\PersonSolfaController;
use App\Http\Controllers\API\PersonTaameenValueController;
use App\Http\Controllers\API\PayrollController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/persons', [PersonController::class, 'getAllPersons']);
Route::get('/person-index', [PersonController::class, 'getAllPersonIndex']);
Route::get('/person/{id}', [PersonController::class, 'getPersonByID']);
Route::put('/person/update/{id}', [PersonController::class, 'update']);
Route::post('/person/insert',  [PersonController::class, 'insertPerson']);
Route::patch('/person/remove/{id}', [PersonController::class, 'remove']);
Route::delete('/person/destroy/{id}', [PersonController::class, 'destroy']);
Route::put('/person/revert/{id}', [PersonController::class, 'revertPersonRemoval']);

//Route::post('/person/docs/upload', 'App\Http\Controllers\API\PersonController@uploadDocs');
Route::get('/person/{id}/docs/{directory}', [PersonController::class, 'getFile']);
Route::put('/person/docs/{id}', [PersonController::class, 'updateFile']);
Route::get('/person/{id}/docs', [PersonController::class, 'getAllFiles']);
Route::delete('/person/{id}/remove/docs/{directory}', [PersonController::class, 'removeFile']);
Route::delete('/person/{id}/remove/docs', [PersonController::class, 'removeAllFiles']);

Route::get('/mohafzat', [MohafzaController::class, 'getAllMohafzat']);
Route::get('/mohafza/{id}', [MohafzaController::class, 'getMohafzaByID']);
Route::post('/mohafza/insert', [MohafzaController::class, 'insertMohafza']);
Route::put('/mohafza/update/{id}', [MohafzaController::class, 'update']);
Route::delete('/mohafza/delete/{id}', [MohafzaController::class, 'delete']);

Route::get('/departments', [DepartmentsController::class, 'getAllDepartments']);
Route::get('/department/{id}', [DepartmentsController::class, 'getDepartmentByID']);
Route::post('/department/insert', [DepartmentsController::class, 'insertDepartment']);
Route::put('/department/update/{id}', [DepartmentsController::class, 'update']);
Route::delete('/department/delete/{id}', [DepartmentsController::class, 'delete']);

Route::get('/jobs', [JobsController::class, 'getAllJobs']);
Route::get('/job/{id}', [JobsController::class, 'getJobByID']);
Route::post('/job/insert', [JobsController::class, 'insertJob']);
Route::put('/job/update/{id}', [JobsController::class, 'update']);
Route::delete('/job/delete/{id}', [JobsController::class, 'delete']);

Route::get('/taameenat-data', [TaameenatConstantsController::class, 'index']);
Route::put('/taameenat-data/update', [TaameenatConstantsController::class, 'update']);

Route::get('/attendance', [AttendanceController::class, 'getAttendance']);
Route::post('/attendance', [AttendanceController::class, 'insertAttendance']);
Route::put('/attendance', [AttendanceController::class, 'updateAttendance']);
Route::delete('/attendance', [AttendanceController::class, 'deleteAttendance']);

Route::get('/weekdays', [WeekDaysController::class, 'getWeekDays']);
Route::put('/weekdays', [WeekDaysController::class, 'updateWeekDays']);

Route::get('/workingtimes', [WorkingTimesController::class, 'getWorkingTimes']);
Route::put('/workingtimes', [WorkingTimesController::class, 'updateWorkingTimes']);

Route::get('/absence', [AbsenceController::class, 'getAbsence']);
Route::post('/absence', [AbsenceController::class, 'insertAbsence']);
Route::put('/absence/{id}', [AbsenceController::class, 'updateAbsence']);
Route::delete('/absence/{id}', [AbsenceController::class, 'deleteAbsence']);

Route::get('/vacationtype', [VacationTypeController::class, 'get']);
Route::post('/vacationtype', [VacationTypeController::class, 'insert']);
Route::put('/vacationtype/{id}', [VacationTypeController::class, 'update']);
Route::delete('/vacationtype/{id}', [VacationTypeController::class, 'delete']);

Route::get('/officialvacations', [YearlyOfficialVacationsController::class, 'get']);
Route::post('/officialvacations', [YearlyOfficialVacationsController::class, 'insert']);
Route::put('/officialvacations/{id}', [YearlyOfficialVacationsController::class, 'update']);
Route::delete('/officialvacations/{id}', [YearlyOfficialVacationsController::class, 'delete']);

Route::get('/person-vacations', [PersonVacationsController::class, 'get']);
Route::post('/person-vacations', [PersonVacationsController::class, 'insert']);
Route::put('/person-vacations/{id}', [PersonVacationsController::class, 'update']);
Route::delete('/person-vacations/{id}', [PersonVacationsController::class, 'delete']);

Route::get('/person-vacations-limits', [PersonVacationsController::class, 'get']);
Route::post('/person-vacations-limits', [PersonVacationsController::class, 'insert']);
Route::put('/person-vacations-limits/{id}', [PersonVacationsController::class, 'update']);
Route::delete('/person-vacations-limits/{id}', [PersonVacationsController::class, 'delete']);

Route::get('/hafez', [PersonHafezController::class, 'get']);
Route::post('/hafez', [PersonHafezController::class, 'insert']);
Route::put('/hafez/{id}', [PersonHafezController::class, 'update']);
Route::delete('/hafez/{id}', [PersonHafezController::class, 'delete']);

Route::get('/khasm', [PersonKhosoomatController::class, 'get']);
Route::post('/khasm', [PersonKhosoomatController::class, 'insert']);
Route::put('/khasm/{id}', [PersonKhosoomatController::class, 'update']);
Route::delete('/khasm/{id}', [PersonKhosoomatController::class, 'delete']);

Route::get('/person-taameen', [PersonTaameenValueController::class, 'get']);
Route::post('/person-taameen', [PersonTaameenValueController::class, 'insert']);
Route::put('/person-taameen/{id}', [PersonTaameenValueController::class, 'update']);
Route::delete('/person-taameen/{id}', [PersonTaameenValueController::class, 'delete']);

Route::get('/person-salary', [PersonSalaryController::class, 'get']);
Route::post('/person-salary', [PersonSalaryController::class, 'insert']);
Route::put('/perso-salary/{id}', [PersonSalaryController::class, 'update']);
Route::delete('/person-salary/{id}', [PersonSalaryController::class, 'delete']);

Route::get('/solfa', [PersonSolfaController::class, 'get']);
Route::post('/solfa', [PersonSolfaController::class, 'insert']);
Route::put('/solfa/{id}', [PersonSolfaController::class, 'update']);
Route::delete('/solfa/{id}', [PersonSolfaController::class, 'delete']);

Route::get('/closed-months', [ClosedMonthsController::class, 'get']);

Route::get('/payroll', [PayrollController::class, 'getPayroll']);
Route::post('/payroll', [PayrollController::class, 'insertPayrollRecord']);
Route::post('/payroll', [PayrollController::class, 'insertPayrollRecord']);
Route::put('/payroll/{id}', [PayrollController::class, 'updatePayrollRecord']);
Route::put('/close-payroll', [PayrollController::class, 'closePayroll']);

