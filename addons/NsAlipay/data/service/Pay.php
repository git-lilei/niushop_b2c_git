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

namespace addons\NsAlipay\data\service;

use data\service\BaseService;
use data\service\UnifyPay;

/**
 * 支付宝支付配置
 */
class Pay extends BaseService
{
	

	/**
	 * 支付宝原路退款
	 * @param unknown $refund_no
	 * @param unknown $out_trade_no商户订单号不是支付流水号
	 * @param unknown $refund_fee
	 */
	public function aliPayRefund($param)
	{
        $alipay_verify = new AliPayVerify();
        $pay = $alipay_verify->aliPayClass();
		$retval = $pay->aliPayRefund($param);
		return $retval;
	}
	
	/**
	 * 支付宝转账
	 * @param unknown $out_biz_no
	 * @param unknown $ali_account
	 * @param unknown $money
	 * @return \data\extend\alipay\提交表单HTML文本
	 */
	public function aliPayTransfer($out_biz_no, $ali_account, $money)
	{
        $alipay_verify = new AliPayVerify();
        $aliay_pay = $alipay_verify->aliPayClass();
        $result = $aliay_pay->aliPayTransfer($out_biz_no, $ali_account, $money);
        return $result;
	}
	
	/**
	 * 获取支付宝配置参数是否正确,支付成功后使用
	 */
	public function getVerifyResult($params, $type)
	{
		$alipay_verify = new AliPayVerify();
		$pay = $alipay_verify->aliPayClass();
		$verify = $pay->getVerifyResult($params, $type);
		return $verify;
	}
	
	/**
	 * 关闭订单
	 * @param unknown $out_trade_no
	 */
	public function orderClose($out_trade_no)
	{
        $alipay_verify = new AliPayVerify();
        $pay = $alipay_verify->aliPayClass();
		$res = $pay->setOrderClose($out_trade_no);
		return $res;
	}


    /**
     * 执行支付宝支付
     */
    public function aliPay($param)
    {
        $out_trade_no = $param["out_trade_no"];
        $notify_url = $param["notify_url"];
        $return_url = $param["return_url"];
        $show_url = $param["show_url"];
        $pay_service = new UnifyPay();
        $data = $pay_service->getPayInfo($out_trade_no);
        if (empty($data)) {
            return 0;
        }
        //修改支付方式
        $pay_service->modifyOrderPaymentType($out_trade_no, 2);

        $ali_verify = new AliPayVerify();
        $ali_pay = $ali_verify->aliPayClass();
        $retval = $ali_pay->setAliPay($out_trade_no, $data['pay_body'], $data['pay_detail'], $data['pay_money'], 3, $notify_url, $return_url, $show_url);
        return $retval;
    }


	
}