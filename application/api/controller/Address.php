<?php
/**
 * Address.php
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

namespace app\api\controller;

use data\service\Address as AddressService;

class Address extends BaseApi
{
	
	/**
	 * 获取省列表
	 */
	public function province()
	{
		$address = new AddressService();
		$title = '省列表';
		$province_list = $address->getProvinceList();
		return $this->outMessage($title, $province_list);
	}
	
	/**
	 * 获取城市列表
	 *
	 */
	public function city()
	{
		$address = new AddressService();
		$title = '城市列表';
		$province_id = isset($this->params['province_id']) ? $this->params['province_id'] : 0;
		$city_list = $address->getCityList($province_id);
		return $this->outMessage($title, $city_list);
	}
	
	/**
	 * 获取区域地址
	 */
	public function district()
	{
		$address = new AddressService();
		$title = '区县列表';
		$city_id = isset($this->params['city_id']) ? $this->params['city_id'] : 0;
		$district_list = $address->getDistrictList($city_id);
		return $this->outMessage($title, $district_list);
	}
	
}