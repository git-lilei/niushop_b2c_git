<?php
/**
 * Express.php
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

use data\model\NsPickedUpAuditorModel;
use data\model\NsPickedUpAuditorViewModel;
use data\service\Address;
use data\service\Express as ExpressService;

/**
 * 物流
 */
class Express extends BaseController
{
	/**
	 * 物流地址
	 */
	public function expressAddress()
	{
		if (request()->isAjax()) {
			$express = new ExpressService();
			$pageindex = request()->post('pageIndex', '');
			$list = $express->getShopExpressAddressList($pageindex, PAGESIZE, [
				'shop_id' => $this->instance_id
			], '');
			return $list;
		} else {
			return view($this->style . 'Express/expressAddress');
		}
	}
	
	/**
	 * 功能说明：获取省
	 */
	public function getProvinceList()
	{
		$address = new Address();
		$data = $address->getProvinceList();
		return $data;
	}
	
	/**
	 * 功能说明：获取市
	 */
	public function getCityList()
	{
		$province = request()->post('province', '');
		$address = new Address();
		$data = $address->getCityList($province);
		return $data;
	}
	
	/**
	 * 功能说明：获取区/县
	 */
	public function getDistrictList()
	{
		$city = request()->post('city', '');
		$address = new Address();
		$data = $address->getDistrictList($city);
		return $data;
	}
	
	/**
	 * 添加物流地址
	 */
	public function addExpressAddress()
	{
		// 获取数据一律使用三元运算符
		$contact = request()->post('contact', '');
		$mobile = request()->post('mobile', '');
		$phone = request()->post('phone', '');
		$company_name = request()->post('company_name', '');
		$province = request()->post('province', '');
		$city = request()->post('city', '');
		$district = request()->post('district', '');
		$zipcode = request()->post('zipcode', '');
		$address = request()->post('address', '');
		$express = new ExpressService();
		$data = array(
			'shop_id' => 0,
			'contact' => $contact,
			'mobile' => $mobile,
			'phone' => $phone,
			'company_name' => $company_name,
			'province' => $province,
			'city' => $city,
			'district' => $district,
			'zipcode' => $zipcode,
			'address' => $address,
			'is_consigner' => 1,
			'is_receiver' => 1,
			'create_date' => time()
		);
		
		$retval = $express->addShopExpressAddress($data);
		return AjaxReturn($retval);
	}
	
	/**
	 * 功能说明： 根据id查看收货地址详情
	 */
	public function ExpressAddressInfo()
	{
		$express_address_id = request()->post('express_address_id', '');
		$express = new ExpressService();
		$condition = [ 'express_address_id' => $express_address_id ];
		$retval = $express->getExpressAddressInfo($condition);
		return $retval;
	}
	
	/**
	 * 修改物流地址
	 */
	public function updateExpressAddress()
	{
		$express = new ExpressService();
		$express_address_id = request()->post('express_address_id', '');
		$contact = request()->post('contact', '');
		$mobile = request()->post('mobile', '');
		$phone = request()->post('phone', '');
		$company_name = request()->post('company_name', '');
		$province = request()->post('province', '');
		$city = request()->post('city', '');
		$district = request()->post('district', '');
		$zipcode = request()->post('zipcode', '');
		$address = request()->post('address', '');
		
		$data = array(
			'contact' => $contact,
			'mobile' => $mobile,
			'phone' => $phone,
			'company_name' => $company_name,
			'province' => $province,
			'city' => $city,
			'district' => $district,
			'zipcode' => $zipcode,
			'address' => $address,
			'modify_date' => time(),
			'express_address_id' => $express_address_id
		);
		
		$retval = $express->updateShopExpressAddress($data);
		return AjaxReturn($retval);
	}
	
