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

namespace app\web\controller;

use think\Request;

/**
 * 商品控制器
 */
class Goods extends BaseWeb
{
	/**
	 * 商品详情
	 */
	public function detail($goods_id = '', $sku_id = '')
	{
		if (empty($goods_id)) {
			$goods_id = request()->get('goods_id', 0);
		}
		
		if (empty($sku_id)) {
			$sku_id = request()->get('sku_id', 0);
		}
		
		if (empty($goods_id) && empty($sku_id)) {
			$redirect = __URL(__URL__ . '/index');
			$this->redirect($redirect);
		}
		$default_client = request()->cookie("default_client", "");
		if ($default_client == "web") {
		} elseif (request()->isMobile() && $this->web_info['wap_status'] == 1) {
			Request::instance()->module("wap");
			if (empty($sku_id) || $sku_id == 0) {
				$redirect = __URL(__URL__ . "/wap/goods/detail?goods_id=" . $goods_id);
			} else {
				$redirect = __URL(__URL__ . "/wap/goods/detail?sku_id=" . $sku_id);
			}
			
			$this->redirect($redirect);
			exit();
		}
		
		$from = request()->get("from", "");//来源，point 积分
		$this->assign('from', $from);
		$this->assign('goods_id', $goods_id);
		$this->assign('sku_id', $sku_id);
		
		$data = api('System.Goods.goodsDetail', [ 'goods_id' => $goods_id, 'sku_id' => $sku_id ]);
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
		
		//判断用户是否收藏该商品
		$whether_collection = 0;
		if (!empty($this->uid)) {
			$whether_collection = api("System.Goods.whetherCollection", [ 'fav_id' => $data['goods_detail']['goods_id'], 'fav_type' => 'goods' ]);
			$whether_collection = $whether_collection['data'];
		}
		$this->assign("whether_collection", $whether_collection);
		
		//商家服务
		$merchant_service_list = api('System.Config.merchantService');
		$merchant_service_list = $merchant_service_list['data'];
		
		$this->assign("merchant_service_list", $merchant_service_list);
		$this->assign("data", $data);
		$this->assign("goods_detail", $data['goods_detail']);
		
		$this->assign("title_before", $data['goods_detail']['goods_name']);
		$_SESSION['login_pre_url'] = request()->url(true);
		if (!empty($data["pc_custom_template"])) {
			// 用户自定义商品详情界面
			return $this->view($this->style . 'goods/' . $data["pc_custom_template"]);
		} else {

//			优先商品预售
			if ($data['goods_detail']['is_open_presell'] == 1) {
				return $this->view($this->style . 'goods/detail_presell');
			} elseif (!empty($data['goods_detail']['promotion_detail'])) {
				if (!empty($data['goods_detail']['promotion_detail']['group_buy'])) {
					
					//团购
					return $this->view($this->style . 'goods/detail_groupbuy');
					
				} elseif (!empty($data['goods_detail']['promotion_detail']['discount_detail'])) {
					
					//限时折扣
					return $this->view($this->style . 'goods/detail_discount');
				}
			}
			return $this->view($this->style . 'goods/detail');
		}
		
	}
	
	/**
	 * 通过sku_id访问商品详情
	 * @param $sku_id
	 */
	public function sku($sku_id)
	{
		if (empty($sku_id)) {
			$sku_id = request()->get('sku_id', 0);
		}
		return $this->detail('', $sku_id);
	}
	
	/**
	 * 商品列表
	 */
	public function lists()
	{
		$category_id = request()->get('category_id', ''); // 商品分类
		$keyword = request()->get('keyword', ''); // 关键词
		$shipping_fee = request()->get('shipping_fee', ''); // 是否包邮，0：包邮；1：运费价格
		$stock = request()->get('stock', ''); // 仅显示有货，大于0
		$order = request()->get('obyzd', ''); // 排序字段,order by ziduan
		$sort = request()->get('sort', ''); // 排序方式
		$brand_id = request()->get('brand_id', ''); // 品牌id
		$brand_name = request()->get('brand_name', ''); // 品牌名牌
		$min_price = request()->get('min_price', ''); // 价格区间,最小
		$max_price = request()->get('max_price', ''); // 最大
		$province_id = request()->get('province_id', ''); // 商品所在地
		$province_name = request()->get('province_name', ''); // 所在地名称
		$attr = request()->get('attr', ''); // 属性值
		$spec = request()->get('spec', ''); // 规格值
		
		$data = [
			'category_id' => $category_id,
			'keyword' => $keyword,
			'shipping_fee' => $shipping_fee,
			'stock' => $stock,
			'order' => $order,
			'sort' => $sort,
			'brand_id' => $brand_id,
			'brand_name' => $brand_name,
			'min_price' => $min_price,
			'max_price' => $max_price,
			'province_id' => $province_id,
			'province_name' => $province_name,
			'attr' => $attr,
			'spec' => $spec,
		];
		$this->assign('data', $data);
		foreach ($data as $k => $v) {
			$this->assign("$k", "$v");
		}
		
		$goods_category_info = api("System.Goods.getGoodsCategoryDetail", [ 'category_id' => $category_id ]);;
		$goods_category_info = $goods_category_info['data'];
		
		$params = input();
		
		$params['page_index'] = $params['page'] = input('page', 1);
		
		$data = api('System.Goods.goodsListByConditions', $params);
		$data = $data['data'];
		$this->assign("data", $data);
		
		unset($params['action']);
		unset($params['page_index']);
		$this->assign("params", $params);
		
		$title_before = $data['curr_category_name'];
		if (!empty($keyword)) {
			$title_before = $keyword . "_搜索";
		}
		$this->assign("title_before", $title_before);
		$template = 'goods/lists';
		if (!empty($goods_category_info["pc_custom_template"])) {
			$template = 'goods/' . $goods_category_info["pc_custom_template"];
		}
		return $this->view($this->style . $template);
	}
	
