<?php
/**
 * Distribution.php
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

namespace addons\NsO2o\admin\controller;

use addons\NsO2o\data\service\O2o as O2oService;
use app\admin\controller\BaseController;
use app\admin\controller\Express;
use data\service\Address;
use data\service\Config;

/**
 * 本地配送
 */
class Distribution extends BaseController
{
	
	public $addon_view_path;
	
	public function __construct()
	{
		parent::__construct();
		$this->addon_view_path = ADDON_DIR . '/NsO2o/template/';
	}
	
	/**
	 * 配送费用设置
	 */
	public function DistributionConfig()
	{
		$o2o_service = new O2oService();
		$config_service = new Config();
		if (request()->isAjax()) {
			$discount_json = request()->post('discount_json', '');
			$retval = $o2o_service->setDistributionConfig($discount_json);
			$distribution_time_json = request()->post('distribution_time_json', '');
			$set_time_res = $config_service->setDistributionTimeConfig($distribution_time_json);
			if ($retval && $set_time_res) {
				return AjaxReturn($retval);
			}
		} else {
			//获取物流配送三级菜单
			$express = new Express();
			$child_menu_list = $express->getExpressChildMenu(3);
			$this->assign('child_menu_list', $child_menu_list);
			$express_child = $express->getExpressChild(3, 2);
			$this->assign('express_child', $express_child);
			
			$distribution_config = $o2o_service->getDistributionConfig();
			$this->assign('distribution_config', $distribution_config);
			
			//获取配送时间设置
			$distribution_time = $config_service->getDistributionTimeConfig($this->instance_id);
			if ($distribution_time == 0) {
				$time_json = json_encode([]);
			} else {
				$time_json = $distribution_time['value'];
			}
			$this->assign('time_json', $time_json);
			
			return view($this->addon_view_path . $this->style . 'Distribution/distributionConfig.html');
		}
	}
	
	/**
	 * 配送人员列表
	 */
	public function DistributionUserList()
	{
		//获取物流配送三级菜单
		$express = new Express();
		$child_menu_list = $express->getExpressChildMenu(3);
		$this->assign('child_menu_list', $child_menu_list);
		$express_child = $express->getExpressChild(3, 1);
		$this->assign('express_child', $express_child);
		
		$o2o_service = new O2oService();
		if (request()->isAjax()) {
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$search_text = request()->post('search_text', '');
			$condition = array(
				'name' => array(
					'like',
					'%' . $search_text . '%'
				)
			);
			$retval = $o2o_service->getDistributionUserList($page_index, $page_size, $condition);
			return $retval;
		}
		return view($this->addon_view_path . $this->style . 'Distribution/distributionUserList.html');
	}
	
	/**
	 * 获取配送人员信息
	 */
	public function AddUpdateDistributionUser()
	{
		$o2o_service = new O2oService();
		if (request()->isAjax()) {
			$id = request()->post('id', '');
			$condition['id'] = $id;
			$distribution_user_info = $o2o_service->getDistributionUserInfo($condition);
			return $distribution_user_info;
		}
	}
	
	/**
	 * 添加修改配送人员
	 */
	public function AddUpdateDistributionUserAjax()
	{
		$o2o_service = new O2oService();
		if (request()->isAjax()) {
			$id = request()->post('id', '');
			$name = request()->post('name', '');
			$mobile = request()->post('mobile', '');
			$remark = request()->post('remark', '');
			
			if (empty($id)) {
				$retval = $o2o_service->addDistributionUser($name, $mobile, $remark);
			} else {
				$retval = $o2o_service->modifyDistributionUser($id, $name, $mobile, $remark);
			}
			return AjaxReturn($retval);
		}
	}
	
	/**
	 * 功能：删除配送人员
	 */
	public function deleteDistributionUser()
	{
		if (request()->isAjax()) {
			$o2o_service = new O2oService();
			$id = request()->post('id', '');
			$retval = $o2o_service->deleteDistributionUser($id);
			return AjaxReturn($retval);
		}
	}
	
	/**
	 * 配送区域列表
	 */
	public function DistributionAreaManagement()
	{
	    ini_set('memory_limit', '500M'); //临时内存调整
		//获取物流配送三级菜单
		$express = new Express();
		$child_menu_list = $express->getExpressChildMenu(3);
		$this->assign('child_menu_list', $child_menu_list);
		$express_child = $express->getExpressChild(3, 3);
		$this->assign('express_child', $express_child);
		$dataAddress = new Address();
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
		
		return view($this->addon_view_path . $this->style . 'Distribution/distributionAreaManagement.html');
	}
	
	/**
	 * 获取本地配送地区设置
	 */
	public function getDistributionArea()
	{
		$o2o_service = new O2oService();
		$res = $o2o_service->getDistributionAreaInfo(0);
		if ($res != '') {
			$this->assign("provinces", explode(',', $res['province_id']));
			$this->assign("citys", explode(',', $res['city_id']));
			$this->assign("districts", $res["district_id"]);
		}
	}
	
	/**
	 * 通过ajax添加或编辑本地配送区域
	 */
	public function addOrUpdateDistributionAreaAjax()
	{
		if (request()->isAjax()) {
			$o2o_service = new O2oService();
			$store_id = request()->post("store_id", 0);
			$province_id = request()->post("province_id", "");
			$city_id = request()->post("city_id", "");
			$district_id = request()->post("district_id", "");
			$data = array(
				"store_id" => $store_id,
				"province_id" => $province_id,
				"city_id" => $city_id,
				"district_id" => $district_id
			);
			$res = $o2o_service->addOrUpdateDistributionArea($data);
			return AjaxReturn($res);
		}
	}
}