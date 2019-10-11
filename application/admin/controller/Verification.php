<?php
/**
 * Cms.php
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

namespace app\admin\controller;

use data\service\orderVertify as VerificationService;
use data\service\Member as MemberService;

/**
 * 核销管理
 */
class Verification extends BaseController
{
	
	/**
	 * 核销人员列表
	 */
	public function index()
	{
		if (request()->isAjax()) {
			$verification = new VerificationService();
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$list = $verification->getVerificationPersonnelList($page_index, $page_size, "", "create_time desc");
			return $list;
		}
		$child_menu_list = array();
		$child_menu_list[0] = array(
			'url' => "Verification/index",
			'menu_name' => '核销人员',
			"active" => 1
		);
		$child_menu_list[1] = array(
			'url' => "Verification/virtualGoodsVerificationList?type=to_reply",
			'menu_name' => '核销记录',
			"active" => 0
		);
		$this->assign('child_menu_list', $child_menu_list);
		return view($this->style . 'Verification/index');
	}
	
	/**
	 * 获取用户列表
	 */
	public function getMemberList()
	{
		if (request()->isAjax()) {
			$search_text = request()->post("search_text", "");
			$v_id = request()->post("v_id", "");
			$member = new MemberService();
			$condition = [
				'su.is_member' => 1,
				'su.nick_name|su.user_tel|su.user_email' => array(
					'like',
					'%' . $search_text . '%'
				),
				'su.is_system' => 0,
				'su.uid' => array(
					'not in', $v_id
				)
			];
			$list = $member->getMemberList(1, 0, $condition);
			return $list;
		}
	}
	
	/**
	 * 添加核销人员
	 */
	public function addVerificationPerson()
	{
		$verification = new VerificationService();
		$uid = request()->post("uid", "");
		$res = $verification->addVerificationPersonne($uid, $this->instance_id);
		return AjaxReturn($res);
	}
	
	/**
	 * 删除核销人员
	 * @return number
	 */
	public function deleteVerificationPerson()
	{
		$verification = new VerificationService();
		$vid = request()->post("vid", "");
		$res = $verification->deleteVerificationPerson($vid);
		return AjaxReturn($res);
	}
	
	
	/**
	 * 核销
	 * @return number
	 */
	public function verificationVirtualGoods()
	{
		$virtual_goods_id = request()->post("virtual_goods_id", 0);
		$uid = $this->uid;
		$verification = new VerificationService();
		$res = $verification->verificationVirtualGoods($uid, $virtual_goods_id, true);
		return AjaxReturn($res);
	}
	
	
	/**
	 * 核销记录
	 * @return number
	 */
	public function virtualGoodsVerificationList()
	{
		if (request()->isAjax()) {
			$verification = new VerificationService();
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$search_name = request()->post('search_name', '');
			$condition = array();
			if ($search_name != '') {
				$condition["goods_name"] = array(
					'like',
					'%' . $search_name . '%'
				);
			}
			$list = $verification->getVirtualGoodsVerificationList($page_index, $page_size, $condition);
			return $list;
		}
		
		$type = request()->get('type', '');
		$child_menu_list = array();
		$child_menu_list[0] = array(
			'url' => "Verification/index",
			'menu_name' => '核销人员',
			"active" => 0
		);
		$child_menu_list[1] = array(
			'url' => "Verification/virtualGoodsVerificationList?type=to_reply",
			'menu_name' => '核销记录',
			"active" => 1
		);
		$this->assign('child_menu_list', $child_menu_list);
		
		return view($this->style . "Verification/virtualGoodsVerificationList");
	}
	
}