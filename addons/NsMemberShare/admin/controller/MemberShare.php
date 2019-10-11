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

namespace addons\NsMemberShare\admin\controller;


use addons\NsMemberShare\data\service\MemberShare as MemberShareService;
use app\admin\controller\BaseController;
use data\service\Promotion as PromotionService;
use data\service\promotion\PromoteRewardRule;
use data\service\Shop;

/**
 * 会员分享设置
 */
class MemberShare extends BaseController
{
	
	public $addon_view_path;
	
	public function __construct()
	{
		parent::__construct();
		$this->addon_view_path = ADDON_DIR . '/NsMemberShare/template/';
	}
	
	public function index()
	{
		$member_register_service = new MemberShareService();
		$rewardRule = new PromoteRewardRule();
		if (request()->isAjax()) {
			$share_point = request()->post('share_point', 0);
			$share_coupon = request()->post("share_coupon", 0);
			$res = $member_register_service->setRewardRule($share_point, $share_coupon);
			return AjaxReturn($res);
		}
		
		$coupon = new PromotionService();
		
		$condition = 'shop_id = ' . $this->instance_id . ' AND (start_time <= ' . time() . ' AND end_time >= ' . time() . ' AND term_of_validity_type = 0)';
		$condition .= ' OR (term_of_validity_type = 1)';
		
		$coupon_list = $coupon->getCouponTypeList(1, 0, $condition, 'start_time desc');
		$this->assign("coupon_list", $coupon_list['data']);
		
		$reward_rule_info = $rewardRule->getRewardRuleInfo("share_point,share_coupon");
		$this->assign("reward_rule_info", $reward_rule_info);
		
		$member_action_config = $member_register_service->getMemberActionConfig();
		$this->assign("member_action_config", $member_action_config);
		
		return view($this->addon_view_path . $this->style . 'MemberShare/index.html');
	}
	
	public function setMemberActionConfig()
	{
		$share = request()->post('share', 0);
		$share_coupon = request()->post('share_coupon', 0);
		$params = [
			'share' => $share,
			'share_coupon' => $share_coupon
		];
		$member_register_service = new MemberShareService();
		$res = $member_register_service->setMemberActionConfig($params);
		return AjaxReturn($res);
	}
	
	/**
	 * 分享设置
	 */
	public function shareConfig(){
	    $shop = new Shop();
	    if (request()->isAjax()) {
	        $goods_param_1 = request()->post('goods_param_1', '');
	        $goods_param_2 = request()->post('goods_param_2', '');
	        $shop_param_1 = request()->post('shop_param_1', '');
	        $shop_param_2 = request()->post('shop_param_2', '');
	        $shop_param_3 = request()->post('shop_param_3', '');
	        $qrcode_param_1 = request()->post('qrcode_param_1', '');
	        $qrcode_param_2 = request()->post('qrcode_param_2', '');
	        $data = array(
	            'goods_param_1' => $goods_param_1,
	            'goods_param_2' => $goods_param_2,
	            'shop_param_1' => $shop_param_1,
	            'shop_param_2' => $shop_param_2,
	            'shop_param_3' => $shop_param_3,
	            'qrcode_param_1' => $qrcode_param_1,
	            'qrcode_param_2' => $qrcode_param_2
	        );
	        $res = $shop->updateShopShareConfig($data);
	        return AjaxReturn($res);
	    }
	    $config = $shop->getShopShareConfig($this->instance_id);
	    $this->assign("config", $config);
	    return view($this->addon_view_path . $this->style . 'MemberShare/shareConfig.html');
	}
	
}