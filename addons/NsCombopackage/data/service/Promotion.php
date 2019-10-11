<?php
/**
 * Promote.php
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

namespace addons\NsCombopackage\data\service;

/**
 * 组合套餐
 */
use data\model\NsComboPackagePromotionModel;
use data\model\NsGoodsPromotionModel;
use data\model\NsGoodsViewModel;
use data\service\BaseService;
use data\service\Promotion as PromotionService;
use think\Cache;

class Promotion extends BaseService
{
	/**
	 * 添加或编辑组合套餐
	 */
	public function addOrEditComboPackage($id, $combo_package_name, $combo_package_price, $goods_id_array, $is_shelves, $shop_id, $original_price, $save_the_price)
	{
		Cache::clear('combo_package');
		$data = array(
			"combo_package_name" => $combo_package_name,
			"combo_package_price" => $combo_package_price,
			"goods_id_array" => $goods_id_array,
			"is_shelves" => $is_shelves,
			"shop_id" => $shop_id,
			"original_price" => $original_price,
			"save_the_price" => $save_the_price
		);
		$nsComboPackage = new NsComboPackagePromotionModel();
		if ($id == 0) {
			$data["create_time"] = time();
			$nsComboPackage->save($data);
			$this->addUserLog($this->uid, 1, '营销', '组合套餐', '添加组合套餐：' . $combo_package_name);
			$combo_id = $nsComboPackage->id;
			$res = $combo_id;
		} else if ($id > 0) {
			$data["update_time"] = time();
			$combo_id = $id;
			$res = $nsComboPackage->save($data, [
				"id" => $id,
				"shop_id" => $shop_id
			]);
			$this->addUserLog($this->uid, 1, '营销', '组合套餐', '修改组合套餐：' . $combo_package_name);
		}
		$goods_promotion_model = new NsGoodsPromotionModel();
		$goods_promotion_model->destroy([ 'promotion_id' => $combo_id, 'promotion_addon' => 'NsCombopackage' ]);
		$goods_id_array = explode(',', $goods_id_array);
		foreach ($goods_id_array as $goods_id) {
			$goods_promotion_model = new NsGoodsPromotionModel();
			$data_goods_promotion = [
				'goods_id' => $goods_id,
				'label' => '组',
				'remark' => '',
				'status' => 1,
				'is_all' => 0,
				'promotion_addon' => 'NsCombopackage',
				'promotion_id' => $combo_id,
				'is_goods_promotion' => 0,
				'start_time' => time(),
				'end_time' => 0
			];
			$goods_promotion_model->save($data_goods_promotion);
		}
		return $res;
		
	}
	
	/**
	 * 获取组合套餐详情
	 */
	public function getComboPackageDetail($id)
	{
		$cache = Cache::tag('combo_package')->get('getComboPackageDetail' . $id);
		if (!empty($cache)) return $cache;
		
		$nsComboPackage = new NsComboPackagePromotionModel();
		$info = $nsComboPackage->getInfo([
			"id" => $id
		]);
		Cache::tag('combo_package')->set('getComboPackageDetail' . $id, $info);
		return $info;
	}
	
	/**
	 * 获取组合套餐列表
	 *
	 * @param unknown $page_index
	 * @param unknown $page_size
	 * @param unknown $condition
	 * @param string $order
	 * @param string $field
	 * @return number[]|unknown[]
	 */
	public function getComboPackageList($page_index, $page_size, $condition, $order = "", $field = "*")
	{
		$cache = Cache::tag('combo_package')->get('getComboPackageList' . json_encode([ $page_index, $page_size, $condition, $order, $field ]));
		if (!empty($cache)) return $cache;
		
		$nsComboPackage = new NsComboPackagePromotionModel();
		$list = $nsComboPackage->pageQuery($page_index, $page_size, $condition, $order, $field);
		Cache::tag('combo_package')->set('getComboPackageList' . json_encode([ $page_index, $page_size, $condition, $order, $field ]), $list);
		return $list;
	}
	
	/**
	 * 删除组合套餐
	 */
	public function deleteComboPackage($ids)
	{
		Cache::clear('combo_package');
		$nsComboPackage = new NsComboPackagePromotionModel();
		$res = $nsComboPackage->destroy([
			"id" => array(
				"in",
				$ids
			)
		]);
		$goods_promotion_model = new NsGoodsPromotionModel();
		$goods_promotion_model->destroy([ 'promotion_id' => [ "in", $ids ], 'promotion_addon' => 'NsCombopackage' ]);
		return $res;
	}
	
