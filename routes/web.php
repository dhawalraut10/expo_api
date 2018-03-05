<?php

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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('details/', "ExpoController@list");
$router->post('save/', "ExpoController@saveDetails");
$router->post('saveimage/', "ExpoController@saveImage");
$router->get('expo/', "ExpoController@expolist");
$router->get('expo/{id}', "ExpoController@expodetails");
$router->post('saveExpoDetails/', "ExpoController@saveDetailsRecursively");
$router->post('register/', "ExpoController@registration");
$router->post('login/', "ExpoController@login");
