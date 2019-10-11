<?php
/**
 * Config.php
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


use data\service\Config as ConfigService;
use addons\NsDiyView\data\service\Config as DiyViewConfig;
use data\service\Upgrade;
use data\service\WebSite;
use data\service\Weixin;
use think\Cache;


/**
 * 配置
 */
class Config extends BaseApi
{
	
	/**
	 * 商家服务
	 */
	public function merchantService()
	{
		$config = new ConfigService();
		$instance_id = isset($this->params['instance_id']) ? $this->params['instance_id'] : $this->instance_id;
		$merchant_service_list = $config->getExistingMerchantService($instance_id);
		return $this->outMessage("", $merchant_service_list);
	}
	
	/**
	 * 默认图片
	 */
	public function defaultImages()
	{
		$config = new ConfigService();
		$defaultImages = $config->getDefaultImages($this->instance_id);
		return $this->outMessage("", $defaultImages);
	}
	
	/**
	 * 提现设置
	 */
	public function balanceWithdraw()
	{
		$config = new ConfigService();
		$balanceConfig = $config->getBalanceWithdrawConfig($this->instance_id);
		return $this->outMessage("", $balanceConfig);
	}
	
	/**
	 * 公告信息
	 */
	public function notice()
	{
		$config = new ConfigService();
		$instance_id = isset($this->params['instance_id']) ? $this->params['instance_id'] : $this->instance_id;
		$user_notice = $config->getUserNotice($instance_id);
		return $this->outMessage("", $user_notice);
	}
	
	/**
	 * 商家配置
	 */
	public function trade()
	{
		$config = new ConfigService();
		$instance_id = isset($this->params['instance_id']) ? $this->params['instance_id'] : $this->instance_id;
		$shopSet = $config->getShopConfig($instance_id);
		return $this->outMessage("", $shopSet);
	}
	
	/**
	 * seo设置
	 */
	public function seo()
	{
		$config = new ConfigService();
		$seo_config = $config->getSeoConfig($this->instance_id);
		return $this->outMessage("", $seo_config);
	}
	
	/**
	 * 商城热卖关键字
	 */
	public function hotSearch()
	{
		$config = new ConfigService();
		$instance_id = isset($this->params['instance_id']) ? $this->params['instance_id'] : $this->instance_id;
		$hot_keys = $config->getHotsearchConfig($instance_id);
		return $this->outMessage("", $hot_keys);
	}
	
	/**
	 * 登录验证码设置
	 */
	public function loginVerifyCode()
	{
		$config = new ConfigService();
		$instance_id = isset($this->params['instance_id']) ? $this->params['instance_id'] : $this->instance_id;
		$login_verify_code_1 = $config->getLoginVerifyCodeConfig($instance_id);
		return $this->outMessage("", $login_verify_code_1);
	}
	
	/**
	 * 第三方登录配置  QQ
	 */
	public function qQLogin()
	{
		$config = new ConfigService();
		$instance_id = isset($this->params['instance_id']) ? $this->params['instance_id'] : $this->instance_id;
		$qq_info = $config->getQQConfig($instance_id);
		return $this->outMessage("", $qq_info);
	}
	
	/**
	 * 第三方登录配置  微信
	 */
	public function wchatLogin()
	{
		$config = new ConfigService();
		$instance_id = isset($this->params['instance_id']) ? $this->params['instance_id'] : $this->instance_id;
		$Wchat_info = $config->getWchatConfig($instance_id);
		return $this->outMessage("", $Wchat_info);
	}
	
	/**
	 * 客服链接
	 */
	public function customService()
	{
		$config = new ConfigService();
		$instance_id = isset($this->params['instance_id']) ? $this->params['instance_id'] : $this->instance_id;
		$custom_service = $config->getCustomServiceConfig($instance_id);
		if (empty($custom_service)) {
			$custom_service['id'] = '';
			$custom_service['value']['service_addr'] = '';
		} else if (isset($custom_service['value']['checked_num']) && $custom_service['value']['checked_num'] == 1) {
			$custom_service['value']['service_addr'] = $custom_service['value']['meiqia_service_addr'];
		} else if (isset($custom_service['value']['checked_num']) && $custom_service['value']['checked_num'] == 2) {
			$custom_service['value']['service_addr'] = $custom_service['value']['kf_service_addr'];
		} else if (isset($custom_service['value']['checked_num']) && $custom_service['value']['checked_num'] == 3) {
			if (request()->isMobile()) {
				if (request()->isMobile()) {
					$is_weixin = isWeixin();
					if ($is_weixin) {
						$custom_service['value']['service_addr'] = 'http://wpa.qq.com/msgrd?v=3&uin=' . $custom_service['value']['qq_service_addr'] . '&site=qq&menu=yes';
					} else {
						$custom_service['value']['service_addr'] = 'mqqwpa://im/chat?chat_type=wpa&uin=' . $custom_service['value']['qq_service_addr'] . '&version=1&src_type=web&web_src=oicqzone.com';
					}
				} else {
					$custom_service['value']['service_addr'] = 'http://wpa.qq.com/msgrd?v=3&uin=' . $custom_service['value']['qq_service_addr'] . '&site=qq&menu=yes';
				}
			} else {
				$custom_service['value']['service_addr'] = 'http://wpa.qq.com/msgrd?v=3&uin=' . $custom_service['value']['qq_service_addr'] . '&site=qq&menu=yes';
			}
		}
		return $this->outMessage("客服链接", $custom_service);
	}
	
