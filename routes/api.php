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
Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers', //like the one above

], function ($router) {
    Route::resource('agent', 'AgentController'); //its not always the case  we can create custom functions in the controller but we want them to be with a specific method
    Route::get('commis', 'AgentController@getAllCommis');
    route::get('transporteur', 'AgentController@getAllTransporteur');
    // Route::get('completed-missions', 'MissionController@getFinishedMission');
    // Route::get('deleted-missions', 'MissionController@missionTrashed'); //its not always the case  we can create custom functions in the controller but we want them to be with a specific method
});

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers', //like the one above

], function ($router) {
    Route::resource('mission', 'MissionController'); //its not always the case  we can create custom functions in the controller but we want them to be with a specific method
    Route::get('completed-missions', 'MissionController@getFinishedMission');
    Route::post('facturation/{mission}', 'MissionController@createFacture');
    Route::get('deleted-missions', 'MissionController@missionTrashed'); //its not always the case  we can create custom functions in the controller but we want them to be with a specific method
});

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers', //like the one above

], function ($router) {

    Route::resource('client', 'ClientController');
    Route::get('allClients', 'ClientController@getAllClients');
});

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers', //like the one above

], function ($router) {
    Route::resource('roles', 'RoleController');
    Route::get('thisRoles', 'RoleController@getRolesForAgent');
});

//Route::get('create-pdf-facture', [FactureController::class, 'generateFacture']);
Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers\admin', //like the one above

], function ($router) {
    //Route::resource('facture', 'FactureController');
    Route::get('factures', 'FactureController@getFactures');
    Route::get('facture/{mission}', 'FactureController@show');
    Route::delete('facture/{facture}', 'FactureController@destroy');
    Route::get('close-facture/{mission}', 'FactureController@closeFacture');
    Route::get('generate-facture/{mission}', 'FactureController@generateFacture');
    Route::get('showFacture/{mission}', 'FactureController@showFacInfo');
    Route::put('update-facture/{mission}', 'FactureController@updateFacture');
    Route::get('closed-factures', 'FactureController@getClosedFactures');
});