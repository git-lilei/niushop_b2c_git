<?php
/**
 * Config.php
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
 * @date : 2015.4.24
 * @version : v1.0.0.0
 */

namespace addons\NsDiyView\data\service;

/**
 * 系统配置业务层
 */
use data\model\ConfigModel;
use data\model\SysWapCustomTemplateModel;
use think\Cache;
use data\service\Goods;
use data\service\GoodsCategory;
use data\service\Config as BaseConfig;

class Config extends BaseConfig
{
	
	private $config_module;
	
	function __construct()
	{
		parent::__construct();
		$this->config_module = new ConfigModel();
	}
	
	/**
	 * 修改状态
	 */
	public function updateConfigEnable($id, $is_use)
	{
		Cache::clear('config');
		$config_model = new ConfigModel();
		$data = array(
			"is_use" => $is_use,
			"modify_time" => time()
		);
		$retval = $config_model->save($data, [
			"id" => $id
		]);
		return $retval;
	}
	
	/**
	 * 开启关闭自定义模板
	 * @param 店铺id $shop_id
	 * @param 1：开启，0：禁用 $is_enable
	 */
	public function setIsEnableWapCustomTemplate($shop_id, $is_enable)
	{
		Cache::tag('config')->set("IsEnableWapCustomTemplate" . $shop_id, '');
		$config_model = new ConfigModel();
		$info = $this->config_module->getInfo([
			'key' => 'WAP_CUSTOM_TEMPLATE_IS_ENABLE',
			'instance_id' => $shop_id
		], 'value');
		$data['instance_id'] = $shop_id;
		$data['value'] = $is_enable;
		if (empty($info)) {
			$data['key'] = 'WAP_CUSTOM_TEMPLATE_IS_ENABLE';
			$data['is_use'] = 1;
			$data['create_time'] = time();
			$res = $config_model->save($data);
		} else {
			$data['modify_time'] = time();
			$res = $config_model->save($data, [
				'key' => 'WAP_CUSTOM_TEMPLATE_IS_ENABLE'
			]);
		}
		return $res;
	}
	
	/**
	 * 获取自定义模板是否启用，0 不启用 1 启用
	 * @param unknown $shop_id
	 * @return number|unknown
	 */
	public function getIsEnableWapCustomTemplate($shop_id)
	{
		$cache = Cache::tag('config')->get("IsEnableWapCustomTemplate" . $shop_id);
		if (!empty($cache)) {
			return $cache;
		}
		$is_enable = 0;
		$config_model = new ConfigModel();
		$value = $config_model->getInfo([
			'key' => 'WAP_CUSTOM_TEMPLATE_IS_ENABLE',
			'instance_id' => $shop_id
		], 'value');
		if (!empty($value)) {
			$is_enable = $value["value"];
		}
		Cache::tag('config')->set("IsEnableWapCustomTemplate" . $shop_id, $is_enable);
		return $is_enable;
	}
	
