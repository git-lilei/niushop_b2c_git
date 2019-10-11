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
namespace addons\NsAlipay\data\service;

use data\service\BaseService;
use data\model\ConfigModel;
use think\Cache;

/**
 * 支付宝支付配置
 */
class AlipayConfig extends BaseService
{
    /**
     * 设置支付宝支付
     * @param unknown $instanceid
     * @param unknown $appid
     * @param unknown $appkey
     * @param unknown $mch_id
     * @param unknown $mch_key
     * @param unknown $is_use
     */
    public function setAlipayConfig($instanceid, $partnerid, $seller, $ali_key, $is_use = 1)
    {
        Cache::set("getAlipayConfig" . $instanceid, null);
        $data = array(
            'ali_partnerid' => $partnerid,
            'ali_seller' => $seller,
            'ali_key' => $ali_key,
        );
        $value = json_encode($data);
        $config_module = new ConfigModel();
        $info = $config_module->getInfo([
            'key' => 'ALIPAY',
            'instance_id' => $instanceid
        ], 'value');
        if (empty($info)) {
            $config_module = new ConfigModel();
            $data = array(
                'instance_id' => $instanceid,
                'key' => 'ALIPAY',
                'value' => $value,
                'is_use' => $is_use,
                'create_time' => time()
            );
            $res = $config_module->save($data);
        } else {
            $config_module = new ConfigModel();
            $data = array(
                'key' => 'ALIPAY',
                'value' => $value,
                'is_use' => $is_use,
                'modify_time' => time()
            );
            $res = $config_module->save($data, [
                'instance_id' => $instanceid,
                'key' => 'ALIPAY'
            ]);
        }
        return $res;
    }
    
    /**
     * 设置新版支付宝支付
     * @param unknown $instanceid
     * @param unknown $appid
     * @param unknown $appkey
     * @param unknown $mch_id
     * @param unknown $mch_key
     * @param unknown $is_use
     */
    public function setAlipayConfigNew($instanceid, $public_key, $app_id, $private_key, $alipay_public_key)
    {
    	Cache::set("getAlipayConfigNew" . $instanceid, []);
    	$data = array(
    			'app_id' => $app_id,
    			'private_key' => $private_key,
    			'public_key' => $public_key,
    			'alipay_public_key' => $alipay_public_key
    	);
    	$is_use = 1;
    	$value = json_encode($data);
    	$config_module = new ConfigModel();
    	$info = $config_module->getInfo([
    			'key' => 'ALIPAY_NEW',
    			'instance_id' => $instanceid
    	], 'value');
    	if (empty($info)) {
    		$config_module = new ConfigModel();
    		$data = array(
    				'instance_id' => $instanceid,
    				'key' => 'ALIPAY_NEW',
    				'value' => $value,
    				'is_use' => $is_use,
    				'create_time' => time()
    		);
    		$res = $config_module->save($data);
    	} else {
    		$config_module = new ConfigModel();
    		$data = array(
    				'key' => 'ALIPAY_NEW',
    				'value' => $value,
    				'is_use' => $is_use,
    				'modify_time' => time()
    		);
    		$res = $config_module->save($data, [
    				'instance_id' => $instanceid,
    				'key' => 'ALIPAY_NEW'
    		]);
    	}
    	return $res;
    }
    
    /**
     * 获取新版支付宝支付
     * @param unknown $instanceid
     * @param unknown $appid
     * @param unknown $appkey
     * @param unknown $mch_id
     * @param unknown $mch_key
     * @param unknown $is_use
     */
    public function getAlipayConfigNew($instance_id)
    {
    	$cache = Cache::get("getAlipayConfigNew" . $instance_id);

    	if (empty($cache)) {
    		$config_model = new ConfigModel();
    		$info = $config_model->getInfo([
    				'instance_id' => $instance_id,
    				'key' => 'ALIPAY_NEW'
    		], 'value,is_use');
    		if (empty($info['value'])) {
    			$data = array(
    					'value' => array(
    							'app_id' => '',
    							'private_key' => '',
    							'public_key' => '',
    							'alipay_public_key' => ''
    					),
    					'is_use' => 0
    			);
    		} else {
    			$info['value'] = json_decode($info['value'], true);
    			$data = $info;
    		}
    		Cache::set("getAlipayConfigNew" . $instance_id, $data);
    		return $data;
    	} else {
    		return $cache;
    	}
    }
    
    /**
     * 获取支付宝支付
     * @param unknown $instanceid
     * @param unknown $appid
     * @param unknown $appkey
     * @param unknown $mch_id
     * @param unknown $mch_key
     * @param unknown $is_use
     */
    public function getAlipayConfig($instance_id)
    {
        $cache = Cache::get("getAlipayConfig" . $instance_id);
        if (empty($cache)) {
            $config_model = new ConfigModel();
            $info = $config_model->getInfo([
                'instance_id' => $instance_id,
                'key' => 'ALIPAY'
            ], 'value,is_use');
            if (empty($info['value'])) {
                $data = array(
                    'value' => array(
                        'ali_partnerid' => '',
                        'ali_seller' => '',
                        'ali_key' => ''
                    ),
                    'is_use' => 0
                );
            } else {
                $info['value'] = json_decode($info['value'], true);
                $data = $info;
            }
            Cache::set("getAlipayConfig" . $instance_id, $data);
            return $data;
        } else {
            return $cache;
        }
    }
    
