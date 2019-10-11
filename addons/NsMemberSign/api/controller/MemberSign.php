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

namespace addons\NsMemberSign\api\controller;

use addons\NsMemberSign\data\service\MemberSign as MemberSignService;
use app\api\controller\BaseApi;
use data\service\Member;

/**
 * 会员行为——签到
 */
class MemberSign extends BaseApi
{
	/**
	 * 签到配置
	 * @return string
	 */
	public function getSignInConfig()
	{
		$member_sign = new MemberSignService();
		$res = $member_sign->getSignInConfig();
		return $this->outMessage("签到配置", $res);
	}
	
	/**
	 * 会员是否已签到
	 */
	public function isSignIn()
	{
		$title = '获取会员是否已签到';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		
		$member_sign = new MemberSignService();
		$is_sign_in = $member_sign->isSignIn($this->uid);
		return $this->outMessage($title, $is_sign_in);
	}
	
	/**
	 * 用户签到
	 */
	public function signIn()
	{
		$title = "用户签到";
		$member_sign = new MemberSignService();
		$is_sign_in = $member_sign->isSignIn($this->uid);
		if ($is_sign_in > 0) {
			return $this->outMessage($title, "", '-1', "您今天已经签过到了");
		}
		$retval = $member_sign->memberSign($this->uid);//签到
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 签到记录
	 */
	public function signInRecords()
	{
		$title = "签到记录";
		if (empty($this->uid)) {
			return $this->outMessage($title, '', '-9999', "当前未登录");
		}
		$year = isset($this->params['year']) ? $this->params['year'] : date('Y');
		$month = isset($this->params['month']) ? $this->params['month'] : date('m');
		
		$firstday = date($year . "-" . $month . "-01 00:00:00");
		$lastday = date('Y-m-d 23:59:59', strtotime("$firstday +1 month -1 day"));
		$start_time = strtotime($firstday);//月份第一天时间戳
		$end_time = strtotime($lastday);
		$day_list = getDayStep($end_time, $start_time);
		$member_service = new Member();
		$condition = array(
			"uid" => $this->uid,
			"type" => 1,
			"create_time" => array(
				"between", [ $start_time, $end_time ]
			)
		);
		$list = $member_service->getMemberBehaviorRecordsQuery($condition);
		foreach ($list as $v) {
			$date = date("d", $v["create_time"]);
			$day_list[ $date ] = $day_list[ $date ] + 1;
		}
		return $this->outMessage($title, $day_list);
	}
}