	/**
	 * 获取手机端首页排版
	 */
	public function wapPageLayout()
	{
		$config = new ConfigService();
		$instance_id = isset($this->params['instance_id']) ? $this->params['instance_id'] : $this->instance_id;
		$res = $config->getWapPageLayoutConfig($instance_id);
		return $this->outMessage("获取", $res);
	}
	
	/**
	 * 获取首页魔方
	 */
	public function wapHomeMagicCube()
	{
		$config = new ConfigService();
		$instance_id = isset($this->params['instance_id']) ? $this->params['instance_id'] : $this->instance_id;
		$res = $config->getWapHomeMagicCubeConfig($instance_id);
		return $this->outMessage("获取首页魔方", $res);
	}
	
	/**
	 * 获取底部菜单
	 */
	public function bottomNav()
	{
		$config = new ConfigService();
		$data = $config->getWapBottomType($this->instance_id);
		$data = json_decode($data['template_data'], true);
		foreach ($data['template_data'] as $key => $val) {
			if (strpos($val['href'], "https://") === 0 || strpos($val['href'], "http://") === 0) {
				$data['template_data'][ $key ]['href'] = $val['href'];
			} else {
				$data['template_data'][ $key ]['href'] = __URL($val['href']);
			}
		}
		return $this->outMessage('底部菜单', $data);
	}
	
	/**
	 * 版权
	 */
	public function copyRight()
	{
		$title = '底部加载';
		$upgrade = new Upgrade();
		$is_load = $upgrade->isLoadCopyRight();
		$website = new WebSite();
		$web_site_info = $website->getWebSiteInfo();
		$result = array(
			"is_load" => $is_load
		);
		$bottom_info = array();
		if ($is_load == 1) {
			$config = new ConfigService();
			$bottom_info = $config->getCopyrightConfig($this->instance_id);
		}
		if (!empty($web_site_info["web_icp"])) {
			$bottom_info['copyright_meta'] = $web_site_info["web_icp"];
		} else {
			$bottom_info['copyright_meta'] = '';
		}
		$bottom_info['web_gov_record'] = $web_site_info["web_gov_record"];
		$bottom_info['web_gov_record_url'] = $web_site_info["web_gov_record_url"];
		
		$result["bottom_info"] = $bottom_info;
		$result["default_logo"] = "/blue/img/logo.png";
		return $this->outMessage($title, $result);
	}
	
	/**
	 * 获取APP欢迎页配置
	 */
	public function getAppWelcomePageConfig()
	{
		$title = "获取App欢迎页配置";
		$config = new ConfigService();
		$res = $config->getAppWelcomePageConfig($this->instance_id);
		if (!empty($res['value']['welcome_page_picture'])) {
			if (strpos($res['value']['welcome_page_picture'], "http://") === false && strpos($res['value']['welcome_page_picture'], "https://") === false) {
				$res['value']['welcome_page_picture'] = getBaseUrl() . "/" . $res['value']['welcome_page_picture'];
			}
		}
		return $this->outMessage($title, $res['value']);
	}
	
	/**
	 * 获取最新版App信息
	 */
	public function getAppUpgradeInfo()
	{
		$title = "获取最新版App信息";
		$app_type = $this->get($this->params['app_type']);
		if (empty($app_type)) {
			return $this->outMessage($title, null, -1, "缺少字段app_type");
		}
		$config = new ConfigService();
		$res = $config->getLatestAppVersionInfo($app_type);
		if (!empty($res)) {
			if (!empty($res['download_address'])) {
				if (strpos($res['download_address'], "http://") === false && strpos($res['download_address'], "https://") === false) {
					$res['download_address'] = getBaseUrl() . "/" . $res['download_address'];
				}
			}
			return $this->outMessage($title, $res);
		} else {
			return $this->outMessage($title, null, -1, "暂无更新");
		}
	}
	