	/**
	 * 功能说明：运费模板管理-列表分页
	 */
	public function freightTemplateList()
	{
		if (request()->isAjax()) {
			$pageindex = request()->post('page_index', 1);
			$page_page = request()->post('page_size', PAGESIZE);
			$co_id = request()->post("co_id", 0);
			if ($co_id) {
				$condition['co_id'] = $co_id;
			}
			
			$shippingfee_list = new ExpressService();
			$express_list_pagequery = $shippingfee_list->getShippingFeeList($pageindex, $page_page, $condition, 'is_default desc,create_time desc');
			$totalcount = $express_list_pagequery['total_count'];
			$pagecount = $express_list_pagequery['page_count'];
			$this->assign('data_length', count($express_list_pagequery['data']));
			$this->assign('totalcount', $totalcount);
			$this->assign('pagecount', $pagecount);
			$this->assign("co_id", $co_id);
			$this->assign('express_list_pagequery', $express_list_pagequery['data']); // 列表
			return view($this->style . 'Express/freightTemplateListPage');
		} else {
			$co_id = request()->get("co_id", 0);
			$this->assign("co_id", $co_id);
			return view($this->style . 'Express/freightTemplateList');
		}
	}
	
	/**
	 * 功能说明：运费模板管理-添加
	 */
	public function freightTemplateEdit()
	{
		$express = new ExpressService();
		$address = new Address();
		if (request()->isAjax()) {
			$data = request()->post("data", "");
			$json_data = json_decode($data, true);
			$shipping_fee_id = $json_data['shipping_fee_id']; // 0：添加，大于0：修改
			$co_id = $json_data['co_id']; // 物流公司id
			$is_default = $json_data['is_default']; // 是否默认
			$shipping_fee_name = $json_data['shipping_fee_name']; // 运费模板名称
			$province_id_array = $json_data['province_id_array']; // 省id组
			$city_id_array = $json_data['city_id_array']; // 市id组
			$district_id_array = $json_data["district_id_array"]; // 区县id组
			
			$weight_is_use = $json_data['weight_is_use']; // 是否启用重量运费，0：不启用，1：启用
			$weight_snum = $json_data['weight_snum']; // 首重
			$weight_sprice = $json_data['weight_sprice']; // 首重运费
			$weight_xnum = $json_data['weight_xnum']; // 续重
			$weight_xprice = $json_data['weight_xprice']; // 续重运费
			
			$volume_is_use = $json_data['volume_is_use']; // 是否启用体积计算运费，0：不启用，1：启用
			$volume_snum = $json_data['volume_snum']; // 首体积量
			$volume_sprice = $json_data['volume_sprice']; // 首体积运费
			$volume_xnum = $json_data['volume_xnum']; // 续体积量
			$volume_xprice = $json_data['volume_xprice']; // 续体积运费
			
			$bynum_is_use = $json_data['bynum_is_use']; // 是否启用计件方式运费，0：不启用，1：启用
			$bynum_snum = $json_data['bynum_snum']; // 首件
			$bynum_sprice = $json_data['bynum_sprice']; // 首件运费
			$bynum_xnum = $json_data['bynum_xnum']; // 续件
			$bynum_xprice = $json_data['bynum_xprice']; // 续件运费
			
			
			$data = array(
				'shipping_fee_name' => $shipping_fee_name,
				'co_id' => $co_id,
				'is_default' => $is_default,
				'province_id_array' => $province_id_array,
				'city_id_array' => $city_id_array,
				'district_id_array' => $district_id_array,
				'shop_id' => 0,
				'weight_is_use' => $weight_is_use,
				'weight_snum' => $weight_snum,
				'weight_xnum' => $weight_xnum,
				'weight_sprice' => $weight_sprice,
				'weight_xprice' => $weight_xprice,
				'volume_is_use' => $volume_is_use,
				'volume_snum' => $volume_snum,
				'volume_sprice' => $volume_sprice,
				'volume_xnum' => $volume_xnum,
				'volume_xprice' => $volume_xprice,
				'bynum_is_use' => $bynum_is_use,
				'bynum_snum' => $bynum_snum,
				'bynum_sprice' => $bynum_sprice,
				'bynum_xnum' => $bynum_xnum,
				'bynum_xprice' => $bynum_xprice,
				'update_time' => time()
			);
			
			if ($shipping_fee_id) {
				$data["shipping_fee_id"] = $shipping_fee_id;
				$retval = $express->updateShippingFee($data);
			} else {
				$data["create_time"] = time();
				$retval = $express->addShippingFee($data);
			}
			return AjaxReturn($retval);
		} else {
		    ini_set('memory_limit', '500M'); //临时内存调整
			$co_id = request()->get("co_id", 0); // 物流公司id
			
			$shipping_fee_id = request()->get("shipping_fee_id", 0); // 运费模板id（用于修改运费模板）
			
			if (!$co_id && !$shipping_fee_id) {
				$redirect = __URL(__URL__ . '/' . ADMIN_MODULE . "/express/expresscompany");
				$this->redirect($redirect);
			}
			$this->assign("co_id", $co_id);
			$this->assign("shipping_fee_id", $shipping_fee_id);
			$current_province_id_array = ""; // 省，排除当前编辑的省市id组
			$current_city_id_array = ""; // 市
			$current_district_id_array = ""; // 区县id组
			$is_default = $express->isHasExpressCompanyDefaultTemplate($co_id); // 获取当前物流公司是否有默认地区
			
			if ($shipping_fee_id) {
				// 编辑修改
				$shipping_fee_detail = $express->shippingFeeDetail($shipping_fee_id);
				if ($shipping_fee_detail['is_default']) {
					$is_default = $shipping_fee_detail['is_default'];
				}
				$current_province_id_array = $shipping_fee_detail['province_id_array'];
				$current_city_id_array = $shipping_fee_detail['city_id_array'];
				$current_district_id_array = $shipping_fee_detail['district_id_array'];
				$this->assign("shipping_fee_detail", $shipping_fee_detail);
			}
			$this->assign("is_default", $is_default);
			
			// 当前物流公司已存在的省市id组，将禁用，不能再选择,
			$existing_address_list = $express->getExpressCompanyProvincesAndCitiesById($co_id, $current_province_id_array, $current_city_id_array, $current_district_id_array);
			$existing_address_list['co_id'] = $co_id;
			$address_list = $address->getAreaTree_ext($existing_address_list); // 获取地区树，排除当前物流公司的所有省市（修改时的省市不能禁用）
			
			$this->assign("address_list", $address_list);
			return view($this->style . 'Express/freightTemplateEdit');
		}
	}
	
