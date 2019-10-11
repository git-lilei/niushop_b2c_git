<?php
/**
 * GoodsPreference.php
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

use data\model\NsCouponGoodsModel;
use data\model\NsCouponModel;
use data\model\NsCouponTypeModel;
use data\model\NsGoodsLadderPreferentialModel;
use data\model\NsGoodsMemberDiscountModel;
use data\model\NsGoodsModel;
use data\model\NsGoodsSkuModel;
use data\model\NsMemberLevelModel;
use data\model\NsMemberModel;
use data\model\NsPointConfigModel;
use data\service\BaseService;
use data\service\Config;
use data\service\Goods;
use data\service\Promotion;

/**
 * 商品优惠价格操作类(运费，商品优惠)(没有考虑订单优惠活动例如满减送)
 */
class GoodsPreference extends BaseService
{
	/**
	 * ***************************************************************************************订单商品管理开始**************************************************
	 */
	/**
	 * 获取商品sku列表价格
	 */
	public function getGoodsSkuListPrice($goods_sku_list)
	{
		$price = 0;
		if (!empty($goods_sku_list)) {
			$goods_sku_list_array = explode(",", $goods_sku_list);
			foreach ($goods_sku_list_array as $k => $v) {
				$sku_data = explode(":", $v);
				$sku_id = $sku_data[0];
				$sku_count = $sku_data[1];
				$sku_price = $this->getGoodsSkuPrice($sku_id);
				$sku_price = $this->getGoodsLadderPreferentialPrice($sku_id, $sku_count, $sku_price);
				$price = $price + $sku_price * $sku_count;
			}
			return $price;
		} else {
			return $price;
		}
	}
	
	/**
	 * 根据商品sku获取预售总价格
	 */
	public function getGoodsPresell($goods_sku_list)
	{
		$presell_money = 0;
		if (!empty($goods_sku_list)) {
			$goods_sku_list_array = explode(",", $goods_sku_list);
			$goods_service = new Goods();
			foreach ($goods_sku_list_array as $k => $v) {
				$sku_data = explode(":", $v);
				$sku_id = $sku_data[0];
				$sku_count = $sku_data[1];
				$goods_id = $goods_service->getGoodsId($sku_id);
				$presell_price = $this->getGoodsPresellPrice($goods_id);
				$presell_money = $presell_money + $presell_price * $sku_count;
			}
			return $presell_money;
		} else {
			return $presell_money;
		}
	}
	
	/**
	 * 获取商品积分兑换价格
	 */
	public function getGoodsSkuListPointChangePrice($goods_sku_list)
	{
		$price = 0;
		if (!empty($goods_sku_list)) {
			$goods_sku_list_array = explode(",", $goods_sku_list);
			foreach ($goods_sku_list_array as $k => $v) {
				$sku_data = explode(":", $v);
				$sku_id = $sku_data[0];
				$sku_count = $sku_data[1];
				$sku_price = $this->getGoodsSkuPointChangePrice($sku_id);
				if ($sku_price != 0) {
					$sku_price = $this->getGoodsLadderPreferentialPrice($sku_id, $sku_count, $sku_price);
					$price = $price + $sku_price * $sku_count;
				}
			}
			return $price;
		} else {
			return $price;
		}
	}
	
	/**
	 * ***************************************************************************************订单商品管理开始**************************************************
	 */
	/**
	 * 获取组合商品sku列表价格
	 */
	public function getComboPackageGoodsSkuListPrice($goods_sku_list)
	{
		$price = 0;
		if (!empty($goods_sku_list)) {
			$goods_sku_list_array = explode(",", $goods_sku_list);
			foreach ($goods_sku_list_array as $k => $v) {
				$sku_data = explode(":", $v);
				$sku_id = $sku_data[0];
				$sku_count = $sku_data[1];
				$sku_price = $this->getComboPackageGoodsSkuPrice($sku_id);
				$price = $price + $sku_price * $sku_count;
			}
			return $price;
		} else {
			return $price;
		}
	}
	
