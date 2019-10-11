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

namespace app\admin\controller;

use addons\NsAlisms\data\service\AlismsConfig;
use addons\NsWxtemplatemsg\data\service\WeixinTemplate;
use data\service\Address as DataAddress;
use data\service\Config as WebConfig;
use data\service\Goods;
use data\service\GoodsBrand;
use data\service\GoodsCategory;
use data\service\GoodsGroup;
use data\service\Notice;
use data\service\Shop;
use data\service\Upgrade;
use data\service\WebSite;
use think\console\command\optimize\Autoload;
use think\console\command\optimize\Schema;
use think\console\Input;
use think\console\Output;
use data\service\Member;

/**
 * 网站设置模块控制器
 */
class Config extends BaseController
{
	
	/**
	 * 基础设置 下级菜单
	 */
	public function infrastructureChildMenu($tag)
	{
		$child_menu_list = array(
			array(
				'url' => "config/webconfig",
				'menu_name' => "网站设置",
				"active" => 0,
				"tag" => 1
			),
			array(
				'url' => "config/visitconfig",
				'menu_name' => "运营",
				"active" => 0,
				"tag" => 5
			),
			array(
				'url' => "config/registerandvisit",
				'menu_name' => "注册与访问",
				"active" => 0,
				"tag" => 6
			),
			array(
				'url' => "config/pictureuploadsetting",
				'menu_name' => "上传设置",
				"active" => 0,
				"tag" => 7
			),
			array(
				'url' => "config/customPseudoStaticRule",
				'menu_name' => "伪静态路由",
				"active" => 0,
				"tag" => 10
			),
			array(
				'url' => "config/partylogin",
				'menu_name' => "第三方登录",
				"active" => 0,
				"tag" => 11
			),
			array(
				'url' => "config/customservice",
				'menu_name' => "客服",
				"active" => 0,
				"tag" => 14
			),
			array(
				'url' => "config/appUpgradeList",
				'menu_name' => "App版本管理",
				"active" => 0,
				"tag" => 16
			),
			array(
				'url' => "config/apisecure",
				'menu_name' => "API安全",
				"active" => 0,
				"tag" => 17
			)
		);
		
		if (!empty($tag)) {
			foreach ($child_menu_list as $k => $v) {
				if ($v['tag'] == $tag) {
					$child_menu_list[ $k ]["active"] = 1;
				}
			}
		}
		$this->assign("child_menu_list", $child_menu_list);
	}
	
	/**
	 * 网站设置
	 */
	public function webConfig()
	{
		if (request()->isPost()) {
			// 网站设置
			$title = request()->post('title', ''); // 网站标题
			$logo = request()->post('logo', ''); // 网站logo
			$web_desc = request()->post('web_desc', ''); // 网站描述
			$key_words = request()->post('key_words', ''); // 网站关键字
			$web_icp = request()->post('web_icp', ''); // 网站备案号
			$web_style_pc = 1; // request()->post('web_style_pc', ''); // 前台网站风格 已废弃，改为读取配置
			$web_qrcode = request()->post('web_qrcode', ''); // 网站公众号二维码
			$web_url = request()->post('web_url', ''); // 店铺网址
			$web_phone = request()->post('web_phone', ''); // 网站联系方式
			$web_email = request()->post('web_email', ''); // 网站邮箱
			$web_qq = request()->post('web_qq', ''); // 网站qq号
			$web_weixin = request()->post('web_weixin', ''); // 网站微信号
			$web_address = request()->post('web_address', ''); // 网站联系地址
			$web_popup_title = request()->post("web_popup_title", ""); // 网站弹出框标题
			$third_count = request()->post("third_count", ''); // 第三方统计
			$web_wechat_share_logo = request()->post("web_wechat_share_logo", ""); // 网站微信分享logo
			$web_gov_record = request()->post("web_gov_record", ""); // 公安网备信息
			$web_gov_record_url = request()->post("web_gov_record_url", ""); // 公安网备链接
			
			$data = array(
				'title' => $title,
				'logo' => $logo,
				'web_desc' => $web_desc,
				'key_words' => $key_words,
				'web_icp' => $web_icp,
				'style_id_pc' => $web_style_pc,
				'web_qrcode' => $web_qrcode,
				'web_url' => $web_url,
				'web_phone' => $web_phone,
				'web_email' => $web_email,
				'web_qq' => $web_qq,
				'web_weixin' => $web_weixin,
				'web_address' => $web_address,
				'third_count' => $third_count,
				'modify_time' => time(),
				'web_popup_title' => $web_popup_title,
				'web_wechat_share_logo' => $web_wechat_share_logo,
				'web_gov_record' => $web_gov_record,
				'web_gov_record_url' => $web_gov_record_url
			);
			
			$retval = $this->website->updateWebSite($data);
			return AjaxReturn($retval);
		} else {
			$this->infrastructureChildMenu(1);
			
			$list = $this->website->getWebSiteInfo();
			$style_list_pc = $this->website->getWebStyleList([
				'type' => 1
			]); // 前台网站风格
			$style_list_admin = $this->website->getWebStyleList([
				'type' => 2
			]); // 后台网站风格
			$path = getQRcode(__URL(__URL__), 'upload/qrcode', 'url');
			$this->assign('style_list_pc', $style_list_pc);
			$this->assign('style_list_admin', $style_list_admin);
			$this->assign("website", $list);
			$this->assign("qrcode_path", $path);
			return view($this->style . "Config/webConfig");
		}
	}
	
	/**
	 * seo设置
	 */
	public function seoConfig()
	{
		$Config = new WebConfig();
		if (request()->isAjax()) {
			$seo_title = request()->post("seo_title", '');
			$seo_meta = request()->post("seo_meta", '');
			$seo_desc = request()->post("seo_desc", '');
			$seo_other = request()->post("seo_other", '');
			$retval = $Config->SetSeoConfig($this->instance_id, $seo_title, $seo_meta, $seo_desc, $seo_other);
			return AjaxReturn($retval);
		} else {
			$shopSet = $Config->getSeoConfig($this->instance_id);
			$this->assign("info", $shopSet);
		}
		return view($this->style . "Config/seoConfig");
	}
	
	/**
	 * 版权设置
	 */
	public function copyrightinfo()
	{
		$Config = new WebConfig();
		if (request()->isAjax()) {
			$copyright_logo = request()->post("copyright_logo", '');
			$copyright_meta = request()->post("copyright_meta", '');
			$copyright_link = request()->post("copyright_link", '');
			$copyright_desc = request()->post("copyright_desc", '');
			$copyright_companyname = request()->post("copyright_companyname", '');
			
			$upgrade = new Upgrade();
			$is_load = $upgrade->isLoadCopyRight();
			if ($is_load == 1) {
				$retval = $Config->SetCopyrightConfig($this->instance_id, $copyright_logo, $copyright_meta, $copyright_link, $copyright_desc, $copyright_companyname);
				return AjaxReturn($retval);
			} else {
				$rs = [
					'code' => 0,
					'message' => "未授权用户无法编辑版权信息"
				];
				return $rs;
			}
		} else {
			$shopSet = $Config->getCopyrightConfig($this->instance_id);
			$this->assign("info", $shopSet);
		}
		
		return view($this->style . "Config/copyrightinfo");
	}
	
	/**
	 * qq登录配置
	 */
	public function loginQQConfig()
	{
		$appkey = request()->post('appkey', '');
		$appsecret = request()->post('appsecret', '');
		$url = request()->post('url', '');
		$call_back_url = request()->post('call_back_url', '');
		$is_use = request()->post('is_use', 0);
		$web_config = new WebConfig();
		// 获取数据
		$retval = $web_config->setQQConfig($this->instance_id, $appkey, $appsecret, $url, $call_back_url, $is_use);
		return AjaxReturn($retval);
	}
	
	/**
	 * 微信登录配置
	 */
	public function loginWeixinConfig()
	{
		$appid = request()->post('appkey', '');
		$appsecret = request()->post('appsecret', '');
		$url = request()->post('url', '');
		$call_back_url = request()->post('call_back_url', '');
		$is_use = request()->post('is_use', 0);
		$web_config = new WebConfig();
		// 获取数据
		$retval = $web_config->setWchatConfig($this->instance_id, $appid, $appsecret, $url, $call_back_url, $is_use);
		return AjaxReturn($retval);
	}
	
	/**
	 * 第三方登录 页面显示
	 */
	public function loginConfig()
	{
		$type = request()->get('type', 'qq');
		if ($type == "qq") {
			$secend_menu['module_name'] = "QQ登录";
		} else {
			$secend_menu['module_name'] = "微信登录";
		}
		$this->assign("secend_menu", $secend_menu);
		$this->assign("type", $type);
		$web_config = new WebConfig();
		// qq登录配置
		// 获取当前域名
		$domain_name = \think\Request::instance()->domain();
		// 获取回调域名qq回调域名
		$qq_call_back = __URL(__URL__ . '/wap/login/callback');
		// 获取qq配置信息
		$qq_config = $web_config->getQQConfig($this->instance_id);
		$qq_config['value']["AUTHORIZE"] = $domain_name;
		$qq_config['value']["CALLBACK"] = $qq_call_back;
		$this->assign("qq_config", $qq_config);
		// 微信登录配置
		// 微信登录返回
		$wchat_call_back = __URL(__URL__ . '/wap/login/callback');
		$wchat_config = $web_config->getWchatConfig($this->instance_id);
		$wchat_config['value']["AUTHORIZE"] = $domain_name;
		$wchat_config['value']["CALLBACK"] = $wchat_call_back;
		$this->assign("wchat_config", $wchat_config);
		
		return view($this->style . "Config/loginConfig");
	}
	
	/**
	 * 广告列表
	 */
	public function shopAdList()
	{
		if (request()->isAjax()) {
			$shop_ad = new Shop();
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$list = $shop_ad->getShopAdList($page_index, $page_size, [
				'shop_id' => $this->instance_id
			], 'id desc');
			return $list;
		}
		return view($this->style . "Config/shopAdList");
	}
	
	/**
	 * 添加店铺广告
	 */
	public function addShopAd()
	{
		if (request()->isAjax()) {
			$ad_image = request()->post('ad_image', '');
			$link_url = request()->post('link_url', '');
			$sort = request()->post('sort', 0);
			$type = request()->post('type', 0);
			$background = request()->post('background', '#FFFFFF');
			$shop_ad = new Shop();
			$data = array(
				"shop_id" => 0,
				"ad_image" => $ad_image,
				"link_url" => $link_url,
				"sort" => $sort,
				"type" => $type,
				"background" => $background
			);
			
			$res = $shop_ad->addShopAd($data);
			return AjaxReturn($res);
		}
		return view($this->style . "Config/addShopAd");
	}
	
	/**
	 * 修改店铺广告
	 */
	public function updateShopAd()
	{
		$shop_ad = new Shop();
		if (request()->isAjax()) {
			$id = request()->post('id', '');
			$ad_image = request()->post('ad_image', '');
			$link_url = request()->post('link_url', '');
			$sort = request()->post('sort', 0);
			$type = request()->post('type', 0);
			$background = request()->post('background', '#FFFFFF');
			
			$data = array(
				"shop_id" => 0,
				"ad_image" => $ad_image,
				"link_url" => $link_url,
				"sort" => $sort,
				"type" => $type,
				"background" => $background,
				"id" => $id
			);
			
			$res = $shop_ad->updateShopAd($data);
			return AjaxReturn($res);
		}
		$id = request()->get('id', '');
		if (!is_numeric($id)) {
			$this->error('未获取到信息');
		}
		$info = $shop_ad->getShopAdDetail($id);
		$this->assign('info', $info);
		return view($this->style . "Config/updateShopAd");
	}
	
	public function deleteShopAd()
	{
		$id = request()->post('id', '');
		$res = 0;
		if (!empty($id)) {
			$shop_ad = new Shop();
			$res = $shop_ad->deleteShopAd($id);
		}
		return AjaxReturn($res);
	}
	
	/**
	 * 店铺导航列表
	 */
	public function shopNavigationList()
	{
		if (request()->isAjax()) {
			$shop = new Shop();
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$type = request()->post("nav_type", 1);
			$condition['type'] = $type; // 导航类型 1：pc端 2：手机端
			$list = $shop->shopNavigationList($page_index, $page_size, $condition, 'create_time desc');
			return $list;
		} else {
			$type = request()->get("nav_type", 1);
			$this->assign('nav_type', $type);
			return view($this->style . "Config/shopNavigationList");
		}
	}
	
	/**
	 * PC端模板列表
	 */
	public function pcTemplate()
	{
		$this->getCollatingTemplateList("web");
		$config = new WebConfig();
		$use_template = $config->getUsePCTemplate($this->instance_id);
		$value = "default";
		if (!empty($use_template)) {
			$value = $use_template['value'];
		} else {
			$config->setUsePCTemplate($this->instance_id, "default");
			$this->updateTemplateUse("web", "default");
		}
		$this->assign("use_template", $value);
		return view($this->style . "Config/pcTemplate");
	}
	
	/**
	 * 店铺装修
	 */
	public function diyview()
	{
		return view($this->style . "Config/diyview");
	}
	
