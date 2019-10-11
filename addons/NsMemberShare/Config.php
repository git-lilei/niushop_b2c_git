<?php
// +----------------------------------------------------------------------
// | test [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.zzstudio.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Byron Sampson <xiaobo.sun@gzzstudio.net>
// +----------------------------------------------------------------------
namespace addons\NsMemberShare;

use addons\BaseConfig;

class Config extends BaseConfig
{
	/**
	 * 菜单设置
	 */
	public function menu()
	{
		$menu = [
			[
				'module_name' => '会员分享',
				'controller' => 'MemberShare',
				'method' => 'index',
				'parent' => [ 'module' => 'admin', 'controller' => 'promotion', 'method' => 'memberPromotion', 'level' => 2 ],
				'url' => 'memberShare/index',
				'is_menu' => 1,
				'is_dev' => 0,
				'sort' => 4,
				'desc' => '会员分享',
				'module_picture' => '',
				'icon_class' => '',
				'is_control_auth' => 1,
			],
		    [
    		    'module_name' => '分享内容设置',
    		    'controller' => 'MemberShare',
    		    'method' => 'shareConfig',
    		    'parent' => [ 'module' => 'admin', 'controller' => 'promotion', 'method' => 'memberPromotion', 'level' => 2 ],
    		    'url' => 'memberShare/shareConfig',
    		    'is_menu' => 1,
    		    'is_dev' => 0,
    		    'sort' => 5,
    		    'desc' => '分享内容设置',
    		    'module_picture' => '',
    		    'icon_class' => '',
    		    'is_control_auth' => 1,
		    ],
		];
		return $menu;
		
	}
}