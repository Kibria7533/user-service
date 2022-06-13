<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use Illuminate\Support\Facades\Route;

Route::get('/start-queue', function () {
   \Illuminate\Support\Facades\Artisan::call('queue:work',['--queue'=>'user.q']);
});
$router->get('/', function () {
    return 'Hello World from user service';
});
$router->get('/users', ["as" => "users", "uses" => "UserController@getList"]);
$router->post('/store', ["as" => "users.store", "uses" => "UserController@store"]);
$router->delete('/delete/{id}', ["as" => "users.delete", "uses" => "UserController@destroy"]);
