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

namespace data\service;

/**
 * 订单
 */
use addons\NsO2o\data\model\NsO2oOrderDeliveryModel;
use data\extend\Kd100;
use data\extend\Kdniao;
use data\model\AlbumPictureModel;
use data\model\NsCustomerServiceModel;
use data\model\NsCustomerServiceRecordsModel;
use data\model\NsGoodsEvaluateModel;
use data\model\NsGoodsModel;
use data\model\NsGoodsSkuModel;
use data\model\NsMemberRechargeViewModel;
use data\model\NsOrderActionModel;
use data\model\NsOrderCustomerAccountRecordsModel;
use data\model\NsOrderExpressCompanyModel;
use data\model\NsOrderGoodsExpressModel;
use data\model\NsOrderGoodsModel;
use data\model\NsOrderGoodsPromotionDetailsModel;
use data\model\NsOrderModel;
use data\model\NsOrderPaymentModel;
use data\model\NsOrderPickupModel;
use data\model\NsOrderRefundAccountRecordsModel;
use data\model\NsOrderRefundModel;
use data\model\NsOrderShopReturnModel;
use data\model\NsPickedUpAuditorModel;
use data\service\Config as WebConfig;
use data\service\Goods as GoodsService;
use data\service\GoodsCalculate\GoodsCalculate;
use data\service\Order\Order as OrderService;
use data\service\promotion\GoodsPreference;
use data\model\NsOrderGoodsViewModel;

class OrderQuery extends OrderService
{
	/**
	 * 获取订单详情
	 */
	public function getOrderDetail($order_id)
	{
		// 查询主表信息
		$detail = $this->getDetail($order_id);
		if (empty($detail)) {
			return array();
		}
		$detail['pay_status_name'] = $this->getPayStatusInfo([ "pay_status" => $detail['pay_status'] ])['status_name'];
		$shipping_info = $this->getShippingStatusInfo([ "shipping_status" => $detail['shipping_status'] ]);
		$detail['shipping_status_name'] = $shipping_info['status_name'];
		$shipping_info = $this->getShippingTypeInfo([ "shipping_type" => $detail['shipping_type'] ]);
		$detail['shipping_type_name'] = $shipping_info['type_name'];
		
		if ($detail['shipping_type'] == 3 && $detail['shipping_status'] == 1) {
			$orderDelivery = new NsO2oOrderDeliveryModel();
			$detail['distribution_info'] = $orderDelivery->getInfo([
				"order_id" => $order_id
			], "express_no,order_delivery_user_name,order_delivery_user_mobile,remark");
		}
		
		$express_list = $this->getOrderGoodsExpressList($order_id);
		// 未发货的订单项
		$order_goods_list = array();
		// 已发货的订单项
		$order_goods_delive = array();
		// 没有配送信息的订单项
		$order_goods_exprss = array();
		foreach ($detail["order_goods"] as $order_goods_obj) {
			$shipping_status = $order_goods_obj["shipping_status"];
			if ($shipping_status == 0) {
				// 未发货
				$order_goods_list[] = $order_goods_obj;
			} else {
				$order_goods_delive[] = $order_goods_obj;
			}
		}
		$detail["order_goods_no_delive"] = $order_goods_list;
		// 没有配送信息的订单项
		if (!empty($order_goods_delive) && count($order_goods_delive) > 0) {
			foreach ($order_goods_delive as $goods_obj) {
				$is_have = false;
				$order_goods_id = $goods_obj["order_goods_id"];
				foreach ($express_list as $express_obj) {
					$order_goods_id_array = $express_obj["order_goods_id_array"];
					$goods_id_str = explode(",", $order_goods_id_array);
					if (in_array($order_goods_id, $goods_id_str)) {
						$is_have = true;
					}
				}
				if (!$is_have) {
					$order_goods_exprss[] = $goods_obj;
				}
			}
		}
		$goods_packet_list = array();
		if (count($order_goods_exprss) > 0) {
			$packet_obj = array(
				"packet_name" => "无需物流",
				"express_name" => "",
				"express_code" => "",
				"express_id" => 0,
				"is_express" => 0,
				"order_goods_list" => $order_goods_exprss
			);
			$goods_packet_list[] = $packet_obj;
		}
		if (!empty($express_list) && count($express_list) > 0 && count($order_goods_delive) > 0) {
			$packet_num = 1;
			foreach ($express_list as $express_obj) {
				$packet_goods_list = array();
				$order_goods_id_array = $express_obj["order_goods_id_array"];
				$goods_id_str = explode(",", $order_goods_id_array);
				foreach ($order_goods_delive as $delive_obj) {
					$order_goods_id = $delive_obj["order_goods_id"];
					if (in_array($order_goods_id, $goods_id_str)) {
						$packet_goods_list[] = $delive_obj;
					}
				}
				$packet_obj = array(
					"packet_name" => "包裹  + " . $packet_num,
					"express_name" => $express_obj["express_name"],
					"express_code" => $express_obj["express_no"],
					"express_id" => $express_obj["id"],
					"express_company_id" => $express_obj["express_company_id"],
					"is_express" => 1,
					"order_goods_list" => $packet_goods_list
				);
				$packet_num = $packet_num + 1;
				$goods_packet_list[] = $packet_obj;
			}
		}
		$detail["goods_packet_list"] = $goods_packet_list;
		
		if ($detail['is_virtual'] == 1) {
			
			// 虚拟商品列表
			$virtual_goods = new Goods();
			$virtual_goods_list = $virtual_goods->getVirtualGoodsListByOrderNo($detail['order_no']);
			
			$detail['virtual_goods_list'] = $virtual_goods_list;
		}
		//活动内容
		$detail['promotion_type_info'] = $this->getPrtomotionTypeInfo([ "promotion_type" => $detail['promotion_type'] ]);
		$detail['promotion_type_name'] = $detail['promotion_type_info']["name"];
		//订单类型
		$order_type_info = $this->getOrderTypeInfo([ "order_type" => $detail["order_type"] ]);
		$detail['order_type_name'] = $order_type_info['name'];
		//订单关联信息
		$relation_info_list = hook("getOrderRelationInfo", $detail);
		$relation_info_list = arrayFilter($relation_info_list);
		if (!empty($detail)) {
			foreach ($relation_info_list as $k => $v) {
				$detail[ $v["key"] ] = $v["info"];
			}
		}
		return $detail;
	}
	
	/**
	 * 获取订单基础信息
	 */
	public function getOrderInfo($condition)
	{
		$order_model = new NsOrderModel();
		$order_info = $order_model->getInfo($condition);
		return $order_info;
	}
	
	/**
	 * 查询订单
	 */
	public function orderQuery($where = "", $field = "*")
	{
		$order_model = new NsOrderModel();
		$list = $order_model->getQuery($where, $field);
		return $list;
	}
	
	/**
	 * 查询订单的订单项列表
	 */
	public function getOrderGoods($order_id, $refund = 0)
	{
		$order_goods = new NsOrderGoodsModel();
		
		$where['order_id'] = $order_id;
		if ($refund == 1) {
			$where['refund_status'] = array( '<=', 0 );
		}
		$order_goods_list = $order_goods->getQuery($where);
		
		foreach ($order_goods_list as $k => $v) {
			$order_goods_list[ $k ]['express_info'] = $this->getOrderGoodsExpress($v['order_goods_id']);
			$shipping_status_info = $this->getShippingStatusInfo([ "shipping_status" => $v['shipping_status'] ]);
			$order_goods_list[ $k ]['shipping_status_name'] = $shipping_status_info['status_name'];
			
			// 商品图片
			$picture = new AlbumPictureModel();
			$picture_info = $picture->get($v['goods_picture']);
			$order_goods_list[ $k ]['picture_info'] = $picture_info;
			if ($v['refund_status'] != 0) {
				$order_refund_status_info = $this->getOrderRefundStatusInfo([ "order_type" => 1, "refund_status" => $v['refund_status'] ]);
				
				$order_goods_list[ $k ]['refund_operation'] = $order_refund_status_info['refund_operation'];
				$order_goods_list[ $k ]['status_name'] = $order_refund_status_info['status_name'];
				
			} else {
				$order_goods_list[ $k ]['refund_operation'] = [];
				$order_goods_list[ $k ]['status_name'] = '';
			}
		}
		return $order_goods_list;
	}
	
	/***
	 * 查询订单的订单项列表
	 */
	public function getOrderGoodsInfo($order_goods_id)
	{
		$picture = new AlbumPictureModel();
		$order_goods = new NsOrderGoodsModel();
		$order_goods_info = $order_goods->getInfo([
			'order_goods_id' => $order_goods_id
		], '*');
		
		$order_goods_info['goods_picture'] = $picture->get($order_goods_info['goods_picture'])['pic_cover'];
		return $order_goods_info;
	}
	
