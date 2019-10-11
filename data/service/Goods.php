<?php
/**
 * Goods.php
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

namespace data\service;

/**
 * 商品服务层
 */

use data\extend\WchatOauth;
use data\model\AlbumPictureModel;
use data\model\NsAttributeModel;
use data\model\NsCartModel;
use data\model\NsClickFabulousModel;
use data\model\NsConsultModel;
use data\model\NsConsultTypeModel;
use data\model\NsCouponTypeModel;
use data\model\NsGoodsAttributeDeletedModel;
use data\model\NsGoodsAttributeModel;
use data\model\NsGoodsBrowseModel;
use data\model\NsGoodsCategoryModel;
use data\model\NsGoodsDeletedModel;
use data\model\NsGoodsDeletedViewModel;
use data\model\NsGoodsEvaluateModel;
use data\model\NsGoodsGroupModel;
use data\model\NsGoodsLadderPreferentialModel;
use data\model\NsGoodsMemberDiscountModel;
use data\model\NsGoodsModel;
use data\model\NsGoodsSkuDeletedModel;
use data\model\NsGoodsSkuModel;
use data\model\NsGoodsSkuPictureDeleteModel;
use data\model\NsGoodsSkuPictureModel;
use data\model\NsGoodsSpecModel;
use data\model\NsGoodsSpecValueModel;
use data\model\NsGoodsViewModel;
use data\model\NsMemberLevelModel;
use data\model\NsMemberModel;
use data\model\NsOrderGoodsModel;
use data\model\NsOrderModel;
use data\model\NsPromotionDiscountModel;
use data\model\NsShopModel;
use data\model\NsVirtualGoodsGroupModel;
use data\model\NsVirtualGoodsModel;
use data\model\NsVirtualGoodsTypeModel;
use data\model\NsVirtualGoodsViewModel;
use data\model\UserModel;
use data\service\promotion\GoodsDiscount;
use data\service\promotion\GoodsExpress;
use data\service\promotion\GoodsMansong;
use data\service\promotion\GoodsPreference;
use think\Cache;
use think\Db;
use think\Log;
use think\Session;

class Goods extends BaseService
{
	
	private $goods;
	
	function __construct()
	{
		parent::__construct();
		$this->goods = new NsGoodsModel();
	}
	
	/***********************************************************商品开始*********************************************************/
	
	/**
	 * 添加修改商品
	 */
	public function editGoods($data)
	{
		$goods_id = $data['goods_id'];
		$sku_array = $data['skuArray'];
		
		if (!is_numeric($data['goods_type'])) {
			$goods_config = hook('getGoodsConfig', [ 'type' => $data['goods_type'] ]);
			$goods_type = arrayFilter($goods_config)[0]['id'];
			$is_virtual = arrayFilter($goods_config)[0]['is_virtual'];
		} else {
			$goods_type = $data['goods_type'];
			$goods_config = hook('getGoodsConfig', [ 'type_id' => $data['goods_type'] ]);
			$is_virtual = arrayFilter($goods_config)[0]['is_virtual'];
		}
		
		//取分类
		$goods_category = new GoodsCategory();
		$category_list = $goods_category->getGoodsCategoryId($data['category_id']);
		
		$goods_data = array(
			'goods_type' => $goods_type,
			'goods_name' => $data['goods_name'],
			'shop_id' => $this->instance_id,
			'keywords' => $data['keywords'],
			'introduction' => $data['introduction'],
			'description' => $data['description'],
			'code' => $data['code'],
			'state' => $data['state'],
			"goods_unit" => $data['goods_unit'],
			
			'category_id' => $data['category_id'],
			'category_id_1' => $category_list[0],
			'category_id_2' => $category_list[1],
			'category_id_3' => $category_list[2],
			
			'supplier_id' => $data['supplier_id'],    //供应商
			'brand_id' => $data['brand_id'],       //品牌
			'group_id_array' => $data['group_id_array'], //分组
			
			//价钱
			'market_price' => $data['market_price'],
			'price' => $data['price'],
			'promotion_price' => $data['price'],
			'cost_price' => $data['cost_price'],
			
			//积分
			'point_exchange_type' => $data['point_exchange_type'],
			'point_exchange' => $data['point_exchange'],
			'give_point' => $data['give_point'],
			'max_use_point' => $data['max_use_point'],
			'integral_give_type' => $data['integral_give_type'], //积分赠送类型 0固定值 1按比率
			
			//会员折扣
			'is_member_discount' => $data['is_member_discount'],
			
			//物流
			'shipping_fee' => $data['shipping_fee'],
			'shipping_fee_id' => $data['shipping_fee_id'],
			'goods_weight' => $data['goods_weight'],
			'goods_volume' => $data['goods_volume'],
			'shipping_fee_type' => $data['shipping_fee_type'],
			
			//库存
			'stock' => $data['stock'],
			'min_stock_alarm' => $data['min_stock_alarm'],  //库存预警
			'is_stock_visible' => $data['is_stock_visible'], //显示库存
			
			//限购
			'max_buy' => $data['max_buy'],
			'min_buy' => $data['min_buy'],
			
			//基础量
			'clicks' => $data['clicks'],
			'sales' => $data['sales'],
			'shares' => $data['shares'],
			
			//地址
			'province_id' => $data['province_id'],
			'city_id' => $data['city_id'],
			
			//图片
			'picture' => $data['picture'],
			'img_id_array' => $data['img_id_array'],
			'sku_img_array' => $data['sku_img_array'],
			'QRcode' => $data['QRcode'],
			'goods_video_address' => $data['goods_video_address'],
			
			//属性规格
			'goods_attribute_id' => $data['goods_attribute_id'],
			'goods_spec_format' => $data['goods_spec_format'],
			
			//日期
			'production_date' => strtotime($data['production_date']),
			'shelf_life' => $data['shelf_life'], //保质期
			
			//模板
			'pc_custom_template' => $data['pc_custom_template'],
			'wap_custom_template' => $data['wap_custom_template'],
			
			//预售
			'is_open_presell' => $data['is_open_presell'],
			'presell_time' => getTimeTurnTimeStamp($data['presell_time']),
			'presell_day' => $data['presell_day'],
			'presell_delivery_type' => $data['presell_delivery_type'],
			'presell_price' => $data['presell_price'],
			
			//是否为虚拟
			'is_virtual' => $is_virtual
		);
		
		$this->goods->startTrans();
		$error = 0;
		try {
			// 检查当前添加的规格集合中，是否有新增的规格、规格值
			$goods_spec_format = json_decode($data['goods_spec_format'], true);
			$spec_id_arr = array();
			$spec_value_id_arr = array();
			foreach ($goods_spec_format as $k => $v) {
				
				if ($v['spec_id'] < 0) {
					$temp_spec_id = $goods_spec_format[ $k ]['spec_id'];// 记录之前spec_id的值，后续用于替换
					$spec_params = [
						'spec_name' => $v['spec_name'],
						'show_type' => $v['value'][0]['spec_show_type'],
						'is_visible' => 1,
						'sort' => 0,
						'spec_value_str' => '',
						'attr_id' => 0,
						'is_screen' => 1,
						'spec_des' => "",
						'goods_id' => $goods_id
					];
					$goods_spec_format[ $k ]['spec_id'] = $this->addGoodsSpec($spec_params);
					
				}
				
				// 由于需要替换操作，需要先处理规格值，从里到外
				foreach ($v['value'] as $k_value => $v_value) {
					
					// 规格已经添到库中，但是规格值还没有进库，需要添加
					if ($goods_spec_format[ $k ]['value'][ $k_value ]['spec_value_id'] < 0) {
						$goods_spec_format[ $k ]['value'][ $k_value ]['spec_id'] = $goods_spec_format[ $k ]['spec_id'];
						
						// 记录之前spec_value_id的值，后续用于替换
						$temp_spec_value_id = $goods_spec_format[ $k ]['value'][ $k_value ]['spec_value_id'];
						
						// 添加规格值
						$goods_spec_value = array(
							'spec_id' => $goods_spec_format[ $k ]['value'][ $k_value ]['spec_id'],
							'spec_value_name' => $v_value['spec_value_name'],
							'spec_value_data' => $v_value['spec_value_data'],
							'is_visible' => 1,
							'sort' => 0,
							'create_time' => time()
						);
						$goods_spec_format[ $k ]['value'][ $k_value ]['spec_value_id'] = $this->addGoodsSpecValue($goods_spec_value);
						
						array_push($spec_value_id_arr, $goods_spec_format[ $k ]['value'][ $k_value ]['spec_value_id']);
						
						// 替换规格值id
						foreach ($sku_array as $sku_k => $item) {
							$attr_value_items = explode(";", $item['attr_value_items']);
							foreach ($attr_value_items as $attr_value_k => $attr_value_v) {
								$attr_value = explode(":", $attr_value_v);
								$spec_value_id = $attr_value[1];
								//匹配规格值id
								if ($spec_value_id == $temp_spec_value_id) {
									$sku_array[ $sku_k ]['attr_value_items'] = str_replace($spec_value_id, $goods_spec_format[ $k ]['value'][ $k_value ]['spec_value_id'], $sku_array[ $sku_k ]['attr_value_items']);
								}
							}
						}
						
					}
				}
				
				if ($v['spec_id'] < 0) {
					
					// 记录新增的规格id，后续用于绑定当前商品
					array_push($spec_id_arr, $goods_spec_format[ $k ]['spec_id']);
					
					// 替换规格id
					foreach ($sku_array as $sku_k => $item) {
						$attr_value_items = explode(";", $item['attr_value_items']);
						foreach ($attr_value_items as $attr_value_k => $attr_value_v) {
							$attr_value = explode(":", $attr_value_v);
							$spec_id = $attr_value[0];
							//匹配规格id
							if ($spec_id == $temp_spec_id) {
								$sku_array[ $sku_k ]['attr_value_items'] = str_replace($spec_id, $goods_spec_format[ $k ]['spec_id'], $sku_array[ $sku_k ]['attr_value_items']);
							}
						}
					}
				}
			}
			$goods_spec_format = json_encode($goods_spec_format, JSON_UNESCAPED_UNICODE);
			$goods_data['goods_spec_format'] = $goods_spec_format;
			$data['goods_spec_format'] = $goods_spec_format;
			$_SESSION['goods_spec_format'] = $goods_spec_format;
			
			if (empty($goods_id)) {
				
				$goods_data['create_time'] = time();
				$goods_data['sale_date'] = time();
				
				$this->goods->save($goods_data);
				$goods_id = $this->goods->goods_id;
				
				//添加商品记录
				$this->addUserLog($this->uid, 1, '商品', '添加商品', '添加商品:' . $goods_data['goods_name']);
				
				if (!empty($sku_array)) {
					
					foreach ($sku_array as $k => $v) {
						$res = $this->editGoodsSkuItem($this->goods->goods_id, $v);
						$sku_array[ $k ]['sku_id'] = $res;
						if (!$res) {
							$error = 1;
						}
					}
					
					// sku图片添加
					if (!empty($data['sku_picture_values'])) {
						$sku_picture_array = json_decode($data['sku_picture_values'], true);
						foreach ($sku_picture_array as $k => $v) {
							$goods_sku_pic = array(
								"shop_id" => $this->instance_id,
								"goods_id" => $goods_id,
								"spec_id" => $v["spec_id"],
								"spec_value_id" => $v["spec_value_id"],
								"sku_img_array" => $v["img_ids"],
								"create_time" => time(),
								"modify_time" => time()
							);
							$res = $this->addGoodsSkuPicture($goods_sku_pic);
							if (!$res) {
								$error = 1;
							}
						}
					}
				} else {
					
					$extend_json = isset($data['extend_json']) ? $data['extend_json'] : '';
					$extend_json = json_encode($extend_json);
					
					$goods_sku = new NsGoodsSkuModel();
					// 添加一条skuitem
					$sku_data = array(
						'goods_id' => $goods_id,
						'sku_name' => '',
						'market_price' => $data['market_price'],
						'price' => $data['price'],
						'promote_price' => $data['price'],
						'cost_price' => $data['cost_price'],
						'stock' => $data['stock'],
						'picture' => 0,
						'code' => $data['code'],
						'QRcode' => '',
						'create_date' => time(),
						'volume' => $data['goods_volume'],
						'weight' => $data['goods_weight'],
						'extend_json' => $extend_json
					);
					$res = $goods_sku->save($sku_data);
					$data['sku_id'] = $goods_sku->sku_id;
					if (!$res) {
						$error = 1;
					}
				}
			} else {
				
				$data_goods['update_time'] = time();
				$this->goods->save($goods_data, [
					'goods_id' => $goods_id
				]);
				$this->addUserLog($this->uid, 1, '商品', '修改商品', '修改商品:' . $data['goods_name']);
				
				if (!empty($sku_array)) {
					
					// 删除商品规格、以及与当前商品关联的规格、规格值
					$this->deleteSkuItemAndGoodsSpec($goods_id, $sku_array);
					
					foreach ($sku_array as $k => $v) {
						$res = $this->editGoodsSkuItem($goods_id, $v);
						$sku_array[ $k ]['sku_id'] = $res;
						if (!$res) {
							$error = 1;
						}
					}
					// 修改时先删除原来的规格图片
					$this->deleteGoodsSkuPicture([
						"goods_id" => $goods_id
					]);
					
					// sku图片添加
					if (!empty($data['sku_picture_values'])) {
						$sku_picture_array = json_decode($data['sku_picture_values'], true);
						foreach ($sku_picture_array as $k => $v) {
							$goods_sku_pic = array(
								"shop_id" => $this->instance_id,
								"goods_id" => $goods_id,
								"spec_id" => $v["spec_id"],
								"spec_value_id" => $v["spec_value_id"],
								"sku_img_array" => $v["img_ids"],
								"create_time" => time(),
								"modify_time" => time()
							);
							$res = $this->addGoodsSkuPicture($goods_sku_pic);
							if (!$res) {
								$error = 1;
							}
						}
					}
				} else {
					
					$extend_json = isset($data['extend_json']) ? $data['extend_json'] : '';
					$extend_json = json_encode($extend_json);
					
					$sku_data = array(
						'goods_id' => $goods_id,
						'sku_name' => '',
						'market_price' => $data['market_price'],
						'price' => $data['price'],
						'promote_price' => $data['price'],
						'cost_price' => $data['cost_price'],
						'stock' => $data['stock'],
						'picture' => 0,
						'code' => $data['code'],
						'QRcode' => '',
						'update_date' => time(),
						'volume' => $data['goods_volume'],
						'weight' => $data['goods_weight'],
						'extend_json' => $extend_json
					);
					
					$goods_sku = new NsGoodsSkuModel();
					$edit_goods_sku_info = $goods_sku->getInfo([ 'goods_id' => $goods_id, 'attr_value_items' => '' ]);
					if (!empty($edit_goods_sku_info)) {
						$goods_sku->save($sku_data, [ 'goods_id' => $goods_id ]);
					} else {
						$goods_sku->destroy([ 'goods_id' => $goods_id ]);
						$goods_sku->save($sku_data);
					}
					
				}
				$this->modifyGoodsPromotionPrice($goods_id);
			}
			
			// 将新增的规格与当前商品进行关联
			if (count($spec_id_arr) > 0) {
				$spec_id_arr = implode(",", $spec_id_arr);
				$ns_goods_spec_model = new NsGoodsSpecModel();
				$ns_goods_spec_model->save([ 'goods_id' => $goods_id ], [ 'spec_id' => [ "in", $spec_id_arr ] ]);
			}
			
			//规格值
			if (count($spec_value_id_arr) > 0) {
				$spec_value_id_arr = implode(",", $spec_value_id_arr);
				$ns_goods_spec_value_model = new NsGoodsSpecValueModel();
				$ns_goods_spec_value_model->save([ 'goods_id' => $goods_id ], [ 'spec_value_id' => [ "in", $spec_value_id_arr ] ]);
			}
			
			// 每次都要重新更新商品属性
			$goods_attribute_model = new NsGoodsAttributeModel();
			$goods_attribute_model->destroy([ 'goods_id' => $goods_id ]);
			if (!empty($data['goods_attribute'])) {
				$goods_attribute_array = json_decode($data['goods_attribute'], true);
				if (!empty($goods_attribute_array[0]['attr_value_id'])) {
					foreach ($goods_attribute_array as $k => $v) {
						$goods_attribute_model = new NsGoodsAttributeModel();
						$attribute_data = array(
							'goods_id' => $goods_id,
							'shop_id' => $this->instance_id,
							'attr_value_id' => $v['attr_value_id'],
							'attr_value' => $v['attr_value'],
							'attr_value_name' => $v['attr_value_name'],
							'sort' => $v['sort'],
							'create_time' => time()
						);
						$goods_attribute_model->save($attribute_data);
					}
				}
			}
			
			// 阶梯优惠信息
			$ladder_preference_arr = explode(",", $data['ladder_preference']);
			// 先清除原有的优惠
			$nsGoodsLadderPreferential = new NsGoodsLadderPreferentialModel();
			$nsGoodsLadderPreferential->destroy([ 'goods_id' => $goods_id ]);
			
			if (!empty($ladder_preference_arr[0])) {
				foreach ($ladder_preference_arr as $v) {
					$ladder_preference_info = explode(":", $v);
					$ladder_data = array(
						"goods_id" => $goods_id,
						"quantity" => $ladder_preference_info[0],
						"price" => $ladder_preference_info[1]
					);
					$nsGoodsLadderPreferential = new NsGoodsLadderPreferentialModel();
					$nsGoodsLadderPreferential->save($ladder_data);
				}
			}
			unset($_SESSION['goods_spec_format']);
			
			//设置会员折扣
			$this->setMemberDiscount($goods_id, $data['member_discount_arr'], $data['decimal_reservation_number']);
			
			//编辑商品成功
			if (empty($data['goods_id'])) {
				$data['goods_id'] = $goods_id;
				$data['skuArray'] = $sku_array;
				hook("addGoodsSuccess", $data);
			} else {
				hook("editGoodsSuccess", $data);
			}
			
			if ($error == 0) {
				
				//编辑商品后清除商品详情缓存
				Cache::tag("niu_goods")->set("getBasisGoodsDetail" . $goods_id, null);
				Cache::tag("niu_goods")->set("getBusinessGoodsInfo_" . $goods_id, null);
				
				$this->goods->commit();
				return $goods_id;
			} else {
				$this->goods->rollback();
				return 0;
			}
		} catch (\Exception $e) {
			$this->goods->rollback();
			Log::write('编辑商品出错--' . $e->getMessage());
			return $e->getMessage();
		}
	}
	
	/**
	 * 商品下架
	 */
	public function modifyGoodsOffline($condition)
	{
		Cache::clear("niu_goods_group");
		Cache::clear("niu_goods_category_block");
		Cache::clear("niu_goods");
		$data = array(
			"state" => 0,
			'update_time' => time()
		);
		$result = $this->goods->save($data, "goods_id  in($condition)");
		if ($result > 0) {
			// 商品下架成功钩子
			hook("goodsOfflineSuccess", [
				'goods_id' => $condition
			]);
			return SUCCESS;
		} else {
			return UPDATA_FAIL;
		}
	}
	
	/**
	 * 商品上架
	 */
	public function modifyGoodsOnline($condition)
	{
		Cache::clear("niu_goods_group");
		Cache::clear("niu_goods_category_block");
		Cache::clear("niu_goods");
		$data = array(
			"state" => 1,
			'update_time' => time()
		);
		$result = $this->goods->save($data, "goods_id  in($condition)");
		if ($result > 0) {
			// 商品上架成功钩子
			hook("goodsOnlineSuccess", [
				'goods_id' => $condition
			]);
			return SUCCESS;
		} else {
			return UPDATA_FAIL;
		}
	}
	
	/**
	 * 修改 商品的 促销价格
	 */
	protected function modifyGoodsPromotionPrice($goods_id)
	{
		$discount_goods = new GoodsDiscount();
		$goods = new NsGoodsModel();
		$goods_sku = new NsGoodsSkuModel();
		$discount = $discount_goods->getDiscountByGoodsId($goods_id);
		if ($discount == -1) {
			// 当前商品没有参加活动
		} else {
			// 当前商品有正在进行的活动
			// 查询出商品的价格进行修改
			$goods_price = $goods->getInfo([
				'goods_id' => $goods_id
			], 'price');
			$goods->save([
				'promotion_price' => $goods_price['price'] * $discount / 10
			], [
				'goods_id' => $goods_id
			]);
			// 查询出所有的商品sku价格进行修改
			$goods_sku_list = $goods_sku->getQuery([
				'goods_id' => $goods_id
			], 'sku_id, price');
			foreach ($goods_sku_list as $k => $v) {
				$goods_sku = new NsGoodsSkuModel();
				$goods_sku->save([
					'promote_price' => $v['price'] * $discount / 10
				], [
					'sku_id' => $v['sku_id']
				]);
			}
		}
	}
	
