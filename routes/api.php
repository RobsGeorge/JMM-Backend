<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PersonController;

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



Route::get('/persons', array('uses'=>'App\Http\Controllers\API\PersonController@getAllPersons'));
Route::get('/person-index', array('uses'=>'App\Http\Controllers\API\PersonController@getPersonIndex'));
Route::get('/person/{id}', array('uses'=>'App\Http\Controllers\API\PersonController@getPersonByID'));
Route::post('/person/insert', array('uses' => 'App\Http\Controllers\API\PersonController@insertPerson'));

Route::get('/person/add', array('as' => 'person.create', 'uses' =>'App\Http\Controllers\PersonNewController@create'));
Route::get('/person/entry-questions/insert/{id}', array('as'=> 'person.entry-questions', 'uses'=>'App\Http\Controllers\PersonNewController@getQuestions'));
Route::post('/person/entry-questions/submit', array('as'=> 'person.entry-questions-submit', 'uses'=>'App\Http\Controllers\PersonNewController@submitQuestions'));

Route::get('/person/edit/{id}', array('as' => 'person.edit', 'uses' => 'App\Http\Controllers\PersonNewController@edit'));
Route::patch('/person/update/{id}', array('as'=> 'person.update', 'uses'=> 'App\Http\Controllers\PersonNewController@updates'));  