	/**
	 * 获取商品sku列表购买后可得积分
	 */
	public function getGoodsSkuListGivePoint($goods_sku_list)
	{
		$point = 0;
		if (!empty($goods_sku_list)) {
			$goods = new Goods();
			$goods_sku_list_array = explode(",", $goods_sku_list);
			foreach ($goods_sku_list_array as $k => $v) {
				$sku_data = explode(":", $v);
				$sku_id = $sku_data[0];
				$sku_count = $sku_data[1];
				$goods_id = $goods->getGoodsId($sku_id);
				$give_point = $goods->getGoodsGivePoint($goods_id);
				$point += $give_point * $sku_count;
			}
			return $point;
		} else {
			return $point;
		}
	}
	
	public function getGoodsSkuListGivePointNew($goods_sku_list)
	{
		$point = 0;
		if (!empty($goods_sku_list)) {
			$goods = new Goods();
			$goods_sku_list_array = explode(",", $goods_sku_list);
			foreach ($goods_sku_list_array as $k => $v) {
				$sku_data = explode(":", $v);
				$sku_id = $sku_data[0];
				$num = $sku_data[1];
				$goods_id = $goods->getGoodsId($sku_id);
				$give_point = $goods->getGoodsGivePointNew($goods_id, $sku_id, $num);
				$point += $give_point * $num;
			}
		}
		return $point;
	}
	
	/**
	 * 获取商品sku列表使用优惠券详情
	 */
	public function getGoodsCouponPromoteDetail($coupon_id, $coupon_money, $goods_sku_list)
	{
		$promote_coupon_detail = array();
		// 获取商品总价
		$all_goods_money = $this->getGoodsSkuListPrice($goods_sku_list);
		// 获取优惠券详情
		$coupon = new NsCouponModel();
		$coupon_type_id = $coupon->getInfo([
			'coupon_id' => $coupon_id
		], 'coupon_type_id');
		$promote = new Promotion();
		$coupon_type_detail = $promote->getCouponTypeDetail($coupon_type_id['coupon_type_id']);
		// 拆分sku
		$goods_sku_list_array = explode(",", $goods_sku_list);
		
		if ($coupon_type_detail['range_type'] == 1) {
			
			// 优惠券全场使用
			$count_discount_moeny = 0;
			foreach ($goods_sku_list_array as $k => $v) {
				// 获取sku总价
				$sku_data = explode(':', $v);
				$goods_money = $this->getGoodsSkuListPrice($v);
				$sku_id = $sku_data[0];
				$discount_money = $k == (count($goods_sku_list_array) - 1) ? $coupon_money - $count_discount_moeny : round($coupon_money * $goods_money / $all_goods_money, 2);
				$promote_item = array(
					'sku_id' => $sku_id,
					'money' => $discount_money
				);
				$promote_coupon_detail[] = $promote_item;
				$count_discount_moeny = $discount_money + $count_discount_moeny;
			}
		} else {
			// 优惠券部分商品使用
			$coupon_goods_money = 0;
			$goods_list = $coupon_type_detail['goods_list'];
			
			$list = array(); // 整理后的商品数组
			$goods = new Goods();
			foreach ($goods_list as $k_goods => $v_goods) {
				foreach ($goods_sku_list_array as $k => $v) {
					$sku_data = explode(':', $v);
					$sku_id = $sku_data[0];
					$goods_id = $goods->getGoodsId($sku_id);
					if ($goods_id == $v_goods['goods_id']) {
						$goods_money = $this->getGoodsSkuListPrice($v);
						$coupon_goods_money += $goods_money;
						$list[] = $v;
					}
				}
			}
			if ($coupon_goods_money == 0) {
				$coupon_goods_money = $all_goods_money;
			}
			$count_discount_moeny = 0;
			foreach ($list as $k => $v) {
				// 获取sku总价
				$sku_data = explode(':', $v);
				$goods_money = $this->getGoodsSkuListPrice($v);
				$sku_id = $sku_data[0];
				$discount_money = $k == (count($goods_sku_list_array) - 1) ? $coupon_money - $count_discount_moeny : round($coupon_money * $goods_money / $coupon_goods_money, 2);
				
				$promote_item = array(
					'sku_id' => $sku_id,
					'money' => $discount_money
				);
				$promote_coupon_detail[] = $promote_item;
				$count_discount_moeny = $discount_money + $count_discount_moeny;
			}
		}
		return $promote_coupon_detail;
	}
	
