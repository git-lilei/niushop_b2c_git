<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
use data\extend\Barcode;
use data\extend\email\Email;
use data\extend\QRcode as QRcode;
use data\service\Extend;
use data\service\WebSite;
use think\Config;
use think\Hook;
use think\Request;
use think\response\Redirect;
use think\Route;
use think\Session;
use think\View;
use think\Response;
use think\exception\HttpResponseException;

// 错误级别
// error_reporting(E_ERROR | E_WARNING | E_PARSE);
// 去除警告错误
error_reporting(E_ALL ^ E_NOTICE);
$public_config = defined('PUBLIC_CONFIG') ? 1 : 0;
if ($public_config) {
	\think\Loader::addNamespace('data', '../data/');
} else
	\think\Loader::addNamespace('data', 'data/');

define("UPLOAD_AVATOR", 'avator');
/**
 * 配置pc端缓存
 */
function getShopCache()
{
	if (!Request::instance()->isAjax()) {
		$model = Request::instance()->module();
		$model = strtolower($model);
		$controller = Request::instance()->controller();
		$controller = strtolower($controller);
		$action = Request::instance()->action();
		$action = strtolower($action);
		if ($model == 'web' && $controller == 'index' && $action = "index") {
			if (Request::instance()->isMobile()) {
				Redirect::create("wap/index/index");
			} else {
				Request::instance()->cache('__URL__', 1800);
			}
		}
		if ($model == 'web' && $controller != 'goods' && $controller != 'member') {
			Request::instance()->cache('__URL__', 1800);
		}
		if ($model == 'web' && $controller == 'goods' && $action == 'brandlist') {
			Request::instance()->cache('__URL__', 1800);
		}
	}
}

/**
 * 关闭站点
 */
function webClose($reason)
{
	$view = new View();
	if (Request::instance()->isMobile()) {
		$result = $view->instance(Config::get('view_replace_str'))->fetch('./template/wap/wap_close_tpl.html', [ 'reason' => $reason ]);
	} else {
		$result = $view->instance(Config::get('view_replace_str'))->fetch('./template/web/web_close_tpl.html', [ 'reason' => $reason ]);
	}
	$response = Response::create($result, 'html')->header([]);
	throw new HttpResponseException($response);
}


/**
 * 获取手机端缓存
 */
function getWapCache()
{
	if (!Request::instance()->isAjax()) {
		$model = Request::instance()->module();
		$model = strtolower($model);
		$controller = Request::instance()->controller();
		$controller = strtolower($controller);
		$action = Request::instance()->action();
		$action = strtolower($action);
		// 店铺页面缓存8分钟
		if ($model == 'wap' && $controller == 'shop' && $action == 'index') {
			Request::instance()->cache('__URL__', 480);
		}
		if ($model == 'wap' && $controller != 'goods' && $controller != 'member') {
			Request::instance()->cache('__URL__', 1800);
		}
		if ($model == 'wap' && $controller == 'goods' && $action != 'brandlist') {
			Request::instance()->cache('__URL__', 1800);
		}
		if ($model == 'wap' && $controller == 'goods' && $action != 'goodsGroupList') {
			Request::instance()->cache('__URL__', 1800);
		}
	}
}

// 应用公共函数库
/**
 * 循环删除指定目录下的文件及文件夹
 *
 * @param string $dirpath 文件夹路径
 */
function NiuDelDir($dirpath)
{
	$dh = opendir($dirpath);
	while (($file = readdir($dh)) !== false) {
		if ($file != "." && $file != "..") {
			$fullpath = $dirpath . "/" . $file;
			if (!is_dir($fullpath)) {
				unlink($fullpath);
			} else {
				NiuDelDir($fullpath);
				rmdir($fullpath);
			}
		}
	}
	closedir($dh);
	$isEmpty = true;
	$dh = opendir($dirpath);
	while (($file = readdir($dh)) !== false) {
		if ($file != "." && $file != "..") {
			$isEmpty = false;
			break;
		}
	}
	return $isEmpty;
}

/**
 * 生成数据的返回值
 */
function AjaxReturn($err_code, $data = [], $message = "")
{
	if (empty($message)) {
		$code_message = getErrorInfo($err_code);
	} else {
		$code_message = $message;
	}
	
	$rs = [
		'code' => $err_code,
		'message' => $code_message
	];
	if (!empty($data))
		$rs['data'] = $data;
	return $rs;
}

/**
 * 判断当前是否是微信浏览器
 */
function isWeixin()
{
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
		return 1;
	}
	return 0;
}

/**
 * 判断当前是否微信小程序
 */
function isWechatApplet($uid = 0)
{
	$model = Request::instance()->module();
	if (Session::get($model . $uid . 'from') == 'WECHATAPPLET') {
		return 1;
	}
	
	return 0;
}

/**
 * 获取上传根目录
 */
function getUploadPath()
{
	$list = \think\config::get("view_replace_str.__UPLOAD__");
	return $list;
}

/**
 * 获取系统根目录
 */
function getRootPath()
{
	return dirname(dirname(dirname(dirname(__File__))));
}

/**
 * 通过第三方获取随机用户名
 */
function setUserNameOauth($type)
{
	$time = time();
	$name = $time . rand(100, 999);
	return $type . '_' . $name;
}

/**
 * 获取标准二维码格式
 */
function getQRcode($url, $path, $qrcode_name)
{
	if (!is_dir($path)) {
		$mode = intval('0777', 8);
		mkdir($path, $mode, true);
		chmod($path, $mode);
	}
	$path = $path . '/' . $qrcode_name . '.png';
	if (file_exists($path)) {
		unlink($path);
	}
	QRcode::png($url, $path, '', 4, 1);
	return $path;
}

