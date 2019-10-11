<?php
/**
 * tuangou.php
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

namespace addons\NsPintuan\admin\controller;

use addons\NsPintuan\data\service\Pintuan;
use app\admin\controller\BaseController;

/**
 * 团购
 */
class Tuangou extends BaseController
{
	
	public $addon_view_path;
	
	public function __construct()
	{
		parent::__construct();
		$this->addon_view_path = ADDON_DIR . '/NsPintuan/template/';
	}
	
	/**
	 * 团购列表
	 */
	public function tuangouList()
	{
		if (request()->isAjax()) {
			$pintuan = new Pintuan();
			$page_index = request()->post("pageIndex", 1);
			$start_date = request()->post('start_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('start_date'));
			$end_date = request()->post('end_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('end_date'));
			$goods_name = request()->post("goods_name", "");
			$is_open = request()->post("is_open", "");
			$page_size = request()->post('page_size', 0);
			
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
				$condition["create_time"] = [
					[
						">",
						$start_date
					]
				];
			} elseif ($start_date == 0 && $end_date != 0) {
				$condition["create_time"] = [
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
			
			$condition["ng.shop_id"] = $this->instance_id;
			$list = $pintuan->getGooodsPintuanList($page_index, $page_size, $condition, 'ng.create_time desc');
			return $list;
		} else {
			$pintuan = new Pintuan();
			$data = $pintuan->getTuangouType();
			$this->assign('tuangou_type', $data);
			
			$child_menu_list = array(
				array(
					'url' => "tuangou/pintuanlist",
					'menu_name' => "拼团列表",
					"active" => 0,
					'module' => 'NsPintuan'
				),
				array(
					'url' => "tuangou/tuangouList",
					'menu_name' => "拼团设置",
					"active" => 1,
					'module' => 'NsPintuan'
				)
			);
			$this->assign("child_menu_list", $child_menu_list);
			
			return view($this->addon_view_path . $this->style . "Tuangou/tuangouList.html");
		}
	}
	
	/**
	 * 拼团设置
	 */
	public function updateGoodsPintuan()
	{
		if (request()->isAjax()) {
			$tuangou_id = request()->post('tuangou_id', 0);
			$goods_id = request()->post('goods_id', 0);
			$is_open = request()->post('is_open', 0);
			$is_show = request()->post('is_show', 0);
			$tuangou_num = request()->post('tuangou_num', 0);
			$tuangou_money = request()->post('tuangou_money', 0);
			$tuangou_time = request()->post('tuangou_time', 0);
			$tuangou_type = request()->post('tuangou_type', 0);
			$colonel_commission = request()->post('colonel_commission', 0);
			$colonel_coupon = request()->post('colonel_coupon', 0);
			$colonel_point = request()->post('colonel_point', 0);
			$remark = request()->post('remark', '');
			$colonel_content = request()->post('colonel_content', '');
			
			// 转化拼团设置
			$tuangou_array = array(
				"colonel_commission" => $colonel_commission,
				"colonel_coupon" => $colonel_coupon,
				"colonel_point" => $colonel_point,
				"colonel_content" => $colonel_content
			);
			$tuangou_content_json = json_encode($tuangou_array);
			if (empty($goods_id)) {
				return AjaxReturn(-1);
			}
			$pintuan = new Pintuan();

            $condition = array(
                "goods_id" => $goods_id,
                "status" => 0
            );
            $count = $pintuan->getPintuanCount($condition);
            if($count > 0){
                return AjaxReturn(TUANGOU_EXIST);
            }
			$res = $pintuan->addUpdateGoodsPintuan($tuangou_id, $goods_id, $is_open, $is_show, $tuangou_money, $tuangou_num, $tuangou_time, $tuangou_type, $tuangou_content_json, $remark);
			$code_message = "";
			if($res["code"] <= 0){
			    $code_message = $res["data"];
			}
			return AjaxReturn($res["code"], $res, $code_message);
		}
	}
	
	/**
	 * 拼团的详细信息
	 */
	public function getPintuanDetail()
	{
		if (request()->isAjax()) {
			$goods_id = request()->post('goods_id', 0);
			if (!empty($goods_id)) {
				$pintuan = new Pintuan();
				$list = $pintuan->getGoodsPintuanDetail($goods_id);
				return $list;
			} else {
				return [];
			}
		}
	}
	
	/**
	 * 开关拼团
	 */
	public function modifyGoodsPintuan()
	{
		if (request()->isAjax()) {
			$goods_id = request()->post('goods_id', 0);
			$is_open = request()->post('is_open', 0);
			if (!empty($goods_id)) {
				$pintuan = new Pintuan();
				if($is_open == 0){
                    $condition = array(
                        "goods_id" => $goods_id,
                        "status" => 0
                    );
                    $count = $pintuan->getPintuanCount($condition);
                    if($count > 0){
                        return AjaxReturn(TUANGOU_EXIST);
                    }
                }

				$res = $pintuan->modifyGoodsTuangou($goods_id, $is_open);
				$code_message = "";
				if($res["code"] <= 0){
				    $code_message = $res["data"];
				}
				return AjaxReturn($res["code"], $res, $code_message);
			} else {
				return AjaxReturn(-1);
			}
		}
	}
	
	/**
	 * 拼团列表
	 */
	public function pintuanList()
	{
		if (request()->isAjax()) {
			$pintuan = new Pintuan();
			$page_index = request()->post("pageIndex", 1);
			$start_date = request()->post('start_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('start_date'));
			$end_date = request()->post('end_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('end_date'));
			$group_name = request()->post("group_name", "");
			$status = request()->post('status', 0);
			$page_size = request()->post('page_size', 0);
			
			if ($start_date != 0 && $end_date != 0) {
				$condition["create_time"] = [
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
				$condition["create_time"] = [
					[
						">",
						$start_date
					]
				];
			} elseif ($start_date == 0 && $end_date != 0) {
				$condition["create_time"] = [
					[
						"<",
						$end_date
					]
				];
			}
			if (!empty($group_name)) {
				$condition["group_name"] = array(
					"like",
					"%" . $group_name . "%"
				);
			}
			if (!empty($status)) {
				$condition["status"] = $status;
			}
			$list = $pintuan->getGoodsPintuanStatusList($page_index, $page_size, $condition, 'create_time desc', '');
			return $list;
		} else {
			$child_menu_list = array(
				array(
					'url' => "tuangou/pintuanlist",
					'menu_name' => "拼团列表",
					"active" => 1,
					'module' => 'NsPintuan'
				),
				array(
					'url' => "tuangou/tuangouList",
					'menu_name' => "拼团设置",
					"active" => 0,
					'module' => 'NsPintuan'
				)
			);
			$this->assign("child_menu_list", $child_menu_list);
			return view($this->addon_view_path . $this->style . 'Tuangou/pintuanList.html');
		}
	}
	
	public function tuangouGoodsIsRecommend()
	{
		if (request()->isAjax()) {
			$group_id = request()->post('group_id', 0);
			$is_recommend = request()->post('is_recommend', 0);
			if (!empty($group_id)) {
				$tuangou = new Pintuan();
				$res = $tuangou->modifyTuangouGroupRecommend($group_id, $is_recommend);
				return AjaxReturn($res);
			} else {
				return AjaxReturn(-1);
			}
		}
	}
	
	/**
	 * 拼团完成(未达到条件)
	 *
	 * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
	 */
	public function pintuanGroupComplete()
	{
		if (request()->isAjax()) {
			$group_id = request()->post('group_id', 0);
			if (!empty($group_id)) {
				$tuangou = new Pintuan();
				$res = $tuangou->pintuanGroupComplete($group_id);
				return AjaxReturn($res);
			} else {
				return AjaxReturn(-1);
			}
		}
	}
	
	/**
	 * 拼团关闭后 退款(未达到条件)
	 *
	 * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
	 */
	public function tuangouGroupRefund()
	{
		if (request()->isAjax()) {
			$group_id = request()->post('group_id', 0);
			if (!empty($group_id)) {
				$tuangou = new Pintuan();
				$res = $tuangou->tuangouGroupRefund($group_id);
				return AjaxReturn($res);
			} else {
				return AjaxReturn(-1);
			}
		}
	}
}