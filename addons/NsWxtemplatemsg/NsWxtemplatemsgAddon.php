<?php
// +----------------------------------------------------------------------
// | test [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.zzstudio.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Byron Sampson <xiaobo.sun@gzzstudio.net>
// +----------------------------------------------------------------------
namespace addons\NsWxtemplatemsg;

use addons\NsWxtemplatemsg\data\service\WeixinTemplate;
use data\extend\WchatOauth;
use data\model\NsOrderModel;
use data\model\NsTuangouGroupModel;
use data\model\UserModel;
use data\service\Config;

class NsWxtemplatemsgAddon extends \addons\Addons
{
    
    public $info = array(
        'name' => 'NsWxtemplatemsg', // 插件名称标识
        'title' => '微信模板消息', // 插件中文名
        'description' => '微信模板消息插件', // 插件概述
        'status' => 1, // 状态 1启用 0禁用
        'author' => 'niushop', // 作者
        'version' => '1.0', // 版本号
        'has_addonslist' => 0, // 是否有下级插件 例如：第三方登录插件下有 qq登录，微信登录
        'content' => '', // 插件的详细介绍或使用方法
        'ico' => 'addons/NsWxtemplatemsg/ico.png'
    );
    
    /**
     * 订单提交成功通知
     *
     * @param unknown $params
     */
	public function orderCreateSuccess($params = [])
	{
    //		try {
    //			// 根据订单ID查询会员openid
    //			$order_id = $params['order_id'];
    //			$order_query = new OrderQuery();
    //			$order_info = $order_query->getOrderInfo([ "order_id" => $order_id ]);
    //			$pay_type_info = $order_query->getPayTypeInfo([ "pay_type" => $order_info['payment_type'] ]);
    //			$pay_type_name = $pay_type_info["type_name"];
    //			$uid = $order_info['buyer_id'];
    //			$url = __URL(__URL__ . "/wap/order/detail?orderId=$order_id");
    //			$keyword1 = $order_info['order_no']; // 订单编号
    //			$keyword2 = getTimeStampTurnTime($order_info['create_time']); // 创建时间
    //			$keyword3 = $order_info['order_money']; // 订单金额
    //			$keyword4 = $pay_type_name; // 支付类型
    //			$this->templateMessageSend('OPENTM200444240', '', $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //		} catch (\Exception $e) {
    //			return $e->getMessage();
    //		}
	}
    
    /**
     * 订单发货成功通知
     *
     * @param unknown $params
     */
	public function orderDeliverySuccess($params = [])
	{
    //		try {
    //			$order_id = $params['order_id'];
    //			$order_query = new OrderQuery();
    //			$order_info = $order_query->getOrderInfo([ "order_id" => $order_id ]);
    //			$uid = $order_info['buyer_id'];
    //			$url = __URL(__URL__ . "/wap/order/detail?orderId=$order_id");
    //			$keyword1 = $order_info['order_no']; // 订单编号
    //			$keyword2 = $params['express_name'] != '' ? $params['express_name'] : '无需物流'; // 快递公司
    //			$keyword3 = $params['express_no'] != '' ? $params['express_no'] : '无需物流'; // 快递单号
    //			$keyword4 = '';
    //			$this->templateMessageSend('OPENTM201541214', '', $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //		} catch (\Exception $e) {
    //			return $e->getMessage();
    //		}
	}
    //
    /**
     * 订单付款成功通知
     * @param unknown $params
     */
	public function orderPaySuccess($params = [])
	{
    //		if (isset($params['order_pay_no'])) {
    //			$this->orderOnLinePaySuccess($params);
    //		} else {
    //			$this->orderOffLinePaySuccess($params);
    //		}
	}
    
