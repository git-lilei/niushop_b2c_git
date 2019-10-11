<?php
/**
 * GoodsAttribute.php
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
 * 商品属性服务层
 */
use data\model\NsAttributeModel;
use data\model\NsAttributeValueModel;
use data\model\NsGoodsAttributeModel;

class GoodsAttribute extends BaseService
{
	/**
	 * 添加 商品类型
	 */
	public function addAttribute($params)
	{
		$attribute = new NsAttributeModel();
		$attribute->startTrans();
		try {
			$attribute->save($params['data']);
			$attr_id = $attribute->attr_id;
			if (!empty($params['value_string'])) {
				$value_array = explode(';', $params['value_string']);
				foreach ($value_array as $k => $v) {
					$new_array = explode('|', $v);
					$data_value = array(
						'attr_id' => $attr_id,
						'attr_value_name' => $new_array[0],
						'type' => $new_array[1],
						'sort' => $new_array[2],
						'is_search' => $new_array[3],
						'value' => $new_array[4]
					);
					$this->addAttributeValue($data_value);
				}
			}
			$attribute->commit();
			$params['data']['attr_id'] = $attr_id;
			hook("goodsAttributeSaveSuccess", $params['data']);
			return $attr_id;
		} catch (\Exception $e) {
			$attribute->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 修改商品相关属性
	 */
	public function updateAttribute($params)
	{
		$attribute = new NsAttributeModel();
		$attribute->startTrans();
		try {
			$res = $attribute->save($params['data'], [
				'attr_id' => $params['attr_id']
			]);
			if (!empty($params['value_string'])) {
				$value_array = explode(';', $params['value_string']);
				foreach ($value_array as $k => $v) {
					$new_array = explode('|', $v);
					$data_value = array(
						'attr_id' => $params['attr_id'],
						'attr_value_name' => $new_array[0],
						'type' => $new_array[1],
						'sort' => $new_array[2],
						'is_search' => $new_array[3],
						'value' => $new_array[4]
					);
					$this->addAttributeValue($data_value);
				}
			}
			$attribute->commit();
			hook("goodsAttributeSaveSuccess", $params['data']);
			return $res;
		} catch (\Exception $e) {
			$attribute->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 添加 商品类型属性
	 */
	public function addAttributeValue($data)
	{
		$attribute_value = new NsAttributeValueModel();
		$attribute_value->save($data);
		return $attribute_value->attr_value_id;
	}
	
	/**
	 * 修改商品属性值 单个值
	 */
	public function modifyAttributeValue($attr_value_id, $field_name, $field_value)
	{
		$attribute_value = new NsAttributeValueModel();
		return $attribute_value->save([
			"$field_name" => $field_value
		], [
			'attr_value_id' => $attr_value_id
		]);
	}
	
	/**
	 * 修改商品属性排序
	 */
	public function modifyGoodsAttributeSort($attr_value_id, $sort, $shop_id)
	{
		$goods_attribute = new NsGoodsAttributeModel();
		return $goods_attribute->save([
			"sort" => $sort
		], [
			"attr_value_id" => $attr_value_id,
			"shop_id" => $shop_id
		]);
	}
	
	/**
	 * 修改 商品类型 单个字段
	 */
	public function modifyAttributeField($attr_id, $field_name, $field_value)
	{
		$attribute = new NsAttributeModel();
		return $attribute->save([
			"$field_name" => $field_value
		], [
			'attr_id' => $attr_id
		]);
	}
	
	/**
	 * 删除 商品类型
	 */
	public function deleteAttributeService($attr_id)
	{
		$attribute = new NsAttributeModel();
		$attribute_value = new NsAttributeValueModel();
		$res = $attribute->destroy($attr_id);
		$attribute_value->destroy([
			'attr_id' => $attr_id
		]);
		hook("goodsAttributeDeleteSuccess", [
			'attr_id' => $attr_id
		]);
		return $res;
	}
	
	
	/**
	 * 删除 商品类型属性
	 */
	public function deleteAttributeValue($attr_id, $attr_value_id)
	{
		$attribute_value = new NsAttributeValueModel();
		// 检测类型属性数量
		$result = $this->getGoodsAttrValueCount([
			'attr_id' => $attr_id
		]);
		if ($result == 1) {
			return -2;
		} else {
			return $attribute_value->destroy($attr_value_id);
		}
	}
	
	/**
	 * 获取属性详情
	 */
	public function getAttributeInfo($condition)
	{
		$attribute = new NsAttributeModel();
		$info = $attribute->getInfo($condition, "*");
		return $info;
	}
	
	/**
	 * 获取商品类型详情
	 */
	public function getAttributeDetail($attr_id, $condition = [])
	{
		$attribute = new NsAttributeModel();
		$info = $attribute->get($attr_id);
		if (!empty($info)) {
			$condition['attr_id'] = $attr_id;
			$array = $this->getAttributeValueList(1, 0, $condition, 'sort');
			$info['value_list'] = $array;
		}
		return $info;
	}
	
	/**
	 * 查询商品属性
	 */
	public function getGoodsAttributeQuery($condition)
	{
		$goods_attribute = new NsGoodsAttributeModel();
		$query = $goods_attribute->getQuery($condition);
		return $query;
	}
	
	/**
	 * 获取商品类型属性列表
	 */
	public function getAttributeValueList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '*')
	{
		$attribute_value = new NsAttributeValueModel();
		return $attribute_value->pageQuery($page_index, $page_size, $condition, $order, $field);
	}
	
	/**
	 * 获取 商品类型列表
	 */
	public function getAttributeList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '*')
	{
		$attribute = new NsAttributeModel();
		$attribute_value = new NsAttributeValueModel();
		$list = $attribute->pageQuery($page_index, $page_size, $condition, $order, $field);
		if (!empty($list['data'])) {
			foreach ($list['data'] as $k => $v) {
				$new_array = $attribute_value->getQuery([
					'attr_id' => $v['attr_id']
				], 'attr_value_name');
				$value_str = '';
				foreach ($new_array as $kn => $vn) {
					$value_str = $value_str . ',' . $vn['attr_value_name'];
				}
				$value_str = substr($value_str, 1);
				$list['data'][ $k ]['value_str'] = $value_str;
			}
		}
		return $list;
	}
	
	/**
	 * 获取一定条件下商品类型值的 条数
	 */
	public function getGoodsAttrValueCount($condition)
	{
		$attr_value = new NsAttributeValueModel();
		$count = $attr_value->where($condition)->count();
		return $count;
	}
	
	/**
	 * 查询商品分类下的商品属性及商品规格
	 */
	public function getGoodsAttrSpecQuery($condition)
	{
		if ($condition["attr_id"] == 0) {
			return -1;
		}
		$goods = new Goods();
		$goods_attribute = $this->getAttributeInfo($condition);
		$condition_spec["spec_id"] = array(
			"in",
			$goods_attribute['spec_id_array']
		);
		$condition_spec["is_visible"] = 1;
		$condition_spec['goods_id'] = 0; // 与商品关联的规格不进行查询
		$spec_list = $goods->getGoodsSpecQuery($condition_spec); // 商品规格
		
		$attribute_detail = $this->getAttributeDetail($condition["attr_id"], [
			'is_search' => 1
		]);
		$attribute_list = $attribute_detail['value_list']['data'];
		
		foreach ($attribute_list as $k => $v) {
			$value_items = explode(",", $v['value']);
			$attribute_list[ $k ]['value_items'] = $value_items;
		}
		
		$list["spec_list"] = $spec_list; // 商品规格集合
		$list["attribute_list"] = $attribute_list; // 商品属性集合
		return $list;
	}
}