	/**
	 * 运费模板删除
	 */
	public function freightTemplateDelete()
	{
		$shipping_fee_id = request()->post('shipping_fee_id', '');
		$express = new ExpressService();
		$retval = $express->shippingFeeDelete($shipping_fee_id);
		return AjaxReturn($retval);
	}
	
	/**
	 * 物流公司模板信息
	 */
	public function ExpressTemplate()
	{
		// 微物流->运单模版选择打印项全部检索
		$express = new ExpressService();
		$express_shipping_items_select = $express->getExpressShippingItemsLibrary();
		$field_name = [];
		foreach ($express_shipping_items_select as $key => $value) {
			$field_name[ $key ] = str_replace("A", "", $value['field_name']);
		}
		array_multisort($field_name, SORT_NUMERIC, SORT_ASC, $express_shipping_items_select);
		if (request()->get('co_id')) {
			$id = request()->get('co_id');
		} else {
			$redirect = __URL(__URL__ . '/' . ADMIN_MODULE . "/express/expresscompany");
			$this->redirect($redirect);
		}
		$express_company_detail = $express->expressCompanyDetail($id);
		$express_shipping_detail = $express->getExpressShipping($id);
		$sid = 0;
		if (!empty($express_shipping_detail)) {
			$sid = $express_shipping_detail["sid"];
		}
		$print = $express->getExpressShippingItems($sid);
		
		if (!empty($print)) {
			foreach ($print as $key => $value) {
				$field_name[ $key ] = str_replace("A", "", $value['field_name']);
			}
			array_multisort($field_name, SORT_NUMERIC, SORT_ASC, $print);
		}
		$this->assign('express_company_select', $express_company_detail);
		$this->assign('express_shipping_select', $express_shipping_detail);
		$this->assign('print', $print);
		$this->assign("express_id", $id);
		$this->assign('express_shipping_items_select', $express_shipping_items_select);
		
		return view($this->style . 'Express/expressTemplate');
	}
	
