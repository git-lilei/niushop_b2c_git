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
namespace addons\NsDiyView;

use addons\BaseConfig;
class Config extends BaseConfig
{
    /**
     * 菜单设置
     */
    public function menu(){
        $menu = [
           [
              'module_name' => '自定义模板',
              'controller' => 'config',
              'method' => 'wapcustomTemplateList',
              'parent' => ['module' => 'admin', 'controller' => 'config',  'method' => 'diyview', 'level' => 2],
              'url' => 'config/wapcustomTemplateList',
              'is_menu' => 1,
              'is_dev' => 0,
              'sort' => 11,
              'desc' => '拼团设置',
              'module_picture' => '',
              'icon_class' => '',
              'is_control_auth' => 1,
            ],
            [
                'module_name' => '自定义模板设置',
                'controller' => 'config',
                'method' => 'wapCustomTemplateEdit',
                'parent' => ['module' => 'admin', 'controller' => 'config',  'method' => 'diyview', 'level' => 2],
                'url' => 'config/wapCustomTemplateEdit',
                'is_menu' => 1,
                'is_dev' => 0,
                'sort' => 11,
                'desc' => '自定义模板设置',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1,
            ],
            [
                'module_name' => '自定义模板开启',
                'controller' => 'config',
                'method' => 'setIsEnableWapCustomTemplate',
                'parent' => ['module' => 'admin', 'controller' => 'config',  'method' => 'diyview', 'level' => 2],
                'url' => 'config/setIsEnableWapCustomTemplate',
                'is_menu' => 1,
                'is_dev' => 0,
                'sort' => 11,
                'desc' => '自定义模板开启',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1,
            ],
        ];
        return $menu;

    }
}