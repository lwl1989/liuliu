<?php

namespace App\Http\Controllers\Resource;


use App\Exceptions\ErrorConstant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        return $this->cover($request);
    }

    public function cover(Request $request)
    {
        $path = $request->getRequestUri();

		if (strpos($path, 'api/') === 1) {
			$path = substr($path, 4);
		}

        $allow = [
            '/user/avatar', '/goods/cover', '/goods/cate/cover', '/message/setting/image', '/department/group/file', '/department/image',
            '/content/ad/cover', '/content/banner/cover', '/content/app/icon', '/content/welcome/cover', '/shop/cover', '/activity/answer/file',
	        '/preferential/cover', '/preferential/content', '/tax/licence/file'
        ];

        if (!in_array($path, $allow)) {
            return ['code' => ErrorConstant::SYSTEM_ERR, 'response' => 'path it\'s not allow'];
        }
        // 自動計算文件的md5
        // Storage::putFile('photos', new File('/path/to/photo'));

        // 手動指定文件名...
        // Storage::putFileAs('photos', new File('/path/to/photo'), 'photo.jpg');
        //通過path獲取路徑

	    $file = $request->file('file');

	    $md5 = md5(time());
	    $path = sprintf('%s/%s/%s/%s', $path, $md5[0], $md5[1], $md5[2]);

	    //if ($file->isValid()) {
		    $originalName = $file->getClientOriginalName(); // 文件原名
		    $ext = $file->getClientOriginalExtension();     // 擴展名
		    $realPath = $file->getRealPath();   //臨時文件的絕對路徑
		    $type = $file->getClientMimeType();     // image/jpeg

		    if ($type !== 'image/png' || $type !== 'image/jpg' || $type !== 'image/jpeg' ) {
			    $path = substr($path, 1) . '/'.  uniqid() . '.' . $ext;
			    Storage::disk('public')->put($path, file_get_contents($realPath));
		    } else {//application/vnd.ms-excel
			    //手動指定驅動爲public
			    $path = Storage::disk('public')->put($path, $file);
		    }

		    $sfsHost = env('SFS_URL', null);

		    if (empty($sfsHost)) {
			    $url = $request->getSchemeAndHttpHost() . Storage::url($path);
		    } else {
			    $url = rtrim($sfsHost, '/') . Storage::url($path);
		    }
	    //}

        return ['name' => $originalName, 'path' => $path, 'url' => $url, 'type' => $type];

    }
}