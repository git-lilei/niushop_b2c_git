<?php
/**
 * Wchat.php
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

use data\extend\WchatOauth;
use data\service\Config as ConfigService;

class Wchat extends BaseApi
{
	/**
	 * 获取分享相关票据
	 */
	public function getShareTicket()
	{
		$title = "获取微信分享相关票据";
		$config = new ConfigService();
		$auth_info = $config->getInstanceWchatConfig(0);
		// 获取票据
		if (!empty($auth_info['value']['appid'])) {
			// 针对单店版获取微信票据
			$wexin_auth = new WchatOauth();
			$signPackage['appId'] = $auth_info['value']['appid'];
			$signPackage['jsTimesTamp'] = time();
			$signPackage['jsNonceStr'] = $wexin_auth->get_nonce_str();
			$jsapi_ticket = $wexin_auth->jsapi_ticket();
			$signPackage['ticket'] = $jsapi_ticket;
			$url = request()->url(true);
			$Parameters = "jsapi_ticket=" . $signPackage['ticket'] . "&noncestr=" . $signPackage['jsNonceStr'] . "&timestamp=" . $signPackage['jsTimesTamp'] . "&url=" . $url;
			$signPackage['jsSignature'] = sha1($Parameters);
			return $this->outMessage($title, $signPackage);
		} else {
			$signPackage = array(
				'appId' => '',
				'jsTimesTamp' => '',
				'jsNonceStr' => '',
				'ticket' => '',
				'jsSignature' => ''
			);
			return $this->outMessage($title, $signPackage, '-9001', "当前微信没有配置!");
		}
	}
	
}