	/**
	 * 获取订单列表
	 */
	public function getOrderList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$order_model = new NsOrderModel();
		$sku_model = new NsGoodsSkuModel();
		$goods = new NsGoodsModel();
		// 查询主表
		$order_list = $order_model->pageQuery($page_index, $page_size, $condition, $order, '*');
		$config = new WebConfig();
		$shop_config = $config->getShopConfig(0);
		$order_list['shouhou'] = $shop_config['shouhou_day_number'];
		if (!empty($order_list['data'])) {
			foreach ($order_list['data'] as $k => $v) {
				
				// 查询订单项表
				$order_item = new NsOrderGoodsModel();
				$order_item_list = $order_item->getQuery([
					'order_id' => $v['order_id']
				]);
				foreach ($order_item_list as $key_item => $v_item) {
					$picture = new AlbumPictureModel();
					
					$goods_sku_info = $sku_model->getInfo([ 'sku_id' => $v_item['sku_id'] ]);
					$goods_info = $goods->getInfo([ "goods_id" => $v_item["goods_id"] ]);
					//查询图片
					if (!empty($goods_sku_info['picture'])) {
						$v_item["goods_picture"] = $goods_sku_info['picture'];
					} else {
						$v_item["goods_picture"] = $goods_info['picture'];
					}
					
					$goods_picture = $picture->get($v_item['goods_picture']);
					if (empty($goods_picture)) {
						$goods_picture = array(
							'pic_cover' => '',
							'pic_cover_big' => '',
							'pic_cover_mid' => '',
							'pic_cover_small' => '',
							'pic_cover_micro' => '',
							"upload_type" => 1,
							"domain" => ""
						);
					}
					
					$order_item_list[ $key_item ]['picture'] = $goods_picture;
					
					if (!empty($v_item['refund_status']) && $v_item["gift_flag"] == 0) {
						$order_refund_status_info = $this->getOrderRefundStatusInfo([ "order_type" => 1, "refund_status" => $v_item['refund_status'] ]);
						$order_item_list[ $key_item ]['refund_operation'] = $order_refund_status_info['refund_operation'];
						$order_item_list[ $key_item ]['status_name'] = $order_refund_status_info['status_name'];
						
					} else {
						$order_item_list[ $key_item ]['refund_operation'] = [];
						$order_item_list[ $key_item ]['status_name'] = '';
					}
					
					// 查询是否有售后信息
					$order_item_list[ $key_item ]['customer_info'] = $this->getCustomerServiceInfo(0, $v_item['order_goods_id']);
				}
				
				$order_list['data'][ $k ]['order_item_list'] = $order_item_list;
				$order_list['data'][ $k ]['operation'] = '';
				// 订单来源名称
				$order_from_info = $this->getOrderFormInfo([ "order_form" => $v['order_from'] ]);
				$order_list['data'][ $k ]['order_from_name'] = $order_from_info['type_name'];
				$order_list['data'][ $k ]['order_from_tag'] = $order_from_info['tag'];
				$pay_type_info = $this->getPayTypeInfo([ "pay_type" => $v['payment_type'] ]);
				$order_list['data'][ $k ]['pay_type_name'] = $pay_type_info["type_name"];
				
				$shipping_type_info = $this->getShippingTypeInfo([ "shipping_type" => $order_list['data'][ $k ]['shipping_type'] ]);
				$order_list['data'][ $k ]['shipping_type_name'] = empty($shipping_type_info) ? '' : $shipping_type_info["type_name"];
				
				$order_status_info = $this->getOrderStatusInfo([ "order_type" => $v['order_type'], "order_status" => $v['order_status'], "shipping_type" => $v["shipping_type"] ]);
				
				$order_type_info = $this->getOrderTypeInfo([ "order_type" => $v['order_type'] ]);
				$order_list['data'][ $k ]['order_type_name'] = $order_type_info["name"];
				if ($order_list['data'][ $k ]['payment_type'] == 4 && $order_list['data'][ $k ]['order_status'] != 5) {
					$operation = array(
						'no' => 'close',
						'color' => '#E61D1D',
						'name' => '交易关闭',
						'class_name' => 'ns-bg-color'
					);
					array_push($order_status_info['operation'], $operation);
					
					$member_operation = array(
						'no' => 'close',
						'name' => '关闭订单',
						'color' => '#999999',
						'class_name' => 'ns-bg-color-gray-shade-20'
					);
					array_push($order_status_info['member_operation'], $member_operation);
				}
				if ($order_list['data'][ $k ]['payment_type'] == 4 && $v['pay_status'] == 0 && $v['order_status'] == 2) {
					$operation = array(
						'no' => 'received_payment',
						'color' => '#E61D1D',
						'name' => '收到货款',
						'class_name' => 'ns-bg-color'
					);
					array_push($order_status_info['operation'], $operation);
				}
				$order_list['data'][ $k ]['operation'] = $order_status_info['operation'];
				$order_list['data'][ $k ]['member_operation'] = $order_status_info['member_operation'];
				$order_list['data'][ $k ]['status_name'] = $order_status_info['status_name'];
				$order_list['data'][ $k ]['is_refund'] = $order_status_info['is_refund'];
			}
		}
		return $order_list;
	}
	
	/**
	 * 查询订单中的商品是否有限购，如果有限购，则查询是否有购买记录等
	 */
	public function getGoodsPurchaseRestrictionForOrder($goods_sku_list)
	{
		$array = array();
		$res_array = array();
		$messages = "";
		$ns_goods_sku_model = new NsGoodsSkuModel();
		if (!empty($goods_sku_list)) {
			
			$goods_sku_list_array = explode(",", $goods_sku_list);
			foreach ($goods_sku_list_array as $k => $v) {
				$sku_data = explode(":", $v);
				$sku_id = $sku_data[0];
				$sku_count = $sku_data[1];
				$goods_sku_info = $ns_goods_sku_model->getInfo([
					"sku_id" => $sku_id
				], "goods_id");
				
				if (!empty($goods_sku_info['goods_id'])) {
					array_push($array, $goods_sku_info['goods_id'] . ":" . $sku_count . ":" . $sku_id);
				}
			}
		}
		if (count($array) > 0) {
			$goods_service = new GoodsService();
			$ns_goods_model = new NsGoodsModel();
			foreach ($array as $k => $v) {
				$data = explode(":", $array[ $k ]);
				$goods_id = $data[0];
				$num = $data[1];
				$sku_id = $data[2];
				$goods_name = $ns_goods_model->getInfo([
					'shop_id' => 0,
					"goods_id" => $goods_id
				], "goods_name");
				$sku_name = $ns_goods_sku_model->getInfo([
					'sku_id' => $sku_id
				], "sku_name");
				$res = $goods_service->getGoodsPurchaseRestrictionForCurrentUser($goods_id, $num, "order");
				$res['goods_name'] = $goods_name['goods_name'] . $sku_name['sku_name'];
				array_push($res_array, $res);
			}
		}
		if (count($res_array) > 0) {
			foreach ($res_array as $k => $v) {
				if ($res_array[ $k ]['code'] == 0) {
					$messages = "您购买的商品“" . $res_array[ $k ]['goods_name'] . "”限购" . $res_array[ $k ]['value'] . "件";
					break;
				}
			}
		}
		return $messages;
	}
	
	/**
	 * 统计订单数量
	 */
	public function getOrderCount($condition)
	{
		$order = new NsOrderModel();
		$count = $order->getCount($condition);
		return $count;
	}
	
	/**
	 * 获取待处理售后申请数量
	 */
	public function getCustomerCount($condition)
	{
		$customer = new NsCustomerServiceModel();
		$count = $customer->where($condition)->count();
		return $count;
	}
	
	/**
	 * 统计订单总额
	 */
	public function getPayMoneySum($condition)
	{
		$order_model = new NsOrderModel();
		$money_sum = $order_model->where($condition)->sum('order_money');
		return $money_sum;
	}
	
	/**
	 * 统计订单商品总额
	 */
	public function getGoodsNumSum($condition)
	{
		$order_model = new NsOrderModel();
		$order_list = $order_model->where($condition)->select();
		$goods_sum = 0;
		foreach ($order_list as $k => $v) {
			$order_goods = new NsOrderGoodsModel();
			$goods_sum += $order_goods->where([
				'order_id' => $v['order_id']
			])->sum('num');
		}
		return $goods_sum;
	}
	
	/**
	 * 统计订单各状态数量
	 */
	public static function getOrderStatusNum($condition = [])
	{
		$order = new NsOrderModel();
		$orderStatusNum['all'] = $order->where($condition)->count(); // 全部
		$condition['order_status'] = 0; // 待付款
		$orderStatusNum['wait_pay'] = $order->where($condition)->count();
		$condition['order_status'] = 1; // 待发货
		$orderStatusNum['wait_delivery'] = $order->where($condition)->count();
		$condition['order_status'] = 2; // 待收货
		$orderStatusNum['wait_recieved'] = $order->where($condition)->count();
		$condition['order_status'] = 3; // 已收货
		$orderStatusNum['recieved'] = $order->where($condition)->count();
		$condition['order_status'] = 4; // 交易成功
		$orderStatusNum['success'] = $order->where($condition)->count();
		$condition['order_status'] = 5; // 已关闭
		$orderStatusNum['closed'] = $order->where($condition)->count();
		$condition['order_status'] = 6; // 定金待付款
		$orderStatusNum['deposit_wait_pay'] = $order->where($condition)->count();
		$condition['order_status'] = 7; // 备货中
		$orderStatusNum['instock'] = $order->where($condition)->count();
		$condition['order_status'] = -1; // 退款中
		$orderStatusNum['refunding'] = $order->where($condition)->count();
		$condition['order_status'] = -2; // 已退款
		$orderStatusNum['refunded'] = $order->where($condition)->count();
		$condition['order_status'] = array(
			'in',
			'3,4'
		); // 已收货
		$condition['is_evaluate'] = 0; // 未评价
		$orderStatusNum['wait_evaluate'] = $order->where($condition)->count(); // 待评价
		$condition = [];
		$condition['order_type'] = 4;
		$orderStatusNum['pintuan_order_num'] = $order->where($condition)->count(); // 拼团订单
		$condition['order_type'] = 6;
		$orderStatusNum['presell_order_num'] = $order->where($condition)->count(); // 预售订单
		return $orderStatusNum;
	}
	
	/**
	 * 获取指定订单的评价信息
	 */
	public function getOrderEvaluateByOrder($order_id)
	{
		$goodsEvaluate = new NsGoodsEvaluateModel();
		$condition['order_id'] = $order_id;
		$field = 'order_id, order_no, order_goods_id, goods_id, goods_name, goods_price, goods_image, shop_id, shop_name, content, addtime, image, explain_first, member_name, uid, is_anonymous, scores, again_content, again_addtime, again_image, again_explain';
		$list = $goodsEvaluate->getQuery($condition, $field, 'order_goods_id ASC');
		return $list;
	}
	
	/**
	 * 获取指定会员的评价信息
	 */
	public function getOrderEvaluateByMember($uid)
	{
		$goodsEvaluate = new NsGoodsEvaluateModel();
		$condition['uid'] = $uid;
		$field = 'order_id, order_no, order_goods_id, goods_id, goods_name, goods_price, goods_image, shop_id, shop_name, content, addtime, image, explain_first, member_name, uid, is_anonymous, scores, again_content, again_addtime, again_image, again_explain';
		return $goodsEvaluate->getQuery($condition, $field, 'order_goods_id ASC');
	}
	
	/**
	 * 评价信息 分页
	 */
	public function getOrderEvaluateDataList($page_index, $page_size, $condition, $order)
	{
		$goodsEvaluate = new NsGoodsEvaluateModel();
		$list = $goodsEvaluate->pageQuery($page_index, $page_size, $condition, $order, "*");
		return $list;
	}
	
	/**
	 * 获取评价列表
	 */
	public function getOrderEvaluateList($page_index, $page_size, $condition, $order)
	{
		$goodsEvaluate = new NsGoodsEvaluateModel();
		$field = 'order_id, order_no, order_goods_id, goods_id, goods_name, goods_price, goods_image, shop_id, shop_name, content, addtime, image, explain_first, member_name, uid, is_anonymous, scores, again_content, again_addtime, again_image, again_explain';
		
		$list = $goodsEvaluate->pageQuery($page_index, $page_size, $condition, $order, $field);
		return $list;
	}
	
	/**
	 * 店铺订单账户统计列表
	 */
	public function getShopOrderAccountList($shop_id, $start_time, $end_time, $page_index, $page_size)
	{
		$order_model = new NsOrderModel();
		$condition["create_time"] = [
			[
				">=",
				getTimeTurnTimeStamp($start_time)
			],
			[
				"<=",
				getTimeTurnTimeStamp($end_time)
			]
		];
		$condition['order_status'] = array( 'NEQ', 0 );
		$condition['order_status'] = array( 'NEQ', 5 );
		if ($shop_id != 0) {
			$condition['shop_id'] = array( 'NEQ', 0 );
		}
		$list = $order_model->pageQuery($page_index, $page_size, $condition, 'create_time desc', '*');
		return $list;
	}
	
	/**
	 * 获取在一段时间之内订单收入明细表
	 */
	public function getShopOrderSumList($shop_id, $start_time, $end_time, $page_index, $page_size)
	{
		$order_model = new NsOrderModel();
		$condition["create_time"] = [
			[
				">=",
				getTimeTurnTimeStamp($start_time)
			],
			[
				"<=",
				getTimeTurnTimeStamp($end_time)
			]
		];
		$condition['order_status'] = array( 'NEQ', 0 );
		$condition['order_status'] = array( 'NEQ', 5 );
		if ($shop_id != 0) {
			$condition['shop_id'] = array( 'NEQ', 0 );
		}
		$list = $order_model->pageQuery($page_index, $page_size, $condition, 'create_time desc', '*');
		return $list;
		
	}
	
	/**
	 * 获取订单在一段时间之内退款列表
	 */
	public function getShopOrderRefundList($shop_id, $start_time, $end_time, $page_index, $page_size)
	{
		$order_model = new NsOrderModel();
		$condition["create_time"] = [
			[
				">=",
				getTimeTurnTimeStamp($start_time)
			],
			[
				"<=",
				getTimeTurnTimeStamp($end_time)
			]
		];
		$condition['order_status'] = array( 'NEQ', 0 );
		$condition['order_status'] = array( 'NEQ', 5 );
		$condition['refund_money'] = array( 'GT', 0 );
		if ($shop_id != 0) {
			$condition['shop_id'] = array( 'NEQ', 0 );
		}
		$list = $order_model->pageQuery($page_index, $page_size, $condition, 'create_time desc', '*');
		return $list;
	}
	
	/**
	 * 得到订单走势
	 */
	public function getShopOrderStatics($shop_id, $start_time, $end_time)
	{
		$order_sum = $this->getShopOrderSum($shop_id, $start_time, $end_time);
		$order_refund_sum = $this->getShopOrderSumRefund($shop_id, $start_time, $end_time);
		$order_sum_account = $order_sum - $order_refund_sum;
		$array = array(
			'order_sum' => $order_sum,
			'order_refund_sum' => $order_refund_sum,
			'order_account' => $order_sum_account
		);
		return $array;
	}
	
	/**
	 * 获取店铺在一段时间之内退款统计
	 */
	public function getShopOrderSumRefund($shop_id, $start_time, $end_time)
	{
		$order_model = new NsOrderModel();
		$condition["create_time"] = [
			[
				">=",
				getTimeTurnTimeStamp($start_time)
			],
			[
				"<=",
				getTimeTurnTimeStamp($end_time)
			]
		];
		$condition['order_status'] = array( 'not in', '0,5' );
		if ($shop_id != 0) {
			$condition['shop_id'] = array( 'NEQ', 0 );
		}
		$order_sum = $order_model->getSum($condition, 'refund_money');
		return $order_sum;
		
	}
	
	/**
	 * 获取一段时间之内店铺订单支付统计
	 */
	public function getShopOrderSum($shop_id, $start_time, $end_time)
	{
		$order_model = new NsOrderModel();
		$condition["create_time"] = [
			[
				">=",
				getTimeTurnTimeStamp($start_time)
			],
			[
				"<=",
				getTimeTurnTimeStamp($end_time)
			]
		];
		$condition['order_status'] = array( 'NEQ', 0 );
		$condition['order_status'] = array( 'NEQ', 5 );
		if ($shop_id != 0) {
			$condition['shop_id'] = array( 'NEQ', 0 );
		}
		$order_sum = $order_model->getSum($condition, 'pay_money');
		if (!empty($order_sum)) {
			return $order_sum;
		} else {
			return 0;
		}
	}
	
	/**
	 * 统计账户详情
	 */
	public function getShopOrderAccountDetail($shop_id)
	{
		// 获取总销售统计
		$account_all = $this->getShopOrderStatics($shop_id, '2015-1-1', '3050-1-1');
		// 获取今日销售统计
		$date_day_start = date("Y-m-d", time());
		$date_day_end = date("Y-m-d H:i:s", time());
		$account_day = $this->getShopOrderStatics($shop_id, $date_day_start, $date_day_end);
		// 获取周销售统计（7天）
		$date_week_start = date('Y-m-d', strtotime('-7 days'));
		$date_week_end = $date_day_end;
		$account_week = $this->getShopOrderStatics($shop_id, $date_week_start, $date_week_end);
		// 获取月销售统计(30天)
		$date_month_start = date('Y-m-d', strtotime('-30 days'));
		$date_month_end = $date_day_end;
		$account_month = $this->getShopOrderStatics($shop_id, $date_month_start, $date_month_end);
		$array = array(
			'day' => $account_day,
			'week' => $account_week,
			'month' => $account_month,
			'all' => $account_all
		);
		return $array;
	}
	
	/**
	 * 账户统计
	 */
	public function getShopAccountCountInfo($shop_id)
	{
		// 本月第一天
		$date_month_start = getTimeTurnTimeStamp(date('Y-m-d', strtotime('-30 days')));
		$date_month_end = getTimeTurnTimeStamp(date("Y-m-d H:i:s", time()));
		// 下单金额
		$condition["create_time"] = [
			[
				">=",
				$date_month_start
			],
			[
				"<=",
				$date_month_end
			]
		];
		$condition['order_status'] = array(
			'NEQ',
			0
		);
		$condition['order_status'] = array(
			'NEQ',
			5
		);
		if ($shop_id != 0) {
			$condition['shop_id'] = array(
				'NEQ',
				0
			);
		}
		$order_money = $this->getShopSaleSum($condition);
		// 下单会员
		$order_user_num = $this->getShopSaleUserSum($condition);
		// 下单量
		$order_num = $this->getShopSaleNumSum($condition);
		// 下单商品数
		$order_goods_num = $this->getShopSaleGoodsNumSum($condition);
		// 平均客单价
		if ($order_user_num > 0) {
			$user_money_average = $order_money / $order_user_num;
		} else {
			$user_money_average = 0;
		}
		// 平均价格
		if ($order_goods_num > 0) {
			$goods_money_average = $order_money / $order_goods_num;
		} else {
			$goods_money_average = 0;
		}
		$array = array(
			"order_money" => sprintf('%.2f', $order_money),
			"order_user_num" => $order_user_num,
			"order_num" => $order_num,
			"order_goods_num" => $order_goods_num,
			"user_money_average" => sprintf('%.2f', $user_money_average),
			"goods_money_average" => sprintf('%.2f', $goods_money_average)
		);
		return $array;
	}
	
	/**
	 * 查询一段时间内下单商品数
	 */
	public function getShopSaleGoodsNumSum($condition)
	{
		$order_model = new NsOrderModel();
		$order_list = $order_model->where($condition)->select();
		$order_string = "";
		$goods_num = 0;
		foreach ($order_list as $k => $v) {
			$order_id = $v["order_id"];
			$order_string = $order_string . "," . $order_id;
		}
		
		if ($order_string != '') {
			$order_string = substr($order_string, 1);
			$order_goods_model = new NsOrderGoodsModel();
			$condition = array(
				'order_id' => array( 'in', $order_string )
			);
			$goods_num = $order_goods_model->getSum($condition, "num");
		}
		if (!empty($goods_num)) {
			return $goods_num;
		} else {
			return 0;
		}
	}
	
	/**
	 * 查询一段时间下单量
	 */
	public function getShopSaleNumSum($condition)
	{
		$order_model = new NsOrderModel();
		$order_sum = $order_model->getCount($condition);
		if (!empty($order_sum)) {
			return $order_sum;
		} else {
			return 0;
		}
	}
	
	/**
	 * 查询一点时间下单用户
	 */
	public function getShopSaleUserSum($condition)
	{
		$order_model = new NsOrderModel();
		$order_sum = $order_model->distinct(true)->field('buyer_id')->where($condition)->select();
		if (!empty($order_sum)) {
			return count($order_sum);
		} else {
			return 0;
		}
	}
	
	/**
	 * 查询一段时间下单量
	 */
	public function getShopSaleSum($condition)
	{
		$order_model = new NsOrderModel();
		$order_sum = $order_model->getSum($condition, 'pay_money');
		if (!empty($order_sum)) {
			return $order_sum;
		} else {
			return 0;
		}
	}
	
	/**
	 * 商品销售统计 列表
	 */
	public function getShopGoodsSalesList($page_index = 1, $page_size = 0, $condition = [], $order = '')
	{
		$goods_model = new NsGoodsModel();
		$tmp_array = $condition;
		if (!empty($condition["order_status"])) {
			$order_condition["order_status"] = $condition["order_status"];
			unset($tmp_array["order_status"]);
		}
		$goods_list = $goods_model->pageQuery($page_index, $page_size, $tmp_array, $order, '*');
		// 条件
		$start_date = getTimeTurnTimeStamp(date('Y-m-d', strtotime('-30 days')));
		$end_date = getTimeTurnTimeStamp(date("Y-m-d H:i:s", time()));
		$order_condition['create_time'] = [
			'between',
			[
				$start_date,
				$end_date
			]
		];
		
		$order_condition["shop_id"] = $condition["shop_id"];
		$goods_calculate = new GoodsCalculate();
		// 得到条件内的订单项
		$order_goods_list = $goods_calculate->getOrderGoodsSelect($order_condition);
		// 遍历商品
		foreach ($goods_list["data"] as $k => $v) {
			$data = array();
			$goods_sales_num = $goods_calculate->getGoodsSalesNum($order_goods_list, $v["goods_id"]);
			$goods_sales_money = $goods_calculate->getGoodsSalesMoney($order_goods_list, $v["goods_id"]);
			$data["sales_num"] = $goods_sales_num;
			$data["sales_money"] = $goods_sales_money;
			$goods_list["data"][ $k ]["sales_info"] = $data;
		}
		return $goods_list;
	}
	
	/**
	 * 销售商品
	 */
	public function getShopGoodsSalesQuery($shop_id, $start_date, $end_date, $condition)
	{
		// 商品
		$goods_model = new NsGoodsModel();
		$goods_list = $goods_model->getQuery($condition);
		// 订单项
		$condition['create_time'] = [
			'between',
			[
				$start_date,
				$end_date
			]
		];
		$order_condition["create_time"] = [
			[
				">=",
				$start_date
			],
			[
				"<=",
				$end_date
			]
		];
		$order_condition['order_status'] = array(
			'NEQ',
			0
		);
		$order_condition['order_status'] = array(
			'NEQ',
			5
		);
		if ($shop_id != '') {
			$order_condition["shop_id"] = $shop_id;
		}
		$goods_calculate = new GoodsCalculate();
		$order_goods_list = $goods_calculate->getOrderGoodsSelect($order_condition);
		// 遍历商品
		foreach ($goods_list as $k => $v) {
			$goods_sales_num = $goods_calculate->getGoodsSalesNum($order_goods_list, $v["goods_id"]);
			$goods_sales_money = $goods_calculate->getGoodsSalesMoney($order_goods_list, $v["goods_id"]);
			$goods_list[ $k ]["sales_num"] = $goods_sales_num;
			$goods_list[ $k ]["sales_money"] = $goods_sales_money;
		}
		return $goods_list;
	}
	
	public function getOrderGoodsExpressDetail($order_ids)
	{
		$order_goods_model = new NsOrderGoodsModel();
		$order_model = new NsOrderModel();
		$order_goods_express = new NsOrderGoodsExpressModel();
		// 查询订单的订单项的商品信息
		$order_goods_list = $order_goods_model->where(" order_id in ($order_ids)")->select();
		
		for ($i = 0; $i < count($order_goods_list); $i++) {
			$order_id = $order_goods_list[ $i ]["order_id"];
			$order_goods_id = $order_goods_list[ $i ]["order_goods_id"];
			$order_obj = $order_model->get($order_id);
			$order_goods_list[ $i ]["order_no"] = $order_obj["order_no"];
			$goods_express_obj = $order_goods_express->where("FIND_IN_SET($order_goods_id,order_goods_id_array)")->select();
			if (!empty($goods_express_obj)) {
				$order_goods_list[ $i ]["express_company"] = $goods_express_obj[0]["express_company"];
				$order_goods_list[ $i ]["express_no"] = $goods_express_obj[0]["express_no"];
			} else {
				$order_goods_list[ $i ]["express_company"] = "";
				$order_goods_list[ $i ]["express_no"] = "";
			}
		}
		return $order_goods_list;
	}
	
	/**
	 * 通过订单id 得到 该订单的发货物流
	 */
	public function getOrderGoodsExpressList($order_id)
	{
		$order_goods_express_model = new NsOrderGoodsExpressModel();
		$express_list = $order_goods_express_model->getQuery([
			"order_id" => $order_id
		]);
		return $express_list;
	}
	
	/**
	 * 获取订单项的物流信息
	 */
	private function getOrderGoodsExpress($order_goods_id)
	{
		$order_goods = new NsOrderGoodsModel();
		$order_goods_info = $order_goods->getInfo([
			'order_goods_id' => $order_goods_id
		], 'order_id,shipping_status');
		if ($order_goods_info['shipping_status'] == 0) {
			return null;
		} else {
			$order_express_list = $this->getOrderExpress($order_goods_info['order_id']);
			foreach ($order_express_list as $k => $v) {
				$order_goods_id_array = explode(",", $v['order_goods_id_array']);
				if (in_array($order_goods_id, $order_goods_id_array)) {
					return $v;
				}
			}
			return null;
		}
	}
	
	/**
	 * 获取订单的物流信息
	 */
	public function getOrderExpress($order_id)
	{
		$order_goods_express = new NsOrderGoodsExpressModel();
		$order_express_list = $order_goods_express->all([
			'order_id' => $order_id
		]);
		return $order_express_list;
	}
	
	/**
	 * 查询订单项的物流信息
	 */
	public function getOrderGoodsExpressMessage($express_id)
	{
		try {
			$order_express_model = new NsOrderGoodsExpressModel();
			$express_obj = $order_express_model->get($express_id);
			if (!empty($express_obj)) {
				$order_id = $express_obj["order_id"];
				$order_model = new NsOrderModel();
				// 订单编号
				$order_obj = $order_model->get($order_id);
				$order_no = $order_obj["order_no"];
				$shop_id = $order_obj["shop_id"];
				// 物流公司信息
				$express_company_id = $express_obj["express_company_id"];
				$express_company_model = new NsOrderExpressCompanyModel();
				$express_company_obj = $express_company_model->get($express_company_id);
				// 快递公司编号
				$express_no = $express_company_obj["express_no"];
				// 快递单号
				$send_no = $express_obj["express_no"];
				
				// 订单操作
				$order_time_arr = $order_model->getInfo([
					'order_id' => $express_obj["order_id"]
				], "create_time,pay_time,consign_time");
				$retval = array();
				if (!empty($order_time_arr['create_time'])) {
					$retval[0] = array(
						"AcceptTime" => date("Y-m-d H:i:s", $order_time_arr['create_time']),
						"AcceptStation" => "您的订单已提交，请尽快完成支付"
					);
					if (!empty($order_time_arr['pay_time'])) {
						$retval[1] = array(
							"AcceptTime" => date("Y-m-d H:i:s", $order_time_arr['pay_time']),
							"AcceptStation" => "您的订单已支付完成，请等待卖家发货"
						);
						if (!empty($order_time_arr['consign_time'])) {
							$retval[2] = array(
								"AcceptTime" => date("Y-m-d H:i:s", $order_time_arr['consign_time']),
								"AcceptStation" => "您的订单已发货"
							);
						}
					}
				}
				
				// 快递接口配置
				$config = new Config();
				$express_config = $config->getOrderExpressMessageConfig($shop_id);
				if ($express_config["is_use"] == 0) {
					return array(
						"Success" => false,
						"Reason" => "未启用物流查询"
					);
				} else {
					$result = $this->getThirdPartyExpressMessage($express_config["value"], $shop_id, $order_no, $express_no, $send_no);
					if ($result["Success"]) {
						if ($express_config["value"]["type"] == 1) {
							foreach ($result["Traces"] as $val) {
								$value = array(
									"AcceptTime" => $val["AcceptTime"],
									"AcceptStation" => $val["AcceptStation"]
								);
								array_push($retval, $value);
							}
						} else {
							foreach ($result["content"] as $val) {
								$value = array(
									"AcceptTime" => $val["time"],
									"AcceptStation" => $val["context"]
								);
								array_push($retval, $value);
							}
						}
						return array(
							"Success" => true,
							"Reason" => $result["Reason"],
							"Traces" => array_reverse($retval)
						);
					} else {
						return $result;
					}
				}
			} else {
				return array(
					"Success" => false,
					"Reason" => "订单物流信息有误!"
				);
			}
		} catch (\Exception $e) {
			return array(
				"Success" => false,
				"Reason" => "订单物流信息有误!"
			);
		}
	}
	
	/**
	 * 调用第三方接口获取物流信息
	 * @param unknown $config 物流跟踪配置
	 * @param unknown $shop_id 店铺id
	 * @param unknown $order_no 订单号
	 * @param unknown $express_no 物流公司编号
	 * @param unknown $send_no 快递单号
	 */
	public function getThirdPartyExpressMessage($config, $shop_id, $order_no, $express_no, $send_no)
	{
		switch ($config["type"]) {
			case 1:
				// 快递鸟
				$kdniao = new Kdniao($shop_id);
				$data = array(
					"OrderCode" => $order_no,
					"ShipperCode" => $express_no,
					"LogisticCode" => $send_no
				);
				$result = $kdniao->getOrderTracesByJson(json_encode($data));
				$result = json_decode($result, true);
				break;
			case 2:
				// 快递100免费版
				$kd100 = new Kd100($shop_id);
				$result = $kd100->getExpressTracesFreeEdition($express_no, $send_no, "asc");
				break;
			case 3:
				// 快递100企业版
				$kd100 = new Kd100($shop_id);
				$result = $kd100->getExpressTracesEnterpriseEdition($express_no, $send_no);
				break;
		}
		return $result;
	}
	
	/**
	 * 获取订单备注信息
	 */
	public function getOrderSellerMemo($order_id)
	{
		$order = new NsOrderModel();
		$res = $order->getQuery([
			'order_id' => $order_id
		], "seller_memo");
		$seller_memo = "";
		if (!empty($res[0]['seller_memo'])) {
			$seller_memo = $res[0]['seller_memo'];
		}
		return $seller_memo;
	}
	
	/**
	 * 得到订单的收货地址
	 */
	public function getOrderReceiveDetail($order_id)
	{
		$order = new NsOrderModel();
		$res = $order->getInfo([
			'order_id' => $order_id
		], "order_id,receiver_mobile,receiver_province,receiver_city,receiver_district,receiver_address,receiver_zip,receiver_name,fixed_telephone");
		return $res;
	}
	
	/**
	 * 获取自提点运费
	 */
	public function getPickupMoney($goods_sku_list_price)
	{
		$goods_preference = new GoodsPreference();
		$pick_money = $goods_preference->getPickupMoney($goods_sku_list_price);
		return $pick_money;
	}
	
	/**
	 * 订单数量
	 */
	public function getOrderNumByOrderStatu($condition)
	{
		$order = new NsOrderModel();
		$num = $order->getCount($condition);
		return $num;
	}
	
	/**
	 * 查询会员的某个订单的条数
	 */
	public function getUserOrderDetailCount($user_id, $order_id)
	{
		$orderModel = new NsOrderModel();
		$condition = array(
			"buyer_id" => $user_id,
			"order_id" => $order_id
		);
		$order_count = $orderModel->getCount($condition);
		return $order_count;
	}
	
	/**
	 * 查询会员某个条件的订单的条数
	 */
	public function getUserOrderCountByCondition($condition)
	{
		$orderModel = new NsOrderModel();
		$order_count = $orderModel->getCount($condition);
		return $order_count;
	}
	
	/**
	 * 查询会员某个条件下的订单商品数量
	 */
	public function getUserOrderGoodsCountByCondition($condition)
	{
		$order_goods = new NsOrderGoodsModel();
		$order_count = $order_goods->getCount($condition);
		return $order_count;
	}
	
	/**
	 * 获取订单项实际可退款余额
	 */
	public function orderGoodsRefundBalance($order_goods_id)
	{
		$order_goods = new NsOrderGoodsModel();
		$order_goods_info = $order_goods->getInfo([
			'order_goods_id' => $order_goods_id
		], 'order_id,sku_id,goods_money,point_exchange_type,refund_status');
		$order_goods_promotion = new NsOrderGoodsPromotionDetailsModel();
		$promotion_money = $order_goods_promotion->where([
			'order_id' => $order_goods_info['order_id'],
			'sku_id' => $order_goods_info['sku_id']
		])->sum('discount_money');
		if (empty($promotion_money)) {
			$promotion_money = 0;
		}
		$money = $order_goods_info['goods_money'] - $promotion_money;
		// 计算其他方式支付金额
		$order = new NsOrderModel();
		$order_other_pay_money = $order->getInfo([
			'order_id' => $order_goods_info['order_id']
		], 'order_money,point_money,user_money,coin_money,user_platform_money,tax_money,shipping_money,pay_money,order_type');
		
		$order_goods_real_money = $order_other_pay_money['order_money'] - $order_other_pay_money['shipping_money'] - $order_other_pay_money['tax_money'];
		
		//如果该订单为积分兑换
		if ($order_goods_info["point_exchange_type"] == 2 && $order_other_pay_money['pay_money'] == 0 || $order_goods_info["point_exchange_type"] == 3) {
			$money = 0;
		}
		
		if ($order_goods_real_money != 0 && $money != 0) {
			$refund_balance = $money / $order_goods_real_money * $order_other_pay_money['user_platform_money'];
			if ($refund_balance < 0) {
				$refund_balance = 0;
			}
		} else {
			$refund_balance = 0;
		}
		
		if ($refund_balance > $order_other_pay_money['shipping_money']) {
			$refund_balance -= $order_other_pay_money['shipping_money'];
		}
		
		$order_info = $this->order->getInfo([ "order_type" => $order_goods_info['order_id'] ]);
		//特殊订单金额计算
		$hook_result = hook("getOrderGoodsRefundBanlance", [ "order_type" => $order_info["order_type"], "order_id" => $order_info["order_type"] ]);
		$hook_result = arrayFilter($hook_result);
		if (!empty($hook_result[0])) {
			$refund_balance = $hook_result[0];
		}
		
		$freight = $this->getOrderRefundFreight($order_goods_id);
		
		if ($freight > 0 && $order_other_pay_money["user_platform_money"] >= $freight) {
			$refund_balance += $freight;
			
		}
		return $refund_balance;
	}
	
	/**
	 * 查询店铺的退货设置
	 */
	public function getShopReturnSet($shop_id)
	{
		$shop_return = new NsOrderShopReturnModel();
		$shop_return_obj = $shop_return->get($shop_id);
		if (empty($shop_return_obj)) {
			$data = array(
				"shop_id" => $shop_id,
				"create_time" => time()
			);
			$shop_return->save($data);
			$shop_return_obj = $shop_return->get($shop_id);
		}
		return $shop_return_obj;
	}
	
	/**
	 * 获取订单项实际可退款金额
	 */
	public function orderGoodsRefundMoney($order_goods_id)
	{
		$order_goods = new NsOrderGoodsModel();
		$order_goods_info = $order_goods->getInfo([
			'order_goods_id' => $order_goods_id
		], 'order_id,sku_id,goods_money,point_exchange_type,refund_status');
		$order_goods_promotion = new NsOrderGoodsPromotionDetailsModel();
		$promotion_money = $order_goods_promotion->where([
			'order_id' => $order_goods_info['order_id'],
			'sku_id' => $order_goods_info['sku_id']
		])->sum('discount_money');
		if (empty($promotion_money)) {
			$promotion_money = 0;
		}
		$money = $order_goods_info['goods_money'] - $promotion_money;
		// 计算其他方式支付金额
		$order = new NsOrderModel();
		$order_other_pay_money = $order->getInfo([
			'order_id' => $order_goods_info['order_id']
		], 'order_money,point_money,user_money,coin_money,user_platform_money,tax_money,shipping_money,pay_money,order_type');
		
		//如果该订单为积分兑换
		if ($order_goods_info["point_exchange_type"] == 2 && $order_other_pay_money['pay_money'] == 0 || $order_goods_info["point_exchange_type"] == 3) {
			$money = 0;
		}
		
		$all_other_pay_money = $order_other_pay_money['point_money'] + $order_other_pay_money['user_money'] + $order_other_pay_money['coin_money'] + $order_other_pay_money['user_platform_money'] - $order_other_pay_money['tax_money'];
		if ($all_other_pay_money != 0 && $money > 0) {
			if ($order_other_pay_money['user_platform_money'] > $order_other_pay_money['shipping_money']) {
				$all_other_pay_money -= $order_other_pay_money['shipping_money'];
			}
			$other_pay = $money / ($order_other_pay_money['order_money'] - $order_other_pay_money['shipping_money'] - $order_other_pay_money['tax_money']) * $all_other_pay_money;
			$money = $money - round($other_pay, 2);
		}
		if ($money < 0) {
			$money = 0;
		}
		
		$order_info = $this->order->getInfo([ "order_type" => $order_goods_info['order_id'] ]);
		//特殊订单金额计算
		$hook_result = hook("getOrderGoodsRefundMoney", [ "order_type" => $order_info["order_type"], "order_id" => $order_info["order_type"] ]);
		$hook_result = arrayFilter($hook_result);
		if (!empty($hook_result[0])) {
			$money = $hook_result[0];
		}
		
		$freight = $this->getOrderRefundFreight($order_goods_id);
		if ($freight > 0 && $order_other_pay_money["user_platform_money"] < $freight) {
			$money += $freight;
		}
		
		return $money;
	}
	
	/**
	 * 查询订单项退款信息
	 */
	public function getOrderGoodsRefundInfo($order_goods_id)
	{
		// 查询基础信息
		$order = new NsOrderModel();
		$order_goods = new NsOrderGoodsModel();
		$order_goods_info = $order_goods->get($order_goods_id);
		
		// 商品图片
		$picture = new AlbumPictureModel();
		$picture_info = $picture->get($order_goods_info['goods_picture']);
		$order_goods_info['picture_info'] = $picture_info;
		if ($order_goods_info['refund_status'] != 0) {
			
			$refund_status_info = $this->getOrderRefundStatusInfo([ "refund_status" => $order_goods_info['refund_status'] ]);
			$order_goods_info['refund_operation'] = $refund_status_info['refund_operation'];
			$order_goods_info['status_name'] = $refund_status_info['status_name'];
			// 查询订单项的操作日志
			$order_refund = new NsOrderRefundModel();
			$refund_info = $order_refund->getQuery([
				'order_goods_id' => $order_goods_id
			]);
			$order_goods_info['refund_info'] = $refund_info;
		} else {
			$order_goods_info['refund_operation'] = null;
			$order_goods_info['status_name'] = '';
			$order_goods_info['refund_info'] = null;
		}
		$order_info = $order->getInfo([ 'order_id' => $order_goods_info['order_id'] ], 'order_type,order_status');
		$order_goods_info['order_type'] = $order_info['order_type'];
		$order_goods_info['order_status'] = $order_info['order_status'];
		return $order_goods_info;
	}
	
	/**
	 * 根据外部交易号查询订单编号，为了兼容多店版。所以返回一个数组
	 */
	public function getOrderNoByOutTradeNo($out_trade_no)
	{
		if (!empty($out_trade_no)) {
			$order_model = new NsOrderModel();
			$info = $order_model->getInfo([ 'out_trade_no' => $out_trade_no ], 'order_no');
			return $info;
		}
		return [ "order_no" => "" ];
	}
	
	/**
	 * 根据外部交易号查询订单状态
	 */
	public function getOrderStatusByOutTradeNo($out_trade_no)
	{
		$order_model = new NsOrderModel();
		$order_status = $order_model->getInfo([
			'out_trade_no' => $out_trade_no
		], 'order_status');
		return $order_status;
		
	}
	
	/**
	 * 根据订单查询付款方式，用于进行退款操作时，选择退款方式
	 */
	public function getTermsOfPaymentByOrderId($order_id)
	{
		if (!empty($order_id)) {
			$order_model = new NsOrderModel();
			$order_info = $order_model->getInfo([ 'order_id' => $order_id ], "out_trade_no,pay_money,order_type");
			
			// 如果订单实际支付金额为0，或者订单为预售订单 则只能进行线下
			if ($order_info['pay_money'] == 0 || $order_info['order_type'] == 6) {
				return 10; // 线下退款id为10
			}
			// 准确的查询出付款方式
			$ns_order_payment_model = new NsOrderPaymentModel();
			$pay_type = $ns_order_payment_model->getInfo([ "out_trade_no" => $order_info['out_trade_no'] ], 'pay_type');
			return $pay_type['pay_type'];
		}
		return 0;
	}
	
	/**
	 * 根据订单项id查询订单退款账户记录
	 */
	public function getOrderRefundAccountRecordsByOrderGoodsId($order_goods_id)
	{
		$model = new NsOrderRefundAccountRecordsModel();
		$info = $model->getInfo([
			"order_goods_id" => $order_goods_id
		], "*");
		return $info;
	}
	
	/**
	 * 获取快递单打印内容
	 */
	public function getOrderPrint($order_ids, $shop_id)
	{
		$order_goods_model = new NsOrderGoodsModel();
		$order_goods_express = new NsOrderGoodsExpressModel();
		// 查询订单的订单项的商品信息
		$order_id_array = explode(',', $order_ids);
		$order_goods_list = array();
		foreach ($order_id_array as $order_id) {
			$order_express_list = $order_goods_express->getQuery([
				"order_id" => $order_id
			]);
			if (!empty($order_express_list) && count($order_express_list) > 0) {
				$express_order_goods_ids = "";
				foreach ($order_express_list as $order_express_obj) {
					$order_goods_id_array = $order_express_obj["order_goods_id_array"];
					if (!empty($express_order_goods_ids)) {
						$express_order_goods_ids .= "," . $order_goods_id_array;
					} else {
						$express_order_goods_ids = $order_goods_id_array;
					}
					$order_goods_list_print = $order_goods_model->where("FIND_IN_SET(order_goods_id, '$order_goods_id_array') and order_id=$order_id and shop_id=$shop_id")->select();
					$order_print_item = $this->dealPrintOrderGoodsList($order_id, $order_goods_list_print, 1, $order_express_obj["express_company_id"], $order_express_obj["express_name"], $order_express_obj["express_no"], $order_express_obj["id"]);
					$order_goods_list[] = $order_print_item;
				}
				$order_goods_list_print = $order_goods_model->where("FIND_IN_SET(order_goods_id, '$express_order_goods_ids')=0 and order_id=$order_id and shop_id=$shop_id")->select();
				if (!empty($order_goods_list_print) && count($order_goods_list_print) > 0) {
					$order_print_item = $this->dealPrintOrderGoodsList($order_id, $order_goods_list_print, 0, 0, "", "");
					$order_goods_list[] = $order_print_item;
				}
			} else {
				$order_goods_list_print = $order_goods_model->where("order_id=$order_id and shop_id=$shop_id")->select();
				$order_print_item = $this->dealPrintOrderGoodsList($order_id, $order_goods_list_print, 0, 0, "", "");
				$order_goods_list[] = $order_print_item;
			}
		}
		
		return $order_goods_list;
	}
	
	/**
	 * 通过订单id获取未发货订单项
	 */
	public function getNotshippedOrderByOrderId($order_ids)
	{
		$order_goods_model = new NsOrderGoodsModel();
		$order_id_array = explode(',', $order_ids);
		$order_goods_list = array();
		foreach ($order_id_array as $order_id) {
			$order_goods_list_print = $order_goods_model->getQuery([
				"order_id" => $order_id,
				"shipping_status" => 0,
				"refund_status" => 0
			]);
			$order_goods_item = $this->dealPrintOrderGoodsList($order_id, $order_goods_list_print, 0, $order_goods_list_print[0]["tmp_express_company_id"], $order_goods_list_print[0]["tmp_express_company"], $order_goods_list_print[0]["tmp_express_no"]);
			$order_goods_list[] = $order_goods_item;
		}
		return $order_goods_list;
	}
	
	/**
	 * 查询订单项售后信息
	 */
	public function getCustomerServiceInfo($id, $order_goods_id)
	{
		// 查询基础信息
		$customer_service = new NsCustomerServiceModel();
		if ($id > 0) {
			$order_goods_info = $customer_service->getInfo([ 'order_goods_id' => $order_goods_id, 'id' => $id ], '*');
		} else {
			$order_goods_info = $customer_service->getFirstData([ 'order_goods_id' => $order_goods_id ], 'create_time desc');
		}
		
		if (!empty($order_goods_info)) {
			// 商品图片
			$picture = new AlbumPictureModel();
			$picture_info = $picture->get($order_goods_info['goods_picture']);
			$order_goods_info['picture_info'] = $picture_info;
			if ($order_goods_info['audit_status'] != 0) {
				$order_refund_status = $this->getOrderRefundStatusInfo([ "refund_status" => $order_goods_info['audit_status'] ]);
				$order_goods_info['refund_operation'] = $order_refund_status['refund_operation'];
				$order_goods_info['status_name'] = $order_refund_status['status_name'];
				
				// 查询订单项的操作日志
				$cs_records = new NsCustomerServiceRecordsModel();
				$refund_info = $cs_records->getQuery([
					'order_goods_id' => $order_goods_id
				], "*");
				$order_goods_info['refund_info'] = $refund_info;
				
				if ($order_goods_info['audit_status'] == 5) {
					$order_customer_model = new NsOrderCustomerAccountRecordsModel();
					$customer_info = $order_customer_model->getInfo([ "order_goods_id" => $order_goods_id ], "*");
					if (!empty($customer_info)) {
						$order_goods_info['customer_info'] = $customer_info;
					}
				}
			} else {
				$order_goods_info['refund_operation'] = null;
				$order_goods_info['status_name'] = '';
				$order_goods_info['refund_info'] = null;
			}
			$order_goods_info['refund_real_money'] = $this->orderGoodsRefundMoney($order_goods_id);
			
		} else {
			$order_goods_info = null;
		}
		return $order_goods_info;
	}
	
	/**
	 * 获取售后列表
	 */
	public function getCustomerServiceList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$customer_service = new NsCustomerServiceModel();
		$picture = new AlbumPictureModel();
		$goods_model = new NsGoodsModel();
		// 查询主表
		$customer_service_list = $customer_service->pageQuery($page_index, $page_size, $condition, $order, '*');
		if (!empty($customer_service_list['data'])) {
			foreach ($customer_service_list['data'] as $k => $v) {
				
				// 通过sku_id查询ns_goods_sku中code
				// 查询商品sku表开始
				$goods_sku = new NsGoodsSkuModel();
				$goods_sku_info = $goods_sku->getInfo([
					'sku_id' => $v['sku_id']
				], 'code');
				$customer_service_list['data'][ $k ]['code'] = $goods_sku_info['code'];
				// 查询商品sku结束
				
				$goods_info = $goods_model->getInfo([ "goods_id" => $v["goods_id"] ]);
				//查询图片
				if (!empty($goods_sku_info['picture'])) {
					$v["goods_picture"] = $goods_sku_info['picture'];
				} else {
					$v["goods_picture"] = $goods_info['picture'];
				}
				
				$goods_picture = $picture->get($v['goods_picture']);
				if (empty($goods_picture)) {
					$goods_picture = array(
						'pic_cover' => '',
						'pic_cover_big' => '',
						'pic_cover_mid' => '',
						'pic_cover_small' => '',
						'pic_cover_micro' => '',
						"upload_type" => 1,
						"domain" => ""
					);
				}
				$customer_service_list['data'][ $k ]['picture'] = $goods_picture;
				if ($v['audit_status'] != 0) {
					$order_refund_status = $this->getOrderRefundStatusInfo([ "refund_status" => $v['audit_status'] ]);
					$customer_service_list['data'][ $k ]['refund_operation'] = $order_refund_status['refund_operation'];
					$customer_service_list['data'][ $k ]['status_name'] = $order_refund_status['status_name'];
					
				} else {
					$customer_service_list['data'][ $k ]['refund_operation'] = null;
					$customer_service_list['data'][ $k ]['status_name'] = '';
				}
				
				$order_list['data'][ $k ]['operation'] = '';
				// 订单来源名称
				$order_from = $this->getOrderFormInfo([ "order_type" => $v['order_from'] ]);
				$customer_service_list['data'][ $k ]['order_from_name'] = $order_from['type_name'];
				$customer_service_list['data'][ $k ]['order_from_tag'] = $order_from['tag'];
				$pay_type_info = $this->getPayTypeInfo([ "pay_type" => $v['payment_type'] ]);
				$customer_service_list['data'][ $k ]['pay_type_name'] = $pay_type_info["type_name"];
				// 根据订单类型判断订单相关操作
				$customer_service_list['data'][ $k ]['shipping_type_name'] = array();
				$shipping_status_info = $this->getShippingType([ "shipping_type" => $order_list['data'][ $k ]['shipping_type'] ]);
				$order_list['data'][ $k ]['shipping_type_name'] = $shipping_status_info;
			}
		}
		return $customer_service_list;
	}
	
	/**
	 * 根据订单项id查询订单退款账户记录  (售后)
	 */
	public function getOrderCustomerAccountRecordsByOrderGoodsId($order_goods_id)
	{
		$model = new NsOrderCustomerAccountRecordsModel();
		$info = $model->getInfo([
			"order_goods_id" => $order_goods_id
		], "*");
		return $info;
	}
	
	/***
	 * 判断用户是否使用过货到付款
	 */
	public function userIsUsedCashonDelivery($uid)
	{
		$order_model = new NsOrderModel();
		$condition = [
			"payment_type" => 4,
			"order_status" => array(
				"in",
				"0,1,2,3,4,-1"
			),
			"buyer_id" => $uid
		];
		$count = $order_model->getCount($condition);
		return $count;
	}
	
	/**
	 * 获取该订单应退运费 运费是不会退部分的
	 */
	public function getOrderRefundFreight($order_goods_id)
	{
		$ns_order = new NsOrderModel();
		$ns_order_goods = new NsOrderGoodsModel();
		$order_goods_info = $ns_order_goods->getInfo([
			"order_goods_id" => $order_goods_id
		], "order_id");
		$order_id = $order_goods_info["order_id"];
		
		$shipping_money = 0; // 所退运费
		// 查询该订单是否已支付 尚未发货
		$order_info = $ns_order->getInfo([
			"order_id" => $order_id,
			"order_status" => array(
				"in",
				"1,-1,6"
			),
			"shipping_status" => 0
		], "shipping_money");
		
		if (empty($order_info) || $order_info["shipping_money"] == 0) {
			return $shipping_money;
		}
		// 查询该订单是否有订单项已经发货
		$shipped_num = $ns_order_goods->getCount([
			"order_id" => $order_id,
			"shipping_status" => 1
		]);
		// 查询订单订单项数量
		$order_item_num = $ns_order_goods->getCount([ "order_id" => $order_id ]);
		// 未发起退款申请订单项数量
		$order_goods_non_refunds_num = $ns_order_goods->getCount([
			"order_id" => $order_id,
			"shipping_status" => 0,
			"refund_status" => 0
		]);
		
		// 商家未确定退款订单项数量
		$order_goods_agree_refunds_num = $ns_order_goods->getCount([
			"order_id" => $order_id,
			"shipping_status" => 0,
			"refund_status" => 4
		]);
		// 已退款的数量
		$order_goods_refunded_num = $ns_order_goods->getCount([
			"order_id" => $order_id,
			"refund_status" => 5
		]);
		
		// 用户取消和商家拒绝的订单项数量
		$order_goods_not_refunds_num = $ns_order_goods->getCount([
			"order_id" => $order_id,
			"refund_status" => array( "in", "-1,-2" )
		]);
		
		if ($order_goods_not_refunds_num == 0 && $shipped_num == 0) {
			if ($order_goods_non_refunds_num == 1 || ($order_goods_agree_refunds_num == 1 && ($order_goods_agree_refunds_num + $order_goods_refunded_num) == $order_item_num)) {
				$shipping_money = $order_info["shipping_money"];
			}
		}
		
		return $shipping_money;
	}
	
	/**
	 * 查询订单自提码等信息
	 */
	public function getOrderPickupInfo($order_id)
	{
		$ns_order_pickup = new NsOrderPickupModel();
		$res = $ns_order_pickup->getInfo([ 'order_id' => $order_id, 'picked_up_status' => 0 ], "picked_up_code,picked_up_id");
		return $res;
	}
	
	/**
	 * 判断用户是否是自提点的审核员
	 */
	public function currUserIsPickedUpAuditor($picked_up_id, $uid)
	{
		$ns_picked_up_auditor = new NsPickedUpAuditorModel();
		$count = $ns_picked_up_auditor->getCount([
			"pickup_id" => $picked_up_id,
			"uid" => $uid
		]);
		return $count;
	}
	
	/**
	 * 获取订单详情
	 */
	public function getDetail($order_id)
	{
		// 查询主表
		$order_detail = $this->order->getInfo([
			"order_id" => $order_id,
			"is_deleted" => 0
		]);
		if (empty($order_detail)) {
			return array();
		}
		// 发票信息
		$temp_array = array();
		if ($order_detail["buyer_invoice"] != "") {
			$temp_array = explode("$", $order_detail["buyer_invoice"]);
		}
		$order_detail["buyer_invoice_info"] = $temp_array;
		if (empty($order_detail)) {
			return '';
		}
		//支付类型
		$payment_type_info = $this->getPayTypeInfo([ "pay_type" => $order_detail['payment_type'] ]);
		$order_detail['payment_type_name'] = $payment_type_info["type_name"];
		$express_company_name = "";
		if ($order_detail['shipping_type'] == 1) {
			$order_detail['shipping_type_name'] = '物流配送';
			$express_company = new NsOrderExpressCompanyModel();
			
			$express_obj = $express_company->getInfo([
				"co_id" => $order_detail["shipping_company_id"]
			], "company_name");
			if (!empty($express_obj["company_name"])) {
				$express_company_name = $express_obj["company_name"];
			}
		} elseif ($order_detail['shipping_type'] == 2) {
			$order_detail['shipping_type_name'] = '门店自提';
		} else {
			$order_detail['shipping_type_name'] = '';
		}
		$order_detail["shipping_company_name"] = $express_company_name;
		// 查询订单项表
		$order_detail['order_goods'] = $this->getOrderGoods($order_id);
		
		// 查询订单提货信息表
		if ($order_detail['shipping_type'] == 2) {
			$order_pickup_model = new NsOrderPickupModel();
			$order_pickup_info = $order_pickup_model->getInfo([
				'order_id' => $order_id
			], '*');
			$address = new Address();
			$order_pickup_info['province_name'] = $address->getProvinceName($order_pickup_info['province_id']);
			$order_pickup_info['city_name'] = $address->getCityName($order_pickup_info['city_id']);
			$order_pickup_info['district_name'] = $address->getDistrictName($order_pickup_info['district_id']);
			$order_detail['order_pickup'] = $order_pickup_info;
		} else {
			$order_detail['order_pickup'] = null;
		}
		
		// 查询订单操作
		$order_status_info = $this->getOrderStatusInfo([ "order_type" => $order_detail["order_type"], "order_status" => $order_detail['order_status'], "shipping_type" => $order_detail['shipping_type'] ]);
		$order_detail['operation'] = $order_status_info['operation'];
		$order_detail['member_operation'] = $order_status_info['member_operation'];
		$order_detail['status_name'] = $order_status_info['status_name'];
		// 查询订单操作日志
		$order_action = new NsOrderActionModel();
		$order_action_log = $order_action->getQuery([
			'order_id' => $order_id
		], '*', 'action_time desc');
		$order_detail['order_action'] = $order_action_log;
		
		$order_detail['address'] = $order_detail["receiver_address"];
		return $order_detail;
	}
	
	/**
	 * 获取订单整体商品金额(根据订单项)
	 */
	public function getOrderGoodsMoney($order_id)
	{
		$order_goods = new NsOrderGoodsModel();
		$money = $order_goods->getSum([
			'order_id' => $order_id
		], 'goods_money');
		if (empty($money)) {
			$money = 0;
		}
		return $money;
	}
	
	/**
	 * 获取新支付交易号
	 */
	public function getOrderNewOutTradeNo($order_id)
	{
		$order_model = new NsOrderModel();
		$order_info = $order_model->getInfo([ 'order_id' => $order_id, 'order_status' => 0], 'out_trade_no');
		if(!empty($order_info)){
    		$order_action = new OrderAction();
    		$new_no = $order_action->createNewOutTradeNo($order_id);
    		
    		//操作完成后  修改订单支付方式
    		$order_data = array(
    			'out_trade_no' => $new_no
    		);
    		
    		$order_model->save($order_data, [ 'order_id' => $order_id ]);
    		
    		$pay_model = new NsOrderPaymentModel();
    		$pay_data = array(
    			"out_trade_no" => $new_no
    		);
    		$pay_model->save($pay_data, [ 'out_trade_no' => $order_info['out_trade_no'] ]);
    		
    		$pay = new UnifyPay();
    		$pay->modifyNo($order_info['out_trade_no'], $new_no);//关闭
    		return $new_no;
		}
	}
	
	/*********************************************************************wait*************************************************************************/
	
	/**
	 * 查询订单项售后详情
	 */
	public function getCustomerServiceDetail($id, $order_goods_id)
	{
		// 查询基础信息
		$customer_service = new NsCustomerServiceModel();
		if ($id > 0) {
			$order_goods_info = $customer_service->getInfo([ 'order_goods_id' => $order_goods_id, 'id' => $id ], '*');
		} else {
			$order_goods_info = $customer_service->getFirstData([ 'order_goods_id' => $order_goods_id ], 'create_time desc');
		}
		
		if (!empty($order_goods_info)) {
			// 商品图片
			$picture = new AlbumPictureModel();
			$picture_info = $picture->get($order_goods_info['goods_picture']);
			$order_goods_info['picture_info'] = $picture_info;
			if ($order_goods_info['audit_status'] != 0) {
				$refund_status_info = $this->getOrderRefundStatusInfo([ "refund_status" => $order_goods_info['audit_status'] ]);
				$order_goods_info['refund_operation'] = $refund_status_info['refund_operation'];
				$order_goods_info['status_name'] = $refund_status_info['status_name'];
				
				// 查询订单项的操作日志
				$cs_records = new NsCustomerServiceRecordsModel();
				$refund_info = $cs_records->all([
					'order_goods_id' => $order_goods_id
				]);
				$order_goods_info['refund_info'] = $refund_info;
			} else {
				$order_goods_info['refund_operation'] = null;
				$order_goods_info['status_name'] = '';
				$order_goods_info['refund_info'] = null;
			}
			$order_goods_info['refund_real_money'] = $this->orderGoodsRefundMoney($order_goods_id);
			
		} else {
			$order_goods_info = null;
		}
		return $order_goods_info;
	}
	
	/**
	 * 获取用户可使用优惠券
	 */
	public function getMemberCouponList($goods_sku_list)
	{
		$goods_preference = new GoodsPreference();
		$coupon_list = $goods_preference->getMemberCouponList($goods_sku_list);
		return $coupon_list;
	}
	
	/**
	 * 充值列表
	 */
	public function getOrderRechargeList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$recharge_model = new NsMemberRechargeViewModel();
		// 查询主表
		$list = $recharge_model->getViewList($page_index, $page_size, $condition, $order, '*');
		return $list;
	}
	
	/**
	 * 查询订单项物流数量
	 */
	public function getOrderGoodsExpressCount($condition)
	{
		$order_express_model = new NsOrderGoodsExpressModel();
		$count = $order_express_model->getCount($condition);
		return $count;
	}
	
	/**
	 * 订单项数量
	 */
	public function getOrderGoodsCount($condition)
	{
		$order_goods_model = new NsOrderGoodsModel();
		$count = $order_goods_model->getCount($condition);
		return $count;
	}
	
	/**
	 * 处理订单打印数据
	 */
	public function dealPrintOrderGoodsList($order_id, $order_goods_list_print, $is_express, $express_company_id, $express_company_name, $express_no, $express_id = 0)
	{
		$order_goods_ids = "";
		$print_goods_array = array();
		$is_print = 1;
		$tmp_express_company = "";
		$tmp_express_company_id = 0;
		$tmp_express_no = "";
		foreach ($order_goods_list_print as $k => $order_goods_print_obj) {
			$print_goods_array[] = array(
				"goods_id" => $order_goods_print_obj["goods_id"],
				"goods_name" => $order_goods_print_obj["goods_name"],
				"sku_id" => $order_goods_print_obj["sku_id"],
				"sku_name" => $order_goods_print_obj["sku_name"]
			);
			if (!empty($order_goods_ids)) {
				$order_goods_ids = $order_goods_ids . "," . $order_goods_print_obj["order_goods_id"];
			} else {
				$order_goods_ids = $order_goods_print_obj["order_goods_id"];
			}
			if ($k == 0) {
				$tmp_express_company = $order_goods_print_obj["tmp_express_company"];
				$tmp_express_company_id = $order_goods_print_obj["tmp_express_company_id"];
				$tmp_express_no = $order_goods_print_obj["tmp_express_no"];
			}
		}
		if (empty($tmp_express_company) || empty($tmp_express_company_id) || empty($tmp_express_no)) {
			$is_print = 0;
		}
		if ($is_express == 0) {
			$express_company_id = $tmp_express_company_id;
			$express_company_name = $tmp_express_company;
			$express_no = $tmp_express_no;
		}
		$order_model = new NsOrderModel();
		$order_obj = $order_model->get($order_id);
		$order_goods_item_obj = array(
			"order_id" => $order_id,
			"order_goods_ids" => $order_goods_ids,
			"goods_array" => $print_goods_array,
			"is_devlier" => $is_express,
			"is_print" => $is_print,
			"express_company_id" => $express_company_id,
			"express_company_name" => $express_company_name,
			"express_no" => $express_no,
			"order_no" => $order_obj["order_no"],
			"express_id" => $express_id,
		    "buyer_id" => $order_obj["buyer_id"]
		);
		return $order_goods_item_obj;
	}
	
	/**
	 * 获取订单项可退实际金额
	 * @param unknown $order_id
	 * @param unknown $order_goods_id
	 */
	public function getRefundRealMoney($order_id, $order_goods_id)
	{
		$ns_order = new NsOrderModel();
		$ns_order_goods = new NsOrderGoodsModel();
		
		$order_info = $ns_order->getInfo([ 'order_id' => $order_id ], 'pay_money,user_platform_money,shipping_money');
		if (empty($order_info) || empty($order_info['pay_money'])) return 0.00;
		
		if ($order_info['user_platform_money'] > 0 && $order_info['shipping_money'] > 0) {
			$order_money = $order_info['pay_money'] + $order_info['user_platform_money'];
			$order_info['pay_money'] = round(($order_money - $order_info['shipping_money']) * round(($order_info['pay_money'] / $order_money), 2), 2);
		} else {
			$order_info['pay_money'] -= $order_info['shipping_money'];
		}
		$total_money = $ns_order_goods->getSum([ 'order_id' => $order_id, 'gift_flag' => 0 ], 'goods_money');
		$order_goods_data = $ns_order_goods->getQuery([ 'order_id' => $order_id, 'gift_flag' => 0 ], 'goods_money,order_goods_id');
		if (!empty($order_goods_data)) {
			if (count($order_goods_data) == 1) return $order_info['pay_money'];
			$surplus = $order_info['pay_money'];
			$data = [];
			foreach ($order_goods_data as $k => $item) {
				if ($k == (count($order_goods_data) - 1)) {
					$data[ $item['order_goods_id'] ] = $surplus;
				} else {
					$rate = round(($item['goods_money'] / $total_money), 2);
					$data[ $item['order_goods_id'] ] = round(($surplus * $rate), 2);
					$surplus -= $data[ $item['order_goods_id'] ];
				}
			}
			return $data[ $order_goods_id ];
		}
	}
	
	/**
	 * 获取订单项可退余额
	 * @param unknown $order_id
	 * @param unknown $order_goods_id
	 */
	public function getRefundBalance($order_id, $order_goods_id)
	{
		$ns_order = new NsOrderModel();
		$ns_order_goods = new NsOrderGoodsModel();
		
		$order_info = $ns_order->getInfo([ 'order_id' => $order_id ], 'pay_money,user_platform_money,shipping_money');
		if (empty($order_info) || empty($order_info['user_platform_money'])) return 0.00;
		
		if ($order_info['pay_money'] > 0 && $order_info['shipping_money'] > 0) {
			$order_money = $order_info['pay_money'] + $order_info['user_platform_money'];
			$order_info['pay_money'] = round(($order_money - $order_info['shipping_money']) * round(($order_info['pay_money'] / $order_money), 2), 2);
			$order_info['user_platform_money'] = $order_money - $order_info['shipping_money'] - $order_info['pay_money'];
		} else {
			$order_info['user_platform_money'] -= $order_info['shipping_money'];
		}
		
		$total_money = $ns_order_goods->getSum([ 'order_id' => $order_id, 'gift_flag' => 0 ], 'goods_money');
		$order_goods_data = $ns_order_goods->getQuery([ 'order_id' => $order_id, 'gift_flag' => 0 ], 'goods_money,order_goods_id');
		if (!empty($order_goods_data)) {
			if (count($order_goods_data) == 1) return $order_info['user_platform_money'];
			$surplus = $order_info['user_platform_money'];
			$data = [];
			foreach ($order_goods_data as $k => $item) {
				if ($k == (count($order_goods_data) - 1)) {
					$data[ $item['order_goods_id'] ] = $surplus;
				} else {
					$rate = round(($item['goods_money'] / $total_money), 2);
					$data[ $item['order_goods_id'] ] = round(($surplus * $rate), 2);
					$surplus -= $data[ $item['order_goods_id'] ];
				}
			}
			return $data[ $order_goods_id ];
		}
	}
	
	/**
	 * 获取订单项可退运费
	 * @param unknown $order_id
	 * @param unknown $order_goods_id
	 */
	public function getRefundFreight($order_id, $order_goods_id)
	{
		$ns_order = new NsOrderModel();
		$ns_order_goods = new NsOrderGoodsModel();
		
		$order_info = $ns_order->getInfo([ 'order_id' => $order_id ], 'shipping_money');
		if (empty($order_info) || empty($order_info['shipping_money'])) return 0.00;
		
		// 查询该订单是否有订单项已经发货 有发货则不退运费
		$shipped_num = $ns_order_goods->getCount([ "order_id" => $order_id, "shipping_status" => 1 ]);
		if ($shipped_num > 0) return 0.00;
		
		// 订单项的数量
		$order_goods_num = $ns_order_goods->getCount([ "order_id" => $order_id, 'gift_flag' => 0 ]);
		// 退款完成的数量
		$refund_completed_num = $ns_order_goods->getCount([ "order_id" => $order_id, 'refund_status' => 5 ]);
		// 如果退的是最后一项订单项
		if (($order_goods_num - $refund_completed_num) == 1) {
			return $order_info['shipping_money'];
		}
		return 0.00;
	}
	
	/**
	 * 获取订单项可退金额
	 * @param unknown $order_id
	 * @param unknown $order_goods_id
	 */
	public function getRefundMoney($order_id, $order_goods_id)
	{
		$ns_order = new NsOrderModel();
		$order_info = $ns_order->getInfo([ 'order_id' => $order_id ], 'order_type');
		
		$money = 0;
		if (!empty($order_info)) {
			$hook_res = hook('getRefundMoney', [
				'order_id' => $order_id,
				'order_goods_id' => $order_goods_id,
				'order_type' => $order_info['order_type']
			]);
			$hook_res = arrayFilter($hook_res);
			if (!empty($hook_res[0])) {
				$refund_money = $hook_res[0]['refund_money'];
				$refund_balance = $hook_res[0]['refund_balance'];
				$freight = $hook_res[0]['freight'];
			} else {
				$refund_money = $this->getRefundRealMoney($order_id, $order_goods_id);
				$refund_balance = $this->getRefundBalance($order_id, $order_goods_id);
				$freight = $this->getRefundFreight($order_id, $order_goods_id);
			}
			$money = round(($refund_money + $refund_balance + $freight), 2);
		}
		return $money;
	}
	
	/**
	 * 获取出库单列表
	 */
	public function getShippingList($order_ids)
	{
	    $order_goods_view = new NsOrderGoodsViewModel();
	    $condition = array(
	        'nog.order_id' => array(
	            "in",
	            $order_ids
	        ),
	        'no.order_status' => array(
	            "neq",
	            0
	        )
	    );
	    $list = $order_goods_view->getShippingList(1, 0, $condition, "");
	    foreach ($list as $v) {
	        $res = $order_goods_view->getOrderGoodsViewQuery(1, 0, [
	            'no.order_id' => array(
	                "in",
	                $order_ids
	            ),
	            'nog.sku_id' => $v["sku_id"]
	        ], "");
	        $v["order_list"] = $res;
	    }
	    return $list;
	}
	
	/**
	 * 添加打印时临时物流信息
	 */
	public function addTmpExpressInformation($print_order_arr, $deliver_goods)
	{
	    if (! empty($print_order_arr) && count($print_order_arr) > 0) {
	        $ns_order_goods = new NsOrderGoodsModel();
	        $order_goods_express = new NsOrderGoodsExpressModel();
	        $ns_order_goods->startTrans();
	        $order_action = new OrderAction();
	        try {
	            foreach ($print_order_arr as $order_print_info) {
	                $ns_order_goods->update([
	                    "tmp_express_company" => $order_print_info["tmp_express_company_name"],
	                    "tmp_express_company_id" => $order_print_info["tmp_express_company_id"],
	                    "tmp_express_no" => $order_print_info["tmp_express_no"]
	                ], [
	                    "order_id" => $order_print_info['order_id'],
	                    "order_goods_id" => array(
	                        "in",
	                        explode(",", $order_print_info["order_goods_ids"])
	                    )
	                ]);
	                // 订单物流表
	                if ($order_print_info['is_devlier'] == 1) {
	                    $order_goods_express->update([
	                        "express_company_id" => $order_print_info["tmp_express_company_id"],
	                        "express_company" => $order_print_info["tmp_express_company_name"],
	                        "express_name" => $order_print_info["tmp_express_company_name"],
	                        "express_no" => $order_print_info["tmp_express_no"]
	                    ], [
	                        "order_id" => $order_print_info['order_id'],
	                        "id" => $order_print_info['express_id']
	                    ]);
	                }
	                // 订单发货
	                if ($order_print_info['is_devlier'] == 0 && $deliver_goods == 1) {
	                    $data = array(
            				'order_id' => $order_print_info['order_id'],
            				'order_goods_id_array' => $order_print_info['order_goods_ids'],
            				'express_name' => $order_print_info["tmp_express_company_name"],
            				'shipping_type' => 1,
            				'express_company_id' => $order_print_info['tmp_express_company_id'],
            				'express_no' => $order_print_info["tmp_express_no"],
            				'buyer_id' => $order_print_info["buyer_id"]
            			);
	                    $order_action->orderDelivery($data);
	                }
	            }
	            $ns_order_goods->commit();
	            return $retval = array(
	                "code" => 1,
	                "message" => "操作成功"
	            );
	        } catch (\Exception $e) {
	            $ns_order_goods->rollback();
	            return $e->getMessage();
	        }
	    } else {
	        return $retval = array(
	            "code" => 0,
	            "message" => "操作失败"
	        );
	    }
	}
	
	/**
	 * 获取订单交易号
	 * @param unknown $order_id
	 */
	public function getOrderOutTradeNo($order_id){
	    $ns_order = new NsOrderModel();
	    $order_info = $ns_order->getInfo([ 'order_id' => $order_id, 'order_status' => 0], 'out_trade_no');
	    if(!empty($order_info['out_trade_no'])){
	        return $order_info['out_trade_no'];
	    }
	}

}