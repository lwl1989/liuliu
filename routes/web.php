<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//普通路由
Route::get('login','Auth\LoginController@showLoginForm')->name('login');
Route::get('logout', 'Auth\LoginController@logout')->name('logout');
Route::get('ul', 'Resource\UlController@index')->name('ul');
//Route::post('login/third', 'Auth\ThirdLoginController@login');

//未登入會自動跳轉的路由
Route::group(['middleware'=>'auth'], function () {
    Route::get('/', 'Manager\AdminController@index')->name('home');
    Route::get('/index', 'Manager\AdminController@index');
    Route::get('/google/map/key', 'Resource\GoogleMapController@getKey');
});
//不需要驗證登入的接口路由
Route::group(['middleware' => ['format'] ], function (){
    Route::post('login','Auth\LoginController@login');
    Route::get('login/verify', 'Auth\VerifyController@get');

    Route::get('country/code', 'HomeController@countryCode');
	Route::get('turn/country/code/{id}', 'HomeController@turnCountryCode');
});


/**! 分享外部的url都寫在下面 !**/
Route::group(['prefix' => 'share'], function() {
    Route::get('goods/{id}', 'Share\GoodsController@share');
	Route::get('message/{id}', 'Share\GoodsController@message');
	Route::get('share/{id}', 'Goods\GoodsController@device');
	Route::get('vcard', 'Share\VcardController@vCard');
	Route::get('vpreferential', 'Share\VcardController@vPreferential');
	Route::get('vpreferential/info', 'Share\VcardController@info');
});

/**! 測試縣府內部 MySql/MSSQL Server用 !**/
Route::prefix('third')->group(function() {
    //Route::get('ttc', 'ThirdPushController@tcc');
    //Route::get('tt', 'ThirdPushController@tt');
});

Route::group(['prefix'=>'admin','middleware'=>['auth','auth_admin','format']], function (){
    Route::get('select','Manager\AdminController@select');
    Route::post('create','Manager\AdminController@create');
    Route::post('update','Manager\AdminController@update');
    Route::delete('delete','Manager\AdminController@delete');
    Route::get('get','Manager\AdminController@get');

    Route::post('password','Manager\AdminController@changeAdminPassword');
    Route::post('change/password','Manager\AdminController@changePassword');
    Route::get('perm','Manager\AdminController@perm');
    Route::get('info','Manager\AdminController@info');
    Route::get('check/username','Manager\AdminController@checkUsername');
});

Route::group(['prefix'=>'goods','middleware'=>['auth','auth_admin','format']], function (){
    Route::get('count','Goods\GoodsController@count');
    Route::get('select','Goods\GoodsController@select');
    Route::get('get','Goods\GoodsController@get');
    Route::post('create','Goods\GoodsController@create');
    Route::post('update','Goods\GoodsController@update');
    Route::delete('delete','Goods\GoodsController@delete');
    Route::post('stock/add','Goods\GoodsController@goodsStockAdd');
    Route::post('stock/sub','Goods\GoodsController@goodsStockSub');
    Route::get('stage','Goods\GoodsController@getStage');
    Route::get('copy/{goodsId}','Goods\GoodsController@copyRecord');

    Route::put('status/switch/{status}','Goods\GoodsController@statusSwitch');
    Route::post('sale/off','Goods\GoodsController@offSale');
    Route::post('sale/on','Goods\GoodsController@onSale');
    Route::post('recommend/{status}','Goods\GoodsController@recommend');
    Route::delete('stock/clear','Goods\GoodsController@stockClearAll');

    Route::get('stock','Goods\GoodsController@getStock');
    Route::get('stock/logs','Goods\GoodsController@getStockLog');

    Route::group(['prefix'=>'cate'], function (){
        Route::get('select','Goods\GoodsCategoryController@select');
        Route::get('delete/check','Goods\GoodsCategoryController@canDelete');
        Route::post('update','Goods\GoodsCategoryController@update');
        Route::post('create','Goods\GoodsCategoryController@create');
        Route::put('sort','Goods\GoodsCategoryController@sort');
        Route::post('cover','Resource\ImageUploadController@cover');
    });

    Route::post('cover','Resource\ImageUploadController@cover');

    Route::group(['prefix' => 'record'], function (){
        Route::get('select', 'Goods\GoodsRecordController@select');
        Route::get('count', 'Goods\GoodsRecordController@count');
        Route::get('shop/{id}', 'Goods\GoodsRecordController@getGoods');
        Route::get('rank', 'Goods\GoodsRecordController@rank');
    });
});

