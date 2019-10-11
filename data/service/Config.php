<?php
/**
 * Config.php
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

/**
 * 系统配置业务层
 */
use data\model\ConfigModel;
use data\model\NoticeModel;
use data\model\NsAppUpgradeModel;
use data\model\SysShortcutMenuModel;
use data\model\SysWapBlockTempModel;
use data\model\SysWapCustomTemplateModel;
use think\Cache;
use think\Db;
use data\model\SysWapEntranceModel;

class Config extends BaseService
{
	
	private $config_module;
	
	function __construct()
	{
		parent::__construct();
		$this->config_module = new ConfigModel();
	}
	
	/*************************************************微信公众号设置*********************************************************/
	/**
	 * 微信公众平台设置
	 */
	public function setInstanceWchatConfig($instance_id, $appid, $appsecret, $token)
	{
		Cache::tag('config')->set("InstanceWchatConfig" . $instance_id, '');
		$author_appid = 'instanceid_' . $instance_id;
		cache::set('token-' . $author_appid, null);
		$data = array(
			'appid' => trim($appid),
			'appsecret' => trim($appsecret),
			'token' => trim($token)
		);
		$value = json_encode($data);
		$info = $this->config_module->getInfo([
			'key' => 'SHOPWCHAT',
			'instance_id' => $instance_id
		], 'value');
		if (empty($info)) {
			$config_module = new ConfigModel();
			$data = array(
				'instance_id' => $instance_id,
				'key' => 'SHOPWCHAT',
				'value' => $value,
				'is_use' => 1,
				'create_time' => time()
			);
			$res = $config_module->save($data);
		} else {
			$config_module = new ConfigModel();
			$data = array(
				'key' => 'SHOPWCHAT',
				'value' => $value,
				'is_use' => 1,
				'modify_time' => time()
			);
			$res = $config_module->save($data, [
				'instance_id' => $instance_id,
				'key' => 'SHOPWCHAT'
			]);
		}
		return $res;
	}
	
	/**
	 * 获取微信公众平台设置
	 */
	public function getInstanceWchatConfig($instance_id)
	{
		$cache = Cache::tag('config')->get("InstanceWchatConfig" . $instance_id);
		if (!empty($cache)) {
			return $cache;
		}
		$info = $this->config_module->getInfo([
			'key' => 'SHOPWCHAT',
			'instance_id' => $instance_id
		], 'value');
		if (empty($info)) {
			$info = array(
				'value' => array(
					'appid' => '',
					'appsecret' => '',
					'token' => 'TOKEN'
				),
				'is_use' => 1
			);
		} else {
			$info['value'] = json_decode($info['value'], true);
		}
		Cache::tag('config')->set("InstanceWchatConfig" . $instance_id, $info);
		return $info;
	}
	/*************************************************微信公众号设置结束******************************************************/
	
	/*************************************************微信第三方登录网站应用设置************************************************/
	/**
	 * 获取微信基本配置(WCHAT)(开放平台)
	 */
	public function getWchatConfig($instance_id)
	{
		$wchat_config = Cache::tag('config')->get("wchat_config" . $instance_id);
		if (empty($wchat_config)) {
			$info = $this->config_module->getInfo([
				'key' => 'WCHAT',
				'instance_id' => $instance_id
			], 'value,is_use');
			if (empty($info)) {
				$wchat_config = array(
					'value' => array(
						'APP_KEY' => '',
						'APP_SECRET' => '',
						'AUTHORIZE' => '',
						'CALLBACK' => ''
					),
					'is_use' => 0
				);
			} else {
				$info['value'] = json_decode($info['value'], true);
				$wchat_config = $info;
			}
			Cache::tag('config')->set("wchat_config" . $instance_id, $wchat_config);
		}
		return $wchat_config;
	}
	
	/**
	 * 开放平台网站应用授权登录
	 */
	public function setWchatConfig($instance_id, $appid, $appsecret, $url, $call_back_url, $is_use)
	{
		Cache::tag('config')->set("wchat_config" . $instance_id, '');
		$info = array(
			'APP_KEY' => trim($appid),
			'APP_SECRET' => trim($appsecret),
			'AUTHORIZE' => $url,
			'CALLBACK' => $call_back_url
		);
		$value = json_encode($info);
		$count = $this->config_module->where([
			'key' => 'WCHAT',
			'instance_id' => $instance_id
		])->count();
		if ($count > 0) {
			$data = array(
				'value' => $value,
				'is_use' => $is_use,
				'modify_time' => time()
			);
			$res = $this->config_module->where([
				'key' => 'WCHAT',
				'instance_id' => $instance_id
			])->update($data);
			if ($res == 1) {
				return SUCCESS;
			} else {
				return UPDATA_FAIL;
			}
		} else {
			$data = array(
				'instance_id' => $instance_id,
				'key' => 'WCHAT',
				'value' => $value,
				'is_use' => $is_use,
				'create_time' => time()
			);
			$res = $this->config_module->save($data);
			return $res;
		}
	}
	
	public function setWchatConfigIsuse($is_use)
	{
		Cache::tag('config')->set("wchat_config" . $this->instance_id, "");
		$res = $this->config_module->save([ "is_use" => $is_use ], [ 'key' => "WCHAT" ]);
		return $res;
	}
	/*************************************************微信第三方登录网站应用设置结束*********************************************/
	
	/*************************************************QQ第三方登录网站应用设置*************************************************/
	/**
	 * qq互联登录设置
	 */
	public function setQQConfig($instance_id, $appkey, $appsecret, $url, $call_back_url, $is_use)
	{
		Cache::tag('config')->set("qq_config" . $instance_id, '');
		$info = array(
			'APP_KEY' => trim($appkey),
			'APP_SECRET' => trim($appsecret),
			'AUTHORIZE' => $url,
			'CALLBACK' => $call_back_url
		);
		$value = json_encode($info);
		$count = $this->config_module->where([
			'key' => 'QQLOGIN',
			'instance_id' => $instance_id
		])->count();
		if ($count > 0) {
			$data = array(
				'value' => $value,
				'is_use' => $is_use,
				'modify_time' => time()
			);
			$res = $this->config_module->where([
				'key' => 'QQLOGIN',
				'instance_id' => $instance_id
			])->update($data);
			if ($res == 1) {
				return SUCCESS;
			} else {
				return UPDATA_FAIL;
			}
		} else {
			$data = array(
				'instance_id' => $instance_id,
				'key' => 'QQLOGIN',
				'value' => $value,
				'is_use' => $is_use,
				'create_time' => time()
			);
			$res = $this->config_module->save($data);
			return $res;
		}
	}
	
	public function setQQConfigIsUse($is_use)
	{
		Cache::tag('config')->set("qq_config" . $this->instance_id, "");
		$res = $this->config_module->save([ "is_use" => $is_use ], [ 'key' => "QQLOGIN" ]);
		return $res;
	}
	
	/**
	 * 获取QQ互联配置(QQ)
	 */
	public function getQQConfig($instance_id)
	{
		$qq_config = Cache::tag('config')->get("qq_config" . $instance_id);
		if (empty($qq_config)) {
			$info = $this->config_module->getInfo([
				'key' => 'QQLOGIN',
				'instance_id' => $instance_id
			], 'value,is_use');
			if (empty($info['value'])) {
				$qq_config = array(
					'value' => array(
						'APP_KEY' => '',
						'APP_SECRET' => '',
						'AUTHORIZE' => '',
						'CALLBACK' => ''
					),
					'is_use' => 0
				);
			} else {
				$info['value'] = json_decode($info['value'], true);
				$qq_config = $info;
			}
			Cache::tag('config')->set("qq_config" . $instance_id, $qq_config);
		}
		return $qq_config;
	}
	
	/*************************************************QQ第三方登录设置结束****************************************************/
	
	/*************************************************系统登录设置**********************************************************/
	/**
	 * 获取登录配置信息
	 */
	public function getLoginConfig()
	{
		$wchat_config = $this->getWchatConfig($this->instance_id);
		$qq_config = $this->getQQConfig($this->instance_id);
		$mobile_config = $this->getMobileMessage($this->instance_id);
		$email_config = $this->getEmailMessage($this->instance_id);
		$data = array(
			'wchat_login_config' => $wchat_config,
			'qq_login_config' => $qq_config,
			'mobile_config' => $mobile_config,
			'email_config' => $email_config
		);
		return $data;
	}
	
	/**
	 * 获取登录验证码设置
	 */
	public function getLoginVerifyCodeConfig($instanceid)
	{
		$verify_config = Cache::tag('config')->get("LoginVerifyCodeConfig" . $instanceid);
		if (empty($verify_config)) {
			$info = $this->config_module->getInfo([
				'key' => 'LOGINVERIFYCODE',
				'instance_id' => $instanceid
			], 'value, is_use');
			if (empty($info['value'])) {
				$verify_config = array(
					'value' => array(
						'platform' => 0,
						'admin' => 0,
						'pc' => 0,
						'error_num' => 0
					),
					'is_use' => 1
				);
				Cache::set("LoginVerifyCodeConfig" . $instanceid, $verify_config);
			} else {
				$info['value'] = json_decode($info['value'], true);
				$verify_config = $info;
				Cache::tag('config')->set("LoginVerifyCodeConfig" . $instanceid, $verify_config);
			}
		}
		return $verify_config;
	}
	
	/**
	 *  设置登录验证码
	 */
	public function setLoginVerifyCodeConfig($instanceid, $platform, $admin, $pc, $error_num)
	{
		$data = array(
			'platform' => $platform,
			'admin' => $admin,
			'pc' => $pc,
			'error_num' => $error_num
		);
		$value = json_encode($data);
		$info = $this->config_module->getInfo([
			'key' => 'LOGINVERIFYCODE',
			'instance_id' => $instanceid
		], 'value');
		if (empty($info)) {
			$config_module = new ConfigModel();
			$data = array(
				'instance_id' => $instanceid,
				'key' => 'LOGINVERIFYCODE',
				'value' => $value,
				'is_use' => 1,
				'create_time' => time()
			);
			$res = $config_module->save($data);
		} else {
			$config_module = new ConfigModel();
			$data = array(
				'key' => 'LOGINVERIFYCODE',
				'value' => $value,
				'is_use' => 1,
				'modify_time' => time()
			);
			$res = $config_module->save($data, [
				'instance_id' => $instanceid,
				'key' => 'LOGINVERIFYCODE'
			]);
		}
		Cache::tag('config')->set("LoginVerifyCodeConfig" . $instanceid, '');
		return $res;
	}
	
	/*************************************************系统登录设置结束*******************************************************/
	
	/*************************************************商城热卖设置(shop)****************************************************/
	/**
	 * 商城热卖关键字设置
	 */
	public function setHotsearchConfig($instanceid, $keywords, $is_use)
	{
		Cache::tag('config')->set("getHotsearchConfig" . $instanceid, null);
		$data = $keywords;
		$value = json_encode($data);
		$info = $this->config_module->getInfo([
			'key' => 'HOTKEY',
			'instance_id' => $instanceid
		], 'value');
		$config_module = new ConfigModel();
		if (empty($info)) {
			$data = array(
				'instance_id' => $instanceid,
				'key' => 'HOTKEY',
				'value' => $value,
				'is_use' => $is_use,
				'create_time' => time()
			);
			$res = $config_module->save($data);
		} else {
			$data = array(
				'value' => $value,
				'is_use' => $is_use,
				'modify_time' => time()
			);
			$res = $config_module->save($data, [
				'instance_id' => $instanceid,
				'key' => 'HOTKEY'
			]);
		}
		return $res;
	}
	
	/**
	 * 商城热卖关键字获取
	 */
	public function getHotsearchConfig($instanceid)
	{
		$cache = Cache::tag('config')->get("getHotsearchConfig" . $instanceid);
		if (empty($cache)) {
			$info = $this->config_module->getInfo([
				'key' => 'HOTKEY',
				'instance_id' => $instanceid
			], 'value');
			if (empty($info)) {
				return null;
			} else {
				$data = json_decode($info['value'], true);
				Cache::tag('config')->set("getHotsearchConfig" . $instanceid, $data);
				return $data;
			}
		} else {
			return $cache;
		}
	}
	/*************************************************商城热卖设置(shop)结束*************************************************/
	
	/*************************************************商城默认搜索设置(shop)*************************************************/
	/**
	 * 设置默认搜索关键字
	 */
	public function setDefaultSearchConfig($instanceid, $keywords, $is_use)
	{
		Cache::tag('config')->set("getDefaultSearchConfig" . $instanceid, null);
		$data = $keywords;
		$value = json_encode($data);
		$info = $this->config_module->getInfo([
			'key' => 'DEFAULTKEY',
			'instance_id' => $instanceid
		], 'value');
		$config_module = new ConfigModel();
		if (empty($info)) {
			$data = array(
				'instance_id' => $instanceid,
				'key' => 'DEFAULTKEY',
				'value' => $value,
				'is_use' => $is_use,
				'create_time' => time()
			);
			$res = $config_module->save($data);
		} else {
			$data = array(
				'value' => $value,
				'is_use' => $is_use,
				'modify_time' => time()
			);
			$res = $config_module->save($data, [
				'instance_id' => $instanceid,
				'key' => 'DEFAULTKEY'
			]);
		}
		return $res;
	}
	
	/**
	 * 获取默认搜索关键字
	 */
	public function getDefaultSearchConfig($instanceid)
	{
		$cache = Cache::tag('config')->get("getDefaultSearchConfig" . $instanceid);
		if (empty($cache)) {
			$info = $this->config_module->getInfo([
				'key' => 'DEFAULTKEY',
				'instance_id' => $instanceid
			], 'value');
			if (empty($info)) {
				return null;
			} else {
				$data = json_decode($info['value'], true);
				Cache::tag('config')->set("getDefaultSearchConfig" . $instanceid, $data);
				return $data;
			}
		} else {
			return $cache;
		}
	}
	/*************************************************商城默认搜索设置(shop)结束**********************************************/
	
