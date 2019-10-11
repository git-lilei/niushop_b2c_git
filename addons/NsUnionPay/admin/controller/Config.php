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

namespace addons\NsUnionPay\admin\controller;


use addons\NsUnionPay\data\service\UnionPayConfig;
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
		$this->addon_view_path = ADDON_DIR . '/NsUnionPay/template/';
	}
	
	/**
	 * 银联卡支付
	 */
	public function payUnionConfig()
	{
		$web_config = new UnionPayConfig();
		if (request()->isAjax()) {
			// 银联卡
			$merchant_number = str_replace(' ', '', request()->post('merchant_number', ''));
			$sign_cert_pwd = str_replace(' ', '', request()->post('sign_cert_pwd', ''));
			$certs_path = str_replace(' ', '', request()->post('certs_path', ''));
			$log_path = str_replace(' ', '', request()->post('log_path', ''));
			$service_charge = str_replace(' ', '', request()->post('service_charge', ''));
			$is_use = request()->post('is_use', 0);
			$value = request()->post("value", "");
			// 获取数据
			$retval = $web_config->setUnionpayConfig($this->instance_id, $merchant_number, $sign_cert_pwd, $certs_path, $log_path, $service_charge, $is_use);
			$res_two = $web_config->setOriginalRoadRefundSetting($this->instance_id, $value);
			
			return AjaxReturn($retval);
		}
		
		$data = $web_config->getUnionpayConfig($this->instance_id);
		$this->assign("config", $data);
		// 退款
		$refund_data = $web_config->getOriginalRoadRefundSetting($this->instance_id);
		
		if (!empty($data)) {
			$original_road_refund_setting_info = json_decode($refund_data['value'], true);
		}
		$this->assign("original_road_refund_setting_info", $original_road_refund_setting_info);
		
		return view($this->addon_view_path . $this->style . "Config/unionPayConfig.html");
	}
}