<?php
/**
 * Login.php
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

namespace app\web\controller;

use data\extend\ThinkOauth;

/**
 * 登录控制器
 */
class Login extends BaseWeb
{
	public function __construct()
	{
		parent::__construct();
		$default_client = request()->cookie("default_client", "");
		if ($default_client == "web") {
		} elseif (request()->isMobile()) {
			$redirect = __URL(__URL__ . "/wap");
			$this->redirect($redirect);
			exit();
		}
		
		// 当切换到PC端时，隐藏右下角返回手机端按钮
//		if (!request()->isMobile() && $default_client == "web") {
//			$default_client = "";
//		}
	}
	
	public function index()
	{
		if (request()->isAjax()) {
			$token = request()->post('token', "");
			if (!empty($token)) {
				session("niu_access_token", $token);
				$member_detail = api("System.Member.memberInfo");
				if ($member_detail['code'] == 0) {
					session("niu_member_detail", $member_detail['data']);
					$res['code'] = 1;
					$_SESSION['login_pre_url'] = "";
					return 1;
				} else {
					return 0;
				}
			}
		} else {
			
			// 登录成功会返回到上一页
			$login_pre_url = $_SESSION['login_pre_url'];
			if (empty($login_pre_url)) {
				if (!empty($_SERVER['HTTP_REFERER'])) {
					$pre_url = $_SERVER['HTTP_REFERER'];
					if (strpos($pre_url, 'login')) {
						$pre_url = '';
					}
					$_SESSION['login_pre_url'] = $pre_url;
				}
			}
			$this->assign("login_pre_url", $_SESSION['login_pre_url']);
			$this->assign("title_before", "用户登录");
			return $this->view($this->style . 'login/login');
		}
	}
	
	/**
	 * 注册
	 */
	public function register()
	{
		$reg_config = api("System.Login.getRegisterAndVisitInfo");
		$reg_config = $reg_config['data'];
		if (trim($reg_config['register_info']) == "" || $reg_config['is_register'] == 0) {
			$this->error("抱歉,商城暂未开放注册!");
		} else {
			$this->assign("reg_config", $reg_config);
			$this->assign("title_before", "注册");
			return $this->view($this->style . 'login/register');
		}
	}
	
	/**
	 * 注册扩展信息/绑定
	 */
	public function registerExt()
	{
		$this->assign("title_before", "完善信息");
		return $this->view($this->style . "login/register_ext");
	}
	
	/*
	 * 找回密码
	 */
	public function find()
	{
		$this->assign("title_before", "找回密码");
		return $this->view($this->style . "login/find");
	}
	
	/**
	 * 第三方登录登录
	 */
	public function oauthLogin()
	{
		$type = request()->get('type', '');
		$test = ThinkOauth::getInstance($type);
		$this->redirect($test->getRequestCodeURL());
	}
	
	/**
	 * qq登录返回
	 */
	public function callback()
	{
		$code = request()->get('code', '');
		if (empty($code))
			die();
		$qq = ThinkOauth::getInstance('QQLOGIN');
		$token = $qq->getAccessToken($code);
		$user = new Member();
		if (!empty($token['openid'])) {
			if (!empty($_SESSION['bind_pre_url'])) {
				// 1.检测当前qqopenid是否已经绑定，如果已经绑定直接返回绑定失败
				$bind_pre_url = $_SESSION['bind_pre_url'];
				$_SESSION['bind_pre_url'] = '';
				$is_bund = $user->checkUserQQopenid($token['openid']);
				if ($is_bund == 0) {
					// 2.绑定操作
					$qq = ThinkOauth::getInstance('QQLOGIN', $token);
					$data = $qq->call('user/get_user_info');
					$_SESSION['qq_info'] = json_encode($data);
					// 执行用户信息更新user服务层添加更新绑定qq函数（绑定，解绑）
					$res = $user->bindQQ($token['openid'], json_encode($data));
					// 如果执行成功执行跳转
					
					if ($res) {
						$this->success('绑定成功', $bind_pre_url);
					} else {
						$this->error('绑定失败', $bind_pre_url);
					}
				} else {
					$this->error('该qq已经绑定', $bind_pre_url);
				}
			} else {
				$retval = $user->qqLogin($token['openid']);
				// 已经绑定
				if ($retval == 1) {
					if (!empty($_SESSION['login_pre_url'])) {
						$this->redirect($_SESSION['login_pre_url']);
					} else
						$this->redirect(__URL__);
					// $this->success("登录成功", "Index/index");
				}
				if ($retval == USER_NBUND) {
					$qq = ThinkOauth::getInstance('QQLOGIN', $token);
					$data = $qq->call('user/get_user_info');
					$_SESSION['qq_info'] = json_encode($data);
					$this->assign("qq_info", json_encode($data));
					$this->assign("qq_openid", $token['openid']);
					$this->assign("data", $data);
					return $this->view($this->style . 'login/qq_callback');
				}
			}
		}
	}
}