/**
 * 根据HTTP请求获取用户位置
 */
function getUserLocation()
{
	$key = "16199cf2aca1fb54d0db495a3140b8cb"; // 高德地图key
	$url = "http://restapi.amap.com/v3/ip?key=$key";
	$json = file_get_contents($url);
	$obj = json_decode($json, true); // 转换数组
	$obj["message"] = $obj["status"] == 0 ? "失败" : "成功";
	return $obj;
}

function httpUtil($url, $data = '', $method = 'GET')
{
	try {
		$curl = curl_init(); // 启动一个CURL会话
		curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
		if ($method == 'POST') {
			curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
			if ($data != '') {
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
			}
		}
		curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
		curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
		$tmpInfo = curl_exec($curl); // 执行操作
		curl_close($curl); // 关闭CURL会话
		return json_decode($tmpInfo, true); // 返回数据
	} catch (Exception $e) {
	}
}

/**
 * 根据 ip 获取 当前城市
 */
function get_city_by_ip()
{
	if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
		$cip = $_SERVER["HTTP_CLIENT_IP"];
	} elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	} elseif (!empty($_SERVER["REMOTE_ADDR"])) {
		$cip = $_SERVER["REMOTE_ADDR"];
	} else {
		$cip = "";
	}
	$url = 'https://restapi.amap.com/v3/ip';
	$data = array(
		'output' => 'json',
		'key' => '16199cf2aca1fb54d0db495a3140b8cb',
		'ip' => $cip
	);
	
	$postdata = http_build_query($data);
	$opts = array(
		'http' => array(
			'method' => 'POST',
			'header' => 'Content-type: application/x-www-form-urlencoded',
			'content' => $postdata
		)
	);
	
	$context = stream_context_create($opts);
	
	$result = file_get_contents($url, false, $context);
	
	if (!empty($result)) {
		$res = json_decode($result, true);
		
		if (!empty($res)) {
			
			if (empty($res['province'])) {
				$res['province'] = '北京市';
			}
			if (!empty($res['province']) && $res['province'] == "局域网") {
				$res['province'] = '北京市';
			}
			
			if (is_array($res['province'])) {
				$province_count = count($res['province']);
				if ($province_count == 0) {
					$res['province'] = '北京市';
				}
			}
			if (is_array($res['city'])) {
				$city_count = count($res['city']);
				if ($city_count == 0) {
					$res['city'] = '北京市';
				}
			}
		} else {
			$res['province'] = '北京市';
			$res['city'] = '北京市';
		}
		
		return $res;
	} else {
		return array(
			"province" => '北京市',
			"city" => '北京市'
		);
	}
}

/**
 * 颜色十六进制转化为rgb
 */
function hColor2RGB($hexColor)
{
	$color = str_replace('#', '', $hexColor);
	if (strlen($color) > 3) {
		$rgb = array(
			'r' => hexdec(substr($color, 0, 2)),
			'g' => hexdec(substr($color, 2, 2)),
			'b' => hexdec(substr($color, 4, 2))
		);
	} else {
		$color = str_replace('#', '', $hexColor);
		$r = substr($color, 0, 1) . substr($color, 0, 1);
		$g = substr($color, 1, 1) . substr($color, 1, 1);
		$b = substr($color, 2, 1) . substr($color, 2, 1);
		$rgb = array(
			'r' => hexdec($r),
			'g' => hexdec($g),
			'b' => hexdec($b)
		);
	}
	return $rgb;
}

/**
 * 制作推广二维码
 *
 * @param string $path 二维码地址
 * @param string $thumb_qrcode 中继二维码地址
 * @param string $user_headimg 头像
 * @param string $shop_logo 店铺logo
 * @param string $user_name 用户名
 * @param array $data 画布信息 数组
 * @param string $create_path 图片创建地址 没有的话不创建图片
 */
