<?php
/**
 * User.php
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

namespace data\service;

use addons\Nsfx\data\service\NfxUser;
use data\model\AdminUserModel;
use data\model\NsMemberModel;
use data\model\UserLogModel;
use data\model\UserModel;
use data\model\WebSiteModel;
use data\model\WeixinFansModel;
use think\Cache;
use think\facade\Cookie;
use think\Session as Session;

class User extends BaseService
{
	
	protected $user;
	
	function __construct()
	{
		parent::__construct();
		$this->user = new UserModel();
	}
	
	/*************************************************获取session中用户信息*************************************************/
	/**
	 * 获取当前登录用户的uid
	 */
	public function getSessionUid()
	{
		return $this->uid;
	}
	
	/**
	 * 获取当前登录用户的实例ID
	 */
	public function getSessionInstanceId()
	{
		return $this->instance_id;
	}
	
	/**
	 * 获取当前登录用户是否是总系统管理员
	 */
	public function getSessionUserIsAdmin()
	{
		return $this->is_admin;
	}
	
	/**
	 * 获取当前登录用户是否是前台会员
	 */
	public function getSessionUserIsMember()
	{
		return $this->is_member;
	}
	
	public function getSessionUserIsSystem()
	{
		return $this->is_system;
	}
	
	/**
	 * 获取当前登录用户的权限列
	 */
	public function getSessionModuleIdArray()
	{
		return $this->module_id_array;
	}
	
	/**
	 * 获取用户名
	 */
	public function getSessionUserName()
	{
		return $this->user_name;
	}
	
	/**
	 * 获取用户头像
	 */
	public function getSessionUserHeadImg()
	{
		return $this->user_headimg;
	}
	
	/**
	 * 获取当前用户所在实例名称（单商户为商城主题）
	 */
	public function getInstanceName()
	{
		if (empty($this->instance_name)) {
			$web_site = new WebSite();
			$info = $web_site->getWebSiteInfo();
			return $info['title'];
		} else {
			return $this->instance_name;
		}
		
	}
	/*************************************************获取session中用户信息*************************************************/
	
	/************************************************用户信息操作***********************************************************/
	/**
	 * 用户添加基础方法
	 */
	public function add($user_name, $password, $email, $mobile, $is_system, $qq_openid, $qq_info, $wx_openid, $wx_info, $wx_unionid, $is_member, $instance_id = 0)
	{
		if (!empty($user_name)) {
			
			$count = $this->user->where([
				'user_name' => $user_name
			])->count();
			if ($count > 0) {
				return USER_REPEAT;
			}
			$nick_name = $user_name;
		} elseif (!empty($mobile)) {
			$preg_phone = '/^1[3456789]\d{9}$/ims';
			if (preg_match($preg_phone, $mobile)) {
				return REGISTER_MOBILE_ERROR;
			}
			$count = $this->user->getCount([ 'user_tel' => $mobile ]);
			if ($count > 0) {
				return USER_REPEAT;
			}
			$nick_name = $mobile;
		} elseif (!empty($email)) {
			$preg_email = '/^[a-zA-Z0-9]+([-_.][a-zA-Z0-9]+)*@([a-zA-Z0-9]+[-.])+([a-z]{2,5})$/ims';
			if (!preg_match($preg_email, $email)) {
				return REGISTER_EMAIL_ERROR;
			}
			$count = $this->user->where([
				'user_email' => $email
			])->count();
			if ($count > 0) {
				return USER_REPEAT;
			}
			$nick_name = $email;
		}
		if (!empty($qq_openid)) {
			$qq_info_array = json_decode($qq_info);
			$nick_name = $this->filterStr($qq_info_array->nickname);
			$type = 'qq';
			$qq_info = $this->filterStr($qq_info);
		} elseif (!empty($wx_openid) || !empty($wx_unionid)) {
			$wx_info_array = json_decode($wx_info);
			$nick_name = $this->filterStr($wx_info_array->nickname);
			$type = 'wchat';
			$wx_info = $this->filterStr($wx_info);
		} else {
			$user_head_img = '';
		}
		
		/*
		 * if(empty($user_name))
			 * {
			 * $user_name = $this->createUserName();
			 * }
		 */
		$data = array(
			'user_name' => $user_name,
			/* 'real_password' => $password, */
			'user_password' => md5($password),
			'user_status' => 1,
			'user_headimg' => '',
			'nick_name' => $nick_name,
			'is_system' => (bool) $is_system,
			'is_member' => (bool) $is_member,
			'user_tel' => $mobile,
			'user_tel_bind' => 0,
			'user_qq' => '',
			'qq_openid' => $qq_openid,
			'qq_info' => $qq_info,
			'reg_time' => time(),
			'login_num' => 0,
			'user_email' => $email,
			'user_email_bind' => 0,
			'wx_openid' => $wx_openid,
			'wx_sub_time' => '0',
			'wx_notsub_time' => '0',
			'wx_is_sub' => 0,
			'wx_info' => $wx_info,
			'other_info' => '',
			'instance_id' => $instance_id,
			'wx_unionid' => $wx_unionid
		);
		$this->user->save($data);
		$uid = $this->user->uid;
		//Log::write('会员注册成功' . __URL(__URL__.'wap/login/updateUserImg?uid='.$uid.'&type='.$type));
		//$url = str_replace('api.php', 'index.php', __URL(__URL__ . 'wap/login/updateUserImg?uid=' . $uid . '&type=' . $type));
		//http($url, 1);
		//用户添加成功后
		$data['uid'] = $uid;
		hook("userAddSuccess", $data);
		return $uid;
	}
	
	/**
	 * 修改用户信息
	 */
	public function updateUser($uid, $user_name, $email, $sex, $status, $mobile, $nick_name)
	{
		Cache::clear('sys_user_' . $uid);
		$user_info = $this->user->getInfo([ 'uid' => $uid ], '*');
		//前期判断
		if (!empty($user_name)) {
			if ($user_info['user_name'] != $user_name) {
				$count = $this->user->where([
					'user_name' => $user_name
				])->count();
				if ($count > 0) {
					return USER_REPEAT;
				}
			}
			
		}
		if (!empty($mobile)) {
			if ($user_info['user_tel'] != $mobile) {
				$count = $this->user->where([
					'user_tel' => $mobile
				])->count();
				if ($count > 0) {
					return USER_MOBILE_REPEAT;
				}
			}
		}
		if (!empty($email)) {
			if ($user_info['user_email'] != $email) {
				$count = $this->user->where([
					'user_email' => $email
				])->count();
				if ($count > 0) {
					return USER_EMAIL_REPEAT;
				}
			}
		}
		if (empty($nick_name)) {
			$nick_name = $user_name;
		}
		$data = array(
			'user_tel' => $mobile,
			'user_email' => $email,
			'sex' => $sex,
			'user_status' => $status,
			'nick_name' => $nick_name
		);
		
		if (!empty($user_name)) {
			$data['user_name'] = $user_name;
		}
		
		$retval = $this->user->save($data, [ 'uid' => $uid ]);
		return $retval;
		
	}
	
	/**
	 * 修改密码
	 */
	public function modifyUserPasswordByUid($userid, $password)
	{
		$data = array(
			'user_password' => md5($password)
		);
		$retval = $this->user->save($data, [ 'uid' => $userid ]);
		return $retval;
	}
	
	
	/**
	 * 创建生成用户名
	 */
	protected function createUserName()
	{
		$user_name = "n" . date("ymdh" . rand(1111, 9999));
		return $user_name;
	}
	
	/**
	 * 系统用户修改密码
	 */
	public function modifyUserPassword($uid, $old_password, $new_password)
	{
		$condition = array(
			'uid' => $uid,
			'user_password' => md5($old_password)
		);
		$res = $this->user->getInfo($condition, $field = "uid");
		if (!empty($res['uid'])) {
			$data = array(
				'user_password' => md5($new_password)
			);
			$res = $this->user->save($data, [
				'uid' => $uid
			]);
			return $res;
		} else
			return PASSWORD_ERROR;
	}
	
	/**
	 * 修改用户名
	 */
	public function modifyUserName($uid, $user_name)
	{
		$info = $this->user->get($uid);
		if ($info['user_name'] == $user_name) {
			return 1;
		}
		$count = $this->user->where([
			'user_name' => $user_name
		])->count();
		if ($count > 0) {
			return USER_REPEAT;
		}
		$data = array(
			'user_name' => $user_name
		);
		$res = $this->user->save($data, [
			'uid' => $uid
		]);
		return $res;
	}
	
	/**
	 * 会员锁定
	 */
	public function userLock($uid)
	{
		$retval = $this->user->save([
			'user_status' => 0
		], [
			'uid' => $uid
		]);
		return $retval;
	}
	
	/**
	 * 会员解锁
	 */
	public function userUnlock($uid)
	{
		$retval = $this->user->save([
			'user_status' => 1
		], [
			'uid' => $uid
		]);
		return $retval;
	}
	
	/**
	 * 通过账号密码 来更新会员的微信信息
	 */
	public function updateUserWchat($user_name, $password, $wx_openid, $wx_info, $wx_unionid)
	{
		$condition = array(
			'user_name' => $user_name,
			'user_password' => md5($password)
		);
		$user_info = $this->user->getInfo($condition, $field = 'uid,user_status,user_name,is_system,instance_id, is_member, current_login_ip, current_login_time, current_login_type, wx_openid, wx_unionid');
		if (empty($user_info)) {
			if (empty($password)) {
				$condition = array(
					'user_tel' => $user_name
				);
			} else {
				$condition = array(
					'user_tel' => $user_name,
					'user_password' => md5($password)
				);
			}
			
			$user_info = $this->user->getInfo($condition, $field = 'uid,user_status,user_name,is_system,instance_id, is_member, current_login_ip, current_login_time, current_login_type, wx_openid, wx_unionid');
		}
		if (empty($user_info)) {
			$condition = array(
				'user_email' => $user_name,
				'user_password' => md5($password)
			);
			$user_info = $this->user->getInfo($condition, $field = 'uid,user_status,user_name,is_system,instance_id, is_member, current_login_ip, current_login_time, current_login_type, wx_openid, wx_unionid');
		}
		if (!empty($user_info) && $user_info["wx_openid"] == "" && $user_info["wx_unionid"] == "") {
			if (!empty($wx_openid) || !empty($wx_unionid)) {
				$wx_info_array = json_decode($wx_info);
				$nick_name = $this->filterStr($wx_info_array->nickname);
			}
			$data = array(
				'user_headimg' => '',
				'nick_name' => $nick_name,
				'wx_openid' => $wx_openid,
				'wx_info' => $wx_info,
				'wx_unionid' => $wx_unionid
			);
			$user_model = new UserModel();
			$user_model->save($data, [ "uid" => $user_info['uid'] ]);
			//绑定
			if (!empty($wx_openid) || !empty($wx_unionid)) {
				// 添加关注
				switch (NS_VERSION) {
					case NS_VER_B2C:
						break;
					case NS_VER_B2C_FX:
						// 判断当前版本
						$nfx_user = new NfxUser();
						$nfx_user->userAssociateShop($user_info["uid"], 0, 0);
						break;
				}
			}
		}
		Cache::clear('sys_user_' . $user_info['uid']);
	}
	
	/**
	 * 修改用户手机
	 */
	public function modifyMobile($uid, $mobile)
	{
		$retval = $this->user->save([
			'user_tel' => $mobile
		], [
			'uid' => $uid
		]);
		return $retval;
	}
	
	/**
	 * 修改用户昵称
	 */
	public function modifyNickName($uid, $nickname)
	{
		$retval = $this->user->save([
			'nick_name' => $nickname
		], [
			'uid' => $uid
		]);
		return $retval;
	}
	
	/**
	 * 修改用户邮箱
	 */
	public function modifyEmail($uid, $email)
	{
		$retval = $this->user->save([
			'user_email' => $email
		], [
			'uid' => $uid
		]);
		return $retval;
	}
	
	/**
	 * 修改用户qq
	 */
	public function modifyQQ($uid, $qq)
	{
		$retval = $this->user->save([
			'user_qq' => $qq
		], [
			'uid' => $uid
		]);
		return $retval;
	}
	
	/**
	 * 重置用户密码
	 */
	public function resetUserPassword($uid)
	{
		$this->user->save([
			'user_password' => md5(123456)
		], [
			'uid' => $uid
		]);
		return 1;
	}
	
	/**
	 * 修改用户头像
	 */
	public function modifyUserHeadimg($uid, $user_headimg)
	{
		$info = $this->user->get($uid);
		if ($info['user_headimg'] == $user_headimg) {
			return 1;
		}
		$data = array(
			'user_headimg' => $user_headimg
		);
		$res = $this->user->save($data, [
			'uid' => $uid
		]);
		return $res;
	}
	
	/**
	 * 绑定用户手机
	 */
	public function userTelBind($uid)
	{
		return $this->user->save([
			'user_tel_bind' => 1
		], [
			'uid' => $uid
		]);
	}
	
	/**
	 * 取消手机绑定
	 */
	public function removeUserTelBind($uid)
	{
		return $this->user->save([
			'user_tel_bind' => 0
		], [
			'uid' => $uid
		]);
	}
	
	/**
	 * 用户邮箱绑定
	 */
	public function userEmailBind($uid)
	{
		return $this->user->save([
			'user_email_bind' => 1
		], [
			'uid' => $uid
		]);
	}
	
	/**
	 * 取消邮箱绑定
	 */
	public function removeUserEmailBind($uid)
	{
		return $this->user->save([
			'user_email_bind' => 0
		], [
			'uid' => $uid
		]);
	}
	
	/**
	 * 绑定qq
	 */
	public function bindQQ($qq_openid, $qq_info)
	{
		$model = $this->getRequestModel();
		$this->uid = Session::get($model . 'uid');
		$data = array(
			'qq_openid' => $qq_openid,
			'qq_info' => $qq_info
		);
		$res = $this->user->save($data, [
			'uid' => $this->uid
		]);
		return $res;
	}
	
	/**
	 * 取消绑定qq
	 */
	public function removeBindQQ()
	{
		$model = $this->getRequestModel();
		$this->uid = Session::get($model . 'uid');
		$data = array(
			'qq_openid' => '',
			'qq_info' => ''
		);
		$res = $this->user->save($data, [
			'uid' => $this->uid
		]);
		return $res;
	}
	
	
	/**
	 * 微信绑定用户
	 */
	public function wchatBindMember($uid, $bind_message_info)
	{
		$bind_message_info = json_decode(Session::get("bind_message_info"), true);
		session::set("member_bind_first", null);
		if (!empty($bind_message_info)) {
			$config = new Config();
			$register_config = $config->getRegisterAndVisitInfo(0);
			if (!empty($register_config) && $register_config["is_requiretel"] == 1 && $bind_message_info["is_bind"] == 1 && !empty($bind_message_info["token"])) {
				$token = $bind_message_info["token"];
				if (!empty($token['openid']) || ! empty($bind_message_info['wx_unionid'])) {
					$this->updateUserWchatByUid($uid, $token['openid'], $bind_message_info['info'], $bind_message_info['wx_unionid']);
				}
			}
		}
	}
	
	/**
	 * 通过账号密码 来更新会员的微信信息
	 */
	public function updateUserWchatByUid($uid, $wx_openid, $wx_info, $wx_unionid)
	{
		$user_model = new UserModel();
		$condition = array(
			'uid' => $uid
		);
		$user_info = $user_model->getInfo($condition, $field = 'uid,user_status,user_name,is_system,instance_id, is_member, current_login_ip, current_login_time, current_login_type, wx_openid, wx_unionid, user_headimg');
		if (!empty($user_info) && $user_info["wx_openid"] == "" && $user_info["wx_unionid"] == "") {
			if (!empty($wx_openid) || !empty($wx_unionid)) {
				$wx_info_array = json_decode($wx_info);
				$nick_name = $this->filterStr($wx_info_array->nickname);
				$wx_info = $this->filterStr($wx_info);
			}
			
			$data = array(
				'nick_name' => $nick_name,
				'wx_info' => $wx_info,
				'wx_unionid' => $wx_unionid
			);
			if (! empty($wx_openid)) {
			    $data['wx_openid'] = $wx_openid;
            }
			$user_model = new UserModel();
			$user_model->save($data, [ "uid" => $user_info['uid'] ]);
			$url = str_replace('api.php', 'index.php', __URL(__URL__ . 'wap/login/updateUserImg?uid=' . $uid . '&type=wchat'));
			$type = empty($user_info['user_headimg']) ? 'wchat' : 1;
			http($url, $type);
			//绑定
			if (!empty($wx_openid) || !empty($wx_unionid)) {
				// 添加关注
				switch (NS_VERSION) {
					case NS_VER_B2C:
						break;
					case NS_VER_B2C_FX:
						if (!empty($_SESSION['source_uid'])) {
							// 判断当前版本
							$nfx_user = new NfxUser();
							$nfx_user->userAssociateShop($user_info["uid"], 0, $_SESSION['source_uid']);
						} else {
							// 判断当前版本
							$nfx_user = new NfxUser();
							$nfx_user->userAssociateShop($user_info["uid"], 0, 0);
						}
				}
			}
		}
	}
	
	/**
	 * 更新会员头像
	 */
	public function updateUserImg($uid, $type)
	{
		$condition = array(
			'uid' => $uid
		);
		
		$user_info = $this->user->getInfo($condition, $field = 'uid,qq_info,wx_info');
		$qq_info = $user_info['qq_info'];
		$wx_info = $user_info['wx_info'];
		
		if ($type == 'qq') {
			$qq_info_array = json_decode($qq_info, true);
			$user_head_img = $qq_info_array['figureurl_qq_2'];
		} elseif ($type == 'wchat') {
			$wx_info_array = json_decode($wx_info, true);
			$user_head_img = $wx_info_array['headimgurl'];
			
		} else {
			$user_head_img = '';
		}
		
		$local_path = '';
		if (!empty($user_head_img)) {
			if (!file_exists('upload/user')) {
				$mode = intval('0777', 8);
				mkdir('upload/user', $mode, true);
				if (!file_exists('upload/user')) {
					die('upload/user不可写，请检验读写权限!');
				}
			}
			$local_path = 'upload/user/' . time() . rand(111, 999) . '.png';
			save_weixin_img($local_path, $user_head_img);
		}
		if (!empty($local_path)) {
			$retval = $this->user->save([ 'user_headimg' => $local_path, ], [ 'uid' => $uid ]);
		} else {
			$retval = 0;
		}
		return $retval;
	}
	
	/**
	 * 绑定微信号
	 */
	public function bindWchat($unionid, $wx_info)
	{
		$data = array(
			'wx_unionid' => $unionid,
			'wx_info' => $wx_info
		);
		$res = $this->user->save($data, [
			'uid' => $this->uid
		]);
		return $res;
	}
	/************************************************用户信息操作结束********************************************************/
	
	/************************************************用户信息查询***********************************************************/
	/**
	 * 获取用户信息
	 */
	public function getUserInfo()
	{
		$res = $this->user->getInfo('uid=' . $this->uid, '*');
		return $res;
	}
	
	/**
	 * 通过uid获取用户信息
	 */
	public function getUserInfoByUid($uid)
	{
		$res = $this->user->getInfo([ 'uid' => $uid ], "*");
		return $res;
	}
	
	/**
	 * 根据用户名获取用户信息
	 */
	public function getUserInfoByUsername($username)
	{
		$res = $this->user->getInfo([ 'user_name' => $username ], '*');
		return $res;
	}
	
	/**
	 * 根据条件查询
	 */
	public function getUserInfoByCondition($condition, $field = "*")
	{
		$res = $this->user->getInfo($condition, $field);
		return $res;
	}
	
	/**
	 * 获取会员信息通过Openid
	 */
	public function getUidByOpenid($openid)
	{
		$user_info = $this->user->getInfo([ 'wx_openid' => $openid ], 'uid');
		return $user_info;
	}
	
	/**
	 * 获取用户详情
	 */
	public function getUserDetail($uid = '')
	{
		//获取会员ID
		if (empty($uid)) {
			$uid = $this->uid;
		}
		$user_info = $this->user->get($uid);
		if (!empty($user_info['qq_openid'])) {
			$qq_info = json_decode($user_info['qq_info'], true);
			$user_info['qq_info_array'] = $qq_info;
		}
		if (!empty($user_info['wx_openid'])) {
			$wx_info = json_decode($user_info['wx_info'], true);
			$user_info['wx_info_array'] = $wx_info;
		}
		return $user_info;
	}
	
	/**
	 * 检测用户qq是否绑定
	 */
	public function checkUserQQopenid($qq_openid)
	{
		$user = new UserModel();
		return $user->where([
			'qq_openid' => $qq_openid
		])->count();
	}
	
	/**
	 * 检测用户微信是否绑定
	 */
	public function checkUserWchatopenid($openid)
	{
		$user = new UserModel();
		return $user->where([
			'wx_openid' => $openid
		])->count();
	}
	
	/**
	 * 获取会员信息
	 */
	public function getUserInfoDetail($uid)
	{
		$user_info = $this->user->getInfo(array( "uid" => $uid ));
		$member = new NsMemberModel();
		$user_info['member'] = $member->getInfo(array( "uid" => $uid ));
		return $user_info;
	}
	
	/**
	 * 检测会员是否关注
	 */
	public function checkUserIsSubscribe($uid)
	{
		$user_info = $this->user->getInfo([ 'uid' => $uid ], 'openid');
		if (!empty($user_info['openid'])) {
			$weixin_fans = new WeixinFansModel();
			$count = $weixin_fans->where([ 'openid' => $user_info['openid'], 'is_subscribe' => 1 ])->count();
			if ($count > 0) {
				return 1;
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}
	
	/**
	 * 检测会员是否关注店铺
	 */
	public function checkUserIsSubscribeInstance($uid)
	{
		$user_info = $this->user->getInfo([ 'uid' => $uid ], 'wx_openid');
		if (!empty($user_info['wx_openid'])) {
			$weixin_fans = new WeixinFansModel();
			$count = $weixin_fans->where([ 'openid' => $user_info['wx_openid'], 'is_subscribe' => 1 ])->count();
			if ($count > 0) {
				return 1;
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}
	
	/**
	 * 检测手机是否绑定
	 */
	public function memberIsMobile($mobile)
	{
		$mobile_info = $this->user->get([
			'user_tel' => $mobile
		]);
		return !empty($mobile_info);
	}
	
	/**
	 * 检测邮箱是否绑定
	 */
	public function memberIsEmail($email)
	{
		$email_info = $this->user->get([
			'user_email' => $email
		]);
		return !empty($email_info);
	}
	
	/**
	 * 检测手机是否注册
	 */
	public function checkMobileIsHas($mobile)
	{
		$user = new UserModel();
		$count = $user->getCount([ 'user_tel' => $mobile ]);
		return $count;
	}
	
	/**
	 * 根据用户邮箱更改密码
	 */
	public function updateUserPasswordByEmail($userInfo, $password)
	{
		$user_info = $this->user->getInfo([ 'user_email' => $userInfo ], 'uid');
		if (!empty($user_info['uid'])) Cache::clear('sys_user_' . $user_info['uid']);
		
		$data = array(
			'user_password' => md5($password)
		);
		$retval = $this->user->save($data, [ 'user_email' => $userInfo ]);
		return $retval;
	}
	
	/**
	 * 根据用户手机更改密码
	 */
	public function updateUserPasswordByMobile($userInfo, $password)
	{
		$data = array(
			'user_password' => md5($password)
		);
		$retval = $this->user->save($data, [ 'user_tel' => $userInfo ]);
		return $retval;
	}
	
	/**
	 * 获取会员信息
	 */
	public function getDetail($condition = [], $field = "*")
	{
		$res = [];
		if (!empty($condition)) {
			$user_model = new UserModel();
			$res = $user_model->getInfo($condition, $field);
		}
		return $res;
	}
	/************************************************用户信息查询结束********************************************************/
	
	/************************************************用户登录**************************************************************/
	
	/**
	 * 用户登录之后初始化数据
	 */
	private function initLoginInfo($user_info)
	{
		$model = $this->getRequestModel();
		Session::set($model . 'uid', $user_info['uid']);
		Session::set($model . 'is_system', $user_info['is_system']);
		Session::set($model . 'is_member', $user_info['is_member']);
		Session::set($model . 'instance_id', $user_info['instance_id']);
		Session::set($model . 'user_name', $user_info['user_name']);
		Session::set($model . 'user_headimg', $user_info['user_headimg']);
        
        Cookie::set($model . 'uid', $user_info['uid']);
        Cookie::set($model . 'is_system', $user_info['is_system']);
        Cookie::set($model . 'is_member', $user_info['is_member']);
        Cookie::set($model . 'instance_id', $user_info['instance_id']);
        Cookie::set($model . 'user_name', $user_info['user_name']);
        Cookie::set($model . 'user_headimg', $user_info['user_headimg']);
		//单店版本
		$website = new WebSiteModel();
		$instance_name = $website->getInfo('', 'title');
		Session::set($model . 'instance_name', $instance_name['title']);
        Cookie::set($model . 'instance_name', $instance_name['title']);
        
		if ($user_info['is_system']) {
			$admin_info = new AdminUserModel();
			$admin_info = $admin_info->getInfo('uid=' . $user_info['uid'], 'is_admin,group_id_array');
			Session::set($model . 'is_admin', $admin_info['is_admin']);
			Session::set($model . 'group_id', $admin_info['group_id_array']);
            
            Cookie::set($model . 'is_admin', $admin_info['is_admin']);
            Cookie::set($model . 'group_id', $admin_info['group_id_array']);
		} else {
			Session::set($model . 'is_admin', '');
			Session::set($model . 'group_id', '');
            
            Cookie::set($model . 'is_admin', '');
            Cookie::set($model . 'group_id', '');
		}
		if ($user_info['current_login_time'] == 0) {
			$user_info['current_login_time'] = time();
		}
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
		
		//同步微信信息
		$bind_message_info = json_decode(Session::get("bind_message_info"), true);
		$this->wchatBindMember($user_info['uid'], $bind_message_info);
		//用户登录成功钩子
		hook("userLoginSuccess", $user_info);
		return $retval;
	}
	
	/**
	 * 系统用户登录
	 */
	public function login($user_name, $password = '')
	{
		$this->Logout();
		$condition = array(
			'user_name' => $user_name,
			'user_password' => md5($password)
		);
		$user_info = $this->user->getInfo($condition, $field = 'uid,user_status,user_name,is_system,instance_id, is_member, current_login_ip, current_login_time, current_login_type, user_headimg');
		
		if (empty($user_info)) {
			if (empty($password)) {
				$condition = array(
					'user_tel' => $user_name
				);
			} else {
				$condition = array(
					'user_tel' => $user_name,
					'user_password' => md5($password)
				);
			}
			
			$user_info = $this->user->getInfo($condition, $field = 'uid,user_status,user_name,is_system,instance_id, is_member, current_login_ip, current_login_time, current_login_type, user_headimg');
		}
		if (empty($user_info)) {
			$condition = array(
				'user_email' => $user_name,
				'user_password' => md5($password)
			);
			$user_info = $this->user->getInfo($condition, $field = 'uid,user_status,user_name,is_system,instance_id, is_member, current_login_ip, current_login_time, current_login_type, user_headimg');
		}
		if (!empty($user_info)) {
			if ($user_info['user_status'] == 0) {
				return USER_LOCK;
			} else {
			    $this->bindWechatInfo($user_info['uid']);
				//登录成功后增加用户的登录次数
				$this->user->where("user_name|user_tel|user_email", "eq", $user_name)
					->setInc('login_num', 1);
				$this->initLoginInfo($user_info);
				return $user_info['uid'];
			}
		} else
			return USER_ERROR;
	}
	
	/**
	 * 绑定微信信息 （仅用户微信浏览器端非自动注册情况下）
	 * @param unknown $uid
	 */
	public function bindWechatInfo($uid){
	    // 如果是微信浏览器
	    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
	        $domain_name = \think\Request::instance()->domain();
	        $token_json = Session::get($domain_name . "member_access_token");
	        if(!empty($token_json)){
	            $token = json_decode($token_json, true);
	            if (!empty($token['openid'])) {
	                $count = $this->user->getCount(['wx_openid' => $token['openid']]);
	                if(!$count){
    	                $this->user->save([
    	                    'wx_openid' => $token['openid'],
    	                    'wx_info' => $token['info'],
    	                    'wx_unionid' => $token['unionid']
    	                ], ['uid' => $uid]);
    	                Session::set($domain_name . "member_access_token", null);
    	                $url = str_replace('api.php', 'index.php', __URL(__URL__ . 'wap/login/updateUserImg?uid=' . $uid . '&type=wchat'));
    	                http($url, 1);
	                }
	            }
	        }
	    }
	}
	
	/*
	 * qq登录
	 */
	public function qqLogin($qq)
	{
		$this->Logout();
		$condition = array(
			'qq_openid' => $qq
		);
		$user_info = $this->user->getInfo($condition, $field = 'uid,user_status,user_name,is_system,instance_id,is_member, current_login_ip, current_login_time, current_login_type, user_headimg');
		if (!empty($user_info)) {
			if ($user_info['user_status'] == 0) {
				return USER_LOCK;
			} else {
				$this->initQQLogin($user_info['uid']);
				return 1;
			} 
		} else
			return USER_NBUND;
	}
	
	/**
	 * 初始化微信登录信息
	 */
	private function initQQLogin($uid)
	{
	    $member = new Member();
	    $user_info = $member->getMemberDetail($this->instance_id, $uid);
	    $token = array(
	        'uid' => $uid,
	        'request_time' => time()
	    );
	    $encode = $this->niuEncrypt(json_encode($token));
	    Session::set('niu_member_detail', $user_info);
	    Session::set('niu_access_token', $encode);
	
	    $model = $this->getRequestModel();
	    Session::set($model . 'uid', $user_info['user_info']['uid']);
	    Session::set($model . 'is_system', $user_info['user_info']['is_system']);
	    Session::set($model . 'is_member', $user_info['user_info']['is_member']);
	    Session::set($model . 'instance_id', $user_info['user_info']['instance_id']);
	    Session::set($model . 'user_name', $user_info['user_info']['user_name']);
	    Session::set($model . 'user_headimg', $user_info['user_info']['user_headimg']);
	}
	
	/*
	 * 微信第三方登录
	 */
	public function wchatLogin($openid)
	{
		$this->Logout();
		$condition = array(
			'wx_openid' => $openid
		);
		$user_info = $this->user->getInfo($condition, $field = '*');
		if (!empty($user_info)) {
			if ($user_info['user_status'] == 0) {
				return USER_LOCK;
			} else {
				$this->initWchatLogin($user_info['uid']);
				return 1;
			}
		} else
			return USER_NBUND;
	}
	
	/**
	 * 系统加密方法
	 *
	 * @param string $data 要加密的字符串
	 * @param string $key 加密密钥
	 * @param int $expire 过期时间 单位 秒
	 * @return string
	 */
	public function niuEncrypt($data, $key = '', $expire = 0)
	{
		$key = md5(empty($key) ? 'addexdfsdfewfscvsrdf!@#' : $key);
		$data = base64_encode($data);
		$x = 0;
		$len = strlen($data);
		$l = strlen($key);
		$char = '';
		
		for ($i = 0; $i < $len; $i++) {
			if ($x == $l)
				$x = 0;
			$char .= substr($key, $x, 1);
			$x++;
		}
		
		$str = sprintf('%010d', $expire ? $expire + time() : 0);
		
		for ($i = 0; $i < $len; $i++) {
			$str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
		}
		return str_replace(array(
			'+',
			'/',
			'='
		), array(
			'-',
			'_',
			''
		), base64_encode($str));
	}
	
	/**
	 * 初始化微信登录信息
	 */
	private function initWchatLogin($uid)
	{
		$member = new Member();
		$user_info = $member->getMemberDetail($this->instance_id, $uid);
		$token = array(
			'uid' => $uid,
			'request_time' => time()
		);
		$encode = $this->niuEncrypt(json_encode($token));
		Session::set('niu_member_detail', $user_info);
		Session::set('niu_access_token', $encode);
		
		$model = $this->getRequestModel();
		Session::set($model . 'uid', $user_info['user_info']['uid']);
		Session::set($model . 'is_system', $user_info['user_info']['is_system']);
		Session::set($model . 'is_member', $user_info['user_info']['is_member']);
		Session::set($model . 'instance_id', $user_info['user_info']['instance_id']);
		Session::set($model . 'user_name', $user_info['user_info']['user_name']);
		Session::set($model . 'user_headimg', $user_info['user_info']['user_headimg']);
		
	}
	
	/**
	 * 微信unionid登录
	 */
	public function wchatUnionLogin($unionid)
	{
		$this->Logout();
		$condition = array(
			'wx_unionid' => $unionid
		);
		$user_info = $this->user->getInfo($condition, $field = 'uid,user_status,user_name,is_system,instance_id,is_member, current_login_ip, current_login_time, current_login_type, user_headimg');
		if (!empty($user_info)) {
			if ($user_info['user_status'] == 0) {
				return USER_LOCK;
			} else {
				$this->initWchatLogin($user_info['uid']);
				return 1;
			}
		} else
			return USER_NBUND;
	}
	
	/**
	 * 当前只针对存在unionid不存在openid
	 */
	public function refreshUserOpenid($wx_openid, $wx_unionid)
	{
		$user_info = $this->user->getInfo([ 'wx_unionid' => $wx_unionid ], 'wx_openid,wx_unionid,uid');
		if (!empty($user_info)) {
			if (empty($user_info['wx_openid'])) {
				$data = array(
					'wx_openid' => $wx_openid
				);
				$retval = $this->user->save($data, [ 'wx_unionid' => $wx_unionid ]);
			} else {
				$retval = 1;
			}
			
		} else {
			$retval = 1;
		}
	}
	
	/**
	 * 过滤特殊字符
	 */
	private function filterStr($str)
	{
		if ($str) {
			$name = $str;
			$name = preg_replace_callback('/\xEE[\x80-\xBF][\x80-\xBF]|\xEF[\x81-\x83][\x80-\xBF]/', function ($matches) { return ''; }, $name);
			$name = preg_replace_callback('/xE0[x80-x9F][x80-xBF]‘.‘|xED[xA0-xBF][x80-xBF]/S', function ($matches) { return ''; }, $name);
			// 汉字不编码
			$name = json_encode($name);
			$name = preg_replace_callback("/\\\ud[0-9a-f]{3}/i", function ($matches) { return ''; }, $name);
			if (!empty($name)) {
				$name = json_decode($name);
				return $name;
			} else {
				return '';
			}
			
		} else {
			return '';
		}
	}
	
	/**
	 * 用户退出
	 */
	public function Logout()
	{
		$model = $this->getRequestModel();
		Session::set($model . 'uid', '');
		Session::set($model . 'is_admin', 0);
		Session::set($model . 'group_id', '');
		Session::set($model . 'instance_name', '');
		Session::set($model . 'is_member', '');
		Session::set($model . 'is_system', '');
		Session::set($model . 'user_headimg', '');
		Session::set('module_list', []);
        
        Cookie::set($model . 'uid', '');
        Cookie::set($model . 'is_admin', 0);
        Cookie::set($model . 'group_id', '');
        Cookie::set($model . 'instance_name', '');
        Cookie::set($model . 'is_member', '');
        Cookie::set($model . 'is_system', '');
        Cookie::set($model . 'user_headimg', '');
        Cookie::set('module_list', []);
		if ($model == "app") {
            Session::set('niu_access_token', '');
            Session::set('niu_member_detail', '');
            
            Cookie::set('niu_access_token', '');
            Cookie::set('niu_member_detail', '');
		}
	}
	/**********************************************************用户登录结束************************************************/
	
	/**
	 *  获取一定条件下用户数量
	 */
	public function getUserCount($condition)
	{
		$user = new UserModel();
		$user_list = $user->getQuery($condition, "count(*) as count");
		return $user_list[0]["count"];
	}
	
	/**
	 * 获取用户操作日志列表
	 */
	public function getUserOperationLogList($page_index, $page_size, $condition, $order = "", $field = "*")
	{
		$user_log = new UserLogModel();
		$list = $user_log->pageQuery($page_index, $page_size, $condition, $order, $field);
		return $list;
	}
}