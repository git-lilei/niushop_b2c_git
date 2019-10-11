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

namespace app\wap\controller;

use data\service\Goods as GoodsService;
use think\Request;

/**
 * 商品相关
 */
class Goods extends BaseWap
{
	
	/**
	 * 商品详情
	 */
	public function detail()
	{
		$goods_id = request()->get('goods_id', 0);
		$sku_id = request()->get('sku_id', 0);
		$bargain_id = request()->get('bargain_id', 0);
		$group_id = request()->get("group_id", 0);
		$from = request()->get("from", "");//来源，point 积分
		
		if (empty($goods_id) && empty($sku_id)) {
			$redirect = __URL(__URL__ . '/index');
			$this->redirect($redirect);
		}
		
		$this->assign('goods_id', $goods_id);
		$this->assign('sku_id', $sku_id);
		$this->assign('bargain_id', $bargain_id);
		$this->assign('group_id', $group_id);
		$this->assign('from', $from);
		
		// 切换到PC端
		if (!request()->isMobile() && $this->web_info['web_status'] == 1) {
			Request::instance()->module("web");
			if (empty($sku_id) || $sku_id == 0) {
				$redirect = __URL(__URL__ . "/goods/detail?goods_id=" . $goods_id);
			} else {
				$redirect = __URL(__URL__ . "/goods/detail?sku_id=" . $sku_id);
			}
			$this->redirect($redirect);
			exit();
		}
		
		// 检测当前是否是ios
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
			$is_ios = true;
		} else {
			$is_ios = false;
		}
		$this->assign("is_ios", $is_ios);
		
		$data = api('System.Goods.goodsDetail', [ 'goods_id' => $goods_id, 'sku_id' => $sku_id, 'bargain_id' => $bargain_id, 'group_id' => $group_id ]);
		$data = $data['data'];
		if (empty($data['goods_detail'])) {
			$redirect = __URL(__URL__ . '/index');
			$this->redirect($redirect);
		}
		
		if ($data['goods_detail']['is_virtual'] == 1) {
			$virtual_goods = api("System.Config.virtualGoodsConfig");
			$virtual_goods = $virtual_goods['data'];
			if ($virtual_goods == 0) {
				$redirect = __URL(__URL__ . '/index');
				$this->error("未开启虚拟商品功能", $redirect);
			}
		}
		
		$this->assign("data", $data);
		$this->assign("title_before", $data['goods_detail']['goods_name']);
		
		$goods_detail = $data['goods_detail'];
		$this->assign("goods_detail", $goods_detail);
		
		$whether_collection = 0;
		$cart_count = 0;
		if (!empty($this->uid)) {
			//判断用户是否收藏该商品
			$whether_collection = api("System.Goods.whetherCollection", [ 'fav_id' => $goods_id, 'fav_type' => 'goods' ]);
			$whether_collection = $whether_collection['data'];
			
			//获取购物车数量
			$cart_count = api("System.Goods.cartCount");
			$cart_count = $cart_count['data'];
		}
		
		$this->assign("whether_collection", $whether_collection);
		$this->assign("cart_count", $cart_count);
		
		//商家服务
		$merchant_service_list = api('System.Config.merchantService');
		$merchant_service_list = $merchant_service_list['data'];
		$this->assign("merchant_service_list", $merchant_service_list);
		
		//客服
		$custom_service = api("System.Config.customService");
		$custom_service = $custom_service['data'];
		$this->assign("custom_service", $custom_service);
		