function showUserQecode($upload_path, $path, $thumb_qrcode, $user_headimg, $shop_logo, $user_name, $data, $create_path)
{
	
	// 暂无法生成
	if (!strstr($path, "http://") && !strstr($path, "https://")) {
		if (!file_exists($path)) {
			$path = "public/static/images/template_qrcode.png";
		}
	}
	
	if (!file_exists($upload_path)) {
		$mode = intval('0777', 8);
		mkdir($upload_path, $mode, true);
	}
	
	// 定义中继二维码地址
	
	$image = \think\Image::open($path);
	// 生成一个固定大小为360*360的缩略图并保存为thumb_....jpg
	$image->thumb(288, 288, \think\Image::THUMB_CENTER)->save($thumb_qrcode);
	// 背景图片
	$dst = $data["background"];
	
	if (!strstr($dst, "http://") && !strstr($dst, "https://")) {
		if (!file_exists($dst)) {
			$dst = "public/static/images/qrcode_bg/shop_qrcode_bg.png";
		}
	}
	// $dst = "http://pic107.nipic.com/file/20160819/22733065_150621981000_2.jpg";
	// 生成画布
	list ($max_width, $max_height) = getimagesize($dst);
	// $dests = imagecreatetruecolor($max_width, $max_height);
	$dests = imagecreatetruecolor(640, 1134);
	$dst_im = getImgCreateFrom($dst);
	imagecopy($dests, $dst_im, 0, 0, 0, 0, $max_width, $max_height);
	// ($dests, $dst_im, 0, 0, 0, 0, 640, 1134, $max_width, $max_height);
	imagedestroy($dst_im);
	// 并入二维码
	// $src_im = imagecreatefrompng($thumb_qrcode);
	$src_im = getImgCreateFrom($thumb_qrcode);
	$src_info = getimagesize($thumb_qrcode);
	
	// imagecopy($dests, $src_im, $data["code_left"] * 2, $data["code_top"] * 2, 0, 0, $src_info[0], $src_info[1]);
	imagecopy($dests, $src_im, $data["code_left"] * 2, $data["code_top"] * 2, 0, 0, $src_info[0], $src_info[1]);
	imagedestroy($src_im);
	// 并入用户头像
	
	if (!strstr($user_headimg, "http://") && !strstr($user_headimg, "https://")) {
		if (!file_exists($user_headimg)) {
			$user_headimg = "public/static/images/qrcode_bg/head_img.png";
		}
	}
	$src_im_1 = getImgCreateFrom($user_headimg);
	$src_info_1 = getimagesize($user_headimg);
	// imagecopy($dests, $src_im_1, $data['header_left'] * 2, $data['header_top'] * 2, 0, 0, $src_info_1[0], $src_info_1[1]);
	// imagecopy($dests, $src_im_1, $data['header_left'] * 2, $data['header_top'] * 2, 0, 0, $src_info_1[0], $src_info_1[1]);
	imagecopyresampled($dests, $src_im_1, $data['header_left'] * 2, $data['header_top'] * 2, 0, 0, 80, 80, $src_info_1[0], $src_info_1[1]);
	imagedestroy($src_im_1);
	
	// 并入网站logo
	if ($data['is_logo_show'] == '1') {
		if (!strstr($shop_logo, "http://") && !strstr($shop_logo, "https://")) {
			if (!file_exists($shop_logo)) {
				$shop_logo = "public/static/images/logo.png";
			}
		}
		$src_im_2 = getImgCreateFrom($shop_logo);
		$src_info_2 = getimagesize($shop_logo);
		// imagecopy($dests, $src_im_2, $data['logo_left'] * 2, $data['logo_top'] * 2, 0, 0, $src_info_2[0], $src_info_2[1]);
		imagecopyresampled($dests, $src_im_2, $data['logo_left'] * 2, $data['logo_top'] * 2, 0, 0, 200, 80, $src_info_2[0], $src_info_2[1]);
		imagedestroy($src_im_2);
	}
	// 并入用户姓名
	if ($user_name == "") {
		$user_name = "用户";
	}
	$rgb = hColor2RGB($data['nick_font_color']);
	$bg = imagecolorallocate($dests, $rgb['r'], $rgb['g'], $rgb['b']);
	$name_top_size = $data['name_top'] * 2 + $data['nick_font_size'];
	@imagefttext($dests, $data['nick_font_size'], 0, $data['name_left'] * 2, $name_top_size, $bg, "public/static/font/Microsoft.ttf", $user_name);
	header("Content-type: image/jpeg");
	if ($create_path == "") {
		imagejpeg($dests);
	} else {
		imagejpeg($dests, $create_path);
	}
}

/**
 * 把微信生成的图片存入本地
 *
 * @param [type] $username
 *            [用户名]
 * @param [string] $LocalPath
 *            [要存入的本地图片地址]
 * @param [type] $weixinPath
 *            [微信图片地址]
 *
 * @return [string] [$LocalPath]失败时返回 FALSE
 */
function save_weixin_img($local_path, $weixin_path)
{
	$weixin_path_a = str_replace("https://", "http://", $weixin_path);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $weixin_path_a);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //不验证证书
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$r = curl_exec($ch);
	curl_close($ch);
	if (!empty($local_path) && !empty($weixin_path_a)) {
		$msg = file_put_contents($local_path, $r);
	}
	unset($r);
	// 判断头像是否有拉取到
	if (!getimagesize($local_path)) {
		@unlink($local_path);
		$local_path = "";
	}
	return $local_path;
}

// 分类获取图片对象
function getImgCreateFrom($img_path)
{
	$ename = getimagesize($img_path);
	$ename = explode('/', $ename['mime']);
	$ext = $ename[1];
	switch ($ext) {
		case "png":
			
			$image = imagecreatefrompng($img_path);
			break;
		case "jpeg":
			
			$image = imagecreatefromjpeg($img_path);
			break;
		case "jpg":
			
			$image = imagecreatefromjpeg($img_path);
			break;
		case "gif":
			
			$image = imagecreatefromgif($img_path);
			break;
	}
	return $image;
}

/**
 * 生成流水号
 *
 * @return string
 */
function getSerialNo()
{
	$no_base = date("ymdhis", time());
	$serial_no = $no_base . rand(111, 999);
	return $serial_no;
}

/**
 * 删除图片文件
 *
 * @param unknown $img_path
 */
function removeImageFile($img_path)
{
	// 检查图片文件是否存在
	if (file_exists($img_path)) {
		return unlink($img_path);
	} else {
		return false;
	}
}

/**
 * 数组去除空值重新排序
 * @param unknown $array
 */
function arrayFilter($array)
{
	if (!is_array($array)) {
		return '';
	} else {
		$array = array_filter($array);
		$new_array = [];
		foreach ($array as $k => $v) {
			$new_array[] = $v;
		}
		return $new_array;
	}
}

/**
 * 发送邮件
 *
 * @param unknown $toemail
 * @param unknown $title
 * @param unknown $content
 * @return boolean
 */
function emailSend($email_host, $email_id, $email_pass, $email_port, $email_is_security, $email_addr, $toemail, $title, $content, $shopName = "")
{
	$result = false;
	try {
		$mail = new Email();
		if (!empty($shopName)) {
			$mail->_shopName = $shopName;
		} else {
			$mail->_shopName = "NiuShop开源电商";
		}
		$mail->setServer($email_host, $email_id, $email_pass, $email_port, $email_is_security);
		$mail->setFrom($email_addr);
		$mail->setReceiver($toemail);
		$mail->setMail($title, $content);
		$result = $mail->sendMail();
	} catch (\Exception $e) {
		$result = false;
	}
	return $result;
}

