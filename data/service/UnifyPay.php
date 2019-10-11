<?php
/**
 * UnifyPay.php
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
 * 统一支付接口服务层
 */
use addons\NsAlipay\data\service\AliPayVerify;
use addons\NsPresell\data\service\OrderAction as PresellOrderAction;
use data\model\NsMemberBalanceWithdrawModel;
use data\model\NsOrderModel;
use data\model\NsOrderPaymentModel;
use data\model\NsOrderPresellModel;
use data\model\UserModel;
use data\service\Member\MemberAccount;
use data\service\niubusiness\NbsBusinessAssistant;
use data\service\Pay\UnionPay;
use data\service\Pay\WeiXinPay;
use think\Cache;
use think\Log;
use data\model\NsMemberRechargeModel;
use addons\NsPintuan\data\model\NsTuangouGroupModel;

class UnifyPay extends BaseService
{
	
	/**
	 * 创建待支付单据
	 * @param unknown $pay_no
	 * @param unknown $pay_body
	 * @param unknown $pay_detail
	 * @param unknown $pay_money
	 * @param unknown $type 订单类型  1. 商城订单  2.
	 * @param unknown $pay_money
	 */
	public function createPayment($shop_id, $out_trade_no, $pay_body, $pay_detail, $pay_money, $type, $type_alis_id)
	{
		$pay = new NsOrderPaymentModel();
		$data = array(
			'shop_id' => $shop_id,
			'out_trade_no' => $out_trade_no,
			'type' => $type,
			'type_alis_id' => $type_alis_id,
			'pay_body' => $pay_body,
			'pay_detail' => $pay_detail,
			'pay_money' => $pay_money,
			'create_time' => time(),
			'original_money' => $pay_money
		);
		if ($pay_money <= 0) {
			$data['pay_status'] = 1;
		}
		$res = $pay->save($data);
		return $res;
	}
	
	/**
	 * 根据支付编号修改待支付单据
	 * @param unknown $out_trade_no
	 * @param unknown $shop_id
	 * @param unknown $pay_body
	 * @param unknown $pay_detail
	 * @param unknown $pay_money
	 * @param unknown $type 订单类型  1. 商城订单  2.
	 * @param unknown $type_alis_id
	 */
	public function updatePayment($out_trade_no, $shop_id, $pay_body, $pay_detail, $pay_money, $type, $type_alis_id)
	{
		$pay = new NsOrderPaymentModel();
		$data = array(
			'shop_id' => $shop_id,
			'type' => $type,
			'type_alis_id' => $type_alis_id,
			'pay_body' => $pay_body,
			'pay_detail' => $pay_detail,
			'pay_money' => $pay_money,
			'modify_time' => time()
		);
		if ($pay_money <= 0) {
			$data['pay_status'] = 1;
		}
		$res = $pay->save($data, [ 'out_trade_no' => $out_trade_no ]);
		return $res;
	}
	
	
	/**
	 * 创建订单支付编号
	 * @param unknown $order_id
	 */
	public function createOutTradeNo()
	{
		$cache = Cache::get("niubfd" . time());
		if (empty($cache)) {
			Cache::set("niubfd" . time(), 1000);
			$cache = Cache::get("niubfd" . time());
		} else {
			$cache = $cache + 1;
			Cache::set("niubfd" . time(), $cache);
		}
		$no = time() . rand(1000, 9999) . $cache;
		return $no;
	}
	
	/**
	 * 删除待支付单据
	 */
	public function delPayment($out_trade_no)
	{
		$pay = new NsOrderPaymentModel();
		$res = $pay->where('out_trade_no', $out_trade_no)->delete();
		return $res;
	}
	
	/**
	 * 重新设置编号，用于修改价格订单
	 */
	public function modifyNo($out_trade_no, $new_no)
	{
		$retval = $this->closePaymentPartyInterface($out_trade_no);
		return $retval;
	}
	
	/**
	 * 修改支付价格
	 */
	public function modifyPayMoney($out_trade_no, $pay_money)
	{
		$pay = new NsOrderPaymentModel();
		$data = array(
			'pay_money' => $pay_money,
			'original_money' => $pay_money
		);
		$retval = $pay->save($data, [ 'out_trade_no' => $out_trade_no ]);
	}
	
