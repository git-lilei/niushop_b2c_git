<?php
/**
 * GoodsCategory.php
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
 * 商品分类服务层
 */
use data\model\NsAttributeModel;
use data\model\NsGoodsBrandModel;
use data\model\NsGoodsCategoryBlockModel;
use data\model\NsGoodsCategoryModel;
use data\model\NsGoodsGroupModel;
use data\model\NsGoodsModel;
use think\Cache;

class GoodsCategory extends BaseService
{
	
	private $goods_category;
	
	function __construct()
	{
		parent::__construct();
		$this->goods_category = new NsGoodsCategoryModel();
	}
	
	/***********************************************************商品分类开始*********************************************************/
	
	/**
	 * 添加或者修改商品分类信息
	 * @param int $goods_classid 添加时$goods_classid=0
	 */
	public function editGoodsCategory($params)
	{	
		Cache::clear('niu_goods_category');
		if ($params['data']['pid'] == 0) {
			$params['data']['level'] = 1;
		} else {
			$params['data']['level'] = $this->getGoodsCategoryDetail((int) $params['data']['pid'])['level'] + 1;
		}
		if ($params['category_id'] == 0) {
			$result = $this->goods_category->save($params['data']);
			if ($result) {
				// 创建商品分类楼层
				$this->addGoodsCategoryBlock($this->goods_category->category_id);
				$params['data']['category_id'] = $this->goods_category->category_id;
				$this->addUserLog($this->uid, 1, '商品', '添加商品分类', '添加商品分类:' . $params['data']['category_name']);
				hook("goodsCategorySaveSuccess", $params['data']);
				$res = $this->goods_category->category_id;
			} else {
				$res = $this->goods_category->getError();
			}
		} else {
			$res = $this->goods_category->save($params['data'], [
				'category_id' => $params['category_id']
			]);
			
			if ($res !== false) {
				$goods_model = new NsGoodsModel();
				$condition['category_id_1|category_id_2|category_id_3'] = $params['category_id'];
				$goods_list = $goods_model->getQuery($condition);
				$goods_arr = [];
				if ($goods_list) {
					foreach ($goods_list as $val => $key) {
						$goods_arr[] = $key['goods_id'];
					}
				}
				
				//直接修改到顶级分类
				if ($params['data']['pid'] == 0) {
					if($params['levels'] != 1){
						$data_arr = [
								'category_id_1' => $params['category_id'],
								'category_id_2' => 0,
								'category_id_3' => 0
						];
						foreach ($goods_list as $val => $key) {
							$goods_model = new NsGoodsModel();
							$goods_model->save($data_arr, [ 'goods_id' => $key['goods_id'] ]);
						}		
					}

				} else {
					//当前等级为3
					$goods_category = $this->goods_category->getInfo([ 'category_id' => $params['data']['pid'] ]);
					if ($params['levels'] == "3") {
						//修改到二级
						if ($goods_category['pid'] == 0) {
							$data_2 = [
								'category_id_1' => $params['data']['pid'],
								'category_id_2' => $params['category_id'],
								'category_id_3' => 0
							];
							foreach ($goods_list as $val => $key) {
								$goods_model = new NsGoodsModel();
								$goods_model->save($data_2, [ 'goods_id' => $key['goods_id'] ]);
							}
						} else {
							//还是三级
							$data_3 = [
								'category_id_1' => $goods_category['pid'],
								'category_id_2' => $params['data']['pid'],
								'category_id_3' => $params['category_id']
							];
							foreach ($goods_list as $val => $key) {
								$goods_model = new NsGoodsModel();
								$goods_model->save($data_3, [ 'goods_id' => $key['goods_id'] ]);
							}
						}
					} else if ($params['levels'] == "2") {
						//还是二级
						$data_4 = [
							'category_id_1' => $params['data']['pid'],
						];
						foreach ($goods_list as $val => $key) {
							$goods_model = new NsGoodsModel();
							$goods_model->save($data_4, [ 'goods_id' => $key['goods_id'] ]);
						}
					}
				}
				
				//$this->addGoodsCategoryBlock($params['category_id']);
				$this->addUserLog($this->uid, 1, '商品', '修改商品分类', '修改商品分类:' . $params['data']['category_name']);
				$this->goods_category->save([
					"level" => (int) $params['data']['level'] + 1
				], [
					"pid" => $params['category_id']
				]);
				$params['data']['category_id'] = $params['category_id'];
				hook("goodsCategorySaveSuccess", $params['data']);
				return $res;
			} else {
				$res = $this->goods_category->getError();
			}
		}
		return $res;
	}
	
