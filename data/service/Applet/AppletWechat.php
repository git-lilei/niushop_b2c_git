<?php
/**
 * AppletWechat.php
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
 * @date : 2015.4.24
 * @version : v1.0.0.0
 */

namespace data\service\Applet;

use data\service\Goods;
use data\service\User;

class AppletWechat extends User
{
	
	/**
	 * 微信unionid登录
	 */
	public function wchatAppUnionLogin($unionid)
	{
		$condition = array(
			'wx_unionid' => $unionid
		);
		$user_info = $this->user->getInfo($condition, $field = 'uid,user_status,user_name,is_system,instance_id,is_member, current_login_ip, current_login_time, current_login_type');
		if (!empty($user_info)) {
			if ($user_info['user_status'] == 0) {
				return USER_LOCK;
			} else {
				$this->initLoginInfo($user_info);
				return 10;
			}
		} else
			return USER_NBUND;
	}
	
	/*
	 * 微信第三方登录
	 */
	public function wchatAppLogin($openid, $wx_unionid)
	{
		$condition = array(
			'wx_openid' => $openid
		);
		$user_info = $this->user->getInfo($condition, $field = 'uid,user_status,user_name,is_system,instance_id,is_member, current_login_ip, current_login_time, current_login_type');
		if (!empty($user_info)) {
			if ($user_info['user_status'] == 0) {
				return USER_LOCK;
			} else {
				$this->initLoginInfo($user_info);
				return 1;
			}
		} else {
			if (!empty($wx_unionid)) {
				$res = $this->wchatAppUnionLogin($wx_unionid);
				return $res;
			} else {
				return USER_NBUND;
			}
		}
	}
	
	/**
	 * 用户登录之后初始化数据
	 * @param unknown $user_info
	 */
	private function initLoginInfo($user_info)
	{
		$model = $this->getRequestModel();
		//单店版本
//        $website = new WebSiteModel();
//        $instance_name = $website->getInfo('', 'title');
		$data = array(
			'last_login_time' => $user_info['current_login_time'],
			'last_login_ip' => $user_info['current_login_ip'],
			'last_login_type' => $user_info['current_login_type'],
			'current_login_ip' => request()->ip(),
			'current_login_time' => time(),
			'current_login_type' => 1
		);
		if ($model != 'app') {
			$this->addUserLog($user_info['uid'], 1, '用户', '用户登录', '');
		}
		
		//添加日志
		//离线购物车同步
		$goods = new Goods();
		$goods->syncUserCart($user_info['uid']);
		$retval = $this->user->save($data, [ 'uid' => $user_info['uid'] ]);
		//用户登录成功钩子
		hook("userLoginSuccess", $user_info);
		return $retval;
	}
	
	/**
	 * 获取会员信息通过UniondId
	 */
	public function getUserDetailByUnionid($wx_unionid)
	{
		$user_info = $this->user->getInfo([ 'wx_unionid' => $wx_unionid ], 'uid,instance_id');
		return $user_info;
	}
	
	public function getUserDetailByOpenid($openid)
	{
		$user_info = $this->user->getInfo([ 'wx_openid' => $openid ], 'uid,instance_id');
		return $user_info;
	}
}