	/**
	 * 获取格式化后的手机端自定义模板
	 * @param number $id
	 * @return multitype:Ambigous <multitype:, mixed, unknown> unknown
	 */
	public function getFormatCustomTemplate($id = 0, $type= 1)
	{
		$custom_template = array();
		if ($id === 0) {
			$template_info = $this->getDefaultWapCustomTemplate($type);
		} else {
			$template_info = $this->getWapCustomTemplateById($id);
		}
		if (!empty($template_info)) {
			$goods = new Goods();
			$custom_template_info = json_decode($template_info["template_data"], true);
			foreach ($custom_template_info as $k => $v) {
				$custom_template_info[ $k ]["style_data"] = json_decode($v["control_data"], true);
			}
			// 给数组排序
			$sort = array(
				'direction' => 'SORT_ASC', // 排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
				'field' => 'sort'
			);
			$arrSort = array();
			foreach ($custom_template_info as $uniqid => $row) {
				foreach ($row as $key => $value) {
					$arrSort[ $key ][ $uniqid ] = $value;
				}
			}
			if ($sort['direction']) {
				array_multisort($arrSort[ $sort['field'] ], constant($sort['direction']), $custom_template_info);
			}

			foreach ($custom_template_info as $k => $v) {
				
				if ($v['control_name'] == "GoodsSearch") {
					
					// 商品搜索
					$custom_template_info[ $k ]["style_data"]['goods_search'] = json_decode($v["style_data"]['goods_search'], true);
				} elseif ($v["control_name"] == "GoodsList") {
					
					// 商品列表
					$custom_template_info[ $k ]["style_data"]['goods_list'] = json_decode($v["style_data"]['goods_list'], true);
					if ($custom_template_info[ $k ]["style_data"]['goods_list']["goods_source"] > 0) {
						
						$goods_list = $goods->getGoodsListNew(1, $custom_template_info[ $k ]["style_data"]['goods_list']["goods_limit_count"], [
							"ng.category_id" => $custom_template_info[ $k ]["style_data"]['goods_list']["goods_source"],
							"ng.state" => 1
						], "ng.sort desc,ng.create_time desc");
						$goods_query = array();
						if (!empty($goods_list)) {
							$goods_query = $goods_list["data"];
						}
						$custom_template_info[ $k ]["goods_list"] = $goods_query;
					}
				} elseif ($v["control_name"] == "ImgAd") {
					
					// 图片广告
					if (trim($v["style_data"]["img_ad"]) != "") {
						$custom_template_info[ $k ]["style_data"]["img_ad"] = json_decode($v["style_data"]["img_ad"], true);
					} else {
						$custom_template_info[ $k ]["style_data"]["img_ad"] = array();
					}
				} elseif ($v["control_name"] == "NavHyBrid") {
					
					$custom_template_info[ $k ]["style_data"]["nav_hybrid"] = json_decode($v["style_data"]["nav_hybrid"], true);
				} elseif ($v["control_name"] == "GoodsClassify") {
					if ($type = 2) {
                        unset($custom_template_info[$k]);
                    } else {
                        // 商品分类
                        if (trim($v["style_data"]["goods_classify"]) != "") {
                            $category = new GoodsCategory();
                            $category_array = json_decode($v["style_data"]["goods_classify"], true);
                            foreach ($category_array as $t => $m) {
                                $category_info = $category->getGoodsCategoryDetail($m["id"]);
                                $category_array[$t]["name"] = $category_info["short_name"];
                                $goods_list = $goods->getGoodsListNew(1, $m["show_count"], [
                                    "ng.category_id" => $m["id"],
                                    "ng.state" => 1
                                ], "ng.sort desc,ng.create_time desc");
                                $category_array[$t]["goods_list"] = $goods_list["data"];
                            }
                            $custom_template_info[$k]["style_data"]["goods_classify"] = $category_array;
                        } else {
                            $custom_template_info[$k]["style_data"]["goods_classify"] = array();
                        }
                    }
				} elseif ($v["control_name"] == "Footer") {
                    // 底部菜单
                    if (trim($v["style_data"]["footer"]) != "") {
                        $custom_template_info[ $k ]["style_data"]["footer"] = json_decode($v["style_data"]["footer"], true);
                    } else {
                        $custom_template_info[ $k ]["style_data"]["footer"] = array();
                    }
				} elseif ($v["control_name"] == "CustomModule") {
					if ($type = 2) {
                        unset($custom_template_info[$k]);
                    } else {
                        // 自定义模块
                        $custom_module = json_decode($v["style_data"]['custom_module'], true);

                        $custom_module_list = $this->getFormatCustomTemplate($custom_module['module_id']);
                        if (!empty($custom_module_list)) {
                            for ($i = 0; $i < count($custom_module_list['template_data']); $i++) {

                                array_push($custom_template_info, $custom_module_list['template_data'][$i]);
                            }
                        }
                    }
				} elseif ($v["control_name"] == "Coupons") {
					
					// 优惠券
					$custom_template_info[ $k ]["style_data"]['coupons'] = json_decode($v["style_data"]['coupons'], true);
				} elseif ($v["control_name"] == "Video") {
					
					// 视频
					$custom_template_info[ $k ]["style_data"]['video'] = json_decode($v["style_data"]['video'], true);
				} elseif ($v["control_name"] == "ShowCase") {
					
					// 橱窗
					$custom_template_info[ $k ]["style_data"]['show_case'] = json_decode($v["style_data"]['show_case'], true);
				} elseif ($v['control_name'] == "Notice") {
					
					// 公告
					$custom_template_info[ $k ]['style_data']['notice'] = json_decode($v['style_data']['notice'], true);
				} elseif ($v['control_name'] == "TextNavigation") {
					
					// 文本导航
					$custom_template_info[ $k ]['style_data']['text_navigation'] = json_decode($v['style_data']['text_navigation'], true);
				} elseif ($v['control_name'] == "Title") {
					
					// 标题
					$custom_template_info[ $k ]['style_data']['title'] = json_decode($v['style_data']['title'], true);
				} elseif ($v['control_name'] == "AuxiliaryLine") {
					
					// 辅助线
					$custom_template_info[ $k ]['style_data']['auxiliary_line'] = json_decode($v['style_data']['auxiliary_line'], true);
				} elseif ($v['control_name'] == "AuxiliaryBlank") {
					
					// 辅助空白
					$custom_template_info[ $k ]['style_data']['auxiliary_blank'] = json_decode($v['style_data']['auxiliary_blank'], true);
				}
			}
			$custom_template["template_name"] = $template_info["template_name"];
			$custom_template["template_data"] = $custom_template_info;
		}
		return $custom_template;
	}
	
	/**
	 * 获取手机端自定义模板列表
	 * @param number $page_index
	 * @param number $page_size
	 * @param string $condition
	 * @param string $order
	 * @param string $field
	 * @return multitype:number unknown
	 */
	public function getWapCustomTemplateList($page_index = 1, $page_size = 0, $condition = '', $order = 'id desc', $field = '*')
	{
		$data = [ $page_index, $page_size, $condition, $order, $field ];
		$data = json_encode($data);
		$cache = Cache::tag('wap_custom_template')->get("getWapCustomTemplateList" . $data);
		if (!empty($cache)) {
			return $cache;
		}
		$model = new SysWapCustomTemplateModel();
		$list = $model->pageQuery($page_index, $page_size, $condition, $order, $field);
		Cache::tag('wap_custom_template')->set("getWapCustomTemplateList" . $data, $list);
		return $list;
	}
	