    /**
     * 订单线上付款成功 （订单付款成功通知，本店分销提成通知，下级分店分销提成通知， 下下级分店分销提成通知）（暂时没有测试）
     */
    //	public function orderOnLinePaySuccess($params = [])
    //	{
    //		try {
    //			$order_pay_no = $params['order_pay_no'];
    //			$order_model = new NsOrderModel();
    //			$order_query = new OrderQuery();
    //			// 可能是多个订单
    //			$order_id_array = $order_model->where([
    //				'out_trade_no' => $order_pay_no
    //			])->column('order_id');
    //			foreach ($order_id_array as $k => $order_id) {
    //				$order_info = $order_query->getOrderInfo([ "order_id" => $order_id ]);
    //				$uid = $order_info['buyer_id'];
    //				$url = __URL(__URL__ . "/wap/order/detail?orderId=$order_id");
    //				$keyword1 = $order_info['order_no']; // 订单编号
    //				$keyword2 = getTimeStampTurnTime($order_info['pay_time']); // 支付时间
    //				$keyword3 = $order_info['pay_money']; // 支付金额
    //				$pay_type_info = $order_query->getPayTypeInfo([ "pay_type" => $order_info['payment_type'] ]);
    //				$pay_type_name = $pay_type_info["type_name"];
    //				$keyword4 = $pay_type_name; // 支付方式
    //				$this->templateMessageSend('OPENTM200444326', '', $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //			}
    //		} catch (\Exception $e) {
    //			return $e->getMessage();
    //		}
    //	}
    
    /**
     * 订单线下付款成功 （订单付款成功通知，本店分销提成通知，下级分店分销提成通知， 下下级分店分销提成通知）
     */
    //	public function orderOffLinePaySuccess($params = [])
    //	{
    //		try {
    //			$order_id = $params['order_id'];
    //			$order_query = new OrderQuery();
    //			$order_info = $order_query->getOrderInfo([ "order_id" => $order_id ]);
    //			$uid = $order_info['buyer_id'];
    //			$url = __URL(__URL__ . "/wap/order/detail?orderId=$order_id");
    //			$keyword1 = $order_info['order_no']; // 订单编号
    //			$keyword2 = getTimeStampTurnTime($order_info['pay_time']); // 支付时间
    //			$keyword3 = $order_info['pay_money']; // 支付金额
    //			$pay_type_info = $order_query->getPayTypeInfo([ "pay_type" => $order_info['payment_type'] ]);
    //			$pay_type_name = $pay_type_info["type_name"];
    //			$keyword4 = $pay_type_name; // 支付方式
    //			$this->templateMessageSend('OPENTM200444326', '', $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //		} catch (\Exception $e) {
    //			return $e->getMessage();
    //		}
    //	}
    
    /**
     * 订单申请退款通知
     */
    //	public function orderGoodsRefundAskforSuccess($params = [])
    //	{
    //		try {
    //			$order_id = $params['order_id'];
    //			$order_query = new OrderQuery();
    //			$order_info = $order_query->getOrderInfo([ "order_id" => $order_id ]);
    //			$order_goods_id_str = $params['order_goods_id'];
    //			$order_goods = new NsOrderGoodsModel();
    //			$order_goods_id_arr = explode(',', $order_goods_id_str);
    //			$goods_name = '';
    //			foreach ($order_goods_id_arr as $k => $v) {
    //				$goods_name = $goods_name . ',' . $order_goods->getInfo([
    //						'order_goods_id' => $v
    //					], 'goods_name')['goods_name'];
    //			}
    //			$goods_name = substr($goods_name, 1);
    //			$uid = $order_info['buyer_id'];
    //			$url = __URL(__URL__ . "/wap/order/detail?orderId=$order_id");
    //			$keyword1 = $params['refund_require_money']; // 退款金额
    //			$keyword2 = $goods_name; // 商品详情
    //			$keyword3 = $order_info['order_no']; // 订单编号
    //			$keyword4 = ''; // 无
    //			$this->templateMessageSend('OPENTM207103254', '', $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //		} catch (\Exception $e) {
    //			return $e->getMessage();
    //		}
    //	}
    
