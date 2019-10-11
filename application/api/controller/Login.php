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

namespace app\api\controller;

use data\service\Applet\AppletWechat;
use data\service\Config as ConfigService;
use data\service\Member;
use data\service\User;
use think\Cache;
use think\Session;

/**
 * 登录、注册相关api
 */
class Login extends BaseApi
{
	/**
	 * 获取微信基础信息
	 */
	function getWechatBasicInfo()
	{
		$title = '获取小程序基础信息';
		$config = new ConfigService();
		$applet_config = $config->getInstanceAppletConfig($this->instance_id);
		if (!empty($applet_config["value"])) {
			$appid = $applet_config["value"]['appid'];
			$secret = $applet_config["value"]['appsecret'];
		} else {
			return $this->outMessage($title, '', -50, '商家未配置小程序');
		}
		$code = $this->get('code', '');
		$url = 'https://api.weixin.qq.com/sns/jscode2session';
		$url = $url . '?appid=' . $appid;
		$url .= '&secret=' . $secret;
		$url .= '&js_code=' . $code . '&grant_type=authorization_code';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		$result = curl_exec($ch);
		curl_close($ch);
		return $this->outMessage($title, $result);
	}

	/**
	 * 获取微信详细信息
	 *
	 * @return \think\response\Json
	 */
	function getWechatParticularInfo()
	{
		$title = '获取小程序详细信息';
		$config = new ConfigService();
		$applet_config = $config->getInstanceAppletConfig($this->instance_id);
		$sessionKey = $this->get('sessionKey', '');
		$encryptedData = $this->get('encryptedData', '');
		$iv = $this->get('iv', '');
		if (!empty($applet_config["value"])) {
			$appid = $applet_config["value"]['appid'];
		} else {
			return $this->outMessage($title, '', -50, '商家未配置小程序');
		}
		$wchat_applet = new WchatApplet($appid, $sessionKey);
		$errCode = $wchat_applet->decryptData($encryptedData, $iv, $data);
		if ($errCode < 0) {
			$message = '登录失败';
			switch ($errCode) {
				case -41001:
					$message = 'encodingAesKey 非法';
					break;
				case -41002:
					$message = 'aes 解密失败';
					break;
				case -41003:
					$message = 'buffer 非法';
					break;
				case -41004:
					$message = 'base64 解密失败';
					break;
				default:
					break;
			}
			return $this->outMessage($title, [ 'code' => -1, 'message' => $message ]);
		} else {
			return $this->outMessage($title, [ 'code' => 0, 'data' => $data ]);
		}
	}
	
