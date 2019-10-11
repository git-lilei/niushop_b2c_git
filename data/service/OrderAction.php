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
use data\model\AlbumPictureModel;
use data\model\NsCartModel;
use data\model\NsGoodsEvaluateModel;
use data\model\NsGoodsModel;
use data\model\NsGoodsSkuModel;
use data\model\NsOrderActionModel;
use data\model\NsOrderExpressCompanyModel;
use data\model\NsOrderGoodsExpressModel;
use data\model\NsOrderGoodsModel;
use data\model\NsOrderModel;
use data\model\NsOrderPaymentModel;
use data\model\NsOrderPickupModel;
use data\model\NsOrderPresellModel;
use data\model\NsOrderPromotionDetailsModel;
use data\model\NsPickedUpAuditorViewModel;
use data\model\NsPromotionGiftGoodsModel;
use data\model\NsPromotionGiftModel;
use data\model\NsPromotionMansongRuleModel;
use data\model\UserModel;
use data\service\GoodsCalculate\GoodsCalculate;
use data\service\Member as MemberService;
use data\service\Member\MemberAccount;
use data\service\Member\MemberCoupon;
use data\service\Order\Order as OrderService;
use data\service\promotion\GoodsPreference;
use think\Log;

class OrderAction extends OrderService
{
	/*****************************************************评价 begin*****************************************************************************/
	/**
	 * 商品评价-添加
	 */
	public function addGoodsEvaluate($data)
	{
		$goodsEvaluate = new NsGoodsEvaluateModel();
		$goods = new NsGoodsModel();
		$res = $goodsEvaluate->saveAll($data["dataArr"]);
		$result = false;
		
		if ($res != false) {
			// 修改订单评价状态
			$order = new NsOrderModel();
			$order_data = [ 'is_evaluate' => 1 ];
			$result = $order->save($order_data, [ 'order_id' => $data["order_id"] ]);
			
			foreach ($data["dataArr"] as $item) {
				$good_info = $goods->get($item['goods_id']);
				$evaluates = $good_info['evaluates'] + 1;
				$star = $good_info['star'] + $item['scores'];
				$match_point = $star / $evaluates;
				$match_ratio = $match_point / 5 * 100;
				$goods_data = array(
					'evaluates' => $evaluates,
					'star' => $star,
					'match_point' => $match_point,
					'match_ratio' => $match_ratio
				);
				$goods->update($goods_data, [
					'goods_id' => $item['goods_id']
				]);
				// 修改订单项表评价状态
				$ns_order_goods = new NsOrderGoodsModel();
				$ns_order_goods->save([ 'is_evaluate' => 1 ], [ 'order_goods_id' => $item['order_goods_id'] ]);
			}
			hook("goodsEvaluateSuccess", [
				'order_id' => $data["order_id"],
				'data' => $data["dataArr"]
			]);
		}
		return $result;
	}
	
	/**
	 * 评价数据处理
	 */
	public function orderGoodsEvaluate($param)
	{
		$order_id = $param['order_id'];
		$order_no = $param['order_no'];
		$goods = $param['goods_evaluate'];
		$member_service = new Member();
		$order_query = new OrderQuery();
		$goodsEvaluateArray = json_decode($goods);
		
		$member_detail = $member_service->getMemberDetail($this->instance_id);
		
		if (!empty($member_detail['member_name'])) {
			$member_name = $member_detail['member_name'];
		} else {
			$member_name = '***';
		}
		$dataArr = array();
		foreach ($goodsEvaluateArray as $key => $goodsEvaluate) {
			$orderGoods = $order_query->getOrderGoodsInfo($goodsEvaluate->order_goods_id);
			$data = array(
				
				'order_id' => $order_id,
				'order_no' => $order_no,
				'order_goods_id' => intval($goodsEvaluate->order_goods_id),
				
				'goods_id' => $orderGoods['goods_id'],
				'goods_name' => $orderGoods['goods_name'],
				'goods_price' => $orderGoods['goods_money'],
				'goods_image' => $orderGoods['goods_picture'],
				'shop_id' => $orderGoods['shop_id'],
				'shop_name' => "默认",
				'content' => $goodsEvaluate->content,
				'addtime' => time(),
				'image' => $goodsEvaluate->imgs,
				
				'member_name' => $member_name,
				'explain_type' => $goodsEvaluate->explain_type,
				'uid' => $this->uid,
				'is_anonymous' => $goodsEvaluate->is_anonymous,
				'scores' => intval($goodsEvaluate->scores)
			);
			$dataArr[] = $data;
		}
		$data = array(
			"dataArr" => $dataArr,
			"order_id" => $order_id
		);
		$result = $this->addGoodsEvaluate($data);
		if ($result) {
			//会员行为——评论送优惠券、积分
			$member = new MemberService();
			$member->memberAction([ 'type' => 'NsMemberEvaluate', 'uid' => $this->uid, 'order_id' => $data["order_id"] ]);
		}
		return $result;
	}
	
	/**
	 * 商品评价-回复
	 */
	public function addGoodsEvaluateExplain($explain_first, $order_goods_id)
	{
		$goodsEvaluate = new NsGoodsEvaluateModel();
		$data = array(
			'explain_first' => $explain_first
		);
		$res = $goodsEvaluate->save($data, [
			'order_goods_id' => $order_goods_id
		]);
		hook("goodsEvaluateExplainSuccess", [
			'order_goods_id' => $order_goods_id,
			'explain_first' => $explain_first
		]);
		return $res;
	}
	
	/**
	 * 商品评价-追评
	 */
	public function addGoodsEvaluateAgain($param)
	{
		$goodsEvaluate = new NsGoodsEvaluateModel();
		$data = array(
			'again_content' => $param["again_content"],
			'again_addtime' => time(),
			'again_image' => $param["again_image"]
		);
		$res = $goodsEvaluate->save($data, [
			'order_goods_id' => $param["order_goods_id"]
		]);
		hook("goodsEvaluateAgainSuccess", [
			'again_content' => $param["again_content"],
			'againImageList' => $param["again_image"],
			'order_goods_id' => $param["order_goods_id"]
		]);
		return $res;
	}
	
