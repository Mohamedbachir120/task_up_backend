<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

    
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\ObjectifController;
use App\Http\Controllers\CollaborationController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser'])->name('login');
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::POST('/update_password',[AuthController::class,'update_password']);
Route::post('/auth/refresh',[AuthController::class,'refresh']);

Route::get('/departements',[DepartementController::class,'index']);



Route::controller(DepartementController::class)->middleware('auth:sanctum')->group(function(){
    Route::get('/direction_departements','direction_departements')->middleware('isDirecteur');
});


Route::controller(TaskController::class)->middleware('auth:sanctum')->group(function(){

     Route::post('/task','store');
     Route::get('/fetch_initial_data','fetch_initial_data');
     Route::get('/tasks','index');
     Route::post('/assign_sub_task/{id}','assign_sub_task');
     Route::post('/mark_as_finished/{id}','mark_as_finished');
     Route::get('/get_day_tasks','getDayTasks');
     Route::get('/get_task_date','getTaskDate');
     Route::delete('/task/{id}','delete');
     Route::post('/generate_report','generate_report');
     Route::post('/generate_departement_report','generate_departement_report');
     Route::get('/rapports','rapports');
     Route::get('/sub_tasks/{id}','sub_tasks');
     Route::get('/get_month_task','getMonthTask');
     Route::get('/project_tasks/{id}','project_tasks');
     Route::get('/perfomances','perfomances');
     Route::get('/task_per_department_status','TaskPerDepartmentStatus')->middleware('isChefDepartment');
     Route::get('/task_per_direction_status','TaskPerDirectionStatus')->middleware('isDirecteur');
     
     
     Route::get('/task_per_project','TaskPerProject')->middleware('isChefDepartment');
     Route::get('/task_per_personne','TaskPerPersonne')->middleware('isChefDepartment');

     Route::get('/search','search');

     
    Route::get('/task_per_department','TaskPerDepartement')->middleware('isDirecteur');

});
Route::controller(DocumentController::class)->middleware('auth:sanctum')->group(function(){
    Route::delete('/document/{id}','destroy');
});
Route::controller(AlertController::class)->middleware('auth:sanctum')->group(function(){
    Route::get('/alert','index');
});
Route::controller(ObjectifController::class)->middleware('auth:sanctum')->group(function(){

    Route::post('/objectif','store');
    Route::get('/objectifs','index');


});
Route::controller(ProjectController::class)->middleware('auth:sanctum')->group(function(){

    Route::get("/department_projects","department_projects");
    Route::post('/project' ,'store');
});


Route::controller(CollaborationController::class)->middleware('auth:sanctum')->group(function(){
    Route::get('/collaboration','index');
    Route::post('/collaboration','store')->middleware('isChefDepartment');
});