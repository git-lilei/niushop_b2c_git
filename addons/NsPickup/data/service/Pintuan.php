<?php
/**
 * Pintuan.php
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

namespace addons\NsPintuan\data\service;

use addons\NsPintuan\data\model\NsPromotionTuangouModel;
use addons\NsPintuan\data\model\NsTuangouGroupModel;
use addons\NsPintuan\data\model\NsTuangouTypeModel;
use data\model\AlbumPictureModel;
use data\model\NsGoodsModel;
use data\model\NsGoodsPromotionModel;
use data\model\NsGoodsViewModel;
use data\model\NsOrderGoodsModel;
use data\model\NsOrderModel;
use data\model\NsOrderPromotionDetailsModel;
use data\model\UserModel;
use data\service\Goods;
use data\service\Member\MemberAccount;
use data\service\OrderQuery;
use data\service\OrderRefund;
use think\Cache;

/**
 * 拼团订单
 */
class Pintuan extends \data\service\Order\Order
{
	
	/**
	 * 获取拼团列表
	 */
	public function getGooodsPintuanList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$goods_view = new NsGoodsViewModel();
		$list = $goods_view->getGoodsViewList($page_index, $page_size, $condition, $order);
		if (!empty($list['data'])) {
			// 用户针对商品的收藏
			foreach ($list['data'] as $k => $v) {
				$goods_info = $this->getGoodsPintuanDetail($v['goods_id']);
				$list['data'][ $k ]['tuangou_money'] = $goods_info["tuangou_money"];
				$list['data'][ $k ]['tuangou_num'] = $goods_info["tuangou_num"];
				$list['data'][ $k ]['tuangou_time'] = $goods_info["tuangou_time"];
				$list['data'][ $k ]['tuangou_type'] = $goods_info["tuangou_type"];
				$list['data'][ $k ]['tuangou_content_json'] = $goods_info["tuangou_content_json"];
				$list['data'][ $k ]['is_open'] = $goods_info["is_open"];
				$list['data'][ $k ]['tuangou_type_name'] = $goods_info["tuangou_type_info"]['type_name'];
			}
		}
		return $list;
	}
	
	/**
	 * 获取商品拼团详情
	 */
	public function getGoodsPintuanDetail($goods_id)
	{
		$promotion_tuangou = new NsPromotionTuangouModel();
		$tuangou_info = $promotion_tuangou->getInfo([
			'goods_id' => $goods_id
		], 'tuangou_id,goods_id,tuangou_money,tuangou_num,tuangou_time,tuangou_type,tuangou_content_json,is_open,is_show	');
		
		if (!empty($tuangou_info)) {
			$tuangou_info["tuangou_type_info"] = $this->getPintuanType($tuangou_info["tuangou_type"]);
		}
		return $tuangou_info;
	}
	
	/**
	 * 获取团购的全部类型
	 */
	public function getTuangouType()
	{
		$cache = Cache::tag('pintuan')->get('getTuangouType');
		if (!empty($cache)) return $cache;
		
		$tuangou_type_model = new NsTuangouTypeModel();
		$res = $tuangou_type_model->getQuery('', '*', '');
		Cache::tag('pintuan')->set('getTuangouType', $res);
		return $res;
	}
	
	/**
	 * 获取拼团类型
	 */
	public function getPintuanType($type_id)
	{
		$cache = Cache::tag('pintuan')->get('getPintuanType' . $type_id);
		if (!empty($cache)) return $cache;
		
		$tuangou_type = new NsTuangouTypeModel();
		$type_info = $tuangou_type->getInfo([
			'type_id' => $type_id
		], '*');
		Cache::tag('pintuan')->get('getPintuanType' . $type_id, $type_info);
		return $type_info;
	}
	
	/**
	 * 修改或添加商品团购
	 */
	public function addUpdateGoodsPintuan($tuangou_id, $goods_id, $is_open, $is_show, $tuangou_money, $tuangou_num, $tuangou_time, $tuangou_type, $tuangou_content_json, $remark)
	{
		Cache::clear('pintuan');
		$tuangou = new NsPromotionTuangouModel();
		$data = [
			'goods_id' => $goods_id,
			'is_open' => $is_open,
			'is_show' => $is_show,
			'tuangou_money' => $tuangou_money,
			'tuangou_num' => $tuangou_num,
			'tuangou_time' => $tuangou_time,
			'tuangou_type' => $tuangou_type,
			
			// 'colonel_commission'=>$colonel_commission,
			// 'colonel_coupon'=>$colonel_coupon,
			// 'colonel_point'=>$colonel_point,
			'tuangou_content_json' => $tuangou_content_json,
			'remark' => $remark
		];
		
		if (empty($tuangou_id)) {
			$data['create_time'] = time();
			$tuangou_id = $tuangou->save($data);
			$res = $tuangou_id;
			
		} else {
			$data['modify_time'] = time();
			$res = $tuangou->save($data, [
				'tuangou_id' => $tuangou_id
			]);
			
		}
		$goods_promotion_model = new NsGoodsPromotionModel();
		$goods_promotion_model->destroy([ 'goods_id' => $goods_id, 'promotion_addon' => 'NsPintuan' ]);
		$data_goods_promotion = [
			'goods_id' => $goods_id,
			'label' => '拼',
			'remark' => '',
			'status' => $is_open,
			'is_all' => 0,
			'promotion_addon' => 'NsPintuan',
			'promotion_id' => $goods_id,
			'is_goods_promotion' => 1,
			'start_time' => time(),
			'end_time' => 0
		];
		$goods_promotion_model->save($data_goods_promotion);
		return $res;
	}
	
	/**
	 * 开关拼团
	 */
	public function modifyGoodsTuangou($goods_id, $is_open)
	{
		Cache::clear('pintuan');
		$data = [
			'is_open' => $is_open
		];
		
		$tuangou = new NsPromotionTuangouModel();
		$goods_promotion_model = new NsGoodsPromotionModel();
		$goods_promotion_model->save([ 'status' => $is_open ], [ 'goods_id' => $goods_id, 'promotion_addon' => 'NsPintuan' ]);
		$tuangou_info = $tuangou->getInfo([ 'goods_id' => $goods_id ]);
		if ($tuangou_info) {
			$res = $tuangou->save($data, [
				'goods_id' => $goods_id
			]);
		} else {
			$res = -2;
		}
		
		return $res;
	}
	
	/**
	 * 获取拼团列表
	 */
	public function getGoodsPintuanStatusList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '')
	{
		$tuangou_group = new NsTuangouGroupModel();
		$list = $tuangou_group->pageQuery($page_index, $page_size, $condition, $order, $field);
		foreach ($list["data"] as $k => $v) {
			// 剩余团购人数
			$list["data"][ $k ]["poor_num"] = $v["tuangou_num"] - $v["real_num"];
			$list["data"][ $k ]["remaining_time"] = $v["end_time"] - $v["create_time"];
		}
		return $list;
	}
	
	/**
	 * 获取拼团组合详情
	 */
	public function getGoodsGroupDetail($goods_id, $status)
	{
		$tuangou_group = new NsTuangouGroupModel();
		if (empty($status)) {
			$group_info = $tuangou_group->getInfo([
				'goods_id' => $goods_id
			]);
		} else {
			$group_info = $tuangou_group->getInfo([
				'goods_id' => $goods_id,
				'status' => $status
			]);
		}
		
		if (!empty($group_info)) {
			$group_info["tuangou_type_info"] = $this->getPintuanType($group_info["tuangou_type"]);
		}
		return $group_info;
	}
	
	/**
	 * 获取拼团组合订单数据
	 */
	public function getTuangouGroupOrder($tuangou_group_id)
	{
		$order = new NsOrderModel();
		$order_info = $order->getInfo([
			'tuangou_group_id' => $tuangou_group_id
		], '');
		return $order_info;
	}
	
	/**
	 * 团购商品是否首页显示
	 */
	public function modifyTuangouGroupRecommend($group_id, $is_recommend)
	{
		Cache::clear('pintuan');
		$data = [
			'is_recommend' => $is_recommend
		];
		$tuangou = new NsTuangouGroupModel();
		$res = $tuangou->save($data, [
			'group_id' => $group_id
		]);
		return $res;
	}
	
	
	/**
	 * 创建拼团
	 */
	public function tuangouGroupCreate($data)
	{
		$tuangou_group = new NsTuangouGroupModel();
		
		$goods_sku_info = $data["goods_sku_array"][0]["goods_sku_info"];
		$goods_info = $data["goods_sku_array"][0]["goods_info"];
		
		// 商品拼团设置
		$promotion_tuangou = new NsPromotionTuangouModel();
		$promotion_tuangou_info = $promotion_tuangou->getinfo([
			"goods_id" => $goods_sku_info["goods_id"]
		], "*");
		//拼团类型
		$tuangou_type = new NsTuangouTypeModel();
		$tuangou_type_info = $tuangou_type->getinfo([
			"type_id" => $promotion_tuangou_info["tuangou_type"]
		], "type_name");
		
		$now_time = time();
		$data = array(
			"group_uid" => $data["buyer_id"],
			"group_name" => $data["user_info"]["nick_name"],
			"user_tel" => $data["address"]["mobile"],
			"goods_id" => $goods_sku_info["goods_id"],
			"goods_name" => $goods_info["goods_name"],
			"tuangou_money" => $promotion_tuangou_info["tuangou_money"],
			"tuangou_type" => $promotion_tuangou_info["tuangou_type"],
			"tuangou_type_name" => $tuangou_type_info["type_name"],
			"price" => 0,
			"tuangou_num" => $promotion_tuangou_info["tuangou_num"],
			"real_num" => 0,
			"create_time" => $now_time,
			"end_time" => $now_time + $promotion_tuangou_info["tuangou_time"] * 3600,
			"status" => 0,
			"is_recommend" => 0,
			"group_user_head_img" => $data["user_info"]["user_headimg"]
		);
		$retval = $tuangou_group->save($data);
		if ($retval > 0) {
			return $tuangou_group->group_id;
		} else {
			return 0;
		}
	}
	
	/**
	 * 团购增加拼团人数
	 */
	public function tuangouGroupModify($tuangou_group_id)
	{
		$tuangou_group = new NsTuangouGroupModel();
		$tuangou_group->startTrans();
		try {
			$tuangou_group = new NsTuangouGroupModel();
			$tuangou_group_info = $tuangou_group->getInfo([
				"group_id" => $tuangou_group_id
			], "tuangou_num, create_time, end_time, status, real_num, goods_id, group_uid");
			if (empty($tuangou_group_info)) {
				return 0;
			}
			
			if ($tuangou_group_info["tuangou_num"] <= $tuangou_group_info["real_num"]) {
				return 0;
			}
			if ($tuangou_group_info["status"] != 1) {
				return 0;
			}
			$time = time();
			if ($tuangou_group_info["create_time"] > $time || $tuangou_group_info["end_time"] < $time) {
				return 0;
			}
			
			$now_num = $tuangou_group_info["real_num"] + 1;
			$data = array(
				"real_num" => $now_num
			);
			if ($now_num == $tuangou_group_info["tuangou_num"]) {
				$data["status"] = 2;
			}
			$tuangou_group->save($data, [
				"group_id" => $tuangou_group_id
			]);
			// 如果拼团已完成,订单状态变为待发货状态
			
			if ($data["status"] == 2) {
				
				$order = new NsOrderModel();
				$order_data = array(
					"order_status" => 1
				);
				
				$res = $order->save($order_data, [
					"tuangou_group_id" => $tuangou_group_id,
					"order_status" => 6
				]);
				
				// 给团长发送佣金 积分 优惠券
				$goods_pintuan = new NsPromotionTuangouModel();
				$goods_pintuan_info = $goods_pintuan->getInfo([
					"goods_id" => $tuangou_group_info["goods_id"]
				], "tuangou_content_json");
				if (!empty($goods_pintuan_info["tuangou_content_json"])) {
					$tuangou_content_array = json_decode($goods_pintuan_info["tuangou_content_json"], true);
					$member_account = new MemberAccount();
					if ($tuangou_content_array["colonel_point"] > 0) {
						$res = $member_account->addMemberAccountData(0, 1, $tuangou_group_info["group_uid"], 1, $tuangou_content_array["colonel_point"], 21, $tuangou_group_id, "团长拼团成功后赠送积分");
					}
					if ($tuangou_content_array["colonel_commission"] > 0) {
						$member_account->addMemberAccountData(0, 2, $tuangou_group_info["group_uid"], 1, $tuangou_content_array["colonel_commission"], 22, $tuangou_group_id, "团长拼团成功后赠送余额");
					}
				}
				
				//虚拟商品
				$goods_service = new  Goods();
				$order_list = $order->getQuery([ "tuangou_group_id" => $tuangou_group_id, "order_status" => 1 ], "order_id, order_no, order_status, buyer_id, is_virtual");
				foreach ($order_list as $list_k => $list_v) {
					if ($list_v["is_virtual"] == 1) {
						$user_model = new UserModel();
						$user_info = $user_model->getInfo([ "uid" => $list_v["buyer_id"] ], "nick_name");
						$goods_service->virtualOrderAction($list_v["order_id"], $user_info["nick_name"], $list_v["order_no"]);
					}
				}
				
				$tuangou_group->commit();
				// 调用短信邮箱通知钩子 拼团成功通知用户
				runhook("Notify", "groupBookingSuccessOrFailUser", [
					'pintuan_group_id' => $tuangou_group_id,
					'type' => 'success'
				]);
				// 拼团成功通知商户
				runhook("Notify", "groupBookingSuccessBusiness", [
					'pintuan_group_id' => $tuangou_group_id
				]);
				// 拼团成功微信模板消息
				hook('groupBookingSuccessOrFail', [
					'pintuan_group_id' => $tuangou_group_id,
					'type' => 'success'
				]);
				return 2;
			}
			$tuangou_group->commit();
			return 1;
		} catch (\Exception $e) {
			dump($e->getMessage());
			$tuangou_group->rollback();
			return 0;
		}
	}
	
	/**
	 * 拼团关闭
	 */
	public function tuangouGroupClose($tuangou_group_id)
	{
		$tuangou_group = new NsTuangouGroupModel();
		$data = array(
			"status" => -1
		);
		$retval = $tuangou_group->save($data, [
			"group_id" => $tuangou_group_id
		]);
		return $retval;
	}
	
	/**
	 * 拼团完成改为可发货
	 */
	public function pintuanGroupComplete($tuangou_group_id)
	{
		$order = new NsOrderModel();
		$order->startTrans();
		try {
			$tuangou_group = new NsTuangouGroupModel();
			$tuangou_group_count = $tuangou_group->getCount([
				"status" => -1,
				"group_id" => $tuangou_group_id
			]);
			if (!$tuangou_group_count > 0) {
				return 0;
			}
			// 改变订单为待发货状态
			$order_condition = array(
				"tuangou_group_id" => $tuangou_group_id,
				"order_status" => 6
			);
			$order_data = array(
				"order_status" => 1
			);
			$order->save($order_data, $order_condition);
			// 改变拼团状态
			$data = array(
				"status" => 2
			);
			$tuangou_group->save($data, [
				"group_id" => $tuangou_group_id
			]);
			
			// 调用短信邮箱通知钩子 拼团成功通知用户
			runhook("Notify", "groupBookingSuccessOrFailUser", [
				'pintuan_group_id' => $tuangou_group_id,
				'type' => 'success'
			]);
			// 拼团成功通知商户
			runhook("Notify", "groupBookingSuccessBusiness", [
				'pintuan_group_id' => $tuangou_group_id
			]);
			// 拼团成功微信模板消息
			hook('groupBookingSuccessOrFail', [
				'pintuan_group_id' => $tuangou_group_id,
				'type' => 'success'
			]);
			$order->commit();
			return 1;
		} catch (\Exception $e) {
			$order->rollback();
			return 0;
		}
	}
	
	/**
	 * 拼团关闭后退款
	 */
	public function tuangouGroupRefund($tuangou_group_id)
	{
		$order = new NsOrderModel();
		$order->startTrans();
		try {
			// 循环给订单退款
			$order_list = $order->getQuery([
				"tuangou_group_id" => $tuangou_group_id,
				"order_status" => 6
			], "*", '');
			$order_refund = new OrderRefund();
			$order_action = new \data\service\OrderAction();
			foreach ($order_list as $k => $v) {
				$order_goods = new NsOrderGoodsModel();
				$order_goods_list = $order_goods->getQuery([
					"order_id" => $v["order_id"]
				], "*", '');
				foreach ($order_goods_list as $t => $m) {
					$order_refund->orderGoodsConfirmRefund($v["order_id"], $m["order_goods_id"], $v["pay_money"], $v["user_platform_money"], $v["payment_type"], '拼团失败后退款');
				}
				// 关闭订单
				$order_action->orderClose($v["order_id"]);
			}
			// 订单关闭之后,拼团状态变为
			$tuangou_group = new NsTuangouGroupModel();
			$tuangou_group_count = $tuangou_group->getCount([ "status" => -1, "group_id" => $tuangou_group_id ]);
			if (!$tuangou_group_count > 0) {
				$order->rollback();
				return 0;
			}
			// 已退款
			$tuangou_group_data = array(
				"status" => -2
			);
			$tuangou_group->save($tuangou_group_data, [ "group_id" => $tuangou_group_id ]);
			
			// 调用短信邮箱通知钩子 拼团失败
			runhook("Notify", "groupBookingSuccessOrFailUser", [
				'pintuan_group_id' => $tuangou_group_id,
				'type' => 'fail'
			]);
			// 拼团失败微信模板消息
			hook('groupBookingSuccessOrFail', [
				'pintuan_group_id' => $tuangou_group_id,
				'type' => 'fail'
			]);
			$order->commit();
			return 1;
		} catch (\Exception $e) {
			$order->rollback();
			return 0;
		}
	}
	
	public function getOrderDetail($order_id)
	{
		// 查询主表信息
		$order_query = new OrderQuery();
		$detail = $order_query->getDetail($order_id);
		if (empty($detail)) {
			return array();
		}
		$detail['pay_status_name'] = $this->getPayStatusInfo($detail['pay_status'])['status_name'];
		$detail['shipping_status_name'] = $this->getShippingInfo($detail['shipping_status'])['status_name'];
		
		$express_list = $this->getOrderGoodsExpressList($order_id);
		// 未发货的订单项
		$order_goods_list = array();
		// 已发货的订单项
		$order_goods_delive = array();
		// 没有配送信息的订单项
		$order_goods_exprss = array();
		foreach ($detail["order_goods"] as $order_goods_obj) {
			$shipping_status = $order_goods_obj["shipping_status"];
			if ($shipping_status == 0) {
				// 未发货
				$order_goods_list[] = $order_goods_obj;
			} else {
				$order_goods_delive[] = $order_goods_obj;
			}
		}
		$detail["order_goods_no_delive"] = $order_goods_list;
		// 没有配送信息的订单项
		if (!empty($order_goods_delive) && count($order_goods_delive) > 0) {
			foreach ($order_goods_delive as $goods_obj) {
				$is_have = false;
				$order_goods_id = $goods_obj["order_goods_id"];
				foreach ($express_list as $express_obj) {
					$order_goods_id_array = $express_obj["order_goods_id_array"];
					$goods_id_str = explode(",", $order_goods_id_array);
					if (in_array($order_goods_id, $goods_id_str)) {
						$is_have = true;
					}
				}
				if (!$is_have) {
					$order_goods_exprss[] = $goods_obj;
				}
			}
		}
		$goods_packet_list = array();
		if (count($order_goods_exprss) > 0) {
			$packet_obj = array(
				"packet_name" => "无需物流",
				"express_name" => "",
				"express_code" => "",
				"express_id" => 0,
				"is_express" => 0,
				"order_goods_list" => $order_goods_exprss
			);
			$goods_packet_list[] = $packet_obj;
		}
		if (!empty($express_list) && count($express_list) > 0 && count($order_goods_delive) > 0) {
			$packet_num = 1;
			foreach ($express_list as $express_obj) {
				$packet_goods_list = array();
				$order_goods_id_array = $express_obj["order_goods_id_array"];
				$goods_id_str = explode(",", $order_goods_id_array);
				foreach ($order_goods_delive as $delive_obj) {
					$order_goods_id = $delive_obj["order_goods_id"];
					if (in_array($order_goods_id, $goods_id_str)) {
						$packet_goods_list[] = $delive_obj;
					}
				}
				$packet_obj = array(
					"packet_name" => "包裹  + " . $packet_num,
					"express_name" => $express_obj["express_name"],
					"express_code" => $express_obj["express_no"],
					"express_id" => $express_obj["id"],
					"is_express" => 1,
					"order_goods_list" => $packet_goods_list
				);
				$packet_num = $packet_num + 1;
				$goods_packet_list[] = $packet_obj;
			}
		}
		$detail["goods_packet_list"] = $goods_packet_list;
		$virtual_goods = new Goods();
		$virtual_goods_list = $virtual_goods->getVirtualGoodsListByOrderNo($detail['order_no']);
		$detail['virtual_goods_list'] = $virtual_goods_list;
		// 订单优惠类型
		$ns_order_promotion = new NsOrderPromotionDetailsModel();
		$promotion_detail = $ns_order_promotion->getInfo([
			"order_id" => $order_id
		], "promotion_type");
		$detail['promotion_type'] = $promotion_detail['promotion_type'];
		
		// 关联拼团信息
		$tuangou_group = new NsTuangouGroupModel();
		$goods_pintuan = new NsPromotionTuangouModel();
		$tuangou_group_info = $tuangou_group->getInfo([
			"group_id" => $detail["tuangou_group_id"]
		], "*");
		if (!empty($tuangou_group_info)) {
			$surplus_num = $tuangou_group_info["tuangou_num"] - $tuangou_group_info["real_num"];
			$tuangou_group_info["poor_num"] = $surplus_num;
			$order = new NsOrderModel();
			$user = new UserModel();
			$order_list = $order->getQuery([
				"tuangou_group_id" => $detail["tuangou_group_id"]
			], "buyer_id", '');
			$user_list = array();
			foreach ($order_list as $k => $v) {
				$user_info = $user->getInfo([
					"uid" => $v["buyer_id"]
				], "user_headimg, nick_name");
				$user_list[ $k ]["user_name"] = $user_info["nick_name"];
				$user_list[ $k ]["user_headimg"] = $user_info["user_headimg"];
				$user_list[ $k ]["uid"] = $v["buyer_id"];
			}
			$tuangou_group_info["user_list"] = $user_list;
			// 商品拼单设置
			$goods_pintuan_info = $goods_pintuan->getInfo([
				"goods_id" => $tuangou_group_info["goods_id"]
			], "tuangou_content_json");
			if (!empty($goods_pintuan_info["tuangou_content_json"])) {
				$tuangou_content_array = json_decode($goods_pintuan_info["tuangou_content_json"], true);
				$tuangou_group_info["goods_tuangou"] = $tuangou_content_array;
			}
		}
		$detail["tuangou_group_info"] = $tuangou_group_info;
		return $detail;
	}
	
	public function getPintuanOrderList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$goods = new NsGoodsModel();
		$order_model = new NsOrderModel();
		$tuangou_group = new NsTuangouGroupModel();
		$list = $tuangou_group->pageQuery($page_index, $page_size, $condition, $order, "*");
		foreach ($list["data"] as $k => $v) {
			// 剩余团购人数
			$order_info = $order_model->getFirstData([
				"tuangou_group_id" => $v["group_id"],
				"buyer_id" => $v["group_uid"]
			], "pay_time desc");
			if (empty($order_info)) {
				$order_id = 0;
			} else {
				$order_id = $order_info["order_id"];
			}
			$goods_info = $goods->getInfo([
				"goods_id" => $v["goods_id"]
			], "picture");
			$picture = new AlbumPictureModel();
			$picture = $picture->get($goods_info['picture']);
			if (empty($picture)) {
				$picture = array(
					'pic_cover' => '',
					'pic_cover_big' => '',
					'pic_cover_mid' => '',
					'pic_cover_small' => '',
					'pic_cover_micro' => '',
					"upload_type" => 1,
					"domain" => ""
				);
			}
			$list["data"][ $k ]["picture_info"] = $picture;
			$list["data"][ $k ]["order_id"] = $order_id;
		}
		return $list;
	}
	
	/**
	 * 查询商品列表
	 */
	public function getTuangouGoodsList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$goods_view = new NsGoodsViewModel();
		$list = $goods_view->getPintuanGoodsViewList($page_index, $page_size, $condition, $order);
		return $list;
	}
	
	/**
	 * 获取拼团详情
	 */
	public function getTuangouDetail($group_id, $goods_id)
	{
		$tuangou_group = new NsTuangouGroupModel();
		$tuangou_group_info = $tuangou_group->getInfo([
			"group_id" => $group_id,
			'goods_id' => $goods_id,
			'status' => 1
		], "*");
		return $tuangou_group_info;
	}
	
	/**
	 * 查询拼团是否真实存在
	 */
	public function getTuangouGroupCount($group_id, $goods_id)
	{
		$tuangou_group = new NsTuangouGroupModel();
		$tuangou_group_count = $tuangou_group->getCount([
			"group_id" => $group_id,
			'goods_id' => $goods_id,
			'status' => 1
		]);
		return $tuangou_group_count;
	}
	
	/**
	 * 根据group_id 获取拼团详情
	 */
	public function getGroupDetailByGroupId($group_id)
	{
		$tuangou_group = new NsTuangouGroupModel();
		$goods_pintuan = new NsPromotionTuangouModel();
		$tuangou_group_info = $tuangou_group->getInfo([
			"group_id" => $group_id
		], "*");
		if (!empty($tuangou_group_info)) {
			$surplus_num = $tuangou_group_info["tuangou_num"] - $tuangou_group_info["real_num"];
			$tuangou_group_info["poor_num"] = $surplus_num;
			$order = new NsOrderModel();
			$user = new UserModel();
			$order_list = $order->getQuery([
				"tuangou_group_id" => $group_id
			], "buyer_id", '');
			$user_list = array();
			foreach ($order_list as $k => $v) {
				$user_info = $user->getInfo([
					"uid" => $v["buyer_id"]
				], "user_headimg, nick_name");
				$user_list[ $k ]["user_name"] = $user_info["nick_name"];
				$user_list[ $k ]["user_headimg"] = $user_info["user_headimg"];
				$user_list[ $k ]["uid"] = $v["buyer_id"];
			}
			$tuangou_group_info["user_list"] = $user_list;
			// 商品拼单设置
			$goods_pintuan_info = $goods_pintuan->getInfo([
				"goods_id" => $tuangou_group_info["goods_id"]
			], "tuangou_content_json");
			if (!empty($goods_pintuan_info["tuangou_content_json"])) {
				$tuangou_content_array = json_decode($goods_pintuan_info["tuangou_content_json"], true);
				$tuangou_group_info["goods_tuangou"] = $tuangou_content_array;
			}
		}
		return $tuangou_group_info;
	}
	
	public function orderPayBefore($out_trade_no)
	{
		$order_model = new NsOrderModel();
		$order_info = $order_model->getInfo([ 'out_trade_no' => $out_trade_no ], "tuangou_group_id");
		if ($order_info['tuangou_group_id'] > 0) {
			$tuangou_group = new NsTuangouGroupModel();
			$pingtuan_info = $tuangou_group->getInfo([ 'group_id' => $order_info['tuangou_group_id'] ], "tuangou_num, status");
			
			$condition_1['order_status'] = [ "in", "1,2,3,4" ];
			$condition_1['tuangou_group_id'] = $order_info['tuangou_group_id'];
			
			$order_list_count = $order_model->getCount($condition_1);
			
			if ($pingtuan_info['tuangou_num'] <= $order_list_count || !in_array($pingtuan_info['status'], [ 0, 1 ])) {
				return 0;
			} else {
				return 1;
			}
		} else {
			return 1;
		}
		
	}
	
	/**
	 * 获取拼团数
	 */
	public function getPintuanCount($condition)
	{
		$tuangou_group = new NsTuangouGroupModel();
		$count = $tuangou_group->getCount($condition);
		return $count;
	}
	
	/**
	 * 拼团过期自动关闭
	 */
	public function pintuanGroupClose()
	{
		// 拼团过期时关闭拼团订单
		$pintuan_group = new NsTuangouGroupModel();
		$pintuan_group->startTrans();
		try {
			$condition['end_time'] = array(
				'LT',
				time()
			);
			$condition['status'] = array(
				'EQ',
				1
			);
			$count = $pintuan_group->getCount($condition);
			if ($count) {
				$pintuan_group->save([ 'status' => -1 ], $condition);
			}
			$pintuan_group->commit();
			Cache::clear('pintuan');
			return 1;
		} catch (\Exception $e) {
			$pintuan_group->rollback();
			return $e->getMessage();
		}
	}
	
}