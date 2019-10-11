<?php
/**
 * GoodsBrand.php
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
 * 商品品牌服务层
 */
use data\model\NsGoodsBrandModel as NsGoodsBrand;
use think\Cache;

class GoodsBrand extends BaseService
{
	
	private $goods_brand;
	
	function __construct()
	{
		parent::__construct();
		$this->goods_brand = new NsGoodsBrand();
	}
	
	/***********************************************************商品品牌开始*********************************************************/
	
	/**
	 * 添加或修改品牌
	 */
	public function editGoodsBrand($params = [])
	{
		Cache::clear("niu_goods_brand");
		$data = array(
			'shop_id' => $params['shop_id'],
			'brand_name' => $params['brand_name'],
			'brand_initial' => $params['brand_initial'],
			'describe' => $params['describe'],
			'brand_pic' => $params['brand_pic'],
			'brand_recommend' => $params['brand_recommend'],
			'sort' => $params['sort'],
			'brand_ads' => $params['brand_ads'],
			'category_name' => $params['category_name'],
			'category_id_1' => $params['category_id_1'],
			'category_id_2' => $params['category_id_2'],
			'category_id_3' => $params['category_id_3']
		);
		if ($params['brand_id'] == "") {
			$this->goods_brand->save($data);
			$data['brand_id'] = $this->goods_brand->brand_id;
			$this->addUserLog($this->uid, 1, '商品', '添加商品品牌', '添加商品品牌:' . $params['brand_name']);
			hook("goodsBrandSaveSuccess", $data);
			return $this->goods_brand->brand_id;
		} else {
			$res = $this->goods_brand->save($data, [
				"brand_id" => $params['brand_id']
			]);
			$this->addUserLog($this->uid, 1, '商品', '修改商品品牌', '修改商品品牌:' . $params['brand_name']);
			$data['brand_id'] = $params['brand_id'];
			hook("goodsBrandSaveSuccess", $data);
			return $res;
		}
	}
	
	/**
	 * 修改品牌排序号
	 */
	public function modifyGoodsBrandSort($brand_id, $sort)
	{
		Cache::clear("niu_goods_brand");
		$data = array();
		$data['sort'] = $sort;
		$res = $this->goods_brand->save($data, [
			'brand_id' => $brand_id
		]);
		return $res;
	}
	
	/**
	 * 修改品牌推荐
	 */
	public function modifyGoodsBrandRecomend($brand_id, $brand_recommend)
	{
		Cache::clear("niu_goods_brand");
		$data = array();
		$data['brand_recommend'] = $brand_recommend;
		$res = $this->goods_brand->save($data, [
			'brand_id' => $brand_id
		]);
		return $res;
	}
	
	/**
	 * 删除品牌
	 */
	public function deleteGoodsBrand($brand_id_array)
	{
		Cache::clear("niu_goods_brand");
		$res = $this->goods_brand->destroy($brand_id_array);
		hook("goodsBrandDeleteSuccess", [
			'brand_id' => $brand_id_array
		]);
		$this->addUserLog($this->uid, 1, '商品', '删除商品品牌', '删除商品品牌');
		return $res;
	}
	
	/**
	 * 根据id获取商品品牌信息
	 */
	public function getGoodsBrandInfo($brand_id, $field = ' * ')
	{
		$cache = Cache::tag("niu_goods_brand")->get("getGoodsBrandInfo" . $brand_id . '_' . $field);
		if (empty($cache)) {
			$info = $this->goods_brand->getInfo(array(
				'brand_id' => $brand_id
			), $field);
			Cache::tag("niu_goods_brand")->set("getGoodsBrandInfo" . $brand_id . '_' . $field, $info);
			return $info;
		} else {
			return $cache;
		}
		
	}
	
	/**
	 * 根据品牌名字获取商品品牌信息
	 */
	public function getGoodsBrandName($brand_name, $field = ' * ')
	{
		$cache = Cache::tag("niu_goods_brand")->get("getGoodsBrandName" . $brand_name . '_' . $field);
		if (empty($cache)) {
			$info = $this->goods_brand->getInfo(array(
				'brand_name' => $brand_name
			), $field);
			Cache::tag("niu_goods_brand")->set("getGoodsBrandName" . $brand_name . '_' . $field, $info);
			return $info;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 获取商品品牌列表
	 */
	public function getGoodsBrandList($page_index = 1, $page_size = 0, $condition = '', $order = 'brand_initial asc', $field = '*')
	{
		$data = array( $page_index, $page_size, $condition, $order, $field );
		$data = json_encode($data);
		$cache = Cache::tag("niu_goods_brand")->get("getGoodsBrandList" . $data);
		if (empty($cache)) {
			$list = $this->goods_brand->pageQuery($page_index, $page_size, $condition, $order, $field);
			Cache::tag("niu_goods_brand")->set("getGoodsBrandList" . $data, $list);
			return $list;
		} else {
			return $cache;
		}
		
	}
	
	/***********************************************************商品品牌结束*********************************************************/
}