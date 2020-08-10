<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/4/28
 * Time: 18:16
 */

namespace App\Models\Content;


use App\Admin\Models\Contents;
use App\Models\Common\Tags;
use App\Models\Model;

class ContentTags extends Model
{
    public $table = 'content_relation';
    public $timestamps = false;


    /**
     * @param array $list
     * @param int $tagType 1 tag 2 topic
     * @return array
     */
    public static function getContentTagWithResult(array $list, $tagType = 1): array
    {
        if (empty($list)) {
            return $list;
        }

        $listIds = array_column($list, 'id');

        $ccs = ContentTags::query()->whereIn('content_id', $listIds)->whereIn('typ', $tagType)->get()->toArray();
        $tagsIds = array_column($ccs, 'relation_id');
        if ($tagType == 1) {
            $tags = Tags::query()->whereIn('id', $tagsIds)->get()->toArray();
        } else {
            $tags = Topics::query()->whereIn('id', $tagsIds)->get()->toArray();
        }
        foreach ($list as &$item) {
            $item['tag_id'] = 0;
            foreach ($ccs as $cc) {
                if ($cc['content_id'] == $item['id']) {
                    $item['tag_id'] = $cc['relation_id'];
                    break;
                }
            }

            if ($item['tag_id'] != 0) {
                foreach ($tags as $tag) {
                    if ($item['tag_id'] == $tag['id']) {
                        $item['tag_name'] = $tag['name'];
                        break;
                    }
                }
            }

        }

        return $list;
    }
}