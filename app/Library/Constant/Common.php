<?php

namespace App\Library\Constant;


class Common
{


    //共用
    const DELETED = 1;
    const NO_DELETE = 0;
    //能共用就共用
    const STATUS_NORMAL = 1;
    const STATUS_DISABLE = 2;
    //管理員權限
    const ADMIN_ROLE_SHOP = 1;
    const ADMIN_ROLE_EMPLOYEE = 2;
    const ADMIN_ROLE_MANAGER = 3;


    const ROLE_MAP = [
        self::ADMIN_ROLE_SHOP => '特約商店',
        self::ADMIN_ROLE_EMPLOYEE => '縣府員工',
        self::ADMIN_ROLE_MANAGER => '管理員'
    ];

    public static function getRoleNumber(string $role): int
    {
        $arr = array_reverse(self::ROLE_MAP);
        return isset($arr[$role]) ? $arr[$role] : 0;
    }

    const PERMISSION_MAPPING_ADMIN = [
        'admins' => [
            'name' => '帳戶管理',
            'actions' => [
                [
                    'en' => 'admins',
                    'name' => '縣府員工',
                    'vue' => 'admins'
                ],
                [
                    'en' => 'company',
                    'name' => '特約商店',
                    'vue' => 'company'
                ],
                [
                    'en' => 'department',
                    'name' => '縣府單位',
                    'vue' => 'department',
                ]
            ]
        ],
        'department' => [
            'name' => '資料維護',
            'actions' => [
                [
                    'en' => 'users',
                    'name' => '臺東縣民',
                    'vue' => 'users',
                ],
                [
                    'en' => 'users_decode',
                    'name' => '臺東縣民(身份證解碼)',
                    'vue' => 'users/decode',
                ],
                [
                    'en' => 'department_group',
                    'name' => '縣民群組',
                    'vue' => 'department/group',
                ],
                [
                    'en' => 'ad',
                    'name' => '廣告活動',
                    'vue' => 'content/ad'
                ],
                [
                    'en' => 'banner',
                    'name' => '置頂公告',
                    'vue' => 'content/banner'
                ],
                [
                    'en' => 'welcome',
                    'name' => '歡迎頁',
                    'vue' => 'content/welcome'
                ],
                [
                    'en' => 'ext_app',
                    'name' => 'APP小舖',
                    'vue' => 'content/app'
                ],
                [
                    'en' => 'versions',
                    'name' => '版本管理',
                    'vue' => 'content/versions'
                ],
	            [
		            'en' => 'password',
		            'name' => '驗證碼查詢',
		            'vue' => 'content/password'
	            ]
            ]
        ],
        'message' => [
            'name' => '事件管理',
            'actions' => [
                [
                    'en' => 'message_setting',
                    'name' => '推播設定',
                    'vue' => 'message/setting',
                ],
                [
                    'en' => 'message_list',
                    'name' => '推播訊息',
                    'vue' => 'message/list',
                ],
                [
                    'en' => 'message_question',
                    'name' => '建立問卷',
                    'vue' => 'message/question',
                ],
                [
                    'en' => 'message_activity',
                    'name' => '建立活動',
                    'vue' => 'message/activity',
                ]
            ]
        ],
        'gold' => [
            'name' => '臺東金幣',
            'actions' => [
                [
                    'en' => 'gold_account',
                    'name' => '金幣帳戶',
                    'vue' => 'gold/account',
                ],
                [
                    'en' => 'gold_send',
                    'name' => '金幣發放',
                    'vue' => 'gold/send',
                ],
                [
                    'en' => 'gold_export',
                    'name' => '產生報表',
                    'vue' => 'gold/export',
                ],
	            [
		            'en' => 'gold_recycle',
		            'name' => '回收個人金幣',
		            'vue' => 'gold/recycle',
	            ]
            ],
        ],
        'exchange' => [
            'name' => '好禮兌換',
            'actions' => [
                [
                    'en' => 'goods',
                    'name' => '商品兌換',
                    'vue' => 'goods/list',
                ],
                [
                    'en' => 'record',
                    'name' => '兌換記錄查詢',
                    'vue' => 'goods/record',
                ],
	            [
	            	'en' => 'preferential_list',
		            'name' => '數位縣民優惠',
		            'vue' => 'goods/preferential'
	            ]
            ]
        ],
	    'tax'   => [
	        'name'  => '繳稅通知',
		    'actions'    => [
			    [
				    'en' => 'tax_notice',
				    'name' => '通知紀錄',
				    'vue' => 'tax/notice',
			    ],
			    [
				    'en' => 'tax_licence',
				    'name' => '車牌資料設定',
				    'vue' => 'tax/licence'
			    ]
		    ]
	    ]
    ];
    const PERMISSION_MAPPING_SHOP = [
        'exchange' => [
            'name' => '好禮兌換',
            'actions' => [
                [
                    'en' => 'goods',
                    'name' => '商品兌換',
                    'vue' => 'goods/list',
                ],
                [
                    'en' => 'record',
                    'name' => '兌換記錄查詢',
                    'vue' => 'goods/record',
                ],
            ]
        ]
    ];

    const PERMISSION_MAPPING = array(
        'admins' => [
            'name' => '帳戶管理',
            'actions' => [
                [
                    'en' => 'admins',
                    'name' => '縣府員工',
                    'vue' => 'admins'
                ]
            ]
        ],
        'department' => [
            'name' => '資料維護',
            'actions' => [
                /*[
                    'en' => 'users_decode',
                    'name' => '臺東縣民(身份證解碼)',
                    'vue' => 'users/decode',
                ],*/
                [
                    'en' => 'department_group',
                    'name' => '縣民群組',
                    'vue' => 'department/group',
                ],
	            [
		            'en' => 'password',
		            'name' => '驗證碼查詢',
		            'vue' => 'content/password'
	            ]
            ]
        ],
        'message' => [
            'name' => '事件管理',
            'actions' => [
                [
                    'en' => 'message_setting',
                    'name' => '推播設定',
                    'vue' => 'message/setting',
                ],
                [
                    'en' => 'message_list',
                    'name' => '推播訊息',
                    'vue' => 'message/list',
                ],
                [
                    'en' => 'message_question',
                    'name' => '建立問卷',
                    'vue' => 'message/question',
                ],
                [
                    'en' => 'message_activity',
                    'name' => '建立活動',
                    'vue' => 'message/activity',
                ],
            ]
        ],

        'gold' => [
            'name' => '臺東金幣',
            'actions' => [
                [
                    'en' => 'gold_export',
                    'name' => '產生報表',
                    'vue' => 'gold/export',
                ],
	            [
		            'en' => 'gold_send',
		            'name' => '金幣發放',
		            'vue' => 'gold/send',
	            ],
            ],
        ],
	    'tax'   => [
		    'name'  => '繳稅通知',
		    'actions'    => [
			    [
				    'en' => 'tax_notice',
				    'name' => '通知紀錄',
				    'vue' => 'tax/notice',
			    ],
			    [
				    'en' => 'tax_licence',
				    'name' => '車牌資料設定',
				    'vue' => 'tax/licence'
			    ]
		    ]
	    ]
    );

}