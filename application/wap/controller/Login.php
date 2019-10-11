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

namespace app\wap\controller;

use data\extend\ThinkOauth;
use data\service\Config as WebConfig;
use data\service\WebSite;
use data\service\Weixin;
use think\Cookie;
use think\Session;

/**
 * 前台用户登录
 */
class Login extends BaseWap
{
	
	/**
	 * 判断wap端是否开启
	 */
	public function determineWapWhetherToOpen()
	{
		if ($this->web_info['wap_status'] == 3 && $this->web_info['web_status'] == 1) {
			Cookie::set("default_client", "web");
			$this->redirect(__URL(\think\Config::get('view_replace_str.SHOP_MAIN') . "/web"));
		} elseif ($this->web_info['wap_status'] == 2) {
			webClose($this->web_info['close_reason']);
		} elseif (($this->web_info['wap_status'] == 3 && $this->web_info['web_status'] == 3) || ($this->web_info['wap_status'] == 3 && $this->web_info['web_status'] == 2)) {
			webClose($this->web_info['close_reason']);
		}
	}
	
	/**
	 * 登录界面
	 */
	public function index()
	{
		if (request()->isAjax()) {
			$token = request()->post('token', "");
			if (!empty($token)) {
				session("niu_access_token", $token);
				$member_detail = api("System.Member.memberInfo");
				if ($member_detail['code'] == 0) {
					session("niu_member_detail", $member_detail['data']);
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
			$this->assign("title", "登录");
			$this->assign("title_before", "登录");
			return $this->view($this->style . 'login/login');
		}
	}
	
	/**
	 * 微信绑定用户
	 */
	public function wchatBindMember($user_name, $password, $bind_message_info)
	{
		session::set("member_bind_first", null);
		if (!empty($bind_message_info)) {
			$config = new WebConfig();
			$register_config = $config->getRegisterAndVisitInfo(0);
			if (!empty($register_config) && $register_config["is_requiretel"] == 1 && $bind_message_info["is_bind"] == 1 && !empty($bind_message_info["token"])) {
				$token = $bind_message_info["token"];
				if (!empty($token['openid'])) {
					$this->user->updateUserWchat($user_name, $password, $token['openid'], $bind_message_info['info'], $bind_message_info['wx_unionid']);
					// 拉取用户头像
					$uid = $this->user->getSessionUid();
					$url = str_replace('api.php', 'index.php', __URL(__URL__ . 'wap/login/updateUserImg?uid=' . $uid . '&type=wchat'));
					http($url, 1);
				}
			}
		}
	}
	
	/**
	 * 第三方登录登录
	 */
	public function oauthLogin()
	{
		$config = new WebConfig();
		$type = request()->get('type', '');
		if ($type == "WCHAT") {
		    if (request()->isMobile()) {
    			if (isWeixin()) {
        			$config_info = $config->getInstanceWchatConfig($this->instance_id);
        			if (empty($config_info["value"]["appid"]) || empty($config_info["value"]["appsecret"])) {
        				$this->error("请先配置微信公众号!");
        			}
    				$this->wchatLogin();
    				if (!empty(session("niu_access_token"))) {
        				if (!empty($_SESSION['login_pre_url'])) {
        					$this->redirect($_SESSION['login_pre_url']);
        				} else {
        					$redirect = __URL(__URL__ . "/wap/member/index");
        					$this->redirect($redirect);
        				}
    				}
    			}else{
    			    $this->error("请在微信浏览器中执行该操作！");
    			}
		    }else{
		        $config_info = $config->getWchatConfig($this->instance_id);
		        if (empty($config_info["value"]["APP_KEY"]) || empty($config_info["value"]["APP_SECRET"])) {
		            $this->error("当前系统未设置微信第三方登录!");
		        }
		    }
		} else if ($type == "QQLOGIN") {
			$config_info = $config->getQQConfig($this->instance_id);
			if (empty($config_info["value"]["APP_KEY"]) || empty($config_info["value"]["APP_SECRET"])) {
				$this->error("当前系统未设置QQ第三方登录!");
			}
		}
		$_SESSION['login_type'] = $type;
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
		// 获取注册配置
		$webconfig = new WebConfig();
		$register_config = $webconfig->getRegisterAndVisitInfo($this->instance_id);
		$loginBind = request()->get("loginBind", "");
		if ($_SESSION['login_type'] == 'QQLOGIN') {
			$qq = ThinkOauth::getInstance('QQLOGIN');
			$token = $qq->getAccessToken($code);
			if (!empty($token['openid'])) {
				if (!empty($_SESSION['bind_pre_url'])) {
					// 1.检测当前qqopenid是否已经绑定，如果已经绑定直接返回绑定失败
					$bind_pre_url = $_SESSION['bind_pre_url'];
					$_SESSION['bind_pre_url'] = '';
					$is_bund = $this->user->checkUserQQopenid($token['openid']);
					if ($is_bund == 0) {
						// 2.绑定操作
						$qq = ThinkOauth::getInstance('QQLOGIN', $token);
						$data = $qq->call('user/get_user_info');
						$_SESSION['qq_info'] = json_encode($data);
						// 执行用户信息更新user服务层添加更新绑定qq函数（绑定，解绑）
						$res = $this->user->bindQQ($token['openid'], json_encode($data));
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
					$retval = $this->user->qqLogin($token['openid']);
					// 已经绑定
					if ($retval == 1) {
						if (!empty($_SESSION['login_pre_url'])) {
							$this->redirect($_SESSION['login_pre_url']);
						} else {
							if (request()->isMobile()) {
								$redirect = __URL(__URL__ . "/wap/member/index");
							} else {
								$redirect = __URL(__URL__ . "/member/index");
							}
							$this->redirect($redirect);
						}
					}
					if ($retval == USER_NBUND) {
						$qq = ThinkOauth::getInstance('QQLOGIN', $token);
						$data = $qq->call('user/get_user_info');
						$_SESSION['qq_info'] = json_encode($data);
						$_SESSION['qq_openid'] = $token['openid'];
						if ($register_config["is_requiretel"] == 1 && empty($loginBind)) {
							if (request()->isMobile()) {
								$this->redirect(__URL(__URL__ . "/wap/login/registerext"));
							} else {
								$this->redirect(__URL(__URL__ . "/web/login/registerext"));
							}
						}
						if ($register_config['is_register'] == 0) {
							$this->error('抱歉,商城暂未开放注册!');
						}
						$result = $this->user->registerMember('', '123456', '', '', $token['openid'], json_encode($data), '', '', '');
						if ($result > 0) {
							if (!empty($_SESSION['login_pre_url'])) {
								$this->redirect($_SESSION['login_pre_url']);
							} else {
								if (request()->isMobile()) {
									$redirect = __URL(__URL__ . "/wap/member/index");
								} else {
									$redirect = __URL(__URL__ . "/member/index");
								}
							}
							$this->redirect($redirect);
						}
					}
				}
			}
		} elseif ($_SESSION['login_type'] == 'WCHAT') {
			$wchat = ThinkOauth::getInstance('WCHAT');
			$token = $wchat->getAccessToken($code);
			if (!empty($token['unionid'])) {
				$retval = $this->user->wchatUnionLogin($token['unionid']);
				// 已经绑定
				if ($retval == 1) {
					if (!empty($_SESSION['login_pre_url'])) {
						$this->redirect($_SESSION['login_pre_url']);
					} else {
						if (request()->isMobile()) {
							$redirect = __URL(__URL__ . "/wap/member/index");
						} else {
							$redirect = __URL(__URL__ . "/member/index");
						}
						$this->redirect($redirect);
					}
				}
			}
			if ($retval == USER_NBUND) {
				// 2.绑定操作
				$wchat = ThinkOauth::getInstance('WCHAT', $token);
				$data = $wchat->call('sns/userinfo');
				
				$_SESSION['wx_info'] = json_encode($data);
				$_SESSION['wx_unionid'] = $token['unionid'];
				
				if ($register_config["is_requiretel"] == 1 && empty($loginBind)) {
					if (request()->isMobile()) {
						$this->redirect(__URL(__URL__ . "/wap/login/registerext"));
					} else {
						$this->redirect(__URL(__URL__ . "/web/login/registerext"));
					}
				} else {
					if ($register_config['is_register'] == 0) {
						$this->error('抱歉,商城暂未开放注册!');
					}
					$result = $this->user->registerMember('', '123456', '', '', '', '', '', json_encode($data), $token['unionid']);
				}
				
				if ($result > 0) {
					if (!empty($_SESSION['login_pre_url'])) {
						$this->redirect($_SESSION['login_pre_url']);
					} else {
						if (request()->isMobile()) {
							$redirect = __URL(__URL__ . "/wap/member/index");
						} else {
							$redirect = __URL(__URL__ . "/member/index");
						}
						$this->redirect($redirect);
					}
				}
			}
		}
	}
	
	/**
	 * 微信授权登录返回
	 */
	public function wchatCallBack()
	{
		$code = request()->get('code', '');
		if (empty($code))
			die();
		$wchat = ThinkOauth::getInstance('WCHATLOGIN');
		$token = $wchat->getAccessToken($code);
		$wchat = ThinkOauth::getInstance('WCHATLOGIN', $token);
		$data = $wchat->call('/sns/userinfo');
		var_dump($data);
	}
	
	/**
	 * 注册账户
	 */
	public function register()
	{
		$reg_config = api("System.Login.getRegisterAndVisitInfo");
		$reg_config = $reg_config['data'];
		if (trim($reg_config['register_info']) == "" || $reg_config['is_register'] == 0) {
			$this->error("抱歉,商城暂未开放注册!");
		}
		$this->assign("title", "注册");
		$this->assign("title_before", "注册");
		return $this->view($this->style . 'login/register');
	}
	
	/**
	 * 完善信息
	 */
	public function registerExt()
	{
		$this->assign("title", "完善信息");
		$this->assign("title_before", "完善信息");
		return $this->view($this->style . "login/register_ext");
	}
	
	/**
	 * 注册后登陆
	 */
	public function registerLogin()
	{
		if (request()->isAjax()) {
			$username = request()->post('username', '');
			$mobile = request()->post('mobile', '');
			$password = request()->post('password', '');
			if (!empty($username)) {
				$res = $this->user->login($username, $password);
			} else {
				$res = $this->user->login($mobile, $password);
			}
			$_SESSION['order_tag'] = ""; // 清空订单
			if ($res > 0) {
				if (!empty($_SESSION['login_pre_url'])) {
					$this->redirect($_SESSION['login_pre_url']);
				} else {
					$redirect = __URL(__URL__ . "/member/index");
					$this->redirect($redirect);
				}
			}
		}
	}
	
	/**
	 * 制作推广二维码
	 */
	function showUserQrcode()
	{
		$uid = request()->get('uid', 0);
		if (!is_numeric($uid)) {
			$this->error('无法获取到会员信息');
		}
		$instance_id = $this->instance_id;
		// 读取生成图片的位置配置
		$weixin = new Weixin();
		$data = api("System.Config.getWeixinQrcodeConfig", [ 'uid' => $uid ]);
		$data = $data['data'];
		
		$member_info = $this->user->getUserInfoByUid($uid);
		
		// 获取所在店铺信息
		$web = new WebSite();
		$shop_info = $web->getWebDetail();
		$shop_logo = $shop_info["logo"];
		
		// 查询并生成二维码
		$path = 'upload/qrcode/' . 'qrcode_' . $uid . '_' . $instance_id . '.png';
		
		if (!file_exists($path)) {
			$url = $weixin->getUserWchatQrcode($uid);
			if ($url == WEIXIN_AUTH_ERROR) {
				exit();
			} else {
				getQRcode($url, 'upload/qrcode', "qrcode_" . $uid . '_' . $instance_id);
			}
		}
		// 定义中继二维码地址
		$thumb_qrcode = 'upload/qrcode/thumb_' . 'qrcode_' . $uid . '_' . $instance_id . '.png';
		$image = \think\Image::open($path);
		// 生成一个固定大小为360*360的缩略图并保存为thumb_....jpg
		$image->thumb(288, 288, \think\Image::THUMB_CENTER)->save($thumb_qrcode);
		// 背景图片
		$dst = $data["background"];
		if (stristr($dst, "http://") === false && stristr($dst, "https://") === false) {
			if (!file_exists($dst)) {
				$dst = "public/static/images/qrcode_bg/qrcode_bg.png";
			}
		}
		// 生成画布
		list ($max_width, $max_height) = getimagesize($dst);
		$dests = imagecreatetruecolor($max_width, $max_height);
		$dst_im = getImgCreateFrom($dst);
		imagecopy($dests, $dst_im, 0, 0, 0, 0, $max_width, $max_height);
		imagedestroy($dst_im);
		// 并入二维码
		// $src_im = imagecreatefrompng($thumb_qrcode);
		$src_im = getImgCreateFrom($thumb_qrcode);
		$src_info = getimagesize($thumb_qrcode);
		imagecopy($dests, $src_im, $data["code_left"] * 2, $data["code_top"] * 2, 0, 0, $src_info[0], $src_info[1]);
		imagedestroy($src_im);
		// 并入用户头像
		$user_headimg = $member_info["user_headimg"];
		if (stristr($user_headimg, "http://") === false && stristr($user_headimg, "https://") === false) {
			if (!file_exists($user_headimg)) {
				$user_headimg = "public/static/images/qrcode_bg/head_img.png";
			}
		}
		$src_im_1 = getImgCreateFrom($user_headimg);
		$src_info_1 = getimagesize($user_headimg);
		// imagecopy($dests, $src_im_1, $data['header_left'] * 2, $data['header_top'] * 2, 0, 0, $src_info_1[0], $src_info_1[1]);
		imagecopyresampled($dests, $src_im_1, $data['header_left'] * 2, $data['header_top'] * 2, 0, 0, 80, 80, $src_info_1[0], $src_info_1[1]);
		// imagecopy($dests, $src_im_1, $data['header_left'] * 2, $data['header_top'] * 2, 0, 0, $src_info_1[0], $src_info_1[1]);
		imagedestroy($src_im_1);
		
		// 并入网站logo
		if ($data['is_logo_show'] == '1') {
			if (stristr($shop_logo, "http://") === false && stristr($shop_logo, "https://") === false) {
				if (!file_exists($shop_logo)) {
					$shop_logo = "public/static/images/logo.png";
				}
			}
			$src_im_2 = getImgCreateFrom($shop_logo);
			$src_info_2 = getimagesize($shop_logo);
			imagecopy($dests, $src_im_2, $data['logo_left'] * 2, $data['logo_top'] * 2, 0, 0, $src_info_2[0], $src_info_2[1]);
			imagedestroy($src_im_2);
		}
		// 并入用户姓名
		$rgb = hColor2RGB($data['nick_font_color']);
		$bg = imagecolorallocate($dests, $rgb['r'], $rgb['g'], $rgb['b']);
		$name_top_size = $data['name_top'] * 2 + $data['nick_font_size'];
		@imagefttext($dests, $data['nick_font_size'], 0, $data['name_left'] * 2, $name_top_size, $bg, "public/static/font/Microsoft.ttf", $member_info["nick_name"]);
		header("Content-type: image/jpeg");
		ob_clean();
		imagejpeg($dests);
	}
	
	/**
	 * 获取微信推广二维码
	 */
	public function qrcode()
	{
		$this->determineWapWhetherToOpen();
		$uid = request()->get('source_uid', $this->uid);
		if (!is_numeric($uid) && $uid <= 0) {
			$this->error('无法获取到会员信息');
		}
		
		$this->assign('source_uid', $uid);
		
		$data = api("System.Config.getWeixinQrcodeConfig", [ 'uid' => $uid ]);
		$data = $data['data'];
		if (empty($data)) {
			$this->error("商家未设置推广二维码");
		}
		
		$this->assign("title", '我的推广码');
		$this->assign("title_before", "我的推广码");
		return $this->view($this->style . "login/qrcode");
	}
	
	/**
	 * 用户锁定界面
	 */
	public function lock()
	{
		$this->assign("title", lang('user_locked'));
		$this->assign("title_before", lang('user_locked'));
		return $this->view($this->style . "login/lock");
	}
	
	public function find()
	{
		$this->assign("title", '忘记密码');
		$this->assign("title_before", "忘记密码");
		return $this->view($this->style . "login/find");
	}
	
	/**
	 * 绑定账号
	 */
	public function bind()
	{
		$this->assign("title", "绑定账号");
		$this->assign("title_before", "绑定账号");
		return $this->view($this->style . "Login/bind");
	}
	
	/**
	 * 更新会员头像
	 */
	public function updateUserImg()
	{
		$uid = request()->get('uid', '');
		$type = request()->get('type', 'wchat');
		$retval = $this->user->updateUserImg($uid, $type);
		return $retval;
	}
}