		//检测当前是否是微信浏览器
		$is_weixin = api("System.Config.isWeixin");
		$is_weixin = $is_weixin['data'];
		$this->assign("is_weixin", $is_weixin);

//		优先商品预售
		if ($data['goods_detail']['is_open_presell'] == 1) {
			return $this->view($this->style . 'goods/detail_presell');
		} elseif (!empty($data['goods_detail']['promotion_detail'])) {
			
			if ($bargain_id && !empty($data['goods_detail']['promotion_detail']['bargain'])) {
				
				//砍价
				return $this->view($this->style . 'goods/detail_bargain');
				
			} elseif (!empty($data['goods_detail']['promotion_detail']['pintuan'])) {
				
				//拼团
				return $this->view($this->style . 'goods/detail_pintuan');
				
			} elseif (!empty($data['goods_detail']['promotion_detail']['group_buy'])) {
				
				//团购
				return $this->view($this->style . 'goods/detail_groupbuy');
				
			} elseif (!empty($data['goods_detail']['promotion_detail']['discount_detail'])) {
				
				//限时折扣
				return $this->view($this->style . 'goods/detail_discount');
			}
		}
		return $this->view($this->style . 'goods/detail');
	}
	
	/**
	 * 购物车页面
	 */
	public function cart()
	{
		$this->assign("title", lang("购物车"));
		$this->assign("title_before", lang("购物车"));
		return $this->view($this->style . 'goods/cart');
	}
	
	/**
	 * 平台商品分类列表
	 */
	public function category()
	{
		$show_type = api("System.Goods.goodsCategoryShowType");
		$show_type = $show_type['data'];
		$this->assign('show_type', $show_type);
		$this->assign("title", lang("商品分类"));
		$this->assign("title_before", lang("商品分类"));
		return $this->view($this->style . 'goods/category');
	}
	
	/**
	 * 品牌专区
	 */
	public function brand()
	{
		$this->assign("title_before", lang("品牌专区"));
		$this->assign("title", lang("品牌专区"));
		return $this->view($this->style . 'goods/brand');
	}
	
	/**
	 * 商品列表
	 */
	public function lists()
	{
		// 查询购物车中商品的数量
		$category_id = request()->get('category_id', ''); // 商品分类
		$brand_id = request()->get('brand_id', ''); // 品牌
		$this->assign('brand_id', $brand_id);
		$this->assign('category_id', $category_id);
		$template = 'goods/lists';
		// 筛选条件
		if ($category_id != "") {
			$goods_category_info = api("System.Goods.getGoodsCategoryDetail", [ 'category_id' => $category_id ]);
			$goods_category_info = $goods_category_info['data'];
			if (!empty($goods_category_info["wap_custom_template"])) {
				$template = 'goods/' . $goods_category_info["wap_custom_template"];
			}
		}
		
		$params = input();
		
		$data = api('System.Goods.goodsListByConditions', $params);
		$data = $data['data'];
		
		$this->assign("data", $data);
		$this->assign("params", $params);
		
		$title_before = $data['current_category']['category_name'];
		if (!empty($params['keyword'])) {
			$title_before = $params['keyword'] . "_搜索";
		}
		
		$this->assign("title_before", $title_before);
		return $this->view($this->style . $template);
	}
	
	/**
	 * 积分中心
	 */
	public function point()
	{
		$this->assign('title', "积分中心");
		$this->assign("title_before", "积分中心");
		return $this->view($this->style . 'goods/point');
	}
	
	/**
	 * 商品组合套餐列表
	 */
	public function combo()
	{
		if (!addon_is_exit('NsCombopackage')) {
			$this->error('未检测到组合套餐插件');
		}
		$goods_id = request()->get("goods_id", 0);
		if (empty($goods_id)) {
			$this->error('缺少参数');
		}
		$this->assign("goods_id", $goods_id);
		$this->assign("title", lang("combo_package"));
		$this->assign("title_before", lang("combo_package"));
		return $this->view($this->style . "goods/combo");
	}
	
	/**
	 * 优惠券列表
	 */
	public function coupon()
	{
		$this->assign("title", '优惠券领取');
		$this->assign("title_before", "优惠券领取");
		return $this->view($this->style . 'goods/coupon');
	}
	
	/**
	 * 领取优惠券
	 */
	public function couponReceive()
	{
		$this->assign("title", '领取优惠券');
		$this->assign("title_before", "领取优惠券");
		return $this->view($this->style . 'goods/coupon_receive');
	}
	
	/**
	 * 拼团专区
	 */
	public function pintuan()
	{
		if (!addon_is_exit('NsPintuan')) {
			$this->error('未检测到拼团插件');
		}
		$this->assign('title', "拼团专区");
		$this->assign("title_before", "拼团专区");
		return $this->view($this->style . "goods/pintuan");
	}
	
	/**
	 * 团购专区
	 */
	public function groupBuy()
	{
		$this->assign("title", "团购专区");
		$this->assign("title_before", "团购专区");
		return $this->view($this->style . 'goods/groupbuy');
	}
	
	/**
	 * 专题活动列表页面
	 */
	public function topics()
	{
		$this->assign('title', "专题活动");
		$this->assign("title_before", "专题活动");
		return $this->view($this->style . 'goods/topics');
	}
	
	public function topicDetail()
	{
		$topic_id = request()->get('topic_id', 0);
		if (!is_numeric($topic_id)) {
			$this->error("没有获取到专题信息");
		}
		$topic_goods = api("System.Goods.promotionTopicDetail", [ 'topic_id' => $topic_id ]);
		$topic_goods = $topic_goods['data'];
		$this->assign('info', $topic_goods);
		$this->assign('title', "专题信息");
		$this->assign("title_before", "专题信息");
		return $this->view($this->style . 'goods/' . $topic_goods['wap_topic_template']);
	}
	
	/**
	 * 砍价商品列表
	 */
	public function bargain()
	{
		if (!addon_is_exit('NsBargain')) {
			$this->error('未检测到砍价插件');
		}
		$this->assign('title', "砍价专区");
		$this->assign("title_before", "砍价专区");
		return $this->view($this->style . 'goods/bargain');
	}
	
	/**
	 * 砍价商品发起页面
	 */
	public function bargainLaunch()
	{
		if (!addon_is_exit('NsBargain')) {
			$this->error('未检测到砍价插件');
		}
		$launch_id = request()->get('launch_id', 0);
		if (empty($launch_id)) {
			$this->error('缺少参数');
		}
		$this->assign('launch_id', $launch_id);
		$this->assign("title_before", "我的砍价");
		$this->assign('title', "我的砍价");
		$this->assign("uid", $this->uid);
		return $this->view($this->style . 'goods/bargain_launch');
	}
	
	/**
	 * 限时折扣
	 */
	public function discount()
	{
		$current_time = getCurrentTime();
		$this->assign('ms_time', $current_time);
		$this->assign("title_before", "限时折扣");
		$this->assign("title", "限时折扣");
		return $this->view($this->style . 'goods/discount');
	}
	
	/**
	 * 创建商品海报
	 */
	public function createGoodsPoster()
	{
		if (request()->isAjax()) {
			$goods_id = request()->post('goods_id', 0);
			$uid = request()->post('uid', 0);
			$goods = new GoodsService();
			$result = $goods->createGoodsPoster($goods_id, $uid);
			return $result;
		}
	}
}