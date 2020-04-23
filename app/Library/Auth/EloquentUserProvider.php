<?php

namespace App\Library\Auth;


use App\Models\Admin;
use App\Models\RegisterUsers\Users;
use Illuminate\Auth\AuthenticationException;
use App\Library\RedisFacade as Redis;
use App\Library\Constant\Redis as RedisConstant;

class EloquentUserProvider extends \Illuminate\Auth\EloquentUserProvider
{
    /**
     * @param mixed $identifier
     * @param string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Model|null|object
     */
    public function retrieveByToken($identifier, $token)
    {
        if ($identifier == "") {//It's restful api token
            return $this->retrieveByRestfulToken($token);
        }

        return parent::retrieveByToken($identifier, $token);
    }

    /**
     * Create a new instance of the model.
     *
     * @return Users|Admin
     */
    public function createModel()
    {
        $class = '\\'.ltrim($this->model, '\\');

        return new $class;
    }

    /**
     * @param string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null|object|static
     */
    private function retrieveByRestfulToken(string $token)
    {
        //解析token 獲取到ID
        //$token
        try {
//            $md5 = md5($token);
//
//            $user = Redis::get(RedisConstant::CLIENT_LOGIN_USER_PREFIX.$md5);
//            $user = json_decode($user, true);

            //if (!is_array($user) || empty($user)) {
                $uid = $this->_validateToken($token);
                $model = $this->createModel();

                $model = $model->newQuery()
                    ->where($model->getAuthIdentifierName(), $uid)
                    ->first();

                if (empty($model)) {
                    return null;
                }

                if (!$this->_checkToken($model, $token)) {
                    return null;
                }
//            } else {
//                if (!isset($user['id'])) {
//                    return null;
//                }
//
//                $model = $this->createModel();
//
//                if ($model instanceof Users and !$this->_checkToken($user, $token)) {
//                    return null;
//                }
//
//                foreach ($user as $key => $value) {
//                    $model->setAttribute($key, $value);
//                }
//            }

//            if (!is_null($model) && $model instanceof Users) {
//                Redis::setex(RedisConstant::CLIENT_LOGIN_USER_PREFIX.$md5, json_encode($model->toArray()));
//            }

            return $model;
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param $user
     * @param string $token
     * @return bool
     */
    private function _checkToken($user, string $token) : bool
    {
        if ($user instanceof Admin){
            return true;
        }

        if ($user instanceof Users) {
            $user = $user->toArray();
        }

        if (is_array($user) && isset($user['login_token'])) {
            if (isset($user['login_token']) and $user['login_token'] == $token) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $token
     * @return int
     * @throws AuthenticationException
     */
    private function _validateToken(string $token): int
    {
        //nPnyxtKN8oZgoV4Lrttjzq26Lxfq7bqaZfbUsoZ8WFYlDwYhQztKQ1-wlpYjHNV3xc8bgJLPuw73Vo_knwgzbnwRh2lMEL1pd3rgPpkumwg
        try{
            $user = Encrypt::auth($token);

            $uid = $user['uid'];
        }catch (\Exception $exception) {
//            try{
//                $user = Crypt::decryptString($token);
//                $user = json_decode($user, true);
//                if(isset($user['uid'])) {
//                    $uid = $user['uid'];
//                }else{
//                    throw new AuthenticationException('Unauthenticated.');
//                }
//            }catch (\Exception $exception) {
                throw new AuthenticationException('Unauthenticated.');
            //}
        }

        return $uid;
    }

}