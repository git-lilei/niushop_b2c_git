<?php
/**
 * BaseController.php
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

use data\service\Auth;
use data\service\Shop;
use data\service\Upload;
use data\service\WebSite;
use think\Controller;
use data\service\Config as ConfigService;
use data\service\Upgrade as UpgradeService;

class BaseController extends Controller
{
	protected $user = null;
	
	protected $website = null;
	
	protected $auth = '';
	
	protected $uid;
	
	protected $instance_id;
	
	protected $instance_name;
	
	protected $user_name;
	
	protected $user_headimg;
	
	protected $module = null;
	
	protected $controller = null;
	
	protected $action = null;
	
	protected $module_info = null;
	
	protected $rootid = null;
	
	protected $moduleid = null;
	
	protected $second_menu_id = null;
	// 二级菜单module_id 手机自定义模板临时添加，用来查询三级菜单
	
	/**
	 * 当前版本的路径
	 */
	protected $style = null;
	
	public function __construct()
	{
		parent::__construct();
		$this->website = new WebSite();
		$this->auth = new Auth();
		$this->init();
		$this->assign("pageshow", PAGESHOW);
		$this->assign("pagesize", PAGESIZE);
	}
	
	/**
	 * 功能说明：action基类 调用 加载头部数据的方法
	 */
	public function init()
	{
		$this->uid = $this->auth->getSessionUid();
		$is_system = $this->auth->getSessionUserIsSystem();
		
		if (empty($this->uid)) {
			if (request()->isAjax()) {
				echo json_encode(AjaxReturn(NO_LOGIN));
				exit();
			} else {
				$redirect = __URL(__URL__ . '/' . ADMIN_MODULE . "/login");
				$this->redirect($redirect);
			}
		}
		if (empty($is_system)) {
			$redirect = __URL(__URL__ . '/' . ADMIN_MODULE . "/login");
			$this->redirect($redirect);
		}
		if (!defined('IS_ROUTE')) exit();
		$this->instance_id = $this->auth->getSessionInstanceId();
		$this->instance_name = $this->auth->getInstanceName();
		$this->module = \think\Request::instance()->module();
		$this->controller = \think\Request::instance()->controller();
		if ($this->controller == 'Menu') {
			$this->controller = session('controller');
		}
		$this->action = \think\Request::instance()->action();
		$this->module_info = $this->auth->getModuleIdByModule($this->controller, $this->action);
		// 过滤控制权限 为0
		if (empty($this->module_info)) {
			$this->moduleid = 0;
			$check_auth = 1;
		} elseif ($this->module_info["is_control_auth"] == 0) {
			$this->moduleid = $this->module_info['module_id'];
			$check_auth = 1;
		} else {
			$this->moduleid = $this->module_info['module_id'];
			$check_auth = $this->auth->checkAuth($this->moduleid);
		}
		if ($check_auth) {
			
			// 网站信息
			$web_info = $this->website->getWebSiteInfo();
			$this->style = STYLE_DEFAULT_ADMIN . '/';
			$this->assign("style", STYLE_DEFAULT_ADMIN);
			$this->assign("base", $this->style . 'base');
			$this->assign('web_phone', $web_info['web_phone']);
			$this->assign('web_email', $web_info['web_email']);
			//弹出框标题
			if (empty($web_info['title'])) {
				$this->assign("web_popup_title", "Niushop开源商城");
			} else {
				$this->assign("web_popup_title", $web_info['title']);
			}
			
			$this->assign('niu_version', NIU_VERSION);
			$this->assign('niu_ver_date', NIU_VER_DATE);
			$warm_prompt_is_show = $this->getWarmPromptIsShow();
			$this->assign('warm_prompt_is_show', $warm_prompt_is_show);
			$this->assign("instance_id", $this->instance_id);
			
			if (!request()->isAjax()) {
				/* 店铺导航 */
				$shop = new Shop();
				$ShopNavigationData = $shop->shopNavigationList(1, 6, [
					"type" => 3
				], 'sort');
				
				$root_array = $this->auth->getModuleRootAndSecondMenu($this->moduleid);
				$this->rootid = $root_array[0];
				$second_menu_id = $root_array[1];
				$root_module_info = $this->auth->getSystemModuleInfo($this->rootid, 'module,module_name,url,module_picture');
				$first_menu_list = $this->auth->getchildModuleQuery(0);
				
				if ($this->rootid != 0) {
					$second_menu_list = $this->auth->getchildModuleQuery($this->rootid);
				} else {
					$second_menu_list = '';
				}
				
				$this->user_name = $this->auth->getSessionUserName();
				$this->user_headimg = $this->auth->getSessionUserHeadImg();
				$this->assign("headid", $this->rootid);
				$this->assign("second_menu_id", $second_menu_id);
				$this->assign("moduleid", $this->moduleid);
				$this->assign("title_name", $this->instance_name);
				$this->assign("user_name", $this->user_name);
				$this->assign("user_headimg", $this->user_headimg);
				$this->assign("headlist", $first_menu_list);
				$this->assign("leftlist", $second_menu_list);
				$this->assign("frist_menu", $root_module_info); // 当前选中的导航菜单
				$this->assign("secend_menu", $this->module_info);
				$this->assign('is_show_shortcut_menu', 0);// 是否显示
				$path_info_url = request()->url();
				$replace_url = str_replace(request()->root() . '/admin/', '', $path_info_url);
				$child_menu_list = array(
					array(
						'url' => $replace_url,
						'menu_name' => $this->module_info['module_name'],
						'active' => 1
					)
				);
				$this->assign('child_menu_list', $child_menu_list);
				$this->assign('ShopNavigationData', $ShopNavigationData['data']);
				$this->assign('first_menu_list', $first_menu_list);
				$this->assign('second_menu_list', $second_menu_list);
				$this->second_menu_id = $second_menu_id; // 临时添加，用来查询3级菜单 手机端自定义模板
				$this->getNavigation();
				$this->getCopyRight($web_info);
				
				// 供ueditor编辑器使用
				$_SESSION['ROOT_PATH'] = ROOT_PATH;
			}
		} else {
			if (request()->isAjax()) {
				echo json_encode(AjaxReturn(NO_AITHORITY));
				exit();
			} else {
				$this->error("当前用户没有操作权限");
			}
		}
	}
	
	/**
	 * 获取导航
	 */
	public function getNavigation()
	{
		$first_list = $this->auth->getchildModuleQuery(0);
		$list = array();
		foreach ($first_list as $k => $v) {
			$submenu = $this->auth->getchildModuleQuery($v['module_id']);
			$list[ $k ]['data'] = $v;
			$list[ $k ]['sub_menu'] = $submenu;
		}
		$this->assign("nav_list", $list);
	}
	
	/**
	 * 获取操作提示是否显示
	 */
	public function getWarmPromptIsShow()
	{
		$is_show = cookie("warm_promt_is_show");
		if ($is_show == null) {
			$is_show = 'show';
		}
		return $is_show;
	}
	
	/**
	 * 获取系统信息
	 */
	public function getSystemConfig()
	{
		$system_config['os'] = php_uname(); // 服务器操作系统
		$system_config['server_software'] = $_SERVER['SERVER_SOFTWARE']; // 服务器环境
		$system_config['upload_max_filesize'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknow'; // 文件上传限制
		$system_config['gd_version'] = gd_info()['GD Version']; // GD（图形处理）版本
		$system_config['max_execution_time'] = ini_get("max_execution_time") . "秒"; // 最大执行时间
		$system_config['port'] = $_SERVER['SERVER_PORT']; // 端口
		$system_config['dns'] = $_SERVER['HTTP_HOST']; // 服务器域名
		$system_config['php_version'] = PHP_VERSION; // php版本
		$system_config['sockets'] = extension_loaded('sockets'); //是否支付sockets
		$system_config['openssl'] = extension_loaded('openssl'); //是否支付openssl
		$system_config['curl'] = function_exists('curl_init'); // 是否支持curl功能
		$system_config['upload_dir_jurisdiction'] = check_dir_iswritable(ROOT_PATH . "upload/"); // upload目录读写权限
		$system_config['runtime_dir_jurisdiction'] = check_dir_iswritable(ROOT_PATH . "runtime/"); // runtime目录读写权限
		$system_config['fileinfo'] = extension_loaded('fileinfo'); //是否支付fileinfo
		
		$this->assign("system_config", $system_config);
	}
	
	/**
	 * 获取三级菜单
	 * 目前只有固定模板和自定义模板用
	 */
	public function getThreeLevelModule()
	{
		$child_menu_list_old = $this->auth->getchildModuleQuery($this->second_menu_id);
		$child_menu_list = [];
		foreach ($child_menu_list_old as $k => $v) {
			$active = 0;
			$param = request()->param();
			if (strpos(strtolower(request()->pathinfo()), strtolower($v['url']))) {
				$active = 1;
			} else
				if (!empty($param['addons']) && strpos(strtolower($v['url']), strtolower($param['addons'])) !== false) {
					$active = 1;
				}
			$child_menu_list[] = array(
				'url' => $v['url'],
				'menu_name' => $v['module_name'],
				'active' => $active
			);
		}
		
		$this->assign('child_menu_list', $child_menu_list);
	}
	
	/**
	 * 图片上传
	 */
	public function uploadImage()
	{
		if (!empty($_FILES['file_upload'])) {
			$file_path = request()->post("file_path", "common_image");
			$upload = new Upload();
			$result = $upload->image($_FILES["file_upload"], $file_path);
			return json_encode($result);
		}
	}
	
	/**
	 * 图片上传
	 */
	public function uploadGoodsImage()
	{
		if (!empty($_FILES['file_upload'])) {
			$file_path = request()->post("file_path", "common_image");
			$thumb_type = request()->post("thumb_type", "");//缩略图类型  big,mid,small,thumb
			$upload = new Upload();
			$result = $upload->image($_FILES["file_upload"], $file_path, $thumb_type);
			return $result;
		}
	}
	
	/**
	 * 压缩包上传
	 */
	public function uploadCompressedFile()
	{
		if (!empty($_FILES['file_upload'])) {
			$file_path = request()->post("file_path", "common_zip");
			$upload = new Upload();
			$result = $upload->compressedFiles($_FILES["file_upload"], $file_path);
			return json_encode($result);
		}
	}
	
	/**
	 * 文件上传
	 */
	public function uploadFile()
	{
		if (!empty($_FILES['file_upload'])) {
			$file_path = request()->post("file_path", "common_file");
			$upload = new Upload();
			$result = $upload->file($_FILES["file_upload"], $file_path);
			return json_encode($result);
		}
	}
	
	/**
	 * 上传到相册
	 */
	public function imageToAlbum()
	{
		if (!empty($_FILES['file_upload'])) {
			$file_path = request()->post("file_path", "common_album");
			$album_id = request()->post("album_id", 0);
			$pic_id = request()->post("pic_id", 0);
			$thumb_type = request()->post("thumb_type", "");//缩略图类型  big,mid,small,thumb
			$upload = new Upload();
			$result = $upload->imageToAlbum($_FILES["file_upload"], $file_path, $thumb_type, [ 'album_id' => $album_id, 'pic_id' => $pic_id ]);
			if (request()->isAjax()) {
				return $result;
			} else {
				return json_encode($result);
			}
		}
	}
	
	/**
	 * 视频上传
	 */
	public function uploadVideo()
	{
		if (!empty($_FILES['file_upload'])) {
			$file_path = request()->post("file_path", "common_album");
			$upload = new Upload();
			$result = $upload->video($_FILES["file_upload"], $file_path);
			if (request()->isAjax()) {
				return $result;
			} else {
				return json_encode($result);
			}
		}
	}
	
	/**
	 * 音频上传
	 */
	public function uploadAudio()
	{
	    if (!empty($_FILES['file_upload'])) {
	        $file_path = request()->post("file_path", "common_audio");
	        $upload = new Upload();
	        $result = $upload->audio($_FILES["file_upload"], $file_path);
	        if (request()->isAjax()) {
	            return $result;
	        } else {
	            return json_encode($result);
	        }
	    }
	}
	
	/**
	 * 版权
	 */
	public function getCopyRight($web_info)
	{
		$upgrade = new UpgradeService();
		$is_load = $upgrade->isLoadCopyRight();
		$bottom_info = array();
		if ($is_load == 1) {
			$config = new ConfigService();
			$bottom_info = $config->getCopyrightConfig($this->instance_id);
		}
		if (!empty($web_info["web_icp"])) {
			$bottom_info['copyright_meta'] = $web_info["web_icp"];
		} else {
			$bottom_info['copyright_meta'] = '';
		}
		$bottom_info['web_gov_record'] = $web_info["web_gov_record"];
		$bottom_info['web_gov_record_url'] = $web_info["web_gov_record_url"];
		
		$copy_right_info = array(
			"is_load" => $is_load
		);
		$copy_right_info["bottom_info"] = $bottom_info;
		$this->assign('copy_right_info', $copy_right_info);
		
	}
	
}