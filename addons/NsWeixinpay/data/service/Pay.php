<?php
/**
 * AlipayConfig.php
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

namespace addons\NsWeixinpay\data\service;


use data\model\NsOrderPaymentModel;
use data\service\BaseService;
use data\service\UnifyPay;
use addons\NsWeixinpay\data\service\WeiXinPay;

/**
 * 支付宝支付配置
 */
class Pay extends BaseService
{
	
	/**
	 * 执行微信支付
	 */
	public function wchatPay($out_trade_no, $trade_type, $red_url, $applet_openid = "")
	{
		$unify_pay = new UnifyPay();
		$data = $unify_pay->getPayInfo($out_trade_no);
        if (empty($data) < 0) {
            return 0;
        }
        //修改支付方式
        $unify_pay->modifyOrderPaymentType($out_trade_no, 1);

		$weixin_pay = new WeiXinPay();
		if ($trade_type == 'JSAPI') {
			$openid = $weixin_pay->get_openid();
			$product_id = '';
		}
		if ($trade_type == 'NATIVE') {
			$openid = '';
			$product_id = $out_trade_no;
		}
		if ($trade_type == 'MWEB') {
			$openid = '';
			$product_id = $out_trade_no;
		}
		if ($trade_type == 'APPLET') {
			$openid = $applet_openid;
		}
		
		$retval = $weixin_pay->setWeiXinPay($data['pay_body'], $data['pay_detail'], $data['pay_money'] * 100, $out_trade_no, $red_url, $trade_type, $openid, $product_id);
		return $retval;
	}
	/**
	 * 获取微信jsapi
	 */
	public function getWxJsApi($UnifiedOrderResult)
	{
		$weixin_pay = new WeiXinPay();
		$retval = $weixin_pay->GetJsApiParameters($UnifiedOrderResult);
		return $retval;
	}

	/**
	 * 微信支付检测签名串
	 */
	public function checkSign($post_obj, $sign)
	{
		$weixin_pay = new WeiXinPay();
		$retval = $weixin_pay->checkSign($post_obj, $sign);
		return $retval;
	}

	/**
	 * 微信退款
	 */
	public function setWeiXinRefund($refund_no, $out_trade_no, $refund_fee, $total_fee)
	{
		$weixin_pay = new WeiXinPay();
		$retval = $weixin_pay->setWeiXinRefund($refund_no, $out_trade_no, $refund_fee, $total_fee);
		return $retval;
	}

	/**
	 * 提现 微信转账
	 */
	public function wechatTransfers($openid, $partner_trade_no, $amount, $realname, $desc)
	{
		$weixin_pay = new WeiXinPay();
		$retval = $weixin_pay->EnterprisePayment($openid, $partner_trade_no, $amount, $realname, $desc);
		return $retval;
	}

    /**
     * 订单关闭
     * @param $out_trade_no
     * @return unknown
     */
	public function setOrderClose($out_trade_no){
        $weixin_pay = new WeiXinPay();
        $retval = $weixin_pay->setOrderClose($out_trade_no);
        return $retval;
    }
}