	/**
	 * 修改支付类型
	 */
	public function modifyOrderPaymentType($out_trade_no, $pay_type)
	{
		//修改支付信息
		$pay = new NsOrderPaymentModel();
		$pay->save([ 'pay_type' => $pay_type ], [ 'out_trade_no' => $out_trade_no ]);
		
		//修改订单支付方式
		$order_model = new NsOrderModel();
		$res = $order_model->save([ 'payment_type' => $pay_type ], [ 'out_trade_no' => $out_trade_no ]);
	}
	
	/**
	 * 关闭订单(数据库操作)
	 */
	public function closePayment($out_trade_no)
	{
		$pay = new NsOrderPaymentModel();
		$data = array(
			'pay_status' => -1
		);
		$retval = $pay->save($data, [ 'out_trade_no' => $out_trade_no ]);
		return $retval;
	}
	
	/**
	 * 获取支付信息
	 */
	public function getPayInfo($out_trade_no)
	{
		$pay = new NsOrderPaymentModel();
		$info = $pay->getInfo([ 'out_trade_no' => $out_trade_no ], '*');
		return $info;
	}
	
	/**
	 * 获取支付配置
	 */
	public function getPayConfig()
	{
		$data_config = hook('payconfig', []);
		return arrayFilter($data_config);
	}
	
	/**
	 * 线上支付主动根据支付方式执行支付成功的通知
	 * @param unknown $out_trade_no
	 */
	public function onlinePay($out_trade_no, $pay_type, $trade_no)
	{
		$pay = new NsOrderPaymentModel();
		$ns_order = new NsOrderModel();
		try {
			$pay_info = $pay->getInfo([ 'out_trade_no' => $out_trade_no ]);
			if ($pay_info['pay_status'] == 1) {
				return 1;
			}
			$data = array(
				'pay_status' => 1,
				'pay_type' => $pay_type,
				'pay_time' => time(),
				'trade_no' => $trade_no
			);
			$pay->save($data, [ 'out_trade_no' => $out_trade_no ]);
			
			if ($pay_info['balance_money'] > 0) {
				$ns_order->save([
					"user_platform_money" => $pay_info['balance_money'],
					"pay_money" => $pay_info['pay_money']
				], [ 'out_trade_no' => $out_trade_no ]);
			}
			
			$pay_info = $pay->getInfo([ 'out_trade_no' => $out_trade_no ], 'type');
			switch ($pay_info['type']) {
				case 1:
					$order_action = new OrderAction();
					$order_action->onLinePaymentUpdateBalance($out_trade_no);
					$order_action->orderOnLinePay($out_trade_no, $pay_type);
					break;
				case 2:
					$assistant = new NbsBusinessAssistant();
					$assistant->payOnlineBusinessAssistantApply($out_trade_no);
					break;
				case 4:
					//充值
					$member = new Member();
					$member->payMemberRecharge($out_trade_no, $pay_type);
					//账户余额充值调用钩子
					break;
				case 5: //预售订单支付
					$order_action = new PresellOrderAction();
					$order_action->onLinePresellOrderUpdateBalance($out_trade_no);
					$order_action->presellOrderOnLinePay($out_trade_no, $pay_type);
					break;
				default:
					break;
			}
			return 1;
		} catch (\Exception $e) {
			Log::write("weixin-------------------------------" . $e->getMessage());
			return $e->getMessage();
		}
		
	}
	
	/**
	 * 只是执行单据支付，不进行任何处理用于执行支付后被动调用
	 */
	public function offLinePay($out_trade_no, $pay_type)
	{
		$pay = new NsOrderPaymentModel();
		$data = array(
			'pay_status' => 1,
			'pay_type' => $pay_type,
			'pay_time' => time()
		);
		$retval = $pay->save($data, [ 'out_trade_no' => $out_trade_no ]);
		return $retval;
	}
	
	/**
	 * 关闭第三方接口
	 */
	public function closePaymentPartyInterface($out_trade_no)
	{
		try {
			//检测已开通接口
			$data = $this->getPayInfo($out_trade_no);
			if (!empty($data)) {
				if ($data['pay_type'] == 1) {
                    //微信支付
				    $addon_name = "NsWeixinpay";
				} elseif ($data['pay_type'] == 2) {
					//支付宝支付
                    $addon_name = "NsAlipay";
				} elseif ($data['pay_type'] == 3) {
					//银联卡支付
                    $addon_name = "NsUnionPay";
				}
				$result = hook("closePay", ["addon_name" => $addon_name, "out_trade_no" => $out_trade_no]);
                $result = arrayFilter($result);
                return $result["data"];
			}
			return 1;
		} catch (\Exception $e) {
			return 0;
		}
	}
	



