<?php

namespace addons\NsBargain\admin\controller;

use addons\NsBargain\data\service\Bargain as BargainService;
use app\admin\controller\BaseController;

/**
 * 砍价
 *
 */
class Bargain extends BaseController
{
	public $addon_view_path;
	
	public function __construct()
	{
		parent::__construct();
		$this->addon_view_path = ADDON_DIR . '/NsBargain/template/';
	}
	
	/**
	 * 砍价活动列表
	 */
	public function index()
	{
		if (request()->isAjax()) {
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', PAGESIZE);
			
			$condition = [];
			
			$status = request()->post('status', 'all');
			if ($status != 'all') {
				$condition['status'] = $status;
			}
			$bargain_name = request()->post('bargain_name', '');
			if (!empty($bargain_name)) {
				$condition['bargain_name'] = array( 'like', "%$bargain_name%" );
			}
			$bargain_service = new BargainService();
			$list = $bargain_service->getBargainList($page_index, $page_size, $condition, "create_time desc");
			return $list;
		}
		
		$child_menu_list = array(
			array(
				'url' => "bargain/index",
				'menu_name' => "砍价列表",
				"active" => 1,
				'module' => 'NsBargain'
			),
			array(
				'url' => "bargain/config",
				'menu_name' => "砍价设置",
				"active" => 0,
				'module' => 'NsBargain'
			)
		);
		$this->assign("child_menu_list", $child_menu_list);
		return view($this->addon_view_path . $this->style . 'Bargain/index.html');
	}
	
	/**
	 * 砍价配置
	 */
	public function config()
	{
		$bargain_service = new BargainService();
		if (request()->isAjax()) {
			$is_use = request()->post('is_use', '');
			$activity_time = request()->post('activity_time', 1);
			$bargain_max_number = request()->post('bargain_max_number', 1);
			$cut_methods = request()->post('cut_methods', '');
			$launch_cut_method = request()->post('launch_cut_method', '');
			$propaganda = request()->post('propaganda', '');
			$rule = request()->post('rule', '');
			$result = $bargain_service->setConfig($is_use, $activity_time, $bargain_max_number, $cut_methods, $launch_cut_method, $propaganda, $rule);
			return AjaxReturn($result);
		}
		
		$child_menu_list = array(
			array(
				'url' => "bargain/index",
				'menu_name' => "砍价列表",
				"active" => 0,
				'module' => 'NsBargain'
			),
			array(
				'url' => "bargain/config",
				'menu_name' => "砍价设置",
				"active" => 1,
				'module' => 'NsBargain'
			)
		);
		$this->assign("child_menu_list", $child_menu_list);
		
		$config_info = $bargain_service->getConfig();
		$this->assign('config', $config_info);
		return view($this->addon_view_path . $this->style . 'Bargain/config.html');
	}
	
	/**
	 * 添加活动
	 */
	public function addBargain()
	{
		return view($this->addon_view_path . $this->style . 'Bargain/addBargain.html');
	}
	
	/**
	 * 修改活动
	 */
	public function editBargain()
	{
		$bargain_id = request()->get('bargain_id', 0);
		
		$bargain_service = new BargainService();
		$info = $bargain_service->getBargainDetail($bargain_id);
		$this->assign('info', $info);
		
		$goods_ids = '';
		foreach ($info['goods_list'] as $item) {
			$goods_ids .= $item['goods_id'] . ',';
		}
		$this->assign('goods_ids', rtrim($goods_ids, ','));
		
		return view($this->addon_view_path . $this->style . 'Bargain/editBargain.html');
	}
	
	/**
	 * 添加/修改 砍价活动
	 */
	public function ajaxAddEditBargain()
	{
		if (request()->isAjax()) {
			$bargain_service = new BargainService();
			$bargain_id = request()->post('bargain_id', 0);
			$bargain_name = request()->post('bargain_name', '');
			$start_time = request()->post('start_time', '');
			$end_time = request()->post('end_time', '');
			$bargain_min_rate = request()->post('bargain_min_rate', 0);
			$bargain_min_number = request()->post('bargain_min_number', 0);
			$one_min_rate = request()->post('one_min_rate', 0);
			$one_max_rate = request()->post('one_max_rate', 0);
			$goods_id_array = request()->post('goods_id_array', '');
			
			$goods_array = explode(',', $goods_id_array);
			$result = $bargain_service->setBargain($bargain_id, $bargain_name, $start_time, $end_time, $bargain_min_rate, $bargain_min_number, $one_min_rate, $one_max_rate, $goods_array);
			return $result;
		}
	}
	
	/**
	 * 删除砍价活动
	 */
	public function delBargain()
	{
		if (request()->isAjax()) {
			$bargain_service = new BargainService();
			$bargain_id = request()->post('bargain_id', '');
			if (empty($bargain_id)) {
				$this->error("没有获取到砍价信息");
			}
			$result = $bargain_service->delBargain($bargain_id);
			return AjaxReturn($result);
		}
	}
	
	/**
	 * 砍价活动发起记录
	 */
	public function bargainLaunch()
	{
		if (request()->isAjax()) {
			$bargain_service = new BargainService();
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$bargain_id = request()->post('bargain_id', 0);
			$condition = array( 'bargain_id' => $bargain_id );
			$list = $bargain_service->getBargainLaunchList($page_index, $page_size, $condition);
			return $list;
		}
		$bargain_id = request()->get('bargain_id', 0);
		return view($this->addon_view_path . $this->style . 'Bargain/bargainLaunch.html');
	}
	
	/**
	 * 该发起记录砍价记录
	 */
	public function bargainPartake()
	{
		if (request()->isAjax()) {
			$launch_id = request()->post('launch_id', 0);
			$bargain_service = new BargainService();
			$list = $bargain_service->getBargainPartakeList($launch_id);
			return $list;
		}
		$launch_id = request()->get('launch_id', 0);
		return view($this->addon_view_path . $this->style . 'Bargain/bargainPartake.html');
	}
	
	/**
	 * 获取砍价详情
	 */
	public function getBarginInfo()
	{
		$bargain_id = request()->get('bargain_id', '');
		if (!is_numeric($bargain_id)) {
			$this->error("没有获取到砍价信息");
		}
		$bargain_service = new BargainService();
		$detail = $bargain_service->getBargainDetail($bargain_id);
		$goods_ids = '';
		foreach ($detail['goods_list'] as $item) {
			$goods_ids .= $item['goods_id'] . ',';
		}
		$detail['goods_ids'] = rtrim($goods_ids, ',');
		return $detail;
	}
	
	/**
	 * 关闭砍价活动
	 */
	public function closeBargain()
	{
		if (request()->isAjax()) {
			$bargain_id = request()->get('bargain_id', '');
			if (!is_numeric($bargain_id)) {
				return AjaxReturn('-1111');
			}
			$bargain_service = new BargainService();
			$res = $bargain_service->closeBargain($bargain_id);
			return AjaxReturn($res);
		}
	}
	
}