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
namespace addons\NsWxtemplatemsg;

use addons\BaseConfig;
class Config extends BaseConfig
{
    /**
     * 菜单设置
     */
    public function menu(){
        $menu = [
            [
                'module_name' => '模板消息配置',
                'controller' => 'TempMsg',
                'method' => 'config',
                'parent' => [ 'module' => 'admin', 'controller' => 'config', 'method' => 'notifyindex', 'level' => 2 ],
                'url' => 'TempMsg/config',
                'is_menu' => 1,
                'is_dev' => 0,
                'sort' => 2,
                'desc' => '模板消息配置',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1,
            ]
        ];
        return $menu;
    }
}