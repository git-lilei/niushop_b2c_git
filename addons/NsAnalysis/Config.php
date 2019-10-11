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
namespace addons\NsAnalysis;

use addons\BaseConfig;
class Config extends BaseConfig
{
    /**
     * 菜单设置
     */
    public function menu(){
        $menu = [
            [
              'module_name' => '商品分析',
              'controller' => 'account',
              'method' => 'shopgoodssaleslist',
              'parent' => ['module' => 'admin', 'controller' => 'account', 'method' => 'shopsalesaccount', 'level' => 1],
              'url' => 'account/payaliconfig',
              'is_menu' => 1,
              'is_dev' => 0,
              'sort' => 2,
              'desc' => '商品分析',
              'module_picture' => '',
              'icon_class' => '',
              'is_control_auth' => 1,
            ],
            [
                'module_name' => '会员分析',
                'controller' => 'account',
                'method' => 'memberAnalysis',
                'parent' => ['module' => 'admin', 'controller' => 'account', 'method' => 'shopsalesaccount', 'level' => 1],
                'url' => 'account/memberAnalysis',
                'is_menu' => 1,
                'is_dev' => 0,
                'sort' => 3,
                'desc' => '会员分析',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1,
            ],
            [
                'module_name' => '交易分析',
                'controller' => 'account',
                'method' => 'transactionAnalysis',
                'parent' => ['module' => 'admin', 'controller' => 'account',  'method' => 'shopsalesaccount', 'level' => 1],
                'url' => 'account/transactionAnalysis',
                'is_menu' => 1,
                'is_dev' => 0,
                'sort' => 4,
                'desc' => '交易分析',
                'module_picture' => '',
                'icon_class' => '',
                'is_control_auth' => 1,
            ]
        ];
        return $menu;

    }
}