	/**
	 * 获取商品对应sku的价格
	 */
	public function getGoodsSkuPrice($sku_id)
	{
		$goods_sku = new NsGoodsSkuModel();
		$goods_sku_info = $goods_sku->getInfo([
			'sku_id' => $sku_id
		], 'goods_id,price,promote_price');
		if (!empty($this->uid)) {
			$member_price = $this->getGoodsSkuMemberPrice($sku_id, $this->uid);
			// 处理会员价
			$goods = new Goods();
			$member_price = $goods->handleMemberPrice($goods_sku_info['goods_id'], $member_price);
			if ($member_price < $goods_sku_info['promote_price']) {
				return $member_price;
			} else {
				return $goods_sku_info['promote_price'];
			}
		} else {
			return $goods_sku_info['promote_price'];
		}
	}
	
	/**
	 * 获取商品对应sku的价格
	 */
	public function getGoodsSkuPointChangePrice($sku_id)
	{
		$goods_sku = new NsGoodsSkuModel();
		$goods_sku_info = $goods_sku->getInfo([
			'sku_id' => $sku_id
		], 'goods_id,price,promote_price');
		$goods_model = new NsGoodsModel();
		$goods_info = $goods_model->getInfo([ 'goods_id' => $goods_sku_info['goods_id'] ], 'point_exchange_type');
		if ($goods_info['point_exchange_type'] == 0 || $goods_info['point_exchange_type'] == 1) {
			if (!empty($this->uid)) {
				$member_price = $this->getGoodsSkuMemberPrice($sku_id, $this->uid);
				if ($member_price < $goods_sku_info['promote_price']) {
					return $member_price;
				} else {
					return $goods_sku_info['promote_price'];
				}
			} else {
				return $goods_sku_info['promote_price'];
			}
		} else {
			return 0;
		}
		
	}
	
	/**
	 * 获取组合商品对应sku的价格
	 */
	public function getComboPackageGoodsSkuPrice($sku_id)
	{
		$goods_sku = new NsGoodsSkuModel();
		$goods_sku_info = $goods_sku->getInfo([
			'sku_id' => $sku_id
		], 'goods_id,price');
		if (!empty($this->uid)) {
			$member_price = $this->getGoodsSkuMemberPrice($sku_id, $this->uid);
			if ($member_price < $goods_sku_info['price']) {
				return $member_price;
			} else {
				return $goods_sku_info['price'];
			}
		} else {
			return $goods_sku_info['price'];
		}
	}
	
	/**
	 * 获取商品sku的积分兑换值
	 */
	public function getGoodsSkuExchangePoint($sku_id)
	{
		$goods_sku = new NsGoodsSkuModel();
		$goods_sku_info = $goods_sku->getInfo([
			'sku_id' => $sku_id
		], 'goods_id');
		$goods = new Goods();
		$point = $goods->getGoodsPointExchange($goods_sku_info['goods_id']);
		return $point;
	}
	
	/**
	 * 获取商品列表总积分
	 */
	public function getGoodsListExchangePoint($goods_sku_list)
	{
		$goods_sku_list_array = explode(",", $goods_sku_list);
		$point = 0;
		foreach ($goods_sku_list_array as $k => $v) {
			// 获取sku总价
			$sku_data = explode(':', $v);
			$sku_id = $sku_data[0];
			$sku_point = $this->getGoodsSkuExchangePoint($sku_id);
			$point += $sku_point * $sku_data[1];
		}
		return $point;
	}
	
	/**
	 * 获取积分对应金额
	 */
	public function getPointMoney($point, $shop_id)
	{
		if ($point == 0) {
			return 0;
		}
		$point_config = new NsPointConfigModel();
		$config = $point_config->getInfo([ 'shop_id' => $shop_id ], 'is_open, convert_rate');
		if (!empty($config)) {
			if ($config['is_open'] == 1) {
				$money = $point * $config['convert_rate'];
			} else {
				$money = NO_OPEN_POINT_PAY;
			}
		} else {
			$money = NO_OPEN_POINT_PAY;
		}
		return $money;
	}
	