	/**
	 * 品牌
	 */
	public function brand()
	{
		$page_index = request()->get('page', 1);
		$category_id = request()->get('category_id', 0);
		$this->assign('page_index', $page_index);
		$this->assign('category_id', $category_id);
		$this->assign("title_before", "品牌列表");
		return $this->view($this->style . 'goods/brand');
	}
	
	/**
	 * 全部商品分类
	 */
	public function category()
	{
		$this->assign("title_before", "商品分类");
		return $this->view($this->style . 'goods/category');
	}
	
	/**
	 * 积分中心
	 */
	public function point()
	{
		$id = request()->get('id', '');
		$this->assign("id", $id);
		$this->assign("title_before", "积分中心");
		return $this->view($this->style . 'goods/point');
	}
	
	/**
	 * 商品购买咨询
	 */
	public function consult()
	{
		$this->assign('goods_id', request()->get('goods_id', ''));
		$this->assign('page_index', request()->get('page', 1));
		$this->assign('ct_id', request()->get('ct_id', ''));
		$this->assign("title_before", "商品咨询");
		return $this->view($this->style . 'goods/consult');
	}
	
	/**
	 * 购物车
	 */
	public function cart()
	{
		$this->assign("title_before", "购物车");
		return $this->view($this->style . 'goods/cart');
	}
	
	/**
	 * 选择优惠套餐
	 */
	public function combo()
	{
		if (!addon_is_exit('NsCombopackage')) {
			$this->error('未检测到组合套餐插件');
		}
		$combo_id = request()->get("combo_id", 0);
		if (empty($combo_id)) {
			$this->error('缺少参数');
		}
		$this->checkLogin();
		$this->assign("combo_id", $combo_id);
		$this->assign('curr_id', request()->get("curr_id", 0));
		$this->assign("title_before", "组合套餐");
		return $this->view($this->style . "goods/combo");
	}
	
	/**
	 * 优惠券
	 */
	public function coupon()
	{
		$this->assign('page_index', request()->get('page', 1));
		$this->assign('page_size', 9);
		$this->assign("title_before", "优惠券");
		$_SESSION['login_pre_url'] = request()->url(true);
		return $this->view($this->style . 'goods/coupon');
	}
	
	/**
	 * 团购专区
	 */
	public function groupBuy()
	{
		$this->assign('page_index', request()->get("page", 1));
		$this->assign("title_before", "团购专区");
		return $this->view($this->style . 'goods/groupbuy');
	}
	
	/**
	 * 专题活动列表页面
	 */
	public function topics()
	{
		$this->assign("title_before", "专题活动列表");
		return $this->view($this->style . 'goods/topics');
	}
	
	/**
	 * 专区活动详情
	 */
	public function topicDetail()
	{
		$topic_id = request()->get('topic_id', 0);
		if (!is_numeric($topic_id)) {
			$this->error("没有获取到专题信息");
		}
		$this->assign("topic_id", $topic_id);
		$detail = api("System.Goods.promotionTopicDetail", [ 'topic_id' => $topic_id ]);
		$detail = $detail['data'];
		$this->assign('info', $detail);
		$this->assign("title_before", "专题活动详情");
		return $this->view($this->style . 'goods/' . $detail['pc_topic_template']);
	}
	
	/**
	 * 限时折扣
	 */
	public function discount()
	{
		$page_index = request()->get('page', 1);
		$category_id = request()->get('category_id', 0);
		$this->assign("page_index", $page_index);
		$this->assign("category_id", $category_id);
		$this->assign("title_before", "限时折扣");
		return $this->view($this->style . 'goods/discount');
	}
}