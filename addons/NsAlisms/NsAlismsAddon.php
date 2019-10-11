<?php
// +----------------------------------------------------------------------
// | test [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.zzstudio.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Byron Sampson <xiaobo.sun@gzzstudio.net>
// +----------------------------------------------------------------------
namespace addons\NsAlisms;

use addons\NsAlisms\data\service\AlismsConfig;

class NsAlismsAddon extends \addons\Addons
{
	public $info = array(
		'name' => 'NsAlisms', // 插件名称标识
		'title' => '阿里云短信', // 插件中文名
		'description' => '支持阿里云短信配置与发送', // 插件概述
		'status' => 1, // 状态 1启用 0禁用
		'author' => 'niushop', // 作者
		'version' => '1.0', // 版本号
		'has_addonslist' => 0, // 是否有下级插件 例如：第三方登录插件下有 qq登录，微信登录
		'content' => '', // 插件的详细介绍或使用方法
		'ico' => 'addons/NsAlisms/ico.png'
	);
	
	public function smsconfig($param)
	{
		$alisms_config = new AlismsConfig();
		$config = $alisms_config->getMobileMessage($param['instance_id']);
		$config["logo"] = "addons/NsAlisms/aliyun.jpg";
		$config["pay_name"] = "阿里云短信";
		$config["desc"] = "该系统支持短信接口";
		$config['url'] = __URL('__URL__/NsAlisms/' . ADMIN_MODULE . '/Config/alismsConfig');
		return $config;
	}
	
	/**
	 * 短信发送（短信插件实现接口）
	 * @param unknown $param 说明传入参数    signName（短信签名） smsParam(短信变量赋值json)   mobile（手机号）  code（模板id）
	 * @return string|multitype:number string |multitype:number unknown Ambigous <number, string, unknown, NULL>
	 */
	public function smssend($param)
	{
		$alisms_config = new AlismsConfig();
		$config = $alisms_config->getMobileMessage(0);
		if ($config['is_use'] == 0) {
			return '';
		}
		if (empty($config['value']['appKey']) || empty($config['value']['secretKey']) || empty($config['value']['freeSignName']) || empty($config['is_use'])) {
			return [
				'code' => -1,
				'message' => "短信配置信息有误!",
				'param' => 0
			];
		}
		$result = $alisms_config->aliSmsSend($config['value']['appKey'], $config['value']['secretKey'], $param['signName'], $param['smsParam'], $param['mobile'], $param['code'], $config['value']['user_type']);
		$result = $this->dealAliSmsResult($result);
		return [
			'code' => $result["code"],
			'message' => $result["message"],
			'param' => rand(100000, 999999)
		];
		
	}
	
	/**
	 * 处理阿里大于 的返回数据
	 */
	private function dealAliSmsResult($result)
	{
		$deal_result = array();
		$alisms_config = new AlismsConfig();
		$config = $alisms_config->getMobileMessage(0);
		try {
			if ($config['value']['user_type'] == 0) {
				#旧用户发送
				if (!empty($result)) {
					if (!isset($result->result)) {
						$result = json_decode(json_encode($result), true);
						#发送失败
						$deal_result["code"] = $result["code"];
						$deal_result["message"] = $result["msg"];
					} else {
						#发送成功
						$deal_result["code"] = 0;
						$deal_result["message"] = "发送成功";
					}
				}
			} else {
				#新用户发送
				if (!empty($result)) {
					if ($result->Code == "OK") {
						#发送成功
						$deal_result["code"] = 0;
						$deal_result["message"] = "发送成功";
					} else {
						#发送失败
						$deal_result["code"] = -1;
						$deal_result["message"] = $result->Message;
					}
				}
			}
		} catch (\Exception $e) {
			$deal_result["code"] = -1;
			$deal_result["message"] = "发送失败!";
		}
		
		return $deal_result;
	}
	// 钩子名称（需要该钩子调用的页面）
	
	/**
	 * 插件安装
	 * @see \addons\Addons::install()
	 */
	public function install()
	{
		return true;
	}
	
	/**
	 * 插件卸载
	 * @see \addons\Addons::uninstall()
	 */
	public function uninstall()
	{
		return true;
	}
}