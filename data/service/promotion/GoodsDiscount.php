<?php
/**
 * GoodsDiscount.php
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
 * @date : 2015.1.17
 * @version : v1.0.0.0
 */

namespace data\service\promotion;

use data\model\AlbumPictureModel;
use data\model\NsPromotionDiscountGoodsModel;
use data\model\NsPromotionDiscountGoodsViewModel;
use data\model\NsPromotionDiscountModel;
use data\model\NsShopModel;
use data\service\BaseService;

/**
 * 商品显示折扣活动
 */
class GoodsDiscount extends BaseService
{
	
	/**
	 * 获取 一个商品的 限时折扣信息
	 */
	public function getDiscountByGoodsId($goods_id)
	{
		$discount_goods = new NsPromotionDiscountGoodsModel();
		$discount = $discount_goods->getInfo([
			'goods_id' => $goods_id,
			'status' => 1
		], 'discount');
		if (empty($discount)) {
			return -1;
		} else {
			return $discount['discount'];
		}
	}
	
	/**
	 * 查询商品在某一时间段是否有限时折扣活动
	 */
	public function getGoodsIsDiscount($goods_id, $start_time, $end_time)
	{
		$discount_goods = new NsPromotionDiscountGoodsModel();
		$condition_1 = array(
			'start_time' => array(
				'ELT',
				$end_time
			),
			'end_time' => array(
				'EGT',
				$end_time
			),
			'status' => array(
				'NEQ',
				3
			),
			'goods_id' => $goods_id
		);
		$condition_2 = array(
			'start_time' => array(
				'ELT',
				$start_time
			),
			'end_time' => array(
				'EGT',
				$start_time
			),
			'status' => array(
				'NEQ',
				3
			),
			'goods_id' => $goods_id
		);
		$condition_3 = array(
			'start_time' => array(
				'EGT',
				$start_time
			),
			'end_time' => array(
				'ELT',
				$end_time
			),
			'status' => array(
				'NEQ',
				3
			),
			'goods_id' => $goods_id
		);
		$count_1 = $discount_goods->where($condition_1)->count();
		$count_2 = $discount_goods->where($condition_2)->count();
		$count_3 = $discount_goods->where($condition_3)->count();
		$count = $count_1 + $count_2 + $count_3;
		return $count;
	}
	
	/**
	 * 查询限时折扣的商品
	 */
	public function getDiscountGoodsList($page_index = 1, $page_size = 0, $condition = array(), $order = '')
	{
		$discount_goods = new NsPromotionDiscountGoodsViewModel();
		$goods_list = $discount_goods->getViewList($page_index, $page_size, $condition, $order);
		if (!empty($goods_list['data'])) {
			foreach ($goods_list['data'] as $k => $v) {
				if ($v['point_exchange_type'] == 0 || $v['point_exchange_type'] == 2) {
					$goods_list['data'][ $k ]['display_price'] = '￥' . $v["promotion_price"];
				} else {
					if ($v['point_exchange_type'] == 1 && $v["promotion_price"] > 0) {
						$goods_list['data'][ $k ]['display_price'] = '￥' . $v["promotion_price"] . '+' . $v["point_exchange"] . '积分';
					} else {
						$goods_list['data'][ $k ]['display_price'] = $v["point_exchange"] . '积分';
					}
				}
			}
		}
		return $goods_list;
	}
}