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
namespace addons\NsAlipay;

use addons\NsAlipay\data\service\AliPay;
use addons\NsAlipay\data\service\AlipayConfig;
use addons\NsAlipay\data\service\Pay;
use data\service\UnifyPay;
use think\Log;

class NsAlipayAddon extends \addons\Addons
{
	
	public $info = array(
		'name' => 'NsAlipay', // 插件名称标识
		'title' => '支付宝', // 插件中文名
		'description' => '该系统支持即时到账接口', // 插件概述
		'status' => 1, // 状态 1启用 0禁用
		'author' => 'niushop', // 作者
		'version' => '1.0', // 版本号
		'has_addonslist' => 0, // 是否有下级插件 例如：第三方登录插件下有 qq登录，微信登录
		'content' => '', // 插件的详细介绍或使用方法
		'ico' => 'addons/NsAlipay/ico.png'
	);
	
	/**
	 * 后台支付设置
	 */
	public function payconfig($param)
	{
		$alipay_config = new AlipayConfig();
		$status = $alipay_config->getAliPayStatus(0);
		$status_is = json_decode($status['value'], true);
		$config['is_use'] = $status_is['is_use'];
		$config["logo"] = $this->info['ico'];
		$config["pay_name"] = "支付宝";
		$config['addon_name'] = $this->info['name'];
		$config["desc"] = "该系统支持即时到账接口";
		$config['url'] = __URL('__URL__/NsAlipay/' . ADMIN_MODULE . '/Config/payaliconfig');
		$config['pay_logo'] = "public/admin/images/pay.png";  //支付按钮
		$config['pay_url'] = 'APP_MAIN/pay/onlinepay';//支付跳转页面
		$config['h5_icon'] = 'zhifu.png';
		$config['pc_icon'] = 'alipay.png';
		$config['lang'] = 'alipay';
		return $config;
	}
	
	/**
	 * 支付(必存在)
	 */
	public function pay($param)
	{
	    if($param['addon_name'] != $this->info['name'])
	    {
	        return '';
	    }
		if (!isWeixin()) {
	        $pay_service = new Pay();
            $out_trade_no = $param["out_trade_no"];
            $notify_url = $param["notify_url"];
            $return_url = $param["return_url"];
            $show_url = $param["return_url"];
            $param = array(
                "out_trade_no" => $out_trade_no,
                "notify_url" => $notify_url,
                "return_url" => $return_url,
                "show_url" => $show_url,
            );
            $res = $pay_service->aliPay($param);
			$html = "<meta charset='UTF-8'><script>window.location.href='" . $res . "'</script>";
			return success(['return_type' => 'html', 'html' => $html]);
		} else {
		    
		    $pay = new UnifyPay();
		    $this->assign("status", -1);
		    $out_trade_no = $param["out_trade_no"];
		    $order_no = $pay->getOrderNoByOutTradeNo($out_trade_no);
		    $this->assign("order_no", $order_no);
// 		    if (request()->isMobile()) {
    		    $view_replace_str = [
    		        'WAP_IMG' => __ROOT__ . '/addons/NsAlipay/template/wap/public/images'
    		    ];
	            $view = $this->fetch(ADDON_DIR . '/NsAlipay/template/wap/pay/pay_tips.html', [], $view_replace_str);
// 		    } 
		    return success(['return_type' => 'html', 'html' => $view]);

		}
	}
	
	
	/**
	 * 异步回调(必存在)
	 * @param unknown $param
	 */
	public function payNotify($param)
	{
		try{
	        $pay_service = new Pay();
			$params = request()->post();
	
			$verify_result = $pay_service->getVerifyResult($params, 'notify');
			if ($verify_result) { // 验证成功
				$out_trade_no = request()->post('out_trade_no', '');
				// 支付宝交易号
				$trade_no = request()->post('trade_no', '');
				
				// 交易状态
				$trade_status = request()->post('trade_status', '');
	            $pay = new UnifyPay();
				if ($trade_status == 'TRADE_FINISHED') {
					$retval = $pay->onlinePay($out_trade_no, 2, $trade_no);
				} else
					if ($trade_status == 'TRADE_SUCCESS') {
						$retval = $pay->onlinePay($out_trade_no, 2, $trade_no);
					}
				echo "success";
			} else {
				// 验证失败
				echo "fail";
			}
		} catch (\Exception $e) {
			
		}
	}

