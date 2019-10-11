<?php
/**
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

namespace addons\NsMemberSign\admin\controller;

use addons\NsMemberSign\data\service\MemberSign as MemberSignService;
use app\admin\controller\BaseController;
use data\service\Promotion as PromotionService;
use data\service\promotion\PromoteRewardRule;

/**
 * 会员行为——签到
 */
class MemberSign extends BaseController
{
	
	public $addon_view_path;
	
	public function __construct()
	{
		parent::__construct();
		$this->addon_view_path = ADDON_DIR . '/NsMemberSign/template/';
	}
	
	public function index()
	{
		$member_register_service = new MemberSignService();
		$rewardRule = new PromoteRewardRule();
		if (request()->isAjax()) {
			$sign_point = request()->post('sign_point', 0);
			$sign_coupon = request()->post("sign_coupon", 0);
			$res = $member_register_service->setRewardRule($sign_point, $sign_coupon);
			return AjaxReturn($res);
		}
		
		$coupon = new PromotionService();
		
		$condition = 'shop_id = ' . $this->instance_id . ' AND (start_time <= ' . time() . ' AND end_time >= ' . time() . ' AND term_of_validity_type = 0)';
		$condition .= ' OR (term_of_validity_type = 1)';
		
		$coupon_list = $coupon->getCouponTypeList(1, 0, $condition, 'start_time desc');
		$this->assign("coupon_list", $coupon_list['data']);
		
		$reward_rule_info = $rewardRule->getRewardRuleInfo("sign_point,sign_coupon");
		$this->assign("reward_rule_info", $reward_rule_info);
		
		$member_action_config = $member_register_service->getMemberActionConfig();
		$this->assign("member_action_config", $member_action_config);
		
		return view($this->addon_view_path . $this->style . 'MemberSign/index.html');
	}
	
	public function setMemberActionConfig()
	{
		$click_coupon = request()->post('sign', 0);
		$sign_coupon = request()->post('sign_coupon', 0);
		$params = [
			'sign' => $click_coupon,
			'sign_coupon' => $sign_coupon
		];
		$member_register_service = new MemberSignService();
		$res = $member_register_service->setMemberActionConfig($params);
		return AjaxReturn($res);
	}
	
}