	/**
	 * 订单自动评价
	 */
	public function ordersAutoEvaluate()
	{
		$config = new Config();
		$config_info = $config->getConfig(0, 'SYSTEM_DEFAULT_EVALUATE');
		$config_info = json_decode($config_info['value'], true);
		
		if ($config_info['day'] > 0) {
			$time = time() - ($config_info['day'] * 24 * 60 * 60);
			$condition = array(
				'order_status' => 4,
				'finish_time' => [ '<', $time ],
				'is_evaluate' => 0,
				'is_deleted' => 0
			);
			
			$ns_order = new NsOrderModel();
			$count = $ns_order->getCount($condition);
			
			if ($count) {
				try {
					$order_list = $ns_order->getQuery($condition, 'order_id,order_no,user_name,order_id,shop_id,buyer_id');
					$ns_order_goods = new NsOrderGoodsModel();
					foreach ($order_list as $order_item) {
						$order_goods_list = $order_goods_condition = array(
							'is_evaluate' => 0,
							'order_id' => $order_item['order_id']
						);
						$order_goods_list = $ns_order_goods->getQuery($order_goods_list, 'order_goods_id,goods_id,goods_name,goods_money,goods_picture');
						
						if (!empty($order_goods_list)) {
							$data = array();
							foreach ($order_goods_list as $order_goods_item) {
								$picture_model = new AlbumPictureModel();
								$img_info = $picture_model->getInfo([ 'pic_id' => $order_goods_item['goods_picture'] ]);
								array_push($data,
									array(
										'order_id' => $order_item['order_id'],
										'order_no' => $order_item['order_no'],
										'order_goods_id' => $order_goods_item['order_goods_id'],
										'goods_id' => $order_goods_item['goods_id'],
										'goods_name' => $order_goods_item['goods_name'],
										'goods_price' => $order_goods_item['goods_money'],
										'goods_image' => $img_info['pic_cover_small'],
										'shop_id' => $order_item['shop_id'],
										'shop_name' => "默认",
										'content' => $config_info['evaluate'],
										'addtime' => time(),
										'image' => '',
										'member_name' => $order_item['user_name'],
										'explain_type' => 1,
										'uid' => $order_item['buyer_id'],
										'is_anonymous' => 1,
										'scores' => 5
									)
								);
							}
							
							$function_data = array(
								"dataArr" => $data,
								"order_id" => $order_item['order_id']
							);
							$this->addGoodsEvaluate($function_data);
						}
					}
					return 1;
				} catch (\Exception $e) {
					Log::write("系统默认评价错误，错误信息" . $e->getMessage());
					return $e->getMessage();
				}
			}
		}
	}
	/*****************************************************评价 end*****************************************************************************/
	
	/*****************************************************收发货 begin*****************************************************************************/
	/**
	 * 订单发货
	 */
	public function orderDelivery($data)
	{
		$retval = $this->expressDelivery($data);
		if ($retval) {
			$params = [
				'order_id' => $data["order_id"],
				'order_goods_id_array' => $data["order_goods_id_array"],
				'express_name' => $data["express_name"],
				'shipping_type' => $data["shipping_type"],
				'express_company_id' => $data["express_company_id"],
				'express_no' => $data["express_no"],
				'buyer_id' => $data['buyer_id']
			];
			hook('orderDeliverySuccess', $params);
		}
		return $retval;
	}
	
