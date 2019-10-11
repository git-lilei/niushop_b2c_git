<?php
/**
 * Config.php
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
namespace addons\NsWeixinpay\admin\controller;

use addons\NsWeixinpay\data\service\WxpayConfig;
use app\admin\controller\BaseController;

/**
 * 网站设置模块控制器
 */
class Config extends BaseController
{
	public $addon_view_path;
	
	public function __construct()
	{
		parent::__construct();
		$this->addon_view_path = ADDON_DIR . '/NsWeixinpay/template/';
	}
    /**
     * 微信支付配置
     */
    public function payWchatConfig()
    {
        $web_config = new WxpayConfig();
        if (request()->isAjax()) {
            // 微信支付
            $type = request()->post('type', '');
            $appkey = str_replace(' ', '', request()->post('appkey', ''));
            $appsecret = str_replace(' ', '', request()->post('appsecret', ''));
            $paySignKey = str_replace(' ', '', request()->post('paySignKey', ''));
            $MCHID = str_replace(' ', '', request()->post('MCHID', ''));
            $is_use = request()->post('is_use', 0);
        
            $value = request()->post("value", "");
        
            $transferValue = request()->post("transferValue", "");
        
            $res_one = $web_config->setWpayConfig($this->instance_id, $appkey, $appsecret, $MCHID, $paySignKey, $is_use);
            $res_two = $web_config->setOriginalRoadRefundSetting($this->instance_id, 'wechat', $value);
        
            // $retval = $web_config->checkPayConfigEnabledOne($this->instance_id, 'wechat');
            // if ($retval == 1) {
            $res_three = $web_config->setTransferAccountsSetting($this->instance_id, $transferValue);
            // } else {
            // $res_three = $retval;
            // }
        
            if ($res_one > 0 && $res_two > 0 && $res_three > 0) {
                return AjaxReturn(1);
            } else {
                return AjaxReturn(- 1);
            }
        }
        $data = $web_config->getWpayConfig($this->instance_id);
        $this->assign("config", $data);
        // 获取当前域名
        $root_url = __URL(__URL__ . "/wap/pay");
        $root_url = str_replace(".html", "/", $root_url);
        $this->assign('root_url', $root_url);
        
        $pay_list = $web_config->getWpayConfig($this->instance_id);
        
        // 退款
        $refund_data = $web_config->getOriginalRoadRefundSetting($this->instance_id);
        
        if (! empty($data)) {
            $original_road_refund_setting_info = json_decode($refund_data['value'], true);
        }
        $this->assign("original_road_refund_setting_info", $original_road_refund_setting_info);
        
        // 转账
        $accounts_data = $web_config->getTransferAccountsSetting($this->instance_id);
        if (! empty($data)) {
            $transfer_accounts_setting_info = json_decode($accounts_data['value'], true);
        }
        $this->assign("transfer_accounts_setting_info", $transfer_accounts_setting_info);
        
        return view($this->addon_view_path.$this->style . "Config/payConfig.html");
    }
}