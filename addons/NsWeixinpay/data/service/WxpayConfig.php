<?php
/**
 * AlipayConfig.php
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
 * @date : 2015.1.17
 * @version : v1.0.0.0
 */

namespace addons\NsWeixinpay\data\service;

use data\model\ConfigModel;
use data\service\BaseService;
use think\Cache;

/**
 * 微信支付配置
 */
class WxpayConfig extends BaseService
{
	/**
	 * 设置微信支付
	 */
	public function setWpayConfig($instanceid, $appid, $appkey, $mch_id, $mch_key, $is_use)
	{
		Cache::set("getWpayConfig" . $instanceid, null);
		$config_module = new ConfigModel();
		$data = array(
			'appid' => $appid,
			'appkey' => $appkey,
			'mch_id' => $mch_id,
			'mch_key' => $mch_key
		);
		$value = json_encode($data);
		$info = $config_module->getInfo([
			'key' => 'WPAY',
			'instance_id' => $instanceid
		], 'value');
		if (empty($info)) {
			$config_module = new ConfigModel();
			$data = array(
				'instance_id' => $instanceid,
				'key' => 'WPAY',
				'value' => $value,
				'is_use' => $is_use,
				'create_time' => time()
			);
			$res = $config_module->save($data);
		} else {
			$config_module = new ConfigModel();
			$data = array(
				'key' => 'WPAY',
				'value' => $value,
				'is_use' => $is_use,
				'modify_time' => time()
			);
			$res = $config_module->save($data, [
				'instance_id' => $instanceid,
				'key' => 'WPAY'
			]);
		}
		return $res;
	}
	
	/**
	 * 获取微信支付
	 */
	public function getWpayConfig($instance_id)
	{
		$cache = Cache::get("getWpayConfig" . $instance_id);
		$config_module = new ConfigModel();
		if (empty($cache)) {
			$info = $config_module->getInfo([
				'instance_id' => $instance_id,
				'key' => 'WPAY'
			], 'value,is_use');
			if (empty($info['value'])) {
				$data = array(
					'value' => array(
						'appid' => '',
						'appkey' => '',
						'mch_id' => '',
						'mch_key' => ''
					),
					'is_use' => 0
				);
			} else {
				$info['value'] = json_decode($info['value'], true);
				$data = $info;
			}
			Cache::set("getWpayConfig" . $instance_id, $data);
			return $data;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 设置原路退款信息
	 */
	public function setOriginalRoadRefundSetting($shop_id, $type, $value)
	{
		$key = 'ORIGINAL_ROAD_REFUND_SETTING_WECHAT';
		$config_module = new ConfigModel();
		$info = $config_module->getInfo([
			'key' => $key,
			'instance_id' => $shop_id
		], 'value');
		
		if (empty($info)) {
			$config_module = new ConfigModel();
			$data = array(
				'instance_id' => $shop_id,
				'key' => $key,
				'value' => $value,
				'is_use' => 1,
				'create_time' => time()
			);
			$res = $config_module->save($data);
		} else {
			$config_module = new ConfigModel();
			$data = array(
				'key' => $key,
				'value' => $value,
				'is_use' => 1,
				'modify_time' => time()
			);
			$res = $config_module->save($data, [
				'instance_id' => $shop_id,
				'key' => $key
			]);
		}
		return $res;
	}
	
	/**
	 * 获取原路退款信息
	 *
	 */
	public function getOriginalRoadRefundSetting($shop_id)
	{
		$key = 'ORIGINAL_ROAD_REFUND_SETTING_WECHAT';
		$config_module = new ConfigModel();
		$info = $config_module->getInfo([
			'key' => $key,
			'instance_id' => $shop_id
		], 'value');
		return $info;
	}
	
	/**
	 * 设置转账配置信息
	 */
	public function setTransferAccountsSetting($shop_id, $value)
	{
		$key = 'TRANSFER_ACCOUNTS_SETTING_WECHAT';
		$config_module = new ConfigModel();
		$info = $config_module->getInfo([
			'key' => $key,
			'instance_id' => $shop_id
		], 'value');
		
		if (empty($info)) {
			$config_module = new ConfigModel();
			$data = array(
				'instance_id' => $shop_id,
				'key' => $key,
				'value' => $value,
				'is_use' => 1,
				'create_time' => time()
			);
			$res = $config_module->save($data);
		} else {
			$config_module = new ConfigModel();
			$data = array(
				'key' => $key,
				'value' => $value,
				'is_use' => 1,
				'modify_time' => time()
			);
			$res = $config_module->save($data, [
				'instance_id' => $shop_id,
				'key' => $key
			]);
		}
		return $res;
	}
	
	/**
	 * 获取转账配置信息
	 */
	public function getTransferAccountsSetting($shop_id)
	{
		$key = 'TRANSFER_ACCOUNTS_SETTING_WECHAT';
		$config_module = new ConfigModel();
		$info = $config_module->getInfo([
			'key' => $key,
			'instance_id' => $shop_id
		], 'value');
		return $info;
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
	    $config_module = new ConfigModel();
	    $info = $config_module->getInfo([
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
	
}