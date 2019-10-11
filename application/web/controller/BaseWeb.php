<?php
/**
 * BaseWeb.php
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

namespace app\web\controller;

use data\service\Config as WebConfig;
use data\service\WebSite;
use think\Controller;
use think\Cookie;
use think\Session;

class BaseWeb extends Controller
{
	
	protected $uid;
	
	protected $instance_id;
	
	protected $web_info;
	
	public $style;
	
	protected $use_pc_template;
	
	//加密后的uid
	public $token;
	
	//会员基础信息
	public $member_detail;
	
	public function __construct()
	{
		parent::__construct();
		$this->init();
	}
	
	/**
	 * 基础数据初始化
	 */
	public function init()
	{
		
		$config = new WebConfig();
		$web_site = new WebSite();
		$this->web_info = $web_site->getWebSiteInfo();
		
		//初始化模板加载数据
		if (!request()->isAjax()) {
			
			if ($this->web_info['web_status'] == 3 && $this->web_info['wap_status'] == 1) {
				Cookie::delete("default_client");
				$this->redirect(__URL(\think\Config::get('view_replace_str.APP_MAIN')));
			} elseif ($this->web_info['web_status'] == 2) {
				Cookie::delete("default_client");
				// 首页特殊处理
				$controller = \think\Request::instance()->controller();
				$action = \think\Request::instance()->action();
				if ($controller != 'Index' || $action != 'index') {
					webClose($this->web_info['close_reason']);
				}
			} elseif (($this->web_info['web_status'] == 3 && $this->web_info['wap_status'] == 3) || ($this->web_info['web_status'] == 3 && $this->web_info['wap_status'] == 2)) {
				Cookie::delete("default_client");
				webClose($this->web_info['close_reason']);
			}
			
			$keyword = request()->get('keyword', '');
			if ($keyword !== "") {
				$keyword = ihtmlspecialchars($keyword);
			}
			$this->assign("keyword", $keyword);
		}
		
		//初始化默认端口
		$default_client = request()->cookie("default_client", "");
		$this->assign("default_client", $default_client);
		
		$this->token = session("niu_access_token");
		$this->member_detail = session("niu_member_detail");
		
		//初始化基础数据
		if (!empty($this->member_detail)) {
			$this->uid = $this->member_detail['user_info']['uid'];
			$this->assign("uid", $this->uid);
		} else {
			$this->assign("uid", '');
		}
		
		$this->instance_id = 0;
		
		//SEO搜索引擎
		$seo = api("System.Config.seo");
		$seo = $seo['data'];
		$this->assign("seo_config", $seo);
		
		$color_scheme = api("System.Config.webSiteColorScheme",['flag'=>'web']);
		$color_scheme = $color_scheme['data'];
		$theme_css = "theme.css";
		if(!empty($color_scheme)){
			$theme_css = $color_scheme['file_name'];
		}
		$this->assign("theme_css", $theme_css);
		
		$this->assign("member_detail", $this->member_detail);
		
		$this->assign("title", $this->web_info['title']);
		$this->assign("web_info", $this->web_info);
		$this->assign("title_before", '');
		$this->assign('page_size', PAGESIZE);
		
		// 获取当前使用的PC端模板
		$this->use_pc_template = $config->getUsePCTemplate($this->instance_id);
		if (empty($this->use_pc_template)) {
			$this->use_pc_template['value'] = 'default';
		}
		
		if (!checkTemplateIsExists("web", $this->use_pc_template['value'])) {
			$this->error("模板配置有误，请联系商城管理员");
		}
		
		$default_images = api("System.Config.defaultImages");
		$default_images = $default_images['data'];
		$this->assign("default_goods_img", $default_images["value"]["default_goods_img"]); // 默认商品图片
		$this->assign("default_headimg", $default_images["value"]["default_headimg"]); // 默认用户头像
		$this->assign("default_cms_thumbnail", $default_images["value"]["default_cms_thumbnail"]); // 默认文章缩略图
		
		$this->style = "web/" . $this->use_pc_template['value'] . "/";
		$this->assign("style", "web/" . $this->use_pc_template['value']);
		$this->assign("base", "web/" . $this->use_pc_template['value'] . '/base');
		$this->assign("member_base", "web/" . $this->use_pc_template['value'] . '/member/member_base');
		$this->assign("goods_detail_base", "web/" . $this->use_pc_template['value'] . '/goods/detail');
	}
	
	public function _empty($name)
	{
	}
	
	protected function view($template = '', $vars = [], $replace = [], $code = 200)
	{
		$view_replace_str = [
			'WEB_CSS' => __ROOT__ . '/template/web/' . $this->use_pc_template['value'] . '/public/css',
			'WEB_FONT' => __ROOT__ . '/template/web/' . $this->use_pc_template['value'] . '/public/font',
			'WEB_JS' => __ROOT__ . '/template/web/' . $this->use_pc_template['value'] . '/public/js',
			'WEB_IMG' => __ROOT__ . '/template/web/' . $this->use_pc_template['value'] . '/public/img',
			'WEB_PLUGIN' => __ROOT__ . '/template/web/' . $this->use_pc_template['value'] . '/public/plugin',
		];
		
		if (empty($replace)) {
			$replace = $view_replace_str;
		} else {
			$replace = array_merge($view_replace_str, $replace);
		}
		return view($template, $vars, $replace, $code);
	}
	
	/**
	 * js中调用api
	 */
	public function ajaxApi()
	{
		$method = input("method", "");
		$param = input("param", "");
		if (empty($method)) {
			return [
				'title' => "javascript调用api",
				'data' => "",
				'code' => -400,
				'message' => "接口发生错误：method is not empty",
			];
		}
		if (!empty($param)) {
			$param = json_decode($param, true);
		}
		$res = api($method, $param);
		return $res;
	}
	
	/**
	 * 语言包接口
	 */
	public function langApi()
	{
		$data = input("data", "");
		if (!empty($data)) {
			$data = explode(",", $data);
			//键值反转
			$data = array_flip($data);
			
			foreach ($data as $k => $v) {
				$data[ $k ] = lang($k);
			}
		}
		return $data;
	}
	
	/**
	 * 随机验证码生成
	 */
	public function random()
	{
		$len = 4;
		$srcstr = "1a2s3d4f5g6hj8k9qwertyupzxcvbnm";
		mt_srand();
		$strs = "";
		for ($i = 0; $i < $len; $i++) {
			$strs .= $srcstr[ mt_rand(0, 30) ];
		}
		
		Session::set('randomCode', $strs);
		
		// 声明需要创建的图层的图片格式
		@ header("Content-Type:image/png");
		
		// 验证码图片的宽度
		$width = 80;
		// 验证码图片的高度
		$height = 35;
		// 创建一个图层
		$im = imagecreate($width, $height);
		// 背景色
		$back = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
		// 模糊点颜色
		$pix = imagecolorallocate($im, 187, 230, 247);
		// 字体色
		$font = imagecolorallocate($im, 41, 163, 238);
		// 绘模糊作用的点
		mt_srand();
		for ($i = 0; $i < 1000; $i++) {
			imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $pix);
		}
		// 输出字符
		imagestring($im, 10, 15, 10, $strs, $font);
		
		// 输出矩形
		imagerectangle($im, 0, 0, $width - 1, $height - 1, $font);
		// 输出图片
		imagepng($im);
		
		imagedestroy($im);
		
		$strs = md5($strs);
		
		return $strs;
	}
	
	/**
	 * 检测用户
	 */
	protected function checkLogin()
	{
		$redirect = __URL(__URL__ . "/login");
		if (empty($this->token)) {
			$_SESSION['login_pre_url'] = __URL(__URL__ . $_SERVER['PATH_INFO']);
			$this->redirect($redirect);
		}
	}
}