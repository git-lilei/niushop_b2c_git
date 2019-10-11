<?php
// +----------------------------------------------------------------------
// | NiuCloud [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: niuteam <niuteam@163.com>
// +----------------------------------------------------------------------
namespace app\common\behavior;

use think\Cache;
use think\Request;
use think\Log;

/**
 * 初始化基础信息
 * @author niuteam
 */
class InitBase
{
	/**
	 * 初始化行为入口  入口必须是run
	 */
	public function run()
	{
	    
		// 初始化常量
		$this->initConst();
		// 注册命名空间
		$this->registerNamespace();
		$public_config = defined('PUBLIC_CONFIG') ? 1 : 0;
		if ($public_config) {
			$this->initConfigPublic();
		} else
			$this->initConfig();
		
		//检测入口文件
		$base_file = Request::instance()->baseFile();
		$is_index = strpos($base_file, 'index.php');
		if ($is_index) {
		    $this->event();
		}
	}
	
	/**
	 * 初始化常量
	 */
	private function initConst()
	{
		
		$root = Request::instance()->root();
		$root = str_replace('/index.php', '', $root);
		define("__ROOT__", $root);
		/**
		 * *************************************************************伪静态*******************************************************************************
		 */
		$url = \think\Request::instance()->url(true);
		$url = strtolower($url);
		if(strpos($url, 'invokefunction') || strpos($url, 'think\request') || strpos($url, 'think\app'))
		{
		    exit();
		}
		// 入口文件,系统未开启伪静态
		$rewrite = REWRITE_MODEL;
		if (!$rewrite) {
			define('__URL__', \think\Request::instance()->domain() . \think\Request::instance()->baseFile());
		} else {
			// 系统开启伪静态
			if (empty($root)) {
				define('__URL__', \think\Request::instance()->domain());
			} else {
				define('__URL__', \think\Request::instance()->domain() . \think\Request::instance()->root());
			}
		}
		/**
		 * *************************************************************伪静态*******************************************************************************
		 */
		
		define('UPLOAD', "upload"); // 上传文件路径
		define('ADMIN_MODULE', "ad19blmp"); // 重新定义后台模块
		
		define("PAGESIZE", 14);
		define("PAGESHOW", 5);
		define("PICTURESIZE", 15);
		define('ADDON_DIR', 'addons');
		// 评价图片存放路径
		define("UPLOAD_COMMENT", UPLOAD . "/comment/");
		
		// 插件目录
		define('ADDON_PATH', ROOT_PATH . 'addons' . DS);
		// 数据库路径
		define('DB_PATH', UPLOAD . '/dbsql');
		// 条形码存放路径
		define("BAR_CODE", UPLOAD . '/barcode');
		
		// 商品视频存放路径
		define("GOODS_VIDEO_PATH", UPLOAD . '/goods_video');
		
		// 系统默认图
		define("UPLOAD_WEB_COMMON", UPLOAD . '/web_common/');
		
		//商家服务小图标
		define("UPLOAD_ICO", UPLOAD . '/upload_ico/');
		
		//存放文件
		define("UPLOAD_FILE", UPLOAD . '/upload_file/');
		
		//水印图片
		define("UPLOAD_WATERMARK", UPLOAD . '/upload_watermark/');
		
		
	}
	
	
	/**
	 * 初始化配置信息
	 */
	private function initConfigPublic()
	{
		
		$config_array['template'] = [
			// 模板引擎类型 支持 php think 支持扩展
			'type' => 'Think',
			// 模板路径
			'view_path' => './template/',
			
			// 模板后缀
			'view_suffix' => 'html',
			// 模板文件名分隔符
			'view_depr' => DS,
			// 模板引擎普通标签开始标记
			'tpl_begin' => '{',
			// 模板引擎普通标签结束标记
			'tpl_end' => '}',
			// 标签库标签开始标记
			'taglib_begin' => '{',
			// 标签库标签结束标记
			'taglib_end' => '}',
			'taglib_load' => true, // 是否使用内置标签库之外的其它标签库，默认自动检测
			'taglib_build_in' => 'cx',
			// 引入模板标签
			'taglib_pre_load' => 'data\taglib\Niu',
			
			// 是否开启模板编译缓存,设为false则每次都会重新编译
			'tpl_cache' => true
		]; // 内置标签库名称(标签使用不必指定标签库名称),以逗号分隔 注意解析顺序
		
		//模板常量
		$config_array['view_replace_str'] = [
			
			'__PUBLIC__' => __ROOT__ . '/public/',
			'__STATIC__' => __ROOT__ . '/public/static',
			'ADMIN_IMG' => __ROOT__ . '/template/admin/public/images',
			'ADMIN_CSS' => __ROOT__ . '/template/admin/public/css',
			'ADMIN_JS' => __ROOT__ . '/template/admin/public/js',
			'__TEMP__' => '../template',
			'__ROOT__' => __ROOT__,
			'__URL__' => __URL__,
			'UPLOAD_URL' => __URL__ . '/' . ADMIN_MODULE,
			'ADMIN_MODULE' => ADMIN_MODULE,
			'ADMIN_MAIN' => __URL__ . '/' . ADMIN_MODULE,
			'APP_MAIN' => __URL__ . '/wap',
			'SHOP_MAIN' => __URL__ . '',
			'__UPLOAD__' => __ROOT__,
			'__MODULE__' => '/' . ADMIN_MODULE,
			'__ADDONS__' => __ROOT__ . '/addons', // 插件目录
			
			// 上传文件路径
			'UPLOAD_GOODS' => UPLOAD . '/goods/', // 存放商品图片主图
			'UPLOAD_GOODS_SKU' => UPLOAD . '/goods_sku/', // 存放商品sku图片
			'UPLOAD_GOODS_BRAND' => UPLOAD . '/goods_brand/', // 存放商品品牌图
			'UPLOAD_GOODS_GROUP' => UPLOAD . '/goods_group/', // 存放商品分组图片
			'UPLOAD_GOODS_CATEGORY' => UPLOAD . '/goods_category/', // 存放商品分组图片
			'UPLOAD_COMMON' => UPLOAD . '/common/', // 存放公共图片、网站logo、独立图片、没有任何关联的图片
			'UPLOAD_AVATOR' => UPLOAD . '/avator/', // 存放用户头像
			'UPLOAD_PAY' => UPLOAD . '/pay/', // 存放支付生成的二维码图片
			'UPLOAD_ADV' => UPLOAD . '/image_collection/', // //存放广告位图片，由于原“advertising”文件夹名称会被过滤掉。2017年9月14日 14:58:07 修改为“image_collection”
			'UPLOAD_EXPRESS' => UPLOAD . '/express/', // 存放物流
			'UPLOAD_CMS' => UPLOAD . '/cms/', // 存放文章图片
			'UPLOAD_VIDEO' => UPLOAD . "/video/",// 存放视频文件
			
			'SHOP_CSS' => '../template/shop/public/css',
			'SHOP_JS' => '../template/shop/public/js',
			'SHOP_IMG' => '../template/shop/public/img',
			'SHOP_PLUGIN' => '../template/shop/public/plugin',
			'WAP_CSS' => __ROOT__ . '/template/wap/public/css',
			'WAP_JS' => __ROOT__ . '/template/wap/public/js',
			'WAP_IMG' => __ROOT__ . '/template/wap/public/img',
			'WAP_PLUGIN' => __ROOT__ . '/template/wap/public/plugin',
		];
		// 验证码排至文件
		$config_array['captcha'] = [
			
			// 验证码字符集合
			'codeSet' => '0123456789',
			// 验证码字体大小(px)
			'fontSize' => 15,
			
			// 是否画混淆曲线
			'useCurve' => false,
			
			// 是否添加杂点
			'useNoise' => false,
			
			// 验证码图片高度
			'imageH' => 30,
			// 验证码图片宽度
			'imageW' => 100,
			// 验证码位数
			'length' => 4,
			// 验证成功后是否重置
			'reset' => true
		];
		$config_array['paginate'] = [
			'type' => 'bootstrap',
			'var_page' => 'page',
			'list_rows' => PAGESIZE,
			'list_showpages' => PAGESHOW,
			'picture_page_size' => PICTURESIZE
		];
		config($config_array);
	}
	
