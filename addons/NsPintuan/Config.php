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
namespace addons\NsPintuan;

use addons\BaseConfig;
class Config extends BaseConfig
{
    /**
     * 菜单设置
     */
    public function menu(){
        $menu = [
           [
                'module_name' => '拼团',
                'controller' => 'tuangou',
                'method' => 'pintuanlist',
                'parent' => ['module' => 'admin', 'controller' => 'promotion',  'method' => 'index', 'level' => 2],
                'url' => 'tuangou/pintuanlist',
                'is_menu' => 1,
                'is_dev' => 0,
                'sort' => 11,
                'desc' => '拼团设置',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1,
            ],
            [
                'module_name' => '拼团设置',
                'controller' => 'tuangou',
                'method' => 'tuangouList',
                'parent' => ['module' => 'admin', 'controller' => 'promotion',  'method' => 'index', 'level' => 2],
                'url' => 'tuangou/tuangouList',
                'is_menu' => 0,
                'is_dev' => 0,
                'sort' => 1,
                'desc' => '拼团设置',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1,
            ],
            [
                'module_name' => '拼团列表',
                'controller' => 'tuangou',
                'method' => 'pintuanlist',
                'parent' => ['module' => 'admin', 'controller' => 'promotion',  'method' => 'index', 'level' => 2],
                'url' => 'tuangou/pintuanlist',
                'is_menu' => 0,
                'is_dev' => 0,
                'sort' => 1,
                'desc' => '拼团设置',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1,
            ],
        ];
        return $menu;

    }
}