    /**
     * 订单完成
     * @param $params
     * @return string
     */
    //	public function orderComplete($params){
    //        try {
    //            $order_id = $params['order_id'];
    //            $order_query = new OrderQuery();
    //            $order_info = $order_query->getOrderInfo([ "order_id" => $order_id, "order_status" => 4 ]);
    //            if(!empty($order_info)){
    //                $uid = $order_info['buyer_id'];
    //                $url = __URL(__URL__ . "/wap/order/detail?orderId=$order_id");
    //                $keyword = array();
    //                $keyword["keyword1"] = $order_info["order_no"];//完成时间
    //                $keyword["keyword2"] = getTimeStampTurnTime($order_info["finish_time"]);//订单编号
    //                $keyword["keyword3"] = "已完成";//完成状态
    //                $data = array(
    //                    "uid" => $uid,
    //                    "template_no" => 'OPENTM414529660',
    //                    "first" => '',
    //                    "url" => $url,
    //                    "keyword" => $keyword
    //                );
    //                $this->tmplmsg($data);
    //            }
    //        } catch (\Exception $e) {
    //            return $e->getMessage();
    //        }
    //
    //    }
    /**
     * 订单退款结果通知（卖家确认退款）
     */
	public function orderGoodsConfirmRefundSuccess($params = [])
	{
    //		try {
    //			$order_id = $params['order_id'];
    //			$order_query = new OrderQuery();
    //			$order_info = $order_query->getOrderInfo([ "order_id" => $order_id ]);
    //			$uid = $order_info['buyer_id'];
    //			$url = __URL(__URL__ . "/wap/order/detail?orderId=$order_id");
    //			$keyword1 = $order_info['order_no']; // 订单编号
    //			$keyword2 = $order_info['pay_money']; // 订单金额
    //			$keyword3 = $params['refund_real_money']; // 实退金额
    //			$keyword4 = ''; // 支付方式
    //			$this->templateMessageSend('OPENTM205986235', '', $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //		} catch (\Exception $e) {
    //			return $e->getMessage();
    //		}
	}
    
    /**
     * 会员提现申请通知
     */
    //	public function memberWithdrawApplyCreateSuccess($params = [])
    //	{
    //		try {
    //			$id = $params['id'];
    //			if ($params['type'] == 'balance') {
    //				$withdraw = new NsMemberBalanceWithdrawModel();
    //				$info = $withdraw->getInfo([
    //					'id' => $id
    //				], '*');
    //				$url = __URL(__URL__ . "/wap/member/withdrawal");
    //			} elseif ($params['type'] == 'commission') {
    //				$withdraw = new NfxUserCommissionWithdrawModel();
    //				$info = $withdraw->getInfo([
    //					'id' => $id
    //				], '*');
    //				$url = __URL(__URL__ . "/wap/Distribution/account");
    //			}
    //			$uid = $info['uid'];
    //			$keyword1 = $info['cash']; // 本次提现金额
    //			$keyword2 = $info['account_number']; // 提现账户
    //			$keyword3 = getTimeStampTurnTime($info['ask_for_date']); // 申请时间
    //			$keyword4 = getTimeStampTurnTime($info['ask_for_date'] + 3 * 24 * 3600); // 预计到账时间
    //			$this->templateMessageSend('OPENTM207292959', '提现申请提醒', $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //		} catch (\Exception $e) {
    //			return $e->getMessage();
    //		}
    //	}
    
    /**
     * 会员提现申请审核通过（提现审核结果通知）
     */
    //	public function memberWithdrawAuditAgree($params = [])
    //	{
    //		try {
    //			$id = $params['id'];
    //
    //			if ($params['type'] == 'balance') {
    //				$withdraw = new NsMemberBalanceWithdrawModel();
    //				$info = $withdraw->getInfo([
    //					'id' => $id
    //				], '*');
    //				$url = __URL(__URL__ . "/wap/member/withdrawal");
    //			} elseif ($params['type'] == 'commission') {
    //				$withdraw = new NfxUserCommissionWithdrawModel();
    //				$info = $withdraw->getInfo([
    //					'id' => $id
    //				], '*');
    //				$url = __URL(__URL__ . "/wap/Distribution/account");
    //			}
    //
    //			$uid = $info['uid'];
    //			$keyword1 = $info['cash']; // 本次提现金额
    //			$keyword2 = $info['account_number']; // 提现账户
    //			$keyword3 = getTimeStampTurnTime($info['ask_for_date']); // 申请时间
    //			$keyword4 = '已通过';
    //			$this->templateMessageSend('OPENTM400094285', '提现审核结果通知', $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //		} catch (\Exception $e) {
    //			return $e->getMessage();
    //		}
    //	}
    
    /**
     * 会员注册成功通知
     */
	public function memberRegisterSuccess($params = [])
	{
    //		try {
    //			$uid = $params['uid'];
    //			$url = '';
    //			$keyword1 = $params['member_name']; // 会员昵称
    //			$keyword2 = getTimeStampTurnTime($params['reg_time']); // 注册时间
    //			$keyword3 = '';
    //			$keyword4 = '';
    //			$this->templateMessageSend('OPENTM203347141', '', $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //		} catch (\Exception $e) {
    //			return $e->getMessage();
    //		}
	}

