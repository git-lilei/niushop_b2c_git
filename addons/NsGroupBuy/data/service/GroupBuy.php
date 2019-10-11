<?php
/**
 * Shop.php
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

namespace addons\NsGroupBuy\data\service;

/**
 * 团购服务层
 */
use addons\NsGroupBuy\data\model\NsPromotionGroupBuyLadderModel;
use addons\NsGroupBuy\data\model\NsPromotionGroupBuyModel;
use data\model\AlbumPictureModel;
use data\model\NsGoodsModel;
use data\model\NsGoodsPromotionModel;
use data\service\BaseService;
use data\service\Member;
use data\service\Promotion;
use think\Log;

class GroupBuy extends BaseService
{
	/**
	 * 团购列表
	 */
	public function getPromotionGroupBuyList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '*')
	{
		$promotion_group_buy = new NsPromotionGroupBuyModel();
		$list = $promotion_group_buy->pageQuery($page_index, $page_size, $condition, $order, '*');
		$goods_model = new NsGoodsModel();
		foreach ($list['data'] as $key => $val) {
			$goods_info = $goods_model->getInfo([ 'goods_id' => $val['goods_id'] ], 'goods_name');
			$list['data'][ $key ]["goods_name"] = $goods_info['goods_name'];
		}
		return $list;
	}
	
	/**
	 * 获取团购活动详情
	 */
	function getPromotionGroupBuyDetail($group_id)
	{
		$promotion_group_buy = new NsPromotionGroupBuyModel();
		$promotion_group_buy_ladder = new NsPromotionGroupBuyLadderModel();
		$info = $promotion_group_buy->getInfo([ 'group_id' => $group_id ], '*');
		$price_list = $promotion_group_buy_ladder->getQuery([ 'group_id' => $group_id ], '*', 'id asc');
		$info['price_list'] = $price_list;
		return $info;
	}
	
	/**
	 * 添加 修改团购
	 */
	public function addPromotionGroupBuy($shop_id, $goods_id, $start_time, $end_time, $max_num, $group_name, $price_json, $group_id = 0, $remark)
	{
		//添加活动
		$promotion_group_buy = new NsPromotionGroupBuyModel();
		$promotion_group_buy->startTrans();
		try {
			$min_num = 0;
			$price_array = json_decode($price_json, true);
			if (empty($price_array)) {
				$promotion_group_buy->rollback();
				return 0;
			}
			foreach ($price_array as $t => $m) {
				if ($min_num == 0 || $min_num >= $m["0"]) {
					$min_num = $m["0"];
				}
			}
			$data = array(
				"shop_id" => $shop_id,
				"start_time" => $start_time,
				"goods_id" => $goods_id,
				"end_time" => $end_time,
				"max_num" => $max_num,
				"min_num" => $min_num,
				"group_name" => $group_name,
				'remark' => $remark,
			);
			if ($group_id > 0) {
				$data["modify_time"] = time();
				$promotion_group_buy->save($data, [ "group_id" => $group_id ]);
			} else {
				$data["create_time"] = time();
				$promotion_group_buy->save($data);
				$group_id = $promotion_group_buy->group_id;
			}
			
			$goods_promotion_model = new NsGoodsPromotionModel();
			$goods_promotion_model->destroy([ 'promotion_id' => $group_id, 'promotion_addon' => 'NsGroupBuy' ]);
			
			//判断是否存在其他单品活动
			$promotion_service = new Promotion();
			$result = $promotion_service->getGoodsIdsPromotionIsExist($goods_id, $start_time, $end_time);//判断所选商品是否存在单品活动
			if ($result["code"] <= 0) {
				$promotion_group_buy->rollback();
				return $result;
			}
			
			$data_goods_promotion = [
				'goods_id' => $goods_id,
				'label' => '团',
				'remark' => '',
				'status' => 0,
				'is_all' => 0,
				'promotion_addon' => 'NsGroupBuy',
				'is_goods_promotion' => 1,
				'promotion_id' => $group_id,
				'start_time' => $start_time,
				'end_time' => $end_time
			];
			$goods_promotion_model->save($data_goods_promotion);
			//创建阶梯价格
			//首先删掉原来的阶梯价格
			$promotion_group_buy_ladder = new NsPromotionGroupBuyLadderModel();
			$promotion_group_buy_ladder->destroy([ "group_id" => $group_id ]);
			
			if (empty($price_array)) {
				$promotion_group_buy->rollback();
				return error();
			} else {
				//循环添加阶梯价格
				foreach ($price_array as $k => $v) {
					$promotion_group_buy_ladder = new NsPromotionGroupBuyLadderModel();
					$temp_data = array(
						"group_id" => $group_id,
						"num" => $v[0],
						"group_price" => $v[1]
					);
					$promotion_group_buy_ladder->save($temp_data);
				}
			}
			$promotion_group_buy->commit();
			return success($group_id);
		} catch (\Exception $e) {
			$promotion_group_buy->rollback();
			return 0;
		}
	}
	
	/**
	 * 获取商品团购活动信息
	 */
	public function getGoodsFirstPromotionGroupBuy($goods_id)
	{
		$promotion_group_buy = new NsPromotionGroupBuyModel();
		$promotion_group_buy_ladder = new NsPromotionGroupBuyLadderModel();
		$time = time();
		$promotion_group_buy_info = $promotion_group_buy->getFirstData([ "goods_id" => $goods_id, "start_time" => [ "lt", $time ], "end_time" => [ "gt", $time ], "status" => 0 ], "create_time desc");
		if (!empty($promotion_group_buy_info)) {
			$promotion_group_buy_ladder_query = $promotion_group_buy_ladder->getQuery([ "group_id" => $promotion_group_buy_info["group_id"] ]);
			$promotion_group_buy_info["price_array"] = $promotion_group_buy_ladder_query;
		}
		return $promotion_group_buy_info;
	}
	
	/**
	 * 删除团购活动
	 */
	function delPromotionGroupBuy($group_id)
	{
		$promotion_group_buy = new NsPromotionGroupBuyModel();
		$promotion_group_buy_ladder = new NsPromotionGroupBuyLadderModel();
		$promotion_group_buy->startTrans();
		try {
			$goods_promotion_model = new NsGoodsPromotionModel();
			$goods_promotion_model->destroy([ 'promotion_id' => $group_id, 'promotion_addon' => 'NsGroupBuy' ]);
			$res1 = $promotion_group_buy->destroy([ 'group_id' => array( "in", $group_id ) ]);
			$res2 = $promotion_group_buy_ladder->destroy([ 'group_id' => array( "in", $group_id ) ]);
			
			if ($res1 > 0 || $res2 > 0) {
				$promotion_group_buy->commit();
			}
			
			return $res1;
		} catch (\Exception $e) {
			$promotion_group_buy->rollback();
			Log::write('团购删除失败：' . $e->getMessage());
			return 0;
		}
		
	}
	
	/**
	 * 获取团购商品列表
	 */
	public function getPromotionGroupBuyGoodsList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '*')
	{
		$picture = new AlbumPictureModel();
		$ns_promotion_group_buy_ladder = new NsPromotionGroupBuyLadderModel();
		
		$goods_model = new NsGoodsModel();
		$viewObj = $goods_model->alias("ng")
			->join('ns_promotion_group_buy npgb', 'ng.goods_id = npgb.goods_id', 'left')
			->field($field);
		$queryList = $goods_model->viewPageQueryNew($viewObj, $page_index, $page_size, $condition, "", $order);
		
		$viewObj = $goods_model->alias("ng")
			->join('ns_promotion_group_buy npgb', 'ng.goods_id = npgb.goods_id', 'left')
			->field($field);
		$queryCount = $goods_model->viewCount($viewObj, $condition);
		$list = $goods_model->setReturnList($queryList, $queryCount, $page_size);
		
		foreach ($list['data'] as $key => $val) {
			$picture_info = $picture->getInfo([ "pic_id" => $val['picture'] ]);
			$list['data'][ $key ]["picture"] = $picture_info;
			$group_buy_ladder_info = $ns_promotion_group_buy_ladder->getInfo([ 'group_id' => $val['group_id'] ], "group_price");
			$list['data'][ $key ]["group_price"] = $group_buy_ladder_info["group_price"];
            $list['data'][ $key ]["display_price"] = bl_cf($group_buy_ladder_info["group_price"]);
			// 商品是否收藏
			if (!empty($this->uid)) {
				$member = new Member();
				$list['data'][ $key ]['is_favorite'] = $member->getIsMemberFavorites($this->uid, $val['goods_id'], 'goods');
			} else {
				$list['data'][ $key ]['is_favorite'] = 0;
			}
		}
		return $list;
	}
	
	/**
	 * 获取拼团价格(单价)
	 */
	public function getGoodsGroupBuySkuPrice($goods_id, $num)
	{
		$ns_promotion_group_buy = new NsPromotionGroupBuyModel();
		$group_buy_info = $ns_promotion_group_buy->getFirstData([ 'goods_id' => $goods_id, "status" => 0 ], "create_time desc");
		
		if (!empty($group_buy_info)) {
			$ns_promotion_group_buy_ladder = new NsPromotionGroupBuyLadderModel();
			$ns_promotion_group_buy_ladder_info = $ns_promotion_group_buy_ladder->getFirstData([ 'group_id' => $group_buy_info["group_id"], "num" => [ "elt", $num ] ], "num desc");
			if (!empty($ns_promotion_group_buy_ladder_info)) {
				$money = $ns_promotion_group_buy_ladder_info["group_price"];
				return success([ "money" => $money, "group_id" => $group_buy_info ]);
			} else {
				return error();
			}
		} else {
			return error();
		}
		
	}
	
	/**
	 * 整合订单活动数据结构
	 */
	public function getOrderGoodsSkuArray($data)
	{
		//团购活动
		$total_money = 0;
		foreach ($data["goods_sku_array"] as $k => $v) {
			$sku_price_result = $this->getGoodsGroupBuySkuPrice($v['goods_id'], $v['num']);
			if ($sku_price_result["code"] <= 0) {
				return error([]);
			}
			$data["goods_sku_array"][ $k ]["sku_price"] = sprintf("%.2f", $sku_price_result["data"]["money"]);
			$data["goods_sku_array"][ $k ]["total_money"] = sprintf("%.2f", $data["goods_sku_array"][ $k ]["sku_price"] * $v['num']);
			$data["goods_sku_array"][ $k ]["total_price"] = sprintf("%.2f", $data["goods_sku_array"][ $k ]["total_money"]);//订单项总金额
			$total_money += $data["goods_sku_array"][ $k ]["total_money"];
			$data["promotion_id"] = $sku_price_result["data"]["group_id"];
		}
		$data["total_money"] = $total_money;
		return success($data);
	}
	
	/*
	 * 团购活动自动过期
	 */
	public function autoGroupBuyClose()
	{
		$promotion_group_buy = new NsPromotionGroupBuyModel();
		$promotion_group_buy->startTrans();
		try {
			$condition['end_time'] = array( 'LT', time() );
			$condition['status'] = array( 'NEQ', 0 );//排成已使用的 团购
			$count = $promotion_group_buy->getCount($condition);
			if ($count) {
				$promotion_group_buy->save([ 'status' => -1 ], $condition);
			}
			$promotion_group_buy->commit();
			return 1;
		} catch (\Exception $e) {
			$promotion_group_buy->rollback();
			return $e->getMessage();
		}
	}
}