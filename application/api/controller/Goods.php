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

namespace app\api\controller;

use data\service\Address;
use data\service\Config as ConfigService;
use data\service\Goods as GoodsService;
use data\service\GoodsAttribute;
use data\service\GoodsBrand;
use data\service\GoodsCategory as GoodsCategoryService;
use data\service\Member as MemberService;
use data\service\OrderAction;
use data\service\OrderQuery;
use data\service\Promotion as PromotionService;
use data\service\promotion\GoodsExpress;
use data\service\promotion\GoodsPreference;
use data\service\Shop as ShopService;

class Goods extends BaseApi
{
	
	/**
	 * 获取商品品牌列表
	 */
	public function goodsBrandList()
	{
		$title = "获取商品品牌列表";
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$condition = isset($this->params['condition']) ? $this->params['condition'] : [];
		$order = isset($this->params['order']) ? $this->params['order'] : 'sort desc';
		$field = isset($this->params['field']) ? $this->params['field'] : '';
		
		$goods_brand = new GoodsBrand();
		$list = $goods_brand->getGoodsBrandList($page_index, $page_size, $condition, $order, $field);
		return $this->outMessage($title, $list);
	}
	
	/**
	 * 根据定位查询当前商品的运费
	 */
	public function shippingFeeByLocation()
	{
		$title = '根据定位查询当前商品运费';
		$goods_id = isset($this->params['goods_id']) ? $this->params['goods_id'] : 0;
		if (!empty($goods_id)) {
			$user_location = get_city_by_ip();
			if ($user_location['status'] == 1) {
				// 定位成功，查询当前城市的运费
				$goods_express = new GoodsExpress();
				$address = new Address();
				$province = $address->getProvinceId($user_location["province"]);
				$city = $address->getCityId($user_location["city"]);
				$district = $address->getCityFirstDistrict($city['city_id']);
				$express = $goods_express->getGoodsExpressTemplate($goods_id, $province['province_id'], $city['city_id'], $district);
				
				if (!empty($express) && is_string($express)) {
					if (is_string($express)) {
						$express = str_replace('￥', '', $express);
					}
					$new_express = array();
					$new_express[0] = $express == '免邮' ? array(
						'co_id' => 0,
						'express_fee' => '免邮'
					) : array(
						'co_id' => 0,
						'express_fee' => $express
					);
					$express = $new_express;
				}
				return $this->outMessage($title, $express);
			}
		}
	}
	
	/**
	 * 根据地址id查询当前商品的运费
	 */
	public function getShippingFeeByAddressId()
	{
		$title = "根据地址查询当前商品的运费,传入地址信息id";
		$goods_id = request()->post("goods_id", 0);
		$province_id = request()->post("province_id", 0);
		$city_id = request()->post("city_id", 0);
		$district_id = request()->post("district_id", 0);
		$express = "";
		if (!empty($goods_id)) {
			$goods_express = new GoodsExpress();
			$express = $goods_express->getGoodsExpressTemplate($goods_id, $province_id, $city_id, $district_id);
		}
		
		return $this->outMessage($title, $express);
	}
	
	/**
	 * 根据IP定位查询当前商品的运费
	 */
	public function shippingFeeByIp()
	{
		$goods_id = isset($this->params['goods_id']) ? $this->params['goods_id'] : 0;
		$goods_sku_list = isset($this->params['goods_sku_list']) ? $this->params['goods_sku_list'] : '';
		$province_id = isset($this->params['province_id']) ? $this->params['province_id'] : 0;
		$city_id = isset($this->params['city_id']) ? $this->params['city_id'] : 0;
		$district_id = isset($this->params['district_id']) ? $this->params['district_id'] : 0;
		
		$res = [];
		if (!empty($goods_id)) {
			
			$goods_express = new GoodsExpress();
			$address = new Address();
			$goods_preference = new GoodsPreference();
			$promotion = new PromotionService();
			
			if (empty($province_id) && empty($city_id) && empty($district_id)) {
				
				$user_location = get_city_by_ip();
				$res['user_location'] = $user_location;
				if ($user_location['status'] == 1) {
					
					// 定位成功，查询当前城市的运费
					$province = $address->getProvinceId($user_location["province"]);
					$city = $address->getCityId($user_location["city"]);
					$district = $address->getCityFirstDistrict($city['city_id']);
					
					$province_id = $province['province_id'];
					$city_id = $city['city_id'];
					$district_id = $district;
				}
			}
			
			$express = $goods_express->getGoodsExpressTemplate($goods_id, $province_id, $city_id, $district_id);
			$res['express'] = $express;
			
			$count_money = $goods_preference->getGoodsSkuListPrice($goods_sku_list); // 商品金额
			$promotion_full_mail = $promotion->getPromotionFullMail();
			$no_mail = checkIdIsinIdArr($city['city_id'], $promotion_full_mail['no_mail_city_id_array']);
			if ($no_mail) {
				$promotion_full_mail['is_open'] = 0;
			}
			
			if ($promotion_full_mail['is_open'] == 1) {
				// 满额包邮开启
				if ($count_money >= $promotion_full_mail["full_mail_money"]) {
					$res['express'] = "免邮";
				}
			}
		}
		return $this->outMessage('运费', $res);
	}
	
	/**
	 * 商品评价信息的数量
	 */
	public function goodsEvaluateCount()
	{
		$title = "商品评价信息的数量";
		$goods_id = isset($this->params['goods_id']) ? $this->params['goods_id'] : 0;
		if (empty($goods_id)) {
			return $this->outMessage($title, null, '-50', "无法获取商品信息");
		}
		$goods = new GoodsService();
		$evaluates_count = $goods->getGoodsEvaluateCount($goods_id);
		return $this->outMessage($title, $evaluates_count);
	}
	
	/**
	 * 功能：商品评论列表
	 */
	public function goodsComments()
	{
		$title = "获取商品评论,传入商品参数商品id，comments_type:1,2,3";
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$comments_type = isset($this->params['comments_type']) ? $this->params['comments_type'] : '';
		$condition['goods_id'] = isset($this->params['goods_id']) ? $this->params['goods_id'] : 0;
		if (empty($condition['goods_id'])) {
			return $this->outMessage($title, null, '-50', "无法获取商品信息");
		}
		$order_query = new OrderQuery();
		switch ($comments_type) {
			case 1:
				$condition['explain_type'] = 1;
				break;
			case 2:
				$condition['explain_type'] = 2;
				break;
			case 3:
				$condition['explain_type'] = 3;
				break;
			case 4:
				$condition['image|again_image'] = array(
					'NEQ',
					''
				);
				break;
		}
		$condition['is_show'] = 1;
		$goodsEvaluationList = $order_query->getOrderEvaluateDataList($page_index, $page_size, $condition, 'addtime desc');
		// 查询评价用户的头像
		$memberService = new MemberService();
		foreach ($goodsEvaluationList['data'] as $v) {
			$v["user_img"] = $memberService->getMemberImage($v["uid"]);
		}
		return $this->outMessage($title, $goodsEvaluationList);
	}
	
