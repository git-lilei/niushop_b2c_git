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
namespace addons\NsWeixinpay;

use addons\NsWeixinpay\data\service\WxpayConfig;
use data\service\UnifyPay;
use addons\NsWeixinpay\data\service\Pay;
use think\Log;
use data\service\Config as WebConfig;

class NsWeixinpayAddon extends \addons\Addons
{
	
	public $info = array(
		'name' => 'NsWeixinpay', // 插件名称标识
		'title' => '微信支付', // 插件中文名
		'description' => '该系统支持微信网页支付和扫码支付', // 插件概述
		'status' => 1, // 状态 1启用 0禁用
		'author' => 'niushop', // 作者
		'version' => '1.0', // 版本号
		'has_addonslist' => 0, // 是否有下级插件 例如：第三方登录插件下有 qq登录，微信登录
		'content' => '', // 插件的详细介绍或使用方法
		'ico' => 'addons/NsWeixinpay/ico.png'
	);
	
	public function payconfig($param)
	{
		$weixinpay_config = new WxpayConfig();
		$wechat_config = $weixinpay_config->getWpayConfig(0);
		$config['is_use'] = $wechat_config['is_use'];
		$config["logo"] = $this->info['ico'];
		$config["pay_name"] = "微信支付";
		
		$config['addon_name'] = $this->info['name'];
		$config["desc"] = "该系统支持微信网页支付和扫码支付";
		$config['url'] = __URL('__URL__/NsWeixinpay/' . ADMIN_MODULE . '/Config/paywchatconfig');
		$config['pay_logo'] = "public/admin/images/pay.png";  //支付按钮
		$config['pay_url'] = 'APP_MAIN/pay/onlinepay';//支付页面
		$config['h5_icon'] = 'weifu.png';
		$config['pc_icon'] = 'wechat_qr.png';
		$config['lang'] = 'wechat_payment';
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
	    $pay_service = new Pay();
	    if (!isWeixin()) {
	        // 扫码支付
	        if (request()->isMobile()) {
	            $res = $pay_service->wchatPay($param['out_trade_no'], 'MWEB', $param['notify_url']);
	            if (empty($res['mweb_url'])) 
	            {
	                return error(['return_type' => 'url', 'out_message' => json_encode($res, JSON_UNESCAPED_UNICODE)]);
	            }
	            return success(['return_type' => 'url', 'url' => $res["mweb_url"]]);
	        } else {
	            $res = $pay_service->wchatPay($param['out_trade_no'], 'NATIVE', $param['notify_url']);
	            if ($res["return_code"] == "SUCCESS") {
	                if (empty($res['code_url'])) {
	                    $code_url = json_encode($res, JSON_UNESCAPED_UNICODE);
	                } else {
	                    $code_url = $res['code_url'];
	                }
	                if (!empty($res["err_code"]) && $res["err_code"] == "ORDERPAID" && $res["err_code_des"] == "该订单已支付") {
	                    $code_url = json_encode($res, JSON_UNESCAPED_UNICODE);;
	                }
	            } else {
	                $code_url = json_encode($res, JSON_UNESCAPED_UNICODE);
	            }
	            return success(['return_type' => 'qrcode', 'code_url' => $code_url]);
	        }
	    } else {
	        // jsapi支付
	        $res = $pay_service->wchatPay($param['out_trade_no'], 'JSAPI', $param['notify_url']);
	        if (!empty($res["return_code"]) && $res["return_code"] == "FAIL" && $res["return_msg"] == "JSAPI支付必须传openid") {
	            return error(['return_type' => 'url', 'out_message' => json_encode($res, JSON_UNESCAPED_UNICODE)]);
	        } else {
	            $retval = $pay_service->getWxJsApi($res);
	            $this->assign("out_trade_no", $param['out_trade_no']);
	            $this->assign('jsApiParameters', $retval);
	            $config = new WebConfig();
	            $wap_template = $config->getUseWapTemplate($this->instance_id);
	            if (empty($wap_template)) {
	                $wap_template['value'] = 'default';
	            }
	            $view_replace_str = [
	                'WAP_CSS' => __ROOT__ . '/template/wap/'.$wap_template['value'].'/public/css',
	                'WAP_JS' => __ROOT__ . '/template/wap/'.$wap_template['value'].'/public/js',
	                'WAP_PLUGIN' => __ROOT__ . '/template/wap/'.$wap_template['value'].'/public/plugin',
	            ];
	            $view = $this->fetch(ADDON_DIR . '/NsWeixinpay/template/wechat_pay.html', [], $view_replace_str);
	            return success(['return_type' => 'html', 'html' => $view]);
	           
	        }
	    }
	}
	
