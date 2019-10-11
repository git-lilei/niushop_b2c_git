<?php
/**
 * BaseApi.php
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

use data\service\Config;
use think\Controller;
use think\Request;
use think\Session;

class BaseApi extends Controller
{
	public $params;//外界获取参数
	
	public $api_result;
	
	protected $uid;
	
	protected $instance_id;
	
	//加密后的uid
	public $token;
	
	protected $auth_key = 'addexdfsdfewfscvsrdf!@#';
	
	/**
	 * 当前版本的路径
	 */
	public function __construct($params = [])
	{
		parent::__construct();

		//获取参数
		$this->params = $params;
		if (empty($params)) {
			$this->params = input();
		}
		//验证检测
		$this->api_result = new ApiResult();
		$check_sign = $this->checkSign();
		if ($check_sign == 0) {
			echo $this->outMessage('sign检测错误，请检测签名!', '', -7777);
			exit();
		}
		$this->token = $this->params['token'];
		$this->instance_id = 0;
		$check_token = $this->checkToken();
		
		if ($check_token == 0) {
			return $this->outMessage('token已失效', '');
		}
		
	}
	
	/**
	 * 检测签名
	 */
	private function checkSign()
	{
		$config = new Config();
		$api_config = $config->getApiSecureConfig();
		if (empty($api_config)) {
			return 1;
		} else {
			if ($api_config['is_open_api_secure']) {
				//秘钥解密
				$check_sign = isset($this->params['sign']) ? $this->params['sign'] : '';
				$private_key = isset($this->params['private_key']) ? $this->params['private_key'] : '';
				if ($private_key == $api_config['private_key']) {
					return 1;
				}
				if (empty($check_sign)) {
					return 0;
				}
				unset($this->params['sign']);
				unset($this->params['private_key']);
				$sign = getSign($api_config['private_key'], $this->params);
				if ($check_sign != $sign) {
					return 0;
				}
				return 1;
			} else {
				return 1;
			}
		}
	}
	
	/**
	 * 检测token
	 */
	public function checkToken()
	{
		$token = isset($this->params['token']) ? $this->params['token'] : '';
		$is_applet = $this->get('is_applet', 0);
		Session::set('is_applet', $is_applet);
		if (cookie('niu_access_token'))
		    $token = cookie('niu_access_token');
		else if (session('niu_access_token'))
		    $token = cookie('niu_access_token');
		if (!empty($token)) {
			$data = $this->niuDecrypt($token);
			$data = json_decode($data, true);
			if (!empty($data['uid'])) {
				$this->uid = $data["uid"];
				$model = 'app';
				Session::set($model . 'uid', $this->uid);
				Session::set($model . 'is_system', 0);
				Session::set($model . 'is_member', 1);
				Session::set($model . 'instance_id', 0);
				// 判断是否小程序请求接口
				if ($is_applet == 1) {
				    $model = 'api';
                    Session::set($model . 'uid', $this->uid);
                    Session::set($model . $this->uid . 'from', 'WECHATAPPLET');
                    Session::set($model . 'is_system', 0);
                    Session::set($model . 'is_member', 1);
                    Session::set($model . 'instance_id', 0);
				}
				return 1;
			} else {
				return 0;
			}
		} else {
			return 1;
		}
	}
	
	/**
	 * 返回信息
	 * @param $title
	 * @param $data
	 * @param int $code
	 * @param string $message
	 * @return string
	 */
	protected function outMessage($title, $data, $code = 0, $message = "success")
	{
	    $title = lang($title);
		$this->api_result->code = $code;
		$this->api_result->data = $data;
		$this->api_result->message = $message;
		$this->api_result->title = $title;
		
		if ($this->api_result) {
			return json_encode($this->api_result, JSON_UNESCAPED_UNICODE);
		} else {
			abort(404);
		}
	}
	
	/**
	 * 系统解密方法
	 *
	 * @param string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
	 * @param string $key 加密密钥
	 * @return string
	 */
	protected function niuDecrypt($data, $key = '')
	{
		$key = md5(empty($key) ? $this->auth_key : $key);
		$data = str_replace(array(
			'-',
			'_'
		), array(
			'+',
			'/'
		), $data);
		$mod4 = strlen($data) % 4;
		if ($mod4) {
			$data .= substr('====', $mod4);
		}
		$data = base64_decode($data);
		$expire = substr($data, 0, 10);
		$data = substr($data, 10);
		
		if ($expire > 0 && $expire < time()) {
			return '';
		}
		$x = 0;
		$len = strlen($data);
		$l = strlen($key);
		$char = $str = '';
		
		for ($i = 0; $i < $len; $i++) {
			if ($x == $l)
				$x = 0;
			$char .= substr($key, $x, 1);
			$x++;
		}
		
		for ($i = 0; $i < $len; $i++) {
			if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
				$str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
			} else {
				$str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
			}
		}
		return base64_decode($str);
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
		$key = md5(empty($key) ? $this->auth_key : $key);
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
	 * 检测验证码是否正确
	 */
	public function checkCaptcha()
	{
		$web_config = new Config();
		$code_config = $web_config->getLoginVerifyCodeConfig($this->instance_id);
		$vertification = isset($this->params['vertification']) ? $this->params['vertification'] : '';
		$result = [
			'code' => -1,
			'message' => "验证码错误"
		];
		if ($code_config["value"]['pc'] == 1) {
			if (captcha_check($vertification)) {
				$result = [
					'code' => 1,
					'message' => "验证码正确"
				];
			}
		}
		return $this->outMessage('检测验证码是否正确', $result);
	}
	
	/**
	 * 获取API接受参数
	 * @param $key
	 * @param string $default_value
	 * @return string
	 */
	public function get($key, $default_value = "")
	{
		$v = isset($this->params[ $key ]) ? $this->params[ $key ] : $default_value;
		return $v;
	}
}

class ApiResult
{
	
	public $code;
	
	public $message;
	
	public $data;
	
	public $title;
	
	public function __construct()
	{
		$this->code = 0;
		$this->title = '';
		$this->message = "success";
		$this->data = null;
	}
}