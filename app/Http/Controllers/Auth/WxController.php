<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/4/21
 * Time: 20:18
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Iwanli\Wxxcx\Wxxcx;

class WxController extends Controller
{
    protected $wxxcx;

    function __construct(Wxxcx $wxxcx)
    {
        $this->wxxcx = $wxxcx;
    }

    public function login() : array
    {
        //code 在小程序端使用 wx.login 获取
        $code = request('code', '');
        //encryptedData 和 iv 在小程序端使用 wx.getUserInfo 获取
        $encryptedData = request('encryptedData', '');
        $iv = request('iv', '');

        //根据 code 获取用户 session_key 等信息, 返回用户openid 和 session_key
        $userInfo = $this->wxxcx->getLoginInfo($code);

        //todo:check exist
        //todo:insert or update login log
        //获取解密后的用户信息
        $result = $this->wxxcx->getUserInfo($encryptedData, $iv);
        if(is_array($result)) {
            return $result;
        }

        return json_decode($result, true);
    }
}