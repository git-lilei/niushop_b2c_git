<?php
/**
 * Pay.php
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

namespace app\api\controller;

use addons\NsPintuan\data\service\Pintuan;
use addons\NsPresell\data\service\Orderpresell;
use addons\NsWeixinpay\data\service\WxpayConfig;
use data\service\Config as ConfigService;
use data\service\Member as MemberService;
use data\service\OrderQuery;
use data\service\UnifyPay;
use addons\NsWeixinpay\data\service\Pay as PayService;

/**
 * 支付控制器
 */
class Pay extends BaseApi
{
	
	public $shop_config;
	
	public function __construct($params = [])
	{
		parent::__construct($params);
		$config = new ConfigService();
		$this->shop_config = $config->getShopConfig(0);
	}
	
	/**
	 * 获取支付相关信息
	 */
	public function getPayValue()
	{
		$title = "获取支付信息";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$out_trade_no = $page_index = isset($this->params['out_trade_no']) ? $this->params['out_trade_no'] : 1;;
		if (empty($out_trade_no)) {
			return $this->outMessage($title, "", -50, "缺少必填参数out_trade_no");
		}
		$is_support_pintuan = IS_SUPPORT_PINTUAN;
		
		if ($is_support_pintuan == 1) {
			
			$pintuan = new Pintuan();
			$res = $pintuan->orderPayBefore($out_trade_no);
			if ($res == 0)
				return $this->outMessage($title, "", -50, "拼团支付已关闭!");
			
		}
		$pay = new UnifyPay();
		$member = new MemberService();
		$pay_value = $pay->getPayInfo($out_trade_no);
		
		if ($pay_value['pay_status'] != 0) {
			// 订单已经支付
			return $this->outMessage($title, '', -50, '订单已经支付或者订单价格为0.00，无需再次支付!');
		}
		if ($pay_value['type'] == 1) {
			// 订单
			$order_status = $this->getOrderStatusByOutTradeNo($out_trade_no);
			// 订单关闭状态下是不能继续支付的
			if ($order_status == 5) {
				return $this->outMessage($title, '', -50, '订单已关闭');
			}
		}
		$zero1 = time(); // 当前时间 ,注意H 是24小时 h是12小时
		$zero2 = $pay_value['create_time'];
		if ($zero1 > ($zero2 + ($this->shop_config['order_buy_close_time'] * 60))) {
			return $this->outMessage($title, '', -50, '订单已关闭');
		} else {
			$member_info = $member->getUserInfo();
			$data = array(
				'pay_value' => $pay_value,
				'nick_name' => $member_info['nick_name']
			);
			return $this->outMessage($title, $data);
		}
	}
	
	/**
	 * 订单待支付
	 */
	public function orderPay()
	{
	    $title = '订单待支付';
		$order_id = request()->post('order_id', 0);
		$order_action = new \addons\NsPresell\data\service\OrderAction();
		$order_query = new OrderQuery();
		if ($order_id != 0) {
			// 更新支付流水号
			$order_action->createNewOutTradeNoReturnBalance($order_id);
			$new_out_trade_no = $order_query->getOrderNewOutTradeNo($order_id);
			if (empty($new_out_trade_no)) {
			    return $this->outMessage($title, '', -1, '支付配置有误');
			}
			return $this->outMessage($title, $new_out_trade_no);
		} else {
            return $this->outMessage($title, '', -1, '无法获取支付信息');
		}
	}

	/**
	 * 预售定金待支付
	 */
	public function orderPresellPay()
	{
		$title = '预售定金待支付';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$order_id = request()->post('order_id', 0);

		$order_action = new \addons\NsPresell\data\service\OrderAction();
		$oder_presell = new Orderpresell();
		$presell_order_info = $oder_presell->getOrderPresellInfo(0, [
			'relate_id' => $order_id
		]);
		$presell_order_id = $presell_order_info['presell_order_id'];

		if ($presell_order_id != 0) {
			// 更新支付流水号
			$oder_presell->createNewOutTradeNoReturnBalancePresellOrder($presell_order_id);
			$new_out_trade_no = $order_action->getPresellOrderNewOutTradeNo($presell_order_id);
			return $this->outMessage($title, $new_out_trade_no);
		} else {
			return $this->outMessage($title, '', -1, '无法获取支付信息');
		}
	}