	/**
	 * 初始化配置信息
	 */
	private function initConfig()
	{
		
		$config_array['template'] = [
			// 模板引擎类型 支持 php think 支持扩展
			'type' => 'Think',
			// 模板路径
			'view_path' => 'template/',
			
			// 模板后缀
			'view_suffix' => 'html',
			// 模板文件名分隔符
			'view_depr' => DS,
			// 模板引擎普通标签开始标记
			'tpl_begin' => '{',
			// 模板引擎普通标签结束标记
			'tpl_end' => '}',
			// 标签库标签开始标记
			'taglib_begin' => '{',
			// 标签库标签结束标记
			'taglib_end' => '}',
			'taglib_load' => true, // 是否使用内置标签库之外的其它标签库，默认自动检测
			'taglib_build_in' => 'cx',
			// 引入模板标签
			'taglib_pre_load' => 'data\taglib\Niu',
			
			// 是否开启模板编译缓存,设为false则每次都会重新编译
			'tpl_cache' => false
		]; // 内置标签库名称(标签使用不必指定标签库名称),以逗号分隔 注意解析顺序
		
		//模板常量
		$config_array['view_replace_str'] = [
			
			'__PUBLIC__' => __ROOT__ . '/public/',
			'__STATIC__' => __ROOT__ . '/public/static',
			'ADMIN_IMG' => __ROOT__ . '/template/admin/public/images',
			'ADMIN_CSS' => __ROOT__ . '/template/admin/public/css',
			'ADMIN_JS' => __ROOT__ . '/template/admin/public/js',
			'__TEMP__' => __ROOT__ . '/template',
			'__ROOT__' => __ROOT__,
			'__URL__' => __URL__,
			'UPLOAD_URL' => __URL__ . '/' . ADMIN_MODULE,
			'ADMIN_MODULE' => ADMIN_MODULE,
			'ADMIN_MAIN' => __URL__ . '/' . ADMIN_MODULE,
			'APP_MAIN' => __URL__ . '/wap',
			'SHOP_MAIN' => __URL__ . '',
			'__UPLOAD__' => __ROOT__,
			'__MODULE__' => '/' . ADMIN_MODULE,
			'__ADDONS__' => __ROOT__ . '/addons', // 插件目录
			
			// 上传文件路径
			'UPLOAD_GOODS' => UPLOAD . '/goods/', // 存放商品图片主图
			'UPLOAD_GOODS_SKU' => UPLOAD . '/goods_sku/', // 存放商品sku图片
			'UPLOAD_GOODS_BRAND' => UPLOAD . '/goods_brand/', // 存放商品品牌图
			'UPLOAD_GOODS_GROUP' => UPLOAD . '/goods_group/', // 存放商品分组图片
			'UPLOAD_GOODS_CATEGORY' => UPLOAD . '/goods_category/', // 存放商品分组图片
			'UPLOAD_COMMON' => UPLOAD . '/common/', // 存放公共图片、网站logo、独立图片、没有任何关联的图片
			'UPLOAD_AVATOR' => UPLOAD . '/avator/', // 存放用户头像
			'UPLOAD_PAY' => UPLOAD . '/pay/', // 存放支付生成的二维码图片
			'UPLOAD_ADV' => UPLOAD . '/image_collection/', // //存放广告位图片，由于原“advertising”文件夹名称会被过滤掉。2017年9月14日 14:58:07 修改为“image_collection”
			'UPLOAD_EXPRESS' => UPLOAD . '/express/', // 存放物流
			'UPLOAD_CMS' => UPLOAD . '/cms/', // 存放文章图片
			'UPLOAD_VIDEO' => UPLOAD . "/video/",// 存放视频文件
			
			'SHOP_CSS' => __ROOT__ . '/template/shop/public/css',
			'SHOP_JS' => __ROOT__ . '/template/shop/public/js',
			'SHOP_IMG' => __ROOT__ . '/template/shop/public/img',
			'SHOP_PLUGIN' => __ROOT__ . '/template/shop/public/plugin',
			'WAP_CSS' => __ROOT__ . '/template/wap/public/css',
			'WAP_JS' => __ROOT__ . '/template/wap/public/js',
			'WAP_IMG' => __ROOT__ . '/template/wap/public/img',
			'WAP_PLUGIN' => __ROOT__ . '/template/wap/public/plugin',
		];
		// 验证码排至文件
		$config_array['captcha'] = [
			
			// 验证码字符集合
			'codeSet' => '0123456789',
			// 验证码字体大小(px)
			'fontSize' => 15,
			
			// 是否画混淆曲线
			'useCurve' => false,
			
			// 是否添加杂点
			'useNoise' => false,
			
			// 验证码图片高度
			'imageH' => 30,
			// 验证码图片宽度
			'imageW' => 100,
			// 验证码位数
			'length' => 4,
			// 验证成功后是否重置
			'reset' => true
		];
		$config_array['paginate'] = [
			'type' => 'bootstrap',
			'var_page' => 'page',
			'list_rows' => PAGESIZE,
			'list_showpages' => PAGESHOW,
			'picture_page_size' => PICTURESIZE
		];
		config($config_array);
	}
	
	/**
	 * 注册命名空间
	 */
	private function registerNamespace()
	{
		$public_config = defined('PUBLIC_CONFIG') ? 1 : 0;
		if ($public_config) {
			\think\Loader::addNamespace('data', '../data/');
		} else
			\think\Loader::addNamespace('data', 'data/');
		
	}
	
	/**
	 * 执行事件
	 */
	private function event()
	{
		$cache = Cache::tag('config')->get('load_task');
		$last_time = cache("last_load_time");
		if(empty($last_time))
		{
		    $last_time = 0;
		}
		if (empty($cache)||time()-$last_time > 300) {
			Cache::tag('config')->set('load_task', 1);
			$redirect = __URL(__URL__ . "/wap/task/event");
        	$ch = curl_init();
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        	curl_setopt($ch, CURLOPT_HEADER, true);
        	curl_setopt($ch, CURLOPT_URL, $redirect);
        	curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        	curl_exec($ch);
		}
		
	}
	
}