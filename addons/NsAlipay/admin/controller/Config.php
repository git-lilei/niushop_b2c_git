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
namespace addons\NsAlipay\admin\controller;

use addons\NsAlipay\data\service\AlipayConfig;
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
		$this->addon_view_path = ADDON_DIR . '/NsAlipay/template/';
	}
    /**
     * 支付宝配置
     */
    public function payAliConfig()
    {
        $web_config = new AlipayConfig();
 
        if (request()->isAjax()) {
                    // 支付宝
            $partnerid = str_replace(' ', '', request()->post('partnerid', ''));
            $seller = str_replace(' ', '', request()->post('seller', ''));
            $ali_key = str_replace(' ', '', request()->post('ali_key', ''));
           
            $status = request()->post('status', 0);
            
            $private_key = request()->post("private_key","");
            $public_key = request()->post("public_key","");
            $app_id = request()->post("appid","");
            $alipay_public_key = request()->post("alipay_public_key", "");
            // 获取数据
            if($status == 0){
            	$res_one = $web_config->setAlipayConfig($this->instance_id, $partnerid, $seller, $ali_key);
            }else{
            	$res_one = $web_config->setAlipayConfigNew($this->instance_id, $public_key, $app_id, $private_key, $alipay_public_key);
            }
            $is_use = request()->post('is_use', '');
            $res_two = $web_config->setAlipayStatus($this->instance_id, $is_use);
            
            $value = request()->post("value", "");
            $res_two = $web_config->setOriginalRoadRefundSetting($this->instance_id, $value);
            
            $transferValue = request()->post("transferValue", "");
            $res_three = $web_config->setTransferAccountsSetting($this->instance_id, $transferValue);

            $new_type = request()->post('new_type', 1);            
            $res_foer = $web_config->setAliPayVersionSetting($this->instance_id, $new_type);
            
            if ($res_one > 0 && $res_two > 0 && $res_three > 0) {
                return AjaxReturn(1);
            } else {
                return AjaxReturn(- 1);
            }
        }
        $data = $web_config->getAlipayConfig($this->instance_id);
        $this->assign("config", $data);
        
        $data_new = $web_config->getAlipayConfigNew($this->instance_id);
        $this->assign("config_new", $data_new);
       
        //查找当前版本
        $edition_data = $web_config->getAliPayVersion($this->instance_id);
        if($edition_data == ""){
        	$edition_data['is_use'] = 1;
        }else{
        	$edition_data = json_decode($edition_data['value'],true);
        }        
        $this->assign("edition_data",$edition_data);
        // 退款
        $refund_data = $web_config->getOriginalRoadRefundSetting($this->instance_id);
        
        if (! empty($data)) {
            $original_road_refund_setting_info = json_decode($refund_data['value'], true);
        }
        $this->assign("original_road_refund_setting_info", $original_road_refund_setting_info);
        
        $alipay_status = $web_config->getAliPayStatus($this->instance_id);
        if(!empty($alipay_status)){
        	$alipay_status_info = json_decode($alipay_status['value'], true);
        }
        $this->assign("alipay_status_info", $alipay_status_info);
        // 转账
        $accounts_data = $web_config->getTransferAccountsSetting($this->instance_id);
        if (! empty($data)) {
            $transfer_accounts_setting_info = json_decode($accounts_data['value'], true);
        }
        $this->assign("new",request()->get("new"));
        $this->assign("transfer_accounts_setting_info", $transfer_accounts_setting_info);
        
        return view($this->addon_view_path.$this->style . "Config/payAliConfig.html");
    }
}