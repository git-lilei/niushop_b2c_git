<?php
/**
 * Express.php
 *
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

namespace data\service;

/**
 * 物流
 */
use data\model\NsExpressShippingItemsLibraryModel;
use data\model\NsExpressShippingItemsModel;
use data\model\NsExpressShippingModel;
use data\model\NsOrderExpressCompanyModel;
use data\model\NsOrderShippingFeeModel;
use data\model\NsShopExpressAddressModel;
use data\service\Address as Address;
use think\Cache;
use think\Log;

class Express extends BaseService
{
	
	/***********************************************************物流模板开始*********************************************************/
	
	/**
	 * 添加物流模板
	 * @param unknown $data
	 * @return number|unknown
	 */
	public function addShippingFee($data)
	{
		Cache::clear('express');
		$order_shipping_fee = new NsOrderShippingFeeModel();
		$order_shipping_fee->startTrans();
		try {
			
			$order_shipping_fee->save($data);
			$order_shipping_fee->commit();
			return 1;
		} catch (\Exception $e) {
			$order_shipping_fee->rollback();
			Log::write("检测错误" . $e->getMessage());
			return $e->getMessage();
		}
	}
	
	/**
	 * 修改物流模板
	 * @param unknown $data
	 * @return number|unknown
	 */
	public function updateShippingFee($data)
	{
		Cache::clear('express');
		$order_shipping_fee = new NsOrderShippingFeeModel();
		$order_shipping_fee->startTrans();
		try {
			$order_shipping_fee->save($data, [
				'shipping_fee_id' => $data["shipping_fee_id"]
			]);
			$order_shipping_fee->commit();
			return 1;
		} catch (\Exception $e) {
			$order_shipping_fee->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 运费模板删除
	 * @param unknown $shipping_fee_id
	 * @return number
	 */
	public function shippingFeeDelete($shipping_fee_id)
	{
		Cache::clear('express');
		$order_shipping_fee = new NsOrderShippingFeeModel();
		$condition = array(
			'shop_id' => 0,
			'shipping_fee_id' => array(
				array(
					"in",
					$shipping_fee_id
				)
			)
		);
		$order_shipping_return = $order_shipping_fee->destroy($condition);
		if ($order_shipping_return > 0) {
			return 1;
		} else {
			return -1;
		}
	}
	
	/**
	 * 运费模板详情
	 * @param unknown $shipping_fee_id
	 * @return string
	 */
	public function shippingFeeDetail($shipping_fee_id)
	{
		$cache = Cache::tag('express')->get('shippingFeeDetail' . $shipping_fee_id);
		if (!empty($cache)) {
			$order_shipping_fee_info = $cache;
		} else {
			$order_shipping_fee = new NsOrderShippingFeeModel();
			$order_shipping_fee_info = $order_shipping_fee->get($shipping_fee_id);
			Cache::tag('express')->set('shippingFeeDetail' . $shipping_fee_id, $order_shipping_fee_info);
		}
		$address = new Address();
		$province = $address->getProvinceList();
		$address_name = "";
		$province_array = explode(",", $order_shipping_fee_info["province_id_array"]);
		$city_array = explode(",", $order_shipping_fee_info["city_id_array"]);
		foreach ($province_array as $e) {
			foreach ($province as $p) {
				if ($e == $p["province_id"]) {
					$address_name = $address_name . $p["province_name"] . ",";
				}
			}
		}
		
		$address_name = substr($address_name, 0, strlen($address_name) - 1);
		$order_shipping_fee_info["address_name"] = $address_name;
		return $order_shipping_fee_info;
	}
	
	/**
	 * 根据物流公司id查询是否有默认地区
	 * @param int $co_id 物流公司id
	 */
	public function isHasExpressCompanyDefaultTemplate($co_id)
	{
		$cache = Cache::tag('express')->get('isHasExpressCompanyDefaultTemplate' . $co_id);
		if (!empty($cache)) {
			return $cache;
		}
		$ns_order_shipping_fee = new NsOrderShippingFeeModel();
		$list = $ns_order_shipping_fee->getQuery([
			'co_id' => $co_id
		], 'is_default');
		$is_default = 1; // 是否有默认地区 1,可以添加默认地区：0，不可以添加默认地区
		foreach ($list as $v) {
			if ($v['is_default']) {
				$is_default = 0;
				break;
			}
		}
		Cache::tag('express')->set('isHasExpressCompanyDefaultTemplate' . $co_id, $is_default);
		return $is_default;
	}
	
	/**
	 * 运费模板列表
	 * @param string $where
	 * @param string $fields
	 * @return mixed|\think\Collection|\think\db\false|PDOStatement|string|array
	 */
	public function shippingFeeQuery($where = "", $fields = "*")
	{
		$data = [ $where, $fields ];
		$data = json_encode($data);
		$cache = Cache::tag('express')->get('shippingFeeQuery' . $data);
		if (!empty($cache)) {
			return $cache;
		}
		$order_shipping_fee = new NsOrderShippingFeeModel();
		$query = $order_shipping_fee->getQuery($where, $fields);
		Cache::tag('express')->set('shippingFeeQuery' . $data, $query);
		return $query;
	}
	
	/**
	 * 查询物流公司所有的省，市，区
	 * @param unknown $express_company_id
	 * @return mixed|array[]|string[]
	 */
	public function getExpressCompanyAddressList($express_company_id)
	{
		$cache = Cache::tag('express')->get('getExpressCompanyAddressList' . $express_company_id);
		if (!empty($cache)) {
			return $cache;
		}
		$order_shipping_fee = new NsOrderShippingFeeModel();
		$province_id_array = '';
		$city_id_array = '';
		$district_id_array = '';
		$shipping_fee_list = $order_shipping_fee->getQuery([ 'co_id' => $express_company_id ], 'province_id_array,city_id_array,district_id_array');
		foreach ($shipping_fee_list as $k => $v) {
			if (!empty($v['province_id_array'])) {
				$province_id_array = $province_id_array . $v['province_id_array'] . ',';
			}
			if (!empty($v['city_id_array'])) {
				$city_id_array = $city_id_array . $v['city_id_array'] . ',';
				
			}
			if (!empty($v['district_id_array'])) {
				$district_id_array = $district_id_array . $v['district_id_array'] . ',';
			}
		}
		if (!empty($province_id_array)) {
			$province_id_array = explode(',', $province_id_array);
			$province_array = array_filter($province_id_array);
		} else {
			$province_array = array();
		}
		if (!empty($city_id_array)) {
			$city_id_array = explode(',', $city_id_array);
			$city_array = array_filter($city_id_array);
		} else {
			$city_array = array();
		}
		if (!empty($district_id_array)) {
			$district_id_array = explode(',', $district_id_array);
			$district_array = array_filter($district_id_array);
		} else {
			$district_array = array();
		}
		
		$data = array(
			'province_id_array' => $province_array,
			'city_id_array' => $city_id_array,
			'district_id_array' => $district_array
		);
		Cache::tag('express')->set('getExpressCompanyAddressList' . $express_company_id, $data);
		return $data;
	}
	
	/**
	 * 获取物流模板列表
	 * @param number $page_index
	 * @param number $page_size
	 * @param string $condition
	 * @param string $order
	 */
	public function getShippingFeeList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$data = [ $page_index, $page_size, $condition, $order ];
		$data = json_encode($data);
		$cache = Cache::tag('express')->get('getShippingFeeList' . $data);
		if (!empty($cache)) {
			$list = $cache;
		} else {
			$ns_order_shipping_fee = new NsOrderShippingFeeModel();
			$list = $ns_order_shipping_fee->pageQuery($page_index, $page_size, $condition, $order, '*');
			Cache::tag('express')->set('getShippingFeeList' . $data, $list);
		}
		
		$address = new Address();
		foreach ($list['data'] as $k => $v) {
			$list['data'][ $k ]['address_list'] = $address->getAddressListById($v['province_id_array'], $v['city_id_array']);
		}
		return $list;
	}
	
	/**
	 * 获取物流公司的省市id组，排除默认地区
	 * @param int $co_id 物流公司id
	 * @param array $province_id_array 排除当前编辑的省id组
	 * @param array $city_id_array 排除当前编辑的市id组
	 * @param array $current_district_id_array 排序当前编辑的区县id组
	 */
	public function getExpressCompanyProvincesAndCitiesById($co_id, $current_province_id_array, $current_city_id_array, $current_district_id_array)
	{
		$data = [ $co_id, $current_province_id_array, $current_city_id_array, $current_district_id_array ];
		$data = json_encode($data);
		$cache = Cache::tag('express')->get('getExpressCompanyProvincesAndCitiesById' . $data);
		if (!empty($cache)) {
			return $cache;
		}
		$curr_province_id_array = []; // 省id组
		$curr_city_id_array = []; // 市id组
		$curr_district_id_array = []; // 区县id组
		
		// 编辑运费模板时的省id组排除
		if (!empty($current_province_id_array)) {
			if (!strstr($current_province_id_array, ',')) {
				array_push($curr_province_id_array, $current_province_id_array);
			} else {
				$curr_province_id_array = explode(',', $current_province_id_array);
			}
		}
		
		// 编辑运费模板时的市id组排除
		if (!empty($current_city_id_array)) {
			if (!strstr($current_city_id_array, ',')) {
				array_push($curr_city_id_array, $current_city_id_array);
			} else {
				$curr_city_id_array = explode(',', $current_city_id_array);
			}
		}
		
		// 编辑运费模板时的区县id组排除
		if (!empty($current_district_id_array)) {
			if (!strstr($current_district_id_array, ',')) {
				array_push($curr_district_id_array, $current_district_id_array);
			} else {
				$curr_district_id_array = explode(',', $current_district_id_array);
			}
		}
		
		$ns_order_shipping_fee = new NsOrderShippingFeeModel();
		$list = $ns_order_shipping_fee->getQuery([
			'co_id' => $co_id,
			'is_default' => 0
		], 'province_id_array,city_id_array,district_id_array');
		
		// 1.把当前公司的所有省市id进行组拼
		$province_id_array = [];
		$city_id_array = [];
		$district_id_array = [];
		
		$res_list['province_id_array'] = [];
		$res_list['city_id_array'] = [];
		$res_list['district_id_array'] = [];
		
		foreach ($list as $k => $v) {
			
			if (!strstr($v['province_id_array'], ',')) {
				array_push($province_id_array, $v['province_id_array']);
			} else {
				$temp_province_array = explode(",", $v['province_id_array']);
				foreach ($temp_province_array as $temp_province_id) {
					array_push($province_id_array, $temp_province_id);
				}
			}
			
			if (!strstr($v['city_id_array'], ',')) {
				array_push($city_id_array, $v['city_id_array']);
			} else {
				$temp_city_array = explode(",", $v['city_id_array']);
				foreach ($temp_city_array as $temp_city_id) {
					array_push($city_id_array, $temp_city_id);
				}
			}
			
			if (!strstr($v['district_id_array'], ',')) {
				array_push($district_id_array, $v['district_id_array']);
			} else {
				$temp_district_array = explode(",", $v['district_id_array']);
				foreach ($temp_district_array as $temp_district_id) {
					array_push($district_id_array, $temp_district_id);
				}
			}
		}
		
		// 2.排除当前编辑用到的省id组
		if (count($province_id_array)) {
			foreach ($province_id_array as $province_id) {
				$flag = true;
				foreach ($curr_province_id_array as $temp_province_id) {
					
					if ($province_id == $temp_province_id) {
						$flag = false;
					}
				}
				if ($flag) {
					array_push($res_list['province_id_array'], $province_id);
				}
			}
		}
		
		// 3.排除当前编辑用到的市id组
		if (count($city_id_array)) {
			foreach ($city_id_array as $city_id) {
				$flag = true;
				foreach ($curr_city_id_array as $temp_city_id) {
					if ($city_id == $temp_city_id) {
						$flag = false;
					}
				}
				if ($flag) {
					array_push($res_list['city_id_array'], $city_id);
				}
			}
		}
		
		// 4.排除当前编辑用到的区县id组
		if (count($district_id_array)) {
			foreach ($district_id_array as $district_id) {
				$flag = true;
				foreach ($curr_district_id_array as $temp_district_id) {
					
					if ($district_id == $temp_district_id) {
						$flag = false;
					}
				}
				if ($flag) {
					array_push($res_list['district_id_array'], $district_id);
				}
			}
		}
		Cache::tag('express')->set('getExpressCompanyProvincesAndCitiesById' . $data, $res_list);
		return $res_list;
	}
	
	/***********************************************************物流模板结束*********************************************************/
	
	
	/***********************************************************物流公司开始*********************************************************/
	
	/**
	 * 添加物流公司
	 * @param unknown $data
	 * @return unknown
	 */
	public function addExpressCompany($data)
	{
		Cache::clear('express');
		$ns_express_company = new NsOrderExpressCompanyModel();
		$ns_express_company->startTrans();
		try {
			if ($data['is_default'] == 1) {
				$this->defaultExpressCompany();
			}
			
			$ns_express_company->save($data);
			$co_id = $ns_express_company->co_id;
			
			$shipping_data = array(
				"shop_id" => 0,
				"template_name" => $data['company_name'],
				"co_id" => $co_id
			);
			$sid = $this->addExpressShipping($shipping_data);
			$shipping_items_data = array(
				"sid" => $sid
			);
			$this->addExpressShippingItems($shipping_items_data);
			$ns_express_company->commit();
			return $ns_express_company->co_id;
		} catch (\Exception $e) {
			$ns_express_company->rollback();
			return $e->getCode();
		}
	}
	
	/**
	 * 修改物流公司
	 * @param unknown $data
	 * @return boolean
	 */
	public function updateExpressCompany($data)
	{
		Cache::clear('express');
		$ns_express_company = new NsOrderExpressCompanyModel();
		if ($data["is_default"] == 1) {
			$this->defaultExpressCompany();
		}
		
		$res = $ns_express_company->save($data, [
			'co_id' => $data["co_id"]
		]);
		return $res;
	}
	
	/**
	 * 把别的改为未默认,把当前设置为默认
	 */
	public function defaultExpressCompany()
	{
		Cache::clear('express');
		$ns_express_company = new NsOrderExpressCompanyModel();
		$data = array(
			'is_default' => 0
		);
		$ns_express_company->save($data, [
			'shop_id' => 0
		]);
	}
	
	/**
	 * 处理物流公司禁用
	 * @param unknown $express_company_id
	 */
	public function dealWithExpressCompany($express_company_id)
	{
		Cache::clear('express');
		$disabled_province = '';
		$disabled_city = '';
		$disabled_district = '';
		$address = new Address();
		//查询所有的地区信息
//		$area_list = $address->getAreaList();
		//查询所有的省信息
		$province_list = $address->getProvinceList();
		//查询所有的市信息
		$city_list = $address->getCityList();
		//查询所有的区县的信息
		$district_list = $address->getDistrictList();
		$company_address_list = $this->getExpressCompanyAddressList($express_company_id);
		//查询物流公司所有地区，省，市，区
		foreach ($district_list as $k_district => $v_district) {
			$district_id = $v_district["district_id"];
			$district_id_deal_array[ $district_id ] = $k_district;
			$is_set = in_array($district_id, $company_address_list['district_id_array']);
			if ($is_set) {
				$disabled_district .= $district_id . ',';
			}
		}
		//整理市的集合
		foreach ($city_list as $k_city => $v_city) {
			$city_is_disabled = $this->dealCityDistrictData($v_city["city_id"], $company_address_list["district_id_array"]);
			if ($city_is_disabled) {
				$disabled_city .= $v_city["city_id"] . ',';
			}
			
		}
		//整理省的集合
		foreach ($province_list as $k_province => $v_province) {
			$province_is_disabled = $this->dealProvinceCityData($v_province['province_id'], $company_address_list['city_id_array']);
			if ($province_is_disabled) {
				$disabled_province .= $v_province['province_id'] . ',';
			}
		}
		$express_company_model = new NsOrderExpressCompanyModel();
		$data = array(
			'disabled_province' => $disabled_province,
			'disabled_city' => $disabled_city,
			'disabled_district' => $disabled_district
		);
		$express_company_model->save($data, [ 'co_id' => $express_company_id ]);
		
	}
	
	/**
	 * 处理省和市的信息
	 * @param unknown $province_id
	 * @param unknown $city_id_array
	 * @return number
	 */
	private function dealProvinceCityData($province_id, $city_id_array)
	{
		if (empty($city_id_array)) {
			return 1;
		}
		$address = new Address();
		$is_disabled = 1;
		$city_child_list = $address->getCityList($province_id);
		if (!empty($city_child_list)) {
			foreach ($city_child_list as $city_obj) {
				if (!in_array($city_obj['city_id'], $city_id_array)) {
					$is_disabled = 0;
					break;
				}
			}
		}
		
		return $is_disabled;
	}
	
	/**
	 * 处理市和 地区的信息
	 * @param unknown $city_id
	 * @param unknown $select_district_ids
	 * @return number
	 */
	private function dealCityDistrictData($city_id, $select_district_ids)
	{
		if (empty($select_district_ids)) {
			return 1;
		}
		$address = new Address();
		$is_disabled = 1;
		$district_child_list = $address->getDistrictList($city_id);
		if (!empty($district_child_list)) {
			foreach ($district_child_list as $k => $district_obj) {
				if (!in_array($district_obj['district_id'], $select_district_ids)) {
					$is_disabled = 0;
					break;
				}
			}
		}
		
		return $is_disabled;
	}
	
	/**
	 * 删除物流公司
	 * @param unknown $co_id
	 * @return number
	 */
	public function expressCompanyDelete($co_id)
	{
		Cache::clear('express');
		$ns_express_company = new NsOrderExpressCompanyModel();//物流公司
		$ns_express_shipping = new NsExpressShippingModel(); //打印模板
		$ns_express_shipping_item = new NsExpressShippingItemsModel(); //打印模板项
		$ns_order_shipping_fee = new NsOrderShippingFeeModel(); //运费模板
		$express_shipping_list = $ns_express_shipping->getQuery([ "co_id" => [ "in", $co_id ] ], "sid");
		$ns_express_company->startTrans();
		try {
			$condition = array(
				'shop_id' => 0,
				'co_id' => array(
					"in",
					$co_id
				)
			);
			
			$ns_express_company->destroy($condition);//删除物流公司
			
			if (!empty($express_shipping_list)) {
				foreach ($express_shipping_list as $v) {
					$ns_express_shipping_item->where([ "sid" => $v["sid"] ])->delete();//删除打印模板项
				}
			}
			
			$ns_express_shipping->destroy([ "co_id" => [ "in", $co_id ] ]);//删除打印模板
			
			$ns_order_shipping_fee->destroy([ "co_id" => [ "in", $co_id ] ]); //删除运费模板
			$ns_express_company->commit();
			return 1;
		} catch (\Exception $e) {
			dump($e->getMessage());
			$ns_express_company->rollback();
			return -1;
		}
	}
	
	/**
	 * 物流公司详情
	 * @param unknown $co_id
	 * @return mixed|\data\model\NsOrderExpressCompanyModel|NULL
	 */
	public function expressCompanyDetail($co_id)
	{
		$cache = Cache::tag('express')->get('expressCompanyDetail' . $co_id);
		if (!empty($cache)) {
			return $cache;
		}
		$ns_express_company = new NsOrderExpressCompanyModel();
		$data = $ns_express_company->get($co_id);
		Cache::tag('express')->set('expressCompanyDetail' . $co_id, $data);
		return $data;
	}
	
	/**
	 * 获取物流公司
	 * @param array $condition
	 * @param string $field
	 * @return mixed|\think\Collection|\think\db\false|PDOStatement|string|array
	 */
	public function getExpressCompanyData($condition = [], $field = "*")
	{
		$data = [ $condition, $field ];
		$data = json_encode($data);
		$cache = Cache::tag('express')->get('getExpressCompanyData' . $data);
		if (!empty($cache)) {
			return $cache;
		}
		$ns_express_company = new NsOrderExpressCompanyModel();
		//默认可以安装的物流公司
		$list = $ns_express_company->getQuery($condition, $field);
		Cache::tag('express')->set('getExpressCompanyData' . $data, $list);
		return $list;
	}
	
	/**
	 * 物流公司列表
	 * @param string $where
	 * @param string $field
	 * @return mixed|\think\Collection|\think\db\false|PDOStatement|string|array
	 */
	public function expressCompanyQuery($where = "", $field = "*")
	{
		$data = [ $where, $field ];
		$data = json_encode($data);
		$cache = Cache::tag('express')->get('expressCompanyQuery' . $data);
		if (!empty($cache)) {
			return $cache;
		}
		$ns_express_company = new NsOrderExpressCompanyModel();
		$list = $ns_express_company->where($where)
			->field($field)
			->select();
		Cache::tag('express')->set('expressCompanyQuery' . $data, $list);
		return $list;
	}
	
	/**
	 * 获取物流公司
	 * @param number $page_index
	 * @param number $page_size
	 * @param string $condition
	 * @param string $order
	 * @return mixed|number[]|string[]|\think\Collection[]|\think\db\false[]|PDOStatement[]|array[]
	 */
	public function getExpressCompanyList($page_index = 1, $page_size = 0, $condition = '', $order = 'co_id desc')
	{
		$data = [ $page_index, $page_size, $condition, $order ];
		$data = json_encode($data);
		$cache = Cache::tag('express')->get('getExpressCompanyList' . $data);
		if (!empty($cache)) {
			return $cache;
		}
		$ns_express_company = new NsOrderExpressCompanyModel();
		$list = $ns_express_company->pageQuery($page_index, $page_size, $condition, $order, '*');
		Cache::tag('express')->set('getExpressCompanyList' . $data, $list);
		return $list;
	}
	
	/***********************************************************物流公司结束*********************************************************/
	
	
	/***********************************************************物流模板打印开始*********************************************************/
	
	/**
	 * 添加物流的模板库
	 * @param unknown $data
	 * @return unknown
	 */
	private function addExpressShipping($data)
	{
		$express_model = new NsExpressShippingModel();
		$data["size_type"] = 1;
		$data["width"] = 0;
		$data["height"] = 0;
		$data["image"] = '';
		$express_model->save($data);
		return $express_model->sid;
	}
	
	/**
	 * 添加打印项
	 * @param unknown $data
	 */
	private function addExpressShippingItems($data)
	{
		$library_model = new NsExpressShippingItemsLibraryModel();
		$library_list = $library_model->getQuery([
			"shop_id" => 0,
			"is_enabled" => 1
		]);
		$x_length = 10;
		$y_length = 11;
		foreach ($library_list as $library_obj) {
			$item_model = new NsExpressShippingItemsModel();
			$data = array(
				"sid" => $data["sid"],
				"field_name" => $library_obj["field_name"],
				"field_display_name" => $library_obj["field_display_name"],
				"is_print" => 1,
				"x" => $x_length,
				"y" => $y_length
			);
			$y_length = $y_length + 25;
			$item_model->save($data);
		}
	}
	
	/**
	 * 更新物流模板的信息, 以及打印的信息
	 * @param unknown $param
	 * @return number|unknown
	 */
	public function updateExpressShipping($param)
	{
		Cache::clear('express');
		$express_model = new NsExpressShippingModel();
		$express_model->startTrans();
		try {
			$data = array(
				"width" => $param["width"],
				"height" => $param["height"],
				"image" => $param["image"]
			);
			$express_model->save($data, [
				"sid" => $param["template_id"]
			]);
			$this->updateExpressShippingItem([ "sid" => $param["template_id"], "itemsArray" => $param["itemsArray"] ]);
			$express_model->commit();
			return 1;
		} catch (\Exception $e) {
			
			$express_model->rollback();
			return $e->getCode();
		}
	}
	
	/**
	 * 编辑物流模板的打印项信息
	 * @param unknown $param
	 */
	public function updateExpressShippingItem($param)
	{
		Cache::clear('express');
		$items_str = explode(";", $param["itemsArray"]);
		foreach ($items_str as $item_obj) {
			$item_obj_str = explode(",", $item_obj);
			$data = array(
				"field_display_name" => $item_obj_str[1],
				"is_print" => $item_obj_str[2],
				"x" => $item_obj_str[3],
				"y" => $item_obj_str[4]
			);
			$field_name = $item_obj_str[0];
			$express_item_model = new NsExpressShippingItemsModel();
			$express_item_model->save($data, [
				"sid" => $param["sid"],
				"field_name" => $field_name
			]);
		}
	}
	
	/**
	 * 得到物流模板
	 * @param unknown $co_id
	 * @return mixed|array|\think\db\false|PDOStatement|string|\think\Model|NULL
	 */
	public function getExpressShipping($co_id)
	{
		$cache = Cache::tag('express')->get('getExpressShipping' . $co_id);
		if (!empty($cache)) {
			return $cache;
		}
		$express_model = new NsExpressShippingModel();
		$express_obj = $express_model->getInfo([
			"co_id" => $co_id
		], "*");
		Cache::tag('express')->set('getExpressShipping' . $co_id, $express_obj);
		return $express_obj;
	}
	
	/**
	 * 获取物流模板内容
	 * @return mixed|\think\Collection|\think\db\false|PDOStatement|string|array
	 */
	public function getExpressShippingItemsLibrary()
	{
		$cache = Cache::tag('express')->get('getExpressShippingItemsLibrary' . 0);
		if (!empty($cache)) {
			return $cache;
		}
		$express_model = new NsExpressShippingItemsLibraryModel();
		$item_list = $express_model->getQuery([
			"shop_id" => 0
		]);
		Cache::tag('express')->set('getExpressShippingItemsLibrary' . $this->instance_id, $item_list);
		return $item_list;
	}
	
	/**
	 * 得到物流模板的打印项
	 * @param unknown $sid
	 * @return mixed|\think\Collection|\think\db\false|PDOStatement|string|array
	 */
	public function getExpressShippingItems($sid)
	{
		$cache = Cache::tag('express')->get('getExpressShippingItems' . $sid);
		if (!empty($cache)) {
			return $cache;
		}
		$express_model = new NsExpressShippingItemsModel();
		$item_list = $express_model->getQuery([
			"sid" => $sid
		]);
		Cache::tag('express')->set('getExpressShippingItems' . $sid, $item_list);
		return $item_list;
	}
	
	/***********************************************************物流模板打印结束*********************************************************/
	
	/***********************************************************物流公司地址开始*********************************************************/
	
	/**
	 * 添加物流公司地址
	 * @param unknown $data
	 * @return unknown
	 */
	public function addShopExpressAddress($data)
	{
		Cache::clear('express');
		$shop_express_address = new NsShopExpressAddressModel();
		$data_consigner = array(
			'is_consigner' => 0,
			'is_receiver' => 0
		);
		$shop_express_address->save($data_consigner, [
			'shop_id' => 0
		]);
		$shop_express_address = new NsShopExpressAddressModel();
		
		$shop_express_address->save($data);
		$express_address_id = $shop_express_address->express_address_id;
		return $express_address_id;
	}
	
	/**
	 * 修改物流地址
	 * @param unknown $data
	 * @return boolean
	 */
	public function updateShopExpressAddress($data)
	{
		Cache::clear('express');
		$shop_express_address = new NsShopExpressAddressModel();
		
		$retval = $shop_express_address->save($data, [
			'express_address_id' => $data["express_address_id"]
		]);
		return $retval;
	}
	
	/**
	 * 修改公司发货标记
	 * @param unknown $express_address_id
	 * @return boolean
	 */
	public function modifyShopExpressAddressConsigner($express_address_id)
	{
		Cache::clear('express');
		$shop_express_address = new NsShopExpressAddressModel();
		$shop_express_address->save([
			'is_consigner' => 0
		], [
			'shop_id' => 0
		]);
		$retval = $shop_express_address->save([
			'is_consigner' => 1
		], [
			'express_address_id' => $express_address_id
		]);
		return $retval;
	}
	
	/**
	 * 修改公司收货标记
	 * @param unknown $express_address_id
	 * @return boolean
	 */
	public function modifyShopExpressAddressReceiver($express_address_id)
	{
		Cache::clear('express');
		$shop_express_address = new NsShopExpressAddressModel();
		$shop_express_address->save([
			'is_receiver' => 0
		], [
			'shop_id' => 0
		]);
		$retval = $shop_express_address->save([
			'is_receiver' => 1
		], [
			'express_address_id' => $express_address_id
		]);
		return $retval;
	}
	
	/**
	 * 查询单条物流地址详情
	 * @param unknown $express_address_id
	 * @return mixed|array|\think\db\false|PDOStatement|string|\think\Model|NULL
	 */
	public function getExpressAddressInfo($condition, $field = "*")
	{
		$json_data = json_encode($condition);
		$cache = Cache::tag('express')->get('getExpressAddressInfo' . $json_data);
		if (!empty($cache)) {
			return $cache;
		} else {
			$shop_express_address = new NsShopExpressAddressModel();
			$retval = $shop_express_address->getInfo($condition, $field);
			Cache::tag('express')->set('getExpressAddressInfo' . $json_data, $retval);
			return $retval;
		}
		
	}
	
	/**
	 * 删除物流地址
	 * @param unknown $express_address_id_array
	 * @return number
	 */
	public function deleteShopExpressAddress($express_address_id_array)
	{
		Cache::clear('express');
		$shop_express_address = new NsShopExpressAddressModel();
		$condition = array(
			'shop_id' => 0,
			'express_address_id' => $express_address_id_array
		);
		$retval = $shop_express_address->destroy($condition);
		return $retval;
	}
	
	/**
	 * 获取公司默认收货地址
	 * @return mixed|string
	 */
	public function getDefaultShopExpressAddress()
	{
		$cache = Cache::tag('express')->get('getDefaultShopExpressAddress' . 0);
		if (!empty($cache)) {
			$data = $cache;
		} else {
			$shop_express_address = new NsShopExpressAddressModel();
			$data = $shop_express_address->getInfo([
				'shop_id' => 0,
				'is_receiver' => 1
			], '*');
			Cache::tag('express')->set('getDefaultShopExpressAddress' . 0, $data);
		}
		
		if (!empty($data)) {
			$address = new Address();
			$address_info = $address->getAddress($data['province'], $data['city'], $data['district']);
			$data['address_info'] = $address_info;
		}
		return $data;
	}
	
	/**
	 * 获取公司物流地址
	 * @param number $page_index
	 * @param number $page_size
	 * @param string $condition
	 * @param string $order
	 * @return mixed|string
	 */
	public function getShopExpressAddressList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$data = [ $page_index, $page_size, $condition, $order ];
		$data = json_encode($data);
		$cache = Cache::tag('express')->get('getShopExpressAddressList' . $data);
		if (!empty($cache)) {
			$list = $cache;
		} else {
			$shop_express_address = new NsShopExpressAddressModel();
			$list = $shop_express_address->pageQuery($page_index, $page_size, $condition, $order, '*');
			Cache::tag('express')->set('getShopExpressAddressList' . $data, $list);
		}
		
		if (!empty($list['data'])) {
			$address = new Address();
			foreach ($list['data'] as $k => $v) {
				
				$address_info = $address->getAddress($v['province'], $v['city'], $v['district']);
				$list['data'][ $k ]['address_info'] = $address_info;
			}
		}
		return $list;
	}
	
	/***********************************************************物流公司地址结束*********************************************************/
	
}