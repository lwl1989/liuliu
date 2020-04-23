<?php

namespace App\Services\RegisterUsers;


use App\Library\Constant\Common;
use App\Library\Constant\User;
use App\Models\Admin\AdminShopProfile;
use App\Models\RegisterUsers\UserGold;
use App\Models\RegisterUsers\UserGoldStage;
use App\Models\RegisterUsers\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserGoldService
{
    /**
     * @param $userId
     * @return array
     */
    public static function getGoldByStage($userId, $page, $limit): array
    {
        $result = DB::select('select stage_id,sum(`remainder`) as total,expire_time as period_time 
                from user_gold_stage where user_id = ? and expire_time > ?  group by stage_id having total > 0 order by expire_time asc, stage_id asc', [
            $userId, date('Y-m-d H:i:s')
        ]);

        if (isset($result[0])) {
            $golds = json_decode(json_encode($result), true);

            if ($limit == 1) {
				return $golds[0];
            } else {
	            return $golds;
            }
        }
        return [];
    }
	/**
     * @param $userId
     * @return array
     */
    public static function getGoldByStageAlias($userId): array
    {
        $result = DB::select('select stage_id,sum(`remainder`) as total,expire_time as period_time 
                from user_gold_stage where user_id = ? and expire_time > ?  group by stage_id having total > 0 order by expire_time asc', [
            $userId, date('Y-m-d H:i:s')
        ]);
		
        if (isset($result[0])) {
            $golds = json_decode(json_encode($result), true);
			return  $golds;
        }
		return [];
      
    }
    /**
     * 獲取某個用戶的金幣及其最早過期時間
     * @param $userId
     * @return array
     */
    public static function getGold($userId): array
    {

        $result = DB::select('select sum(`remainder`) as total,min(expire_time) as min_time 
                from user_gold_stage where id in (select id
                  from user_gold_stage where user_id = ? and expire_time > ?)', [
            $userId, date('Y-m-d H:i:s')
        ]);

        if (isset($result[0])) {
            $golds = json_decode(json_encode($result[0]), true);
            return $golds;
        }

        return [];
    }

    /**
     * 獲取金幣日誌列表
     * @param $userId
     * @param int $enter
     * @param int $event
     * @param int $stageId
     * @param int $limit
     * @param int $page
     * @return array
     */
    public static function goldLimit($userId, int $event = 0, $stageId = 0, int $enter = -1, int $limit = 15, int $page = 1): array
    {
        $query = UserGold::query();
        $query->where('user_id', $userId)
            ->where('status', Common::STATUS_NORMAL)
            ->where('number', '>', 0);
        //出入帳
        if ($enter !== -1) {
            $query->where('user_golds.is_enter', $enter);
        }
        //事件類型
        if ($event !== 0) {
            $query->where('user_golds.event_type', $event);
        }
        //分期
        if ($stageId !== 0) {
            $query->where('user_golds.stage_id', $stageId);
        }

        $list = $query
            //->skip(($page - 1) * $limit)
            ->orderByDesc('create_time')
	        //->take($limit)
            ->get([
				'id',
                'is_enter as log_type',
                'user_id',
                'stage_id',
                'number',
                'remark',
                //'period',
                'event_id',
                'event_type',
                'create_time'
            ])
	        ->toArray();

		$arr_data = [];
		$arr = [];

		foreach ($list as $key => &$value) {
			if ($value['event_type'] == User::GOLD_EVENT_PARK) {
				$arr_data[$value['event_id']][] = $value;
				
				$i = 0;
				foreach ($arr_data as $j => $v) {
					$number = 0;
					$bak = [];
					foreach ($v as $k => $val) {
						$bak = $val;
						$number += $val['number'];
					
					}
					$arr[$i] = $bak;
					$arr[$i]['number'] = $number;
					$i++;
				}
				unset($list[$key]);
			}

			if ($value['event_type'] == User::GOLD_RECYCLE_PERSON) {
				$value['remark'] = '縣府已手動回收您的金幣';
			}

			if ($value['event_type'] == User::GOLD_EVENT_EXPIRE) {
				$value['remark'] = '金幣到期未使用';
			}
		}

		//echo "<pre>";print_r($list);
		//echo "<pre>";print_r($arr);

		$result = array_merge($list, $arr);
		//echo "<pre>";print_r($result);exit;

        $lostExpires = [];

        $userStage = UserGoldStage::query()
            ->where('user_id',$userId)
            ->where('remainder', '>', 0)
            ->where('expire_time','<', date('Y-m-d H:i:s', time()))
            ->get(['expire_time', 'id', 'stage_id', 'remainder'])
            ->toArray();

        if (!empty($userStage)) {
            //用戶期別IDS
            $userStageIds = array_column($userStage, 'id');

            $exists = UserGold::query()
                ->where('user_id', $userId)
                ->where('event_type', User::GOLD_EVENT_EXPIRE)
                ->where('is_enter', User::USER_GOLD_OUTER)
                ->whereIn('event_id', $userStageIds)
                ->get(['event_id as expire_id'])
	            ->toArray();

            if(!empty($exists)) {
                $exists = array_column($exists, 'expire_id');
                $lostExpire = array_diff($userStageIds, $exists);
            }else{
                $lostExpire = $userStageIds;
            }

            if(!empty($lostExpire)) {
                $userStage = array_column($userStage, null, 'id');
                foreach ($lostExpire as $userStageId) {
                    $item = [
                        'is_enter'      =>   User::USER_GOLD_OUTER,
                        'event_type'    =>   User::GOLD_EVENT_EXPIRE,
                        'event_id'      =>   $userStageId,
                        'stage_id'      =>   $userStage[$userStageId]['stage_id'],
                        'create_time'   =>   $userStage[$userStageId]['expire_time'],
                        'number'        =>   $userStage[$userStageId]['remainder'],
                        'remark'        =>   '金幣到期未使用'
                    ];
                    $id = UserGold::query()->insertGetId($item);
                    $lostExpires[] = array_merge($item, ['id'=>$id]);
                }
            }
        }

	    if(!empty($lostExpires)) {
		    foreach ($lostExpires as &$expire) {
			    $expire['log_type'] = $expire['is_enter'];
			    unset($expire);
		    }
		    $add = $limit - count($result);
		    while ($add > 0 and !empty($lostExpires)) {
		    	
//                $list = array_merge($list, array_pop($lostExpires));
//                if (!empty($list)) {
//
//	                $date = array_column($list, 'create_time');
//	                array_multisort($date, SORT_DESC, $list);
//	                array_slice($list, ($page-1)*$limit, $limit);
//                }

			    array_unshift($result, array_pop($lostExpires));
			    $add --;
		    }
	    }
		$order_time = [];
		foreach ($result as &$value){
			$order_time[] = strtotime($value['create_time']);
		}

		array_multisort($order_time, SORT_DESC, $result);
	    $result = array_slice($result, ($page - 1) * $limit, $limit);

        return $result;
    }

    /**
     * @param $user
     * @param $stageId
     * @param $tranUser
     * @param $number
     * @return array
     */
    public static function tranGold(array $user, $stageId, array $tranUser, $number) : array
    {
        $status = [
            'golds'     =>  0
        ];
        $userId = $user['id'] ?? ($user['user_id'] ?? Auth::id());
        $tranId = $tranUser['id'] ?? $tranUser['user_id'];
        DB::beginTransaction();
        try{
            $affected = UserGoldStage::query()
                ->where('user_id', $userId)
                ->where('stage_id', $stageId)
                ->where('remainder','>=',$number)
                ->update([
                    'used'      =>  DB::raw('used + '.$number),
                    'remainder' =>  DB::raw('remainder - '.$number),
                ]);

            if ($affected > 0) {
                $exists = UserGoldStage::query()
                    ->where('user_id', $tranId)
                    ->where('stage_id', $stageId)
                    ->first(['id','remainder','sum']);

                if (!empty($exists)) {
                    $exists->setAttribute('remainder', $exists->getAttributeValue('remainder') + $number);
                    $exists->setAttribute('sum', $exists->getAttributeValue('sum') + $number);

                    $exists->save();
                } else {
                    $expire =  UserGoldStage::query()
                        ->where('user_id', $userId)
                        ->where('stage_id', $stageId)
                        ->first(['expire_time']);

                    if (!empty($expire)) {
                        UserGoldStage::query()->insert([
                            'sum'       =>  $number,
                            'remainder' =>  $number,
                            'stage_id'  =>  $stageId,
                            'user_id'   =>  $tranId,
                            'expire_time'=> $expire->getAttribute('expire_time')
                        ]);
                    }
                }

	            $name   = '轉贈您的好友'.$tranUser['name'].'(+'.$tranUser['code'].$tranUser['mobile'].')';
	            $remark = '獲得好友'.$user['name'].'(+'.$user['code'].$user['mobile'].')轉贈金幣';

                //判斷$tranId是否是公益團體
	            $user_info = Users::query()->find($tranId, ['mobile'])->toArray();
                if (!empty($user_info)) {
					//判斷手機號是否是公益團體
	                $shop = AdminShopProfile::query()
		                ->where('type', Common::STATUS_DISABLE)
		                ->where('mobile', $user_info['mobile'])
		                ->orWhere('mobile', '0'. $user_info['mobile'])
		                ->get(['id', 'name'])
		                ->toArray();
	                if (count($shop) > 0) {
		                $name   = '轉贈'. $shop[0]['name'];
		                $remark = '獲得'.'(+'.$user['code'].$user['mobile'].')轉贈金幣';
	                }
                }

                UserGold::query()->insert([
                    'user_id'   => $userId,
                    'stage_id'  => $stageId,
                    'event_type'=> User::GOLD_EVENT_TRAN_OUT,
                    'event_id'  => $tranId,
                    'number'    => $number,
                    //轉贈您的好友梁家寧(098765431)
                    'remark'    => $name,
                    'is_enter'  => User::USER_GOLD_OUTER
                ]);

                UserGold::query()->insert([
                    'user_id' => $tranId,
                    'stage_id' => $stageId,
                    'event_type' => User::GOLD_EVENT_TRAN_IN,
                    'number' => $number,
                    'event_id'  =>  $userId,
                    //獲得好友梁家寧(098765431)轉贈金幣
                    'remark' => $remark,
                    'is_enter'  => User::USER_GOLD_ENTER
                ]);

                $status['golds'] = $number;
            }
            DB::commit();
        }catch (\Exception $exception) {
            DB::rollBack();
            $status['golds'] = 0;
            $status['message'] = $exception->getMessage();
        }

        return $status;
    }
}