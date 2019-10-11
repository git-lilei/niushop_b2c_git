<?php
/**
 * Commission.php
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

use data\service\Config;
use data\service\Goods;
use data\service\GoodsBrand;
use data\service\GoodsCategory;
use data\service\GoodsGroup;
use data\service\Shop;

/**
 * 专题活动 控制器
 */
class Block extends BaseController
{
	public $replace;
	
	public $shop_template;
	
	public function __construct()
	{
		parent::__construct();
		$config = new Config();
		$pc_template = $config->getUsePCTemplate($this->instance_id);
		$this->shop_template = $pc_template['value'];
	}
	
	/**
	 * 编辑板块
	 */
	public function edit()
	{
		$shop = new Shop();
		$block_id = request()->get("block_id", 0);
		if (request()->isAjax()) {
			$block_name = input("block_name", "");
			$id = input("id", "");
			$pc_template = input("pc_template", "");
			$block_template = input("block_template", "");
			$block_json = input("block_json", "");
			$is_use = input("is_use", 0);
			$block_data = array();
			$block_data['instance_id'] = $this->instance_id;
			$block_data['name'] = $block_name;
			$block_data['pc_template'] = $pc_template;
			$block_data['block_template'] = $block_template;
			$block_data['is_use'] = $is_use;
			$block_data['value'] = $block_json;
			if (!$id) {
				$block_data['create_time'] = time();
			} else {
				$block_data['modify_time'] = time();
				$block_data['id'] = $id;
				
			}
			$res = $shop->editGoodsFloor($block_data);
			return AjaxReturn($res);
		} else {
			$block_template_list = $this->getBlockTemplateList();
			$this->assign("block_template_list", $block_template_list);
			$data = "";
			$block_template = "";//板块模板文件
			//默认选中第一个
			if (!empty($block_template_list)) {
				$block_template = $block_template_list[0];
			}
			if ($block_id > 0) {
				$block_info = $shop->getFloorInfo([ 'id' => $block_id ]);
				if ($block_info) {
					$this->assign("block_info", $block_info);
					$data = $block_info['value'];
					$block_template = $block_info['block_template'];
					$this->assign("block_info", $block_info);
				}
			}
			
			$theme_css = __ROOT__ . '/template/web/' . $this->shop_template . '/public/css/themes/theme.css';
			$this->assign("theme_css",$theme_css);
			
			$block_html = $this->loadBlock($data, $block_template);
			$this->assign("block_html", $block_html);
			$this->assign('pc_template', $this->shop_template);
			return view($this->style . "Block/edit");
		}
	}
	
	/**
	 * 文本编辑框
	 */
	public function textPopUp()
	{
		$data = input("data", "");
		if (!empty($data)) {
			$data = json_decode($data, true);
			$this->assign("data", $data);
		}
		
		$shop = new Shop();
		$navigation_list = $shop->shopNavigationList(0, 0, [ 'type' => 2 ], 'sort');
		if (!empty($navigation_list['data'])) {
			$this->assign("diy_view_link", $navigation_list['data']);
		}
		
		return view($this->style . "Block/text_pop_up");
		
	}
	
	/**
	 * 商品分类编辑框
	 */
	public function productCategoryPopUp()
	{
		$data = input("data", "");
		if (!empty($data)) {
			$data = json_decode($data, true);
			$this->assign("data", $data);
		}
		$product_model = new GoodsCategory();
		//产品分类列表
		$list = $product_model->getProductCategoryTree([]);
		if (!empty($list['data'])) {
			$this->assign('product_category_list', $list['data']);
		}
		return view($this->style . "Block/product_category_pop_up");
	}
	
	/**
	 * 品牌编辑框
	 */
	public function brandPopUp()
	{
		$data = input("data", "");
		if (!empty($data)) {
			$data = json_decode($data, true);
			$this->assign("data", $data);
		}
		
		//产品品牌
		$goods_brand = new GoodsBrand();
		$goods_brand_list = $goods_brand->getGoodsBrandList(1, 0);
		$this->assign('goods_brand_list', $goods_brand_list['data']);
		
		if (!empty($goods_brand_list['data'])) {
			$this->assign('brand_list', $goods_brand_list['data']);
		}
		return view($this->style . "Block/brand_pop_up");
		
	}
	
