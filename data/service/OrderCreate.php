<?php
/**
 * OrderCreate.php
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

namespace data\service;

use addons\NsO2o\data\model\NsO2oDistributionConfigModel;
use data\model\AlbumPictureModel;
use data\model\ConfigModel;
use data\model\NsCouponGoodsModel;
use data\model\NsCouponModel;
use data\model\NsCouponTypeModel;
use data\model\NsGoodsMemberDiscountModel;
use data\model\NsGoodsModel;
use data\model\NsGoodsSkuModel;
use data\model\NsMemberModel;
use data\model\NsOrderGoodsModel;
use data\model\NsOrderGoodsPromotionDetailsModel;
use data\model\NsOrderModel;
use data\model\NsOrderPickupModel;
use data\model\NsOrderPromotionDetailsModel;
use data\model\NsPickupPointModel;
use data\model\NsPromotionFullMailModel;
use data\model\NsPromotionGiftGoodsModel;
use data\model\NsPromotionMansongModel;
use data\model\NsPromotionMansongRuleModel;
use data\model\UserModel as UserModel;
use data\service\GoodsCalculate\GoodsCalculate;
use data\service\Member\MemberAccount;
use data\service\Member\MemberCoupon;
use data\service\Order\Order as OrderService;
use data\service\promotion\GoodsExpress;
use data\service\promotion\GoodsMansong;
use data\service\promotion\GoodsPreference;
use think\Cache;

/**
 * 订单创建方法
 */
class OrderCreate extends OrderService
{
	
	/**
	 * 订单号生成
	 */
	public function createOrderNo($shop_id)
	{
		$time_str = date('YmdHi');
		$num = 0;
		$max_no = Cache::get($shop_id . "_" . $time_str);
		if (!isset($max_no) || empty($max_no)) {
			$max_no = 1;
		} else {
			$max_no = $max_no + 1;
		}
		$order_no = $time_str . sprintf("%04d", $max_no);
		Cache::set($shop_id . "_" . $time_str, $max_no);
		return $order_no;
	}
	
	/**
	 * 创建订单支付编号
	 */
	public function createOutTradeNo()
	{
		$pay_no = new UnifyPay();
		return $pay_no->createOutTradeNo();
	}
	
