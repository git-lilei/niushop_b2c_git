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

namespace addons\NsMemberEvaluate\data\service;

use data\model\NsGoodsCommentModel;
use data\service\BaseService;
use data\service\Config;
use data\service\Member;
use data\service\promotion\PromoteRewardRule;


/**
 * 会员行为——评价
 */
class MemberEvaluate extends BaseService
{
	
	/**
	 * 获取会员行为设置
	 */
	public function getMemberActionConfig()
	{
		$config = new Config();
		$comment_coupon = $config->getConfig($this->instance_id, 'COMMENT_COUPON');
		if (empty($comment_coupon)) {
			$params = [
				'comment_coupon' => ''
			];
			$this->setMemberActionConfig($params);
			$array = array(
				'comment_coupon' => ''
			);
		} else {
			$array = array(
				'comment_coupon' => $comment_coupon['value']
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
			'key' => 'COMMENT_COUPON',
			'value' => $params['comment_coupon'],
			'desc' => '评论送优惠券',
			'is_use' => 1
		);
		
		$config = new Config();
		$res = $config->setConfig($array);
		return $res;
	}
	
	public function setRewardRule($comment_point, $comment_coupon)
	{
		$promote = new PromoteRewardRule();
		$data = array(
			'comment_point' => $comment_point,
			'comment_coupon' => $comment_coupon
		);
		$promote->setRewardRule($data);
	}
	
	/**
	 * 评论送积分
	 */
	public function commentGivePoint($order_id)
	{
		// 给记录表添加记录
		$goods_comment = new NsGoodsCommentModel();
		$rewardRule = new PromoteRewardRule();
		// 查询评论赠送积分数量，然后叠加
		$uid = $this->uid;
		$info = $rewardRule->getRewardRuleInfo("comment_point");
		$data = array(
			'shop_id' => 0,
			'uid' => $uid,
			'order_id' => $order_id,
			'status' => 1,
			'number' => $info['comment_point'],
			'create_time' => time()
		);
		$retval = $goods_comment->save($data);
		if ($retval > 0) {
			// 给总记录表加记录
			$rewardRule->addMemberPointData(0, $uid, $info['comment_point'], 20, '评论赠送积分');
		}
	}
	
	/**
	 * 评论送优惠券
	 * @param $uid
	 * @return int
	 */
	public function commentGiveCoupon($uid)
	{
		$member_action_config = $this->getMemberActionConfig();
		$res = 0;
		if ($member_action_config['comment_coupon'] == 1) {
			$promote = new PromoteRewardRule();
			$reward_rule_info = $promote->getRewardRuleInfo("comment_coupon");
			if ($reward_rule_info['comment_coupon'] != 0) {
				$member = new Member();
				$res = $member->memberGetCoupon($uid, $reward_rule_info['comment_coupon'], 2);
			}
		}
		return $res;
	}
}