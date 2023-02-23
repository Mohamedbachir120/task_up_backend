<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

    
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;

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
    // Route::get('/affectation_access/{id}','show');
    // Route::post('/affectation_access/{id}','update');
    // Route::delete('/affectation_access/{id}','destroy');


});
Route::controller(ProjectController::class)->middleware('auth:sanctum')->group(function(){

    Route::get("/department_projects","department_projects");
});