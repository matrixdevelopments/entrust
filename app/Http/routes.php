<?php

Route::get('/', 'WelcomeController@index');

Route::get('home', 'HomeController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);

Route::group(['middleware' => ['entrust', 'auth'], 'roles' => 'admin', 'perms' => 'create-post'], function ()
{
	get('dashboardAdmin', function ()
	{
		return "El usuario autentificado tiene el rol de Administrador y tiene permisos para crear post's";
	});
});