	/**
	 * 获取商品当前单品优惠活动
	 */
	public function getGoodsPromote($goods_id)
	{
		$goods = new NsGoodsModel();
		$promote_info = $goods->getInfo([
			'goods_id' => $goods_id
		], 'promotion_type,promote_id');
		if ($promote_info['promotion_type'] == 0) {
			// 无促销活动
			return '';
		} elseif ($promote_info['promotion_type'] == 1) {
			// 团购(注意查询活动时间)
			return '团购';
		} elseif ($promote_info['promotion_type'] == 2) {
			// 限时折扣(注意查询活动时间)
			return '限时折扣';
		}
	}
	
	/**
	 * 获取商品sku列表的商品列表形式
	 */
	public function getGoodsSkuListGoods($goods_sku_list)
	{
		$array = array();
		if (!empty($goods_sku_list)) {
			$goods = new Goods();
			$goods_sku_list_array = explode(',', $goods_sku_list);
			foreach ($goods_sku_list_array as $k => $v) {
				$sku_item = explode(":", $v);
				$goods_id = $goods->getGoodsId($sku_item[0]);
				$array[] = array(
					$goods_id,
					$sku_item[0],
					$sku_item[1]
				);
			}
		}
		return $array;
	}
	
	/**
	 * 获取商品列表所属店铺(只针对单店)
	 */
	public function getGoodsSkuListShop($goods_sku_list)
	{
		if (!empty($goods_sku_list)) {
			$goods_sku_list_array = explode(',', $goods_sku_list);
			$v = $goods_sku_list_array[0];
			$sku_item = explode(":", $v);
			// 获取商品goods_id
			$goods = new Goods();
			$goods_id = $goods->getGoodsId($sku_item[0]);
			$shop_id = $goods->getGoodsShopid($goods_id);
			return $shop_id;
			// $array[] = array($goods_id, $sku_item[0], $sku_item[1]);
		} else {
			return 0;
		}
	}
	
	/**
	 * 获取用户未使用的，未过期的优惠券，根据优惠券类型进行分组，排除重复的
	 */
	public function getDoNotUserMemberCouponList()
	{
		$coupon_model = new NsCouponModel();
		$condition['end_time'] = array(
			'GT',
			time()
		);
		$condition['state'] = 1;
		$condition['uid'] = $this->uid;
		$condition['shop_id'] = $this->instance_id;
		$member_coupon_list = $coupon_model->getQuery($condition, "*", "", "coupon_type_id");
		return $member_coupon_list;
	}
	
	/**
	 * 获取当前会员可用优惠券
	 */
	public function getMemberCouponList($goods_sku_list)
	{
		// 1.获取当前会员所有优惠券
		$coupon_list = array();
		$goods_sku_list_array = $this->getGoodsSkuListGoods($goods_sku_list);
		$member_coupon_list = $this->getDoNotUserMemberCouponList();
		if (!empty($member_coupon_list)) {
			$coupon_type_model = new NsCouponTypeModel();
			foreach ($member_coupon_list as $k => $v) {
				$type_info = $coupon_type_model->getInfo([
					'coupon_type_id' => $v['coupon_type_id']
				], 'coupon_name,at_least');
				$member_coupon_list[ $k ]['coupon_name'] = $type_info['coupon_name'];
				$member_coupon_list[ $k ]['at_least'] = $type_info['at_least'];
			}
		}
		// 2.获取当前优惠券是否可用
		if (!empty($member_coupon_list)) {
			foreach ($member_coupon_list as $k => $coupon) {
				// 查询优惠券类型的情况
				$coupon_type = new NsCouponTypeModel();
				$type_info = $coupon_type->getInfo([
					'coupon_type_id' => $coupon['coupon_type_id']
				], 'range_type,at_least');
				if ($type_info['range_type'] == 1) {
					// 全场商品使用的优惠券
					$price = $this->getGoodsSkuListPrice($goods_sku_list);
					if ($type_info['at_least'] <= $price) {
						$coupon_list[] = $coupon;
					}
				} else {
					// 部分商品使用的优惠券
					$coupon_goods = new NsCouponGoodsModel();
					$coupon_goods_list = $coupon_goods->getQuery([
						'coupon_type_id' => $coupon['coupon_type_id']
					]);
					$new_sku_list = '';
					foreach ($coupon_goods_list as $k_coupon => $goods) {
						foreach ($goods_sku_list_array as $k_goods_sku => $v_goods_sku) {
							if ($goods['goods_id'] == $v_goods_sku[0]) {
								$new_sku_list = $new_sku_list . $v_goods_sku[1] . ':' . $v_goods_sku[2] . ',';
							}
						}
					}
					if (!empty($new_sku_list)) {
						$new_sku_list = substr($new_sku_list, 0, strlen($new_sku_list) - 1);
					}
					$price = $this->getGoodsSkuListPrice($new_sku_list);
					if (!empty($new_sku_list) && $type_info['at_least'] <= $price) {
						$coupon_list[] = $coupon;
					}
				}
			}
		}
		//优惠券价格倒序
		array_multisort($coupon_list, SORT_DESC);
		return $coupon_list;
	}
	
