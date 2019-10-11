<?php
/**
 * Goods.php
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

use data\service\Address;
use data\service\Album;
use data\service\Config as ConfigService;
use data\service\Express;
use data\service\Goods as GoodsService;
use data\service\GoodsAttribute;
use data\service\GoodsBrand;
use data\service\GoodsCategory;
use data\service\GoodsGroup;
use data\service\GoodsSupplier;
use data\service\Member;
use think\Config;
use data\service\Promotion;
/**
 * 商品控制器
 */
class Goods extends BaseController
{
	/**
	 * 根据商品ID查询单个商品，然后进行编辑操作
	 */
	public function GoodsSelect()
	{
		$goods_detail = new GoodsService();
		$goods = $goods_detail->getGoodsDetail(request()->get('goodsId'));
		return $goods;
	}
	
	/**
	 * 商品列表
	 */
	public function goodsList()
	{
		$goodservice = new GoodsService();
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$start_date = request()->post('start_date') == '' ? 0 : getTimeTurnTimeStamp(request()->post('start_date'));
			$end_date = request()->post('end_date') == '' ? 0 : getTimeTurnTimeStamp(request()->post('end_date'));
			$goods_name = request()->post('goods_name', '');
			$goods_code = request()->post('code', '');
			$state_type = request()->post('state', '');
			$category_id_1 = request()->post('category_id_1', '');
			$category_id_2 = request()->post('category_id_2', '');
			$category_id_3 = request()->post('category_id_3', '');
			$selectGoodsLabelId = request()->post('selectGoodsLabelId', '');
			$supplier_id = request()->post('supplier_id', '');
			$stock_warning = request()->post("stock_warning", 0); // 库存预警
			$sort_rule = request()->post("sort_rule", ""); // 字段排序规则
			$goods_type = request()->post("goods_type", "all"); // 商品类型
			
			if ($goods_type !== "" && $goods_type != "all") {
				$condition["ng.goods_type"] = $goods_type;
			}
			if (!empty($selectGoodsLabelId)) {
				$selectGoodsLabelIdArray = explode(',', $selectGoodsLabelId);
				$selectGoodsLabelIdArray = array_filter($selectGoodsLabelIdArray);
				$str = "FIND_IN_SET(" . $selectGoodsLabelIdArray[0] . ",ng.group_id_array)";
				for ($i = 1; $i < count($selectGoodsLabelIdArray); $i++) {
					$str .= "AND FIND_IN_SET(" . $selectGoodsLabelIdArray[ $i ] . ",ng.group_id_array)";
				}
				$condition[""] = [
					[
						"EXP",
						$str
					]
				];
			}
			
			if ($start_date != 0 && $end_date != 0) {
				$condition["ng.create_time"] = [
					[
						">",
						$start_date
					],
					[
						"<",
						$end_date
					]
				];
			} elseif ($start_date != 0 && $end_date == 0) {
				$condition["ng.create_time"] = [
					[
						">",
						$start_date
					]
				];
			} elseif ($start_date == 0 && $end_date != 0) {
				$condition["ng.create_time"] = [
					[
						"<",
						$end_date
					]
				];
			}
			
			if ($state_type > 0) {
				$condition["ng.state"] = 0;
			} else {
				$condition["ng.state"] = 1;
			}
			if (!empty($goods_name)) {
				$condition["ng.goods_name"] = array(
					"like",
					"%" . $goods_name . "%"
				);
			}
			if (!empty($goods_code)) {
				$condition["ng.code"] = array(
					"like",
					"%" . $goods_code . "%"
				);
			}
			if (!empty($category_id_3)) {
				$condition["ng.category_id_3"] = $category_id_3;
			} elseif (!empty($category_id_2)) {
				$condition["ng.category_id_2"] = $category_id_2;
			} elseif (!empty($category_id_1)) {
				$condition["ng.category_id_1"] = $category_id_1;
			}
			
			if ($supplier_id != '') {
				$condition['ng.supplier_id'] = $supplier_id;
			}
			
			$condition["ng.shop_id"] = $this->instance_id;
			
			// 库存预警
			if ($stock_warning == 1) {
				$condition['ng.min_stock_alarm'] = array(
					"neq",
					0
				);
				$condition['ng.stock'] = array(
					"exp",
					"<= ng.min_stock_alarm"
				);
			}
			
			$order = array();
			if (!empty($sort_rule)) {
				$sort_rule_arr = explode(",", $sort_rule);
				$sort_field = $sort_rule_arr[0];
				$sort_value = $sort_rule_arr[1];
				if ($sort_value == "a") {
					$sort_value = "ASC";
				} elseif ($sort_value == "d") {
					$sort_value = "DESC";
				} else {
					$sort_value = "";
				}
				
				if (!empty($sort_value)) {
					switch ($sort_field) {
						case "price":
							$order['ng.price'] = $sort_value;
							break;
						case "stock":
							$order['ng.stock'] = $sort_value;
							break;
						case "sales":
							$order['ng.sales'] = $sort_value;
							break;
						case "sort":
							$order['ng.sort'] = $sort_value;
							break;
					}
				}
			} else {
				// 默认时间排序
				$order['ng.create_time'] = 'desc';
			}
			
			$result = $goodservice->getBackStageGoodsList($page_index, $page_size, $condition, $order);
			
			// 根据商品分组id，查询标签名称
			foreach ($result['data'] as $k => $v) {
				if (!empty($v['group_id_array'])) {
					$goods_group_id = explode(',', $v['group_id_array']);
					$goods_group_name = '';
					foreach ($goods_group_id as $key => $val) {
						$goods_group = new GoodsGroup();
						$goods_group_info = $goods_group->getGoodsGroupDetail($val);
						if (!empty($goods_group_info)) {
							$goods_group_name .= $goods_group_info['group_name'] . ',';
						}
					}
					$goods_group_name = rtrim($goods_group_name, ',');
					$result["data"][ $k ]['goods_group_name'] = $goods_group_name;
				}
			}
			return $result;
		} else {
			$goods_group = new GoodsGroup();
			$groupList = $goods_group->getGoodsGroupList(1, 0, [
				'shop_id' => $this->instance_id,
				'pid' => 0
			]);
			if (!empty($groupList['data'])) {
				foreach ($groupList['data'] as $k => $v) {
					$v['sub_list'] = $goods_group->getGoodsGroupList(1, 0, 'pid = ' . $v['group_id']);
				}
			}
			
			$this->assign("goods_group", $groupList['data']);
			$search_info = request()->get('search_info', '');
			$this->assign("search_info", $search_info);
			// 查找一级商品分类
			$goodsCategory = new GoodsCategory();
			$oneGoodsCategory = $goodsCategory->getGoodsCategoryListByParentId(0);
			$this->assign("oneGoodsCategory", $oneGoodsCategory);
			// 供货商列表
			$supplier = new GoodsSupplier();
			$supplier_list = $supplier->getSupplierList();
			$this->assign("supplier_list", $supplier_list['data']);
			// 上下架
			$state_type = request()->get("state_type", "");
			$this->assign("state", $state_type);
			// 库存预警
			$stock_warning = request()->get("stock_warning", 0);
			$this->assign("stock_warning", $stock_warning);
			if ($state_type == 2) {
				$child_menu_list = array(
					array(
						'url' => "goods/goodslist",
						'menu_name' => "出售中",
						"active" => 0
					),
					array(
						'url' => "goods/goodslist?state_type=2",
						'menu_name' => "已下架",
						'active' => 1
					),
					array(
						'url' => "goods/goodslist?stock_warning=1",
						'menu_name' => "库存预警",
						"active" => 0
					),
					array(
						'url' => "goods/recyclelist",
						'menu_name' => "回收站",
						"active" => 0
					)
				);
			} else
				if ($stock_warning == 1) {
					$child_menu_list = array(
						array(
							'url' => "goods/goodslist",
							'menu_name' => "出售中",
							"active" => 0
						),
						array(
							'url' => "goods/goodslist?state_type=2",
							'menu_name' => "已下架",
							'active' => 0
						),
						array(
							'url' => "goods/goodslist?stock_warning=1",
							'menu_name' => "库存预警",
							"active" => 1
						),
						array(
							'url' => "goods/recyclelist",
							'menu_name' => "回收站",
							"active" => 0
						)
					);
				} else {
					$child_menu_list = array(
						array(
							'url' => "goods/goodslist",
							'menu_name' => "出售中",
							"active" => 1
						),
						array(
							'url' => "goods/goodslist?state_type=2",
							'menu_name' => "已下架",
							'active' => 0
						),
						array(
							'url' => "goods/goodslist?stock_warning=1",
							'menu_name' => "库存预警",
							"active" => 0
						),
						array(
							'url' => "goods/recyclelist",
							'menu_name' => "回收站",
							"active" => 0
						)
					);
				}
			$this->assign('child_menu_list', $child_menu_list);
			
			// 查询会员等级
			$member = new Member();
			$level_list = $member->getMemberLevelList(1, 0);
			$this->assign('level_list', $level_list["data"]);
			
			// 虚拟商品分组
			$virtual_goods_group = $goodservice->getVirtualGoodsGroup();
			$this->assign("virtual_goods_group", $virtual_goods_group);
			//商品类型
			$goods_type_list = hook('getGoodsConfig', [ 'type' => 'all' ]);
			$this->assign('goods_type_list', $goods_type_list);
			//品牌列表
			$goodsbrand = new GoodsBrand();
			$brand_list = $goodsbrand->getGoodsBrandList(1, 0);
			$this->assign("brand_list", $brand_list['data']);
			
			return view($this->style . "Goods/goodsList");
		}
	}
	
	public function getCategoryByParentAjax()
	{
		if (request()->isAjax()) {
			$parentId = request()->post("parentId", '');
			$goodsCategory = new GoodsCategory();
			$res = $goodsCategory->getGoodsCategoryListByParentId($parentId);
			return $res;
		}
	}
	
	/**
	 * 功能说明：通过ajax来的得到页面的数据
	 */
	public function SelectCateGetData()
	{
		$goods_category_id = request()->post("goods_category_id", ''); // 商品类目用
		$goods_category_name = request()->post("goods_category_name", ''); // 商品类目名称显示用
		$goods_attr_id = request()->post("goods_attr_id", ''); // 关联商品类型ID
		$quick = request()->post("goods_category_quick", ''); // JSON格式
		setcookie("goods_category_id", $goods_category_id, time() + 3600 * 24);
		setcookie("goods_category_name", $goods_category_name, time() + 3600 * 24);
		setcookie("goods_attr_id", $goods_attr_id, time() + 3600 * 24);
		setcookie("goods_category_quick", $quick, time() + 3600 * 24);
		return 1;
	}
	
	/**
	 * 获取用户快速选择商品
	 */
	public function getQuickGoods()
	{
		if (isset($_COOKIE["goods_category_quick"])) {
			return $_COOKIE["goods_category_quick"];
		} else {
			return -1;
		}
	}
	
	/**
	 * 添加商品
	 */
	public function addGoods()
	{
		$type = request()->get("type", 'all');
		//商品类型
		$this->assign('goods_type', $type);
		
		//加载初始信息
		$this->goodsAssign(0);
		
		$res = hook('addGoods', [ 'type' => $type ]);
		$res = arrayFilter($res);
		if (empty($res[0])) {
			$this->error("商品类型不存在");
		}
		return $res[0];
	}
	
	/**
	 * 编辑商品
	 */
	public function editGoods()
	{
		$goods_id = request()->get('goods_id', 0);
		
		//加载初始信息
		$goods_type = $this->goodsAssign($goods_id);
		
		$config = hook('getGoodsConfig', [ 'type_id' => $goods_type ]);
		$config = arrayFilter($config);
		$this->assign('goods_type', $config[0]['name']);
		
		$res = hook('editGoods', [ 'type' => $config[0]['name'], 'goods_id' => $goods_id ]);
		$res = arrayFilter($res);
		if (empty($res[0])) {
			$this->error("商品类型不存在");
		}
		return $res[0];
	}
	
	/**
	 * 商品需要的值
	 */
	public function goodsAssign($goods_id)
	{
		$goods_type = 'all';
		$this->assign("goods_id", $goods_id);
		
		//分组
		$goods_group = new GoodsGroup();
		$groupList = $goods_group->getGoodsGroupList(1, 0, [
			'shop_id' => $this->instance_id
		]);
		
		$this->assign("group_list", $groupList['data']); // 分组
		if (empty($groupList['data'])) {
			$this->assign("group_str", '');
		} else {
			$this->assign("group_str", json_encode($groupList['data']));
		}
		
		//物流
		$express = new Express();
		$this->assign("shipping_list", $express->shippingFeeQuery("")); // 物流
		// 物流公司
		$expressCompanyList = $express->getExpressCompanyList(1, 0, [ 'shop_id' => $this->instance_id ]);
		$this->assign("expressCompanyList", $expressCompanyList['data']);
		
		//供应商
		$supplier = new GoodsSupplier();
		$supplier_list = $supplier->getSupplierList();
		$this->assign("supplier_list", $supplier_list['data']);
		
		//是否安装预售
		$is_presell = addon_is_exit('NsPresell');
		$this->assign("is_presell", $is_presell);
		
		$goods = new GoodsService();
		
		// 商品类型
		$goods_attribute = new GoodsAttribute();
		$goods_attribute_list = $goods_attribute->getAttributeList(1, 0, [
			'is_use' => 1
		], "", "attr_id,attr_name,spec_id_array");
		$this->assign("goods_attribute_list", $goods_attribute_list['data']);
		
		//商品规格
		$goods_spec_list = $goods->getGoodsSpecInfoQuery(0, $goods_id);
		$this->assign('goods_spec_list', $goods_spec_list['spec_list']);
		
		//不存在分组的商品规格
		$attribute_spec_list = '';
		foreach ($goods_attribute_list['data'] as $item) {
			if (empty($item['spec_id_array'])) continue;
			$attribute_spec_list .= empty($attribute_spec_list) ? '' : ',';
			$attribute_spec_list .= $item['spec_id_array'];
		}
		$spec_condition = array(
			'spec_id' => [ 'not in', $attribute_spec_list ],
			'is_visible' => 1,
			"goods_id" => [ 'in', '0,' . $goods_id ]
		);
		
		$goods_spec_list = $goods->getGoodsSpecQuery($spec_condition, $goods_id);
		$this->assign('rests_goods_spec_list', $goods_spec_list);
		
		// 相册默认列表
		$album = new Album();
		$detault_album_detail = $album->getDefaultAlbumDetail();
		$this->assign('detault_album_id', $detault_album_detail['album_id']);
		
		//模板
		$template_url = array();
		$config = new ConfigService();
		$pc_template = $config->getUsePCTemplate($this->instance_id);
		$wap_template = $config->getUseWapTemplate($this->instance_id);
		
		$template_url["pc_template_url"] = "template/web/" . $pc_template['value'] . '/Goods/';
		$template_url["wap_template_url"] = "template/wap/" . $wap_template['value'] . '/Goods/';
		$this->assign("template_url", $template_url);
		
		// 查询会员等级
		$member = new Member();
		$level_list = $member->getMemberLevelList(1, 0);
		if (isset($level_list["data"]) && count($level_list["data"]) > 0) {
			foreach ($level_list["data"] as $key => $val) {
				if ($goods_id > 0) {
					$discount_info = $goods->getGoodsDiscountByMemberLevel($val["level_id"], $goods_id);
					$level_list["data"][ $key ]["discount"] = $discount_info["discount"];
					$level_list["data"][ $key ]["decimal_reservation_number"] = $discount_info["decimal_reservation_number"];
				} else {
					$level_list["data"][ $key ]["discount"] = "";
					$level_list["data"][ $key ]["decimal_reservation_number"] = 2;
				}
			}
			
		}
		$this->assign('level_list', $level_list["data"]);
		
		if ($goods_id > 0) {
			
			if (!is_numeric($goods_id)) {
				$this->error("参数错误");
			}
			
			$goods_info = $goods->getGoodsDetail($goods_id);
			
			if (!empty($goods_info)) {
				
				$goods_type = $goods_info['goods_type'];
				//虚拟扩展
				$this->assign('extend_json', $goods_info['sku_list'][0]['extend_json']);
				$goods_info['presell_time'] = getTimeStampTurnTime($goods_info['presell_time']);
				$goods_info['sku_list'] = json_encode($goods_info['sku_list']);
				$goods_info['goods_group_list'] = json_encode($goods_info['goods_group_list']);
				$goods_info['img_list'] = json_encode($goods_info['img_list']);
				$goods_info['goods_attribute_list'] = json_encode($goods_info['goods_attribute_list']);
				// 判断规格数组中图片路径是id还是路径
				if (trim($goods_info['goods_spec_format']) != "") {
					$album = new Album();
					$goods_spec_array = json_decode($goods_info['goods_spec_format'], true);
					foreach ($goods_spec_array as $k => $v) {
						foreach ($v["value"] as $t => $m) {
							if (is_numeric($m["spec_value_data"]) && $m["spec_show_type"] == 3) {
								$picture_detail = $album->getAlubmPictureDetail([
									"pic_id" => $m["spec_value_data"]
								]);
								if (!empty($picture_detail)) {
									$goods_spec_array[ $k ]["value"][ $t ]["spec_value_data_src"] = $picture_detail["pic_cover_micro"];
								}
							} elseif (!is_numeric($m["spec_value_data"]) && $m["spec_show_type"] == 3) {
								$goods_spec_array[ $k ]["value"][ $t ]["spec_value_data_src"] = $m["spec_value_data"];
							}
						}
					}
					
					$this->assign('spec_arr_count', count($goods_spec_array));
					$goods_spec_format = json_encode($goods_spec_array, JSON_UNESCAPED_UNICODE);
					
					$goods_info['goods_spec_format'] = $goods_spec_format;
				}
				
				$extent_sort = count($goods_info["extend_category"]);
				$this->assign("extent_sort", $extent_sort);
				if ($goods_info["group_id_array"] == "") {
					$this->assign("edit_group_array", array());
				} else {
					$this->assign("edit_group_array", explode(",", $goods_info["group_id_array"]));
				}
				/**
				 * 当前cookie中存的goodsid
				 */
				$update_goods_id = isset($_COOKIE["goods_update_goodsid"]) ? $_COOKIE["goods_update_goodsid"] : 0;
				if ($update_goods_id == $goods_id) {
					$category_name = str_replace(":", "", $_COOKIE["goods_category_name"]);
					$goods_info["category_id"] = $_COOKIE["goods_category_id"];
					$goods_info["category_name"] = $category_name;
				}
				$goods_info['description'] = str_replace(PHP_EOL, '', $goods_info['description']);
				
				// 规格数据转json
				if (!empty($goods_info["sku_picture_array"])) {
					$sku_picture_array_str = json_encode($goods_info["sku_picture_array"]);
				} else {
					$sku_picture_array_str = '';
				}
				$this->assign("sku_picture_array_str", $sku_picture_array_str);
				
				// 商品阶梯优惠
				$ladder_preferential = $goods->getGoodsLadderPreferential([
					"goods_id" => $goods_id
				]);
				
				$this->assign("ladder_preferential", $ladder_preferential);
				
				$goods_info['description'] = $str = str_replace(array("\r\n", "\r", "\n"), "",  $goods_info['description']);
				
				$this->assign("goods_info", $goods_info);
				
				return $goods_type;
			} else {
				$this->error("商品不存在");
			}
		}
	}
	
	/**
	 * 虚拟商品管理控制器
	 */
	public function virtualGoodsList()
	{
		$goods_id = request()->get('goods_id', 0);
		$this->assign('goods_id', $goods_id);
		
		// 商品详情
		$goods = new GoodsService();
		$goods_info = $goods->getGoodsDetail($goods_id);
		$this->assign('goods_info', $goods_info);
		// 虚拟商品信息
		$virtual_goods_type_info = $goods->getVirtualGoodsTypeInfo([
			'relate_goods_id' => $goods_id
		]);
		$this->assign('virtual_goods_type_info', $virtual_goods_type_info);
		
		// 虚拟商品分组信息
		$virtual_goods_group_id = $virtual_goods_type_info['virtual_goods_group_id'];
		$virtual_group_info = $goods->getVirtualGoodsGroupInfo($virtual_goods_group_id);
		$this->assign('virtual_group_info', $virtual_group_info);
		
		// 卡密库存
		$virtual_goods_count = $goods->getVirtualGoodsCountByGoodsId($goods_id);
		$this->assign("virtual_goods_count", $virtual_goods_count);
		
		return view($this->style . "Goods/virtualGoodsList");
	}
	
	/**
	 * 虚拟商品管理
	 */
	public function virtualGoodsManage()
	{
		$goods_id = request()->get("goods_id", '');
		
		// 商品详情
		$goods = new GoodsService();
		$goods_info = $goods->getGoodsDetail($goods_id);
		$this->assign('goods_info', $goods_info);
		
		$goods_config = hook('getGoodsConfig', [ 'type_id' => $goods_info['goods_type'] ]);
		$type_config = arrayFilter($goods_config)[0];
		$type = $type_config['name'];
		
		$res = hook('virtualGoodsManage', [ 'type' => $type ]);
		$res = arrayFilter($res);
		if (empty($res[0])) {
			$this->error("商品类型不存在");
		}
		
		return $res[0];
	}
	
	/**
	 * 根据goodsid查询虚拟商品列表
	 */
	public function getVirtualGoodsListByGoodsId()
	{
		$goods_id = request()->post("goods_id", 0);
		$page_index = request()->post("page_index", 1);
		$page_size = request()->post("page_size", PAGESIZE);
		$search_name = request()->post("search_name", '');
		$use_status = request()->post("use_status", '');
		$virtual_code = request()->post("virtual_code", '');
		
		if ($goods_id == 0) {
			return [];
		}
		
		$condition = array();
		$condition['ng.goods_id'] = $goods_id;
		$condition['ng.goods_name'] = [
			'like',
			'%' . $search_name . '%'
		];
		if ($use_status != '') {
			$condition['nvg.use_status'] = $use_status;
		}
		if ($virtual_code != '') {
			$condition['nvg.virtual_code'] = $virtual_code;
		}
		
		$virtual_goods = new GoodsService();
		$res = $virtual_goods->getVirtualGoodsListByGoodsId($page_index, $page_size, $condition, 'nvg.virtual_goods_id desc');
		return $res;
	}
	
	/**
	 * 添加虚拟商品
	 */
	public function addVirtualGoods()
	{
		$card_number = request()->post("card_number", "");
		$card_password = request()->post("card_password", "");
		$validity_period = request()->post("validity_period", 0);
		$confine_use_number = request()->post("confine_use_number", 0);
		$goods_id = request()->post("goods_id", 0);
		$res = -1;
		if (!empty($card_number) && !empty($card_password) && $goods_id > 0) {
			$goods_service = new GoodsService();
			$remark = "卡号：" . $card_number . '&nbsp;&nbsp;密码：' . $card_password;
			$data = array(
				'virtual_goods_name' => "",
				'money' => 0,
				'buyer_id' => "",
				'buyer_nickname' => '',
				'order_goods_id' => 0,
				'order_no' => '',
				'validity_period' => $validity_period,
				'start_time' => 0,
				'end_time' => 0,
				'use_number' => 0,
				'confine_use_number' => $confine_use_number,
				'use_status' => -2,
				'create_time' => time(),
				'goods_id' => $goods_id,
				'sku_id' => $goods_id,
				'remark' => $remark
			);
			$res = $goods_service->addVirtualGoods($data);
		}
		return $res;
	}
	
	/**
	 * 根据主键id删除虚拟商品，支持多个删除
	 */
	public function deleteVirtualGoodsById()
	{
		$virtual_goods_id = request()->post("virtual_goods_id", "");
		$res = -1;
		if (!empty($virtual_goods_id)) {
			$goods = new GoodsService();
			$res = $goods->deleteVirtualGoodsById($virtual_goods_id);
		}
		return AjaxReturn($res);
	}
	
	/**
	 * 获取商品品牌列表，商品编辑时用到
	 */
	public function getGoodsBrandList()
	{
		$page_index = request()->post("page_index", 1);
		$page_size = request()->post('page_size', PAGESIZE);
		$brand_name = request()->post("brand_name", "");
		$search_name = request()->post("search_name", "");
		$brand_id = request()->post("brand_id", "");
		$condition = request()->post("condition", []);
		if (!empty($condition)) {
			$condition = json_decode($condition, true);
		}
		$condition['shop_id'] = $this->instance_id;
		// 排除当前选中的品牌，然后模糊查询
		$condition['brand_name|brand_initial'] = array(
			[
				"like",
				"%$search_name%"
			],
			[
				'eq',
				$brand_name
			],
			'or'
		);
		// 判断当时编辑商品还是添加商品，如果存在品牌id，则排除该品牌，防止搜索结果出现重复数据
		if (!empty($brand_id)) {
			$condition['brand_id'] = [
				'neq',
				$brand_id
			];
		}
		$goodsbrand = new GoodsBrand();
		$goods_brand_list = $goodsbrand->getGoodsBrandList($page_index, $page_size, $condition, 'brand_id desc', 'brand_id,brand_name,brand_pic');
		return $goods_brand_list;
	}
	
	/**
	 * 根据商品类型id查询，商品规格信息
	 */
	public function getGoodsSpecListByAttrId()
	{
		$goods_attribute = new GoodsAttribute();
		$condition["attr_id"] = request()->post("attr_id", 0);
		$list = $goods_attribute->getGoodsAttrSpecQuery($condition);
		return $list;
	}
	
	/**
	 * 功能说明：通过节点的ID查询得到某个节点下的子集
	 */
	public function getChildCateGory()
	{
		$categoryID = request()->post('categoryID', '');
		$goods_category = new GoodsCategory();
		$list = $goods_category->getGoodsCategoryListByParentId($categoryID);
		return $list;
	}
	
	/**
	 * 删除商品
	 */
	public function deleteGoods()
	{
		$goods_ids = request()->post('goods_ids');
		$goodservice = new GoodsService();
		$retval = $goodservice->deleteGoods($goods_ids);
		return AjaxReturn($retval);
	}
	
	/**
	 * 删除回收站商品
	 */
	public function emptyDeleteGoods()
	{
		$goods_ids = request()->post('goods_ids');
		$goodsservice = new GoodsService();
		$res = $goodsservice->deleteRecycleGoods($goods_ids);
		return AjaxReturn($res);
	}
	
	/**
	 * 商品品牌列表
	 */
	public function goodsBrandList()
	{
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$search_text = request()->post("search_text", "");
			$goodsbrand = new GoodsBrand();
			$result = $goodsbrand->getGoodsBrandList($page_index, $page_size, [
				'shop_id' => $this->instance_id,
				'brand_name' => array(
					"like",
					"%" . $search_text . "%"
				)
			], "brand_id desc");
			$goods_catefory = new GoodsCategory();
			foreach ($result['data'] as $v) {
				$v['category_id_1_name'] = !empty($goods_catefory->getName($v['category_id_1'])['category_name']) ? $goods_catefory->getName($v['category_id_1'])['category_name'] : "";
				$v['category_id_2_name'] = !empty($goods_catefory->getName($v['category_id_2'])['category_name']) ? $goods_catefory->getName($v['category_id_2'])['category_name'] : "";
				$v['category_id_3_name'] = !empty($goods_catefory->getName($v['category_id_3'])['category_name']) ? $goods_catefory->getName($v['category_id_3'])['category_name'] : "";
			}
			return $result;
		} else {
			return view($this->style . "Goods/goodsBrandList");
		}
	}
	
	/**
	 * 添加商品品牌
	 */
	public function addGoodsBrand()
	{
		if (request()->isAjax()) {
			$goodsbrand = new GoodsBrand();
			$brand_name = request()->post('brand_name', '');
			$brand_initial = request()->post('brand_initial', '');
			$describe = request()->post('describe', '');
			$brand_pic = request()->post('brand_pic', '');
			$brand_recommend = request()->post('brand_recommend', '');
			$category_name = request()->post('category_name', '');
			$category_id_1 = request()->post('category_id_1', 0);
			$category_id_2 = request()->post('category_id_2', 0);
			$category_id_3 = request()->post('category_id_3', 0);
			$brand_ads = request()->post('brand_ads', '');
			$sort = 0;
			$params = [
				"shop_id" => $this->instance_id,
				"brand_name" => $brand_name,
				"brand_initial" => $brand_initial,
				"describe" => $describe,
				"brand_pic" => $brand_pic,
				"brand_recommend" => $brand_recommend,
				"category_name" => $category_name,
				"category_id_1" => $category_id_1,
				"category_id_2" => $category_id_2,
				"category_id_3" => $category_id_3,
				"sort" => $sort,
				"brand_ads" => $brand_ads,
			];
			$res = $goodsbrand->editGoodsBrand($params);
			return AjaxReturn($res);
		} else {
			$goodscategory = new GoodsCategory();
			$list = $goodscategory->getGoodsCategoryListByParentId(0);
			$this->assign('goods_category_list', $list);
			
			return view($this->style . "Goods/addGoodsBrand");
		}
	}
	
	/**
	 * 选择商品分类
	 */
	function changeCategory()
	{
		$pid = request()->post('pid', 0);
		$list = array();
		if ($pid > 0) {
			$goodscategory = new GoodsCategory();
			$list = $goodscategory->getGoodsCategoryListByParentId($pid);
		}
		return $list;
	}
	
	/**
	 * 修改商品品牌
	 */
	public function updateGoodsBrand()
	{
		$goodsbrand = new GoodsBrand();
		if (request()->isAjax()) {
			$brand_id = request()->post('brand_id', '');
			$brand_name = request()->post('brand_name', '');
			$brand_initial = request()->post('brand_initial', '');
			$describe = request()->post('describe', '');
			$brand_pic = request()->post('brand_pic', '');
			$brand_recommend = request()->post('brand_recommend', 0);
			$category_name = request()->post('category_name', '');
			$category_id_1 = request()->post('category_id_1', 0);
			$category_id_2 = request()->post('category_id_2', 0);
			$category_id_3 = request()->post('category_id_3', 0);
			$brand_ads = request()->post('brand_ads', '');
			$sort = request()->post('sort', 0);
			$params = [
				"brand_id" => $brand_id,
				"shop_id" => $this->instance_id,
				"brand_name" => $brand_name,
				"brand_initial" => $brand_initial,
				"describe" => $describe,
				"brand_pic" => $brand_pic,
				"brand_recommend" => $brand_recommend,
				"category_name" => $category_name,
				"category_id_1" => $category_id_1,
				"category_id_2" => $category_id_2,
				"category_id_3" => $category_id_3,
				"sort" => $sort,
				"brand_ads" => $brand_ads,
			];
			$res = $goodsbrand->editGoodsBrand($params);
			return AjaxReturn($res);
		} else {
			$brand_id = request()->get('brand_id', '');
			if (!is_numeric($brand_id)) {
				$this->error('未获取到信息');
			}
			$brand_info = $goodsbrand->getGoodsBrandInfo($brand_id);
			if (empty($brand_info)) {
				return $this->error("没有查询到商品品牌信息");
			}
			$this->assign('brand_info', $brand_info);
			$goodscategory = new GoodsCategory();
			$list = $goodscategory->getGoodsCategoryListByParentId(0);
			$this->assign('goods_category_list', $list);
			
			return view($this->style . "Goods/editGoodsBrand");
		}
	}
	
	/**
	 * 删除商品品牌
	 */
	public function deleteGoodsBrand()
	{
		$brand_id = request()->post('brand_id', '');
		$goodsbrand = new GoodsBrand();
		$res = $goodsbrand->deleteGoodsBrand($brand_id);
		return AjaxReturn($res);
	}
	
	/**
	 * 商品分类列表
	 */
	public function goodsCategoryList()
	{
		$goods_category = new GoodsCategory();
		$one_list = $goods_category->getCategoryTreeUseInAdmin();
		$this->assign("category_list", $one_list);
		return view($this->style . "Goods/goodsCategoryList");
	}
	
	/**
	 * 添加商品分类
	 */
	public function addGoodsCategory()
	{
		$goodscate = new GoodsCategory();
		if (request()->isAjax()) {
			$category_name = request()->post("category_name", '');
			$pid = request()->post("pid", '');
			$is_visible = request()->post('is_visible', '');
			$keywords = request()->post("keywords", '');
			$description = request()->post("description", '');
			$sort = request()->post("sort", 0);
			$category_pic = request()->post('category_pic', '');
			$attr_id = request()->post("attr_id", 0);
			$attr_name = request()->post("attr_name", '');
			$short_name = request()->post("short_name", '');
			$pc_custom_template = request()->post("pc_custom_template", "");
			$wap_custom_template = request()->post("wap_custom_template", "");
			$params = array(
				'category_id' => 0,
				'data' => [
					'category_name' => $category_name,
					'short_name' => $short_name,
					'pid' => $pid,
					'is_visible' => $is_visible,
					'keywords' => $keywords,
					'description' => $description,
					'sort' => $sort,
					'category_pic' => $category_pic,
					'attr_id' => $attr_id,
					'attr_name' => $attr_name,
					'pc_custom_template' => $pc_custom_template,
					'wap_custom_template' => $wap_custom_template
				]
			);
			$result = $goodscate->editGoodsCategory($params);
			return AjaxReturn($result);
		} else {
			$category_list = $goodscate->getGoodsCategoryTree(0);
			$this->assign('category_list', $category_list);
			$goods_attribute = new GoodsAttribute();
			$goodsAttributeList = $goods_attribute->getAttributeList(1, 0);
			$this->assign("goodsAttributeList", $goodsAttributeList['data']);
			
			$template_url = array();
			$config = new ConfigService();
			$pc_template = $config->getUsePCTemplate($this->instance_id);
			$wap_template = $config->getUseWapTemplate($this->instance_id);
			$template_url["pc_template_url"] = "template/web/" . $pc_template['value'] . '/Goods/';
			$template_url["wap_template_url"] = "template/wap/" . $wap_template['value'] . '/Goods/';
			$this->assign("template_url", $template_url);
			
			return view($this->style . "Goods/addGoodsCategory");
		}
	}
	
	/**
	 * 修改商品分类
	 */
	public function updateGoodsCategory()
	{
		$goodscate = new GoodsCategory();
		if (request()->isAjax()) {
			$levels = request()->post("levels", "");
			$category_id = request()->post("category_id", '');
			$category_name = request()->post("category_name", '');
			$short_name = request()->post("short_name", '');
			$pid = request()->post("pid", '');
			$is_visible = request()->post('is_visible', '');
			$keywords = request()->post("keywords", '');
			$description = request()->post("description", '');
			$sort = request()->post("sort", '');
			$attr_id = request()->post("attr_id", 0);
			$attr_name = request()->post("attr_name", '');
			$category_pic = request()->post('category_pic', '');
			$goods_category_quick = request()->post("goods_category_quick", '');
			$pc_custom_template = request()->post("pc_custom_template", "");
			$wap_custom_template = request()->post("wap_custom_template", "");
			if ($goods_category_quick != '') {
				setcookie("goods_category_quick", $goods_category_quick, time() + 3600 * 24);
			}
			$params = array(
				'category_id' => $category_id,
				'levels' => $levels,
				'data' => [
					'category_name' => $category_name,
					'short_name' => $short_name,
					'pid' => $pid,
					'is_visible' => $is_visible,
					'keywords' => $keywords,
					'description' => $description,
					'sort' => $sort,
					'category_pic' => $category_pic,
					'attr_id' => $attr_id,
					'attr_name' => $attr_name,
					'pc_custom_template' => $pc_custom_template,
					'wap_custom_template' => $wap_custom_template
				]
			);
			$result = $goodscate->editGoodsCategory($params);
			return AjaxReturn($result);
		} else {
			$category_id = request()->get('category_id', '');
			$result = $goodscate->getGoodsCategoryDetail($category_id);
			$this->assign("data", $result);
			// 查询比当前等级高的 分类
			if ($result['level'] == 1) {
				$chile_list = $goodscate->getGoodsCategoryTree($category_id);
				if (empty($chile_list)) {
					$category_list = $goodscate->getGoodsCategoryTree(0);
				} else {
					$is_have = false;
					foreach ($chile_list as $k => $v) {
						if ($v["level"] == 3) {
							$is_have = true;
						}
					}
					if ($is_have) {
						$category_list = array();
					} else {
						$category_list = $goodscate->getGoodsCategoryListByParentId(0);
					}
				}
			} elseif ($result['level'] == 2) {
				$chile_list = $goodscate->getGoodsCategoryListByParentId($category_id);
				if (empty($chile_list)) {
					$category_list = $goodscate->getGoodsCategoryTree(0);
				} else {
					$category_list = $goodscate->getGoodsCategoryListByParentId(0);
				}
			} elseif ($result['level'] == 3) {
				$category_list = $goodscate->getGoodsCategoryTree(0);
			}
			if (!empty($category_list)) {
				foreach ($category_list as $k => $v) {
					if ($v["category_id"] == $category_id && $category_id !== 0) {
						unset($category_list[ $k ]);
					} else {
						if (isset($v["child_list"])) {
							$temp_array = $v["child_list"];
							foreach ($temp_array as $t => $m) {
								if ($m["category_id"] == $category_id && $category_id !== 0) {
									unset($temp_array[ $t ]);
								}
							}
							sort($temp_array);
							$category_list[ $k ]["child_list"] = $temp_array;
						}
					}
				}
				sort($category_list);
			}
			$this->assign('category_list', $category_list);
			$goods_attribute = new GoodsAttribute();
			$goodsAttributeList = $goods_attribute->getAttributeList(1, 0);
			$this->assign("goodsAttributeList", $goodsAttributeList['data']);
			
			$template_url = array();
			$config = new ConfigService();
			$pc_template = $config->getUsePCTemplate($this->instance_id);
			$wap_template = $config->getUseWapTemplate($this->instance_id);
			$template_url["pc_template_url"] = "template/web/" . $pc_template['value'] . '/Goods/';
			$template_url["wap_template_url"] = "template/wap/" . $wap_template['value'] . '/Goods/';
			$this->assign("template_url", $template_url);
			
			return view($this->style . "Goods/updateGoodsCategory");
		}
	}
	
	/**
	 * 删除商品分类
	 */
	public function deleteGoodsCategory()
	{
		$goodscate = new GoodsCategory();
		$category_id = request()->post('category_id', '');
		$res = $goodscate->deleteGoodsCategory($category_id);
		if ($res > 0) {
			$goods_category_quick = request()->post("goods_category_quick", '');
			if ($goods_category_quick != '') {
				setcookie("goods_category_quick", $goods_category_quick, time() + 3600 * 24);
			}
		}
		return AjaxReturn($res);
	}
	
	/**
	 * 功能说明：查询商品属性
	 */
	public function getGoodsAttributeList()
	{
		$goods = new GoodsService();
		$condition['shop_id'] = $this->instance_id;
		$provList = $goods->getGoodsAttributeList($condition, '*', 'create_time desc');
		return $provList;
	}
	
	/**
	 * 商品添加
	 */
	public function GoodsCreateOrUpdate()
	{
		if (request()->isAjax()) {
			$data = request()->post('data/a', '');
			$goodservice = new GoodsService();
			$retval = $goodservice->editGoods($data);
			if ($retval > 0) {
				$url = __URL(Config::get('view_replace_str.APP_MAIN') . '/goods/detail?goods_id=' . $retval);
				$pay_qrcode = getQRcode($url, 'upload/goods_qrcode', 'goods_qrcode_' . $retval);
				$goodservice->goodsQRcodeMake($data['goods_id'], $pay_qrcode);
			}
			return AjaxReturn($retval);
		}
	}
	
	/**
	 * 获取省列表，商品添加时用户可以设置商品所在地
	 */
	public function getProvince()
	{
		$address = new Address();
		$province_list = $address->getProvinceList();
		return $province_list;
	}
	
	/**
	 * 获取城市列表
	 */
	public function getCity()
	{
		$address = new Address();
		$province_id = request()->post('province_id', 0);
		$city_list = $address->getCityList($province_id);
		return $city_list;
	}
	
	/**
	 * 商品分组列表
	 */
	public function goodsGroupList()
	{
		if (request()->isAjax()) {
			$goodsgroup = new GoodsGroup();
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$condition = array();
			$list = $goodsgroup->getGoodsGroupList($page_index, $page_size, $condition, "group_id desc");
			return $list;
		} else {
			return view($this->style . "Goods/goodsGroupList");
		}
	}
	
	/**
	 * 添加商品分组
	 */
	public function addGoodsGroup()
	{
		$goodsgroup = new GoodsGroup();
		if (request()->isAjax()) {
			$group_name = request()->post('group_name', '');
			$pid = request()->post('pid', 0);
			$is_visible = request()->post('is_visible', '');
			$sort = request()->post('sort', 0);
			$group_pic = request()->post('group_pic', '');
			$group_dec = request()->post('group_dec', '');
			$data = array(
				'shop_id' => $this->instance_id,
				'group_name' => $group_name,
				'pid' => $pid,
				'is_visible' => $is_visible,
				'sort' => $sort,
				'group_pic' => $group_pic,
				'group_dec' => $group_dec,
			);
			$result = $goodsgroup->editGoodsGroup($data);
			return AjaxReturn($result);
		} else {
			return view($this->style . "Goods/addGoodsGroup");
		}
	}
	
	/**
	 * 修改商品分类
	 */
	public function updateGoodsGroup()
	{
		$goodsgroup = new GoodsGroup();
		if (request()->isAjax()) {
			$group_id = request()->post('group_id', '');
			$group_name = request()->post('group_name', '');
			$pid = request()->post('pid', '');
			$is_visible = request()->post('is_visible', '');
			$sort = request()->post('sort', '');
			$group_pic = request()->post('group_pic', '');
			$group_dec = request()->post('group_dec', '');
			$data = array(
				'group_id' => $group_id,
				'shop_id' => $this->instance_id,
				'group_name' => $group_name,
				'pid' => $pid,
				'is_visible' => $is_visible,
				'sort' => $sort,
				'group_pic' => $group_pic,
				'group_dec' => $group_dec,
			);
			$result = $goodsgroup->editGoodsGroup($data);
			return AjaxReturn($result);
		} else {
			$group_id = request()->get('group_id', '');
			$result = $goodsgroup->getGoodsGroupDetail($group_id);
			$this->assign("data", $result);
			
			return view($this->style . "Goods/updateGoodsGroup");
		}
	}
	
	/**
	 * 删除商品分类
	 */
	public function deleteGoodsGroup()
	{
		$goodsgroup = new GoodsGroup();
		$group_id = request()->post('group_id', '');
		$res = $goodsgroup->deleteGoodsGroup($group_id);
		return AjaxReturn($res);
	}
	
	/**
	 * 修改 商品 分类 单个字段
	 */
	public function modifyGoodsCategoryField()
	{
		$goodscate = new GoodsCategory();
		$fieldid = request()->post('fieldid', '');
		$fieldname = request()->post('fieldname', '');
		$fieldvalue = request()->post('fieldvalue', '');
		$res = $goodscate->modifyGoodsCategoryField($fieldid, $fieldname, $fieldvalue);
		return $res;
	}
	
	/**
	 * 修改 商品 分组 单个字段
	 */
	public function modifyGoodsGroupField()
	{
		$goodsgroup = new GoodsGroup();
		$fieldid = request()->post('fieldid', '');
		$fieldname = request()->post('fieldname', '');
		$fieldvalue = request()->post('fieldvalue', '');
		$res = $goodsgroup->modifyGoodsGroupField($fieldid, $fieldname, $fieldvalue);
		return $res;
	}
	
	/**
	 * 商品上架
	 */
	public function modifyGoodsOnline()
	{
		$condition = request()->post('goods_ids', '');
		$goods_detail = new GoodsService();
		$result = $goods_detail->modifyGoodsOnline($condition);
		return AjaxReturn($result);
	}
	
	/**
	 * 商品下架
	 */
	public function modifyGoodsOffline()
	{
		$condition = request()->post('goods_ids', '');
		$goods_detail = new GoodsService();
		$result = $goods_detail->modifyGoodsOffline($condition);
		return AjaxReturn($result);
	}
	
	/**
	 * 获取筛选后的商品
	 */
	public function getSearchGoodsList()
	{
		$page_index = request()->post("page_index", 1);
		$page_size = request()->post("page_size", PAGESIZE);
		$search_text = request()->post("search_text", "");
		$is_have_sku = request()->post("is_have_sku", 1);
		$goods_type = request()->post("goods_type", "");
		$condition = array(
			"goods_name" => [
				"like",
				"%$search_text%"
			],
			"stock" => [
				"GT",
				0
			]
		);
		if ($is_have_sku == 0) {
			$condition["goods_spec_format"] = '[]';
		}
		if ($goods_type !== "") {
			$condition["goods_type"] = $goods_type;
		}
		$goods_detail = new GoodsService();
		$result = $goods_detail->getSearchGoodsList($page_index, $page_size, $condition, 'create_time desc');
		return $result;
	}
	
	/**
	 * 获取 商品分组一级分类
	 */
	public function getGoodsGroupFristLevel()
	{
		$goods_group = new GoodsGroup();
		$list = $goods_group->getGoodsGroupListByParentId(0);
		return $list;
	}
	
	/**
	 * 修改分组
	 */
	public function modifyGoodsGroup()
	{
		$goods_id = request()->post('goods_id', '');
		$goods_type = request()->post('goods_type', '');
		$goods_detail = new GoodsService();
		$result = $goods_detail->modifyGoodsGroup($goods_id, $goods_type);
		return AjaxReturn($result);
	}
	
	/**
	 * 修改推荐商品
	 */
	public function modifyGoodsRecommend()
	{
		$goods_ids = request()->post('goods_id', '');
		$recommend_type = request()->post('recommend_type', '');
		$goods_detail = new GoodsService();
		$result = $goods_detail->modifyGoodsRecommend($goods_ids, $recommend_type);
		return AjaxReturn($result);
	}
	
	/**
	 * 商品属性
	 */
	public function goodsSpecList()
	{
		$goods = new GoodsService();
		if (request()->isAjax()) {
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$condition = array();
			$condition['goods_id'] = 0; // 与商品关联的规格不进行查询
			$list = $goods->getGoodsSpecList($page_index, $page_size, $condition, 'create_time desc');
			return $list;
		}
		return view($this->style . 'Goods/goodsSpecList');
	}
	
	/**
	 * 修改商品规格单个属性值
	 */
	public function setGoodsSpecField()
	{
		$goods = new GoodsService();
		$spec_id = request()->post("id", '');
		$field_name = request()->post("name", '');
		$field_value = request()->post("value", '');
		$retval = $goods->modifyGoodsSpecField($spec_id, $field_name, $field_value);
		return AjaxReturn($retval);
	}
	
	/**
	 * 添加规格
	 */
	public function addGoodsSpec()
	{
		$goods = new GoodsService();
		if (request()->isAjax()) {
			$spec_name = request()->post('spec_name', '');
			$is_visible = request()->post('is_visible', '');
			$sort = request()->post('sort', 0);
			$show_type = request()->post('show_type', '');
			$spec_value_str = request()->post('spec_value_str', '');
			$attr_id = request()->post('attr_id', 0);
			$is_screen = request()->post('is_screen', 0);
			$spec_des = request()->post('spec_des', 0);
			$params = [
				'spec_name' => $spec_name,
				'show_type' => $show_type,
				'is_visible' => $is_visible,
				'sort' => $sort,
				'spec_value_str' => $spec_value_str,
				'attr_id' => $attr_id,
				'is_screen' => $is_screen,
				'spec_des' => $spec_des,
				'goods_id' => 0
			];
			$res = $goods->addGoodsSpec($params);
			return AjaxReturn($res);
		}
		return view($this->style . 'Goods/addGoodsSpec');
	}
	
	/**
	 * 修改规格
	 */
	public function updateGoodsSpec()
	{
		$goods = new GoodsService();
		$spec_id = request()->get('spec_id', '');
		if (request()->isAjax()) {
			$spec_id = request()->post('spec_id', '');
			$spec_name = request()->post('spec_name', '');
			$is_visible = request()->post('is_visible', '');
			$show_type = request()->post('show_type', '');
			$sort = request()->post('sort', '');
			$spec_value_str = request()->post('spec_value_str', '');
			$is_screen = request()->post('is_screen', 0);
			$spec_des = request()->post('spec_des', 0);
			$params = [
				'spec_id' => $spec_id,
				'spec_name' => $spec_name,
				'show_type' => $show_type,
				'is_visible' => $is_visible,
				'sort' => $sort,
				'spec_value_str' => $spec_value_str,
				'is_screen' => $is_screen,
				'spec_des' => $spec_des,
				'goods_id' => 0
			];
			$res = $goods->updateGoodsSpec($params);
			return AjaxReturn($res);
		}
		$detail = $goods->getGoodsSpecDetail($spec_id);
		$this->assign('info', $detail);
		return view($this->style . 'Goods/updateGoodsSpec');
	}
	
	/**
	 * 修改商品规格属性
	 * 备注：编辑商品时，也用到了这个方法，公共的啊
	 */
	public function modifyGoodsSpecValueField()
	{
		$goods = new GoodsService();
		$spec_value_id = request()->post("spec_value_id", '');
		$field_name = request()->post('field_name', '');
		$field_value = request()->post('field_value', '');
		$retval = $goods->modifyGoodsSpecValueField($spec_value_id, $field_name, $field_value);
		return AjaxReturn($retval);
	}
	
	/**
	 * 删除商品规格
	 */
	public function deleteGoodsSpec()
	{
		$spec_id = request()->post('spec_id', 0);
		$goods = new GoodsService();
		$res = $goods->deleteGoodsSpec($spec_id);
		return AjaxReturn($res);
	}
	
	/**
	 * 删除商品规格属性
	 */
	public function deleteGoodsSpecValue()
	{
		$goods = new GoodsService();
		$spec_id = request()->post('spec_id', 0);
		$spec_value_id = request()->post('spec_value_id', 0);
		
		$res = $goods->deleteGoodsSpecValue($spec_id, $spec_value_id);
		return AjaxReturn($res);
	}
	
	/**
	 * 商品类型
	 */
	public function attributelist()
	{
		if (request()->isAjax()) {
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', 0);
			$search_name = request()->post("search_name", "");
			$goods_type_name = request()->post("goods_type_name", "");
			$goods_type_id = request()->post("goods_type_id", "");
			$condition = array();
			if ($search_name != "") {
				$condition["attr_name"] = array(
					[
						"like",
						"%$search_name%"
					],
					[
						'eq',
						$goods_type_name
					],
					'or'
				);
			}
			if (!empty($goods_type_id)) {
				$condition["attr_id"] = $goods_type_id;
			}
			$goods_attribute = new GoodsAttribute();
			$goodsEvaluateList = $goods_attribute->getAttributeList($page_index, $page_size, '', 'create_time desc');
			return $goodsEvaluateList;
		}
		return view($this->style . "Goods/attributelist");
	}
	
	/**
	 * 添加一条商品属性值
	 */
	public function addAttributeServiceValue()
	{
		$goods_attribute = new GoodsAttribute();
		$attr_id = request()->post('attr_id', '');
		$data = array(
			'attr_id' => $attr_id,
			'attr_value_name' => "",
			'type' => 1,
			'sort' => 0,
			'is_search' => 1,
			'value' => ""
		);
		$res = $goods_attribute->addAttributeValue($data);
		return AjaxReturn($res);
	}
	
	/**
	 * 添加商品类型
	 */
	public function addAttributeService()
	{
		if (request()->isAjax()) {
			$attr_name = request()->post('attr_name', '');
			$is_use = request()->post('is_visible', '');
			$sort = request()->post('sort', '');
			$spec_id_array = request()->post('spec_id_array', '');
			$value_string = request()->post('data_obj_str', '');
			$brand_id_array = request()->post('select_brank', '');
			$params = array(
				'value_string' => $value_string,
				'data' => [
					"attr_name" => $attr_name,
					"is_use" => $is_use,
					"spec_id_array" => $spec_id_array,
					"sort" => $sort,
					'brand_id_array' => $brand_id_array,
					"create_time" => time()
				]
			);
			$goods_attribute = new GoodsAttribute();
			$goodsAttribute = $goods_attribute->addAttribute($params);
			return AjaxReturn($goodsAttribute);
		}
		return view($this->style . 'Goods/addGoodsAttribute');
	}
	
	public function getGoodsSpecList()
	{
		$page_index = request()->post('page_index', 1);
		$page_size = request()->post('page_size', PAGESIZE);
		$condition = request()->post('condition', "");
		if (!empty($condition)) {
			$condition = json_decode($condition, true);
		}
		$goods = new GoodsService();
		$goods_spec_list = $goods->getGoodsSpecList($page_index, $page_size, $condition, 'create_time desc');
		return $goods_spec_list;
	}
	
	/**
	 * 删除一条商品类型属性
	 */
	public function deleteAttributeValue()
	{
		$goods_attribute = new GoodsAttribute();
		$attr_id = request()->post('attr_id', 0);
		$attr_value_id = request()->post('attr_value_id', 0);
		$res = $goods_attribute->deleteAttributeValue($attr_id, $attr_value_id);
		return AjaxReturn($res);
	}
	
	/**
	 * 修改商品类型
	 */
	public function updateGoodsAttribute()
	{
		$goods_attribute = new GoodsAttribute();
		$attr_id = request()->get('attr_id', '');
		if (request()->isAjax()) {
			$attr_id = request()->post('attr_id', '');
			$attr_name = request()->post('attr_name', '');
			$is_use = request()->post('is_visible', '');
			$sort = request()->post('sort', '');
			$spec_id_array = request()->post('select_box', '');
			$value_string = request()->post('data_obj_str', '');
			$brand_id_array = request()->post('select_brank', '');
			$params = array(
				'value_string' => $value_string,
				'attr_id' => $attr_id,
				'data' => [
					"attr_name" => $attr_name,
					"is_use" => $is_use,
					"spec_id_array" => $spec_id_array,
					"sort" => $sort,
					'brand_id_array' => $brand_id_array,
					"modify_time" => time()
				]
			);
			$res = $goods_attribute->updateAttribute($params);
			return AjaxReturn($res);
		}
		
		$attribute_detail = $goods_attribute->getAttributeDetail($attr_id);
		
		$this->assign('info', $attribute_detail);
		$this->assign('attr_id', $attr_id);
		
		return view($this->style . 'Goods/updateGoodsAttribute');
	}
	
	/**
	 * 修改商品类型单个属性
	 */
	public function setAttributeField()
	{
		$attr_id = request()->post("id");
		$field_name = request()->post("name");
		$field_value = request()->post("value");
		$goods_attribute = new GoodsAttribute();
		$reval = $goods_attribute->modifyAttributeField($attr_id, $field_name, $field_value);
		return AjaxReturn($reval);
	}
	
	/**
	 * 实时更新属性值
	 */
	public function modifyAttributeValue()
	{
		$goods_attribute = new GoodsAttribute();
		$attr_value_id = request()->post('attr_value_id');
		$field_name = request()->post('field_name');
		$field_value = request()->post('field_value');
		$res = $goods_attribute->modifyAttributeValue($attr_value_id, $field_name, $field_value);
		// 修改成功后修改商品属性表属性排序
		if ($res) {
			if ($field_name == "sort") {
				$res = $goods_attribute->modifyGoodsAttributeSort($attr_value_id, $field_value, $this->instance_id);
			}
		}
		return $res;
	}
	
	/**
	 * 删除商品类型
	 */
	public function deleteAttr()
	{
		$attr_id = request()->post('attr_id');
		$goods_attribute = new GoodsAttribute();
		$res = $goods_attribute->deleteAttributeService($attr_id);
		return AjaxReturn($res);
	}
	
	/**
	 * 商品评论
	 */
	public function goodscomment()
	{
		if (request()->isAjax()) {
			$page_index = request()->post('page_index');
			$page_size = request()->post('page_size');
			
			$search = request()->post('search');
			$condition['goods_name'] = array(
				'like',
				"%" . $search . "%"
			);
			
			$member_name = request()->post('member_name', '');
			$start_date = request()->post('start_date') == '' ? 0 : getTimeTurnTimeStamp(request()->post('start_date'));
			$end_date = request()->post('end_date') == '' ? 0 : getTimeTurnTimeStamp(request()->post('end_date'));
			$explain_type = request()->post('explain_type', '');
			if ($start_date != 0 && $end_date != 0) {
				$condition["addtime"] = [
					[
						">",
						$start_date
					],
					[
						"<",
						$end_date
					]
				];
			} elseif ($start_date != 0 && $end_date == 0) {
				$condition["addtime"] = [
					[
						">",
						$start_date
					]
				];
			} elseif ($start_date == 0 && $end_date != 0) {
				$condition["addtime"] = [
					[
						"<",
						$end_date
					]
				];
			}
			if ($explain_type != "") {
				$condition["explain_type"] = $explain_type;
			}
			if (!empty($member_name)) {
				$condition["member_name"] = array(
					"like",
					"%" . $member_name . "%"
				);
			}
			
			$goods = new GoodsService();
			$goodsEvaluateList = $goods->getGoodsEvaluateList($page_index, $page_size, $condition, 'addtime desc');
			return $goodsEvaluateList;
		}
		return view($this->style . "Goods/goodsComment");
	}
	
	/**
	 * 添加商品评价回复
	 */
	public function replyEvaluateAjax()
	{
		if (request()->isAjax()) {
			$id = request()->post('evaluate_id');
			$replyType = request()->post('replyType');
			$replyContent = request()->post('evaluate_reply');
			$goods = new GoodsService();
			$params = [
				'id' => $id,
				'reply_content' => $replyContent,
				'reply_type' => $replyType
			];
			$res = $goods->addGoodsEvaluateReply($params);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 设置评价的显示状态
	 */
	public function modifyEvaluateShowStatus()
	{
		if (request()->isAjax()) {
			$id = request()->post('evaluate_id');
			$goods = new GoodsService();
			$res = $goods->modifyEvaluateShowStatus($id);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 删除评价
	 */
	public function deleteEvaluateAjax()
	{
		if (request()->isAjax()) {
			$id = request()->post('evaluate_id');
			$goods = new GoodsService();
			$res = $goods->deleteEvaluate($id);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 商品规格dialog插件
	 */
	public function controlDialogSku()
	{
		$attr_id = request()->get("attr_id", 0);
		$this->assign("attr_id", $attr_id);
		return view($this->style . 'Goods/controlDialogSku');
	}
	
	/**
	 * 商品回收站列表
	 */
	public function recycleList()
	{
		if (request()->isAjax()) {
			$goodservice = new GoodsService();
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$start_date = request()->post('start_date') == '' ? 0 : getTimeTurnTimeStamp(request()->post('start_date'));
			$end_date = request()->post('end_date') == '' ? 0 : getTimeTurnTimeStamp(request()->post('end_date'));
			$goods_name = request()->post('goods_name', '');
			$category_id_1 = request()->post('category_id_1', '');
			$category_id_2 = request()->post('category_id_2', '');
			$category_id_3 = request()->post('category_id_3', '');
			if ($start_date != 0 && $end_date != 0) {
				$condition["ng.create_time"] = [
					[
						">",
						$start_date
					],
					[
						"<",
						$end_date
					]
				];
			} elseif ($start_date != 0 && $end_date == 0) {
				$condition["ng.create_time"] = [
					[
						">",
						$start_date
					]
				];
			} elseif ($start_date == 0 && $end_date != 0) {
				$condition["ng.create_time"] = [
					[
						"<",
						$end_date
					]
				];
			}
			if (!empty($goods_name)) {
				$condition["ng.goods_name"] = array(
					"like",
					"%" . $goods_name . "%"
				);
			}
			if (!empty($category_id_3)) {
				$condition["ng.category_id_3"] = $category_id_3;
			} elseif (!empty($category_id_2)) {
				$condition["ng.category_id_2"] = $category_id_2;
			} elseif (!empty($category_id_1)) {
				$condition["ng.category_id_1"] = $category_id_1;
			}
			$condition["ng.shop_id"] = $this->instance_id;
			$result = $goodservice->getGoodsDeletedList($page_index, $page_size, $condition, "ng.create_time desc");
			return $result;
		} else {
			$search_info = request()->post('search_info', '');
			$this->assign("search_info", $search_info);
			// 查找一级商品分类
			$goodsCategory = new GoodsCategory();
			$oneGoodsCategory = $goodsCategory->getGoodsCategoryListByParentId(0);
			$this->assign("oneGoodsCategory", $oneGoodsCategory);
			
			$child_menu_list = array(
				array(
					'url' => "goods/goodslist",
					'menu_name' => "出售中",
					"active" => 0
				),
				array(
					'url' => "goods/goodslist?state_type=2",
					'menu_name' => "已下架",
					'active' => 0
				),
				
				array(
					'url' => "goods/goodslist?stock_warning=1",
					'menu_name' => "库存预警",
					"active" => 0
				),
				array(
					'url' => "goods/recyclelist",
					'menu_name' => "回收站",
					"active" => 1
				)
			);
			$this->assign('child_menu_list', $child_menu_list);
			
			return view($this->style . 'Goods/recycleList');
		}
	}
	
	/**
	 * 回收站商品恢复
	 */
	public function regainGoodsDeleted()
	{
		if (request()->isAjax()) {
			$goods_ids = request()->post('goods_ids');
			$goods = new GoodsService();
			$res = $goods->regainGoodsDeleted($goods_ids);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 拷贝商品
	 */
	public function copyGoods()
	{
		$goods_id = request()->post('goods_id', '');
		$goodservice = new GoodsService();
		$res = $goodservice->copyGoodsInfo($goods_id);
		if ($res > 0) {
			$goodsId = $res;
			$url = Config::get('view_replace_str.APP_MAIN') . '/goods/detail?goods_id=' . $goodsId;
			$pay_qrcode = getQRcode($url, 'upload/goods_qrcode', 'goods_qrcode_' . $goodsId);
			$goodservice->goodsQRcodeMake($goodsId, $pay_qrcode);
		}
		return AjaxReturn($res);
	}
	
	/**
	 * 商品分类选择
	 */
	public function dialogSelectCategory()
	{
		$category_id = request()->get("category_id", 0);
		$goodsid = request()->get("goodsid", 0);
		$flag = request()->get("flag", 'category');
		// 扩展分类标签id,用户回调方法
		$box_id = request()->get("box_id", '');
		// 已选择扩展分类(用于控制重复选择)
		$category_extend_id = request()->get("category_extend_id", '');
		if (!empty($category_extend_id) && $category_id != 0) {
			$category_extend_id = explode(",", $category_extend_id);
			foreach ($category_extend_id as $k => $v) {
				if ($v == $category_id) {
					unset($category_extend_id[ $k ]);
				}
			}
			sort($category_extend_id);
			$category_extend_id = implode(',', $category_extend_id);
		}
		$this->assign("flag", $flag);
		$this->assign("goodsid", $goodsid);
		$this->assign("box_id", $box_id);
		$this->assign("category_extend_id", $category_extend_id);
		
		$goods_category = new GoodsCategory();
		$list = $goods_category->getGoodsCategoryListByParentId(0);
		$this->assign("cateGoryList", $list);
		$category_select_ids = "";
		$category_select_names = "";
		if ($category_id != 0) {
			$category_select_result = $goods_category->getParentCategory($category_id);
			$category_select_ids = $category_select_result["category_ids"];
			$category_select_names = $category_select_result["category_names"];
		}
		
		$this->assign("category_select_ids", $category_select_ids);
		$this->assign("category_select_names", $category_select_names);
		return view($this->style . 'Goods/dialogSelectCategory');
	}
	
	/**
	 * 更改商品排序
	 */
	public function modifyGoodsSort()
	{
		if (request()->isAjax()) {
			$goods_id = request()->post("goods_id", "");
			$sort = request()->post("sort", "");
			$goods = new GoodsService();
			$res = $goods->modifyGoodsSort($goods_id, $sort);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 生成商品二维码
	 */
	public function updateGoodsQrcode()
	{
		$goods_ids = request()->post('goods_id', '');
		$goods_ids = explode(',', $goods_ids);
		if (!empty($goods_ids) && is_array($goods_ids)) {
			$goods = new GoodsService();
			foreach ($goods_ids as $v) {
				$url = __URL(Config::get('view_replace_str.APP_MAIN') . '/goods/detail?goods_id=' . $v);
				try {
					$pay_qrcode = getQRcode($url, 'upload/goods_qrcode', 'goods_qrcode_' . $v);
				} catch (\Exception $e) {
					return AjaxReturn(UPLOAD_FILE_ERROR);
				}
				$result = $goods->goodsQRcodeMake($v, $pay_qrcode);
			}
		}
		return AjaxReturn($result);
	}
	
	/**
	 * 查询条件下的商品分组列表
	 */
	public function getGoodsGroupQuery()
	{
		$goodsgroup = new GoodsGroup();
		$text = request()->post("search", "");
		$condition["group_name"] = array(
			'like',
			"%{$text}%"
		);
		$list = $goodsgroup->getGoodsGroupQueryList($condition);
		return $list;
	}
	
	/**
	 * 修改商品名称或促销语
	 */
	public function ajaxEditGoodsNameOrIntroduction()
	{
		if (request()->isAjax()) {
			$goods = new GoodsService();
			$goods_id = request()->post("goods_id", "");
			$up_type = request()->post("up_type", "");
			$up_content = request()->post("up_content", "");
			$res = $goods->modifyGoodsNameOrIntroduction($goods_id, $up_type, $up_content);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 获取限时折扣商品
	 */
	public function getDialogGoodsList(){
		$goods_id_array = request()->post("goods_id_array", "");
		$type = request()->post("type", "");
		$discount_id = request()->post("discount_id","");
		// 选择指定id的商品
		if (!empty($goods_id_array)) {
			if ($type == "select") {
				$condition["goods_id"] = array(
						"not in",
						$goods_id_array
				);
			} elseif ($type == "selected") {
				$condition["goods_id"] = array(
						"in",
						$goods_id_array
				);
			}
		}
	
		$promotion = new Promotion();
		$list = $promotion->getDialogGoodsList($goods_id_array, $discount_id, $type);
		
		return $list;
	}
	
	/**
	 * 获取添加活动商品列表
	 */
	public function getSelectGoodslist()
	{
		$goods = new GoodsService();
		$page_index = request()->post("page_index", 1);
		$page_size = request()->post("page_size", PAGESIZE);
		$goods_name = request()->post('goods_name', '');
		$goods_id_array = request()->post("goods_id_array", "");
		$type = request()->post("type", "");
		$category_id_1 = request()->post('category_id_1', '');
		$category_id_2 = request()->post('category_id_2', '');
		$category_id_3 = request()->post('category_id_3', '');
		
		$data = request()->post("data");
		$data = json_decode($data, true);
		$goods_type = $data['goods_type'];
		$state = $data['state'];
		
		$condition = array();
		
		if (!empty($goods_name)) {
			$condition["goods_name"] = array(
				"like",
				"%" . $goods_name . "%"
			);
		}
		
		// 选择指定id的商品
		if (!empty($goods_id_array)) {
			if ($type == "select") {
				$condition["goods_id"] = array(
					"not in",
					$goods_id_array
				);
			} elseif ($type == "selected") {
				$condition["goods_id"] = array(
					"in",
					$goods_id_array
				);
			}
		}
		
		// 商品分类
		if (!empty($category_id_3)) {
			$condition["category_id_3"] = $category_id_3;
		} elseif (!empty($category_id_2)) {
			$condition["category_id_2"] = $category_id_2;
		} elseif (!empty($category_id_1)) {
			$condition["category_id_1"] = $category_id_1;
		}
		$list = $goods->getSelectGoodsList($page_index, $page_size, $condition, "create_time desc", "goods_id,goods_name,stock,promotion_price,price");
		return $list;
	}
	
	/**
	 * 删除已上传的视频
	 */
	function delSelectedVideo()
	{
		$src = request()->post('src');
		$res = 1;
		if (!empty($src)) {
			$res = unlink($src);
		}
		
		return $res;
	}
	
	/**
	 * 批量处理
	 */
	public function batchProcessingGoods()
	{
		if (request()->isAjax()) {
			$info = array(
				"price" => request()->post("price", 0),
				"market_price" => request()->post("market_price", 0),
				"cost_price" => request()->post("cost_price", 0),
				"stock" => request()->post("stock", 0),
				"catrgory_one" => request()->post("catrgory_one", 0),
				"catrgory_two" => request()->post("catrgory_two", 0),
				"catrgory_three" => request()->post("catrgory_three", 0),
				"brand_id" => request()->post("brand_id", 0),
				"goods_ids" => request()->post("goods_ids", "")
			);
			$goods = new GoodsService();
			$res = $goods->batchProcessingGoods($info);
			return $res;
		}
	}
	
	/**
	 * 添加虚拟商品（点卡添加）
	 */
	public function ajaxAddVirtualGoods()
	{
		if (request()->isAjax()) {
			$virtual_card_json = request()->post('virtual_card', '');
			$goods_id = request()->post('goods_id', '');
			$virtual_goods_type_id = request()->post('virtual_goods_type_id', '');
			$sku_id = request()->post('sku', '');
			$virtual_goods = new GoodsService();
			$res = $virtual_goods->addBatchVirtualCard($virtual_goods_type_id, $goods_id, $virtual_card_json, $sku_id);
			return $res;
		}
	}
	
	/**
	 * 批量添加商品分类
	 */
	public function batchAddGoodsCategory()
	{
		if (request()->isAjax()) {
			$content = request()->post("content", "");
			$goodscate = new GoodsCategory();
			$res = $goodscate->batchAddGoodsCategory($content);
			return $res;
		}
	}
	
	/**
	 * 单一获取规格数据
	 */
	public function getGoodsSpecInfoQuery()
	{
		$attr_id = request()->post("attr_id", 0);
		$goods_id = request()->post("goods_id", 0);
		$condition = array(
			"attr_id" => $attr_id
		);
		$goods = new GoodsService();
		$res = $goods->getGoodsSpecInfoQuery($condition, $goods_id);
		return $res;
	}
	
	/**
	 * 设置会员商品折扣率
	 */
	public function setMemberDiscount()
	{
		if (request()->isAjax()) {
			$goods = new GoodsService();
			$goods_ids = request()->post("goods_ids", "");
			$discount_info = request()->post("member_discount_arr", "");
			$decimal_reservation_number = request()->post("decimal_reservation_number", 2);
			$res = $goods->setMemberDiscount($goods_ids, $discount_info, $decimal_reservation_number);
			return $res;
		}
	}
	
	/**
	 * 查看会员商品折扣
	 */
	public function showMemberDiscountAjax()
	{
		if (request()->isAjax()) {
			$goods = new GoodsService();
			$goods_id = request()->post("goods_id", "");
			$list = $goods->showMemberDiscount($goods_id);
			return $list;
		}
	}
	
	/**
	 * 修改商品品牌推荐
	 */
	public function updateGoodsBrandType()
	{
		$goodsbrand = new GoodsBrand();
		$brand_recommend = request()->post("brand_recommend", "");
		$brand_id = request()->post("brand_id", "");
		$res = $goodsbrand->modifyGoodsBrandRecomend($brand_id, $brand_recommend);
		return $res;
	}
	
	/**
	 * 修改商品品牌排序
	 */
	public function updateGoodsBrandSort()
	{
		$goodsbrand = new GoodsBrand();
		$brand_id = request()->post("brand_id", "");
		$sort = request()->post("sort", "");
		$res = $goodsbrand->modifyGoodsBrandSort($brand_id, $sort);
		
		return $res;
	}
	
	public function brandName()
	{
		$brand_name = request()->post("brand_name", "");
		$goodsbrand = new GoodsBrand();
		$res = $goodsbrand->getGoodsBrandName($brand_name);
		
		return $res;
	}
	
	/**
	 * 获取商品sku列表
	 */
	public function getGoodsSkuList()
	{
		if (request()->isAjax()) {
			$goods_id = request()->post('goods_id', '');
			$goods = new GoodsService();
			$goods_sku_list = $goods->getGoodsSku($goods_id);
			return $goods_sku_list;
		}
	}
	
	/**
	 * 修改商品规格
	 */
	public function editGoodsSku()
	{
		if (request()->isAjax()) {
			$sku_data = request()->post('sku_data');
			$goods_id = request()->post('goods_id');
			$goods_sku_arr = json_decode($sku_data, true);
			$goods = new GoodsService();
			$res = $goods->updateGoodsSkuBatch($goods_sku_arr, $goods_id);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 商品sku详情列表
	 */
	public function goodsSkuDetailsList()
	{
		if (request()->isAjax()) {
			$goods_id = request()->post('goods_id', '');
			$goods = new GoodsService();
			$sku_list = $goods->getGoodsSkuDetailsList($goods_id);
			return $sku_list;
		}
	}
	
	/**
	 * 根据品牌ID获取品牌
	 */
	public function getGooodsBrandInfo(){
		
		$brand_id = request()->post("brand_id", "");
		
		$goods_brand = new GoodsBrand();		
		$brand_detail = $goods_brand->getGoodsBrandInfo($brand_id);
		
		return $brand_detail;
	}
}