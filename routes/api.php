<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

    
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AlertController;

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

Route::controller(TaskController::class)->middleware('auth:sanctum')->group(function(){

    // Route::get('/affectation_access','index');
     Route::post('/task','store');
     Route::get('/fetch_initial_data','fetch_initial_data');
     Route::get('/tasks','index');
     Route::post('/assign_sub_task/{id}','assign_sub_task');
     Route::post('/mark_as_finished/{id}','mark_as_finished');
     Route::get('/get_day_tasks','getDayTasks');
     Route::get('/get_task_date','getTaskDate');
     Route::delete('/task/{id}','delete');
     Route::post('/generate_report','generate_report');
     Route::get('/rapports','rapports');
     Route::get('/sub_tasks/{id}','sub_tasks');

    // Route::get('/affectation_access/{id}','show');
    // Route::post('/affectation_access/{id}','update');
    // Route::delete('/affectation_access/{id}','destroy');


});
Route::controller(DocumentController::class)->middleware('auth:sanctum')->group(function(){
    Route::delete('/document/{id}','destroy');
});
Route::controller(AlertController::class)->middleware('auth:sanctum')->group(function(){
    Route::get('/alert','index');
});
Route::controller(ProjectController::class)->middleware('auth:sanctum')->group(function(){

    Route::get("/department_projects","department_projects");
    Route::post('/project' ,'store');
});