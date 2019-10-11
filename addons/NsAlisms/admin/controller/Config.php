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
namespace addons\NsAlisms\admin\controller;

use addons\NsAlisms\data\service\AlismsConfig;
use app\admin\controller\BaseController;

/**
 * 阿里云短信模块控制器
 */
class Config extends BaseController
{
	public $addon_view_path;
	
	public function __construct()
	{
		parent::__construct();
		$this->addon_view_path = ADDON_DIR . '/NsAlisms/template/';
	}
    
    /**
     * 邮件短信接口设置
     */
    public function alismsConfig()
    {
        if(request()->isAjax())
        {
            $app_key = request()->post('app_key', '');
            $secret_key = request()->post('secret_key', '');
            $free_sign_name = request()->post('free_sign_name', '');
            $is_use = request()->post('is_use', '');
            $user_type = request()->post('user_type', 0); // 用户类型 0:旧用户，1：新用户 默认是旧用户
            $mobile = request()->post("mobile", '');
            $config = new AlismsConfig();  
            $res = $config->setMobileMessage($this->instance_id, $app_key, $secret_key, $free_sign_name, $is_use, $user_type, $mobile);
            return AjaxReturn($res);
        }
        $config = new AlismsConfig();
        $mobile_message = $config->getMobileMessage($this->instance_id);
        $this->assign('mobile_message', $mobile_message);
          return view($this->addon_view_path.$this->style . "Config/messageConfig.html");
    }

}