    /**
     * 获取原路退款信息
     */
    public function getOriginalRoadRefundSetting($shop_id)
    {
        $config_model = new ConfigModel();
        $info = $config_model->getInfo([
            'key' => 'ORIGINAL_ROAD_REFUND_SETTING_ALIPAY',
            'instance_id' => $shop_id
        ], 'value');
        return $info;
    }
    
    /**
     * 获取支付宝版本信息
     */
    public function getAliPayVersion($shop_id)
    {
    	$config_model = new ConfigModel();
    	$info = $config_model->getInfo([
    			'key' => 'VERSION_ALIPAY',
    			'instance_id' => $shop_id
    	], 'value');
    	return $info;
    }
    
    /**
     * 获取支付状态信息
     */
    public function getAliPayStatus($shop_id)
    {
    	$config_model = new ConfigModel();
    	$info = $config_model->getInfo([
    			'key' => 'ALIPAY_STATUS',
    			'instance_id' => $shop_id
    	], 'value');
    	return $info;
    }
    
    /**
     * 设置原路退款信息
     */
    public function setOriginalRoadRefundSetting($shop_id, $value)
    {
        $config_model = new ConfigModel();
        $info = $config_model->getInfo([
            'key' => 'ORIGINAL_ROAD_REFUND_SETTING_ALIPAY',
            'instance_id' => $shop_id
        ], 'value');
    
        if (empty($info)) {
            $config_module = new ConfigModel();
            $data = array(
                'instance_id' => $shop_id,
                'key' => 'ORIGINAL_ROAD_REFUND_SETTING_ALIPAY',
                'value' => $value,
                'is_use' => 1,
                'create_time' => time()
            );
            $res = $config_module->save($data);
        } else {
            $config_module = new ConfigModel();
            $data = array(
                'key' => 'ORIGINAL_ROAD_REFUND_SETTING_ALIPAY',
                'value' => $value,
                'is_use' => 1,
                'modify_time' => time()
            );
            $res = $config_module->save($data, [
                'instance_id' => $shop_id,
                'key' => 'ORIGINAL_ROAD_REFUND_SETTING_ALIPAY'
            ]);
        }
        return $res;
    }

    /**
     * 设置支付状态信息
     */
    public function setAlipayStatus($shop_id, $is_use)
    {
    	$config_model = new ConfigModel();
    	$info = $config_model->getInfo([
    			'key' => 'ALIPAY_STATUS',
    			'instance_id' => $shop_id
    	], 'value');
    
    	if (empty($info)) {
    		$config_module = new ConfigModel();
    		$data = array(
    				'instance_id' => $shop_id,
    				'key' => 'ALIPAY_STATUS',
    				'value' => $is_use,
    				'is_use' => 1,
    				'create_time' => time()
    		);
    		$res = $config_module->save($data);
    	} else {
    		$config_module = new ConfigModel();
    		$data = array(
    				'key' => 'ALIPAY_STATUS',
    				'value' => $is_use,
    				'is_use' => 1,
    				'modify_time' => time()
    		);
    		$res = $config_module->save($data, [
    				'instance_id' => $shop_id,
    				'key' => 'ALIPAY_STATUS'
    		]);
    	}
    	return $res;
    }
    
    
    /**
     * 设置支付宝配置版本信息
     */
    public function setAliPayVersionSetting($shop_id, $new_type)
    {
    	$config_model = new ConfigModel();
    	$info = $config_model->getInfo([
    			'key' => 'VERSION_ALIPAY',
    			'instance_id' => $shop_id
    	], 'value');
    
    	if (empty($info)) {
    		$config_module = new ConfigModel();
    		$data = array(
    				'instance_id' => $shop_id,
    				'key' => 'VERSION_ALIPAY',
    				'value' => $new_type,
    				'is_use' => 1,
    				'create_time' => time()
    		);
    		$res = $config_module->save($data);
    	} else {
    		$config_module = new ConfigModel();
    		$data = array(
    				'key' => 'VERSION_ALIPAY',
    				'value' => $new_type,
    				'is_use' => 1,
    				'modify_time' => time()
    		);
    		$res = $config_module->save($data, [
    				'instance_id' => $shop_id,
    				'key' => 'VERSION_ALIPAY'
    		]);
    	}
    	return $res;
    }
    
    /**
     * 获取转账配置信息
     */
    public function getTransferAccountsSetting($shop_id)
    {
        $config_model = new ConfigModel();
        $info = $config_model->getInfo([
            'key' => 'TRANSFER_ACCOUNTS_SETTING_ALIPAY',
            'instance_id' => $shop_id
        ], 'value');
        return $info;
    }
    
    /**
     * 设置转账配置信息
     */
    public function setTransferAccountsSetting($shop_id, $value)
    {
        
       $key = 'TRANSFER_ACCOUNTS_SETTING_ALIPAY';
       $config_model = new ConfigModel();
        $info = $config_model->getInfo([
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
    
    
}