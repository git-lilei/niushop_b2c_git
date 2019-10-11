<?php
/**
 * GoodsSupplier.php
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
 * 供货商服务层
 */
use data\model\NsGoodsDeletedModel;
use data\model\NsGoodsModel;
use data\model\NsSupplierModel;
use think\Cache;

class GoodsSupplier extends BaseService
{
	
	/**
	 * 添加供货商
	 */
	public function addSupplier($data)
	{
		Cache::clear("niu_supplier");
		$supplier = new NsSupplierModel();
		$supplier->save($data);
		return $supplier->supplier_id;
	}
	
	/**
	 * 修改供货商
	 */
	public function updateSupplier($data)
	{
		Cache::clear("niu_supplier");
		$supplier = new NsSupplierModel();
		return $supplier->save($data, [ 'supplier_id' => $data['supplier_id'] ]);
	}
	
	/**
	 * 删除供货商
	 */
	public function deleteSupplier($supplier_id_array)
	{
		Cache::clear("niu_supplier");
		$supplier = new NsSupplierModel();
		if (strstr($supplier_id_array, ',')) {
			$new_array = explode(',', $supplier_id_array);
			$res = 0;
			foreach ($new_array as $k => $v) {
				if ($this->checkSupplierIsUse($v) <= 0) {
					$res += $supplier->destroy($v);
				} else {
					$res = -1;
					break;
				}
			}
		} else {
			if ($this->checkSupplierIsUse($supplier_id_array) <= 0) {
				$res = $supplier->destroy($supplier_id_array);
			} else {
				$res = -1;
			}
		}
		return $res;
	}
	
	/**
	 * 获取单条供货商详情
	 */
	public function getSupplierInfo($supplier_id)
	{
		$cache = Cache::tag("niu_supplier")->get("getSupplierInfo" . $supplier_id);
		if (!empty($cache)) return $cache;
		
		$supplier = new NsSupplierModel();
		$data = $supplier->get($supplier_id);
		Cache::tag("niu_supplier")->set("getSupplierInfo" . $supplier_id, $data);
		return $data;
	}
	
	/**
	 * 供货商列表
	 */
	public function getSupplierList($page_index = 1, $page_size = 0, $condition = '', $order = 'supplier_id desc', $field = '*')
	{
		$cache = Cache::tag('niu_supplier')->get('getSupplierList' . json_encode([ $page_index, $page_size, $condition, $order, $field ]));
		if (!empty($cache)) return $cache;
		
		$supplier = new NsSupplierModel();
		$list = $supplier->pageQuery($page_index, $page_size, $condition, $order, $field);
		Cache::tag('niu_supplier')->set('getSupplierList' . json_encode([ $page_index, $page_size, $condition, $order, $field ]), $list);
		return $list;
	}
	
	/**
	 * 判断供货商是否使用过
	 * return int  大于0 使用过   等于0 没使用过
	 */
	protected function checkSupplierIsUse($supplier_id)
	{
		$goods = new NsGoodsModel();
		$goods_deleted = new NsGoodsDeletedModel();
		$count = $goods->getCount([ 'supplier_id' => $supplier_id ]);
		$count += $goods_deleted->getCount([ 'supplier_id' => $supplier_id ]);
		return $count;
	}
	
}