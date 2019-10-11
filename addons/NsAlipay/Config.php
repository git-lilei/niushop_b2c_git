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
namespace addons\NsAlipay;

use addons\BaseConfig;
class Config extends BaseConfig
{
    /**
     * 菜单设置
     */
    public function menu(){
        $menu = [
            [
              'module_name' => '支付宝支付',
              'controller' => 'Config',
              'method' => 'payaliconfig',
              'parent' => ['module' => 'admin', 'controller' => 'Config',  'method' => 'shopset'],
              'url' => 'Config/payaliconfig',
              'is_menu' => 0,
              'is_dev' => 0,
              'sort' => 9,
              'desc' => '支付宝支付配置',
              'module_picture' => '',
              'icon_class' => '',
              'is_control_auth' => 1,
            ]
        ];
        return $menu;

    }
}