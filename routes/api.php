<?php

use Illuminate\Http\Request;

  $api = app('Dingo\Api\Routing\Router');

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



 $api->version('v1',function($api){


 	$api->group([

 	 'middleware' => [
             'cors'
         ],
        'namespace' => 'App\Api\V1\Controllers',

 	],function($api){

 		$api->post('user/signup','UserController@signUp');
       
         $api->post('user/login','UserController@login');



 	});


     /**
      * 需要验证才能获取到资料的路由
      */
     $api->group([
         'middleware' => [
             'api.auth',
             'cors'
         ],
         'namespace' => 'App\Api\V1\Controllers',
     ], function ($api) {


         $api->get('user/userinfo', 'UserController@userInfo');

     });





 });

