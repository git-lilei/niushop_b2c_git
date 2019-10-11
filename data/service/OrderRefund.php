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

use addons\NsAlipay\data\service\AliPayVerify;
use data\model\AlbumPictureModel;
use data\model\NsCustomerServiceModel;
use data\model\NsCustomerServiceRecordsModel;
use data\model\NsGoodsModel;
use data\model\NsGoodsSkuModel;
use data\model\NsOrderCustomerAccountRecordsModel;
use data\model\NsOrderGoodsModel;
use data\model\NsOrderModel;
use data\model\NsOrderPaymentModel;
use data\model\NsOrderPresellModel;
use data\model\NsOrderRefundAccountRecordsModel;
use data\model\NsOrderRefundModel;
use data\model\NsOrderShopReturnModel;
use data\model\UserModel;
use data\service\Member\MemberAccount;
use data\service\Order\Order;
use data\service\Pay\UnionPay;
use data\service\Pay\WeiXinPay;
use data\model\BaseModel;

class OrderRefund extends Order
{
	/**************************************************************************************订单退款开始*************************************/
	
	/**
	 * 查询订单项退款信息
	 */
	public function getOrderGoodsRefundInfo($order_goods_id)
	{
		// 查询基础信息
		$order_goods_info = $this->order_goods->get($order_goods_id);
		
		// 商品图片
		$picture = new AlbumPictureModel();
		$picture_info = $picture->get($order_goods_info['goods_picture']);
		$order_goods_info['picture_info'] = $picture_info;
		if ($order_goods_info['refund_status'] != 0) {
			$order_refund_status_info = $this->getOrderRefundStatusInfo([ "refund_status" => $order_goods_info['refund_status'] ]);
			$order_goods_info['refund_operation'] = $order_refund_status_info['refund_operation'];
			$order_goods_info['status_name'] = $order_refund_status_info['status_name'];
			
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
		return $order_goods_info;
	}
	
	/**
	 * 退款申请
	 */
	public function orderGoodsRefundAskfor($order_id, $order_goods_id, $refund_type, $refund_require_money, $refund_reason)
	{
		$this->order_goods->startTrans();
		try {
			$status_id = $this->getOrderRefundStatus([])[1]['status_id'];
			// 订单项退款操作
			$order_goods = new NsOrderGoodsModel();
			$order_goods_info = $order_goods->getInfo(["order_goods_id" => $order_goods_id], "*");
			
			//赠品不可以退款和售后
			if($order_goods_info["gift_flag"] > 0){
			    $this->order_goods->rollback();
			    return REFUND_ERROR_GIFT;
			}
			//虚拟商品只能申请退款,不能退货
			if($order_goods_info["is_virtual"] == 1 && $refund_type == 2){
			    $this->order_goods->rollback();
			    return REFUND_ERROR_VIRTUAL;
			}
			$order_goods_data = array(
				'refund_status' => $status_id,
				'refund_time' => time(),
				'refund_type' => $refund_type,
				'refund_require_money' => $refund_require_money,
				'refund_reason' => $refund_reason
			);
			$order_goods->save($order_goods_data, [
				'order_goods_id' => $order_goods_id
			]);
			
			// 退款记录
			$this->addOrderRefundAction($order_goods_id, $status_id, 1, $this->uid);
			// 订单退款操作
			$res = $this->orderGoodsRefundFinish($order_id);
			
			
			//			runhook("Notify", "orderRefundBusiness", [
			//				"shop_id" => 0,
			//				"order_id" => $order_id
			//			]); // 商家退款提醒
			message("refund_order", [
			    "shop_id" => 0,
			    "order_id" => $order_id
			]);// 商家退款提醒
			//模板消息发送
			$params = [
			    'order_id' => $order_id,
			    'order_goods_id' => $order_goods_id,
			    'refund_type' => $refund_type,
			    'refund_require_money' => $refund_require_money,
			    'refund_reason' => $refund_reason
			];
			//			hook('orderGoodsRefundAskforSuccess', $params);
			message("refund_apply", $params);
			$this->order_goods->commit();
			return 1;
		} catch (\Exception $e) {
			$this->order_goods->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 买家取消退款
	 */
	public function orderGoodsCancel($order_id, $order_goods_id)
	{
		$this->order_goods->startTrans();
		try {
			$status_id = $this->getOrderRefundStatus([])[ -2 ]['status_id'];
			
			// 订单项退款操作
			$order_goods = new NsOrderGoodsModel();
			$order_goods_data = array(
				'refund_status' => $status_id
			);
			$order_goods->save($order_goods_data, [
				'order_goods_id' => $order_goods_id,
				'buyer_id' => $this->uid
			]);
			
			// 退款记录
			$this->addOrderRefundAction($order_goods_id, $status_id, 1, $this->uid);
			// 订单退款操作
			$this->orderGoodsRefundFinish($order_id);
			
			hook("orderGoodsCancelSuccess", [
				'order_id' => $order_id,
				'order_goods_id' => $order_goods_id
			]);
			
			$this->order_goods->commit();
			return 1;
		} catch (\Exception $e) {
			$this->order_goods->rollback();
			return $e->getMessage();
		}
		
	}
	
	/**
	 * 订单状态变更
	 */
	public function orderGoodsRefundFinish($order_id)
	{
		$orderInfo = NsOrderModel::get($order_id);
		$orderInfo->startTrans();
		try {
			$order_goods_model = new NsOrderGoodsModel();
			$order_info = $this->order->getInfo([ "order_id" => $order_id ]);
			$total_count = $order_goods_model->where(["order_id" => $order_id, 'gift_flag' => 0])->count();
			$refunding_count = $order_goods_model->where("order_id=$order_id AND refund_status<>0 AND refund_status<>5 AND refund_status>0")->count();
			$refunded_count = $order_goods_model->where("order_id=$order_id AND refund_status=5")->count();
			$shipping_status = $orderInfo->shipping_status;
			$all_refund = 0;
			if ($refunding_count > 0) {
				$orderInfo->order_status = $this->getOrderStatus([ "order_type" => $order_info["order_type"] ])[ -1 ]['status_id']; // 退款中
			} elseif ($refunded_count == $total_count) {
				$all_refund = 1;
			} elseif ($shipping_status == $this->getShippingStatus([ "order_type" => $order_info["order_type"] ])[0]['shipping_status']) {
				$orderInfo->order_status = $this->getOrderStatus([ "order_type" => $order_info["order_type"] ])[1]['status_id']; // 待发货
			} elseif ($shipping_status == $this->getShippingStatus([ "order_type" => $order_info["order_type"] ])[1]['shipping_status']) {
				
				$orderInfo->order_status = $this->getOrderStatus([ "order_type" => $order_info["order_type"] ])[2]['status_id']; // 已发货
			} elseif ($shipping_status == $this->getShippingStatus([ "order_type" => $order_info["order_type"] ])[2]['shipping_status']) {
				
				$orderInfo->order_status = $this->getOrderStatus([ "order_type" => $order_info["order_type"] ])[3]['status_id']; // 已收货
			}
			
			$order_action = new OrderAction();
			// 订单恢复正常操作
			if ($all_refund == 0) {
				$retval = $orderInfo->save();
				if ($refunding_count == 0) {
					$order_action->orderDoDelivery($order_id);
				}
			} else {
				// 全部退款订单转化为交易关闭
				$retval = $order_action->orderClose($order_id);
			}
			
			$orderInfo->commit();
			return $retval;
		} catch (\Exception $e) {
			$orderInfo->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 买家退货
	 */
	public function orderGoodsReturnGoods($order_id, $order_goods_id, $refund_shipping_company, $refund_shipping_code)
	{
		$order_goods = NsOrderGoodsModel::get($order_goods_id);
		$order_goods->startTrans();
		try {
			$status_id = $this->getOrderRefundStatus([])[3]['status_id'];
			// 订单项退款操作
			$order_goods->refund_status = $status_id;
			$order_goods->refund_shipping_company = $refund_shipping_company;
			$order_goods->refund_shipping_code = $refund_shipping_code;
			$retval = $order_goods->save();
			
			// 退款记录
			$this->addOrderRefundAction($order_goods_id, $status_id, 1, $this->uid);
			
			$params = [
				'order_id' => $order_id,
				'order_goods_id' => $order_goods_id,
				'refund_shipping_company' => $refund_shipping_company,
				'refund_shipping_code' => $refund_shipping_code
			];
			hook("orderGoodsReturnGoodsSuccess", $params);
			$order_goods->commit();
			return $retval;
		} catch (\Exception $e) {
			$order_goods->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 卖家同意退款
	 */
	public function orderGoodsRefundAgree($order_id, $order_goods_id)
	{
		$this->order_goods->startTrans();
		try {
			
			// 退款信息
			$orderGoodsInfo = NsOrderGoodsModel::get($order_goods_id);
			$refund_type = $orderGoodsInfo->refund_type;
			if ($refund_type == 1) { // 仅退款
				$status_id = $this->getOrderRefundStatus()[4]['status_id'];
			} else { // 退货退款
				$status_id = $this->getOrderRefundStatus()[2]['status_id'];
			}
			
			// 订单项退款操作
			$order_goods = new NsOrderGoodsModel();
			$order_goods_data = array(
				'refund_status' => $status_id
			);
			$res = $order_goods->save($order_goods_data, [
				'order_goods_id' => $order_goods_id
			]);
			
			// 退款记录
			
			$this->addOrderRefundAction($order_goods_id, $status_id, 2, $this->uid);
			
			hook("orderGoodsRefundAgreeSuccess", [
				'order_id' => $order_id,
				'order_goods_id' => $order_goods_id
			]);
			
			$this->order_goods->commit();
			return 1;
		} catch (\Exception $e) {
			$this->order_goods->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 卖家永久拒绝退款
	 */
	public function orderGoodsRefuseForever($order_id, $order_goods_id)
	{
		$this->order_goods->startTrans();
		try {
			
			$status_id = $this->getOrderRefundStatus()[ -1 ]['status_id'];
			// 订单项退款操作
			$order_goods = new NsOrderGoodsModel();
			$order_goods_data = array(
				'refund_status' => $status_id
			);
			$order_goods->save($order_goods_data, [
				'order_goods_id' => $order_goods_id
			]);
			
			// 退款记录
			$this->addOrderRefundAction($order_goods_id, $status_id, 2, $this->uid);
			// 订单恢复正常操作
			
			$this->orderGoodsRefundFinish($order_id);
			
			hook("orderGoodsRefuseForeverSuccess", [
				'order_id' => $order_id,
				'order_goods_id' => $order_goods_id
			]);
			$this->order_goods->commit();
			return 1;
		} catch (\Exception $e) {
			$this->order_goods->rollback();
			return $e;
		}
	}
	
	/**
	 * 卖家拒绝本次退款
	 */
	public function orderGoodsRefuseOnce($order_id, $order_goods_id)
	{
		$this->order_goods->startTrans();
		try {
			$status_id = $this->getOrderRefundStatus()[ -3 ]['status_id'];
			
			// 订单项退款操作
			$order_goods = new NsOrderGoodsModel();
			$order_goods_data = array(
				'refund_status' => $status_id
			);
			$order_goods->save($order_goods_data, [
				'order_goods_id' => $order_goods_id
			]);
			
			// 退款日志
			$this->addOrderRefundAction($order_goods_id, $status_id, 2, $this->uid);
			// 订单恢复正常操作
			$this->orderGoodsRefundFinish($order_id);
			
			hook("orderGoodsRefuseOnceSuccess", [
				'order_id' => $order_id,
				'order_goods_id' => $order_goods_id
			]);
			
			$this->order_goods->commit();
			return 1;
		} catch (\Exception $e) {
			$this->order_goods->rollback();
			return $e;
		}
		
	}
	
	/**
	 * 卖家确认收货
	 */
	public function orderGoodsConfirmRecieve($order_id, $order_goods_id, $storage_num, $isStorage, $goods_id, $sku_id)
	{
		$this->order_goods->startTrans();
		try {
			$status_id = $this->getOrderRefundStatus()[4]['status_id'];
			$goods_service = new Goods();
			// 订单项退款操作
			$order_goods = new NsOrderGoodsModel();
			$order_goods_data = array(
				'refund_status' => $status_id
			);
			$res = $order_goods->save($order_goods_data, [
				'order_goods_id' => $order_goods_id
			]);
			
			// 退款记录
			$this->addOrderRefundAction($order_goods_id, $status_id, 2, $this->uid);
			if ($isStorage > 0) {
				$goods_sku = new NsGoodsSkuModel();
				$goods = new NsGoodsModel();
				
				//退货入库(返回商品和sku的库存)
				$receive_res = $goods_service->receive(["sku_id" => $sku_id, "goods_id" => $goods_id, "num" => $storage_num, "type" => 2]);
				if($receive_res["code"] <= 0){
				    $this->order_goods->rollback();
				    return $receive_res["code"];
				}
// 				// 商品sku表入库
// 				$goods_sku->where(["goods_id" => $goods_id,"sku_id" => $sku_id])->setInc('stock', $storage_num);
// 				// 商品表入库
// 				$goods->where(["goods_id" => $goods_id])->setInc('stock', $storage_num);
			}
			hook("orderGoodsConfirmRecieveSuccess", [
				'order_id' => $order_id,
				'order_goods_id' => $order_goods_id
			]);
			$this->order_goods->commit();
			return 1;
		} catch (\Exception $e) {
			$this->order_goods->rollback();
			return $e;
		}
	}
	
	/**
	 * 卖家确认退款
	 */
	public function orderGoodsConfirmRefund($order_id, $order_goods_id, $refund_real_money, $refund_balance_money, $refund_way, $refund_remark){
	    $order_model = new NsOrderModel();
	    $order_info = $order_model->getInfo(["order_id" => $order_id], "pay_money,user_platform_money,refund_money,refund_balance_money,order_type,pay_status");
	    
	    // 如果是预售订单
	    if($order_info['order_type'] == 6){
	        $ns_presell_order = new NsOrderPresellModel();
	        $presell_order_info = $ns_presell_order -> getInfo(["relate_id" => $order_id], "is_full_payment,presell_pay,platform_money");
	        if($presell_order_info['is_full_payment'] == 0 && $order_info['pay_status'] > 0){
	            $presell_order_info['presell_pay'] += $order_info['pay_money'];
	            $presell_order_info['platform_money'] += $order_info['user_platform_money'];
	        }
	        $can_refund_money = round(($presell_order_info['presell_pay'] + $presell_order_info['platform_money']), 2);
	        $surplus_balance = $presell_order_info['platform_money'];
	    }else{
            $can_refund_money = round(($order_info['pay_money'] + $order_info['user_platform_money']) - ($order_info['refund_money'] + $order_info['refund_balance_money']), 2); // 最大可退金额
    	    $surplus_balance = round($order_info['user_platform_money'] - $order_info['refund_balance_money'], 2); // 剩余可退余额
	    }
	    
	    if($refund_real_money > $can_refund_money) return "退款金额超出可退金额，退款失败";	    
	    
	    if($surplus_balance > $refund_real_money){
	        $refund_balance_money = $refund_real_money;
	        $refund_real_money = 0.00;
	    }else{
	        $refund_balance_money = $surplus_balance;
	        $refund_real_money -= $surplus_balance;
	    }
	    
	    $refund_trade_no = date("YmdHis", time()) . rand(100000, 999999);
	    // 在线原路退款（微信/支付宝）
	    $refund = $this->onlineOriginalRoadRefund($order_id, $refund_real_money, $refund_way, $refund_trade_no, $order_info['pay_money']);
	    if ($refund['is_success'] == 1) {
	        $retval = $this->orderGoodsConfirmRefundAction($order_id, $order_goods_id, $refund_real_money, $refund_balance_money, $refund_trade_no, $refund_way, $refund_remark);
	        if ($retval) {
	            hook("orderGoodsConfirmRefundSuccess", [
	                'order_id' => $order_id,
	                'order_goods_id' => $order_goods_id,
	                'refund_real_money' => $refund_real_money
	            ]);
	        }
	        return $retval;
	    } else {
	        return $refund['msg'];
	    }
	}
	
	/**
	 * 卖家确认退款
	 *
	 * @param 订单id $order_id
	 * @param 订单项id $order_goods_id
	 * @param 实际退款金额 $refund_real_money
	 * @param 退款余额 $refund_balance_money
	 * @param 退款交易号 $refund_trade_no
	 * @param 退款方式（1：微信，2：支付宝，10：线下） $refund_way
	 * @param 备注 $refund_remark
	 */
	public function orderGoodsConfirmRefundAction($order_id, $order_goods_id, $refund_real_money, $refund_balance_money, $refund_trade_no, $refund_way, $refund_remark)
	{
		$order_goods = NsOrderGoodsModel::get($order_goods_id);
		$order_goods->startTrans();
		try {
			$status_id = $this->getOrderRefundStatus([])[5]['status_id'];
			
			// 订单项退款操作
			$order_goods->refund_type = $refund_way;
			$order_goods->refund_status = $status_id;
			$order_goods->refund_real_money = $refund_real_money; // 退款金额
			$order_goods->refund_balance_money = $refund_balance_money; // 退款余额
			$order_goods->save();
			
			// 执行余额账户修正
			// 退款记录
			$this->addOrderRefundAction($order_goods_id, $status_id, 2, $this->uid);
			$order_model = new NsOrderModel();
			
			// 订单添加退款金额、余额
			$order_info = $order_model->getInfo([
				'order_id' => $order_id
			], '*');
			$member_account = new MemberAccount();
			$member_account->addMemberConsum($order_info['shop_id'], $order_info['buyer_id'], ($refund_real_money + $refund_balance_money) * -1);
			$order_query = new OrderQuery();
			$freight = $order_query->getRefundFreight($order_id, $order_goods_id);
			
			// 添加退款帐户记录
			if (empty($refund_remark)) {
				$pay_type_info = $this->getPayTypeInfo([ "pay_type" => $refund_way ]);
				$remark = "订单编号:" . $order_info['order_no'] . "，退款方式为:[" . $pay_type_info["type_name"] . "]，退款金额:" . $refund_real_money . "元，退款余额：" . $refund_balance_money . "元";
				if ($freight > 0) {
					$remark .= '，含运费' . $freight . '元';
				}
			} else {
				$remark = $refund_remark;
			}
			
			$this->addOrderRefundAccountRecords($order_goods_id, $refund_trade_no, $refund_real_money, $refund_way, $order_info['buyer_id'], $remark);
			
			$order_model->save([
				'refund_money' => $order_info['refund_money'] + $refund_real_money,
				'refund_balance_money' => $order_info['refund_balance_money'] + $refund_balance_money
			], [
				'order_id' => $order_id
			]);
			$this->orderGoodsRefundExt($order_id, $order_goods_id, $refund_balance_money);
			
			// 订单恢复正常操作
			$retval = $this->orderGoodsRefundFinish($order_id);
			
			// 退款是 扣除已发放的积分
			$give_point = $order_goods["give_point"];
			if ($order_info["give_point_type"] == 3) {
				$member_account = new MemberAccount();
				$text = "退款成功,扣除已发放的积分";
				$member_account->addMemberAccountData($order_info['shop_id'], 1, $order_info['buyer_id'], 0, -$give_point, 1, $order_id, $text);
			}
			
			$total_point = $order_info["give_point"] - $give_point;
			
			$order_model->save([
				"give_point" => $total_point
			], [
				'order_id' => $order_id
			]);
			
			
			message("refund_result", [
			    'order_id' => $order_id,
			    'order_goods_id' => $order_goods_id,
			    'refund_real_money' => $refund_real_money
			]);
			
			$order_goods->commit();
			
			return 1;
		} catch (\Exception $e) {
			$order_goods->rollback();
			return $e->getMessage();
		}
	}
	
	
	/**
	 * 订单项目退款处理
	 */
	private function orderGoodsRefundExt($order_id, $order_goods_id, $refund_balance_money)
	{
		$order_model = new NsOrderModel();
		$order_info = $order_model->getInfo([
			'order_id' => $order_id
		], '*');
		
		$member_account = new MemberAccount();
		
		if ($refund_balance_money > 0) {
			$member_account->addMemberAccountData($order_info['shop_id'], 2, $order_info['buyer_id'], 1, $refund_balance_money, 2, $order_id, '订单退款');
		}
	}
	
	/**
	 * 添加订单退款账号记录
	 */
	public function addOrderRefundAccountRecords($order_goods_id, $refund_trade_no, $refund_money, $refund_way, $buyer_id, $remark)
	{
		$model = new NsOrderRefundAccountRecordsModel();
		
		$data = array(
			'order_goods_id' => $order_goods_id,
			'refund_trade_no' => $refund_trade_no,
			'refund_money' => $refund_money,
			'refund_way' => $refund_way,
			'buyer_id' => $buyer_id,
			'refund_time' => time(),
			'remark' => $remark
		);
		$res = $model->save($data);
		return $res;
	}
	
	/**
	 * 在线原路退款（微信、支付宝）
	 */
	private function onlineOriginalRoadRefund($order_id, $refund_fee, $refund_way, $refund_trade_no, $total_fee)
	{
		// 1.根据订单id查询外部交易号
		$order_model = new NsOrderModel();
		$out_trade_no = $order_model->getInfo(['order_id' => $order_id], "out_trade_no");
		
		// 2.根据外部交易号查询trade_no（交易号）支付宝支付会返回一个交易号，微信传空
		$ns_order_payment_model = new NsOrderPaymentModel();
		$trade_no = $ns_order_payment_model->getInfo(["out_trade_no" => $out_trade_no['out_trade_no']], 'trade_no');
		
		// 3.根据用户选择的退款方式，进行不同的原路退款操作
        if($refund_way == 1){
            $addon_name = "NsWeixinpay";
        }else if($refund_way == 2){
            $addon_name = "NsAlipay";
        }else if($refund_way == 3){
            $addon_name = "NsUnionPay";
        }else{
            $retval = array("is_success" => 1,'msg' => "");
            return $retval;
        }
        $result = hook("refund", ["addon_name" => $addon_name, "out_trade_no" => $out_trade_no['out_trade_no'], "refund_fee" => $refund_fee, "total_fee" => $total_fee, "trade_no" => $trade_no['trade_no'],"refund_no" => $refund_trade_no]);
        $result = arrayFilter($result);
        $result = $result[0];
        return $result;
	}
	
	/**
	 * 更新店铺的退货信息
	 */
	public function updateShopReturnSet($shop_id, $address, $real_name, $mobile, $zipcode)
	{
		$shop_return = new NsOrderShopReturnModel();
		$data = array(
			"shop_address" => $address,
			"seller_name" => $real_name,
			"seller_mobile" => $mobile,
			"seller_zipcode" => $zipcode,
			"modify_time" => time()
		);
		$result_id = $shop_return->save($data, [
			"shop_id" => $shop_id
		]);
		return $result_id;
	}
	
	/**
	 * 申请售后
	 */
	public function orderGoodsCustomerServiceAskfor($order_goods_id, $refund_type, $refund_money, $refund_reason)
	{
		$customer_service = new NsCustomerServiceModel();
		$customer_service->startTrans();
		try {
			$ns_order_goods = new NsOrderGoodsModel();
			$order_goods_info = $ns_order_goods->getInfo([ 'order_goods_id' => $order_goods_id ], '*');
			
			//赠品不可以退款和售后
			if($order_goods_info["gift_flag"] > 0){
			    $customer_service->rollback();
			    return REFUND_ERROR_GIFT;
			}
			//虚拟商品只能申请退款,不能退货
			if($order_goods_info["is_virtual"] == 1 && $refund_type == 2){
			    $customer_service->rollback();
			    return REFUND_ERROR_VIRTUAL;
			}
			$ns_order = new NsOrderModel();
			$order_info = $ns_order->getInfo([ 'order_id' => $order_goods_info['order_id'] ], '*');
			$count = $customer_service->getCount([ "order_goods_id" => $order_goods_id, "audit_status" => [ "not in", "-1,-2,-3,5" ] ]);
			if ($count > 0) {
				$customer_service->rollback();
				return AFTER_SALE_EXIST;
			}
			
			// 添加售后记录
			$data = array(
				'goods_id' => $order_goods_info['goods_id'],
				'order_id' => $order_goods_info['order_id'],
				'order_no' => $order_info['order_no'],
				'order_goods_id' => $order_goods_id,
				'goods_name' => $order_goods_info['goods_name'],
				'sku_id' => $order_goods_info['sku_id'],
				'sku_name' => $order_goods_info['sku_name'],
				'price' => $order_goods_info['price'],
				'goods_picture' => $order_goods_info['goods_picture'],
				'num' => $order_goods_info['num'],
				'goods_money' => $order_goods_info['goods_money'],
				'shop_id' => 0,
				'buyer_id' => $order_goods_info['buyer_id'],
				'order_type' => $order_goods_info['order_type'],
				'refund_money' => $refund_money,
				'refund_way' => $refund_type,
				'refund_reason' => $refund_reason,
				'audit_status' => 1,
				'order_from' => $order_info['order_from'],
				'receiver_province' => $order_info['receiver_province'],
				'receiver_city' => $order_info['receiver_city'],
				'receiver_district' => $order_info['receiver_district'],
				'receiver_address' => $order_info['receiver_address'],
				'receiver_mobile' => $order_info['receiver_mobile'],
				'payment_type' => $order_info['payment_type'],
				'fixed_telephone' => $order_info['fixed_telephone'],
				'receiver_name' => $order_info['receiver_name'],
				'user_name' => $order_info['user_name'],
				'create_time' => time()
			);
			$customer_service->save($data);
			// 售后记录
			$status_id = $this->getOrderRefundStatus()[1]['status_id'];
			$this->addOrderCustomerServiceAction($order_goods_id, $status_id, 1, $this->uid);
			
			$customer_service->commit();
			return 1;
		} catch (\Exception $e) {
			$customer_service->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 售后申请 同意
	 */
	public function orderGoodsCustomerAgree($id, $order_id, $order_goods_id)
	{
		$customer_service = new NsCustomerServiceModel();
		$customer_service->startTrans();
		try {
			// 退款信息
			$customer_service_info = $customer_service->getInfo([ "order_goods_id" => $order_goods_id ]);
			$refund_type = $customer_service_info["refund_way"];
			if ($refund_type == 1) { // 仅退款
				$status_id = $this->getOrderRefundStatus()[4]['status_id'];
			} else { // 退货退款
				$status_id = $this->getOrderRefundStatus()[2]['status_id'];
			}
			// 订单项退款操作
			$customer_service = new NsCustomerServiceModel();
			$order_goods_data = array(
				'audit_status' => $status_id
			);
			$customer_service->save($order_goods_data, [
				'order_goods_id' => $order_goods_id,
				'id' => $id
			]);
			
			// 退款记录
			$this->addOrderCustomerServiceAction($order_goods_id, $status_id, $refund_type, $this->uid);
			
			hook("orderGoodsRefundAgreeSuccess", [
				'order_id' => $order_id,
				'order_goods_id' => $order_goods_id
			]);
			$customer_service->commit();
			return 1;
		} catch (\Exception $e) {
			$customer_service->rollback();
			return $e->getMessage();
		}
		
	}
	
	/**
	 * 添加售后日志
	 */
	public function addOrderCustomerServiceAction($order_goods_id, $refund_status_id, $action_way, $uid)
	{
		$refund_status_info = $this->getOrderRefundStatusInfo([ "refund_status" => $refund_status_id ]);
		$refund_status_name = $refund_status_info['status_name'];
		$user = new UserModel();
		$user_name = $user->getInfo([
			'uid' => $uid
		], 'user_name');
		$cs_records = new NsCustomerServiceRecordsModel();
		$data_refund = array(
			'order_goods_id' => $order_goods_id,
			'refund_status' => $refund_status_id,
			'action' => $refund_status_name,
			'action_way' => $action_way,
			'action_userid' => $uid,
			'action_username' => $user_name['user_name'],
			'action_time' => time()
		);
		$retval = $cs_records->save($data_refund);
		return $retval;
	}
	
	/**
	 * 售后 拒绝永久
	 */
	public function orderCustomerRefuseForever($id, $order_id, $order_goods_id)
	{
		$customer_service = new NsCustomerServiceModel();
		$customer_service->startTrans();
		try {
			
			$status_id = $this->getOrderRefundStatus()[ -1 ]['status_id'];
			// 订单项退款操作
			$customer_service = new NsCustomerServiceModel();
			$order_goods_data = array(
				'audit_status' => $status_id
			);
			$customer_service->save($order_goods_data, [
				'order_goods_id' => $order_goods_id,
				'id' => $id
			]);
			
			// 退款记录
			$this->addOrderCustomerServiceAction($order_goods_id, $status_id, 2, $this->uid);
			hook("orderGoodsRefuseForeverSuccess", [
				'order_id' => $order_id,
				'order_goods_id' => $order_goods_id
			]);
			$customer_service->commit();
			return 1;
		} catch (\Exception $e) {
			$customer_service->rollback();
			return $e;
		}
		
	}
	
	/**
	 * 售后 拒绝一次
	 */
	public function orderCustomerRefuseOnce($id, $order_id, $order_goods_id)
	{
		$customer_service = new NsCustomerServiceModel();
		$customer_service->startTrans();
		try {
			$status_id = $this->getOrderRefundStatus()[ -3 ]['status_id'];
			
			// 订单项退款操作
			$customer_service = new NsCustomerServiceModel();
			$order_goods_data = array(
				'audit_status' => $status_id
			);
			$customer_service->save($order_goods_data, [
				'order_goods_id' => $order_goods_id,
				'id' => $id
			]);
			
			// 退款日志
			$this->addOrderCustomerServiceAction($order_goods_id, $status_id, 2, $this->uid);
			hook("orderGoodsRefuseOnceSuccess", [
				'order_id' => $order_id,
				'order_goods_id' => $order_goods_id
			]);
			$customer_service->commit();
			return 1;
		} catch (\Exception $e) {
			$customer_service->rollback();
			return $e;
		}
	}
	
	/**
	 * 买家退货 售后
	 */
	public function orderGoodsCustomerExpress($id, $order_goods_id, $refund_shipping_company, $refund_shipping_code)
	{
		$customer_service = new NsCustomerServiceModel();
		$customer_service->startTrans();
		try {
			$status_id = $this->getOrderRefundStatus()[3]['status_id'];
			// 订单项退款操作
			$customer_service = new NsCustomerServiceModel();
			$order_goods_data = array(
				'audit_status' => $status_id,
				'refund_shipping_company' => $refund_shipping_company,
				'refund_shipping_code' => $refund_shipping_code
			);
			$customer_service->save($order_goods_data, [
				'order_goods_id' => $order_goods_id,
				'id' => $id
			]);
			
			// 退款记录
			$this->addOrderCustomerServiceAction($order_goods_id, $status_id, 1, $this->uid);
			
			$params = [
				'order_goods_id' => $order_goods_id,
				'refund_shipping_company' => $refund_shipping_company,
				'refund_shipping_code' => $refund_shipping_code
			];
			hook("orderGoodsReturnGoodsSuccess", $params);
			$customer_service->commit();
			return 1;
		} catch (\Exception $e) {
			$customer_service->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 售后 卖家确认收货
	 */
	public function orderCustomerConfirmReceive($id, $order_id, $order_goods_id, $storage_num, $isStorage, $goods_id, $sku_id)
	{
		$customer_service = new NsCustomerServiceModel();
		$customer_service->startTrans();
		try {
			$status_id = $this->getOrderRefundStatus()[4]['status_id'];
			
			// 订单项退款操作
			$customer_service = new NsCustomerServiceModel();
			$order_goods_data = array(
				'audit_status' => $status_id
			);
			$customer_service->save($order_goods_data, [
				'order_goods_id' => $order_goods_id,
				'id' => $id
			]);
			
			// 退款记录
			$this->addOrderCustomerServiceAction($order_goods_id, $status_id, 2, $this->uid);
			if ($isStorage > 0) {
				$goods_sku = new NsGoodsSkuModel();
				$goods = new NsGoodsModel();
				// 商品sku表入库
				$goods_sku->where([
					"goods_id" => $goods_id,
					"sku_id" => $sku_id
				])->setInc('stock', $storage_num);
				// 商品表入库
				$goods->where([
					"goods_id" => $goods_id
				])->setInc('stock', $storage_num);
			}
			
			hook("orderGoodsConfirmRecieveSuccess", [
				'order_id' => $order_id,
				'order_goods_id' => $order_goods_id
			]);
			$customer_service->commit();
			return 1;
		} catch (\Exception $e) {
			$customer_service->rollback();
			return $e;
		}
	}

	/**
	 * 售后 确认退款
	 */
	public function orderCustomerConfirmRefund($id, $order_id, $order_goods_id, $refund_real_money, $refund_balance_money, $refund_way, $refund_remark)
	{
	    $order_model = new NsOrderModel();
	    $customer_service = new NsCustomerServiceModel();
	    $order_info = $order_model->getInfo(["order_id" => $order_id], "pay_money,user_platform_money,refund_money,refund_balance_money,order_type,pay_status");
	    
	    $refunded_money = $customer_service->getSum(["order_id" => $order_id], 'refund_money'); // 已退还的金额
	    $refunded_balance = $customer_service->getSum(["order_id" => $order_id], 'refund_balance_money'); // 已退还的余额
	    // 如果是预售订单
	    if($order_info['order_type'] == 6){
	        $ns_presell_order = new NsOrderPresellModel();
	        $presell_order_info = $ns_presell_order -> getInfo(["relate_id" => $order_id], "is_full_payment,presell_pay,platform_money");
	        if($presell_order_info['is_full_payment'] == 0 && $order_info['pay_status'] > 0){
	            $presell_order_info['presell_pay'] += $order_info['pay_money'];
	            $presell_order_info['platform_money'] += $order_info['user_platform_money'];
	        }
	        $can_refund_money = round(($presell_order_info['presell_pay'] + $presell_order_info['platform_money']) - ($refunded_money + $refunded_balance), 2);
	        $surplus_balance = round($presell_order_info['platform_money'] - $refunded_balance);
	    }else{
	        $can_refund_money = round(($order_info['pay_money'] + $order_info['user_platform_money']) - ($refunded_money + $refunded_balance), 2); // 最大可退金额
	        $surplus_balance = round($order_info['user_platform_money'] - $order_info['refund_balance_money'], 2); // 剩余可退余额
	    }
	    if($refund_real_money > $can_refund_money) return "退款金额超出可退金额，退款失败";
	     
	    if($surplus_balance > $refund_real_money){
	        $refund_balance_money = $refund_real_money;
	        $refund_real_money = 0.00;
	    }else{
	        $refund_balance_money = $surplus_balance;
	        $refund_real_money -= $surplus_balance;
	    }
	    
	    if ($refund_real_money > $can_refund_money) {
	        return "实际退款超过订单支付金额，退款失败";
	    } else {
	        	
	        $refund_trade_no = date("YmdHis", time()) . rand(100000, 999999);
	        // 在线原路退款（微信/支付宝）
	        $refund = $this->onlineOriginalRoadRefund($order_id, $refund_real_money, $refund_way, $refund_trade_no, $order_info['pay_money']);
	        if ($refund['is_success'] == 1) {
	    
	            $retval = $this->orderGoodsCustomerConfirmRefund($id, $order_id, $order_goods_id, $refund_real_money, $refund_balance_money, $refund_trade_no, $refund_way, $refund_remark);
	    
	            if ($retval) {
	                //					hook("orderGoodsConfirmRefundSuccess", [
	                //						'order_id' => $order_id,
	                //						'order_goods_id' => $order_goods_id,
	                //						'refund_real_money' => $refund_real_money
	                //					]);
	                message("refund_result", [
	                    'order_id' => $order_id,
	                    'order_goods_id' => $order_goods_id,
	                    'refund_real_money' => $refund_real_money
	                ]);
	            }
	            return $retval;
	        } else {
	            return $refund['msg'];
	        }
	    }
	}
	
	/**
	 * 卖家确认退款 售后
	 */
	public function orderGoodsCustomerConfirmRefund($id, $order_id, $order_goods_id, $refund_real_money, $refund_balance_money, $refund_trade_no, $refund_way, $refund_remark)
	{
		$customer_service = new NsCustomerServiceModel();
		$customer_service->startTrans();
		try {
			$status_id = $this->getOrderRefundStatus()[5]['status_id'];
			
			// 订单项退款操作
			$customer_service = new NsCustomerServiceModel();
			$order_goods_data = array(
				'audit_status' => $status_id,
				'refund_real_money' => $refund_real_money, // 退款金额
				'refund_balance_money' => $refund_balance_money,  // 退款余额
				'audit_time' => time()
			);
			$customer_service->save($order_goods_data, [
				'order_goods_id' => $order_goods_id,
				'id' => $id
			]);
			// 执行余额账户修正
			
			// 退款记录
			$this->addOrderCustomerServiceAction($order_goods_id, $status_id, 2, $this->uid);
			$order_model = new NsOrderModel();
			
			// 订单添加退款金额、余额
			$order_info = $order_model->getInfo([
				'order_id' => $order_id
			], '*');
			
			// 添加退款帐户记录
			if (empty($refund_remark)) {
				$pay_type_info = $this->getPayType([ "pay_type" => $refund_way ]);
				$remark = "订单编号:" . $order_info['order_no'] . "，退款方式为:[" . $pay_type_info . "]，退款金额:" . $refund_real_money . "元，退款余额：" . $refund_balance_money . "元";
			} else {
				$remark = $refund_remark;
			}
			$this->addOrderCustomerAccountRecords($order_goods_id, $refund_trade_no, $refund_real_money, $refund_way, $order_info['buyer_id'], $remark);
			
			$customer_service->commit();
			return 1;
		} catch (\Exception $e) {
			$customer_service->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 添加订单退款账号记录 售后
	 */
	public function addOrderCustomerAccountRecords($order_goods_id, $refund_trade_no, $refund_money, $refund_way, $buyer_id, $remark)
	{
		$model = new NsOrderCustomerAccountRecordsModel();
		
		$data = array(
			'order_goods_id' => $order_goods_id,
			'refund_trade_no' => $refund_trade_no,
			'refund_money' => $refund_money,
			'refund_way' => $refund_way,
			'buyer_id' => $buyer_id,
			'refund_time' => time(),
			'remark' => $remark
		);
		$res = $model->save($data);
		return $res;
	}
	
	/**
	 * 添加订单退款日志
	 */
	public function addOrderRefundAction($order_goods_id, $refund_status_id, $action_way, $uid)
	{
		$refund_status_info = $this->getOrderRefundStatusInfo([ "refund_status" => $refund_status_id ]);
		$refund_status_name = $refund_status_info["status_name"];
		$user = new UserModel();
		$user_name = $user->getInfo([ 'uid' => $uid ], 'user_name');
		$order_refund = new NsOrderRefundModel();
		$data_refund = array(
			'order_goods_id' => $order_goods_id,
			'refund_status' => $refund_status_id,
			'action' => $refund_status_name,
			'action_way' => $action_way,
			'action_userid' => $uid,
			'action_username' => $user_name['user_name'],
			'action_time' => time()
		);
		$retval = $order_refund->save($data_refund);
		return $retval;
	}
	
	/**
	 * 售后 卖家确认收货
	 */
	public function orderCustomerConfirmRecieve($id, $order_id, $order_goods_id, $storage_num, $isStorage, $goods_id, $sku_id)
	{
		$customer_service = new NsCustomerServiceModel();
		$customer_service->startTrans();
		try {
			$status_id = $this->getOrderRefundStatus()[4]['status_id'];
			
			// 订单项退款操作
			$customer_service = new NsCustomerServiceModel();
			$order_goods_data = array(
				'audit_status' => $status_id
			);
			$customer_service->save($order_goods_data, [
				'order_goods_id' => $order_goods_id,
				'id' => $id
			]);
			
			// 退款记录
			$this->addOrderCustomerServiceAction($order_goods_id, $status_id, 2, $this->uid);
			if ($isStorage > 0) {
				$goods_sku = new NsGoodsSkuModel();
				$goods = new NsGoodsModel();
				// 商品sku表入库
				$goods_sku->where([
					"goods_id" => $goods_id,
					"sku_id" => $sku_id
				])->setInc('stock', $storage_num);
				// 商品表入库
				$goods->where([
					"goods_id" => $goods_id
				])->setInc('stock', $storage_num);
			}
			$customer_service->commit();
			hook("orderGoodsConfirmRecieveSuccess", [
				'order_id' => $order_id,
				'order_goods_id' => $order_goods_id
			]);
			return 1;
		} catch (\Exception $e) {
			$customer_service->rollback();
			return $e;
		}
		
	}
}