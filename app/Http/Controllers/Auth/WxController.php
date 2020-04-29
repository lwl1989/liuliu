<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/4/21
 * Time: 20:18
 */

namespace App\Http\Controllers\Auth;

use App\Exceptions\ErrorConstant;
use App\Http\Controllers\Controller;
use App\Library\Auth\Encrypt;
use App\Models\RegisterUsers\UserBind;
use App\Models\RegisterUsers\Users;
use App\Models\RegisterUsers\UserThird;
use Iwanli\Wxxcx\Wxxcx;

class WxController extends Controller
{
    protected $wxxcx;

    function __construct(Wxxcx $wxxcx)
    {
        $this->wxxcx = $wxxcx;
    }


    /**
     * @api {get} /user/{id} 获取用户信息
     * @apiGroup User
     * @apiName Get-user
     */
    /**
     * @Name 微信登录并自动注册
     * @return array
     * @throws
     */
    public function login() : array
    {
        $code = request('code', '');
        $encryptedData = request('encryptedData', '');
        $iv = request('iv', '');

        $userInfo = $this->wxxcx->getLoginInfo($code);
        if(!isset($userInfo['openid'])) {
            return ['code'  =>  ErrorConstant::DATA_ERR, 'response' =>  'verify error'];
        }
        $exists = UserBind::query()->where('open_id', $userInfo['openid'])->first(['id']);
        if (empty($exists)) {
            $uid = Users::query()->insertGetId([
                'username' =>  $userInfo['openid'],
                'password' =>  '',
                'typ'      =>  3 //wechat
            ]);
            UserBind::query()->insert([
                'user_id'   =>  $uid,
                'typ'       =>  3
            ]);
        }else{
            $uid = $exists['uid'];
        }

        $encrypt = Encrypt::getLoginResult(['uid' => $uid, 'device_uuid' => $userInfo['openid']]);
        //获取解密后的用户信息
        $result = $this->wxxcx->getUserInfo($encryptedData, $iv);
        if(is_array($result)) {
            $result['token'] = $encrypt['token'];
            return $result;
        }

        return json_decode($result, true);
    }

    public function login1() : array  {
        return [];
    }

    public function getToken() : array  {
        $res = Encrypt::generateToken([
            'uid'   =>  1,
            'device_uuid'  => 123
        ]);
        return [
            'token' => $res
        ];
    }
}