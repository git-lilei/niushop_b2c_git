<?php
/**
 * MemberRegister.php
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

namespace addons\NsMemberRegister\data\service;

/**
 * 会员行为——注册
 */
use data\service\BaseService;
use data\service\Config;
use data\service\promotion\PromoteRewardRule;

class MemberRegister extends BaseService
{
	/**
	 * 获取会员行为设置
	 */
	public function getMemberActionConfig()
	{
		$config = new Config();
		$register_integral = $config->getConfig($this->instance_id, 'REGISTER_INTEGRAL');
		$register_coupon = $config->getConfig($this->instance_id, 'REGISTER_COUPON');
		
		if (empty($register_integral)) {
			$params = [
				'register' => '',
				'reg_coupon' => ''
			];
			$this->setMemberActionConfig($params);
			$array = array(
				'register_integral' => '',
				'register_coupon' => ''
			);
		} else {
			$array = array(
				'register_integral' => $register_integral['value'],
				'register_coupon' => $register_coupon['value']
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
			'key' => 'REGISTER_INTEGRAL',
			'value' => $params['register'],
			'desc' => '注册送积分',
			'is_use' => 1
		);
		$array[1] = array(
			'instance_id' => $this->instance_id,
			'key' => 'REGISTER_COUPON',
			'value' => $params['reg_coupon'],
			'desc' => '注册送优惠券',
			'is_use' => 1
		);
		
		$config = new Config();
		$res = $config->setConfig($array);
		return $res;
	}
	
	public function setRewardRule($reg_member_self_point, $reg_coupon)
	{
		$promote = new PromoteRewardRule();
		$data = array(
			'reg_member_self_point' => $reg_member_self_point,
			'reg_coupon' => $reg_coupon
		);
		$promote->setRewardRule($data);
	}
	
	/**
	 *  注册会员 送积分
	 */
	public function registerMemberGivePoint($uid)
	{
		//检测是否开启注册送积分的功能
		$config_info = $this->getMemberActionConfig();
		if ($config_info['register_integral'] > 0) {
			$promote = new PromoteRewardRule();
			$reward_rule_info = $promote->getRewardRuleInfo("reg_member_self_point");
			$res = $promote->addMemberPointData($this->instance_id, $uid, $reward_rule_info['reg_member_self_point'], 7, '注册会员赠送积分');
			switch (NS_VERSION) {
				case NS_VER_B2C_FX:
					//单店分销版本  上级添加积分
					$promote->SendPointMemberUpperThree($uid);
					break;
			}
			return $res;
		} else {
			return 0;
		}
	}
	
}