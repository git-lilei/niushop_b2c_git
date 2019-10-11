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

namespace addons\NsMemberShare\data\service;

use data\service\BaseService;
use data\service\Config;
use data\service\Member;
use data\service\promotion\PromoteRewardRule;


/**
 * 会员行为——签到
 */
class MemberShare extends BaseService
{
	
	/**
	 * 获取会员行为设置
	 */
	public function getMemberActionConfig()
	{
		$config = new Config();
		$share_integral = $config->getConfig($this->instance_id, 'SHARE_INTEGRAL');
		$share_coupon = $config->getConfig($this->instance_id, 'SHARE_COUPON');
		if (empty($share_integral) && empty($share_coupon)) {
			$params = [
				'share' => '',
				'share_coupon' => ''
			];
			$this->setMemberActionConfig($params);
			$array = array(
				'share_integral' => '',
				'share_coupon' => ''
			);
		} else {
			$array = array(
				'share_integral' => $share_integral['value'],
				'share_coupon' => $share_coupon['value']
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
			'key' => 'SHARE_INTEGRAL',
			'value' => $params['share'],
			'desc' => '分享送积分',
			'is_use' => 1
		);
		$array[1] = array(
			'instance_id' => $this->instance_id,
			'key' => 'SHARE_COUPON',
			'value' => $params['share_coupon'],
			'desc' => '分享送优惠券',
			'is_use' => 1
		);
		
		$config = new Config();
		$res = $config->setConfig($array);
		return $res;
	}
	
	public function setRewardRule($share_point, $share_coupon)
	{
		$promote = new PromoteRewardRule();
		$data = array(
			'share_point' => $share_point,
			'share_coupon' => $share_coupon
		);
		$promote->setRewardRule($data);
	}
	
	/**
	 * 会员分享
	 */
	public function memberShare($uid)
	{
		$member = new Member();
		
		// 获取奖励配置
		$member_action_config = $this->getMemberActionConfig();
		
		$promote = new PromoteRewardRule();
		$reward_rule_info = $promote->getRewardRuleInfo("share_point,share_coupon");
		
		//判断今天是否已经分享过
		$count = $member->getIsMemberShare($uid, 0);
		$res = 0;
		
		// 判断是否开启分享送积分
		if ($member_action_config['share_integral'] > 0) {
			if ($reward_rule_info['share_point'] != 0 && $count <= 0) {
				$res = $promote->addMemberPointData(0, $uid, $reward_rule_info['share_point'], 6, '分享赠送积分');
			}
		}
		
		// 判断是否开启分享送优惠券
		if ($member_action_config['share_coupon'] > 0) {
			if ($reward_rule_info['share_coupon'] != 0 && $count <= 0) {
				$res = $member->memberGetCoupon($uid, $reward_rule_info['share_coupon'], 2);
			}
		}
		
		return $res;
	}
}