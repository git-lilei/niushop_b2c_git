<?php

use think\Route;
use think\Cookie;
use think\Request;

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
$root_url = \think\Request::instance()->url(true);
$upload_url = \think\Request::instance()->domain() . \think\Request::instance()->root() . '/upload';
if (strpos($root_url, $upload_url) !== false) {
	exit();
}
/*****************************************************************************************************设置后台登录模块**********************************************************************************/
//检测后台系统模块
if (ADMIN_MODULE != 'admin') {
	Route::group(ADMIN_MODULE, function () {
		Route::rule(':controller/:action', 'admin/:controller/:action');
		Route::rule(':controller', 'admin/:controller/index');
		Route::rule('', 'admin/index/index');
		
	});
	Route::group('admin', function () {
		Route::rule(':controller/:action', 'web/:controller/:action');
		Route::rule(':controller', 'web/:controller/index');
		Route::rule('', 'web/index/index');
		
	});
}


/************************************************************************************************检测入口文件*****************************************************************************************/
//检测入口文件
$base_file = Request::instance()->baseFile();

$is_api = strpos($base_file, 'api.php');
$pay = strpos($base_file, 'pay.php');
if ($pay) {
    Route::bind('wap/pay/payNotify');
}
/********************************************************************************检测打开端口******************************************************************************************************/
//检测浏览器类型以及显示方式(电脑端、手机端)
function getShowModule()
{
	$default_client = Cookie::get('default_client');
	if (!empty($default_client)) {
		$default_client = Cookie::get('default_client');
	} else {
		if (Request::instance()->get('default_client') == 'web') {
			$default_client = 'web';
		} else {
			$default_client = 'wap';
		}
	}
	$is_mobile = Request::instance()->isMobile();
	
	if ($is_mobile) {
		return 'wap';
	} else {
		return 'web';
	}
}

$show_module = getShowModule();
/*****************************************************************************************************针对商品详情设置路由***************************************************************************/
//设置商品详情页面

/*     if($show_module == 'shop')
  {
	  $goods_info_url = 'shop/goods/detail';
	  $shop_url       = 'shop/index/index';
  }else{
	  $goods_info_url = 'wap/goods/detail';
	  $shop_url       = 'wap/index/index';
  }  */
//pc端开启路由去除shop
/*****************************************************************************************************普通路由设置开始******************************************************************************/

// 第三方登录回调
$code = Request::instance()->get("code", "");
if (!empty($code)) {
	Route::bind('wap/login/callback');
}

$common_route = [
	//pc端商品相关
	/*    ''           =>[
	 '/'       =>   [$shop_url],
	], */
	'[goods]' => [
		
		//商品列表
		//  'goodsinfo'     => [$goods_info_url],
		':action' => [ 'web/goods/:action' ],
	
	],
	'[list]' => [
		
		//商品列表
		'/' => [ 'web/goods/lists' ],
	
	],
	'[index]' => [
		
		//商品列表
		':action' => [ 'web/index/:action' ],
		'/' => [ 'web/index/index' ],
	],
	'[help]' => [
		
		//商品列表
		':action' => [ 'web/help/:action' ],
		'/' => [ 'web/help/index' ],
	],
	'[login]' => [
		
		//商品列表
		':action' => [ 'web/login/:action' ],
		'/' => [ 'web/login/index' ],
	],
	'[member]' => [
		
		//商品列表
		':action' => [ 'web/member/:action' ],
		'/' => [ 'web/member/index' ],
	],
	'[order]' => [
		
		//商品列表
		':action' => [ 'web/order/:action' ],
		'/' => [ 'web/order/index' ],
	],
	'[topic]' => [
		
		//商品列表
		':action' => [ 'web/topic/:action' ],
		'/' => [ 'web/topic/index' ],
	],
	'[article]' => [
		
		//文章
		':action' => [ 'web/article/:action' ],
		'/' => [ 'web/article/index' ],
	],
	'[shop]' => [
		
		':controller/:action' => [ 'web/:controller/:action' ],
		':controller' => [ 'web/:controller/index' ],
		'/' => [ 'web/index/index' ],
	],
	'[notice]' => [
		
		//商品列表
		':action' => [ 'web/notice/:action' ],
		'/' => [ 'web/notice/index' ],
	],
	'[wap]' => [
		':controller/:action' => [ 'wap/:controller/:action' ],
		':controller' => [ 'wap/:controller/index' ],
		'/' => [ 'wap/index/index' ],
	],
	'[' . ADMIN_MODULE . ']' => [
		':controller/:action' => [ 'admin/:controller/:action' ],
		':controller' => [ 'admin/:controller/index' ],
		'/' => [ 'admin/index/index' ],
	],


];
$api_route = [];
/*****************************************************************************************************普通路由设置结束******************************************************************************/
if ($is_api) {
	return $api_route;
} else {
	return $common_route;
}