	/**
	 * 商品编辑框
	 */
	public function productPopUp()
	{
		$data = input("data", "");
		if (!empty($data)) {
			$data = json_decode($data, true);
			$this->assign("data", $data);
		}
		$product_model = new GoodsCategory();
		
		//产品分类列表
		$list = $product_model->getProductCategoryTree(0);
		if (!empty($list['data'])) {
			$this->assign('product_category_list', $list['data']);
		}
		
		// 产品标签列表
		$goods_group = new GoodsGroup();
		$group_list = $goods_group->getGoodsGroupList(1, 0);
		if (!empty($group_list['data'])) {
			$this->assign('label_list', $group_list['data']);
		}
		
		//产品品牌
		$goods_brand = new GoodsBrand();
		$goods_brand_list = $goods_brand->getGoodsBrandList(1, 0);
		
		if (!empty($goods_brand_list['data'])) {
			$this->assign('brand_list', $goods_brand_list['data']);
		}
		
		//产品推荐
		$recommend_list = array();
		$recommend_list[] = [
			'recommend_id' => 'is_hot',
			'name' => '热卖'
		];
		$recommend_list[] = [
			'recommend_id' => 'is_recommend',
			'name' => '精品'
		];
		$recommend_list[] = [
			'recommend_id' => 'is_new',
			'name' => '新品'
		];
		$this->assign('recommend_list', $recommend_list);
		
		return view($this->style . "Block/product_pop_up");
	}
	
	/**
	 * 广告图片编辑框
	 */
	public function advPopUp()
	{
		$data = input("data", "");
		$data_height = input("data_height", "660");
		$data_width = input("data_width", "400");
		if (!empty($data)) {
			$data = json_decode($data, true);
			$this->assign("data", $data);
		}
		$this->assign("data_width", $data_width);
		$this->assign("data_height", $data_height);
		// 自定义链接集合
		$shop = new Shop();
		$navigation_list = $shop->shopNavigationList(0, 0, [ 'type' => 2 ], 'sort');
		if (!empty($navigation_list['data'])) {
			$this->assign("diy_view_link", $navigation_list['data']);
		}
		return view($this->style . "Block/adv_pop_up");
	}
	
	
	/**
	 * 渲染板块界面
	 * 创建时间：2018年11月7日15:35:50
	 *
	 * @param string json $data 可接受post值（名称要对应），传参
	 * @param $block_template
	 * @return mixed
	 */
	public function loadBlock($data, $block_template)
	{
		$style = $this->shop_template;
		if (!empty($data)) {
			$shop = new Shop();
			$data = json_decode($data, true);
			$data = $shop->formatBlockData($data);
			$this->assign("data", $data);
		}
		$pc_path = __ROOT__ . '/template/web/' . $this->shop_template . '/block';
		$this->assign("pc_path", $pc_path);
		$res = $this->fetch('template' . DS . 'web' . DS . $style . DS . 'block' . DS . $block_template);
		return $res;
	}
	
	/**
	 * 获取板块模板列表
	 */
	private function getBlockTemplateList()
	{
		$style = $this->shop_template;
		$app_path = 'template' . DS . 'web' . DS . $style . DS . 'block' . DS;
		if (is_dir($app_path)) {
			$sub_dir_arr = scandir($app_path);
			$template_arr = [];
			foreach ($sub_dir_arr as $dir_name) {
				//只保存html文件，过滤其他类型文件
				if ($dir_name != '.' && $dir_name != '..' && strstr($dir_name, ".html")) {
					$template_arr[] = $dir_name;
				}
			}
			return $template_arr;
		}
	}
	
