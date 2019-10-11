<?php
/**
 * Extend.php
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

use data\service\Extend as ExtendService;
use think\Cache;
use think\Db;

/**
 * 扩展模块控制器
 */
class Extend extends BaseController
{
	
	protected $extend;
	
	public function __construct()
	{
		$this->extend = new ExtendService();
		parent::__construct();
	}
	
	/**
	 * 插件管理
	 */
	public function addonsList()
	{
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$list = $this->extend->getAddonsList($page_index, $page_size, "", "id desc");
			return $list;
		}
		
		$child_menu_list = array(
			array(
				'url' => "extend/addonslist",
				'menu_name' => "插件管理",
				"active" => 1
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
	            "active" => 0
	        )
		);
		$this->assign("child_menu_list", $child_menu_list);
		return view($this->style . "Extend/addonsList");
	}
	
	/**
	 * 添加插件
	 */
	public function addAddons()
	{
		return view($this->style . "Extend/addAddons");
	}
	
	/**
	 * 钩子管理
	 */
	public function hooksList()
	{
		if (request()->isAjax()) {
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', 0);
			$list = $this->extend->getHooksList($page_index, $page_size, '', 'id desc');
			return $list;
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
				"active" => 1
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
	            "active" => 0
	        )
		);
		$this->assign("child_menu_list", $child_menu_list);
		return view($this->style . "Extend/hooksList");
	}
	
	/**
	 * 添加钩子
	 */
	public function addHooks()
	{
		if (request()->isAjax()) {
			$name = request()->post('name', '');
			$desc = request()->post('desc', '');
			$type = request()->post('type', 1);
			$res = $this->extend->addHooks($name, $desc, $type);
			return Ajaxreturn($res);
		}
		return view($this->style . "Extend/addHooks");
	}
	
	/**
	 * 修改钩子
	 */
	public function updateHooks()
	{
		$id = request()->get('id', 0);
		$info = $this->extend->getHoodsInfo([
			'id' => $id
		]);
		if (!empty($info['addons'])) {
			$info['addons'] = explode(',', $info['addons']);
		}
		$this->assign('info', $info);
		if (request()->isAjax()) {
			$id = request()->post('id', '');
			$name = request()->post('name', '');
			$desc = request()->post('desc', '');
			$type = request()->post('type', 1);
			$addons = request()->post('addons', '');
			$res = $this->extend->editHooks($id, $name, $desc, $type, $addons);
			return Ajaxreturn($res);
		}
		return view($this->style . "Extend/updateHooks");
	}
	
	/**
	 * 删除 钩子
	 */
	public function deleteHooks()
	{
		$id = request()->post('id', 0);
		$res = $this->extend->deleteHooks($id);
		return AjaxReturn($res);
	}
	
	/**
	 * 安装插件
	 */
	public function install()
	{
		if (request()->isAjax()) {
			$addon_name = trim(request()->post('addon_name', ''));
			if (!empty($addon_name)) {
				$res = $this->extend->installAddon($addon_name);
				return $res;
			}
		}
	}
	
	
	/**
	 * 卸载插件
	 */
	public function uninstall()
	{
		if (request()->isAjax()) {
			$id = trim(request()->post('id', 0));
			$db_addons = $this->extend->getAddonsInfo([
				'id' => $id
			], '*');
			Cache::clear('addon');
			Cache::clear('module');
			$class = get_addon_class($db_addons['name']);
			if (!$db_addons || !class_exists($class))
				return [ 'code' => 0, 'message' => '插件不存在' ];
			
			session('addons_uninstall_error', null);
			$addons = new $class();
			$uninstall_flag = $addons->uninstall();
			if (!$uninstall_flag)
				return [ 'code' => 0, 'message' => '执行插件预卸载操作失败' . session('addons_uninstall_error') ];
			// 判断是否有菜单，有的话需要删除
			$this->extend->removeMenu($db_addons['name']);
			$hooks_update = $this->extend->removeHooks($db_addons['name']);
			if ($hooks_update === false) {
				return [ 'code' => 0, 'message' => '卸载插件所挂载的钩子数据失败' ];
			}
			cache('hooks', null);
			$delete = $this->extend->deleteAddons([
				'name' => $db_addons['name']
			]);
			if ($delete === false) {
				return [ 'code' => 0, 'message' => '卸载插件失败' ];
			} else {
				// 删除移动的资源文件
				// $File = new \com\File();
				// $File->del_dir('./static/addons/'.$db_addons ['name']);
				return [ 'code' => 1, 'message' => '卸载成功' ];
			}
		}
	}
}