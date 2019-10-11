<?php
/**
 * PayParam.php
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

namespace data\service\Pay;

use addons\NsAlipay\data\service\AlipayConfig;
use addons\NsUnionPay\data\service\UnionPayConfig;
use addons\NsWeixinpay\data\service\WxpayConfig;
use data\service\BaseService;
use data\service\Config;
use think\Log;

/**
 * 功能说明：第三方支付接口
 */
class PayParam extends BaseService
{
	// 实例ID
	protected $instance;
	
	/**
	 * *********************************************微信公众平台参数******************************************
	 */
	protected $appid;
	// 用于微信公众号appid
	protected $appsecret;
	// 用于微信支公众号appkey
	/**
	 * ********************************************微信公众平台结束******************************************
	 */
	
	/**
	 * *********************************************微信支付参数******************************************
	 */
	protected $pay_appid;
	// 用于微信支付的公众号appid
	protected $pay_appsecret;
	// 用于微信支付的公众号appkey（在jsapi支付中使用获取openid，扫码支付不使用）
	protected $pay_mchid;
	// 用于微信支付的商户号
	protected $pay_mchkey;
	// 用于微信支付的商户秘钥
	/**
	 * ********************************************微信支付参数结束****************************************
	 */
	
	/**
	 * ********************************************支付宝支付参数******************************************
	 */
	protected $ali_partnerid;
	// 支付宝商户id 以2088开始的纯数字
	protected $ali_seller;
	// 支付宝商户账号(邮箱)
	protected $ali_key;
	// 支付宝商户秘钥
	protected $apiclient_cert;
	// 数字证书密钥
	protected $apiclient_key;
	// 数字证书key
	
	/**
	 * ********************************************支付宝支付参数结束****************************************
	 */
	
	/**
	 * ********************************************小程序支付参数支付参数******************************************
	 */
	//小程序appid
	protected $applet_appid;
	//支付商户号
	protected $applet_mchid;
	//小程序key
	protected $applet_key;
	/**
	 * ********************************************小程序参数结束****************************************
	 */
	
	/**
	 * ********************************************银联支付参数开始********************************************
	 */
	//银联商户号
	protected $union_merid;
	//证书签名密码
	protected $union_cert_pwd;
	/**
	 * ********************************************银联支付参数结束********************************************
	 */
	
	// 构造函数如果是多用户系统需要传入对应的实例ID
	public function __construct()
	{
		parent::__construct();
		$this->getParam(0);
	}
	
	// 获取支付所需参数
	protected function getParam($instance)
	{
		$config = new Config();
		$wxpay_config = new WxpayConfig();
		$alipay_config = new AlipayConfig();
		if (addon_is_exit("NsUnionPay")) {
			$unionpay_config = new UnionPayConfig();
		}
		$this->appid = '';
		$this->appsecret = '';
		// 获取微信支付参数(统一支付到平台)
		$wchat_config = $wxpay_config->getWpayConfig($instance);
		$this->pay_appid = $wchat_config['value']['appid'];
		$this->pay_appsecret = $wchat_config['value']['appkey'];
		$this->pay_mchid = $wchat_config['value']['mch_id'];
		$this->pay_mchkey = $wchat_config['value']['mch_key'];
		
		$original_road_refund_setting = $config->getOriginalRoadRefundSetting($instance, "wechat");
		if (!empty($original_road_refund_setting)) {
			
			$wchat_pem_config = json_decode($original_road_refund_setting['value'], true);
		} else {
			$wchat_pem_config = [
				"apiclient_cert" => "",
				"apiclient_key" => ""
			];
		}
		
		$this->apiclient_cert = $wchat_pem_config['apiclient_cert'];
		$this->apiclient_key = $wchat_pem_config['apiclient_key'];
// 		Log::write("this->apiclient_cert:" . $this->apiclient_cert);
// 		Log::write("this->apiclient_key:" . $this->apiclient_key);
		
// 		// 获取微信小程序支付参数(统一支付到平台账户)
// 		$applet_config = $config->getInstanceAppletConfig($instance);
// 		$this->applet_appid = $applet_config['value']['appid'];
// 		$this->applet_mchid = $this->pay_mchid;
// 		$this->applet_key = $this->pay_mchkey;
		
// 		// 获取支付宝支付参数(统一支付到平台账户)
// 		$alipay_config = $alipay_config->getAlipayConfig($instance);
// 		$this->ali_partnerid = $alipay_config['value']['ali_partnerid'];
// 		$this->ali_seller = $alipay_config['value']['ali_seller'];
// 		$this->ali_key = $alipay_config['value']['ali_key'];
		
		
// 		if (addon_is_exit("NsUnionPay")) {
// 			// 获取银联支付参数(统一支付到平台账号 )
// 			$unionpay_config = $unionpay_config->getUnionpayConfig($instance);
// 			$this->union_merid = $unionpay_config['value']['merchant_number'];
// 			$this->union_cert_pwd = $unionpay_config['value']['certificate_key'];
// 		}
		
	}
	
	/**
	 * *************************************************获取微信公众号参数************************************
	 */
	public function getAppid()
	{
		return $this->appid;
	}
	
	public function getAppsecret()
	{
		return $this->appsecret;
	}
	
	/**
	 * ***********************************************获取微信公众号参数结束************************************
	 */
	
	/**
	 * *************************************************获取微信支付参数************************************
	 */
	public function getPayAppid()
	{
		return $this->pay_appid;
	}
	
	public function getPayAppSecret()
	{
		return $this->pay_appsecret;
	}
	
	public function getPayMchid()
	{
		return $this->pay_mchid;
	}
	
	public function getPayMchkey()
	{
		return $this->pay_mchkey;
	}
	
	public function getApiClientCert()
	{
		return $this->apiclient_cert;
	}
	
	public function getApiClientKey()
	{
		return $this->apiclient_key;
	}
	
	/**
	 * ***********************************************获取微信支付参数结束************************************
	 */
	
	/**
	 * ***********************************************获取支付宝支付参数开始***********************************
	 */
	public function getAliPartnerid()
	{
		return $this->ali_partnerid;
	}
	
	public function getAliSeller()
	{
		return $this->ali_seller;
	}
	
	public function getAliKey()
	{
		return $this->ali_key;
	}
	/**
	 * ***********************************************获取支付宝支付参数结束***********************************
	 */
	
	/**
	 * ***********************************************小程序支付参数开始***********************************
	 */
	public function getAppletAppid()
	{
		return $this->applet_appid;
	}
	
	public function getAppletMchid()
	{
		return $this->applet_mchid;
	}
	
	public function getAppletKey()
	{
		return $this->applet_key;
	}
	/**
	 * ***********************************************小程序支付参数结束***********************************
	 */
	
	/**
	 * ********************************************获取银联支付参数开始********************************************
	 */
	
	public function getUnionMerid()
	{
		
		return $this->union_merid;
	}
	
	public function getUnionCertPwd()
	{
		
		return $this->union_cert_pwd;
	}
	/**
	 * ********************************************获取银联支付参数结束********************************************
	 */
	
}