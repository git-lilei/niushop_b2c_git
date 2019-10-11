<?php
/**
 * AliPay.php
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

namespace addons\NsAlipay\data\service;

use data\extend\alipay_new\AopClient;
use data\extend\alipay_new\request\AlipayFundTransToaccountTransferRequest;
use data\extend\alipay_new\request\AlipayTradeCloseRequest;
use data\extend\alipay_new\request\AlipayTradePagePayRequest;
use data\extend\alipay_new\request\AlipayTradeRefundRequest;
use data\extend\alipay_new\request\AlipayTradeWapPayRequest;
use data\service\BaseService;
use think\Request;

/**
 * 功能说明：自定义支付宝支付接入类(应用于商户立即转账create_direct_pay_by_user)
 */
class AliPayNew extends BaseService
{
	
	public $aop;
	
	function __construct()
	{
		parent::__construct();
//         \think\Loader::addNamespace('alipaynew', '../data/extend/alipaynew');
		// 获取支付宝支付参数(统一支付到平台账户)
		$alipay_config_service = new AlipayConfig();
		$alipay_new_config = $alipay_config_service->getAlipayConfigNew(0);
		
		// 获取支付宝支付参数(统一支付到平台账户)
		$aop = new AopClient();
		$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
		$aop->appId = $alipay_new_config['value']['app_id'];
		$aop->rsaPrivateKey = $alipay_new_config['value']['private_key'];
		$aop->alipayrsaPublicKey = $alipay_new_config['value']['public_key'];
		$aop->alipayPublicKey = $alipay_new_config['value']['alipay_public_key'];
		$aop->apiVersion = '1.0';
		$aop->signType = 'RSA2';
		$aop->postCharset = 'UTF-8';
		$aop->format = 'json';
		$this->aop = $aop;
	}
	
	public function index()
	{
		// 防止默认目录错误
	}
	
	/**
	 * 支付宝基本设置
	 *
	 * @return unknown
	 */
	public function getAlipayConfig()
	{
		// 合作身份者id，以2088开头的16位纯数字
		$alipay_config['appid'] = $this->appId;
		
		// 私钥
		$alipay_config['private_key'] = $this->rsaPrivateKey;
		
		// 公钥
		$alipay_config['public_key'] = $this->alipayrsaPublicKey;
		
		// 版本
		$alipay_config['apiVersion'] = '1.0';
		
		// 字签名方式
		$alipay_config['signType'] = 'RSA2';
		// 编码
		$alipay_config['postCharset'] = 'GBK';
		// 仅支持JSON
		$alipay_config['format'] = 'json';
		
		return $alipay_config;
	}
	
	/**
	 * 设置支付宝支付传入参数
	 *
	 * @param unknown $orderNumber
	 * @param unknown $body
	 * @param unknown $detail
	 * @param unknown $total_fee
	 * @param unknown $payment_type
	 * @param unknown $notify_url
	 * @param unknown $return_url
	 * @param unknown $show_ur
	 * @return unknown
	 */
	public function setAliPay($out_trade_no, $body, $detail, $total_fee, $payment_type, $notify_url, $return_url, $show_url)
	{
		// 订单名称
		$subject = $body;
		
		// 构造要请求的参数数组，无需改动
		$parameter = array(
			"out_trade_no" => $out_trade_no,
			"subject" => $subject,
			"total_amount" => $total_fee,
			"body" => $body,
			"product_code" => 'FAST_INSTANT_TRADE_PAY',
		);
		
		$parameter = json_encode($parameter);
		
		$is_mobile = Request::instance()->isMobile();
		$parArr = [];
		if ($is_mobile == true) {
			$request = new AlipayTradeWapPayRequest();
		} else {
			$request = new AlipayTradePagePayRequest();
		}
		
		$request->setBizContent($parameter);
		
		$request->SetReturnUrl($return_url);
		
		$request->SetNotifyUrl($notify_url);
		$result = $this->aop->pageExecute($request, 'get');
		return $result;
	}
	
	/**
	 * 订单关闭
	 * @param unknown $orderNumber
	 * @return multitype:number string |multitype:number mixed
	 */
	public function setOrderClose($orderNumber)
	{
		$parameter = array(
			"out_trade_no" => $orderNumber
		);
		// 建立请求
		$request = new AlipayTradeCloseRequest();
		$request->setBizContent(json_encode($parameter));
		$result = $this->aop->execute($request);
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		if (!empty($resultCode) && $resultCode == 10000) {
			return 1;
		} else {
			return $resultCode;
		}
		
	}
	
	/**
	 * 获取配置参数是否正确
	 *
	 * @return unknown
	 */
	public function getVerifyResult($params, $type)
	{
		$res = $this->aop->rsaCheckV1($params, $this->aop->alipayrsaPublicKey, $this->aop->signType);
		
		return $res;
	}
	
	/**
	 * 支付宝支付原路返回
	 *
	 * @param unknown $refund_no
	 * @param unknown $out_trade_no商户订单号不是支付流水号
	 * @param unknown $refund_fee
	 */
	public function aliPayRefund($param)
	{
        $refund_no = $param["refund_no"];
        $out_trade_no = $param["trade_no"];
        $refund_fee = $param["refund_fee"];
		$parameter = array(
			'trade_no' => $out_trade_no,
			'refund_amount' => $refund_fee,
		    'out_request_no' => $refund_no
		);
		// 建立请求
		$request = new AlipayTradeRefundRequest ();
		$request->setBizContent(json_encode($parameter));
		$result = $this->aop->execute($request);
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		if (!empty($resultCode) && $resultCode == 10000) {
			return array(
				"is_success" => 1,
				'msg' => "success"
			);
		} else {
			return array(
				"is_success" => 0,
				'msg' => $responseNode
			);
		}
	}
	
	/**
	 * 支付宝转账
	 *
	 * @param unknown $out_biz_no订单编号
	 * @param unknown $ali_account转账账户
	 * @param unknown $money转账金额
	 * @return \data\extend\alipay\提交表单HTML文本
	 */
	public function aliPayTransfer($out_biz_no, $ali_account, $money)
	{
		$parameter = array(
			'out_biz_no' => $out_biz_no,
			'payee_account' => $ali_account,
			'payee_type' => 'ALIPAY_LOGONID',
			'amount' => $money
		);
		// 建立请求
		$request = new AlipayFundTransToaccountTransferRequest();
		$request->setBizContent(json_encode($parameter));
		$result = $this->aop->execute($request);
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;

		if (!empty($resultCode) && $resultCode == 10000) {
			$res = array(
				"is_success" => 1,
				'msg' => "success",
				'result_code' => 'SUCCESS'
			);
			return $res;
		} else {
			$res = array(
				"is_success" => 0,
				'error_msg' => $responseNode,
				'result_code' => 'FAIL'
			);
			return $res;
		}
	}
}