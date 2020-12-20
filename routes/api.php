<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SoldierController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\MissionController;

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

Route::prefix('soldiers')->group(function () {
	Route::post('/create',[SoldierController::class, 'createSoldier']);
	Route::post('/update/{id}',[SoldierController::class, 'updateSoldier']);
	Route::post('/state/{id}',[SoldierController::class, 'stateSoldier']);
	Route::get('/viewAll',[SoldierController::class, 'viewAll']);
	Route::get('/details/{id}',[SoldierController::class, 'detailsSoldier']);
	Route::get('/history/{id}',[SoldierController::class, 'missionHistory']);

});

Route::prefix('teams')->group(function () {
	Route::post('/create',[TeamController::class, 'createTeam']);
	Route::post('/update/{id}',[TeamController::class, 'updateTeam']);
	Route::post('/delete/{id}',[TeamController::class, 'deleteTeam']);
	Route::post('/add/leader',[TeamController::class, 'addLeader']);
	Route::post('/add/soldier',[TeamController::class, 'addSoldier']);
	Route::post('/add/mission',[TeamController::class, 'addMission']);
	Route::get('/viewMembers/{id}',[TeamController::class, 'teamMembers']);
	Route::post('/sackSoldier',[TeamController::class, 'sackSoldier']);
	Route::post('/changeLeader',[TeamController::class, 'changeLeader']);

});

Route::prefix('missions')->group(function () {
	Route::post('/create',[MissionController::class, 'createMission']);
	Route::post('/update/{id}',[MissionController::class, 'updateMission']);
	Route::get('/viewAll',[MissionController::class, 'viewAll']);
	Route::get('/details/{id}',[MissionController::class, 'detailsMission']);
	
});