    /**
     * 会员余额充值成功通知
     * @param array $params
     */
    //	public function memberBalanceRechargeSuccess($params)
    //	{
    //		try {
    //			$pay = new NsOrderPaymentModel();
    //			$pay_info = $pay->getInfo([ 'out_trade_no' => $params['out_trade_no'] ], 'pay_money');
    //			$member_account = new MemberAccount();
    //			$member_balance = $member_account->getMemberBalance($params['uid']);
    //			$uid = $params['uid'];
    //			$url = '';
    //			$keyword1 = $pay_info['pay_money'] . '元';  //本次充值金额
    //			$keyword2 = getTimeStampTurnTime($params['time']); //充值时间
    //			$keyword3 = $member_balance . '元'; //充值后余额
    //			$keyword4 = '';
    //			$this->templateMessageSend('OPENTM205041253', '', $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //		} catch (\Exception $e) {
    //			return $e->getMessage();
    //		}
    //
    //	}
    
    /**
     * **************************************************分销版本模板消息**********************************************************************
     */
    /**
     * 分销商申请创建成功
     */
    //	public function promoterApplyCreateSuccess($params = [])
    //	{
    //		try {
    //			$uid = $params['uid'];
    //			$url = '';
    //			$keyword1 = $params['promoter_shop_name']; // 店铺名称
    //			$keyword2 = getTimeStampTurnTime($params['regidter_time']); // 通过时间
    //			$keyword3 = '';
    //			$keyword4 = '';
    //			$this->templateMessageSend('OPENTM409846856', '分销商申请提醒', $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //		} catch (\Exception $e) {
    //			return $e->getMessage();
    //		}
    //	}
    
    /**
     * 分销商审核结果 （分销商的审核通知， 下级分销商审核通知， 下下级分销商审核通知）
     */
    //	public function promoterAuditAgreeSuccess($params = [])
    //	{
    //		try {
    //			$uid = $params['uid'];
    //			$url = '';
    //			$keyword1 = $params['promoter_shop_name']; // 店铺名称
    //			$keyword2 = getTimeStampTurnTime($params['regidter_time']); // 通过时间
    //			$keyword3 = '';
    //			$keyword4 = '';
    //			$this->templateMessageSend('OPENTM409846856', $params['title'], $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //			// 判断当前分销商是否有上级
    //		} catch (\Exception $e) {
    //			return $e->getMessage();
    //		}
    //	}
    //
    /**
     * 订单分销成功通知
     * @param array $params
     */
    //	public function orderDistributionSuccess($params = [])
    //	{
    //		try {
    //			$nfx_promoter = new NfxPromoterModel();
    //			$nfx_commission_distribution = new NfxCommissionDistributionModel();
    //			$uid = $params['uid'];
    //			$url = '';
    //			$keyword1 = $params['order_no'];
    //			$keyword2 = $params['order_money'];
    //			$keyword3 = '0.00';
    //
    //			$nfx_promoter_info = $nfx_promoter->getInfo([ "uid" => $params['uid'] ], "promoter_id");
    //			if (!empty($nfx_promoter_info)) {
    //				$nfx_commission_money_info = $nfx_commission_distribution->getInfo([ "order_id" => $params['order_id'], "promoter_id" => $nfx_promoter_info['promoter_id'] ], "commission_money");
    //				$keyword3 = !empty($nfx_commission_money_info) ? sprintf("%.2f", $nfx_commission_money_info['commission_money']) : "0.00";
    //			}
    //			$keyword4 = getTimeStampTurnTime($params['notice_time']);
    //			$this->templateMessageSend('OPENTM201010537', $params['title'], $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //		} catch (\Exception $e) {
    //			return $e->getMessage();
    //		}
    //	}
    
    /**
     * 拼团开团通知
     * @param array $params
     */
    //	public function openGroupNotice($params = [])
    //	{
    //		try {
    //			$pintuan_info = $this->getPintuanInfo($params['pintuan_group_id']);
    //			if (!empty($pintuan_info)) {
    //				$uid = $pintuan_info['group_uid'];
    //				$url = '';
    //				$keyword1 = $pintuan_info['goods_name'];
    //				$keyword2 = $pintuan_info['tuangou_money'];
    //				$keyword3 = $pintuan_info['tuangou_num'] . '人团';
    //				$keyword4 = date("Y-m-d H:i:d", $pintuan_info['end_time']);
    //				$this->templateMessageSend('OPENTM410729522', '开团成功提醒', $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //			}
    //		} catch (\Exception $e) {
    //			return $e->getMessage();
    //		}
    //	}
    