	/**
	 * 订单创建
	 */
	public function orderCreate($data)
	{
		//调用订单创建
		$order_result = hook("orderCreate", $data);
		$order_result = arrayFilter($order_result);
		if (!empty($order_result[0])) {
			return $order_result[0];
		} else {
			if ($data["order_type"] > 1) {
				return error([]);
			}
		}
		
		//订单计算
		$data = $this->orderCalculate($data);
		//判断订单合法性
		if (empty($data)) {
			return error();
		}
		
		if (!empty($data["code"]) && $data["code"] <= 0) {
			return $data;
		}
		//物流判断
		if ($data["shipping_data"]["code"] <= 0) {
			return $data["shipping_data"];
		}
        
		if ($data["is_virtual"] == 0) {
			if ($data["pay_type"] == 4) {
				// 如果是货到付款 判断当前地址是否符合货到付款的地址
				$address = new Address();
				$result = $address->getDistributionAreaIsUser(0, $data["address"]["province"], $data["address"]["city"], $data["address"]["district"]);
				if (!$result) {
					return error([], ORDER_CASH_DELIVERY);
				}
			}
		}
		
		$this->order->startTrans();
		
		try {
			
			$order_model = new NsOrderModel();
			
			//订单编号
			$order_no = $this->createOrderNo($data["shop_id"]);
			$out_trade_no = $this->createOutTradeNo();
			$order_data = array(
				'order_type' => $data['order_type'],
				'order_no' => $order_no,
				'out_trade_no' => $out_trade_no,
				'payment_type' => $data['pay_type'],
				'shipping_type' => $data['shipping_info']['shipping_type'],
				'order_from' => $data["order_from"],
				'buyer_id' => $data["buyer_id"],
				'user_name' => $data['user_name'],
				'buyer_ip' => $data['buyer_ip'],
				'buyer_message' => $data['buyer_message'],//卖家备注
				'buyer_invoice' => $data['buyer_invoice'],//发票
				'shipping_time' => $data['shipping_info']['shipping_time'], // '买家要求配送时间',
				'receiver_mobile' => $data['address']['mobile'], // '收货人的手机号码',
				'receiver_province' => $data['address']['province'], // '收货人所在省',
				'receiver_city' => $data['address']['city'], // '收货人所在城市',
				'receiver_district' => $data['address']['district'], // '收货人所在街道',
				'receiver_address' => $data['address']['address_info'], // '收货人详细地址',
				'receiver_zip' => $data['address']['zip_code'], // '收货人邮编',
				'receiver_name' => $data['address']['consigner'], // '收货人姓名',
				'shop_id' => $data["shop_id"], // '卖家店铺id',
				'shop_name' => $data["shop_name"], // varchar(100) NOT NULL DEFAULT '' COMMENT '卖家店铺名称',
				'goods_money' => $data["goods_money"], // decimal(19, 2) NOT NULL COMMENT '商品总价',
				'tax_money' => $data["tax_money"], // 税费
				'order_money' => $data["order_money"], // decimal(10, 2) NOT NULL COMMENT '订单总价',
				'point' => $data["offset_money_array"]["point"]["num"], // int(11) NOT NULL COMMENT '订单消耗积分',
				'point_money' => $data["offset_money_array"]["point"]["offset_money"], // decimal(10, 2) NOT NULL COMMENT '订单消耗积分抵多少钱',
				'coupon_money' => $data["coupon_money"], // _money decimal(10, 2) NOT NULL COMMENT '订单代金券支付金额',
				'coupon_id' => $data["coupon_id"], // int(11) NOT NULL COMMENT '订单代金券id',
				'user_money' => $data["offset_money_array"]["user_money"]["offset_money"], // decimal(10, 2) NOT NULL COMMENT '订单预存款支付金额',
				'promotion_money' => $data["promotion_money"], // decimal(10, 2) NOT NULL COMMENT '订单优惠活动金额',
				'shipping_money' => $data["shipping_money"], // decimal(10, 2) NOT NULL COMMENT '订单运费',
				'pay_money' => $data["pay_money"], // decimal(10, 2) NOT NULL COMMENT '订单实付金额',
				'refund_money' => 0, // decimal(10, 2) NOT NULL COMMENT '订单退款金额',
				'give_point' => $data["give_point"], // int(11) NOT NULL COMMENT '订单赠送积分',
				'order_status' => 0, // tinyint(4) NOT NULL COMMENT '订单状态',
				'pay_status' => 0, // tinyint(4) NOT NULL COMMENT '订单付款状态',
				'shipping_status' => 0, // tinyint(4) NOT NULL COMMENT '订单配送状态',
				'review_status' => 0, // tinyint(4) NOT NULL COMMENT '订单评价状态',
				'feedback_status' => 0, // tinyint(4) NOT NULL COMMENT '订单维权状态',
				'user_platform_money' => $data["offset_money_array"]["platform_money"]["offset_money"], // 平台余额支付
				'coin_money' => $data['coin'],
				'create_time' => time(),
				"give_point_type" => $data["give_point_type"],
				'shipping_company_id' => $data['shipping_info']['shipping_company_id'],
				'fixed_telephone' => $data['address']['phone'],
				'distribution_time_out' => $data['shipping_info']['distribution_time_out'],
				'is_virtual' => $data['is_virtual'],
				'promotion_type' => isset($data["promotion_type"]) ? $data["promotion_type"] : 0,
				'promotion_id' => isset($data["promotion_id"]) ? $data["promotion_id"] : 0,
			);
			
			//创建订单
			$order_model->save($order_data);
			$order_id = $order_model->order_id;
			$order_data["order_id"] = $order_id;
			$data["order_data"] = $order_data;
			$order_goods_id_array = [];
			//生成订单项
			foreach ($data["goods_sku_array"] as $data_k => $data_v) {
				
				//限购判断(用户购买数不能大于商品最大购买数)
				if ($data_v["goods_info"]["max_buy"] != 0 && $data_v["goods_info"]["max_buy"] < ($data_v["buyed_num"] + $data_v["num"])) {
					$this->order->rollback();
					return error([], FULL_MAX_BUY_NUM);
				}
				$goods_calculate = new GoodsCalculate();
				$res = $goods_calculate->subGoodsStock($data_v['goods_id'], $data_v['sku_id'], $data_v["num"], '');
				//库存判断
				if ($res < 0) {
					$this->order->rollback();
					return error([], LOW_STOCKS);
				}
				$goods_calculate->addGoodsSales($data_v['goods_id'], $data_v['sku_id'], $data_v["num"]);
				$order_sku_data = array(
					'order_id' => $order_id,
					'goods_id' => $data_v['goods_id'],
					'goods_name' => $data_v['goods_name'],
					'sku_id' => $data_v['sku_id'],
					'sku_name' => $data_v['goods_sku_info']['sku_name'],
					'price' => $data_v["sku_price"],
					'num' => $data_v["num"],
					'adjust_money' => $data_v["adjust_money"],
					'cost_price' => $data_v['goods_sku_info']['cost_price'],
					'goods_money' => $data_v['total_money'],
					'goods_picture' => $data_v['goods_picture'], // 如果当前商品有SKU图片，就用SKU图片。没有则用商品主图
					'shop_id' => $data["shop_id"],
					'buyer_id' => $data["buyer_id"],
					'goods_type' => $data_v["goods_info"]['goods_type'],
					'promotion_id' => $data_v["goods_info"]['promote_id'],
					'promotion_type_id' => $data_v["goods_info"]['promotion_type'],
					'point_exchange_type' => $data_v["goods_info"]['point_exchange_type'],
					'order_type' => 1, // 订单类型默认1
					'give_point' => $data_v['total_give_point'],
					'is_virtual' => $data_v['is_virtual']
				);
				$order_goods = new NsOrderGoodsModel();
				$order_goods_id = $order_goods->save($order_sku_data);
				
				// 库存减少销量增加
				$order_goods_id_array[ $data_v['sku_id'] ] = $order_goods_id;
				
			}
			
			// 如果是订单自提需要添加自提相关信息
			if ($data["shipping_info"]["shipping_type"] == 2) {
				
				if (!empty($data["shipping_info"]["pick_up_id"])) {
					$pickup_model = new NsPickupPointModel();
					$pickup_point_info = $pickup_model->getInfo([ 'id' => $data["shipping_info"]["pick_up_id"] ], '*');
					$order_pick_up_model = new NsOrderPickupModel();
					$data_pickup = array(
						'order_id' => $order_id,
						'name' => $pickup_point_info['name'],
						'address' => $pickup_point_info['address'],
						'contact' => $pickup_point_info['contact'],
						'phone' => $pickup_point_info['phone'],
						'city_id' => $pickup_point_info['city_id'],
						'province_id' => $pickup_point_info['province_id'],
						'district_id' => $pickup_point_info['district_id'],
						'supplier_id' => $pickup_point_info['supplier_id'],
						'longitude' => $pickup_point_info['longitude'],
						'latitude' => $pickup_point_info['latitude'],
						'create_time' => time(),
						'picked_up_id' => $data["shipping_info"]["pick_up_id"]
					);
					if ($data["pay_money"] == 0) {
						$data_pickup['picked_up_code'] = $this->getPickupCode($data["shop_id"]);
					}
					
					$order_pick_up_model->save($data_pickup);
				}
			}
			
			// 积分兑换抵用金额
			$member_account_service = new MemberAccount();
			//账户抵现
			foreach ($data["offset_money_array"] as $offset_k => $offset_v) {
				$retval_point = $member_account_service->addMemberAccountData($data["shop_id"], $offset_v['account_type'], $data["buyer_id"], 0, $offset_v["num"] * (-1), 1, $order_id, '商城订单');
				if ($retval_point < 0) {
					$this->order->rollback();
					return error([], ORDER_CREATE_LOW_POINT);
				}
			}
			// 使用优惠券
			if ($data["coupon_id"] > 0) {
				//添加优惠券 优惠信息
				foreach ($data["coupon_array"]["coupon_goods_list"] as $k => $v) {
					$order_goods_promotion_details = new NsOrderGoodsPromotionDetailsModel();
					$coupon_data = array(
						'order_id' => $order_id,
						'promotion_id' => $v["coupon_id"],
						'sku_id' => $v['sku_id'],
						'promotion_type' => 'COUPON',
						'discount_money' => $v['money'],
						'used_time' => time()
					);
					$order_goods_promotion_details->save($coupon_data);
				}
				
				$coupon_service = new MemberCoupon();
				$retval = $coupon_service->useCoupon($data["buyer_id"], $data["coupon_id"], $order_id);
				if (!($retval > 0)) {
					$this->order->rollback();
					return error($retval, FULL_COUPON);
				}
			}
			
			//满额包邮
			if (!empty($data["promotion_full_mail_array"])) {
				$order_promotion_details = new NsOrderPromotionDetailsModel();
				$data_promotion_details = array(
					'order_id' => $order_id,
					'promotion_id' => $data["promotion_full_mail_array"]["promotion_id"],
					'promotion_type_id' => 2,
					'promotion_type' => $data["promotion_full_mail_array"]["promotion_type"],
					'promotion_name' => $data["promotion_full_mail_array"]["promotion_name"],
					'promotion_condition' => $data["promotion_full_mail_array"]["promotion_condition"],
					'discount_money' => $data["promotion_full_mail_array"]["discount_money"],
					'used_time' => time()
				);
				
				$order_promotion_details->save($data_promotion_details);
			}
			
			//优惠活动 写表
			if (!empty($data["promotion_array"])) {
				foreach ($data["promotion_array"] as $promotion_k => $promotion_v) {
					$order_promotion_details_model = new NsOrderPromotionDetailsModel();
					$data_promotion_details = array(
						'order_id' => $order_id,
						'promotion_id' => $promotion_v['promotion_id'],
						'promotion_type_id' => $promotion_v['promotion_type_id'],
						'promotion_type' => $promotion_v['promotion_type'],
						'promotion_name' => $promotion_v['promotion_name'],
						'promotion_condition' => $promotion_v['promotion_condition'],
						'discount_money' => $promotion_v['discount_money'],
						'used_time' => time()
					);
					$order_promotion_details_model->save($data_promotion_details);
					
					// 添加到对应商品项优惠满减
					if (!empty($promotion_v["promotion_sku_list"])) {
						foreach ($promotion_v["promotion_sku_list"] as $promotion_sku_k => $promotion_sku_v) {
							$order_goods_promotion_details = new NsOrderGoodsPromotionDetailsModel();
							$data_details = array(
								'order_id' => $order_id,
								'promotion_id' => $promotion_sku_v['promotion_id'],
								'sku_id' => $promotion_sku_v['sku_id'],
								'promotion_type' => $promotion_sku_v['promotion_type'],
								'discount_money' => $promotion_sku_v['discount_money'],
								'used_time' => time()
							);
							$order_goods_promotion_details->save($data_details);
						}
					}
					
				}
			}
			
			if (!empty($data["gift_array"])) {
				//生成订单项
				foreach ($data["gift_array"] as $gift_k => $gift_v) {
					
					//库存判断
					if ($gift_v['goods_sku_info']['stock'] < $gift_v["num"] || $gift_v["num"] <= 0) {
						$this->order->rollback();
						return error([], LOW_STOCKS);
					}
					
					$gift_sku_data = array(
						'order_id' => $order_id,
						'goods_id' => $gift_v['goods_id'],
						'goods_name' => $gift_v['goods_name'],
						'sku_id' => $gift_v['sku_id'],
						'sku_name' => $gift_v["goods_sku_info"]['sku_name'],
						'price' => $gift_v["sku_price"],
						'num' => $gift_v["num"],
						'adjust_money' => $gift_v["adjust_money"],
						'cost_price' => $gift_v['goods_info']['cost_price'],
						'goods_money' => $gift_v['total_money'],
						'goods_picture' => $gift_v['goods_picture'], // 如果当前商品有SKU图片，就用SKU图片。没有则用商品主图
						'shop_id' => $data["shop_id"],
						'buyer_id' => $data["buyer_id"],
						'goods_type' => $gift_v["goods_info"]['goods_type'],
						'promotion_id' => $gift_v["goods_info"]['promote_id'],
						'promotion_type_id' => $gift_v["goods_info"]['promotion_type'],
						'point_exchange_type' => $gift_v["goods_info"]['point_exchange_type'],
						'order_type' => 1, // 订单类型默认1
						'give_point' => $gift_v['total_give_point'],
						'is_virtual' => $gift_v['is_virtual'],
						'gift_flag' => $gift_v['gift_flag'],
					);
					
					$order_goods = new NsOrderGoodsModel();
					$order_goods_id = $order_goods->save($gift_sku_data);
					// 库存减少销量增加
					$goods_calculate = new GoodsCalculate();
					$goods_calculate->subGoodsStock($gift_v['goods_id'], $gift_v['sku_id'], $gift_v["num"], '');
					$goods_calculate->addGoodsSales($gift_v['goods_id'], $gift_v['sku_id'], $gift_v["num"]);
					
					$order_goods_id_array[ $data_v['sku_id'] ] = $order_goods_id;
				}
			}
			
			//生成操作日志
			$order_action_service = new OrderAction();
			$action_data = array(
				"remark" => "创建订单",
				"uid" => $data['buyer_id'],
				"order_id" => $order_id,
			);
			$order_action_service->addOrderAction($action_data);
			//订单创建后
			$this->orderCreateSuccess($data);
			
			$this->order->commit();
			return success([ 'order_id' => $order_id, 'out_trade_no' => $out_trade_no ]);
		} catch (\Exception $e) {
			
			$this->order->rollback();
			return error([], ERROR);
		}
	}
	