	/**
	 * 修改商品 推荐 1=热销 2=推荐 3=新品
	 */
	public function modifyGoodsRecommend($goods_ids, $goods_type)
	{
		$goods = new NsGoodsModel();
		$goods->startTrans();
		try {
			$goods_id_array = explode(',', $goods_ids);
			$goods_type = explode(',', $goods_type);
			$data = array(
				"is_new" => $goods_type[0],
				"is_recommend" => $goods_type[1],
				"is_hot" => $goods_type[2]
			);
			foreach ($goods_id_array as $k => $v) {
				$goods = new NsGoodsModel();
				$goods->save($data, [
					'goods_id' => $v
				]);
			}
			$goods->commit();
			return 1;
		} catch (\Exception $e) {
			$goods->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 更改商品排序
	 */
	public function modifyGoodsSort($goods_id, $sort)
	{
		$goods = new NsGoodsModel();
		return $goods->save([
			'sort' => $sort
		], [
			'goods_id' => $goods_id
		]);
	}
	
	/**
	 * 修改商品点击量
	 */
	public function modifyGoodsClicks($goods_id)
	{
		$res = 0;
		$model = new NsGoodsModel();
		$info = $model->getInfo([
			'goods_id' => $goods_id
		], "clicks");
		if (!empty($info)) {
			$clicks = 0;
			if (!empty($info['clicks'])) {
				$clicks = $info['clicks'];
			}
			$clicks++;
			$res = $model->save([
				'clicks' => $clicks
			], [
				'goods_id' => $goods_id
			]);
		}
		return $res;
	}
	
	/**
	 * 修改商品分组
	 */
	public function modifyGoodsGroup($goods_id, $goods_type)
	{
		Cache::clear('niu_goods_group');
		$data = array(
			"group_id_array" => $goods_type,
			"update_time" => time()
		);
		$result = $this->goods->save($data, "goods_id  in($goods_id)");
		if ($result > 0) {
			return SUCCESS;
		} else {
			return UPDATA_FAIL;
		}
	}
	
	/**
	 * 修改商品名称或促销语
	 */
	public function modifyGoodsNameOrIntroduction($goods_id, $up_type, $up_content)
	{
		$condition = array(
			"goods_id" => $goods_id,
			"shop_id" => $this->instance_id
		);
		if ($up_type == "goods_name") {
			return $this->goods->save([
				"goods_name" => $up_content
			], $condition);
		} elseif ($up_type == "introduction") {
			return $this->goods->save([
				"introduction" => $up_content
			], $condition);
		}
	}
	
	/**
	 * 商品批量处理
	 */
	public function batchProcessingGoods($info)
	{
		if (!empty($info['goods_ids'])) {
			$goods_model = new NsGoodsModel(); // 商品主表
			$goods_sku_model = new NsGoodsSkuModel(); // 商品sku表
			// 开启事物
			$goods_model->startTrans();
			try {
				$goods_id_array = explode(',', $info['goods_ids']);
				if (count($goods_id_array) > 0) {
					foreach ($goods_id_array as $v) {
						$goods_data = array(); // 商品修改项
						if ($info['brand_id'] != 0) {
							$goods_data['brand_id'] = $info['brand_id'];
						}
						
						if ($info['catrgory_one'] > 0) {
							$goods_data['category_id_1'] = $info['catrgory_one'];
							$goods_data['category_id_2'] = $info['catrgory_two'];
							$goods_data['category_id_3'] = $info['catrgory_three'];
							if ($info['catrgory_three'] > 0) {
								$goods_data['category_id'] = $info['catrgory_three'];
							} elseif ($info['catrgory_two'] > 0) {
								$goods_data['category_id'] = $info['catrgory_two'];
							} else {
								$goods_data['category_id'] = $info['catrgory_one'];
							}
						}
						
						$condition["goods_id"] = $v;
						// 商品sku列表
						$goods_sku_list = $goods_sku_model->getQuery($condition);
						foreach ($goods_sku_list as $goods_sku) {
							$data = array(); // 商品sku修该项
							if ($info['price'] != 0) {
								$price = $goods_sku["price"] + $info['price'];
								$data['price'] = $price < 0 ? 0 : $price;
								$data['promote_price'] = $price < 0 ? 0 : $price;
							}
							if ($info['market_price'] != 0) {
								$market_price = $goods_sku["market_price"] + $info['market_price'];
								$data['market_price'] = $market_price < 0 ? 0 : $market_price;
							}
							if ($info['cost_price'] != 0) {
								$cost_price = $goods_sku["cost_price"] + $info['cost_price'];
								$data['cost_price'] = $cost_price < 0 ? 0 : $cost_price;
							}
							if ($info['stock'] != 0) {
								$stock = $goods_sku["stock"] + $info['stock'];
								$data['stock'] = $stock < 0 ? 0 : $stock;
							}
							$goods_sku_model = new NsGoodsSkuModel(); // 商品sku表
							if (count($data) > 0) {
								$goods_sku_model->save($data, [
									"sku_id" => $goods_sku['sku_id']
								]);
							}
						}
						
						$goods_data['stock'] = $goods_sku_model->getSum($condition, "stock");
						$goods_data['promotion_price'] = $goods_sku_model->getMin($condition, "price");
						$goods_data['price'] = $goods_sku_model->getMin($condition, "price");
						$goods_data['market_price'] = $goods_sku_model->getMin($condition, "market_price");
						$goods_data['cost_price'] = $goods_sku_model->getMin($condition, "cost_price");
						$goods_model = new NsGoodsModel(); // 商品主表
						if (count($goods_data) > 0) {
							$goods_model->save($goods_data, [
								"goods_id" => $v
							]);
						}
						$this->modifyGoodsPromotionPrice($v);
					}
				}
				$goods_model->commit();
				return $retval = array(
					"code" => 1,
					"message" => '操作成功'
				);
			} catch (\Exception $e) {
				$goods_model->rollback();
				return $retval = array(
					"code" => 0,
					"message" => $e->getMessage()
				);
			}
		} else {
			return $retval = array(
				"code" => 0,
				"message" => '请至少选择一件商品'
			);
		}
	}
	
	/**
	 * 删除商品
	 */
	public function deleteGoods($goods_id)
	{
		Cache::clear("niu_goods_group");
		Cache::clear("niu_goods_category_block");
		Cache::clear("niu_goods");
		$this->goods->startTrans();
		try {
			// 商品删除之前钩子
			hook("goodsDeleteBefore", [
				'goods_id' => $goods_id
			]);
			// 将商品信息添加到商品回收库中
			$this->addGoodsDeleted($goods_id);
			$res = $this->goods->destroy($goods_id);
			
			if ($res > 0) {
				$goods_id_array = explode(',', $goods_id);
				$goods_sku_model = new NsGoodsSkuModel();
				$goods_attribute_model = new NsGoodsAttributeModel();
				$goods_sku_picture = new NsGoodsSkuPictureModel();
				foreach ($goods_id_array as $k => $v) {
					// 删除商品sku
					$goods_sku_model->destroy([
						'goods_id' => $v
					]);
					// 删除商品属性
					$goods_attribute_model->destroy([
						'goods_id' => $v
					]);
					// 删除规格图片
					$goods_sku_picture->destroy([
						'goods_id' => $v
					]);
				}
			}
			$this->goods->commit();
			if ($res > 0) {
				// 商品删除成功钩子
				hook("goodsDeleteSuccess", [
					'goods_id' => $goods_id
				]);
				return SUCCESS;
			} else {
				return DELETE_FAIL;
			}
		} catch (\Exception $e) {
			$this->goods->rollback();
			return DELETE_FAIL;
		}
	}
	
	/**
	 * 获取单条商品的详细信息
	 */
	public function getGoodsDetail($goods_id)
	{
		// 查询商品主表
		$goods = new NsGoodsModel();
		$goods_detail = $goods->get($goods_id);
		if ($goods_detail == null) {
			return null;
		}
		$goods_preference = new GoodsPreference();
		if (!empty($this->uid)) {
			$member_discount = $goods_preference->getMemberLevelDiscount($this->uid);
		} else {
			$member_discount = 1;
		}
		
		$goods_detail['member_price'] = sprintf("%.2f", $member_discount * $goods_detail['price']);
		
		// sku多图数据
		$sku_picture_list = $this->getGoodsSkuPicture($goods_id);
		$goods_detail["sku_picture_list"] = $sku_picture_list;
		$goods_all_picture = array();
		foreach ($sku_picture_list as $picture_obj) {
			$spec_value_id = $picture_obj["spec_value_id"];
			$goods_all_picture[ $spec_value_id ] = $picture_obj;
		}
		
		// 查询商品分组表
		$goods_group = new NsGoodsGroupModel();
		$goods_group_list = $goods_group->all($goods_detail['group_id_array']);
		$goods_detail['goods_group_list'] = $goods_group_list;
		
		// 查询商品sku表
		$goods_sku = new NsGoodsSkuModel();
		$goods_sku_detail = $goods_sku->where([ 'goods_id' => $goods_id ])->select();
		
		foreach ($goods_sku_detail as $k => $goods_sku) {
			$goods_sku_detail[ $k ]['member_price'] = sprintf("%.2f", $goods_sku['price'] * $member_discount);
			
			$picture_model = new AlbumPictureModel();
			$sku_img_ids = $goods_sku['sku_img_array'];
			if (!empty($sku_img_ids)) {
				$picture_list = $picture_model->getQuery("pic_id in ($sku_img_ids)");
			} else {
				$picture_list = [];
			}
			$goods_sku_detail[ $k ]['picture_list'] = $picture_list;
			$goods_sku_detail[ $k ]['extend_json'] = json_decode($goods_sku_detail[ $k ]['extend_json'], true);
		}
		
		$goods_detail['sku_list'] = $goods_sku_detail;
		
		//默认数据，选择第一个
		$goods_detail['sku_id'] = $goods_sku_detail[0]['sku_id'];
		$goods_detail['price'] = $goods_sku_detail[0]['price'];
		$goods_detail['promotion_price'] = $goods_sku_detail[0]['promote_price'];
		$goods_detail['sku_name'] = $goods_sku_detail[0]['sku_name'];
		
		$spec_list = json_decode($goods_detail['goods_spec_format'], true);
		
		if (!empty($spec_list)) {
			foreach ($spec_list as $k => $v) {
				
				$spec_list[ $k ]['sort'] = 0;
				
				foreach ($v["value"] as $m => $t) {
					if (empty($t["spec_show_type"])) {
						$spec_list[ $k ]["value"][ $m ]["spec_show_type"] = 1;
					}
					
					//默认选中第一个规格
					$spec_list[ $k ]["value"][ $m ]["selected"] = ($m == 0) ? true : false;
					
					//匹配规格是否允许点击
					foreach ($goods_sku_detail as $d => $dv) {
						$value = $spec_list[ $k ]["value"][ $m ]['spec_id'] . ":" . $spec_list[ $k ]["value"][ $m ]['spec_value_id'];
						$match = strstr($dv['attr_value_items'], $value);
						if ($match) {
							$spec_list[ $k ]["value"][ $m ]['disabled'] = $dv['stock'] == 0 ? true : false;
						}
					}
					
					$picture = 0;
					$sku_img_array = $goods_all_picture[ $spec_list[ $k ]["value"][ $m ]['spec_value_id'] ];
					if (!empty($sku_img_array)) {
						$array = explode(",", $sku_img_array['sku_img_array']);
						$picture = $array[0];
					}
					// 查询SKU规格主图，没有返回0
					$spec_list[ $k ]["value"][ $m ]["picture"] = $picture;
					// $this->getGoodsSkuPictureBySpecId($goods_id, $spec_list[$k]["value"][$m]['spec_id'], $spec_list[$k]["value"][$m]['spec_value_id']);
				}
			}
		}
		$goods_detail['spec_list'] = $spec_list;
		// 查询图片表
		$goods_img = new AlbumPictureModel();
		$goods_img_list = [];
		$img_temp_array = array();
		if (!empty($goods_detail['img_id_array'])) {
			
			$img_array = explode(",", $goods_detail['img_id_array']);
			$img_array = array_filter($img_array);
			
			if (!empty($img_array)) {
				$img_ids = implode(',', $img_array);
				$goods_img_list = Db::query("select * from sys_album_picture where pic_id in(" . $img_ids . ") order by instr('," . $img_ids . ",',CONCAT(',',pic_id,',')) ");
				foreach ($img_array as $k => $v) {
					if (!empty($goods_img_list)) {
						foreach ($goods_img_list as $t => $m) {
							if ($m["pic_id"] == $v) {
								$img_temp_array[] = $m;
							}
						}
					}
				}
			}
			
		}
		
		$goods_picture = $goods_img->get($goods_detail['picture']);
		$goods_detail["img_temp_array"] = $img_temp_array;
		$goods_detail['img_list'] = $goods_img_list;
		$goods_detail['picture_detail'] = $goods_picture;
		// 查询分类名称
		$goods_category = new GoodsCategory();
		$category_name = $goods_category->getGoodsCategoryName($goods_detail['category_id_1'], $goods_detail['category_id_2'], $goods_detail['category_id_3']);
		$goods_detail['category_name'] = $category_name;
		// 扩展分类
		$extend_category_array = array();
		if (!empty($goods_detail['extend_category_id'])) {
			$extend_category_ids = $goods_detail['extend_category_id'];
			$extend_category_id_1s = $goods_detail['extend_category_id_1'];
			$extend_category_id_2s = $goods_detail['extend_category_id_2'];
			$extend_category_id_3s = $goods_detail['extend_category_id_3'];
			$extend_category_id_str = explode(",", $extend_category_ids);
			$extend_category_id_1s_str = explode(",", $extend_category_id_1s);
			$extend_category_id_2s_str = explode(",", $extend_category_id_2s);
			$extend_category_id_3s_str = explode(",", $extend_category_id_3s);
			foreach ($extend_category_id_str as $k => $v) {
				$extend_category_name = $goods_category->getGoodsCategoryName($extend_category_id_1s_str[ $k ], $extend_category_id_2s_str[ $k ], $extend_category_id_3s_str[ $k ]);
				$extend_category_array[] = array(
					"extend_category_name" => $extend_category_name,
					"extend_category_id" => $v,
					"extend_category_id_1" => $extend_category_id_1s_str[ $k ],
					"extend_category_id_2" => $extend_category_id_2s_str[ $k ],
					"extend_category_id_3" => $extend_category_id_3s_str[ $k ]
				);
			}
		}
		$goods_detail['extend_category_name'] = "";
		$goods_detail['extend_category'] = $extend_category_array;
		
		// 查询商品类型相关信息
		if ($goods_detail['goods_attribute_id'] != 0) {
			$attribute_model = new NsAttributeModel();
			$attribute_info = $attribute_model->getInfo([
				'attr_id' => $goods_detail['goods_attribute_id']
			], 'attr_name');
			$goods_detail['goods_attribute_name'] = $attribute_info['attr_name'];
			$goods_attribute_model = new NsGoodsAttributeModel();
			$goods_attribute_list = $goods_attribute_model->getQuery([
				'goods_id' => $goods_id
			], '*', 'sort');
			
			$goods_detail['goods_attribute_list'] = $goods_attribute_list;
		} else {
			$goods_detail['goods_attribute_name'] = '';
			$goods_detail['goods_attribute_list'] = array();
		}
		// 查询商品单品活动信息
		$goods_preference = new GoodsPreference();
		$goods_promotion_info = $goods_preference->getGoodsPromote($goods_id);
		if (!empty($goods_promotion_info)) {
			$goods_discount_info = new NsPromotionDiscountModel();
			$goods_detail['promotion_detail'] = $goods_discount_info->getInfo([
				'discount_id' => $goods_detail['promote_id']
			], 'start_time, end_time,discount_name');
		}
		// 判断活动内容是否为空
		if (!empty($goods_detail['promotion_detail'])) {
			$goods_detail['promotion_info'] = $goods_promotion_info;
		} else {
			$goods_detail['promotion_info'] = "";
		}
		// 查询商品满减送活动
		$goods_mansong = new GoodsMansong();
		$goods_detail['mansong_name'] = $goods_mansong->getGoodsMansongName($goods_id);
		// 查询包邮活动
		$full = new Promotion();
		$baoyou_info = $full->getPromotionFullMail();
		if ($baoyou_info['is_open'] == 1) {
			if ($baoyou_info['full_mail_money'] == 0) {
				$goods_detail['baoyou_name'] = '全場包郵';
			} else {
				$goods_detail['baoyou_name'] = '滿' . $baoyou_info['full_mail_money'] . '包郵';
			}
		} else {
			$goods_detail['baoyou_name'] = '';
		}
		$goods_express = new GoodsExpress();
		$goods_detail['shipping_fee_name'] = $goods_express->getGoodsExpressTemplate($goods_id, 1, 1, 1);
		
		$shop_model = new NsShopModel();
		$shop_name = $shop_model->getInfo(array(
			"shop_id" => $goods_detail["shop_id"]
		), "shop_name");
		$goods_detail["shop_name"] = $shop_name["shop_name"];
		// 查询商品规格图片
		$goos_sku_picture = new NsGoodsSkuPictureModel();
		$goos_sku_picture_query = $goos_sku_picture->getQuery([
			"goods_id" => $goods_id
		]);
		$album_picture = new AlbumPictureModel();
		foreach ($goos_sku_picture_query as $k => $v) {
			if ($v["sku_img_array"] != "") {
				$spec_name = '';
				$spec_value_name = '';
				foreach ($spec_list as $t => $m) {
					if ($m["spec_id"] == $v["spec_id"]) {
						foreach ($m["value"] as $c => $b) {
							if ($b["spec_value_id"] == $v["spec_value_id"]) {
								$spec_name = $b["spec_name"];
								$spec_value_name = $b["spec_value_name"];
							}
						}
					}
				}
				$goos_sku_picture_query[ $k ]["spec_name"] = $spec_name;
				$goos_sku_picture_query[ $k ]["spec_value_name"] = $spec_value_name;
				$tmp_img_array = $album_picture->getQuery([
					"pic_id" => [
						"in",
						$v["sku_img_array"]
					]
				]);
				$pic_id_array = explode(',', (string) $v["sku_img_array"]);
				$goos_sku_picture_query[ $k ]["sku_picture_query"] = array();
				$sku_picture_query_array = array();
				foreach ($pic_id_array as $t => $m) {
					foreach ($tmp_img_array as $q => $z) {
						if ($m == $z["pic_id"]) {
							$sku_picture_query_array[] = $z;
						}
					}
				}
				$goos_sku_picture_query[ $k ]["sku_picture_query"] = $sku_picture_query_array;
				// $goos_sku_picture_query[$k]["sku_picture_query"] = $album_picture->getQuery(["pic_id"=>["in",$v["sku_img_array"]]]);
			} else {
				unset($goos_sku_picture_query[ $k ]);
			}
		}
		sort($goos_sku_picture_query);
		$goods_detail["sku_picture_array"] = $goos_sku_picture_query;
		
		// 查询商品的已购数量，暂时注释这个功能
//		$orderGoods = new NsOrderGoodsModel();
//		$num = $orderGoods->getSum([
//			"goods_id" => $goods_id,
//			"buyer_id" => $this->uid,
//			"order_status" => array(
//				"neq",
//				5
//			)
//		], "num");
//		$goods_detail["purchase_num"] = $num;
		
		return $goods_detail;
	}
	
	/**
	 * 查询商品的基础信息，每次访问商品详情时，点击量都会发生变化，如果缓存了，则看不到点击量的变化。其他字段也可能会出现问题
	 */
	public function getBasisGoodsDetail($param = [])
	{
		//如果没有传入goods_id，只有sku_id，要先查询出来goods_id
		if (empty($param['goods_id']) && !empty($param['sku_id'])) {
			$goods_sku = new NsGoodsSkuModel();
			$goods_sku_info = $goods_sku->getInfo([ 'sku_id' => $param['sku_id'] ], 'goods_id');
			if (!empty($goods_sku_info)) {
				$param['goods_id'] = $goods_sku_info['goods_id'];
			}
		}
		$cache = Cache::tag("niu_goods")->get("getBasisGoodsDetail" . $param['goods_id']);
		if (empty($cache)) {
			
			// 商品的基础信息
			$goods = new NsGoodsModel();
			$goods_detail = $goods->get($param['goods_id']);
			if ($goods_detail == null) {
				return null;
			}
			
			$goods_detail['bargain_id'] = $param['bargain_id'];
			$goods_detail['group_id'] = $param['group_id'];
			
			// 查询商品sku
			$goods_sku = new NsGoodsSkuModel();
			$goods_sku_detail = $goods_sku->where([
				'goods_id' => $param['goods_id']
			])->select();
			$goods_detail = json_decode($goods_detail, true);
			
			// 查询商品标签表
			$goods_group = new NsGoodsGroupModel();
			$goods_group_list = $goods_group->getQuery([ 'group_id' => [ 'in', $goods_detail['group_id_array'] ] ], 'group_name');
			$goods_detail['goods_group_list'] = $goods_group_list;
			
			$spec_list = json_decode($goods_detail['goods_spec_format'], true);
			if (!empty($spec_list)) {
				// 排序字段
				//			$sort = array(
				//				'field' => 'sort'
				//			);
				//			$arrSort = array();
				$album = new Album();
				foreach ($spec_list as $k => $v) {
					$spec_list[ $k ]['sort'] = 0;
					
					foreach ($v["value"] as $m => $t) {
						if (empty($t["spec_show_type"])) {
							$spec_list[ $k ]["value"][ $m ]["spec_show_type"] = 1;
						}
						
						// 规格图片
						// 判断规格数组中图片路径是id还是路径
						if ($t["spec_show_type"] == 2) {
							if (is_numeric($t["spec_value_data"])) {
								$picture_detail = $album->getAlubmPictureDetail([
									"pic_id" => $t["spec_value_data"]
								]);
								if (!empty($picture_detail)) {
									$spec_list[ $k ]["value"][ $m ]["picture_id"] = $picture_detail['pic_id'];
									$spec_list[ $k ]["value"][ $m ]["spec_value_data"] = $picture_detail["pic_cover_micro"];
									$spec_list[ $k ]["value"][ $m ]["spec_value_data_big_src"] = $picture_detail["pic_cover_big"];
								} else {
									$spec_list[ $k ]["value"][ $m ]["spec_value_data"] = '';
									$spec_list[ $k ]["value"][ $m ]["spec_value_data_big_src"] = '';
									$spec_list[ $k ]["value"][ $m ]["picture_id"] = 0;
								}
							} else {
								$spec_list[ $k ]["value"][ $m ]["spec_value_data_big_src"] = $t["spec_value_data"];
								$spec_list[ $k ]["value"][ $m ]["picture_id"] = 0;
							}
						}
					}
				}
				
				// 排序字段
				//			foreach ($spec_list as $uniqid => $row) {
				//				foreach ($row as $key => $value) {
				//					$arrSort[ $key ][ $uniqid ] = $value;
				//				}
				//			}
				//          array_multisort($arrSort[$sort['field']], SORT_ASC, $spec_list);
			}
			$goods_detail['spec_list'] = $spec_list;
			
			//查询规格图片
			$picture_model = new AlbumPictureModel();
			foreach ($goods_sku_detail as $k => $v) {
				if (!empty($v['sku_img_array'])) {
					$goods_sku_detail[ $k ]['sku_img_list'] = $picture_model->getQuery([ "pic_id" => [ "in", $v['sku_img_array'] ] ]);
					if (!empty($goods_sku_detail[ $k ]['sku_img_list']) && count($goods_sku_detail[ $k ]['sku_img_list']) == 1) {
						$goods_sku_detail[ $k ]['sku_img_main'] = $goods_sku_detail[ $k ]['sku_img_list'][0];
					} else {
						$goods_sku_detail[ $k ]['sku_img_main'] = $picture_model->getInfo([ "pic_id" => $v['picture'] ]);
					}
				}
			}
			
			$goods_detail['sku_list'] = $goods_sku_detail;
			
			if (!empty($goods_detail['img_id_array'])) {
				// 查询图片表
				$goods_img_list = Db::query("select * from sys_album_picture where pic_id in(" . $goods_detail['img_id_array'] . ") order by instr('," . $goods_detail['img_id_array'] . ",',CONCAT(',',pic_id,',')) ");
				$goods_detail['goods_img_list'] = $goods_img_list;
			}
			
			// 查询分类名称
			$goods_category = new GoodsCategory();
			if (!empty($goods_detail["category_id"])) {
				$category_name = $goods_category->getCategoryParentQuery($goods_detail["category_id"]);
				$goods_detail['parent_category_name'] = $category_name;
			}
			
			// 查询商品类型相关信息
			if ($goods_detail['goods_attribute_id'] != 0) {
				$attribute_model = new NsAttributeModel();
				$attribute_info = $attribute_model->getInfo([
					'attr_id' => $goods_detail['goods_attribute_id']
				], 'attr_name');
				$goods_detail['goods_attribute_name'] = $attribute_info['attr_name'];
				$goods_attribute_model = new NsGoodsAttributeModel();
				$goods_attribute_list = $goods_attribute_model->getQuery([
					'goods_id' => $param['goods_id']
				], 'attr_id, goods_id, shop_id, attr_value_id, attr_value, attr_value_name, sort', 'sort desc');
				$goods_detail['goods_attribute_list'] = $goods_attribute_list;
			} else {
				$goods_detail['goods_attribute_name'] = '';
				$goods_detail['goods_attribute_list'] = array();
			}
			
			$goods_attribute_list = $goods_detail['goods_attribute_list'];
			$goods_attribute_list_new = array();
			foreach ($goods_attribute_list as $item) {
				$attr_value_name = '';
				foreach ($goods_attribute_list as $key => $item_v) {
					if ($item_v['attr_value_id'] == $item['attr_value_id']) {
						$attr_value_name .= $item_v['attr_value_name'] . ',';
						unset($goods_attribute_list[ $key ]);
					}
				}
				if (!empty($attr_value_name)) {
					array_push($goods_attribute_list_new, array(
						'attr_value_id' => $item['attr_value_id'],
						'attr_value' => $item['attr_value'],
						'attr_value_name' => rtrim($attr_value_name, ',')
					));
				}
			}
			
			$goods_detail['goods_attribute_list'] = $goods_attribute_list_new;
			
			if ($goods_detail['match_ratio'] == 0) {
				$goods_detail['match_ratio'] = 100;
			}
			if ($goods_detail['match_point'] == 0) {
				$goods_detail['match_point'] = 5;
			}
			// 处理小数
			$goods_detail['match_ratio'] = round($goods_detail['match_ratio'], 2);
			$goods_detail['match_point'] = round($goods_detail['match_point'], 2);
			
			Cache::tag("niu_goods")->set("getBasisGoodsDetail" . $param['goods_id'], $goods_detail);
		} else {
			$goods_detail = $cache;
		}
		
		return $this->getBusinessGoodsInfo($goods_detail, $param);
	}
	
	/**
	 * 查询商品的业务数据
	 * @param array $goods_detail 商品基础信息
	 * @param array $param 存放sku_id、活动id，不进行缓存
	 */
	public function getBusinessGoodsInfo($goods_detail, $param)
	{
		$cache = Cache::tag("niu_goods")->get("getBusinessGoodsInfo_" . $goods_detail['goods_id']);
		if (empty($cache)) {
			
			$promotion = new Promotion();
			
			// 积分抵现比率
			$integral_balance = 0; // 积分可抵金额
			$point_config = $promotion->getPointConfig();
			if ($point_config["is_open"] == 1) {
				if ($goods_detail['max_use_point'] > 0 && $point_config['convert_rate'] > 0) {
					$integral_balance = $goods_detail['max_use_point'] * $point_config['convert_rate'];
				}
			}
			
			$goods_detail['integral_balance'] = $integral_balance;
			
			// 获取当前时间
			$goods_detail['current_time'] = getCurrentTime();
			
			//阶梯优惠
			$goods_ladder_preferential_list = $this->getGoodsLadderPreferential([ 'goods_id' => $goods_detail["goods_id"] ], "quantity desc", "quantity,price");
			$goods_ladder_preferential_list = array_reverse($goods_ladder_preferential_list);
			$goods_detail['goods_ladder_preferential_list'] = $goods_ladder_preferential_list;
			
			//优惠券
			$goods_detail['goods_coupon_list'] = $this->getGoodsCoupon($goods_detail['goods_id']);
			
			//*******************************************营销活动*******************************************
			
			//营销活动详情
			$promotion_detail = [];
			$goods_detail['mansong_name'] = '';
			
			$is_arr = [
					$goods_detail["goods_id"],
					0
			];
			$goods_promotion = $promotion->getGoodsPromotionQuery([ 'goods_id' => ["in",$is_arr]]);
			if (!empty($goods_promotion)) {
				foreach ($goods_promotion as $k => $v) {
					if ($v['promotion_addon'] == "DISCOUNT") {
						//限时折扣
						$goods_discount_info = new NsPromotionDiscountModel();
						$discount_detail = $goods_discount_info->getInfo([
							'discount_id' => $v['promotion_id']
						], 'start_time, end_time,discount_name');
						if (!empty($discount_detail)) {
							$promotion_detail['discount_detail'] = $discount_detail;
						}
					} elseif ($v['promotion_addon'] == "MANJIAN") {
						//满减送
						// 查询商品满减送活动
						$goods_mansong = new GoodsMansong();
						$goods_detail['mansong_name'] = $goods_mansong->getGoodsMansongName($goods_detail["goods_id"]);
						
					} else {
						//插件内的营销活动详情，包括：NsCombopackage 组合套餐、NsBargain 砍价，NsPintuan 拼团，NsGroupBuy 团购
						$promotion_detail_addon = hook("getPromotionDetail", [ 'promotion_type' => $v['promotion_addon'], 'goods_id' => $goods_detail["goods_id"], 'bargain_id' => $param['bargain_id'], 'group_id' => $param['group_id'] ]);
						$promotion_detail_addon = array_filter($promotion_detail_addon);
						if (!empty($promotion_detail_addon)) {
							foreach ($promotion_detail_addon as $addon_k => $addon_v) {
								if ($addon_v['promotion_type'] == "NsCombopackage") {
									$promotion_detail['combo_package'] = $addon_v;
								} elseif ($addon_v['promotion_type'] == "NsPintuan") {
									$promotion_detail['pintuan'] = $addon_v;
								} elseif ($addon_v['promotion_type'] == "NsBargain") {
									$promotion_detail['bargain'] = $addon_v;
								} elseif ($addon_v['promotion_type'] == "NsGroupBuy") {
									$promotion_detail['group_buy'] = $addon_v;
									//团购活动要更新最大最小购买量
									$goods_detail['min_buy'] = $addon_v['data']['min_num'];
									$goods_detail['max_buy'] = $addon_v['data']['max_num'];
								}
							}
						}
						
					}
				}
			}
			
			$goods_detail['promotion_detail'] = $promotion_detail;
			
			//限购
			$purchase_restriction_num = $goods_detail['min_buy'] > 0 ? $goods_detail['min_buy'] : 1;
			$goods_purchase_restriction = $this->getGoodsPurchaseRestrictionForCurrentUser($goods_detail["goods_id"], $purchase_restriction_num);
			$goods_detail['goods_purchase_restriction'] = $goods_purchase_restriction;
			
			// 查询包邮活动
			$baoyou_info = $promotion->getPromotionFullMail();
			if ($baoyou_info['is_open'] == 1) {
				if ($baoyou_info['full_mail_money'] == 0) {
					$goods_detail['baoyou_name'] = '全場包郵';
				} else {
					$goods_detail['baoyou_name'] = '滿' . $baoyou_info['full_mail_money'] . '包郵';
				}
			} else {
				$goods_detail['baoyou_name'] = '';
			}
			
			$goods_express = new GoodsExpress();
			$goods_detail['shipping_fee_name'] = $goods_express->getGoodsExpressTemplate($goods_detail["goods_id"], 1, 1, 1);
			if (is_string($goods_detail['shipping_fee_name'])) {
				$shipping_fee_name_arr = array();
				array_push($shipping_fee_name_arr, array(
					'co_id' => 0,
					'company_name' => $goods_detail['shipping_fee_name'],
					'is_default' => 0,
					'express_fee' => 0
				));
				$goods_detail['shipping_fee_name'] = $shipping_fee_name_arr;
			}
			
			// 查询商品的已购数量，暂时注释这个功能
//			if (!empty($this->uid)) {
//				$orderGoods = new NsOrderGoodsModel();
//				$num = $orderGoods->getSum([
//					"goods_id" => $goods_detail["goods_id"],
//					"buyer_id" => $this->uid,
//					"order_status" => array(
//						"neq",
//						5
//					)
//				], "num");
//				$goods_detail["purchase_num"] = $num;
//			} else {
//				$goods_detail["purchase_num"] = 0;
//			}
			Cache::tag("niu_goods")->set("getBusinessGoodsInfo_" . $goods_detail['goods_id'], $goods_detail, 30);
		} else {
			$goods_detail = $cache;
		}
		
		// *********************************以下数据需要实时查询，保证数据的准确性*********************************
		// 会员价
		$goods_member_discount = 100;//默认会员折扣率为100%
		$member_decimal_reservation_number = 2;//默认保留角和分
		if (!empty($this->uid)) {
		    $ns_goods_member_discount = new NsGoodsMemberDiscountModel();
		
		    $member = new NsMemberModel();
		    $member_info = $member->getInfo([
		        'uid' => $this->uid
		    ], 'member_level');
		
		    $member_goods_discount = $ns_goods_member_discount->getInfo([ 'goods_id' => $goods_detail['goods_id'], 'level_id' => $member_info['member_level'] ], 'discount,decimal_reservation_number');
		
		    //商品会员等级折扣
		    if (!empty($member_goods_discount)) {
		        $goods_member_discount = $member_goods_discount['discount'];
		        $member_decimal_reservation_number = $member_goods_discount['decimal_reservation_number'];
		    } else {
		        $member_level_model = new NsMemberLevelModel();
		        $member_level_discount = $member_level_model->getInfo([ 'level_id' => $member_info['member_level'] ], 'goods_discount');
		        if (!empty($member_level_discount['goods_discount'])) $goods_member_discount = $member_level_discount['goods_discount'] * 100;
		    }
		}
		// 查询商品会员价
		if ($goods_member_discount == 100) {
		    foreach ($goods_detail['sku_list'] as $k => $v) {
		        $goods_detail['sku_list'][ $k ]['member_price'] = sprintf("%.2f", $v['price']);
		    }
		} else {
		    foreach ($goods_detail['sku_list'] as $k => $goods_sku) {
		        $goods_detail['sku_list'][ $k ]['member_price'] = sprintf("%.2f", round($goods_member_discount * $goods_sku['price'] / 100, $member_decimal_reservation_number));
		    }
		}
		
		$goods = new NsGoodsModel();
		$goods_info = $goods->getInfo([ 'goods_id' => $param['goods_id'] ], "collects,sales");
		$goods_detail['collects'] = $goods_info['collects'];
		$goods_detail['sales'] = $goods_info['sales'];
		$goods_detail['sku_name'] = '';
		
		//实时查询库存、价格
		$goods_sku = new NsGoodsSkuModel();
		$sku_list = $goods_sku->getQuery([ "goods_id" => $param['goods_id'] ], "stock,sku_id,promote_price,market_price");
		if (!empty($param['sku_id'])) {
			$goods_detail['sku_id'] = $param['sku_id'];
		} else {
			//默认选中第一个规格
			$goods_detail['sku_id'] = $sku_list[0]['sku_id'];
		}
		
		foreach ($sku_list as $sku_k => $sku_v) {
			foreach ($goods_detail['sku_list'] as $k => $v) {
				if ($v['sku_id'] == $sku_v['sku_id']) {
					$goods_detail['sku_list'][ $k ]['stock'] = $sku_v['stock'];
					$goods_detail['sku_list'][ $k ]['promote_price'] = $sku_v['promote_price'];
				}
				if ($goods_detail['sku_id'] == $v['sku_id']) {
					$goods_detail['member_price'] = $v['member_price'];
				}
			}
			if ($goods_detail['sku_id'] == $sku_v['sku_id']) {
				$goods_detail['stock'] = $sku_v['stock'];
				$goods_detail['promotion_price'] = $sku_v['promote_price'];
				$goods_detail['market_price'] = $sku_v['market_price'];
			}
		}
		
		//赠送积分
		if ($goods_detail['integral_give_type'] == 1 && $goods_detail['give_point'] > 0) {
			$price = $goods_detail['member_price'] > $goods_detail['promotion_price'] ? $goods_detail['member_price'] : $goods_detail['promotion_price'];
			$goods_detail['give_point'] = round($price * $goods_detail['give_point'] * 0.01);
		}
		
		$curr_sku_index = 0;//当前选中的规格下标
		$spec_list = $goods_detail['spec_list'];
		if (!empty($goods_detail['sku_id'])) {
			foreach ($spec_list as $k => $v) {
				
				foreach ($v["value"] as $m => $t) {
					
					foreach ($goods_detail['sku_list'] as $a => $b) {
						//找到当前选中的sku信息
						if ($b['sku_id'] == $goods_detail['sku_id']) {
							$curr_sku_index = $a;
							$value = $spec_list[ $k ]["value"][ $m ]['spec_id'] . ":" . $spec_list[ $k ]["value"][ $m ]['spec_value_id'];
							$spec_list[ $k ]["value"][ $m ]['selected'] = (strstr($b['attr_value_items'], $value)) ? true : false;// && $b['stock'] > 0
							//暂时不对没有库存进行限制，因为匹配容易出问题，限制住库存就行，匹配规格是否允许点击
							$spec_list[ $k ]["value"][ $m ]['disabled'] = false;//(strstr($b['attr_value_items'], $value) && $b['stock'] == 0) ? true : false;
							$goods_detail['price'] = $b['price'];
							$goods_detail['sku_name'] = $b['sku_name'];
						}
					}
				}
			}
		}
		
		$goods_detail['spec_list'] = $spec_list;
		
		//检测当前选中的规格是否存在图片集合
		if (isset($goods_detail['sku_list'][ $curr_sku_index ]['sku_img_list'])) {
			
			$goods_detail['sku_picture'] = $goods_detail['sku_list'][ $curr_sku_index ]['sku_img_main']['pic_id'];
			
			//将SKU图片合并到商品主图集合中
			$current_sku_img_list = json_encode($goods_detail['sku_list'][ $curr_sku_index ]['sku_img_list']);
			$deal_sku_img_list = json_decode($current_sku_img_list, true);
			if (!empty($goods_detail['goods_img_list'])) {
				$goods_detail['img_list'] = $this->array_unique(array_merge($deal_sku_img_list, $goods_detail['goods_img_list']));
			} else {
				$goods_detail['img_list'] = $deal_sku_img_list;
			}
		} else {
			if (!empty($goods_detail['goods_img_list'])) {
				$goods_detail['img_list'] = $goods_detail['goods_img_list'];
			}
			$goods_detail['sku_picture'] = 0;
		}
		return $goods_detail;
	}
	
	/**
	 * 获取商品可得积分
	 */
	public function getGoodsGivePoint($goods_id)
	{
		$goods = new NsGoodsModel();
		$point_info = $goods->getInfo([
			'goods_id' => $goods_id
		], 'give_point');
		return $point_info['give_point'];
	}
	
	/**
	 * 获取商品的店铺ID
	 */
	public function getGoodsShopid($goods_id)
	{
		$goods_model = new NsGoodsModel();
		$goods_info = $goods_model->getInfo([
			'goods_id' => $goods_id
		], 'shop_id');
		return $goods_info['shop_id'];
	}
	
	/**
	 * 获取商品的图片信息
	 */
	public function getGoodsImg($goods_id)
	{
		$goods_info = $this->goods->getInfo([
			'goods_id' => $goods_id
		], 'picture');
		$pic_info = array();
		if (!empty($goods_info)) {
			$picture = new AlbumPictureModel();
			$pic_info['pic_cover'] = '';
			if (!empty($goods_info['picture'])) {
				$pic_info = $picture->get($goods_info['picture']);
			}
		}
		return $pic_info;
	}
	
	/**
	 * 查询商品兑换所需积分
	 */
	public function getGoodsPointExchange($goods_id)
	{
		$goods_model = new NsGoodsModel();
		$goods_info = $goods_model->getInfo([
			'goods_id' => $goods_id
		], 'point_exchange_type,point_exchange');
		if ($goods_info['point_exchange_type'] == 0) {
			return 0;
		} else {
			return $goods_info['point_exchange'];
		}
	}
	
	/**
	 * 获取某种条件下商品数量
	 */
	public function getGoodsCount($condition)
	{
		$count = $this->goods->where($condition)->count();
		return $count;
	}
	
	/**
	 * 获取指定条件下商品列表
	 */
	public function getGoodsList($page_index = 1, $page_size = 0, $condition = [], $order = 'ng.sort desc,ng.create_time desc', $group_id = 0)
	{
		$goods_view = new NsGoodsViewModel();
		// 针对商品分类
		if (!empty($condition['ng.category_id'])) {
			$goods_category = new GoodsCategory();
			$category_list = $goods_category->getCategoryTreeList($condition['ng.category_id']);
			unset($condition['ng.category_id']);
			$query_goods_ids = "";
			$goods_list = $goods_view->getGoodsViewQueryField($condition, "ng.goods_id");
			if (!empty($goods_list) && count($goods_list) > 0) {
				foreach ($goods_list as $goods_obj) {
					if ($query_goods_ids === "") {
						$query_goods_ids = $goods_obj["goods_id"];
					} else {
						$query_goods_ids = $query_goods_ids . "," . $goods_obj["goods_id"];
					}
				}
				$condition = " ng.goods_id in (" . $query_goods_ids . ") and ( ng.category_id in (" . $category_list . "))";
			}
		}
		$goods_view = new NsGoodsViewModel();
		$list = $goods_view->getGoodsViewList($page_index, $page_size, $condition, $order);
		if (!empty($list['data'])) {
			// 用户针对商品的收藏
			foreach ($list['data'] as $k => $v) {
				$list['data'][ $k ]['is_favorite'] = 0;
				// 查询商品单品活动信息
				$goods_preference = new GoodsPreference();
				$goods_promotion_info = $goods_preference->getGoodsPromote($v['goods_id']);
				$list["data"][ $k ]['promotion_info'] = $goods_promotion_info;
				
				if ($v['point_exchange_type'] == 0 || $v['point_exchange_type'] == 2) {
					$list['data'][ $k ]['display_price'] = bl_cf($v["promotion_price"]);
				} else {
					if ($v['point_exchange_type'] == 1 && $v["promotion_price"] > 0) {
						$list['data'][ $k ]['display_price'] = bl_cf($v["promotion_price"]) . '+' . $v["point_exchange"] . '积分';
					} else {
						$list['data'][ $k ]['display_price'] = $v["point_exchange"] . '积分';
					}
				}
				
				// 查询商品标签
				$ns_goods_group = new NsGoodsGroupModel();
				$group_name = "";
				if (!empty($v['group_id_array'])) {
					$group_id_array = explode(",", $v['group_id_array']);
					
					if (empty($group_id) || !in_array($group_id, $group_id_array)) {
						$group_id = $group_id_array[0];
					}
					
					$group_info = $ns_goods_group->getInfo([
						"group_id" => $group_id
					], "group_name");
					
					if (!empty($group_info)) {
						$group_name = $group_info['group_name'];
					}
				}
				$list["data"][ $k ]['group_name'] = $group_name;
			}
		}
		return $list;
	}
	
	/**
	 * 直接查询商品列表
	 */
	public function getGoodsViewList($page_index = 1, $page_size = 0, $condition = '', $order = 'ng.sort desc')
	{
		$goods_view = new NsGoodsViewModel();
		$list = $goods_view->getGoodsViewList($page_index, $page_size, $condition, $order);
		return $list;
	}
	
	/**
	 * 排行数据查询
	 */
	public function getGoodsRankViewList($page_index = 1, $page_size = 0, $condition = '', $order = 'ng.sort desc')
	{
		$goods_model = new NsGoodsModel();
		// 针对商品分类
		$viewObj = $goods_model->alias("ng")
			->join('sys_album_picture ng_sap', 'ng_sap.pic_id = ng.picture', 'left')
			->field("ng.goods_id,ng.goods_name,ng_sap.pic_cover_mid,ng.promotion_price,ng.market_price,ng.goods_type,ng.stock,ng_sap.pic_id,ng.max_buy,ng.state,ng.is_hot,ng.is_recommend,ng.is_new,ng.sales,ng_sap.pic_cover_small");
		$queryList = $goods_model->viewPageQuery($viewObj, $page_index, $page_size, $condition, $order);
		$queryCount = $this->getGoodsQueryCount($condition);
		$list = $goods_model->setReturnList($queryList, $queryCount, $page_size);
		return $list;
	}
	
	/**
	 * 按照添加查询特定分页列表
	 */
	public function getSearchGoodsList($page_index = 1, $page_size = 0, $condition = '', $order = 'create_time desc', $field = '*')
	{
		$result = $this->goods->pageQuery($page_index, $page_size, $condition, $order, $field);
		foreach ($result['data'] as $k => $v) {
			$picture = new AlbumPictureModel();
			$pic_info = array();
			$pic_info['pic_cover'] = '';
			if (!empty($v['picture'])) {
				$pic_info = $picture->get($v['picture']);
			}
			$result['data'][ $k ]['picture_info'] = $pic_info;
			//商品类型配置
			$goods_config = hook('getGoodsConfig', [ 'type_id' => $v['goods_type'] ]);
			$result['data'][ $k ]['type_config'] = arrayFilter($goods_config)[0];
		}
		return $result;
	}
	
	/**
	 * 获取商品赠送积分
	 */
	public function getGoodsGivePointNew($goods_id, $sku_id, $num)
	{
		$give_point = 0; // 赠送积分
		$goods = new NsGoodsModel();
		$goods_preference = new GoodsPreference();
		$goods_info = $goods->getInfo([
			'goods_id' => $goods_id
		], 'give_point,integral_give_type');
		if ($goods_info['integral_give_type'] == 0) {
			$give_point = $goods_info['give_point'];
		} else {
			if ($goods_info['give_point'] > 0) {
				$sku_price = $goods_preference->getGoodsSkuPrice($sku_id);
				$sku_price = $goods_preference->getGoodsLadderPreferentialPrice($sku_id, $num, $sku_price);
				$give_point = round($sku_price * ($goods_info['give_point'] * 0.01));
			}
		}
		return $give_point;
	}
	
	/**
	 * 获取销售排行的商品
	 */
	public function getGoodsRank($condition)
	{
		$goods = new NsGoodsModel();
		$goods_list = $goods->where($condition)
			->order("real_sales desc")
			->limit(6)
			->select();
		return $goods_list;
	}
	
	/**
	 * 查询当前用户所购买的商品限购，是否能够继续购买
	 * 1.查询当前商品是否限购
	 * 2.如果该商品限购，则查询当前用户的订单项表中是否有该商品的记录
	 * @param $goods_id
	 * @param int $num
	 * @param string $flag
	 * @return array 1：允许购买，0：不允许购买
	 */
	public function getGoodsPurchaseRestrictionForCurrentUser($goods_id, $num = 0, $flag = "")
	{
		$res = array(
			"code" => 1,
			"message" => "允许购买",
			"value" => 0
		);
		$ns_goods_model = new NsGoodsModel();
		$max_buy = $ns_goods_model->getInfo([
			"goods_id" => $goods_id,
			"shop_id" => $this->instance_id
		], 'max_buy');
		
		$result = $num; // 用户购买的数量 + 购物车中的数量 + 订单交易数量不能超过商品的限购
		
		// 检测该商品是否有限购
		if (!empty($max_buy)) {
			if ($max_buy['max_buy'] > 0) {
				
				// 如果当前是订单验证，不需要查询购物车
				if ($flag != "order") {
					
					// 检测购物车中是否存在该商品
					$cart_list = $this->getCart($this->uid);
					if (!empty($cart_list)) {
						foreach ($cart_list as $k => $v) {
							if ($v['goods_id'] == $goods_id) {
								$result += $v['num'];
							}
						}
					}
				}
				if (!empty($this->uid)) {
					
					// 用户可能分开进行购买，统计当前用户购买了多少件该商品
					$ns_order_goods_model = new NsOrderGoodsModel();
					$order_goods_list = $ns_order_goods_model->getQuery([
						"goods_id" => $goods_id,
						"shop_id" => $this->instance_id,
						"buyer_id" => $this->uid
					], "order_id,num");
					if (!empty($order_goods_list)) {
						
						$ns_order_model = new NsOrderModel();
						foreach ($order_goods_list as $k => $v) {
							
							// 查询订单记录，排除已关闭的订单
							$count = $ns_order_model->getCount([
								'order_id' => $v['order_id'],
								"order_status" => [
									"neq",
									5
								]
							]);
							if ($count > 0) {
								$result += $v['num'];
							}
						}
					}
				}
				if ($result > $max_buy['max_buy']) {
					$res['code'] = 0;
					$res['message'] = "该商品每人限购" . $max_buy['max_buy'] . "件";
					$res['value'] = $result - $max_buy['max_buy']; // 还能购买的商品数量
				}
			}
		}
		
		return $res;
	}
	
	/**
	 * 添加营销活动时获取商品列表
	 */
	public function getSelectGoodsList($page_index, $page_size, $condition, $order, $field)
	{
		$ns_goods = new NsGoodsModel();
		$list = $ns_goods->pageQuery($page_index, $page_size, $condition, $order, $field);
		return $list;
	}
	
	/**
	 * 根据条件、指定数量查询商品
	 */
	public function getGoodsQueryLimit($condition, $field, $page_size = PAGESIZE, $order = "ng.sort desc,ng.goods_id desc")
	{
		$goods_model = new NsGoodsModel();
		$list = $goods_model->alias("ng")
			->join('sys_album_picture ng_sap', 'ng_sap.pic_id = ng.picture', 'left')
			->field($field)
			->where($condition)
			->order($order)
			->limit("0,$page_size")
			->select();
		return $list;
	}
	
	/**
	 * 商品表视图，不关联任何表
	 */
	public function getGoodsViewQueryField($condition, $field, $order)
	{
		$goods_model = new NsGoodsModel();
		$viewObj = $goods_model->alias('ng')->field($field);
		$list = $viewObj->where($condition)
			->order($order)
			->select();
		return $list;
	}
	
	/**
	 * 获取商品查询数量，分页用
	 */
	public function getGoodsQueryCount($condition, $where_sql = "")
	{
		$goods_model = new NsGoodsModel();
		$viewObj = $goods_model->alias('ng');
		if (!empty($where_sql)) {
			$count = $goods_model->viewCountNew($viewObj, $condition, $where_sql);
		} else {
			$count = $goods_model->viewCount($viewObj, $condition);
		}
		return $count;
	}
	
	/**
	 * 后台商品列表
	 */
	public function getBackStageGoodsList($page_index = 1, $page_size = 0, $condition = '', $order = 'ng.sort desc')
	{
		// $json = json_encode($condition);
		// $list_cache = Cache::tag("goods_service")->get("get_back_stage_goods_list" . $json . $page_index);
		// if (empty($list_cache)) {
		
		$goods_model = new NsGoodsModel();
		// 针对商品分类
		if (!empty($condition['ng.category_id'])) {
			$goods_category = new GoodsCategory();
			
			// 获取当前商品分类的子分类
			$category_list = $goods_category->getCategoryTreeList($condition['ng.category_id']);
			
			unset($condition['ng.category_id']);
			$query_goods_ids = "";
			$goods_list = $this->getGoodsViewQueryField($condition, "ng.goods_id", "");
			if (!empty($goods_list) && count($goods_list) > 0) {
				foreach ($goods_list as $goods_obj) {
					if ($query_goods_ids === "") {
						$query_goods_ids = $goods_obj["goods_id"];
					} else {
						$query_goods_ids = $query_goods_ids . "," . $goods_obj["goods_id"];
					}
				}
				$condition = " ng.goods_id in (" . $query_goods_ids . ") and ( ng.category_id in (" . $category_list . ")";
			}
		}
		
		$viewObj = $goods_model->alias("ng")
			->join('sys_album_picture ng_sap', 'ng_sap.pic_id = ng.picture', 'left')
			->field("ng.goods_id,ng.goods_name,ng.promotion_price,ng.market_price,ng.goods_type,ng.stock,ng.introduction,ng.max_buy,ng.state,ng.is_hot,ng.is_recommend,ng.is_new,ng.sales,ng.shipping_fee,ng_sap.pic_cover_micro,ng.code,ng.create_time,ng.QRcode,ng.price,ng.real_sales,ng.sort,ng.group_id_array,ng.is_virtual");
		$query_list = $goods_model->viewPageQuery($viewObj, $page_index, $page_size, $condition, $order);
		
		//关联插件信息
		foreach ($query_list as $list_k => $list_v) {
			$info_list = hook("getGoodsRelationInfo", [ "goods_info" => $list_v ]);
			$info_list = arrayFilter($info_list);
			if (!empty($info_list)) {
				foreach ($info_list as $info_k => $info_v) {
					$query_list[ $list_k ][ $info_v["key"] ] = $info_v["info"];
				}
			}
			//商品类型配置
			$goods_config = hook('getGoodsConfig', [ 'type_id' => $list_v['goods_type'] ]);
			$query_list[ $list_k ]['type_config'] = arrayFilter($goods_config)[0];
		}
		
		$queryCount = $this->getGoodsQueryCount($condition);
		$list = $goods_model->setReturnList($query_list, $queryCount, $page_size);
		// Cache::tag("goods_service")->set("get_back_stage_goods_list" . $json . $page_index, $list);
		return $list;
		// } else {
		// return $list_cache;
		// }
	}
	
	/**
	 * 优化过后的商品列表
	 */
	public function getGoodsListNew($page_index = 1, $page_size = 0, $condition = [], $order = 'ng.sort desc,ng.goods_id desc')
	{
		$goods_model = new NsGoodsModel();
		$where_sql = "";
		// 针对商品分类
		if (!empty($condition['ng.category_id'])) {
			$select_category_id = $condition['ng.category_id'];
			unset($condition['ng.category_id']);
			$category_model = new NsGoodsCategoryModel();
			$select_category_obj = $category_model->getInfo([
				"category_id" => $select_category_id
			], "level");
			$select_level = $select_category_obj["level"];
			if ($select_level == 1) {
				$where_sql = "(ng.category_id_1=$select_category_id or FIND_IN_SET( " . $select_category_id . ",ng.extend_category_id_1))";
			} elseif ($select_level == 2) {
				$where_sql = "(ng.category_id_2=$select_category_id or FIND_IN_SET( " . $select_category_id . ",ng.extend_category_id_2))";
			} elseif ($select_level == 3) {
				$where_sql = "(ng.category_id_3=$select_category_id or FIND_IN_SET( " . $select_category_id . ",ng.extend_category_id_3))";
			}
		}
		$viewObj = $goods_model->alias("ng")
			->join('sys_album_picture ng_sap', 'ng_sap.pic_id = ng.picture', 'left')
			->field("ng.goods_id,ng.goods_name,ng_sap.pic_cover_mid,ng.promotion_price,ng.market_price,ng.goods_type,ng.stock,ng_sap.pic_id,ng.max_buy,ng.state,ng.is_hot,ng.is_recommend,ng.is_new,ng.sales,ng_sap.pic_cover_small,ng.group_id_array,ng.shipping_fee,ng.point_exchange_type,ng.point_exchange,ng.is_open_presell,ng.img_id_array,ng.introduction");
		$queryList = $goods_model->viewPageQueryNew($viewObj, $page_index, $page_size, $condition, $where_sql, $order);
		$queryCount = $this->getGoodsQueryCount($condition, $where_sql);
		$list = $goods_model->setReturnList($queryList, $queryCount, $page_size);
		
		$album_picture_model = new AlbumPictureModel();
		foreach ($list['data'] as $k => $v) {
			if ($v['point_exchange_type'] == 0 || $v['point_exchange_type'] == 2) {
				$list['data'][ $k ]['display_price'] = bl_cf($v["promotion_price"]);
			} else {
				if ($v['point_exchange_type'] == 1 && $v["promotion_price"] > 0) {
					$list['data'][ $k ]['display_price'] = bl_cf($v["promotion_price"]) . '+' . $v["point_exchange"] . lang('积分');
				} else {
					$list['data'][ $k ]['display_price'] = $v["point_exchange"] . lang('积分');
				}
			}
			
			//查询商品图片集合
			if (!empty($v['img_id_array'])) {
				$img_id_array = explode(",", $v['img_id_array']);
				$img_list = array();
				foreach ($img_id_array as $ck => $cv) {
					$picture = $album_picture_model->getQuery([ 'pic_id' => $cv ], "pic_cover_big,pic_cover_mid,pic_cover_small");
					if (!empty($picture)) {
						$img_list[] = $picture[0];
					}
				}
				$list['data'][ $k ]['img_list'] = $img_list;
			}
			
			// 查询商品标签
			$ns_goods_group = new NsGoodsGroupModel();
			$group_name = "";
			if (!empty($v['group_id_array'])) {
			    $group_id_array = explode(",", $v['group_id_array']);
			    	
			    if (!empty($group_id_array[0])) {
			        $group_id = $group_id_array[0];
    			    $group_info = $ns_goods_group->getInfo([
    			        "group_id" => $group_id
    			    ], "group_name");
    			    	
    			    if (!empty($group_info['group_name'])) {
    			        $group_name = $group_info['group_name'];
    			    }
			    }
			}
			$list["data"][ $k ]['group_name'] = $group_name;
		}
		return $list;
	}
	
	/**
	 * 商品列表
	 */
	public function getRecommendGoodsList($page_index = 1, $page_size = 0, $condition = '', $order = 'ng.sort desc')
	{
		$goods_model = new NsGoodsModel();
		$viewObj = $goods_model->alias("ng")
			->join('sys_album_picture ng_sap', 'ng_sap.pic_id = ng.picture', 'left')
			->field("ng.price,ng.brand_id,ng.goods_id,ng.goods_name,ng_sap.pic_cover_mid,ng.promotion_price,ng.market_price,ng.goods_type,ng.stock,ng_sap.pic_id,ng.max_buy,ng.state,ng.is_hot,ng.is_recommend,ng.is_new,ng.sales,ng_sap.pic_cover_small,ng.group_id_array,ng.shipping_fee,ng.point_exchange_type,ng.point_exchange,ng.is_open_presell");
		$list = $goods_model->viewPageQueryNew($viewObj, $page_index, $page_size, $condition, '', $order);
		return $list;
	}
	
	/**
	 * 获取分组商品列表
	 */
	public function getGroupGoodsList($goods_group_id, $condition = [], $num = 0, $order = '')
	{
		$goods_list = array();
		$goods = new NsGoodsModel();
		$condition['state'] = 1;
		$list = $goods->getQuery($condition, '*', $order);
		foreach ($list as $k => $v) {
			$picture = new AlbumPictureModel();
			$picture_info = $picture->get($v['picture']);
			$v['picture_info'] = $picture_info;
			$group_id_array = explode(',', $v['group_id_array']);
			if (in_array($goods_group_id, $group_id_array) || $goods_group_id == 0) {
				$goods_list[] = $v;
			}
		}
		$member = new Member();
		$goods_preference = new GoodsPreference();
		foreach ($goods_list as $k => $v) {
			if (!empty($this->uid)) {
				$goods_list[ $k ]['is_favorite'] = $member->getIsMemberFavorites($this->uid, $v['goods_id'], 'goods');
			} else {
				$goods_list[ $k ]['is_favorite'] = 0;
			}
			
			$goods_sku = new NsGoodsSkuModel();
			// 获取sku列表
			$sku_list = $goods_sku->where([
				'goods_id' => $v['goods_id']
			])->select();
			$goods_list[ $k ]['sku_list'] = $sku_list;
			
			// 查询商品单品活动信息
			$goods_promotion_info = $goods_preference->getGoodsPromote($v['goods_id']);
			$goods_list[ $k ]['promotion_info'] = $goods_promotion_info;
		}
		if ($num == 0) {
			return $goods_list;
		} else {
			$count_list = count($goods_list);
			if ($count_list > $num) {
				return array_slice($goods_list, 0, $num);
			} else {
				return $goods_list;
			}
		}
	}
	
	/**
	 * 获取限时折扣的商品
	 */
	public function getDiscountGoodsList($page_index = 1, $page_size = 0, $condition = [], $order = '')
	{
		$goods_discount = new GoodsDiscount();
		$goods_list = $goods_discount->getDiscountGoodsList($page_index, $page_size, $condition, $order);
		return $goods_list;
	}
	
	/***********************************************************商品结束*********************************************************/
	
	
	/***********************************************************商品Sku*********************************************************/
	
	/**
	 * 添加商品sku列表
	 */
	private function editGoodsSkuItem($goods_id, $sku_item)
	{
		$goods_sku = new NsGoodsSkuModel();
		$sku_name = $this->createSkuName($sku_item['attr_value_items']);
		
		$condition = array(
			'goods_id' => $goods_id,
			'attr_value_items' => $sku_item['attr_value_items']
		);
		$sku_count = $goods_sku->where($condition)->find();
		
		$picture = 0;
		if (!empty($sku_item['sku_img'])) {
			$sku_img_array = explode(',', $sku_item['sku_img']);
			$picture = $sku_img_array[0];
		}
		
		$extend_json = isset($sku_item['extend_json']) ? $sku_item['extend_json'] : [];
		$extend_json = json_encode($extend_json);
		
		$data = array(
			'goods_id' => $goods_id,
			'sku_name' => $sku_name,
			'price' => $sku_item['sku_price'],
			'promote_price' => $sku_item['sku_price'],
			'market_price' => $sku_item['market_price'],
			'cost_price' => $sku_item['cost_price'],
			'stock' => isset($sku_item['stock_num']) ? $sku_item['stock_num'] : 0,
			'picture' => $picture,
			'sku_img_array' => $sku_item['sku_img'],
			'code' => $sku_item['code'],
			'QRcode' => '',
			'volume' => isset($sku_item['volume']) ? $sku_item['volume'] : 0,
			'weight' => isset($sku_item['weight']) ? $sku_item['weight'] : 0,
			'extend_json' => $extend_json
		);
		
		if (empty($sku_count)) {
			$data['create_date'] = time();
			$data['attr_value_items'] = $sku_item['attr_value_items'];
			$data['attr_value_items_format'] = $sku_item['attr_value_items'];
			
			$goods_sku->save($data);
			return $goods_sku->sku_id;
		} else {
			
			$data['update_date'] = time();
			$res = $goods_sku->save($data, [
				'sku_id' => $sku_count['sku_id']
			]);
			return $res;
		}
	}
	
	/**
	 * 组装sku name
	 */
	private function createSkuName($pvs)
	{
		$name = '';
		$pvs_array = explode(';', $pvs);
		foreach ($pvs_array as $k => $v) {
			$value = explode(':', $v);
			$prop_id = $value[0];
			$prop_value = $value[1];
			$value_name = $this->getUserSkuName($prop_value);
			$name = $name . $value_name . ' ';
		}
		return $name;
	}
	
	/**
	 * 获取用户自定义的规格值名称
	 */
	private function getUserSkuName($spec_id)
	{
		$sku_name = "";
		$goods_spec_format = $_SESSION['goods_spec_format'];
		if (!empty($goods_spec_format)) {
			$goods_spec_format = json_decode($goods_spec_format, true);
			foreach ($goods_spec_format as $spec_value) {
				foreach ($spec_value["value"] as $spec) {
					if ($spec_id == $spec['spec_value_id']) {
						$sku_name = $spec['spec_value_name'];
					}
				}
			}
		}
		return $sku_name;
	}
	
	/**
	 * 批量修改sku信息
	 */
	public function updateGoodsSkuBatch($goods_sku_arr, $goods_id)
	{
		$goods_model = new NsGoodsModel();
		$goods_model->startTrans();
		try {
			
			$goods_price = 0;
			$goods_stock = 0;
			$market_price = 0;
			//sku修改
			foreach ($goods_sku_arr as $item) {
				
				$goods_sku = new NsGoodsSkuModel();
				$data = array(
					'price' => $item['price'],
					'promote_price' => $item['price'],
					'market_price' => $item['market_price'],
					'cost_price' => $item['cost_price'],
					'stock' => $item['stock'],
					'code' => $item['code'],
					'update_date' => time()
				);
				$goods_sku->save($data, [ 'sku_id' => $item['sku_id'] ]);
				
				if ($goods_price == 0 || $goods_price > $item['price']) {
					$goods_price = $item['price'];
				}
				if ($market_price == 0 || $market_price > $item['market_price']) {
					$market_price = $item['market_price'];
				}
				$goods_stock += $item['stock'];
			}
			
			//商品表修改
			$goods_data = array(
				'price' => $goods_price,
				'promotion_price' => $goods_price,
				'market_price' => $market_price,
				'stock' => $goods_stock
			);
			$goods_model->save($goods_data, [ 'goods_id' => $goods_id ]);
			
			//编辑商品后清除商品详情缓存
			Cache::tag("niu_goods")->set("getBasisGoodsDetail" . $goods_id, null);
			Cache::tag("niu_goods")->set("getBusinessGoodsInfo_" . $goods_id, null);
			$goods_model->commit();
			return 1;
		} catch (\Exception $e) {
			$goods_model->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 删除当前商品的SKU项，以及关联的规格、规格值
	 */
	private function deleteSkuItemAndGoodsSpec($goods_id, $sku_list_array)
	{
		$sku_item_list_array = array();
		foreach ($sku_list_array as $k => $sku_item) {
			$sku_item_list_array[] = $sku_item['attr_value_items'];
		}
		$goods_spec = new NsGoodsSpecModel();
		$goods_spec_value = new NsGoodsSpecValueModel();
		
		// 当前商品的规格数组
		$spec_id_arr = array();
		
		// 当前商品的规格值数组
		$spec_value_id_arr = array();
		
		foreach ($sku_item_list_array as $k => $v) {
			$one = explode(";", $v);
			foreach ($one as $one_k => $one_v) {
				$curr_arr = explode(":", $one_v);
				$spec_id = $curr_arr[0];
				$spec_value_id = $curr_arr[1];
				array_push($spec_id_arr, $spec_id);
				array_push($spec_value_id_arr, $spec_value_id);
			}
		}
		$spec_id_arr = array_unique($spec_id_arr);
		$spec_id_arr = array_values($spec_id_arr);
		
		$spec_value_id_arr = array_unique($spec_value_id_arr);
		$spec_value_id_arr = array_values($spec_value_id_arr);
		
		// 要删除的规格id数组
		$del_spec_id_arr = array();
		
		// 要删除的规格值id数组
		$del_spec_value_id_arr = array();
		
		// 查询当前商品关联的规格列表
		$goods_spec_id_array = $goods_spec->getQuery([
			'goods_id' => $goods_id
		], "spec_id");
		
		if (!empty($goods_spec_id_array)) {
			foreach ($goods_spec_id_array as $k => $v) {
				
				// 如果不存在则加入到规格删除队列数组中...
				if (!in_array($v['spec_id'], $spec_id_arr)) {
					array_push($del_spec_id_arr, $v['spec_id']);
				}
				
				// 查询当前规格的所有规格值列表
				$goods_spec_value_id_array = $goods_spec_value->getQuery([
					'spec_id' => $v['spec_id']
				], "spec_value_id");
				
				if (!empty($goods_spec_value_id_array)) {
					
					foreach ($goods_spec_value_id_array as $k_value => $v_value) {
						
						// 如果不存在则加入到规格值删除队列数组中...
						if (!in_array($v_value['spec_value_id'], $spec_value_id_arr)) {
							array_push($del_spec_value_id_arr, $v_value['spec_value_id']);
						}
					}
				}
			}
		}
		
		// echo "要删除的规格：";//测试代码，建议保留.....
		// print_r(json_encode($del_spec_id_arr));
		
		// echo "要删除的规格值：";//测试代码，建议保留.....
		// print_r(json_encode($del_spec_value_id_arr));
		
		// 删除当前商品没有用到的规格值集合
		if (count($del_spec_value_id_arr) > 0) {
			$del_spec_value_id_arr = implode($del_spec_value_id_arr, ",");
			$goods_spec_value->destroy([
				'spec_value_id' => [
					'in',
					$del_spec_value_id_arr
				]
			]);
		}
		
		// 删除当前商品没有用到的规格集合
		if (count($del_spec_id_arr) > 0) {
			$del_spec_id_arr = implode($del_spec_id_arr, ",");
			$goods_spec->destroy([
				'spec_id' => [
					'in',
					$del_spec_id_arr
				]
			]);
		}
		$goods_sku = new NsGoodsSkuModel();
		$list = $goods_sku->where('goods_id=' . $goods_id)->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				if (!in_array($v['attr_value_items'], $sku_item_list_array)) {
					$goods_sku->destroy($v['sku_id']);
				}
			}
		}
	}
	
	/**
	 * 通过商品skuid查询goods_id
	 */
	public function getGoodsId($sku_id)
	{
		$goods_sku = new NsGoodsSkuModel();
		$sku_info = $goods_sku->getInfo([
			'sku_id' => $sku_id
		], 'goods_id');
		return $sku_info['goods_id'];
	}
	
	/**
	 * 获取单个商品的sku属性
	 */
	public function getGoodsAttribute($goods_id)
	{
		// 查询商品主表
		$goods = new NsGoodsModel();
		$goods_detail = $goods->get($goods_id);
		$spec_list = array();
		if (!empty($goods_detail) && !empty($goods_detail['goods_spec_format']) && $goods_detail['goods_spec_format'] != "[]") {
			$spec_list = json_decode($goods_detail['goods_spec_format'], true);
			if (!empty($spec_list)) {
				foreach ($spec_list as $k => $v) {
					foreach ($v["value"] as $m => $t) {
						if (empty($t["spec_show_type"])) {
							$spec_list[ $k ]["value"][ $m ]["spec_show_type"] = 1;
						}
						$spec_list[ $k ]["value"][ $m ]["picture"] = $this->getGoodsSkuPictureBySpecId($goods_id, $spec_list[ $k ]["value"][ $m ]['spec_id'], $spec_list[ $k ]["value"][ $m ]['spec_value_id']);
					}
				}
			}
		}
		return $spec_list;
	}
	
	/**
	 * 获取商品的sku信息
	 */
	public function getGoodsSku($goods_id)
	{
		$goods_sku = new NsGoodsSkuModel();
		$list = $goods_sku->getQuery([ 'goods_id' => $goods_id ]);
		return $list;
	}
	
	/**
	 * 商品规格详情列表
	 */
	public function getGoodsSkuDetailsList($goods_id)
	{
		$goods_sku_model = new NsGoodsSkuModel();
		$goods_sku_list = $goods_sku_model->getQuery([ 'goods_id' => $goods_id ]);
		$picture = new AlbumPictureModel();
		foreach ($goods_sku_list as $item) {
			if (!empty($item['picture'])) {
				$item['pic_cover'] = $picture->getInfo([ 'pic_id' => $item['picture'] ], 'pic_cover_mid')['pic_cover_mid'];
			} else {
				$item['pic_cover'] = '';
			}
		}
		return $goods_sku_list;
	}
	
	/**
	 * 查询sku多图数据
	 */
	public function getGoodsSkuPicture($goods_id)
	{
		$goods_sku = new NsGoodsSkuPictureModel();
		$sku_picture_list = $goods_sku->getQuery([
			"goods_id" => $goods_id
		]);
		$total_sku_img_array = array();
		foreach ($sku_picture_list as $k => $v) {
			$sku_img_ids = $v["sku_img_array"];
			$sku_img_array = explode(",", $sku_img_ids);
			if (!empty($total_sku_img_array)) {
				$total_sku_img_array = array_keys(array_flip($total_sku_img_array) + array_flip($sku_img_array));
			} else {
				$total_sku_img_array = $sku_img_array;
			}
		}
		$total_sku_img_ids = implode(",", $total_sku_img_array);
		$picture_model = new AlbumPictureModel();
		if (!empty($total_sku_img_ids)) {
			$picture_list = $picture_model->getQuery("pic_id in ($total_sku_img_ids)");
		} else {
			$picture_list = '';
		}
		
		foreach ($sku_picture_list as $k => $v) {
			$sku_img_ids = $v["sku_img_array"];
			$sku_img_array = explode(",", $sku_img_ids);
			$album_picture_list = array();
			foreach ($picture_list as $picture_obj) {
				$curr_pic_id = $picture_obj["pic_id"];
				if (in_array($curr_pic_id, $sku_img_array)) {
					$album_picture_list[] = $picture_obj;
				}
			}
			$sku_picture_list[ $k ]["album_picture_list"] = $album_picture_list;
		}
		return $sku_picture_list;
	}
	
	/**
	 * 根据商品id、规格id、规格值id查询
	 */
	public function getGoodsSkuPictureBySpecId($goods_id, $spec_id, $spec_value_id)
	{
		$picture = 0;
		$goods_sku = new NsGoodsSkuPictureModel();
		$sku_img_array = $goods_sku->getInfo([
			"goods_id" => $goods_id,
			"spec_id" => $spec_id,
			"spec_value_id" => $spec_value_id,
			"shop_id" => $this->instance_id
		], "sku_img_array");
		if (!empty($sku_img_array)) {
			$array = explode(",", $sku_img_array['sku_img_array']);
			$picture = $array[0];
		}
		return $picture;
	}
	
	/***********************************************************商品Sku结束*********************************************************/
	
	
	/***********************************************************商品规格、规格值、规格图片*********************************************************/
	
	//去重复，保留一个
	private function array_unique($array)
	{
		$out = array();
		foreach ($array as $key => $value) {
			if (!in_array($value, $out)) {
				$out[ $key ] = $value;
			}
		}
		return $out; //最后返回数组out
	}
	
	/**
	 * 添加 商品规格
	 */
	public function addGoodsSpec($params)
	{
		$goods_spec = new NsGoodsSpecModel();
		$goods_spec->startTrans();
		try {
			$data = array(
				'shop_id' => $this->instance_id,
				'spec_name' => $params['spec_name'],
				'show_type' => $params['show_type'],
				'is_visible' => $params['is_visible'],
				'sort' => $params['sort'],
				"is_screen" => $params['is_screen'],
				'spec_des' => $params['spec_des'],
				'create_time' => time(),
				'goods_id' => $params['goods_id']
			);
			$goods_spec->save($data);
			$spec_id = $goods_spec->spec_id;
			// 添加规格并修改上级分类关联规格
			if ($params['attr_id'] > 0) {
				$attribute = new NsAttributeModel();
				$attribute_info = $attribute->getInfo([
					"attr_id" => $params['attr_id']
				], "*");
				if ($attribute_info["spec_id_array"] == '') {
					$attribute->save([
						"spec_id_array" => $spec_id
					], [
						"attr_id" => $params['attr_id']
					]);
				} else {
					$attribute->save([
						"spec_id_array" => $attribute_info["spec_id_array"] . "," . $spec_id
					], [
						"attr_id" => $params['attr_id']
					]);
				}
			}
			$spec_value_array = explode(',', $params['spec_value_str']);
			$spec_value_array = array_filter($spec_value_array); // 去空
			$spec_value_array = array_unique($spec_value_array); // 去重复
			foreach ($spec_value_array as $k => $v) {
				$data = array(
					'spec_id' => $spec_id,
					'spec_value_name' => $v,
					'spec_value_data' => '',
					'is_visible' => 1,
					'sort' => 255,
					'create_time' => time()
				);
				if ($params['show_type'] == 2) {
					$spec_value = explode(':', $v);
					$data['spec_value_name'] = $spec_value[0];
					$data['spec_value_data'] = $spec_value[1];
					$this->addGoodsSpecValue($data);
				} else {
					$this->addGoodsSpecValue($data);
				}
			}
			$goods_spec->commit();
			$data['spec_id'] = $spec_id;
			hook("goodsSpecSaveSuccess", $data);
			return $spec_id;
		} catch (\Exception $e) {
			$goods_spec->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 修改 商品规格
	 */
	public function updateGoodsSpec($params)
	{
		$goods_spec = new NsGoodsSpecModel();
		$goods_spec->startTrans();
		try {
			$data = array(
				'shop_id' => $this->instance_id,
				'spec_name' => $params['spec_name'],
				'show_type' => $params['show_type'],
				'is_visible' => $params['is_visible'],
				'is_screen' => $params['is_screen'],
				'sort' => $params['sort'],
				'spec_des' => $params['spec_des'],
				'goods_id' => $params['goods_id']
			);
			$res = $goods_spec->save($data, [
				'spec_id' => $params['spec_id']
			]);
			// 删掉规格下的属性
			$this->deleteSpecValue([
				"spec_id" => $params['spec_id']
			]);
			if (!empty($params['spec_value_str'])) {
				$spec_value_array = explode(',', $params['spec_value_str']);
				$spec_value_array = array_filter($spec_value_array); // 去空
				$spec_value_array = array_unique($spec_value_array); // 去重复
				foreach ($spec_value_array as $k => $v) {
					$data = array(
						'spec_id' => $params['spec_id'],
						'spec_value_name' => $v,
						'spec_value_data' => '',
						'is_visible' => 1,
						'sort' => 255,
						'create_time' => time()
					);
					if ($params['show_type'] == 2) {
						$spec_value = explode(':', $v);
						$data['spec_value_name'] = $spec_value[0];
						$data['spec_value_data'] = $spec_value[1];
						$this->addGoodsSpecValue($data);
					} else {
						$this->addGoodsSpecValue($data);
					}
				}
			}
			$goods_spec->commit();
			$data['spec_id'] = $params['spec_id'];
			hook("goodsSpecSaveSuccess", $data);
			return $res;
		} catch (\Exception $e) {
			$goods_spec->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 修改商品规格单个字段
	 */
	public function modifyGoodsSpecField($spec_id, $field_name, $field_value)
	{
		$goods_spec = new NsGoodsSpecModel();
		return $goods_spec->save([
			"$field_name" => $field_value
		], [
			'spec_id' => $spec_id
		]);
	}
	
	/**
	 * 删除 商品规格
	 */
	public function deleteGoodsSpec($spec_id)
	{
		$goods_spec = new NsGoodsSpecModel();
		$goods_spec_value = new NsGoodsSpecValueModel();
		$goods_spec->startTrans();
		try {
			$spec_id_array = explode(',', $spec_id);
			foreach ($spec_id_array as $k => $v) {
				$goods_spec->destroy($v);
				$goods_spec_value->destroy([
					'spec_id' => $v
				]);
			}
			
			$goods_spec->commit();
			hook("goodsSpecDeleteSuccess", $spec_id);
			return 1;
		} catch (\Exception $e) {
			$goods_spec->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 获取 商品规格详情
	 */
	public function getGoodsSpecDetail($spec_id)
	{
		$goods_spec = new NsGoodsSpecModel();
		$goods_spec_value = new NsGoodsSpecValueModel();
		$info = $goods_spec->getInfo([
			'spec_id' => $spec_id
		], '*');
		$goods_spec_value_name = '';
		if (!empty($info)) {
			// 去除规格属性空值
			$goods_spec_value->destroy([
				'spec_id' => $info['spec_id'],
				'spec_value_name' => ''
			]);
			$spec_value_list = $goods_spec_value->getQuery([
				'spec_id' => $info['spec_id'],
				"goods_id" => 0
			]);
			foreach ($spec_value_list as $kv => $vv) {
				$goods_spec_value_name = $goods_spec_value_name . ',' . $vv['spec_value_name'];
			}
		}
		$info['spec_value_name_list'] = substr($goods_spec_value_name, 1);
		$info['spec_value_list'] = $spec_value_list;
		return $info;
	}
	
	/**
	 * 获取规格信息
	 */
	public function getGoodsSpecInfoQuery($condition, $goods_id = 0)
	{
		$condition_spec = array();
		if ($condition["attr_id"] > 0) {
			$goods_attribute_service = new GoodsAttribute();
			$goods_attribute = $goods_attribute_service->getAttributeInfo($condition);
			$condition_spec["spec_id"] = array(
				"in",
				$goods_attribute['spec_id_array']
			);
		}
		$condition_spec["is_visible"] = 1;
		$condition_spec['goods_id'] = [ 'in', '0,' . $goods_id ]; // 与商品关联的规格不进行查询
		$spec_list = $this->getGoodsSpecQuery($condition_spec, $goods_id); // 商品规格
		$list["spec_list"] = $spec_list; // 商品规格集合
		return $list;
	}
	
	/**
	 * 商品规格列表
	 */
	public function getGoodsAttributeList($condition, $field, $order)
	{
		$spec = new NsGoodsSpecModel();
		$list = $spec->getQuery($condition, $field, $order);
		return $list;
	}
	
	/**
	 * 获取所需规格
	 */
	public function getGoodsSpecQuery($condition, $goods_id = 0)
	{
		$goods_spec = new NsGoodsSpecModel();
		$goods_spec_query = $goods_spec->getQuery($condition, "*", 'sort');
		foreach ($goods_spec_query as $k => $v) {
			$goods_spec_value = new NsGoodsSpecValueModel();
			$goods_spec_value_query = $goods_spec_value->getQuery([
				"spec_id" => $v["spec_id"],
				"goods_id" => [ 'in', '0,' . $goods_id ]
			]);
			$goods_spec_query[ $k ]["values"] = $goods_spec_value_query;
		}
		return $goods_spec_query;
	}
	
	/**
	 * 获取 商品规格列表
	 */
	public function getGoodsSpecList($page_index = 1, $page_size = 0, $condition = [], $order = '', $field = '*')
	{
		$goods_spec = new NsGoodsSpecModel();
		$goods_spec_value = new NsGoodsSpecValueModel();
		$goods_spec_list = $goods_spec->pageQuery($page_index, $page_size, $condition, $order, $field);
		if (!empty($goods_spec_list['data'])) {
			foreach ($goods_spec_list['data'] as $ks => $vs) {
				$goods_spec_value_name = '';
				$spec_value_list = $goods_spec_value->getQuery([
					'spec_id' => $vs['spec_id'],
					'goods_id' => 0
				]);
				foreach ($spec_value_list as $kv => $vv) {
					$goods_spec_value_name = $goods_spec_value_name . ',' . $vv['spec_value_name'];
				}
				$goods_spec_list['data'][ $ks ]['spec_value_list'] = $spec_value_list;
				$goods_spec_value_name = $goods_spec_value_name == '' ? '' : substr($goods_spec_value_name, 1);
				$goods_spec_list['data'][ $ks ]['spec_value_name_list'] = $goods_spec_value_name;
			}
		}
		return $goods_spec_list;
	}
	
	/**
	 * 添加商品规格属性
	 */
	public function addGoodsSpecValue($data)
	{
		$goods_spec_value = new NsGoodsSpecValueModel();
		$goods_spec_value->save($data);
		return $goods_spec_value->spec_value_id;
	}
	
	/**
	 * 删除商品规格值
	 */
	public function deleteSpecValue($condition)
	{
		$goods_spec_value = new NsGoodsSpecValueModel();
		return $goods_spec_value->destroy($condition);
	}
	
	/**
	 * 修改 商品规格属性 单个字段
	 */
	public function modifyGoodsSpecValueField($spec_value_id, $field_name, $field_value)
	{
		$goods_spec_value = new NsGoodsSpecValueModel();
		return $goods_spec_value->save([
			"$field_name" => $field_value
		], [
			'spec_value_id' => $spec_value_id
		]);
	}
	
	/**
	 * 删除 商品规格属性
	 */
	public function deleteGoodsSpecValue($spec_id, $spec_value_id)
	{
		// 检测是否使用
		$res = $this->checkGoodsSpecValueIsUse($spec_id, $spec_value_id);
		// 检测规格属性数量
		$result = $this->getGoodsSpecValueCount([
			'spec_id' => $spec_id
		]);
		if ($res) {
			return -1;
		} elseif ($result == 1) {
			return -2;
		} else {
			$goods_spec_value = new NsGoodsSpecValueModel();
			return $goods_spec_value->destroy($spec_value_id);
		}
	}
	
	/**
	 * 商品规格值列表
	 */
	public function getGoodsAttributeValueList($condition, $field)
	{
		$attribute = new NsGoodsSpecValueModel();
		$list = $attribute->getQuery($condition, $field);
		return $list;
	}
	
	/**
	 * 检测 商品规格是否使用过
	 * 返回true = 使用过 或者 false = 没有使用过
	 */
	public function checkGoodsSpecIsUse($spec_id)
	{
		// 1.查询所有当前规格下，所有的商品属性，组成字符串
		$goods_spec_value = new NsGoodsSpecValueModel();
		$goods_sku = new NsGoodsSkuModel();
		$goods_sku_delete = new NsGoodsSkuDeletedModel();
		$spec_value_list = $goods_spec_value->getQuery([
			'spec_id' => $spec_id,
			'goods_id' => 0
		]);
		if (!empty($spec_value_list)) {
			$res = 0;
			foreach ($spec_value_list as $k => $v) {
				$check_str = $spec_id . ':' . $v['spec_value_id'] . ';';
				$res += $goods_sku->where(" CONCAT(attr_value_items, ';') like '%" . $check_str . "%'")->count();
				$res += $goods_sku_delete->where(" CONCAT(attr_value_items, ';') like '%" . $check_str . "%'")->count();
				if ($res > 0) {
					return true;
					break;
				}
			}
			if ($res == 0) {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * 检测 商品规格属性是否使用过
	 * 返回true = 使用过 或者 false = 没有使用过
	 */
	public function checkGoodsSpecValueIsUse($spec_id, $spec_value_id)
	{
		$check_str = $spec_id . ':' . $spec_value_id . ';';
		$goods_sku = new NsGoodsSkuModel();
		$goods_sku_delete = new NsGoodsSkuDeletedModel();
		// 商品sku
		$res = $goods_sku->where(" CONCAT(attr_value_items, ';') like '%" . $check_str . "%'")->count();
		// 商品回收站sku
		$res_delete = $goods_sku_delete->where(" CONCAT(attr_value_items, ';') like '%" . $check_str . "%'")->count();
		if (($res + $res_delete) > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 获取一定条件商品规格值 条数
	 */
	public function getGoodsSpecValueCount($condition)
	{
		$spec_value = new NsGoodsSpecValueModel();
		$count = $spec_value->where($condition)->count();
		return $count;
	}
	
	/**
	 * 判断商品属性名称是否已经存在
	 * 存在 返回 true 不存在返回 false
	 */
	public function checkGoodsSpecValueNameIsUse($spec_id, $value_name)
	{
		$goods_spec_value = new NsGoodsSpecValueModel();
		$num = $goods_spec_value->where([
			'spec_id' => $spec_id,
			'spec_value' => $value_name,
			'goods_id' => 0
		])->count();
		return $num > 0 ? true : false;
	}
	
	/**
	 * 添加商品规格关联图
	 */
	public function addGoodsSkuPicture($data)
	{
		$goods_sku_picture = new NsGoodsSkuPictureModel();
		$retval = $goods_sku_picture->save($data);
		return $retval;
	}
	
	/**
	 * 删除商品规格图片
	 */
	public function deleteGoodsSkuPicture($condition)
	{
		$goods_sku_picture = new NsGoodsSkuPictureModel();
		$retval = $goods_sku_picture->destroy($condition);
		return $retval;
	}
	
	/***********************************************************商品规格、规格值、规格图片结束*********************************************************/
	
	
	/***********************************************************购物车*********************************************************/
	
	/**
	 * 添加购物车
	 */
	public function addCart($params)
	{
		$retval = array(
			'code' => 0,
			"message" => ""
		);
		// 商品限购，判断是否允许添加到购物车
		$goods_purchase_restriction = array(
			"code" => 1,
			"message" => "添加购物车成功"
		);
		if ($params['uid'] > 0) {
			$cart = new NsCartModel();
			$condition = array(
				'buyer_id' => $params['uid'],
				'sku_id' => $params['sku_id']
			);
			
			// 查询当前用户所购买的商品限购，是否允许添加到购物车中
			$goods_purchase_restriction = $this->getGoodsPurchaseRestrictionForCurrentUser($params['goods_id'], $params['num']);
			if ($goods_purchase_restriction['code'] == 0) {
				$retval = $goods_purchase_restriction;
				return $retval;
			}
			
			$count = $cart->where($condition)->count();
			if ($count == 0 || empty($count)) {
				$data = array(
					'buyer_id' => $params['uid'],
					'shop_id' => $this->instance_id,
					'shop_name' => $params['shop_name'],
					'goods_id' => $params['goods_id'],
					'goods_name' => $params['goods_name'],
					'sku_id' => $params['sku_id'],
					'sku_name' => $params['sku_name'],
					'price' => $params['price'],
					'num' => $params['num'],
					'goods_picture' => $params['picture_id'],
					'bl_id' => $params['bl_id']
				);
				$cart->save($data);
				$retval['code'] = $cart->cart_id;
				$retval['message'] = lang("added_cart_success");
			} else {
				$cart = new NsCartModel();
				// 查询商品限购
				$goods = new NsGoodsModel();
				$get_num = $cart->getInfo($condition, 'cart_id,num');
				$max_buy = $goods->getInfo([
					'goods_id' => $params['goods_id']
				], 'max_buy');
				$new_num = $params['num'] + $get_num['num'];
				if ($max_buy['max_buy'] != 0) {
					if ($new_num > $max_buy['max_buy']) {
						$new_num = $max_buy['max_buy'];
					}
				}
				$data = array(
					'num' => $new_num
				);
				$res = $cart->save($data, $condition);
				if ($res) {
					$retval['code'] = $get_num['cart_id'];
					$retval['message'] = lang("added_cart_success");
				}
			}
		} else {
			
			// 未登录的情况下添加购物车
			$cart_array = cookie('cart_array');
			$data = array(
				'shop_id' => $this->instance_id,
				'goods_id' => $params['goods_id'],
				'sku_id' => $params['sku_id'],
				'num' => $params['num'],
				'goods_picture' => $params['picture_id']
			);
			if (!empty($cart_array)) {
				$cart_array = json_decode($cart_array, true);
				$tmp_array = array();
				foreach ($cart_array as $k => $v) {
					$tmp_array[] = $v['cart_id'];
				}
				$cart_id = max($tmp_array) + 1;
				$is_have = true;
				foreach ($cart_array as $k => $v) {
					if ($v["goods_id"] == $params['goods_id'] && $v["sku_id"] == $params['sku_id']) {
						$is_have = false;
						$cart_array[ $k ]["num"] = $data["num"] + $v["num"];
					}
				}
				
				if ($is_have) {
					$data["cart_id"] = $cart_id;
					$cart_array[] = $data;
				}
				// 检查商品限购，是否允许添加到购物车中
				$goods_purchase_restriction = $this->getGoodsPurchaseRestrictionForCurrentUser($params['goods_id'], $params['num']);
			} else {
				$data["cart_id"] = 1;
				$cart_array[] = $data;
			}
			try {
				// 商品限购了，不允许添加
				if ($goods_purchase_restriction['code'] == 0) {
					$retval = $goods_purchase_restriction;
				} else {
					$cart_array_string = json_encode($cart_array);
					cookie('cart_array', $cart_array_string, 3600);
					$retval['code'] = 1;
					$retval['message'] = lang("added_cart_success");
				}
			} catch (\Exception $e) {
				$retval['code'] = 0;
				$retval['message'] = lang("failed_to_add_cart");
			}
		}
		return $retval;
	}
	
	/**
	 * 购物车修改数量
	 */
	public function modifyCartAdjustNumber($cart_id, $num)
	{
		if ($this->uid > 0) {
			$cart = new NsCartModel();
			$data = array(
				'num' => $num
			);
			$retval = $cart->save($data, [
				'cart_id' => $cart_id
			]);
			return $retval;
		} else {
			$result = $this->modifyCookieCartNum($cart_id, $num);
			return $result;
		}
	}
	
	/**
	 * 修改cookie购物车的数量
	 */
	private function modifyCookieCartNum($cart_id, $num)
	{
		// 获取购物车
		$cart_goods_list = cookie('cart_array');
		if (empty($cart_goods_list)) {
			$cart_goods_list = array();
		} else {
			$cart_goods_list = json_decode($cart_goods_list, true);
		}
		foreach ($cart_goods_list as $k => $v) {
			if ($v["cart_id"] == $cart_id) {
				$cart_goods_list[ $k ]["num"] = $num;
			}
		}
		sort($cart_goods_list);
		try {
			cookie('cart_array', json_encode($cart_goods_list), 3600);
			return 1;
		} catch (\Exception $e) {
			return 0;
		}
	}
	
	/**
	 * 用户登录后同步购物车数据
	 */
	public function syncUserCart($uid)
	{
		$cart = new NsCartModel();
		$cart_query = $cart->getQuery([
			"buyer_id" => $uid
		]);
		// 获取购物车
		$cart_goods_list = cookie('cart_array');
		if (empty($cart_goods_list)) {
			$cart_goods_list = array();
		} else {
			$cart_goods_list = json_decode($cart_goods_list, true);
		}
		$goodsmodel = new NsGoodsModel();
		$web_site = new WebSite();
		$goods_sku = new NsGoodsSkuModel();
		$web_info = $web_site->getWebSiteInfo();
		// 遍历cookie购物车
		if (!empty($cart_goods_list)) {
			foreach ($cart_goods_list as $k => $v) {
				// 商品信息
				$goods_info = $goodsmodel->getInfo([
					'goods_id' => $v['goods_id']
				], 'picture, goods_name, price');
				// sku信息
				$sku_info = $goods_sku->getInfo([
					'sku_id' => $v['sku_id']
				], 'price, sku_name, promote_price');
				if (empty($goods_info)) {
					break;
				}
				if (empty($sku_info)) {
					break;
				}
				// 查看用户会员价
				$goods_preference = new GoodsPreference();
				if (!empty($this->uid)) {
					$member_discount = $goods_preference->getMemberLevelDiscount($uid);
				} else {
					$member_discount = 1;
				}
				$member_price = $member_discount * $sku_info['price'];
				if ($member_price > $sku_info["promote_price"]) {
					$price = $sku_info["promote_price"];
				} else {
					$price = $member_price;
				}
				
				$params = [
					'uid' => $uid,
					'shop_name' => $web_info['title'],
					'goods_id' => $v["goods_id"],
					'goods_name' => $goods_info["goods_name"],
					'sku_id' => $v["sku_id"],
					'sku_name' => $sku_info["sku_name"],
					'price' => $price,
					'num' => $v["num"],
					'picture_id' => $goods_info["picture"],
					'bl_id' => 0
				];
				
				// 判断此用户有无购物车
				if (empty($cart_query)) {
					// 获取商品sku信息
					$this->addCart($params);
				} else {
					$is_have = true;
					foreach ($cart_query as $t => $m) {
						if ($m["sku_id"] == $v["sku_id"] && $m["goods_id"] == $v["goods_id"]) {
							$is_have = false;
							$num = $m["num"] + $v["num"];
							$this->modifyCartAdjustNumber($m["cart_id"], $num);
							break;
						}
					}
					if ($is_have) {
						$this->addCart($params);
					}
				}
			}
		}
		cookie('cart_array', null);
	}
	
	/**
	 * 购物车项目删除
	 */
	public function deleteCart($cart_id_array)
	{
		if ($this->uid > 0) {
			$cart = new NsCartModel();
			$retval = $cart->destroy($cart_id_array);
			return $retval;
		} else {
			$result = $this->deleteCookieCart($cart_id_array);
			return $result;
		}
	}
	
	/**
	 * 删除购物车cookie
	 */
	private function deleteCookieCart($cart_id_array)
	{
		// 获取删除条件拼装
		$cart_id_array = trim($cart_id_array);
		if (empty($cart_id_array) && $cart_id_array != 0) {
			return 0;
		}
		// 获取购物车
		$cart_goods_list = cookie('cart_array');
		if (empty($cart_goods_list)) {
			$cart_goods_list = array();
		} else {
			$cart_goods_list = json_decode($cart_goods_list, true);
		}
		foreach ($cart_goods_list as $k => $v) {
			if (strpos((string) $cart_id_array, (string) $v["cart_id"]) !== false) {
				unset($cart_goods_list[ $k ]);
			}
		}
		if (empty($cart_goods_list)) {
			cookie('cart_array', null);
			return 1;
		} else {
			sort($cart_goods_list);
			try {
				cookie('cart_array', json_encode($cart_goods_list), 3600);
				return 1;
			} catch (\Exception $e) {
				return 0;
			}
		}
	}
	
	/**
	 * 获取购物车中项目，根据cartid
	 */
	public function getCartList($carts)
	{
		$cart = new NsCartModel();
		$cart_list = $cart->getQuery([
			'buyer_id' => $this->uid
		], '*', 'cart_id');
		$cart_array = explode(',', $carts);
		$list = array();
		foreach ($cart_list as $k => $v) {
			$goods = new NsGoodsModel();
			$goods_info = $goods->getInfo([
				'goods_id' => $v['goods_id']
			], 'max_buy,state,point_exchange_type,point_exchange,max_use_point');
			// 获取商品sku信息
			$goods_sku = new NsGoodsSkuModel();
			$sku_info = $goods_sku->getInfo([
				'sku_id' => $v['sku_id']
			], 'stock');
			if (empty($sku_info)) {
				$cart->destroy([
					'buyer_id' => $this->uid,
					'sku_id' => $v['sku_id']
				]);
				continue;
			} else {
				if ($sku_info['stock'] == 0) {
					$cart->destroy([
						'buyer_id' => $this->uid,
						'sku_id' => $v['sku_id']
					]);
					continue;
				}
			}
			
			$v['stock'] = $sku_info['stock'];
			$v['max_buy'] = $goods_info['max_buy'];
			$v['point_exchange_type'] = $goods_info['point_exchange_type'];
			$v['point_exchange'] = $goods_info['point_exchange'];
			if ($goods_info['state'] != 1) {
				$this->deleteCart($v['cart_id']);
				unset($v);
			}
			$num = $v['num'];
			if ($goods_info['max_buy'] != 0 && $goods_info['max_buy'] < $v['num']) {
				$num = $goods_info['max_buy'];
			}
			
			if ($sku_info['stock'] < $num) {
				$num = $sku_info['stock'];
			}
			if ($num != $v['num']) {
				// 更新购物车
				$this->modifyCartAdjustNumber($v['cart_id'], $sku_info['stock']);
				$v['num'] = $num;
			}
			$v["max_use_point"] = $goods_info["max_use_point"] * $num;
			// 获取阶梯优惠后的价格
			$v["price"] = $this->getGoodsLadderPreferentialInfo($v["goods_id"], $v['num'], $v['price']);
			// 获取图片信息
			$picture = new AlbumPictureModel();
			$picture_info = $picture->get($v['goods_picture']);
			$v['picture_info'] = $picture_info;
			if (in_array($v['cart_id'], $cart_array)) {
				$list[] = $v;
			}
		}
		return $list;
	}
	
	/**
	 * 获取购物车
	 */
	public function getCart($uid)
	{
		if ($uid > 0) {
			$cart = new NsCartModel();
			$cart_goods_list = $cart->getQuery([
				'buyer_id' => $uid
			], '*', 'cart_id desc');
		} else {
			$cart_goods_list = cookie('cart_array');
			if (empty($cart_goods_list)) {
				$cart_goods_list = null;
			} else {
				$cart_goods_list = json_decode($cart_goods_list, true);
			}
		}
		$goods_id_array = array();
		if (!empty($cart_goods_list)) {
			foreach ($cart_goods_list as $k => $v) {
				$goods = new NsGoodsModel();
				$goods_info = $goods->getInfo([
					'goods_id' => $v['goods_id']
				], 'max_buy,state,point_exchange_type,point_exchange,goods_name,price, picture, min_buy ');
				// 获取商品sku信息
				$goods_sku = new NsGoodsSkuModel();
				$sku_info = $goods_sku->getInfo([
					'sku_id' => $v['sku_id']
				], 'stock, price, sku_name, promote_price');
				// 将goods_id 存放到数组中
				$goods_id_array[] = $v["goods_id"];
				// 验证商品或sku是否存在,不存在则从购物车移除
				if ($uid > 0) {
					if (empty($goods_info)) {
						$cart->destroy([
							'goods_id' => $v['goods_id'],
							'buyer_id' => $uid
						]);
						unset($cart_goods_list[ $k ]);
						continue;
					}
					if (empty($sku_info)) {
						unset($cart_goods_list[ $k ]);
						$cart->destroy([
							'buyer_id' => $uid,
							'sku_id' => $v['sku_id']
						]);
						continue;
					}
				} else {
					if (empty($goods_info)) {
						unset($cart_goods_list[ $k ]);
						$this->deleteCart($v['cart_id']);
						continue;
					}
					if (empty($sku_info)) {
						unset($cart_goods_list[ $k ]);
						$this->deleteCart($v['cart_id']);
						continue;
					}
				}
				// 为cookie信息完善商品和sku信息
				if ($uid > 0) {
					// 查看用户会员价
					$goods_preference = new GoodsPreference();
					$member_discount = 1;
					if (!empty($uid)) {
						$goods_member_discount = $goods_preference->getGoodsMemberDiscount($uid, $v["goods_id"]);
						if (!empty($goods_member_discount)) {
							$member_discount = $goods_member_discount;
						} else {
							$member_discount = $goods_preference->getMemberLevelDiscount($uid);
						}
					}
					$member_price = $member_discount * $sku_info['price'];
					$member_price = $this->handleMemberPrice($v["goods_id"], $member_price);
					if ($member_price > $sku_info["promote_price"]) {
						$price = $sku_info["promote_price"];
					} else {
						$price = $member_price;
					}
					$update_data = array(
						"goods_name" => $goods_info["goods_name"],
						"sku_name" => $sku_info["sku_name"],
						"goods_picture" => $v['goods_picture'], // $goods_info["picture"],
						"price" => $price
					);
					// 更新数据
					$cart->save($update_data, [
						"cart_id" => $v["cart_id"]
					]);
					$cart_goods_list[ $k ]["price"] = $price;
					$cart_goods_list[ $k ]["goods_name"] = $goods_info["goods_name"];
					$cart_goods_list[ $k ]["sku_name"] = $sku_info["sku_name"];
					$cart_goods_list[ $k ]["goods_picture"] = $v['goods_picture']; // $goods_info["picture"];
				} else {
					$cart_goods_list[ $k ]["price"] = $sku_info["promote_price"];
					$cart_goods_list[ $k ]["goods_name"] = $goods_info["goods_name"];
					$cart_goods_list[ $k ]["sku_name"] = $sku_info["sku_name"];
					$cart_goods_list[ $k ]["goods_picture"] = $v['goods_picture']; // $goods_info["picture"];
				}
				
				$cart_goods_list[ $k ]['stock'] = $sku_info['stock'];
				$cart_goods_list[ $k ]['max_buy'] = $goods_info['max_buy'];
				$cart_goods_list[ $k ]['min_buy'] = $goods_info['min_buy'];
				$cart_goods_list[ $k ]['point_exchange_type'] = $goods_info['point_exchange_type'];
				$cart_goods_list[ $k ]['point_exchange'] = $goods_info['point_exchange'];
				
				if ($goods_info['state'] != 1) {
					unset($cart_goods_list[ $k ]);
					// 更新cookie购物车
					$this->deleteCart($v['cart_id']);
					continue;
				}
				$num = $v['num'];
				if ($goods_info['max_buy'] != 0 && $goods_info['max_buy'] < $v['num']) {
					$num = $goods_info['max_buy'];
				}
				if ($sku_info['stock'] < $num) {
					$num = $sku_info['stock'];
				}
				// 商品最小购买数大于现购买数
				if ($goods_info['min_buy'] > 0 && $num < $goods_info['min_buy']) {
					$num = $goods_info['min_buy'];
				}
				// 商品最小购买数大于现有库存
				if ($goods_info['min_buy'] > $sku_info['stock']) {
					unset($cart_goods_list[ $k ]);
					// 更新cookie购物车
					$this->deleteCart($v['cart_id']);
					continue;
				}
				if ($num != $v['num']) {
					// 更新购物车
					$cart_goods_list[ $k ]['num'] = $num;
					$this->modifyCartAdjustNumber($v['cart_id'], $num);
				}
				
				$cart_goods_list[ $k ]["promotion_price"] = round($cart_goods_list[ $k ]["price"], 2);
				// 阶梯优惠后的价格
				$cart_goods_list[ $k ]["price"] = $this->getGoodsLadderPreferentialInfo($v['goods_id'], $num, $cart_goods_list[ $k ]["price"]);
			}
			// 为购物车图片
			foreach ($cart_goods_list as $k => $v) {
				$picture = new AlbumPictureModel();
				$picture_info = $picture->get($v['goods_picture']);
				$cart_goods_list[ $k ]['picture_info'] = $picture_info;
			}
			//			sort($cart_goods_list);
			// $cart_goods_list[0]["goods_id_array"] = $goods_id_array;
		}
		return $cart_goods_list;
	}
	
	
	/**
	 * 获取购物车数量
	 */
	public function getCartCount($uid)
	{
		$cart_count = 0;
		if ($uid > 0) {
			$cart = new NsCartModel();
			$cart_count = $cart->getCount([
				'buyer_id' => $uid
			]);
		} else {
			$cart_goods_list = cookie('cart_array');
			if (empty($cart_goods_list)) {
				$cart_goods_list = null;
			} else {
				$cart_goods_list = json_decode($cart_goods_list, true);
				$cart_count = count($cart_goods_list);
			}
		}
		return $cart_count;
	}
	
	/***********************************************************购物车结束*********************************************************/
	
	
	/***********************************************************商品咨询*********************************************************/
	
	/**
	 * 添加 商品咨询
	 */
	public function addConsult($data)
	{
		$consult = new NsConsultModel();
		$consult->save($data);
		$data['consult_id'] = $consult->consult_id;
		hook("consultSaveSuccess", $data);
		$res = $consult->consult_id;
		return $res;
	}
	
	/**
	 * 回复 商品咨询 （店铺后台）
	 */
	public function replyConsult($consult_id, $consult_reply)
	{
		$consult = new NsConsultModel();
		$data = array(
			'consult_reply' => $consult_reply,
			'consult_reply_time' => time()
		);
		$res = $consult->save($data, [
			'consult_id' => $consult_id
		]);
		$data['consult_id'] = $consult_id;
		hook("replyConsultSaveSuccess", $data);
		return $res;
	}
	
	/**
	 * 删除 商品咨询（店铺后台）
	 */
	public function deleteConsult($consult_id)
	{
		$consult = new NsConsultModel();
		return $consult->destroy($consult_id);
	}
	
	/**
	 * 获取商品咨询类型列表
	 */
	public function getConsultTypeList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$consult_type = new NsConsultTypeModel();
		$list = $consult_type->pageQuery($page_index, $page_size, $condition, $order, '');
		return $list;
	}
	
	/**
	 * 获取商品咨询列表
	 */
	public function getConsultList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$consult = new NsConsultModel();
		$list = $consult->pageQuery($page_index, $page_size, $condition, $order, '');
		if (!empty($list)) {
			foreach ($list['data'] as $k => $v) {
				$pic_info = $this->getGoodsImg($v['goods_id']);
				$list['data'][ $k ]['picture_info'] = $pic_info;
			}
		}
		return $list;
	}
	
	/**
	 * 获取咨询个数
	 */
	public function getConsultCount($condition)
	{
		$consult = new NsConsultModel();
		$count = $consult->where($condition)->count();
		return $count;
	}
	
	/***********************************************************商品咨询结束*********************************************************/
	
	
	/***********************************************************商品评价*********************************************************/
	
	/**
	 * 添加商品评价回复
	 * $id 评价id
	 * $replyContent 回复内容
	 * $replyType 回复类型
	 */
	public function addGoodsEvaluateReply($params)
	{
		$goodsEvaluate = new NsGoodsEvaluateModel();
		if ($params['reply_type'] == 1) {
			return $goodsEvaluate->save([
				'explain_first' => $params['reply_content']
			], [
				'id' => $params['id']
			]);
		} elseif ($params['reply_type'] == 2) {
			return $goodsEvaluate->save([
				'again_explain' => $params['reply_content']
			], [
				'id' => $params['id']
			]);
		}
	}
	
	/**
	 * 设置评价显示状态
	 */
	public function modifyEvaluateShowStatus($id)
	{
		$goodsEvaluate = new NsGoodsEvaluateModel();
		$showStatu = $goodsEvaluate->getInfo([
			'id' => $id
		], 'is_show');
		if ($showStatu['is_show'] == 1) {
			return $goodsEvaluate->save([
				'is_show' => 0
			], [
				'id' => $id
			]);
		} elseif ($showStatu['is_show'] == 0) {
			return $goodsEvaluate->save([
				'is_show' => 1
			], [
				'id' => $id
			]);
		}
	}
	
	/**
	 * 删除评价
	 */
	public function deleteEvaluate($id)
	{
		$goodsEvaluate = new NsGoodsEvaluateModel();
		return $goodsEvaluate->destroy($id);
	}
	
	/**
	 * 商品评价信息
	 */
	public function getGoodsEvaluate($goods_id)
	{
		$goodsEvaluateModel = new NsGoodsEvaluateModel();
		$condition['goods_id'] = $goods_id;
		$field = 'order_id, order_no, order_goods_id, goods_id, goods_name, goods_price, goods_image, storeid, storename, content, addtime, image, explain_first, member_name, uid, is_anonymous, scores, again_content, again_addtime, again_image, again_explain';
		return $goodsEvaluateModel->getQuery($condition, $field, 'id ASC');
	}
	
	/**
	 * 商品评价表
	 */
	public function getGoodsEvaluateList($page_index = 1, $page_size = 0, $condition = [], $order = '', $field = '*')
	{
		$goodsEvaluateModel = new NsGoodsEvaluateModel();
		return $goodsEvaluateModel->pageQuery($page_index, $page_size, $condition, $order, $field);
	}
	
	/**
	 * 商品评价信息的数量
	 * @evaluate_count总数量 @imgs_count带图的数量 @praise_count好评数量 @center_count中评数量 bad_count差评数量
	 */
	public function getGoodsEvaluateCount($goods_id)
	{
		$goods_evaluate = new NsGoodsEvaluateModel();
		$evaluate_count_list['evaluate_count'] = $goods_evaluate->where([
			'goods_id' => $goods_id,
			'is_show' => 1
		])->count();
		
		$evaluate_count_list['imgs_count'] = $goods_evaluate->where([
			'goods_id' => $goods_id,
			'is_show' => 1
		])->where('image|again_image', 'NEQ', '')->count();
		
		$evaluate_count_list['praise_count'] = $goods_evaluate->where([
			'goods_id' => $goods_id,
			'explain_type' => 1,
			'is_show' => 1
		])->count();
		
		$evaluate_count_list['center_count'] = $goods_evaluate->where([
			'goods_id' => $goods_id,
			'explain_type' => 2,
			'is_show' => 1
		])->count();
		
		$evaluate_count_list['bad_count'] = $goods_evaluate->where([
			'goods_id' => $goods_id,
			'explain_type' => 3,
			'is_show' => 1
		])->count();
		return $evaluate_count_list;
	}
	
	/***********************************************************商品评价结束*********************************************************/
	
	
	/***********************************************************商品回收站、拷贝*********************************************************/
	
	/**
	 * 商品删除以前 将商品挪到 回收站中
	 */
	private function addGoodsDeleted($goods_ids)
	{
		$this->goods->startTrans();
		try {
			$goods_id_array = explode(',', $goods_ids);
			foreach ($goods_id_array as $k => $v) {
				// 得到商品的信息 备份商品
				$goods_info = $this->goods->get($v);
				$goods_delete_model = new NsGoodsDeletedModel();
				$goods_info = json_decode(json_encode($goods_info), true);
				$goods_delete_obj = $goods_delete_model->getInfo([
					"goods_id" => $v
				]);
				if (empty($goods_delete_obj)) {
					$goods_info["update_time"] = time();
					$goods_delete_model->save($goods_info);
					// 商品的sku 信息备份
					$goods_sku_model = new NsGoodsSkuModel();
					$goods_sku_list = $goods_sku_model->getQuery([
						"goods_id" => $v
					]);
					foreach ($goods_sku_list as $goods_sku_obj) {
						$goods_sku_deleted_model = new NsGoodsSkuDeletedModel();
						$goods_sku_obj = json_decode(json_encode($goods_sku_obj), true);
						$goods_sku_obj["update_date"] = time();
						$goods_sku_deleted_model->save($goods_sku_obj);
					}
					// 商品的属性 信息备份
					$goods_attribute_model = new NsGoodsAttributeModel();
					$goods_attribute_list = $goods_attribute_model->getQuery([
						'goods_id' => $v
					]);
					foreach ($goods_attribute_list as $goods_attribute_obj) {
						$goods_attribute_delete_model = new NsGoodsAttributeDeletedModel();
						$goods_attribute_obj = json_decode(json_encode($goods_attribute_obj), true);
						$goods_attribute_delete_model->save($goods_attribute_obj);
					}
					// 商品的sku图片备份
					$goods_sku_picture = new NsGoodsSkuPictureModel();
					$goods_sku_picture_list = $goods_sku_picture->getQuery([
						'goods_id' => $v
					]);
					foreach ($goods_sku_picture_list as $goods_sku_picture_list_obj) {
						$goods_sku_picture_delete = new NsGoodsSkuPictureDeleteModel();
						$goods_sku_picture_list_obj = json_decode(json_encode($goods_sku_picture_list_obj), true);
						$goods_sku_picture_delete->save($goods_sku_picture_list_obj);
					}
				}
			}
			$this->goods->commit();
			return 1;
		} catch (\Exception $e) {
			Log::write('wwwwwwwwwwww' . $e->getMessage());
			$this->goods->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 恢复商品
	 */
	public function regainGoodsDeleted($goods_ids)
	{
		$goods_array = explode(",", $goods_ids);
		$this->goods->startTrans();
		try {
			foreach ($goods_array as $goods_id) {
				$goods_delete_model = new NsGoodsDeletedModel();
				$goods_delete_obj = $goods_delete_model->getInfo([
					"goods_id" => $goods_id
				]);
				$goods_delete_obj = json_decode(json_encode($goods_delete_obj), true);
				$goods_model = new NsGoodsModel();
				$goods_model->save($goods_delete_obj);
				$goods_delete_model->where("goods_id=$goods_id")->delete();
				// sku 恢复
				$goods_sku_delete_model = new NsGoodsSkuDeletedModel();
				$sku_delete_list = $goods_sku_delete_model->getQuery([
					"goods_id" => $goods_id
				]);
				foreach ($sku_delete_list as $sku_obj) {
					$sku_obj = json_decode(json_encode($sku_obj), true);
					$sku_model = new NsGoodsSkuModel();
					$sku_model->save($sku_obj);
				}
				$goods_sku_delete_model->where("goods_id=$goods_id")->delete();
				// 属性恢复
				$goods_attribute_delete_model = new NsGoodsAttributeDeletedModel();
				$attribute_delete_list = $goods_attribute_delete_model->getQuery([
					"goods_id" => $goods_id
				]);
				foreach ($attribute_delete_list as $attribute_delete_obj) {
					$attribute_delete_obj = json_decode(json_encode($attribute_delete_obj), true);
					$attribute_model = new NsGoodsAttributeModel();
					$attribute_model->save($attribute_delete_obj);
				}
				$goods_attribute_delete_model->where("goods_id=$goods_id")->delete();
				// sku图片恢复
				$goods_sku_picture_delete = new NsGoodsSkuPictureDeleteModel();
				$goods_sku_picture_delete_list = $goods_sku_picture_delete->getQuery([
					'goods_id' => $goods_id
				]);
				foreach ($goods_sku_picture_delete_list as $goods_sku_picture_list_delete_obj) {
					$goods_sku_picture = new NsGoodsSkuPictureModel();
					$goods_sku_picture_list_delete_obj = json_decode(json_encode($goods_sku_picture_list_delete_obj), true);
					$goods_sku_picture->save($goods_sku_picture_list_delete_obj);
				}
				$goods_sku_picture_delete->where("goods_id=$goods_id")->delete();
			}
			$this->goods->commit();
			return SUCCESS;
		} catch (\Exception $e) {
			dump($e->getMessage());
			$this->goods->rollback();
			return UPDATA_FAIL;
		}
	}
	
	/**
	 * 拷贝商品信息
	 */
	public function copyGoodsInfo($goods_id)
	{
		$goods_detail = $this->getGoodsDetail($goods_id);
		$goods_attribute_arr = array();
		foreach ($goods_detail['goods_attribute_list'] as $item) {
			$item_arr = array(
				'attr_value_id' => $item['attr_value_id'],
				'attr_value' => $item['attr_value'],
				'attr_value_name' => $item['attr_value_name'],
				'sort' => $item['sort']
			);
			array_push($goods_attribute_arr, $item_arr);
		}
		
		$skuArray = array();
		foreach ($goods_detail['sku_list'] as $item) {
			if (!empty($item['attr_value_items'])) {
				$skuArray[] = array(
					'attr_value_items' => $item['attr_value_items'],
					'sku_price' => $item['price'],
					'market_price' => $item['market_price'],
					'cost_price' => $item['cost_price'],
					'stock' => 0,
					'code' => $item['code'],
					'sku_img' => $item['sku_img_array'],
					'extend_json' => $item['extend_json'],
					'volume' => $item['volume'],
					'weight' => $item['weight']
				);
			}
		}
		// sku规格图片
		$goods_sku_picture = new NsGoodsSkuPictureModel();
		$goods_sku_picture_query = $goods_sku_picture->getQuery([
			"goods_id" => $goods_id
		], "goods_id, shop_id, spec_id, spec_value_id, sku_img_array");
		$goods_sku_picture_query_array = array();
		foreach ($goods_sku_picture_query as $k => $v) {
			$goods_sku_picture_query_array[ $k ]["spec_id"] = $v["spec_id"];
			$goods_sku_picture_query_array[ $k ]["spec_value_id"] = $v["spec_value_id"];
			$goods_sku_picture_query_array[ $k ]["img_ids"] = $v["sku_img_array"];
		}
		if (empty($goods_sku_picture_query_array)) {
			$goods_sku_picture_str = "";
		} else {
			$goods_sku_picture_str = json_encode($goods_sku_picture_query_array);
		}
		// 阶梯优惠信息
		$goodsLadderPreferentialList = $this->getGoodsLadderPreferential([
			"goods_id" => $goods_id
		]);
		
		$ladder_preference = implode(",", $goodsLadderPreferentialList);
		
		//商品会员折扣信息
		$ns_goods_member_discount = new NsGoodsMemberDiscountModel();
		$discount_info = $ns_goods_member_discount->getQuery([
			"goods_id" => $goods_detail['goods_id']
		]);
		$decimal_reservation_number = 2;
		if (!empty($discount_info)) {
			$decimal_reservation_number = $discount_info[0]['decimal_reservation_number'];
		}
		$discount_info = json_encode($discount_info);
		
		$data = array(
			'goods_id' => 0,
			'goods_type' => $goods_detail['goods_type'],
			'goods_name' => $goods_detail['goods_name'] . '--副本',
			'shop_id' => $goods_detail['shop_id'],
			'keywords' => $goods_detail['keywords'],
			'introduction' => $goods_detail['introduction'],
			'description' => $goods_detail['description'],
			'code' => $goods_detail['code'],
			'state' => $goods_detail['state'],
			"goods_unit" => $goods_detail['goods_unit'],
			
			'category_id' => $goods_detail['category_id'],
			'category_id_1' => $goods_detail['category_id_1'],
			'category_id_2' => $goods_detail['category_id_2'],
			'category_id_3' => $goods_detail['category_id_3'],
			
			'supplier_id' => $goods_detail['supplier_id'],    //供应商
			'brand_id' => $goods_detail['brand_id'],       //品牌
			'group_id_array' => $goods_detail['group_id_array'], //分组
			
			//价钱
			'market_price' => $goods_detail['market_price'],
			'price' => $goods_detail['price'],
			'promotion_price' => $goods_detail['price'],
			'cost_price' => $goods_detail['cost_price'],
			
			//积分
			'point_exchange_type' => $goods_detail['point_exchange_type'],
			'point_exchange' => $goods_detail['point_exchange'],
			'give_point' => $goods_detail['give_point'],
			'max_use_point' => $goods_detail['max_use_point'],
			'integral_give_type' => $goods_detail['integral_give_type'], //积分赠送类型 0固定值 1按比率
			
			//会员折扣
			'is_member_discount' => $goods_detail['is_member_discount'],
			
			//物流
			'shipping_fee' => $goods_detail['shipping_fee'],
			'shipping_fee_id' => $goods_detail['shipping_fee_id'],
			'goods_weight' => $goods_detail['goods_weight'],
			'goods_volume' => $goods_detail['goods_volume'],
			'shipping_fee_type' => $goods_detail['shipping_fee_type'],
			
			//库存
			'stock' => 0,
			'min_stock_alarm' => $goods_detail['min_stock_alarm'],  //库存预警
			'is_stock_visible' => $goods_detail['is_stock_visible'], //显示库存
			
			//限购
			'max_buy' => $goods_detail['max_buy'],
			'min_buy' => $goods_detail['min_buy'],
			
			//基础量
			'clicks' => $goods_detail['clicks'],
			'sales' => $goods_detail['sales'],
			'shares' => $goods_detail['shares'],
			
			//地址
			'province_id' => $goods_detail['province_id'],
			'city_id' => $goods_detail['city_id'],
			
			//图片
			'picture' => $goods_detail['picture'],
			'img_id_array' => $goods_detail['img_id_array'],
			'sku_img_array' => $goods_detail['sku_img_array'],
			'QRcode' => $goods_detail['QRcode'],
			'goods_video_address' => $goods_detail['goods_video_address'],
			
			//属性规格
			'goods_attribute_id' => $goods_detail['goods_attribute_id'],
			'goods_spec_format' => $goods_detail['goods_spec_format'],
			
			//日期
			'production_date' => $goods_detail['production_date'],
			'shelf_life' => $goods_detail['shelf_life'], //保质期
			
			//模板
			'pc_custom_template' => $goods_detail['pc_custom_template'],
			'wap_custom_template' => $goods_detail['wap_custom_template'],
			
			//预售
			'is_open_presell' => $goods_detail['is_open_presell'],
			'presell_time' => $goods_detail['presell_time'],
			'presell_day' => $goods_detail['presell_day'],
			'presell_delivery_type' => $goods_detail['presell_delivery_type'],
			'presell_price' => $goods_detail['presell_price'],
			
			'sku_picture_values' => $goods_sku_picture_str,
			'ladder_preference' => $ladder_preference,
			'member_discount_arr' => $discount_info,
			'decimal_reservation_number' => $decimal_reservation_number
		);
		
		$data['skuArray'] = $skuArray;
		$res = $this->editGoods($data);
		return $res;
	}
	
	/**
	 * 删除回收站商品
	 */
	public function deleteRecycleGoods($goods_id)
	{
		$goods_delete = new NsGoodsDeletedModel();
		$goods_delete->startTrans();
		try {
			$res = $goods_delete->where("goods_id in ($goods_id) and shop_id=$this->instance_id ")->delete();
			if ($res > 0) {
				$goods_id_array = explode(',', $goods_id);
				$goods_sku_model = new NsGoodsSkuDeletedModel();
				$goods_attribute_model = new NsGoodsAttributeDeletedModel();
				$goods_sku_picture_delete = new NsGoodsSkuPictureDeleteModel();
				foreach ($goods_id_array as $k => $v) {
					// 删除商品sku
					$goods_sku_model->where("goods_id = $v")->delete();
					// 删除商品属性
					$goods_attribute_model->where("goods_id = $v")->delete();
					// 删除图片
					$goods_sku_picture_delete->where("goods_id = $v")->delete();
				}
			}
			$goods_delete->commit();
			if ($res > 0) {
				return SUCCESS;
			} else {
				return DELETE_FAIL;
			}
		} catch (\Exception $e) {
			$goods_delete->rollback();
			return DELETE_FAIL;
		}
	}
	
	/**
	 * 商品回收库的分页
	 */
	public function getGoodsDeletedList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		// 针对商品分类
		if (!empty($condition['ng.category_id'])) {
			$goods_category = new GoodsCategory();
			$category_list = $goods_category->getCategoryTreeList($condition['ng.category_id']);
			$condition['ng.category_id'] = array(
				'in',
				$category_list
			);
		}
		$goods_view = new NsGoodsDeletedViewModel();
		$list = $goods_view->getGoodsViewList($page_index, $page_size, $condition, $order);
		if (!empty($list['data'])) {
			// 用户针对商品的收藏
			foreach ($list['data'] as $k => $v) {
				if (!empty($this->uid)) {
					$member = new Member();
					$list['data'][ $k ]['is_favorite'] = $member->getIsMemberFavorites($this->uid, $v['goods_id'], 'goods');
				} else {
					$list['data'][ $k ]['is_favorite'] = 0;
				}
				// 查询商品单品活动信息
				$goods_preference = new GoodsPreference();
				$goods_promotion_info = $goods_preference->getGoodsPromote($v['goods_id']);
				$list["data"][ $k ]['promotion_info'] = $goods_promotion_info;
			}
		}
		return $list;
	}
	
	/***********************************************************商品回收站、拷贝结束*********************************************************/
	
	/***********************************************************商品轨迹*********************************************************/
	
	/**
	 * 添加商品轨迹
	 */
	public function addGoodsBrowse($goods_id, $uid)
	{
		$goods_browse = new NsGoodsBrowseModel();
		try {
			// 判断原足迹中是否有这个商品
			$condition = array(
				"goods_id" => $goods_id,
				"uid" => $uid
			);
			$count = $goods_browse->getCount($condition);
			if ($count > 0) {
				$goods_browse->destroy($condition);
			}
			$goods_model = new NsGoodsModel();
			$goods_info = $goods_model->getInfo([
				"goods_id" => $goods_id
			], "category_id");
			$data = array(
				"goods_id" => $goods_id,
				"uid" => $uid,
				"create_time" => time(),
				"category_id" => $goods_info["category_id"],
				"add_date" => date('Y-m-d', time())
			);
			$goods_browse->save($data);
			$goods_browse->commit();
			return $goods_browse->browse_id;
		} catch (\Exception $e) {
			$goods_browse->rollback();
			return 0;
		}
	}
	
	/**
	 * 删除商品轨迹
	 */
	public function deleteGoodsBrowse($condition)
	{
		$goods_browse = new NsGoodsBrowseModel();
		$retval = $goods_browse->destroy($condition);
		return $retval;
	}
	
	/**
	 * 获取商品轨迹列表
	 */
	public function getGoodsBrowseList($page_index, $page_size, $condition, $order, $field = "*")
	{
		$goods_browse = new NsGoodsBrowseModel();
		$goods_browse_list = $goods_browse->pageQuery($page_index, $page_size, $condition, $order, $field);
		$category_list = array();
		if (!empty($goods_browse_list)) {
			foreach ($goods_browse_list["data"] as $k => $v) {
				$goods_info = $this->goods->getInfo([
					"goods_id" => $v["goods_id"]
				], "category_id, category_id_1, goods_name, promotion_type, promotion_price, shop_id, price, picture, clicks, point_exchange_type, point_exchange");
				
				$ablum_picture = new AlbumPictureModel();
				$picture_info = $ablum_picture->getInfo([
					"pic_id" => $goods_info["picture"]
				]);
				$goods_info["picture_info"] = $picture_info;
				$goods_category = new NsGoodsCategoryModel();
				$category_info = $goods_category->getInfo([
					"category_id" => $v["category_id"]
				], "category_name, short_name, category_id");
				// 判断数组是否存在(拼装分类列表)
				if (!empty($category_info)) {
					if (!in_array($category_info, $category_list)) {
						$category_list[] = $category_info;
					}
				}
				$goods_browse_list["data"][ $k ]["goods_info"] = $goods_info;
				$goods_browse_list["data"][ $k ]["category"] = $category_info;
			}
		}
		$goods_browse_list["category_list"] = $category_list;
		
		return $goods_browse_list;
	}
	
	/***********************************************************商品轨迹结束*********************************************************/
	
	
	/***********************************************************商品会员折扣*********************************************************/
	
	/**
	 * 设置商品折扣
	 */
	public function setMemberDiscount($goods_ids, $discount_info, $decimal_reservation_number)
	{
		if (!empty($goods_ids) && !empty($discount_info)) {
			$ns_goods_member_discount = new NsGoodsMemberDiscountModel();
			$ns_goods_member_discount->startTrans();
			try {
				$discount_info_arr = json_decode($discount_info, true);
				$goods_ids = explode(",", $goods_ids);
				foreach ($goods_ids as $goods_id) {
					foreach ($discount_info_arr as $v) {
						$count = $ns_goods_member_discount->getCount([ "level_id" => $v["level_id"], "goods_id" => $goods_id ]);
						$data["goods_id"] = $goods_id;
						$data["discount"] = $v["discount"];
						$data["level_id"] = $v["level_id"];
						$data["decimal_reservation_number"] = $decimal_reservation_number;
						$ns_goods_member_discount = new NsGoodsMemberDiscountModel();
						if ($count == 0) {
							$ns_goods_member_discount->save($data);
						} else {
							$ns_goods_member_discount->save($data, [ "level_id" => $v["level_id"], "goods_id" => $goods_id ]);
						}
					}
				}
				
				$ns_goods_member_discount->commit();
				return array(
					"code" => 1,
					"message" => "设置成功"
				);
			} catch (\Exception $e) {
				$ns_goods_member_discount->rollback();
				return array(
					"code" => 0,
					"message" => $e->getMessage()
				);
			}
		} else {
			return array(
				"code" => 0,
				"message" => "操作失败"
			);
		}
	}
	
	/**
	 * 获取商品会员折扣
	 */
	public function getGoodsDiscountByMemberLevel($level_id, $goods_id)
	{
		$ns_goods_member_discount = new NsGoodsMemberDiscountModel();
		$goods_member_discount_detail = $ns_goods_member_discount->getInfo([ "level_id" => $level_id, "goods_id" => $goods_id ], "discount,decimal_reservation_number");
		if (!empty($goods_member_discount_detail["discount"])) {
			$member_level_discount = $goods_member_discount_detail;
		} else {
			$member_level_discount = array(
				"discount" => "",
				"decimal_reservation_number" => 2
			);
		}
		return $member_level_discount;
	}
	
	/**
	 * 获取商品会员折扣列表
	 */
	public function showMemberDiscount($goods_id)
	{
		$ns_goods_member_discount = new NsGoodsMemberDiscountModel();
		$discount_list = $ns_goods_member_discount->getQuery([ "goods_id" => $goods_id ], "level_id,discount,decimal_reservation_number");
		$decimal_reservation_number = 2;
		if (!empty($discount_list)) {
			$decimal_reservation_number = $discount_list[0]['decimal_reservation_number'];
		}
		$list = array(
			"discount_list" => $discount_list,
			"decimal_reservation_number" => $decimal_reservation_number
		);
		return $list;
	}
	
	/**
	 * 处理会员价
	 */
	public function handleMemberPrice($goods_id, $member_price)
	{
		$discount_info = $this->showMemberDiscount($goods_id);
		$decimal_reservation_number = $discount_info['decimal_reservation_number'];
		if ($decimal_reservation_number >= 0) {
			$member_price = round($member_price, $decimal_reservation_number);
		}
		return sprintf("%.2f", $member_price);
	}
	
	/***********************************************************商品会员折扣结束*********************************************************/
	
	
	/***********************************************************虚拟商品*********************************************************/
	
	/**
	 * 添加虚拟商品
	 *
	 * @param string $virtual_code 虚拟码
	 * @param string $virtual_goods_name 虚拟商品名称
	 * @param double $money 金额
	 * @param int $buyer_id 买家id
	 * @param string $buyer_nickname 买家昵称
	 * @param int $order_goods_id 关联订单项id
	 * @param int $order_no 订单编号
	 * @param int $validity_period 有效期/天(0表示不限制)
	 * @param int $start_time 有效期开始时间
	 * @param int $end_time 有效期结束时间
	 * @param int $use_number 使用次数
	 * @param int $confine_use_number 限制使用次数
	 * @param int $use_status 使用状态
	 * @param string $goods_id 商品名称
	 */
	public function addVirtualGoods($data)
	{
		$virtual_goods_model = new NsVirtualGoodsModel();
		$data['virtual_code'] = $this->generateVirtualCode();
		$res = $virtual_goods_model->save($data);
		Cache::clear('niu_virtual_goods');
		return $res;
	}
	
	/**
	 * 编辑虚拟商品类型
	 *
	 * @param int $virtual_goods_type_id 虚拟商品类型id，0表示添加
	 * @param int $virtual_goods_group_id 关联虚拟商品分组id
	 * @param string $virtual_goods_type_name 虚拟商品类型名称
	 * @param int $validity_period 有效期/天(0表示不限制)
	 * @param int $is_enabled 是否启用（禁用后要查询关联的虚拟商品给予弹出确认提示框）
	 * @param double $money 金额
	 * @param string $config_info 配置信息(API接口、参数等)
	 * @param int $confine_use_number 限制使用次数
	 */
	public function editVirtualGoodsType($params)
	{
		Cache::clear('niu_virtual_goods_category');
		$virtual_goods_type_model = new NsVirtualGoodsTypeModel();
		if ($params['virtual_goods_type_id'] == 0) {
			// 添加
			$data = array(
				'virtual_goods_group_id' => $params['virtual_goods_group_id'],
				'validity_period' => $params['validity_period'],
				'confine_use_number' => $params['confine_use_number'],
				'shop_id' => $this->instance_id,
				'create_time' => time(),
				'relate_goods_id' => $params['goods_id']
			);
			
			// 如果不是点卡的话，添加配置信息
			if ($params['virtual_goods_group_id'] != 3) {
				$data['value_info'] = $params['value_info'];
			}
			$res = $virtual_goods_type_model->save($data);
		} else {
			
			// 修改
			$data = array(
				'validity_period' => $params['validity_period'],
				'confine_use_number' => $params['confine_use_number'],
				'relate_goods_id' => $params['goods_id']
			);
			
			// 如果不是点卡的话，添加配置信息
			if ($params['virtual_goods_group_id'] != 3) {
				$data['value_info'] = $params['config_info'];
			}
			$res = $virtual_goods_type_model->save($data, [
				'virtual_goods_type_id' => $params['virtual_goods_type_id']
			]);
		}
		
		if ($params['virtual_goods_group_id'] == 3) {
			
			if ($params['config_info'] != '') {
				$value_array = json_decode($params['config_info'], true);
				foreach ($value_array as $item) {
					$data = array(
						'virtual_goods_name' => "",
						'money' => 0,
						'buyer_id' => "",
						'buyer_nickname' => '',
						'order_goods_id' => 0,
						'order_no' => '',
						'validity_period' => $params['validity_period'],
						'start_time' => 0,
						'end_time' => 0,
						'use_number' => 0,
						'confine_use_number' => $params['confine_use_number'],
						'use_status' => -2,
						'create_time' => time(),
						'goods_id' => $params['goods_id'],
						'sku_id' => $params['goods_id'],
						'remark' => $item['remark']
					);
					$this->addVirtualGoods($data);
				}
				
				//更新库存
				$this->modifyVirtualCardByGoodsStock($params['goods_id']);
			}
		}
		return $res;
	}
	
	/**
	 * 生成虚拟码
	 */
	public function generateVirtualCode()
	{
		$time_str = date('YmdHis');
		$rand_code = rand(0, 999999);
		$virtual_code = $time_str . $rand_code . 0;
		$virtual_code = md5($virtual_code);
		$virtual_code = substr($virtual_code, 16, 32);
		return $virtual_code;
	}
	
	/**
	 * 根据主键id删除虚拟商品
	 */
	public function deleteVirtualGoodsById($virtual_goods_id)
	{
		
		$virtual_goods_model = new NsVirtualGoodsModel();
		$goods_model = new NsGoodsModel();
		$goods_sku_model = new NsGoodsSkuModel();
		
		$virtual_goods_model->startTrans();
		try {
			$condition = [ 'virtual_goods_id' => [ 'in', $virtual_goods_id ] ];
			$list = $virtual_goods_model->getQuery($condition, 'goods_type,goods_id,sku_id');
			foreach ($list as $item) {
				if ($item['goods_type'] == 4) {
					$goods_model->where([ 'goods_id' => $item['goods_id'] ])->setDec('stock', 1);
					$goods_sku_model->where([ 'sku_id' => $item['sku_id'] ])->setDec('stock', 1);
				}
			}
			$res = $virtual_goods_model->destroy($condition);
			$virtual_goods_model->commit();
			return $res;
		} catch (\Exception $e) {
			return -1;
		}
	}
	
	/**
	 * 批量添加点卡库存
	 */
	public function addBatchVirtualCard($virtual_goods_type_id, $goods_id, $virtual_card_json, $sku_id)
	{
		
		$virtual_goods_model = new NsVirtualGoodsModel();
		$goods_model = new NsGoodsModel();
		$goods_sku_model = new NsGoodsSkuModel();
		
		$virtual_goods_model->startTrans();
		try {
			$virtual_card_array = json_decode($virtual_card_json, true);
			$sku_info = $goods_sku_model->getInfo([ "sku_id" => $sku_id ], "sku_name");
			$data = [];
			foreach ($virtual_card_array as $k => $item) {
				$data[ $k ] = array(
					'virtual_goods_name' => $sku_info["sku_name"],
					'money' => 0,
					'buyer_id' => "",
					'buyer_nickname' => '',
					'order_goods_id' => 0,
					'order_no' => '',
					'validity_period' => 0,
					'start_time' => 0,
					'end_time' => 0,
					'use_number' => 0,
					'confine_use_number' => 1,
					'use_status' => -2,
					'create_time' => time(),
					'goods_id' => $goods_id,
					'sku_id' => $sku_id,
					'remark' => $item['remark'],
					'virtual_code' => '',
					'goods_type' => $virtual_goods_type_id
				);
			}
			$res = $virtual_goods_model->saveAll($data);
			$goods_model->where([ 'goods_id' => $goods_id ])->setInc('stock', count($data));
			$goods_sku_model->where([ 'sku_id' => $sku_id ])->setInc('stock', count($data));
			$virtual_goods_model->commit();
			return [ 'code' => 1, 'message' => '添加成功' ];
		} catch (\Exception $e) {
			$virtual_goods_model->rollback();
			return [ 'code' => 0, 'message' => $e->getMessage() ];
		}
	}
	
	/**
	 * 根据点卡库存更新商品库存
	 */
	public function modifyVirtualCardByGoodsStock($goods_id)
	{
		$virtual_goods_type_model = new NsVirtualGoodsTypeModel();
		$virtual_goods_type_info = $virtual_goods_type_model->getInfo([ 'relate_goods_id' => $goods_id ], '*');
		if ($virtual_goods_type_info['virtual_goods_group_id'] == 3) {
			$virtual_goods_model = new NsVirtualGoodsModel();
			$virtual_count = $virtual_goods_model->getCount([ 'use_status' => -2, 'goods_id' => $goods_id ]);
			$goods_model = new NsGoodsModel();
			$goods_model->save([ 'stock' => $virtual_count ], [ 'goods_id' => $goods_id ]);
			$goods_sku_model = new NsGoodsSkuModel();
			$goods_sku_model->save([ 'stock' => $virtual_count ], [ 'goods_id' => $goods_id ]);
		}
	}
	
	/**
	 * 虚拟订单，生成虚拟商品
	 * 1、根据订单id查询订单项(虚拟订单项只会有一条数据)
	 * 2、根据购买的商品获取虚拟商品类型信息
	 * 3、根据购买的商品数量添加相应的虚拟商品数量
	 */
	public function virtualOrderAction($param)
	{
		$order_goods_model = new NsOrderGoodsModel();
		$order_goods_model->startTrans();
		try {
			$order_goods_items = $param["order_goods_items"];
			$order_id = $param["order_id"];
			$order_no = $param["order_no"];
			$buyer_nickname = $param["buyer_nickname"];
			
			if (!empty($order_goods_items)) {
				
				$goods_id = $order_goods_items['goods_id'];
				$goods_model = new NsGoodsModel();
				$goods_info = $goods_model->getInfo([ 'goods_id' => $goods_id ], "production_date, shelf_life, goods_type");
				$sku_model = new NsGoodsSkuModel();
				$sku_info = $sku_model->getInfo([ "sku_id" => $order_goods_items["sku_id"] ], "extend_json, sku_name");
				// 生成虚拟商品
				for ($i = 0; $i < $order_goods_items['num']; $i++) {
					$validity_period = $goods_info['shelf_life']; // 有效期至
					$start_time = time();
					if ($validity_period == 0) {
						$end_time = 0;
					} else {
						$end_time = strtotime("+$validity_period days");
					}
					if ($goods_info['goods_type'] != 4) {
						$remark = "";
						if (!empty($sku_info["extend_json"])) {
							$json_array = json_decode($sku_info["extend_json"], true);
						}
						switch ($goods_info['goods_type']) {
							case 0:
								$confine_use_number = 1;
								break;
							case 2:
								$confine_use_number = 0;
								$remark = $json_array["extend_1"];
								break;
							case 3:
								$confine_use_number = 0;
								$remark = "网盘链接：" . $json_array["extend_1"] . "  提取码：" . $json_array["extend_2"];
								break;
						}
						$data = array(
							'virtual_code' => $this->generateVirtualCode(),
							'goods_id' => $order_goods_items['goods_id'],
							'sku_id' => $order_goods_items['sku_id'],
							'shop_id' => $order_goods_items['shop_id'],
							'virtual_goods_name' => $order_goods_items['goods_name'],
							'money' => $order_goods_items['price'],
							'buyer_id' => $order_goods_items['buyer_id'],
							'buyer_nickname' => $buyer_nickname,
							'order_goods_id' => $order_goods_items['order_goods_id'],
							'order_no' => $order_no,
							'validity_period' => $validity_period,
							'start_time' => $start_time,
							'end_time' => $end_time,
							'use_number' => 0,
							'confine_use_number' => 1,
							'use_status' => 0,
							'remark' => $remark,
							'create_time' => time(),
							'goods_type' => $goods_info["goods_type"]
						);
						
						$virtual_goods_model = new NsVirtualGoodsModel();
						$virtual_goods_model->save($data);
					} else {
						//设置卡券归属
						$data = [
							'money' => $order_goods_items['price'],
							'buyer_id' => $order_goods_items['buyer_id'],
							'buyer_nickname' => $buyer_nickname,
							'order_goods_id' => $order_goods_items['order_goods_id'],
							'order_no' => $order_no,
							'validity_period' => $validity_period,
							'start_time' => $start_time,
							'end_time' => $end_time,
							'use_number' => 0,
							'confine_use_number' => 1,
							'use_status' => 0,
							'create_time' => time(),
						];
						$virtual_goods_model = new NsVirtualGoodsModel();
						$virtual_goods_model->where([ 'goods_id' => $order_goods_items['goods_id'], 'sku_id' => $order_goods_items['sku_id'], 'buyer_id' => 0, 'use_status' => -2 ])->limit(1)->update($data);
					}
					
				}
			}
			//店铺服务自动完成订单
			
			$order_goods_model->commit();
			return 1;
		} catch (\Exception $e) {
			$order_goods_model->rollback();
			return 0;
		}
		
	}
	
	/**
	 * 根据商品id查询点卡库存（虚拟商品列表）
	 */
	public function getVirtualGoodsListByGoodsId($page_index, $page_size, $condition, $order = "")
	{
		$ns_virtual_goods_view = new NsVirtualGoodsViewModel();
		$list = $ns_virtual_goods_view->getViewList($page_index, $page_size, $condition, $order);
		
		return $list;
	}
	
	/**
	 * 根据商品id查询点卡库存数量
	 */
	public function getVirtualGoodsCountByGoodsId($goods_id)
	{
		$ns_virtual_goods_view = new NsVirtualGoodsViewModel();
		$res = $ns_virtual_goods_view->getCount([
			'goods_id' => $goods_id
		]);
		return $res;
	}
	
	/**
	 * 获取虚拟商品分组
	 */
	public function getVirtualGoodsGroup($condition = '1=1')
	{
		$cache = Cache::tag('niu_virtual_goods_group')->get('getVirtualGoodsGroup' . json_encode($condition));
		if (!empty($cache)) return $cache;
		
		$virtual_group_model = new NsVirtualGoodsGroupModel();
		$list = $virtual_group_model->getQuery($condition);
		
		Cache::tag('niu_virtual_goods_group')->set('getVirtualGoodsGroup' . json_encode($condition), $list);
		return $list;
	}
	
	/**
	 * 获取虚拟商品分组详情
	 */
	public function getVirtualGoodsGroupInfo($virtual_goods_group_id)
	{
		$cache = Cache::tag('niu_virtual_goods_group')->get('getVirtualGoodsGroupInfo' . $virtual_goods_group_id);
		if (!empty($cache)) return $cache;
		
		$virtual_group_model = new NsVirtualGoodsGroupModel();
		$virtual_goods_group_info = $virtual_group_model->getInfo([ 'virtual_goods_group_id' => $virtual_goods_group_id ], '*');
		
		Cache::tag('niu_virtual_goods_group')->set('getVirtualGoodsGroupInfo' . $virtual_goods_group_id, $virtual_goods_group_info);
		return $virtual_goods_group_info;
	}
	
	/**
	 * 根据订单编号查询虚拟商品列表
	 */
	public function getVirtualGoodsListByOrderNo($order_no)
	{
		$virtual_goods_model = new NsVirtualGoodsModel();
		$list = $virtual_goods_model->getQuery([
			"order_no" => $order_no
		], "*", "virtual_goods_id asc");
		if (!empty($list)) {
			
			foreach ($list as $k => $v) {
				if ($v['use_status'] == -1) {
					$list[ $k ]['use_status_msg'] = '已过期';
				} elseif ($v['use_status'] == 0) {
					$list[ $k ]['use_status_msg'] = '未使用';
				} elseif ($v['use_status'] == 1) {
					$list[ $k ]['use_status_msg'] = '已使用';
				}
				$path = $this->getVirtualQecode($v["virtual_goods_id"]);
				$list[ $k ]['path'] = $path;
			}
		}
		return $list;
	}
	
	/**
	 * 制作核销二维码
	 */
	private function getVirtualQecode($virtual_goods_id)
	{
		if (empty($this->uid)) {
			return "";
		}
		$url = __URL(__URL__ . '/wap/verification/goods?vg_id=' . $virtual_goods_id);
		
		// 查询并生成二维码
		
		$upload_path = "upload/qrcode/virtual_qrcode";
		if (!file_exists($upload_path)) {
			mkdir($upload_path, 0777, true);
		}
		$path = $upload_path . '/virtual_' . $virtual_goods_id . '.png';
		getQRcode($url, $upload_path, "virtual_" . $virtual_goods_id);
		return $path;
	}
	
	/**
	 * 根据id查询虚拟商品类型
	 */
	public function getVirtualGoodsTypeById($virtual_goods_type_id)
	{
		$virtual_goods_type_model = new NsVirtualGoodsTypeModel();
		$res = $virtual_goods_type_model->getInfo([
			'virtual_goods_type_id' => $virtual_goods_type_id
		], "*");
		return $res;
	}
	
	/**
	 * 获取虚拟商品详情
	 */
	public function getVirtualGoodsTypeInfo($condition = [])
	{
		
		$virtual_goods_type_model = new NsVirtualGoodsTypeModel();
		$res = $virtual_goods_type_model->getInfo($condition, "*");
		return $res;
	}
	
	/**
	 * 获取虚拟商品列表
	 */
	function getVirtualGoodsList($page_index, $page_size, $condition, $order = "")
	{
		$ns_virtual_goods_view = new NsVirtualGoodsViewModel();
		$list = $ns_virtual_goods_view->getViewList($page_index, $page_size, $condition, $order);
		foreach ($list["data"] as $k => $v) {
			$album_picture = new AlbumPictureModel();
			$picture_info = $album_picture->getInfo([
				"pic_id" => $v["picture"]
			], "pic_cover_mid");
			if (empty($picture_info)) {
				$picture_info_src = '';
			} else {
				$picture_info_src = $picture_info["pic_cover_mid"];
			}
			$list["data"][ $k ]["picture_info"] = $picture_info_src;
			
			$path = $this->getVirtualQecode($v["virtual_goods_id"]);
			$list["data"][ $k ]['path'] = $path;
			
		}
		return $list;
	}
	
	/***********************************************************虚拟商品结束*********************************************************/
	
	/**
	 * 获取商品优惠劵
	 */
	public function getGoodsCoupon($goods_id)
	{
		$coupon_type = new NsCouponTypeModel();
		$coupon_type_id_list = $coupon_type->getCouponTypeListByGoodsdetail($goods_id);
		return $coupon_type_id_list;
	}
	
	/**
	 * 查询点赞状态
	 */
	public function getGoodsSpotFabulous($uid, $goods_id)
	{
		$click_goods = new NsClickFabulousModel();
		$start_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$end_time = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
		$condition = array(
			'shop_id' => $this->instance_id,
			'uid' => $uid,
			'goods_id' => $goods_id,
			'create_time' => array(
				'between',
				[
					$start_time,
					$end_time
				]
			)
		);
		
		$retval = $click_goods->getInfo($condition);
		return $retval;
	}
	
	/**
	 * 获取商品阶梯优惠
	 */
	public function getGoodsLadderPreferential($condition, $order = "", $filed = "*")
	{
		$nsGoodsLadderPreferential = new NsGoodsLadderPreferentialModel();
		$list = $nsGoodsLadderPreferential->pageQuery(1, 0, $condition, $order, $filed);
		return $list["data"];
	}
	
	/**
	 * 获取购买数量满足条件的阶梯优惠信息
	 */
	public function getGoodsLadderPreferentialInfo($goods_id, $num, $goods_price)
	{
		$nsGoodsLadderPreferential = new NsGoodsLadderPreferentialModel();
		$condition["goods_id"] = $goods_id;
		$condition["quantity"] = array(
			"ELT",
			$num
		);
		$res = $nsGoodsLadderPreferential->pageQuery(1, 1, $condition, "quantity desc", "*");
		if ($res["total_count"] > 0) {
			$goods_price -= $res["data"][0]["price"];
		}
		$goods_price = $goods_price < 0 ? 0 : round($goods_price, 2);
		return $goods_price;
	}
	
	/**
	 * 获取商品运费模板情况
	 */
	public function getGoodsExpressTemplate($goods_id, $province_id, $city_id, $district_id)
	{
		$goods_express = new GoodsExpress();
		$retval = $goods_express->getGoodsExpressTemplate($goods_id, $province_id, $city_id, $district_id);
		return $retval;
	}
	
	/**
	 * 二维码路径进库
	 */
	public function goodsQRcodeMake($goods_id, $url)
	{
		$data = array(
			'QRcode' => $url
		);
		$result = $this->goods->save($data, [
			'goods_id' => $goods_id
		]);
		if ($result > 0) {
			return SUCCESS;
		} else {
			return UPDATA_FAIL;
		}
	}
	
	/**
	 * 生成商品海报
	 * @param unknown $goods_id
	 * @param string $uid
	 */
	public function createGoodsPoster($goods_id, $uid = "")
	{
		try {
			$data = $this->getGoodsPosterData($goods_id, $uid);
			if (empty($data)) return [ 'code' => -1, 'message' => '未获取到生成海报所需数据' ];
			if ($data == -50 || $data == -10) {
		        return $data;
            }
			$is_applet = Session::get('is_applet') == 1 ? 1 : 0;

			if ($is_applet == 0) {
                $image_config = [
                    'width' => 740,
                    'height' => 1100,
                    'path' => 'upload/goods_poster/' . date('Ymd', time()) . '/',
                    'file_name' => 'goods_poster_g' . $goods_id . '.jpg',
                ];
			} else {
			    $image_config = [
                    'width' => 740,
                    'height' => 1100,
                    'path' => 'upload/applet_goods_poster/' . date('Ymd', time()) . '/',
                    'file_name' => 'goods_poster_g' . $goods_id . '.jpg',
                ];
            }
			
			if (!empty($data['user_info'])) {
				$image_config['file_name'] = 'goods_poster_g' . $goods_id . '_u' . $uid . '.jpg';
				$image_config['height'] = 1240;
			}
			
			$image_config['image_path'] = $image_config['path'] . $image_config['file_name'];
			// 创建画布
			$poster = imagecreatetruecolor($image_config['width'], $image_config['height']);
			// 海报所需颜色
			$color = [
				'white' => imagecolorallocate($poster, 255, 255, 255),
				'gray' => imagecolorallocate($poster, 102, 102, 102),
				'black' => imagecolorallocate($poster, 10, 10, 10),
				'red' => imagecolorallocate($poster, 255, 0, 0),
				'light_gray' => imagecolorallocate($poster, 241, 241, 241)
			];
			// 创建白色背景
			imagefilledrectangle($poster, 0, 0, $image_config['width'], $image_config['height'], $color['white']);
			// 获取图片资源
			$goods_image = getImgCreateFrom($data['goods_info']['picture']); // 商品图片
			$qrcode_image = getImgCreateFrom($data['goods_info']['qrcode_path']); // 二维码
			$logo_image = getImgCreateFrom($data['logo']);
			list ($logo_width, $logo_height) = getimagesize($data['logo']);
			list ($qrcode_width, $qrcode_height) = getimagesize($data['goods_info']['qrcode_path']);
			
			// 对logo按高度来进行等比率放大缩小
			$new_logo_width = round($logo_width * round(($logo_height / 80), 2));
			
			$goods_name = $this->handleStr($data['goods_info']['goods_name'], 22, 490, 2);
			$introduction = $this->handleStr($data['goods_info']['introduction'], 18, 490, 1);
			$promotion_price = '￥' . $data['goods_info']['promotion_price'];
			$market_price = '￥' . $data['goods_info']['market_price'];
			$collects = $data['goods_info']['sales'] . '人喜欢';
			$collects_offset = imagettfbbox(18, 0, ROOT_PATH . 'public/static/font/Microsoft.ttf', $collects);
			
			imagecopyresampled($poster, $logo_image, (($image_config['width'] - $new_logo_width) / 2), 20, 0, 0, $new_logo_width, 80, $logo_width, $logo_height);
			imagecopyresampled($poster, $goods_image, 20, 120, 0, 0, 700, 700, 700, 700); // 商品图片
			
			if (empty($data['user_info'])) {
				// 将图片写入画布
				imagecopyresampled($poster, $qrcode_image, ($image_config['width'] - 220), 840, 0, 0, 210, 210, $qrcode_width, $qrcode_height);
				// 将文字写入画布
				imagettftext($poster, 22, 0, 20, 875, $color['black'], ROOT_PATH . 'public/static/font/Microsoft.ttf', $goods_name); // 商品名称
				imagettftext($poster, 18, 0, 20, 955, $color['gray'], ROOT_PATH . 'public/static/font/Microsoft.ttf', $introduction); // 商品推销语
				imagettftext($poster, 30, 0, 20, 1035, $color['red'], ROOT_PATH . 'public/static/font/Microsoft.ttf', $promotion_price); // 销售价
				imagettftext($poster, 30, 0, 21, 1036, $color['red'], ROOT_PATH . 'public/static/font/Microsoft.ttf', $promotion_price); // 销售价加粗
				imagettftext($poster, 18, 0, ($image_config['width'] - 250 - $collects_offset[2]), 1031, $color['red'], ROOT_PATH . 'public/static/font/Microsoft.ttf', $collects); // 多少人喜欢
				imagettftext($poster, 16, 0, ($image_config['width'] - 180), 1080, $color['gray'], ROOT_PATH . 'public/static/font/Microsoft.ttf', '长按扫码购买');
				
				if ($data['goods_info']['promotion_price'] < $data['goods_info']['market_price']) {
					$promotion_price_offset = imagettfbbox(27, 0, ROOT_PATH . 'public/static/font/Microsoft.ttf', $promotion_price);
					$market_price_offset = imagettfbbox(27, 0, ROOT_PATH . 'public/static/font/Microsoft.ttf', $market_price);
					imagettftext($poster, 18, 0, ($promotion_price_offset[2] + 50), 1031, $color['gray'], ROOT_PATH . 'public/static/font/Microsoft.ttf', $market_price);
					imagefilledrectangle($poster, ($promotion_price_offset[2] + 45), 1016, ($promotion_price_offset[2] + $market_price_offset[2] + 15), 1018, $color['gray']);
				}
			} else {
				$user_headimg = getImgCreateFrom($data['user_info']['user_headimg']);
				list ($headimg_width, $headimg_height) = getimagesize($data['user_info']['user_headimg']);
				$nick_name = $this->handleStr($data['user_info']['nick_name'], 22, 600, 1);
				$promotion_content = $this->handleStr($data['promotion_content'], 20, 600, 1);
				
				// 将图片写入画布
				imagecopyresampled($poster, $user_headimg, 20, 840, 0, 0, 80, 80, $headimg_width, $headimg_height);
				// 用户信息下的那条线
				imagefilledrectangle($poster, 20, 940, 720, 943, $color['light_gray']);
				
				imagecopyresampled($poster, $qrcode_image, ($image_config['width'] - 230), 960, 0, 0, 210, 210, $qrcode_width, $qrcode_height);
				// 将文字写入画布
				imagettftext($poster, 22, 0, 120, 880, $color['black'], ROOT_PATH . 'public/static/font/Microsoft.ttf', $nick_name); // 昵称
				imagettftext($poster, 20, 0, 120, 913, $color['gray'], ROOT_PATH . 'public/static/font/Microsoft.ttf', $promotion_content); // 推广语
				imagettftext($poster, 22, 0, 20, 995, $color['black'], ROOT_PATH . 'public/static/font/Microsoft.ttf', $goods_name); // 商品名称
				imagettftext($poster, 18, 0, 20, 1075, $color['gray'], ROOT_PATH . 'public/static/font/Microsoft.ttf', $introduction); // 商品推销语
				imagettftext($poster, 30, 0, 20, 1165, $color['red'], ROOT_PATH . 'public/static/font/Microsoft.ttf', $promotion_price); // 销售价
				imagettftext($poster, 30, 0, 21, 1166, $color['red'], ROOT_PATH . 'public/static/font/Microsoft.ttf', $promotion_price); // 销售价加粗    
				imagettftext($poster, 18, 0, ($image_config['width'] - 250 - $collects_offset[2]), 1163, $color['red'], ROOT_PATH . 'public/static/font/Microsoft.ttf', $collects);
				imagettftext($poster, 16, 0, ($image_config['width'] - 190), 1210, $color['gray'], ROOT_PATH . 'public/static/font/Microsoft.ttf', '长按扫码购买');
				
				if ($data['goods_info']['promotion_price'] < $data['goods_info']['market_price']) {
					$promotion_price_offset = imagettfbbox(27, 0, ROOT_PATH . 'public/static/font/Microsoft.ttf', $promotion_price);
					$market_price_offset = imagettfbbox(27, 0, ROOT_PATH . 'public/static/font/Microsoft.ttf', $market_price);
					imagettftext($poster, 18, 0, ($promotion_price_offset[2] + 50), 1163, $color['gray'], ROOT_PATH . 'public/static/font/Microsoft.ttf', $market_price);
					imagefilledrectangle($poster, ($promotion_price_offset[2] + 45), 1148, ($promotion_price_offset[2] + $market_price_offset[2] + 15), 1150, $color['gray']);
				}
			}
			
			// 定义输出类型
			header("Content-type: image/jpeg");
			if (!file_exists($image_config['path'])) {
				mkdir($image_config['path'], 0777, true);
			}
			// 输出图片
			imagejpeg($poster, $image_config['image_path']);
			// 销毁画布资源
			imagedestroy($poster);
			// 删除临时二维码
			unlink($data['goods_info']['qrcode_path']);
			
			return [
				'code' => 1,
				'path' => $image_config['image_path'],
				'name' => $image_config['file_name']
			];
		} catch (\Exception $e) {
			return [
				'code' => -1,
				'message' => $e->getMessage()
			];
		}
	}
	
	/**
	 * 获取生成海报所需数据
	 * @param unknown $goods_id
	 * @param unknown $uid
	 */
	public function getGoodsPosterData($goods_id, $uid)
	{
		$user = new UserModel();
		$config = new Config();
		$website = new WebSite();
		$goods_info = $this->goods->getInfo([ 'goods_id' => $goods_id ], 'goods_name,introduction,picture,market_price,promotion_price,sales');
		if (empty($goods_info)) return;

        $is_applet = Session::get('is_applet') == 1 ? 1 : 0;
		$picture_model = new AlbumPictureModel();
		$picture_info = $picture_model->getInfo([ 'pic_id' => $goods_info['picture'] ], 'pic_cover_big');
		$goods_info['picture'] = $picture_info['pic_cover_big'];

		if ($is_applet == 0) {
            $qrcode_config = [
                'path' => 'upload/goods_poster/' . date('Ymd', time()) . '/',
                'name' => 'temp_qrcode_g' . $goods_id,
                'url' => __URL('APP_MAIN/goods/detail?goods_id=' . $goods_id)
            ];
        } else {
		    $qrcode_config = [
                'path' => 'upload/applet_goods_poster/' . date('Ymd', time()) . '/',
                'name' => 'temp_qrcode_g' . $goods_id . '_u' . $uid,
                'url' => 'pages/goods/goodsdetail/goodsdetail',
                'scene' => 'goods_id-' . $goods_id . '|source_uid-' . $uid
            ];
        }
		
		$user_info = [];
		if (!empty($uid)) {
			$user_info = $user->getInfo([ 'uid' => $uid ], 'nick_name,user_headimg');
			if ($is_applet == 0) {
                $qrcode_config = [
                    'path' => 'upload/goods_poster/' . date('Ymd', time()) . '/',
                    'name' => 'temp_qrcode_g' . $goods_id . '_u' . $uid,
                    'url' => __URL('APP_MAIN/goods/detail?goods_id=' . $goods_id . '&source_uid=' . $uid)
                ];
			} else {
			    $qrcode_config = [
                    'path' => 'upload/applet_goods_poster/' . date('Ymd', time()) . '/',
                    'name' => 'temp_qrcode_g' . $goods_id . '_u' . $uid,
                    'url' => 'pages/goods/goodsdetail/goodsdetail',
                    'scene' => 'goods_id=' . $goods_id . '&source_uid=' . $uid
                ];
            }
			$user_info['user_headimg'] = !empty($user_info['user_headimg']) ? $user_info['user_headimg'] : ROOT_PATH . 'public/static/images/default_user_portrait.gif';
		}
		if ($is_applet == 0) {
            getQRcode($qrcode_config['url'], $qrcode_config['path'], $qrcode_config['name']);
        } else {
		    $wchat_oauth = new WchatOauth();
		    $applet_code = $wchat_oauth->getAppletQrcode($qrcode_config['scene'], $qrcode_config['url'], true, $qrcode_config['path'], $qrcode_config['name']);
		    if ($applet_code == -50 || $applet_code == -10) {
		        return $applet_code;
            }
        }
		$goods_info['qrcode_path'] = $qrcode_config['path'] . $qrcode_config['name'] . '.png';

		$poster_config = $config->getPosterConfig();
		$website_config = $website->getWebSiteInfo();
		
		$data = [
			'goods_info' => $goods_info,
			'user_info' => $user_info,
			'promotion_content' => !empty($poster_config['value']['promotion_content']) ? $poster_config['value']['promotion_content'] : '收获源自分享的快乐，给你好玩儿！让你好看！',
			'logo' => !empty($website_config['logo']) ? $website_config['logo'] : ROOT_PATH . 'public/static/images/logo.png'
		];
		
		return $data;
	}
	
	/**
	 * 处理字符串超出之后隐藏
	 * @param unknown $str
	 * @param unknown $size
	 * @param unknown $width
	 * @param unknown $max_line
	 */
	private function handleStr($str, $size, $width, $max_line)
	{
		if (empty($str)) return $str;
		mb_internal_encoding("UTF-8");
		$letter = [];
		$content = '';
		$line = 1;
		for ($i = 0; $i < mb_strlen($str); $i++) {
			$letter[] = mb_substr($str, $i, 1);
		}
		foreach ($letter as $l) {
			$temp_str = $content . " " . $l;
			$fontBox = imagettfbbox($size, 0, ROOT_PATH . 'public/static/font/Microsoft.ttf', $temp_str);
			if (($fontBox[2] > $width) && ($content !== "")) {
				$content .= "\n";
				$line += 1;
			}
			if ($line <= $max_line) {
				$content .= $l;
			} else {
				$content = mb_substr($content, 0, (mb_strlen($content) - 2)) . '...';
				break;
			}
		}
		return $content;
	}
	
	
	/********************************************************出入库流程 start****************************************************************************/
	
	/***
	 * 入库(收货)
	 * @param array $param
	 */
	public function receive($param)
	{
		$goods_sku_model = new NsGoodsSkuModel();
		$goods_model = new NsGoodsModel();
		
		$sku_id = $param["sku_id"];//sku id
		$num = $param["num"];//入库数量
		$type = $param["type"];//入库类型 1.盘盈入库 2. 退货入库 3. 其它入库
		// 	    $sku_info = $goods_sku_model->getInfo(["sku_id" => $sku_id], "goods_id, stock");
		$goods_id = $param["goods_id"];
		$res = $goods_sku_model->where([ 'sku_id' => $sku_id ])->setInc('stock', $num);
		if ($res <= 0)
			return error([]);
		
		$res = $goods_model->where([ 'goods_id' => $goods_id ])->setInc('stock', $num);
		if ($res <= 0)
			return error([]);
		
		return success(1);
	}
	
	/***
	 * 出库(发货)
	 * @param array $param
	 */
	public function delivery($param)
	{
		
		$goods_sku_model = new NsGoodsSkuModel();
		$goods_model = new NsGoodsModel();
		
		$sku_id = $param["sku_id"];//sku id
		$num = $param["num"];//入库数量
		$type = $param["type"];//入库类型 1.盘亏入库  2.订单发货(销售出库) 3.其他出库
		$goods_id = $param["goods_id"];
		//销售入库的库存在创建订单时,已经提前减去了
		if ($type != 2) {
			// 	        $sku_info = $goods_sku_model->getInfo(["sku_id" => $sku_id], "goods_id, stock");
			// 	        //库存不足
			// 	        if($sku_info["stock"] < $num){
			// 	            return error([], LOW_STOCKS);
			// 	        }
			$res = $goods_sku_model->where([ 'sku_id' => $sku_id, "stock" => [ ">=", $num ] ])->setDec('stock', $num);
			if ($res <= 0)
				return error([], LOW_STOCKS);
			
			//             $goods_info = $goods_model->getInfo(["goods_id" => $goods_id], "stock");
			//             // 库存不足
			//             if($goods_info["stock"] < $num){
			//                 return error([], LOW_STOCKS);
			//             }
			$res = $goods_model->where([ 'goods_id' => $goods_id, "stock" => [ ">=", $num ] ])->setDec('stock', $num);
			if ($res <= 0)
				return error([], LOW_STOCKS);
		}
		
		return success(1);
	}
	/********************************************************出入库流程 end****************************************************************************/
	
	/**
	 * 查询商品信息
	 * @param unknown $condition
	 * @return unknown
	 */
	public function getGoodsSkuDetail($condition)
	{
		$picture = new AlbumPictureModel();
		$sku_model = new NsGoodsSkuModel();
		$goods_model = new NsGoodsModel();
		$goods_sku_info = $sku_model->getInfo([ 'sku_id' => $condition['sku_id'] ]);
		$goods_info = $goods_model->getInfo([ "goods_id" => $goods_sku_info["goods_id"] ]);
		//查询图片
		if (empty($goods_sku_info['picture'])) {
			$goods_sku_info["goods_picture"] = $goods_info['picture'];
		}
		if (empty($goods_sku_info['sku_name'])) {
			$goods_sku_info['sku_name'] = $goods_info['goods_name'];
		}
		$goods_picture = $picture->get($goods_sku_info['goods_picture']);
		if (empty($goods_picture)) {
			$goods_picture = array(
				'pic_cover' => '',
				'pic_cover_big' => '',
				'pic_cover_mid' => '',
				'pic_cover_small' => '',
				'pic_cover_micro' => '',
				"upload_type" => 1,
				"domain" => ""
			);
		}
		$goods_sku_info['picture'] = $goods_picture;
		return $goods_sku_info;
	}
	
	/**
	 * 根据条件获取商品id组
	 */
	public function getGoodsIdsByCondition($params = [])
	{
		try {
			$ns_goods = new NsGoodsModel();
			switch ($params['from_type']) {
				case 'label': // 按标签
					if (!empty($params['label'])) {
						$label_str = "FIND_IN_SET(" . $params['label'] . ", group_id_array)";
						$condition = [
							'' => [ 'exp', Db::raw($label_str) ]
						];
						$goods_arr = $ns_goods->getQuery($condition, 'goods_id');
					}
					break;
				case 'category': // 按分类
					if (!empty($params['category'])) {
						$category_arr = explode(',', $params['category']);
						$goods_arr = $ns_goods->getQuery([ 'category_id_'.count($category_arr) => $category_arr[ (count($category_arr) - 1) ] ], 'goods_id');
					}
					break;
				case 'brand': // 按品牌
					if (!empty($params['brand'])) {
						$goods_arr = $ns_goods->getQuery([ 'brand_id' => $params['brand'] ], 'goods_id');
					}
					break;
				case 'recommend': // 按推荐
					if (!empty($params['recommend'])) {
						if ($params['recommend'] == 1) {
							$goods_arr = $ns_goods->getQuery([ 'is_hot' => 1 ], 'goods_id');
						} elseif ($params['recommend'] == 2) {
							$goods_arr = $ns_goods->getQuery([ 'is_recommend' => 1 ], 'goods_id');
						} elseif ($params['recommend'] == 3) {
							$goods_arr = $ns_goods->getQuery([ 'is_new' => 1 ], 'goods_id');
						}
					}
					break;
				case 'goods_type': // 按类型
					if (!empty($params['goods_type'])) {
						$goods_arr = $ns_goods->getQuery([ 'goods_type' => $params['goods_type'] ], 'goods_id');
					}
					break;
				case 'goods_ids':
					if (!empty($params['goods_ids'])) {
						return explode(',', $params['goods_ids']);
					}
					break;
				case 'search': // 按搜索内容
					if (!empty($params['search'])) {
						$goods_arr = $ns_goods->getQuery([ 'goods_name' => [ 'like', '%' . $params['search'] . '%' ] ], 'goods_id');
					}
					break;
			}
			
			if (!empty($goods_arr)) {
				$goods_id_arr = array_reduce($goods_arr, function ($carry, $item) {
					$data = [ $item['goods_id'] ];
					return array_merge($carry, $data);
				}, []);
				return $goods_id_arr;
			}
		} catch (\Exception $e) {
			Log::write('根据条件获取商品id错误：' . $e->getMessage());
			return null;
		}
	}
}