//縣府單位
Route::group(['prefix' => 'department', 'middleware' => ['auth', 'auth_admin', 'format']], function(){
	Route::get('count','Department\DepartmentController@count');
	Route::get('select','Department\DepartmentController@select');
	Route::post('create','Department\DepartmentController@create');
	Route::post('image','Resource\ImageUploadController@cover');
    Route::delete('delete','Department\DepartmentController@delete');
	Route::get('getOne','Department\DepartmentController@getOne');
	Route::post('update','Department\DepartmentController@update');
	Route::get('getUnit','Department\DepartmentController@getAdminBusinessUnitList');
	Route::get('getAllUnit','Department\DepartmentController@getAllBusinessUnitList');
	Route::get('getSecondaryUnit','Department\DepartmentController@getSecondaryUnit');
	Route::get('check/name','Department\DepartmentController@checkName');
	Route::get('getlist','Department\DepartmentController@getList');
});

//縣民群組
Route::group(['prefix' => 'department/group', 'middleware' => ['auth', 'auth_admin', 'format']], function(){
	Route::get('count','Department\DepartmentGroupController@count');
	Route::get('select','Department\DepartmentGroupController@select');
	Route::post('create','Department\DepartmentGroupController@create');
	Route::post('file','Resource\ImageUploadController@cover');
	Route::delete('delete','Department\DepartmentGroupController@delete');
	Route::get('getOne','Department\DepartmentGroupController@getOne');
	Route::post('update','Department\DepartmentGroupController@update');
	Route::get('download','Department\DepartmentGroupController@download');
	Route::get('getGroupsOfDepartment', 'Department\DepartmentGroupController@getGroupsOfDepartment');
	Route::get('getGroupMember', 'Department\DepartmentGroupController@selectMember');
	Route::get('searchQuestionnaire','Department\DepartmentGroupController@searchQuestionnaire');
	Route::get('searchActivity', 'Department\DepartmentGroupController@searchActivity');
});

//臺東縣民
Route::group(['prefix' => 'users', 'middleware' => ['auth', 'auth_admin', 'format']], function (){
	Route::get('count','Users\UsersController@count');
	Route::get('select','Users\UsersController@select');
	Route::get('get','Users\UsersController@get');
	Route::post('update','Users\UsersController@update');

	Route::put('decode/field', 'Users\UsersController@decodeField');
});

//金幣帳戶
Route::group(['prefix' => 'gold/account', 'middleware' => ['auth', 'auth_admin', 'format']], function (){
	Route::get('getExpireTime/{id}','Gold\GoldAccountController@getExpireTime');
	Route::get('count','Gold\GoldAccountController@count');
	Route::get('select','Gold\GoldAccountController@select');
	Route::delete('delete','Gold\GoldAccountController@delete');
	Route::get('getReserveGold','Gold\GoldAccountController@getReserveGold');
	Route::post('updateReserveGold','Gold\GoldAccountController@updateReserveGold');
	Route::post('createGold','Gold\GoldAccountController@createGold');
	Route::post('updateGold','Gold\GoldAccountController@updateGold');
	Route::get('department','Gold\GoldAccountController@getDepartmentStageGold');
	Route::get('departmentStage','Gold\GoldAccountController@departmentStage');
});

