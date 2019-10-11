<?php
/**
 * Promotion.php
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

namespace addons\NsCombopackage\admin\controller;

use addons\NsCombopackage\data\service\Promotion as PromotionService;
use app\admin\controller\BaseController;

/**
 * 组合套餐营销控制器
 */
class Promotion extends BaseController
{
	
	public $addon_view_path;
	
	public function __construct()
	{
		parent::__construct();
		$this->addon_view_path = ADDON_DIR . '/NsCombopackage/template/';
	}
	
	/**
	 * 组合套餐列表
	 */
	public function comboPackagePromotionList()
	{
		if (request()->isAjax()) {
			$promotionService = new PromotionService();
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$combo_package_name = request()->post("search_text", "");
			$order = "create_time desc";
			$condition["combo_package_name"] = array(
				"like",
				"%$combo_package_name%"
			);
			$list = $promotionService->getComboPackageList($page_index, $page_size, $condition, $order);
			return $list;
		}
		return view($this->addon_view_path . $this->style . "Promotion/comboPackagePromotionList.html");
	}
	
	/**
	 * 组合套餐编辑
	 */
	public function comboPackagePromotionEdit()
	{
		$id = request()->get("id", 0);
		$promotionService = new PromotionService();
		$info = $promotionService->getComboPackageDetail($id);
		$this->assign("info", $info);
		$this->assign("id", $id);
		return view($this->addon_view_path . $this->style . "Promotion/comboPackagePromotionEdit.html");
	}
	
	/**
	 * 添加或编辑组合套餐
	 */
	public function addOrEditComboPackage()
	{
		$promotionService = new PromotionService();
		$id = request()->post("id", 0);
		$combo_package_name = request()->post("combo_package_name", "");
		$combo_package_price = request()->post("combo_package_price", "");
		$goods_id_array = request()->post("goods_id_array", "");
		$is_shelves = request()->post("is_shelves", 1);
		$original_price = request()->post("original_price", "");
		$save_the_price = request()->post("save_the_price", "");
		
		$res = $promotionService->addOrEditComboPackage($id, $combo_package_name, $combo_package_price, $goods_id_array, $is_shelves, $this->instance_id, $original_price, $save_the_price);
		return AjaxReturn($res);
	}
	
	/**
	 * 删除组合套餐
	 */
	public function deleteComboPackage()
	{
		$promotionService = new PromotionService();
		$ids = request()->post("ids", "");
		$res = $promotionService->deleteComboPackage($ids);
		return AjaxReturn($res);
	}
}