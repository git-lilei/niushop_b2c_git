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

namespace addons\NsMemberSign\data\service;

use data\model\NsCouponTypeModel;
use data\service\BaseService;
use data\service\Config;
use data\service\Member;
use data\service\promotion\PromoteRewardRule;


/**
 * 会员行为——签到
 */
class MemberSign extends BaseService
{
	
	/**
	 * 获取会员行为设置
	 */
	public function getMemberActionConfig()
	{
		$config = new Config();
		$sign_integral = $config->getConfig($this->instance_id, 'SIGN_INTEGRAL');
		$sign_coupon = $config->getConfig($this->instance_id, 'SIGN_COUPON');
		if (empty($sign_integral) && empty($sign_coupon)) {
			$params = [
				'sign' => '',
				'sign_coupon' => ''
			];
			$this->setMemberActionConfig($params);
			$array = array(
				'sign_integral' => '',
				'sign_coupon' => ''
			);
		} else {
			$array = array(
				'sign_integral' => $sign_integral['value'],
				'sign_coupon' => $sign_coupon['value']
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
			'key' => 'SIGN_INTEGRAL',
			'value' => $params['sign'],
			'desc' => '签到送积分',
			'is_use' => 1
		);
		$array[1] = array(
			'instance_id' => $this->instance_id,
			'key' => 'SIGN_COUPON',
			'value' => $params['sign_coupon'],
			'desc' => '签到送优惠券',
			'is_use' => 1
		);
		
		$config = new Config();
		$res = $config->setConfig($array);
		return $res;
	}
	
	public function setRewardRule($sign_point, $sign_coupon)
	{
		$promote = new PromoteRewardRule();
		$data = array(
			'sign_point' => $sign_point,
			'sign_coupon' => $sign_coupon
		);
		$promote->setRewardRule($data);
	}
	
	/**
	 * 签到配置
	 */
	public function getSignInConfig()
	{
		//检测是否开启签到送积分的功能
		$member_action_config = $this->getMemberActionConfig();
		$sign_in_info = [];
		$sign_in_info['sign_integral'] = $member_action_config['sign_integral'];
		$sign_in_info['sign_coupon'] = $member_action_config['sign_coupon'];
		
		$promote = new PromoteRewardRule();
		$reward_rule_info = $promote->getRewardRuleInfo("sign_point,sign_coupon");
		$integral_info = [];
		$coupon_info = [];
		if ($member_action_config['sign_point'] > 0) {
			//查询 当前店铺签到赠送积分数量
			$integral_info["sign_point"] = $reward_rule_info["sign_point"];
		}
		
		//签到送优惠券
		if ($member_action_config['sign_coupon'] > 0) {
			if ($reward_rule_info['sign_coupon'] != 0) {
				$coupon_type = new NsCouponTypeModel();
				$coupon_info = $coupon_type->getInfo([ 'coupon_type_id' => $reward_rule_info['sign_coupon'] ]);
			}
		}
		$sign_in_info["integral_info"] = $integral_info;
		$sign_in_info["coupon_info"] = $coupon_info;
		return $sign_in_info;
	}
	
	/**
	 * 是否签过到
	 */
	public function isSignIn($uid)
	{
		$member_service = new Member();
		$start_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$end_time = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
		$condition = array(
			"uid" => $uid,
			"type" => 1,
			"create_time" => array(
				"between",
				[ $start_time, $end_time ]
			)
		);
		$is_sign = $member_service->getMemberBehaviorRecordsInfo($condition);
		return $is_sign;
	}
	
	/**
	 * 会员签到 操作  送积分
	 */
	public function memberSign($uid)
	{
		$member_service = new Member();
		$data = array(
			"uid" => $uid,
			"type" => 1,
			"create_time" => time()
		);
		$res = $member_service->addMemberBehaviorRecords($data);
		if ($res <= 0) {
			return 0;
		}
		
		//检测是否开启签到送积分的功能
		$member_action_config = $this->getMemberActionConfig();
		$promote = new PromoteRewardRule();
		$reward_rule_info = $promote->getRewardRuleInfo("sign_point,sign_coupon");
		if ($member_action_config['sign_integral'] > 0) {
            //查询 当前店铺签到赠送积分数量
            $res = 0;
            $res = $promote->addMemberPointData($this->instance_id, $uid, $reward_rule_info['sign_point'], 5, '签到赠送积分');
		}
		
		//签到送优惠券
		if ($member_action_config['sign_coupon'] > 0) {
            if ($reward_rule_info['sign_coupon'] != 0) {
                $res = 0;
                $res = $member_service->memberGetCoupon($uid, $reward_rule_info['sign_coupon'], 2);
			}
		}
		return $res;
	}
}