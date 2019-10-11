<?php

namespace addons\NsGroupBuy\admin\controller;

use addons\NsGroupBuy\data\service\GroupBuy;
use app\admin\controller\BaseController;

/**
 * 团购
 * @author lzw
 *
 */
class Promotion extends BaseController
{

	public $addon_view_path;
	
	public function __construct()
	{
		parent::__construct();
		$this->addon_view_path = ADDON_DIR . '/NsGroupBuy/template/';
	}
	/**
	 * 团购活动列表
	 */
	public function groupBuyList()
	{
	    $group_buy_service = new GroupBuy();
	    if (request()->post()) {
	        $page_index = request()->post("page_index", 1);
	        $page_size = request()->post("page_size", PAGESIZE);
	        $search_text = request()->post("search_text", '');
	        $condition['group_name'] = [
	            'like',
	            '%' . $search_text . '%',
	        ];
	        $list = $group_buy_service->getPromotionGroupBuyList($page_index, $page_size, $condition, 'group_id desc', '*');
	        return $list;
	    } else {
	        return view($this->addon_view_path . $this->style . "Promotion/groupBuyList.html");
	    }
	
	}
	
	/**
	 * 团购活动添加页面
	 */
	public function addGroupBuy()
	{
	    return view($this->addon_view_path . $this->style . "Promotion/addGroupBuy.html");
	}
	
	/**
	 * 团购活动修改页面
	 */
	public function updateGroupBuy()
	{
	    $group_id = request()->get('group_id');
	    $group_buy_service = new GroupBuy();
	    $info = $group_buy_service->getPromotionGroupBuyDetail($group_id);
	    $this->assign('info', $info);
	
	    return view($this->addon_view_path . $this->style . "Promotion/updateGroupBuy.html");
	}
	
	/**
	 * ajax添加、修改团购活动
	 */
	public function ajaxAddUpdateGroupBuy()
	{
	    $group_buy_service = new GroupBuy();
	    $group_id = request()->post('group_id', '');
	    $group_name = request()->post('group_name');
	    $goods_id = request()->post('goods_id');
	    $start_time = request()->post('start_time');
	    $end_time = request()->post('end_time');
	    $max_num = request()->post('max_num');
	    //         $min_num = request()->post('min_num');
	    $price_json = request()->post('price_json');
	    $remark = request()->post('remark');
	    $shop_id = 0;
	    $start_time = strtotime($start_time);
	    $end_time = strtotime($end_time);
	    $res = $group_buy_service->addPromotionGroupBuy($shop_id, $goods_id, $start_time, $end_time, $max_num, $group_name, $price_json, $group_id, $remark);
	    $message = "";
	    if($res["code"] <= 0){
	        $message = $res["data"];
	    }
	    return AjaxReturn($res["code"], $res, $message);
	
	}
	
	/**
	 * 删除团购活动
	 */
	public function delGroupBuy()
	{
	    $group_buy_service = new GroupBuy();
	    $group_id = request()->post('group_id');
	    $res = $group_buy_service->delPromotionGroupBuy($group_id);
	    return AjaxReturn($res);
	}
	
}