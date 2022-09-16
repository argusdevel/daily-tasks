<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Tasks\TasksController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/registration', [AuthController::class, 'registration']);
Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::get('/refresh', [AuthController::class, 'refresh']);

    Route::controller(TasksController::class)->group(function () {
        Route::get('/get_selection', 'getSelection');
        Route::post('/tick_task', 'tickTask');
        Route::post('/change_task', 'changeTask');
    });
});
