<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 山西牛酷信息科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: http://www.niushop.com.cn
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 * @author : niuteam
 * @date : 2015.1.17
 * @version : v1.0.0.0
 */

namespace addons\NsGroupBuy\api\controller;

use app\api\controller\BaseApi;
use addons\NsGroupBuy\data\service\GroupBuy as GroupBuyService;

/**
 * 团购接口
 */
class GroupBuy extends BaseApi
{
	
	/**
	 * 团购商品列表
	 */
	public function goodsList()
	{
		$title = "团购专区商品列表";
		$group_buy_service = new GroupBuyService();
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$condition = array(
			"state" => 1,
			"npgb.start_time" => array(
				"<",
				time()
			),
			"npgb.end_time" => array(
				">",
				time()
			)
		);
		$field = 'ng.goods_id,ng.promotion_price,ng.goods_name,ng.picture,npgb.group_id,npgb.group_name,npgb.shop_id,npgb.goods_id,npgb.start_time,npgb.end_time,npgb.max_num,npgb.min_num,npgb.status';
		$group_goods_list = $group_buy_service->getPromotionGroupBuyGoodsList($page_index, $page_size, $condition, 'npgb.group_id desc', $field);
		return $this->outMessage($title, $group_goods_list);
	}
}