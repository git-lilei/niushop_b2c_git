<?php
/**
 * Verification.php
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

class Verification extends BaseWap
{
	public function __construct()
	{
		parent::__construct();
		$this->checkLogin();
	}
	
	/**
	 * 核销商品详情
	 */
	public function detail()
	{
		$this->assign("title", '虚拟商品详情');
		$this->assign("title_before", '虚拟商品详情');
		return $this->view($this->style . "verification/detail");
	}
	
	/**
	 * 虚拟商品
	 */
	public function share()
	{
		$this->assign("title", "虚拟商品");
		$this->assign("title_before", "虚拟商品");
		return $this->view($this->style . 'verification/share');
	}
	
	/**
	 * 核销商品审核
	 */
	public function goods()
	{
		$this->assign("title", '核销商品核销');
		$this->assign("title_before", '核销商品核销');
		return $this->view($this->style . "verification/goods");
	}
	
	/**
	 * 我的虚拟码列表
	 */
	public function code()
	{
		$this->assign("title", lang('member_my_virtual_code'));
		$this->assign("title_before", lang('member_my_virtual_code'));
		return $this->view($this->style . "verification/code");
	}
	
	/**
	 * 核销台
	 */
	public function index()
	{
		$this->assign("title", '核销台');
		$this->assign("title_before", '核销台');
		return $this->view($this->style . "verification/index");
	}
}