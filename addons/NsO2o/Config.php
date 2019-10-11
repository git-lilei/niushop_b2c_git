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
namespace addons\NsO2o;

use addons\BaseConfig;
class Config extends BaseConfig
{
    /**
     * 菜单设置
     */
    public function menu(){
        $menu = [
            [
              'module_name' => '本地配送费用',
              'controller' => 'distribution',
              'method' => 'distributionConfig',
              'parent' => ['module' => 'admin', 'controller' => 'express',  'method' => 'expresscompany'],
              'url' => 'distribution/distributionConfig',
              'is_menu' => 0,
              'is_dev' => 0,
              'sort' => 1,
              'desc' => '配送费用',
              'module_picture' => '',
              'icon_class' => '',
              'is_control_auth' => 1,
            ],
            [
                'module_name' => '本地配送人员',
                'controller' => 'distribution',
                'method' => 'distributionUserList',
                'parent' => ['module' => 'admin', 'controller' => 'express',  'method' => 'expresscompany'],
                'url' => 'distribution/distributionUserList',
                'is_menu' => 0,
                'is_dev' => 0,
                'sort' => 1,
                'desc' => '本地配送人员',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1,
            ],
            [
                'module_name' => '本地配送地区',
                'controller' => 'distribution',
                'method' => 'distributionAreaList',
                'parent' => ['module' => 'admin', 'controller' => 'express',  'method' => 'expresscompany'],
                'url' => 'distribution/distributionAreaList',
                'is_menu' => 0,
                'is_dev' => 0,
                'sort' => 1,
                'desc' => '本地配送地区',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1,
            ],
            [
                'module_name' => '本地配送地区管理',
                'controller' => 'distribution',
                'method' => 'distributionareamanagement',
                'parent' => ['module' => 'admin', 'controller' => 'express',  'method' => 'expresscompany'],
                'url' => 'distribution/distributionareamanagement',
                'is_menu' => 0,
                'is_dev' => 0,
                'sort' => 9,
                'desc' => '本地配送地区管理',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1,
            ],
        ];
        return $menu;

    }
}