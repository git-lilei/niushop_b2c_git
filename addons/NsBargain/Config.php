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
namespace addons\NsBargain;

use addons\BaseConfig;
class Config extends BaseConfig
{
    /**
     * 菜单设置
     */
    public function menu(){
        $menu = [
           [
                'module_name' => '砍价',
                'controller' => 'Bargain',
                'method' => 'index',
                'parent' => ['module' => 'admin', 'controller' => 'promotion',  'method' => 'index', 'level' => 2],
                'url' => 'Bargain/index',
                'is_menu' => 1,
                'is_dev' => 0,
                'sort' => 12,
                'desc' => '砍价',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1
            ],
            [
                'module_name' => '砍价配置',
                'controller' => 'bargain',
                'method' => 'config',
                'parent' => ['module' => 'admin', 'controller' => 'promotion',  'method' => 'index', 'level' => 2],
                'url' => 'bargain/config',
                'is_menu' => 0,
                'is_dev' => 0,
                'sort' => 1,
                'desc' => '砍价配置',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1
            ],
            [
                'module_name' => '添加活动',
                'controller' => 'bargain',
                'method' => 'addBargain',
                'parent' => ['module' => 'admin', 'controller' => 'promotion',  'method' => 'index', 'level' => 2],
                'url' => 'bargain/addBargain',
                'is_menu' => 0,
                'is_dev' => 0,
                'sort' => 1,
                'desc' => '添加活动',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1
            ],
            [
                'module_name' => '修改活动',
                'controller' => 'bargain',
                'method' => 'editBargain',
                'parent' => ['module' => 'admin', 'controller' => 'promotion',  'method' => 'index', 'level' => 2],
                'url' => 'bargain/editBargain',
                'is_menu' => 0,
                'is_dev' => 0,
                'sort' => 1,
                'desc' => '添加活动',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1
            ]
        ];
        return $menu;

    }
}