	/**
	 * 功能说明：运单模板管理-添加-添加保存-编辑保存
	 * 更新 物流公司的模板
	 */
	public function SetPrintTemplate()
	{
		// 模板id
		$template_id = request()->post('templateID', '');
		// 物流id
		$co_id = request()->post('express_id', '');
		// 模板宽度
		$width = request()->post('width_length', '');
		// 模板高度
		$height = request()->post('heigth_length', '');
		// 图片路径
		$img = request()->post('imgUrl', '');
		// 打印项
		$itemsArray = request()->post('sendDatas', '');
		$express_service = new ExpressService();
		// 更新模板信息
		
		$data = array(
			"template_id" => $template_id,
			"width" => $width,
			"height" => $height,
			"image" => $img,
			"itemsArray" => $itemsArray
		);
		$result = $express_service->updateExpressShipping($data);
		return $result;
	}
	
	public function GetTemPalteElement()
	{
		if (request()->post('templateID')) {
			$id = request()->post('templateID');
		} else {
			return false;
		}
		$express = new ExpressService();
		$express_shipping_select = $express->getExpressShippingItems($id);
		$data = $express_shipping_select[0][0];
		$data['template'] = $express_shipping_select[1];
		return AjaxReturn($data);
	}
	
	/**
	 * 功能：物流公司
	 */
	public function expressCompany()
	{
		$expressCompany = new ExpressService();
		if (request()->isAjax()) {
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$search_text = request()->post('search_text', '');
			$condition = array(
				'shop_id' => $this->instance_id,
				'company_name|express_no' => array(
					'like',
					'%' . $search_text . '%'
				)
			);
			$retval = $expressCompany->getExpressCompanyList($page_index, $page_size, $condition);
			return $retval;
		}
		//获取物流配送三级菜单
		$child_menu_list = $this->getExpressChildMenu(1);
		$this->assign('child_menu_list', $child_menu_list);
		$express_child = $this->getExpressChild(1, 1);
		$this->assign('express_child', $express_child);
		$express_company_data = $expressCompany->getExpressCompanyData([]);
		$this->assign("express_company_data", $express_company_data);
		return view($this->style . 'Express/expressCompany');
	}
	
	/**
	 * 功能：添加物流公司
	 */
	public function addExpressCompany()
	{
		$expressCompany = new ExpressService();
		if (request()->isAjax()) {
			$company_name = request()->post('company_name', '');
			$express_logo = request()->post('express_logo', '');
			$express_no = request()->post('express_no', '');
			$is_enabled = request()->post('is_enabled', '');
			$image = request()->post('image', '');
			$phone = request()->post('phone', '');
			$orders = request()->post('orders', '');
			$is_default = request()->post('is_default', '0');
			$data = array(
				"shop_id" => $this->instance_id,
				"company_name" => $company_name,
				"express_logo" => $express_logo,
				"express_no" => $express_no,
				"is_enabled" => $is_enabled,
				"image" => $image,
				"phone" => $phone,
				"orders" => $orders,
				"is_default" => $is_default
			);
			
			$retval = $expressCompany->addExpressCompany($data);
			return AjaxReturn($retval);
		}
		return view($this->style . 'Express/addExpressCompany');
	}
	
	/**
	 * 功能：修改物流公司信息
	 */
	public function updateExpressCompany()
	{
		$expressCompany = new ExpressService();
		if (request()->isAjax()) {
			$co_id = request()->post('co_id', '');
			$company_name = request()->post('company_name', '');
			$express_logo = request()->post('express_logo', '');
			$express_no = request()->post('express_no', '');
			$is_enabled = request()->post('is_enabled', '');
			$image = request()->post('image', '');
			$phone = request()->post('phone', '');
			$orders = request()->post('orders', '');
			$is_default = request()->post('is_default', '0');
			
			$data = array(
				'shop_id' => 0,
				'company_name' => $company_name,
				'express_logo' => $express_logo,
				'express_no' => $express_no,
				'is_enabled' => $is_enabled,
				'image' => $image,
				'phone' => $phone,
				'orders' => $orders,
				'is_default' => $is_default,
				'co_id' => $co_id
			);
			
			$retval = $expressCompany->updateExpressCompany($data);
			return AjaxReturn($retval);
		}
		$co_id = request()->get("co_id", "");
		if (empty($co_id)) {
			$redirect = __URL(__URL__ . '/' . ADMIN_MODULE . "/express/expresscompany");
			$this->redirect($redirect);
		}
		$expressCompanyinfo = $expressCompany->expressCompanyDetail($co_id);
		$this->assign('expressCompany', $expressCompanyinfo);
		return view($this->style . 'Express/updateExpressCompany');
	}
	