	/**
	 * 查询会员等级折扣
	 */
	public function getMemberLevelDiscount($uid)
	{
		// 查询会员等级
		$member = new NsMemberModel();
		$member_info = $member->getInfo([
			'uid' => $uid
		], 'member_level');
		if (!empty($member_info)) {
			$member_level = new NsMemberLevelModel();
			$level_info = $member_level->getInfo([
				'level_id' => $member_info['member_level']
			], 'goods_discount');
			if (!empty($level_info)) {
				return $level_info['goods_discount'];
			} else {
				return 1;
			}
		} else {
			return 1;
		}
	}
	
	/**
	 * 获取商品会员价
	 */
	public function getGoodsSkuMemberPrice($goods_sku_id, $uid)
	{
		// 查询sku相关信息
		$goods_sku = new NsGoodsSkuModel();
		$sku_info = $goods_sku->getInfo([
			'sku_id' => $goods_sku_id
		], 'price, goods_id');
		
		$goods_member_discount = $this->getGoodsMemberDiscount($uid, $sku_info["goods_id"]);
		// 判断商品是否有设置会员折扣率 如果没有则使用店铺设置会员折扣率
		if (!empty($goods_member_discount)) {
			$member_level_discount = $goods_member_discount;
		} else {
			$member_level_discount = $this->getMemberLevelDiscount($uid);
		}
		return $sku_info['price'] * $member_level_discount;
	}
	
	/**
	 * 获取自提点运费
	 */
	public function getPickupMoney($goods_sku_list_price)
	{
		$config_service = new Config();
		$config_info = $config_service->getConfig($this->instance_id, 'PICKUPPOINT_FREIGHT');
		if (!empty($config_info)) {
			$pick_up_info = json_decode($config_info['value'], true);
			if ($pick_up_info['is_enable'] == 1 && $goods_sku_list_price <= $pick_up_info['manjian_freight']) {
				$pick_money = $pick_up_info['pickup_freight'];
			} else {
				$pick_money = 0;
			}
		} else {
			$pick_money = 0;
		}
		return $pick_money;
	}
	
	/**
	 * 根据获取商品阶梯优惠后价格
	 */
	public function getGoodsLadderPreferentialPrice($skuid, $num, $goods_price)
	{
		$ns_goods_sku = new NsGoodsSkuModel();
		$ns_goods_ladder_preferential = new NsGoodsLadderPreferentialModel();
		$sku_detail = $ns_goods_sku->getInfo([
			"sku_id" => $skuid
		], "goods_id"); // 通过skuid获取到goods_id
		// 获取阶梯优惠信息
		if (!empty($sku_detail)) {
			$condition = array(
				"goods_id" => $sku_detail['goods_id'],
				"quantity" => array(
					"ELT",
					$num
				)
			);
			$ladder_preferential_detail = $ns_goods_ladder_preferential->pageQuery(1, 1, $condition, "quantity desc", "*");
			if ($ladder_preferential_detail["total_count"] > 0) {
				$goods_price = $goods_price - $ladder_preferential_detail["data"][0]["price"];
			}
		}
		
		$goods_price = $goods_price < 0 ? 0 : $goods_price;
		return $goods_price;
	}
	
