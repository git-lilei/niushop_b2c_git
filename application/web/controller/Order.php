<?php
/**
 * Order.php
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

namespace app\web\controller;

use data\service\OrderAction;
use data\service\OrderQuery;
use think\Cookie;
use addons\NsPresell\data\service\Orderpresell;
use addons\NsPresell\data\service\OrderAction as PresellOrderAction;

/**
 * 订单控制器
 */
class Order extends BaseWeb
{
	/**
	 * 订单后期支付页面
	 */
	public function orderPay()
	{
	    $order_id = request()->get('id', 0);
		$out_trade_no = request()->get('out_trade_no', 0);
		$order_action = new OrderAction();
		$order_query = new OrderQuery();
		if ($order_id != 0) {
		    $order_action->createNewOutTradeNoReturnBalance($order_id);
			$out_trade_no = $order_query->getOrderOutTradeNo($order_id);
			if (empty($out_trade_no)) {
				$this->error("支付配置有误");
			}
			$url = __URL(__URL__ . '/wap/pay/pay?out_trade_no=' . $out_trade_no);
			header("Location: " . $url);
			exit();
		} else {
			// 待结算订单处理
			if ($out_trade_no != 0) {
				$url = __URL(__URL__ . '/wap/pay/getpayvalue?out_trade_no=' . $out_trade_no);
				exit();
			} else {
				$this->error("没有获取到支付信息");
			}
		}
	}
	
	/**
	 * 订单后期预定金支付页面
	 */
	public function orderPresellPay()
	{
		$order_id = request()->get('id', 0);
		
		$order_query = new Orderpresell();
		$presell_order_info = $order_query->getOrderPresellInfo(0, [ 'relate_id' => $order_id ]);
		$presell_order_id = $presell_order_info['presell_order_id'];
		
		if ($presell_order_id != 0) {
		    $order_query->createNewOutTradeNoReturnBalancePresellOrder($presell_order_id);
		    $out_trade_no = $order_query->getPresellOrderOutTradeNo($presell_order_id);
		    if (empty($out_trade_no)) {
		        $this->error("支付配置有误");
		    }
		    $url = __URL(__URL__ . '/wap/pay/pay?out_trade_no=' . $out_trade_no);
		    header("Location: " . $url);
		    exit();
		}
	}
	
	/**
	 * 添加订单创建所需数据
	 */
	public function addOrderCreateData()
	{
		$data = request()->post("data", "");
		Cookie::set("orderCreateData", $data);
		return AjaxReturn(1);
	}
	
	/**
	 * 清除创建所需数据
	 */
	public function deleteCreateData()
	{
		$data = Cookie::get('orderCreateData');
		$data = json_decode($data, true);
		Cookie::set('orderCreateData', '');
		//立即购买
		if ($data["order_tag"] == "2") {
			//清除购物车
			$res = api("System.Goods.deleteCartByGoods", [ "goods_sku_list" => $data["goods_sku_list"] ]);
		}
	}
	
	/**
	 * 文件下载
	 */
	public function download(){
	    $virtual_code = request()->get("virtual_code", "");//文件编码
	    $res = api("System.Order.downloadVirtualGoods", [ "virtual_code" => $virtual_code ]);
	    if($res["code"] < 0){
	        $this->error($res["message"]);
	        exit();
	    }
	    
	    download($res["data"]["path"], $res["data"]["name"]);
	    
	}
}