//金幣發放
Route::group(['prefix' => 'gold/send', 'middleware' => ['auth', 'auth_admin', 'format']], function (){
	Route::get('count','Gold\GoldSendController@count');
	Route::get('select','Gold\GoldSendController@select');
	Route::delete('delete','Gold\GoldSendController@delete');
	Route::get('getIndexInfo','Gold\GoldSendController@getIndexInfo');
	Route::get('getRemainGold','Gold\GoldSendController@getRemainGold');
	Route::get('getPreinstallGold','Gold\GoldSendController@getPreInstallGold');
	Route::post('updatePreInstallGold','Gold\GoldSendController@updatePreInstallGold');
	Route::post('createPreinstallGold','Gold\GoldSendController@createPreInstallGold');
	Route::get('recycleGold','Gold\GoldSendController@recycleGold');
});

Route::group(['prefix' => 'gold/external', 'middleware' => ['auth', 'auth_admin', 'format']], function() {
    Route::get('select',    'Gold\GoldExternalController@select');
    Route::post('create',   'Gold\GoldExternalController@create');
    Route::post('update',    'Gold\GoldExternalController@update');
    Route::delete('delete', 'Gold\GoldExternalController@delete');
});

Route::group(['prefix'=>'gold/export','middleware' => ['auth', 'auth_admin', 'format']],function (){
    Route::get('goodsAmountExport','Gold\GoldExportController@goodsAmountExport');
	Route::get('accountExport','Gold\AccountExportController@export');
});

Route::group(['prefix' => 'gold/recycle', 'middleware' => ['auth', 'auth_admin', 'format']], function (){
	Route::get('count','Gold\GoldRecycleController@count');
	Route::get('select','Gold\GoldRecycleController@select');
	Route::get('goldStage','Gold\GoldRecycleController@goldStage');
	Route::get('recyclePerson','Gold\GoldRecycleController@recyclePerson');
	Route::get('recycleInfo','Gold\GoldRecycleController@recycleInfo');
});

Route::group([ 'prefix' => 'report'], function () {
	Route::get('export', 'Report\ParkingController@export');//路邊停車繳費成功調用接口
});

//消息設定
Route::group(['prefix' => 'message/setting', 'middleware' => ['auth', 'auth_admin', 'format']], function (){
	Route::get('count','Message\SettingController@count');
	Route::get('select','Message\SettingController@select');
	Route::get('getIndex','Message\SettingController@getIndex');
	Route::delete('delete','Message\SettingController@delete');
	Route::post('save','Message\SettingController@save');
	Route::post('saveSend','Message\SettingController@saveSend');
	Route::get('saveTaskId','Message\SettingController@saveTaskId');
	Route::post('saveExample','Message\SettingController@saveExample');
	Route::post('image','Resource\ImageUploadController@cover');
	Route::get('getUuid','Message\SettingController@getUuid');
	Route::post('send','Message\SettingController@send');
	Route::get('getGroupId','Message\SettingController@getGroupId');
	Route::get('messageInfo','Message\SettingController@messageInfo');
});

//消息推播
Route::group(['prefix' => 'message/send', 'middleware' => ['auth', 'auth_admin', 'format']], function (){
	Route::get('count','Message\SendController@count');
	Route::get('select','Message\SendController@select');
	Route::post('stopTask','Message\SendController@stopTask');
    Route::get('recycleGold','Message\SendController@recycleGold');
    Route::get('getMsgMame','Message\SendController@getMsgMame');
    Route::get('getReadMembers', 'Message\SendController@getReadMembers');
    Route::get('getSatisfaction', 'Message\SendController@getSatisfaction');
    Route::get('exportSatisfaction', 'Message\SendController@exportSatisfaction');
	Route::get('messageInfo','Message\SendController@messageInfo');
	Route::post('updateSend','Message\SendController@updateSend');
	Route::get('deleteTask/{id}','Message\SendController@deleteTask');
});