/**
 * 执行钩子
 *
 * @param unknown $hookid
 * @param string $params
 */
function message($tag, $params = null)
{
    $notify = new \data\extend\hook\Notify();
    $result = $notify -> sendmessage($tag, $params);
    return $result;
}

/**
 * 格式化字节大小
 *
 * @param number $size
 *            字节数
 * @param string $delimiter
 *            数字和单位分隔符
 * @return string 格式化后的带单位的大小
 * @author
 *
 */
function format_bytes($size, $delimiter = '')
{
	$units = array(
		'B',
		'KB',
		'MB',
		'GB',
		'TB',
		'PB'
	);
	for ($i = 0; $size >= 1024 && $i < 5; $i++)
		$size /= 1024;
	return round($size, 2) . $delimiter . $units[ $i ];
}

/**
 * 获取插件类的类名
 *
 * @param $name 插件名
 * @param string $type
 *            返回命名空间类型
 * @param string $class
 *            当前类名
 * @return string
 */
function get_addon_class($name, $type = '', $class = null)
{
	
	return 'addons\\' . $name . '\\' . $name . 'Addon';
}

/**
 * 返回插件设置
 * @param unknown $name
 * @return string
 */
function get_addon_config($name)
{
	return 'addons\\' . $name . '\\Config';
}

/**
 * 处理插件钩子
 *
 * @param string $hook
 *            钩子名称
 * @param mixed $params
 *            传入参数
 * @param mixed $extra
 *            额外参数
 * @param bool $once
 *            只获取一个有效返回值
 * @return void
 */
function hook($hook, $params = [], $extra = null, $once = false)
{
	return \think\Hook::listen($hook, $params, $extra, $once);
}

/**
 * 判断钩子是否存在
 * 2017年8月25日19:43:08
 *
 * @param unknown $hook
 * @return boolean
 */
function hook_is_exist($hook)
{
	$res = \think\Hook::get($hook);
	if (empty($res)) {
		return false;
	}
	return true;
}

/**
 * 插件显示内容里生成访问插件的url
 *
 * @param string $url
 *            url
 * @param array $param
 *            参数
 */
function addons_url($url, $param = [])
{
	$url = parse_url($url);
	$case = config('url_convert');
	$addons = $case ? \think\Loader::parseName($url['scheme']) : $url['scheme'];
	$controller = $case ? \think\Loader::parseName($url['host']) : $url['host'];
	$action = trim($case ? strtolower($url['path']) : $url['path'], '/');
	/* 解析URL带的参数 */
	if (isset($url['query'])) {
		parse_str($url['query'], $query);
		$param = array_merge($query, $param);
	}
	if (strpos($action, '/') !== false) {
		// 有插件类型 插件类型://插件名/控制器名/方法名
		$controller_action = explode('/', $action);
		$params = array(
			'addons_type' => $addons,
			'addons' => $controller,
			'controller' => $controller_action[0],
			'action' => $controller_action[1]
		);
	} else {
		// 没有插件类型 插件名://控制器名/方法名
		$params = array(
			'addons' => $addons,
			'controller' => $controller,
			'action' => $action
		);
	}
	/* 基础参数 */
	$params = array_merge($params, $param); // 添加额外参数
	$return_url = url("shop/addons/execute", $params, '', true);
	return $return_url;
}

/**
 * 时间戳转时间
 *
 * @param unknown $time_stamp
 */
function getTimeStampTurnTime($time_stamp)
{
	if ($time_stamp > 0) {
		$time = date('Y-m-d H:i:s', $time_stamp);
	} else {
		$time = "";
	}
	return $time;
}

function getTimeStampTurnTimeByYmd($time)
{
	$res = "";
	if ($time > 0) {
		$res = date("Y-m-d", $time);
	}
	return $res;
}

/**
 * 时间转时间戳
 */
function getTimeTurnTimeStamp($time)
{
	$time_stamp = strtotime($time);
	return $time_stamp;
}

/**
 * 导出数据为excal文件
 */
function dataExcel($expTitle, $expCellName, $expTableData)
{
	include 'data/extend/phpexcel_classes/PHPExcel.php';
	$xlsTitle = iconv('utf-8', 'gb2312', $expTitle); // 文件名称
	$fileName = $expTitle . date('_YmdHis'); // or $xlsTitle 文件名称可根据自己情况设定
	$cellNum = count($expCellName);
	$dataNum = count($expTableData);
	$objPHPExcel = new \PHPExcel();
	$cellName = array(
		'A',
		'B',
		'C',
		'D',
		'E',
		'F',
		'G',
		'H',
		'I',
		'J',
		'K',
		'L',
		'M',
		'N',
		'O',
		'P',
		'Q',
		'R',
		'S',
		'T',
		'U',
		'V',
		'W',
		'X',
		'Y',
		'Z',
		'AA',
		'AB',
		'AC',
		'AD',
		'AE',
		'AF',
		'AG',
		'AH',
		'AI',
		'AJ',
		'AK',
		'AL',
		'AM',
		'AN',
		'AO',
		'AP',
		'AQ',
		'AR',
		'AS',
		'AT',
		'AU',
		'AV',
		'AW',
		'AX',
		'AY',
		'AZ'
	);
	for ($i = 0; $i < $cellNum; $i++) {
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[ $i ] . '2', $expCellName[ $i ][1]);
	}
	for ($i = 0; $i < $dataNum; $i++) {
		for ($j = 0; $j < $cellNum; $j++) {
			$objPHPExcel->getActiveSheet(0)->setCellValue($cellName[ $j ] . ($i + 3), " " . $expTableData[ $i ][ $expCellName[ $j ][0] ]);
		}
	}
	$objPHPExcel->setActiveSheetIndex(0);
	ob_end_clean();//清除缓冲区,避免乱码
	header('pragma:public');
	header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
	header("Content-Disposition:attachment;filename=$fileName.xls"); // attachment新窗口打印inline本窗口打印
	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
}

