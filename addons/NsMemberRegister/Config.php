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
namespace addons\NsMemberRegister;

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
				'module_name' => '会员注册',
				'controller' => 'MemberRegister',
				'method' => 'index',
				'parent' => [ 'module' => 'admin', 'controller' => 'promotion', 'method' => 'memberPromotion', 'level' => 2 ],
				'url' => 'memberRegister/index',
				'is_menu' => 1,
				'is_dev' => 0,
				'sort' => 1,
				'desc' => '会员注册',
				'module_picture' => '',
				'icon_class' => '',
				'is_control_auth' => 1,
			]
		];
		return $menu;
		
	}
}