	/**
	 * 根据文件夹选择xml配置文件集合
	 * @param string $folder 文件夹：shop(pc端),wap(手机端)
	 */
	public function getTemplateXmlList($folder)
	{
		$file_path = str_replace("\\", "/", ROOT_PATH . 'template/' . $folder);
		$config_list = $this->getfiles($file_path);
		return $config_list;
	}
	
	/**
	 * 根据文件夹获取整理后的模板集合
	 * @param string $folder 文件夹：shop(pc端),wap(手机端)
	 */
	public function getCollatingTemplateList($folder)
	{
		$config_list = $this->getTemplateXmlList($folder);
		
		$xmlTag = array(
			'folder',
			'theme',
			'preview',
			'introduce'
		);
		switch ($folder) {
			case "web":
				// XML标签配置，PC端专属属性
				array_push($xmlTag, "bgcolor");
				break;
			case "wap":
				break;
		}
		$xml = new \DOMDocument();
		$template_list = array();
		$template_count = count($config_list); // 模板数量
		
		// $not_readable_list = array(); // 文件不可读数量
		
		// $not_writeable_list = array(); // 文件不可写数量
		
		foreach ($config_list as $k => $config) {
			if ($config['is_readable']) {
				
				// 获取xml文件内容
				$xml_txt = fopen($config['xml_path'], "r,w");
				$xml_str = fread($xml_txt, filesize($config['xml_path'])); // 指定读取大小，这里把整个文件内容读取出来
				$xml_text = str_replace("\r\n", "<br />", $xml_str);
				$xml->loadXML($xml_text);
				$template = $xml->getElementsByTagName('template'); // 最外层节点
				foreach ($template as $p) {
					foreach ($xmlTag as $x) {
						$node = $p->getElementsByTagName($x);
						$template_list[ $k ][ $x ] = $node->item(0)->nodeValue;
					}
				}
			}
			// if (! $config['is_readable']) {
			// $not_readable_list[] = $config['xml_path'];
			// }
			
			// if (! $config['is_writable']) {
			// $not_writeable_list[] = $config['xml_path'];
			// }
		}
		// 文件不可读数量及文件路径
		// $this->assign("not_readable_count", count($not_readable_list));
		// $this->assign("not_readable_list", $not_readable_list);
		
		// 文件不可写数量及文件路径
		// $this->assign("not_writable_count", count($not_writeable_list));
		// $this->assign("not_writeable_list", $not_writeable_list);
		$this->assign("template_count", $template_count);
		$this->assign("template_list", $template_list);
	}
	
	/**
	 * 更新当前选中的模板,修改对应的XML文件，存到数据库中
	 * @param string $type 类型：shop、wap
	 * @param string $folder 文件夹：shop、wap
	 */
	public function updateTemplateUse($type, $folder)
	{
		$res = 0; // 返回值
		if (empty($type) || empty($folder)) {
			return AjaxReturn($res);
		}
		$config = new WebConfig();
		if ($type == "web") {
			$res = $config->setUsePCTemplate($this->instance_id, $folder);
		} elseif ($type == "wap") {
			$res = $config->setUseWapTemplate($this->instance_id, $folder);
		}
		return AjaxReturn($res);
	}
	
	/**
	 * 店铺导航添加
	 */
	public function addShopNavigation()
	{
		$shop = new Shop();
		if (request()->isAjax()) {
			$nav_title = request()->post('nav_title', '');
			$nav_url = request()->post('nav_url', '');
			$type = request()->post('type', '');
			$sort = request()->post('sort', '');
			$align = request()->post('align', '');
			$nav_type = request()->post('nav_type', '');
			$is_blank = request()->post('is_blank', '');
			$template_name = request()->post("template_name", '');
			$nav_icon = request()->post("nav_icon", '');
			$is_show = request()->post('is_show', '');
			$applet_nav = request()->post('applet_nav', '');

			$data = array(
				'shop_id' => 0,
				'nav_title' => $nav_title,
				'nav_url' => $nav_url,
				'type' => $type,
				'align' => $align,
				'sort' => $sort,
				'nav_type' => $nav_type,
				'is_blank' => $is_blank,
				'template_name' => $template_name,
				'create_time' => time(),
				'modify_time' => time(),
				'nav_icon' => $nav_icon,
				'is_show' => $is_show,
                'applet_nav' => $applet_nav
			);

			$retval = $shop->addShopNavigation($data);
			return AjaxReturn($retval);
		} else {
		    $nav_type = request()->get('nav_type', 1);
		    $this->assign('nav_type', $nav_type);
			$shopNavTemplate = $shop->getShopNavigationTemplate($nav_type);
			$this->assign("shopNavTemplate", $shopNavTemplate);
			$this->assign("shopNavTemplateJson", json_encode($shopNavTemplate));

			return view($this->style . "Config/addShopNavigation");
		}
	}

	/**
	 * 修改店铺导航
	 */
	public function updateShopNavigation()
	{
		$shop = new Shop();
		if (request()->isAjax()) {
			$nav_id = request()->post('nav_id', '');
			$nav_title = request()->post('nav_title', '');
			$nav_url = request()->post('nav_url', '');
			$type = request()->post('type', '');
			$sort = request()->post('sort', '');
			$align = request()->post('align', '');
			$nav_type = request()->post('nav_type', '');
			$is_blank = request()->post('is_blank', '');
			$template_name = request()->post("template_name", '');
			$nav_icon = request()->post("nav_icon", '');
			$is_show = request()->post('is_show', '');
			$applet_nav = request()->post('applet_nav', '');

			$data = array(
				'nav_title' => $nav_title,
				'nav_url' => $nav_url,
				'type' => $type,
				'align' => $align,
				'sort' => $sort,
				'nav_type' => $nav_type,
				'is_blank' => $is_blank,
				'template_name' => $template_name,
				'modify_time' => time(),
				'nav_icon' => $nav_icon,
				'is_show' => $is_show,
				"nav_id" => $nav_id,
                'applet_nav' => $applet_nav
			);
			$retval = $shop->updateShopNavigation($data);
			return AjaxReturn($retval);
		} else {
			$nav_id = request()->get('nav_id', '');
			if (!is_numeric($nav_id)) {
				$this->error('未获取到信息');
			}
			$data = $shop->shopNavigationDetail($nav_id);
			$this->assign('data', $data);
			$nav_type = request()->get('nav_type', 1);
			$this->assign('nav_type', $nav_type);
			$shopNavTemplate = $shop->getShopNavigationTemplate($nav_type);
			$this->assign("shopNavTemplate", $shopNavTemplate);
			$this->assign("shopNavTemplateJson", json_encode($shopNavTemplate));
			return view($this->style . "Config/updateShopNavigation");
		}
	}
	
	/**
	 * 删除店铺导航
	 */
	public function deleteShopNavigation()
	{
		if (request()->isAjax()) {
			$shop = new Shop();
			$nav_id = request()->post('nav_id', '');
			if (empty($nav_id)) {
				$this->error('未获取到信息');
			}
			$retval = $shop->deleteShopNavigation($nav_id);
			return AjaxReturn($retval);
		}
	}
	
	/**
	 * 修改店铺导航排序
	 */
	public function modifyShopNavigationSort()
	{
		if (request()->isAjax()) {
			$shop = new Shop();
			$nav_id = request()->post('nav_id', '');
			$sort = request()->post('sort', '');
			$retval = $shop->modifyShopNavigationSort($nav_id, $sort);
			return AjaxReturn($retval);
		}
	}
	
