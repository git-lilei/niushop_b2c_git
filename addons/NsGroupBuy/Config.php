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
namespace addons\NsGroupBuy;

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
				'module_name' => '团购',
				'controller' => 'Promotion',
				'method' => 'groupBuyList',
				'parent' => [ 'module' => 'admin', 'controller' => 'promotion', 'method' => 'index', 'level' => 2 ],
				'url' => 'promotion/groupbuylist',
				'is_menu' => 1,
				'is_dev' => 0,
				'sort' => 13,
				'desc' => '团购列表',
				'module_picture' => '',
				'icon_class' => '',
				'is_control_auth' => 1
			],
			[
				'module_name' => '添加团购',
				'controller' => 'Promotion',
				'method' => 'addGroupBuy',
				'parent' => [ 'module' => 'admin', 'controller' => 'promotion', 'method' => 'index', 'level' => 2 ],
				'url' => 'promotion/addgroupbuy',
				'is_menu' => 1,
				'is_dev' => 0,
				'sort' => 13,
				'desc' => '添加团购',
				'module_picture' => '',
				'icon_class' => '',
				'is_control_auth' => 1
			],
			[
				'module_name' => '修改团购团购',
				'controller' => 'Promotion',
				'method' => 'updateGroupBuy',
				'parent' => [ 'module' => 'admin', 'controller' => 'promotion', 'method' => 'index', 'level' => 2 ],
				'url' => 'promotion/updategroupbuy',
				'is_menu' => 1,
				'is_dev' => 0,
				'sort' => 13,
				'desc' => '修改团购',
				'module_picture' => '',
				'icon_class' => '',
				'is_control_auth' => 1
			],
		
		];
		return $menu;
		
	}
}