	/**
	 * 站点配置
	 */
	public function webSite()
	{
		$title = "获取站点配置";
		$web_config = new WebSite();
		$web_info = $web_config->getWebSiteInfo();
		return $this->outMessage($title, $web_info);
	}
	
	/**
	 * 通知配置
	 */
	public function noticeConfig()
	{
		$title = "查询通知是否开启";
		$web_config = new ConfigService();
		$noticeMobile = $web_config->getNoticeMobileConfig(0);
		$noticeEmail = $web_config->getNoticeEmailConfig(0);
		$notice['noticeEmail'] = $noticeEmail[0]['is_use'];
		$notice['noticeMobile'] = $noticeMobile[0]['is_use'];
		return $this->outMessage($title, $notice);
	}
	
	/**
	 * 虚拟商品配置
	 */
	public function virtualGoodsConfig()
	{
		$title = "查询虚拟商品配置";
		$web_config = new ConfigService();
		$is_open = $web_config->getIsOpenVirtualGoodsConfig(0);
		return $this->outMessage($title, $is_open);
	}
	
	/**
	 * 检测当前是否是微信浏览器
	 */
	public function isWeixin()
	{
		$is_weixin = isWeixin();
		return $this->outMessage("检测当前是否是微信浏览器", $is_weixin);
	}
	
	/**
	 * 获取PC端分类显示模式
	 */
	public function webCategoryDisplay()
	{
		$web_config = new ConfigService();
		$res = $web_config->getWebCategoryDisplay($this->instance_id);
		return $this->outMessage("获取PC端分类显示模式", $res);
	}
	
	/**
	 * 获取微信配置
	 * @return string
	 */
	public function wchatConfig()
	{
		$web_config = new ConfigService();
		$res = $web_config->getInstanceWchatConfig($this->instance_id);
		return $this->outMessage("获取微信配置", $res);
	}
	
	/**
	 * 获取订单自动关闭配置
	 */
	public function orderAutoCloseConfig()
	{
		$web_config = new ConfigService();
		$res = $web_config->getConfig($this->instance_id, 'ORDER_BUY_CLOSE_TIME');
		return $this->outMessage("获取订单自动关闭配置", $res);
	}
	
	/**
	 * 订单余额支付配置
	 */
	public function balancePay()
	{
		$web_config = new ConfigService();
		$res = $web_config->getConfig($this->instance_id, "ORDER_BALANCE_PAY");
		return $this->outMessage("获取订单使用余额配置", $res);
	}
	
	/**
	 * 获取当前时间
	 */
	public function getCurrentTime()
	{
		$data = array( 'current_time' => time() );
		return $this->outMessage('获取当前时间', $data);
	}
	
	/**
	 * 获取推广二维码配置
	 * @return string
	 */
	public function getWeixinQrcodeConfig()
	{
		$weixin = new Weixin();
		$uid = isset($this->params['uid']) ? $this->params['uid'] : $this->uid;
		$type = isset($this->params['type']) ? $this->params['type'] : 1;
		$res = $weixin->getWeixinQrcodeConfig($uid, $type);
		return $this->outMessage('获取推广二维码配置', $res);
	}
	
	/**
	 * 获取验证码
	 */
	public function getVertification()
	{
		$key = $this->get('key', '-504*504');
		$key = md5('@' . $key . '*');
		
		$code = [];
		$codeNX = 0;
		$codeSet = '0123456789';
		$font_size = 25;
		$length = 4;
		$width = $length * $font_size * 1.5 + $length * $font_size / 2;
		$height = $font_size * 2.5;
		$img = imagecreate($width, $height);
		imagecolorallocate($img, 243, 251, 254);
		$color = imagecolorallocate($img, mt_rand(1, 150), mt_rand(1, 150), mt_rand(1, 150));
		$ttfPath = __DIR__ . '/../../../vendor/topthink/think-captcha/assets/ttfs/';
		
		$dir = dir($ttfPath);
		$ttfs = [];
		while (false !== ($file = $dir->read())) {
			if ('.' != $file[0] && substr($file, -4) == '.ttf') {
				$ttfs[] = $file;
			}
		}
		$dir->close();
		$fontttf = $ttfs[ array_rand($ttfs) ];
		$fontttf = $ttfPath . $fontttf;
		
		$this->cure($img, $width, $height, $font_size, $color);
		
		for ($i = 0; $i < $length; $i++) {
			$code[ $i ] = $codeSet[ mt_rand(0, strlen($codeSet) - 1) ];
			$codeNX += mt_rand($font_size * 1.2, $font_size * 1.5);
			imagettftext($img, $font_size, mt_rand(-40, 40), $codeNX, $font_size * 1.6, $color, $fontttf, $code[ $i ]);
		}
		$code = implode('', $code);
		Cache::set($key, $code, 300);
		
		ob_start();
		imagepng($img);
		$content = ob_get_clean();
		imagedestroy($img);
		$data = base64_encode($content);
		return $this->outMessage('获取验证码', $data);
	}
	