    /**
     * 用户参团通知
     * @param array $params
     */
    //	public function addGroupNotice($params = [])
    //	{
    //		try {
    //			$pintuan_info = $this->getPintuanInfo($params['pintuan_group_id']);
    //			$user = new UserModel();
    //			$user_info = $user->getInfo([ 'uid' => $params['uid'] ], "nick_name");
    //			if (!empty($pintuan_info) && !empty($user_info)) {
    //				$uid = $params['uid'];
    //				$url = '';
    //				$keyword1 = $pintuan_info['goods_name'];
    //				$keyword2 = $pintuan_info['tuangou_money'];
    //				$keyword3 = $user_info['nick_name'];
    //				$keyword4 = $pintuan_info['tuangou_num'] . '人团';
    //				$this->templateMessageSend('OPENTM414066517', '参团成功提醒', $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //			}
    //		} catch (\Exception $e) {
    //			return $e->getMessage();
    //		}
    //	}
    
    /**
     * 拼团成功或失败通知
     * @param array $params
     */
    //	public function groupBookingSuccessOrFail($params = [])
    //	{
    //		try {
    //			$pintuan_info = $this->getPintuanInfo($params['pintuan_group_id']);
    //			$user_list = $this->getPintuanUserList($params['pintuan_group_id']);
    //			if (!empty($pintuan_info) && !empty($user_list)) {
    //				foreach ($user_list as $user_info) {
    //					$uid = $user_info['buyer_id'];
    //					$url = '';
    //					$keyword1 = $user_info['order_no'];
    //					$keyword2 = $pintuan_info['goods_name'];
    //					$keyword3 = $pintuan_info['tuangou_money'];
    //					$keyword4 = $pintuan_info['tuangou_num'] . '人团';
    //					if ($params['type'] == "success") {
    //						$this->templateMessageSend('OPENTM409367318', '拼团成功通知', $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //					} elseif ($params['type'] == "fail") {
    //						$this->templateMessageSend('OPENTM409367317', '拼团失败通知', $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //					}
    //				}
    //			}
    //		} catch (\Exception $e) {
    //			return $e->getMessage();
    //		}
    //	}
    
    /**
     * 砍价发起通知
     * @param $param
     */
    //	public function bargain($param){
    //        try {
    //            $uid = $param['uid'];
    //            $url = '';
    //            $keyword1 = $param['goods_name'];
    //            $keyword2 = $param['money'];
    //            $keyword3 = '';
    //            $keyword4 = '';
    //
    //            $this->templateMessageSend('OPENTM411530622', '发起砍价', $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //        } catch (\Exception $e) {
    //            return $e->getMessage();
    //        }
    //    }
    
    /**
     * 砍价成功或失败通知（用户）
     * @param $param
     */
    //    public function bargainNotice($param){
    //        try {
    //            $uid = $param['uid'];
    //            $url = '';
    //            $keyword1 = $param['goods_name'];
    //            $keyword2 = $param['money'];
    //            $keyword3 = '';
    //            $keyword4 = '';
    //
    //            if($param['type'] == 'success'){
    //                $notice = "砍价成功通知";
    //            }elseif($param['type'] == 'fail'){
    //                $notice = "砍价失败通知";
    //            }
    //
    //            $this->templateMessageSend('OPENTM411530622', $notice, $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4);
    //        } catch (\Exception $e) {
    //            return $e->getMessage();
    //        }
    //    }
    /**
     * 获取拼团通知所需信息
     * @param unknown $pintuan_group_id
     */
    private function getPintuanInfo($pintuan_group_id)
    {
        $tuangou_group = new NsTuangouGroupModel();
        $tuangou_group_info = $tuangou_group->getInfo([ 'group_id' => $pintuan_group_id ], 'group_uid,group_name,goods_name,tuangou_money,tuangou_type_name,tuangou_num,real_num,create_time,end_time');
        if (!empty($tuangou_group_info)) {
            $tuangou_group_info['surplus_num'] = $tuangou_group_info['tuangou_num'] - $tuangou_group_info['real_num'];
            $day = floor(($tuangou_group_info['end_time'] - time()) / 86400);
            $hours = floor(($tuangou_group_info['end_time'] - time() - $day * 86400) / 3600);
            $tuangou_group_info['surplus_time'] = $day > 0 ? $day . '天' : '';
            $tuangou_group_info['surplus_time'] .= $hours > 0 ? $hours . '小时' : '';
        }
        return $tuangou_group_info;
    }
    
