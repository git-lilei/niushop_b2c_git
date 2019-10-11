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

namespace addons\NsMemberShare\api\controller;

use addons\NsMemberShare\data\service\MemberShare as MemberShareService;
use app\api\controller\BaseApi;

/**
 * 会员行为——分享
 */
class MemberShare extends BaseApi
{
	/**
	 * 分享奖励
	 */
	public function shareReward()
	{
		$title = '分享奖励';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$member_share = new MemberShareService();
		$res = $member_share->memberShare($this->uid);
		$this->outMessage($title, $res);
	}
}