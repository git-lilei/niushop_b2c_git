<?php
/**
 * Index.php
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

namespace app\web\controller;

use think\Cookie;

/**
 * 首页控制器
 */
class Index extends BaseWeb
{
	/*
	 * 首页
	 */
	public function index()
	{
		$default_client = request()->cookie("default_client", "");
		if ($default_client == "web") {
		} elseif (request()->isMobile() && $this->web_info['wap_status'] != 2) {
			$redirect = __URL(__URL__ . "/wap");
			$this->redirect($redirect);
			exit();
		}
		if ($this->web_info['web_status'] == 2) {
			webClose($this->web_info['close_reason']);
		}
		return $this->view($this->style . 'index/index');
	}
	
	/**
	 * 错误页
	 */
	public function errorTemplate()
	{
		return $this->view($this->style . 'index/error');
	}
	
	/**
	 * 删除设置页面打开cookie
	 */
	public function deleteClientCookie()
	{
		Cookie::delete("default_client");
		return AjaxReturn(1);
	}
	
	/**
	 * 设置页面打开cookie
	 */
	public function setClientCookie()
	{
		$client = request()->post('client', '');
		Cookie::set("default_client", $client);
		return AjaxReturn(1);
	}
}