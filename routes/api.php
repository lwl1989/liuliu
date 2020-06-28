<?php

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

Route::group(['middleware' => ['format']], function () {
    Route::get('forgeUser','Users\UsersController@forgeUser');
    Route::post('webhook','Controller@webHook');
    Route::get('wx/login','Auth\WxController@login');
    Route::get('wx/token','Auth\WxController@getToken');

    Route::get('scenes', 'Content\ScenesController@page');

    Route::get('question/info/{id}', 'Content\QuestionController@info');
    Route::get('questions', 'Content\QuestionController@timeLine');
    Route::get('tags', 'Common\TagsController@getAll');

    Route::group(['prefix'=>'topic'], function (){
        Route::get('recommend','Content\TopicController@recommend');
        Route::get('info/{id}','Content\TopicController@info');
    });

    Route::group(['prefix' => 'content'],function (){
        Route::get('/detail','Content\ContentController@detail');
        Route::get('/comments','Content\CommentController@detail');
    });
    Route::group(['prefix'=>'recommend'], function (){
        Route::get('/content','Content\ContentController@recommend');
        Route::get('/coach','Users\UserCoachController@recommend');
        Route::get('/topic','Content\TopicController@recommend');
    });
});

Route::group(['middleware' => ['format','auth:c_api']], function () {
    Route::get('user/tags','Common\TagsController@getList');
    Route::get('user/menu','Common\TagsController@getMenu');
    Route::group(['prefix' => 'static'],function (){
        Route::post('user/avatar', 'Resource\ImageUploadController@upload');
        Route::post('content/cover', 'Resource\ImageUploadController@upload');
        Route::post('content/images', 'Resource\ImageUploadController@upload');
        Route::post('content/videos', 'Resource\ImageUploadController@upload');
        //Route::post('user/avatar', 'Resource\ImageUploadController@upload');
        //Route::post('user/avatar', 'Resource\ImageUploadController@upload');
    });


    Route::group(['prefix' => 'content'],function (){
        //Route::post('comment', 'Content\ContentController@comment');
        Route::post('release', 'Content\ContentController@release');


        Route::get('/', 'Content\ContentController@myList');

        Route::post('zan', 'Content\ZanController@zan');
        Route::post('unzan', 'Content\ZanController@unZan');
    });

    Route::group(['prefix' => 'question'],function (){
        Route::post('release', 'Content\QuestionController@release');
        Route::post('answer', 'Content\QuestionController@answer');
    });

    Route::group(['prefix' => 'user'],function (){
        Route::get('follows/{uid}', 'Users\UsersController@follows');
        Route::get('contents/{uid}', 'Users\UsersController@contents');
        Route::get('comments/{uid}', 'Users\UsersController@comments');
        Route::get('question/{uid}', 'Users\UsersController@questions');
        Route::get('answer/{uid}', 'Users\UsersController@myAnswer');
        Route::post('center', 'Users\UsersController@center');
        Route::post('follow', 'Users\UsersController@follow');
        Route::post('unfollow', 'Users\UsersController@unFollow');
    });

    Route::group(['prefix' => 'coach'], function (){
        Route::get('content/{uid}/{typ}','Users\UserCoachController@contents');
        Route::get('answer/{uid}/{typ}','Users\UserCoachController@answers');
        Route::get('info/{uid}','Users\UserCoachController@info');
        Route::get('recommend','Users\UserCoachController@recommend');
        Route::get('tag/{tag_id}','Users\UserCoachController@tag');
        Route::post('join','Users\UserCoachController@join');
    });

    Route::group(['prefix'=>'scene'], function (){
        Route::get('/detail/{scene_id}', 'Content\ScenesController@detail');
        Route::post('/reply/{scene_id}', 'Content\ScenesController@reply');
        Route::post('/release', 'Content\ScenesController@release');
        Route::get('/replies/{scene_id}', 'Content\ScenesController@replies');
        Route::get('/index', 'Content\ScenesController@index');
    });
});
