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
namespace addons\NsAlisms\data\service;

use data\service\BaseService;
use data\model\ConfigModel;
use data\extend\alisms\top\request\AlibabaAliqinFcSmsNumSendRequest;
use data\extend\alisms\top\TopClient;
use think\Cache;

/**
 * 阿里云短信配置
 */
class AlismsConfig extends BaseService
{
    /**
     * 获取阿里云短信接口
     * @param unknown $instanceid
     * @return mixed
     */
    public function getMobileMessage($instanceid)
    {
        $cache = Cache::get("getMobileMessage" . $instanceid);
        if (empty($cache)) {
            $config_module = new ConfigModel();
            $info = $config_module->getInfo([
                'key' => 'MOBILEMESSAGE',
                'instance_id' => $instanceid
            ], 'value, is_use');
            if (empty($info['value'])) {
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
            Cache::set("getMobileMessage" . $instanceid, $data);
            return $data;
        } else {
            return $cache;
        }
    }
    
    /**
     * 设置阿里云短信接口
     * @param unknown $instanceid
     * @param unknown $app_key
     * @param unknown $secret_key
     * @param unknown $free_sign_name
     * @param unknown $is_use
     * @param unknown $user_type
     * @return boolean
     */
    public function setMobileMessage($instanceid, $app_key, $secret_key, $free_sign_name, $is_use, $user_type)
    {
        Cache::set("getMobileMessage" . $instanceid, null);
        $data = array(
            'appKey' => trim($app_key),
            'secretKey' => trim($secret_key),
            'freeSignName' => trim($free_sign_name),
            'user_type' => $user_type
        );
        $value = json_encode($data);
        $config_module = new ConfigModel();
        $info = $config_module->getInfo([
            'key' => 'MOBILEMESSAGE',
            'instance_id' => $instanceid
        ], 'value');
        if (empty($info)) {
            $config_module = new ConfigModel();
            $data = array(
                'instance_id' => $instanceid,
                'key' => 'MOBILEMESSAGE',
                'value' => $value,
                'is_use' => $is_use,
                'create_time' => time()
            );
            $res = $config_module->save($data);
        } else {
            $config_module = new ConfigModel();
            $data = array(
                'key' => 'MOBILEMESSAGE',
                'value' => $value,
                'is_use' => $is_use,
                'modify_time' => time()
            );
            $res = $config_module->save($data, [
                'instance_id' => $instanceid,
                'key' => 'MOBILEMESSAGE'
            ]);
        }
        return $res;
    }
    
    /**
     * 阿里大于短信发送
     *
     * @param unknown $appkey
     * @param unknown $secret
     * @param unknown $signName
     * @param unknown $smsParam
     * @param unknown $send_mobile
     * @param unknown $template_code
     */
    public function aliSmsSend($appkey, $secret, $signName, $smsParam, $send_mobile, $template_code, $sms_type = 0)
    {
        if ($sms_type == 0) {
            // 旧用户发送短信
            return $this->aliSmsSendOld($appkey, $secret, $signName, $smsParam, $send_mobile, $template_code);
        } else {
            // 新用户发送短信
            return $this->aliSmsSendNew($appkey, $secret, $signName, $smsParam, $send_mobile, $template_code);
        }
    }
    
    /**
     * 阿里大于旧用户发送短信
     *
     * @param unknown $appkey
     * @param unknown $secret
     * @param unknown $signName
     * @param unknown $smsParam
     * @param unknown $send_mobile
     * @param unknown $template_code
     * @return Ambigous <unknown, \ResultSet, mixed>
     */
    public function aliSmsSendOld($appkey, $secret, $signName, $smsParam, $send_mobile, $template_code)
    {
        require_once 'data/extend/alisms/TopSdk.php';
        $c = new TopClient();
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $req = new AlibabaAliqinFcSmsNumSendRequest();
        $req->setExtend("");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName($signName);
        $req->setSmsParam($smsParam);
        $req->setRecNum($send_mobile);
        $req->setSmsTemplateCode($template_code);
        $result = $resp = $c->execute($req);
        return $result;
    }
    
    /**
     * 阿里大于新用户发送短信
     *
     * @param unknown $appkey
     * @param unknown $secret
     * @param unknown $signName
     * @param unknown $smsParam
     * @param unknown $send_mobile
     * @param unknown $template_code
     */
    function aliSmsSendNew($appkey, $secret, $signName, $smsParam, $send_mobile, $template_code)
    {
        require_once 'data/extend/alisms_new/aliyun-php-sdk-core/Config.php';
        require_once 'data/extend/alisms_new/SendSmsRequest.php';
        // 短信API产品名
        $product = "Dysmsapi";
        // 短信API产品域名
        $domain = "dysmsapi.aliyuncs.com";
        // 暂时不支持多Region
        $region = "cn-hangzhou";
        $profile = \DefaultProfile::getProfile($region, $appkey, $secret);
        \DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
        $acsClient = new \DefaultAcsClient($profile);
    
        $request = new \SendSmsRequest();
        // 必填-短信接收号码
        $request->setPhoneNumbers($send_mobile);
        // 必填-短信签名
        $request->setSignName($signName);
        // 必填-短信模板Code
        $request->setTemplateCode($template_code);
        // 选填-假如模板中存在变量需要替换则为必填(JSON格式)
        $request->setTemplateParam($smsParam);
        // 选填-发送短信流水号
        $request->setOutId("0");
        // 发起访问请求
        $acsResponse = $acsClient->getAcsResponse($request);
        return $acsResponse;
    }
    
    
    
}