/**
 * 获取url参数
 *
 * @param unknown $action
 * @param string $param
 */
function __URL($url, $param = '', $app = '')
{
	$url = \str_replace('SHOP_MAIN', '', $url);
	$url = \str_replace('APP_MAIN', 'wap', $url);
	$url = \str_replace('ADMIN_MAIN', ADMIN_MODULE, $url);
	$url = \str_replace('__URL__', '', $url);
	$url = \str_replace(__URL__ . '/wap', 'wap', $url);
	$url = \str_replace(__URL__ . ADMIN_MODULE, ADMIN_MODULE, $url);
	$url = \str_replace(__URL__, '', $url);
	if (empty($url)) {
		return __URL__;
	} else {
		$str = substr($url, 0, 1);
		if ($str === '/' || $str === "\\") {
			$url = substr($url, 1, strlen($url));
		}
		if (REWRITE_MODEL) {
			
			$url = urlRouteConfig($url, $param);
			return $url;
		}
		$action_array = explode('?', $url);
		// 检测是否是pathinfo模式
		$url_model = url_model();
		
		
		if ($url_model) {
			$base_url = __URL__ . '/' . $action_array[0];
			$tag = '?';
		} else {
			$base_url = __URL__ . '?s=/' . $action_array[0];
			$tag = '&';
		}
		if (!empty($action_array[1])) {
			// 有参数
			return $base_url . $tag . $action_array[1];
		} else {
			if (!empty($param)) {
				return $base_url . $tag . $param;
			} else {
				return $base_url;
			}
		}
	}
}

/**
 * 特定路由规则
 */
function urlRoute()
{
	
	/**
	 * *********************************************************************************特定路由规则***********************************************
	 */
	if (REWRITE_MODEL) {
		$website = new WebSite();
		$url_route_list = $website->getUrlRoute();
		
		if (!empty($url_route_list['data'])) {
			foreach ($url_route_list['data'] as $k => $v) {
				// 针对特定路由特殊处理
				if ($v['route'] == 'web/goods/detail') {
					Route::get($v['rule'] . '-<goods_id>', $v['route'], []);
				} elseif ($v['route'] == 'web/article/detail') {
					Route::get($v['rule'] . '-<article_id>', $v['route'], []);
				} elseif ($v['route'] == 'web/goods/sku') {
					Route::get($v['rule'] . '-<sku_id>', $v['route'], []);
				} else {
					Route::get($v['rule'], $v['route'], []);
				}
			}
		}
	}
}

function urlRouteConfig($url, $param)
{
	// 针对商品信息编辑
	$main = \str_replace('/index.php', '', __URL__);
	if (!empty($param)) {
		$url = $main . '/' . $url . '?' . $param;
	} else {
		$action_array = explode('?', $url);
		$url = $main . '/' . $url;
	}
	$html = Config::get('default_return_type');
	$url = str_replace('.' . $html, '', $url);
	// 针对店铺端进行处理
	$model = Request::instance()->module();
	if ($model == 'web') {
		\think\Loader::addNamespace('data', 'data/');
		$website = new WebSite();
		$url_route_list = $website->getUrlRoute();
		if (!empty($url_route_list['data'])) {
			foreach ($url_route_list['data'] as $k => $v) {
				$v['route'] = str_replace('web/', '', $v['route']);
				// 针对特定功能处理
				if ($v['route'] == 'goods/detail') {
					$url = str_replace('goods/detail?goods_id=', $v['rule'] . '-', $url);
				} elseif ($v['route'] == 'article/detail') {
					$url = str_replace('article/detail?article_id=', $v['rule'] . '-', $url);
				} elseif ($v['route'] == 'goods/sku') {
					$url = str_replace('goods/sku?sku_id=', $v['rule'] . '-', $url);
				} else {
					$url = str_replace($v['route'], $v['rule'], $url);
				}
			}
		}
	}
	
	$url_array = explode('?', $url);
	
	if (!empty($url_array[1])) {
		$url = $url_array[0] . '.' . $html . '?' . $url_array[1];
	} else {
		$url = $url_array[0] . '.' . $html;
	}
	return $url;
}

/**
 * 返回系统是否配置了伪静态
 *
 * @return string
 */
function rewrite_model()
{
	$rewrite_model = REWRITE_MODEL;
	if ($rewrite_model) {
		return 1;
	} else {
		return 0;
	}
}

function url_model()
{
	$url_model = 0;
	try {
		\think\Loader::addNamespace('data', 'data/');
		$website = new WebSite();
		$website_info = $website->getWebSiteInfo();
		if (!empty($website_info)) {
			$url_model = isset($website_info["url_type"]) ? $website_info["url_type"] : 0;
		}
	} catch (Exception $e) {
		$url_model = 0;
	}
	return $url_model;
}

function admin_model()
{
	$admin_model = ADMIN_MODULE;
	return $admin_model;
}

/**
 * 过滤特殊字符(微信qq)
 *
 * @param unknown $str
 */
