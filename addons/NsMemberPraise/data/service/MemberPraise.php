<?php
/**
 *
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

namespace addons\NsMemberPraise\data\service;

use data\model\NsClickFabulousModel;
use data\service\BaseService;
use data\service\Config;
use data\service\Member;
use data\service\promotion\PromoteRewardRule;


/**
 * 会员行为——点赞
 */
class MemberPraise extends BaseService
{
	
	/**
	 * 获取会员行为设置
	 */
	public function getMemberActionConfig()
	{
		$config = new Config();
		$click_coupon = $config->getConfig($this->instance_id, 'CLICK_COUPON');
		if (empty($click_coupon)) {
			$params = [
				'click_coupon' => ''
			];
			$this->setMemberActionConfig($params);
			$array = array(
				'click_coupon' => ''
			);
		} else {
			$array = array(
				'click_coupon' => $click_coupon['value']
			);
		}
		
		return $array;
	}
	
	/**
	 * 设置会员行为设置
	 */
	public function setMemberActionConfig($params)
	{
		$array[0] = array(
			'instance_id' => $this->instance_id,
			'key' => 'CLICK_COUPON',
			'value' => $params['click_coupon'],
			'desc' => '点赞送优惠券',
			'is_use' => 1
		);
		
		$config = new Config();
		$res = $config->setConfig($array);
		return $res;
	}
	
	public function setRewardRule($click_point, $click_coupon)
	{
		$promote = new PromoteRewardRule();
		$data = array(
			'click_point' => $click_point,
			'click_coupon' => $click_coupon
		);
		$promote->setRewardRule($data);
	}
	
	/**
	 * 点赞送优惠券
	 * @param $uid
	 */
	public function praiseGiveCoupon($uid)
	{
		$member_action_config = $this->getMemberActionConfig();
		$res = 0;
		if ($member_action_config['click_coupon'] == 1) {
			$promote = new PromoteRewardRule();
			$reward_rule_info = $promote->getRewardRuleInfo("click_coupon");
			if ($reward_rule_info['click_coupon'] != 0) {
				$member = new Member();
				$res = $member->memberGetCoupon($uid, $reward_rule_info['click_coupon'], 2);
			}
		}
		return $res;
	}
	
	/**
	 * 点赞送积分
	 */
	public function praiseGivePoint($uid, $goods_id)
	{
		$click_goods = new NsClickFabulousModel();
		// 点赞成功送积分
		$rewardRule = new PromoteRewardRule();
		// 查询点赞赠送积分数量，然后叠加
		$info = $rewardRule->getRewardRuleInfo("click_point");
		$data = array(
			'shop_id' => $this->instance_id,
			'uid' => $uid,
			'goods_id' => $goods_id,
			'status' => 1,
			'number' => $info['click_point'],
			'create_time' => time()
		);
		$retval = $click_goods->save($data);
		if ($retval > 0) {
			$rewardRule->addMemberPointData($this->instance_id, $uid, $info['click_point'], 19, '点赞赠送积分');
		}
		return $retval;
	}
}