	/**
	 * 根据主键id删除手机端自定义模板
	 * @param unknown $id
	 * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
	 */
	public function deleteWapCustomTemplateById($id)
	{
		Cache::clear('wap_custom_template');
		$model = new SysWapCustomTemplateModel();
		$res = $model->destroy([
			"id" => [
				"in",
				$id
			]
		]);
		return $res;
	}
	
	/**
	 * 设置默认手机自定义模板
	 */
	public function setDefaultWapCustomTemplate($id, $type = 1)
	{
		Cache::clear('wap_custom_template');
		$model = new SysWapCustomTemplateModel();
		$res = $model->save([
			"is_default" => 0
		], [
			"id" => array(
				'NEQ',
				$id
			),
            'type' => $type
		]);
		
		$res = $model->save([
			"is_default" => 1,
			"modify_time" => time()
		], [
			"id" => $id
		]);
		return $res;
	}
	
	/**
	 * 根据id获取手机端自定义模板
	 */
	public function getWapCustomTemplateById($id)
	{
		$cache = Cache::tag('wap_custom_template')->get("getWapCustomTemplateById" . $id);
		if (!empty($cache)) {
			return $cache;
		}
		$model = new SysWapCustomTemplateModel();
		$res = $model->getInfo([
			'id' => $id
		]);
		Cache::tag('wap_custom_template')->set("getWapCustomTemplateById" . $id, $res);
		return $res;
	}
	
	/**
	 * 编辑手机端自定义模板
	 * @param unknown $template_name
	 * @param unknown $template_data
	 * @return Ambigous <boolean, number, \think\false, string>
	 */
	public function editWapCustomTemplate($id, $template_name, $template_data, $type = 1)
	{
		Cache::clear('wap_custom_template');
		$data['shop_id'] = $this->instance_id;
		$data['template_name'] = $template_name;
		$data['template_data'] = $template_data;
		$data['modify_time'] = time();
		$data['create_time'] = time();
		$model = new SysWapCustomTemplateModel();
		if ($id == 0) {
			// 添加
			$default_custom_template = $this->getDefaultWapCustomTemplate($type);
			if (empty($default_custom_template)) {
				$data['is_default'] = 1;
				$data['type'] = $type;
			}
			$res = $model->save($data);
		} else {
			$res = $model->save($data, [
				'id' => $id
			]);
		}
		return $res;
	}
	
	/**
	 * 获取默认自定义模板
	 * @return unknown
	 */
	public function getDefaultWapCustomTemplate($type = 1)
	{
		$cache = Cache::tag('wap_custom_template')->get("getDefaultWapCustomTemplate_" . $type);
		if (!empty($cache)) {
			return $cache;
		}
		$model = new SysWapCustomTemplateModel();
		$res = $default_custom_template = $model->getInfo([
			"shop_id" => $this->instance_id,
			"is_default" => 1,
            'type' => $type
		]);
		Cache::tag('wap_custom_template')->set("getDefaultWapCustomTemplate_" . $type, $res);
		return $res;
	}
	
	/**
	 * 获取手机端自定义模板页面
	 */
	public function getDiyViewType($type = 1)
	{
	    if ($type == 1) {
            return [
                'index' => [
                    'name' => '首页'
                ],
                'category' => [
                    'name' => '分类'
                ]
            ];
		} else {
	        return [
                'index' => [
                    'name' => '首页'
                ]
            ];
        }
		
	}
	
	/**
	 * 设置模板
	 */
	public function setDiyView($template_type, $id, $type = 1)
	{
		Cache::clear('wap_custom_template');
		$model = new SysWapCustomTemplateModel();
		$model->save([ 'is_default' => 0, 'template_type' => "" ], [ 'template_type' => $template_type, 'type' => $type]);
		$res = $model->save([ 'template_type' => $template_type, 'is_default' => 1, 'modify_time' => time() ], [ 'id' => $id ]);
		return $res;
	}
	
	/**
	 * 获取微页面
	 */
	public function getDiyView($template_type, $type)
	{
//		$cache = Cache::tag('wap_custom_template')->get("getDiyView");
//		if (!empty($cache)) {
//			return $cache;
//		}
		$model = new SysWapCustomTemplateModel();
		$res = $default_custom_template = $model->getInfo([
			"template_type" => $template_type,
			"is_default" => 1,
            'type' => $type
		]);
		$template = '';
		if (!empty($res)) {
			$template = $this->getFormatCustomTemplate($res['id']);
		}

//		Cache::tag('wap_custom_template')->set("getDiyView", $res);
		return $template;
	}

    /**
     * 是否存在默认微页面
     * @param $template_type
     * @param $type
     */
	public function hasDefaultDiyView($template_type, $type)
    {
        $model = new SysWapCustomTemplateModel();
        $count = $model->getCount([
            "template_type" => $template_type,
			"is_default" => 1,
            'type' => $type
        ]);
        return $count;
    }
}