	/**
	 * 订单创建成功
	 */
	public function orderCreateSuccess($data)
	{
		$pay = new UnifyPay();
		$pay_body = $data["shop_name"];
		//支付信息拼装
		$first_goods_name = $data["goods_sku_array"][0]["goods_info"]["goods_name"];
		if (count($data["goods_sku_array"]) > 1) {
			$first_goods_name .= '等多件';
		}
		if (!empty($first_goods_name)) {
			$pay_body .= '-' . $first_goods_name;
		}
		$patterns = array( '/&amp;/' );
		$replacements = array( '' );
		$pay_body = preg_replace($patterns, $replacements, $pay_body);
		if (strlen($pay_body) > 127) {
			$num = round(127 / 3);
			$pay_body = mb_substr($pay_body, 0, $num, 'UTF-8');
			$pay_body .= '...';
		}
		$pay->createPayment($data["shop_id"], $data["order_data"]["out_trade_no"], $pay_body, $data["shop_name"] . "订单", $data["pay_money"], 1, $data["order_data"]["order_id"]);
		
		$order_action = new OrderAction();
		if ($data["pay_type"] == 4) {
			$order_action->orderOnLinePay($data["order_data"]["out_trade_no"], 4);
		} else {
			if ($data["order_data"]['user_platform_money'] != 0) {
				if ($data["order_data"]['pay_money'] == 0) {
					$order_action->orderOnLinePay($data["order_data"]["out_trade_no"], 5);
				}
			} else {
				
				if ($data["order_data"]['pay_money'] == 0) {
					$order_action->orderOnLinePay($data["order_data"]["out_trade_no"], 1); // 默认微信支付
				}
			}
		}
//		runhook("Notify", "orderCreate", array(
//			"order_id" => $data["order_data"]["order_id"]
//		));

		message("create_order", ["order_id" => $data["order_data"]["order_id"]]);
//		hook('orderCreateSuccess', [
//			'order_id' => $data["order_data"]["order_id"]
//		]);
		hook("orderCreateSuccessAction", $data);
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
	 * 根据sku信息时间商品的价格
	 */
	public function getOrderGoodsSkuPrice($data, $order_data)
	{
		
		$goods_sku_info = $data["goods_sku_info"];//sku信息
		
		$goods_member_discount = $this->getGoodsMemberDiscount($data['buyer_id'], $goods_sku_info["goods_id"]);
		
		// 判断商品是否有设置会员折扣率 如果没有则使用店铺设置会员折扣率
		if (!empty($goods_member_discount)) {
			$member_level_discount = $goods_member_discount;
		} else {
			$member_level_discount = $data['discount'];
		}
		$member_price = $goods_sku_info['price'] * $member_level_discount;
		
		// 处理会员价
		$goods = new Goods();
		$member_price = $goods->handleMemberPrice($goods_sku_info['goods_id'], $member_price);
		
		//商品活动价和商品会员价比较
		if ($member_price < $goods_sku_info['promote_price']) {
			$sku_price = $member_price;
		} else {
			$sku_price = $goods_sku_info['promote_price'];
		}
		//订单计算
		$order_goods_preference = new GoodsPreference();
		$sku_price = $order_goods_preference->getOrderGoodsLadderPreferentialPrice($data, $sku_price);//商品阶梯价格计算
		
		return $sku_price;
	}
	
	/**
	 * 得到用户购买数量
	 */
	public function getOrderGoodsNum($data)
	{
		// 用户可能分开进行购买，统计当前用户购买了多少件该商品
		$order_goods_model = new NsOrderGoodsModel();
		$order_goods_num = $order_goods_model->alias("nog")
			->join('ns_order no', 'no.order_id = nog.order_id', 'left')
			->where([
				"nog.goods_id" => $data["goods_id"],
				"nog.shop_id" => $data["shop_id"],
				"nog.buyer_id" => $data["buyer_id"],
				"no.order_status" => [
					"neq",
					5
				]
			])
			->sum("nog.num");
		return $order_goods_num;
	}
	
	/**
	 * 组合商品sku数据(订单)
	 */
	public function getGoodsSkuArray($data)
	{
		
		$goods_sku_list = $data["goods_sku_list"];
		$goods_sku_model = new NsGoodsSkuModel();
		$goods_model = new NsGoodsModel();
		$album_picture_model = new AlbumPictureModel();
		
		$goods_sku_array = [];
		$max_use_point = 0;//最大使用积分
		$virtual_product_num = 0;//虚拟商品数量
		$is_virtual = 0;//虚拟订单标识
		$total_price = 0;//总价
		
		//订单计算
		if (!empty($goods_sku_list)) {
			//整理传入的商品数据
			$goods_sku_list_array = explode(",", $goods_sku_list);
			foreach ($goods_sku_list_array as $k => $v) {
				
				$temp_array = [];
				$sku_data = explode(":", $v);
				$temp_array["sku_id"] = $sku_data[0];
				$temp_array["num"] = $sku_data[1];
				$temp_array["shop_id"] = $data["shop_id"];
				$goods_sku_info = $goods_sku_model->getInfo([ "sku_id" => $sku_data[0] ], "*");
				if (empty($goods_sku_info)) {
					return error();
				}
				//判断商品库存是否大于购买数量
//                 if ($goods_sku_info['stock'] < $temp_array["num"] || $temp_array["num"] <= 0) {
//                     return error();
//                 }
				$goods_info = $goods_model->getInfo([ "goods_id" => $goods_sku_info["goods_id"] ], "*");
				if (empty($goods_info)) {
					return error();
				}
				$temp_array["goods_sku_info"] = $goods_sku_info;
				$temp_array["goods_info"] = $goods_info;
				//查询图片
				if (!empty($goods_sku_info['picture'])) {
					$temp_array["goods_picture"] = $goods_sku_info['picture'];
				} else {
					$temp_array["goods_picture"] = $goods_info['picture'];
				}
				$goods_picture_info = $album_picture_model->getInfo([ "pic_id" => $temp_array["goods_picture"] ], "*");
				$temp_array["goods_picture_info"] = $goods_picture_info;
				$temp_array["goods_id"] = $goods_sku_info["goods_id"];
				$temp_array["goods_name"] = $goods_info["goods_name"] . $goods_sku_info["sku_name"];
				
				if ($goods_info["is_virtual"] == 1) {
					if ($data["order_config"]['is_open_virtual_goods'] == 0)
						return error([], VIRTUAL_NO_OPEN);
					
					$virtual_product_num += 1;//如果是虚拟商品 虚拟商品数量价一
					$temp_array["is_virtual"] = 1;
				} else {
					$temp_array["is_virtual"] = 0;
				}
				
				$temp_array["discount"] = $data["discount"];
				$temp_array["buyer_id"] = $data["buyer_id"];
				
				//商品活动价格
				$sku_price = $this->getOrderGoodsSkuPrice($temp_array, $data);
				$sku_price = sprintf("%.2f", $sku_price);
				$order_goods_money = $sku_price * $temp_array["num"];
				$temp_array["total_price"] = sprintf("%.2f", $order_goods_money);//订单项总金额
				
				$temp_array["sku_price"] = $sku_price;
				$temp_array["total_money"] = $order_goods_money;
				$total_price += $order_goods_money;
				
				//用户已购买 商品数量
				$buyed_num = $this->getOrderGoodsNum($temp_array);
				$temp_array["buyed_num"] = $buyed_num;
				
				$temp_array['adjust_money'] = 0;
				$can_point = 0;
				//积分抵现
				if ($data["point_config"]["is_open"] == 1) {
					$can_point = ceil($order_goods_money / $data["point_config"]["convert_rate"]);
					if ($can_point > $goods_info["max_use_point"]) {
						$can_point = $goods_info["max_use_point"];
					}
				}
				$temp_array["max_use_point"] = $can_point;
				$max_use_point += $can_point;
				
				$goods_sku_array[] = $temp_array;
			}
		} else {
			return error();
		}
		if ($virtual_product_num > 0) {
			$is_virtual = 1;
		}
		
		$data["goods_sku_array"] = $goods_sku_array;
		$data["is_virtual"] = $is_virtual;
		$data["total_money"] = $total_price;
		//判断积分账户
		if ($data['member_account']['point'] < $max_use_point) {
			$max_use_point = $data['member_account']['point'];
		}
		// 如果积分抵现比率为0 或未开启
		if (empty($data['point_config']['convert_rate']) || empty($data['point_config']['is_open'])) {
			$max_use_point = 0;
		}
		$data["max_use_point"] = $max_use_point;
		/******************************************************************营销活动 start********************************************************************/
		//营销活动订单数据 promotion_type  1 组合套餐 2 团购  3 砍价   4 积分兑换
		
		$promotion_order_data = $this->getOrderSkuPromotion($data);
		
		if (!empty($promotion_order_data)) {
			if ($promotion_order_data["code"] <= 0) {
				
				return $promotion_order_data;
			}
			$data = $promotion_order_data["data"];
		}
		/*******************************************************************营销活动 end*******************************************************************/
		$total_give_point = 0;//总返积分
		foreach ($data["goods_sku_array"] as $k => $v) {
			//订单项返积分
			if ($v['goods_info']['integral_give_type'] == 0) {
				$give_point = $v['goods_info']['give_point'];
			} else {
				if ($v['goods_info']['give_point'] > 0) {
					$give_point = round($v["sku_price"] * ($v['goods_info']['give_point'] * 0.01));
				} else {
					$give_point = 0;
				}
			}
			
			$data["goods_sku_array"][ $k ]["give_point"] = $give_point;
			$data["goods_sku_array"][ $k ]["total_give_point"] = $give_point * $v["num"];
			
			$total_give_point += $data["goods_sku_array"][ $k ]["total_give_point"];
		}
		$data["give_point"] = $total_give_point;
		
		return success($data);
		
	}
	
	/**
	 * 获取订单所需 站点信息
	 */
	public function getOrderShopInfo($params)
	{
		$data = [];
		
		$shop_id = 0;//单商户默认店铺id为0
		
		$data["shop_id"] = $shop_id;
		// 单店版查询网站内容
		$web_site = new WebSite();
		$web_info = $web_site->getWebSiteInfo();
		$data["shop_name"] = $web_info['title'];
		$data["buyer_id"] = $params["buyer_id"];
		
		//交易配置
		$config_service = new Config();
		$config_info = $config_service->getShopConfig($data["shop_id"]);
		$data["order_config"] = $config_info;
		// 获取购买人信息
		$user_model = new UserModel();
		$user_info = $user_model->getInfo([ 'uid' => $data["buyer_id"] ], '*');
		$data["user_info"] = $user_info;
		
		$data["user_name"] = $user_info["nick_name"];
		// 会员账户信息
		$member_account_service = new MemberAccount();
		$member_account = $member_account_service->getMemberAccountInfo($data["buyer_id"]);
		$data["member_account"] = $member_account;
		
		// 订单来源
		if (isWechatApplet($this->uid)) {
			$order_from = 4; // 微信小程序
		} elseif (isWeixin()) {
			$order_from = 1; // 微信
		} elseif (request()->isMobile()) {
			$order_from = 2; // 手机
		} else {
			$order_from = 3; // 电脑
		}
		
		$data["order_from"] = $order_from;
		
		// 积分返还类型
		$config = new ConfigModel();
		$config_info = $config->getInfo([
			"instance_id" => $shop_id,
			"key" => "SHOPPING_BACK_POINTS"
		], "value");
		$give_point_type = $config_info["value"];
		$data["give_point_type"] = $give_point_type;
		
		//会员折扣
		$order_goods_preference = new GoodsPreference();
		$discount = $order_goods_preference->getMemberLevelDiscount($data["buyer_id"]);
		$data["discount"] = $discount;
		
		//积分配置
		$promotion_service = new Promotion();
		$point_config = $promotion_service->getPointConfig();
		$data["point_config"] = $point_config;
		return $data;
	}
	
	/**
	 * 满额包邮活动
	 */
	public function getPromotionFullMail($data)
	{
		$promotion_array = [];
		// 计算订单的满额包邮
		$full_mail_model = new NsPromotionFullMailModel();
		// 店铺的满额包邮
		$full_mail_obj = $full_mail_model->getInfo([ "shop_id" => $data["shop_id"] ], "*");
		
		$no_mail = checkIdIsinIdArr($data["address"]["city"], $full_mail_obj['no_mail_city_id_array']);
		if ($no_mail) {
			$full_mail_obj['is_open'] = 0;
		}
		if (!empty($full_mail_obj)) {
			$is_open = $full_mail_obj["is_open"];
			$full_mail_money = $full_mail_obj["full_mail_money"];
			$order_real_money = $data["total_money"] - $data["promotion_money"] - $data["coupon_money"];
			if ($is_open == 1 && $order_real_money >= $full_mail_money && $data['shipping_money'] > 0) {
				// 符合满额包邮 邮费设置为0
				$promotion_array["promotion_id"] = $full_mail_obj["mail_id"];
				$promotion_array["promotion_type"] = 'MANEBAOYOU';
				$promotion_array["promotion_name"] = '满额包邮';
				$promotion_array["promotion_condition"] = '满' . $full_mail_money . '元,包邮!';
				$promotion_array["discount_money"] = $data['shipping_money'];
			}
		}
		
		return $promotion_array;
	}
	
	/**
	 * 订单优惠券活动优惠
	 */
	public function getOrderPromotionCoupon($data)
	{
		$coupon_array = array();
		$coupon_money = 0;
		if (!empty($data["coupon_id"])) {
			// 获取优惠券金额
			$coupon = new MemberCoupon();
			$coupon_money = $coupon->getCouponMoney($data["coupon_id"]);
			
			if ($coupon_money > 0) {
				// 获取优惠券详情
				$coupon = new NsCouponModel();
				$coupon_type_id = $coupon->getInfo([ 'coupon_id' => $data["coupon_id"] ], 'coupon_type_id');
				$promote = new Promotion();
				$coupon_type_detail = $promote->getCouponTypeDetail($coupon_type_id['coupon_type_id']);
				
				if ($coupon_type_detail['range_type'] == 1) {
					
					// 优惠券全场使用
					$count_discount_money = 0;
					
					foreach ($data["goods_sku_array"] as $k => $v) {
						$discount_money = $k == (count($data["goods_sku_array"]) - 1) ? $coupon_money - $count_discount_money : round($coupon_money * $v["total_money"] / $data["total_money"], 2);
						
						$coupon_item = array(
							'sku_id' => $v["sku_id"],
							'money' => $discount_money,
							'coupon_id' => $data["coupon_id"]
						);
						$coupon_array["coupon_goods_list"][] = $coupon_item;
						
						$count_discount_money = $discount_money + $count_discount_money;
					}
					
				} else {
					// 优惠券部分商品使用
					$coupon_goods_money = 0;
					$coupon_goods_list = $coupon_type_detail['goods_list'];
					
					$coupon_goods_id_array = [];
					$tymp_array = [];
					//查询优惠券可用商品
					foreach ($coupon_goods_list as $list_k => $list_v) {
						$coupon_goods_id_array[] = $list_v["goods_id"];
					}
					
					foreach ($data["goods_sku_array"] as $k => $v) {
						
						if (in_array($v["goods_id"], $coupon_goods_id_array) !== false) {
							$coupon_goods_money += $v["total_money"];
							$tymp_array[] = $v;
						}
						
					}
					
					if ($coupon_goods_money == 0) {
						$coupon_goods_money = $data["total_money"];
					}
					$count_discount_moeny = 0;
					foreach ($tymp_array as $k => $v) {
						// 获取sku总价
						$goods_money = $this->getOrderGoodsSkuPrice($v, $data);
						$discount_money = $k == (count($tymp_array) - 1) ? $coupon_money - $count_discount_moeny : round($coupon_money * $v["total_money"] / $coupon_goods_money, 2);
						
						$coupon_item = array(
							'sku_id' => $v["sku_id"],
							'money' => $discount_money,
							'coupon_id' => $data["coupon_id"]
						);
						$coupon_array["coupon_goods_list"][] = $coupon_item;
						$count_discount_moeny = $discount_money + $count_discount_moeny;
					}
				}
			}
			
		}
		$coupon_array["money"] = $coupon_money;
		return $coupon_array;
	}
	
	/**
	 * 满减优惠活动
	 */
	public function getOrderManJian($data)
	{
		$mansong_array = array();
		if ($data["promotion_type"] == 0) {
			// 订单满减送活动优惠
			$goods_mansong = new GoodsMansong();
			if (!empty($data["goods_sku_array"])) {
				$time = date("Y-m-d H:i:s", time());
				// 检测店铺是否存在正在进行的全场满减送活动
				$condition = array(
					'status' => 1,
					'range_type' => 1,
					'shop_id' => $data["shop_id"]
				);
				$promotion_mansong = new NsPromotionMansongModel();
				$list_quan = $promotion_mansong->getQuery($condition, '*', 'create_time desc');
				if (!empty($list_quan[0])) {
					// 存在全场满减送
					$rule_list = $goods_mansong->getMansongRule($list_quan[0]['mansong_id']); // 得到满减规则
					$discount_List = array();
					$rule_detail = [];
					
					$gift_array = [];//赠品id
					foreach ($rule_list as $k_rule => $rule) {
						
						//是否满足满减条件
						if ($rule['price'] <= $data["total_money"]) {
							foreach ($data["goods_sku_array"] as $goods_sku_k => $goods_sku_v) {
								$goods_sku_promote_price = $rule['discount'] * $goods_sku_v["total_money"] / $data["total_money"];
								
								$temp_array = $goods_sku_v;
								
								$temp_array["rule"] = $rule;
								$temp_array["promotion_price"] = $goods_sku_promote_price;
								$temp_array["discount_money"] = $goods_sku_promote_price;
								$temp_array["promotion_id"] = $rule["rule_id"];
								$temp_array["promotion_type"] = 'MANJIAN';
								
								$temp_array["gift_id"] = $rule["gift_id"];
								
								$discount_List[] = $temp_array;
							}
							$gift_array[] = $rule["gift_id"];
							$rule_detail = $rule;
							break;
						}
					}
					
					$mansong_array[0] = array(
						'promotion_id' => $rule_detail["rule_id"],
						'promotion_type_id' => 1,
						'promotion_type' => 'MANJIAN',
						'promotion_condition' => '满' . $rule_detail['price'] . '元，减' . $rule_detail['discount'],
						'promotion_name' => '满减送活动',
						'discount_money' => $rule_detail['discount'],
						'promotion_sku_list' => $discount_List,
						'gift_array' => $gift_array,
						'free_shipping' => $rule_detail['free_shipping']
					);
					if ($data["is_virtual"] == 1) {
						$mansong_array[0]['gift_array'] = [];
					}
				} else {
					// 存在部分商品满减送活动(只可能存在部分商品满减送)
					// 1.查询商品列表可能的满减送活动列表    (ing)
					$mansong_service = new GoodsMansong();
					$promotion_array = array();
					//组合具备活动属性的商品
					foreach ($data["goods_sku_array"] as $k => $v) {
						$promotion = $mansong_service->getGoodsMansongPromotion($v["goods_id"]);
						if (!empty($promotion)) {
							$promotion_array[] = $promotion;
						}
					}
					//去重（商品活动重合项）
					$mansong_list = array_unique($promotion_array);
					
					if (!empty($mansong_list)) {
						// 循环满减送活动
						foreach ($mansong_list as $k => $v) {
							
							$discount_info_detail = [];
							$new_sku_list_array = array();
							$new_sku_list_price = 0;//总价
							//满减活动商品项
							$mansong_goods_list = $mansong_service->getMansongGoods($v['mansong_id'], "goods_id");
							$mansong_goods_id_array = [];
							foreach ($mansong_goods_list as $man_k => $man_v) {
								$mansong_goods_id_array[] = $man_v["goods_id"];
							}
							// 查询组装新的sku列表
							foreach ($data["goods_sku_array"] as $ck => $cv) {
								//判断是否存在与活动商品数据中
								if (in_array($cv["goods_id"], $mansong_goods_id_array) !== false) {
									$new_sku_list_array[] = $cv;
									$new_sku_list_price += $cv["total_money"];
								}
							}
							if (!empty($new_sku_list_array)) {
								// 得到满减规则
								$rule_list = $goods_mansong->getMansongRule($v['mansong_id']);
								$discount_List = array();
								$rule_detail = [];
								$gift_array = [];
								// 获取订单项减现金额
								foreach ($rule_list as $k_rule => $rule) {
									if ($rule['price'] <= $new_sku_list_price) {
										foreach ($new_sku_list_array as $k_goods_sku => $v_goods_sku) {
											$goods_sku_promote_price = $rule['discount'] * $v_goods_sku["total_money"] / $new_sku_list_price;
											$temp_array = $v_goods_sku;
											$temp_array["rule"] = $rule;
											$temp_array["promotion_price"] = $goods_sku_promote_price;
											
											$temp_array["discount_money"] = $goods_sku_promote_price;
											$temp_array["promotion_id"] = $rule["rule_id"];
											$temp_array["promotion_type"] = 'MANJIAN';
											
											$temp_array["gift_id"] = $rule["gift_id"];
											
											$discount_List[] = $temp_array;
										}
										$gift_array[] = $rule["gift_id"];
										$rule_detail = $rule;
										break;
									}
								}
								$discount_info_detail = array(
									'promotion_id' => $rule_detail["rule_id"],
									'promotion_type_id' => 1,
									'promotion_type' => 'MANJIAN',
									'promotion_condition' => '满' . $rule_detail['price'] . '元，减' . $rule_detail['discount'],
									'promotion_name' => '满减送活动',
									'discount_money' => $rule_detail['discount'],
									'promotion_sku_list' => $discount_List,
									"gift_array" => $gift_array,
									'free_shipping' => $rule_detail['free_shipping']
								);
								if ($data["is_virtual"] == 1) {
									$discount_info_detail['gift_array'] = [];
								}
								
							}
							$mansong_array[] = $discount_info_detail;
						}
					}
				}
			}
		}
		
		return $mansong_array;
		
	}
	
	
	/**
	 * 获取订单优惠(列表)
	 */
	public function getOrderPromotionArray($data)
	{
		$promotion_array = [];
		//满减送
		if ($data["order_type"] == 1 && $data["promotion_type"] == 0) {
			$manjian_array = $this->getOrderManJian($data);
			$promotion_array = array_merge($promotion_array, $manjian_array);
		}
		
		//赠品
		if ($data["order_type"] == 1 && $data["promotion_type"] == 5) {
			$manjian_array = $this->getOrderPromotionGift($data);
			$promotion_array = array_merge($promotion_array, $manjian_array);
		}
		
		$promotion_list = hook("getOrderPromotionArray", $data);
		$promotion_list = arrayFilter($promotion_list);
		if (!empty($promotion_list[0])) {
			foreach ($promotion_list as $k => $v) {
				if ($v["code"] <= 0) {
					
					return $v;
				}
				$promotion_array = array_merge($promotion_array, $v["data"]);
			}
		}
		
		return success($promotion_array);
	}
	
	/**
	 * 订单账户抵现
	 */
	public function getOrderOffsetAccountMoney($data)
	{
		$offset_money_array = array();
		
		//积分
		$order_goods_preference = new GoodsPreference();
		if ($data["promotion_type"] == 4) {
			$point_money = 0;
		} else {
			$point_money = $order_goods_preference->getPointMoney($data["point"], 0);
			
		}
		$offset_money_array["point"] = [ "offset_money" => $point_money, "num" => $data["point"], "account_type" => 1 ];
		
		//会员余额
		$offset_money_array["user_money"] = [ "offset_money" => $data["user_money"], "num" => $data["user_money"], "account_type" => 2 ];
		
		//平台余额
		$offset_money_array["platform_money"] = [ "offset_money" => $data["platform_money"], "num" => $data["platform_money"], "account_type" => 2 ];
		
		//购物币
		//         $offset_money_array[] = ["offset_money"=> 0, "num" => $data["coin"], "account_type" => 3];
		
		return $offset_money_array;
	}
	
	/**
	 * 订单物流计算
	 */
	public function getCalculateShippingMoney($data)
	{
		if (empty($data['shipping_info'])) {
			return success([ "shipping_money" => 0 ]);
		}
		
		if ($data["is_virtual"] == 1) {
			return success([ "shipping_money" => 0 ]);
		}
		
		//物流运算
		$order_goods_express = new GoodsExpress();
		$order_goods_preference = new GoodsPreference();
		$shipping_data = [];
		
		if ($data['shipping_info']['shipping_type'] == 1) {
			//如果有营销活动, 没有物流公司也可以
			if ($data["promotion_type"] == 3) {
				$shipping_money = 0;
			} else {
				$shipping_money = $order_goods_express->getSkuListExpressFee($data["goods_sku_list"], $data['shipping_info']['shipping_company_id'], $data["address"]['province'], $data["address"]['city'], $data["address"]['district']);
				//报错
				if ($shipping_money < 0) {
					return error([], $shipping_money);
				}
			}
			
			
		} elseif ($data['shipping_info']['shipping_type'] == 2 && addon_is_exit('NsPickup')) {
			
			// 根据自提点服务费用计算
			$shipping_money = $order_goods_preference->getPickupMoney($data["total_money"]);
			
		} elseif ($data['shipping_info']['shipping_type'] == 3 && addon_is_exit('NsO2o')) {
			
			$shipping_money = $order_goods_express->getGoodsO2oPrice($data["total_money"], $data["shop_id"], $data["address"]['province'], $data["address"]['city'], $data["address"]['district'], 0);
		} else {
			return error();
		}
		if ($shipping_money < 0) {
			return error([], $shipping_money);
		}
		
		$shipping_data["shipping_money"] = $shipping_money;
		return success($shipping_data);
	}
	
	/**
	 * 计算订单税费
	 */
	public function getOrderTax($data)
	{
		//计算税费
		if (!empty($data["buyer_invoice"])) {
			// 添加税费
			$config_service = new Config();
			$tax_value = $config_service->getConfig(0, 'ORDER_INVOICE_TAX');
			if (empty($tax_value['value'])) {
				$tax = 0;
			} else {
				$tax = $tax_value['value'];
			}
			$tax_money = $data['order_money'] * $tax / 100;
		} else {
			$tax_money = 0;
		}
		
		return $tax_money;
	}
	
	/**
	 * 获取赠品商品列表
	 */
	public function getOrderGiftList($data)
	{
		$promotion_gift_goods_model = new NsPromotionGiftGoodsModel();
		$goods_model = new NsGoodsModel();
		$goods_sku_model = new NsGoodsSkuModel();
		$album_picture_model = new AlbumPictureModel();
		if (!empty($data["gift_id_array"])) {
			foreach ($data["gift_id_array"] as $k => $v) {
				$gift_goods_info = $promotion_gift_goods_model->getInfo([ 'gift_id' => $v ], "goods_id");
				$goods_id = $gift_goods_info["goods_id"];
				
				$temp_array = [];
				$temp_array["shop_id"] = $data["shop_id"];
				$temp_array["num"] = 1;
				$goods_sku_info = $goods_sku_model->getInfo([ 'goods_id' => $goods_id ], "*");
				if (empty($goods_sku_info))
					continue;
				
				if (empty($createNewOutTradeNo))
					$temp_array["sku_id"] = $goods_sku_info["sku_id"];
				
				$goods_info = $goods_model->getInfo([ "goods_id" => $goods_sku_info["goods_id"] ]);
				
				if (empty($goods_sku_info))
					continue;
				
				//查询图片
				if (!empty($goods_sku_info['picture'])) {
					$temp_array["goods_picture"] = $goods_sku_info['picture'];
				} else {
					$temp_array["goods_picture"] = $goods_info['picture'];
				}
				$goods_picture_info = $album_picture_model->getInfo([ "pic_id" => $temp_array["goods_picture"] ], "*");
				$temp_array["goods_picture_info"] = $goods_picture_info;
				
				$temp_array["goods_sku_info"] = $goods_sku_info;
				$temp_array["goods_info"] = $goods_info;
				
				$temp_array["goods_name"] = $goods_info["goods_name"] . $goods_sku_info["sku_name"];
				$temp_picture = $this->getSkuPictureBySkuId($goods_sku_info);
				$temp_array["goods_picture"] = $temp_picture != 0 ? $temp_picture : $goods_info['picture']; // 如果当前商品有SKU图片，就用SKU图片。没有则用商品主图
				
				$temp_array["goods_id"] = $goods_sku_info["goods_id"];
				
				if ($goods_info["is_virtual"] == 1) {
					$temp_array["is_virtual"] = 1;
				} else {
					$temp_array["is_virtual"] = 0;
				}
				
				$temp_array["buyer_id"] = $data["buyer_id"];
				//商品活动价格
				$sku_price = 0;
				
				//用户已购买 商品数量
				$buyed_num = $this->getOrderGoodsNum($temp_array);
				$temp_array["buyed_num"] = $buyed_num;
				
				$order_goods_money = $sku_price * $temp_array["num"];
				
				$temp_array["sku_price"] = $sku_price;
				$temp_array["total_money"] = $order_goods_money;
				
				$temp_array["give_point"] = 0;
				$temp_array["total_give_point"] = 0;
				
				$temp_array['gift_flag'] = $v;
				
				$temp_array['adjust_money'] = 0;
				$goods_sku_array[] = $temp_array;
				
			}
		}
		return $goods_sku_array;
	}
	
	/**
	 * 订单商品计算
	 */
	public function orderCalculate($data)
	{
		//调用订单创建
		$order_result = hook("orderCalculate", $data);
		$order_result = arrayFilter($order_result);
		if (!empty($order_result[0])) {
			return $order_result[0];
		} else {
			if ($data["order_type"] > 1) {
				return error([]);
			}
		}
		
		$shop_data = $this->getOrderShopInfo($data);//站点信息
		
		$order_data = array_merge($data, $shop_data);
		
		$result_data = $this->getGoodsSkuArray($order_data);//重组商品列表
		
		if ($result_data["code"] <= 0) {
			return $result_data;
		}
		
		$order_data = $result_data["data"];
		$shipping_data = $this->getCalculateShippingMoney($order_data);
		
		$order_data["shipping_data"] = $shipping_data;
		if ($shipping_data["code"] <= 0) {
			$shipping_money = 0;
		} else {
			$shipping_money = $shipping_data["data"]["shipping_money"];
		}
		
		//优惠活动
		$promotion_money = 0;//优惠金额
		$promotion_result = $this->getOrderPromotionArray($order_data);
		
		if ($promotion_result["code"] <= 0) {
			return $promotion_result;
		}
		//赠品id数组
		$gift_id_array = [];
		
		$promotion_array = $promotion_result["data"];
		
		if (!empty($promotion_array)) {
			foreach ($promotion_array as $promotion_k => $promotion_v) {
				$promotion_money += $promotion_v["discount_money"];
				if ($promotion_v["promotion_type"] == 'MANJIAN') {
					if (!empty($promotion_v["gift_array"])) {
						$gift_id_array = array_merge($gift_id_array, $promotion_v["gift_array"]);
					}
				}
				//是否满足满减送免邮
				if ($promotion_v['free_shipping'] == 1) {
					$shipping_money = 0;
				}
			}
		}
		
		$order_data["gift_id_array"] = $gift_id_array;
		//统计赠品
		$gift_goods_array = [];
		if (!empty($gift_id_array)) {
			$gift_goods_array = $this->getOrderGiftList($order_data);
		}
		$order_data["gift_array"] = $gift_goods_array;
		
		//抵扣金额
		$offset_money_array = $this->getOrderOffsetAccountMoney($order_data);
		if (!empty($offset_money_array)) {
			$offset_money = 0;
			if (!empty($offset_money_array)) {
				foreach ($offset_money_array as $k_offset_money => $v_offset_money) {
					$offset_money += $v_offset_money['offset_money'];
				}
			}
		} else {
			$offset_money = 0;
		}

//		if ($order_data["promotion_type"] == 3) {//砍价活动
//			$order_data['address'] = $order_data["promotion_info"]["bargain_info"]["address_info"];
//		}
		
		//优惠券
		$coupon_array = $this->getOrderPromotionCoupon($order_data);
		
		//如果优惠金额大于订单总金额，优惠金额变为订单总金额
		$coupon_money = $coupon_array["money"];//优惠金额
		
		$order_data['goods_money'] = $order_data["total_money"];//订单商品金额
		$order_money = $order_data["total_money"];
		if ($order_money < $promotion_money) {
			$promotion_money = $order_money;
		}
		$order_money -= $promotion_money;
		$order_data['promotion_money'] = $promotion_money;
		
		if ($order_money < $coupon_money) {
			$coupon_money = $order_money;
		}
		$order_money -= $coupon_money;
		$order_data['coupon_money'] = $coupon_money;
  
		//TODO: 加上优惠卷金额，即所有的优惠金额。
		$promotion_money += $coupon_money;
        $order_data['promotion_money'] = $promotion_money;
		
		//满额包邮
		$order_data['shipping_money'] = $shipping_money;
		$promotion_full_mail_array = $this->getPromotionFullMail($order_data);
		if (!empty($promotion_full_mail_array)) {
			//判断是否符合满额包邮的条件
			$shipping_money = 0;
		}
		
		$order_data['shipping_money'] = $shipping_money;
		
		$order_data['order_money'] = $order_money + $shipping_money;
		//税费计算
		$tax_money = $this->getOrderTax($order_data);
		// 计算税费,暂时定为0
		$order_data['tax_money'] = $tax_money;
		
		$order_data['order_money'] = $order_data['order_money'] + $tax_money;
		
		// 累计抵扣金额
		if ($offset_money > $order_data['order_money']) {
			$offset_money = $order_data['order_money'];
		}
		$data_order['offset_money'] = $offset_money;
		
		//实际需要支付
		$order_data['pay_money'] = $order_data['order_money'] - $offset_money;
		
		$order_data['promotion_array'] = $promotion_array;
		$order_data['offset_money_array'] = $offset_money_array;
		$order_data['coupon_array'] = $coupon_array;
		
		$order_data['promotion_full_mail_array'] = $promotion_full_mail_array;
		
		if ($order_data["is_virtual"] == 1) {
			$order_data['address']['mobile'] = $order_data['user_telephone'];
			$order_data['address']['province'] = ''; // '收货人所在省',
			$order_data['address']['city'] = ''; // '收货人所在城市',
			$order_data['address']['district'] = ''; // '收货人所在街道',
			$order_data['address']['address_info'] = ''; // '收货人详细地址',
			$order_data['address']['zip_code'] = ''; // '收货人邮编',
			$order_data['address']['consigner'] = ''; // '收货人姓名',
			$order_data['address']['phone'] = '';
		}
		
		return $order_data;
	}
	
	/**
	 * 获取订单当前状态 名称
	 */
	public function getOrderStatusName($order_status)
	{
		$order_status_info = $this->getOrderStatus([ "order_type" => 1, "order_status" => $order_status ]);
		if (empty($order_status_info)) {
			return false;
		}
		return $order_status_info["status_name"];
	}
	
	/**
	 * 获取自提码
	 */
	public function getPickupCode($shop_id)
	{
		$pickup_code = substr(sha1(date('YmdHis') . uniqid() . $shop_id), 24);
		return $pickup_code;
	}
	
	
	/**************************************************************************************************订单创建前查询***********************/
	/**
	 * 获取对应sku列表价格
	 */
	public function getGoodsSkuListPrice($goods_sku_list)
	{
		$goods_preference = new GoodsPreference();
		$money = $goods_preference->getGoodsSkuListPrice($goods_sku_list);
		return $money;
	}
	
	/**
	 * 获取组合商品sku列表价格
	 */
	public function getComboPackageGoodsSkuListPrice($goods_sku_list)
	{
		$goods_preference = new GoodsPreference();
		$money = $goods_preference->getComboPackageGoodsSkuListPrice($goods_sku_list);
		return $money;
	}
	
	/**
	 * 获取邮费
	 */
	public function getExpressFee($goods_sku_list, $express_company_id, $province, $city, $district)
	{
		$goods_express = new GoodsExpress();
		$fee = $goods_express->getSkuListExpressFee($goods_sku_list, $express_company_id, $province, $city, $district);
		return $fee;
	}
	
	/**
	 * 获取用户可使用优惠券
	 */
	public function getMemberCouponList($data)
	{
		if ($data["order_type"] != 1) {
			return array();
		}
		$coupon_list = array();
		//查询用户优惠券
		$coupon_model = new NsCouponModel();
		$condition = array(
			'end_time' => array(
				'GT',
				time()
			),
			'state' => 1,
			'uid' => $data['buyer_id'],
			'shop_id' => $data['shop_id']
		);
		$member_coupon_list = $coupon_model->getQuery($condition, "*", "", "coupon_type_id");
//         if (! empty($member_coupon_list)) {
//             $coupon_type_model = new NsCouponTypeModel();
//             foreach ($member_coupon_list as $k => $v) {
//                 $type_info = $coupon_type_model->getInfo([
//                     'coupon_type_id' => $v['coupon_type_id']
//                 ], 'coupon_name,at_least');
//                 $member_coupon_list[$k]['coupon_name'] = $type_info['coupon_name'];
//                 $member_coupon_list[$k]['at_least'] = $type_info['at_least'];
//             }
//         }
		
		// 1.获取当前会员所有优惠券
		$goods_sku_list_array = $data['goods_sku_array'];
		
		// 2.获取当前优惠券是否可用
		if (!empty($member_coupon_list)) {
			foreach ($member_coupon_list as $k => $coupon) {
				// 查询优惠券类型的情况
				$coupon_type = new NsCouponTypeModel();
				$type_info = $coupon_type->getInfo([
					'coupon_type_id' => $coupon['coupon_type_id']
				], 'range_type,at_least,coupon_name,at_least');
				$member_coupon_list[ $k ]['coupon_name'] = $type_info['coupon_name'];
				$member_coupon_list[ $k ]['at_least'] = $type_info['at_least'];
				$member_coupon_list[ $k ]['range_type'] = $type_info['range_type'];
				if ($type_info['range_type'] == 1) {
					// 全场商品使用的优惠券
					if ($type_info['at_least'] <= $data["total_money"]) {
						$coupon_list[] = $coupon;
					}
				} else {
					// 部分商品使用的优惠券
					$coupon_goods = new NsCouponGoodsModel();
					$coupon_goods_list = $coupon_goods->getQuery([
						'coupon_type_id' => $coupon['coupon_type_id']
					]);
					$goods_id_array = [];
					foreach ($coupon_goods_list as $k => $v) {
						$goods_id_array[] = $v['goods_id'];
					}
					$new_total_price = 0;
					
					foreach ($goods_sku_list_array as $k_goods_sku => $v_goods_sku) {
						if (in_array($v_goods_sku["goods_id"], $goods_id_array) !== false) {
							$new_total_price += $v_goods_sku["total_money"];
						}
					}
					
					if ($new_total_price > 0 && $type_info['at_least'] <= $new_total_price) {
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
	 * 查询商品列表可用积分数
	 */
	public function getGoodsSkuListUsePoint($goods_sku_list)
	{
		$point = 0;
		$goods_sku_list_array = explode(",", $goods_sku_list);
		foreach ($goods_sku_list_array as $k => $v) {
			
			$sku_data = explode(':', $v);
			$sku_id = $sku_data[0];
			$goods = new Goods();
			$goods_id = $goods->getGoodsId($sku_id);
			$goods_model = new NsGoodsModel();
			$point_use = $goods_model->getInfo([
				'goods_id' => $goods_id
			], 'point_exchange_type,point_exchange');
			if ($point_use['point_exchange_type'] == 1) {
				$point += $point_use['point_exchange'];
			}
		}
		return $point;
	}
	
	/**
	 * 处理自提地址的排序
	 */
	public function pickupPointListSort($address, $pickup_point_list)
	{
		$arr = array();
		if (!empty($address) && !empty($pickup_point_list)) {
			$district_arr = array();
			$city_arr = array();
			$province_arr = array();
			foreach ($pickup_point_list as $key => $pickup_point) {
				if ($pickup_point["district_id"] == $address["district"]) {
					array_push($district_arr, $pickup_point_list[ $key ]);
					unset($pickup_point_list[ $key ]);
				} elseif ($pickup_point["city_id"] == $address["city"]) {
					array_push($city_arr, $pickup_point_list[ $key ]);
					unset($pickup_point_list[ $key ]);
				} elseif ($pickup_point["province_id"] == $address["province"]) {
					array_push($province_arr, $pickup_point_list[ $key ]);
					unset($pickup_point_list[ $key ]);
				}
			}
			$arr = array_merge($district_arr, $city_arr, $province_arr, $pickup_point_list);
		}
		return $arr;
	}
	
	/**
	 * 组装本地配送时间说明
	 */
	public function getDistributionTime($data)
	{
		$config_service = new Config();
		$distribution_time = $config_service->getDistributionTimeConfig($data["shop_id"]);
		if ($distribution_time == 0) {
			$time_desc = '';
		} else {
			$time_obj = json_decode($distribution_time['value'], true);
			if ($time_obj['morning_start'] != '' && $time_obj['morning_end'] != '') {
				$morning_time_desc = '上午' . $time_obj['morning_start'] . '&nbsp;至&nbsp;' . $time_obj['morning_end'] . '&nbsp;&nbsp;';
			} else {
				$morning_time_desc = '';
			}
			
			if ($time_obj['afternoon_start'] != '' && $time_obj['afternoon_end'] != '') {
				$afternoon_time_desc = '下午' . $time_obj['afternoon_start'] . '&nbsp;至&nbsp;' . $time_obj['afternoon_end'];
			} else {
				$afternoon_time_desc = '';
			}
			$time_desc = $morning_time_desc . $afternoon_time_desc;
		}
		return $time_desc;
	}
	
	/**
	 * 订单数据整理
	 */
	public function dataCollation($data)
	{
	    //TODO:修改订单支付选择顺序。
		$data_result = hook("dataCollation", $data);
		$data_result = arrayFilter($data_result);
		if (!empty($data_result[0])) {
			return $data_result[0];
		} else {
			if ($data["order_type"] > 1) {
				return error([]);
			}
		}
		$shop_data = $this->getOrderShopInfo($data);
		$data = array_merge($data, $shop_data);
		//重组商品列表
		$result_data = $this->getGoodsSkuArray($data);
		if ($result_data["code"] <= 0) {
			return $result_data['data'];
		}
		$data = $result_data["data"];
		
		//支付方式
		$pay_type = [];
		
        if ($data["order_config"]["order_online_pay"] == 1) {
		    array_push($pay_type, ["type_id" => 1,"type_name" => lang("在线支付"), 'sort' => 10]);
		}
		
		$express_type = array();
		
		if ($data["is_virtual"] != 1) {
			//商家物流
			if ($data["order_config"]["seller_dispatching"] == 1) {
				$express_type[] = [
					"type_id" => 1,
					"type_name" => lang("物流配送"),
                    'sort' => 40
				];
				// 是否允许用户选择物流公司
				$express_compnay_list = [];
				if ($data["order_config"]["is_logistics"] == 1) {
					$goods_express_service = new GoodsExpress();
					$express_compnay_list = $goods_express_service->getExpressCompany($data["shop_id"], $data["goods_sku_list"], $data["address"]["province"], $data["address"]["city"], $data["address"]["district"]);
				}
				$data["express_company_list"] = $express_compnay_list;
				$time_slot = [];
				if (!empty($data["order_config"]["time_slot"])) {
					foreach ($data["order_config"]["time_slot"] as $vo) {
						array_push($time_slot, $vo['start'] . ':00-' . $vo['end'] . ':00');
					}
				}
				$data["time_slot"] = $time_slot;
				
			}
			
			//自提点
			if ($data["order_config"]["buyer_self_lifting"] == 1 && addon_is_exit('NsPickup')) {
				$express_type[] = [
					"type_id" => 2,
					"type_name" => lang("门店自提"),
                    'sort' => 50
				];
				$shop_service = new Shop();
				$pickup_point_list = $shop_service->getPickupPointQuery([ "province_id" => $data["address"]["province"], "city_id" => $data["address"]["city"], "district_id" => $data["address"]["district"] ]);
//                 $pickup_point_list["data"] = $this->pickupPointListSort($data["address"], $pickup_point_list["data"]);
				$data["pickup_point_list"] = $pickup_point_list;
			}
			
			// 本地配送
			if ($data["order_config"]["is_open_o2o"] == 1 && addon_is_exit('NsO2o')) {
				//判断是否可以用本地配送
				$distribution_config = new NsO2oDistributionConfigModel();
				$config = $distribution_config->getFirstData([ 'store_id' => $data["shop_id"], 'order_money' => array( 'ELT', $data["total_money"] ), "is_start" => 1 ], 'order_money desc');
				if (!empty($config)) {
					$express_type[] = [
						"type_id" => 3,
						"type_name" => lang("本地配送"),
                        'sort' => 10
					];
					//开启指定配送时间
					if ($data["order_config"]["order_designated_delivery_time"] == 1) {
						$distribution_time = $this->getDistributionTime($data);
						$data['distribution_time'] = $distribution_time;
					}
				}
				
			}
			
			//发票
			$invoice_info = [];
			if($data['order_config']['order_invoice_type']){
    			$invoice_info["order_invoice_tax"] = $data["order_config"]["order_invoice_tax"];
    			$order_invoice_content_list = [];
    			
    			$order_invoice_str = $data["order_config"]["order_invoice_content"];
    			if (!empty($order_invoice_str)) {
    				$order_invoice_array = explode(",", $order_invoice_str);
    				foreach ($order_invoice_array as $v) {
    					if (!empty($v)) {
    						$order_invoice_content_list[] = $v;
    					}
    				}
    			}
    			$invoice_info["order_invoice_content_list"] = $order_invoice_content_list;
			}
			if (!empty($invoice_info["order_invoice_content_list"])) {
				$data["invoice_info"] = $invoice_info;
			}
			
			//货到付款配置
			if ($data["order_config"]["order_delivery_pay"] == 1) {
			    array_push($pay_type, ["type_id" => 4,"type_name" => lang("货到付款"), 'sort' => 20]);
			}
			
		}
		usort($pay_type, function($a, $b){
		    if ($a['sort'] > $b['sort']) return false;
		    return true;
        });
        usort($express_type, function($a, $b){
            if ($a['sort'] > $b['sort']) return false;
            return true;
        });
		$data["express_type"] = $express_type;
		$data["pay_type"] = $pay_type;
		//优惠券
		$data["coupon_list"] = $this->getMemberCouponList($data);
		
		//优惠活动
		$promotion_money = 0;//优惠金额
		$promotion_result = $this->getOrderPromotionArray($data);
		if ($promotion_result["code"] <= 0) {
			return $promotion_result;
		}
		//赠品id数组
		$gift_id_array = [];
		$promotion_array = $promotion_result["data"];
		if (!empty($promotion_array)) {
			foreach ($promotion_array as $promotion_k => $promotion_v) {
				$promotion_money += $promotion_v["discount_money"];
				if ($promotion_v["promotion_type"] == 'MANJIAN') {
					if (!empty($promotion_v["gift_array"])) {
						$gift_id_array = array_merge($gift_id_array, $promotion_v["gift_array"]);
					}
					//是否满足满减送免邮
					if ($promotion_v['rule']['free_shipping'] == 1) {
						$shipping_money = 0;
					}
					
				}
			}
		}
		$data["gift_id_array"] = $gift_id_array;
		//统计赠品
		$gift_goods_array = [];
		if (!empty($gift_id_array)) {
			$gift_goods_array = $this->getOrderGiftList($data);
		}
		$data["gift_array"] = $gift_goods_array;
		
		return $data;
		
	}
	
	/**
	 * 营销活动
	 */
	public function getOrderSkuPromotion($data)
	{
		//营销活动订单数据 promotion_type  1 组合套餐 2 团购  3 砍价   4 积分兑换
		$promotion_order_data = hook("getOrderGoodsSkuArray", $data);
		$promotion_order_data = arrayFilter($promotion_order_data);
		if (!empty($promotion_order_data[0])) {
			return $promotion_order_data[0];
		}
		
		if ($data["promotion_type"] == 4) {
			//如果没有营销活动  判断是否是积分兑换
			$total_price = 0;//总价格
			$total_buy_point = 0;//总积分
			$total_max_buy_point = 0;
			foreach ($data["goods_sku_array"] as $k => $v) {
				//积分  point_exchange_type  0 非积分兑换 1 积分加现金购买 2 积分兑换或直接购买 3 只支持积分兑换
				if ($v["goods_info"]["point_exchange_type"] == 0) {
					return error();
				} else if ($v["goods_info"]["point_exchange_type"] == 1) {
					$buy_point = $v["goods_info"]["point_exchange"];//兑换所需积分
					$sku_price = $v["goods_info"]["price"];//兑换所需金额
				} else if ($v["goods_info"]["point_exchange_type"] == 2 || $v["goods_info"]["point_exchange_type"] == 3) {
					$sku_price = 0;//兑换所需金额
					$buy_point = $v["goods_info"]["point_exchange"];//兑换所需积分
				}
				
				//商品活动价格
				$order_goods_money = sprintf("%.2f", $sku_price * $v["num"]);
				$goods_total_buy_point = $buy_point * $v["num"];
				
				$data["goods_sku_array"][ $k ]["sku_price"] = sprintf("%.2f", $sku_price);
				$data["goods_sku_array"][ $k ]["buy_point"] = $buy_point;//单积分
				$data["goods_sku_array"][ $k ]["total_buy_point"] = $goods_total_buy_point;//订单项总积分
				$data["goods_sku_array"][ $k ]["total_price"] = $order_goods_money;//订单项总金额
				
				$data["goods_sku_array"][ $k ]["total_money"] = $order_goods_money;
				$max_use_point = 0;
				$total_max_buy_point += $max_use_point;
				$data["goods_sku_array"][ $k ]["max_use_point"] = $max_use_point;
				$total_price += $order_goods_money;
				$total_buy_point += $goods_total_buy_point;
				
			}
			$data["max_use_point"] = $total_max_buy_point;
			$data["total_money"] = $total_price;//总金额
			$data["total_buy_point"] = $total_buy_point;//总积分
			$data["point"] = $total_buy_point;//所需积分
		} else if ($data["promotion_type"] == 3) {
			//如果没有营销活动  判断是否是积分兑换
			$total_price = 0;//总价格
			foreach ($data["goods_sku_array"] as $k => $v) {
				$sku_price = $v["goods_sku_info"]["price"];//兑换所需金额
				//商品活动价格
				$order_goods_money = sprintf("%.2f", $sku_price * $v["num"]);
				
				$data["goods_sku_array"][ $k ]["sku_price"] = sprintf("%.2f", $sku_price);
				$data["goods_sku_array"][ $k ]["total_price"] = $order_goods_money;//订单项总金额
				
				$data["goods_sku_array"][ $k ]["total_money"] = $order_goods_money;
				$total_price += $order_goods_money;
				
			}
			$data["total_money"] = $total_price;//总金额
			
		}
		
		return success($data);
		
	}
	
	/**
	 * 获取满减送规则
	 */
	public function getMansongRule($mansong_id)
	{
		$mansong_rule = new NsPromotionMansongRuleModel();
		$rule_list = $mansong_rule->getQuery([
			'mansong_id' => $mansong_id
		], '*', 'price desc');
		return $rule_list;
	}
	
	/**
	 * 赠品优惠营销详情
	 * @param unknown $data
	 */
	public function getOrderPromotionGift($data)
	{
		$temp_array = $data["goods_sku_array"][0];
		
		$temp_array["promotion_price"] = $data["total_money"];
		$temp_array["discount_money"] = $data["total_money"];
		$temp_array["promotion_id"] = $data["promotion_info"]["gift_info"]["gift_records_id"];
		$temp_array["promotion_type"] = 'GIFT';
		$discount_List[] = $temp_array;
		
		$promotion_array[0] = array(
			'promotion_id' => $data["promotion_info"]["gift_info"]["gift_records_id"],
			'promotion_type_id' => 5,
			'promotion_type' => 'GIFT',
			'promotion_condition' => '',
			'promotion_name' => '赠品活动',
			'discount_money' => $data["total_money"],
			'promotion_sku_list' => $discount_List,
			'free_shipping' => 1//是否免邮
		);
		
		return $promotion_array;
	}
	
}