	/**
	 * 获取组合套餐商品列表
	 *
	 * @param int $goods_id
	 */
	public function getComboPackageGoodsArray($goods_id)
	{
		$cache = Cache::tag('combo_package')->get('getComboPackageGoodsArray' . $goods_id);
		if (!empty($cache)) return $cache;
		
		$nsComboPackage = new NsComboPackagePromotionModel();
		$condition = "FIND_IN_SET($goods_id, goods_id_array ) AND is_shelves = 1";
		$list = $nsComboPackage->getQuery($condition);
		$goods = new NsGoodsViewModel();
		foreach ($list as $k => $v) {
			$main_goods = $goods->getGoodsViewQuery(1, 1, [
				"ng.state" => 1,
				"ng.goods_id" => $goods_id,
				"ng.goods_type" => 1
			], "");
			$list[ $k ]["main_goods"] = $main_goods[0];
			$goods_array = $goods->getGoodsViewQuery(1, 0, [
				"ng.state" => 1,
				"ng.goods_id" => array(
					array(
						"in",
						$v["goods_id_array"]
					),
					array(
						"neq",
						$goods_id
					)
				),
				"ng.goods_type" => 1
			], "");
			$goods_count = $goods->getGoodsrViewCount([
				"ng.goods_id" => array(
					array(
						"in",
						$v["goods_id_array"]
					),
					array(
						"neq",
						$goods_id
					)
				),
				"ng.goods_type" => 1
			]);
			// 计算原价
			$list[ $k ]["original_price"] = $goods->getSum([
				"goods_id" => array(
					"in",
					$v["goods_id_array"]
				),
				"goods_type" => 1
			], "price");
			
			$list[ $k ]["save_the_price"] = $list[ $k ]["original_price"] - $v["combo_package_price"];
			$list[ $k ]["goods_array"] = $goods_array;
			// 如果套餐中有商品已下架，则整个套餐都不予显示
			if (count($goods_array) != $goods_count) {
				unset($list[ $k ]);
			}
		}
		Cache::tag('combo_package')->set('getComboPackageGoodsArray' . $goods_id, $list);
		return $list;
	}
	
	/**
	 * 获取指定组合套餐商品列表
	 *
	 * @param int $id 组合套餐id
	 * @param int $curr_goods_id 当前访问的goods_id
	 */
	public function getComboPackageGoodsById($id, $curr_goods_id)
	{
		$cache = Cache::tag('combo_package')->get('getComboPackageGoodsById_' . $id . '_' . $curr_goods_id);
		if (!empty($cache)) return $cache;
		
		$combo_package_model = new NsComboPackagePromotionModel();
		$promotion_service = new PromotionService();
		$combo_package_condition = "id = $id AND is_shelves = 1";
		$combo_package = $combo_package_model->getInfo($combo_package_condition, "id,combo_package_name,combo_package_price,goods_id_array,is_shelves,shop_id,original_price,save_the_price");
		if (!empty($combo_package)) {
			// 查询组合套餐中的商品信息
			//$goods_condition = "goods_id in(" . $combo_package['goods_id_array'] . ")";
			$combo_package['goods_list'] = array();
			if (!empty($curr_goods_id)) {
				$curr_goods = $promotion_service->getCollatingGoodsDetail($curr_goods_id);
				array_push($combo_package['goods_list'], $curr_goods);
			}
			$goods_id_array = explode(",", $combo_package['goods_id_array']);
			foreach ($goods_id_array as $k => $v) {
				if ($v != $curr_goods_id) {
					$item = $promotion_service->getCollatingGoodsDetail($v);
					array_push($combo_package['goods_list'], $item);
				}
			}
		}
		Cache::tag('combo_package')->set('getComboPackageGoodsById_' . $id . '_' . $curr_goods_id, $combo_package);
		return $combo_package;
	}
	
	/**
	 * 整合订单数据
	 */
	public function getOrderGoodsSkuArray($data)
	{
		// 获取组合套餐详情
		$combo_package_detail = $this->getComboPackageDetail($data["promotion_info"]['combo_package_info']['combo_package_id']);
		
		if (empty($combo_package_detail)) {
			return error([]);
		}
		$data["combo_package_detail"] = $combo_package_detail;
		$combo_package_price = $combo_package_detail['combo_package_price'] * $data["promotion_info"]['combo_package_info']['buy_num'];//组合套餐价格*套餐数量
		$count_combo_package_price = 0;
		$ratio = $combo_package_price / $data["total_money"];
		
		//组合套餐 针对到订单项
		$sku_array_count = count($data["goods_sku_array"]);
		foreach ($data['goods_sku_array'] as $k => $v) {
			if ($k == ($sku_array_count - 1)) {
				$data['goods_sku_array'][ $k ]["total_money"] = sprintf("%.2f", $combo_package_price - $count_combo_package_price);//价格极小时可能会出现问题
				$data['goods_sku_array'][ $k ]["total_price"] = sprintf("%.2f", $combo_package_price - $count_combo_package_price);
				$data['goods_sku_array'][ $k ]["sku_price"] = round($data['goods_sku_array'][ $k ]["total_money"] / $v["num"], 2);
			} else {
				$data['goods_sku_array'][ $k ]["sku_price"] = round($v["sku_price"] * $ratio, 2);
				$data['goods_sku_array'][ $k ]["total_price"] = sprintf("%.2f", $data['goods_sku_array'][ $k ]["sku_price"] * $v["num"]);
				$data['goods_sku_array'][ $k ]["total_money"] = sprintf("%.2f", $data['goods_sku_array'][ $k ]["sku_price"] * $v["num"]);
				$count_combo_package_price += $data['goods_sku_array'][ $k ]["total_money"];
			}
		}
		$data["total_money"] = $combo_package_price;
		$data["promotion_id"] = $data['combo_package_info']['combo_package_id'];
		
		return success($data);
	}
}