function filterStr($str)
{
	if ($str) {
		$name = $str;
		$name = preg_replace_callback('/\xEE[\x80-\xBF][\x80-\xBF]|\xEF[\x81-\x83][\x80-\xBF]/', function ($matches) {
			return '';
		}, $name);
		$name = preg_replace_callback('/xE0[x80-x9F][x80-xBF]‘.‘|xED[xA0-xBF][x80-xBF]/S', function ($matches) {
			return '';
		}, $name);
		// 汉字不编码
		$name = json_encode($name);
		$name = preg_replace_callback("/\\\ud[0-9a-f]{3}/i", function ($matches) {
			return '';
		}, $name);
		if (!empty($name)) {
			$name = json_decode($name);
			return $name;
		} else {
			return '';
		}
	} else {
		return '';
	}
}

/**
 * 检测ID是否在ID组
 *
 * @param unknown $id
 *            数字
 * @param unknown $id_arr
 *            数字,数字
 */
function checkIdIsinIdArr($id, $id_arr)
{
	$id_arr = ',' . $id_arr . ',';
	$result = strpos($id_arr, ',' . $id . ',');
	if ($result !== false) {
		return 1;
	} else {
		return 0;
	}
}

/**
 * 用于用户自定义模板判断 为空的话输出
 */
function __isCustomNullUrl($url)
{
	if (trim($url) == "") {
		return "javascript:;";
	} else {
		return __URL('APP_MAIN/' . $url);
	}
}

/**
 * 图片路径拼装(用于完善用于外链的图片)
 *
 * @param unknown $img_path
 * @param unknown $type
 * @param unknown $url
 * @return string
 */
function __IMG($img_path)
{
	$path = "";
	if (!empty($img_path)) {
		if (stristr($img_path, "http://") === false && stristr($img_path, "https://") === false) {
			$path = __ROOT__ . '/' . $img_path;
		} else {
			$path = $img_path;
		}
	}
	return $path;
}

/**
 * *
 * 判断一个数组是否存在于另一个数组中
 *
 * @param unknown $arr
 * @param unknown $contrastArr
 * @return boolean
 */
function is_all_exists($arr, $contrastArr)
{
	if (!empty($arr) && !empty($contrastArr)) {
		for ($i = 0; $i < count($arr); $i++) {
			if (!in_array($arr[ $i ], $contrastArr)) {
				return false;
			}
		}
		return true;
	}
}

/**
 * 检查模版是否存在
 */
function checkTemplateIsExists($folder, $curr_template)
{
	$file_path = str_replace("\\", "/", ROOT_PATH . 'template/' . $folder . "/" . $curr_template . "/config.xml");
	return file_exists($file_path);
}

/**
 * 通用提示页(专用于数据库的操作)
 *
 * @param string $msg
 *            提示消息（支持语言包变量）
 * @param integer $status
 *            状态（0：失败；1：成功）
 * @param string $extra
 *            附加数据
 */
function showMessage($msg, $status = 0, $extra = '')
{
	$result = array(
		'status' => $status,
		'message' => $msg,
		'result' => $extra
	);
	return $result;
}

/**
 * 发送HTTP请求方法，目前只支持CURL发送请求
 *
 * @param string $url
 *            请求URL
 * @param array $params
 *            请求参数
 * @param string $method
 *            请求方法GET/POST
 * @return array $data 响应数据
 */
function http($url, $timeout = 30, $header = array())
{
	if (!function_exists('curl_init')) {
		throw new Exception('server not install curl');
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	if (!empty($header)) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	}
	$data = curl_exec($ch);
	list ($header, $data) = explode("\r\n\r\n", $data);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if ($http_code == 301 || $http_code == 302) {
		$matches = array();
		preg_match('/Location:(.*?)\n/', $header, $matches);
		$url = trim(array_pop($matches));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		$data = curl_exec($ch);
	}
	
	if ($data == false) {
		curl_close($ch);
	} else {
	    @curl_close($ch);
    }
	return $data;
}

/**
 * 多维数组排序
 */
function my_array_multisort($data, $sort_order_field, $sort_order = SORT_DESC, $sort_type = SORT_NUMERIC)
{
	foreach ($data as $val) {
		$key_arrays[] = $val[ $sort_order_field ];
	}
	array_multisort($key_arrays, $sort_order, $sort_type, $data);
	return $data;
}

/**
 * 掩饰用户名
 *
 * @param unknown $username
 */
function cover_up_username($username)
{
	if (!empty($username)) {
//         $patterns = '/^(.{1})(.*)(.{1})$/';
//         if (preg_match($patterns, $username)) {
//             $username = preg_replace($patterns, "$1*****$3", $username);
//         }
		
		$length = mb_strlen($username, 'utf-8');
		
		$first = mb_substr($username, 0, 1);
		$last = mb_substr($username, $length - 1, 1);
		
		$username = $first . '*****' . $last;
	}
	return $username;
}

/**
 * 生成条形码
 *
 * @param unknown $content
 * @return string
 */
function getBarcode($content)
{
	$barcode = new Barcode(14, $content);
	$path = $barcode->generateBarcode();
	return $path;
}

/**
 * 过滤特殊符号
 */
function ihtmlspecialchars($string)
{
	if (is_array($string)) {
		foreach ($string as $key => $val) {
			$string[ $key ] = ihtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(d{3,5}|x[a-fa-f0-9]{4})|[a-za-z][a-z0-9]{2,5});)/', '&\1',
			str_replace(array( '&', '"', '<', '>' ), array( '&amp;', '&quot;', '&lt;', '&gt;' ), $string));
	}
	return $string;
}

/**
 * 获取网址前缀
 */
function getBaseUrl()
{
	$domain_name = \think\Request::instance()->domain();
	return $domain_name;
}

