<?php
/**
 * Supplier.php
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
use data\model\NsSupplierModel;
use data\model\NsGoodsModel;
use data\model\NsGoodsDeletedModel;
use think\Cache;

class Supplier extends BaseService
{

    /**
     * 添加供货商
     * @param unknown $uid
     * @param unknown $supplier_name
     * @param unknown $desc
     */
    public function addSupplier($uid, $supplier_name, $linkman_name, $linkman_tel, $linkman_address, $desc)
    {
        Cache::clear("niu_supplier");
        $supplier = new NsSupplierModel();
        $data = array(
            'uid' => $uid,
            'supplier_name' => $supplier_name,
            'linkman_name' => $linkman_name,
            'linkman_tel' => $linkman_tel,
            'linkman_address' => $linkman_address,
            'desc' => $desc
        );
        $supplier->save($data);
        return $supplier->supplier_id;
    }
    
    /**
     * 修改供货商
     * @param unknown $supplier_id
     * @param unknown $supplier_name
     * @param unknown $desc
     */
    public function updateSupplier($supplier_id, $supplier_name, $linkman_name, $linkman_tel, $linkman_address, $desc)
    {
        Cache::clear("niu_supplier");
        $supplier = new NsSupplierModel();
        $data = array(
            'uid' => $uid,
            'supplier_name' => $supplier_name,
            'linkman_name' => $linkman_name,
            'linkman_tel' => $linkman_tel,
            'linkman_address' => $linkman_address,
            'desc' => $desc
        );
        return $supplier->save($data, [ 'supplier_id' => $supplier_id ]);
    }
    
    /**
     * 删除供货商
     * @param unknown $supplier_id
     */
    public function deleteSupplier($supplier_id_array)
    {
        Cache::clear("niu_supplier");
        $supplier = new NsSupplierModel();
        if (strstr($supplier_id_array, ',')) {
            $new_array = explode(',', $supplier_id_array);
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
     * @param unknown $supplier_id
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
	 * @param number $page_index
	 * @param number $page_size
	 * @param string $condition
	 * @param string $order
	 * @param string $field
	 */
	public function getSupplierList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '*')
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
	 * @param unknown $supplier_id
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