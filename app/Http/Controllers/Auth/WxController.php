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
use App\Models\RegisterUsers\UserInfo;
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
     * @api {get} /api/wx/login 微信登录并自动注册(wx小程序回调)
     * @apiGroup 登录
     * @apiName login
     *
     * @apiParam {String} code
     * @apiParam {String} encryptedData
     * @apiParam {String} iv
     * @apiVersion 1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "token": "session验证str",
     *       "user": {//用户信息}
     *     }
     */
    /**
     * @Name 微信登录并自动注册
     * @return array
     * @throws
     */
    public function login(): array
    {
        $code = request('code', '');
        $encryptedData = request('encryptedData', '');
        $iv = request('iv', '');

        $userInfo = $this->wxxcx->getLoginInfo($code);
        if (!isset($userInfo['openid'])) {
            log('code =?', $code);
            return ['code' => ErrorConstant::DATA_ERR, 'response' => 'verify error'];
        }
        $exists = UserBind::query()->where('open_id', $userInfo['openid'])->first(['id']);
        if (empty($exists)) {
            $uid = Users::query()->insertGetId([
                'username' => $userInfo['openid'],
                'password' => '',
                'typ' => 3 //wechat
            ]);
            UserBind::query()->insert([
                'user_id' => $uid,
                'typ' => 3
            ]);
            $result = $this->wxxcx->getUserInfo($encryptedData, $iv);
            if (is_array($result)) {
                $user = $result;
                //获取解密后的用户信息

//        {
//            "openId": "OPENID",
//  "nickName": "NICKNAME",
//  "gender": GENDER,
//  "city": "CITY",
//  "province": "PROVINCE",
//  "country": "COUNTRY",
//  "avatarUrl": "AVATARURL",
//  "unionId": "UNIONID",
//  "watermark": {
//            "appid":"APPID",
//    "timestamp":TIMESTAMP
//  }
//}
                UserInfo::query()->insert([
                    'nickname' => $result['nickName'],
                    'gender' => $result['gender'],
                    'city' => $result['city'],
                    'province' => $result['province'],
                    'country' => $result['country'],
                    'avatar' => $result['avatarUrl'],
                    'union_id' => $result['unionId'],
                ]);
            }
        } else {
            $uid = $exists['uid'];
            $user = UserInfo::query()->where('user_id', $uid)->first()->toArray();
        }

        $encrypt = Encrypt::getLoginResult(['uid' => $uid, 'device_uuid' => $userInfo['openid']]);

        $user['id'] = $uid;
        return [
            'token' => $encrypt['token'],
            'user' => $user
        ];
    }

    public function login1(): array
    {
        return [];
    }

    public function getToken(): array
    {
        $res = Encrypt::generateToken([
            'uid' => 1,
            'device_uuid' => 123
        ]);
        return [
            'token' => $res
        ];
    }
}