	/**
	 * 同步回调(必存在)
	 * @param unknown $param
	 */
	public function payReturn($param)
	{
        $msg = request()->get('msg', '');
        return ["msg" => $msg];
	}
	
	/**
	 * 异步回调(必存在)
	 * @param unknown $param
	 */
	public function payNotify($param)
	{
		try{
		    $postStr = file_get_contents('php://input');
		    Log::write("+++++++++++++++++++++++++++++");
		    if (!empty($postStr)) {
		        libxml_disable_entity_loader(true);
		        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
	            $pay_service = new Pay();
	            $pay = new UnifyPay();
		        $check_sign = $pay_service->checkSign($postObj, $postObj->sign);
		        if ($postObj->result_code == 'SUCCESS' && $check_sign == 1) {
		    
		            $retval = $pay->onlinePay($postObj->out_trade_no, 1, '');
		            $xml = "<xml>
	                    <return_code><![CDATA[SUCCESS]]></return_code>
	                    <return_msg><![CDATA[支付成功]]></return_msg>
	                </xml>";
		            echo $xml;
		        } else {
		            $xml = "<xml>
	                    <return_code><![CDATA[FAIL]]></return_code>
	                    <return_msg><![CDATA[支付失败]]></return_msg>
	                </xml>";
		            echo $xml;
		        }
		    }
	    } catch (\Exception $e) {
	    		
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
	    $weixin_pay = new Pay();
	    $retval = $weixin_pay->setWeiXinRefund($param['refund_no'], $param['out_trade_no'], $param['refund_fee']*100, $param['total_fee']*100);
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
        $config_service = new WxpayConfig();
        $transfer_account_config = $config_service->getTransferAccountsSetting(0);
        if (empty($transfer_account_config)) {
            return array(
                "code" => -1,
                "message" => '未配置微信转账功能'
            );
        }
        $transfer_account_config = json_decode($transfer_account_config['value'], true);
        if ($transfer_account_config['is_use'] == 0) {
            return array(
                "code" => -1,
                "message" => '未启用微信转账功能'
            );
        }
	    $weixin_pay = new Pay();
	    $retval = $weixin_pay->wechatTransfers($param['account_number'], $param['withdraw_no'], $param['amount']*100, $param['realname'], $param['desc']);

        $msg = $retval["msg"];
        if ($retval["is_success"] > 0) {
            $status = 1;
        } else {
            $status = -1;
        }
        $retval = ["msg" => $msg, "status" => $status];
	    return success($retval);
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
        $wxpay_config = new WxpayConfig();
        $pay_info = $wxpay_config->getWpayConfig(0);
        $msg = "";
        if (empty($pay_info) || empty($pay_info['value']['appid']) || empty($pay_info['value']['appkey']) || empty($pay_info['value']['mch_id']) || empty($pay_info['value']['mch_key'])) {
            $msg = "<p>请检查微信支付配置信息填写是否正确(<a href='" . __URL(__URL__ . "/NsWeixinpay/" . ADMIN_MODULE . "/config/paywchatconfig") . "' target='_blank'>点击此处进行配置</a>)</p>";
        }

        if ($pay_info['is_use'] == 0) {
            $msg = "<p>当前未开启微信支付配置(<a href='" . __URL(__URL__ . "/NsWeixinpay/" . ADMIN_MODULE . "/config/paywchatconfig") . "' target='_blank'>点击此处去开启</a>)</p>";
        } else {
            $refund_setting_info = $wxpay_config->getOriginalRoadRefundSetting(0);   
            $refund_setting = json_decode($refund_setting_info['value'], true);
            if (empty($refund_setting['apiclient_cert']) || empty($refund_setting['apiclient_key'])) {
                $msg = "<p>请检查微信原路退款配置信息填写是否正确(<a href='" . __URL(__URL__ . "/NsWeixinpay/" . ADMIN_MODULE . "/config/paywchatconfig") . "' target='_blank'>点击此处进行配置</a>)</p>";
            }
            if ($refund_setting['is_use'] == 0) {
                $msg = "<p>当前未开启微信原路退款配置(<a href='" . __URL(__URL__ . "/NsWeixinpay/" . ADMIN_MODULE . "/config/paywchatconfig") . "' target='_blank'>点击此处去开启</a>)</p>";
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
        $result = $pay_service->setOrderClose($param["out_trade_no"]);
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