	/**
	 * 获取提现转账所需要的信息
	 * @param unknown $withdraw_id
	 */
	public function getMemberWithdrawDetail($withdraw_id)
	{
		$member_balance_withdraw = new NsMemberBalanceWithdrawModel();
		$retval = $member_balance_withdraw->getInfo([
			'id' => $withdraw_id
		], '*');
		if (!empty($retval)) {
			$user = new UserModel();
			$userinfo = $user->getInfo([
				'uid' => $retval['uid']
			]);
			$retval['openid'] = $userinfo["wx_openid"];
		}
		return $retval;
	}

	/**
	 * 获取此次交易最大可使用余额
	 */
	public function getMaxAvailableBalance($out_trade_no, $uid)
	{
		$pay = new NsOrderPaymentModel();
		$info = $pay->getInfo([ 'out_trade_no' => $out_trade_no, 'pay_status' => 0 ], 'pay_money');
		$member = new MemberAccount();
		$member_balance = $member->getMemberBalance($uid);
		
		if ($member_balance > $info['pay_money']) {
			$member_balance = $info['pay_money'];
		}
		return $member_balance;
	}
	
	/**
	 * 订单使用余额
	 * 返回值 0继续支付  1余额支付跳转到个人中心 -1支付异常
	 */
	public function orderPaymentUserBalance($out_trade_no, $is_use_balance, $uid)
	{
		// 判断是否使用
		if ($is_use_balance > 0) {
			$pay = new NsOrderPaymentModel();
			$member_account = new MemberAccount();
			
			$pay->startTrans();
			$info = $pay->getInfo([ 'out_trade_no' => $out_trade_no, 'pay_status' => 0 ], 'original_money');
			if (!empty($info['original_money'])) {
				try {
					// 如果可使用余额为0则继续支付
					$member_balance = $this->getMaxAvailableBalance($out_trade_no, $uid);
					if ($member_balance == 0) {
						return array(
							"code" => 0,
							"message" => ""
						);
					}
					$data = array(
						"pay_money" => round(($info['original_money'] - $member_balance), 2),
						"balance_money" => $member_balance,
					);
					// 如果原始支付金额减去所用余额不为0的话 继续使用其他支付方式支付
					if (($info['original_money'] - $member_balance) > 0) {
						$pay->save($data, [ 'out_trade_no' => $out_trade_no ]);
						$member_account->addMemberAccountData(0, 2, $uid, 0, $member_balance * (-1), 1, $info['type_alis_id'], "订单支付使用余额，锁定使用余额");
						$pay->commit();
						return array(
							"code" => 0,
							"message" => ""
						);
					} elseif (($info['original_money'] - $member_balance) == 0) {
						// 如果原始支付金额减去所用余额为0的话 订单使用余额支付
						$data["pay_status"] = 1;
						$data["pay_time"] = time();
						$data["pay_type"] = 5;
						
						$order_action = new OrderAction();
						$ns_order = new NsOrderModel();
						$order_info = $ns_order->getInfo([ 'out_trade_no' => $out_trade_no ], "*");

                        //拼团支付阻断
                        if (addon_is_exit("NsPintuan")) {
                            if ($order_info['tuangou_group_id'] > 0) {
                                $tuangou_group = new NsTuangouGroupModel();
                                $pingtuan_info = $tuangou_group->getInfo([ 'group_id' => $order_info['tuangou_group_id'] ], "tuangou_num, status");
                                $condition_1['order_status'] = [ "in", "1,2,3,4" ];
                                $condition_1['tuangou_group_id'] = $order_info['tuangou_group_id'];

                                $order_list_count = $ns_order->getCount($condition_1);
                                if ($pingtuan_info['tuangou_num'] <= $order_list_count || !in_array($pingtuan_info['status'], [ 0, 1 ])) {
                                    $pay->rollback();
                                    $res = [
                                        'code' => -1,
                                        'message' => "该拼团已完成或已关闭"
                                    ];
                                    return $res;
                                }
                            }
                        }

						// 针对普通订单 只有一条交易号
						if (!empty($order_info)) {
							// 更改订单表支付金额 和 使用余额数
							$ns_order->save([
								"user_platform_money" => $member_balance,
								"pay_money" => round(($info['original_money'] - $member_balance), 2)
							], [ 'out_trade_no' => $out_trade_no ]);
							// 订单线上支付
							$order_action->orderOnLinePay($out_trade_no, 5);
							// 添加账户流水
							$member_account->addMemberAccountData(0, 2, $uid, 0, $member_balance * (-1), 1, $info['type_alis_id'], "商城订单");
							// 更改支付流水表信息
							$pay->save($data, [ 'out_trade_no' => $out_trade_no ]);
							$pay->commit();
							return array(
								"code" => 1,
								"message" => ""
							);
						} else {
							// 针对预售订单 会有两条交易号
							$ns_order_presell = new NsOrderPresellModel();
							$order_presell_info = $ns_order_presell->getInfo([ 'out_trade_no' => $out_trade_no ], "relate_id");
							if (!empty($order_presell_info)) {
								// 预售订单线上支付
								$order = new PresellOrderAction();
								$order->presellOrderOnLinePay($out_trade_no, 5);
								// 更改预售订单表支付金额 和 使用余额数
								$ns_order_presell->save([
									"platform_money" => $member_balance,
									"presell_pay" => round(($info['original_money'] - $member_balance), 2)
								], [ 'out_trade_no' => $out_trade_no ]);
								// 更改订单表支付金额 和 使用余额数
								$ns_order->save([
									"user_platform_money" => $member_balance,
								], [ 'order_id' => $order_presell_info['relate_id'] ]);
								// 添加账户流水
								$member_account->addMemberAccountData(0, 2, $uid, 0, $member_balance * (-1), 1, $info['type_alis_id'], "商城订单");
								// 更改支付流水表信息
								$pay->save($data, [ 'out_trade_no' => $out_trade_no ]);
								$pay->commit();
								return array(
									"code" => 1,
									"message" => ""
								);
							} else {
								$pay->rollback();
								return array(
									"code" => -1,
									"message" => "支付发生异常，未获取到支付信息"
								);
							}
						}
					} elseif (($info['original_money'] - $member_balance) < 0) {
						// 如果原始支付金额减去所用余额小于0的话 回滚所有操作
						$pay->rollback();
						return array(
							"code" => -1,
							"message" => "支付发生异常"
						);
					}
				} catch (\Exception $e) {
					$pay->rollback();
					return array(
						"code" => -1,
						"message" => $e->getMessage()
					);
				}
			} else {
				return array(
					"code" => -1,
					"message" => "订单已经支付或者订单价格为0.00，无需再次支付!"
				);
			}
		} else {
			return array(
				"code" => 0,
				"message" => ""
			);
		}
	}
	
