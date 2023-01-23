<?php

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

//Auth Routes

Route::group([
    //'middleware' => 'auth:api',
    'prefix' => 'auth',
    'namespace' => 'App\Http\Controllers\Auth', //like the one above

], function () {
    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    Route::post('logout', 'AuthController@logout');
    Route::post('me', 'AuthController@me');
});
//forgot password
Route::group([
    'prefix' => 'auth',
    'namespace' => 'App\Http\Controllers',
], function () {
    Route::post('forgot-password', 'NewPasswordController@forgotPassword');
    Route::post('reset-password', 'NewPasswordController@reset');
});

//Auth for agents Routes

Route::group([
    // 'middleware' => 'auth:agent-api',
    'prefix' => 'auth',
    'namespace' => 'App\Http\Controllers\Auth', //like the one above

], function () {
    Route::post('loginAgent', 'AuthAgentController@login');
    //Route::post('register', 'AuthAgentController@register');
    Route::post('logoutAgent', 'AuthAgentController@logout');
    Route::post('agent', 'AuthAgentController@me');
});

//Auth for clients Routes

Route::group([
    // 'middleware' => 'auth:client',
    'prefix' => 'auth',
    'namespace' => 'App\Http\Controllers\Auth', //like the one above

], function () {
    Route::post('login-client', 'AuthClientController@login');
    //Route::post('register', 'AuthClientController@register');
    Route::post('logout-client', 'AuthClientController@logout');
    Route::post('client', 'AuthClientController@me');
});

//change password
Route::group([
    //'middleware' => 'auth:api',
    'prefix' => 'auth',
    'namespace' => 'App\Http\Controllers\Auth', //like the one above

], function () {
    Route::post('change-password', 'ChangePasswordController@changePassword');
});

//Agents Routes

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers', //like the one above

], function ($router) {
    Route::resource('agent', 'AgentController'); //its not always the case  we can create custom functions in the controller but we want them to be with a specific method
    Route::get('commis', 'AgentController@getAllCommis');
    Route::get('transporteur', 'AgentController@getAllTransporteur');
});
//resetting password for agents (admin task)
Route::group([
    'middleware' => 'api',
    'prefix' => 'agent',
    'namespace' => 'App\Http\Controllers', //like the one above

], function ($router) {
    Route::post('reset-password/{agent}', 'AgentController@resetPassword');
});
//Missions Routes

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers', //like the one above

], function ($router) {
    Route::resource('mission', 'MissionController'); //its not always the case  we can create custom functions in the controller but we want them to be with a specific method
    Route::put('file-sending/{mission}', 'MissionController@FileSending');
    Route::get('completed-missions', 'MissionController@getFinishedMission');
    Route::get('deleted-missions', 'MissionController@missionTrashed'); //its not always the case  we can create custom functions in the controller but we want them to be with a specific method
    Route::get('mission-inprog', 'MissionController@missionsInProg');
    Route::get('mission-stats/{id}', 'MissionController@missionsPerMonth');
    Route::get('delete-mission/{id}', 'MissionController@hardDelete');
    Route::get('restore-mission/{id}', 'MissionController@restoreMission');
});

//Clients Route

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers', //like the one above

], function ($router) {

    Route::resource('client', 'ClientController');
    Route::get('allClients', 'ClientController@getAllClients');
    Route::get('mesFactures', 'ClientController@myInvoices');
});

//resetting password for clients (admin task)
Route::group([
    'middleware' => 'api',
    'prefix' => 'client',
    'namespace' => 'App\Http\Controllers', //like the one above

], function ($router) {
    Route::post('reset-password/{client}', 'ClientController@resetPassword');
});
//Roles Routes

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers', //like the one above

], function ($router) {
    Route::resource('roles', 'RoleController');
    Route::get('thisRoles', 'RoleController@getRolesForAgent');
});

//Factures Routes

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers\admin', //like the one above

], function ($router) {
    //Route::resource('facture', 'FactureController');
    Route::get('factures', 'FactureController@getFactures');
    Route::post('facturation/{mission}', 'FactureController@createFacture');
    Route::get('facture/{mission}', 'FactureController@show');
    Route::delete('facture/{facture}', 'FactureController@destroy');
    Route::get('close-facture/{mission}', 'FactureController@closeFacture');
    Route::get('generate-facture/{mission}', 'FactureController@generateFacture');
    Route::get('showFacture/{mission}', 'FactureController@showFacInfo');
    Route::put('update-facture/{mission}', 'FactureController@updateFacture');
    Route::get('closed-factures', 'FactureController@getClosedFactures');
    Route::get('paiment-facture/{facture}', 'FactureController@paidFacture');
    Route::get('paid-factures', 'FactureController@getPaidFactures');
    Route::get('notpaid-factures', 'FactureController@getnotPaidFactures');
    Route::get('recouvrement', 'FactureController@getRecouvrementFac');
});

//Profile Routes
Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers', //like the one above

], function ($router) {
    Route::get('profile', 'ProfileController@myProfile');
    Route::get('myprofile/{id}', 'ProfileController@showThisProfile');
    Route::put('updateProfile/{id}', 'ProfileController@updateMyProfile');
});


//stats Routes
Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    Route::get('stats/{id}', 'CaController@getDataCa');
    // Route::get('stats-recouvrement/{id}', 'CaController@getRecouvrementRev');
    // Route::get('dateete', 'CaController@getYearOp');
});