	/**
	 * 批量添加商品分类
	 */
	public function batchAddGoodsCategory($content)
	{
		Cache::clear('niu_goods_category');
		$this->goods_category->startTrans();
		$category_arr = json_decode($content, true);
		if (count($category_arr) > 0) {
			try {
				foreach ($category_arr as $category) {
					$category_info_arr = json_decode($category, true);
					$data = array(
						'category_name' => $category_info_arr['categoryName'],
						'short_name' => $category_info_arr['categoryShortName'],
						'pid' => $category_info_arr['pid'],
						'level' => $category_info_arr['level'],
						'is_visible' => 1,
						'keywords' => '',
						'description' => '',
						'sort' => $category_info_arr['sort'],
						'category_pic' => '',
						'attr_id' => 0,
						'attr_name' => '',
						'pc_custom_template' => '',
						'wap_custom_template' => ''
					);
					$this->goods_category = new NsGoodsCategoryModel();
					$result = $this->goods_category->save($data);
					if ($result) {
						// 创建商品分类楼层
						$this->addGoodsCategoryBlock($this->goods_category->category_id);
						$data['category_id'] = $this->goods_category->category_id;
						$this->addUserLog($this->uid, 1, '商品', '添加商品分类', '添加商品分类:' . $data['category_name']);
						hook("goodsCategorySaveSuccess", $data);
						$this->goods_category->category_id;
					} else {
						$this->goods_category->rollback();
						return $result = array(
							"code" => 0,
							"message" => $this->goods_category->getError()
						);
					}
				}
				$this->goods_category->commit();
				return $result = array(
					"code" => 1,
					"message" => "添加成功"
				);
			} catch (\Exception $e) {
				$this->goods_category->rollback();
				return $result = array(
					"code" => 0,
					"message" => $e->getMessage()
				);
			}
		} else {
			return $result = array(
				"code" => 0,
				"message" => "操作失败"
			);
		}
	}
	
	/**
	 * 修改商品分类 单个字段
	 */
	public function modifyGoodsCategoryField($category_id, $field_name, $field_value)
	{
		Cache::clear('niu_goods_category');
		$res = $this->goods_category->ModifyTableField('category_id', $category_id, $field_name, $field_value);
		$this->addGoodsCategoryBlock($category_id);
		return $res;
	}
	
	/**
	 * 删除商品分类信息
	 */
	public function deleteGoodsCategory($category_id)
	{
		Cache::clear('niu_goods_category');
		$sub_list = $this->getGoodsCategoryListByParentId($category_id);
		if (!empty($sub_list)) {
			$res = SYSTEM_DELETE_FAIL;
		} else {
			$res = $this->goods_category->destroy($category_id);
			// 删除分类商品楼层
			$this->deleteGoodsCategoryBlock($category_id);
			hook("goodsCategoryDeleteSuccess", $category_id);
		}
		return $res;
	}
	
	/**
	 * 获取指定商品分类的详情
	 */
	public function getGoodsCategoryDetail($category_id)
	{
		$cache = Cache::tag("niu_goods_category")->get("getGoodsCategoryDetail" . $category_id);
		if (!empty($cache)) {
			return $cache;
		}
		$res = $this->goods_category->get($category_id);
		Cache::tag("niu_goods_category")->set("getGoodsCategoryDetail" . $category_id, $res);
		return $res;
	}
	
	/**
	 * 获取分类关键词
	 */
	public function getKeyWords($category_id)
	{
		$cache = Cache::tag("niu_goods_category")->get("getKeyWords" . $category_id);
		if (!empty($cache)) {
			return $cache;
		}
		$res = $this->goods_category->getInfo([
			'category_id' => $category_id
		], 'keywords');
		Cache::tag("niu_goods_category")->set("getKeyWords" . $category_id, $res);
		return $res;
	}
	
	/**
	 * 获取分类级次
	 */
	public function getLevel($category_id)
	{
		$cache = Cache::tag("niu_goods_category")->get("getLevel" . $category_id);
		
		if (!empty($cache)) {
			return $cache;
		}
		$res = $this->goods_category->getInfo([
			'category_id' => $category_id
		], 'level');
		Cache::tag("niu_goods_category")->set("getLevel" . $category_id, $res);
		return $res;
	}
	
