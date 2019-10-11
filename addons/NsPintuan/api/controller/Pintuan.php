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

namespace addons\NsPintuan\api\controller;

use addons\NsPintuan\data\service\Pintuan as PintuanService;
use app\api\controller\BaseApi;

/**
 * 拼团订单控制器
 */
class Pintuan extends BaseApi
{
	
	/**
	 * 拼团分享
	 */
	public function sharePintuan()
	{
		$title = '拼团分享界面';
		$goods_id = isset($this->params['goods_id']) ? $this->params['goods_id'] : 0;
		$group_id = isset($this->params['group_id']) ? $this->params['group_id'] : 0;
		
		if (empty($goods_id) || empty($group_id) || $goods_id == 'undefined' || $group_id == 'undefined') {
			return $this->outMessage($title, null, '-10', "无法获取拼团信息");
		}
		$pintuan = new PintuanService();
		$pintuan_detail = $pintuan->getGroupDetailByGroupId($group_id);
		$data["tuangou_group_info"] = $pintuan_detail;
		$data['current_time'] = time() * 1000;
		
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 获取拼团商品列表
	 */
	public function goodsList()
	{
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$condition = isset($this->params['condition']) ? $this->params['condition'] : "";
		$order = isset($this->params['order']) ? $this->params['order'] : "";
		$condition = !empty($condition) ? json_decode($condition, true) : [];
		
		$pintuan = new PintuanService();
		$list = $pintuan->getTuangouGoodsList($page_index, $page_size, $condition, $order);
		return $this->outMessage("获取拼团商品列表", $list);
	}
	
	/**
	 * 拼团列表
	 */
	public function pintuanList()
	{
		$title = "拼团列表";
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$condition = isset($this->params['condition']) ? $this->params['condition'] : [];
		$order = isset($this->params['order']) ? $this->params['order'] : '';
		$pintuan = new PintuanService();
		$list = $pintuan->getGoodsPintuanStatusList($page_index, $page_size, $condition, $order);
		return $this->outMessage($title, $list);
	}
	
	/**
	 * 我的拼单
	 */
	public function pintuanOrder()
	{
		$title = "我的拼单";
		if (empty($this->uid)) {
			return $this->outMessage($title, -9999, '-9999', "无法获取会员登录信息");
		}
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$condition = isset($this->params['condition']) ? $this->params['condition'] : [];
		$order = isset($this->params['order']) ? $this->params['order'] : 'create_time desc';
		
		$condition['real_num'] = [ "gt", 0 ];
		$condition['group_uid'] = $this->uid;
		$pintuan = new PintuanService();
		$list = $pintuan->getPintuanOrderList($page_index, $page_size, $condition, $order);
		return $this->outMessage($title, $list);
	}
}