	/**
	 * 商品选择弹框控制器
	 */
	public function goodsSelectList()
	{
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$goods_name = request()->post("search_text", "");
			$category_id = request()->post('category_id', '');
			$selectGoodsLabelId = request()->post('label_id', '');
			$supplier_id = request()->post('supplier_id', '');
			$goods_type = request()->post("goods_type", ""); // 商品类型
			$brand_id = request()->post('brand_id', '');
			$alis_id = request()->post("alis_id", '');
			$is_recommend = request()->post("is_recommend", '');//推荐
			$is_new = request()->post("is_new", '');//新品
			$is_hot = request()->post("is_hot", '');//热卖
			$price_min = request()->post('price_min', 0);
			$price_max = request()->post('price_max', 0);
			$goods_id = request()->post('product_id', '');
			if ($goods_id) {
				$condition['goods_id'] = [ 'in', $goods_id ];
			}
			$condition['state'] = 1;
			//商品名称
			if ($goods_name) {
				$condition = array(
					"goods_name" => [
						"like",
						"%$goods_name%"
					],
				);
			}
			if (!empty($price_min) && !empty($price_max)) {
				$condition['price'] = array(
					[ '>', $price_min ],
					[ '<=', $price_max ], 'and'
				);
			}
			
			//商品类型
			if ($goods_type !== "" && $goods_type != 'all') {
				$condition['goods_type'] = [ 'in', $goods_type ];
			}
			//推荐
			if ($is_recommend) {
				$condition['is_recommend'] = 1;
			}
			//新品
			if ($is_new) {
				$condition['is_new'] = 1;
			}
			//热卖
			if ($is_hot) {
				$condition['is_hot'] = 1;
			}
			
			//商品标签
			if (!empty($selectGoodsLabelId)) {
				$selectGoodsLabelIdArray = explode(',', $selectGoodsLabelId);
				$selectGoodsLabelIdArray = array_filter($selectGoodsLabelIdArray);
				$str = "FIND_IN_SET(" . $selectGoodsLabelIdArray[0] . ",group_id_array)";
				for ($i = 1; $i < count($selectGoodsLabelIdArray); $i++) {
					$str .= "AND FIND_IN_SET(" . $selectGoodsLabelIdArray[ $i ] . ",group_id_array)";
				}
				$condition[""] = [
					[
						"EXP",
						$str
					]
				];
			}
			//供货商
			if ($supplier_id != '') {
				$condition['supplier_id'] = $supplier_id;
			}
			
			//品牌
			if ($brand_id != '') {
				$condition['brand_id'] = $brand_id;
			}
			//商品id
			if ($alis_id != '') {
				$condition['goods_id'] = [ 'in', $alis_id ];
			}
			
			//商品分类
			if ($category_id) {
				$condition["category_id|category_id_1|category_id_2|category_id_3"] = $category_id;
			}
			$condition['state'] = 1;
			$goods_detail = new Goods();
			$result = $goods_detail->getSearchGoodsList($page_index, $page_size, $condition, "create_time desc");
			return $result;
		}
		
	}
	
	//获取传值数组的值
	public function getValueByKey($str, $key)
	{
		$arr = explode(',', $str);
		foreach ($arr as $k => $v) {
			$v_arr = explode(':', $v);
			if ($key == $v_arr[0]) {
				return $v_arr[1];
			}
		}
		
		return 0;
	}
	
	/**
	 * 首页分类楼层
	 */
	public function goodsFloorBlock()
	{
		if (request()->isAjax()) {
			$shop = new Shop();
			$list = $shop->getGoodsFloorList(1, 0);
			return $list;
		}
		
		return view($this->style . "Block/goodsFloorBlock");
	}
	
	/**
	 * 修改楼层排序
	 *
	 */
	public function modifyFloorSort()
	{
		if (request()->isAjax()) {
			$shop = new Shop();
			$id = request()->post('id', '');
			$sort = request()->post('sort', '');
			$retval = $shop->modifyFloorSort($sort, $id);
			return AjaxReturn($retval);
		}
	}
	
	/**
	 * 删除楼层
	 *
	 */
	public function deleteFloor()
	{
		if (request()->isAjax()) {
			$shop = new Shop();
			$id = request()->post('id', '');
			$retval = $shop->deleteFloor($id);
			return AjaxReturn($retval);
		}
	}
	
}