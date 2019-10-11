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

namespace addons\NsMemberPraise\admin\controller;

use addons\NsMemberPraise\data\service\MemberPraise as MemberPraiseService;
use app\admin\controller\BaseController;
use data\service\Promotion as PromotionService;
use data\service\promotion\PromoteRewardRule;

/**
 * 会员点赞
 */
class MemberPraise extends BaseController
{
	
	public $addon_view_path;
	
	public function __construct()
	{
		parent::__construct();
		$this->addon_view_path = ADDON_DIR . '/NsMemberPraise/template/';
	}
	
	public function index()
	{
		$member_register_service = new MemberPraiseService();
		if (request()->isAjax()) {
			$click_point = request()->post('click_point', 0);
			$click_coupon = request()->post("click_coupon", 0);
			$res = $member_register_service->setRewardRule($click_point, $click_coupon);
			return AjaxReturn($res);
		}
		
		$coupon = new PromotionService();
		
		$condition = 'shop_id = ' . $this->instance_id . ' AND (start_time <= ' . time() . ' AND end_time >= ' . time() . ' AND term_of_validity_type = 0)';
		$condition .= ' OR (term_of_validity_type = 1)';
		
		$coupon_list = $coupon->getCouponTypeList(1, 0, $condition, 'start_time desc');
		$this->assign("coupon_list", $coupon_list['data']);
		
		$rewardRule = new PromoteRewardRule();
		$reward_rule_info = $rewardRule->getRewardRuleInfo("click_point,click_coupon");
		$this->assign("reward_rule_info", $reward_rule_info);
		
		$member_action_config = $member_register_service->getMemberActionConfig();
		$this->assign("member_action_config", $member_action_config);
		
		return view($this->addon_view_path . $this->style . 'MemberPraise/index.html');
	}
	
	public function setMemberActionConfig()
	{
		$click_coupon = request()->post('click_coupon', 0);
		$params = [
			'click_coupon' => $click_coupon
		];
		$member_register_service = new MemberPraiseService();
		$res = $member_register_service->setMemberActionConfig($params);
		return AjaxReturn($res);
	}
	
}