    /***
     *支付同步回调
     * @param $param
     */
	public function payReturn($param){
	    try{
            $out_trade_no = request()->get('out_trade_no', '');
            $params = request()->get();
            $pay_service = new Pay();
            $verify_result = $pay_service->getVerifyResult($params, 'return');
            if ($verify_result) {
                $trade_no = request()->get('trade_no', '');
                $trade_status = request()->get('trade_status', '');

                if ($trade_no == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                    $msg = 1;
                } else {
//                     echo "trade_status=" . $trade_status;
    //                $html = "trade_status=" . $trade_status;
                    $msg = 0;
                }
            } else {
                $msg = 0;
            }
            return ["msg" => $msg];
        } catch (\Exception $e) {
            return ["msg" => 0];
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
        $pay_service = new Pay();
		$retval = $pay_service->aliPayRefund($param);
		return $retval;
	}
	
	/**
	 * 转账
	 * @param unknown $param
	 */
	public function transfer($param)
	{

	    if($param['addon_name'] != $this->info['name'])
	    {
	        return '';
	    }
        $alipay_config = new AlipayConfig();
        $transfer_account = $alipay_config->getTransferAccountsSetting(0);
        if (empty($transfer_account)) {
            return array(
                "code" => -1,
                "message" => '未配置支付宝转账功能'
            );
        }
        $transfer_account = json_decode($transfer_account['value'], true);
        if ($transfer_account['is_use'] == 0) {
            return array(
                "code" => -1,
                "message" => '未启用支付宝转账功能'
            );
        }
        $pay_service = new Pay();
		$retval = $pay_service->aliPayTransfer($param['withdraw_no'], $param['account_number'], $param['amount']);

        if ($retval["result_code"] == "SUCCESS") {
            $status = 1;
            $msg = "支付宝线上转账成功!";
        } elseif ($retval["result_code"] == "FAIL") {
            $status = -1;
            $msg = $retval["error_msg"];
        }
        $result = ["msg" => $msg, "status" => $status];
        return success($result);
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
        $msg = "";
        $alipay_config = new AlipayConfig();
        $config_info = $alipay_config->getAliPayVersion(0);
        //判断支付宝版本
        if (empty($config_info)) {
            $is_old = 0;
        } else {
            $data = json_decode($config_info["value"], true);
            $is_old = $data["is_use"];
        }
        //根据版本判断配置是否有误
        if ($is_old == 0) {
            $pay_info = $alipay_config->getAlipayConfig(0);
            if (empty($pay_info) || empty($pay_info['value']['ali_partnerid']) || empty($pay_info['value']['ali_seller']) || empty($pay_info['value']['ali_key'])) {
                $msg = "<p>请检查支付宝支付配置信息填写是否正确(<a href='" . __URL(__URL__ . "/NsAlipay/" . ADMIN_MODULE . "/config/payaliconfig") . "' target='_blank'>点击此处进行配置</a>)</p>";
            }
        } else {
            $pay_info = $alipay_config->getAlipayConfigNew(0);
            if (empty($pay_info) || empty($pay_info['value']['app_id']) || empty($pay_info['value']['private_key']) || empty($pay_info['value']['public_key']) || empty($pay_info['value']['alipay_public_key'])) {
                $msg = "<p>请检查支付宝支付配置信息填写是否正确(<a href='" . __URL(__URL__ . "/NsAlipay/" . ADMIN_MODULE . "/config/payaliconfig") . "' target='_blank'>点击此处进行配置</a>)</p>";
            }
        }
        //支付宝支付配置
        $alipay_status_info = $alipay_config->getAliPayStatus(0);
        if (!empty($alipay_status_info)) {
            $alipay_status_info = json_decode($alipay_status_info['value'], true);
            if ($alipay_status_info['is_use'] == 0) {
                $msg = "<p>当前未开启支付宝支付配置(<a href='" . __URL(__URL__ . "/NsAlipay/" . ADMIN_MODULE . "/config/payaliconfig") . "' target='_blank'>点击此处去开启</a>)</p>";
            }
        } else {
            $msg = "<p>当前未开启支付宝支付配置(<a href='" . __URL(__URL__ . "/NsAlipay/" . ADMIN_MODULE . "/config/payaliconfig") . "' target='_blank'>点击此处去开启</a>)</p>";
        }
        //支付宝退款设置
        $refund_setting_info = $alipay_config->getOriginalRoadRefundSetting(0);
        $refund_setting = json_decode($refund_setting_info['value'], true);
        // 支付配置开启后，再判断原路退款配置是否开启、填写了各项值
        if ($refund_setting['is_use'] == 0) {
            $msg = "<p>当前未开启支付宝原路退款配置(<a href='" . __URL(__URL__ . "/NsAlipay/" . ADMIN_MODULE . "/config/payaliconfig") . "' target='_blank'>点击此处去开启</a>)</p>";
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
        $result = $pay_service->orderClose($param["out_trade_no"]);
        return $result;
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