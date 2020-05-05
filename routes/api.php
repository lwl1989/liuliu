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
    Route::get('wx/login','Auth\WxController@login');
    Route::get('wx/token','Auth\WxController@getToken');

    Route::group(['prefix' => 'coach'], function (){
        Route::get('content/{uid}/{typ}','Users\UserCoachController@contents');
        Route::get('answer/{uid}/{typ}','Users\UserCoachController@answers');
        Route::get('info/{uid}','Users\UserCoachController@info');
        Route::get('recommend','Users\UserCoachController@recommend');
    });

    Route::get('question/info/{id}', 'Content\QuestionController@info');
    Route::get('questions', 'Content\QuestionController@timeLime');
    Route::get('tags', 'Common\TagsController@getAll');

    Route:group(['prefix'=>'topic'], function (){
        Route::get('recommend','Content\TopicController@recommend');
        Route::get('info/{id}','Content\TopicController@info');
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
        Route::post('comment', 'Content\ContentController@comment');
        Route::post('release', 'Content\ContentController@release');
        Route::post('/', 'Content\ContentController@myList');
    });

    Route::group(['prefix' => 'question'],function (){
        Route::post('release', 'Content\QuestionController@release');
    });

    Route::group(['prefix' => 'user'],function (){
        Route::get('follows', 'Users\UserController@follows');
        Route::get('contents', 'Users\UserController@contents');
    });
});
//
//
//Route::get('/abc', function (Request $request){
//   return 'dsada';
//});
//不需要驗證登入的接口路由
//Route::group(['middleware' => ['format']], function () {
//    //有登錄的話要帶token
//    Route::post('visitor/register', 'Users\VisitorController@register');
//
//    Route::get('relation/import','Common\CommonController@webImportUserRelations');
//    Route::get('country/code', 'Common\CommonController@getAllCountryCode');
//    Route::get('city/taitung', 'Common\CommonController@getAllTaiTungCityCode');
//
//    Route::post('login', 'Auth\LoginController@login');
//
//    Route::group(['prefix' => 'client/admin'], function () {
//        Route::post('login', 'Auth\LoginController@login');
//        Route::post('verify', 'Auth\VerifyController@get');
//    });
//    //ios
//    Route::post('device/badge', 'Users\MessageController@updateBadge');
//
//    Route::group(['prefix' => 'client'], function () {
//        Route::post('user/exists','Auth\ClientLoginController@oldProfile');
//        //註冊 發送驗證碼
//        Route::post('register', 'Auth\ClientLoginController@register');
//        Route::post('send', 'Auth\ClientLoginController@send');
//        Route::post('login', 'Auth\ClientLoginController@login');
//        //監測手機號是否已被佔用
//        Route::post('check', 'Auth\ClientLoginController@check');
//        Route::post('mobile/check', 'Auth\ClientLoginController@check');
//        //三方登入
//        Route::post('login/third', 'Auth\ThirdLoginController@login');
//        //驗證驗證碼是否正確
//        Route::post('verify/check', 'Auth\ClientLoginController@checkSms');
//        Route::post('number/check', 'Auth\ClientLoginController@checkNumber');
//
//        Route::post('verify/key',   'Auth\ThirdLoginController@getPublicKey');
//
//        Route::middleware('auth:c_api')->get('logout', 'Auth\ClientLoginController@logout');
//    });
//
//    Route::group(['prefix' => 'content'], function () {
//        Route::get('ad', 'Content\AdController@select');
//        Route::get('banner', 'Content\BannerController@select');
//        Route::get('app', 'Content\AppStoreController@select');
//
//        Route::get('welcome', 'Content\WelcomeController@select');
//        Route::get('version', 'Content\VersionsController@getOne');
//        Route::get('app/{id}/click', 'Content\AppStoreController@click');
//
//	    Route::get('version/log', 'Content\AdController@versionLog');
//    });
//
//
//    Route::group(['prefix' => 'gold/event'], function () {
//        Route::get('register', 'Gold\GoldEventSendController@register');
//        Route::get('referees', 'Gold\GoldEventSendController@referees');
//        Route::get('digital', 'Gold\GoldEventSendController@digital');
//    });
//
//
//    Route::get('sights', 'Controller@getSights');
//});
////商品的
//Route::group(['middleware' => ['format'], 'prefix' => 'goods'], function () {
//    //商品列表
//    Route::get('list', 'Goods\GoodsController@select');
//    //分類列表
//    Route::get('categories', 'Goods\GoodsCategoryController@select');
//    Route::middleware('auth:c_api')->post('exchange', 'Goods\GoodsController@exchange');
//    //add restful routed
//    Route::get('', 'Goods\GoodsController@select');
//    Route::get('{id}', 'Goods\GoodsController@get');
//
//
//});
//
////店的
//Route::group(['middleware' => ['format'], 'prefix' => 'shop'], function () {
//    Route::get('team', 'Manager\ShopController@getTeam');
//    Route::get('', 'Manager\ShopController@getNearShop');
//    Route::get('{id}', 'Manager\ShopController@detail');
//});
//
////部門的
//Route::group(['middleware' => ['format'], 'prefix' => 'department'], function () {
//    //部門列表包含topics
//    Route::get('topics', 'Department\DepartmentController@topics');
//
//    //部門消息列表
//    Route::get('message', 'Department\DepartmentController@topics');
//
//    //部門活動列表
//    Route::get('activity', 'Department\DepartmentController@topics');
//
//    //部門新聞列表（大小事）
//
//    //根據縣民群組id獲取用戶token
//    Route::get('group/token/{group_id}', 'Message\SettingController@getTokenByGroupId');
//    //add restful route
//    Route::get('group/{group_id}/token', 'Message\SettingController@getTokenByGroupId');
//});
//
////需要用戶登入權限的接口且前綴爲user的
//Route::group(['middleware' => ['format', 'auth:c_api'], 'prefix' => 'user'], function () {
//    //提交問卷
//    Route::post('questionnaire',  'Users\QuestionController@submit');
//	//提交活動問卷
//	Route::post('activities',  'Users\ActivityController@submit');
//	Route::post('activity/join', 'Users\ActivityController@joinOffLine');
//	//滿意度調差
//    Route::post('survey',  'Users\SurveyController@submit');
//    //用戶金幣
//    Route::get('golds/log', 'Users\GoldController@goldSelect');
//    Route::get('golds', 'Users\GoldController@goldGet');
//    Route::get('golds/stage', 'Users\GoldController@goldGetStage');
//    Route::post('golds/tran', 'Users\GoldController@tran');
//    //登陸後調用 踢出老用戶
//    Route::get('kick', 'Auth\ClientLoginController@kick');
//	Route::post('changeLogin', 'Auth\ClientLoginController@changeLogin');
//    Route::post('third/bind', 'Auth\ThirdLoginController@bind');
//    //上傳頭像
//    Route::post('avatar', 'Resource\ImageUploadController@upload');
//    Route::get('profile', 'Users\UsersController@getAuth');
//    Route::put('profile', 'Users\UsersController@upProfile');
//    //user sub and list
//    Route::post('department/sub', 'Users\DepartmentController@subDepartment');
//    Route::get('department/sub', 'Users\DepartmentController@getUserSubDepartment');  //restful 別名
//    Route::get('department/sublist', 'Users\DepartmentController@getUserSubDepartment');
//
//    //客戶端fcm_token變化 post請求這個接口
//    Route::post('profile/fcm', 'Users\UsersController@changeToken');
//    //動態路由要放到最下面  否則會替換掉  部分沒有多級目錄的router
//
//    //我是V型人活動
//    Route::get('v/person/activity',    'Activity\VTypePersonController@get');
//    Route::post('v/person/activity',    'Activity\VTypePersonController@join');
//
//    //縣民優惠
//	Route::post('preferential/checkin', 'Preferential\PreferentialController@checkIn');
//});
//Route::middleware('format')->get('user/{id}', 'Users\UsersController@get');
////需要用戶登入權限的接口且前綴爲questionnaire的
//Route::group(['middleware' => ['format'], 'prefix' => 'questionnaire'], function () {
//    Route::middleware('auth:c_api')->get('{id}/questions', 'Users\QuestionController@getQuestions');
//    Route::get('{id}/message/{messageId}', 'Users\QuestionController@get');
//    Route::get('{id}/message/{messageId}/answer', 'Users\QuestionController@getQuestionAnswers');
//    //Route::get('downloadAnswer/{id}','Message\QuestionnaireController@downloadAnswer');
//});
//
//Route::group(['middleware' => ['format'], 'prefix' => 'activities'], function () {
//	Route::get('{id}/activity', 'Users\ActivityController@getActivities');
//	Route::get('{id}/message/{messageId}', 'Users\ActivityController@get');
//	Route::get('{id}/message/{messageId}/answer', 'Users\ActivityController@submit');
//});
//
//Route::group(['middleware' => ['format'], 'prefix' => 'activity'], function () {
//	Route::post('answer/file', 'Resource\ImageUploadController@upload');
//});
//
//Route::group(['middleware' => ['format'], 'prefix' => 'survey'], function () {
//
//    Route::get('{id}/message/{messageId}', 'Users\SurveyController@get');
//    Route::get('{id}/message/{messageId}/score', 'Users\SurveyController@getScore');
//
//});
//
//Route::group(['middleware' => ['format', 'auth:c_api']], function(){
//    Route::get('admin/login', 'Users\UsersController@adminToken');
//});
//
////需要管理員登入權限的接口且前綴為admin的
//Route::group(['middleware' => ['format', 'auth:a_api'], 'prefix' => 'admin'], function () {
//    Route::get('departments', 'Message\Manager\ActivityController@department');
//
//	Route::get('activities',                    'Message\Manager\ActivityController@select');
//
//    Route::get('activity/{id}',                 'Message\Manager\DetailController@get');
//    Route::get('activity/report/{id}/{userId}', 'Message\Manager\DetailController@report');
//    Route::get('activity/online/{id}',          'Message\Manager\DetailController@online');
//    Route::get('activity/offline/{id}',         'Message\Manager\DetailController@offline');
//    Route::get('activity/noJoins/{id}',         'Message\Manager\DetailController@noJoins');
//});
//
////開放API接口
//Route::group(['middleware' => ['format'], 'prefix' => 'open'], function () {
//    Route::post('user/account',     'OpenAPI\UserController@checkAccount');
//
//    Route::post('msg/status',       'OpenAPI\MessageController@status');
//    Route::post('msg/push',         'OpenAPI\MessageController@sendUser');
//    Route::post('msg/push/all',     'OpenAPI\MessageController@sendAll');
//
//    Route::get('gold/events',       'OpenAPI\GoldController@select');
//    Route::post('gold/inc',         'OpenAPI\GoldController@inc');
//    Route::post('gold/dec',         'OpenAPI\GoldController@dec');
//
//    Route::get('activities', 'OpenAPI\ActivityController@select');
//
//    Route::get('activity/online/{activityId}',  'OpenAPI\Activity\OnlineController@select');
//    Route::get('activity/offline/{activityId}', 'OpenAPI\Activity\OfflineController@select');
//});
//
////臺東大小事接口
//Route::group(['middleware' => ['format'], 'prefix' => 'message'], function () {
//    //縣府單位消息列表
//    Route::get('department/{unitId}', 'Message\SendController@lists');
//    Route::get('detail/{msgId}', 'Message\SendController@detail');
//	//消息列表(合併接口)
//	Route::get('lists', 'Message\SendController@unionLists');
//	//夾帶金幣消息列表
//    Route::get('goldMsgList', 'Message\SendController@goldMsgListV1');
//    Route::get('golds', 'Message\SendController@goldMsgList');
//	//統計閱讀消息用戶人數
//	Route::post('readRecord', 'Message\SettingController@readRecord');
//    Route::post('read', 'Message\SettingController@readRecord');
//
//	//記錄預定消息多次發送狀態
//	Route::post('callback', 'Message\SendController@callback');
//});
//
//
//Route::group(['middleware' => ['format'], 'prefix' => 'qr'], function () {
//    Route::middleware('auth:c_api')->get('user', 'Resource\QrController@user');
//	Route::middleware('auth:c_api')->get('idnumber', 'Resource\QrController@userIdNumber');
//    Route::get('shop/{id}', 'Resource\QrController@shop');
//    Route::get('goods/{id}', 'Resource\QrController@goods');
//    Route::get('active/{id}', 'Resource\QrController@active');
//	Route::get('newYearActive', 'Resource\QrController@newYearActive');
//	Route::get('multipleActive', 'Resource\QrController@multipleActive');
//});
//
//Route::group(['middleware' => ['format'], 'prefix' => 'park'], function () {
//	Route::post('pay', 'Park\RoadsideController@pay');//路邊停車繳費成功調用接口
//	Route::put('bind', 'Park\RoadsideController@bind');//添加車牌號
//	Route::get('bill', 'Park\MessageController@bill');//查詢所有用戶牌號的繳費帳單
//	Route::get('push', 'Park\MessageController@push');//根據繳費帳單，給用戶推送消息
//	Route::get('listbind', 'Park\RoadsideController@listbind');//車牌列表
//	Route::get('newbind', 'Park\RoadsideController@newbind');//獲取用戶最新綁定的車牌號
//	Route::get('usercar', 'Park\RoadsideController@userCar');//所有用戶綁定車牌號
//	Route::delete('delbind', 'Park\RoadsideController@delbind');//刪除車牌號
//	Route::post('newpay', 'Park\RoadsideController@newPay');//新生停車繳費成功調用接口
//    Route::post('payEcom', 'Park\RoadsideController@payEcom');//路边缴费
//    Route::post('payGreen', 'Park\RoadsideController@payGreen');//新生繳費
//    Route::post('pay/ecom', 'Park\RoadsideController@payEcom');//路边缴费
//    Route::post('pay/green', 'Park\RoadsideController@payGreen');//新生繳費
//});
//
////消息設定
//Route::group(['middleware' => ['format'], 'prefix' => 'message'], function () {
//        Route::post('send', 'Message\SettingController@apiSaveSend');
//});
//
////年終總結
//Route::group(['middleware' => ['format'], 'prefix' => 'summary'], function () {
//	Route::get('export', 'Manager\SummaryController@export');
//});
//
////數位縣民優惠小卡
//Route::group(['middleware' => ['format'], 'prefix' => 'preferential'], function () {
//	Route::get('list', 'Preferential\PreferentialController@select');
//	Route::get('get', 'Preferential\PreferentialController@get');
//});
