<?php
/**
 * System.php
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

use data\service\Album;
use data\service\Goods;
use data\service\Shop;
use think\Cache;

/**
 * 系统模块控制器
 */
class System extends BaseController
{
	/**
	 * 更新缓存
	 */
	public function deleteCache()
	{
		$retval = NiuDelDir('./runtime/cache');
		if ($retval) {
			$retval = NiuDelDir('./runtime/temp');
		}
		return $retval;
	}
	
	/**
	 * 图片选择
	 */
	public function dialogAlbumList()
	{
		$number = request()->get('number', 1);
		$spec_id = request()->get('spec_id', 0);
		$spec_value_id = request()->get('spec_value_id', 0);
		$upload_type = request()->get('upload_type', 1);
		$upload_path = request()->get("upload_path", "common");
		$this->assign("number", $number);
		$this->assign("spec_id", $spec_id);
		$this->assign("spec_value_id", $spec_value_id);
		$this->assign("upload_type", $upload_type);
		$this->assign("upload_path", $upload_path);
		$album = new Album();
		$default_album_detail = $album->getDefaultAlbumDetail();
		$this->assign('default_album_id', $default_album_detail['album_id']);
		return view($this->style . "System/dialogAlbumList");
	}
	
	/**
	 * 模块列表
	 */
	public function moduleList()
	{
		$condition = array(
			'pid' => 0,
			'module' => $this->module
		);
		$frist_list = $this->auth->getSystemModuleList(1, 0, $condition, 'pid,sort');
		$frist_list = $frist_list['data'];
		foreach ($frist_list as $k => $v) {
			$submenu = $this->auth->getSystemModuleList(1, 0, 'pid=' . $v['module_id'], 'pid,sort');
			$v['sub_menu'] = $submenu['data'];
			if (!empty($submenu['data'])) {
				foreach ($submenu['data'] as $ks => $vs) {
					$sub_sub_menu = $this->auth->getSystemModuleList(1, 0, 'pid=' . $vs['module_id'], 'pid,sort');
					$vs['sub_menu'] = $sub_sub_menu['data'];
					if (!empty($sub_sub_menu['data'])) {
						foreach ($sub_sub_menu['data'] as $kss => $vss) {
							$sub_sub_sub_menu = $this->auth->getSystemModuleList(1, 0, 'pid=' . $vss['module_id'], 'pid,sort');
							$vss['sub_menu'] = $sub_sub_sub_menu['data'];
							if (!empty($sub_sub_sub_menu['data'])) {
								foreach ($sub_sub_sub_menu['data'] as $ksss => $vsss) {
									$sub_sub_sub_sub_menu = $this->auth->getSystemModuleList(1, 0, 'pid=' . $vsss['module_id'], 'pid,sort');
									$vsss['sub_menu'] = $sub_sub_sub_sub_menu['data'];
								}
							}
						}
					}
				}
			}
		}
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
				"active" => 1
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
	            "active" => 0
	        )
		);
		$this->assign("child_menu_list", $child_menu_list);
		$list = $frist_list;
		$this->assign("list", $list);
		return view($this->style . 'System/moduleList');
	}
	
	/**
	 * 添加模块
	 */
	public function addModule()
	{
		if (request()->isAjax()) {
			$module_name = request()->post('module_name', '');
			$controller = request()->post('controller', '');
			$method = request()->post('method', '');
			$pid = request()->post('pid', '');
			$url = request()->post('url', '');
			$is_menu = request()->post('is_menu', '');
			$is_control_auth = request()->post('is_control_auth', '');
			$is_dev = request()->post('is_dev', '');
			$sort = request()->post('sort', '');
			$module_picture = request()->post('module_picture', '');
			$desc = request()->post('desc', '');
			$icon_class = '';
			$data = array(
				'module_name' => $module_name,
				'module' => \think\Request::instance()->module(),
				'controller' => $controller,
				'method' => $method,
				'pid' => $pid,
				'url' => $url,
				'is_menu' => $is_menu,
				"is_control_auth" => $is_control_auth,
				'is_dev' => $is_dev,
				'sort' => $sort,
				'module_picture' => $module_picture,
				'desc' => $desc,
				'create_time' => time(),
				'icon_class' => $icon_class
			);
			$retval = $this->auth->addSytemModule($data);
			return AjaxReturn($retval, $retval);
		} else {
			$condition = array(
				'pid' => 0,
				'module' => $this->module
			);
			$frist_list = $this->auth->getSystemModuleList(1, 100, $condition, 'pid,sort');
			$frist_list = $frist_list['data'];
			$list = array();
			foreach ($frist_list as $k => $v) {
				$submenu = $this->auth->getSystemModuleList(1, 100, 'pid=' . $v['module_id'], 'pid,sort');
				$list[ $k ]['data'] = $v;
				$list[ $k ]['sub_menu'] = $submenu['data'];
			}
			$this->assign("list", $list);
			$pid = request()->get('pid', '');
			$this->assign("pid", $pid);
			return view($this->style . 'System/addModule');
		}
	}
	
	/**
	 * 修改模块
	 */
	public function editModule()
	{
		if (request()->isAjax()) {
			$module_id = request()->post('module_id', '');
			$module_name = request()->post('module_name', '');
			$controller = request()->post('controller', '');
			$method = request()->post('method', '');
			$pid = request()->post('pid', '');
			$url = request()->post('url', '');
			$is_menu = request()->post('is_menu', '');
			$is_control_auth = request()->post('is_control_auth', '');
			$is_dev = request()->post('is_dev', '');
			$sort = request()->post('sort', '');
			$module_picture = request()->post('module_picture', '');
			$desc = request()->post('desc', '');
			$icon_class = '';
			$data = array(
				'module_id' => $module_id,
				'module_name' => $module_name,
				'module' => \think\Request::instance()->module(),
				'controller' => $controller,
				'method' => $method,
				'pid' => $pid,
				'url' => $url,
				'is_menu' => $is_menu,
				"is_control_auth" => $is_control_auth,
				'is_dev' => $is_dev,
				'sort' => $sort,
				'module_picture' => $module_picture,
				'desc' => $desc,
				'modify_time' => time(),
				'icon_class' => $icon_class
			);
			$retval = $this->auth->updateSystemModule($data);
			return AjaxReturn($retval);
		} else {
			$module_id = request()->get('module_id', '');
			if (!is_numeric($module_id)) {
				$this->error('未获取到信息');
			}
			$module_info = $this->auth->getSystemModuleInfo($module_id);
			$condition = array(
				'pid' => 0,
				'module' => $this->module
			);
			if ($module_info['level'] == 1) {
				$list = array();
			} elseif ($module_info['level'] == 2) {
				$frist_list = $this->auth->getSystemModuleList(1, 100, $condition, 'pid,sort');
				$list = array();
				foreach ($frist_list['data'] as $k => $v) {
					$list[ $k ]['data'] = $v;
					$list[ $k ]['sub_menu'] = array();
				}
			} elseif ($module_info['level'] == 3) {
				$frist_list = $this->auth->getSystemModuleList(1, 100, $condition, 'pid,sort');
				$frist_list = $frist_list['data'];
				$list = array();
				foreach ($frist_list as $k => $v) {
					$submenu = $this->auth->getSystemModuleList(1, 100, 'pid=' . $v['module_id'], 'pid,sort');
					$list[ $k ]['data'] = $v;
					$list[ $k ]['sub_menu'] = $submenu['data'];
				}
			}
			$this->assign('module_info', $module_info);
			$this->assign("list", $list);
			return view($this->style . 'System/editModule');
		}
	}
	
	/**
	 * 删除模块
	 */
	public function delModule()
	{
		$module_id = request()->post('module_id', '');
		$retval = $this->auth->deleteSystemModule($module_id);
		return AjaxReturn($retval);
	}
	
	/**
	 * 获取图片分组
	 */
	public function albumList()
	{
		$album = new Album();
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$search_text = request()->post('search_text', '');
			$condition = array(
				'shop_id' => $this->instance_id,
				'album_name' => array(
					'like',
					'%' . $search_text . '%'
				)
			);
			$order = " create_time desc";
			$retval = $album->getAlbumClassList($page_index, $page_size, $condition, $order);
			return $retval;
		} else {
			$default_album_detail = $album->getDefaultAlbumDetail();
			$this->assign('default_album_id', $default_album_detail['album_id']);
			return view($this->style . "System/albumList");
		}
	}
	
	/**
	 * 创建相册
	 */
	public function addAlbumClass()
	{
		$album_name = request()->post('album_name', '');
		$sort = request()->post('sort', 0);
		$album = new Album();
		$data = array(
			'album_name' => $album_name,
			'sort' => $sort,
			'album_cover' => '',
			'is_default' => 0,
			'shop_id' => $this->instance_id,
			'create_time' => time(),
			'pid' => 0
		);
		$retval = $album->addAlbumClass($data);
		return AjaxReturn($retval);
	}
	
	/**
	 * 删除相册
	 */
	public function deleteAlbumClass()
	{
		$aclass_id_array = request()->post('aclass_id_array', '');
		$album = new Album();
		$retval = $album->deleteAlbumClass($aclass_id_array);
		return AjaxReturn($retval);
	}
	
	/**
	 * 相册图片列表
	 */
	public function albumPictureList()
	{
		$album = new Album();
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$album_id = request()->post("album_id", 0);
			$is_use = request()->post("is_use", 0);
			$picture_sort = request()->post("picture_sort", 0);
			
			$condition = array();
			$condition["album_id"] = $album_id;
			if ($is_use > 0) {
				$img_array = $album->getGoodsAlbumUsePictureQuery([
					"shop_id" => $this->instance_id
				]);
				if (!empty($img_array)) {
					$img_string = implode(",", $img_array);
					$condition["pic_id"] = [
						"not in",
						$img_string
					];
				}
			}
			$order = "upload_time desc,pic_tag asc";
			if ($picture_sort > 0) {
				switch ($picture_sort) {
				    case 1:
				        $order = "upload_time desc,pic_tag asc";
				        break;
				    case 2:
				        $order = "upload_time asc,pic_tag asc";
				        break;
				    case 3:
				        $order = "pic_tag desc,upload_time desc";
				        break;
				    case 4:
				        $order = "pic_tag asc,upload_time desc";
				        break;
				}
			}
			$list = $album->getPictureList($page_index, $page_size, $condition, $order);
			foreach ($list["data"] as $k => $v) {
				$list["data"][ $k ]["upload_time"] = date("Y-m-d", $v["upload_time"]);
			}
			return $list;
		} else {
			$album_class_list = $album->getAlbumClassList(1, 10, "", "create_time desc");
			$this->assign("album_class_list", $album_class_list['data']);
			$album_id = request()->get('album_id', 0);
			$url = "System/albumPictureList";
			if ($album_id > 0) {
				$url .= "?album_id=" . $album_id;
			}
			$child_menu_list = array(
				array(
					'url' => "System/albumList",
					'menu_name' => "相册",
					"active" => 0
				),
				array(
					'url' => $url,
					'menu_name' => "图片",
					"active" => 1
				)
			);
			$album_detial = $album->getAlbumClassDetail($album_id);
			$this->assign('child_menu_list', $child_menu_list);
			$this->assign("album_name", $album_detial['album_name']);
			$this->assign("album_id", $album_id);
			$this->assign("album_cover", $album_detial['album_cover']);
			return view($this->style . "System/albumPictureList");
		}
	}
	
	/**
	 * 相册图片列表
	 */
	public function dialogAlbumPictureList()
	{
		if (request()->isAjax()) {
			$page_index = request()->post('pageIndex', '');
			$album_id = request()->post('album_id', '');
			$condition = array(
				'album_id' => $album_id
			);
			$album = new Album();
			$list = $album->getPictureList($page_index, 10, $condition);
			foreach ($list["data"] as $k => $v) {
				$list["data"][ $k ]["upload_time"] = date("Y-m-d", $v["upload_time"]);
			}
			return $list;
		} else {
			return view($this->style . "System/dialogAlbumPictureList");
		}
	}
	
	/**
	 * 删除图片
	 */
	public function deletePicture()
	{
		$pic_id_array = request()->post('pic_id_array', '');
		$album = new Album();
		$retval = $album->deletePicture($pic_id_array);
		return AjaxReturn($retval);
	}
	
	/**
	 * 获取相册详情
	 */
	public function getAlbumClassDetail()
	{
		$album_id = request()->post('album_id', '');
		$album = new Album();
		$retval = $album->getAlbumClassDetail($album_id);
		return $retval;
	}
	
	/**
	 * 修改相册
	 */
	public function updateAlbumClass()
	{
		$album_id = request()->post('album_id', '');
		$aclass_name = request()->post('album_name', '');
		$aclass_sort = request()->post('sort', '');
		$album_cover = request()->post('album_cover', '');
		$album = new Album();
		$data = array(
			'album_id' => $album_id,
			'album_name' => $aclass_name,
			'sort' => $aclass_sort,
			"album_cover" => $album_cover
		);
		$retval = $album->updateAlbumClass($data);
		return AjaxReturn($retval);
	}
	
	/**
	 * 删除制定路径文件
	 */
	function delete_file()
	{
		$file_url = request()->post('file_url', '');
		if (file_exists($file_url)) {
			@unlink($file_url);
			$retval = array(
				'code' => 1,
				'message' => '文件删除成功'
			);
		} else {
			$retval = array(
				'code' => 0,
				'message' => '文件不存在'
			);
		}
		return $retval;
	}
	
	/**
	 * 查询相册列表发布商品弹出相册框用到了
	 */
	public function getAlbumClassList()
	{
		$page_index = request()->post("page_index", 1);
		$page_size = request()->post('page_size', PAGESIZE);
		$album_name = request()->post("album_name", "");
		$condition = array(
			'shop_id' => $this->instance_id
		);
		$condition['album_name'] = [
			'like',
			'%' . $album_name . '%'
		];
		$album = new Album();
		$retval = $album->getAlbumClassList($page_index, $page_size, $condition, "is_default desc,create_time desc");
		return $retval;
	}
	
	/**
	 * 查询相册列表，相册列表界面用
	 */
	public function getAlbumClassListByAlbumPicture()
	{
		$page_index = request()->post("page_index", 1);
		$page_size = request()->post('page_size', PAGESIZE);
		$album_name = request()->post("album_name", "");
		$search_name = request()->post("search_name", "");
		//排除当前选中的相册，然后模糊查询
		$condition = array(
			'shop_id' => $this->instance_id,
			'album_name' => array(
				[
					"like",
					"%$search_name%"
				],
				[
					'eq',
					$album_name
				],
				'or'
			)
		);
		
		$album = new Album();
		$retval = $album->getAlbumClassList($page_index, $page_size, $condition, "create_time desc");
		return $retval;
	}
	
	/**
	 * 修改单个字段
	 */
	public function modifyField()
	{
		$fieldid = request()->post('fieldid', '');
		$fieldname = request()->post('fieldname', '');
		$fieldvalue = request()->post('fieldvalue', '');
		$retval = $this->auth->ModifyModuleField($fieldid, $fieldname, $fieldvalue);
		return AjaxReturn($retval);
	}
	
	/**
	 * 图片名称修改
	 */
	public function modifyAlbumPictureName()
	{
		$pic_id = request()->post('pic_id', '');
		$pic_name = request()->post('pic_name', '');
		$album = new Album();
		$retval = $album->modifyAlbumPictureName($pic_id, $pic_name);
		return AjaxReturn($retval);
	}
	
	/**
	 * 转移图片所在相册
	 */
	public function modifyAlbumPictureClass()
	{
		$pic_id = request()->post('pic_id', '');
		$album_id = request()->post('album_id', '');
		$album = new Album();
		$retval = $album->modifyAlbumPictureClass($pic_id, $album_id);
		return AjaxReturn($retval);
	}
	
	/**
	 * 设此图片为本相册的封面
	 */
	function modifyAlbumClassCover()
	{
		$pic_id = request()->post('pic_id', '');
		$album_id = request()->post('album_id', '');
		$album = new Album();
		$retval = $album->modifyAlbumClassCover($pic_id, $album_id);
		return AjaxReturn($retval);
	}
	
	/**
	 * 广告位列表
	 */
	public function shopAdvPositionList()
	{
		$terminal = request()->get("terminal", 1); // PC端或手机端（终端）
		$this->assign("terminal", $terminal);
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$ap_name = request()->post('ap_name', '');
			$ap_dis = request()->post('ap_display', '');
			$condition['type'] = request()->post("type", "1");
			$shop_service = new Shop();
			if ($ap_dis != "") {
				$condition['ap_display'] = $ap_dis;
			}
			if (!empty($ap_name)) {
				$condition["ap_name"] = array(
					"like",
					"%" . $ap_name . "%"
				);
			}
			$condition['instance_id'] = $this->instance_id;
			$list = $shop_service->getPlatformAdvPositionList($page_index, $page_size, $condition);
			return $list;
		}
		return view($this->style . "System/shopAdvPositionList");
	}
	
	/**
	 * 添加广告位
	 */
	public function addShopAdvPosition()
	{
		$shop = new Shop();
		if (request()->isAjax()) {
			$value = request()->post("value", "");
			if (empty($value)) return AjaxReturn(0);
			$value = json_decode($value, true);
			$res = $shop->editAdvPosition($value);
			return $res;
		}
		$terminal = request()->get('terminal', 1);
		$this->assign('terminal', $terminal);
		if ($terminal == 1) {
			$shopNavTemplate = $shop->getShopNavigationTemplate('1');
		} else if ($terminal == 3){
			$shopNavTemplate = $shop->getShopNavigationTemplate('3');
		} else {
		    $shopNavTemplate = $shop->getShopNavigationTemplate('2');
        }

        $nav_list = [];
        foreach ($shopNavTemplate as $k => $item) {
            $nav_list[ $k ] = [
                'template_url' => $item['template_url'],
                'template_name' => $item['template_name']
            ];
        }
        $this->assign('terminal', $terminal);
		$this->assign("nav_data", $nav_list);
		return view($this->style . "System/addShopAdvPosition");
	}

	/**
	 * 修改广告位
	 */
	public function updateShopAdvPosition()
	{
		$shop_service = new Shop();
		if (request()->isAjax()) {
			$value = request()->post("value", "");
			if (empty($value)) return AjaxReturn(0);
			$value = json_decode($value, true);
			$res = $shop_service->editAdvPosition($value);
			return $res;
		}
		$id = request()->get('ap_id', '');
		$info = $shop_service->getAdvPositionDetail([ 'ap_id' => $id ]);
		if (empty($info)) {
			$this->error('未获取到信息');
		}
		$this->assign('info', $info);
		$terminal = request()->get('terminal', 1);
		$this->assign('terminal', $terminal);
		if ($terminal == 1) {
			$shopNavTemplate = $shop_service->getShopNavigationTemplate('1');
		} else if ($terminal == 3){
			$shopNavTemplate = $shop_service->getShopNavigationTemplate('3');
		} else {
		    $shopNavTemplate = $shop_service->getShopNavigationTemplate('2');
        }

        $nav_list = [];
        foreach ($shopNavTemplate as $k => $item) {
            $nav_list[ $k ] = [
                'template_url' => $item['template_url'],
                'template_name' => $item['template_name']
            ];
        }
		$this->assign("nav_data", $nav_list);
		return view($this->style . "System/updateShopAdvPosition");
	}
	
	/**
	 * 检测广告位关键字是否存在
	 */
	public function checkApKeywordIsExists()
	{
		$shop_service = new Shop();
		if (request()->isAjax()) {
			$ap_keyword = request()->post("ap_keyword", '');
			$ap_id = request()->post("ap_id", '');
			$res = 0;
			if ($ap_keyword != "") {
				$res = $shop_service->checkApKeywordIsExists($ap_keyword, $ap_id);
			}
			return $res;
		}
	}
	
	/**
	 * 广告列表 （广告位下级）
	 */
	public function shopAdvList()
	{
		$ap_id = request()->get('ap_id', '');
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$search_text = request()->post('search_text', '');
			$type = request()->post("type", 1);
			$shop_service = new Shop();
			$list = $shop_service->adminGetAdvList($page_index, $page_size, [
				'adv_title' => array( 'like', '%' . $search_text . '%' ),
				'npap.instance_id' => $this->instance_id,
				'npap.type' => $type
			]);
			return $list;
		}
		$terminal = request()->get('terminal', 1);
		$this->assign('terminal', $terminal);
		return view($this->style . "System/shopAdvList");
	}
	
	/**
	 * 修改广告排序
	 */
	public function modifyAdvSort()
	{
		if (request()->isAjax()) {
			$adv_id = request()->post('fieldid', '');
			$slide_sort = request()->post('fieldvalue', '');
			$shop_service = new Shop();
			$res = $shop_service->modifyAdvSlideSort($adv_id, $slide_sort);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 添加广告
	 */
	public function addShopAdv()
	{
		$ap_id = request()->get('ap_id', '');
		$this->assign("ap_id", $ap_id);
		$shop_service = new Shop();
		if (request()->isAjax()) {
			$ap_id = request()->post('ap_id', '');
			$adv_title = request()->post('adv_title', '');
			$adv_url = request()->post('adv_url', '');
			$adv_image = request()->post('adv_image', '');
			$slide_sort = request()->post('slide_sort', '');
			$background = request()->post('background', '');
			$adv_code = request()->post("adv_code", "");
			
			$data = array(
				'ap_id' => $ap_id,
				'adv_title' => $adv_title,
				'adv_url' => $adv_url,
				'adv_image' => $adv_image,
				'slide_sort' => $slide_sort,
				'background' => $background,
				'adv_code' => $adv_code
			);
			
			$res = $shop_service->addPlatformAdv($data);
			return AjaxReturn($res);
		}
		$type = request()->get("type", 1);
		$shop_service = new Shop();
		$list = $shop_service->getPlatformAdvPositionList(1, 0, [ "instance_id" => $this->instance_id, "type" => $type ], '', 'ap_id,ap_name,ap_class,ap_display');
		$this->assign('platform_adv_position_list', $list['data']);
		return view($this->style . "System/addShopAdv");
	}
	
	/**
	 * 修改广告
	 */
	public function updateShopAdv()
	{
		$shop_service = new Shop();
		if (request()->isAjax()) {
			$adv_id = request()->post('adv_id', '');
			$ap_id = request()->post('ap_id', '');
			$adv_title = request()->post('adv_title', '');
			$adv_url = request()->post('adv_url', '');
			$adv_image = request()->post('adv_image', '');
			$slide_sort = request()->post('slide_sort', '');
			$background = request()->post('background', '');
			$adv_code = request()->post("adv_code", "");
			$data = array(
				'ap_id' => $ap_id,
				'adv_title' => $adv_title,
				'adv_url' => $adv_url,
				'adv_image' => $adv_image,
				'slide_sort' => $slide_sort,
				'background' => $background,
				'adv_code' => $adv_code,
				'adv_id' => $adv_id
			);
			$res = $shop_service->updatePlatformAdv($data);
			return AjaxReturn($res);
		}
		$adv_id = request()->get('adv_id', '');
		if (!is_numeric($adv_id)) {
			$this->error('未获取信息');
		}
		$adv_info = $shop_service->getPlatformAdDetail($adv_id);
		$this->assign('adv_info', $adv_info);
		$type = request()->get("type", 1);
		$list = $shop_service->getPlatformAdvPositionList(1, 0, [ "instance_id" => $this->instance_id, "type" => $type ], '', 'ap_id,ap_name,ap_class,ap_display');
		$this->assign('platform_adv_position_list', $list['data']);
		return view($this->style . "System/updateShopAdv");
	}
	
	/**
	 * 删除广告
	 */
	public function deletePlatformAdv()
	{
		$adv_id = request()->post('adv_id', '');
		$shop_service = new Shop();
		$res = $shop_service->deletePlatformAdv($adv_id);
		return AjaxReturn($res);
	}
	
	/**
	 * 搜索商品
	 */
	public function searchGoods()
	{
		$goods_name = request()->post('goods_name', '');
		$category_id = request()->post('category_id', '');
		$category_level = request()->post('category_level', '');
		$where['ng.goods_name'] = array(
			'like',
			'%' . $goods_name . '%'
		);
		$where[ 'ng.category_id_' . $category_level ] = $category_id;
		$where['ng.state'] = 1;
		$where = array_filter($where);
		$goods = new Goods();
		$list = $goods->getGoodsList(1, 0, $where);
		return $list;
	}
	
	/**
	 * 删除广告位
	 */
	public function deletePlatfromAdvPosition()
	{
		$ap_id = request()->post('ap_id', '');
		$shop_service = new Shop();
		$res = $shop_service->deletePlatfromAdvPosition($ap_id);
		return AjaxReturn($res);
	}
	
	/**
	 * 广告位的禁用和启用
	 */
	public function modifyPlatformAdvPositionUse()
	{
		if (request()->isAjax()) {
			$ap_id = request()->post('ap_id', '');
			$is_use = request()->post('is_use', '');
			$shop_service = new Shop();
			$res = $shop_service->modifyPlatformAdvPositionUse($ap_id, $is_use);
			Cache::tag("niu_platform_adv_position")->set("getPlatformAdvPositionDetail" . $ap_id, '');
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 友情链接的是否新窗口打开
	 */
	public function modifyPlatformLinkListIsBlank()
	{
		if (request()->isAjax()) {
			$link_id = request()->post('link_id', '');
			$is_show = request()->post('is_show', '');
			$shop_service = new Shop();
			$res = $shop_service->modifyPlatformLinkListIsBlank($link_id, $is_show);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 友情链接的是否显示
	 */
	public function modifyPlatformLinkListIsShow()
	{
		if (request()->isAjax()) {
			$link_id = request()->post('link_id', '');
			$is_use = request()->post('is_use', '');
			$shop_service = new Shop();
			$res = $shop_service->modifyPlatformLinkListIsShow($link_id, $is_use);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 图片上传
	 */
	public function addAlbumPicture(){
	    if (request()->isAjax()) {
	        $data = request()->post();
	        $album_service = new Album();
	        
	        $data_picture = array(
	            'shop_id' => 0,
	            'is_wide' => "0",
	            'pic_name' => $data["new_name"],
	            'pic_tag' => $data["file_name"],
	            'pic_cover' => $data["path"],
	            'pic_size' => $data["size"],
	            'pic_spec' => $data["pic_spec"],
	            'pic_cover_big' => $data["big_pic_path"],
	            'pic_size_big' => $data["big_pic_size"],
	            'pic_spec_big' => $data["big_pic_spec"],
	            'pic_cover_mid' => $data["mid_pic_path"],
	            'pic_size_mid' => $data["mid_pic_size"],
	            'pic_spec_mid' => $data["mid_pic_spec"],
	            'pic_cover_small' => $data["small_pic_path"],
	            'pic_size_small' => $data["small_pic_size"],
	            'pic_spec_small' => $data["small_pic_spec"],
	            'pic_cover_micro' => $data["thumb_pic_path"],
	            'pic_size_micro' => $data["thumb_pic_size"],
	            'pic_spec_micro' => $data["thumb_pic_spec"],
	            'upload_time' => time(),
	            "upload_type" => $data["upload_type"],
	            "domain" => $data["domain"],
	            "bucket" => $data["bucket"]
	        );
	        
	        //查询默认相册
	        if ($data["album_id"] > 0) {
	            $album_detail = $album_service->getAlbumClassDetail($data["album_id"]);
	        } else {
	            $album_detail = $album_service->getDefaultAlbumDetail();
	        }
	        if (empty($album_detail))
	            return AjaxReturn(0);
	            
	            
            $album_id = $album_detail["album_id"];//相册id
            $data_picture['album_id'] = $album_id;
            $pic_id = $album_service->addPicture($data_picture);
            return AjaxReturn($pic_id);
	            
	    }
	}
}