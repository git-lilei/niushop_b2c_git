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
namespace addons\NsCardGoods;

use addons\NsCardGoods\data\service\CardGoods;
use addons\NsCardGoods\admin\controller\Goods;
use data\extend\upgrade\Unzip;

class NsCardGoodsAddon extends \addons\Addons
{
	
	public $info = array(
		'name' => 'NsCardGoods', // 插件名称标识
		'title' => '卡券商品', // 插件中文名
		'description' => '支持卡券类的账户秘钥销售', // 插件概述
		'status' => 1, // 状态 1启用 0禁用
		'author' => 'niushop', // 作者
		'version' => '1.0', // 版本号
		'has_addonslist' => 0, // 是否有下级插件 例如：第三方登录插件下有 qq登录，微信登录
		'content' => '', // 插件的详细介绍或使用方法
		'ico' => 'addons/NsCardGoods/ico.png'
	);
	
	
	/**
	 * 获取商品的基础化设置
	 * @param unknown $param
	 */
	public function getGoodsConfig($param = [])
	{
		$arr = [
			'id' => 4,
			'name' => $this->info['name'],
			'title' => $this->info['title'],
			'ico' => $this->info['ico'],
			'description' => $this->info['description'],
			'is_virtual' => 1
		];
		
		if (isset($param['type'])) {
			if ($param['type'] == 'all' || $param['type'] == $this->info['name']) {
				return $arr;
			}
		} elseif (isset($param['type_id']) && $param['type_id'] == $arr['id']) {
			return $arr;
		}
		return '';
	}
	
	/**
	 * 添加商品
	 * @param unknown $param
	 */
	public function addGoods($param = [])
	{
		if ($this->info['name'] != $param['type']) {
			return '';
		}
		//添加商品
		$goods = new Goods();
		return $goods->addGoods();
	}
	
	/**
	 * 编辑商品
	 */
	public function editGoods($param = [])
	{
		//修改商品
		if ($this->info['name'] != $param['type']) {
			return '';
		}
		
		//添加商品
		$goods = new Goods();
		return $goods->editGoods($param['goods_id']);
	}
	
	/**
	 * 添加商品后续操作
	 * @param unknown $param
	 */
	public function addGoodsSuccess($param = [])
	{
        if ($this->info['name'] != $param['goods_type']) {
            return '';
        }
	    $card_goods = new CardGoods();
	    $card_goods->addBatchCardStock($param);
		return true;
	}
	
	/**
	 * 修改商品后续操作
	 */
	public function editGoodsSuccess($param = [])
	{
        if ($this->info['name'] != $param['goods_type']) {
            return '';
        }
        $card_goods = new CardGoods();
        $card_goods->addBatchCardStock($param);
		return true;
	}
	
	/**
	 * 虚拟商品管理
	 */
	public function virtualGoodsManage($param = []){
	    if ($this->info['name'] != $param['type']) {
	        return '';
	    }
	    $goods = new Goods();
	    return $goods->virtualGoodsManage();
	}
	
	/**
	 * 删除商品成功
	 * @param unknown $param
	 */
	public function deleteGoodsSuccess($param)
	{
		return true;
	}
	
	/**
	 * 复制商品
	 * @param unknown $param
	 */
	public function copyGoodsSuccess($param)
	{
		return true;
	}
	
	/**
	 * 插件安装
	 * @see \addons\Addons::install()
	 */
	public function install()
	{
		return true;
	}
	
	/**
	 * 插件卸载
	 * @see \addons\Addons::uninstall()
	 */
	public function uninstall()
	{
		return true;
	}
	
}