	/**
	 * 订单项发货
	 */
	public function orderGoodsDelivery($order_id, $order_goods_id_array)
	{
		$order_goods = new NsOrderGoodsModel();
		$goods_service = new Goods();
		$order_goods->startTrans();
		try {
			$order_goods_id_array = explode(',', $order_goods_id_array);
			foreach ($order_goods_id_array as $k => $order_goods_id) {
				$order_goods = new NsOrderGoodsModel();
				$order_goods_info = $order_goods->getInfo([ "order_goods_id" => $order_goods_id ], "sku_id, goods_id, num");
				
				//统一执行出库操作(发货出库)
				$delivery_res = $goods_service->delivery([ "sku_id" => $order_goods_info["sku_id"], "goods_id" => $order_goods_info["goods_id"], "num" => $order_goods_info["num"], "type" => 2 ]);
				if ($delivery_res["code"] <= 0) {
					$order_goods->rollback();
					return $delivery_res["code"];
				}
				$order_goods_id = (int) $order_goods_id;
				$data = array(
					'shipping_status' => 1
				);
				
				$order_goods->save($data, [
					'order_goods_id' => $order_goods_id
				]);
			}
			$order_action = new OrderAction();
			$order_action->orderDoDelivery($order_id);
			
//			runhook("Notify", "orderDelivery", array(
//				"order_goods_ids" => $order_goods_id
//			));
			message("order_deliver", ["order_goods_ids" => $order_goods_id]);//发送消息
			$order_goods->commit();
			return 1;
		} catch (\Exception $e) {
			$order_goods->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 自提点审核员确认提货成功
	 */
	public function pickedUpAuditorConfirmPickup($order_id, $auditor_id, $buyer_name, $buyer_phone)
	{
		// 订单转为已收货状态
		$this->order->startTrans();
		try {
			$data_take_delivery = array(
				'shipping_status' => 2,
				'order_status' => 3,
				'sign_time' => time()
			);
			$order_model = new NsOrderModel();
			$order_model->save($data_take_delivery, [
				'order_id' => $order_id
			]);
			$ns_picked_up_auditor_view = new NsPickedUpAuditorViewModel();
			$picked_up_auditor_info = $ns_picked_up_auditor_view->getViewInfo([ 'npua.auditor_id' => $auditor_id ]);
			if (empty($picked_up_auditor_info)) {
				$this->order->rollback();
				return array(
					'code' => -1,
					'message' => '未获取该审核员的信息'
				);
			}
			$auditor_name = $picked_up_auditor_info['nick_name'];
			
			$action_data = array(
				"order_id" => $order_id,
				"uid" => $auditor_id,
				"remark" => '订单提货' . ' 提货人：' . $buyer_name . ' ' . $buyer_phone . ' 门店审核人员：' . $auditor_name . '确认用户提货',
			);
			$this->addOrderAction($action_data);
			// 记录提货信息
			$order_pickup_model = new NsOrderPickupModel();
			$data_pickup = array(
				'buyer_name' => $buyer_name,
				'buyer_mobile' => $buyer_phone,
				'remark' => '',
				'picked_up_status' => 1,
				'auditor_id' => $auditor_id,
				'picked_up_time' => time()
			);
			$order_pickup_model->save($data_pickup, [
				'order_id' => $order_id
			]);
			$order_goods_model = new NsOrderGoodsModel();
			$order_goods_model->save([
				'shipping_status' => 2
			], [
				'order_id' => $order_id
			]);
			
			$delivery_res = $this->pickupDelivery([ "order_id" => $order_id ]);
			if ($delivery_res["code"] <= 0) {
				$this->order->rollback();
				return array(
					"code" => $delivery_res["code"],
					"message" => getErrorInfo($delivery_res["code"])
				);
			}
			
			$this->giveGoodsOrderPoint($order_id, 2);
			$this->order->commit();
			return array(
				'code' => 1,
				'message' => '提货成功'
			);
		} catch (\Exception $e) {
			$this->order->rollback();
			return array(
				'code' => -1,
				'message' => $e->getMessage()
			);
		}
	}
	
	/**
	 * 订单发货(整体发货)(不考虑订单项)
	 */
	public function orderDoDelivery($order_id)
	{
		$this->order->startTrans();
		try {
			$order_item = new NsOrderGoodsModel();
			$count = $order_item->getCount([
				'order_id' => $order_id,
				'shipping_status' => 0,
				'refund_status' => array(
					'ELT',
					0
				)
			]);
			if ($count == 0) {
				$data_delivery = array(
					'shipping_status' => 1,
					'order_status' => 2,
					'consign_time' => time()
				);
				$order_model = new NsOrderModel();
				$order_model->save($data_delivery, [
					'order_id' => $order_id
				]);
				
				$action_data = array(
					"order_id" => $order_id,
					"remark" => '订单发货',
					"uid" => $this->uid,
				);
				$this->addOrderAction($action_data);
			}
			
			$this->order->commit();
			return 1;
		} catch (\Exception $e) {
			
			$this->order->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 订单自动收货
	 */
	public function orderAutoDelivery($order_id)
	{
		$this->order->startTrans();
		try {
			$data_take_delivery = array(
				'shipping_status' => 2,
				'order_status' => 3,
				'sign_time' => time()
			);
			$order_model = new NsOrderModel();
			$order_model->save($data_take_delivery, [
				'order_id' => $order_id
			]);
			
			$action_data = array(
				"order_id" => $order_id,
				"remark" => '订单自动收货',
				"uid" => 0,
			);
			$this->addOrderAction($action_data);
			// 判断是否需要在本阶段赠送积分
			$this->giveGoodsOrderPoint($order_id, 2);
			$this->order->commit();
			return 1;
		} catch (\Exception $e) {
			
			$this->order->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 物流公司发货
	 */
	public function expressDelivery($param)
	{
		$user = new UserModel();
		$user_name = $user->getInfo([
			'uid' => $param['buyer_id']
		], 'user_name');
		$order_express = new NsOrderGoodsExpressModel();
		$order_express->startTrans();
		
		try {
			$count = $order_express->getCount([
				'order_goods_id_array' => $param["order_goods_id_array"]
			]);
			if ($count == 0) {
				
				$express_company = new NsOrderExpressCompanyModel();
				$express_company_info = $express_company->getInfo([
					'co_id' => $param["express_company_id"]
				], 'company_name');
				$data_goods_delivery = array(
					'order_id' => $param["order_id"],
					'order_goods_id_array' => $param["order_goods_id_array"],
					'express_name' => $param["express_name"],
					'shipping_type' => $param["shipping_type"],
					'express_company' => $express_company_info['company_name'],
					'express_company_id' => $param["express_company_id"],
					'express_no' => $param["express_no"],
					'shipping_time' => time(),
					'uid' => $param['buyer_id'],
					'user_name' => $user_name['user_name']
				);
				$order_express->save($data_goods_delivery);
				// 循环添加到订单商品项
				$order_action = new OrderAction();
				$order_action->orderGoodsDelivery($param["order_id"], $param["order_goods_id_array"]);
				$order_express->commit();
			}
			return 1;
		} catch (\Exception $e) {
			$order_express->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 通过订单id 修改物流单号
	 */
	public function updateDelivery($order_goods_express_id, $express_name, $shipping_type, $express_company_id, $express_no)
	{
		$user = new UserModel();
		$user_name = $user->getInfo([
			'uid' => $this->uid
		], 'user_name');
		$order_express = new NsOrderGoodsExpressModel();
		$order_express->startTrans();
		try {
			$express_company = new NsOrderExpressCompanyModel();
			$express_company_info = $express_company->getInfo([
				'co_id' => $express_company_id
			], 'company_name');
			$data_goods_delivery = array(
				'express_name' => $express_name,
				'shipping_type' => $shipping_type,
				'express_company' => $express_company_info['company_name'],
				'express_company_id' => $express_company_id,
				'express_no' => $express_no,
				'uid' => $this->uid,
				'user_name' => $user_name['user_name']
			);
			$order_express->save($data_goods_delivery, [ 'id' => $order_goods_express_id ]);
			$order_express->commit();
			return 1;
		} catch (\Exception $e) {
			$order_express->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 通过order_id删除快递信息
	 */
	public function deleteDelivery($order_goods_express_id)
	{
		$order_express = new NsOrderGoodsExpressModel();
		$res = $order_express->destroy([ 'id' => $order_goods_express_id ]);
		return $res;
	}
	
	/**
	 * 自动收货
	 */
	public function autoDeilvery()
	{
		$order_model = new NsOrderModel();
		try {
			$config = new Config();
			$config_info = $config->getConfig(0, 'ORDER_AUTO_DELIVERY');
			if (!empty($config_info['value']) & $config_info['value'] != 0) {
				$delivery_time = $config_info['value'];
			} else {
				return 1;
			}
			$time = time() - 3600 * 24 * $delivery_time;//订单自动完成
			$condition = array(
				'order_status' => 2,
				'consign_time' => array( 'LT', $time )
			);
			$order_list = $order_model->getQuery($condition, 'order_id');
			if (!empty($order_list)) {
				foreach ($order_list as $k => $v) {
					if (!empty($v['order_id'])) {
						$this->orderAutoDelivery($v['order_id']);
					}
				}
				
			}
			return 1;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}
	
	/**
	 * 虚拟商品发货
	 * @param $param
	 */
	public function virtualDelivery($param)
	{
		$order_model = new NsOrderModel();
		
		$order_model->startTrans();
		try {
			
			$order_id = $param["order_id"];
			$goods_service = new Goods();
			//订单状态判断  如果状态不是待发货  就停止
			$order_info = $order_model->getInfo([ "order_id" => $order_id ], "order_status, buyer_id, order_no");
			if ($order_info["order_status"] != 1) {
				$order_model->rollback();
				return 0;
			}
			$order_no = $order_info["order_no"];
			
			$user_model = new UserModel();
			$user_info = $user_model->getInfo([ "uid" => $order_info["buyer_id"] ], "nick_name");
			$buyer_nickname = $user_info["nick_name"];
			
			$order_goods_model = new NsOrderGoodsModel();
			$order_goods_list = $order_goods_model->getQuery([ "order_id" => $order_id ]);
			if (empty($order_goods_list)) {
				$order_model->rollback();
				return 0;
			}
			
			//生成虚拟商品,并发放(执行实际的发货流程)
			foreach ($order_goods_list as $list_k => $list_v) {
				$param = array(
					"order_id" => $order_id,
					"order_no" => $order_no,
					"buyer_nickname" => $buyer_nickname,
					"order_goods_items" => $list_v,
				);
				$res = $goods_service->virtualOrderAction($param);
				if ($res <= 0) {
					$order_model->rollback();
					return 0;
				}
			}
			
			foreach ($order_goods_list as $k => $v) {
				//执行出库流程
				$data = array(
					"sku_id" => $v["sku_id"],
					"goods_id" => $v["goods_id"],
					"num" => $v["num"],
					"type" => 2
				);
				$delivery_res = $goods_service->delivery($data);
				if ($delivery_res["code"] <= 0) {
					$order_model->rollback();
					return 0;
				}
			}
			//店铺服务自动完成订单
			$this->orderComplete($order_id);
			$order_model->commit();
			return 1;
		} catch (\Exception $e) {
			$order_model->rollback();
			return 0;
		}
	}
	/*****************************************************收发货 end*****************************************************************************/
	/*****************************************************订单操作 begin*****************************************************************************/
	/**
	 * 订单关闭
	 */
	public function orderClose($order_id)
	{
		$order_info = $this->order->getInfo([
			'order_id' => $order_id
		], 'order_status,pay_status,point, coupon_id, user_money, buyer_id,shop_id,user_platform_money, coin_money,order_type,out_trade_no,payment_type');
		
		if ((in_array($order_info['order_status'], [ 1, 2, 3 ]) && $order_info['payment_type'] != 4) || (in_array($order_info['order_status'], [ 2, 3 ]) && $order_info['payment_type'] == 4)) {
			return 0;
		}
		if ($order_info['order_status'] == 5) {
			return 1;
		}
		// 如果该订单为预售订单 且已支付的预售金的话则不关闭
		if ($order_info['order_type'] == 6 && $order_info['order_status'] == 7) {
			return 1;
		}
		$this->order->startTrans();
		try {
			$data_close = array(
				'order_status' => 5
			);
			$order_model = new NsOrderModel();
			$order_model->save($data_close, [
				'order_id' => $order_id
			]);
			
			$pay = new NsOrderPaymentModel();
			$payInfo = $pay->getInfo([
				'out_trade_no' => $order_info['out_trade_no']
			], 'balance_money');
			
			$account_flow = new MemberAccount();
			if ($order_info['order_status'] == 0 || ($order_info['order_status'] == 6 && $order_info['order_type'] == 6)) {
				// 会员余额返还
				if ($order_info['user_money'] > 0) {
					$account_flow->addMemberAccountData($order_info['shop_id'], 2, $order_info['buyer_id'], 1, $order_info['user_money'], 2, $order_id, '订单关闭返还用户余额');
				}
				$balance_money = $payInfo['balance_money'];
				// 预售订单
				if ($order_info['order_type'] == 6) {
					$order_presell = new NsOrderPresellModel();
					$order_presell_info = $order_presell->getInfo([ "relate_id" => $order_id ], "out_trade_no");
					$order_presell_pay_info = $pay->getInfo([ 'out_trade_no' => $order_presell_info["out_trade_no"] ], 'balance_money');
					if (!empty($order_presell_pay_info['balance_money'])) {
						$balance_money += $order_presell_pay_info['balance_money'];
					}
				}
				
				// 平台余额返还
				if ($balance_money > 0) {
					$account_flow->addMemberAccountData(0, 2, $order_info['buyer_id'], 1, $balance_money, 2, $order_id, '商城订单关闭返还锁定余额');
				}
			}
			
			// 积分返还
			if ($order_info['point'] > 0) {
				$account_flow->addMemberAccountData($order_info['shop_id'], 1, $order_info['buyer_id'], 1, $order_info['point'], 2, $order_id, '订单关闭返还积分');
			}
			
			//购物币
			if ($order_info['coin_money'] > 0) {
				$coin_convert_rate = $account_flow->getCoinConvertRate();
				$account_flow->addMemberAccountData($order_info['shop_id'], 3, $order_info['buyer_id'], 1, $order_info['coin_money'] / $coin_convert_rate, 2, $order_id, '订单关闭返还购物币');
			}
			
			// 优惠券返还
			$coupon = new MemberCoupon();
			if ($order_info['coupon_id'] > 0) {
				$coupon->UserReturnCoupon($order_info['coupon_id']);
			}
			// 退回库存
			$order_goods = new NsOrderGoodsModel();
			$order_goods_list = $order_goods->getQuery([
				'order_id' => $order_id
			]);
			foreach ($order_goods_list as $k => $v) {
				$goods_sku_model = new NsGoodsSkuModel();
				$goods_sku_info = $goods_sku_model->getInfo([
					'sku_id' => $v['sku_id']
				], 'goods_id, stock');
				if ($v['shipping_status'] != 1) {
					// 卖家未发货
					$return_stock = 1;
				} else {
					// 卖家已发货,买家不退货
					if ($v['refund_type'] == 1) {
						$return_stock = 0;
					} else {
						$return_stock = 1;
					}
				}
				// 销量返回
				$goods_model = new NsGoodsModel();
				$sales_info = $goods_model->getInfo([
					'goods_id' => $goods_sku_info['goods_id']
				], 'real_sales');
				$goods_model->save([
					'real_sales' => $sales_info['real_sales'] - $v['num']
				], [
					"goods_id" => $goods_sku_info['goods_id']
				]);
				// 退货返回库存
				if ($return_stock == 1) {
					$data_goods_sku = array(
						'stock' => $goods_sku_info['stock'] + $v['num']
					);
					$goods_sku_model->save($data_goods_sku, [
						'sku_id' => $v['sku_id']
					]);
					$count = $goods_sku_model->getSum([
						'goods_id' => $goods_sku_info['goods_id']
					], 'stock');
					// 商品库存增加
					$goods_model = new NsGoodsModel();
					
					$goods_model->save([
						'stock' => $count
					], [
						"goods_id" => $goods_sku_info['goods_id']
					]);
				}
			}
			$action_data = array(
				"remark" => '订单交易关闭',
				"order_id" => $order_id,
				"uid" => $this->uid,
			);
			$this->addOrderAction($action_data);
			
			hook("orderCloseSuccess", [
				'order_id' => $order_id
			]);
			
			$this->order->commit();
			return 1;
		} catch (\Exception $e) {
			Log::write($e->getMessage());
			$this->order->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 订单完成
	 */
	public function orderComplete($order_id)
	{
		$this->order->startTrans();
		try {
			$data_complete_condition = array(
				'order_status' => 4,
				"finish_time" => time()
			);
			$order_model = new NsOrderModel();
			$order_model->save($data_complete_condition, [
				'order_id' => $order_id
			]);
			$action_data = array(
				"order_id" => $order_id,
				"uid" => $this->uid,
				"remark" => '交易完成',
			);
			$this->addOrderAction($action_data);
			$this->calculateOrderMansong($order_id);
			// 判断是否需要在本阶段赠送积分
			$this->giveGoodsOrderPoint($order_id, 1);
			
			//消息推送
//			runhook("Notify", "orderComplete", array(
//				"order_id" => $order_id
//			));
			message("order_complete", ["order_id" => $order_id]);
			hook("orderCompleteSuccess", [
				'order_id' => $order_id
			]);
			
			$this->order->commit();
			
			return 1;
		} catch (\Exception $e) {
			$this->order->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 订单调价
	 */
	public function orderMoneyAdjust($order_id, $order_goods_id_adjust_array, $shipping_fee)
	{
		// 调整订单
		$retval = $this->orderGoodsAdjustMoney($order_goods_id_adjust_array);
		if ($retval >= 0) {
			// 计算整体商品调整金额
			$this->createNewOutTradeNoReturnBalance($order_id);
			$order_query = new OrderQuery();
			$new_no = $order_query->getOrderNewOutTradeNo($order_id);
			$order_goods_money = $order_query->getOrderGoodsMoney($order_id);
			$retval_order = $this->orderAdjustMoney($order_id, $order_goods_money, $shipping_fee);
			$order_model = new NsOrderModel();
			$order_money = $order_model->getInfo([
				'order_id' => $order_id
			], 'pay_money');
			$pay = new UnifyPay();
			$pay->modifyPayMoney($new_no, $order_money['pay_money']);
			hook("orderMoneyAdjustSuccess", [
				'order_id' => $order_id,
				'order_goods_id_adjust_array' => $order_goods_id_adjust_array,
				'shipping_fee' => $shipping_fee
			]);
			return $retval_order;
		} else {
			return $retval;
		}
	}
	
	/**
	 * 订单价格调整
	 */
	public function orderAdjustMoney($order_id, $goods_money, $shipping_fee)
	{
		$this->order->startTrans();
		try {
			$order_model = new NsOrderModel();
			$order_info = $order_model->getInfo([
				'order_id' => $order_id
			], 'goods_money,shipping_money,order_money,pay_money');
			// 商品金额差额
			$goods_money_adjust = $goods_money - $order_info['goods_money'];
			$shipping_fee_adjust = $shipping_fee - $order_info['shipping_money'];
			$order_money = $order_info['order_money'] + $goods_money_adjust + $shipping_fee_adjust;
			$pay_money = $order_info['pay_money'] + $goods_money_adjust + $shipping_fee_adjust;
			$data = array(
				'goods_money' => $goods_money,
				'order_money' => $order_money,
				'shipping_money' => $shipping_fee,
				'pay_money' => $pay_money
			);
			$retval = $order_model->save($data, [
				'order_id' => $order_id
			]);
			$action_data = array(
				"order_id" => $order_id,
				"uid" => $this->uid,
				"remark" => '调整金额'
			);
			$this->addOrderAction($action_data);
			$this->order->commit();
			return $retval;
		} catch (\Exception $e) {
			$this->order->rollback();
			return 0;
		}
	}
	
	/**
	 * 订单项商品价格调整
	 */
	public function orderGoodsAdjustMoney($order_goods_id_adjust_array)
	{
		$order_goods = new NsOrderGoodsModel();
		$order_goods->startTrans();
		try {
			$order_goods_id_adjust_array = explode(';', $order_goods_id_adjust_array);
			if (!empty($order_goods_id_adjust_array)) {
				foreach ($order_goods_id_adjust_array as $k => $order_goods_id_adjust) {
					$order_goods_adjust_array = explode(',', $order_goods_id_adjust);
					$order_goods_id = $order_goods_adjust_array[0];
					$adjust_money = $order_goods_adjust_array[1];
					$order_goods_info = $order_goods->get($order_goods_id);
					// 调整金额
					$adjust_money_adjust = $adjust_money - $order_goods_info['adjust_money'];
					$data = array(
						'adjust_money' => $adjust_money,
						'goods_money' => $order_goods_info['goods_money'] + $adjust_money_adjust
					);
					$order_goods = new NsOrderGoodsModel();
					$order_goods->save($data, [
						'order_goods_id' => $order_goods_id
					]);
				}
			}
			$order_goods->commit();
			return 1;
		} catch (\Exception $e) {
			$order_goods->rollback();
			return $e;
		}
	}
	
	/**
	 * 订单收货
	 */
	public function OrderTakeDelivery($order_id)
	{
		//订单收货
		$this->order->startTrans();
		try {
			$data_take_delivery = array(
				'shipping_status' => 2,
				'order_status' => 3,
				'sign_time' => time(),
				'pay_status' => 2
			);
			$order_model = new NsOrderModel();
			$order_model->save($data_take_delivery, [
				'order_id' => $order_id
			]);
			$data = array(
				"order_id" => $order_id,
				"uid" => $this->uid,
				"remark" => "订单收货"
			);
			$this->addOrderAction($data);
			// 判断是否需要在本阶段赠送积分
			$this->giveGoodsOrderPoint($order_id, 2);
			hook("orderTakeDeliverySuccess", [
				'order_id' => $order_id
			]);
			$this->order->commit();
			return 1;
		} catch (\Exception $e) {
			
			$this->order->rollback();
			return $e->getMessage();
		}
		
	}
	
	/**
	 * 修改订单数据
	 */
	public function modifyOrderInfo($data, $order_id)
	{
		$order = new NsOrderModel();
		return $order->save($data, [
			'order_id' => $order_id
		]);
	}
	
	/**
	 * 订单提货
	 */
	public function pickupOrder($order_id, $buyer_name, $buyer_phone, $remark)
	{
		// 订单转为已收货状态
		$this->order->startTrans();
		try {
			$data_take_delivery = array(
				'shipping_status' => 2,
				'order_status' => 3,
				'sign_time' => time()
			);
			$order_model = new NsOrderModel();
			$order_model->save($data_take_delivery, [
				'order_id' => $order_id
			]);
			$action_data = array(
				"uid" => $this->uid,
				"remark" => '订单提货' . '提货人：' . $buyer_name . ' ' . $buyer_phone,
				"order_id" => $order_id
			);
			$this->addOrderAction($action_data);
			// 记录提货信息
			$order_pickup_model = new NsOrderPickupModel();
			$data_pickup = array(
				'buyer_name' => $buyer_name,
				'buyer_mobile' => $buyer_phone,
				'remark' => $remark,
				'picked_up_status' => 1,
				'auditor_id' => $this->uid,
				'picked_up_time' => time()
			);
			$order_pickup_model->save($data_pickup, [
				'order_id' => $order_id
			]);
			$order_goods_model = new NsOrderGoodsModel();
			$order_goods_model->save([
				'shipping_status' => 2
			], [
				'order_id' => $order_id
			]);
			$this->giveGoodsOrderPoint($order_id, 2);
			//自提订单出入库流程
			$delivery_res = $this->pickupDelivery([ "order_id" => $order_id ]);
			if ($delivery_res["code"] <= 0) {
				$this->order->rollback();
				return 0;
			}
			
			$this->order->commit();
			return 1;
		} catch (\Exception $e) {
			
			$this->order->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 自提出入库
	 * @param unknown $param
	 * @return string[]|mixed[]|unknown
	 */
	public function pickupDelivery($param)
	{
		//提货时循环执行出库操作
		$goods_service = new Goods();
		$order_goods_model = new NsOrderGoodsModel();
		$order_goods_list = $order_goods_model->getQuery([ 'order_id' => $param["order_id"] ], "goods_id, sku_id, num");
		foreach ($order_goods_list as $k => $v) {
			$delivery_res = $goods_service->delivery([ "sku_id" => $v["goods_id"], "num" => $v["num"], "type" => 2, "goods_id" => $v["goods_id"] ]);
			if ($delivery_res["code"] <= 0) {
				$this->order->rollback();
				return $delivery_res;
			}
		}
		return success();
	}
	
	/**
	 * 添加卖家对订单的备注
	 */
	public function addOrderSellerMemo($param)
	{
		$order = new NsOrderModel();
		$data = array(
			'seller_memo' => $param["memo"]
		);
		$retval = $order->save($data, [
			'order_id' => $param["order_id"]
		]);
		return $retval;
	}
	
	/**
	 * 更新订单的收货地址
	 */
	public function updateOrderReceiveDetail($data)
	{
		$order = new NsOrderModel();
		
		$retval = $order->save($data, [
			'order_id' => $data["order_id"]
		]);
		return $retval;
	}
	
	/**
	 * 删除订单
	 */
	public function deleteOrder($order_id, $operator_type, $operator_id)
	{
		$order_model = new NsOrderModel();
		$data = array(
			"is_deleted" => 1,
			"operator_type" => $operator_type,
			"operator_id" => $operator_id
		);
		$order_id_array = explode(',', $order_id);
		if ($operator_type == 1) {
			// 商家删除 目前之针对已关闭订单
			$order_model->save($data, [
				"order_status" => 5,
				"order_id" => [
					"in",
					$order_id_array
				],
				"shop_id" => $operator_id
			]);
		} elseif ($operator_type == 2) {
			// 用户删除
			$order_model->save($data, [
				"order_status" => 5,
				"order_id" => [
					"in",
					$order_id_array
				],
				"buyer_id" => $operator_id
			]);
		}
		return 1;
	}
	
	/**
	 * 添加订单操作日志
	 */
	public function addOrderAction($data)
	{
		//订单信息
		$order_model = new NsOrderModel();
		$order_info = $order_model->getInfo([ "order_id" => $data["order_id"] ], "order_status,order_type, shipping_type");
		//用户信息
		$user_model = new UserModel();
		$user_info = $user_model->getInfo([ "uid" => $data["uid"] ], "user_name");
		$order_status_info = $this->getOrderStatusInfo([ "order_type" => $order_info["order_type"], "order_status" => $order_info['order_status'], "shipping_type" => $order_info['shipping_type'] ]);
		$data_log = array(
			'order_id' => $data["order_id"],
			'action' => $data["remark"],
			'uid' => $data["uid"],
			'user_name' => empty($user_info["user_name"]) ? '' : $user_info["user_name"],
			'order_status' => $order_info['order_status'],
			'order_status_text' => $order_status_info["status_name"],
			'action_time' => time()
		);
		$order_action = new NsOrderActionModel();
		$order_action->save($data_log);
		return $order_action->action_id;
		
	}
	
	/**
	 * 订单完成后统计满减送赠送
	 */
	private function calculateOrderMansong($order_id)
	{
		$order_info = $this->order->getInfo([ "order_id" => $order_id ], 'shop_id, buyer_id');
		$order_promotion_details = new NsOrderPromotionDetailsModel();
		// 查询满减送活动规则
		$list = $order_promotion_details->getQuery([ 'order_id' => $order_id, 'promotion_type_id' => 1 ], 'promotion_id');
		if (!empty($list)) {
			$promotion_mansong_rule = new NsPromotionMansongRuleModel();
			foreach ($list as $k => $v) {
				$mansong_data = $promotion_mansong_rule->getInfo([ 'rule_id' => $v['promotion_id'] ], 'give_coupon,give_point');
				if (!empty($mansong_data)) {
					// 满减送赠送积分
					if ($mansong_data['give_point'] != 0) {
						$member_account = new MemberAccount();
						$member_account->addMemberAccountData($order_info['shop_id'], 1, $order_info['buyer_id'], 1, $mansong_data['give_point'], 1, $order_id, '订单满减送赠送积分');
					}
					// 满减送赠送优惠券
					if ($mansong_data['give_coupon'] != 0) {
						$member_coupon = new MemberCoupon();
						$member_coupon->userAchieveCoupon($order_info['buyer_id'], $mansong_data['give_coupon'], 1);
					}
				}
			}
		}
	}
	
	/**
	 * 订单发放积分
	 */
	public function giveGoodsOrderPoint($order_id, $type)
	{
		// 判断是否需要在本阶段赠送积分
		$order_model = new NsOrderModel();
		$order_info = $order_model->getInfo([
			"order_id" => $order_id
		], "give_point_type,shop_id,buyer_id,give_point,order_no");
		if ($order_info["give_point_type"] == $type) {
			if ($order_info["give_point"] > 0) {
				$member_account = new MemberAccount();
				$text = "";
				if ($order_info["give_point_type"] == 1) {
					$text = "商城订单完成赠送积分,订单号：" . $order_info['order_no'];
				} elseif ($order_info["give_point_type"] == 2) {
					$text = "商城订单完成收货赠送积分,订单号：" . $order_info['order_no'];
				} elseif ($order_info["give_point_type"] == 3) {
					$text = "商城订单完成支付赠送积分,订单号：" . $order_info['order_no'];
				}
				$member_account->addMemberAccountData($order_info['shop_id'], 1, $order_info['buyer_id'], 1, $order_info['give_point'], 1, $order_id, $text);
			}
		}
	}
	
	/**
	 * 订单长时间未付款自动交易关闭
	 */
	public function ordersClose()
	{
		$order_model = new NsOrderModel();
		try {
			$config = new Config();
			$config_info = $config->getConfig(0, 'ORDER_BUY_CLOSE_TIME');
			
			if (!empty($config_info['value']) && $config_info['value'] != 0) {
				$close_time = $config_info['value'];
			} else {
				return 1;
			}
			$time = time() - $close_time * 60;//订单自动关闭
			$condition = array(
				'order_status' => array( 'in', '0' ),
				'create_time' => array( 'LT', $time ),
				'payment_type' => array( 'neq', 6 )
			);
			$order_list = $order_model->getQuery($condition, 'order_id');
			$presell_order_condition = array(
				'order_status' => array( 'in', '6' ),
				'order_type' => 6,
				'create_time' => array( 'LT', $time ),
			);
			$presell_order_list = $order_model->getQuery($presell_order_condition, 'order_id');
			if (!empty($order_list)) {
				if (!empty($presell_order_list)) {
					foreach ($presell_order_list as $v) {
						array_push($order_list, $v);
					}
				}
				$order_action = new OrderAction();
				foreach ($order_list as $k => $v) {
					if (!empty($v['order_id'])) {
						$order_action->orderClose($v['order_id']);
					}
				}
				
			}
			return 1;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
		
	}
	
	/**
	 * 订单收货后7天自动交易完成
	 */
	public function ordersComplete()
	{
		$order_model = new NsOrderModel();
		try {
			$config = new Config();
			$config_info = $config->getConfig(0, 'ORDER_DELIVERY_COMPLETE_TIME');
			
			if ($config_info['value'] != '') {
				$complete_time = $config_info['value'];
			} else {
				$complete_time = 7;//7天
			}
			$time = time() - 3600 * 24 * $complete_time;//订单自动完成
			$condition = array(
				'order_status' => 3,
				'sign_time' => array( 'LT', $time )
			);
			$order_list = $order_model->getQuery($condition, 'order_id');
			if (!empty($order_list)) {
				$order_action = new OrderAction();
				foreach ($order_list as $k => $v) {
					if (!empty($v['order_id'])) {
						$order_action->orderComplete($v['order_id']);
					}
				}
				
			}
			
			return 1;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}
	/*****************************************************订单操作 end*****************************************************************************/
	/*****************************************************订单支付 begin*****************************************************************************/
	/**
	 * 订单在线支付
	 */
	public function orderOnLinePay($order_pay_no, $pay_type)
	{
		$retval = $this->OrderPay($order_pay_no, $pay_type, 0);
		try {
			if ($retval > 0) {
				
				// 订单的后续判断及操作
				$order_model = new NsOrderModel();
                $condition = array(
                    "out_trade_no" => $order_pay_no
                );
				$order_list = $order_model->getQuery($condition, "order_id");
				foreach ($order_list as $k => $v) {
//					runhook("Notify", "orderPay", array(
//						"order_id" => $v["order_id"]
//					));
					// 判断是否需要在本阶段赠送积分
					$this->giveGoodsOrderPoint($v["order_id"], 3);
				}
			}
		} catch (\Exception $e) {
			
			Log::write($e->getMessage());
		}
		if ($retval) {
//			$pay_info = $this->getPayTypeInfo([ "pay_type" => $pay_type ]);
//			$pay_type_name = $pay_info["type_name"];
			message("order_remind", [
                "out_trade_no" => $order_pay_no,
                "shop_id" => 0
            ]);//订单提醒
//			runhook('Notify', 'orderRemindBusiness', [
//				"out_trade_no" => $order_pay_no,
//				"shop_id" => 0
//			]); // 订单提醒
		}
		return $retval;
	}
	
	/**
	 * 订单线下支付
	 */
	public function orderOffLinePay($order_id, $pay_type, $status)
	{
		if ($pay_type == 10) {
			$this->underLinePaymentUpdateBalance($order_id);
		}
		$order_query = new OrderQuery();
		$new_no = $order_query->getOrderNewOutTradeNo($order_id);
		
		if ($new_no) {
			$retval = $this->OrderPay($new_no, $pay_type, $status);
			if ($retval > 0) {
				
//				runhook("Notify", "orderPay", array(
//					"order_id" => $order_id
//				));
				
				// 判断是否需要在本阶段赠送积分
				
				$this->giveGoodsOrderPoint($order_id, 3);
//				$pay_type_info = $this->getPayTypeInfo([ "pay_type" => $pay_type ]);
//				$pay_type_name = $pay_type_info["type_name"];

                message("order_remind", [
                    "out_trade_no" => $new_no,
                    "shop_id" => 0
                ]);//订单提醒
//				runhook('Notify', 'orderRemindBusiness', [
//					"out_trade_no" => $new_no,
//					"shop_id" => 0
//				]); // 订单提醒
			}
			return $retval;
		} else {
			return 0;
		}
	}
	
	/**
	 * 订单支付后续操作
	 */
	public function orderPaySuccess($data)
	{
		//订单支付后操作
		$result = hook("orderPaySuccessAction", $data);
		
		//         $result = arrayFilter($result);
		//         if(!empty($result[0]) && $result[0]["code"] <= 0){
		
		//             return error();
		//         }
		$user = new UserModel();
		$user_info = $user->getInfo([ "uid" => $data['buyer_id'] ], "nick_name");
		
		// 根据订单id查询订单项中的赠品集合，添加赠品发放记录
		$temp = $this->addPromotionGiftGrantRecords($data["order_id"], $data['buyer_id'], $user_info["nick_name"]);
		//虚拟商品
		if ($data["is_virtual"] == 1) {
			$param = array(
				"order_id" => $data["order_id"]
			);
			$this->virtualDelivery($param);
		}
		return success();
	}
	
	/**
	 * 订单支付
	 */
	public function orderPay($order_pay_no, $pay_type, $status)
	{
	    
		$this->order->startTrans();
		try {
			// 可能是多个订单
			$order_id_array = $this->order->where([ 'out_trade_no' => $order_pay_no, 'order_status' => 0 ])->column('order_id');
			// 检测是否支持拼团版本
			$account = new MemberAccount();
			foreach ($order_id_array as $k => $order_id) {
				$order_info = $this->order->getInfo([ 'order_id' => $order_id ], 'order_money,buyer_id,pay_money,user_name,order_type,order_no,tuangou_group_id,shipping_type, order_id, is_virtual');
				//订单验证
				$verify_data = hook("orderPayVerify", $order_info);
				$verify_data = arrayFilter($verify_data);
				if (!empty($verify_data[0]) && $verify_data[0]["code"] <= 0) {
					$this->order->rollback();
					return 0;
				}
				
				// 修改订单状态
				$data = array(
					'payment_type' => $pay_type,
					'pay_status' => 2,
					'pay_time' => time(),
					'order_status' => 1
				); // 订单转为待发货状态
				
				// 如果该订单为货到付款的话该订单的支付状态仍为未支付
				if ($pay_type == 4) {
					$data['pay_status'] = 0;
				}
				// 如果订单配送方式是自提的话在支付完成后生成自提码
				if ($order_info['shipping_type'] == 2) {
					$ns_order_pickup = new NsOrderPickupModel();
					$order_create = new OrderCreate();
					$pickup_code = $order_create->getPickupCode(0);
					$ns_order_pickup->save([
						'picked_up_code' => $pickup_code
					], [
						'order_id' => $order_id
					]);
				}
				
				$order = new NsOrderModel();
				$order->save($data, [
					'order_id' => $order_id
				]);
				//添加订单操作日志
				if ($pay_type == 10) {
					// 线下支付
					$action_data = array(
						"remark" => '线下支付',
						"uid" => $this->uid,
						"order_id" => $order_id
					);
					$this->addOrderAction($action_data);
				} else {
					// 查询订单购买人ID
					$action_data = array(
						"remark" => '订单支付',
						"uid" => $this->uid,
						"order_id" => $order_id
					);
					$this->addOrderAction($action_data);
				}
				// 增加会员累计消费
				$account->addMemberConsum(0, $order_info['buyer_id'], $order_info['order_money']);
				
				// 可能是多个订单
				$order_id_array = $this->order->where([ 'out_trade_no' => $order_pay_no, 'order_status' => 1 ])->column('order_id');
				
				if ($pay_type == 10) {
					$pay = new UnifyPay();
					$pay->offLinePay($order_pay_no, $pay_type);
				}
				hook('orderPaySuccess', [
					'order_pay_no' => $order_pay_no
				]);
				
				$res = $this->orderPaySuccess($order_info);
				$user_service = new Member();
				$user_service->checkMemberLevel(0, $order_info['buyer_id']);
				
				message("pay_success", ["order_id" => $order_id]);
				if ($res["code"] <= 0) {
					$this->order->rollback();
					return 0;
				}
			}
			$this->order->commit();
			return 1;
		} catch (\Exception $e) {
			$this->order->rollback();
			return 0;
		}
	}
	
	/**
	 * 重新生成交易流水号时返回之前锁定的余额
	 */
	public function createNewOutTradeNoReturnBalance($order_id)
	{
		$pay = new NsOrderPaymentModel();
		$order = new NsOrderModel();
		$order_info = $order->getInfo([
			'order_id' => $order_id,
			'order_status' => 0
		], "out_trade_no,buyer_id");
		if (!empty($order_info)) {
			$pay_info = $pay->getInfo([
				'out_trade_no' => $order_info['out_trade_no'],
				'pay_status' => 0
			], "balance_money,original_money");
			
			if (!empty($pay_info) && $pay_info['balance_money'] > 0) {
				
				$member_account = new MemberAccount();
				$member_account->addMemberAccountData(0, 2, $order_info['buyer_id'], 0, $pay_info['balance_money'], 1, $order_id, "订单重新生成交易号，返还锁定余额");
				$data = array(
					"pay_money" => $pay_info['original_money'],
					"balance_money" => 0
				);
				$pay->save($data, [
					'out_trade_no' => $order_info['out_trade_no']
				]);
			}
		}
	}
	
	/**
	 * 线下支付时判断用户是否选择使用了余额更新到订单表再执行线下支付
	 */
	public function underLinePaymentUpdateBalance($order_id)
	{
		$pay = new NsOrderPaymentModel();
		$order = new NsOrderModel();
		$order_info = $order->getInfo([
			'order_id' => $order_id,
			'order_status' => 0
		], "out_trade_no");
		if (!empty($order_info)) {
			$pay_info = $pay->getInfo([
				'out_trade_no' => $order_info['out_trade_no'],
				'pay_status' => 0
			], "balance_money,pay_money");
			if (!empty($pay_info) && $pay_info['balance_money'] > 0) {
				$data = array(
					"user_platform_money" => $pay_info['balance_money'],
					"pay_money" => $pay_info['pay_money']
				);
				$order->save($data, [
					"order_id" => $order_id
				]);
			}
		}
	}
	
	
	/**
	 * 线上支付时判断用户是否选择使用了余额更新到订单表再执行线上支付
	 */
	public function onLinePaymentUpdateBalance($out_trade_no)
	{
		$pay = new NsOrderPaymentModel();
		$order = new NsOrderModel();
		
		$pay_info = $pay->getInfo([
			'out_trade_no' => $out_trade_no,
			'pay_status' => 0
		], "balance_money,pay_money");
		
		if (!empty($pay_info) && $pay_info['balance_money'] > 0) {
			$data = array(
				"user_platform_money" => $pay_info['balance_money'],
				"pay_money" => $pay_info['pay_money']
			);
			$order->save($data, [
				"out_trade_no" => $out_trade_no
			]);
		}
	}
	
	/**
	 * 收到货款
	 */
	public function receivedPayment($order_id)
	{
		$ns_order = new NsOrderModel();
		$result = $ns_order->save([
			"pay_status" => 2
		], [
			"order_id" => $order_id
		]);
		return $result;
	}
	/*****************************************************订单支付  end*****************************************************************************/
	/*****************************************************订单发放赠品 begin*****************************************************************************/
	/**
	 * 根据订单id查询赠品发放记录需要的信息
	 */
	public function addPromotionGiftGrantRecords($order_id, $uid, $nick_name)
	{
		$order_goods_model = new NsOrderGoodsModel(); // 订单项
		$gift_model = new NsPromotionGiftModel();
		$promotion = new Promotion();
		
		// 查询赠品订单项
		$list = $order_goods_model->getQuery([
			'order_id' => $order_id,
			'gift_flag' => [
				'>',
				0
			]
		], "order_goods_id,goods_id,goods_name,goods_picture,gift_flag,shop_id");
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				
				// 查询赠品id，名称
				$gift_info = $gift_model->getInfo([
					'gift_id' => $v['gift_flag']
				], "gift_id,gift_name");
				
				if (!empty($gift_info)) {
					
					$type = 1;
					$type_name = "满减";
					$relate_id = $v['order_goods_id']; // 关联订单id
					$remark = "满减送赠品";
					$data = array(
						"shop_id" => $v['shop_id'],
						"uid" => $uid,
						"nick_name" => $nick_name,
						"gift_id" => $gift_info['gift_id'],
						"gift_name" => $gift_info['gift_name'],
						"goods_picture" => $v['goods_picture'],
						"goods_name" => $v['goods_name'],
						"type" => $type,
						"type_name" => $type_name,
						"relate_id" => $relate_id,
						"remark" => $remark,
						"create_time" => time(),
					);
					$res = $promotion->addPromotionGiftGrantRecords($data);
					return $res;
				}
			}
		}
	}
	
	/*****************************************************订单发放赠品 end*****************************************************************************/
	
	/**
	 * 获取交易号
	 */
	public function getOrderTradeNo()
	{
		$order_create = new OrderCreate();
		$no = $order_create->createOutTradeNo();
		return $no;
	}
	
	/**
	 * 订单重新生成订单号
	 */
	public function createNewOutTradeNo($orderid)
	{
		$order_create = new OrderCreate();
		$new_no = $order_create->createOutTradeNo();
		return $new_no;
	}
	
	/**
	 * 删除购物车中的数据
	 * 首先要查询当前商品在购物车中的数量，如果商品数量等于1则删除，如果商品数量大于1个，则减少该商品的数量
	 */
	public function deleteCart($goods_sku_list, $uid)
	{
		$cart = new NsCartModel();
		$goods_sku_list_array = explode(",", $goods_sku_list);
		foreach ($goods_sku_list_array as $k => $v) {
			$sku_data = explode(':', $v);
			$sku_id = $sku_data[0];
			$info = $cart->getInfo([
				'buyer_id' => $uid,
				'sku_id' => $sku_id
			], "num,cart_id");
			$cart_id = $info['cart_id'];
			$cart->destroy([
				'buyer_id' => $uid,
				'sku_id' => $sku_id
			]);
		}
		
	}
	
	/**
	 * 添加赠品的订单项
	 */
	public function addOrderGiftGoods($order_id, $goods_sku_list, $adjust_money = 0)
	{
		$this->order_goods->startTrans();
		try {
			$err = 0;
			$goods_sku_list_array = explode(",", $goods_sku_list);
			foreach ($goods_sku_list_array as $k => $goods_sku_array) {
				
				$goods_sku = explode(':', $goods_sku_array);
				$goods_sku_model = new NsGoodsSkuModel();
				$goods_sku_info = $goods_sku_model->getInfo([
					'sku_id' => $goods_sku[0]
				], 'sku_id,goods_id,cost_price,stock,sku_name,attr_value_items');
				
				// 如果当前商品有SKU图片，就用SKU图片。没有则用商品主图 2017年9月19日 15:46:38
				$picture = $this->getSkuPictureBySkuId($goods_sku_info);
				
				$goods_model = new NsGoodsModel();
				$goods_info = $goods_model->getInfo([
					'goods_id' => $goods_sku_info['goods_id']
				], 'goods_name,price,goods_type,picture,promotion_type,promote_id,point_exchange_type,give_point');
				
				$goods_promote = new GoodsPreference();
				$sku_price = $goods_promote->getGoodsSkuPrice($goods_sku_info['sku_id']);
				// 获取商品阶梯优惠后的价格
				$sku_price = $goods_promote->getGoodsLadderPreferentialPrice($goods_sku_info['sku_id'], $goods_sku[1], $sku_price);
				$goods_promote_info = $goods_promote->getGoodsPromote($goods_sku_info['goods_id']);
				if (empty($goods_promote_info)) {
					$goods_info['promotion_type'] = 0;
					$goods_info['promote_id'] = 0;
				}
				if ($goods_sku_info['stock'] < $goods_sku[1] || $goods_sku[1] <= 0) {
					$this->order_goods->rollback();
					return LOW_STOCKS;
				}
				$give_point = 0;
				
				// 库存减少销量增加
				$goods_calculate = new GoodsCalculate();
				$goods_calculate->subGoodsStock($goods_sku_info['goods_id'], $goods_sku_info['sku_id'], $goods_sku[1], '');
				$goods_calculate->addGoodsSales($goods_sku_info['goods_id'], $goods_sku_info['sku_id'], $goods_sku[1]);
				
				//查询赠品id
				$gift_goods_model = new NsPromotionGiftGoodsModel();
				$gift_goods_info = $gift_goods_model->getInfo([ 'goods_id' => $goods_sku_info['goods_id'] ], "gift_id");
				$gift_flag = 1;
				if (!empty($gift_goods_info)) {
					$gift_flag = $gift_goods_info['gift_id'];
				}
				
				$data_order_sku = array(
					'order_id' => $order_id,
					'goods_id' => $goods_sku_info['goods_id'],
					'goods_name' => $goods_info['goods_name'],
					'sku_id' => $goods_sku_info['sku_id'],
					'sku_name' => $goods_sku_info['sku_name'],
					'price' => 0, // $sku_price,赠品商品价格为0
					'num' => $goods_sku[1],
					'adjust_money' => 0,//$adjust_money,
					'cost_price' => $goods_sku_info['cost_price'],
					'goods_money' => 0,//$sku_price * $goods_sku[1] - $adjust_money,
					'goods_picture' => $picture != 0 ? $picture : $goods_info['picture'], // 如果当前商品有SKU图片，就用SKU图片。没有则用商品主图
					'shop_id' => 0,
					'buyer_id' => $this->uid,
					'goods_type' => $goods_info['goods_type'],
					'promotion_id' => $goods_info['promote_id'],
					'promotion_type_id' => $goods_info['promotion_type'],
					'point_exchange_type' => $goods_info['point_exchange_type'],
					'order_type' => 1, // 订单类型默认1
					'give_point' => $give_point,
					
					// 赠品id
					'gift_flag' => $gift_flag
				);
				
				// 积分数量默认0
				if ($goods_sku[1] == 0) {
					$err = 1;
				}
				$order_goods = new NsOrderGoodsModel();
				$order_goods->save($data_order_sku);
			}
			if ($err == 0) {
				$this->order_goods->commit();
				return 1;
			} elseif ($err == 1) {
				$this->order_goods->rollback();
				return ORDER_GOODS_ZERO;
			}
		} catch (\Exception $e) {
			$this->order_goods->rollback();
			return $e->getMessage();
		}
	}
	
}