	/**
	 * 图文验证码干扰线
	 */
	private function cure($img, $width, $height, $font_size, $color)
	{
		$py = 0;
		$A = mt_rand(1, $height / 2);
		$b = mt_rand(-$height / 4, $height / 4);
		$f = mt_rand(-$height / 4, $height / 4);
		$T = mt_rand($height, $width * 2);
		$w = (2 * M_PI) / $T;
		
		$px1 = 0;
		$px2 = mt_rand($width / 2, $width * 0.8);
		
		for ($px = $px1; $px <= $px2; $px = $px + 1) {
			if (0 != $w) {
				$py = $A * sin($w * $px + $f) + $b + $height / 2;
				$i = (int) ($font_size / 5);
				while ($i > 0) {
					imagesetpixel($img, $px + $i, $py + $i, $color);
					$i--;
				}
			}
		}
		
		// 曲线后部分
		$A = mt_rand(1, $height / 2);
		$f = mt_rand(-$height / 4, $height / 4);
		$T = mt_rand($height, $width * 2);
		$w = (2 * M_PI) / $T;
		$b = $py - $A * sin($w * $px + $f) - $height / 2;
		$px1 = $px2;
		$px2 = $width;
		
		for ($px = $px1; $px <= $px2; $px = $px + 1) {
			if (0 != $w) {
				$py = $A * sin($w * $px + $f) + $b + $height / 2;
				$i = (int) ($font_size / 5);
				while ($i > 0) {
					imagesetpixel($img, $px + $i, $py + $i, $color);
					$i--;
				}
			}
		}
	}
	
	/**
	 *  检测插件是否存在
	 */
	public function addonIsExit()
	{
		$data['is_exit_fx'] = addon_is_exit('Nsfx'); // 是否存在分销
		$data['is_exit_pintuan'] = addon_is_exit('NsPintuan'); // 是否存在拼团
		$data['is_exit_bargain'] = addon_is_exit('NsBargain'); // 是否存在砍价
		$data['is_exit_presell'] = addon_is_exit('NsPresell'); // 是否存在预售
        $data['is_exit_sign'] = addon_is_exit('NsMemberSign'); // 是否存在预售
		return $this->outMessage('插件功能检测', $data);
	}
	
	/*
	 * 获取网站风格配色方案
	 */
	public function webSiteColorScheme()
	{
		$web_config = new ConfigService();
		$flag = isset($this->params['flag']) ? $this->params['flag'] : "";
		$info = $web_config->getWebSiteColorScheme($flag);
		return $this->outMessage('获取手机端风格', $info);
	}

    /**
     * 获取小程序端分类显示模式
     */
	public function appletCategoryDisplay()
	{
		$web_config = new ConfigService();
		$show_type = $web_config->getAppletCategoryDisplay($this->instance_id);
		$show_type = json_decode($show_type, true);
		return $this->outMessage("获取小程序端分类显示模式", $show_type);
	}

    /**
     * 是否存在默认微页面
     */
	public function defaultDiyViewIsExit()
    {
        $title = '是否存在默认微页面';
        $template_type = $this->get('template_type', '');
        $type = $this->get('type', 2);
        $has_default_diy_view = 0;
        if (addon_is_exit('NsDiyView') == 1) {
            $diy_view_config = new DiyViewConfig();
            $has_default_diy_view = $diy_view_config->hasDefaultDiyView($template_type, $type);
        }
        return $this->outMessage($title, $has_default_diy_view);
    }
    
    /**
     * 手机端个人中心入口管理
     */
    public function wapEntrance(){
        $web_config = new ConfigService();
        $data = $web_config->getWapEntranceList(['status' => 1]);
        return $this->outMessage('手机端个人中心入口管理', $data);
    }
    
    /**
     * 会员等级配置
     */
    public function memberLevelConfig(){
        $config = new ConfigService();
        $data = $config->getMemberLevelConfig();
        return $this->outMessage('会员等级配置', $data);
    }
}