	/**
	 * 购物车修改数量
	 */
	public function modifyCartNum()
	{
		$title = "修改购物车数量";
		$cart_id = isset($this->params['cart_id']) ? $this->params['cart_id'] : "";
		$num = isset($this->params['num']) ? $this->params['num'] : 0;
		
		if (empty($cart_id)) {
			return $this->outMessage($title, null, '-50', "无法获取购物车信息!");
		}
		if (empty($num)) {
			return $this->outMessage($title, null, '-50', "无法获取商品数量!");
		}
		$applet_goods = new GoodsService();
		$retval = $applet_goods->modifyCartAdjustNumber($cart_id, $num);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 购物车删除
	 */
	public function deleteCart()
	{
		$title = "删除购物车";
		$cart_id_array = isset($this->params['cart_id_array']) ? $this->params['cart_id_array'] : "";
		if (empty($cart_id_array)) {
			return $this->outMessage($title, null, '-50', "无法获取选种商品!");
		}
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息!");
		}
		$applet_goods = new GoodsService();
		$res = $applet_goods->deleteCart($cart_id_array);
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 购物车删除(通过sku)
	 */
	public function deleteCartByGoods()
	{
		$title = "删除购物车";
		$goods_sku_list = isset($this->params['goods_sku_list']) ? $this->params['goods_sku_list'] : "";
		if (empty($goods_sku_list)) {
			return $this->outMessage($title, null, '-50', "无法获取选种商品!");
		}
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息!");
		}
		$order_action = new OrderAction();
		$res = $order_action->deleteCart($goods_sku_list, $this->uid);//删除购物车
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 购物车列表
	 */
	public function cartList()
	{
		$title = "获取购物车信息,需要会员登录";
		$uid = $this->uid;
		if (empty($uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息!");
		}
		$goods = new GoodsService();
		$cart_list = $goods->getCart($uid);
		$list = array();
		$list['cart_list'] = $cart_list;

//		foreach ($list['cart_list'] as $k => $v) {
//
//			if (!empty($list['cart_list'][ $k ]['picture_info']['pic_cover_small'])) {
//				if (strpos($list['cart_list'][ $k ]['picture_info']['pic_cover_small'], "http://") === false && strpos($list['cart_list'][ $k ]['picture_info']['pic_cover_small'], "https://") === false) {
//					$list['cart_list'][ $k ]['picture_info']['pic_cover_small'] = getBaseUrl() . "/" . $list['cart_list'][ $k ]['picture_info']['pic_cover_small'];
//				}
//			}
//		}
		
		// 商品阶梯优惠信息
		$goods_ladder_preferential = array();
		if (count($cart_list) > 0) {
		    $tmp_calc_goods_id = [];//算过的产品不再重新算，否前js重复减价格
			foreach ($cart_list as $v) {
			    if (!in_array($v['goods_id'], $tmp_calc_goods_id)) {
                    $goods_ladder_preferential[] = $goods->getGoodsLadderPreferential([
                        "goods_id" => $v["goods_id"]
                    ], "quantity desc");
			        $tmp_calc_goods_id[] = $v['goods_id'];
                }
			}
		}
		if (!empty($goods_ladder_preferential)) {
			$goods_ladder_preferential = arrayFilter($goods_ladder_preferential);
			$list['goods_ladder_preferential'] = json_encode($goods_ladder_preferential);
		} else {
			$list['goods_ladder_preferential'] = "";
		}
		return $this->outMessage($title, $list);
	}
	
	/**
	 * 获取购物车数量
	 */
	public function cartCount()
	{
		$title = "获取购物车信息,需要会员登录";
		$uid = $this->uid;
		if (empty($uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息!");
		}
		$goods = new GoodsService();
		$cart_count = $goods->getCartCount($uid);
		return $this->outMessage($title, $cart_count);
	}
	
	/**
	 * 添加购物车
	 */
	public function addCart()
	{
		$title = "添加购物车,需要会员登录，以及cart_detail:注意是json序列";
		$goods = new GoodsService();
		$uid = $this->uid;
		if (empty($uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息!");
		}
		
		$cart_detail = isset($this->params['cart_detail']) ? $this->params['cart_detail'] : '';
		
		if (!empty($cart_detail)) {
			$cart_detail = json_decode($cart_detail, true);
		} else {
			return $this->outMessage($title, null, '-50', "无法获取购物车信息!");
		}
		$shop_name = $cart_detail["shop_name"];
		$goods_id = $cart_detail['goods_id'];
		$goods_name = $cart_detail['goods_name'];
		$count = $cart_detail['count'];
		$sku_id = $cart_detail['sku_id'];
		$sku_name = $cart_detail['sku_name'];
		$price = $cart_detail['price'];
		$picture_id = $cart_detail['picture_id'];
		$params = [
			'uid' => $uid,
			'shop_name' => $shop_name,
			'goods_id' => $goods_id,
			'goods_name' => $goods_name,
			'sku_id' => $sku_id,
			'sku_name' => $sku_name,
			'price' => $price,
			'num' => $count,
			'picture_id' => $picture_id,
			'bl_id' => 0
		];
		$retval = $goods->addCart($params);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 商品点赞赠送积分/优惠券
	 */
	public function giveGifts()
	{
		$title = "商品点赞获赠积分";
		$goods_id = $this->get('goods_id', 0);
		
		if (empty($this->uid)) {
			return $this->outMessage($title, -1, '-9999', "无法获取会员登录信息");
		}
		if (empty($goods_id)) {
			return $this->outMessage($title, -1, '-50', "无法获取商品信息");
		}
		
		$goods = new GoodsService();
		$click_detail = $goods->getGoodsSpotFabulous($this->uid, $goods_id);
		if (empty($click_detail)) {
			$member = new MemberService();
			$retval = $member->memberAction([ 'type' => 'NsMemberPraise', 'uid' => $this->uid, 'goods_id' => $goods_id ]);
			return $this->outMessage($title, $retval[0]);
		} else {
			return $this->outMessage($title, -1);
		}
	}
	
	/**
	 * 指定商品优惠券
	 */
	public function goodsCouponList()
	{
		$goods = new GoodsService();
		$goods_id = $this->get($this->params['goods_id'], 0);
		$goods_coupon_list = $goods->getGoodsCoupon($goods_id);
		return $this->outMessage("商品优惠券列表", $goods_coupon_list);
	}
	
	/**
	 * 优惠券列表
	 */
	public function couponList()
	{
		$title = "获取优惠券列表";
		$promotion = new PromotionService();
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$order = isset($this->params['order']) ? $this->params['order'] : "create_time desc";
		$condition = '(count > 0  AND end_time > ' . time() . ' AND is_show = 1 AND term_of_validity_type = 0)';
		$condition .= 'OR (term_of_validity_type = 1 AND is_show = 1)';
		$promotion_list = $promotion->getCouponTypeInfoList($page_index, $page_size, $condition, $order, $this->uid);
		return $this->outMessage($title, $promotion_list);
	}
	
	/**
	 * 优惠券详情
	 */
	public function getCoupon()
	{
		$title = "获取优惠券详情";
		$coupon_type_id = isset($this->params['coupon_type_id']) ? $this->params['coupon_type_id'] : 0;
		if (empty($coupon_type_id)) {
			return $this->outMessage($title, null, '-50', "无法获取优惠券信息");
		}
		$promotion = new PromotionService();
		$condition['coupon_type_id'] = [
			'eq',
			$coupon_type_id
		];
		$data = $promotion->getCouponTypeDetail($coupon_type_id);
		$path = $this->showMemberCouponQrcode($coupon_type_id);
		
		$data = array(
			'data' => $data,
			'path' => $path
		);
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 制作用户分享优惠券二维码
	 */
	private function showMemberCouponQrcode($coupon_type_id)
	{
		$uid = !empty($this->uid) ? $this->uid : 0;
		$url = __URL(__URL__ . '/wap/goods/couponreceive?coupon_type_id=' . $coupon_type_id . '&source_uid=' . $uid);
		
		// 查询并生成二维码
		$upload_path = "upload/qrcode/coupon_qrcode";
		if (!file_exists($upload_path)) {
			mkdir($upload_path, 0777, true);
		}
		$path = $upload_path . '/coupon_' . $coupon_type_id . '_' . $uid . '.png';
		if (!file_exists($path)) {
			getQRcode($url, $upload_path, "coupon_" . $coupon_type_id . '_' . $uid);
		}
		return $path;
	}
	
	/**
	 * 领取商品优惠劵
	 */
	public function receiveGoodsCoupon()
	{
		$title = "领取商品优惠券";
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "当前未登录");
		}
		$coupon_type_id = isset($this->params['coupon_type_id']) ? $this->params['coupon_type_id'] : 0;
		if (empty($coupon_type_id)) {
			return $this->outMessage($title, null, '-50', "无法获取优惠券信息");
		}
		$member = new MemberService();
		$res = $member->memberGetCoupon($this->uid, $coupon_type_id, 3);
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 专题活动列表页面
	 */
	public function promotionTopic()
	{
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$title = '专题活动列表';
		$promotion = new PromotionService();
		$list = $promotion->getPromotionTopicList($page_index, $page_size, [
			'status' => 1,
			"start_time" => array(
				"<",
				time()
			),
			"end_time" => array(
				">",
				time()
			)
		]);
		
		return $this->outMessage($title, $list);
	}
	
	/**
	 * 专题详情
	 */
	public function promotionTopicDetail()
	{
		$title = '专题详情';
		$topic_id = isset($this->params['topic_id']) ? $this->params['topic_id'] : 0;
		if (!is_numeric($topic_id)) {
			return $this->outMessage($title, null, -10, '没有获取到专题信息');
		}
		$promotion = new PromotionService();
		$detail = $promotion->getPromotionTopicDetail($topic_id);
		return $this->outMessage($title, $detail);
	}
	
	/**
	 * 阶梯优惠
	 */
	public function goodsLadderPreferentialList()
	{
		$goods = new GoodsService();
		$goods_id = isset($this->params['goods_id']) ? $this->params['goods_id'] : 0;
		$order = isset($this->params['order']) ? $this->params['order'] : "";
		$filed = isset($this->params['filed']) ? $this->params['filed'] : "";
		$goods_ladder_preferential_list = $goods->getGoodsLadderPreferential([ 'goods_id' => $goods_id ], $order, $filed);
		
		return $this->outMessage("", $goods_ladder_preferential_list);
	}
	
	/**
	 * 查询当前用户所购买的商品限购，是否能够继续购买
	 */
	public function goodsPurchaseRestriction()
	{
		$goods = new GoodsService();
		$goods_id = isset($this->params['goods_id']) ? $this->params['goods_id'] : 0;
		$num = isset($this->params['num']) ? $this->params['num'] : 0;
		$goods_purchase_restriction = $goods->getGoodsPurchaseRestrictionForCurrentUser($goods_id, $num);
		
		return $this->outMessage("", $goods_purchase_restriction);
	}
	
	/**
	 * 判断用户是否收藏商品/店铺
	 */
	public function whetherCollection()
	{
		$member = new MemberService();
		$fav_id = isset($this->params['fav_id']) ? $this->params['fav_id'] : "";
		$fav_type = isset($this->params['fav_type']) ? $this->params['fav_type'] : "";
		$is_member_fav_goods = $member->getIsMemberFavorites($this->uid, $fav_id, $fav_type);
		return $this->outMessage("", $is_member_fav_goods);
	}
	
	/**
	 * 获取限时折扣商品列表
	 */
	public function goodsDiscountList()
	{
		$goods_service = new GoodsService();
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$category_id = isset($this->params['category_id']) ? $this->params['category_id'] : "";
		$condition['ng.state'] = 1;
		$condition['npdg.status'] = 1;
		if (!empty($category_id)) {
			$condition['category_id_1'] = $category_id;
		}
		
		$discount_list = $goods_service->getDiscountGoodsList($page_index, $page_size, $condition, 'discount_goods_id desc');
		
		return $this->outMessage("", $discount_list);
	}
	
	/**
	 * 属性筛选
	 */
	public function attributeSelection()
	{
		$goods_category_service = new GoodsCategoryService();
		$category_id = isset($this->params['category_id']) ? $this->params['category_id'] : "";
		
		$goods_category_info = $goods_category_service->getGoodsCategoryDetail($category_id);
		$attr_id = $goods_category_info["attr_id"];
		$goods_attribute = new GoodsAttribute();
		$attribute_detail = $goods_attribute->getAttributeDetail($attr_id, [
			'is_search' => 1
		]);
		$attribute_list = array();
		if (!empty($attribute_detail['value_list']['data'])) {
			$attribute_list = $attribute_detail['value_list']['data'];
			foreach ($attribute_list as $k => $v) {
				$value_items = explode(",", $v['value']);
				$new_value_items = array();
				foreach ($value_items as $ka => $va) {
					$new_value_items[ $ka ]['value'] = $va;
					$new_value_items[ $ka ]['value_str'] = $attribute_list[ $k ]['attr_value_name'] . ',' . $va . ',' . $attribute_list[ $k ]['attr_value_id'];
				}
				$attribute_list[ $k ]['value'] = trim($v["value"]);
				$attribute_list[ $k ]['value_items'] = $new_value_items;
			}
		}
		$attr_list = $attribute_list;
		
		return $this->outMessage("属性筛选", $attr_list);
	}
	
	/**
	 * 根据商品分类id进行品牌筛选
	 */
	public function brandSelection()
	{
		$goods_category_service = new GoodsCategoryService();
		$category_id = isset($this->params['category_id']) ? $this->params['category_id'] : 0;
		if ($category_id != "") {
			// 查询品牌列表，用于筛选
			$category_brands = $goods_category_service->getGoodsBrandsByGoodsAttr($category_id);
			return $this->outMessage("品牌列表", $category_brands);
		}
	}
	
	/**
	 * 在做商品列表的时候调整该接口
	 */
	public function categoryPriceGrades()
	{
		$goods_category_service = new GoodsCategoryService();
		$category_id = isset($this->params['category_id']) ? $this->params['category_id'] : "";
		if ($category_id != "") {
			
			// 查询价格区间，用于筛选
			$category_price_grades = $goods_category_service->getGoodsCategoryPriceGrades($category_id);
			foreach ($category_price_grades as $k => $v) {
				$category_price_grades[ $k ]['price_str'] = $v[0] . '-' . $v[1];
			}
			
			return $this->outMessage("", $category_price_grades);
		}
	}
	
	/**
	 * 在做商品列表的时候调整该接口
	 */
	public function goodsSpecArray()
	{
		$goods_category_service = new GoodsCategoryService();
		$goods = new GoodsService();
		
		$category_id = isset($this->params['category_id']) ? $this->params['category_id'] : "";
		
		if ($category_id) {
			$goods_category_info = $goods_category_service->getGoodsCategoryDetail($category_id);
			
			$attr_id = $goods_category_info["attr_id"];
			// 查询商品分类下的属性和规格集合
			$goods_attribute_service = new GoodsAttribute();
			$goods_attribute = $goods_attribute_service->getAttributeInfo([
				"attr_id" => $attr_id
			]);
			// 查询本商品类型下的关联规格
			$goods_spec_array = array();
			if ($goods_attribute["spec_id_array"] != "") {
				$goods_spec_array = $goods->getGoodsSpecQuery([
					"spec_id" => [
						"in",
						$goods_attribute["spec_id_array"]
					],
					'goods_id' => 0
				]);
				foreach ($goods_spec_array as $k => $v) {
					foreach ($v["values"] as $z => $c) {
						$c["value_str"] = $c['spec_id'] . ':' . $c['spec_value_id'];
					}
				}
				sort($goods_spec_array);
			}
			return $this->outMessage("关联规格", $goods_spec_array);
		}
	}
	
	/**
	 * 商品咨询
	 */
	public function goodsConsultList()
	{
		$goods = new GoodsService();
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$ct_id = isset($this->params['ct_id']) ? $this->params['ct_id'] : 0;
		$goods_id = isset($this->params['goods_id']) ? $this->params['goods_id'] : 0;
		$order = isset($this->params['order']) ? $this->params['order'] : "consult_addtime desc";
		if (empty($goods_id)) {
			return $this->outMessage("商品购买咨询", null, -1, "缺少必要参数");
		}
		
		if (!empty($ct_id)) {
			$condition['ct_id'] = $ct_id;
		}
		$condition['goods_id'] = $goods_id;
		$consult_list = $goods->getConsultList($page_index, $page_size, $condition, $order);
		return $this->outMessage("商品购买咨询", $consult_list);
	}
	
	/**
	 * 商品咨询添加
	 */
	public function addGoodsConsult()
	{
		$title = '商品咨询添加';
		$randomCode = isset($this->params['random_code']) ? $this->params['random_code'] : '';
		if (!captcha_check($randomCode)) {
			return $this->outMessage($title, -1, '-10', "验证码错误!");
		}
		$goods_id = isset($this->params['goods_id']) ? $this->params['goods_id'] : '';
		$shop_name = isset($this->params['shop_name']) ? $this->params['shop_name'] : '';
		$goods_name = isset($this->params['goods_name']) ? $this->params['goods_name'] : '';
		$ct_id = isset($this->params['ct_id']) ? $this->params['ct_id'] : '';
		$consult_content = isset($this->params['consult_content']) ? $this->params['consult_content'] : '';
		$member = new MemberService();
		$member_info = $member->getMemberDetail();
		$member_name = empty($member_info) ? '' : $member_info['member_name'];
		$goods = new GoodsService();
		$data = array(
			'goods_id' => $goods_id,
			'goods_name' => $goods_name,
			'uid' => $this->uid,
			'member_name' => $member_name,
			'shop_id' => $this->instance_id,
			'shop_name' => $shop_name,
			'ct_id' => $ct_id,
			'consult_content' => $consult_content,
			'consult_addtime' => time()
		);
		$retval = $goods->addConsult($data);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 商品详情，包括活动
	 */
	public function goodsDetail()
	{
		$title = "商品详情";
		$goods_id = $this->get('goods_id', 0);
		$sku_id = $this->get('sku_id', 0);
		$bargain_id = $this->get('bargain_id', 0);
		$group_id = $this->get('group_id', 0);
		
		if (empty($goods_id) && empty($sku_id)) {
			return $this->outMessage($title, null, -1, "缺少必要参数");
		}
		
		$goods = new GoodsService();
		$member = new MemberService();
		
		// 商品详情
		$param = [
			"goods_id" => $goods_id,
			"sku_id" => $sku_id,
			"bargain_id" => $bargain_id,
			"group_id" => $group_id
		];
		$detail = $goods->getBasisGoodsDetail($param);
		if (empty($detail)) {
			$this->outMessage($title, null, -50, "没有获取到商品信息");
		}
		
		$data = [];
		$data['goods_id'] = $goods_id;
		$data['sku_id'] = $sku_id;
		
		$member->addMemberViewHistory($goods_id);
		
		$data['goods_detail'] = $detail;
		
		return $this->outMessage('商品详情', $data);
	}
	
	/**
	 * 更新商品点击量
	 */
	public function modifyGoodsClicks()
	{
		$title = "更新商品点击量";
		$goods_id = isset($this->params['goods_id']) ? $this->params['goods_id'] : 0;
		if (empty($goods_id)) {
			return $this->outMessage($title, 0, -1, "缺少必要参数");
		}
		$goods = new GoodsService();
		$res = $goods->modifyGoodsClicks($goods_id);
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 添加足迹
	 */
	public function addGoodsBrowse()
	{
		$title = "添加足迹";
		$goods_id = isset($this->params['goods_id']) ? $this->params['goods_id'] : 0;
		if (empty($goods_id)) {
			return $this->outMessage($title, 0, -1, "缺少必要参数");
		}
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息!");
		}
		$goods = new GoodsService();
		$res = $goods->addGoodsBrowse($goods_id, $this->uid);
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 品牌信息
	 */
	public function goodsBrandInfo()
	{
		$brand_id = isset($this->params['brand_id']) ? $this->params['brand_id'] : 0;
		$goods_brand = new GoodsBrand();
		$brand_detail = $goods_brand->getGoodsBrandInfo($brand_id);
		return $this->outMessage("商品详情", $brand_detail);
	}
	
	/**
	 * 商品信息，不涉及到活动
	 */
	public function goodsInfo()
	{
		$goods_id = isset($this->params['goods_id']) ? $this->params['goods_id'] : 0;
		$goods_service = new GoodsService();
		$detail = $goods_service->getGoodsDetail($goods_id);
		return $this->outMessage("商品详情", $detail);
	}
	
	/**
	 * 商品列表，多条件
	 */
	public function goodsListByConditions()
	{
		$category_id = $this->get('category_id', ''); // 商品分类
		$keyword = $this->get('keyword', ''); // 关键词
		$shipping_fee = $this->get('shipping_fee', ''); // 是否包邮，0：包邮；1：运费价格
		$stock = $this->get('stock', ''); // 仅显示有货，大于0
		$page_index = $this->get('page_index', 1); // 当前页
		$order = $this->get('order', ''); // 排序字段,order by ziduan
		$sort = $this->get('sort', ''); // 排序方式
		$brand_id = $this->get('brand_id', ''); // 品牌id
		$brand_name = $this->get('brand_name', ''); // 品牌名牌
		$min_price = $this->get('min_price', ''); // 价格区间,最小
		$max_price = $this->get('max_price', ''); // 最大
		$province_id = $this->get('province_id', ''); // 商品所在地
		$province_name = $this->get('province_name', ''); // 所在地名称
		$attr = $this->get('attr', ''); // 属性值
		$spec = $this->get('spec', ''); // 规格值
		$platform_proprietary = $this->get('platform_proprietary', ''); // 平台自营 shopid== 1
		$page_size = $this->get('page_size', PAGESIZE);
		
		$data['attr_str'] = $attr;
		$data['spec_str'] = $spec;
		
		// 将属性条件字符串转化为数组
		$attr_array = $this->stringChangeArray($attr);
		$data['attr_array'] = $attr_array;
		
		// 规格转化为数组
		if ($spec != "") {
			$spec_array = explode(";", $spec);
		} else {
			$spec_array = array();
		}
		$spec_remove_array = array();
		foreach ($spec_array as $k => $v) {
			$spec_remove_array[] = explode(":", $v);
		}
		
		if (!is_numeric($category_id)) {
			$category_id = "";
		}
		
		// 过滤参数
		if ($keyword !== "") {
			$keyword = ihtmlspecialchars($keyword);
		}
		
		// 如果包邮参数不为空，则进行过滤判断
		if ($shipping_fee !== "") {
			if ($shipping_fee != 0 && $shipping_fee != 1) {
				// 非法参数进行过滤
				$shipping_fee = "";
			}
		}
		
		// 仅显示有货
		if ($stock != "") {
			if (!is_numeric($stock)) {
				// 非法参数进行过滤
				$stock = "";
			}
		}
		
		// 如果排序方式不为空，则进行过滤
		if ($sort != "") {
			if ($sort != "desc" && $sort != "asc") {
				// 非法参数进行过滤
				$sort = "";
			}
		}
		
		// 如果排序字段不为空，则进行过滤判断 排序方式 默认按排序号倒序，创建时间倒序排列
		if ($order != "") {
			if ($order != "sales" && $order != "is_new" && $order != "price") {
				// 非法参数进行过滤
				$orderby = "ng.sort desc, ng.goods_id desc";
			} else {
				if ($order == "is_new") {
					$orderby = "ng.create_time " . $sort;
				} else {
					$orderby = "ng." . $order . " " . $sort;
				}
			}
		} else {
			$orderby = "ng.sort desc, ng.goods_id desc";
		}
		
		if ($min_price != "" && $max_price != "") {
			if (!is_numeric($min_price)) {
				$min_price = "";
			}
			if (!is_numeric($max_price)) {
				$max_price = "";
			}
		}
		
		if ($province_id != "") {
			if (!is_numeric($province_id)) {
				$province_id = "";
			}
		}
		
		$data['order'] = $order;
		$data['sort'] = $sort;
		$goods_category = new GoodsCategoryService();
		$goods = new GoodsService();
		if ($category_id != "") {
			// 获取商品分类下的品牌列表、价格区间
			
			// 查询品牌列表，用于筛选 页面展示
			$category_brands = $goods_category->getGoodsBrandsByGoodsAttr($category_id);
			
			// 查询价格区间，用于筛选 选择价格区间 需优化
			$category_price_grades = $goods_category->getGoodsCategoryPriceGrades($category_id);
			
			$category_count = 0; // 默认没有数据
			if ($category_brands != "") {
				$category_count = 1; // 有数据
			}
			$goods_category_info = $goods_category->getGoodsCategoryDetail($category_id);
			$data['curr_category_name'] = $goods_category_info['category_name'];
			
			$attr_id = $goods_category_info["attr_id"];
			
			// 查询商品分类下的属性和规格集合
			$goods_attribute_service = new GoodsAttribute();
			$goods_attribute = $goods_attribute_service->getAttributeInfo([
				"attr_id" => $attr_id
			]);
			
			$attribute_detail = $goods_attribute_service->getAttributeDetail($attr_id, [
				'is_search' => 1
			]);
			
			$attribute_list = array();
			if (!empty($attribute_detail['value_list']['data'])) {
				$attribute_list = $attribute_detail['value_list']['data'];
				foreach ($attribute_list as $k => $v) {
					$is_unset = 0;
					if (!empty($attr_array)) {
						foreach ($attr_array as $t => $m) {
							if (trim($v["attr_value_id"]) == trim($m[2])) {
								unset($attribute_list[ $k ]);
								$is_unset = 1;
							}
						}
					}
					if ($is_unset == 0) {
						$value_items = explode(",", $v['value']);
						$attribute_list[ $k ]['value'] = trim($v["value"]);
						$attribute_list[ $k ]['value_items'] = $value_items;
					}
				}
			}
			$attr_list = $attribute_list;
			
			// 查询本商品类型下的关联规格
			$goods_spec_array = array();
			if ($goods_attribute["spec_id_array"] != "") {
				$goods_spec_array = $goods->getGoodsSpecQuery([
					"spec_id" => [
						"in",
						$goods_attribute["spec_id_array"]
					],
					'is_screen' => 1,
					'goods_id' => 0
				]);
				foreach ($goods_spec_array as $k => $v) {
					if (!empty($spec_remove_array)) {
						foreach ($spec_remove_array as $t => $m) {
							if ($v["spec_id"] == $m[0]) {
								$spec_remove_array[ $t ][2] = $v["spec_name"];
								foreach ($v["values"] as $z => $c) {
									if ($c["spec_value_id"] == $m[1]) {
										$spec_remove_array[ $t ][3] = $c["spec_value_name"];
									}
								}
								unset($goods_spec_array[ $k ]);
							}
						}
					}
				}
				sort($goods_spec_array);
			}
			
			$data['attr_or_spec'] = $attr_list;
			$data['category_brands'] = $category_brands;
			$data['category_count'] = $category_count;
			$data['category_price_grades'] = $category_price_grades;
			$data['category_price_grades_count'] = count($category_price_grades);
			
			$data['goods_spec_array'] = $goods_spec_array;
			$data['curr'] = 0;
		}
		
		// -----------------查询条件筛选---------------------
		$data['category_id'] = $category_id;
		$data['brand_id'] = $brand_id;
		$data['brand_name'] = $brand_name; // 品牌ID
		$data['min_price'] = $min_price;
		$data['max_price'] = $max_price;
		$data['shipping_fee'] = $shipping_fee; // 是否包邮
		$data['stock'] = $stock; // 仅显示有货
		$data['platform_proprietary'] = $platform_proprietary; // 平台自营
		$data['province_name'] = $province_name;
		
		$attr_url = "";
		if ($attr != "") {
			$attr_url .= "&attr=$attr";
		}
		if ($spec != "") {
			$attr_url .= "&spec=$spec";
		}
		$data['attr_url'] = $attr_url;
		
		$params['category_id'] = $category_id;
		$params['brand_id'] = $brand_id;
		$params['min_price'] = $min_price;
		$params['max_price'] = $max_price;
		$params['keyword'] = $keyword;
		$params['page_index'] = $page_index;
		$params['page_size'] = $page_size;
		$params['order'] = $orderby;
		$params['shipping_fee'] = $shipping_fee;
		$params['stock'] = $stock;
		$params['platform_proprietary'] = $platform_proprietary;
		$params['province_id'] = $province_id;
		$params['attr_array'] = $attr_array;
		$params['spec_array'] = $spec_array;
		
		$goods_list = $this->getGoodsListByConditions($params);
		$data['goods_list'] = $goods_list;
		$current_category = [
			"category_id" => 0,
			"category_name" => "全部分类",
		];
		if (!$category_id == "") {
			$current_category = $goods_category->getCategoryParentQuery($category_id);
			$current_category = $current_category[0];
		}
		$data['spec_array'] = $spec_remove_array;
		$data['current_category'] = $current_category;
		$data['total_count'] = $goods_list['total_count'];
		$data['page_index'] = $page_index;
		
		return $this->outMessage('商品列表', $data);
	}
	
	private function getGoodsListByConditions($params)
	{
		$goods = new GoodsService();
		$condition = null;
		
		if ($params['category_id'] != "") {
			// 商品分类Id
			$condition["ng.category_id"] = $params['category_id'];
		}
		
		// 品牌Id
		if ($params['brand_id'] != "") {
			$condition["ng.brand_id"] = [
				"in",
				$params['brand_id']
			];
		}
		
		// 价格区间
		if ($params['max_price'] != "") {
			$condition["ng.promotion_price"] = [
				[
					">=",
					$params['min_price']
				],
				[
					"<=",
					$params['max_price']
				]
			];
		}
		
		// 关键词
		if ($params['keyword'] != "") {
			$condition["ng.goods_name|ng.keywords"] = array(
				"like",
				"%" . $params['keyword'] . "%"
			);
		}
		
		// 包邮
		if ($params['shipping_fee'] != "") {
			$condition["ng.shipping_fee"] = $params['shipping_fee'];
		}
		
		// 仅显示有货
		if ($params['stock'] != "") {
			$condition["ng.stock"] = array(
				">",
				$params['stock']
			);
		}
		
		// 平台直营
		if ($params['platform_proprietary'] != "") {
			$condition["ng.shop_id"] = $params['platform_proprietary'];
		}
		
		// 商品所在地
		if ($params['province_id'] != "") {
			$condition["ng.province_id"] = $params['province_id'];
		}
		// 属性 (条件拼装)
		$array_count = count($params['attr_array']);
		$attr_array = $params['attr_array'];
		$goodsid_str = "";
		$attr_str_where = "";
		if (!empty($attr_array)) {
			// 循环拼装sql属性条件
			foreach ($attr_array as $k => $v) {
				if ($attr_str_where == "") {
					$attr_str_where = "(attr_value_id = '$v[2]' and attr_value_name='$v[1]')";
				} else {
					$attr_str_where = $attr_str_where . " or " . "(attr_value_id = '$v[2]' and attr_value_name='$v[1]')";
				}
			}
			if ($attr_str_where != "") {
				$goods_attribute = new GoodsAttribute();
				$attr_query = $goods_attribute->getGoodsAttributeQuery($attr_str_where);
				
				$attr_array = array();
				foreach ($attr_query as $t => $b) {
					$attr_array[ $b["goods_id"] ][] = $b;
				}
				$goodsid_str = "0";
				foreach ($attr_array as $z => $x) {
					if (count($x) == $array_count) {
						if ($goodsid_str == "") {
							$goodsid_str = $z;
						} else {
							$goodsid_str = $goodsid_str . "," . $z;
						}
					}
				}
			}
		}
		
		// 规格条件拼装
		$spec_count = count($params['spec_array']);
		$spec_where = array();
		
		if ($spec_count > 0) {
			foreach ($params['spec_array'] as $k => $v) {
				$tmp_array = explode(':', $v);
				// 得到规格名称
				$spec_info = $goods->getGoodsAttributeList([
					"spec_id" => $tmp_array[0]
				], 'spec_name', '');
				$spec_name = $spec_info[0]["spec_name"];
				// 得到规格值名称
				$spec_value_info = $goods->getGoodsAttributeValueList([
					"spec_value_id" => $tmp_array[1]
				], 'spec_value_name');
				$spec_value_name = $spec_value_info[0]["spec_value_name"];
				if (!empty($spec_name)) {
					$spec_where[] = array(
						'like',
						'%' . $spec_name . '%'
					);
				}
				if (!empty($spec_value_name)) {
					$spec_where[] = array(
						'like',
						'%' . $spec_value_name . '%'
					);
				}
			}
			if (!empty($spec_where)) {
				$condition["ng.goods_spec_format"] = [
					$spec_where
				];
			}
		}
		if ($goodsid_str != "") {
			$condition["goods_id"] = [
				"in",
				$goodsid_str
			];
		}
		
		$condition['ng.state'] = 1;
		$list = $goods->getGoodsListNew($params['page_index'], $params['page_size'], $condition, $params['order']);
		return $list;
	}
	
	/**
	 * 将属性字符串转化为数组
	 */
	private function stringChangeArray($string)
	{
		if (trim($string) != "") {
			$temp_array = explode(";", $string);
			$attr_array = array();
			foreach ($temp_array as $k => $v) {
				if (strpos($v, ",") === false) {
					$attr_array = array();
					break;
				} else {
					$v_array = explode(",", $v);
					if (count($v_array) != 3) {
						$attr_array = array();
						break;
					} else {
						$attr_array[] = $v_array;
					}
				}
			}
			return $attr_array;
		} else {
			return array();
		}
	}
	
	/**
	 * 商品列表
	 */
	public function goodsList()
	{
		$page_index = $this->get('page_index', 1);
		$page_size = $this->get('page_size', PAGESIZE);
		$condition = $this->get('condition', []);
		$order = $this->get('order', '');
		if (!empty($condition) && !is_array($condition) && json_decode($condition)) {
			$condition = json_decode($condition, true);
		}
		
		$goods = new GoodsService();
		$list = $goods->getGoodsList($page_index, $page_size, $condition, $order);
		return $this->outMessage('商品列表', $list);
	}
	
	/**
	 * 猜你喜欢
	 */
	public function guessMemberLikes()
	{
		$member = new MemberService();
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$guess_member_likes = $member->getGuessMemberLikes($page_index, $page_size);
		return $this->outMessage('猜您喜欢', $guess_member_likes);
	}
	
	/**
	 * 获取最新的限时折扣活动
	 */
	public function newestDiscount()
	{
		$promotion_service = new PromotionService();
		$res = $promotion_service->getNewestDiscount();
		return $this->outMessage('获取一条最新的限时折扣活动', $res);
	}
	
	/**
	 * 商品分类树结构
	 */
	public function goodsCategoryTree()
	{
		$goodscategory = new GoodsCategoryService();
		$goods_category_one = $goodscategory->getCategoryTreeUseInShopIndex();
		return $this->outMessage("商品分类树结构", $goods_category_one);
	}
	
	/**
	 * 根据上级分类id获取商品分类列表
	 */
	public function goodsCategoryListByParentId()
	{
		$pid = isset($this->params['pid']) ? $this->params['pid'] : 0;
		$goods_category = new GoodsCategoryService();
		$list = $goods_category->getGoodsCategoryTree($pid);
		return $this->outMessage('根据上级分类id获取商品分类列表', $list);
	}
	
	/**
	 * PC端楼层
	 */
	public function goodsCategoryBlockPc()
	{
		$shop = new ShopService();
		$config = new ConfigService();
		$template_config = $config->getUsePCTemplate(0);
		$pc_template = !empty($template_config['value']) ? $template_config['value'] : 'default';
		$block_list = $shop->getGoodsFloorList(1, 0, [ 'is_use' => 1, 'pc_template' => $pc_template ], 'sort desc', '*');
		$pc_path = __ROOT__ . '/template/web/' . $pc_template . '/block';
		if ($block_list['data']) {
			$html = "";
			foreach ($block_list['data'] as $k => $v) {
				$data = json_decode($v['value'], true);
				$data = $shop->formatBlockData($data);
				$this->assign("name", $v['name']);
				$this->assign("data", $data);
				$this->assign("pc_path", $pc_path);
				$html .= $this->fetch('.' . DS . 'template' . DS . 'web' . DS . $pc_template . DS . 'block' . DS . $v['block_template']);
			}
		} else {
			$html = '';
		}
		
		return $this->outMessage("", $html);
	}
	
	/**
	 * WAP端商品分类楼层，后续做楼层的时候要跟PC端合并
	 */
	public function goodsCategoryBlockWap()
	{
		$show_num = $this->get('show_num', 4);
		$shop = new ShopService();
		$res = $shop->getGoodsRecommend(1, 0);
		return $this->outMessage("获取商品分类楼层", $res);
	}
	
	/**
	 * 商品分类展示类型
	 */
	public function goodsCategoryShowType()
	{
		$web_config = new ConfigService();
		$show_type = $web_config->getWapCategoryDisplay($this->instance_id);
		$show_type = json_decode($show_type, true);
		return $this->outMessage("获取商品分类展示类型", $show_type);
	}
	
	/**
	 * 商品分类列表
	 */
	public function goodsCategoryList()
	{
		$title = '商品分类列表';
		$goods_category = new GoodsCategoryService();
		$goods_category_list = $goods_category->getCategoryTreeUseInShopIndex();
		// 计算补足数量
		foreach ($goods_category_list as $k => $v) {
			$num = 0;
			if (count($v["child_list"]) < 3) {
				$num = 3 - count($v["child_list"]);
			}
			if (count($v["child_list"]) > 3) {
				$max_row = (count($v["child_list"]) + 1) / 4;
				$max_row = ceil($max_row);
				$num = $max_row * 4 - (count($v["child_list"]) + 1);
			}
			$goods_category_list[ $k ]['num'] = $num;
			
			//php需要用拼接好的地址
//			if (strpos($goods_category_list[ $k ]['category_pic'], "http://") === false && strpos($goods_category_list[ $k ]['category_pic'], "https://") === false) {
//				$goods_category_list[ $k ]['category_pic'] = getBaseUrl() . "/" . $goods_category_list[ $k ]['category_pic'];
//			}
//
//			foreach ($goods_category_list[ $k ]['child_list'] as $child_k => $child_v) {
//				if (strpos($goods_category_list[ $k ]['child_list'][ $child_k ]["category_pic"], "http://") === false && strpos($goods_category_list[ $k ]['child_list'][ $child_k ]["category_pic"], "https://") === false) {
//					$goods_category_list[ $k ]['child_list'][ $child_k ]["category_pic"] = getBaseUrl() . "/" . $goods_category_list[ $k ]['child_list'][ $child_k ]["category_pic"];
//				}
//
//				if (!empty($goods_category_list[ $k ]['child_list'][ $child_k ]['child_list'])) {
//					foreach ($goods_category_list[ $k ]['child_list'][ $child_k ]['child_list'] as $third_k => $third_v) {
//						if (strpos($goods_category_list[ $k ]['child_list'][ $child_k ]['child_list'][ $third_k ]["category_pic"], "http://") === false && strpos($goods_category_list[ $k ]['child_list'][ $child_k ]['child_list'][ $third_k ]["category_pic"], "https://") === false) {
//							$goods_category_list[ $k ]['child_list'][ $child_k ]['child_list'][ $third_k ]["category_pic"] = getBaseUrl() . "/" . $goods_category_list[ $k ]['child_list'][ $child_k ]['child_list'][ $third_k ]["category_pic"];
//						}
//					}
//				}
//			}
		}
		return $this->outMessage($title, $goods_category_list);
	}
	
	/**
	 * 新品
	 */
	public function newGoodsList()
	{
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$category_id = isset($this->params['category_id']) ? $this->params['category_id'] : 0;
		$goods_service = new GoodsService();
		$condition = array(
			"ng.state" => 1,
			"ng.is_new" => 1
		);
		if (!empty($category_id)) {
			$condition['ng.category_id_1|ng.category_id_2|ng.category_id_3'] = $category_id;
		}
		$goods_field = "ng.goods_id,ng.goods_name,ng_sap.pic_cover_mid,ng.promotion_price,ng.stock,ng.sales,ng.point_exchange,ng.point_exchange_type,ng.shipping_fee";
		$list = $goods_service->getGoodsQueryLimit($condition, $goods_field, $page_size);
		return $this->outMessage("新品查询", $list);
	}
	
	/**
	 * 精品
	 */
	public function recommendGoodsList()
	{
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$goods_service = new GoodsService();
		$condition = array(
			"ng.state" => 1,
			"ng.is_recommend" => 1
		);
		$goods_field = "ng.goods_id,ng.goods_name,ng_sap.pic_cover_mid,ng.promotion_price,ng.stock,ng.sales,ng.point_exchange,ng.point_exchange_type,ng.shipping_fee";
		$list = $goods_service->getGoodsQueryLimit($condition, $goods_field, $page_size);
		return $this->outMessage("精品查询", $list);
	}
	
	/**
	 * 热卖商品
	 */
	public function hotGoodsList()
	{
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$goods_service = new GoodsService();
		$condition = array(
			"ng.state" => 1,
			"ng.is_hot" => 1
		);
		$goods_field = "ng.goods_id,ng.goods_name,ng_sap.pic_cover_mid,ng.promotion_price,ng.stock,ng.sales,ng.point_exchange,ng.point_exchange_type,ng.shipping_fee";
		$list = $goods_service->getGoodsQueryLimit($condition, $goods_field, $page_size);
		
		return $this->outMessage("热卖商品查询", $list);
	}
	
	/**
	 * 销量排行榜
	 */
	public function saleGoodsList()
	{
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$goods_service = new GoodsService();
		$goods_field = "ng.goods_id,ng.goods_name,ng_sap.pic_cover_mid,ng.promotion_price,ng.stock,ng.sales";
		$goods_sales_list = $goods_service->getGoodsQueryLimit([
			"ng.state" => 1
		], $goods_field, $page_size, "ng.sales desc");
		
		return $this->outMessage("销量排行榜", $goods_sales_list);
		
	}
	
	/**
	 * 商品排行列表
	 */
	public function goodsRankList()
	{
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$category_id = isset($this->params['category_id']) ? $this->params['category_id'] : "";
		$order = isset($this->params['order']) ? $this->params['order'] : "";
		$goods_service = new GoodsService();
		$condition = [];
		if (!empty($category_id)) {
			$condition["ng.category_id"] = $category_id;
		}
		
		$goods_rank = $goods_service->getGoodsRankViewList($page_index, $page_size, $condition, $order);
		return $this->outMessage("商品排行列表", $goods_rank);
	}
	
	/**
	 * 获取指定商品分类的详情
	 * @return string
	 */
	public function getGoodsCategoryDetail()
	{
		$category_id = isset($this->params['category_id']) ? $this->params['category_id'] : "";
		$goods_category = new GoodsCategoryService();
		$res = $goods_category->getGoodsCategoryDetail($category_id);
		return $this->outMessage("商品排行列表", $res);
	}

    /**
	 * 获取商品海报
	 */
	public function getGoodsPoster()
	{
	    $title = '获取商品海报';
        $goods_id = request()->post('goods_id', 0);
        $goods = new GoodsService();
        $result = $goods->createGoodsPoster($goods_id, $this->uid);
        if ($result == -50) {
            return $this->outMessage($title, '', -1, '商家未配置小程序');
        } else if ($result == -10) {
            return $this->outMessage($title, '', -1, '二维码生成失败，请检查该二维码指向页面是否在小程序线上版本中存在');
        }
         return $this->outMessage($title, $result);
	}
}