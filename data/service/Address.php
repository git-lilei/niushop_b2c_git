<?php
/**
 * Address.php
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
 * @date : 2015.4.24
 * @version : v1.0.0.0
 */

namespace data\service;

/**
 * 区域地址
 */
use data\model\AreaModel as Area;
use data\model\CityModel as City;
use data\model\DistrictModel;
use data\model\DistrictModel as District;
use data\model\NsOffpayAreaModel;
use data\model\ProvinceModel as Province;
use think\Cache;

class Address extends BaseService
{
	
	/***********************************************************地址开始*********************************************************/
	
	/**
	 * $upType 修改类型 1排序 2名称
	 * $regionType 修改地区类型 1省 2市 3县
	 */
	public function updateRegionNameAndRegionSort($params)
	{
		Cache::clear("address");
		if ($params['region_type'] == 1) {
			$province = new Province();
			if ($params['up_type'] == 1) {
				$res = $province->save([
					'sort' => $params['region_sort']
				], [
					'province_id' => $params['region_id']
				]);
				return $res;
			}
			if ($params['up_type'] == 2) {
				$res = $province->save([
					'province_name' => $params['region_name']
				], [
					'province_id' => $params['region_id']
				]);
				return $res;
			}
		}
		if ($params['region_type'] == 2) {
			$city = new City();
			if ($params['up_type'] == 1) {
				$res = $city->save([
					'sort' => $params['region_sort']
				], [
					'city_id' => $params['region_id']
				]);
				return $res;
			}
			if ($params['up_type'] == 2) {
				$res = $city->save([
					'city_name' => $params['region_name']
				], [
					'city_id' => $params['region_id']
				]);
				return $res;
			}
		}
		if ($params['region_type'] == 3) {
			$district = new District();
			if ($params['up_type'] == 1) {
				$res = $district->save([
					'sort' => $params['region_sort']
				], [
					'district_id' => $params['region_id']
				]);
				return $res;
			}
			if ($params['up_type'] == 2) {
				$res = $district->save([
					'district_name' => $params['region_name']
				], [
					'district_id' => $params['region_id']
				]);
				return $res;
			}
		}
	}
	
