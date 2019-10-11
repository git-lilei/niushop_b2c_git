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
namespace addons\NsGoods;

use addons\NsGoods\admin\controller\Goods;

class NsGoodsAddon extends \addons\Addons
{
	
	public $info = array(
		'name' => 'NsGoods', // 插件名称标识
		'title' => '普通商品', // 插件中文名
		'description' => '提供店铺线上服务商品的交易', // 插件概述
		'status' => 1, // 状态 1启用 0禁用
		'author' => 'niushop', // 作者
		'version' => '1.0', // 版本号
		'has_addonslist' => 0, // 是否有下级插件 例如：第三方登录插件下有 qq登录，微信登录
		'content' => '', // 插件的详细介绍或使用方法
		'ico' => 'addons/NsGoods/ico.png'
	);
	
	
	/**
	 * 获取商品的基础化设置
	 * @param unknown $param
	 */
	public function getGoodsConfig($param = [])
	{
		
		$arr = [
			'id' => 1,
			'name' => $this->info['name'],
			'title' => $this->info['title'],
			'ico' => $this->info['ico'],
			'description' => $this->info['description'],
			'is_virtual' => 0
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
	public function addGoods($param)
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
	 * @param unknown $param
	 */
	public function editGoods($param)
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
	public function addGoodsSuccess($param)
	{
		return true;
	}
	
	/**
	 * 修改商品后续操作
	 * @param unknown $param
	 */
	public function editGoodsSuccess($param)
	{
		return true;
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