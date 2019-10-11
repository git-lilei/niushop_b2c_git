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
namespace addons\NsCombopackage;

use addons\BaseConfig;
class Config extends BaseConfig
{
    /**
     * 菜单设置
     */
    public function menu(){
        $menu = [
            [
                'module_name' => '组合套餐',
                'controller' => 'promotion',
                'method' => 'combopackagepromotionlist',
                'parent' => ['module' => 'admin', 'controller' => 'promotion',  'method' => 'index', 'level' => 2],
                'url' => 'promotion/combopackagepromotionlist',
                'is_menu' => 1,
                'is_dev' => 0,
                'sort' => 6,
                'desc' => '相关教程：<a href="http://bbs.niushop.com.cn/forum.php?mod=viewthread&tid=2319&extra=page%3D2" target="_blank">http://bbs.niushop.com.cn/forum.php?mod=viewthread&tid=2319&extra=page%3D2</a>',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1,
            ],
            [
                'module_name' => '编辑组合套餐',
                'controller' => 'promotion',
                'method' => 'combopackagepromotionedit',
                'parent' => ['module' => 'admin', 'controller' => 'promotion',  'method' => 'index', 'level' => 2],
                'url' => 'promotion/combopackagepromotionedit',
                'is_menu' => 0,
                'is_dev' => 0,
                'sort' => 1,
                'desc' => '编辑组合套餐',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1,
            ]
        ];
        return $menu;

    }
}