	/*************************************************商城公告设置(shop)****************************************************/
	/**
	 * 设置公告信息
	 */
	public function setUserNotice($instanceid, $keywords, $is_use)
	{
		Cache::tag('config')->set("config_getUserNotice" . $instanceid, null);
		$data = $keywords;
		$value = json_encode($data);
		$info = $this->config_module->getInfo([
			'key' => 'USERNOTICE',
			'instance_id' => $instanceid
		], 'value');
		$config_module = new ConfigModel();
		if (empty($info)) {
			$data = array(
				'instance_id' => $instanceid,
				'key' => 'USERNOTICE',
				'value' => $value,
				'is_use' => $is_use,
				'create_time' => time()
			);
			$res = $config_module->save($data);
		} else {
			$data = array(
				'value' => $value,
				'is_use' => $is_use,
				'modify_time' => time()
			);
			$res = $config_module->save($data, [
				'instance_id' => $instanceid,
				'key' => 'USERNOTICE'
			]);
		}
		return $res;
	}
	
	/**
	 * 获取公告信息
	 */
	public function getUserNotice($instanceid)
	{
		$cache = Cache::tag('config')->get("config_getUserNotice" . $instanceid);
		if (empty($cache)) {
			$info = $this->config_module->getInfo([
				'key' => 'USERNOTICE',
				'instance_id' => $instanceid
			], 'value');
			if (empty($info)) {
				return null;
			} else {
				$data = json_decode($info['value'], true);
				Cache::tag('config')->set("config_getUserNotice" . $instanceid, $data);
				return $data;
			}
		} else {
			return $cache;
		}
	}
	
	/*************************************************商城公告设置(shop)结束*************************************************/
	
	/*************************************************系统邮箱设置(通知)******************************************************/
	/**
	 * 设置邮箱配置
	 */
	public function setEmailMessage($instanceid, $email_host, $email_port, $email_addr, $email_id, $email_pass, $is_use, $email_is_security)
	{
		Cache::tag('config')->set("getEmailMessage" . $instanceid, null);
		$data = array(
			'email_host' => trim($email_host),
			'email_port' => trim($email_port),
			'email_addr' => trim($email_addr),
			'email_id' => trim($email_id),
			'email_pass' => trim($email_pass),
			'email_is_security' => $email_is_security
		);
		$value = json_encode($data);
		$info = $this->config_module->getInfo([
			'key' => 'EMAILMESSAGE',
			'instance_id' => $instanceid
		], 'value');
		$config_module = new ConfigModel();
		if (empty($info)) {
			$data = array(
				'instance_id' => $instanceid,
				'key' => 'EMAILMESSAGE',
				'value' => $value,
				'is_use' => $is_use,
				'create_time' => time()
			);
			$res = $config_module->save($data);
		} else {
			$data = array(
				'key' => 'EMAILMESSAGE',
				'value' => $value,
				'is_use' => $is_use,
				'modify_time' => time()
			);
			$res = $config_module->save($data, [
				'instance_id' => $instanceid,
				'key' => 'EMAILMESSAGE'
			]);
		}
		return $res;
	}
	