	/**
	 * 登录配置
	 */
	public function loginConfig()
	{
		$title = '获取登录配置信息';
		
		$config = new ConfigService();
		// 登录配置
		$login_config = $config->getLoginConfig();
		// 验证码配置
		$code_config = $config->getLoginVerifyCodeConfig(0);
		// 注册配置
		$reg_config = $config->getRegisterAndVisitInfo(0);
		$data = array(
			'login_config' => $login_config,
			'code_config' => $code_config,
		    'reg_config' => $reg_config
		);
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 登录
	 */
	public function login()
	{
		$title = '会员登录';
		
		$user_name = $this->get('username', '');
		$password = $this->get('password', '');

        $source_uid = $this->get('source_uid', ''); // 小程序使用参数
        $bind_message_info = $this->get('bind_message_info', ''); // 小程序使用参数
        if (! empty($bind_message_info)) {
            Session::set('bind_message_info', $bind_message_info);
            Session::set('source_uid', $source_uid);
        }

		$member = new Member();
		if (empty($user_name)) return $this->outMessage($title, [ 'code' => -1, 'message' => '缺少必须参数username' ]);
		if (empty($password)) return $this->outMessage($title, [ 'code' => -1, 'message' => '缺少必须参数password' ]);
		
		$retval = $member->login($user_name, $password);
		if ($retval > 0) {
			$member_info = $member->getMemberLoginInfo();
			return $this->outMessage($title, $member_info);
		} else {
			return $this->outMessage($title, AjaxReturn($retval));
		}
	}
	
	/**
	 * 手机动态码登录
	 */
	public function mobileLogin()
	{
		$title = "手机动态码登录";
		
		$mobile = $this->get('mobile', '');
		$sms_captcha = $this->get('sms_captcha', '');
		if (empty($mobile)) return $this->outMessage($title, [ 'code' => 0, 'message' => '缺少必须参数mobile' ]);
		if (empty($sms_captcha)) return $this->outMessage($title, [ 'code' => 0, 'message' => '缺少必须参数sms_captcha' ]);

        $source_uid = $this->get('source_uid', ''); // 小程序使用参数
        $bind_message_info = $this->get('bind_message_info', ''); // 小程序使用参数
        if (! empty($bind_message_info)) {
            Session::set('bind_message_info', $bind_message_info);
            Session::set('source_uid', $source_uid);
        }

		$member = new Member();
		$sms_captcha_code = Session::get('mobileVerificationCode');
		$sendMobile = Session::get('sendMobile');
		
		if ($mobile != $sendMobile) return $this->outMessage($title, [ 'code' => 0, 'message' => '登录手机号与验证时的不一致' ]);
		
		if ($sms_captcha == $sms_captcha_code && !empty($sms_captcha_code)) {
			$retval = $member->login($mobile, '');
			if ($retval == USER_ERROR) {
			    // 手机号未注册
                $password = md5('Niushop'.time().rand(100000, 999999)); 
                $retval = $member->registerMember('', $password, '', $mobile, '', '', '', '', '');
			}
		} else {
			$retval = -10;
		}
		
		if ($retval > 0) {
			$data = $member->getMemberLoginInfo();
			return $this->outMessage($title, $data);
		} else {
			return $this->outMessage($title, AjaxReturn($retval));
		}
	}
	
	/**
	 * 注册配置
	 */
	public function registerConfig()
	{
		$title = '获取注册配置信息';
		
		$config = new ConfigService();
		// 注册配置
		$reg_config = $config->getRegisterAndVisitInfo(0);
		// 验证码配置
		$code_config = $config->getLoginVerifyCodeConfig(0);
		
		$data = array(
			'reg_config' => $reg_config,
			'code_config' => $code_config
		);
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 注册访问配置
	 * @return string
	 */
	public function getRegisterAndVisitInfo()
	{
		$config = new ConfigService();
		$reg_config = $config->getRegisterAndVisitInfo(0);
		return $this->outMessage("注册访问配置", $reg_config);
	}
	
	/**
     * 账号注册
     */
    public function usernameRegister(){
        $title = "账号注册";
        $member = new Member();

        $user_name = $this->get('username', '');
        $password = $this->get('password', '');
        $email = $this->get('email', '');
        $mobile = $this->get('mobile', '');

        $source_uid = $this->get('source_uid', ''); // 小程序使用参数
        $bind_message_info = $this->get('bind_message_info', ''); // 小程序使用参数
        if (! empty($bind_message_info)) {
            Session::set('bind_message_info', $bind_message_info);
            Session::set('source_uid', $source_uid);
        }

        $retval_id = $member->registerMember($user_name, $password, $email, $mobile, '', '', '', '', '');
        if ($retval_id > 0) {
            Session::pull('mobileVerificationCode');
            session::pull('mobileVerificationCode_time');
            $data = $member->getMemberLoginInfo();
        }else{
            $data = [
                'code' => -1,
                'message' => getErrorInfo($retval_id)
            ];
        }
        return $this->outMessage($title, $data);
    }
	
	/**
	 * 邮箱注册
	 */
	public function emailRegister()
	{
		$title = "PC端、邮箱注册";
		
		$email = $this->get('email', '');
		$password = $this->get('password', '');
		$email_code = $this->get('email_code', '');
		
		$member = new Member();
		$web_config = new ConfigService();
		
		$notice = $web_config->getNoticeEmailConfig(0);
		
		// 判断邮箱是否开启
		if ($notice[0]['is_use'] == 1) {
			if (empty($email_code)) return $this->outMessage($title, -1, -1, '缺少必须参数email_code');
			$param = session::get('emailVerificationCode');
			if ($email_code != $param) {
				return $this->outMessage($title, -1, -1, '手机验证码错误');
			}
		}
		
		$retval_id = $member->registerMember('', $password, $email, '', '', '', '', '', '');
		
		if ($retval_id > 0) {
			Session::pull('emailVerificationCode');
			$data = $member->getMemberLoginInfo();
		} else {
			$data = [
				'code' => -1,
				'message' => getErrorInfo($retval_id)
			];
		}
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 手机注册
	 */
	public function mobileRegister()
	{
		$title = "手机号注册";
		$mobile = isset($this->params['mobile']) ? $this->params['mobile'] : "";
		$password = isset($this->params['password']) ? $this->params['password'] : "";
		$mobile_code = isset($this->params['mobile_code']) ? $this->params['mobile_code'] : "";
		
		$member = new Member();
		$web_config = new ConfigService();
		
		$noticeMobile = $web_config->getNoticeMobileConfig(0);
		if ($noticeMobile[0]['is_use'] == 1) {
			if (empty($mobile_code)) return $this->outMessage($title, -1, -1, '缺少必须参数mobile_code');
			$param = session::get('mobileVerificationCode');
			if ($mobile_code != $param) {
				return $this->outMessage($title, -1, -1, '手机验证码错误');
			}
		}
		
		$retval_id = $member->registerMember('', $password, '', $mobile, '', '', '', '', '');
		
		if ($retval_id > 0) {
			Session::pull('mobileVerificationCode');
			Session::pull('mobileVerificationCode_times');
			$data = $member->getMemberLoginInfo();
		} else {
			$data = [
				'code' => -1,
				'message' => getErrorInfo($retval_id)
			];
		}
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 绑定账号
	 * 创建时间：2019年1月4日17:00:06
	 */
	public function bindAccount()
	{
		$title = "绑定账号";
//		$web_config = new ConfigService();
		$member = new Member();
		
		// 登录配置
//        $code_config = $web_config->getLoginVerifyCodeConfig(0);
		$user_name = isset($this->params['username']) ? $this->params['username'] : "";
		$password = isset($this->params['password']) ? $this->params['password'] : "";
		
		$retval = $member->login($user_name, $password);
		
		if ($retval > 0) {
			// qq登录
			if (isset($_SESSION['login_type']) && $_SESSION['login_type'] == 'QQLOGIN') {
				$qq_openid = $_SESSION['qq_openid'];
				$qq_info = $_SESSION['qq_info'];
				if (!empty($qq_openid) && !empty($qq_info)) {
					$res = $member->bindQQ($qq_openid, $qq_info);
					if ($res) {
						// 拉取用户头像
						$uid = $member->getSessionUid();
						$url = str_replace('api.php', 'index.php', __URL(__URL__ . 'wap/login/updateUserImg?uid=' . $uid . '&type=qq'));
						http($url, 1);
						unset($_SESSION['qq_openid']);
						unset($_SESSION['qq_info']);
						unset($_SESSION['bind_pre_url']);
						unset($_SESSION['login_type']);
						$result = $member->getMemberLoginInfo();
						$result['message'] = '绑定成功';
					} else {
						$result = [ 'code' => -1, 'message' => '账号绑定失败' ];
					}
				} else {
				    $result = [ 'code' => -1, 'message' => '未获取到绑定信息' ];
				}
			}
			// 微信登录
			if (isset($_SESSION['login_type']) && $_SESSION['login_type'] == 'WCHAT') {
				$unionid = $_SESSION['wx_unionid'];
				$wx_info = $_SESSION['wx_info'];
				$member = new Member();
				if (!empty($unionid) && !empty($wx_info)) {
					$res = $member->bindWchat($unionid, $wx_info);
					if ($res) {
						// 拉取用户头像
						$uid = $member->getSessionUid();
						$url = str_replace('api.php', 'index.php', __URL(__URL__ . 'wap/login/updateUserImg?uid=' . $uid . '&type=wchat'));
						http($url, 1);
						unset($_SESSION['wx_unionid']);
						unset($_SESSION['wx_info']);
						unset($_SESSION['bind_pre_url']);
						unset($_SESSION['login_type']);
						$result = $member->getMemberLoginInfo();
						$result['message'] = '绑定成功';
					} else {
					    $result = [ 'code' => -1, 'message' => '账号绑定失败' ];
					}
				} else {
				    $result = [ 'code' => -1, 'message' => '未获取到绑定信息' ];
				}
			}
		} else {
			$result = [ 'code' => -1, 'message' => '用户名或密码错误' ];
		}
		return $this->outMessage($title, $result);
	}
	
	/**
	 * 找回密码
	 */
	public function findPassword()
	{
		$type = isset($this->params['type']) ? $this->params['type'] : "mobile";
		$info = isset($this->params['info']) ? $this->params['info'] : "";
		$condition = [];
		if ($type == "mobile") {
			$condition = [
				'user_tel' => $info
			];
		} else if ($type == "email") {
			$condition = [
				'user_email' => $info
			];
		}
		$member = new Member();
		$res = $member->getUserInfoByCondition($condition);
		return $this->outMessage("忘记修改密码账号验证", $res);
	}
	
	/**
	 * 找回密码 密码重置
	 */
	public function passwordReset()
	{
		$title = "找回密码密码重置";

        $is_applet = $this->get('is_applet', 0); // 是否小程序
        $account = $this->get('account', '');
        $password = $this->get('password', '');
        $type = $this->get('type', '');
        $code = $this->get('code', '');
		
        if ($is_applet == 1) {
            $key = $this->get('key', '-504*502');
            $key = md5('@' . $key . '--');
            $data = Cache::get($key);
            $param = $data['code'];
        } else {
            $param = Session::get('findPasswordVerificationCode');
        }
		if ($code != $param) {
			return $this->outMessage($title, [ 'code' => -1, 'message' => '动态码错误' ]);
		}
		
		$member = new Member();
		if ($type == "email") {
            $codeEmail = $is_applet == 1 ? $data['email'] : Session::get("codeEmail");
			if ($account != $codeEmail) {
				return $this->outMessage($title, [ 'code' => -1, '该邮箱与验证时的邮箱不符' ]);
			}
			$res = $member->updateUserPasswordByEmail($account, $password);
			// 重置密码后 清除session
			Session::delete('codeEmail');
			Session::delete('findPasswordVerificationCode');
		} elseif ($type == "mobile") {
			$codeMobile =  $is_applet == 1 ? $data['mobile'] : Session::get("codeMobile");
			if ($account != $codeMobile) {
				return $this->outMessage($title, [ 'code' => -1, '该手机号与验证时的手机不符' ]);
			}
			$res = $member->updateUserPasswordByMobile($account, $password);
			// 重置密码后 清除session
			Session::delete('codeMobile');
			Session::delete('findPasswordVerificationCode');
		}
		if ($is_applet == 1) {
            Cache::set($key, null);
        }
		return $this->outMessage($title, AjaxReturn($res));
	}
	
	/**
	 * 完善信息
	 */
	public function perfectInfo()
	{
		$title = "完善信息";
		
		// 登录配置
		$member = new Member();
		$user_name = isset($this->params['username']) ? $this->params['username'] : "";
		$password = isset($this->params['password']) ? $this->params['password'] : "";
	
		$exist = $member->judgeUserNameIsExistence($user_name);
		if ($exist) {
			return $this->outMessage($title, [
				"code" => -1,
				"message" => "该用户名已存在"
			]);
		}
		
		// qq
		if (isset($_SESSION['login_type']) && $_SESSION['login_type'] == 'QQLOGIN') {
			$qq_openid = $_SESSION['qq_openid'];
			$qq_info = $_SESSION['qq_info'];
			$result = $member->registerMember($user_name, $password, '', '', $qq_openid, $qq_info, '', '', '');
		}
		
		// 微信
		if (isset($_SESSION['login_type']) && $_SESSION['login_type'] == 'WCHAT') {
			$unionid = $_SESSION['wx_unionid'];
			$wx_info = $_SESSION['wx_info'];
			$result = $member->registerMember($user_name, $password, '', '', '', '', '', $wx_info, $unionid);
		}
		
		if ($result > 0) {
			
			// 注册成功之后
			if (isset($_SESSION['login_type']) && $_SESSION['login_type'] == 'QQLOGIN') {
				unset($_SESSION['qq_openid']);
				unset($_SESSION['qq_info']);
			} elseif (isset($_SESSION['login_type']) && $_SESSION['login_type'] == 'WCHAT') {
				unset($_SESSION['wx_unionid']);
				unset($_SESSION['wx_info']);
			}
			
			$token = array(
			    'uid' => $result,
			    'request_time' => time()
			);
			$encode = $this->niuEncrypt(json_encode($token));
			
			$url = empty($_SESSION['login_pre_url']) ? __URL(__URL__ . "/index/index") : $_SESSION['login_pre_url'];
			return $this->outMessage($title, [
				"code" => 1,
				"message" => "注册成功",
				"url" => $url,
			    "token" => $encode
			]);
		} else {
			return $this->outMessage($title, [
				"code" => -1,
				"message" => "注册失败"
			]);
		}
		return $this->outMessage("获取注册访问设置", null);
	}
	
	/**
	 * 注册协议
	 */
	public function registerAgreement()
	{
		$config = new ConfigService();
		$info = $config->getRegistrationAgreement(0);
		return $this->outMessage("注册协议", $info['value']);
	}
	
	/**
	 * 发送注册短信验证码
	 */
	public function sendRegisterMobileCode()
	{
		$title = '发送短信验证码';

        $is_applet = $this->get('is_applet', 0); // 是否小程序

        $params['mobile'] = $this->get('mobile', '');
        $params['shop_id'] = 0;
//        $result = runhook('Notify', 'registSmsValidation', $params);

        $params["type"] = "sms";
        $result = message('register_validate', $params);
        if ($is_applet == 1){
            $key = $this->get('key', '-504*502');
            $key = md5('@' . $key . '-');
            $data['code'] = $result['param'];
            $data['mobile'] = $params['mobile'];
            Cache::set($key, $data, 300);
        } else {
            Session::set('mobileVerificationCode', $result['param']);
            Session::set('sendMobile', $params['mobile']);
        }

		if (empty($result)) {
			$result = [
				'code' => -1,
				'message' => "发送失败"
			];
		} else if ($result["code"] != 0) {
			$result = [
				'code' => $result["code"],
				'message' => $result["message"]
			];
		} else if ($result["code"] == 0) {
			$result = [
				'code' => 0,
				'message' => "发送成功"
			];
		}
		return $this->outMessage($title, $result);
	}

	/**
	 * 发送邮箱验证码
	 */
	public function sendRegisterEmailCode()
	{
		$title = "发送邮箱验证码";
		$is_applet = $this->get('is_applet', 0); // 是否小程序

		$params['email'] = $this->get('email', '');
		$params['shop_id'] = 0;
//		$result = runhook('Notify', 'registEmailValidation', $params);
        $params["type"] = "email";
		$result = message("register_validate", $params);

		if ($is_applet == 1){
            $key = $this->get('key', '-504*502');
            $key = md5('@' . $key . '-');
            $data['code'] = $result['param'];
            $data['email'] = $params['email'];
            Cache::set($key, $data, 300);
        } else {
            Session::set('emailVerificationCode', $result['param']);
        }

		if (empty($result)) {
			$result = [
				'code' => -1,
				'message' => "发送失败"
			];
		} elseif ($result['code'] == 0) {
			$result = [
				'code' => 0,
				'message' => "发送成功"
			];
		} else {
			$result = [
				'code' => $result['code'],
				'message' => $result['message']
			];
		}
		return $this->outMessage($title, $result);
	}
	
	/**
	 * 发送找回密码 短信 邮箱验证码
	 */
	public function sendFindPasswordCode()
	{
		$title = "获取前端邮箱/手机号是否存在";

		$is_applet = $this->get('is_applet', 0); // 是否小程序
        $send_type = $this->get('type', '');
        $send_param = $this->get('send_param', '');
		
		if (empty($send_type)) return $this->outMessage($title, [ 'code' => -1, 'message' => '缺少必须参数type' ]);
		if (empty($send_param)) return $this->outMessage($title, [ 'code' => -1, 'message' => '缺少必须参数send_param' ]);
		
		$member = new Member();
		if ($send_type == 'sms') {
			if (!$member->memberIsMobile($send_param)) {
				$result = [
					'code' => -1,
					'message' => "该手机号未注册"
				];
				return $this->outMessage($title, $result);
			} else {
				Session::set("codeMobile", $send_param);
			}
		} elseif ($send_type == 'email') {
			$member->memberIsEmail($send_param);
			if (!$member->memberIsEmail($send_param)) {
				$result = [
					'code' => -1,
					'message' => "该邮箱未注册"
				];
				return $this->outMessage($title, $result);
			} else {
				Session::set("codeEmail", $send_param);
			}
		}
		$params = array(
			"send_type" => $send_type,
			"send_param" => $send_param,
			"shop_id" => 0
		);
//		$result = runhook("Notify", "forgotPassword", $params);
		$result = message("forgot_password", $params);
		Session::set('findPasswordVerificationCode', $result['param']);

        if ($is_applet == 1) {
            $key = $this->get('key', '-504*502');
            $key = md5('@' . $key . '--');
            if ($send_type == 'email') {
                $data['email'] = $send_param;
            } else {
                $data['mobile'] = $send_param;
            }
            $data['code'] = $result['param'];
            Cache::set($key, $data, 300);
        }
		if (empty($result)) {
			$result = [
				'code' => -1,
				'message' => "发送失败"
			];
		} elseif ($result['code'] == 0) {
			$result = [
				'code' => 0,
				'message' => "发送成功"
			];
		} else {
			$result = [
				'code' => $result['code'],
				'message' => $result['message']
			];
		}
		return $this->outMessage($title, $result);
	}
	
    /**
     * 验证注册手机验证码
     */
    public function checkRegisterMobileCode()
    {
        $is_applet = $this->get('is_applet', 0); // 是否小程序
        $send_param = $this->get('send_param', '');

        if ($is_applet == 1) {
            $send_mobile = $this->get('send_mobile', '');
            $key = $this->get('key', '-504*502');
            $key = md5('@' . $key . '-');

            $param = Cache::get($key);
        } else {
            $param = session::get('mobileVerificationCode');
        }

        if (($send_param == $param || $is_applet == 1 && $send_mobile == $param['mobile'] && $send_param == $param['code']) && $send_param != '') {
            $data = [
                'code' => 0,
                'message' => "验证码一致"
            ];
            if ($is_applet == 1)
            Cache::set($key, null);
        } else {
            $data = [
                'code' => 1,
                'message' => "验证码不一致"
            ];
        }

        return $this->outMessage("验证注册手机号验证码", $data);
    }


    /**
     * 验证邮箱验证码
     */
    public function checkRegisterEmailCode()
    {
        $is_applet = $this->get('is_applet', 0); // 是否小程序
        $send_param = $this->get('send_param', '');

        if ($is_applet == 1) {
            $send_email = $this->get('send_email', '');
            $key = $this->get('key', '-504*502');
            $key = md5('@' . $key . '-');

            $param = Cache::get($key);
        } else {
            $param = Session::get('emailVerificationCode');
        }

        if (($send_param == $param || $is_applet == 1 && $send_email == $param['email'] && $send_param == $param['code']) && $send_param != '') {
            $data = [
                'code' => 0,
                'message' => "验证码一致"
            ];
            if ($is_applet == 1)
            Cache::set($key, null);
        } else {
            $data = [
                'code' => 1,
                'message' => "验证码不一致"
            ];
        }

        return $this->outMessage("验证注册邮箱验证码", $data);
    }
	
	/**
	 * 找回密码动态码验证
	 */
	public function checkFindPasswordCode()
	{
		$title = "找回密码动态码验证";
		
		$send_param = isset($this->params['send_param']) ? $this->params['send_param'] : "";
		$param = Session::get('findPasswordVerificationCode');
		
		if ($send_param == $param && $send_param != '') {
			$retval = [
				'code' => 0,
				'message' => "验证码一致"
			];
		} else {
			$retval = [
				'code' => 1,
				'message' => "验证码不一致"
			];
		}
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 微信登录
	 */
	public function wechatLogin()
	{
		$title = "会员登录";
		$openid = request()->post('openid', '');
		$info = request()->post('wx_info', '');
		$source_uid = request()->post('sourceid', '');
		
		if (empty($openid) || $openid == 'undefined') {
			return $this->outMessage($title, '', '-50', "无效的openid");
		}
		// 处理信息
		$member = new Member();
		$applet_wechat = new AppletWechat();
		$wx_info = json_decode($info, true);
		$unionid = $wx_info['unionid'] == 'undefined' || $wx_info['unionid'] == null || $wx_info['unionid'] == 'null' ? '' : $wx_info['unionid'];
		$res = $applet_wechat->wchatAppLogin($openid, $unionid);
		// 返回信息
		if ($res == 1) {
			$user_info = $applet_wechat->getUserDetailByOpenid($openid);
			$member_info = $member->getMemberDetail($user_info['uid'], $user_info['instance_id']);
			$encode = $this->niuEncrypt(json_encode($user_info));
			return $this->outMessage($title, array(
				'member_info' => $member_info,
				'token' => $encode
			));
		} else if ($res == 10) {
			$user_info = $applet_wechat->getUserDetailByUnionid($unionid);
			$member_info = $member->getMemberDetail($user_info['uid'], $user_info['instance_id']);
			$encode = $this->niuEncrypt(json_encode($user_info));
			return $this->outMessage($title, array(
				'member_info' => $member_info,
				'token' => $encode
			));
		} else {
			if ($res == USER_NBUND) {
				return $this->wchatRegister($openid, $wx_info, $source_uid);
			} else {
				return $this->outMessage($title, '', '-50', '用户被锁定或者登录失败!');
			}
		}
	}
	
	public function wchatRegister($openid, $wx_info, $source_uid)
	{
		$title = "会员注册";
		// 处理信息
		$member = new Member();
		$user = new User();
		$weapp_user = new AppletWechat();
		
		$wx_info['opneid'] = $openid;
		$wx_info['sex'] = $wx_info['gender'];
		$wx_info['headimgurl'] = $wx_info['avatarUrl'];
		$wx_info['nickname'] = $wx_info['nickName'];
		$wx_unionid = $wx_info['unionid'];
		$wx_info = json_encode($wx_info);
		
		$retval = $weapp_user->wchatAppLogin($openid, $wx_unionid);
		if ($retval == USER_NBUND) {
			
			if (!empty($source_uid)) {
				$_SESSION['source_uid'] = $source_uid;
			}
			
			// 检测是否开启微信自动注册
			$config = new ConfigService();
			$register_and_visit = $config->getRegisterAndVisit(0);
			$register_config = json_decode($register_and_visit['value'], true);
			if (!empty($register_config) && $register_config["is_requiretel"] == 1) {
				return $this->outMessage($title, '', 20);
			}
			// 注册
			$openid = $wx_unionid == '' || $wx_unionid == 'undefined' || $wx_unionid == 'null' || $wx_unionid == null ? $openid : '';
			$result = $member->registerMember('', '', '', '', '', '', $openid, $wx_info, $wx_unionid);
			
			if ($result > 0) {
				$user_info = $user->getUserInfoByUid($result);
				$member_info = $member->getMemberDetail($user_info['instance_id'], $user_info['uid']);
				
				$token = array(
					'uid' => $user_info['uid'],
					'request_time' => time()
				);
				$encode = $this->niuEncrypt(json_encode($token));
				return $this->outMessage($title, array(
					'member_info' => $member_info,
					'token' => $encode
				));
			} else {
				return $this->outMessage($title, '', '-50', "注册失败");
			}
		} elseif ($retval == USER_LOCK) {
			return $this->outMessage($title, '', '-50', "用户被锁定");
		}
	}

    /**
     * 验证图文验证码
     */
    public function checkVertification()
    {
        $key = $this->get('key', '-504*504');
        $code = $this->get('code', '');
        $key = md5('@' . $key . '*');
        $param = Cache::get($key);
        if ($code == $param && $code != '') {
            $data = [
                'code' => 0,
                'message' => "验证码一致"
            ];
        } else {
            $data = [
                'code' => -1,
                'message' => "验证码不一致"
            ];
        }
        return $this->outMessage("验证图文验证码", $data);
    }
}