	/**
	 * 友情链接列表
	 */
	public function linkList()
	{
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$search_text = request()->post('search_text', '');
			$shop_service = new Shop();
			$list = $shop_service->getLinkList($page_index, $page_size, [
				'link_title' => array(
					'like',
					'%' . $search_text . '%'
				)
			], 'link_id desc');
			return $list;
		}
		return view($this->style . "Config/linkList");
	}
	
	/**
	 * 添加友情链接
	 */
	public function addLink()
	{
		if (request()->isAjax()) {
			$link_title = request()->post('link_title', '');
			$link_url = request()->post('link_url', '');
			$link_pic = request()->post('link_pic', '');
			$link_sort = request()->post('link_sort', 0);
			$is_blank = request()->post('is_blank', '');
			$is_show = request()->post("is_show", '');
			$shop_service = new Shop();
			
			$data = array(
				"link_title" => $link_title,
				"link_url" => $link_url,
				"link_pic" => $link_pic,
				"link_sort" => $link_sort,
				"is_blank" => $is_blank,
				"is_show" => $is_show
			);
			$res = $shop_service->addLink($data);
			return AjaxReturn($res);
		}
		return view($this->style . "Config/addLink");
	}
	
	/**
	 * 修改友情链接
	 */
	public function updateLink()
	{
		$shop_service = new Shop();
		if (request()->isAjax()) {
			$link_id = request()->post('link_id', '');
			$link_title = request()->post('link_title', '');
			$link_url = request()->post('link_url', '');
			$link_pic = request()->post('link_pic', '');
			$link_sort = request()->post('link_sort', 0);
			$is_blank = request()->post("is_blank", '');
			$is_show = request()->post("is_show", '');
			
			$data = array(
				'link_title' => $link_title,
				'link_url' => $link_url,
				'link_pic' => $link_pic,
				'link_sort' => $link_sort,
				'is_blank' => $is_blank,
				'is_show' => $is_show,
				"link_id" => $link_id
			);
			
			$res = $shop_service->updateLink($data);
			return AjaxReturn($res);
		}
		$link_id = request()->get('link_id', '');
		if (empty($link_id)) {
			$this->error('未获取到信息');
		}
		$link_info = $shop_service->getLinkDetail($link_id);
		$this->assign('link_info', $link_info);
		return view($this->style . "Config/updateLink");
	}
	
	/**
	 * 删除友情链接
	 */
	public function delLink()
	{
		$link_id = request()->post('link_id', '');
		$shop_service = new Shop();
		if (empty($link_id)) {
			$this->error('未获取到信息');
		}
		$res = $shop_service->deleteLink($link_id);
		return AjaxReturn($res);
	}
	
	/**
	 * 搜索设置
	 */
	public function searchConfig()
	{
		$type = request()->get('type', 'hot');
		
		$web_config = new WebConfig();
		// 热门搜索
		$keywords_array = $web_config->getHotsearchConfig($this->instance_id);
		if (!empty($keywords_array)) {
			$keywords = implode(",", $keywords_array);
		} else {
			$keywords = '';
		}
		$this->assign('hot_keywords', $keywords);
		// 默认搜索
		$default_keywords = $web_config->getDefaultSearchConfig($this->instance_id);
		$this->assign('default_keywords', $default_keywords);
		$this->assign('type', $type);
		
		return view($this->style . "Config/searchConfig");
	}
	
	/**
	 * 热门搜索 提交修改
	 */
	public function hotSearchConfig()
	{
		$keywords = request()->post('keywords', '');
		if (!empty($keywords)) {
			$keywords_array = explode(",", $keywords);
		} else {
			$keywords_array = array();
		}
		$web_config = new WebConfig();
		$res = $web_config->setHotsearchConfig($this->instance_id, $keywords_array, 1);
		return AjaxReturn($res);
	}
	
	/**
	 * 默认搜索 提交修改
	 */
	public function defaultSearchConfig()
	{
		$keywords = request()->post('default_keywords', '');
		$web_config = new WebConfig();
		$res = $web_config->setDefaultSearchConfig($this->instance_id, $keywords, 1);
		return AjaxReturn($res);
	}
	
	/**
	 * 验证码设置
	 */
	public function codeConfig()
	{
		$this->infrastructureChildMenu(3);
		$webConfig = new WebConfig();
		if (request()->isAjax()) {
			$platform = 0;
			$admin = request()->post('adminCode', 0);
			$pc = request()->post('pcCode', 0);
			$res = $webConfig->setLoginVerifyCodeConfig($this->instance_id, $platform, $admin, $pc, 0);
			return AjaxReturn($res);
		}
		$code_config = $webConfig->getLoginVerifyCodeConfig($this->instance_id);
		$this->assign('code_config', $code_config["value"]);
		return view($this->style . 'Config/codeConfig');
	}
	
	/**
	 * 邮件短信接口设置
	 */
	public function messageConfig()
	{
		$config = new WebConfig();
		$email_message = $config->getEmailMessage($this->instance_id);
		$this->assign('email_message', $email_message);
		return view($this->style . 'Config/messageConfig');
	}
	
	/**
	 * 短信配置中心
	 */
	public function smsConfig()
	{
		$list = hook('smsconfig', [ 'instance_id' => $this->instance_id ]);
		$this->assign("sms_list", $list);
		return view($this->style . 'Config/smsConfig');
	}
	
	/**
	 * ajax 邮件接口
	 */
	public function setEmailMessage()
	{
		$email_host = request()->post('email_host', '');
		$email_port = request()->post('email_port', '');
		$email_addr = request()->post('email_addr', '');
		$email_id = request()->post('email_id', '');
		$email_pass = request()->post('email_pass', '');
		$is_use = request()->post('is_use', 0);
		$email_is_security = request()->post('email_is_security', false);
		$config = new WebConfig();
		$res = $config->setEmailMessage($this->instance_id, $email_host, $email_port, $email_addr, $email_id, $email_pass, $is_use, $email_is_security);
		return AjaxReturn($res);
	}
	
	/**
	 * ajax 短信接口
	 */
	public function setMobileMessage()
	{
		$app_key = request()->post('app_key', '');
		$secret_key = request()->post('secret_key', '');
		$free_sign_name = request()->post('free_sign_name', '');
		$is_use = request()->post('is_use', '');
		$user_type = request()->post('user_type', 0); // 用户类型 0:旧用户，1：新用户 默认是旧用户
		$config = new AlismsConfig();
		$res = $config->setMobileMessage($this->instance_id, $app_key, $secret_key, $free_sign_name, $is_use, $user_type);
		return AjaxReturn($res);
	}
	
	/**
	 * 邮件发送测试接口
	 */
	public function testSend()
	{
		$is_socket = extension_loaded('sockets');
		$is_connect = function_exists("socket_connect");
		if ($is_socket && $is_connect) {
			// $toemail = "854991437@qq.com";//$_POST['email_test'];
			$title = 'Niushop测试邮箱发送';
			$content = '测试邮箱发送成功不成功？';
			$email_host = request()->post('email_host', '');
			$email_port = request()->post('email_port', '');
			$email_addr = request()->post('email_addr', '');
			$email_id = request()->post('email_id', '');
			$email_pass = request()->post('email_pass', '');
			$email_is_security = request()->post('email_is_security', '');
			$toemail = request()->post('email_test', '');
			$res = emailSend($email_host, $email_id, $email_pass, $email_port, $email_is_security, $email_addr, $toemail, $title, $content, $this->instance_name);
			// $config = new WebConfig();
			// $email_message = $config->getEmailMessage($this->instance_id);
			// $email_value = $email_message["value"];
			// $res = emailSend($email_value['email_host'], $email_value['email_id'], $email_value['email_pass'], $email_value['email_addr'], $toemail, $title, $content);
			// var_dump($res);
			// exit;
			if ($res) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(-1);
			}
		} else {
			return AjaxReturn(EMAIL_SENDERROR);
		}
	}
	
	/**
	 * 帮助类型
	 */
	public function helpclass()
	{
		$child_menu_list = array(
			array(
				'url' => "config/helpdocument",
				'menu_name' => "帮助内容",
				"active" => 0
			),
			array(
				'url' => "config/helpclass",
				'menu_name' => "帮助类型",
				"active" => 1
			)
		);
		
		$this->assign('child_menu_list', $child_menu_list);
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$shop_service = new Shop();
			$list = $shop_service->getPlatformHelpClassList($page_index, $page_size, [
				'type' => 1
			], 'class_id desc');
			return $list;
		}
		return view($this->style . "Config/helpClass");
	}
	
	/**
	 * 修改帮助类型
	 */
	public function updateClass()
	{
		if (request()->isAjax()) {
			$class_id = request()->post('class_id', '');
			$type = request()->post('type', 1);
			$class_name = request()->post('class_name', '');
			$parent_class_id = request()->post('parent_class_id', 0);
			$sort = request()->post('sort', '');
			$shop_service = new Shop();
			$data = array(
				'type' => $type,
				'class_name' => $class_name,
				'parent_class_id' => $parent_class_id,
				'sort' => $sort,
				'class_id' => $class_id
			);
			$res = $shop_service->updatePlatformClass($data);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 删除帮助类型
	 */
	public function classDelete()
	{
		$class_id = request()->post('class_id', '');
		$shop_service = new Shop();
		$retval = $shop_service->deleteHelpClass($class_id);
		return AjaxReturn($retval);
	}
	
	/**
	 * 添加 帮助类型
	 */
	public function addHelpClass()
	{
		if (request()->isAjax()) {
			$class_name = request()->post('class_name', '');
			$sort = request()->post('sort', '');
			$shop_service = new Shop();
			
			$data = array(
				'type' => 1,
				'class_name' => $class_name,
				'parent_class_id' => 0,
				'sort' => $sort
			);
			$res = $shop_service->addPlatformHelpClass($data);
			return AjaxReturn($res);
		}
		return view($this->style . 'Config/addHelpClass');
	}
	
	/**
	 * 删除帮助内容标题
	 */
	public function titleDelete()
	{
		$id = request()->post('id', '');
		$shop_service = new Shop();
		$res = $shop_service->deleteHelpTitle($id);
		return AjaxReturn($res);
	}
	
	/**
	 * 帮助内容
	 */
	public function helpDocument()
	{
		$child_menu_list = array(
			array(
				'url' => "config/helpdocument",
				'menu_name' => "帮助内容",
				"active" => 1
			),
			array(
				'url' => "config/helpclass",
				'menu_name' => "帮助类型",
				"active" => 0
			)
		);
		$this->assign('child_menu_list', $child_menu_list);
		
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$shop_service = new Shop();
			$list = $shop_service->getPlatformHelpDocumentList($page_index, $page_size, '', 'create_time desc');
			return $list;
		}
		return view($this->style . "Config/helpDocument");
	}
	
	/**
	 * 修改内容
	 */
	public function updateDocument()
	{
		$shop_service = new Shop();
		if (request()->isAjax()) {
			$uid = $this->auth->getSessionUid();
			$id = request()->post('id', '');
			$title = request()->post('title', '');
			$class_id = request()->post('class_id', '');
			$link_url = request()->post('link_url', '');
			$content = request()->post('content', '');
			$image = request()->post('image', '');
			$is_visibility = request()->post("is_visibility", 1);
			$sort = request()->post('sort', 0);
			
			$data = array(
				'uid' => $uid,
				'class_id' => $class_id,
				'title' => $title,
				'link_url' => $link_url,
				'sort' => $sort,
				'is_visibility' => $is_visibility,
				'content' => $content,
				'image' => $image,
				'modufy_time' => time(),
				'id' => $id
			);
			$revle = $shop_service->updatePlatformDocument($data);
			return AjaxReturn($revle);
		} else {
			$id = request()->get('id', '');
			$this->assign('id', $id);
			$document_detail = $shop_service->getPlatformDocumentDetail($id);
			$document_detail["content"] = htmlspecialchars($document_detail["content"], ENT_COMPAT, "UTF-8");
			$this->assign('document_detail', $document_detail);
			$help_class_list = $shop_service->getPlatformHelpClassList();
			$this->assign('help_class_list', $help_class_list['data']);
			return view($this->style . 'Config/updateDocument');
		}
	}
	
	/**
	 * 修改帮助中心内容的标题与排序
	 */
	public function updateHelpContentTitleAndSort()
	{
		if (request()->isAjax()) {
			$shop_service = new Shop();
			$id = request()->post('id', '');
			$title = request()->post('title', '');
			$sort = request()->post('sort', 0);
			$retval = $shop_service->updatePlatformDocumentTitleAndSort($id, $title, $sort);
			return AjaxReturn($retval);
		}
	}
	
	/**
	 * 添加内容
	 */
	public function addDocument()
	{
		$shop_service = new Shop();
		if (request()->isAjax()) {
			$uid = $this->auth->getSessionUid();
			$title = request()->post('title', '');
			$class_id = request()->post('class_id', '');
			$link_url = request()->post('link_url', '');
			$content = request()->post('content', '');
			$image = request()->post('image', '');
			$is_visibility = request()->post("is_visibility", 1);
			$sort = request()->post('sort', '');
			$data = array(
				'uid' => $uid,
				'class_id' => $class_id,
				'title' => $title,
				'link_url' => $link_url,
				'is_visibility' => $is_visibility,
				'sort' => $sort,
				'content' => $content,
				'image' => $image,
				'create_time' => time(),
				'modufy_time' => time()
			);
			$result = $shop_service->addPlatformDocument($data);
			return AjaxReturn($result);
		} else {
			$help_class_list = $shop_service->getPlatformHelpClassList();
			$this->assign('help_class_list', $help_class_list['data']);
			return view($this->style . 'Config/addDocument');
		}
	}
	
	/**
	 * 根据路径查询配置文件集合
	 */
	function getfiles($path)
	{
		try {
			$config_list = array();
			
			$k = 0;
			if ($dh = opendir($path)) {
				while (($file = readdir($dh)) !== false) {
					if ((is_dir($path . "/" . $file)) && $file != "." && $file != "..") {
						// 当前目录问文件夹
						$xml_path = $path . '/' . $file . '/config.xml';
						$xml_path = str_replace("\\", "/", $xml_path);
						$config_list[ $k ]['xml_path'] = $xml_path; // XML文件路径
						$config_list[ $k ]['is_readable'] = is_readable($xml_path); // 是否可读
						// $config_list[$k]['is_writable'] = is_writable($xml_path); // 是否可写
						$k++;
					}
				}
				closedir($dh);
			}
			$config_list = array_merge($config_list);
		} catch (\Exception $e) {
			echo $e;
		}
		return $config_list;
	}
	
	/**
	 * 固定模板
	 */
	public function fixedtemplate()
	{
		$web_config = new WebConfig();
		
		// 分类显示方式
		$classified_display_mode = $web_config->getWapCategoryDisplay($this->instance_id);
		$this->assign("classified_display_mode", $classified_display_mode);
		
		$this->assign("show_type", 1);
		
		// 手机模板
		$this->getCollatingTemplateList("wap");
		$use_wap_template = $web_config->getUseWapTemplate($this->instance_id);
		$value = "default";
		if (!empty($use_wap_template)) {
			$value = $use_wap_template['value'];
		} else {
			// 使用默认模板
			$web_config->setUseWapTemplate($this->instance_id, "default");
			$this->updateTemplateUse("wap", "default");
		}
		$this->assign("use_template", $value);
		
		return view($this->style . 'Config/fixedtemplate');
	}
	
	/**
	 * 首页公告 设置
	 */
	public function userNotice()
	{
		$shop_service = new Shop();
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$list = $shop_service->getNoticeList($page_index, $page_size, "", "create_time desc");
			return $list;
		}
		return view($this->style . 'Config/userNotice');
	}
	
	/**
	 * 修改公告
	 */
	public function updateWapBasicInformation()
	{
		$web_config = new WebConfig();
		$shopid = $this->instance_id;
		if (request()->isAjax()) {
			$notice_message = request()->post('notice_message', '');
			$is_enable = request()->post('is_enable', '');
			$res = $web_config->setNotice($shopid, $notice_message, $is_enable);
			return AjaxReturn($res);
		}
	}
	
	public function areaManagement()
	{
		// 获取物流配送三级菜单
		$express = new Express();
		$child_menu_list = $express->getExpressChildMenu(4);
		$this->assign('child_menu_list', $child_menu_list);
		$express_child = $express->getExpressChild(4, 1);
		$this->assign('express_child', $express_child);
		
		$dataAddress = new DataAddress();
		$area_list = $dataAddress->getAreaList(); // 区域地址
		$list = $dataAddress->getProvinceList();
		foreach ($list as $k => $v) {
			if ($dataAddress->getCityCountByProvinceId($v['province_id']) > 0) {
				$v['issetLowerLevel'] = 1;
			} else {
				$v['issetLowerLevel'] = 0;
			}
			if (!empty($area_list)) {
				foreach ($area_list as $area) {
					if ($area['area_id'] == $v['area_id']) {
						$list[ $k ]['area_name'] = $area['area_name'];
						break;
					} else {
						$list[ $k ]['area_name'] = "-";
					}
				}
			}
		}
		$this->assign("area_list", $area_list);
		$this->assign("list", $list);
		return view($this->style . 'Config/areaManagement');
	}
	
	public function selectCityListAjax()
	{
		if (request()->isAjax()) {
			$province_id = request()->post('province_id', '');
			$dataAddress = new DataAddress();
			$list = $dataAddress->getCityList($province_id);
			foreach ($list as $v) {
				if ($dataAddress->getDistrictCountByCityId($v['city_id']) > 0) {
					$v['issetLowerLevel'] = 1;
				} else {
					$v['issetLowerLevel'] = 0;
				}
			}
			return $list;
		}
	}
	
	public function selectDistrictListAjax()
	{
		if (request()->isAjax()) {
			$city_id = request()->post('city_id', '');
			$dataAddress = new DataAddress();
			$list = $dataAddress->getDistrictList($city_id);
			return $list;
		}
	}
	
	public function addCityAjax()
	{
		if (request()->isAjax()) {
			$dataAddress = new DataAddress();
			$province_id = request()->post('superiorRegionId', '');
			$city_name = request()->post('regionName', '');
			$zipcode = request()->post('zipcode', '');
			$sort = request()->post('regionSort', '');
			$data = array(
				"province_id" => $province_id,
				"city_name" => $city_name,
				"zipcode" => $zipcode,
				"sort" => $sort
			);
			$res = $dataAddress->editCity($data);
			return AjaxReturn($res);
		}
	}
	
	public function updateCityAjax()
	{
		if (request()->isAjax()) {
			$dataAddress = new DataAddress();
			$city_id = request()->post('eventId', '');
			$province_id = request()->post('superiorRegionId', '');
			$city_name = request()->post('regionName', '');
			$zipcode = request()->post('zipcode', '');
			$sort = request()->post('regionSort', '');
			$data = array(
				'city_id' => $city_id,
				"province_id" => $province_id,
				"city_name" => $city_name,
				"zipcode" => $zipcode,
				"sort" => $sort
			);
			$res = $dataAddress->editCity($data);
			return AjaxReturn($res);
		}
	}
	
	public function addDistrictAjax()
	{
		if (request()->isAjax()) {
			$dataAddress = new DataAddress();
			$city_id = request()->post('superiorRegionId', '');
			$district_name = request()->post('regionName', '');
			$sort = request()->post('regionSort', '');
			$data = array(
				"city_id" => $city_id,
				"district_name" => $district_name,
				"sort" => $sort
			);
			$res = $dataAddress->editDistrict($data);
			return AjaxReturn($res);
		}
	}
	
	public function updateDistrictAjax()
	{
		if (request()->isAjax()) {
			$dataAddress = new DataAddress();
			$district_id = request()->post('eventId', '');
			$city_id = request()->post('superiorRegionId', '');
			$district_name = request()->post('regionName', '');
			$sort = request()->post('regionSort', '');
			$data = array(
				"district_id" => $district_id,
				"city_id" => $city_id,
				"district_name" => $district_name,
				"sort" => $sort
			);
			$res = $dataAddress->editDistrict($data);
			return AjaxReturn($res);
		}
	}
	
	public function updateProvinceAjax()
	{
		if (request()->isAjax()) {
			$dataAddress = new DataAddress();
			$province_id = request()->post('eventId', '');
			$province_name = request()->post('regionName', '');
			$sort = request()->post('regionSort', '');
			$area_id = request()->post('area_id', '');
			$data = array(
				'province_id' => $province_id,
				"province_name" => $province_name,
				"sort" => $sort,
				"area_id" => $area_id
			);
			$res = $dataAddress->updateProvince($data);
			return AjaxReturn($res);
		}
	}
	
	public function addProvinceAjax()
	{
		if (request()->isAjax()) {
			$dataAddress = new DataAddress();
			$province_name = request()->post('regionName', ''); // 区域名称
			$sort = request()->post('regionSort', ''); // 排序
			$area_id = request()->post('area_id', 0); // 区域id
			$data = array(
				"province_name" => $province_name,
				"sort" => $sort,
				"area_id" => $area_id
			);
			$res = $dataAddress->addProvince($data);
			return AjaxReturn($res);
		}
	}
	
	public function deleteRegion()
	{
		if (request()->isAjax()) {
			$type = request()->post('type', '');
			$regionId = request()->post('regionId', '');
			$dataAddress = new DataAddress();
			if ($type == 1) {
				$res = $dataAddress->deleteProvince($regionId);
				return AjaxReturn($res);
			}
			if ($type == 2) {
				$res = $dataAddress->deleteCity($regionId);
				return AjaxReturn($res);
			}
			if ($type == 3) {
				$res = $dataAddress->deleteDistrict($regionId);
				return AjaxReturn($res);
			}
		}
	}
	
	public function updateRegionAjax()
	{
		if (request()->isAjax()) {
			$dataAddress = new DataAddress();
			$up_type = request()->post('up_type', '');
			$region_type = request()->post('region_type', '');
			$region_name = request()->post('region_name', '');
			$region_sort = request()->post('region_sort', '');
			$region_id = request()->post('region_id', '');
			$params = [
				'up_type' => $up_type,
				'region_type' => $region_type,
				'region_name' => $region_name,
				'region_sort' => $region_sort,
				'region_id' => $region_id
			];
			$res = $dataAddress->updateRegionNameAndRegionSort($params);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 购物设置
	 */
	public function shopSet()
	{
		$child_menu_list = array(
			array(
				'url' => "config/shopset",
				'menu_name' => "购物设置",
				"active" => 1
			),
			array(
				'url' => "config/paymentconfig",
				'menu_name' => "支付配置",
				"active" => 0
			),
			array(
				'url' => "config/memberwithdrawsetting",
				'menu_name' => "提现设置",
				"active" => 0
			)
		);
		
		$this->assign('child_menu_list', $child_menu_list);
		$Config = new WebConfig();
		if (request()->isAjax()) {
			$order_auto_delinery = request()->post("order_auto_delinery", 0);
			$order_balance_pay = request()->post("order_balance_pay", 0);
			$order_delivery_complete_time = request()->post("order_delivery_complete_time", 0);
			$order_show_buy_record = request()->post("order_show_buy_record", 0);
			$order_invoice_tax = request()->post("order_invoice_tax", 0);
			$order_invoice_content = request()->post("order_invoice_content", '');
			$order_delivery_pay = request()->post("order_delivery_pay", 0);
			$order_buy_close_time = request()->post("order_buy_close_time", 0);
			$buyer_self_lifting = request()->post("buyer_self_lifting", 0);
			$seller_dispatching = request()->post("seller_dispatching", '1');
			$is_open_o2o = request()->post("is_open_o2o", '0');
			$is_logistics = request()->post("is_logistics", '1');
			$shopping_back_points = request()->post("shopping_back_points", 0);
			$is_open_virtual_goods = request()->post("is_open_virtual_goods", 0); // 是否开启虚拟商品
			$order_designated_delivery_time = request()->post("order_designated_delivery_time", 0); // 是否开启指定配送时间
			$time_slot = request()->post("time_slot", ''); // 配送时间段
			$evaluate_day = request()->post("evaluate_day", 0); // 默认评价天数
			$shouhoudate = request()->post("shouhoudate", 0); // 默认评价天数
			$evaluate = request()->post("evaluate", ''); // 默认评价语
			$order_online_pay = request()->post("order_online_pay", 1); // 是否开启在线支付
			$invoice = request()->post("invoice", 0);
			$params = [
				'order_auto_delinery' => $order_auto_delinery,
				'order_balance_pay' => $order_balance_pay,
				'order_delivery_complete_time' => $order_delivery_complete_time,
				'order_show_buy_record' => $order_show_buy_record,
				'order_invoice_tax' => $order_invoice_tax,
				'order_invoice_content' => $order_invoice_content,
				'order_delivery_pay' => $order_delivery_pay,
				'order_buy_close_time' => $order_buy_close_time,
				'buyer_self_lifting' => $buyer_self_lifting,
				'seller_dispatching' => $seller_dispatching,
				'is_open_o2o' => $is_open_o2o,
				'is_logistics' => $is_logistics,
				'shopping_back_points' => $shopping_back_points,
				'is_open_virtual_goods' => $is_open_virtual_goods,
				'order_designated_delivery_time' => $order_designated_delivery_time,
				'time_slot' => $time_slot,
				'evaluate_day' => $evaluate_day,
				'evaluate' => $evaluate,
				'shouhoudate' => $shouhoudate,
			    'order_online_pay' => $order_online_pay,
				'invoice' => $invoice
			];
			$retval = $Config->setShopConfig($params);
			return AjaxReturn($retval);
		} else {
			// 订单收货之后多长时间自动完成
			$shopSet = $Config->getShopConfig($this->instance_id);
			$this->assign("shopSet", $shopSet);
			$this->assign("is_support_o2o", IS_SUPPORT_O2O);
			return view($this->style . "Config/shopSet");
		}
	}
	
	/**
	 * 消息管理
	 */
	public function notifyIndex()
	{
		$notice_service = new Notice();
		$weixin_template = new WeixinTemplate();
		$template_type_list = $notice_service->getNoticeTemplateType("", "");
		$user_template_list = [];
		$business_template_list = [];
		$config = new WebConfig();
		foreach ($template_type_list as $k => $v) {
			$v['template_list'] = [];
			$template_list_array = [];
			$template_hook = explode(',', $v['template_hook']);
			$v['template_hook_list'] = $template_hook;
			foreach ($template_hook as $key => $val) {
				$template_item = [];
				$template_item['name'] = $val;
				if ($val == 'sms') {
					$template_item['template_type_name'] = '短信验证';
					$template_info = $notice_service->getNoticeTemplateOneDetail($this->instance_id, $val, $v['template_code'], $v['notify_type']);
				} elseif ($val == 'email') {
					$template_item['template_type_name'] = '邮箱验证';
					$template_info = $notice_service->getNoticeTemplateOneDetail($this->instance_id, $val, $v['template_code'], $v['notify_type']);
					
				} elseif ($val == 'wechat') {
					$template_item['template_type_name'] = '微信消息';
					$template_info = $weixin_template->getInfo([ 'instance_id' => $this->instance_id, 'template_code' => $v['template_code'] ]);
				}
				
				$template_item['info'] = $template_info;
				$template_list_array[] = $template_item;
			}
			$v['template_list'] = $template_list_array;
			if ($v['notify_type'] == 'user') {
				$user_template_list[] = $v;
			} else if ($v['notify_type'] == 'business') {
				$business_template_list[] = $v;
			}
			unset($v);
		}
		$this->assign('user_template_list', $user_template_list);
		$this->assign('business_template_list', $business_template_list);
		return view($this->style . 'Config/notifyConfig');
	}
	
	
	
	/**
	 * 商家消息配置
	 */
	public function shopNotifyConfig(){
	    $config = new WebConfig();
	    if (request()->isAjax()) {
	        $mobile = request()->post("mobile", '');
	        $email = request()->post("email", '');
	        $uid = request()->post("uid", '');
	        $data = array(
	            "email" => $email,
	            "mobile" => $mobile,
	            "uid" => $uid,
	        );
	        $json_data = json_encode($data);
	        $result = $config->setShopNotifyConfig($json_data);
	        return AjaxReturn($result);
	    }else{
	        $config_info = $config->getShopNotifyConfig();
	        $config = json_decode($config_info["value"], true);
	        if(!empty($config["uid"])){
	            $member_service = new Member();
	            $userinfo = $member_service->getUserInfoByUid($config["uid"]);
	            $config["nick_name"] = $userinfo["nick_name"];
	        }
	        $this->assign("info", $config);
	        return view($this->style . 'Config/shopNotifyConfig');
	    }
	    
	}
	/**
	 * 消息编辑
	 */
	public function editNotice()
	{
		$template_code = request()->get('template_code', '');
		$notice_service = new Notice();
		//短信或邮箱配置
		$template_type_info = $notice_service->getNoticeTemplateTypeDetail([ 'template_code' => $template_code ]);
		$template_list = $notice_service->getNoticeTemplateList([ 'instance_id' => $this->instance_id, 'template_code' => $template_type_info['template_code'], 'notify_type' => $template_type_info['notify_type'] ]);
		$this->assign('template_list', $template_list);
		$this->assign('info', $template_type_info);
		$sms_info = [];
		$email_info = [];
		foreach ($template_list as $k => $v) {
			$template_item_list = $notice_service->getNoticeTemplateItem($v["template_code"]);
			$v['template_item_list'] = $template_item_list;
			$v['template_item_json'] = json_encode($template_item_list);
			if ($v['template_type'] == 'sms') {
				$sms_info = $v;
			}
			if ($v['template_type'] == 'email') {
				$email_info = $v;
			}
			unset($v);
		}
		//微信模板消息配置
		$weixin_template = new WeixinTemplate();
		$wechat_info = $weixin_template->getInfo([ 'instance_id' => $this->instance_id, 'template_code' => $template_code ]);
		$this->assign('sms_info', $sms_info);
		$this->assign('email_info', $email_info);
		$this->assign('wechat_info', $wechat_info);
		
		return view($this->style . 'Config/editNotice');
	}
	
	/**
	 * 开启和关闭 邮件 和短信的开启和 关闭
	 */
	public function updateNotifyEnable()
	{
		$id = request()->post('id', '');
		$is_use = request()->post('is_use', '');
		$config_service = new WebConfig();
		$retval = $config_service->updateConfigEnable($id, $is_use);
		return AjaxReturn($retval);
	}
	
	/**
	 * 修改模板
	 */
	public function notifyTemplate()
	{
		$type = request()->get('type', 'email');
		$notice_service = new Notice();
		$template_detail = $notice_service->getNoticeTemplateDetail($this->instance_id, $type, "user");
		$template_type_list = $notice_service->getNoticeTemplateType($type, "user");
		for ($i = 0; $i < count($template_type_list); $i++) {
			$template_code = $template_type_list[ $i ]["template_code"];
			$is_enable = 0;
			$template_title = "";
			$template_content = "";
			$sign_name = "";
			foreach ($template_detail as $template_obj) {
				if ($template_obj["template_code"] == $template_code) {
					$is_enable = $template_obj["is_enable"];
					$template_title = $template_obj["template_title"];
					$template_content = str_replace(PHP_EOL, '', $template_obj["template_content"]);
					$sign_name = $template_obj["sign_name"];
					break;
				}
			}
			$template_type_list[ $i ]["is_enable"] = $is_enable;
			$template_type_list[ $i ]["template_title"] = $template_title;
			$template_type_list[ $i ]["template_content"] = $template_content;
			$template_type_list[ $i ]["sign_name"] = $sign_name;
		}
		$template_item_list = $notice_service->getNoticeTemplateItem($template_type_list[0]["template_code"]);
		$this->assign("template_type_list", $template_type_list);
		$this->assign("template_json", json_encode($template_type_list));
		$this->assign("template_select", $template_type_list[0]);
		$this->assign("template_item_list", $template_item_list);
		$this->assign("template_send_item_json", json_encode($template_item_list));
		if ($type == "email") {
			return view($this->style . 'Config/notifyEmailTemplate');
		} else {
			return view($this->style . 'Config/notifySmsTemplate');
		}
	}
	
	/**
	 * 得到可用的变量
	 */
	public function getTemplateItem()
	{
		$template_code = request()->post('template_code', '');
		$notice_service = new Notice();
		$template_item_list = $notice_service->getNoticeTemplateItem($template_code);
		return $template_item_list;
	}
	
	/**
	 * 更新通知模板
	 */
	public function updateNotifyTemplate()
	{
		$template_code = request()->post('type', 'email');
		$template_data = request()->post('template_data', '');
		$notify_type = request()->post("notify_type", "user");
		$notice_service = new Notice();
		$retval = $notice_service->updateNoticeTemplate($this->instance_id, $template_code, $template_data, $notify_type);
		return AjaxReturn($retval);
	}
	
	/**
	 * 更新微信模板消息
	 */
	public function updateWechatTemplate()
	{
		$template_code = request()->post('template_code', '');
		$is_enable = request()->post('is_enable', '0');
		$weixin_template = new WeixinTemplate();
		$retval = $weixin_template->updateTemplate([ 'is_enable' => $is_enable ], [ 'template_code' => $template_code, 'instance_id' => $this->instance_id ]);
		return AjaxReturn($retval);
	}
	
	/**
	 * 更新微信模板消息
	 */
	public function getTemplateNo()
	{
		$template_no = request()->post("template_no", "");
		$template_code = request()->post('template_code', '');
		$weixin_template = new WeixinTemplate();
		$retval = $weixin_template->getTemplateIdByTemplateNo($template_no);
		if (empty($retval)) {
			return AjaxReturn(-1);
		} else {
			$result = $weixin_template->updateTemplate([ 'template_id' => $retval ], [ 'template_no' => $template_no, 'instance_id' => $this->instance_id ]);
			return AjaxReturn(1, $retval);
		}
		
	}
	
	/**
	 * 会员提现设置
	 */
	public function memberWithdrawSetting()
	{
		$config_service = new WebConfig();
		if (request()->isAjax()) {
			$key = 'WITHDRAW_BALANCE';
			$withdraw_account_arr = request()->post("withdraw_account", "1");
			$withdraw_account_arr = explode(",", $withdraw_account_arr);
			$withdraw_account = array(
				array(
					'id' => 'bank_card',
					'name' => '银行卡',
					'value' => 1,
					'is_checked' => 0
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
			foreach ($withdraw_account_arr as $v) {
				$withdraw_account[ $v - 1 ]['is_checked'] = 1;
			}
			$value = array(
				'withdraw_cash_min' => request()->post('cash_min', 0),
				'withdraw_multiple' => request()->post('multiple', 1),
				'withdraw_poundage' => request()->post('poundage', 0),
				'withdraw_message' => request()->post('message', ''),
				'withdraw_account' => $withdraw_account
			);
			$is_use = request()->post('is_use', '');
			$retval = $config_service->setBalanceWithdrawConfig($this->instance_id, $key, $value, $is_use);
			return AjaxReturn($retval);
		} else {
			$list = $config_service->getBalanceWithdrawConfig($this->instance_id);
			$this->assign("list", $list);
			
			$child_menu_list = array(
				array(
					'url' => "config/shopset",
					'menu_name' => "购物设置",
					"active" => 0
				),
				array(
					'url' => "config/paymentconfig",
					'menu_name' => "支付配置",
					"active" => 0
				),
				array(
					'url' => "config/memberwithdrawsetting",
					'menu_name' => "提现设置",
					"active" => 1
				)
			);
			$this->assign("child_menu_list", $child_menu_list);
			
			return view($this->style . "Config/memberWithdrawSetting");
		}
	}
	
	public function customservice()
	{
		$config_service = new WebConfig();
		if (request()->isAjax()) {
			$key = 'SERVICE_ADDR';
			$value = array(
				'meiqia_service_addr' => request()->post('meiqia_service_addr', ''),
				'kf_service_addr' => request()->post('kf_service_addr', ''),
				'qq_service_addr' => request()->post('qq_service_addr', ''),
				'checked_num' => request()->post('checked_num', '')
			);
			$retval = $config_service->setCustomServiceConfig($this->instance_id, $key, $value);
			return AjaxReturn($retval);
		} else {
			$list = $config_service->getCustomServiceConfig($this->instance_id);
			$this->assign("list", $list);
			$this->infrastructureChildMenu(14);
			return view($this->style . "Config/customservice");
		}
	}
	
	/**
	 * 首页商品分类是否显示设置
	 */
	public function iscategory()
	{
		if (request()->isAjax()) {
			$key = 'IS_CATEGORY';
			$value = array(
				'is_category' => request()->post('is_category', '0')
			);
			$config_service = new WebConfig();
			$retval = $config_service->setiscategoryConfig($this->instance_id, $key, $value);
			return AjaxReturn($retval);
		}
	}
	
	/**
	 * 支付
	 */
	public function paymentConfig()
	{
		$config_service = new WebConfig();
		$pay_list = $config_service->getPayConfig();
		$this->assign("pay_list", $pay_list);
		$child_menu_list = array(
			array(
				'url' => "config/shopset",
				'menu_name' => "购物设置",
				"active" => 0
			),
			array(
				'url' => "config/paymentconfig",
				'menu_name' => "支付配置",
				"active" => 1
			),
			array(
				'url' => "config/memberwithdrawsetting",
				'menu_name' => "提现设置",
				"active" => 0
			)
		);
		$this->assign('child_menu_list', $child_menu_list);
		return view($this->style . 'Config/paymentConfig');
	}
	
	/**
	 * 第三方登录页面
	 */
	public function partyLogin()
	{
		$web_config = new WebConfig();
		// qq登录配置
		// 获取当前域名
		$domain_name = \think\Request::instance()->domain();
		// 获取回调域名qq回调域名
		$qq_call_back = $domain_name . \think\Request::instance()->root() . '/wap/login/callback';
		// 获取qq配置信息
		$qq_config = $web_config->getQQConfig($this->instance_id);
		$qq_config['value']["AUTHORIZE"] = $domain_name;
		$qq_config['value']["CALLBACK"] = $qq_call_back;
		$qq_config['name'] = 'qq登录';
		$this->assign("qq_config", $qq_config);
		// 微信登录配置
		// 微信登录返回
		$wchat_call_back = $domain_name . \think\Request::instance()->root() . '/wap/Login/callback';
		$wchat_config = $web_config->getWchatConfig($this->instance_id);
		$wchat_config['value']["AUTHORIZE"] = $domain_name;
		$wchat_config['value']["CALLBACK"] = $wchat_call_back;
		$wchat_config['name'] = '微信登录';
		$this->assign("wchat_config", $wchat_config);
		$this->infrastructureChildMenu(11);
		return view($this->style . 'Config/partyLogin');
	}
	
	/**
	 * 配送地区管理
	 */
	public function distributionAreaManagement()
	{
		ini_set('memory_limit', '500M'); //临时内存调整
		// 获取物流配送三级菜单
		$express = new Express();
		$child_menu_list = $express->getExpressChildMenu(1);
		$this->assign('child_menu_list', $child_menu_list);
		$express_child = $express->getExpressChild(1, 2);
		$this->assign('express_child', $express_child);
		
		$dataAddress = new DataAddress();
		$provinceList = $dataAddress->getProvinceList();
		$cityList = $dataAddress->getCityList();
		foreach ($provinceList as $k => $v) {
			$arr = array();
			foreach ($cityList as $c => $co) {
				if ($co["province_id"] == $v['province_id']) {
					$arr[] = $co;
					unset($cityList[ $c ]);
				}
			}
			$provinceList[ $k ]['city_list'] = $arr;
		}
		$this->assign("list", $provinceList);
		$districtList = $dataAddress->getDistrictList();
		$this->assign("districtList", $districtList);
		$this->getDistributionArea();
		return view($this->style . "Config/distributionAreaManagement");
	}
	
	/**
	 * 注册与访问
	 */
	public function registerAndVisit()
	{
		$config_service = new WebConfig();
		if (request()->isAjax()) {
			$is_register = request()->post('is_register', '');
			$register_info = request()->post('register_info', '');
			$register_info = empty($register_info) ? '' : rtrim($register_info, ',');
			$name_keyword = request()->post('name_keyword', '');
			$pwd_len = request()->post('pwd_len', '');
			$pwd_complexity = request()->post('pwd_complexity', '');
			$pwd_complexity = empty($pwd_complexity) ? '' : rtrim($pwd_complexity, ',');
			$terms_of_service = request()->post('terms_of_service', '');
			$is_requiretel = request()->post('is_requiretel', '');
			$is_use = request()->post('is_use', '1');
			
			$platform = 0;
			$admin = request()->post('adminCode', 0);
			$pc = request()->post('pcCode', 0);
			$error_num = request()->post("error_num", 0);
			$res_one = $config_service->setLoginVerifyCodeConfig($this->instance_id, $platform, $admin, $pc, $error_num);
			
			$res_two = $config_service->setRegisterAndVisit($this->instance_id, $is_register, $register_info, $name_keyword, $pwd_len, $pwd_complexity, $terms_of_service, $is_requiretel, $is_use);
			
			if ($res_one && $res_two) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(-1);
			}
		} else {
			$this->infrastructureChildMenu(6);
			$register_and_visit = $config_service->getRegisterAndVisit($this->instance_id);
			$this->assign('register_and_visit', json_decode($register_and_visit['value'], true));
			
			$code_config = $config_service->getLoginVerifyCodeConfig($this->instance_id);
			$this->assign('code_config', $code_config["value"]);
			
			return view($this->style . "Config/registerAndVisit");
		}
	}
	
	/**
	 * 获取配送地区设置
	 */
	public function getDistributionArea()
	{
		$dataAddress = new DataAddress();
		$res = $dataAddress->getDistributionAreaInfo($this->instance_id);
		if ($res != '') {
			$this->assign("provinces", explode(',', $res['province_id']));
			$this->assign("citys", explode(',', $res['city_id']));
			$this->assign("districts", $res["district_id"]);
		}
	}
	
	/**
	 * 通过ajax添加或编辑配送区域
	 */
	public function addOrUpdateDistributionAreaAjax()
	{
		if (request()->isAjax()) {
			$dataAddress = new DataAddress();
			$province_id = request()->post("province_id", "");
			$city_id = request()->post("city_id", "");
			$district_id = request()->post("district_id", "");
			$data = array(
				"shop_id" => $this->instance_id,
				"province_id" => $province_id,
				"city_id" => $city_id,
				"district_id" => $district_id
			);
			$res = $dataAddress->addOrUpdateDistributionArea($data);
			return AjaxReturn($res);
		}
	}
	
	public function expressMessage()
	{
		// 获取物流配送三级菜单
		$express = new Express();
		$child_menu_list = $express->getExpressChildMenu(1);
		$this->assign('child_menu_list', $child_menu_list);
		$express_child = $express->getExpressChild(1, 3);
		$this->assign('express_child', $express_child);
		
		$config_service = new WebConfig();
		if (request()->isAjax()) {
			$appid = request()->post("appid", "");
			$appkey = request()->post("appkey", "");
			$back_url = request()->post('back_url', "");
			$is_use = request()->post("is_use", "");
			$type = request()->post("type", 1); // 快递接口 1：快递鸟 2：快递100免费版 3快递100企业版
			$customer = request()->post("customer", "");
			$res = $config_service->updateOrderExpressMessageConfig($this->instance_id, $appid, $appkey, $back_url, $is_use, $type, $customer);
			return AjaxReturn($res);
		} else {
			$expressMessageConfig = $config_service->getOrderExpressMessageConfig($this->instance_id);
			$this->assign('emconfig', $expressMessageConfig);
			return view($this->style . "Config/expressMessage");
		}
	}
	
	/**
	 * 上传方式
	 */
	public function uploadType()
	{
		$config_data = array();
		$web_config = new WebConfig();
		$upload_type = $web_config->getUploadType($this->instance_id);
		$config_data["type"] = $upload_type;
		// 获取七牛参数
		$config_qiniu_info = $web_config->getQiniuConfig($this->instance_id);
		$config_data["data"]["qiniu"] = $config_qiniu_info;
		$this->assign("config_data", $config_data);
		
		$this->infrastructureChildMenu(8);
		
		return view($this->style . "Config/uploadType");
	}
	
	/**
	 * 修改上传类型
	 */
	public function setUploadType()
	{
		$config_service = new WebConfig();
		$type = request()->post("type", "1");
		$result = $config_service->setUploadType($this->instance_id, $type);
		return AjaxReturn($result);
	}
	
	/**
	 * 修改七牛配置
	 */
	public function setQiniuConfig()
	{
		$config_service = new WebConfig();
		$Accesskey = request()->post("Accesskey", "");
		$Secretkey = request()->post("Secretkey", "");
		$Bucket = request()->post("Bucket", "");
		$QiniuUrl = request()->post("QiniuUrl", "");
		$value = array(
			"Accesskey" => trim($Accesskey),
			"Secretkey" => trim($Secretkey),
			"Bucket" => trim($Bucket),
			"QiniuUrl" => trim($QiniuUrl)
		);
		$value = json_encode($value);
		$result = $config_service->setQiniuConfig($this->instance_id, $value);
		return AjaxReturn($result);
	}
	
	/**
	 * 商家通知
	 */
	public function businessNotifyTemplate()
	{
		$type = request()->get("type", "email");
		$notice_service = new Notice();
		
		$template_detail = $notice_service->getNoticeTemplateDetail($this->instance_id, $type, "business");
		$template_type_list = $notice_service->getNoticeTemplateType("", "business");
		for ($i = 0; $i < count($template_type_list); $i++) {
			$template_code = $template_type_list[ $i ]["template_code"];
			$notify_type = $template_type_list[ $i ]["notify_type"];
			$is_enable = 0;
			$template_title = "";
			$template_content = "";
			$sign_name = "";
			foreach ($template_detail as $template_obj) {
				if ($template_obj["template_code"] == $template_code && $template_obj["notify_type"] == $notify_type) {
					$is_enable = $template_obj["is_enable"];
					$template_title = $template_obj["template_title"];
					$template_content = str_replace(PHP_EOL, '', $template_obj["template_content"]);
					$sign_name = $template_obj["sign_name"];
					$notification_mode = $template_obj["notification_mode"];
					break;
				}
			}
			$template_type_list[ $i ]["is_enable"] = $is_enable;
			$template_type_list[ $i ]["template_title"] = $template_title;
			$template_type_list[ $i ]["template_content"] = $template_content;
			$template_type_list[ $i ]["sign_name"] = $sign_name;
			$template_type_list[ $i ]["notification_mode"] = $notification_mode;
		}
		$template_item_list = $notice_service->getNoticeTemplateItem($template_type_list[0]["template_code"]);
		$this->assign("template_type_list", $template_type_list);
		$this->assign("template_json", json_encode($template_type_list));
		$this->assign("template_select", $template_type_list[0]);
		$this->assign("template_item_list", $template_item_list);
		$this->assign("template_send_item_json", json_encode($template_item_list));
		if ($type == "email") {
			return view($this->style . 'Config/businessNotifyEmailTemplate');
		} else {
			return view($this->style . 'Config/businessNotifySmsTemplate');
		}
	}
	
	/**
	 * 图片生成配置
	 */
	public function pictureUploadSetting()
	{
		$config_service = new WebConfig();
		if (request()->isAjax()) {
			$thumb_type = request()->post("thumb_type", "1");
			$upload_size = request()->post("upload_size", "0");
			$upload_ext = request()->post("upload_ext", "gif,jpg,jpeg,bmp,png");
			$data = array(
				"thumb_type" => $thumb_type,
				"upload_size" => $upload_size,
				"upload_ext" => $upload_ext
			);
			$retval = $config_service->setPictureUploadSetting($this->instance_id, json_encode($data));
			return AjaxReturn($retval);
		} else {
			$this->infrastructureChildMenu(7);
			$info = $config_service->getPictureUploadSetting($this->instance_id);
			$this->assign("pic_info", $info);
			
			// 获取默认图
			$result = $config_service->getDefaultImages($this->instance_id);
			$this->assign("info", $result);
			// 附件上传
			
			$config_data = array();
			$upload_type = $config_service->getUploadType($this->instance_id);
			$config_data["type"] = $upload_type;
			// 获取七牛参数
			$config_qiniu_info = $config_service->getQiniuConfig($this->instance_id);
			$config_data["data"]["qiniu"] = $config_qiniu_info;
			$this->assign("config_data", $config_data);
			
			// 获取水印图片配置
			$config_water_info = $config_service->getWatermarkConfig($this->instance_id);
			$this->assign("water_info", $config_water_info);
			
			return view($this->style . 'Config/pictureUploadSetting');
		}
	}
	
	/**
	 * 添加首页公告
	 */
	public function addHomeNotice()
	{
		return view($this->style . "Config/addHomeNotice");
	}
	
	/**
	 * 编辑公告
	 */
	public function updateHomeNotice()
	{
		$id = request()->get("id", 0);
		$shop_service = new Shop();
		$info = $shop_service->getNoticeDetail($id);
		if (empty($info)) {
			$this->error("没有获取到公告信息");
		} else {
			$this->assign("info", $info);
		}
		return view($this->style . "Config/updateHomeNotice");
	}
	
	/**
	 * 删除公告
	 */
	public function deleteNotice()
	{
		if (request()->isAjax()) {
			$shop_service = new Shop();
			$id = request()->post('id', '');
			if (empty($id)) {
				$this->error('未获取到信息');
			}
			$retval = $shop_service->deleteNotice($id);
			return AjaxReturn($retval);
		}
	}
	
	/**
	 * 添加或修改首页公告
	 */
	public function addOrModifyHomeNotice()
	{
		if (request()->isAjax()) {
			$id = request()->post("id", 0);
			$title = request()->post("title", "");
			$content = request()->post("content", "");
			$sort = request()->post("sort", 0);
			$shop_service = new Shop();
			$data = array(
				"notice_title" => $title,
				"notice_content" => $content,
				"shop_id" => $this->instance_id,
				"sort" => $sort,
				"id" => $id
			);
			$res = $shop_service->editNotice($data);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 修改公告排序
	 */
	public function modifyNoticeSort()
	{
		if (request()->isAjax()) {
			$shop_service = new Shop();
			$id = request()->post('id', '');
			$sort = request()->post('sort', '');
			$retval = $shop_service->modifyNoticeSort($sort, $id);
			return AjaxReturn($retval);
		}
	}
	
	/**
	 * 配置伪静态路由规则
	 */
	public function customPseudoStaticRule()
	{
		if (request()->isAjax()) {
			$webSite = new WebSite();
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$rule_list = $webSite->getUrlRouteList($page_index, $page_size);
			return $rule_list;
		}
		$this->infrastructureChildMenu(10);
		return view($this->style . "Config/customPseudoStaticRule");
	}
	
	/**
	 * 添加路由规则
	 */
	public function addRoutingRules()
	{
		if (request()->isAjax()) {
			$rule = request()->post("rule", "");
			$route = request()->post("route", "");
			$is_open = request()->post("is_open", 1);
			$route_model = request()->post("route_model", 1);
			$remark = request()->post("remark", "");
			$webSite = new WebSite();
			
			$data = array(
				"rule" => $rule,
				"route" => $route,
				"is_open" => $is_open,
				"route_model" => $route_model,
				"is_system" => 0,
				"remark" => $remark
			);
			$res = $webSite->addUrlRoute($data);
			return AjaxReturn($res);
		}
		return view($this->style . "Config/addRoutingRules");
	}
	
	/**
	 * 编辑路由规则
	 */
	public function updateRoutingRule()
	{
		$webSite = new WebSite();
		if (request()->isAjax()) {
			$routeid = request()->post("routeid", "");
			$rule = request()->post("rule", "");
			$route = request()->post("route", "");
			$is_open = request()->post("is_open", 1);
			$route_model = request()->post("route_model", 1);
			$remark = request()->post("remark", "");
			$data = array(
				"rule" => $rule,
				"route" => $route,
				"is_open" => $is_open,
				"route_model" => $route_model,
				"remark" => $remark,
				"routeid" => $routeid
			);
			
			$res = $webSite->updateUrlRoute($data);
			return AjaxReturn($res);
		}
		$routeid = request()->get("routeid", "");
		$routeDetail = $webSite->getUrlRouteDetail($routeid);
		if (empty($routeDetail)) {
			$this->error("未获取路由规则信息");
		} else {
			$this->assign("routeDetail", $routeDetail);
		}
		return view($this->style . "Config/updateRoutingRules");
	}
	
	/**
	 * 判断路由规则或者路由地址是否存在
	 */
	public function urlRouteIsExists()
	{
		if (request()->isAjax()) {
			$type = request()->post("type", "");
			$value = request()->post("value", "");
			$webSite = new WebSite();
			$res = $webSite->urlRouteIsExists($type, $value);
			return $res;
		}
	}
	
	/**
	 * 删除伪静态路由规则
	 */
	public function deleteUrlRoute()
	{
		if (request()->isAjax()) {
			$routeid = request()->post("routeid", "");
			$webSite = new WebSite();
			$res = $webSite->deleteUrlRoute($routeid);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 访问设置
	 */
	public function visitConfig()
	{
		if (request()->isAjax()) {
			
			$web_style_admin = request()->post('web_style_admin', ''); // 后台网站风格
			$web_status = request()->post("web_status", ''); // 网站运营状态
			$wap_status = request()->post("wap_status", ''); // 手机端网站运营状态
			$visit_pattern = request()->post('visit_pattern', '');
			$close_reason = request()->post("close_reason", ''); // 站点关闭原因
			$is_show_follow = request()->post("is_show_follow", 1);
			
			$data = array(
				'style_id_admin' => $web_style_admin,
				'url_type' => $visit_pattern,
				'web_status' => $web_status,
				'wap_status' => $wap_status,
				'close_reason' => $close_reason,
				'modify_time' => time()
			);
			
			$retval = $this->website->updateVisitWebSite($data);
			
			return AjaxReturn($retval);
		} else {
			
			$this->infrastructureChildMenu(5);
			$list = $this->website->getWebSiteInfo();
			$style_list_admin = $this->website->getWebStyleList([
				'type' => 2
			]); // 后台网站风格
			$path = getQRcode(__URL(__URL__), 'upload/qrcode', 'url');
			$this->assign('style_list_admin', $style_list_admin);
			$this->assign("website", $list);
			$this->assign("qrcode_path", $path);
			return view($this->style . "Config/visitConfig");
		}
	}
	
	/**
	 * 保存 图片 类
	 */
	public function pictureSetting()
	{
		$config_service = new WebConfig();
		if (request()->isAjax()) {
			$thumb_type = request()->post("thumb_type", "1");
			$upload_size = request()->post("upload_size", "0");
			$upload_ext = request()->post("upload_ext", "gif,jpg,jpeg,bmp,png");
			
			$data = array(
				"thumb_type" => $thumb_type,
				"upload_size" => $upload_size,
				"upload_ext" => $upload_ext
			);
			$res_one = $config_service->setPictureUploadSetting($this->instance_id, json_encode($data));
			
			$Accesskey = request()->post("Accesskey", "");
			$Secretkey = request()->post("Secretkey", "");
			$Bucket = request()->post("Bucket", "");
			$QiniuUrl = request()->post("QiniuUrl", "");
			$qi_value = array(
				"Accesskey" => trim($Accesskey),
				"Secretkey" => trim($Secretkey),
				"Bucket" => trim($Bucket),
				"QiniuUrl" => trim($QiniuUrl)
			);
			$qi_value = json_encode($qi_value);
			$res_two = $config_service->setQiniuConfig($this->instance_id, $qi_value);
			
			$img_value = array(
				"default_goods_img" => request()->post("default_goods_img", ""),
				"default_headimg" => request()->post("default_headimg", ""),
				"default_cms_thumbnail" => request()->post("default_cms_thumbnail", "")
			);
			$img_value = json_encode($img_value);
			$res_three = $config_service->setDefaultImages($this->instance_id, $img_value);
			
			$watermark = request()->post("watermark", "0");
			$transparency = request()->post("transparency", "0");
			$waterPosition = request()->post("waterPosition", "");
			$imgWatermark = request()->post("default_watermark", "");
			$data_water = array(
				"watermark" => $watermark,
				"transparency" => $transparency,
				"waterPosition" => $waterPosition,
				"imgWatermark" => $imgWatermark
			);
			$res_four = $config_service->setPictureWatermark($this->instance_id, json_encode($data_water));
			
			if ($res_one > 0 && $res_two > 0 && $res_three > 0 && $res_four > 0) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(-1);
			}
		}
	}
	
	/**
	 * 商家服务
	 */
	public function merchantService()
	{
		$config = new WebConfig();
		if (request()->isAjax()) {
			$value = request()->post("value", "");
			$res = $config->setMerchantServiceConfig($this->instance_id, $value);
			return AjaxReturn($res);
		} else {
			$list = $config->getMerchantServiceConfig($this->instance_id);
			$this->assign("list", $list);
			return view($this->style . 'Config/merchantService');
		}
	}
	
	/**
	 * 通知记录
	 */
	public function notifyList()
	{
		$type = request()->get('type', '');
		$status = request()->get('status', '-1');
		$child_menu_list = array(
			array(
				'url' => "config/notifylist?type=" . $type,
				'menu_name' => "全部"
			),
			array(
				'url' => "config/notifylist?type=" . $type . "&status=0",
				'menu_name' => "未发送"
			),
			array(
				'url' => "config/notifylist?type=" . $type . "&status=1",
				'menu_name' => "发送成功"
			),
			array(
				'url' => "config/notifylist?type=" . $type . "&status=2",
				'menu_name' => "发送失败"
			)
		);
		
		switch (intval($status)) {
			case 0:
				$child_menu_list[1]['active'] = 1;
				break;
			case 1:
				$child_menu_list[2]['active'] = 1;
				break;
			case 2:
				$child_menu_list[3]['active'] = 1;
				break;
			default:
				$child_menu_list[0]['active'] = 1;
		}
		$this->assign("child_menu_list", $child_menu_list);
		
		if (request()->isAjax()) {
			$notice_service = new Notice();
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$search_text = request()->post("search_text", '');
			
			$send_type = request()->post("type", 1);
			$is_send = request()->post("status", '');
			
			$condition = array();
			$condition['send_type'] = $send_type;
			if ($is_send != -1) {
				$condition['is_send'] = $is_send;
			}
			if ($is_send == 2) {
				$condition['is_send'] = -1;
			}
			
			if ($search_text != "") {
				$condition['notice_title'] = array(
					'like',
					'%' . $search_text . '%'
				);
			}
			
			$list = $notice_service->getNoticeRecordsList($page_index, $page_size, $condition, 'create_date desc', '');
			return $list;
		} else {
			$this->assign('type', $type);
			$this->assign('status', $status);
			return view($this->style . 'Config/notifyList');
		}
	}
	
	/**
	 * 通知明细
	 */
	public function notifyDetail()
	{
		if (request()->isAjax()) {
			$notice_service = new Notice();
			$id = request()->post("id", '');
			$condition["id"] = $id;
			$notify_detail = $notice_service->getNotifyRecordsDetail($condition);
			return $notify_detail;
		}
	}
	
	/**
	 * App版本列表，没有APP的话隐藏该功能
	 */
	public function appUpgradeList()
	{
		$config = new WebConfig();
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$res = $config->getAppUpgradeList($page_index, $page_size);
			return $res;
		} else {
			$this->infrastructureChildMenu(16);
			return view($this->style . 'Config/appUpgradeList');
		}
	}
	
	/**
	 * 编辑App版本，没有APP的话隐藏该功能
	 */
	public function editAppUpgrade()
	{
		$config = new WebConfig();
		if (request()->isAjax()) {
			$id = request()->post("id", 0);
			$title = request()->post("title", "");
			$app_type = request()->post("app_type", "");
			$version_number = request()->post("version_number", "");
			$download_address = request()->post("download_address", "");
			$update_log = request()->post("update_log", "");
			$res = $config->editAppUpgrade($id, $title, $app_type, $version_number, $download_address, $update_log);
			return AjaxReturn($res);
		} else {
			$this->infrastructureChildMenu(16);
			$id = request()->get("id", 0);
			$app_upgrade = $config->getAppUpgradeInfo($id);
			$this->assign("app_upgrade", $app_upgrade);
			return view($this->style . 'Config/editAppUpgrade');
		}
	}
	
	/**
	 * 删除App版本
	 */
	public function deleteAppUpgrade()
	{
		$id = request()->post("id", "");
		if (!empty($id)) {
			$config = new WebConfig();
			$res = $config->deleteAppUpgrade($id);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * app欢迎页
	 */
	public function appWelcomePage()
	{
		$config = new WebConfig();
		if (request()->isAjax()) {
			$value = request()->post("value", "");
			$res = $config->setAppWelcomePageConfig($this->instance_id, $value);
			return AjaxReturn($res);
		} else {
			$info = $config->getAppWelcomePageConfig($this->instance_id);
			$this->assign("info", $info['value']);
			return view($this->style . 'Config/appWelcomePage');
		}
	}
	
	/**
	 * 手机端 分类展示方式
	 *
	 */
	public function wapCategoryDisplay()
	{
		// 分类显示方式
		$web_config = new WebConfig();
		$info = $web_config->getWapCategoryDisplay($this->instance_id);
		$this->assign('info', $info);
		return view($this->style . 'Config/wapCategoryDisplay');
	}
	
	public function editWapCategoryDisplay()
	{
		$web_config = new WebConfig();
		$shopid = $this->instance_id;
		if (request()->isAjax()) {
			$classified_display_mode = request()->post("data", 1);
			$res = $web_config->setWapCategoryDisplay($shopid, $classified_display_mode);
			return AjaxReturn($res);
		}
	}

    /**
	 * 小程序端 分类展示方式
	 */
	public function appletCategoryDisplay()
	{
		$web_config = new WebConfig();
		$info = $web_config->getAppletCategoryDisplay($this->instance_id);
		$this->assign('info', $info);
		return view($this->style . 'Config/appletCategoryDisplay');
	}

    /**
     * 修改小程序端 分类展示方式
     */
	public function editAppletCategoryDisplay()
	{
		$web_config = new WebConfig();
		$shopid = $this->instance_id;
		if (request()->isAjax()) {
			$classified_display_mode = request()->post("data", 1);
			$res = $web_config->setAppletCategoryDisplay($shopid, $classified_display_mode);
			return AjaxReturn($res);
		}
	}

	/**
	 * 首页排版
	 */
	public function pageLayout()
	{
		$web_config = new WebConfig();
		if (request()->isAjax()) {
			$data = request()->post("data", "");
			$res = $web_config->setWapPageLayoutConfig($this->instance_id, $data, 1);
			return AjaxReturn($res);
		} else {
			$value = $web_config->getWapPageLayoutConfig($this->instance_id);
			$this->assign("value", json_encode($value));
			return view($this->style . "Config/pageLayout");
		}
	}
	
	/**
	 * API安全
	 */
	public function apiSecure()
	{
		$web_config = new WebConfig();
		if (request()->isAjax()) {
			$data = request()->post("data", "");
			$res = $web_config->setApiSecureConfig($data, 1);
			return AjaxReturn($res);
		} else {
			$api_secure = $web_config->getApiSecureConfig();
			$this->assign("api_secure", $api_secure);
			$this->infrastructureChildMenu(17);
			return view($this->style . "Config/apisecure");
		}
	}
	
	/**
	 * 商品推荐
	 */
	public function goodsRecommend()
	{
		if (request()->isAjax()) {
			$alis_id = request()->post('alis_id', 0);
			$num = request()->post('num', 0);
			$type = request()->post('type', 0);
			$recommend_id = request()->post('recommend_id', 0);
			$recommend_name = request()->post('recommend_name', '');
			$shop = new Shop();
			if (!$type) return -1;
			
			$data = [
				'alis_id' => $alis_id,
				'type' => $type,
				'recommend_name' => $recommend_name,
				'show_num' => $num,
			];
			if ($recommend_id) {
				//编辑
				$data["id"] = $recommend_id;
				$res = $shop->updateGoodsRecommend($data);
			} else {
				//添加
				$res = $shop->addGoodsRecommend($data);
			}
			return AjaxReturn($res);
		}
		
		// 查找一级商品分类
		$goodsCategory = new GoodsCategory();
		$oneGoodsCategory = $goodsCategory->getGoodsCategoryListByParentId(0);
		$this->assign("oneGoodsCategory", $oneGoodsCategory);
		
		//商品标签
		$goods_group = new GoodsGroup();
		$group_list = $goods_group->getGoodsGroupList(1, 0);
		$this->assign("group_list", $group_list['data']);
		
		//品牌列表
		$goodsbrand = new GoodsBrand();
		$brand_list = $goodsbrand->getGoodsBrandList(1, 0);
		$this->assign("brand_list", $brand_list['data']);
		
		$shop = new Shop();
		$list = $shop->getGoodsRecommend(1, 0, []);
		$this->assign("recommend_list", $list);
		return view($this->style . "Config/goodsRecommend");
	}
	
	/**
	 * 首页魔方
	 */
	public function shopCube()
	{
		$web_config = new WebConfig();
		if (request()->isAjax()) {
			$value = request()->post("value", "");
			$res = $web_config->setWapHomeMagicCube($value);
			return AjaxReturn($res);
		} else {
			$shop = new Shop();
			$shopNavTemplate = $shop->getShopNavigationTemplate('2');
			$nav_list = [];
			foreach ($shopNavTemplate as $k => $item) {
				$nav_list[ $k ] = [
					'template_url' => $item['template_url'],
					'template_name' => $item['template_name']
				];
			}
			$this->assign("nav_data", $nav_list);
			
			$info = $web_config->getWapHomeMagicCubeConfig();
			$this->assign('info', $info);
			return view($this->style . "Config/shopCube");
		}
	}
	
	/*
	 * 商品选择弹框控制器
	 */
	public function goodsSelectList()
	{
		if (request()->post()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$goods_name = request()->post("goods_name", "");
			$category_id_1 = request()->post('category_id_1', '');
			$category_id_2 = request()->post('category_id_2', '');
			$category_id_3 = request()->post('category_id_3', '');
			$selectGoodsLabelId = request()->post('selectGoodsLabelId', '');
			$supplier_id = request()->post('supplier_id', '');
			$goods_type = request()->post("goods_type", ""); // 商品类型
			$goods_code = request()->post('code', '');
			$brand_id = request()->post('brand_id', '');
			$data = request()->post("data");
			$alis_id = request()->post("alis_id", '');
			$is_recommend = request()->post("is_recommend", '');//推荐
			$is_new = request()->post("is_new", '');//新品
			$is_hot = request()->post("is_hot", '');//热卖
			$state = $this->getValueByKey($data, 'state');
			$is_have_sku = $this->getValueByKey($data, 'is_have_sku');
			$stock = $this->getValueByKey($data, 'stock');
			
			//商品名称
			$condition = array(
				"goods_name" => [
					"like",
					"%$goods_name%"
				],
			
			);
			
			//商品类型
			if ($goods_type !== "" && $goods_type != 'all') {
				$condition['goods_type'] = [ 'in', $goods_type ];
			}
			//推荐
			if ($is_recommend) {
				$condition['is_recommend'] = 1;
			}
			//新品
			if ($is_new) {
				$condition['is_new'] = 1;
			}
			//热卖
			if ($is_hot) {
				$condition['is_hot'] = 1;
			}
			
			//商品标签
			
			if (!empty($selectGoodsLabelId)) {
				$selectGoodsLabelIdArray = explode(',', $selectGoodsLabelId);
				$selectGoodsLabelIdArray = array_filter($selectGoodsLabelIdArray);
				$str = "FIND_IN_SET(" . $selectGoodsLabelIdArray[0] . ",group_id_array)";
				for ($i = 1; $i < count($selectGoodsLabelIdArray); $i++) {
					$str .= "AND FIND_IN_SET(" . $selectGoodsLabelIdArray[ $i ] . ",group_id_array)";
				}
				$condition[""] = [
					[
						"EXP",
						$str
					]
				];
			}
			//商品编码
			if (!empty($goods_code)) {
				$condition["code"] = array(
					"like",
					"%" . $goods_code . "%"
				);
			}
			
			//供货商
			if ($supplier_id != '') {
				$condition['supplier_id'] = $supplier_id;
			}
			
			//品牌
			if ($brand_id != '') {
				$condition['brand_id'] = $brand_id;
			}
			//商品id
			if ($alis_id != '') {
				$condition['goods_id'] = [ 'in', $alis_id ];
			}
			
			//商品状态
			$condition['state'] = [ 'in', $state ];
			
			//是否有sku
			if ($is_have_sku == 0) {
				$condition["goods_spec_format"] = '[]';
			}
			
			//是否有库存
			if ($stock == 1) {
				$condition['stock'] = [ 'GT', 0 ];
			}
			
			//商品分类
			if (!empty($category_id_3)) {
				$condition["category_id_3"] = $category_id_3;
			} elseif (!empty($category_id_2)) {
				$condition["category_id_2"] = $category_id_2;
			} elseif (!empty($category_id_1)) {
				$condition["category_id_1"] = $category_id_1;
			}
			
			$goods_detail = new Goods();
			$result = $goods_detail->getSearchGoodsList($page_index, $page_size, $condition, "create_time desc");
			return $result;
		} else {
// 	        $goods_id_array = request()->get("goods_id_array", "");
// 	        $this->assign("goods_id_array", $goods_id_array);
			$shop = new Shop();
			$list = $shop->getGoodsRecommend(1, 0);
			$this->assign("recommend_list", $list);
			
			$goods_id_str = '';
			if ($list['data'][0]['type'] == 7) {
				foreach ($list['data'] as $k => $v) {
					$goods_id_str .= $v['alis_id'] . ',';
				}
			}
			$this->assign("goods_id_array", trim($goods_id_str, ','));
			
			$type = request()->get('type');
			$this->assign('type', $type);
			
			$data = request()->get('data');
			$data = rtrim($data, ',');
			$this->assign('data', $data);
			$goods_type = $this->getValueByKey($data, 'goods_type');
			$state = $this->getValueByKey($data, 'state');
			$is_have_sku = $this->getValueByKey($data, 'is_have_sku');
			$stock = $this->getValueByKey($data, 'stock');
			
			// 查找一级商品分类
			$goodsCategory = new GoodsCategory();
			$oneGoodsCategory = $goodsCategory->getGoodsCategoryListByParentId(0);
			$this->assign("oneGoodsCategory", $oneGoodsCategory);
			$goods_group = new GoodsGroup();
			$groupList = $goods_group->getGoodsGroupList(1, 0, [
				'shop_id' => $this->instance_id,
				'pid' => 0
			], "group_id desc");
			if (!empty($groupList['data'])) {
				foreach ($groupList['data'] as $k => $v) {
					$v['sub_list'] = $goods_group->getGoodsGroupList(1, 0, 'pid = ' . $v['group_id'], "group_id desc");
				}
			}
			$this->assign("goods_group", $groupList['data']);
			return view($this->style . "Config/goodsSelectList");
		}
		
	}
	
	//获取传值数组的值
	public function getValueByKey($str, $key)
	{
		$arr = explode(',', $str);
		foreach ($arr as $k => $v) {
			$v_arr = explode(':', $v);
			if ($key == $v_arr[0]) {
				return $v_arr[1];
			}
		}
		
		return 0;
	}
	
	public function deleteGoodsRecommend()
	{
		if (request()->isAjax()) {
			$id = request()->post('id', 0);
			if (!$id) return -1;
			$shop = new Shop();
			$res = $shop->deleteGoodsRecommend($id);
			return AjaxReturn($res);
		}
	}
	
	public function saveRecommendImg()
	{
		if (request()->isAjax()) {
			$id = request()->post('id', 0);
			$img = request()->post('img', '');
			if (!$id) return -1;
			$shop = new Shop();
			$res = $shop->modifyRecommendImg($id, $img);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 手机端分类设置
	 */
	public function wapBottomType()
	{
		$web_config = new WebConfig();
		if (request()->isAjax()) {
			$data = request()->post('nav_data', '');
			$data = [
				'shop_id' => $this->instance_id,
				'template_data' => $data,
			];
			$res = $web_config->setWapBottomType($data);
			return AjaxReturn($res);
		}
		
		//链接地址
		$shop = new Shop();
		$navigation_list = $shop->shopNavigationList(0, 0, [ 'type' => 2 ], 'sort');
		$link_arr = [];
		foreach ($navigation_list['data'] as $k => $v) {
			$url = 'APP_MAIN' . $v['nav_url'];
			$link_arr[ $url ] = $v['nav_title'];
		}
		$info = $web_config->getWapBottomType($this->instance_id);
		$info['data'] = json_decode($info['template_data'], true);
		$this->assign('link_arr', $link_arr);
		
		//展示页面
		$show_page = [
			'index/index' => '首页',
			'goods/category' => '商品分类页',
			'member/index' => '会员中心',
			'goods/cart' => '购物车',
			'goods/groupbuy' => '团购专区',
			'goods/topics' => '专题活动',
			'goods/brand' => '品牌专区',
			'article/lists' => '文章中心',
		];
		
		$footer_data = [ [
			'menu_name' => "首页",
			'color' => '#333',
			'color_hover' => '#126AE4',
			'href' => 'APP_MAIN/index/index',
			'href_name' => '首页',
			'img_src' => "upload/default/wap_nav/nav_home.png",
			'img_src_hover' => "upload/default/wap_nav/nav_home.png"
		], [
			'menu_name' => "商品分类",
			'color' => '#333',
			'color_hover' => '#126AE4',
			'href' => 'APP_MAIN/goods/category',
			'href_name' => '商品分类',
			'img_src' => "upload/default/wap_nav/nav_category.png",
			'img_src_hover' => "upload/default/wap_nav/nav_category.png"
		], [
			'menu_name' => "购物车",
			'color' => '#333',
			'color_hover' => '#126AE4',
			'href' => 'APP_MAIN/goods/cart',
			'href_name' => '购物车',
			'img_src' => "upload/default/wap_nav/nav_cart.png",
			'img_src_hover' => "upload/default/wap_nav/nav_cart.png"
		], [
			'menu_name' => "会员中心",
			'color' => '#333',
			'color_hover' => '#126AE4',
			'href' => 'APP_MAIN/member/index',
			'href_name' => '会员中心',
			'img_src' => "upload/default/wap_nav/nav_member.png",
			'img_src_hover' => "upload/default/wap_nav/nav_member.png"
		] ];
		$this->assign('footer_data', json_encode($footer_data));
		
		$this->assign('show_page', $show_page);
		
		$this->assign('info', $info);
		return view($this->style . "Config/wapBottomType");
	}
	
	/**
	 * 网站页面风格配色方案
	 */
	public function webSiteColorScheme()
	{
		$web_config = new WebConfig();
		if (request()->isAjax()) {
			$flag = request()->post('flag', '');
			$file_name = request()->post('file_name', '');
			$first_color = request()->post('first_color', '');
			$second_color = request()->post('second_color', '');
			$data = [
				'file_name' => $file_name,
				'first_color' => $first_color,
				'second_color' => $second_color
			];
			$res = $web_config->setWebSiteColorScheme($flag, json_encode($data));
			return AjaxReturn($res);
		}
		
		$flag = request()->get('flag', '');
		if (empty($flag) || ($flag != "wap" && $flag != "web")) {
			$this->error('参数错误');
		}
		$this->assign("flag", $flag);
		
		$info = $web_config->getWebSiteColorScheme($flag);
		$this->assign('info', $info);
		
		$theme_list = $this->getThemeList($flag);
		$this->assign("theme_list", $theme_list);
		return view($this->style . "Config/webSiteColorScheme");
	}
	
	
	/**
	 * 获取板块模板列表
	 */
	private function getThemeList($flag)
	{
		$pattern = '~\/[*#]{1}(\S+)[*]\/~';//需要转义/
		$web_config = new WebConfig();
		if ($flag == "web") {
			$template = $web_config->getUseWapTemplate($this->instance_id);
		} elseif ($flag == "web") {
			$template = $web_config->getUsePCTemplate($this->instance_id);
		}
		$template = $web_config->getUseWapTemplate($this->instance_id);
		$style = $template['value'];
		$themes_path = 'template' . DS . $flag . DS . $style . DS . 'public' . DS . 'css' . DS . 'themes' . DS;
		if (is_dir($themes_path)) {
			
			$sub_dir_arr = scandir($themes_path);
			$template_arr = [];
			foreach ($sub_dir_arr as $dir_name) {
				//只保存html文件，过滤其他类型文件
				if (strstr($dir_name, "theme_")) {
					$file_path = $themes_path . $dir_name;
					$str = file_get_contents($file_path);//将整个文件内容读入到一个字符串中
					preg_match($pattern, $str, $str1);
					$color = explode(",", $str1[1]);
					$template_arr[] = [
						'file_name' => $dir_name,
						'color' => $color
					];
				}
			}
			return $template_arr;
		}
	}
	
	public function setstatus()
	{
		$type = request()->post("type", "");
		$is_use = request()->post("is_use", "");
		$web_config = new WebConfig();
		
		if ($type == "WCHAT") {
			$wchat_config = $web_config->setWchatConfigIsuse($is_use);
			return AjaxReturn($wchat_config);
		} else if ($type == "QQLOGIN") {
			$qq_config = $web_config->setQQConfigIsUse($is_use);
			return AjaxReturn($qq_config);
		}
	}
	
	/**
	 * PC端分类展示方式
	 *
	 */
	public function webCategoryDisplay()
	{
		// 分类显示方式
		$web_config = new WebConfig();
		$info = $web_config->getWebCategoryDisplay($this->instance_id);
		$this->assign('info', $info);
		return view($this->style . 'Config/webCategoryDisplay');
	}
	
	/**
	 * PC端分类设置
	 */
	public function editWebCategoryDisplay()
	{
		$web_config = new WebConfig();
		$shopid = $this->instance_id;
		if (request()->isAjax()) {
			$classified_display_mode = request()->post("data", 1);
			$res = $web_config->setWebCategoryDisplay($shopid, $classified_display_mode);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * pc端首页浮动层
	 */
	public function webFloat()
	{
		$shop = new Shop();
		if (request()->isAjax()) {
			$nav_title = request()->post('nav_title', '');
			$nav_url = request()->post('nav_url', '');
			$type = request()->post('type', '');
			$sort = request()->post('sort', '');
			$align = request()->post('align', '');
			$nav_type = request()->post('nav_type', '');
			$is_blank = request()->post('is_blank', '');
			$template_name = request()->post("template_name", '');
			$nav_icon = request()->post("nav_icon", '');
			$is_show = request()->post('is_show', '');
			$data = array(
				'shop_id' => 0,
				'nav_title' => $nav_title,
				'nav_url' => $nav_url,
				'type' => $type,
				'align' => $align,
				'sort' => $sort,
				'nav_type' => $nav_type,
				'is_blank' => $is_blank,
				'template_name' => $template_name,
				'create_time' => time(),
				'modify_time' => time(),
				'nav_icon' => $nav_icon,
				'is_show' => $is_show
			);
			$retval = $shop->setWebFloatConfig($data);
			return AjaxReturn($retval);
		} else {
			$shop = new Shop();
			$retval = $shop->getWebFloatConfig();
			$this->assign('retval', $retval);
			return view($this->style . "Config/webFloat");
		}
	}
	
	
	public function wapFloat()
	{
		
		$shop = new Shop();
		if (request()->isAjax()) {
			$nav_title = request()->post('nav_title', '');
			$nav_url = request()->post('nav_url', '');
			$type = request()->post('type', '');
			$sort = request()->post('sort', '');
			$align = request()->post('align', '');
			$nav_type = request()->post('nav_type', '');
			$is_blank = request()->post('is_blank', '');
			$template_name = request()->post("template_name", '');
			$nav_icon = request()->post("nav_icon", '');
			$is_show = request()->post('is_show', '');
			$data = array(
				'shop_id' => 0,
				'nav_title' => $nav_title,
				'nav_url' => $nav_url,
				'type' => $type,
				'align' => $align,
				'sort' => $sort,
				'nav_type' => $nav_type,
				'is_blank' => $is_blank,
				'template_name' => $template_name,
				'create_time' => time(),
				'modify_time' => time(),
				'nav_icon' => $nav_icon,
				'is_show' => $is_show
			);
			$retval = $shop->setWapFloatConfig($data);
			return AjaxReturn($retval);
		} else {
			$retval = $shop->getWapFloatConfig();
			$this->assign('retval', $retval);
			return view($this->style . "Config/wapFloat");
		}
	}
	
	/**
	 * 表结构缓存(关闭调试模式)
	 */
	public function tableCache()
	{
		ini_set('max_execution_time', 120);
		$schema = new Schema();
		$input = new Input('optimize:schema');
		$output = new Output();
		$res = $schema->doExecute($input, $output);
        if($res == 'succeed') {
	        return AjaxReturn(1);
	    }else{
	        return ['code' => -1, 'message' => $res];
	    }
	}
	
	/**
	 * 命名空间缓存
	 */
	public function classCache()
	{
		ini_set('max_execution_time', 120);
		$autoload = new Autoload();
		$input = new Input('optimize:autoload');
		$output = new Output();
		$res = $autoload->doExecute($input, $output);
        if($res == 'succeed') {
	        return AjaxReturn(1);
	    }else{
	        return ['code' => -1, 'message' => $res];
	    }
	}
	
	/**
	 * 更新缓存
	 */
	public function renewCache(){
	    $child_menu_list = array(
	        array(
	            'url' => "extend/addonslist",
	            'menu_name' => "插件管理",
	            "active" => 0
	        ),
	        array(
	            'url' => "extend/hookslist",
	            'menu_name' => "钩子管理",
	            "active" => 0
	        ),
	        array(
	            'url' => "system/modulelist",
	            'menu_name' => "系统菜单",
	            "active" => 0
	        ),
	        array(
	            'url' => "dbdatabase/databaselist",
	            'menu_name' => "数据备份",
	            "active" => 0
	        ),
	        array(
	            'url' => "dbdatabase/importdatalist",
	            'menu_name' => "数据恢复",
	            "active" => 0
	        ),
	        array(
	            'url' => "config/renewcache",
	            'menu_name' => "更新缓存",
	            "active" => 1
	        )
	    );
	    $this->assign("child_menu_list", $child_menu_list);
	    return view($this->style . 'Config/renewCache');
	}
	
	/**
	 * 手机端个人中心菜单管理 
	 */
	public function wapEntranceManage(){
	    $config = new WebConfig();
	    $list = $config->getWapEntranceList();
	    $this->assign('value', json_encode($list));
	    return view($this->style . 'Config/wapEntranceManage');
	}
	
	/**
	 * 删除入口
	 */
	public function deleteWapEntrance(){
	    if(request()->isAjax()){
	        $id = request()->post('id', '');
	        $config = new WebConfig();
	        $res = $config->deleteWapEntrance($id);
	        return AjaxReturn($res);	        
	    }
	}
	
	/**
	 * 入口编辑
	 */
	public function editWapEntrance(){
	    if(request()->isAjax()){
	        $value = request()->post('value', '');
	        $data = json_decode($value, true);
	        $config = new WebConfig();
	        $res = $config->editWapEntrance($data);
	        return AjaxReturn($res);
	    }
	}
	
	/**
	 * 入口排序改变
	 */
	public function entranceSortChange(){
	    if(request()->isAjax()){
	        $value = request()->post('value', '');
	        $data = json_decode($value, true);
	        $config = new WebConfig();
	        $res = $config->entranceSortChange($data);
	        return AjaxReturn($res);
	    }
	}
}