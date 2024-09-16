<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PersonController;
use App\Http\Controllers\API\DepartmentsController;

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



Route::get('/persons', 'App\Http\Controllers\API\PersonController@getAllPersons');
Route::get('/person-index', 'App\Http\Controllers\API\PersonController@getAllPersonIndex');
Route::get('/person/{id}', 'App\Http\Controllers\API\PersonController@getPersonByID');
Route::put('/person/update/{id}', 'App\Http\Controllers\API\PersonController@update');
Route::post('/person/insert',  'App\Http\Controllers\API\PersonController@insertPerson');
Route::patch('/person/remove/{id}', 'App\Http\Controllers\API\PersonController@remove');
Route::delete('/person/destroy/{id}','App\Http\Controllers\API\PersonController@destroy');
Route::put('/person/revert/{id}', 'App\Http\Controllers\API\PersonController@revertPersonRemoval');

//Route::post('/person/docs/upload', 'App\Http\Controllers\API\PersonController@uploadDocs');
Route::get('/person/{id}/docs/{directory}', 'App\Http\Controllers\API\PersonController@getFile');
Route::put('/person/docs/{id}', 'App\Http\Controllers\API\PersonController@updateFile');
Route::get('/person/{id}/docs', 'App\Http\Controllers\API\PersonController@getAllFiles');
Route::delete('/person/{id}/remove/docs/{directory}', 'App\Http\Controllers\API\PersonController@removeFile');
Route::delete('/person/{id}/remove/docs', 'App\Http\Controllers\API\PersonController@removeAllFiles');


Route::get('/mohafzat', 'App\Http\Controllers\API\MohafzaController@getAllMohafzat');
Route::get('/mohafza/{id}', 'App\Http\Controllers\API\MohafzaController@getMohafzaByID');
Route::post('/mohafza/insert', 'App\Http\Controllers\API\MohafzaController@insertMohafza');
Route::put('/mohafza/update/{id}', 'App\Http\Controllers\API\MohafzaController@update');
Route::delete('/mohafza/delete/{id}', 'App\Http\Controllers\API\MohafzaController@delete');


Route::get('/departments', 'App\Http\Controllers\API\DepartmentsController@getAllDepartments');
Route::get('/department/{id}', 'App\Http\Controllers\API\DepartmentsController@getDepartmentByID');
Route::post('/department/insert', 'App\Http\Controllers\API\DepartmentsController@insertDepartment');
Route::put('/department/update/{id}', 'App\Http\Controllers\API\DepartmentsController@update');
Route::delete('/department/delete/{id}', 'App\Http\Controllers\API\DepartmentsController@delete');


Route::get('/jobs', 'App\Http\Controllers\API\JobsController@getAllJobs');
Route::get('/job/{id}', 'App\Http\Controllers\API\JobsController@getJobByID');
Route::post('/job/insert', 'App\Http\Controllers\API\JobsController@insertJob');
Route::put('/job/update/{id}', 'App\Http\Controllers\API\JobsController@update');
Route::delete('/job/delete/{id}', 'App\Http\Controllers\API\JobsController@delete');



Route::get('/taameenat-data', 'App\Http\Controllers\API\TaameenatConstantsController@index');
Route::put('/taameenat-data/update', 'App\Http\Controllers\API\TaameenatConstantsController@update');

