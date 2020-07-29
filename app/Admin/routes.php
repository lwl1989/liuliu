<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');
    $router->get('/users', 'UsersController@index')->name('index');
    $router->get('/contents/index', 'ContentController@index')->name('index');
    $router->get('/users/{id}/edit', 'UsersController@edit')->name('edit');
    $router->get('/users/{id}', 'UsersController@show')->name('show');
});
