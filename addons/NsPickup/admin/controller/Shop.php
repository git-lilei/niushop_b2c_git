<?php
/**
 * Shop.php
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

namespace addons\NsPickup\admin\controller;

use data\service\Shop as ShopService;
use data\service\Config;
use app\admin\controller\Express;
use app\admin\controller\BaseController;

/**
 * 店铺设置控制器
 */
class Shop extends BaseController
{
	public $addon_view_path;
	
	public function __construct()
	{
		parent::__construct();
		$this->addon_view_path = ADDON_DIR . '/NsPickup/template/';
	}
	
	/**
	 * 自提点列表
	 */
	public function pickupPointList()
	{
		//获取物流配送三级菜单
		$express = new Express();
		$child_menu_list = $express->getExpressChildMenu(2);
		$this->assign('child_menu_list', $child_menu_list);
		$express_child = $express->getExpressChild(2, 1);
		$this->assign('express_child', $express_child);
		
		if (request()->isAjax()) {
			$shop = new ShopService();
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$search_text = request()->post('search_text', '');
			$condition = array(
				'name' => array(
					'like',
					'%' . $search_text . '%'
				)
			);
			$result = $shop->getPickupPointList($page_index, $page_size, $condition, 'create_time asc');
			return $result;
		} else {
			return view($this->addon_view_path . $this->style . "Shop/sinceList.html");
		}
	}
	
	/**
	 * 自提点运费菜单
	 */
	public function pickuppointfreight()
	{
		//获取物流配送三级菜单
		$express = new Express();
		$child_menu_list = $express->getExpressChildMenu(2);
		$this->assign('child_menu_list', $child_menu_list);
		$express_child = $express->getExpressChild(2, 2);
		$this->assign('express_child', $express_child);
		
		$config_service = new Config();
		$config_info = $config_service->getConfig($this->instance_id, 'PICKUPPOINT_FREIGHT');
		$this->assign('config', json_decode($config_info['value']));
		return view($this->addon_view_path . $this->style . "Shop/pickupPointFreight.html");
	}
	
	/**
	 * 自提门店审核人员管理
	 */
	public function pickupAuditorList()
	{
		$express = new Express();
		if (request()->isAjax()) {
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$list = $express->getPickupAuditorList($page_index, $page_size, "", "create_time desc");
			return $list;
		}
		$child_menu_list = $express->getExpressChildMenu(2);
		$this->assign('child_menu_list', $child_menu_list);
		$express_child = $express->getExpressChild(2, 3);
		$this->assign('express_child', $express_child);
		return view($this->addon_view_path . $this->style . "Shop/pickupAuditorList.html");
	}
	
	/**
	 * 添加自提门店审核人员
	 */
	public function addPickupAuditor()
	{
		if (request()->isAjax()) {
			$express = new Express();
			$auditor_arr = request()->post("auditor_arr", "");
			$pickupPoint_id = request()->post("pickupPoint_id", 0);
			$res = $express->addPickupAuditor($auditor_arr, $pickupPoint_id);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 删除自提门店审核人员
	 */
	public function deletePickupAuditor()
	{
		if (request()->isAjax()) {
			$express = new Express();
			$auditor_id = request()->post("auditor_id", "");
			$res = $express->deletePickupAuditor($auditor_id);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 修改自提点运费菜单
	 */
	public function pickupPointFreightAjax()
	{
		if (request()->isAjax()) {
			$is_enable = request()->post('is_enable', 0);
			$pickup_freight = request()->post('pickup_freight', '');
			$manjian_freight = request()->post('manjian_freight', '');
			$config_service = new Config();
			$res = $config_service->setPickupPointFreight($is_enable, $pickup_freight, $manjian_freight);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 添加自提点
	 */
	public function addPickupPoint()
	{
		if (request()->isAjax()) {
			$shop = new ShopService();
			$name = request()->post('name');
			$address = request()->post('address');
			$contact = request()->post('contact');
			$phone = request()->post('phone');
			$province_id = request()->post('province_id');
			$city_id = request()->post('city_id');
			$district_id = request()->post('district_id', 0);
			$longitude = request()->post('longitude', 0);
			$latitude = request()->post('latitude', 0);
			$data = array(
				"shop_id" => $this->instance_id,
				"name" => $name,
				"address" => $address,
				"contact" => $contact,
				"phone" => $phone,
				"province_id" => $province_id,
				"city_id" => $city_id,
				"district_id" => $district_id,
				"longitude" => $longitude,
				"latitude" => $latitude
			);
			$res = $shop->addPickupPoint($data);
			return AjaxReturn($res);
		}
		return view($this->addon_view_path . $this->style . "Shop/addSince.html");
	}
	
	/**
	 * 修改自提点
	 */
	public function updatePickupPoint()
	{
		$pickip_id = request()->get('id', '');
		$shop = new ShopService();
		if (request()->isAjax()) {
			$id = request()->post('id');
			$shop_id = 0;
			$name = request()->post('name');
			$address = request()->post('address');
			$contact = request()->post('contact');
			$phone = request()->post('phone');
			$province_id = request()->post('province_id', 0);
			$city_id = request()->post('city_id', 0);
			$district_id = request()->post('district_id', 0);
			$longitude = request()->post('longitude', 0);
			$latitude = request()->post('latitude', 0);
			$data = array(
				"name" => $name,
				"address" => $address,
				"contact" => $contact,
				"phone" => $phone,
				"province_id" => $province_id,
				"city_id" => $city_id,
				"district_id" => $district_id,
				"longitude" => $longitude,
				"latitude" => $latitude
			);
			$condition = array(
				"id" => $id,
				"shop_id" => $shop_id
			);
			$res = $shop->updatePickupPoint($data, $condition);
			return AjaxReturn($res);
		}
		$pickupPoint_detail = $shop->getPickupPointDetail($pickip_id);
		
		$this->assign('pickupPoint_detail', $pickupPoint_detail);
		$this->assign('pickip_id', $pickip_id);
		return view($this->addon_view_path . $this->style . "Shop/updatePickupPoint.html");
	}
	
	/**
	 * 删除自提点
	 */
	public function deletepickupPoint()
	{
		if (request()->isAjax()) {
			$pickip_id = request()->post('pickupPoint_id');
			$shop = new ShopService();
			$res = $shop->deletePickupPoint($pickip_id);
			return AjaxReturn($res);
		}
	}
}
