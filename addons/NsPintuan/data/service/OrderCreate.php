<?php
/**
 * Order.php
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

namespace addons\NsPintuan\data\service;

use addons\NsO2o\data\model\NsO2oDistributionConfigModel;
use addons\NsPintuan\data\model\NsPromotionTuangouModel;
use data\model\AlbumPictureModel;
use data\model\NsGoodsModel;
use data\model\NsGoodsSkuModel;
use data\model\NsOrderGoodsModel;
use data\model\NsOrderGoodsPromotionDetailsModel;
use data\model\NsOrderModel;
use data\model\NsOrderPickupModel;
use data\model\NsOrderPromotionDetailsModel;
use data\model\NsPickupPointModel;
use data\service\GoodsCalculate\GoodsCalculate;
use data\service\Member\MemberAccount;
use data\service\Member\MemberCoupon;
use data\service\OrderAction;
use data\service\OrderCreate as OrderCreateService;
use data\service\promotion\GoodsExpress;
use data\service\Shop;

;

/**
 * 拼团订单
 */
class Ordercreate extends OrderCreateService
{
	
	public $order;
	
	
	// 订单主表
	function __construct()
	{
		parent::__construct();
		$this->order = new NsOrderModel();
	}
	
	
	/**
	 * 订单创建
	 * @param unknown $data
	 * @return number|string[]|mixed[]|unknown
	 */
	public function orderCreate($data)
	{
		//订单计算
		$data = $this->orderCalculate($data);
		//判断订单合法性
		if (empty($data)) {
			return error();
		}
		//物流判断
		if ($data["shipping_data"]["code"] <= 0) {
			return $data["shipping_data"];
		}
		
		$this->order->startTrans();
		
		try {
			
			$pintuan_service = new Pintuan();
			//创建团购
			if ($data["promotion_info"]["tuangou_group_id"] <= 0) {
				$pintuan_id = $pintuan_service->tuangouGroupCreate($data);
			} else {
				$pintuan_id = $data["promotion_info"]["tuangou_group_id"];
			}
			
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
				'tuangou_group_id' => $pintuan_id
			);
			
			//创建订单
			$result = $order_model->save($order_data);
			
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
				//库存判断
				// 库存减少销量增加
				$goods_calculate = new GoodsCalculate();
				$res = $goods_calculate->subGoodsStock($data_v['goods_id'], $data_v['sku_id'], $data_v["num"], '');
				
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
			
			//操作日志
			$action_data = array(
				"remark" => "创建订单",
				"uid" => $data['buyer_id'],
				"order_id" => $order_id,
			);
			$order_action = new OrderAction();
			$order_action->addOrderAction($action_data);
			
			$this->orderCreateSuccess($data);
			$this->order->commit();
			return success([ 'order_id' => $order_id, 'out_trade_no' => $out_trade_no ]);
		} catch (\Exception $e) {
			$this->order->rollback();
			return error($e->getMessage());
		}
	}
	
	/**
	 * 订单创建成功
	 * @param $data
	 */
	public function orderCreateSuccessAction($data)
	{
	}
	
	/**
	 * 获取拼团价格
	 *
	 * @param unknown $goods_id
	 * @param unknown $num
	 * @param unknown $tuangou_group_id
	 */
	public function getGoodsPintuanPrice($data)
	{
		$promotion_tuangou = new NsPromotionTuangouModel();
		$tuangou_info = $promotion_tuangou->getInfo([
			'goods_id' => $data["goods_id"]
		], 'tuangou_money,tuangou_type,tuangou_content_json,is_open');
		if (!empty($tuangou_info)) {
			return success([ "money" => $tuangou_info['tuangou_money'] ]);
		} else {
			return error([]);
		}
	}
	
	/**
	 * 组合商品sku数据(订单)
	 * @param string $goods_sku_list
	 * @return array[][]|mixed[][]
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
			//实例化商品service
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
//                 //判断商品库存是否大于购买数量
//                 if ($goods_sku_info['stock'] < $temp_array["num"] || $temp_array["num"] <= 0) {
//                     return error();
//                 }
				$goods_info = $goods_model->getInfo([ "goods_id" => $goods_sku_info["goods_id"] ]);
				
				$temp_array["goods_sku_info"] = $goods_sku_info;
				$temp_array["goods_info"] = $goods_info;
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
				$sku_price_result = $this->getGoodsPintuanPrice($temp_array, $data);
				
				if ($sku_price_result["code"] <= 0) {
					return $sku_price_result;
				}
				
				$sku_price = $sku_price_result["data"]["money"];
				$sku_price = sprintf("%.2f", $sku_price);
				$order_goods_money = sprintf("%.2f", $sku_price * $temp_array["num"]);
				
				$temp_array["total_price"] = $order_goods_money;//订单项总金额
				
				
				$temp_array["sku_price"] = $sku_price;
				$temp_array["total_money"] = $order_goods_money;
				$total_price += $order_goods_money;
				
				//用户已购买 商品数量
				$buyed_num = $this->getOrderGoodsNum($temp_array);
				$temp_array["buyed_num"] = $buyed_num;
				$temp_array['adjust_money'] = 0;
				
				//积分抵现
				$temp_array["max_use_point"] = 0;
				$max_use_point += 0;
				
				$goods_sku_array[] = $temp_array;
				
			}
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
	 * 订单商品计算
	 * @param array $goods_sku_list
	 * @return string|unknown[][]|mixed[][]|array[][]|\think\db\false[][]|PDOStatement[][]|string[][]|\think\Model[][]
	 */
	public function orderCalculate($data)
	{
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
		$promotion_array = $this->getOrderPromotionArray($order_data);
		//赠品id数组
		
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
	 * 订单数据整理
	 * @param unknown $data
	 */
	public function dataCollation($data)
	{
		$shop_data = $this->getOrderShopInfo($data);
		$data = array_merge($data, $shop_data);
		//重组商品列表
		$result_data = $this->getGoodsSkuArray($data);
		if ($result_data["code"] <= 0) {
			return $result_data;
		}
		$data = $result_data["data"];
		//配送
		$pay_type = array(
			[
				"type_id" => 1,
				"type_name" => lang("在线支付"),
                'sort' => 10
			]
		);
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
			if ($data["order_config"]["buyer_self_lifting"] == 1) {
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
			if ($data["order_config"]["is_open_o2o"] == 1) {
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
			$order_invoice_tax_str = $data["order_config"]["order_invoice_tax"];
			$invoice_info["order_invoice_tax"] = $order_invoice_tax_str;
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
			if (!empty($invoice_info["order_invoice_tax"]) && !empty($invoice_info["order_invoice_content_list"])) {
				$data["invoice_info"] = $invoice_info;
			}
			
			//货到付款配置
			if ($data["order_config"]["order_delivery_pay"] == 1) {
				$pay_type[] = array(
					"type_id" => 4,
					"type_name" => lang("货到付款"),
                    'sort' => 20
				);
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
		
		$data["gift_id_array"] = [];
		//统计赠品
		$gift_goods_array = [];
		
		$data["gift_array"] = $gift_goods_array;
		
		return $data;
		
	}
	
}