	/**
	 * 获取预售商品单价
	 */
	public function getGoodsPresellPrice($goods_id)
	{
		$goods_model = new NsGoodsModel();
		$presell_price = $goods_model->getInfo([ 'goods_id' => $goods_id ], 'presell_price');
		return $presell_price['presell_price'];
	}
	
	/**
	 * 获取商品会员折扣率
	 */
	public function getGoodsMemberDiscount($uid, $goods_id)
	{
		// 查询会员等级
		$member = new NsMemberModel();
		$member_info = $member->getInfo([
			'uid' => $uid
		], 'member_level');
		$ns_goods_member_discount = new NsGoodsMemberDiscountModel();
		$goods_member_discount_detail = $ns_goods_member_discount->getInfo([ "level_id" => $member_info["member_level"], "goods_id" => $goods_id ], "discount");
		if (!empty($goods_member_discount_detail["discount"])) {
			return $member_level_discount = $goods_member_discount_detail["discount"] / 100;
		} else {
			return 0;
		}
	}
	
	/**
	 * 查询订单最大可使用积分数
	 */
	public function getMaxUsePoint($goods_sku_list)
	{
		// 获取积分配置
		$promotion = new Promotion();
		$point_config = $promotion->getPointConfig();
		// 最大可使用积分
		$max_use_point = 0;
		if ($point_config["is_open"] == 1 && $point_config["convert_rate"] > 0) {
			$goods_sku_list_array = explode(",", $goods_sku_list);
			foreach ($goods_sku_list_array as $k => $v) {
				$sku_data = explode(":", $v);
				$useble_point = $this->getGoodsMaxUsePoint($sku_data[0], $sku_data[1], $point_config["convert_rate"]);
				$max_use_point += $useble_point;
			}
		}
		return $max_use_point;
	}
	
	public function getGoodsMaxUsePoint($sku_id, $num, $convert_rate)
	{
		$goods_sku = new NsGoodsSkuModel();
		$goods_sku_info = $goods_sku->getInfo([
			'sku_id' => $sku_id
		], 'goods_id');
		$ns_goods = new NsGoodsModel();
		$goods_info = $ns_goods->getInfo([ "goods_id" => $goods_sku_info["goods_id"] ], "max_use_point");
		if ($goods_info["max_use_point"] > 0) {
			$sku_price = $this->getGoodsSkuPrice($sku_id);
			$sku_price = $this->getGoodsLadderPreferentialPrice($sku_id, $num, $sku_price);
			$useble_point = ceil($sku_price / $convert_rate);
			if ($useble_point > $goods_info["max_use_point"]) {
				$useble_point = $goods_info["max_use_point"];
			}
			return $useble_point * $num;
		} else {
			return 0;
		}
	}
	/**
	 * ***************************************************************************************订单商品管理结束**************************************************
	 */
	
	
	/*************************************************************************订单商品（new）  start ***************************************************/
	
	
	/**
	 * 根据获取商品阶梯优惠后价格
	 */
	public function getOrderGoodsLadderPreferentialPrice($data, $goods_price)
	{
		$ns_goods_ladder_preferential = new NsGoodsLadderPreferentialModel();
		$sku_info = $data["goods_sku_info"];
		$num = $data["num"];
		// 获取阶梯优惠信息
		if (!empty($sku_info)) {
			$condition = array(
				"goods_id" => $sku_info['goods_id'],
				"quantity" => array(
					"ELT",
					$num
				)
			);
			$ladder_preferential_detail = $ns_goods_ladder_preferential->pageQuery(1, 1, $condition, "quantity desc", "*");
			if ($ladder_preferential_detail["total_count"] > 0) {
				$goods_price = $goods_price - $ladder_preferential_detail["data"][0]["price"];
			}
		}
		
		$goods_price = $goods_price < 0 ? 0 : $goods_price;
		return $goods_price;
	}
	
	/*************************************************************************订单商品（new）  end ***************************************************/
}