	/**
	 * 功能：删除物流公司
	 */
	public function expressCompanyDelete()
	{
		if (request()->isAjax()) {
			$expressCompany = new ExpressService();
			$co_id = request()->post('co_id', '');
			$retval = $expressCompany->expressCompanyDelete($co_id);
			return AjaxReturn($retval);
		}
	}
	
	/**
	 * 功能说明：物流地址删除
	 */
	public function deleteShopExpressAddress()
	{
		if (request()->isAjax()) {
			$expressCompany = new ExpressService();
			$express_address_id = request()->post('express_address_id', '');
			$retval = $expressCompany->deleteShopExpressAddress($express_address_id);
			return AjaxReturn($retval);
		}
	}
	
	/**
	 * 功能说明：设置默认地址
	 * $addressType 为0发货地址 1收货地址
	 */
	public function modifyShopExpressAddress()
	{
		if (request()->isAjax()) {
			$expressCompany = new ExpressService();
			$addressType = request()->post('addressType', '');
			$express_address_id = request()->post('express_address_id', '');
			if ($addressType == 0) {
				$retval = $expressCompany->modifyShopExpressAddressConsigner($express_address_id);
			} elseif ($addressType == 1) {
				$retval = $expressCompany->modifyShopExpressAddressReceiver($express_address_id);
			}
			return AjaxReturn($retval);
		}
	}
	
	/**
	 * 物流配送三级菜单
	 */
	public function getExpressChildMenu($menu_id)
	{
		$child_menu_list = array(
			array(
				"menu_id" => 1,
				'url' => "express/expresscompany",
				'menu_name' => "物流配送",
				"active" => 0
			),
			array(
				"menu_id" => 2,
				'module' => 'nspickup',
				'url' => "shop/pickuppointlist",
				'menu_name' => "门店自提",
				"active" => 0
			),
			array(
				"menu_id" => 3,
				'module' => 'nso2o',
				'url' => "distribution/distributionuserlist",
				'menu_name' => "本地配送",
				"active" => 0
			),
			array(
				"menu_id" => 4,
				'url' => "config/areamanagement",
				'menu_name' => "地址管理",
				"active" => 0
			),
		);
		$is_support_o2o = IS_SUPPORT_O2O;
		$is_support_pickup = addon_is_exit('NsPickup');
		foreach ($child_menu_list as $k => $v) {
			if ($menu_id == $v['menu_id']) {
				$child_menu_list[ $k ]['active'] = 1;
			}
			if ($is_support_o2o != 1) {
				if ($v['menu_id'] == 3) {
					unset($child_menu_list[ $k ]);
				}
			}
			if ($is_support_pickup != 1) {
				if ($v['menu_id'] == 2) {
					unset($child_menu_list[ $k ]);
				}
			}
		}
		return $child_menu_list;
	}
	
	/**
	 * 物流配送
	 */
	public function expressLogistics()
	{
		$express_child = $this->getExpressChild(2, 1);
		$this->assign('express_child', $express_child);
		return view($this->style . 'Express/expressLogistics');
	}
	