	/**
	 * 根据流水号查询订单编号，
	 * 创建时间：2017年10月9日 18:36:54
	 *
	 * @param unknown $out_trade_no
	 * @return string
	 */
	public function getOrderNoByOutTradeNo($out_trade_no)
	{
	    $pay = new UnifyPay();
	    $order_query = new OrderQuery();
	    $member_pay_model = new NsMemberRechargeModel();
	
	    $pay_value = $pay->getPayInfo($out_trade_no);
	    $order_no = "";
	    if ($pay_value['type'] == 1) {
	        // 订单
	        $order_no_result = $order_query->getOrderNoByOutTradeNo($out_trade_no);
            $order_no = empty($order_no_result['order_no']) ? "" : $order_no_result['order_no'];
	    } elseif ($pay_value['type'] == 4) {
	        /* $order_no = [];
	         $pay_info = $member_pay_model->getInfo(["out_trade_no"=>$out_trade_no]);
	         if($pay_info){
	         $order_no['out_trade_no'] = $out_trade_no;
	         $order_no['uid'] = $pay_info['uid'];
	         $order_no['shop_id'] = $pay_value['shop_id'];
	         } */
	    }
	    return $order_no;
	}
	
	/**
	 * 根据外部交易号查询订单状态，订单关闭状态下是不能继续支付的
	 *
	 * @param unknown $out_trade_no
	 * @return number
	 */
	public function getOrderStatusByOutTradeNo($out_trade_no)
	{
	    $order_query = new OrderQuery();
	    $order_status = $order_query->getOrderStatusByOutTradeNo($out_trade_no);
	    if (!empty($order_status)) {
	        return $order_status['order_status'];
	    }
	    return 0;
	}
	
}