	/**
	 * 获取邮箱配置
	 */
	public function getEmailMessage($instanceid)
	{
		$cache = Cache::tag('config')->get("getEmailMessage" . $instanceid);
		if (empty($cache)) {
			$info = $this->config_module->getInfo([
				'key' => 'EMAILMESSAGE',
				'instance_id' => $instanceid
			], 'value, is_use');
			if (empty($info)) {
				$data = array(
					'value' => array(
						'email_host' => '',
						'email_port' => '',
						'email_addr' => '',
						'email_pass' => '',
						'email_id' => '',
						'email_is_security' => false
					),
					'is_use' => 0
				);
			} else {
				$info['value'] = json_decode($info['value'], true);
				$data = $info;
			}
			Cache::tag('config')->set("getEmailMessage" . $instanceid, $data);
			return $data;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 获取店铺邮件通知信息
	 */
	public function getNoticeEmailConfig($shop_id)
	{
		$config_model = new ConfigModel();
		$condition = array(
			'instance_id' => $shop_id,
			'key' => 'EMAILMESSAGE'
		);
		$email_detail = $config_model->getQuery($condition);
		return $email_detail;
	}
	
	/*************************************************系统邮箱设置结束(通知)***************************************************/
	
	/*************************************************系统短信设置(通知)(待处理)************************************************/
	/**
	 * 短信设置
	 */
	public function getMobileMessage($instanceid)
	{
		$cache = Cache::tag('config')->get("getMobileMessage" . $instanceid);
		if (empty($cache)) {
			$info = $this->config_module->getInfo([
				'key' => 'MOBILEMESSAGE',
				'instance_id' => $instanceid
			], 'value, is_use');
			if (empty($info)) {
				$data = array(
					'value' => array(
						'appKey' => '',
						'secretKey' => '',
						'freeSignName' => ''
					),
					'is_use' => $info["is_use"]
				);
			} else {
				$info['value'] = json_decode($info['value'], true);
				$data = $info;
			}
			Cache::tag('config')->set("getMobileMessage" . $instanceid, $data);
			return $data;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 得到店铺的短信配置信息
	 */
	public function getNoticeMobileConfig($shop_id)
	{
		$config_model = new ConfigModel();
		$condition = array(
			'instance_id' => $shop_id,
			'key' => 'MOBILEMESSAGE'
		);
		$mobile_detail = $config_model->getQuery($condition);
		return $mobile_detail;
	}
	
	/*************************************************系统短信设置结束(通知)(待处理)********************************************/
	
	/*************************************************系统app设置***********************************************************/
	/**
	 * 设置app设置
	 */
	public function setInstanceAppletConfig($instance_id, $appid, $appsecret)
	{
		Cache::tag('config')->set("InstanceAppletConfig" . $instance_id, '');
		$data = array(
			'appid' => trim($appid),
			'appsecret' => trim($appsecret)
		);
		$value = json_encode($data);
		$info = $this->config_module->getInfo([
			'key' => 'SHOPAPPLET',
			'instance_id' => $instance_id
		], 'value');
		$config_module = new ConfigModel();
		if (empty($info)) {
			$data = array(
				'instance_id' => $instance_id,
				'key' => 'SHOPAPPLET',
				'value' => $value,
				'is_use' => 1,
				'create_time' => time()
			);
			$res = $config_module->save($data);
		} else {
			$data = array(
				'key' => 'SHOPAPPLET',
				'value' => $value,
				'is_use' => 1,
				'modify_time' => time()
			);
			$res = $config_module->save($data, [
				'instance_id' => $instance_id,
				'key' => 'SHOPAPPLET'
			]);
		}
		return $res;
	}
	
	/**
	 * 获取app设置
	 */
	public function getInstanceAppletConfig($instance_id)
	{
		$cache = Cache::tag('config')->get("InstanceAppletConfig" . $instance_id);
		if (!empty($cache)) {
			return $cache;
		}
		$info = $this->config_module->getInfo([
			'key' => 'SHOPAPPLET',
			'instance_id' => $instance_id
		], 'value');
		if (empty($info)) {
			$info = array(
				'value' => array(
					'appid' => '',
					'appsecret' => ''
				),
				'is_use' => 1
			);
		} else {
			$info['value'] = json_decode($info['value'], true);
		}
		Cache::tag('config')->set("InstanceAppletConfig" . $instance_id, $info);
		return $info;
	}
	
	/*************************************************系统app设置结束********************************************************/
	
	/*************************************************余额支付设置(购物币暂无)**************************************************/
	/**
	 * 获取其他支付方式（余额，购物币）
	 */
	public function getOtherPayTypeConfig()
	{
		$cache = Cache::tag('config')->get("OtherPayTypeConfig");
		if (!empty($cache)) {
			return $cache;
		}
		$info = $this->config_module->getInfo([
			'key' => 'OTHER_PAY',
			'instance_id' => 0
		], 'value');
		if (empty($info)) {
			$info = array(
				'value' => array(
					'is_coin_pay' => 0,
					'is_balance_pay' => 0
				),
				'is_use' => 1
			);
		} else {
			$info['value'] = json_decode($info['value'], true);
			
		}
		Cache::tag('config')->set("OtherPayTypeConfig", $info);
		return $info;
	}
	
	/**
	 * 设置其他支付方式(余额，购物币)
	 */
	public function setOtherPayTypeConfig($is_coin_pay, $is_balance_pay)
	{
		Cache::tag('config')->set("OtherPayTypeConfig", '');
		$data = array(
			'is_coin_pay' => $is_coin_pay,
			'is_balance_pay' => $is_balance_pay
		);
		$value = json_encode($data);
		$info = $this->config_module->getInfo([
			'key' => 'OTHER_PAY',
			'instance_id' => 0
		], 'value');
		$config_module = new ConfigModel();
		if (empty($info)) {
			$data = array(
				'instance_id' => 0,
				'key' => 'OTHER_PAY',
				'value' => $value,
				'is_use' => 1,
				'create_time' => time()
			);
			$res = $config_module->save($data);
		} else {
			$data = array(
				'key' => 'OTHER_PAY',
				'value' => $value,
				'is_use' => 1,
				'modify_time' => time()
			);
			$res = $config_module->save($data, [
				'instance_id' => 0,
				'key' => 'OTHER_PAY'
			]);
		}
		return $res;
	}
	
	/*************************************************余额支付设置(购物币暂无)结束**********************************************/
	
	/*************************************************商城公告设置(shop)*****************************************************/
	/**
	 * 设置公告设置
	 */
	public function setNotice($shopid, $notice_message, $is_enable)
	{
		Cache::tag('config')->set("config_setNotice" . $shopid, null);
		$notice = new NoticeModel();
		$data = array(
			'notice_message' => $notice_message,
			'is_enable' => $is_enable
		);
		$res = $notice->save($data, [
			'shopid' => $shopid
		]);
		return $res;
	}
	
	/**
	 * 获取公告设置
	 */
	public function getNotice($shopid)
	{
		$cache = Cache::tag('config')->get("config_getNotice" . $shopid);
		if (empty($cache)) {
			$notice = new NoticeModel();
			$notice_info = $notice->getInfo([
				'shopid' => $shopid
			]);
			if (empty($notice_info)) {
				$data = array(
					'shopid' => $shopid,
					'notice_message' => '',
					'is_enable' => 0
				);
				$notice->save($data);
				$notice_info = $notice->getInfo([
					'shopid' => $shopid
				]);
			}
			Cache::tag('config')->set("config_setNotice" . $shopid, $notice_info);
			return $notice_info;
		} else {
			return $cache;
		}
	}
	/*************************************************商城公告设置结束(shop)**************************************************/
	
	/*************************************************系统设置(整体)*********************************************************/
	/**
	 * 获取配置值
	 */
	public function getConfig($instance_id, $key)
	{
		$cache = Cache::tag('config')->get("baseConfig" . $instance_id . '_' . $key);
		if (!empty($cache)) {
			return $cache;
		}
		$config = new ConfigModel();
		$info = $config->getInfo([
			'instance_id' => $instance_id,
			'key' => $key
		]);
		Cache::tag('config')->set("baseConfig" . $instance_id . '_' . $key, $info);
		return $info;
	}
	
	/**
	 * 设置配置值
	 */
	public function setConfig($params)
	{
		foreach ($params as $key => $value) {
			Cache::tag('config')->set("baseConfig" . $value['instance_id'] . '_' . $value['key'], '');
			if ($this->checkConfigKeyIsset($value['instance_id'], $value['key'])) {
				$res = $this->updateConfig($value['instance_id'], $value['key'], $value['value'], $value['desc'], $value['is_use']);
			} else {
				$res = $this->addConfig($value['instance_id'], $value['key'], $value['value'], $value['desc'], $value['is_use']);
			}
		}
		return $res;
	}
	
	/**
	 * 添加设置
	 */
	private function addConfig($instance_id, $key, $value, $desc, $is_use)
	{
		$config = new ConfigModel();
		if (is_array($value)) {
			$value = json_encode($value);
		}
		$data = array(
			'instance_id' => $instance_id,
			'key' => $key,
			'value' => $value,
			'desc' => $desc,
			'is_use' => $is_use,
			'create_time' => time()
		);
		$res = $config->save($data);
		return $res;
	}
	
	/**
	 * 修改配置
	 */
	private function updateConfig($instance_id, $key, $value, $desc, $is_use)
	{
		$config = new ConfigModel();
		if (is_array($value)) {
			$value = json_encode($value);
		}
		$data = array(
			'value' => $value,
			'desc' => $desc,
			'is_use' => $is_use,
			'modify_time' => time()
		);
		$res = $config->save($data, [
			'instance_id' => $instance_id,
			'key' => $key
		]);
		return $res;
	}
	
	/**
	 * 判断当前设置是否存在
	 * 存在返回 true 不存在返回 false
	 */
	private function checkConfigKeyIsset($instance_id, $key)
	{
		$config = new ConfigModel();
		$num = $config->where([
			'instance_id' => $instance_id,
			'key' => $key
		])->count();
		return $num > 0 ? true : false;
	}
	
	/**
	 * 修改状态
	 */
	public function updateConfigEnable($id, $is_use)
	{
		Cache::clear('config');
		$config_model = new ConfigModel();
		$data = array(
			"is_use" => $is_use,
			"modify_time" => time()
		);
		$retval = $config_model->save($data, [
			"id" => $id
		]);
		return $retval;
	}
	
	/*************************************************系统设置(整体)结束*****************************************************/
	
	/*************************************************通知系统设置**********************************************************/
	
	/**
	 * 获取店铺通知项目
	 */
	public function getNoticeConfig($shop_id)
	{
		$config_model = new ConfigModel();
		$condition = array(
			'instance_id' => $shop_id,
			'key' => array(
				'in',
				'EMAILMESSAGE,MOBILEMESSAGE'
			)
		);
		$notify_list = $config_model->getQuery($condition);
		if (!empty($notify_list)) {
			for ($i = 0; $i < count($notify_list); $i++) {
				if ($notify_list[ $i ]["key"] == "EMAILMESSAGE") {
					$notify_list[ $i ]["notify_name"] = "邮件通知";
				} else
					if ($notify_list[ $i ]["key"] == "MOBILEMESSAGE") {
						$notify_list[ $i ]["notify_name"] = "短信通知";
					}
			}
			return $notify_list;
		} else {
			return null;
		}
	}
	
	/*************************************************通知系统设置结束*******************************************************/
	
	/*************************************************提现设置*************************************************************/
	/**
	 * 支付的通知项
	 */
	public function getPayConfig()
	{
		return hook('payconfig', []);
	}
	
	/**
	 * 获取提现设置
	 */
	public function getBalanceWithdrawConfig($shop_id)
	{
		$cache = Cache::tag('config')->get("BalanceWithdrawConfig" . '_' . $shop_id);
		if (!empty($cache)) {
			return $cache;
		}
		$key = 'WITHDRAW_BALANCE';
		$info = $this->getConfig($shop_id, $key);
		if (empty($info)) {
			$params[0] = array(
				'instance_id' => $shop_id,
				'key' => $key,
				'value' => array(
					'withdraw_cash_min' => 0.00,
					'withdraw_multiple' => 0,
					'withdraw_poundage' => 0,
					'withdraw_message' => '',
					'withdraw_account' => array(
						array(
							'id' => 'bank_card',
							'name' => '银行卡',
							'value' => 1,
							'is_checked' => 1
						),
						array(
							'id' => 'wechat',
							'name' => '微信',
							'value' => 2,
							'is_checked' => 0
						),
						array(
							'id' => 'alipay',
							'name' => '支付宝',
							'value' => 3,
							'is_checked' => 0
						)
					)
				),
				'desc' => '会员余额提现设置',
				'is_use' => 0
			);
			$this->setConfig($params);
			$info = $this->getConfig($shop_id, $key);
		}
		
		if (empty($info)) {
			$info['id'] = '';
			$info['value']['withdraw_cash_min'] = '';
			$info['value']['withdraw_multiple'] = '';
			$info['value']['withdraw_poundage'] = '';
			$info['value']['withdraw_message'] = '';
			$info['value']['withdraw_account'] = array(
				array(
					'id' => 'bank_card',
					'name' => '银行卡',
					'value' => 1,
					'is_checked' => 1
				),
				array(
					'id' => 'wechat',
					'name' => '微信',
					'value' => 2,
					'is_checked' => 0
				),
				array(
					'id' => 'alipay',
					'name' => '支付宝',
					'value' => 3,
					'is_checked' => 0
				)
			);
		} else {
			$info['value'] = json_decode($info['value'], true);
		}
		Cache::tag('config')->set("BalanceWithdrawConfig" . '_' . $shop_id, $info);
		return $info;
	}
	
	/**
	 *  设置提现设置
	 */
	public function setBalanceWithdrawConfig($shop_id, $key, $value, $is_use)
	{
		Cache::tag('config')->set("BalanceWithdrawConfig" . '_' . $shop_id, '');
		$params[0] = array(
			'instance_id' => $shop_id,
			'key' => $key,
			'value' => array(
				'withdraw_cash_min' => $value['withdraw_cash_min'],
				'withdraw_multiple' => $value['withdraw_multiple'],
				'withdraw_poundage' => $value['withdraw_poundage'],
				'withdraw_message' => $value['withdraw_message'],
				'withdraw_account' => $value['withdraw_account']
			),
			'desc' => '会员余额提现设置',
			'is_use' => $is_use
		);
		$res = $this->setConfig($params);
		return $res;
	}
	/*************************************************提现设置结束*********************************************************/
	
	/*************************************************商城客服设置(shop)****************************************************/
	/**
	 * 获取客服链接设置
	 */
	public function getCustomServiceConfig($shop_id)
	{
		$cache = Cache::tag('config')->get("customserviceConfig");
		if (empty($cache)) {
			$key = 'SERVICE_ADDR';
			$info = $this->getConfig($shop_id, $key);
			if (!empty($info)) {
				if (!empty($info['value'])) {
					$info['value'] = json_decode($info['value'], true);
				} else {
					$info['value'] = [
						'meiqia_service_addr' => '',
						'kf_service_addr' => '',
						'qq_service_addr' => '',
						'checked_num' => 1
					];
				}
				
			}
			
			Cache::tag('config')->set("customserviceConfig", $info);
			return $info;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 设置客服链接
	 */
	public function setCustomServiceConfig($shop_id, $key, $value)
	{
		$params[0] = array(
			'instance_id' => $shop_id,
			'key' => $key,
			'value' => array(
				'meiqia_service_addr' => trim($value['meiqia_service_addr']),
				'kf_service_addr' => trim($value['kf_service_addr']),
				'qq_service_addr' => trim($value['qq_service_addr']),
				'checked_num' => trim($value['checked_num'])
			),
			'desc' => '客服链接地址'
		);
		$res = $this->setConfig($params);
		Cache::tag('config')->set("customserviceConfig", null);
		return $res;
	}
	
	/*************************************************商城客服设置结束(shop)************************************************/
	
	/*************************************************首页分类显示设置(shop)************************************************/
	/**
	 * 商品分类显示设置
	 */
	public function setiscategoryConfig($shop_id, $key, $value)
	{
		$params[0] = array(
			'instance_id' => $shop_id,
			'key' => $key,
			'value' => array(
				'is_category' => $value['is_category']
			),
			'desc' => '首页商品分类是否显示设置',
			'is_use' => 1
		);
		$res = $this->setConfig($params);
		return $res;
	}
	
	/**
	 * 获取商品分类显示设置
	 */
	public function getcategoryConfig($shop_id)
	{
		$key = 'IS_CATEGORY';
		$info = $this->getConfig($shop_id, $key);
		if (empty($info)) {
			$params[0] = array(
				'instance_id' => $shop_id,
				'key' => $key,
				'value' => array(
					'is_category' => ''
				),
				'desc' => '首页商品分类是否显示设置'
			);
			$this->setConfig($params);
			$info = $this->getConfig($shop_id, $key);
		}
		$info['value'] = json_decode($info['value'], true);
		return $info;
	}
	
	/**
	 * 设置手机端分类显示方式，1:缩略图模式，2：列表模式
	 */
	public function setWapCategoryDisplay($instanceid, $value)
	{
		Cache::tag('config')->set("wapCategoryDisplay" . $instanceid, '');
		$key = 'WAP_CLASSIFIED_DISPLAY_MODE';
		$config_model = new ConfigModel();
		$info = $config_model->getInfo([
			'key' => $key,
			'instance_id' => $instanceid
		], 'value');
		
		$data['value'] = $value;
		if (empty($info)) {
			$data['instance_id'] = $instanceid;
			$data['key'] = $key;
			$data['create_time'] = time();
			$data['desc'] = '手机端分类显示方式，1:缩略图模式，2：列表模式';
			$data['is_use'] = 1;
			$res = $config_model->save($data);
		} else {
			$data['modify_time'] = time();
			$res = $config_model->save($data, [
				'key' => $key,
				'instance_id' => $instanceid
			]);
		}
		return $res;
	}
	
	/**
	 * 获取手机端分类显示方式,1:缩略图模式，2：列表模式
	 */
	public function getWapCategoryDisplay($instanceid)
	{
		$cache = Cache::tag('config')->get("wapCategoryDisplay" . $instanceid);
		if (!empty($cache)) {
			return $cache;
		}
		$res = '{"template":"1","style":1,"is_img":"0"}';
		$key = 'WAP_CLASSIFIED_DISPLAY_MODE';
		$info = $this->config_module->getInfo([
			'key' => $key,
			'instance_id' => $instanceid
		], 'value');
		if (!empty($info)) {
			$res = $info['value'];
		}
		Cache::tag('config')->set("wapCategoryDisplay" . $instanceid, $res);
		return $res;
	}

	/**
	 * 获取小程序端分类显示方式
	 */
	public function getAppletCategoryDisplay($instanceid)
	{
		$cache = Cache::tag('config')->get("appletCategoryDisplay" . $instanceid);
		if (!empty($cache)) {
			return $cache;
		}
		$res = '{"template":"1","style":1,"is_img":"0"}';
		$key = 'APPLET_CLASSIFIED_DISPLAY_MODE';
		$info = $this->config_module->getInfo([
			'key' => $key,
			'instance_id' => $instanceid
		], 'value');
		if (!empty($info)) {
			$res = $info['value'];
		}
		Cache::tag('config')->set("appletCategoryDisplay" . $instanceid, $res);
		return $res;
	}

	/**
	 * 设置小程序端分类显示方式
	 */
	public function setAppletCategoryDisplay($instanceid, $value)
	{
		Cache::tag('config')->set("appletCategoryDisplay" . $instanceid, '');
		$key = 'APPLET_CLASSIFIED_DISPLAY_MODE';
		$config_model = new ConfigModel();
		$info = $config_model->getInfo([
			'key' => $key,
			'instance_id' => $instanceid
		], 'value');

		$data['value'] = $value;
		if (empty($info)) {
			$data['instance_id'] = $instanceid;
			$data['key'] = $key;
			$data['create_time'] = time();
			$data['desc'] = '小程序端分类显示方式';
			$data['is_use'] = 1;
			$res = $config_model->save($data);
		} else {

			$data['modify_time'] = time();
			$res = $config_model->save($data, [
				'key' => $key,
				'instance_id' => $instanceid
			]);
		}
		return $res;
	}
	/*************************************************首页分类显示设置结束(shop)**********************************************/
	
	/*************************************************商城seo设置(shop)*****************************************************/
	/**
	 * 获取seo设置
	 */
	public function getSeoConfig($shop_id)
	{
		$seo_config = Cache::tag('config')->get("seo_config" . $shop_id);
		if (empty($seo_config)) {
			$seo_title = $this->getConfig($shop_id, 'SEO_TITLE');
			$seo_meta = $this->getConfig($shop_id, 'SEO_META');
			$seo_desc = $this->getConfig($shop_id, 'SEO_DESC');
			$seo_other = $this->getConfig($shop_id, 'SEO_OTHER');
			if (empty($seo_title) || empty($seo_meta) || empty($seo_desc) || empty($seo_other)) {
				$this->SetSeoConfig($shop_id, '', '', '', '');
				$array = array(
					'seo_title' => '',
					'seo_meta' => '',
					'seo_desc' => '',
					'seo_other' => ''
				);
			} else {
				$array = array(
					'seo_title' => $seo_title['value'],
					'seo_meta' => $seo_meta['value'],
					'seo_desc' => $seo_desc['value'],
					'seo_other' => $seo_other['value']
				);
			}
			Cache::tag('config')->set("seo_config" . $shop_id, $array);
			$seo_config = $array;
		}
		
		return $seo_config;
	}
	
	/**
	 *  seo设置
	 */
	public function SetSeoConfig($shop_id, $seo_title, $seo_meta, $seo_desc, $seo_other)
	{
		$array[0] = array(
			'instance_id' => $shop_id,
			'key' => 'SEO_TITLE',
			'value' => $seo_title,
			'desc' => '标题附加字',
			'is_use' => 1
		);
		$array[1] = array(
			'instance_id' => $shop_id,
			'key' => 'SEO_META',
			'value' => $seo_meta,
			'desc' => '商城关键词',
			'is_use' => 1
		);
		$array[2] = array(
			'instance_id' => $shop_id,
			'key' => 'SEO_DESC',
			'value' => $seo_desc,
			'desc' => '关键词描述',
			'is_use' => 1
		);
		$array[3] = array(
			'instance_id' => $shop_id,
			'key' => 'SEO_OTHER',
			'value' => $seo_other,
			'desc' => '其他页头信息',
			'is_use' => 1
		);
		$res = $this->setConfig($array);
		Cache::tag('config')->set("seo_config" . $shop_id, '');
		return $res;
	}
	/*************************************************商城seo设置结束(shop)*************************************************/
	
	/*************************************************版权设置*************************************************************/
	/**
	 * 版权设置
	 */
	public function getCopyrightConfig($shop_id)
	{
		$copyright_logo = $this->getConfig($shop_id, 'COPYRIGHT_LOGO');
		$copyright_meta = $this->getConfig($shop_id, 'COPYRIGHT_META');
		$copyright_link = $this->getConfig($shop_id, 'COPYRIGHT_LINK');
		$copyright_desc = $this->getConfig($shop_id, 'COPYRIGHT_DESC');
		$copyright_companyname = $this->getConfig($shop_id, 'COPYRIGHT_COMPANYNAME');
		if (empty($copyright_logo) || empty($copyright_meta) || empty($copyright_link) || empty($copyright_desc) || empty($copyright_companyname)) {
			$this->SetCopyrightConfig($shop_id, '', '', '', '', '');
			$array = array(
				'copyright_logo' => '',
				'copyright_meta' => '',
				'copyright_link' => '',
				'copyright_desc' => '',
				'copyright_companyname' => ''
			);
		} else {
			$array = array(
				'copyright_logo' => $copyright_logo['value'],
				'copyright_meta' => $copyright_meta['value'],
				'copyright_link' => $copyright_link['value'],
				'copyright_desc' => $copyright_desc['value'],
				'copyright_companyname' => $copyright_companyname['value']
			);
		}
		return $array;
	}
	
	/**
	 * 版权设置
	 */
	public function SetCopyrightConfig($shop_id, $copyright_logo, $copyright_meta, $copyright_link, $copyright_desc, $copyright_companyname)
	{
		$array[0] = array(
			'instance_id' => $shop_id,
			'key' => 'COPYRIGHT_LOGO',
			'value' => $copyright_logo,
			'desc' => '版权logo',
			'is_use' => 1
		);
		$array[1] = array(
			'instance_id' => $shop_id,
			'key' => 'COPYRIGHT_META',
			'value' => $copyright_meta,
			'desc' => '备案号',
			'is_use' => 1
		);
		$array[2] = array(
			'instance_id' => $shop_id,
			'key' => 'COPYRIGHT_LINK',
			'value' => $copyright_link,
			'desc' => '版权链接',
			'is_use' => 1
		);
		$array[3] = array(
			'instance_id' => $shop_id,
			'key' => 'COPYRIGHT_DESC',
			'value' => $copyright_desc,
			'desc' => '版权信息',
			'is_use' => 1
		);
		$array[4] = array(
			'instance_id' => $shop_id,
			'key' => 'COPYRIGHT_COMPANYNAME',
			'value' => $copyright_companyname,
			'desc' => '公司名称',
			'is_use' => 1
		);
		$res = $this->setConfig($array);
		return $res;
	}
	/*************************************************版权设置结束***********************************************************/
	
	/*************************************************交易设置**************************************************************/
	/**
	 * 店铺交易设置
	 */
	public function getShopConfig($shop_id)
	{
		$order_auto_delinery = $this->getConfig($shop_id, 'ORDER_AUTO_DELIVERY');
		$order_balance_pay = $this->getConfig($shop_id, 'ORDER_BALANCE_PAY');
		$order_delivery_complete_time = $this->getConfig($shop_id, 'ORDER_DELIVERY_COMPLETE_TIME');
		$order_show_buy_record = $this->getConfig($shop_id, 'ORDER_SHOW_BUY_RECORD');
		$order_invoice_tax = $this->getConfig($shop_id, 'ORDER_INVOICE_TAX');
		$order_invoice_content = $this->getConfig($shop_id, 'ORDER_INVOICE_CONTENT');
		$order_delivery_pay = $this->getConfig($shop_id, 'ORDER_DELIVERY_PAY');
		$order_buy_close_time = $this->getConfig($shop_id, 'ORDER_BUY_CLOSE_TIME');
		$buyer_self_lifting = $this->getConfig($shop_id, 'BUYER_SELF_LIFTING');
		$seller_dispatching = $this->getConfig($shop_id, 'ORDER_SELLER_DISPATCHING');
		$is_open_o2o = $this->getConfig($shop_id, 'IS_OPEN_O2O');
		$is_logistics = $this->getConfig($shop_id, 'ORDER_IS_LOGISTICS');
		$shopping_back_points = $this->getConfig($shop_id, 'SHOPPING_BACK_POINTS');
		$is_open_virtual_goods = $this->getConfig($shop_id, 'IS_OPEN_VIRTUAL_GOODS');
		$order_designated_delivery_time = $this->getConfig($shop_id, "IS_OPEN_ORDER_DESIGNATED_DELIVERY_TIME"); // 是否开启指定配送时间
		$time_slot = $this->getConfig($shop_id, "DISTRIBUTION_TIME_SLOT"); // 配送时间时间段
		$system_default_evaluate = $this->getConfig($shop_id, "SYSTEM_DEFAULT_EVALUATE");
		$shouhou_day_number = $this->getConfig($shop_id, "SHOPHOU_DAY_NUMBER");
		$order_online_pay = $this->getConfig($shop_id, 'ORDER_ONLINE_PAY');
		$order_invoice_type = $this->getConfig($shop_id, 'ORDER_INVOICE_TYPE');
		
		if (empty($order_auto_delinery) && empty($order_balance_pay) && empty($order_delivery_complete_time) && empty($order_show_buy_record) && empty($order_invoice_tax) && empty($order_invoice_content) && empty($order_delivery_pay) && empty($order_buy_close_time) && empty($system_default_evaluate) && empty($order_invoice_type)) {
			$params = [
				'order_auto_delinery' => '',
				'order_balance_pay' => '',
				'order_delivery_complete_time' => '',
				'order_show_buy_record' => '',
				'order_invoice_tax' => '',
				'order_invoice_content' => '',
				'order_delivery_pay' => '',
				'order_buy_close_time' => '',
				'buyer_self_lifting' => '',
				'seller_dispatching' => '',
				'is_open_o2o' => '',
				'is_logistics' => '',
				'shopping_back_points' => '',
				'is_open_virtual_goods' => '',
				'order_designated_delivery_time' => '',
				'time_slot' => '',
				'evaluate_day' => 0,
				'evaluate' => '',
				'shouhoudate' => '',
			    'order_online_pay' => '',
				'order_invoice_type' => ''
			];
			$this->setShopConfig($params);
			$array = array(
				'order_auto_delinery' => '',
				'order_balance_pay' => '',
				'order_delivery_complete_time' => '',
				'order_show_buy_record' => '',
				'order_invoice_tax' => '',
				'order_invoice_content' => '',
				'order_delivery_pay' => '',
				'order_buy_close_time' => '',
				'buyer_self_lifting' => '',
				'seller_dispatching' => '',
				'is_open_o2o' => '',
				'is_logistics' => '1',
				'is_open_virtual_goods' => 0,
				'shopping_back_points' => '',
				'order_designated_delivery_time' => 0,
				'time_slot' => '',
				'system_default_evaluate' => array(
					'day' => 0,
					'evaluate' => ''
				),
				'shouhou_day_number' => 0,
			    'order_online_pay' => '',
				'order_invoice_type' => ''
			);
		} else {
			$array = array(
				'order_auto_delinery' => $order_auto_delinery['value'],
				'order_balance_pay' => $order_balance_pay['value'],
				'order_delivery_complete_time' => $order_delivery_complete_time['value'],
				'order_show_buy_record' => $order_show_buy_record['value'],
				'order_invoice_tax' => $order_invoice_tax['value'],
				'order_invoice_content' => $order_invoice_content['value'],
				'order_delivery_pay' => $order_delivery_pay['value'],
				'order_buy_close_time' => $order_buy_close_time['value'],
				'buyer_self_lifting' => $buyer_self_lifting['value'],
				'seller_dispatching' => $seller_dispatching['value'],
				'is_open_o2o' => $is_open_o2o['value'],
				'is_logistics' => $is_logistics['value'],
				'is_open_virtual_goods' => $is_open_virtual_goods['value'],
				'shopping_back_points' => $shopping_back_points['value'],
				'order_designated_delivery_time' => $order_designated_delivery_time['value'],
				'time_slot' => json_decode($time_slot['value'], true),
				'system_default_evaluate' => json_decode($system_default_evaluate['value'], true),
				'shouhou_day_number' => $shouhou_day_number['value'],
			    'order_online_pay' => $order_online_pay['value'],
				'order_invoice_type' => $order_invoice_type['value']
			);
		}
		if ($array['order_buy_close_time'] == 0) {
			$array['order_buy_close_time'] = 60;
		}
		
		return $array;
	}
	
	/**
	 * 交易设置
	 */
	public function setShopConfig($params)
	{
		$array[0] = array(
			'instance_id' => $this->instance_id,
			'key' => 'ORDER_AUTO_DELIVERY',
			'value' => $params['order_auto_delinery'],
			'desc' => '订单多长时间自动完成',
			'is_use' => 1
		);
		$array[1] = array(
			'instance_id' => $this->instance_id,
			'key' => 'ORDER_BALANCE_PAY',
			'value' => $params['order_balance_pay'],
			'desc' => '是否开启余额支付',
			'is_use' => 1
		);
		$array[2] = array(
			'instance_id' => $this->instance_id,
			'key' => 'ORDER_DELIVERY_COMPLETE_TIME',
			'value' => $params['order_delivery_complete_time'],
			'desc' => '收货后多长时间自动完成',
			'is_use' => 1
		);
		$array[3] = array(
			'instance_id' => $this->instance_id,
			'key' => 'ORDER_SHOW_BUY_RECORD',
			'value' => $params['order_show_buy_record'],
			'desc' => '是否显示购买记录',
			'is_use' => 1
		);
		$array[4] = array(
			'instance_id' => $this->instance_id,
			'key' => 'ORDER_INVOICE_TAX',
			'value' => $params['order_invoice_tax'],
			'desc' => '发票税率',
			'is_use' => 1
		);
		$array[5] = array(
			'instance_id' => $this->instance_id,
			'key' => 'ORDER_INVOICE_CONTENT',
			'value' => $params['order_invoice_content'],
			'desc' => '发票内容',
			'is_use' => 1
		);
		$array[6] = array(
			'instance_id' => $this->instance_id,
			'key' => 'ORDER_DELIVERY_PAY',
			'value' => $params['order_delivery_pay'],
			'desc' => '是否开启货到付款',
			'is_use' => 1
		);
		$array[7] = array(
			'instance_id' => $this->instance_id,
			'key' => 'ORDER_BUY_CLOSE_TIME',
			'value' => $params['order_buy_close_time'],
			'desc' => '订单自动关闭时间',
			'is_use' => 1
		);
		$array[8] = array(
			'instance_id' => $this->instance_id,
			'key' => 'BUYER_SELF_LIFTING',
			'value' => $params['buyer_self_lifting'],
			'desc' => '是否开启买家自提',
			'is_use' => 1
		);
		$array[9] = array(
			'instance_id' => $this->instance_id,
			'key' => 'ORDER_SELLER_DISPATCHING',
			'value' => $params['seller_dispatching'],
			'desc' => '是否开启物流配送',
			'is_use' => 1
		);
		$array[10] = array(
			'instance_id' => $this->instance_id,
			'key' => 'ORDER_IS_LOGISTICS',
			'value' => $params['is_logistics'],
			'desc' => '是否允许选择物流',
			'is_use' => 1
		);
		$array[11] = array(
			'instance_id' => $this->instance_id,
			'key' => 'SHOPPING_BACK_POINTS',
			'value' => $params['shopping_back_points'],
			'desc' => '购物返积分设置',
			'is_use' => 1
		);
		$array[12] = array(
			'instance_id' => $this->instance_id,
			'key' => 'IS_OPEN_VIRTUAL_GOODS',
			'value' => $params['is_open_virtual_goods'],
			'desc' => '是否开启虚拟商品',
			'is_use' => 1
		);
		$array[13] = array(
			'instance_id' => $this->instance_id,
			'key' => 'IS_OPEN_ORDER_DESIGNATED_DELIVERY_TIME',
			'value' => $params['order_designated_delivery_time'],
			'desc' => '是否开启订单指定配送时间',
			'is_use' => 1
		);
		$array[14] = array(
			'instance_id' => $this->instance_id,
			'key' => 'IS_OPEN_O2O',
			'value' => $params['is_open_o2o'],
			'desc' => '是否开启本地配送',
			'is_use' => 1
		);
		$array[15] = array(
			'instance_id' => $this->instance_id,
			'key' => 'DISTRIBUTION_TIME_SLOT',
			'value' => $params['time_slot'],
			'desc' => '配送时间时间段',
			'is_use' => 1
		);
		$array[16] = array(
			'instance_id' => $this->instance_id,
			'key' => 'SYSTEM_DEFAULT_EVALUATE',
			'value' => json_encode([
				'day' => $params['evaluate_day'],
				'evaluate' => $params['evaluate']
			]),
			'desc' => '系统默认评价',
			'is_use' => 1
		);
		$array[17] = array(
			'instance_id' => $this->instance_id,
			'key' => 'SHOPHOU_DAY_NUMBER',
			'value' => $params['shouhoudate'],
			'desc' => '可以售后的时间段',
			'is_use' => 1
		);
		$array[18] = array(
		    'instance_id' => $this->instance_id,
		    'key' => 'ORDER_ONLINE_PAY',
		    'value' => $params['order_online_pay'],
		    'desc' => '是否开启在线支付',
		    'is_use' => 1
		);
		$array[19] = array(
				'instance_id' => $this->instance_id,
				'key' => 'ORDER_INVOICE_TYPE',
				'value' => $params['invoice'],
				'desc' => '是否开启在线支付',
				'is_use' => 1
		);
		$res = $this->setConfig($array);
		return $res;
	}
	
	/*************************************************交易设置结束***********************************************************/
	
	/*************************************************注册访问设置(member)**************************************************/
	/**
	 *  注册访问设置
	 */
	public function getRegisterAndVisit($shop_id)
	{
		$register_and_visit = $this->getConfig($shop_id, 'REGISTERANDVISIT');
		if (empty($register_and_visit) || $register_and_visit == null) {
			// 按照默认值显示生成
			$value_array = array(
				'is_register' => "1",
				'register_info' => "plain",
				'name_keyword' => "",
				'pwd_len' => "5",
				'pwd_complexity' => "",
				'terms_of_service' => "",
				'is_requiretel' => 0
			);
			
			$data = array(
				'instance_id' => $shop_id,
				'key' => 'REGISTERANDVISIT',
				'value' => json_encode($value_array),
				'create_time' => time(),
				'is_use' => "1"
			);
			
			$config_model = new ConfigModel();
			$res = $config_model->save($data);
			if ($res > 0) {
				$register_and_visit = $this->getConfig($shop_id, 'REGISTERANDVISIT');
			}
		}
		return $register_and_visit;
	}
	
	/**
	 *  注册访问设置
	 */
	public function getRegisterAndVisitInfo($shop_id)
	{
		$config_info = $this->getRegisterAndVisit($shop_id);
		$reg_config = json_decode($config_info["value"], true);
		if (empty($reg_config["name_keyword"])) {
			$reg_config["name_keyword"] = "<,>,\\,/";
		} else {
			$reg_config["name_keyword"] = $reg_config["name_keyword"] . ",<,>,\\,/";
		}
		
		return $reg_config;
	}
	
	/**
	 * 注册访问设置
	 */
	public function setRegisterAndVisit($shop_id, $is_register, $register_info, $name_keyword, $pwd_len, $pwd_complexity, $terms_of_service, $is_requiretel, $is_use)
	{
		$value_array = array(
			'is_register' => $is_register,
			'register_info' => $register_info,
			'name_keyword' => $name_keyword,
			'pwd_len' => $pwd_len,
			'pwd_complexity' => $pwd_complexity,
			'is_requiretel' => $is_requiretel,
			'terms_of_service' => $terms_of_service
		);
		
		$params[0] = array(
			'instance_id' => $shop_id,
			'key' => 'REGISTERANDVISIT',
			'value' => json_encode($value_array),
			'modify_time' => time(),
			'is_use' => $is_use
		);
		$res = $this->setConfig($params);
		return $res;
	}
	
	/*************************************************注册访问设置(member)结束***********************************************/
	/**
	 * 获取数据库
	 */
	public function getDatabaseList()
	{
		$databaseList = Db::query("SHOW TABLE STATUS");
		return $databaseList;
	}
	
	/*************************************************物流跟踪设置(order)***************************************************/
	/**
	 * 查询物流跟踪的配置信息
	 */
	public function getOrderExpressMessageConfig($shop_id)
	{
		$cache = Cache::tag('config')->get('OrderExpressMessageConfig' . '_' . $shop_id);
		if (!empty($cache)) {
			return $cache;
		}
		
		$express_detail = $this->config_module->getInfo([
			'instance_id' => $shop_id,
			'key' => 'ORDER_EXPRESS_MESSAGE'
		], 'value,is_use');
		if (empty($express_detail['value'])) {
			$express_detail = array(
				'value' => array(
					'type' => 1,
					'appid' => '',
					'appkey' => '',
					'back_url' => ''
				),
				'is_use' => 0
			);
		} else {
			$express_detail['value'] = json_decode($express_detail['value'], true);
		}
		Cache::tag('config')->set('OrderExpressMessageConfig' . '_' . $shop_id, $express_detail);
		return $express_detail;
	}
	
	/**
	 * 更新物流跟踪的配置信息
	 */
	public function updateOrderExpressMessageConfig($shop_id, $appid, $appkey, $back_url, $is_use, $type, $customer)
	{
		Cache::tag('config')->set('OrderExpressMessageConfig' . '_' . $shop_id, '');
		$express_detail = $this->config_module->getInfo([
			'instance_id' => $shop_id,
			'key' => 'ORDER_EXPRESS_MESSAGE'
		], 'value,is_use');
		$value = array(
			"type" => $type,
			"appid" => trim($appid),
			"appkey" => trim($appkey),
			"back_url" => $back_url,
			"customer" => $customer
		);
		$value = json_encode($value);
		$config_model = new ConfigModel();
		if (empty($express_detail)) {
			$data = array(
				"instance_id" => $shop_id,
				"key" => 'ORDER_EXPRESS_MESSAGE',
				"value" => $value,
				"create_time" => time(),
				"modify_time" => time(),
				"desc" => "物流跟踪配置信息",
				"is_use" => $is_use
			);
			$config_model->save($data);
			return $config_model->id;
		} else {
			$data = array(
				"key" => 'ORDER_EXPRESS_MESSAGE',
				"value" => $value,
				"modify_time" => time(),
				"is_use" => $is_use
			);
			$result = $config_model->save($data, [
				"instance_id" => $shop_id,
				"key" => "ORDER_EXPRESS_MESSAGE"
			]);
			return $result;
		}
	}
	/*************************************************物流跟踪设置(order)结束************************************************/
	
	/*************************************************模板选择(shop)*******************************************************/
	/**
	 * 获取当前使用的手机模板
	 */
	public function getUseWapTemplate($instanceid)
	{
		$cache = Cache::tag('config')->get('UseWapTemplate' . '_' . $instanceid);
		if (!empty($cache)) {
			return $cache;
		}
		$config_model = new ConfigModel();
		$res = $config_model->getInfo([
			'key' => 'USE_WAP_TEMPLATE',
			'instance_id' => $instanceid
		], 'value');
		Cache::tag('config')->set('UseWapTemplate' . '_' . $instanceid, $res);
		return $res;
	}
	
	/**
	 * 设置要使用手机模板
	 */
	public function setUseWapTemplate($instanceid, $folder)
	{
		Cache::tag('config')->set('UseWapTemplate' . '_' . $instanceid, '');
		$config_model = new ConfigModel();
		$info = $this->config_module->getInfo([
			'key' => 'USE_WAP_TEMPLATE',
			'instance_id' => $instanceid
		], 'value');
		if (empty($info)) {
			$data['instance_id'] = $instanceid;
			$data['key'] = 'USE_WAP_TEMPLATE';
			$data['value'] = $folder;
			$data['create_time'] = time();
			$data['modify_time'] = time();
			$data['desc'] = '当前使用的手机端模板文件夹';
			$data['is_use'] = 1;
			$res = $config_model->save($data);
		} else {
			$data['instance_id'] = $instanceid;
			$data['value'] = $folder;
			$data['modify_time'] = time();
			$res = $config_model->save($data, [
				'key' => 'USE_WAP_TEMPLATE'
			]);
		}
		return $res;
	}
	
	/**
	 * 获取当前使用的PC端模板
	 */
	public function getUsePCTemplate($instanceid)
	{
		$user_pc_template = Cache::tag('config')->get("user_pc_template" . $instanceid);
		if (empty($user_pc_template)) {
			$config_model = new ConfigModel();
			$user_pc_template = $config_model->getInfo([
				'key' => 'USE_PC_TEMPLATE',
				'instance_id' => $instanceid
			], 'value');
			Cache::tag('config')->set("user_pc_template" . $instanceid, $user_pc_template);
		}
		return $user_pc_template;
	}
	
	/**
	 * 设置要使用的PC端模板
	 */
	public function setUsePCTemplate($instanceid, $folder)
	{
		Cache::tag('config')->set("user_pc_template" . $instanceid, '');
		$config_model = new ConfigModel();
		$info = $this->config_module->getInfo([
			'key' => 'USE_PC_TEMPLATE',
			'instance_id' => $instanceid
		], 'value');
		
		$data['instance_id'] = $instanceid;
		$data['key'] = 'USE_PC_TEMPLATE';
		$data['value'] = $folder;
		$data['create_time'] = time();
		$data['modify_time'] = time();
		if (empty($info)) {
			$data['desc'] = '当前使用的PC端模板文件夹';
			$data['is_use'] = 1;
			$res = $config_model->save($data);
		} else {
			$res = $config_model->save($data, [
				'key' => 'USE_PC_TEMPLATE'
			]);
		}
		return $res;
	}
	/*************************************************模板选择(shop)结束****************************************************/
	
	/*************************************************自提点运费(order)****************************************************/
	/**
	 * 自提点运费设置
	 */
	public function setPickupPointFreight($is_enable, $pickup_freight, $manjian_freight)
	{
		$config_value = array(
			'is_enable' => $is_enable,
			'pickup_freight' => $pickup_freight,
			'manjian_freight' => $manjian_freight
		);
		$config_key = 'PICKUPPOINT_FREIGHT';
		$config_info = $this->getConfig($this->instance_id, $config_key);
		if (empty($config_info)) {
			$res = $this->addConfig($this->instance_id, $config_key, json_encode($config_value), '自提点运费菜单配置', 1);
		} else {
			$res = $this->updateConfig($this->instance_id, $config_key, json_encode($config_value), '自提点运费菜单配置', 1);
		}
		Cache::tag('config')->set("baseConfig" . $this->instance_id . '_' . $config_key, null);
		return $res;
	}
	/*************************************************自提点运费(order)结束************************************************/
	
	/*************************************************自定义模板(shop)****************************************************/
	/**
	 * 开启关闭自定义模板
	 * @param int $is_enable 1：开启，0：禁用
	 */
	public function setIsEnableWapCustomTemplate($shop_id, $is_enable)
	{
		Cache::tag('config')->set("IsEnableWapCustomTemplate" . $shop_id, '');
		$config_model = new ConfigModel();
		$info = $this->config_module->getInfo([
			'key' => 'WAP_CUSTOM_TEMPLATE_IS_ENABLE',
			'instance_id' => $shop_id
		], 'value');
		$data['instance_id'] = $shop_id;
		$data['value'] = $is_enable;
		if (empty($info)) {
			$data['key'] = 'WAP_CUSTOM_TEMPLATE_IS_ENABLE';
			$data['is_use'] = 1;
			$data['create_time'] = time();
			$res = $config_model->save($data);
		} else {
			$data['modify_time'] = time();
			$res = $config_model->save($data, [
				'key' => 'WAP_CUSTOM_TEMPLATE_IS_ENABLE'
			]);
		}
		return $res;
	}
	
	/**
	 * 获取自定义模板是否启用，0 不启用 1 启用
	 */
	public function getIsEnableWapCustomTemplate($shop_id)
	{
		$cache = Cache::tag('config')->get("IsEnableWapCustomTemplate" . $shop_id);
		if (!empty($cache)) {
			return $cache;
		}
		$is_enable = 0;
		$config_model = new ConfigModel();
		$value = $config_model->getInfo([
			'key' => 'WAP_CUSTOM_TEMPLATE_IS_ENABLE',
			'instance_id' => $shop_id
		], 'value');
		if (!empty($value)) {
			$is_enable = $value["value"];
		}
		Cache::tag('config')->set("IsEnableWapCustomTemplate" . $shop_id, $is_enable);
		return $is_enable;
	}
	
	/**
	 * 获取格式化后的手机端自定义模板
	 */
	public function getFormatCustomTemplate($id = 0, $type = 1)
	{
		$custom_template = array();
		if ($id === 0) {
			$template_info = $this->getDefaultWapCustomTemplate($type);
		} else {
			$template_info = $this->getWapCustomTemplateById($id);
		}
		if (!empty($template_info)) {
			$goods = new Goods();
			$custom_template_info = json_decode($template_info["template_data"], true);
			foreach ($custom_template_info as $k => $v) {
				$custom_template_info[ $k ]["style_data"] = json_decode($v["control_data"], true);
			}
			// 给数组排序
			$sort = array(
				'direction' => 'SORT_ASC', // 排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
				'field' => 'sort'
			);
			$arrSort = array();
			foreach ($custom_template_info as $uniqid => $row) {
				foreach ($row as $key => $value) {
					$arrSort[ $key ][ $uniqid ] = $value;
				}
			}
			if ($sort['direction']) {
				array_multisort($arrSort[ $sort['field'] ], constant($sort['direction']), $custom_template_info);
			}
			foreach ($custom_template_info as $k => $v) {
				
				if ($v['control_name'] == "GoodsSearch") {
					
					// 商品搜索
					$custom_template_info[ $k ]["style_data"]['goods_search'] = json_decode($v["style_data"]['goods_search'], true);
				} elseif ($v["control_name"] == "GoodsList") {
					
					// 商品列表
					$custom_template_info[ $k ]["style_data"]['goods_list'] = json_decode($v["style_data"]['goods_list'], true);
					if ($custom_template_info[ $k ]["style_data"]['goods_list']["goods_source"] > 0) {
						
						$goods_list = $goods->getGoodsListNew(1, $custom_template_info[ $k ]["style_data"]['goods_list']["goods_limit_count"], [
							"ng.category_id" => $custom_template_info[ $k ]["style_data"]['goods_list']["goods_source"],
							"ng.state" => 1
						], "ng.sort desc,ng.create_time desc");
						$goods_query = array();
						if (!empty($goods_list)) {
							$goods_query = $goods_list["data"];
						}
						$custom_template_info[ $k ]["goods_list"] = $goods_query;
					}
				} elseif ($v["control_name"] == "ImgAd") {
					
					// 图片广告
					if (trim($v["style_data"]["img_ad"]) != "") {
						$custom_template_info[ $k ]["style_data"]["img_ad"] = json_decode($v["style_data"]["img_ad"], true);
					} else {
						$custom_template_info[ $k ]["style_data"]["img_ad"] = array();
					}
				} elseif ($v["control_name"] == "NavHyBrid") {
					
					$custom_template_info[ $k ]["style_data"]["nav_hybrid"] = json_decode($v["style_data"]["nav_hybrid"], true);
				} elseif ($v["control_name"] == "GoodsClassify") {
					
					// 商品分类
					if (trim($v["style_data"]["goods_classify"]) != "") {
						$category = new GoodsCategory();
						$category_array = json_decode($v["style_data"]["goods_classify"], true);
						foreach ($category_array as $t => $m) {
							$category_info = $category->getGoodsCategoryDetail($m["id"]);
							$category_array[ $t ]["name"] = $category_info["short_name"];
							$goods_list = $goods->getGoodsListNew(1, $m["show_count"], [
								"ng.category_id" => $m["id"],
								"ng.state" => 1
							], "ng.sort desc,ng.create_time desc");
							$category_array[ $t ]["goods_list"] = $goods_list["data"];
						}
						$custom_template_info[ $k ]["style_data"]["goods_classify"] = $category_array;
					} else {
						$custom_template_info[ $k ]["style_data"]["goods_classify"] = array();
					}
				} elseif ($v["control_name"] == "Footer") {
					
					// 底部菜单
					if (trim($v["style_data"]["footer"]) != "") {
						$custom_template_info[ $k ]["style_data"]["footer"] = json_decode($v["style_data"]["footer"], true);
					} else {
						$custom_template_info[ $k ]["style_data"]["footer"] = array();
					}
				} elseif ($v["control_name"] == "CustomModule") {
					
					// 自定义模块
					$custom_module = json_decode($v["style_data"]['custom_module'], true);
					
					$custom_module_list = $this->getFormatCustomTemplate($custom_module['module_id']);
					if (!empty($custom_module_list)) {
						for ($i = 0; $i < count($custom_module_list['template_data']); $i++) {
							
							array_push($custom_template_info, $custom_module_list['template_data'][ $i ]);
						}
					}
				} elseif ($v["control_name"] == "Coupons") {
					
					// 优惠券
					$custom_template_info[ $k ]["style_data"]['coupons'] = json_decode($v["style_data"]['coupons'], true);
				} elseif ($v["control_name"] == "Video") {
					
					// 视频
					$custom_template_info[ $k ]["style_data"]['video'] = json_decode($v["style_data"]['video'], true);
				} elseif ($v["control_name"] == "ShowCase") {
					
					// 橱窗
					$custom_template_info[ $k ]["style_data"]['show_case'] = json_decode($v["style_data"]['show_case'], true);
				} elseif ($v['control_name'] == "Notice") {
					
					// 公告
					$custom_template_info[ $k ]['style_data']['notice'] = json_decode($v['style_data']['notice'], true);
				} elseif ($v['control_name'] == "TextNavigation") {
					
					// 文本导航
					$custom_template_info[ $k ]['style_data']['text_navigation'] = json_decode($v['style_data']['text_navigation'], true);
				} elseif ($v['control_name'] == "Title") {
					
					// 标题
					$custom_template_info[ $k ]['style_data']['title'] = json_decode($v['style_data']['title'], true);
				} elseif ($v['control_name'] == "AuxiliaryLine") {
					
					// 辅助线
					$custom_template_info[ $k ]['style_data']['auxiliary_line'] = json_decode($v['style_data']['auxiliary_line'], true);
				} elseif ($v['control_name'] == "AuxiliaryBlank") {
					
					// 辅助空白
					$custom_template_info[ $k ]['style_data']['auxiliary_blank'] = json_decode($v['style_data']['auxiliary_blank'], true);
				}
			}
			$custom_template["template_name"] = $template_info["template_name"];
			$custom_template["template_data"] = $custom_template_info;
		}
		return $custom_template;
	}
	
	/**
	 * 获取手机端自定义模板列表
	 */
	public function getWapCustomTemplateList($page_index = 1, $page_size = 0, $condition = '', $order = 'id desc', $field = '*')
	{
		$data = [ $page_index, $page_size, $condition, $order, $field ];
		$data = json_encode($data);
		$cache = Cache::tag('wap_custom_template')->get("getWapCustomTemplateList" . $data);
		if (!empty($cache)) {
			return $cache;
		}
		$model = new SysWapCustomTemplateModel();
		$list = $model->pageQuery($page_index, $page_size, $condition, $order, $field);
		Cache::tag('wap_custom_template')->set("getWapCustomTemplateList" . $data, $list);
		return $list;
	}
	
	/**
	 * 根据主键id删除手机端自定义模板
	 */
	public function deleteWapCustomTemplateById($id)
	{
		Cache::clear('wap_custom_template');
		$model = new SysWapCustomTemplateModel();
		$res = $model->destroy([
			"id" => [ "in", $id ]
		]);
		return $res;
	}

	/**
	 * 设置默认手机自定义模板
	 */
	public function setDefaultWapCustomTemplate($id, $type = 1)
	{
		Cache::clear('wap_custom_template');
		$model = new SysWapCustomTemplateModel();
		$model->save([
			"is_default" => 0
		], [
			"id" => array(
				'NEQ',
				$id
			)
		]);

		$res = $model->save([
			"is_default" => 1,
			"modify_time" => time()
		], [
			"id" => $id
		]);
		return $res;
	}
	
	/**
	 * 根据id获取手机端自定义模板
	 */
	public function getWapCustomTemplateById($id)
	{
		$cache = Cache::tag('wap_custom_template')->get("getWapCustomTemplateById" . $id);
		if (!empty($cache)) {
			return $cache;
		}
		$model = new SysWapCustomTemplateModel();
		$res = $model->getInfo([
			'id' => $id
		]);
		Cache::tag('wap_custom_template')->set("getWapCustomTemplateById" . $id, $res);
		return $res;
	}
	
	/**
	 * 编辑手机端自定义模板
	 */
	public function editWapCustomTemplate($id, $template_name, $template_data)
	{
		Cache::clear('wap_custom_template');
		$data['shop_id'] = $this->instance_id;
		$data['template_name'] = $template_name;
		$data['template_data'] = $template_data;
		$data['modify_time'] = time();
		$data['create_time'] = time();
		$model = new SysWapCustomTemplateModel();
		if ($id == 0) {
			// 添加
			$default_custom_template = $this->getDefaultWapCustomTemplate();
			if (empty($default_custom_template)) {
				$data['is_default'] = 1;
			}
			$res = $model->save($data);
		} else {
			$res = $model->save($data, [
				'id' => $id
			]);
		}
		return $res;
	}
	
	/**
	 * 获取默认手机端自定义模板
	 */
	public function getDefaultWapCustomTemplate($type = 1)
	{
		$cache = Cache::tag('wap_custom_template')->get("getDefaultWapCustomTemplate");
		if (!empty($cache)) {
			return $cache;
		}
		$model = new SysWapCustomTemplateModel();
		$res = $default_custom_template = $model->getInfo([
			"shop_id" => $this->instance_id,
			"is_default" => 1,
            'type' => $type
		]);
		Cache::tag('wap_custom_template')->set("getDefaultWapCustomTemplate", $res);
		return $res;
	}
	
	/*************************************************自定义模板(shop)结束**************************************************/
	
	/*************************************************上传设置*************************************************************/
	/**
	 * 获取上传类型
	 */
	public function getUploadType($shop_id)
	{
		$cache = Cache::tag('config')->get('UploadType' . $shop_id);
		if (!empty($cache)) {
			return $cache;
		}
		$upload_type = $this->config_module->getInfo([
			"key" => "UPLOAD_TYPE",
			"instance_id" => $shop_id
		], "*");
		if (empty($upload_type)) {
			$res = $this->addConfig($shop_id, "UPLOAD_TYPE", 1, "上传方式 1 本地  2 七牛", 1);
			$value = 1;
		} else {
			$value = $upload_type['value'];
		}
		Cache::tag('config')->set('UploadType' . $shop_id, $value);
		return $value;
	}
	
	/**
	 * 七牛云上传设置
	 */
	public function getQiniuConfig($shop_id)
	{
		$cache = Cache::tag('config')->get('QiniuConfig' . $shop_id);
		if (!empty($cache)) {
			return $cache;
		}
		$qiniu_info = $this->config_module->getInfo([
			"key" => "QINIU_CONFIG",
			"instance_id" => $shop_id
		], "*");
		if (empty($qiniu_info)) {
			$data = array(
				"Accesskey" => "",
				"Secretkey" => "",
				"Bucket" => "",
				"QiniuUrl" => ""
			);
			$res = $this->addConfig($shop_id, "QINIU_CONFIG", json_encode($data), "七牛云存储参数配置", 1);
			if (!$res > 0) {
				return null;
			} else {
				$qiniu_info = $this->config_module->getInfo([
					"key" => "QINIU_CONFIG",
					"instance_id" => $shop_id
				], "*");
			}
		}
		$value = json_decode($qiniu_info["value"], true);
		Cache::tag('config')->set('QiniuConfig' . $shop_id, $value);
		return $value;
	}
	
	/**
	 * 设置上传类型
	 */
	public function setUploadType($shop_id, $value)
	{
		Cache::tag('config')->set('UploadType' . $shop_id, '');
		$upload_info = $this->config_module->getInfo([
			"key" => "UPLOAD_TYPE",
			"instance_id" => $shop_id
		], "*");
		if (!empty($upload_info)) {
			$data = array(
				"value" => $value
			);
			$res = $this->config_module->save($data, [
				"instance_id" => $shop_id,
				"key" => "UPLOAD_TYPE"
			]);
		} else {
			$res = $this->addConfig($shop_id, "UPLOAD_TYPE", $value, "上传方式 1 本地  2 七牛", 1);
		}
		return $res;
	}
	
	/**
	 * 设置七牛云上传
	 */
	public function setQiniuConfig($shop_id, $value)
	{
		Cache::tag('config')->set('QiniuConfig' . $shop_id, '');
		$qiniu_info = $this->config_module->getInfo([
			"key" => "QINIU_CONFIG",
			"instance_id" => $shop_id
		], "*");
		if (empty($qiniu_info)) {
			$res = $this->addConfig($shop_id, "QINIU_CONFIG", $value, "七牛云存储参数配置", 1);
		} else {
			$data = array(
				"value" => $value
			);
			$res = $this->config_module->save($data, [
				"key" => "QINIU_CONFIG",
				"instance_id" => $shop_id
			]);
		}
		return $res;
	}
	
	/**
	 * 图片上传相关信息设置
	 * @param int $shop_id
	 * @return NULL|mixed
	 */
	public function getPictureUploadSetting($shop_id)
	{
		$cache = Cache::tag('config')->get('PictureUploadSetting' . $shop_id);
		if (!empty($cache)) {
			return $cache;
		}
		$info = $this->config_module->getInfo([
			"key" => "IMG_THUMB",
			"instance_id" => $shop_id
		], "*");
		if (empty($info)) {
			$data = array(
				"thumb_type" => "2",
				"upload_size" => "0",
				"upload_ext" => "gif,jpg,jpeg,bmp,png"
			);
			$res = $this->addConfig($shop_id, "IMG_THUMB", json_encode($data), "thumb_type(缩略)  3 居中裁剪 2 缩放后填充 4 左上角裁剪 5 右下角裁剪 6 固定尺寸缩放", 1);
			if (!$res > 0) {
				return null;
			} else {
				$info = $this->config_module->getInfo([
					"key" => "IMG_THUMB",
					"instance_id" => $shop_id
				], "*");
			}
		}
		$value = json_decode($info["value"], true);
		Cache::tag('config')->set('PictureUploadSetting' . $shop_id, $value);
		return $value;
	}
	
	/**
	 * 设置如偏上传信息
	 */
	public function setPictureUploadSetting($shop_id, $value)
	{
		Cache::tag('config')->set('PictureUploadSetting' . $shop_id, '');
		$info = $this->config_module->getInfo([
			"key" => "IMG_THUMB",
			"instance_id" => $shop_id
		], "*");
		if (!empty($info)) {
			$data = array(
				"value" => $value
			);
			$res = $this->config_module->save($data, [
				"instance_id" => $shop_id,
				"key" => "IMG_THUMB"
			]);
		} else {
			$res = $this->addConfig($shop_id, "IMG_THUMB", $value, "图片生成参数配置  thumb_type(缩略)  3 居中裁剪 2 缩放后填充 4 左上角裁剪 5 右下角裁剪 6 固定尺寸缩放 ", 1);
		}
		return $res;
	}
	
	/**
	 * 设置默认图片
	 */
	public function setDefaultImages($shop_id, $value)
	{
		Cache::tag('config')->set("getDefaultImages" . $shop_id, '');
		$default_image = $this->config_module->getInfo([
			"key" => "DEFAULT_IMAGE",
			"instance_id" => $shop_id
		], "*");
		if (!empty($default_image)) {
			$data = array(
				"value" => $value
			);
			$res = $this->config_module->save($data, [
				"instance_id" => $shop_id,
				"key" => "DEFAULT_IMAGE"
			]);
		} else {
			$res = $this->addConfig($shop_id, "DEFAULT_IMAGE", $value, "默认图片", 1);
		}
		
		return $res;
	}
	
	/**
	 * 获取默认图片
	 */
	public function getDefaultImages($instanceid)
	{
		$cache = Cache::tag('config')->get("getDefaultImages" . $instanceid);
		if (empty($cache)) {
			$info = $this->config_module->getInfo([
				'key' => 'DEFAULT_IMAGE',
				'instance_id' => $instanceid
			], 'value, is_use');
			if (empty($info['value'])) {
				$data = array(
					'value' => array(
						"default_goods_img" => "",
						"default_headimg" => "",
						"default_cms_thumbnail" => ""
					),
					'is_use' => 0
				);
			} else {
				$info['value'] = json_decode($info['value'], true);
				$data = $info;
			}
			Cache::tag('config')->set("getDefaultImages" . $instanceid, $data);
			return $data;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 设置图片水印
	 */
	public function setPictureWatermark($shop_id, $value)
	{
		Cache::tag('config')->set("PictureWatermark" . $shop_id, "");
		$water_info = $this->config_module->getInfo([
			"key" => "WATER_CONFIG",
			"instance_id" => $shop_id
		], "*");
		if (empty($water_info)) {
			$data = array(
				"watermark" => "",
				"transparency" => "",
				"waterPosition" => "",
				"imgWatermark" => ""
			);
			$res = $this->addConfig($shop_id, "WATER_CONFIG", json_encode($data), "图片水印参数配置", 1);
		} else {
			$data = array(
				"value" => $value
			);
			$res = $this->config_module->save($data, [
				"key" => "WATER_CONFIG",
				"instance_id" => $shop_id
			]);
		}
		return $res;
	}
	
	/**
	 * 获取水印配置
	 */
	public function getWatermarkConfig($instanceid)
	{
		$cache = Cache::tag('config')->get("PictureWatermark" . $instanceid);
		if (!empty($cache)) {
			return $cache;
		}
		$water_info = $this->config_module->getInfo([
			"key" => "WATER_CONFIG",
			"instance_id" => $instanceid
		], "*");
		if (empty($water_info)) {
			$data = array(
				"watermark" => "",
				"transparency" => "100",
				"waterPosition" => "9", // 默认水印在右下角
				"imgWatermark" => ""
			);
			$res = $this->addConfig($instanceid, "WATER_CONFIG", json_encode($data), "图片水印参数配置", 1);
			if (!$res > 0) {
				return null;
			} else {
				$water_info = $this->config_module->getInfo([
					"key" => "WATER_CONFIG",
					"instance_id" => $instanceid
				], "*");
			}
		}
		$value = json_decode($water_info["value"], true);
		Cache::tag('config')->set("PictureWatermark" . $instanceid, $value);
		return $value;
	}
	/*************************************************上传设置结束***********************************************************/
	
	/*************************************************支付相关设置(pay)******************************************************/
	
	
	/*************************************************支付相关设置(pay)结束**************************************************/
	
	/*************************************************虚拟商品设置(goods)***************************************************/
	/**
	 * 获取是否开启虚拟商品配置信息 0:禁用，1:开启
	 */
	public function getIsOpenVirtualGoodsConfig($shop_id = 0)
	{
		$info = $this->config_module->getInfo([
			'key' => 'IS_OPEN_VIRTUAL_GOODS',
			'instance_id' => $shop_id
		], 'value');
		if (!empty($info)) {
			return $info['value'];
		} else {
			return 0;
		}
	}
	/*************************************************虚拟商品设置(goods)结束************************************************/
	
	/*************************************************商家服务设置(shop)****************************************************/
	/**
	 * 设置商家服务，固定4个
	 */
	public function setMerchantServiceConfig($instance_id, $value)
	{
		Cache::tag('config')->set("MerchantServiceConfig" . $instance_id, '');
		Cache::tag('config')->set("ExistingMerchantService" . $instance_id, '');
		$config_module = new ConfigModel();
		$info = $config_module->getInfo([
			'instance_id' => $instance_id,
			'key' => 'MERCHANT_SERVICE'
		], "value");
		
		$data = array(
			'key' => 'MERCHANT_SERVICE',
			'instance_id' => $instance_id,
			'value' => $value,
			'is_use' => 1,
			'desc' => '商家服务',
			'modify_time' => time()
		);
		if (empty($info)) {
			
			$res = $config_module->save($data);
		} else {
			
			$res = $config_module->save($data, [
				'instance_id' => $instance_id,
				'key' => 'MERCHANT_SERVICE'
			]);
		}
		return $res;
	}
	
	/**
	 * 获取商家服务，固定4个
	 */
	public function getMerchantServiceConfig($instance_id)
	{
		$cache = Cache::tag('config')->get("MerchantServiceConfig" . $instance_id);
		if (!empty($cache)) {
			return $cache;
		}
		
		$config_module = new ConfigModel();
		$info = $config_module->getInfo([
			'instance_id' => $instance_id,
			'key' => 'MERCHANT_SERVICE'
		], "value");
		$res = array();
		if (!empty($info['value'])) {
			$res = json_decode($info['value'], true);
		}
		
		Cache::tag('config')->set("MerchantServiceConfig" . $instance_id, $res);
		return $res;
	}
	
	/**
	 * 获取已经存在的商家服务，排除空
	 */
	public function getExistingMerchantService($instance_id)
	{
		$cache = Cache::tag('config')->get("ExistingMerchantService" . $instance_id);
		if (!empty($cache)) {
			return $cache;
		}
		$model = new ConfigModel();
		$info = $model->getInfo([
			'instance_id' => $instance_id,
			'key' => 'MERCHANT_SERVICE'
		], "value");
		if (!empty($info)) {
			$info['value'] = json_decode($info['value'], true);
			if (!empty($info['value'])) {
				foreach ($info['value'] as $k => $v) {
					if (empty($v['title'])) {
						unset($info['value'][ $k ]);
					}
				}
			}
			Cache::tag('config')->set("ExistingMerchantService" . $instance_id, $info['value']);
			return $info['value'];
		}
	}
	/*************************************************商家服务设置(shop)结束*************************************************/
	
	/*************************************************系统交易配送时间(order)*************************************************/
	
	/**
	 * 设置本地配送时间设置
	 */
	function setDistributionTimeConfig($value)
	{
		Cache::tag('config')->set("DistributionTimeConfig" . $this->instance_id, null);
		$time_info = $this->config_module->getCount([
			"key" => "DISTRIBUTION_TIME_CONFIG",
			"instance_id" => $this->instance_id
		]);
		if ($time_info == 0) {
			$res = $this->addConfig($this->instance_id, "DISTRIBUTION_TIME_CONFIG", $value, "本地配送时间设置", 1);
			return $res;
		} else {
			$res = $this->config_module->save([
				'value' => $value
			], [
				"key" => "DISTRIBUTION_TIME_CONFIG",
				"instance_id" => $this->instance_id
			]);
			return $res;
		}
	}
	
	/**
	 * 获取本地配送时间设置
	 */
	function getDistributionTimeConfig($instanceid)
	{
		$cache = Cache::tag('config')->get("DistributionTimeConfig" . $instanceid);
		if (!empty($cache)) {
			return $cache;
		}
		$time_info = $this->config_module->getInfo([
			"key" => "DISTRIBUTION_TIME_CONFIG",
			"instance_id" => $instanceid
		], "*");
		
		if (empty($time_info)) {
			return 0;
		}
		Cache::tag('config')->set("DistributionTimeConfig" . $instanceid, $time_info);
		return $time_info;
	}
	
	/*************************************************系统交易配送时间(order)*************************************************/
	
	/*************************************************系统后台菜单***********************************************************/
	/**
	 * 设置快捷菜单
	 */
	function setShortcutMenu($shop_id, $uid, $menu_ids)
	{
		Cache::tag('config')->set("ShortcutMenu" . $shop_id, '');
		$model = new SysShortcutMenuModel();
		// 删除原先的
		$model->destroy([
			'shop_id' => $shop_id,
			'uid' => $uid
		]);
		// 添加新的
		$add_arr = explode(',', $menu_ids);
		foreach ($add_arr as $key => $val) {
			$model = new SysShortcutMenuModel();
			$data = [
				'shop_id' => $shop_id,
				'uid' => $uid,
				'module_id' => $val
			];
			
			$add_res = $model->save($data);
		}
		return $add_res;
	}
	
	/**
	 * 获取快捷菜单
	 */
	function getShortcutMenu()
	{
		$cache = Cache::tag('config')->get("ShortcutMenu" . $this->instance_id);
		if (!empty($cache)) {
			return $cache;
		}
		$model = new SysShortcutMenuModel();
		$list = $model->getViewList(1, 0, '', '');
		Cache::tag('config')->set("ShortcutMenu" . $this->instance_id, $list);
		return $list;
	}
	/*************************************************系统后台菜单结束******************************************************/
	
	/*************************************************系统app升级设置******************************************************/
	/**
	 * 获取App升级列表
	 */
	function getAppUpgradeList($page_index = 1, $page_size = 0, $condition = "", $order = "create_time desc", $field = "*")
	{
		$model = new NsAppUpgradeModel();
		$res = $model->pageQuery($page_index, $page_size, $condition, $order, $field);
		return $res;
	}
	
	/**
	 * 添加App升级
	 * @param int $id 主键id
	 * @param string $app_type App类型，Android，IOS
	 * @param string $version_number 版本号
	 * @param string $download_address app下载地址
	 * @param string $update_log 更新日志
	 * @return int
	 */
	function editAppUpgrade($id, $title, $app_type, $version_number, $download_address, $update_log)
	{
		$model = new NsAppUpgradeModel();
		$data = array();
		$data['title'] = $title;
		$data['app_type'] = $app_type;
		$data['version_number'] = $version_number;
		$data['download_address'] = $download_address;
		$data['update_log'] = $update_log;
		if ($id == 0) {
			$data['create_time'] = time();
			$res = $model->save($data);
		} else {
			$data['update_time'] = time();
			$res = $model->save($data, [ 'id' => $id ]);
		}
		return $res;
	}
	
	/**
	 * 删除App升级
	 * @param int $id 主键id，多个逗号隔开
	 * @return int
	 */
	function deleteAppUpgrade($id)
	{
		$model = new NsAppUpgradeModel();
		$data['id'] = [ "in", $id ];
		$res = $model->destroy($data);
		return $res;
	}
	
	/**
	 * 根据主键id查询App升级信息
	 */
	function getAppUpgradeInfo($id)
	{
		$model = new NsAppUpgradeModel();
		$res = $model->getInfo([ 'id' => $id ]);
		return $res;
	}
	
	/**
	 * 获取最新版App信息
	 * @param string $app_type App类型，Android，IOS
	 * @return array
	 */
	function getLatestAppVersionInfo($app_type)
	{
		$model = new NsAppUpgradeModel();
		$res = $model->getFirstData([
			'app_type' => $app_type
		], "id desc");
		return $res;
	}
	
	/**
	 * App欢迎页配置
	 */
	public function setAppWelcomePageConfig($shop_id, $value)
	{
		$key = "APP_WELCOME_PAGE_CONFIG";
		$params[0] = array(
			'instance_id' => $shop_id,
			'key' => $key,
			'value' => $value,
			'desc' => 'App欢迎页配置',
			'is_use' => 1
		);
		$res = $this->setConfig($params);
		return $res;
	}
	
	/**
	 * 获取App欢迎页配置
	 */
	public function getAppWelcomePageConfig($shop_id)
	{
		$key = "APP_WELCOME_PAGE_CONFIG";
		$info = $this->getConfig($shop_id, $key);
		if (empty($info)) {
			$value = array(
				'residence_time' => 5,
				'jump_link' => '',
				'welcome_page_picture' => '',
				'goods_id' => 0
			);
			$params[0] = array(
				'instance_id' => $shop_id,
				'key' => $key,
				'value' => json_encode($value),
				'desc' => 'App欢迎页配置'
			);
			$this->setConfig($params);
			$info = $this->getConfig($shop_id, $key);
		}
		$info['value'] = json_decode($info['value'], true);
		return $info;
	}
	/*************************************************系统app结束**********************************************************/
	
	/*************************************************手机端排版(shop)*****************************************************/
	/**
	 * 获取手机端首页排版
	 */
	public function getWapPageLayoutConfig($instanceid)
	{
		$cache = Cache::tag('config')->get("getWapPageLayoutConfig" . $instanceid);
		if (empty($cache)) {
			$info = $this->config_module->getInfo([
				'key' => 'WAP_PAGE_LAYOUT',
				'instance_id' => $instanceid
			], 'value');
			if (empty($info)) {
				return null;
			} else {
				$data = json_decode($info['value'], true);
				Cache::tag('config')->set("getWapPageLayoutConfig" . $instanceid, $data);
				return $data;
			}
		} else {
			return $cache;
		}
	}
	
	/**
	 * 设置手机端首页排版
	 */
	public function setWapPageLayoutConfig($instanceid, $data, $is_use)
	{
		Cache::tag('config')->set("getWapPageLayoutConfig" . $instanceid, null);
		$value = $data;
		$info = $this->config_module->getInfo([
			'key' => 'WAP_PAGE_LAYOUT',
			'instance_id' => $instanceid
		], 'value');
		$config_module = new ConfigModel();
		if (empty($info)) {
			$data = array(
				'instance_id' => $instanceid,
				'key' => 'WAP_PAGE_LAYOUT',
				'value' => $value,
				'is_use' => $is_use,
				'desc' => '手机端首页排版',
				'create_time' => time()
			);
			$res = $config_module->save($data);
		} else {
			$data = array(
				'value' => $value,
				'is_use' => $is_use,
				'modify_time' => time()
			);
			$res = $config_module->save($data, [
				'instance_id' => $instanceid,
				'key' => 'WAP_PAGE_LAYOUT'
			]);
		}
		return $res;
	}
	
	/**
	 * 获取手机端首页魔方配置
	 */
	public function getWapHomeMagicCubeConfig($instanceid = 0)
	{
		$cache = Cache::tag('config')->get("getWapHomeMagicCubeConfig" . $instanceid);
		if (empty($cache)) {
			$info = $this->config_module->getInfo([
				'key' => 'WAP_HOME_MAGIC_CUBE',
				'instance_id' => $instanceid
			], 'value');
			if (empty($info)) {
				return null;
			} else {
				$data = json_decode($info['value'], true);
				Cache::tag('config')->set("getWapHomeMagicCubeConfig" . $instanceid, $data);
				return $data;
			}
		} else {
			return $cache;
		}
	}
	
	/**
	 * 设置手机端首页魔方
	 */
	public function setWapHomeMagicCube($value, $is_use = 1, $instanceid = 0)
	{
		Cache::tag('config')->set("getWapHomeMagicCubeConfig" . $instanceid, null);
		$info = $this->config_module->getInfo([
			'key' => 'WAP_HOME_MAGIC_CUBE',
			'instance_id' => $instanceid
		], 'value');
		$data = array(
			'instance_id' => $instanceid,
			'key' => 'WAP_HOME_MAGIC_CUBE',
			'value' => $value,
			'is_use' => $is_use,
			'desc' => '手机端首页魔方',
		);
		if (empty($info)) {
			$data['create_time'] = time();
			$data['desc'] = '手机端首页魔方';
			$res = $this->config_module->save($data);
		} else {
			$data['modify_time'] = time();
			$res = $this->config_module->save($data, [
				'instance_id' => $instanceid,
				'key' => 'WAP_HOME_MAGIC_CUBE'
			]);
		}
		return $res;
	}
	
	/**
	 * 手机端分类
	 */
	public function setWapBottomType($data)
	{
		$model = new SysWapBlockTempModel();
		$data['name'] = 'WAP_BOTTOM_TYPE';
		$info = $model->getInfo([ 'name' => 'WAP_BOTTOM_TYPE', 'shop_id' => $this->instance_id ]);
		if (!empty($info)) {
			$condition['name'] = 'WAP_BOTTOM_TYPE';
			$data['modify_time'] = time();
			$res = $model->save($data, $condition);
		} else {
			$data['create_time'] = time();
			$data['shop_id'] = $this->instance_id;
			$res = $model->save($data);
		}
		Cache::tag('config')->set("wap_bottom_type" . $this->instance_id, null);
		return $res;
	}
	
	/**
	 * 手机端分类
	 */
	public function getWapBottomType($shop_id)
	{
		$info = Cache::tag('config')->get("wap_bottom_type" . $shop_id);
		if (empty($info)) {
			$model = new SysWapBlockTempModel();
			$info = $model->getInfo([ 'shop_id' => $shop_id, 'name' => 'WAP_BOTTOM_TYPE' ]);
			Cache::tag('config')->set("wap_bottom_type" . $shop_id, $info);
		}
		return $info;
	}
	
	/**
	 * 设置WAP端页面配色方案
	 */
	public function setWebSiteColorScheme($flag, $value)
	{
		Cache::tag('config')->set("getWebSiteColorScheme" . $this->instance_id . "_" . $flag, '');
		if ($flag == "wap") {
			$key = 'WAP_COLOR_SCHEME';
		} elseif ($flag == "web") {
			$key = 'WEB_COLOR_SCHEME';
		}
		$config_model = new ConfigModel();
		$info = $config_model->getCount([
			'key' => $key,
			'instance_id' => $this->instance_id
		]);
		
		$data['value'] = $value;
		if ($info == 0) {
			$data['instance_id'] = $this->instance_id;
			$data['key'] = $key;
			$data['create_time'] = time();
			$data['desc'] = $flag . '端页面配色方案';
			$data['is_use'] = 1;
			$res = $config_model->save($data);
		} else {
			$data['modify_time'] = time();
			$res = $config_model->save($data, [
				'key' => $key,
				'instance_id' => $this->instance_id
			]);
		}
		return $res;
	}
	
	/**
	 * 手机端风格
	 */
	public function getWebSiteColorScheme($flag)
	{
		$cache = Cache::tag('config')->get("getWebSiteColorScheme" . $this->instance_id . "_" . $flag);
		if (!empty($cache)) {
			return $cache;
		}
		if ($flag == "wap") {
			$key = 'WAP_COLOR_SCHEME';
		} elseif ($flag == "web") {
			$key = 'WEB_COLOR_SCHEME';
		}
		$config_model = new ConfigModel();
		$info = $config_model->getInfo([
			'key' => $key,
			'instance_id' => $this->instance_id
		], 'value');
		if (!empty($info) && !empty($info['value'])) {
			$info['value'] = json_decode($info['value'], true);
			Cache::tag('config')->set("getWebSiteColorScheme" . $this->instance_id, $info['value'] . "_" . $flag);
		}
		return $info['value'];
	}
	
	/**
	 * 设置PC端分类显示方式，1:缩略图模式，2：列表模式
	 */
	public function setWebCategoryDisplay($instanceid, $value)
	{
		Cache::tag('config')->set("webCategoryDisplay" . $instanceid, '');
		$key = 'PC_CLASSIFIED_DISPLAY_MODE';
		$config_model = new ConfigModel();
		$info = $config_model->getInfo([
			'key' => $key,
			'instance_id' => $instanceid
		], 'value');
		
		$data['value'] = $value;
		if (empty($info)) {
			$data['instance_id'] = $instanceid;
			$data['key'] = $key;
			$data['create_time'] = time();
			$data['desc'] = 'PC端分类显示方式，1:缩略图模式，2：列表模式';
			$data['is_use'] = 1;
			$res = $config_model->save($data);
		} else {
			$data['modify_time'] = time();
			$res = $config_model->save($data, [
				'key' => $key,
				'instance_id' => $instanceid
			]);
		}
		return $res;
	}
	
	/**
	 * 获取Pc端分类显示方式,1:缩略图模式，2：列表模式
	 */
	public function getWebCategoryDisplay($instanceid)
	{
		$cache = Cache::tag('config')->get("webCategoryDisplay" . $instanceid);
		if (!empty($cache)) {
			return $cache;
		}
		$res = '{"template":"1","is_img":"0"}';
		$key = 'PC_CLASSIFIED_DISPLAY_MODE';
		$info = $this->config_module->getInfo([
			'key' => $key,
			'instance_id' => $instanceid
		], 'value');
		if (!empty($info)) {
			$res = $info['value'];
		}
		Cache::tag('config')->set("webCategoryDisplay" . $instanceid, $res);
		return $res;
	}
	/*************************************************手机端排版(shop)结束***************************************************/
	
	/*************************************************系统api安全**********************************************************/
	/**
	 * 获取API安全配置
	 */
	public function getApiSecureConfig()
	{
		$cache = Cache::tag('config')->get("getApiSecureConfig");
		if (empty($cache)) {
			$info = $this->config_module->getInfo([
				'key' => 'API_SECURE_CONFIG',
				'instance_id' => 0
			], 'value');
			if (empty($info)) {
				$config_module = new ConfigModel();
				$value = [
					'is_open_api_secure' => 0,
					'private_key' => randomkeys(16)
				];
				$data = array(
					'instance_id' => 0,
					'key' => 'API_SECURE_CONFIG',
					'value' => json_encode($value),
					'is_use' => 0,
					'desc' => '设置API安全',
					'create_time' => time()
				);
				$res = $config_module->save($data);
				Cache::tag('config')->set("getApiSecureConfig", $data);
				return $value;
			} else {
				$data = json_decode($info['value'], true);
				Cache::tag('config')->set("getApiSecureConfig", $data);
				return $data;
			}
		} else {
			return $cache;
		}
	}
	
	/**
	 * 设置API安全配置
	 */
	public function setApiSecureConfig($data, $is_use)
	{
		Cache::tag('config')->set("getApiSecureConfig", null);
		$value = $data;
		$info = $this->config_module->getInfo([
			'key' => 'API_SECURE_CONFIG',
			'instance_id' => 0
		], 'value');
		$config_module = new ConfigModel();
		if (empty($info)) {
			$data = array(
				'instance_id' => 0,
				'key' => 'API_SECURE_CONFIG',
				'value' => $value,
				'is_use' => $is_use,
				'desc' => '设置API安全',
				'create_time' => time()
			);
			$res = $config_module->save($data);
		} else {
			$data = array(
				'value' => $value,
				'is_use' => $is_use,
				'modify_time' => time()
			);
			$res = $config_module->save($data, [
				'instance_id' => 0,
				'key' => 'API_SECURE_CONFIG'
			]);
		}
		return $res;
	}
	/*************************************************系统api安全结束******************************************************/
	
	/*************************************************系统会员升级(member)*************************************************/
	/**
	 * 查询会员等级升级规则
	 */
	public function getMemberLevelConfig()
	{
		$condition = array(
			'key' => 'MEMBER_LEVEL_CONFIG'
		);
		$info = $this->config_module->getInfo($condition, "*");
		if (empty($info)) {
			$data = array(
				'value' => json_encode([ "type" => 1 ]),
				'desc' => '会员升级规则  1 累计积分 2 累计消费 3 购买次数 4 购买商品',
				'instance_id' => 0,
				'key' => 'MEMBER_LEVEL_CONFIG',
				'is_use' => 1,
				'create_time' => time()
			);
			$this->config_module->save($data);
			$info = $data;
		}
		$data = json_decode($info["value"], true);
		
		return $data;
	}
	
	/**
	 * 会员等级规则修改
	 */
	public function setMemberLevelConfig($data)
	{
		$condition = array(
			'key' => 'MEMBER_LEVEL_CONFIG'
		);
		$res = $this->config_module->save($data, $condition);
		return $res;
	}
	
	/**
	 * 获取注册协议
	 */
	public function getRegistrationAgreement($shop_id)
	{
		$cache = Cache::tag('config')->get("registrationAgreement");
		if (empty($cache)) {
			$key = 'REGISTRATION_AGREEMENT';
			$info = $this->getConfig($shop_id, $key);
			$info['value'] = json_decode($info['value'], true);
			if (empty($info)) {
				$params[0] = array(
					'instance_id' => $shop_id,
					'key' => $key,
					'value' => array(
						'title' => '',
						'content' => ''
					),
					'desc' => '客服链接地址设置'
				);
				$this->setConfig($params);
				$info = $this->getConfig($shop_id, $key);
			} else
				Cache::tag('config')->set("registrationAgreement", $info);
			return $info;
		} else {
			return $cache;
		}
	}
	
	/**
	 *  设置注册协议
	 */
	public function setRegistrationAgreement($shop_id, $value)
	{
		Cache::clear('config');
		$params[0] = array(
			'instance_id' => $shop_id,
			'key' => 'REGISTRATION_AGREEMENT',
			'value' => array(
				'title' => $value['title'],
				'content' => $value['content'],
			),
			'desc' => '设置注册协议',
			'is_use' => 1
		);
		$res = $this->setConfig($params);
		return $res;
	}
	
	/**
	 * 获取海报设置
	 */
	public function getPosterConfig()
	{
		$cache = Cache::tag('config')->get('getPosterConfig');
		if (!empty($cache)) return $cache;
		
		$condition = [ 'key' => 'POSTER_SETTING' ];
		$info = $this->config_module->getInfo($condition, "*");
		if (empty($info)) {
			$info = [
				'value' => [ 'promotion_content' => '' ],
				'is_use' => 1
			];
		} else {
			$info['value'] = json_decode($info['value'], true);
		}
		Cache::tag('config')->set('getPosterConfig', $info);
		return $info;
	}
	
	/**
	 * 设置海报配置
	 */
	public function setPosterConfig($value)
	{
		Cache::tag('config')->set('getPosterConfig', null);
		$condition = [ 'key' => 'POSTER_SETTING' ];
		$info = $this->config_module->getInfo($condition, "key");
		if (empty($info)) {
			$data = [
				'instance_id' => 0,
				'key' => 'POSTER_SETTING',
				'value' => $value,
				'desc' => '商品海报设置',
				'is_use' => 1
			];
			$res = $this->config_module->save($data);
		} else {
			$data = [
				'value' => $value
			];
			$res = $this->config_module->save($data, [ 'key' => 'POSTER_SETTING' ]);
		}
		return $res;
	}
	
	/*************************************************系统会员相关(member)结束*********************************************/
	
	/**
	 * 商家模板消息
	 * @param unknown $condition
	 * @param unknown $data
	 */
	public function getShopNotifyConfig(){
	    
	    $condition = [ 'key' => 'SHOP_NOTIFY_CONFIG' ];
	    $info = $this->config_module->getInfo($condition, "*");
	    if (empty($info)) {
	        $value = array(
	            "mobile" => '', 
	            "email" => '', 
	            "uid" => ''
	        );
	        $json = json_encode($value);
	        $data = array(
	            'instance_id' => 0,
	            'key' => 'SHOP_NOTIFY_CONFIG',
	            'value' => $json,
	            'desc' => '商家消息配置',
	            'is_use' => 1
	        );
	        $this->config_module->save($data);
	        $info = $this->config_module->getInfo($condition, "*");
	    } 

	    return $info;
	    
	    
	}
	/**
	 * 配置商家模板消息
	 * @param unknown $param
	 * @return boolean
	 */
	public function setShopNotifyConfig($value){
	    $condition = [ 'key' => 'SHOP_NOTIFY_CONFIG' ];
	    $info = $this->config_module->getInfo($condition, "*");
	    if (empty($info)) {
	        $data = [
	            'instance_id' => 0,
	            'key' => 'SHOP_NOTIFY_CONFIG',
	            'value' => $value,
	            'desc' => '商家消息设置',
	            'is_use' => 1
	        ];
	        $res = $this->config_module->save($data);
	    } else {
	        $data = [
	            'value' => $value
	        ];
	        $res = $this->config_module->save($data, [ 'key' => 'SHOP_NOTIFY_CONFIG' ]);
	    }
	    return $res;
	}
	
	/**
	 * 获取入口列表
	 * @param unknown $condition
	 */
	public function getWapEntranceList($condition = [], $field = '*', $order = 'sort asc'){
	    $cache = Cache::tag('entrance_config')->get('getWapEntranceList_'.json_encode($condition).'_'.$field.'_'.$order);
	    if(!empty($cache)) return $cache;
	    $model = new SysWapEntranceModel();
	    $list = $model->getQuery($condition, $field, $order);
	    Cache::tag('entrance_config')->set('getWapEntranceList_'.json_encode($condition).'_'.$field.'_'.$order, $list);
	    return $list;
	}
	
	/**
	 * 删除入口
	 * @param unknown $id
	 */
	public function deleteWapEntrance($id){
	    Cache::clear('entrance_config');
	    $model = new SysWapEntranceModel();
	    $res = $model->destroy(['id' => $id, 'type' => 1]);
	    return $res;
	}
	
	/**
	 * 入口文件
	 * @param unknown $params
	 */
	public function editWapEntrance($params){
	    Cache::clear('entrance_config');
	    $data = [
	        'title' => $params['title'],
	        'url' => $params['url'],
	        'icon' => $params['icon'],
	        'status' => $params['status'],
	        'type' => $params['type'],
	        'sort' => $params['sort']
	    ];
	    $model = new SysWapEntranceModel();
	    if($params['id']){
	        $res = $model->save($data, ['id' => $params['id']]);
	    }else{
	        $data['create_time'] = time();
	        $model->save($data);
	        $res = $model->id;
	    }
	    return $res;
	}
	
	/**
	 * 入口排序改变
	 * @param unknown $data
	 */
	public function entranceSortChange($data){
	    if (!empty($data)) {
	        $model = new SysWapEntranceModel();
	        foreach ($data as $item) {
	            $model->save(['sort' => $item['sort']], ['id' => $item['id']]);
	        }
	        Cache::clear('entrance_config');
	    }
	}
}