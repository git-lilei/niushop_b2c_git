<?php
/**
 * tuangou.php
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

namespace addons\NsPresell\admin\controller;

use addons\NsPresell\data\service\OrderAction;
use app\admin\controller\BaseController;

/**
 * 预售
 */
class Orderpresell extends BaseController
{
	
	public $addon_view_path;
	
	public function __construct()
	{
		parent::__construct();
		$this->addon_view_path = ADDON_DIR . '/NsPresell/template/';
	}
	
	
	/**
	 * 预售线下支付
	 */
	public function presellOrderOffLinePay()
	{
		if (request()->isAjax()) {
			$presell_order_id = request()->post('presell_order_id', '');
			$order_action = new OrderAction();
			$res = $order_action->presellOrderOffLinePay($presell_order_id);
			return AjaxReturn($res);
		}
		
	}
	
	/**
	 * 订单备货完成
	 */
	public function orderStockingComplete()
	{
		if (request()->isAjax()) {
			$order_id = request()->post('order_id', 0);
			
			$order_action = new OrderAction();
			$result = $order_action->setOrderStockingComplete($order_id);
			return AjaxReturn($result);
		}
	}
}