    /**
     * 获取参与拼团的用户列表
     * @param unknown $pintuan_group_id
     */
    private function getPintuanUserList($pintuan_group_id)
    {
        $ns_order = new NsOrderModel();
        $buyer_list = $ns_order->getQuery([
            'tuangou_group_id' => $pintuan_group_id,
            'order_status' => 1
        ], 'buyer_id,order_no', '');
        return $buyer_list;
    }
    
    /**
     * 发送模板消息
     * $template_no,$title 作为查询模板消息的条件 $title主要用来查询 当模板编号相同时，用来区分是哪个模板消息 ，可以为空
     */
    protected function templateMessageSend($template_no, $title, $uid, $url, $keyword1, $keyword2, $keyword3, $keyword4)
    {
        $wchat = new WchatOauth();
        $openid = $this->getOpenidByUid($uid);
        if ($openid) {
            // 根据模板编号查出 模板信息
            $where['template_no'] = $template_no;
            if ($title != '') {
                $where['title'] = $title;
            }
            $weixin_template = new WeixinTemplate();
            $t_info = $weixin_template->getInfo($where);
            if ($t_info['is_enable'] == 1) {
                $wchat->templateMessageSend($openid, $t_info['template_id'], $url, $t_info['headtext'], $keyword1, $keyword2, $keyword3, $keyword4, $t_info['bottomtext']);
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
    
    /**
     * 模板消息发送
     * @param $param
     */
    public function tmplmsg($param){
        $wchat = new WchatOauth();
        $openid = $this->getOpenidByUid($param["uid"]);
        if ($openid) {
            // 根据模板编号查出 模板信息
            $condition = [];
            $condition['template_no'] = $param["template_no"];
            if ($param["title"] != '') {
                $condition['title'] = $param["title"];
            }
            $weixin_template = new WeixinTemplate();
            $template_info = $weixin_template->getInfo($condition);
            if ($template_info['is_enable'] == 1) {
                $data = array(
                    "template_id" => $template_info["template_id"],
                    "url" => $param["url"],
                    "openid" => $param["openid"],
                    "first" => $template_info['headtext'],
                    "keyword" => $param["keyword"],
                    "remark" => $template_info['bottomtext']
                );
                $wchat->tmplmsg($data);
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
    
    /**
     * 发送模板消息
     * @param $param
     */
    public function sendWxTemplateMsg($param){
        
        $weixin_template = new WeixinTemplate();
        $weixin_info = $weixin_template->getTemplateConfig();
        if($weixin_info["is_use"] != 1){
            return;
        }
        
        $wchat = new WchatOauth();
        if($param["uid"] == "bind_openid"){
            $config_service = new Config();
            $config = $config_service->getShopNotifyConfig();
            $config_info = json_decode($config["value"], true);
            if(empty($config_info["uid"])){
                return;
            }
            
            $openid = $this->getOpenidByUid($config_info["uid"]);
        }else{
            $openid = $this->getOpenidByUid($param["uid"]);
        }
        
        if ($openid) {
            // 根据模板编号查出 模板信息
            $condition = [];
            $condition['template_no'] = $param["template_no"];
            if ($param["template_code"] != '') {
                $condition['template_code'] = $param["template_code"];
            }
            $weixin_template = new WeixinTemplate();
            $template_info = $weixin_template->getInfo($condition);
            if ($template_info['is_enable'] == 1) {
                $data = array(
                    "template_id" => $template_info["template_id"],
                    "url" => $param["url"],
                    "open_id" => $openid,
                    "first" => $template_info['headtext'],
                    "keyword" => $param["keyword"],
                    "remark" => $template_info['bottomtext']
                );
                $wchat->tmplmsg($data);
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
    /**
     * 根据uid获取openid
     */
    protected function getOpenidByUid($uid)
    {
        $uesr = new UserModel();
        // 获取会员的openid
        $openid = $uesr->getInfo([
            'uid' => $uid
        ], 'wx_openid');
        if ($openid) {
            return $openid['wx_openid'];
        } else {
            return false;
        }
    }
    // 钩子名称（需要该钩子调用的页面）
    
    /**
     * 插件安装
     * @see \addons\Addons::install()
     */
    public function install()
    {
        return true;
    }
    
    /**
     * 插件卸载
     * @see \addons\Addons::uninstall()
     */
    public function uninstall()
    {
        return true;
    }
}