/**
 * 检测目录读写权限
 */
function check_dir_iswritable($dir_path)
{
	// 目录路径
	$dir_path = str_replace("\\", "/", $dir_path);
	// 是否可写
	$is_writale = 1;
	// 判断是否是目录
	if (!is_dir($dir_path)) {
		$is_writale = 0;
		return $is_writale;
	} else {
		$fp = fopen("$dir_path/test.txt", 'w');
		if ($fp) {
			fclose($fp);
			unlink("$dir_path/test.txt");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $is_writale;
}

/**
 * 服务层数据接口
 * @param string $method
 * @param array $params
 */
function service($method)
{
	if (strpos($method, '.') === false) {
		$class = "data\\service\\" . $method;
		return new $class();
		
	} else {
		$method_array = explode('.', $method);
		$class = $method_array[0] . "\\data\\service\\" . $method_array[1];
		return new $class();
	}
}

/**
 * 得到当前时间戳的毫秒数
 *
 * @return number
 */
function getCurrentTime()
{
	$time = time();
	$time = $time * 1000;
	return $time;
}

/**
 * 前端页面api请求(通过api接口实现)
 * @param string $method
 * @param array $params
 * @return mixed
 */
function api($method, $params = [])
{
	if (empty($method)) {
		return AjaxReturn('', 'PARAMETER_ERROR');
	}
	$data = getApiData($method, $params);
	try {
		$res = json_decode($data, true);
		return $res;
	} catch (Exception $e) {
		var_dump($method, $data);
	}
}

/**
 * 获取类
 */
function getApiData($method, $params)
{
	$method_array = explode('.', $method);
	$config = new \data\service\Config();
	$api_config = $config->getApiSecureConfig();
	if (!empty($api_config)) {
		if ($api_config['is_open_api_secure']) {
			$params['private_key'] = $api_config['private_key'];
		}
	}
	$token = session("niu_access_token");
	$params['token'] = $token;
	if ($method_array[0] == 'System') {
		$class_name = 'app\\api\\controller\\' . $method_array[1];
		if (!class_exists($class_name)) {
			return AjaxReturn(-1);
		}
		$api_model = new $class_name($params);
	} else {
		
		$class_name = "addons\\{$method_array[0]}\\api\\controller\\" . $method_array[1];
		if (!class_exists($class_name)) {
			return AjaxReturn(-1);
		}
		$api_model = new $class_name($params);
	}
	$function = $method_array[2];
	$data = $api_model->$function($params);
	return $data;
}

/**
 * 插件是否存在
 */
function addon_is_exit($name)
{
	$extend = new Extend();
	$addons = $extend->getAddons();
	if (in_array($name, $addons)) {
		return 1;
	} else {
		return 0;
	}
}

/**
 * 系统加密方法
 *
 * @param string $data
 *            要加密的字符串
 * @param string $key
 *            加密密钥
 * @param int $expire
 *            过期时间 单位 秒
 * @return string
 */
function encrypt($data, $key = '', $expire = 0)
{
	$key = md5(empty ($key) ? 'niucloud' : $key);
	
	$data = base64_encode($data);
	$x = 0;
	$len = strlen($data);
	$l = strlen($key);
	$char = '';
	
	for ($i = 0; $i < $len; $i++) {
		if ($x == $l)
			$x = 0;
		$char .= substr($key, $x, 1);
		$x++;
	}
	
	$str = sprintf('%010d', $expire ? $expire + time() : 0);
	
	for ($i = 0; $i < $len; $i++) {
		$str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
	}
	return str_replace(array(
		'+',
		'/',
		'='
	), array(
		'-',
		'_',
		''
	), base64_encode($str));
}

/**
 * 系统解密方法
 *
 * @param string $data
 *            要解密的字符串 （必须是encrypt方法加密的字符串）
 * @param string $key
 *            加密密钥
 * @return string
 */
function decrypt($data, $key = '')
{
	$key = md5(empty ($key) ? 'niucloud' : $key);
	$data = str_replace(array(
		'-',
		'_'
	), array(
		'+',
		'/'
	), $data);
	$mod4 = strlen($data) % 4;
	if ($mod4) {
		$data .= substr('====', $mod4);
	}
	$data = base64_decode($data);
	$expire = substr($data, 0, 10);
	$data = substr($data, 10);
	
	if ($expire > 0 && $expire < time()) {
		return '';
	}
	$x = 0;
	$len = strlen($data);
	$l = strlen($key);
	$char = $str = '';
	
	for ($i = 0; $i < $len; $i++) {
		if ($x == $l)
			$x = 0;
		$char .= substr($key, $x, 1);
		$x++;
	}
	
	for ($i = 0; $i < $len; $i++) {
		if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
			$str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
		} else {
			$str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
		}
	}
	return base64_decode($str);
}

/**
 * md5签名
 * @param unknown $key
 * @param unknown $params
 * @return string
 */
function getSign($key, $params)
{
	if (!is_array($params)) $params = array();
	
	ksort($params);
	$text = '';
	foreach ($params as $k => $v) {
		$text .= $k . $v;
	}
	return md5($key . $text . $key);
	
}

/**
 * 错误返回函数
 * @param unknown $data
 * @param string $const
 * @param array $vars
 * @return string[]|mixed[]
 */
function error($data = null, $code = ERROR)
{
	return [
		'code' => $code,
		'data' => $data
	];
}

/**
 * 成功返回函数
 * @param unknown $data
 * @param string $const
 * @return string[]|mixed[]
 */
function success($data = null, $code = SUCCESS)
{
	return [
		'code' => $code,
		'data' => $data
	];
}

/**
 * 用户名、邮箱、手机号掩饰
 * @param unknown $str
 */
function hideStr($str)
{
	if (strpos($str, '@')) {
		$email_array = explode("@", $str);
		$prevfix = (strlen($email_array[0]) < 4) ? "" : substr($str, 0, 3);
		$count = 0;
		$str = preg_replace('/([\d\w+_-]{0,100})@/', '*****@', $str, -1, $count);
		$res = $prevfix . $str;
	} else {
		$pattern = '/^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\d{8}$/';
		if (preg_match($pattern, $str)) {
			$res = substr_replace($str, '****', 3, 4);
		} else {
			if (preg_match("/[\x{4e00}-\x{9fa5}]+/u", $str)) {
				$len = mb_strlen($str, 'UTF-8');
				if ($len >= 3) {
					$res = mb_substr($str, 0, 1, 'UTF-8') . '******' . mb_substr($str, -1, 1, 'UTF-8');
				} elseif ($len == 2) {
					$res = mb_substr($str, 0, 1, 'UTF-8') . '******';
				}
			} else {
				$len = strlen($str);
				if ($len >= 3) {
					$res = substr($str, 0, 1) . '******' . substr($str, -1);
				} elseif ($len == 2) {
					$res = substr($str, 0, 1) . '******';
				}
			}
		}
	}
	return $res;
}

/**
 * 生成随机数
 * @param int $length
 * @return string
 */
function randomkeys($length)
{
	$pattern = 'abcdefghijklmnopqrstuvwxyz';
	$key = '';
	for ($i = 0; $i < $length; $i++) {
		$key .= $pattern{mt_rand(0, 25)};    //生成php随机数
	}
	return $key;
}


/**
 * 获取时间日期数组
 * @param $end_time
 * @param $start_time
 * @return array
 */
function getDayStep($end_time, $start_time)
{
	
	$num = ($end_time - $start_time) / (3600 * 24);
	if ($num < 0) {
		$num = 1;
	}
	$step_name = " day";
	$format = 'd';
	$data = [];
	for ($i = 0; $i < $num; $i++) {
		$data[ date($format, strtotime("+ " . $i . $step_name, $start_time)) ] = 0;
	}
	return $data;
}

/**
 *按日期分组
 */
function groupVisit($arr, $field, $type)
{
	$visit_list = [];
	if (!empty($arr)) {
		foreach ($arr as $v) {
			$date = date($type, strtotime($v[ $field ]));
			$visit_list[ $date ][] = $v;
		}
	}
	return $visit_list;
}

/**
 * 判断字符串是否为 Json 格式
 * @param  string $data Json 字符串
 * @param  bool $assoc 是否返回关联数组。默认返回对象
 * @return bool|array 成功返回转换后的对象或数组，失败返回 false
 */
function isJson($data = '', $assoc = false)
{
	$data = json_decode($data, $assoc);
	if ($data && (is_object($data)) || (is_array($data) && !empty(current($data)))) {
		return true;
	}
	return false;
}


//$fpath为下载文件所在文件夹，默认是downlod
function download($fname, $newname = '')
{
	
	if (empty($newname)) {
		$newname = $fname;
	} else {
		$ext = substr($fname, strrpos($fname, '.') + 1);
		$newname = $newname . "." . $ext;
	}
	
	//检查文件是否存在
	if (!file_exists($fname)) {
		header('HTTP/1.1 404 NOT FOUND');
	} else {
		//以只读和二进制模式打开文件
		$file = fopen($fname, "rb");
		
		//告诉浏览器这是一个文件流格式的文件
		Header("Content-type: application/octet-stream");
		//请求范围的度量单位
		Header("Accept-Ranges: bytes");
		//Content-Length是指定包含于请求或响应中数据的字节长度
		Header("Accept-Length: " . filesize($fname));
		//用来告诉浏览器，文件是可以当做附件被下载，下载后的文件名称为$file_name该变量的值。
		Header("Content-Disposition: attachment; filename=" . $newname);
		
		//读取文件内容并直接输出到浏览器
		echo fread($file, filesize($fname));
		fclose($file);
		exit ();
	}
	
	
}

/**
 * php 7.2代替each函数
 * @param unknown $array
 * @return Ambigous <boolean, multitype:NULL unknown mixed >
 */
function fun_adm_each(&$array)
{
	$res = array();
	$key = key($array);
	if ($key !== null) {
		next($array);
		$res[1] = $res['value'] = $array[ $key ];
		$res[0] = $res['key'] = $key;
	} else {
		$res = false;
	}
	return $res;
}

/**
 * php7.2代替count函数
 * @param unknown $array_or_countable
 * @param string $mode
 * @return number
 */
function fun_adm_count($array_or_countable, $mode = COUNT_NORMAL)
{
	if (is_array($array_or_countable) || is_object($array_or_countable)) {
		return count($array_or_countable, $mode);
	} else {
		return 0;
	}
}

/**
 * 秒数格式化
 * @param unknown $second
 */
function timeString($second)
{
	$day = floor($second / (3600 * 24));
	$hour = floor(($second % (3600 * 24)) / 3600);
	$min = floor((($second % (3600 * 24)) % 3600) / 60);
	$string = '';
	if ($day > 0) $string .= $day . '天';
	if ($hour > 0) $string .= $hour . '小时';
	if ($min > 0) $string .= $min . '分';
	return $string;
}

/**
 * 是否是url链接
 * @param unknown $string
 * @return boolean
 */
function is_url($string){
    if (strstr($string, 'http://') === false && strstr($string, 'https://') === false) {
        return false;
    } else {
        return true;
    }
}

