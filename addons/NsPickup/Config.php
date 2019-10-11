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
namespace addons\NsPickup;

use addons\BaseConfig;
class Config extends BaseConfig
{
    /**
     * 菜单设置
     */
    public function menu(){
        $menu = [
            [
              'module_name' => '门店管理',
              'controller' => 'shop',
              'method' => 'pickuppointlist',
              'parent' => ['module' => 'admin', 'controller' => 'express',  'method' => 'expresscompany'],
              'url' => 'shop/pickuppointlist',
              'is_menu' => 0,
              'is_dev' => 0,
              'sort' => 1,
              'desc' => '自提点管理',
              'module_picture' => '',
              'icon_class' => '',
              'is_control_auth' => 1,
            ],
            [
                'module_name' => '添加门店',
                'controller' => 'shop',
                'method' => 'addpickuppoint',
                'parent' => ['module' => 'admin', 'controller' => 'express',  'method' => 'expresscompany'],
                'url' => 'shop/addpickuppoint',
                'is_menu' => 0,
                'is_dev' => 0,
                'sort' => 1,
                'desc' => '添加自提点',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1,
            ],
            [
                'module_name' => '修改门店',
                'controller' => 'shop',
                'method' => 'updatepickuppoint',
                'parent' => ['module' => 'admin', 'controller' => 'express',  'method' => 'expresscompany'],
                'url' => 'shop/updatepickuppoint',
                'is_menu' => 0,
                'is_dev' => 0,
                'sort' => 1,
                'desc' => '修改门店',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1,
            ],
            [
            'module_name' => '自提运费',
            'controller' => 'shop',
            'method' => 'pickuppointfreight',
            'parent' => ['module' => 'admin', 'controller' => 'express',  'method' => 'expresscompany'],
            'url' => 'shop/pickuppointfreight',
            'is_menu' => 0,
            'is_dev' => 0,
            'sort' => 1,
            'desc' => '修改门店',
            'module_picture' => '',
            'icon_class' => '',
            'is_control_auth' => 1,
            ],
            [
            'module_name' => '门店审核人员',
            'controller' => 'shop',
            'method' => 'pickupAuditorList',
            'parent' => ['module' => 'admin', 'controller' => 'express',  'method' => 'expresscompany'],
            'url' => 'shop/pickupAuditorList',
            'is_menu' => 0,
            'is_dev' => 0,
            'sort' => 1,
            'desc' => '门店审核人员',
            'module_picture' => '',
            'icon_class' => '',
            'is_control_auth' => 1,
            ],
        ];
        return $menu;

    }
}