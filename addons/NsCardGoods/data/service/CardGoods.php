<?php
/**
 *
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 山西牛酷信息科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: http://www.niushop.com.cn
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 * @author : niuteam
 * @date : 2015.4.24
 * @version : v1.0.0.0
 */

namespace addons\NsCardGoods\data\service;


use data\model\NsGoodsModel;
use data\model\NsGoodsSkuModel;
use data\model\NsVirtualGoodsModel;
use data\service\BaseService;


/**
 * 虚拟商品服务层
 */
class CardGoods extends BaseService
{
	/**
	 * @param $card_list
	 */
	public function addBatchCardStock($data, $goods_type = 4)
	{
		
		$virtual_data = array(
			'virtual_code' => '',
			'virtual_goods_name' => $data['goods_name'],
			'money' => $data['price'],
			'confine_use_number' => 1,
			'use_status' => 0,
			'shop_id' => $this->instance_id,
			'goods_id' => $data['goods_id'],
			'sku_id' => isset($data['sku_id']) ? $data['sku_id'] : 0,
			'goods_type' => $goods_type,
			'create_time' => time(),
		);
		
		$goods_sku_model = new NsGoodsSkuModel();
		$virtual_goods = new NsVirtualGoodsModel();
		if (!empty($data['skuArray'])) {
			
			$sku_list = $goods_sku_model->getQuery([ 'goods_id' => $data['goods_id'] ]);
			
			foreach ($sku_list as $key => $item) {
				
				$virtual_data['virtual_goods_name'] = $data['goods_name'] . ' ' . $item['sku_name'];
				$card_arr = explode(',', $data['skuArray'][ $key ]['cards']);
				foreach ($card_arr as $card_item) {
					
					if (empty($card_item)) continue;
					
					//已经存在的跳过
					$info = $virtual_goods->getInfo([ 'sku_id' => $item['sku_id'], 'remark' => $card_item ], 'virtual_goods_id');
					if (!empty($info)) continue;
					
					$virtual_data['sku_id'] = $item['sku_id'];
					$virtual_data['remark'] = $card_item;
					$virtual_data['use_status'] = -2;
					$virtual_goods_save = new NsVirtualGoodsModel();
					$res = $virtual_goods_save->save($virtual_data);
				}
				
				//更新sku库存
				$stock = $virtual_goods->getCount([ 'sku_id' => $item['sku_id'], 'buyer_id' => 0 ]);
				
				$goods_sku_save = new NsGoodsSkuModel();
				$goods_sku_save->save([ 'stock' => $stock ], [ 'sku_id' => $item['sku_id'] ]);
			}
		} else {
			
			$card_arr = explode(',', $data['cards']);
			
			foreach ($card_arr as $item) {
				
				if (empty($item)) continue;
				$virtual_goods = new NsVirtualGoodsModel();
				
				if (empty($virtual_data['sku_id'])) {
					$virtual_data['sku_id'] = $goods_sku_model->getInfo([ 'goods_id' => $virtual_data['goods_id'] ], 'sku_id')['sku_id'];
				}
				
				//已经存在的跳过
				$info = $virtual_goods->getInfo([ 'sku_id' => $virtual_data['sku_id'], 'remark' => $item ], 'virtual_goods_id');
				
				if (!empty($info)) continue;
				
				$virtual_data['remark'] = $item;
				$virtual_goods_save = new NsVirtualGoodsModel();
				$virtual_data['use_status'] = -2;
				$res = $virtual_goods_save->save($virtual_data);
			}
			
			//更新sku库存
			$stock = $virtual_goods->getCount([ 'sku_id' => $virtual_data['sku_id'], 'buyer_id' => 0 ]);
			
			$goods_sku_model->save([ 'stock' => $stock ], [ 'sku_id' => $virtual_data['sku_id'] ]);
		}
		
		//更新商品总库存
		$goods_model = new NsGoodsModel();
		$stock = $virtual_goods->getCount([ 'goods_id' => $data['goods_id'], 'buyer_id' => 0 ]);
		$goods_model->save([ 'stock' => $stock ], [ 'goods_id' => $data['goods_id'] ]);
		return 1;
	}
	
	/**
	 * @param $goods_id
	 */
	public function getVirtualGoods($goods_id)
	{
		
		$goods_sku = new NsGoodsSkuModel();
		$sku_list = $goods_sku->getQuery([ 'goods_id' => $goods_id ], 'sku_id');
		$virtual_goods = new NsVirtualGoodsModel();
		foreach ($sku_list as $item) {
			//获取还未分配给会员的
			$item['virtual_goods'] = $virtual_goods->getQuery([ 'sku_id' => $item['sku_id'], 'buyer_id' => 0 ], 'remark');
		}
		return $sku_list;
	}
}