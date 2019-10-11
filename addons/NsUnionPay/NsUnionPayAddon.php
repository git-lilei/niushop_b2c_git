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
namespace addons\NsUnionPay;

use addons\NsUnionPay\data\service\Pay;
use addons\NsUnionPay\data\service\UnionPay;
use addons\NsUnionPay\data\service\UnionPayConfig;
use data\service\UnifyPay;

class NsUnionPayAddon extends \addons\Addons
{
	
	public $info = array(
		'name' => 'NsUnionPay', // 插件名称标识
		'title' => '银联', // 插件中文名
		'description' => '该系统支持银联即时到账接口', // 插件概述
		'status' => 1, // 状态 1启用 0禁用
		'author' => 'niushop', // 作者
		'version' => '1.0', // 版本号
		'has_addonslist' => 0, // 是否有下级插件 例如：第三方登录插件下有 qq登录，微信登录
		'content' => '', // 插件的详细介绍或使用方法
		'ico' => 'addons/NsUnionPay/ico.png'
	);
	
	public function payconfig($param)
	{
		$unionpay_config = new UnionPayConfig();
		$union_pay = $unionpay_config->getUnionpayConfig(0);
		$config['is_use'] = $union_pay['is_use'];
		$config["pay_name"] = "银联卡支付";
        $config['addon_name'] = $this->info['name'];
		$config["logo"] = $this->info['ico'];
		$config["desc"] = "该系统支持即时到账接口";
		$config['url'] = __URL('__URL__/NsUnionPay/' . ADMIN_MODULE . '/Config/payunionconfig');
		$config['pay_logo'] = "public/admin/images/pay.png";  //支付按钮
		$config['pay_url'] = 'APP_MAIN/pay/onlinepay';//支付页面
		$config['h5_icon'] = 'yinfu.png';
		$config['pc_icon'] = 'unionpay_card.png';
		$config['lang'] = 'union_pay';
		return $config;
	}
	
	
	/**
	 * 支付(必存在)
	 * @param unknown $param
	 */
	public function pay($param)
	{
	    if($param['addon_name'] != $this->info['name'])
	    {
	        return '';
	    }
	    $pay = new UnifyPay();
	    $pay_info = $pay->getPayInfo($param['out_trade_no']);
	    //修改订单支付项的支付类型
	    $pay->modifyOrderPaymentType($param['out_trade_no'], 3);
	    $txnTime = date('YmdHis', $pay_info['create_time']);
	    $union_pay = new UnionPay();
	    $res = $union_pay->frontConsume($param['out_trade_no'], $txnTime, $pay_info['pay_money']);
	    return success(['return_type' => 'html', 'html' => $res]);
	}
	
	/**
	 * 同步回调(必存在)
	 * @param unknown $param
	 */
	public function payReturn($param)
	{
	    try{
            $respCode = $_POST['respCode'];
            $orderId = $_POST['orderId']; // 其他字段也可用类似方式获取
            $txnTime = $_POST['txnTime'];
            // 判断respCode=00、A6后，对涉及资金类的交易，请再发起查询接口查询，确定交易成功后更新数据库。
            $msg = "";
            if ($respCode == 00 || $respCode == 'A6') {
                $pay_service = new Pay();
                $msg = $pay_service->frontReceive($orderId, $txnTime);
            } else {
                $msg = 0;
            }
            return ["msg" => $msg];
        } catch (\Exception $e) {
            return ["msg" => 0];
        }
	}
	
	/**
	 * 异步回调(必存在)
	 * @param unknown $param
	 */
	public function payNotify($param)
	{
	    $orderId = request()->post('orderId', ''); // 其他字段也可用类似方式获取
	    $queryId = request()->post('queryId', ''); // 其他字段也可用类似方式获取
	    $txnTime = request()->post('txnTime', ''); // 其他字段也可用类似方式获取
	    
	    $respCode = request()->post('respCode', '');
	    if(empty($txnTime) || empty($respCode))
	    {
	        return '';
	    }
	    // 判断respCode=00、A6后，对涉及资金类的交易，请再发起查询接口查询，确定交易成功后更新数据库。
	    if ($respCode == 00 || $respCode == 'A6') {
	        $pay_service = new Pay();
	        $result = $pay_service->backReceive($orderId, $txnTime, $queryId);
	    } else {
            echo '交易失败';
        }
	}
	
	/**
	 * 退款(必存在)
	 * @param unknown $param
	 */
	public function refund($param)
	{
        if($param['addon_name'] != $this->info['name'])
        {
            return '';
        }
        $union_pay = new UnionPay();
        $txnTime = date('YmdHis', time());
        $retval = $union_pay->refund($param["refund_no"], $param['trade_no'], $txnTime, $param["refund_fee"]);
        return $retval;
	}
	
	/**
	 * 转账
	 * @param unknown $param
	 */
	public function transfer($param)
	{
	
	}

    /**
     * 验证支付配置
     * @param $param
     * @return string
     */
    public function checkPayTypeConfig($param){
        if($param['addon_name'] != $this->info['name'])
        {
            return '';
        }
        $union_pay_config = new UnionPayConfig();
        $pay_info = $union_pay_config->getUnionpayConfig(0);
        $msg = "";
        if (empty($pay_info) || empty($pay_info['value']['sign_cert_pwd']) || empty($pay_info['value']['certs_path']) || empty($pay_info['value']['log_path']) || empty($pay_info['value']['service_charge'])) {
            $msg = "<p>请检查银联支付配置信息填写是否正确(<a href='" . __URL(__URL__ . "/NsUnionPay/" . ADMIN_MODULE . "/config/payunionconfig") . "' target='_blank'>点击此处进行配置</a>)</p>";
        }

        if ($pay_info['is_use'] == 0) {
            $msg = "<p>当前未开启银联支付配置(<a href='" . __URL(__URL__ . "/NsUnionPay/" . ADMIN_MODULE . "/config/payunionconfig") . "' target='_blank'>点击此处去开启</a>)</p>";
        } else {
            $refund_setting_info = $union_pay_config->getOriginalRoadRefundSetting(0);
            $refund_setting = json_decode($refund_setting_info['value'], true);
            if ($refund_setting['is_use'] == 0) {
                $msg = "<p>当前未开启银联退款配置(<a href='" . __URL(__URL__ . "/NsUnionPay/" . ADMIN_MODULE . "/config/payunionconfig") . "' target='_blank'>点击此处去开启</a>)</p>";
            }
        }

        if(empty($msg)){
            return success();
        }else{
            return error($msg);
        }
    }
    /**
     * 关闭第三方支付
     * @param $param
     */
    public function closePay($param){
        if($param['addon_name'] != $this->info['name'])
        {
            return '';
        }
        $pay_service = new Pay();

        return 1;
    }
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