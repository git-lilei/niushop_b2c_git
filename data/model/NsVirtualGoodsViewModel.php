<?php
/**
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

namespace data\model;

use data\model\BaseModel as BaseModel;

/**
 * 虚拟码商品表
 *
 */
class NsVirtualGoodsViewModel extends BaseModel
{
	protected $table = 'ns_virtual_goods';
	
	/**
	 * 获取列表返回数据格式
	 */
	public function getViewList($page_index, $page_size, $condition, $order)
	{
		
		$queryList = $this->getViewQuery($page_index, $page_size, $condition, $order);
		$queryCount = $this->getViewCount($condition);
		$list = $this->setReturnList($queryList, $queryCount, $page_size);
		return $list;
	}
	
	/**
	 * 获取列表
	 */
	public function getViewQuery($page_index, $page_size, $condition, $order)
	{
		//设置查询视图
		$viewObj = $this->alias('nvg')
			->join('sys_user su', 'nvg.buyer_id = su.uid', 'left')
			->join('ns_goods ng', 'nvg.goods_id = ng.goods_id', 'left')
			->join('ns_goods_sku ngs', 'nvg.sku_id = ngs.sku_id', 'left')
			->field('nvg.virtual_goods_id,nvg.virtual_code,nvg.virtual_goods_name,nvg.money,nvg.goods_type,nvg.buyer_id,su.nick_name,nvg.order_no,nvg.validity_period,nvg.start_time,nvg.end_time,nvg.use_number,nvg.confine_use_number,nvg.use_status,nvg.shop_id,nvg.remark,nvg.create_time,nvg.goods_id,ng.goods_name, ng.picture, ngs.sku_name');
		$list = $this->viewPageQuery($viewObj, $page_index, $page_size, $condition, $order);
		return $list;
	}
	
	/**
	 * 获取列表数量
	 */
	public function getViewCount($condition)
	{
		$viewObj = $this->alias('nvg')
			->join('sys_user su', 'nvg.buyer_id = su.uid', 'left')
			->join('ns_goods ng', 'nvg.goods_id = ng.goods_id', 'left')
			->field('nvg.virtual_goods_id');
		$count = $this->viewCount($viewObj, $condition);
		return $count;
	}
	
}