	/**
	 * 获取物流配送（1）或者本地配送（2）的菜单
	 */
	public function getExpressChild($express_id, $child_id = 1)
	{
		switch ($express_id) {
			case 1 :
				$express_child = array(
					'express_id' => 1,
					array(
						"child_id" => 1,
						'url' => "express/expresscompany",
						'child_name' => "物流公司",
						"active" => 0
					),
					array(
						"child_id" => 2,
						'url' => "config/distributionareamanagement",
						'child_name' => "货到付款地区管理",
						"active" => 0
					),
					array(
						"child_id" => 3,
						'url' => "config/expressmessage",
						'child_name' => "物流跟踪设置",
						"active" => 0
					),
				);
				break;
			case 2 :
				$express_child = array(
					'express_id' => 2,
					array(
						"child_id" => 1,
						'module' => 'nspickup',
						'url' => "shop/pickuppointlist",
						'child_name' => "门店管理",
						"active" => 0
					),
					array(
						"child_id" => 2,
						'module' => 'nspickup',
						'url' => "shop/pickuppointfreight",
						'child_name' => "门店运费",
						"active" => 0
					),
					array(
						"child_id" => 3,
						'module' => 'nspickup',
						'url' => "shop/pickupAuditorList",
						'child_name' => "门店审核人员管理",
						"active" => 0
					)
				);
				break;
			case 3 :
				$express_child = array(
					'express_id' => 3,
					array(
						"child_id" => 1,
						'module' => 'nso2o',
						'url' => "distribution/distributionuserlist",
						'child_name' => "配送人员",
						"active" => 0
					),
					array(
						"child_id" => 2,
						'module' => 'nso2o',
						'url' => "distribution/distributionConfig",
						'child_name' => "配送费用",
						"active" => 0
					),
					
					array(
						"child_id" => 3,
						'module' => 'nso2o',
						'url' => "distribution/distributionareamanagement",
						'child_name' => "配送地区",
						"active" => 0
					)
				);
				
				break;
			
			case 4 :
				$express_child = array(
					'express_id' => 4,
					array(
						"child_id" => 1,
						'url' => "config/areamanagement",
						'child_name' => "地区管理",
						"active" => 0
					),
					array(
						"child_id" => 2,
						'url' => "order/returnsetting",
						'child_name' => "商家地址",
						"active" => 0
					),
				);
				break;
		}
		foreach ($express_child as $k => $v) {
			if ($child_id == $v['child_id']) {
				$express_child[ $k ]['active'] = 1;
			}
		}
		return $express_child;
	}
	
	/**
	 * 添加审核人员
	 */
	public function addPickupAuditor($auditor_arr, $pickupPoint_id)
	{
		$res = 0;
		if (!empty($auditor_arr) && !empty($pickupPoint_id)) {
			$auditor_arr = json_decode($auditor_arr, true);
			foreach ($auditor_arr as $v) {
				$data = array(
					"uid" => $v['uid'],
					"pickup_id" => $pickupPoint_id,
					"create_time" => time()
				);
				$ns_pickedup_auditor = new NsPickedUpAuditorModel();
				$ns_pickedup_auditor->save($data);
			}
			$res = $ns_pickedup_auditor->auditor_id;
		}
		return $res;
	}
	
	/**
	 * 删除门店审核人员
	 */
	public function deletePickupAuditor($auditor_id)
	{
		$ns_pickedup_auditor = new NsPickedUpAuditorModel();
		$res = $ns_pickedup_auditor->destroy([ 'auditor_id' => [ "in", $auditor_id ] ]);
		return $res;
	}
	
	/**
	 * 获取门店审核员列表
	 */
	public function getPickupAuditorList($page_index, $page_size, $condition, $order)
	{
		$ns_pickedup_auditor = new NsPickedUpAuditorViewModel();
		$list = $ns_pickedup_auditor->getViewList($page_index, $page_size, $condition, $order);
		return $list;
	}
	
	/**
	 * 安装物流公司
	 */
	public function installExpressCompany()
	{
		$template_id = request()->post("template_id", 0);
		if (empty($template_id))
			return AjaxReturn(0);
		
		$express_service = new ExpressService();
		$express_company_list = $express_service->getExpressCompanyData();
		foreach ($express_company_list as $k => $v) {
			if ($v["template_id"] == $template_id) {
				$data = array(
					"shop_id" => $this->instance_id,
					"company_name" => $v["company_name"],
					"express_logo" => $v["express_logo"],
					"express_no" => $v["express_no"],
					"is_enabled" => $v["is_enabled"],
					"image" => $v["image"],
					"phone" => $v["phone"],
					"orders" => $v["orders"],
					"is_default" => $v["is_default"],
					"template_id" => $template_id
				);
				$retval = $express_service->addExpressCompany($data);
				return AjaxReturn($retval);
			}
		}
		return AjaxReturn(0);
	}
	
}