	/**
	 * 获取分类名称
	 */
	public function getName($category_id)
	{
		$cache = Cache::tag("niu_goods_category")->get("getName" . $category_id);
		if (!empty($cache)) {
			return $cache;
		}
		$res = $this->goods_category->getInfo([
			'category_id' => $category_id
		], 'category_name');
		Cache::tag("niu_goods_category")->set("getName" . $category_id, $res);
		return $res;
	}
	
	/**
	 * 根据当前商品分类组装分类名称
	 */
	public function getGoodsCategoryName($category_id_1, $category_id_2, $category_id_3)
	{
		$name = '';
		$goods_category = new NsGoodsCategoryModel();
		$info_1 = $goods_category->getInfo([
			'category_id' => $category_id_1
		], 'category_name');
		$info_2 = $goods_category->getInfo([
			'category_id' => $category_id_2
		], 'category_name');
		$info_3 = $goods_category->getInfo([
			'category_id' => $category_id_3
		], 'category_name');
		if (!empty($info_1['category_name'])) {
			$name = $info_1['category_name'] . ' > ';
		}
		if (!empty($info_2['category_name'])) {
			$name = $name . '' . $info_2['category_name'] . ' > ';
		}
		if (!empty($info_3['category_name'])) {
			$name = $name . '' . $info_3['category_name'];
		}
		return $name;
	}
	
	/**
	 * 获取商品分类下的价格区间
	 */
	public function getGoodsCategoryPriceGrades($category_id)
	{
		$goods_model = new NsGoodsModel();
		$max_price = $goods_model->where([
			'category_id|category_id_1|category_id_2|category_id_3' => $category_id
		])->max('price');
		$min_price = $goods_model->where([
			'category_id|category_id_1|category_id_2|category_id_3' => $category_id
		])->min('price');
		$price_grade = 1;
		
		for ($i = 1; $i <= log10($max_price); $i++) {
			$price_grade *= 10;
		}
		// 跨度
		$dx = (ceil(log10(($max_price - $min_price) / 3)) - 1) * $price_grade;
		if ($dx <= 0) {
			$dx = $price_grade;
		}
		$array = array();
		$j = 0;
		while ($j <= $max_price) {
			$array[] = array(
				$j,
				$j + $dx - 1
			);
			$j = $j + $dx;
		}
		return $array;
	}
	
	/**
	 * 根据当前分类ID查询商品分类的三级分类ID
	 */
	public function getGoodsCategoryId($category_id)
	{
		// 获取分类层级
		$goods_category = new NsGoodsCategoryModel();
		$info = $goods_category->get($category_id);
		if ($info['level'] == 1) {
			return array(
				$category_id,
				0,
				0
			);
		}
		if ($info['level'] == 2) {
			// 获取父级
			return array(
				$info['pid'],
				$category_id,
				0
			);
		}
		if ($info['level'] == 3) {
			$info_parent = $goods_category->get($info['pid']);
			// 获取父级
			return array(
				$info_parent['pid'],
				$info['pid'],
				$category_id
			);
		}
	}
	
