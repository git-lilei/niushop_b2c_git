<?php
/**
 * CustomTemplate.php
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

namespace app\wap\controller;

use data\service\Config as WebConfig;
use data\service\Member;

/**
 * 手机端自定义模板控制器
 */
class CustomTemplate extends BaseWap
{
	
	public function customTemplateIndex()
	{
		if ($this->custom_template_is_enable == 0) {
			// 没有开启自定义模板，跳转到首页
			$this->redirect(__URL(\think\Config::get('view_replace_str.APP_MAIN') . "/Index/index"));
		}
		$id = request()->get("id", 0);
		$config = new WebConfig();
		$member = new Member();
		$custom_template_info = $config->getFormatCustomTemplate($id);
		
		$this->assign("custom_template", $custom_template_info);
		
		// 首页优惠券
		$coupon_list = $member->getMemberCouponTypeList($this->instance_id, $this->uid);
		$this->assign('coupon_list', $coupon_list);
		
		// 公众号配置查询
		$wchat_config = $config->getInstanceWchatConfig($this->instance_id);
		
		$is_subscribe = 0; // 标识：是否显示顶部关注 0：[隐藏]，1：[显示]
		if ($this->web_info["is_show_follow"] == 1) {
			// 检查是否配置过微信公众号
			if (!empty($wchat_config['value'])) {
				if (!empty($wchat_config['value']['appid']) && !empty($wchat_config['value']['appsecret'])) {
					// 如何判断是否关注
					if (isWeixin()) {
						if (!empty($this->uid)) {
							// 检查当前用户是否关注
							$user_sub = $this->user->checkUserIsSubscribeInstance($this->uid);
							if ($user_sub == 0) {
								// 未关注
								$is_subscribe = 1;
							}
						}
					}
				}
			}
		}
		
		$this->assign("is_subscribe", $is_subscribe);
		
		// 手机端自定义模板底部菜单
		$this->getWapCustomTemplateFooter();
		
		// 公众号二维码获取
		$source_user_name = "";
		$source_img_url = "";
		$source_uid = request()->get('source_uid', '');
		if (!empty($source_uid)) {
			$_SESSION['source_uid'] = $source_uid;
			$user_info = $member->getUserInfoByUid($_SESSION['source_uid']);
			if (!empty($user_info)) {
				$source_user_name = $user_info["nick_name"];
				if (!empty($user_info["user_headimg"])) {
					$source_img_url = $user_info["user_headimg"];
				}
			}
		}
		
		// 首页公告
		$this->assign('source_user_name', $source_user_name);
		$this->assign('source_img_url', $source_img_url);
		
		return $this->view($this->style . 'CustomTemplate/customTemplateIndex');
	}
	
	
	/**
	 * 手机端base.html公用底部菜单
	 */
	public function getWapCustomTemplateFooter()
	{
		$config = new WebConfig();
		$template_info = $config->getFormatCustomTemplate();
		if (empty($template_info)) {
			$this->custom_template_is_enable = 0;//如果开启了自定义模板，但是没有内容，应该跳转到普通首页
		}
		$custom_footer = array();
		if (!empty($template_info)) {
			$custom_template_info = $template_info['template_data'];
			foreach ($custom_template_info as $k => $v) {
				$custom_template_info[ $k ]["style_data"] = json_decode($v["control_data"], true);
			}
			for ($i = 0; $i < count($custom_template_info); $i++) {
				$v = $custom_template_info[ $i ];
				if ($v["control_name"] == "Footer") {
					// 首页
					if (trim($v["style_data"]["footer"]) != "") {
						// 底部菜单
						$custom_footer = json_decode($v["style_data"]["footer"], true);
						break;
					}
				}
			}
		}
		
		// 当前打开页面时，底部菜单的的对应的选中
		$select_footer_index = 0; // 底部菜单下标标识
		$template_id = request()->get('id', 0);
		if (!empty($custom_footer) && (substr(request()->pathinfo(), -34, -1) != 'CustomTemplate/customTemplateInde' || $template_id != 0)) {
			foreach ($custom_footer as $k => $v) {
				
				// 如果只有wap，没有index/index
				if (strpos(strtolower(request()->module() . "/" . request()->action()), strtolower(request()->pathinfo())) !== false) {
					$select_footer_index = 0;
					break;
				}
				if (strpos(strtolower($v['href']), strtolower(request()->pathinfo())) !== false) {
					$select_footer_index = $k;
				}
			}
		}
		$this->assign("select_footer_index", $select_footer_index);
		$this->assign("custom_footer", $custom_footer);
	}
}