	/**
	 * 根据外部交易号查询订单状态，订单关闭状态下是不能继续支付的
	 */
	public function getOrderStatusByOutTradeNo()
	{
		$title = "获取订单状态";

        $out_trade_no = isset($this->params['out_trade_no']) ? $this->params['out_trade_no'] : '';
        if (empty($out_trade_no)) {
            return $this->outMessage($title, "", '-50', "缺少必填参数out_trade_no");
        }
		$order_query = new OrderQuery();
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$order_status = $order_query->getOrderStatusByOutTradeNo($out_trade_no);
		if (!empty($order_status)) {
			return $this->outMessage($title, ["order_status" => $order_status['order_status']]);
		}
		return $this->outMessage($title, ["order_status" => 0]);
	}

	/**
	 * 小程序支付
	 */
	public function appletWechatPay()
	{
		$title = "订单支付!";
        if (addon_is_exit('NsWeixinpay') != 1) {
            return $this->outMessage($title, "", '-10', "缺少微信支付插件");
        }
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
        $is_applet = $this->get('is_applet');
        if ($is_applet != 1) {
            return $this->outMessage($title, "", '-50', "错误的支付环境");
        }
        $out_trade_no = request()->post('out_trade_no', '');
		$openid = request()->post('openid', '');
		if (empty($out_trade_no)) {
			return $this->outMessage($title, "", '-50', "无法识别的交易号");
		}
		$red_url = str_replace("/index.php", "", __URL__);
		$red_url = str_replace("/api.php", "", __URL__);
		$red_url = str_replace("index.php", "", $red_url);
		$red_url = $red_url . "/pay.php";
		$pay = new PayService();
		$config = new WxpayConfig();

		$res = $pay->wchatPay($out_trade_no, 'APPLET', $red_url, $openid);
		$wchat_config = $config->getWpayConfig($this->instance_id);

		if ($res["result_code"] == "SUCCESS" && $res["return_code"] == "SUCCESS") {
			$appid = $res["appid"];
			$nonceStr = $res["nonce_str"];
			$package = $res["prepay_id"];
			$signType = "MD5";
			$key = $wchat_config['value']['mch_key'];
			$timeStamp = time();
			$sign_string = "appId=$appid&nonceStr=$nonceStr&package=prepay_id=$package&signType=$signType&timeStamp=$timeStamp&key=$key";
			$paySign = strtoupper(md5($sign_string));
			$res["timestamp"] = $timeStamp;
			$res["PaySign"] = $paySign;
		}
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 根据流水号查询订单编号，
	 * 创建时间：2017年10月9日 18:36:54
	 *
	 * @param string $out_trade_no
	 * @return string
	 */
	public function getOrderNoByOutTradeNo()
	{
		$title = '查询订单号';
// 		if (empty($this->uid)) {
// 			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
// 		}
        $out_trade_no = isset($this->params['out_trade_no']) ? $this->params['out_trade_no'] : '';
		if (empty($out_trade_no)) {
			return $this->outMessage($title, "", '-50', "缺少必填参数out_trade_no");
		}
		$order_query = new OrderQuery();
		$pay = new UnifyPay();
		$pay_value = $pay->getPayInfo($out_trade_no);
		$order_no = "";
		if ($pay_value['type'] == 1) {
			// 订单
            $order_no_result = $order_query->getOrderNoByOutTradeNo($out_trade_no);
            $order_no = empty($order_no_result['order_no']) ? "" : $order_no_result['order_no'];
		} elseif ($pay_value['type'] == 4) {
			// 余额充值不进行处理
		}
		return $this->outMessage($title, array(
			'order_no' => $order_no
		));
	}
	
	/**
	 * 获取支付方式配置信息
	 * 创建时间：2018年6月20日10:33:26
	 */
	public function getPayConfig()
	{
		$title = "获取支付方式配置信息";
		$pay = new UnifyPay();
		$res = $pay_config = $pay->getPayConfig();
		if (!empty($res)) {
			return $this->outMessage($title, $res);
		} else {
			return $this->outMessage($title, null, "-9999", "未获取到数据");
		}
	}
	
	/**
	 * 余额支付选择界面
	 */
	public function pay()
	{
		$title = '订单支付！';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$member = new MemberService();
		$pay = new UnifyPay();
		$config = new ConfigService();
		$uid = $member->getSessionUid();
		
		$out_trade_no = request()->post("out_trade_no", 0);
		
		// 支付信息
		$pay_value = $pay->getPayInfo($out_trade_no);
		
		if (empty($out_trade_no) || !is_numeric($out_trade_no) || empty($pay_value)) {
			return $this->outMessage($title, "", '-10', "没有获取到支付信息");
		}
		
		// 此次交易最大可用余额
		$member_balance = $pay->getMaxAvailableBalance($out_trade_no, $uid);
		$data["member_balance"] = $member_balance;
		
		$shop_id = 0;
		$shop_config = $config->getConfig($shop_id, "ORDER_BALANCE_PAY");
		
		// 支付方式配置
		$pay_config = $pay->getPayConfig();
		
		$order_status = $this->getOrderStatusByOutTradeNo($out_trade_no);
		// 订单关闭状态下是不能继续支付的
		if ($order_status == 5) {
			return $this->outMessage($title, "", '-10', "订单已关闭");
		}
		
		// 还需支付的金额
		$need_pay_money = round($pay_value['pay_money'], 2) - round($member_balance, 2);
		
		$zero1 = time(); // 当前时间 ,注意H 是24小时 h是12小时
		$zero2 = $pay_value['create_time'];
		$this->shop_config = $config->getShopConfig(0);
		if ($zero1 > ($zero2 + ($this->shop_config['order_buy_close_time'] * 60))) {
			return $this->outMessage($title, "", '-10', "订单已关闭");
		} else {
			$data["pay_value"] = $pay_value;
			$data["need_pay_money"] = sprintf("%.2f", $need_pay_money);
			$data["shop_config"] = $shop_config;
			$data["pay_config"] = $pay_config;
			
			return $this->outMessage($title, $data);
		}
	}
	
	/**
	 * 订单绑定余额 （若存在余额支付）
	 */
	public function orderBindBalance()
	{
		$title = '余额支付';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
        $out_trade_no = isset($this->params['out_trade_no']) ? $this->params['out_trade_no'] : 0;
        $is_use_balance = isset($this->params['is_use_balance']) ? $this->params['is_use_balance'] : 0;
		$pay = new UnifyPay();
		$res = $pay->orderPaymentUserBalance($out_trade_no, $is_use_balance, $this->uid);
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 获取交易流水号
	 */
	public function outTradeNo(){
	    $title = '获取交易流水号';
	    $pay = new UnifyPay();
	    $out_trade_no = $pay->createOutTradeNo();
	    return $this->outMessage($title, $out_trade_no);
	}

    /**
     * @return string
     */
	public function payInfo(){
        $title = '订单支付信息';
//         if (empty($this->uid)) {
//             return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
//         }
        $out_trade_no = isset($this->params['out_trade_no']) ? $this->params['out_trade_no'] : '';
        if(empty($out_trade_no)){
            return $this->outMessage($title, "", '-10', "没有获取到支付信息");
        }

        $pay = new UnifyPay();
        $pay_info = $pay->getPayInfo($out_trade_no);
        return $this->outMessage($title, $pay_info);
    }
    
    /**
     * 可用最大余额
     * @return string
     */
    public function maxPayBalance(){
        $title = '订单支付信息';
        if (empty($this->uid)) {
            return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
        }
        $out_trade_no = isset($this->params['out_trade_no']) ? $this->params['out_trade_no'] : '';
        if(empty($out_trade_no)){
            return $this->outMessage($title, "", '-10', "没有获取到支付信息");
        }
        $pay = new UnifyPay();
        $balance = $pay->getMaxAvailableBalance($out_trade_no, $this->uid);
        return $this->outMessage($title, ["balance" => $balance]);
    }


}