	/**
	 * 获取地址 返回（例如： 山西省 太原市 小店区）
	 */
	public function getAddress($province_id, $city_id, $district_id)
	{
		$cache = Cache::tag("address")->get("getAddress" . $province_id . '_' . $city_id . '_' . $district_id);
		if (empty($cache)) {
			$province = new Province();
			$city = new City();
			$district = new District();
			$province_name = $province->getInfo('province_id = ' . $province_id, 'province_name');
			$city_name = $city->getInfo('city_id = ' . $city_id, 'city_name');
			$district_name = $district->getInfo('district_id = ' . $district_id, 'district_name');
			$address = $province_name['province_name'] . '&nbsp;' . $city_name['city_name'] . '&nbsp;' . $district_name['district_name'];
			Cache::tag("address")->set("getAddress" . $province_id . '_' . $city_id . '_' . $district_id, $address);
			return $address;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 获取地址 返回数组形式
	 */
	public function getAddressArray($province_id, $city_id, $district_id)
	{
		$cache = Cache::tag("address")->get("getAddressArray" . $province_id . '_' . $city_id . '_' . $district_id);
		if (empty($cache)) {
			$addressArr = array(
				"province_name" => "",
				"city_name" => "",
				"district_name" => ""
			);
			$province = new Province();
			$city = new City();
			$district = new District();
			$province_name = $province->getInfo('province_id = ' . $province_id, 'province_name');
			$city_name = $city->getInfo('city_id = ' . $city_id, 'city_name');
			$district_name = $district->getInfo('district_id = ' . $district_id, 'district_name');
			if (!empty($province_name['province_name'])) $addressArr["province_name"] = $province_name['province_name'];
			if (!empty($city_name['city_name'])) $addressArr["city_name"] = $city_name['city_name'];
			if (!empty($district_name['district_name'])) $addressArr["district_name"] = $district_name['district_name'];
			
			Cache::tag("address")->set("getAddressArray" . $province_id . '_' . $city_id . '_' . $district_id, $addressArr);
			return $addressArr;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 根据省id组、市id组查询地址信息，并整理
	 */
	public function getAddressListById($province_id_arr, $city_id_arr)
	{
		$cache = Cache::tag("address")->get("getAddressListById" . $province_id_arr . '_' . $city_id_arr);
		if (empty($cache)) {
			$province = new Province();
			$city = new City();
			$province_condition = array(
				'province_id' => array(
					'in',
					$province_id_arr
				)
			);
			$city_condition = array(
				'city_id' => array(
					'in',
					$city_id_arr
				)
			);
			$province_list = $province->getQuery($province_condition, 'province_id,province_name', 'sort asc');
			$city_list = $city->getQuery($city_condition, 'province_id,city_name,city_id', 'sort asc');
			foreach ($province_list as $k => $v) {
				$list['province_list'][ $k ] = $v;
				$children_list = array();
				foreach ($city_list as $city_k => $city_v) {
					if ($v['province_id'] == $city_v['province_id']) {
						$children_list[ $city_k ] = $city_v;
					}
				}
				$list['province_list'][ $k ]['city_list'] = $children_list;
			}
			Cache::tag("address")->set("getAddressListById" . $province_id_arr . '_' . $city_id_arr, $list);
			return $list;
		} else {
			return $cache;
		}
		
	}
	
	/***********************************************************地址结束*********************************************************/
	
	
	/***********************************************************省级开始*********************************************************/
	
	/**
	 * 添加省级
	 */
	public function addProvince($data)
	{
		Cache::clear("address");
		$province = new Province();
		$province->save($data);
		return $province->province_id;
	}
	
	/**
	 * 修改省级区域
	 */
	public function updateProvince($data)
	{
		Cache::clear("address");
		$province = new Province();
		return $province->save($data, [
			"province_id" => $data['province_id']
		]);
	}
	
	/**
	 * 删除省
	 */
	public function deleteProvince($province_id)
	{
		Cache::clear("address");
		$province = new Province();
		$city = new City();
		$province->startTrans();
		try {
			$city_list = $city->getQuery([
				'province_id' => $province_id
			], 'city_id');
			foreach ($city_list as $k => $v) {
				$this->deleteCity($v['city_id']);
			}
			$province->destroy($province_id);
			$province->commit();
			return 1;
		} catch (\Exception $e) {
			$province->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 通过省级id获取其下级的数量
	 */
	public function getCityCountByProvinceId($province_id)
	{
		$cache = Cache::tag("address")->get("getCityCountByProvinceId" . '_' . $province_id);
		if (empty($cache)) {
			$city = new City();
			$count = $city->getCount([
				'province_id' => $province_id
			]);
			Cache::tag("address")->set("getCityCountByProvinceId" . '_' . $province_id, $count);
			return $count;
		} else {
			return $cache;
		}
		
	}
	
	/**
	 * 获取省名称
	 */
	public function getProvinceName($province_id)
	{
		$cache = Cache::tag("address")->get("getProvinceName" . $province_id);
		if (empty($cache)) {
			$province = new Province();
			if (!empty($province_id)) {
				$condition = array(
					'province_id' => array(
						'in',
						$province_id
					)
				);
				$list = $province->getQuery($condition, 'province_name');
			}
			$name = '';
			if (!empty($list)) {
				foreach ($list as $k => $v) {
					$name .= $v['province_name'] . ',';
				}
				$name = substr($name, 0, strlen($name) - 1);
			}
			Cache::tag("address")->set("getProvinceName" . $province_id, $name);
			return $name;
		} else {
			return $cache;
		}
		
	}
	
	/**
	 * 获取省id
	 */
	public function getProvinceId($province_name)
	{
		$cache = Cache::tag("address")->get("getProvinceId" . '_' . $province_name);
		if (empty($cache)) {
			$province = new Province();
			$province_id = $province->getInfo([
				'province_name' => $province_name
			], 'province_id');
			Cache::tag("address")->set("getProvinceId" . '_' . $province_name, $province_id);
			return $province_id;
		} else {
			return $cache;
		}
	}
	
	/**
	 *  获取省
	 */
	public function getProvinceListById($province_id)
	{
		$cache = Cache::tag("address")->get("getProvinceListById" . $province_id);
		if (empty($cache)) {
			$province = new Province();
			$condition = array(
				'province_id' => array(
					'in',
					$province_id
				)
			);
			$list = $province->getQuery($condition, 'province_id,area_id,province_name,sort', 'sort asc');
			Cache::tag("address")->set("getProvinceListById" . $province_id, $list);
			return $list;
		} else {
			return $cache;
		}
		
	}
	
	/**
	 * 获取省列表
	 */
	public function getProvinceList($area_id = 0)
	{
		$cache = Cache::tag("address")->get("getProvinceList" . $area_id);
		if (empty($cache)) {
			$province = new Province();
			if ($area_id == -1) {
				$list = array();
			} elseif ($area_id == 0) {
				$list = $province->getQuery('', 'province_id,area_id,province_name,sort', 'sort asc');
			} else {
				$list = $province->getQuery([
					'area_id' => $area_id
				], 'province_id,area_id,province_name,sort', 'sort asc');
			}
			Cache::tag("address")->set("getProvinceList" . $area_id, $list);
			return $list;
		} else {
			return $cache;
		}
		
	}
	
	/***********************************************************省级结束*********************************************************/
	
	
	/***********************************************************市级开始*********************************************************/
	
	/**
	 * 添加市级地区
	 */
	public function editCity($data)
	{
		Cache::clear("address");
		$city = new City();
		if (!empty($data['city_id'])) {
			$res = $city->save($data, [
				'city_id' => $data['city_id']
			]);
			return $res;
		} else {
			$city->save($data);
			return $city->city_id;
		}
	}
	
	/**
	 * 删除 市
	 */
	public function deleteCity($city_id)
	{
		Cache::clear("address");
		$city = new City();
		$district = new District();
		$city->startTrans();
		try {
			$district->destroy([
				'city_id' => $city_id
			]);
			$city->destroy($city_id);
			$city->commit();
			return 1;
		} catch (\Exception $e) {
			$city->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 通过市级id查询区县数量
	 */
	public function getDistrictCountByCityId($city_id)
	{
		$cache = Cache::tag("address")->get("getDistrictCountByCityId" . '_' . $city_id);
		if (empty($cache)) {
			$district = new District();
			$count = $district->getCount([
				'city_id' => $city_id
			]);
			Cache::tag("address")->set("getDistrictCountByCityId" . '_' . $city_id, $count);
			return $count;
		} else {
			return $cache;
		}
		
	}
	
	/**
	 * 获取市名称
	 */
	public function getCityName($city_id)
	{
		$cache = Cache::tag("address")->get("getCityName" . $city_id);
		if (empty($cache)) {
			$city = new City();
			if (!empty($city_id)) {
				$condition = array(
					'city_id' => array(
						'in',
						$city_id
					)
				);
				$list = $city->getQuery($condition, 'city_name');
			}
			
			$name = '';
			if (!empty($list)) {
				foreach ($list as $k => $v) {
					$name .= $v['city_name'] . ',';
				}
				$name = substr($name, 0, strlen($name) - 1);
			}
			Cache::tag("address")->set("getCityName" . $city_id, $name);
			return $name;
		} else {
			return $cache;
		}
		
	}
	
	/**
	 * 获取市id
	 */
	public function getCityId($city_name)
	{
		$cache = Cache::tag("address")->get("getCityId" . '_' . $city_name);
		if (empty($cache)) {
			$city = new City();
			$city_id = $city->getInfo([
				'city_name' => $city_name
			], 'city_id');
			Cache::tag("address")->set("getCityId" . '_' . $city_name, $city_id);
			return $city_id;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 获取市的第一个区
	 */
	public function getCityFirstDistrict($city_id)
	{
		$cache = Cache::tag("address")->get("getCityFirstDistrict" . '_' . $city_id);
		if (empty($cache)) {
			$district_model = new DistrictModel();
			$data = $district_model->getFirstData([
				'city_id' => $city_id
			], '');
			Cache::tag("address")->set("getCityFirstDistrict" . '_' . $city_id, $data);
		} else {
			$data = $cache;
		}
		
		if (!empty($data)) {
			return $data['district_id'];
		} else {
			return 0;
		}
		
	}
	
	/**
	 * 获取市列表
	 */
	public function getCityList($province_id = 0)
	{
		$cache = Cache::tag("address")->get("getCityList" . $province_id);
		if (empty($cache)) {
			$city = new City();
			if ($province_id == 0) {
				$list = $city->getQuery('', 'city_id,province_id,city_name,zipcode,sort', 'sort asc');
			} else {
				$list = $city->getQuery([
					'province_id' => $province_id
				], 'city_id,province_id,city_name,zipcode,sort', 'sort asc');
			}
			Cache::tag("address")->set("getCityList" . $province_id, $list);
			return $list;
		} else {
			return $cache;
		}
	}
	
	/***********************************************************市级结束*********************************************************/
	
	
	/***********************************************************县级开始*********************************************************/
	
	/**
	 * 添加县级地区
	 */
	public function editDistrict($data)
	{
		Cache::clear("address");
		$district = new District();
		if (!empty($data['district_id'])) {
			return $district->save($data, [
				"district_id" => $data['district_id']
			]);
		} else {
			$district->save($data);
			return $district->district_id;
		}
	}
	
	/**
	 * 删除 县
	 */
	public function deleteDistrict($district_id)
	{
		Cache::clear("address");
		$district = new District();
		return $district->destroy($district_id);
	}
	
	/**
	 * 获取区县名称
	 */
	public function getDistrictName($district_id)
	{
		$cache = Cache::tag("address")->get("getDistrictName" . $district_id);
		if (empty($cache)) {
			$district = new DistrictModel();
			
			if (!empty($district_id)) {
				$condition = array(
					'district_id' => array(
						'in',
						$district_id
					)
				);
				$list = $district->getQuery($condition, 'district_name');
			}
			
			$name = '';
			if (!empty($list)) {
				foreach ($list as $k => $v) {
					$name .= $v['district_name'] . ',';
				}
				$name = substr($name, 0, strlen($name) - 1);
			}
			Cache::tag("address")->set("getDistrictName" . $district_id, $name);
			return $name;
		} else {
			return $cache;
		}
		
	}
	
	/**
	 * 获取县id
	 */
	public function getDistrictId($district_name)
	{
		$cache = Cache::tag("address")->get("getDistrictId" . '_' . $district_name);
		if (empty($cache)) {
			$district = new DistrictModel();
			$district_id = $district->getInfo([
				'district_name' => $district_name
			], 'district_id');
			Cache::tag("address")->set("getDistrictId" . '_' . $district_name, $district_id);
			return $district_id;
		} else {
			return $cache;
		}
		
	}
	
	/**
	 * 获取区县列表
	 */
	public function getDistrictList($city_id = 0)
	{
		$cache = Cache::tag("address")->get("getDistrictList" . $city_id);
		if (empty($cache)) {
			$district = new District();
			if ($city_id == 0) {
				$list = $district->getQuery('', 'district_id,city_id,district_name,sort', 'sort asc');
			} else {
				$list = $district->getQuery([
					'city_id' => $city_id
				], 'district_id,city_id,district_name,sort', 'sort asc');
			}
			Cache::tag("address")->set("getDistrictList" . $city_id, $list);
			return $list;
		} else {
			return $cache;
		}
	}
	
	/***********************************************************县级结束*********************************************************/
	
	
	/***********************************************************区域开始*********************************************************/
	
	/**
	 * 获取区域列表
	 */
	public function getAreaList()
	{
		$cache = Cache::tag("address")->get("getAreaList");
		if (empty($cache)) {
			$area = new Area();
			$list = $area->getQuery('', 'area_id,area_name');
			Cache::tag("address")->set("getAreaList", $list);
			return $list;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 获取地区树
	 */
	public function getAreaTree($existing_address_list)
	{
		$cache = Cache::tag("address")->get("getAreaTree" . $existing_address_list);
		if (empty($cache)) {
			$area_list = $this->getAreaList();
			$list = $area_list;
			// 地区
			foreach ($area_list as $k_area => $v_area) {
				
				// 省
				$province_list = $this->getProvinceList($v_area['area_id'] == 0 ? -1 : $v_area['area_id']);
				foreach ($province_list as $key_province => $v_province) {
					
					$province_list[ $key_province ]['is_disabled'] = 0; // 是否可用，0：可用，1：不可用
					
					if (!empty($existing_address_list) && count($existing_address_list['province_id_array'])) {
						foreach ($existing_address_list['province_id_array'] as $province_id) {
							if ($province_id == $v_province['province_id']) {
								$province_list[ $key_province ]['is_disabled'] = 1;
							}
						}
					}
					
					$city_disabled_count = 0; // 市禁用的数量
					$city_list = $this->getCityList($v_province['province_id']); // 市地区的禁用条件是，区县地区都禁用了，市才禁用
					foreach ($city_list as $k_city => $city) {
						
						$city_list[ $k_city ]['is_disabled'] = 0; // 是否可用，0：可用，1：不可用
						$city_list[ $k_city ]['district_list_count'] = 0;
						
						if (!empty($existing_address_list) && count($existing_address_list['city_id_array'])) {
							
							foreach ($existing_address_list['city_id_array'] as $city_id) {
								if ($city_id == $city['city_id']) {
									$city_list[ $k_city ]['is_disabled'] = 1;
									$city_disabled_count++;
								}
							}
						}
						
						// 这个判断主要考虑到“满意包邮”功能不使用区县加的。可以提高速度
						if (!empty($existing_address_list['district_id_array'])) {
							$district_disabled_count = 0; // 区县禁用的数量
							$district_list = $this->getDistrictList($city['city_id']);
							foreach ($district_list as $k_district => $district) {
								$district_list[ $k_district ]['is_disabled'] = 0; // 是否可用，0：可用，1：不可用
								if (!empty($existing_address_list) && count($existing_address_list['district_id_array'])) {
									foreach ($existing_address_list['district_id_array'] as $district_id) {
										if ($district_id == $district['district_id']) {
											$district_list[ $k_district ]['is_disabled'] = 1;
											$district_disabled_count++;
										}
									}
								}
							}
							
							// 判断区县有没有全部禁用，有的话将父亲(省市)设置为不禁用
							if (!empty($existing_address_list['district_id_array']) && count($district_list) != $district_disabled_count && $city_list[ $k_city ]['is_disabled'] == 1) {
								$city_list[ $k_city ]['is_disabled'] = 0;
								$province_list[ $key_province ]['is_disabled'] = 0;
							}
							// $city_list[$k_city]['district_disabled_count'] = $district_disabled_count;
							$city_list[ $k_city ]['district_list'] = $district_list;
							$city_list[ $k_city ]['district_list_count'] = count($district_list);
						}
					}
					
					$province_list[ $key_province ]['city_disabled_count'] = $city_disabled_count;
					$province_list[ $key_province ]['city_list'] = $city_list;
					$province_list[ $key_province ]["city_count"] = count($city_list);
				}
				
				$list[ $k_area ]['province_list'] = $province_list;
				$list[ $k_area ]['province_list_count'] = count($province_list);
			}
			Cache::tag("address")->set("getAreaTree" . $existing_address_list, $list);
			return $list;
		} else {
			return $cache;
		}
		
	}
	
	/**
	 * 运费模板的数据整理
	 */
	public function getAreaTree_ext($existing_address_list)
	{
		$cache = Cache::tag("address")->get("getAreaTree_ext" . json_encode($existing_address_list));
		if (empty($cache)) {
			$select_district_id_array = [];
			if (!empty($existing_address_list)) {
				$select_district_id_array = $existing_address_list["district_id_array"];
			}
			//查询所有的地区信息
			$area_list = $this->getAreaList();
			//查询所有的省信息
			$province_list = $this->getProvinceList();
			//查询所有的市信息
			$city_list = $this->getCityList();
			//查询所有的区县的信息
			$district_list = $this->getDistrictList();
			
			$district_id_deal_array = [];
			//先整理所有区县的是否禁用的整理
			foreach ($district_list as $k_district => $v_district) {
				$is_disabled = 0;
				$district_id = $v_district["district_id"];
				$district_id_deal_array[ $district_id ] = $k_district;
				$is_set = in_array($district_id, $select_district_id_array);
				if ($is_set) {
					$is_disabled = 1;
				}
				$district_list[ $k_district ]["is_disabled"] = $is_disabled;
			}
			//整理市的集合
			foreach ($city_list as $k_city => $v_city) {
				$deal_array = $this->dealCityDistrictData($v_city["city_id"], $district_list, $district_id_deal_array, $existing_address_list["city_id_array"]);
				$child_district_array = $deal_array["child_district"];
				$is_disabled = $deal_array["is_disabled"];
				$city_list[ $k_city ]["district_list"] = $child_district_array;
				$city_list[ $k_city ]["is_disabled"] = $is_disabled;
				$city_list[ $k_city ]["district_list_count"] = count($child_district_array);
			}
			//整理省的集合
			foreach ($province_list as $k_province => $v_province) {
				$deal_array = $this->dealProvinceCityData($v_province["province_id"], $city_list, $existing_address_list["province_id_array"]);
				$child_city_array = $deal_array["child_city"];
				$is_disabled = $deal_array["is_disabled"];
				$province_list[ $k_province ]["city_list"] = $child_city_array;
				$province_list[ $k_province ]["is_disabled"] = $is_disabled;
				$province_list[ $k_province ]["city_count"] = count($child_city_array);
				$province_list[ $k_province ]["city_disabled_count"] = 0;
			}
			//整理地区的集合
			foreach ($area_list as $k_area => $v_area) {
				$deal_array = $this->dealAreaProvinceData($v_area["area_id"], $province_list);
				$child_province_array = $deal_array["child_province"];
				$is_disabled = $deal_array["is_disabled"];
				$area_list[ $k_area ]["province_list"] = $child_province_array;
				$area_list[ $k_area ]["is_disabled"] = $is_disabled;
				$area_list[ $k_area ]["province_list_count"] = count($child_province_array);
			}
			Cache::tag("address")->set("getAreaTree_ext" . json_encode($existing_address_list), $area_list);
			return $area_list;
		} else {
			return $cache;
		}
		
	}
	
	/**
	 * 处理市和 地区的信息
	 */
	private function dealCityDistrictData($city_id, $district_list, $district_id_deal_array, $select_city_ids)
	{
		$is_disabled = 1;
		$district_child_list = $this->getDistrictList($city_id);
		foreach ($district_child_list as $k => $district_obj) {
			$dis_id = $district_obj["district_id"];
			$k_num = $district_id_deal_array[ $dis_id ];
			$district_child_list[ $k ]["is_disabled"] = $district_list[ $k_num ]["is_disabled"];
			if ($district_list[ $k_num ]["is_disabled"] == 0) {
				$is_disabled = 0;
			}
		}
		if (empty($district_child_list)) {
			$is_set = in_array($city_id, $select_city_ids);
			if ($is_set) {
				$is_disabled = 1;
			} else {
				$is_disabled = 0;
			}
		}
		return array(
			"child_district" => $district_child_list,
			"is_disabled" => $is_disabled
		);
	}
	
	/**
	 * 处理省和市的信息
	 */
	private function dealProvinceCityData($province_id, $city_list, $province_id_array)
	{
		$city_child_array = [];
		$is_disabled = 1;
		foreach ($city_list as $city_obj) {
			if ($city_obj["province_id"] == $province_id) {
				$city_child_array[] = $city_obj;
				if ($city_obj["is_disabled"] == 0) {
					$is_disabled = 0;
				}
			}
		}
		if (empty($city_child_array)) {
			$is_set = in_array($province_id, $province_id_array);
			if ($is_set) {
				$is_disabled = 1;
			} else {
				$is_disabled = 0;
			}
		}
		return array(
			"child_city" => $city_child_array,
			"is_disabled" => $is_disabled
		);
	}
	
	/**
	 * 处理区域的信息
	 */
	private function dealAreaProvinceData($area_id, $province_list)
	{
		$province_child_array = [];
		$is_disabled = 1;
		foreach ($province_list as $province_obj) {
			if ($province_obj["area_id"] == $area_id) {
				$province_child_array[] = $province_obj;
				if ($province_obj["is_disabled"] == 0) {
					$is_disabled = 0;
				}
			}
		}
		return array(
			"child_province" => $province_child_array,
			"is_disabled" => $is_disabled
		);
	}
	
	/***********************************************************区域结束*********************************************************/
	
	
	/***********************************************************配送区域开始*********************************************************/
	
	/**
	 * 添加修改配送区域
	 */
	public function addOrUpdateDistributionArea($data)
	{
		Cache::tag("offpayarea")->set("getDistributionAreaInfo" . '_' . $data['shop_id'], null);
		$offpayArea = new NsOffpayAreaModel();
		$res = $this->getDistributionAreaInfo($data['shop_id']);
		Cache::tag("offpayarea")->set("getDistributionAreaInfo" . '_' . $data['shop_id'], null);
		if ($res == '') {
			return $offpayArea->save($data);
		} else {
			return $offpayArea->save($data, [
				'shop_id' => $data['shop_id']
			]);
		}
	}
	
	/**
	 * 获取配送地区
	 */
	public function getDistributionAreaInfo($shop_id)
	{
		$cache = Cache::tag("offpayarea")->get("getDistributionAreaInfo" . '_' . $shop_id);
		if (empty($cache)) {
			$offpayArea = new NsOffpayAreaModel();
			$res = $offpayArea->getInfo([
				'shop_id' => $shop_id
			], "province_id,city_id,district_id");
			Cache::tag("offpayarea")->set("getDistributionAreaInfo" . '_' . $shop_id, $res);
			return $res;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 检测某个地址是否可以 货到付款
	 */
	public function getDistributionAreaIsUser($shop_id, $province_id, $city_id, $district_id)
	{
		$cache = Cache::tag("offpayarea")->get("getDistributionAreaIsUser" . '_' . $shop_id . '_' . $province_id . '_' . $city_id . '_' . $district_id);
		if (empty($cache)) {
			$offpayArea = new NsOffpayAreaModel();
			$off_list = $offpayArea->where(" FIND_IN_SET(" . $province_id . ", province_id) AND FIND_IN_SET( " . $city_id . ", city_id) AND FIND_IN_SET(" . $district_id . ", district_id) ")->select();
			if (!empty($off_list) && count($off_list) > 0) {
				$is_use = true;
			} else {
				$is_use = false;
			}
			Cache::tag("offpayarea")->set("getDistributionAreaIsUser" . '_' . $shop_id . '_' . $province_id . '_' . $city_id . '_' . $district_id, $is_use);
			return $is_use;
		} else {
			return $cache;
		}
		
	}
	
	/***********************************************************配送区域结束*********************************************************/
}