//問卷設定
Route::group(['prefix' => 'question', 'middleware' => ['auth', 'auth_admin', 'format']], function (){
    Route::get('count','Message\QuestionController@count');
    Route::get('select','Message\QuestionController@select');
    Route::get('get','Message\QuestionController@get');
    Route::delete('delete','Message\QuestionController@delete');
    Route::post('save','Message\QuestionController@save');
	Route::get('getAllName','Message\QuestionController@getAllName');
	Route::get('recycleGold','Message\QuestionController@recycleGold');
	Route::get('questionnaireexport/{id}','Message\QuestionnaireController@export');
	Route::get('questionnaireselect/{id}','Message\QuestionnaireController@select');
	Route::get('questionnaiuserdata/{id}/{id2}/{id3}','Message\QuestionnaireController@userdata');
	Route::get('getAnswersCount','Message\QuestionController@getAnswersCount');
});

//活動
Route::group(['prefix' => 'activity', 'middleware' => ['auth', 'auth_admin', 'format']], function (){
	Route::get('count','Message\ActivityController@count');
	Route::get('select','Message\ActivityController@select');
	Route::post('create','Message\ActivityController@create');
	Route::get('get','Message\ActivityController@get');
	Route::delete('delete','Message\ActivityController@delete');
	Route::get('getAllName','Message\ActivityController@getAllName');
	Route::get('onlineUserList','Message\ActivityController@onlineUserList');
	Route::get('onlineUserCount','Message\ActivityController@onlineUserCount');
    Route::get('offlineUserList','Message\ActivityController@offlineUserList');
    Route::get('offlineUserCount','Message\ActivityController@offlineUserCount');
	Route::get('onlineUserExport/{id}/{name}/{time}/{online}/{type}', 'Message\ActivityController@onlineUserExport');
    Route::get('offlineUserExport/{id}/{name}/{time}/{online}/{offline}/{remain}/{type}', 'Message\ActivityController@offlineUserExport');
	Route::get('recycleGold', 'Message\ActivityController@recycleGold');
	Route::post('createDepartmentGroup','Message\ActivityController@createDepartmentGroup');
	Route::get('getAnswers/{id}/{userId}', 'Message\ActivityController@getAnswers');
	Route::get('downloadFile/{id}/{userId}/{mobile}','Message\ActivityController@downloadFile');
	Route::get('getAnswersCount','Message\ActivityController@getAnswersCount');
	Route::get('isFile/{id}/{userId}','Message\ActivityController@isFile');
	Route::get('make/record','Message\ActivityController@makeRecord');
});

Route::group(['prefix'=>'shop', 'middleware' => ['auth', 'auth_admin', 'format']],function (){
    Route::post('cover','Resource\ImageUploadController@cover');
});