	/**
	 * 商品分类
	 */
	public function getGoodsCategoryList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '*')
	{
		$data = array( $page_index, $page_size, $condition, $order, $field );
		$data = json_encode($data);
		$cache = Cache::tag("niu_goods_category")->get("getGoodsCategoryList" . $data);
		if (empty($cache)) {
			$list = $this->goods_category->pageQuery($page_index, $page_size, $condition, $order, $field);
			Cache::tag("niu_goods_category")->set("getGoodsCategoryList" . $data, $list);
			return $list;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 获取商品分类的子分类(一级)
	 */
	public function getGoodsCategoryListByParentId($pid)
	{
		$cache = Cache::tag("niu_goods_category")->get("getGoodsCategoryListByParentId" . $pid);
		if (empty($cache)) {
			$list = $this->getGoodsCategoryList(1, 0, 'pid=' . $pid, 'pid,sort');
			if (!empty($list)) {
				for ($i = 0; $i < count($list['data']); $i++) {
					$parent_id = $list['data'][ $i ]["category_id"];
					$child_list = $this->getGoodsCategoryList(1, 1, 'pid=' . $parent_id, 'pid,sort');
					if (!empty($child_list) && $child_list['total_count'] > 0) {
						$list['data'][ $i ]["is_parent"] = 1;
					} else {
						$list['data'][ $i ]["is_parent"] = 0;
					}
				}
			}
			Cache::tag("niu_goods_category")->set("getGoodsCategoryListByParentId" . $pid, $list['data']);
			return $list['data'];
		} else {
			return $cache;
		}
	}
	
	/**
	 * 获取分类树，（暂时是查询两级）
	 */
	public function getGoodsCategoryTree($pid)
	{
		// 暂时 获取 两级
		$cache = Cache::tag("niu_goods_category")->get("getGoodsCategoryTree" . $pid);
		if (empty($cache)) {
			$one_list = $this->getGoodsCategoryListByParentId($pid);
			foreach ($one_list as $k1 => $v1) {
				$two_list = $this->getGoodsCategoryListByParentId($v1['category_id']);
				$one_list[ $k1 ]['child_list'] = $two_list;
			}
			$list = $one_list;
			Cache::tag("niu_goods_category")->set("getGoodsCategoryTree" . $pid, $list);
			return $list;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 获取商品分类下的品牌列表
	 */
	public function getGoodsCategoryBrands($category_id)
	{
		$brand_list = Cache::tag("goods_category_brands")->get("get_goods_category_brands" . $category_id);
		if (empty($brand_list)) {
			$goods_model = new NsGoodsModel();
			$condition = array(
				'category_id | category_id_1 | category_id_2 | category_id_3' => $category_id
			);
			$brand_id_array = $goods_model->getQuery($condition, 'brand_id');
			$array = array();
			if (!empty($brand_id_array)) {
				foreach ($brand_id_array as $k => $v) {
					$array[] = $v['brand_id'];
				}
			}
			if (!empty($array)) {
				$goods_brand = new NsGoodsBrandModel();
				$condition = array(
					'brand_id' => array(
						'in',
						$array
					)
				);
				$brand_list = $goods_brand->getQuery($condition, 'brand_id,brand_name,brand_pic', 'brand_initial asc');
				Cache::tag("goods_category_brands")->set("get_goods_category_brands" . $category_id, $brand_list);
				return $brand_list;
			} else {
				return [];
			}
		} else {
			return $brand_list;
		}
	}
	
	/**
	 * 根据商品分类关联的商品类型获取品牌
	 */
	public function getGoodsBrandsByGoodsAttr($category_id)
	{
		$brand_list = Cache::tag("goods_category_brands")->get("get_goods_category_brands" . $category_id);
		if (empty($brand_list)) {
			$goods_category = new NsGoodsCategoryModel();
			$goods_category_info = $goods_category->getInfo([ "category_id" => $category_id ], "attr_id");
			if ($goods_category_info['attr_id'] > 0) {
				$goods_attr = new NsAttributeModel();
				$goods_attr_info = $goods_attr->getInfo([ "attr_id" => $goods_category_info['attr_id'] ], "*");
				if (!empty($goods_attr_info["brand_id_array"])) {
					$goods_brand = new NsGoodsBrandModel();
					$condition = array(
						'brand_id' => array(
							'in',
							$goods_attr_info["brand_id_array"]
						)
					);
					$brand_list = $goods_brand->getQuery($condition, 'brand_id,brand_name,brand_pic', 'sort desc');
					Cache::tag("goods_category_brands")->set("get_goods_category_brands" . $category_id, $brand_list);
					return $brand_list;
				} else {
					return array();
				}
			} else {
				return array();
			}
		} else {
			return $brand_list;
		}
	}
	
	/**
	 * 计算商品分类销量
	 */
	public function getGoodsCategorySaleNum()
	{
		$goods_goods_category = new NsGoodsCategoryModel();
		$goods_goods_category_all = $goods_goods_category->all();
		foreach ($goods_goods_category_all as $k => $v) {
			$goods_model = new NsGoodsModel();
			$goods_sale_num = $goods_model->where(array(
				"category_id_1|category_id_2|category_id_3" => $v["category_id"]
			))->sum("sales");
			$goods_goods_category_all[ $k ]["sale_num"] = $goods_sale_num;
		}
		return $goods_goods_category_all;
	}
	
	/**
	 * 获取商品二级分类
	 */
	public function getGoodsSecondCategoryTree()
	{
		$goods_category_model = new NsGoodsCategoryModel();
		$goods_category_two_list = $goods_category_model->getQuery([
			'level' => 2,
			'is_visible' => 1
		], 'category_id, category_name,short_name,pid,category_pic', 'sort');
		if (!empty($goods_category_two_list)) {
			foreach ($goods_category_two_list as $k_cat_two => $v_cat_two) {
				$cat_three_list = $goods_category_model->getQuery([
					'level' => 3,
					'is_visible' => 1,
					'pid' => $v_cat_two['category_id']
				], 'category_id,category_name,short_name,pid,category_pic', 'sort');
				$v_cat_two['count'] = count($cat_three_list);
				$v_cat_two['child_list'] = $cat_three_list;
			}
		}
		return $goods_category_two_list;
	}
	
	/**
	 * 获取商品分类的子项列
	 */
	public function getCategoryTreeList($category_id)
	{
		$cache = Cache::tag("niu_goods_category")->get("getCategoryTreeList" . $category_id);
		if (empty($cache)) {
			$goods_goods_category = new NsGoodsCategoryModel();
			$level = $goods_goods_category->getInfo([
				'category_id' => $category_id
			], 'level');
			if (!empty($level)) {
				$category_list = array();
				if ($level['level'] == 1) {
					$child_list = $goods_goods_category->getQuery([
						'pid' => $category_id
					], 'category_id,pid');
					$category_list = $child_list;
					if (!empty($child_list)) {
						foreach ($child_list as $k => $v) {
							$grandchild_list = $goods_goods_category->getQuery([
								'pid' => $v['category_id']
							], 'category_id');
							if (!empty($grandchild_list)) {
								$category_list = array_merge($category_list, $grandchild_list);
							}
						}
					}
				} elseif ($level['level'] == 2) {
					$child_list = $goods_goods_category->getQuery([
						'pid' => $category_id
					], 'category_id,pid');
					$category_list = $child_list;
				}
				$array = array();
				if (!empty($category_list)) {
					foreach ($category_list as $k => $v) {
						$array[] = $v['category_id'];
					}
				}
				if (!empty($array)) {
					$id_list = implode(',', $array);
					$cache_category_tree_list = $id_list . ',' . $category_id;
				} else {
					$cache_category_tree_list = $category_id;
				}
			} else {
				$cache_category_tree_list = $category_id;
			}
			Cache::tag("niu_goods_category")->set("getCategoryTreeList" . $category_id, $cache_category_tree_list);
			return $cache_category_tree_list;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 获取分类的父级分类
	 */
	public function getCategoryParentQuery($category_id)
	{
		$cache = Cache::tag("niu_goods_category")->get("getCategoryParentQuery" . $category_id);
		if (empty($cache)) {
			$grandparent_category_info = array();
			$goods_goods_category = new NsGoodsCategoryModel();
			$category_info = $goods_goods_category->getInfo([
				"category_id" => $category_id
			], "category_id,category_name,pid,level");
			$nav_name = array();
			if (!empty($category_info)) {
				$level = $category_info["level"];
				if ($level == 3) {
					$parent_category_info = $goods_goods_category->getInfo([
						"category_id" => $category_info["pid"]
					], "category_id,category_name,pid");
					
					if (!empty($parent_category_info)) {
						$grandparent_category_info = $goods_goods_category->getInfo([
							"category_id" => $parent_category_info["pid"]
						], "category_id,category_name,pid");
					}
					$nav_name = array(
						$grandparent_category_info,
						$parent_category_info,
						$category_info
					);
				} elseif ($level == 2) {
					$parent_category_info = $goods_goods_category->getInfo([
						"category_id" => $category_info["pid"]
					], "category_id,category_name,pid");
					$nav_name = array(
						$parent_category_info,
						$category_info
					);
				} else {
					$nav_name = array(
						$category_info
					);
				}
			}
			Cache::tag("niu_goods_category")->set("getCategoryParentQuery" . $category_id, $nav_name);
			return $nav_name;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 得到上级的分类组合
	 */
	public function getParentCategory($category_id)
	{
		$cache = Cache::tag("niu_goods_category")->get("getParentCategory" . $category_id);
		if (empty($cache)) {
			$category_ids = $category_id;
			$category_names = "";
			$goods_category = new NsGoodsCategoryModel();
			$category_obj = $goods_category->get($category_id);
			if (!empty($category_obj)) {
				$category_names = $category_obj["category_name"];
				$pid = $category_obj["pid"];
				while ($pid != 0) {
					$goods_category = new NsGoodsCategoryModel();
					$category_obj = $goods_category->get($pid);
					if (!empty($category_obj)) {
						$category_ids = $category_ids . "," . $pid;
						$category_name = $category_obj["category_name"];
						$category_names = $category_names . "," . $category_name;
						$pid = $category_obj["pid"];
					} else {
						$pid = 0;
					}
				}
			}
			$category_id_str = explode(",", $category_ids);
			$category_names_str = explode(",", $category_names);
			$category_result_ids = "";
			$category_result_names = "";
			for ($i = count($category_id_str); $i >= 0; $i--) {
				if ($category_result_ids == "") {
					$category_result_ids = $category_id_str[ $i ];
				} else {
					$category_result_ids = $category_result_ids . "," . $category_id_str[ $i ];
				}
			}
			for ($i = count($category_names_str); $i >= 0; $i--) {
				if ($category_result_names == "") {
					$category_result_names = $category_names_str[ $i ];
				} else {
					$category_result_names = $category_result_names . ":" . $category_names_str[ $i ];
				}
			}
			$parent_Category = array(
				"category_ids" => $category_result_ids,
				"category_names" => $category_result_names
			);
			Cache::tag("niu_goods_category")->set("getParentCategory" . $category_id, $parent_Category);
			return $parent_Category;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 获取商品分类列表
	 * 该方法被PC端首页和手机端商品分类界面调用
	 * 优化方式：去除没有用到的查询字段
	 */
	public function getCategoryTreeUseInShopIndex()
	{
		$cache = Cache::tag("niu_goods_category")->get("getCategoryTreeUseInShopIndex");
		if (empty($cache)) {
			$goods_category_model = new NsGoodsCategoryModel();
			$goods_category_one = $goods_category_model->getQuery([
				'level' => 1,
				'is_visible' => 1
			], 'category_id, category_name,short_name,pid,category_pic', 'sort desc');
			if (!empty($goods_category_one)) {
				foreach ($goods_category_one as $k_cat_one => $v_cat_one) {
					$goods_category_two_list = $goods_category_model->getQuery([
						'level' => 2,
						'is_visible' => 1,
						'pid' => $v_cat_one['category_id']
					], 'category_id,category_name,short_name,pid,category_pic', 'sort desc');
					$v_cat_one['count'] = count($goods_category_two_list);
					if (!empty($goods_category_two_list)) {
						foreach ($goods_category_two_list as $k_cat_two => $v_cat_two) {
							$cat_three_list = $goods_category_model->getQuery([
								'level' => 3,
								'is_visible' => 1,
								'pid' => $v_cat_two['category_id']
							], 'category_id,category_name,short_name,pid,category_pic', 'sort desc');
							$v_cat_two['count'] = count($cat_three_list);
							$v_cat_two['child_list'] = $cat_three_list;
						}
					}
					$v_cat_one['child_list'] = $goods_category_two_list;
				}
			}
			Cache::tag("niu_goods_category")->set("getCategoryTreeUseInShopIndex", $goods_category_one);
			return $goods_category_one;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 获取商品分类列表应用后台
	 */
	public function getCategoryTreeUseInAdmin()
	{
		$cache = Cache::tag("niu_goods_category")->get("getCategoryTreeUseInAdmin");
		if (empty($cache)) {
			$goods_category_model = new NsGoodsCategoryModel();
			$goods_category_one = $goods_category_model->getQuery([
				'level' => 1
			], 'category_id, category_name,short_name,pid,category_pic,sort,attr_name,is_visible,level,category_pic', 'category_id desc');
			if (!empty($goods_category_one)) {
				foreach ($goods_category_one as $k_cat_one => $v_cat_one) {
					$goods_category_two_list = $goods_category_model->getQuery([
						'level' => 2,
						'pid' => $v_cat_one['category_id']
					], 'category_id,category_name,short_name,pid,category_pic,sort,attr_name,is_visible,level,category_pic', 'category_id desc');
					$v_cat_one['count'] = count($goods_category_two_list);
					if (!empty($goods_category_two_list)) {
						foreach ($goods_category_two_list as $k_cat_two => $v_cat_two) {
							$cat_three_list = $goods_category_model->getQuery([
								'level' => 3,
								'pid' => $v_cat_two['category_id']
							], 'category_id,category_name,short_name,pid,category_pic,sort,attr_name,is_visible,level,category_pic', 'category_id desc');
							$v_cat_two['count'] = count($cat_three_list);
							$v_cat_two['child_list'] = $cat_three_list;
						}
					}
					$v_cat_one['child_list'] = $goods_category_two_list;
				}
			}
			Cache::tag("niu_goods_category")->set("getCategoryTreeUseInAdmin", $goods_category_one);
			return $goods_category_one;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 获取产品分类数
	 */
	public function getProductCategoryTree($condition, $field = '*')
	{
		$list = $this->getGoodsCategoryList(1, 0, $condition, '', $field);
		$tree = $this->list_to_tree($list['data'], 'category_id', 'pid', 'child_list');
		return success($tree);
	}
	
	private function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
	{
		// 创建Tree
		$tree = [];
		if (!is_array($list)) :
			return false;
		
		endif;
		// 创建基于主键的数组引用
		$refer = [];
		foreach ($list as $key => $data) {
			$refer[ $data[ $pk ] ] = &$list[ $key ];
		}
		foreach ($list as $key => $data) {
			// 判断是否存在parent
			$parentId = $data[ $pid ];
			if ($root == $parentId) {
				$tree[] = &$list[ $key ];
			} elseif (isset($refer[ $parentId ])) {
				is_object($refer[ $parentId ]) && $refer[ $parentId ] = $refer[ $parentId ]->toArray();
				$parent = &$refer[ $parentId ];
				$parent[ $child ][] = &$list[ $key ];
			}
		}
		return $tree;
	}
	
	/***********************************************************商品分类结束*********************************************************/
	
	/***********************************************************商品分类楼层开始*********************************************************/
	
	/**
	 * 添加商品分类楼层
	 */
	public function addGoodsCategoryBlock($category_id)
	{
		Cache::clear("niu_goods_category");
		$goods_category = new NsGoodsCategoryModel();
		$goods_category_info = $goods_category->getInfo([
			"category_id" => $category_id
		], "*");
		if (!empty($goods_category_info)) {
			
			$goods_category_block = new NsGoodsCategoryBlockModel();
			$goods_category_block_info = $goods_category_block->getInfo([
				"category_id" => $category_id
			], "*");
			if (empty($goods_category_block_info) && $goods_category_info["pid"] == 0) {
				$data = array(
					"shop_id" => $this->instance_id,
					"category_id" => $category_id,
					"category_name" => $goods_category_info["category_name"],
					"category_alias" => $goods_category_info["category_name"],
					"create_time" => time(),
					"color" => "#FFFFFF",
					"short_name" => mb_substr($goods_category_info["category_name"], 0, 4, 'utf-8')
				);
				$result = $goods_category_block->save($data);
				return $result;
			} else {
				if ($goods_category_info["pid"] > 0) {
					$this->deleteGoodsCategoryBlock($category_id);
					return 1;
				} else {
					$data = array(
						"category_name" => $goods_category_info["category_name"],
						"category_alias" => $goods_category_info["category_name"],
						"modify_time" => time(),
						"short_name" => mb_substr($goods_category_info["category_name"], 0, 4, 'utf-8')
					);
					$result = $goods_category_block->save($data, [
						"category_id" => $category_id
					]);
					return $result;
				}
			}
		} else {
			return 0;
		}
	}
	
	/**
	 * 删除分类商品楼层
	 */
	public function deleteGoodsCategoryBlock($category_id)
	{
		Cache::clear("niu_goods_category");
		$goods_category_block = new NsGoodsCategoryBlockModel();
		$retval = $goods_category_block->destroy([
			"category_id" => $category_id
		]);
		return $retval;
	}
	
	/***********************************************************商品分类楼层结束*********************************************************/
	
	/**
	 * 通过商品标签数组获取商品标签
	 */
	public function getGoodsTabByGoodsGroupId($goods_group_id_str)
	{
		if (!empty($goods_group_id_str)) {
			$ns_group = new NsGoodsGroupModel();
			$goods_tab_arr = $ns_group->getQuery([
				'group_id' => [
					"in",
					$goods_group_id_str
				]
			], "group_id, group_name");
			return $goods_tab_arr;
		}
		return array();
	}
}