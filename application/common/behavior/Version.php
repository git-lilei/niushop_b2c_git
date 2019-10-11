<?php
// +----------------------------------------------------------------------
// | NiuCloud [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: niuteam <niuteam@163.com>
// +----------------------------------------------------------------------
namespace app\common\behavior;

/**
 * 版本管理0
 */
class Version
{
	// 行为扩展的执行入口必须是run
	public function run()
	{
		if (!defined('NIU_VERSION')) {
			define('NIU_VERSION_NO', '3.1.3');
			define('NIU_VERSION', '3.1.3企业版');
			define('NIU_VER_DATE', '2019-7-20');
			define('NIU_RELEASE', '20190720');
		}
	}
}