Route::group(['prefix' => 'content', 'middleware' => ['auth', 'auth_admin', 'format']], function (){
    Route::group(['prefix'=>'ad'],function (){
        Route::get('count','Content\AdController@count');
        Route::get('select','Content\AdController@select');
        Route::get('get','Content\AdController@get');
        Route::post('create','Content\AdController@create');
        Route::post('update','Content\AdController@update');
        Route::delete('delete','Content\AdController@delete');
        Route::post('off','Content\AdController@offSale');
        Route::post('on','Content\AdController@onSale');
        Route::post('cover','Resource\ImageUploadController@cover');
    });
    Route::group(['prefix'=>'banner'],function (){
        Route::get('count','Content\BannerController@count');
        Route::get('select','Content\BannerController@select');
        Route::get('get','Content\BannerController@get');
        Route::post('create','Content\BannerController@create');
        Route::post('update','Content\BannerController@update');
        Route::delete('delete','Content\BannerController@delete');
        Route::post('off','Content\BannerController@offSale');
        Route::post('on','Content\BannerController@onSale');
        Route::post('cover','Resource\ImageUploadController@cover');
    });
    Route::group(['prefix'=>'app'],function (){
        Route::get('count','Content\AppStoreController@count');
        Route::get('select','Content\AppStoreController@select');
        Route::get('get','Content\AppStoreController@get');
        Route::post('create','Content\AppStoreController@create');
        Route::post('update','Content\AppStoreController@update');
        Route::delete('delete','Content\AppStoreController@delete');
        Route::post('off','Content\AppStoreController@offSale');
        Route::post('on','Content\AppStoreController@onSale');
        Route::post('icon','Resource\ImageUploadController@cover');
    });
    Route::group(['prefix'=>'welcome'],function (){
        Route::get('count','Content\WelcomeController@count');
        Route::get('select','Content\WelcomeController@select');
        Route::get('get','Content\WelcomeController@get');
        Route::post('create','Content\WelcomeController@create');
        Route::post('update','Content\WelcomeController@update');
        Route::delete('delete','Content\WelcomeController@delete');
        Route::post('off','Content\WelcomeController@offSale');
        Route::post('on','Content\WelcomeController@onSale');
        Route::post('cover','Resource\ImageUploadController@cover');
    });
    Route::group(['prefix'=>'versions'],function (){
        Route::get('count','Content\VersionsController@count');
        Route::get('select','Content\VersionsController@select');
        Route::get('get','Content\VersionsController@get');
        Route::post('create','Content\VersionsController@create');
        Route::post('update','Content\VersionsController@update');
        Route::delete('delete','Content\VersionsController@delete');
        Route::post('off','Content\VersionsController@offSale');
        Route::post('on','Content\VersionsController@onSale');
        Route::post('cover','Resource\ImageUploadController@cover');
    });
});


Route::group(['prefix'=>'qr', 'middleware' => ['format']],function (){
    //頁面
    Route::get('user/{uid}','Resource\ImageUploadController@cover');

    //2個頁面
    Route::get('shop/{id}','Manager\ShopController@detail');

    Route::middleware('auth')->get('active/{id}',   'Resource\QrController@active');
    Route::middleware('auth')->get('goods/{id}',    'Resource\QrController@goods');
	Route::middleware('auth')->get('preferential/{id}',    'Resource\QrController@preferential');
});

Route::group(['prefix'=>'preferential', 'middleware' => ['auth','auth_admin','format']], function (){
	Route::post('create', 'Preferential\PreferentialController@create');
	Route::get('get', 'Preferential\PreferentialController@get');
	Route::post('resume', 'Preferential\PreferentialController@resume');
	Route::post('pause', 'Preferential\PreferentialController@pause');
	Route::post('update', 'Preferential\PreferentialController@update');
	Route::get('select', 'Preferential\PreferentialController@select');
	Route::post('delete', 'Preferential\PreferentialController@delete');
	Route::get('getUsers', 'Preferential\PreferentialController@getUsers');
	Route::get('getUserCount', 'Preferential\PreferentialController@getUserCount');
	Route::get('exportList', 'Preferential\PreferentialController@exportList');
	Route::get('exportUser', 'Preferential\PreferentialController@exportUser');
	Route::get('download', 'Preferential\PreferentialController@down');
	Route::post('cover','Resource\ImageUploadController@cover');
	Route::post('content','Resource\ImageUploadController@cover');
});

//繳稅通知
Route::group(['prefix' => 'tax', 'middleware' => ['format']], function () {
	Route::group(['prefix' => 'notice'], function () {
		Route::get('select', 'Tax\TaxNotice@select');
		Route::get('down/{id}/{sendTime}/{name}/{importNum}/{validNum}/{noticeNum}/{adminInfo}', 'Tax\TaxNotice@down');
	});
	Route::group(['prefix' => 'licence'], function () {
		Route::post('file', 'Resource\ImageUploadController@cover');
		Route::post('create', 'Tax\TaxLicence@create');
		Route::get('select', 'Tax\TaxLicence@select');
		Route::get('download/{id}', 'Tax\TaxLicence@download');
	});
});
