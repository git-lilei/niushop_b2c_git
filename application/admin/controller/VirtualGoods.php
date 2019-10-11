<?php
/**
 * Cms.php
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


use data\service\VirtualGoods as VirtualGoodsService;

/**
 * 虚拟商品
 */
class VirtualGoods extends BaseController
{
	/**
	 * 获取虚拟商品列表
	 */
	public function virtualGoodsList()
	{
		
		if (request()->isAjax()) {
			$virtualGoods = new VirtualGoodsService();
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$search_name = request()->post('search_name', '');
			$use_status = request()->post('use_status', '');
			$virtual_code = request()->post('virtual_code', '');
			
			$condition = array();
			if ($search_name != '') {
				$condition["ng.goods_name"] = array(
					'like',
					'%' . $search_name . '%'
				);
			}
			if ($virtual_code != '') {
				$condition["nvg.virtual_code"] = $virtual_code;
			}
			if ($use_status != '') {
				$condition["nvg.use_status"] = $use_status;
			}
			$order = "nvg.virtual_goods_id desc";
			$list = $virtualGoods->getVirtualGoodsList($page_index, $page_size, $condition, $order);
			return $list;
		}
		
		$type = request()->get('type', '');
		
		return view($this->style . "VirtualGoods/virtualGoodsList");
	}
}