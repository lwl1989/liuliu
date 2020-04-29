<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/4/28
 * Time: 18:22
 */

namespace App\Http\Controllers\Common;


use App\Http\Controllers\Controller;
use App\Library\Constant\Common;
use App\Models\Common\Tags;
use App\Models\RegisterUsers\UserSubTags;
use Illuminate\Support\Facades\Auth;

class TagsController extends Controller
{
    /**
     * @api               {get} /api/user/tags 选择标签页面
     * @apiGroup          用户操作
     * @apiName           获取tag列表（首页）
     *
     * @apiParam {String} code
     * @apiParam {String} encryptedData
     * @apiParam {String} iv
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *      "tags": [ {
    "id": "1",
    "name": "真人秀",
    "sort": "1",
    "status": "1",
    "create_time": "1588069984",
    "update_time": "1588069984"
    },],
     *      "my_tags": [{
     *                  //...
     *              }],
     *   }
     */
    public function getList(): array
    {
        $tags = Tags::query()->where('status', Common::STATUS_NORMAL)->orderBy('sort', 'desc')->get();

        $myTags = UserSubTags::query()
            ->where('user_id', Auth::id())
            ->where('status', Common::STATUS_NORMAL)
            ->orderBy('create_time', 'desc')->get();

        if (is_array($tags) and is_array($myTags)) {
            $myTagsT = array_column($myTags, 'tag_id');

            foreach ($tags as $index => $tag) {
                if (in_array($tag['id'], $myTagsT)) {
                    unset($tags[$index]);
                }
            }

            $tags = array_values($tags);
        }

        return ['tags' => $tags, 'my_tags' => $myTags];
    }

    public function getAll(): array
    {
        $tags = Tags::query()->where('status', Common::STATUS_NORMAL)->orderBy('sort', 'desc')->get();


        return ['tags' => $tags];
    }

    /**
     * @api               {get} /api/user/menu 用户菜单（导航）
     * @apiGroup          用户操作
     * @apiName           获取用户菜单
     *
     * @apiParam {String} code
     * @apiParam {String} encryptedData
     * @apiParam {String} iv
     * @apiVersion        1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *      "tags": [ {
                "id": "1",
                "name": "真人秀",
                "sort": "1",
                "status": "1",
                "create_time": "1588069984",
                "update_time": "1588069984"
                }],
     *   }
     */
    public function getMenu(): array
    {
        $myTags = UserSubTags::query()
            ->where('user_id', Auth::id())
            ->where('status', Common::STATUS_NORMAL)
            ->orderBy('create_time', 'desc')->get();
        if (!is_array($myTags)) {
            $myTags = Tags::query()->where('status', Common::STATUS_NORMAL)->orderBy('sort', 'desc')->limit(6)->get();
        }

        array_unshift($myTags, [
            'name' => '推荐',
            'id' => -2,
            'sort' => 9999998,
            'status' => Common::STATUS_NORMAL,
            'create_time' => '0000-00-00 00:00:00',
            'update_time' => '0000-00-00 00:00:00'
        ]);
        array_unshift($myTags, [
            'name' => '关注',
            'id' => -1,
            'sort' => 9999999,
            'status' => Common::STATUS_NORMAL,
            'create_time' => '0000-00-00 00:00:00',
            'update_time' => '0000-00-00 00:00:00'
        ]);
        return $myTags;
    }
}