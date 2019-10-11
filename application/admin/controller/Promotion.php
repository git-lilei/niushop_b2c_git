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

namespace app\admin\controller;

use data\service\Address;
use data\service\Config as ConfigService;
use data\service\Goods as GoodsService;
use data\service\GoodsCategory;
use data\service\GoodsGroup;
use data\service\Member;
use data\service\Promotion as PromotionService;
use data\service\GoodsBrand;

/**
 * 营销控制器
 */
class Promotion extends BaseController
{
	public function index()
	{
		$member = new Member();
		$member_action_config = $member->getMemberActionConfig([ 'type' => 'all' ]);
		$this->assign("member_action_config", $member_action_config);
		
		$app_recommend = hook("getAppRecommendConfig", ['type' => 'all']);
		$app_recommend = array_filter($app_recommend);
		$this->assign('app_recommend', $app_recommend);
		return view($this->style . "Promotion/index");
	}
	
	/**
	 * 会员营销
	 * @return \think\response\View
	 */
	public function memberPromotion()
	{
		$member = new Member();
		$member_action_config = $member->getMemberActionConfig([ 'type' => 'all' ]);
		$this->assign("member_action_config", $member_action_config);
		return view($this->style . "Promotion/memberPromotion");
	}
	
	public function gamePromotion()
	{
		return view($this->style . "Promotion/gamePromotion");
	}
	
	/**
	 * 优惠券类型列表
	 */
	public function couponTypeList()
	{
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$search_text = request()->post('search_text', '');
			$coupon = new PromotionService();
			$condition = array(
				'shop_id' => $this->instance_id,
				'coupon_name' => array(
					'like',
					'%' . $search_text . '%'
				)
			);
			$list = $coupon->getCouponTypeList($page_index, $page_size, $condition, 'create_time desc');
			return $list;
		} else {
			return view($this->style . "Promotion/couponTypeList");
		}
	}
	
	/**
	 * 优惠券发放记录
	 */
	public function couponGrantLog()
	{
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$search_text = request()->post('search_text', '');
			$status = request()->post('status', -1);
			$coupon_type_id = request()->post('coupon_type_id', '');
			$coupon = new PromotionService();
			
			$condition = array(
				'coupon_type_id' => $coupon_type_id
			);
			if ($status !== '-1') {
				$condition['state'] = $status;
			}
			$list = $coupon->getCouponGrantLogList($page_index, $page_size, $condition);
			return $list;
		}
		$coupon_type_id = request()->get('coupon_type_id', 0);
		$status = request()->get('status', -1);
		$this->assign('coupon_type_id', $coupon_type_id);
		return view($this->style . "Promotion/couponGrantLog");
	}
	
	/**
	 * 删除优惠券类型
	 */
	public function deleteCoupontype()
	{
		$coupon_type_id = request()->post('coupon_type_id', '');
		if (empty($coupon_type_id)) {
			$this->error("没有获取到优惠券信息");
		}
		$coupon = new PromotionService();
		$res = $coupon->deleteCoupontype($coupon_type_id);
		return AjaxReturn($res);
	}
	
	/**
	 * 添加优惠券类型
	 */
	public function addCouponType()
	{
		if (request()->isAjax()) {
			$coupon_name = request()->post('coupon_name', '');
			$money = request()->post('money', '');
			$count = request()->post('count', '');
			$max_fetch = request()->post('max_fetch', '');
			$at_least = request()->post('at_least', '');
			$need_user_level = request()->post('need_user_level', '');
			$range_type = request()->post('range_type', '');
			$start_time = request()->post('start_time', '');
			$end_time = request()->post('end_time', '');
			$is_show = request()->post('is_show', '');
			$goods_list = request()->post('goods_list', '');
			$term_of_validity_type = request()->post("term_of_validity_type", "");
			$fixed_term = request()->post("fixed_term", "");
			$coupon = new PromotionService();
			
			$data = array(
				'shop_id' => 0,
				'coupon_name' => $coupon_name,
				'money' => $money,
				'count' => $count,
				'max_fetch' => $max_fetch,
				'at_least' => $at_least,
				'need_user_level' => $need_user_level,
				'range_type' => $range_type,
				'start_time' => getTimeTurnTimeStamp($start_time),
				'end_time' => getTimeTurnTimeStamp($end_time),
				'is_show' => $is_show,
				'create_time' => time(),
				'term_of_validity_type' => $term_of_validity_type,
				'fixed_term' => $fixed_term,
				'goods_list' => $goods_list,
			);
			
			$retval = $coupon->addCouponType($data);
			return AjaxReturn($retval);
		} else {
			return view($this->style . "Promotion/addCouponType");
		}
	}
	
	/**
	 * 编辑优惠券
	 * @return unknown[]|string[]|unknown
	 */
	public function updateCouponType()
	{
		$coupon = new PromotionService();
		if (request()->isAjax()) {
			$coupon_type_id = request()->post('coupon_type_id', '');
			$coupon_name = request()->post('coupon_name', '');
			$money = request()->post('money', '');
			$count = request()->post('count', '');
			$repair_count = request()->post('repair_count', '');
			$max_fetch = request()->post('max_fetch', '');
			$at_least = request()->post('at_least', '');
			$need_user_level = request()->post('need_user_level', '');
			$range_type = request()->post('range_type', '');
			$start_time = request()->post('start_time', '');
			$end_time = request()->post('end_time', '');
			$is_show = request()->post('is_show', '');
			$goods_list = request()->post('goods_list', '');
			$term_of_validity_type = request()->post("term_of_validity_type", "");
			$fixed_term = request()->post("fixed_term", "");
			
			$data = array(
				'shop_id' => 0,
				'coupon_name' => $coupon_name,
				'money' => $money,
				'count' => $count,
				'max_fetch' => $max_fetch,
				'at_least' => $at_least,
				'need_user_level' => $need_user_level,
				'range_type' => $range_type,
				'start_time' => getTimeTurnTimeStamp($start_time),
				'end_time' => getTimeTurnTimeStamp($end_time),
				'is_show' => $is_show,
				'create_time' => time(),
				'term_of_validity_type' => $term_of_validity_type,
				'fixed_term' => $fixed_term,
				'goods_list' => $goods_list,
				'coupon_type_id' => $coupon_type_id,
				'repair_count' => $repair_count
			);
			$retval = $coupon->updateCouponType($data);
			return AjaxReturn($retval);
		} else {
			
			$coupon_type_id = request()->get('coupon_type_id', 0);
			if ($coupon_type_id == 0) {
				$this->error("没有获取到类型");
			}
			$coupon_type_data = $coupon->getCouponTypeDetail($coupon_type_id);
			
			$goods_id_array = array();
			foreach ($coupon_type_data['goods_list'] as $k => $v) {
				$goods_id_array[] = $v['goods_id'];
			}
			$goods_id_array = join(',', $goods_id_array);
			
			$coupon_type_data['goods_id_array'] = $goods_id_array;
			$this->assign("coupon_type_info", $coupon_type_data);
			
			return view($this->style . "Promotion/updateCouponType");
		}
	}
	
	/**
	 * 获取优惠券详情
	 */
	public function getCouponTypeInfo()
	{
		$coupon = new PromotionService();
		$coupon_type_id = request()->post('coupon_type_id', '');
		$coupon_type_data = $coupon->getCouponTypeDetail($coupon_type_id);
		return $coupon_type_data;
	}
	
	/**
	 * 功能：积分管理
	 */
	public function pointConfig()
	{
		$pointConfig = new PromotionService();
		if (request()->isAjax()) {
			$convert_rate = request()->post('convert_rate', '');
			$is_open = request()->post('is_open', 0);
			$desc = request()->post('desc', 0);
			$retval = $pointConfig->setPointConfig($convert_rate, $is_open, $desc);
			return AjaxReturn($retval);
		}
		$pointconfiginfo = $pointConfig->getPointConfig();
		$this->assign("pointconfiginfo", $pointconfiginfo);
		return view($this->style . "Promotion/pointConfig");
	}
	
	/**
	 * 赠品列表
	 */
	public function giftList()
	{
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$search_text = request()->post('search_text');
			$type = request()->post("type", 0);
			$is_virtual = request()->post("is_virtual", 0);
			$gift = new PromotionService();
			$condition = array(
				'shop_id' => 0,
				'gift_name' => array(
					'like',
					'%' . $search_text . '%'
				)
			);
			if ($type == 1) {
				$condition["start_time"] = [
					"LT",
					time()
				];
				$condition["end_time"] = [
					"GT",
					time()
				];
			}
			$list = $gift->getPromotionGiftList($page_index, $page_size, $condition);
			return $list;
		}else{
		    $child_menu_list = array(
		        array(
		            'url' => "Promotion/giftList",
		            'menu_name' => "赠品列表",
		            "active" => 1
		        ),
		        array(
		            'url' => "promotion/giftGrantRecordsList",
		            'menu_name' => "赠品发放记录",
		            "active" => 0
		        )
		    );
		    
		    $this->assign("child_menu_list", $child_menu_list);
		}
		return view($this->style . "Promotion/giftList");
	}
	
	
	/**
	 * 赠品列表
	 */
	public function gift()
	{
	    if (request()->isAjax()) {

	        $type = request()->post("type", 0);
	        $is_virtual = request()->post("is_virtual", "all");//是否筛选虚拟商品
	        $gift = new PromotionService();
	        $condition = array(
	            'shop_id' => 0
	        );
	        if ($type == 1) {
	            $condition["start_time"] = [
	                "LT",
	                time()
	            ];
	            $condition["end_time"] = [
	                "GT",
	                time()
	            ];
	        }
	        $list = $gift->getPromotionGiftQuery($condition);
	        //是否查询虚拟商品
			if($is_virtual != "all"){
			    if(!empty($list)){
			        $temp_list = array();
			        foreach($list as  $k => $v){
			            $gift_info = $gift->getGoodsInfoByGiftId($v["gift_id"]);
			            if($gift_info["is_virtual"] == $is_virtual){
			                $temp_list[] = $v;
			            }
			        }
			        $list = $temp_list;
			    }

			}
	        
	        return $list;
	    }else{
	        $child_menu_list = array(
	            array(
	                'url' => "Promotion/giftList",
	                'menu_name' => "赠品列表",
	                "active" => 1
	            ),
	            array(
	                'url' => "promotion/giftGrantRecordsList",
	                'menu_name' => "赠品发放记录",
	                "active" => 0
	            )
	        );
	        
	        $this->assign("child_menu_list", $child_menu_list);
	    }
	    return view($this->style . "Promotion/giftList");
	}
	/**
	 * 赠品发放记录列表
	 */
	public function giftGrantRecordsList()
	{
		$child_menu_list = array(
			array(
				'url' => "Promotion/giftList",
				'menu_name' => "赠品列表",
				"active" => 0
			),
			array(
				'url' => "promotion/giftGrantRecordsList",
				'menu_name' => "赠品发放记录",
				"active" => 1
			)
		);
		
		$this->assign("child_menu_list", $child_menu_list);
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$search_text = request()->post("search_text", "");
			$condition['gift_id'] = request()->post("gift_id", 0);
			if (empty($condition['gift_id'])) {
				unset($condition['gift_id']);
			}
			$condition['pgr.gift_name'] = [
				'like',
				"%$search_text%"
			];
			$gift = new PromotionService();
			$list = $gift->getPromotionGiftGrantRecordsList($page_index, $page_size, $condition, "pgr.id desc");
			return $list;
		}
		$gift_id = request()->get("gift_id", 0);
		$this->assign('gift_id', $gift_id);
		return view($this->style . "Promotion/giftGrantRecordsList");
	}
	
	/**
	 * 添加赠品
	 */
	public function addGift()
	{
		if (request()->isAjax()) {
			$gift_name = request()->post('gift_name', ''); // 赠品活动名称
			$start_time = request()->post('start_time', ''); // 赠品活动开始时间
			$end_time = request()->post('end_time', ''); // 赠品活动结束时间
			$goods_id = request()->post('goods_id', ''); // 要赠送的商品id
			$days = request()->post('days', ''); // 领取有效期/天（0表示不限），2.0版本不用
			$max_num = request()->post('max_num', ''); // 领取限制(次/人 (0表示不限领取次数))，2.0版本不用
			$gift = new PromotionService();
			
			$data = array(
				'gift_name' => $gift_name,
				'shop_id' => $this->instance_id,
				'start_time' => getTimeTurnTimeStamp($start_time),
				'end_time' => getTimeTurnTimeStamp($end_time),
				'days' => $days,
				'max_num' => $max_num,
				'create_time' => time(),
				'goods_id' => $goods_id
			);
			
			$res = $gift->addPromotionGift($data);
			return AjaxReturn($res);
		}
		return view($this->style . "Promotion/addGift");
	}
	
	/**
	 * 修改赠品
	 */
	public function updateGift()
	{
		$gift = new PromotionService();
		if (request()->isAjax()) {
			$gift_id = request()->post('gift_id', '');
			$gift_name = request()->post('gift_name', '');
			$start_time = request()->post('start_time', '');
			$end_time = request()->post('end_time', '');
			$days = request()->post('days', '');
			$max_num = request()->post('max_num', '');
			$goods_id = request()->post('goods_id', '');
			
			$data = array(
				'gift_name' => $gift_name,
				'shop_id' => $this->instance_id,
				'start_time' => getTimeTurnTimeStamp($start_time),
				'end_time' => getTimeTurnTimeStamp($end_time),
				'days' => $days,
				'max_num' => $max_num,
				'modify_time' => time(),
				'gift_id' => $gift_id,
				'goods_id' => $goods_id
			);
			$res = $gift->updatePromotionGift($data);
			return AjaxReturn($res);
		} else {
			$gift_id = request()->get('gift_id', 0);
			if (!is_numeric($gift_id)) {
				$this->error('未获取到信息');
			}
			$info = $gift->getPromotionGiftDetail($gift_id);
			$this->assign('info', $info);
			return view($this->style . "Promotion/updateGift");
		}
	}
	
	/**
	 * 获取赠品 详情
	 *
	 * @param unknown $gift_id
	 */
	public function getGiftInfo($gift_id)
	{
		$gift = new PromotionService();
		$info = $gift->getPromotionGiftDetail($gift_id);
		return $info;
	}
	
	/**
	 * 删除赠品
	 *
	 * @return unknown[]
	 */
	public function deleteGift()
	{
		$gift_id = request()->post("gift_id", 0);
		$gift = new PromotionService();
		$res = $gift->deletePromotionGift($gift_id);
		return ajaxReturn($res);
	}
	
	/**
	 * 满减送 列表
	 */
	public function mansongList()
	{
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$search_text = request()->post('search_text', '');
			$status = request()->post('status', '');
			$condition = array(
				'shop_id' => $this->instance_id,
				'mansong_name' => array(
					'like',
					'%' . $search_text . '%'
				)
			);
			$mansong = new PromotionService();
			if ($status !== '-1') {
				$condition['status'] = $status;
				$list = $mansong->getPromotionMansongList($page_index, $page_size, $condition);
			} else {
				$list = $mansong->getPromotionMansongList($page_index, $page_size, $condition);
			}
			return $list;
		}
		
		$status = request()->get('status', -1);
		$this->assign("status", $status);
		$child_menu_list = array(
			array(
				'url' => "promotion/mansonglist",
				'menu_name' => "全部",
				"active" => $status == '-1' ? 1 : 0
			),
			array(
				'url' => "promotion/mansonglist?status=0",
				'menu_name' => "未发布",
				"active" => $status == 0 ? 1 : 0
			),
			array(
				'url' => "promotion/mansonglist?status=1",
				'menu_name' => "进行中",
				"active" => $status == 1 ? 1 : 0
			),
			array(
				'url' => "promotion/mansonglist?status=3",
				'menu_name' => "已关闭",
				"active" => $status == 3 ? 1 : 0
			),
			array(
				'url' => "promotion/mansonglist?status=4",
				'menu_name' => "已结束",
				"active" => $status == 4 ? 1 : 0
			)
		);
		$this->assign('child_menu_list', $child_menu_list);
		return view($this->style . "Promotion/mansongList");
	}
	
	/**
	 * 添加满减送活动
	 *
	 * @return \think\response\View
	 */
	public function addMansong()
	{
		$mansong = new PromotionService();
		if (request()->isAjax()) {
			$mansong_name = request()->post('mansong_name', '');
			$start_time = request()->post('start_time', '');
			$end_time = request()->post('end_time', '');
			$type = request()->post('type', '');
			$range_type = request()->post('range_type', '');
			$rule = request()->post('rule', '');
			$goods_id_array = request()->post('goods_id_array', '');
			
			$data = array(
				'mansong_name' => $mansong_name,
				'start_time' => $start_time,
				'end_time' => $end_time,
				'shop_id' => $this->instance_id,
				'status' => 0, // 状态重新设置
				'remark' => '',
				'type' => $type,
				'range_type' => $range_type,
				'create_time' => time(),
				"rule" => $rule,
				"goods_id_array" => $goods_id_array
			);
			$res = $mansong->addPromotionMansong($data);
			return AjaxReturn($res);
		} else {
			return view($this->style . "Promotion/addMansong");
		}
	}
	
	/**
	 * 修改 满减送活动
	 */
	public function updateMansong()
	{
		$mansong = new PromotionService();
		if (request()->isAjax()) {
			$mansong_id = request()->post('mansong_id', '');
			$mansong_name = request()->post('mansong_name', '');
			$start_time = request()->post('start_time', '');
			$end_time = request()->post('end_time', '');
			$type = request()->post('type', '');
			$range_type = request()->post('range_type', '');
			$rule = request()->post('rule', '');
			$goods_id_array = request()->post('goods_id_array', '');
			
			$data = array(
				'mansong_name' => $mansong_name,
				'start_time' => $start_time,
				'end_time' => $end_time,
				'shop_id' => $this->instance_id,
				'status' => 0, // 状态重新设置
				'remark' => '',
				'type' => $type,
				'range_type' => $range_type,
				"rule" => $rule,
				"goods_id_array" => $goods_id_array,
				"mansong_id" => $mansong_id
			);
			$res = $mansong->updatePromotionMansong($data);
			return AjaxReturn($res);
		} else {
			$mansong_id = request()->get('mansong_id', '');
			if (!is_numeric($mansong_id)) {
				$this->error('未获取到信息');
			}
			$info = $mansong->getPromotionMansongDetail($mansong_id);
			$info['goods_id_array'] = join(',', $info['goods_id_array']);
			$condition = array(
				'shop_id' => $this->instance_id
			);
			$coupon_type_list = $mansong->getCouponTypeList(1, 0, $condition);
			$gift_list = $mansong->getPromotionGiftList(1, 0, $condition);
			$this->assign('coupon_type_list', $coupon_type_list);
			$this->assign('gift_list', $gift_list);
			$this->assign('mansong_info', $info);
			return view($this->style . "Promotion/updateMansong");
		}
	}
	
	/**
	 * 获取限时折扣；列表
	 */
	public function getDiscountList()
	{
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$search_text = request()->post('search_text', '');
			$status = request()->post('status', '');
			$discount = new PromotionService();
			$condition = array(
				'shop_id' => $this->instance_id,
				'discount_name' => array(
					'like',
					'%' . $search_text . '%'
				)
			);
			if ($status !== '-1') {
				$condition['status'] = $status;
				$list = $discount->getPromotionDiscountList($page_index, $page_size, $condition);
			} else {
				$list = $discount->getPromotionDiscountList($page_index, $page_size, $condition);
			}
			
			return $list;
		}
		
		$status = request()->get('status', -1);
		$this->assign("status", $status);
		$child_menu_list = array(
			array(
				'url' => "promotion/getdiscountList",
				'menu_name' => "全部",
				"active" => $status == '-1' ? 1 : 0
			),
			array(
				'url' => "promotion/getdiscountList?status=0",
				'menu_name' => "未发布",
				"active" => $status == 0 ? 1 : 0
			),
			array(
				'url' => "promotion/getdiscountList?status=1",
				'menu_name' => "进行中",
				"active" => $status == 1 ? 1 : 0
			),
			array(
				'url' => "promotion/getdiscountList?status=3",
				'menu_name' => "已关闭",
				"active" => $status == 3 ? 1 : 0
			),
			array(
				'url' => "promotion/getdiscountList?status=4",
				'menu_name' => "已结束",
				"active" => $status == 4 ? 1 : 0
			)
		);
		$this->assign('child_menu_list', $child_menu_list);
		
		return view($this->style . "Promotion/getDiscountList");
	}
	
	/**
	 * 添加限时折扣
	 */
	public function addDiscount()
	{
		if (request()->isAjax()) {
			$discount = new PromotionService();
			$discount_name = request()->post('discount_name', '');
			$start_time = request()->post('start_time', '');
			$end_time = request()->post('end_time', '');
			$remark = '';
			$goods_id_array = request()->post('goods_id_array', '');
			$decimal_reservation_number = request()->post("decimal_reservation_number", 2);
			
			$data = array(
				'discount_name' => $discount_name,
				'start_time' => getTimeTurnTimeStamp($start_time),
				'end_time' => getTimeTurnTimeStamp($end_time),
				'shop_id' => 0,
				'status' => 0,
				'remark' => $remark,
				'create_time' => time(),
				'decimal_reservation_number' => $decimal_reservation_number,
				'goods_id_array' => $goods_id_array
			);
			
			
			$retval = $discount->addPromotiondiscount($data);
			$message = "";
			if ($retval["code"] <= 0) {
				$message = $retval["data"];
			}
			return AjaxReturn($retval["code"], $retval, $message);
		}
		return view($this->style . "Promotion/addDiscount");
	}
	
	/**
	 * 修改限时折扣
	 */
	public function updateDiscount()
	{
		if (request()->isAjax()) {
			$discount = new PromotionService();
			$discount_id = request()->post('discount_id', '');
			$discount_name = request()->post('discount_name', '');
			$start_time = request()->post('start_time', '');
			$end_time = request()->post('end_time', '');
			$remark = '';
			$goods_id_array = request()->post('goods_id_array', '');
			$decimal_reservation_number = request()->post("decimal_reservation_number", 2);
			
			$data = array(
				'discount_name' => $discount_name,
				'start_time' => getTimeTurnTimeStamp($start_time),
				'end_time' => getTimeTurnTimeStamp($end_time),
				'shop_id' => 0,
				'status' => 0,
				'remark' => $remark,
				'decimal_reservation_number' => $decimal_reservation_number,
				'goods_id_array' => $goods_id_array,
				'discount_id' => $discount_id
			);
			
			$retval = $discount->updatePromotionDiscount($data);
			$message = "";
			if ($retval["code"] <= 0) {
				$message = $retval["data"];
			}
			return AjaxReturn($retval["code"], $retval, $message);
		}
		$info = $this->getDiscountDetail();
		if (!empty($info['goods_list'])) {
			foreach ($info['goods_list'] as $k => $v) {
				$goods_id_array[] = $v['goods_id'];
				$selected_data[ $v['goods_id'] ] = $v['discount'];
			}
		}
		//选择商品的id
		$goods_id_array = join(',', $goods_id_array);
		$info['goods_id_array'] = $goods_id_array;
		//包含折扣的选择商品数据
		$selected_data = json_encode($selected_data);
		$this->assign('selected_data', $selected_data);
		
		$this->assign("info", $info);
		return view($this->style . "Promotion/updateDiscount");
	}
	
	/**
	 * 获取限时折扣详情
	 */
	public function getDiscountDetail()
	{
		$discount_id = request()->get('discount_id', '');
		if (!is_numeric($discount_id)) {
			$this->error("没有获取到折扣信息");
		}
		$discount = new PromotionService();
		$detail = $discount->getPromotionDiscountDetail($discount_id);
		return $detail;
	}
	
	/**
	 * 获取满减送详情
	 */
	public function getMansongDetail()
	{
		$mansong_id = request()->get('mansong_id', '');
		if (!is_numeric($mansong_id)) {
			$this->error("没有获取到满减送信息");
		}
		$mansong = new PromotionService();
		$detail = $mansong->getPromotionMansongDetail($mansong_id);
		return $detail;
	}
	
	/**
	 * 删除限时折扣
	 */
	public function delDiscount()
	{
		$discount_id = request()->post('discount_id', '');
		if (empty($discount_id)) {
			$this->error("没有获取到折扣信息");
		}
		$discount = new PromotionService();
		$res = $discount->deletePromotionDiscount($discount_id);
		return AjaxReturn($res);
	}
	
	/**
	 * 关闭正在进行的限时折扣
	 */
	public function closeDiscount()
	{
		$discount_id = request()->post('discount_id', '');
		if (!is_numeric($discount_id)) {
			$this->error("没有获取到折扣信息");
		}
		$discount = new PromotionService();
		$res = $discount->closePromotionDiscount($discount_id);
		return AjaxReturn($res);
	}
	
	/**
	 * 删除满减送活动
	 *
	 * @return unknown[]
	 */
	public function delMansong()
	{
		$mansong_id = request()->post('mansong_id', '');
		if (empty($mansong_id)) {
			$this->error("没有获取到满减送信息");
		}
		$mansong = new PromotionService();
		$res = $mansong->deletePromotionMansong($mansong_id);
		return AjaxReturn($res);
	}
	
	/**
	 * 关闭满减送活动
	 *
	 * @return unknown[]
	 */
	public function closeMansong()
	{
		$mansong_id = request()->post('mansong_id', '');
		if (!is_numeric($mansong_id)) {
			$this->error("没有获取到满减送信息");
		}
		$mansong = new PromotionService();
		$res = $mansong->closePromotionMansong($mansong_id);
		return AjaxReturn($res);
	}
	
	/**
	 * 满额包邮
	 */
	public function fullShipping()
	{
		$full = new PromotionService();
		if (request()->isAjax()) {
			$is_open = request()->post('is_open', '');
			$full_mail_money = request()->post('full_mail_money', '');
			$no_mail_province_id_array = request()->post('no_mail_province_id_array', '');
			$no_mail_city_id_array = request()->post("no_mail_city_id_array", '');
			$data = array(
				'is_open' => $is_open,
				'full_mail_money' => $full_mail_money,
				'modify_time' => time(),
				'no_mail_province_id_array' => $no_mail_province_id_array,
				'no_mail_city_id_array' => $no_mail_city_id_array
			);
			
			$res = $full->updatePromotionFullMail($data);
			return AjaxReturn($res);
		} else {
			$info = $full->getPromotionFullMail();
			$this->assign("info", $info);
			$existing_address_list['province_id_array'] = explode(',', $info['no_mail_province_id_array']);
			$existing_address_list['city_id_array'] = explode(',', $info['no_mail_city_id_array']);
			$address = new Address();
			// 目前只支持省市，不支持区县，在页面上不会体现 2017年9月14日 19:18:08
			$address_list = $address->getAreaTree($existing_address_list);
			$this->assign("address_list", $address_list);
			$no_mail_province_id_array = array();
			if (count($existing_address_list['province_id_array']) > 0) {
				foreach ($existing_address_list['province_id_array'] as $v) {
					if (!empty($v)) {
						$no_mail_province_id_array[] = $address->getProvinceName($v);
					}
				}
			}
			$no_mail_province = "";
			if (count($no_mail_province_id_array) > 0) {
				$no_mail_province = implode(',', $no_mail_province_id_array);
			}
			$this->assign("no_mail_province", $no_mail_province);
			return view($this->style . "Promotion/fullShipping");
		}
	}
	
	/**
	 * 营销活动列表
	 */
	public function promotionGamesList()
	{
		if (request()->isAjax()) {
			$promotionService = new PromotionService();
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$search_text = request()->post("search_text", '');
			$type = request()->post("type", '');
			
			$condition = array();
			if (!empty($search_text)) {
				$condition['name'] = array(
					'like',
					'%' . $search_text . '%'
				);
			}
			$condition['game_type'] = $type;
			
			$promotion_games_list = $promotionService->getPromotionGamesList($page_index, $page_size, $condition);
			return $promotion_games_list;
		}
		$type = request()->get('type', '');
		$this->assign('type', $type);
		
		return view($this->style . "Games/promotionGamesList");
	}
	
	/**
	 * 营销活动类型列表
	 *
	 * @return \think\response\View
	 */
	public function promotionGameTypeList()
	{
		$promotionService = new PromotionService();
		$game_type_list = $promotionService->getPromotionGameTypeList(1, 0, [ 'is_complete' => 1 ], 'is_complete desc');
		$this->assign('game_type_list', $game_type_list['data']);
		return view($this->style . "Games/promotionGameTypeList");
	}
	
	/**
	 * 添加营销活动
	 */
	public function addPromotionGame()
	{
		$this->promotionGameInit();
		$this->assign('game_id', 0);
		$this->assign('type', request()->get('game_type', 0));
		return view($this->style . "Games/addPromotionGame");
	}
	
	/**
	 * 添加修改互动游戏
	 */
	public function addUpdatePromotionGame()
	{
		if (request()->isAjax()) {
			
			$promotionService = new PromotionService();
			$game_id = request()->post('game_id', '');
			$name = request()->post('game_name', '');
			$type = request()->post('game_type', '');
			$member_level = request()->post('member_level', '');
			$points = request()->post('points', '');
			$start_time = request()->post('start_time', '');
			$end_time = request()->post('end_time', '');
			$remark = request()->post('remark', '');
			$winning_rate = request()->post('winning_rate', '');
			$no_winning_des = request()->post('no_winning_des', '');
			$rule_json = request()->post('rule_array', '');
			$activity_images = request()->post('activity_images', ''); // 活动图片
			$winning_list_display = request()->post('winning_list_display', 1); //是否显示中奖名单
			$join_type = request()->post('join_type', 0); //参与次数限制类型 0全过程 1每天
			$join_frequency = request()->post('join_frequency', 1);//参与次数
			$winning_type = request()->post('winning_type', 0);//中奖次数限制类型 0全过程 1每天
			$winning_max = request()->post('winning_max', 1);//中奖次数
			$promotion_status = request()->post('promotion_status', '');
			if ($promotion_status != '' && $promotion_status != 0) {
				return [
					'code' => -1,
					'message' => '操作错误'
				];
			}
			
			
			$data = array(
				'shop_id' => 0,
				'name' => $name,
				'game_type' => $type,
				'member_level' => $member_level,
				'points' => $points,
				'start_time' => getTimeTurnTimeStamp($start_time),
				'end_time' => getTimeTurnTimeStamp($end_time),
				'remark' => $remark,
				'winning_rate' => $winning_rate,
				'no_winning_des' => $no_winning_des,
				'activity_images' => $activity_images,
				"winning_list_display" => $winning_list_display,
				"join_type" => $join_type,
				"join_frequency" => $join_frequency,
				"winning_type" => $winning_type,
				"winning_max" => $winning_max,
				"game_id" => $game_id,
				"rule_json" => $rule_json
			);
			$res = $promotionService->addUpdatePromotionGame($data);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 修改互动游戏
	 */
	public function updatePromotionGame()
	{
		$this->promotionGameInit();
		
		$game_id = request()->get('game_id', '');
		$this->assign('game_id', $game_id);
		$game_type = request()->get('game_type', '');
		$this->assign('type', $game_type);
		$promotionService = new PromotionService();
		$game_info = $promotionService->getPromotionGameDetail($game_id);
		$this->assign('game_info', $game_info);
		return view($this->style . "Games/updatePromotionGame");
	}
	
	/**
	 * 修改添加互动游戏页面加载项
	 */
	public function promotionGameInit()
	{
		$promotionService = new PromotionService();
		// 活动类型
		$game_type = request()->get('game_type', '');
		$game_type_info = $promotionService->getPromotionGameTypeInfo($game_type);
		$this->assign('game_type', $game_type);
		$this->assign('game_type_info', $game_type_info);
		
		// 会员等级
		$member_service = new Member();
		$member_level_list = $member_service->getMemberLevelList();
		$this->assign('level_list', $member_level_list['data']);
		
		// 优惠劵列表
		/*         $coupon_condition = array(
					'start_time' => array(
						'lt',
						time()
					),
					'end_time' => array(
						'gt',
						time()
					)
				); */
		$coupon_condition = '(count > 0 AND start_time < ' . time() . ' AND end_time > ' . time() . ' AND term_of_validity_type = 0)';
		$coupon_condition .= 'OR (term_of_validity_type = 1)';
		
		$coupon_type_list = $promotionService->getCouponTypeInfoList(1, 0, $coupon_condition);
		$this->assign('coupon_type_list', $coupon_type_list['data']);
		
		// 赠品列表
		$gift_condition = array(
			'start_time' => array(
				'lt',
				time()
			),
			'end_time' => array(
				'gt',
				time()
			)
		);
		$gift_list = $promotionService->getPromotionGiftList(1, 0, $gift_condition);
		$this->assign('gift_list', $gift_list['data']);
	}
	
	/**
	 * 删除互动游戏
	 */
	public function delPromotionGame()
	{
		if (request()->isAjax()) {
			$promotionService = new PromotionService();
			$game_id = request()->post('game_id', '');
			$res = $promotionService->deletePromotionGame($game_id);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 关闭互动游戏
	 */
	public function closePromotionGame()
	{
		if (request()->isAjax()) {
			$promotionService = new PromotionService();
			$game_id = request()->post('game_id', '');
			$res = $promotionService->closePromotionGame($game_id);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 营销游戏奖项列表
	 */
	public function promotionGamesAwardList()
	{
		$game_id = request()->get("game_id", "");
		if (empty($game_id)) {
			$this->error("缺少参数game_id");
		}
		$promotionService = new PromotionService();
		$game_detail = $promotionService->getPromotionGameDetail($game_id);
		$this->assign("game_detail", $game_detail);
		return view($this->style . "Games/promotionGamesAwardList");
	}
	
	/**
	 * 获奖记录
	 */
	public function promotionGamesAccessRecords()
	{
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$search_text = request()->post("search_text", "");
			$is_winning = request()->post("is_winning", "");
			$game_id = request()->post("game_id", "");
			$condition = array();
			if (!empty($search_text)) {
				$condition['np_pgwr.nick_name'] = [
					'like',
					"%" . $search_text . "%"
				];
			}
			if ($is_winning !== "") {
				
				$condition['np_pgwr.is_winning'] = $is_winning;
			}
			if ($game_id !== "") {
				
				$condition['np_pgwr.game_id'] = $game_id;
			}
			$promotionService = new PromotionService();
			$res = $promotionService->getUserPromotionGamesWinningRecords($page_index, $page_size, $condition);
			return $res;
		} else {
			
			$game_id = request()->get("game_id", "");
			if (empty($game_id)) {
				$this->error("缺少参数game_id");
			}
			$this->assign("game_id", $game_id);
		}
		
		return view($this->style . "Games/promotionGamesAccessRecords");
	}
	
	/**
	 * 赠送优惠券类型列表
	 */
	public function sendCouponTypeList()
	{
		if (request()->isAjax()) {
			$coupon = new PromotionService();

	 		$condition = '(count > 0  AND end_time > '.time().' AND is_show = 1 AND term_of_validity_type = 0 AND shop_id = '.$this->instance_id.')';
	        $condition .= 'OR (term_of_validity_type = 1 AND is_show = 1)';			
			$list = $coupon->getCouponTypeList(1, 0, $condition, 'create_time desc');
			return $list;
		} else {
			return view($this->style . "Promotion/couponTypeList");
		}
	}
	
	/**
     * 商品选择弹框控制器
     */
    public function goodsSelectList(){
        if (request()->isAjax()) {
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post("page_size", PAGESIZE);
            $value = request()->post('value', '');
            $is_limit_sku = request()->post('is_limit_sku', '');
            $is_limit_skock = request()->post('is_limit_skock', '');
            $is_limit_state = request()->post('is_limit_state', '');
            $is_limit_goods_type = request()->post('is_limit_goods_type', '');
            
	        $goods = new GoodsService();
	  
	        $condition = [];
	        
            if (!empty($value)) {
                $data = json_decode($value, true);
                switch ($data['from_type']) {
                    case 'label': // 按标签
                        if (!empty($data['label'])) {
                            $label_str = "FIND_IN_SET(" . $data['label'] . ", group_id_array)";
                            $condition = [
                                '' => [ 'exp', $label_str ]
                            ];
                        }
                        break;
                    case 'category': // 按分类
                        if (!empty($data['category'])) {
                            $category_arr = explode(',', $data['category']);
                            $condition['category_id_'.count($category_arr)] = $category_arr[ (count($category_arr) - 1) ];
                        }
                        break;
                    case 'brand': // 按品牌
                        if (!empty($data['brand'])) {
                            $condition['brand_id'] = $data['brand'];
                        }
                        break;
                    case 'recommend': // 按推荐
                        if (!empty($data['recommend'])) {
                            if ($data['recommend'] == 1) {
                                $condition['is_hot'] = 1;
                            } elseif ($data['recommend'] == 2) {
                                $condition['is_recommend'] = 1;
                            } elseif ($data['recommend'] == 3) {
                                $condition['is_new'] = 1;
                            }
                        }
                        break;
                    case 'goods_type': // 按类型
                        if (!empty($data['goods_type'])) {
                            $condition['goods_type'] = $data['goods_type'];
                        }
                        break;
                    case 'goods_ids':
                        if (!empty($data['goods_ids'])) {
                            $condition['goods_in'] = ['in', $data['goods_ids']];
                        }
                        break;
                    case 'search': // 按搜索内容
                        if (!empty($data['search'])) {
                            $condition['goods_name'] = [ 'like', '%' . $data['search'] . '%' ];
                        }
                        break;
                }
            }
	        if(!empty($is_limit_skock)){
	            $condition['stock'] = ['>', 0];
	        }
	        if(!empty($is_limit_state)){
	            $condition['state'] = 1;
	        }
            if(!empty($is_limit_sku)){
	            $condition["goods_spec_format"] = '[]';
	        }
	        if(!empty($is_limit_goods_type)){
	            $condition["is_virtual"] = 0;
	        }
	        
	        $result = $goods->getSearchGoodsList($page_index, $page_size, $condition);
            return $result;
        } else {
            // 商品分组
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
            // 查找一级商品分类
            $goodsCategory = new GoodsCategory();
            $oneGoodsCategory = $goodsCategory->getGoodsCategoryListByParentId(0);
            $this->assign("oneGoodsCategory", $oneGoodsCategory);
            //商品类型
            $goods_type_list = hook('getGoodsConfig', [ 'type' => 'all' ]);
            $this->assign('goods_type_list', $goods_type_list);
            //品牌列表
            $goodsbrand = new GoodsBrand();
            $brand_list = $goodsbrand->getGoodsBrandList(1, 0);
            $this->assign("brand_list", $brand_list['data']);   
             
            $goods_id_array = request()->get("goods_id_array", "");
            $this->assign("goods_id_array", $goods_id_array);
            
            $limit = request()->get("limit", "");
            $this->assign('limit', json_decode($limit, true));
            
            return view($this->style . "Promotion/goodsSelectList");
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
	 * 获取专题活动；列表
	 */
	public function TopicList()
	{
		if (request()->isAjax()) {
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$search_text = request()->post('search_text', '');
			$status = request()->post('status', '');
			$discount = new PromotionService();
			$condition = array(
				'shop_id' => $this->instance_id,
				'topic_name' => array(
					'like',
					'%' . $search_text . '%'
				)
			);
			if ($status !== '-1') {
				$condition['status'] = $status;
				$list = $discount->getPromotionTopicList($page_index, $page_size, $condition);
			} else {
				$list = $discount->getPromotionTopicList($page_index, $page_size, $condition);
			}
			
			return $list;
		}
		
		$status = request()->get('status', -1);
		$this->assign("status", $status);
		$child_menu_list = array(
			array(
				'url' => "promotion/TopicList",
				'menu_name' => "全部",
				"active" => $status == '-1' ? 1 : 0
			),
			array(
				'url' => "promotion/TopicList?status=1",
				'menu_name' => "进行中",
				"active" => $status == 1 ? 1 : 0
			),
			array(
				'url' => "promotion/TopicList?status=3",
				'menu_name' => "已关闭",
				"active" => $status == 3 ? 1 : 0
			),
			array(
				'url' => "promotion/TopicList?status=4",
				'menu_name' => "已结束",
				"active" => $status == 4 ? 1 : 0
			),
		);
		$this->assign('child_menu_list', $child_menu_list);
		
		return view($this->style . "Promotion/getTopicList");
	}
	
	/**
	 * 添加专题活动
	 */
	public function addTopic()
	{
		if (request()->isAjax()) {
			$discount = new PromotionService();
			$topic_name = request()->post('topic_name', '');
			$keyword = request()->post('keyword', '');
			$desc = request()->post('desc', '');
			$picture_img = request()->post('picture_img', '');
			$scroll_img = request()->post('scroll_img', '');
			$background_img = request()->post('background_img', '');
			$background_color = request()->post('background_color', '');
			$start_time = request()->post('start_time', '');
			$end_time = request()->post('end_time', '');
			$pc_topic_template = request()->post('pc_topic_template', '');
			$wap_topic_template = request()->post('wap_topic_template', '');
			$content = request()->post('content', '');
			$range_type = request()->post('range_type', '');
			$is_head = request()->post('is_head', 1);
			$is_foot = request()->post('is_foot', 1);
			$goods_id_array = request()->post('goods_id_array', '');
			
			$data = array(
				'topic_name' => $topic_name,
				'keyword' => $keyword,
				'desc' => $desc,
				'picture_img' => $picture_img,
				'scroll_img' => $scroll_img,
				'background_img' => $background_img,
				'background_color' => $background_color,
				'start_time' => getTimeTurnTimeStamp($start_time),
				'end_time' => getTimeTurnTimeStamp($end_time),
				'shop_id' => 0,
				'introduce' => $content,
				'status' => 0,
				'is_head' => $is_head,
				'is_foot' => $is_foot,
				'pc_topic_template' => $pc_topic_template,
				'wap_topic_template' => $wap_topic_template,
				'create_time' => time(),
				'goods_id_array' => $goods_id_array,
				'range_type' => $range_type
			);
			$retval = $discount->addPromotionTopic($data);
			return AjaxReturn($retval);
		}
		$template_url = array();
		$config = new ConfigService();
		$pc_template = $config->getUsePCTemplate($this->instance_id);
		$wap_template = $config->getUseWapTemplate($this->instance_id);
		$template_url["pc_template_url"] = "template/web/" . $pc_template['value'] . '/Goods/';
		$template_url["wap_template_url"] = "template/web/" . $wap_template['value'] . '/Goods/';
		$template_url['pc_file'] = 'topic_detail';
		$template_url['wap_file'] = 'topic_detail';
		$this->assign("template_url", $template_url);
		
		return view($this->style . "Promotion/addTopic");
	}
	
	/**
	 * 修改专题活动
	 */
	public function updateTopic()
	{
		if (request()->isAjax()) {
			$discount = new PromotionService();
			$topic_id = request()->post('topic_id', '');
			$topic_name = request()->post('topic_name', '');
			$keyword = request()->post('keyword', '');
			$desc = request()->post('desc', '');
			$picture_img = request()->post('picture_img', '');
			$scroll_img = request()->post('scroll_img', '');
			$background_img = request()->post('background_img', '');
			$background_color = request()->post('background_color', '');
			$start_time = request()->post('start_time', '');
			$end_time = request()->post('end_time', '');
			$pc_topic_template = request()->post('pc_topic_template', '');
			$wap_topic_template = request()->post('wap_topic_template', '');
			$content = request()->post('content', '');
			$range_type = request()->post('range_type', '');
			$is_head = request()->post('is_head', 1);
			$is_foot = request()->post('is_foot', 1);
			$goods_id_array = request()->post('goods_id_array', '');
			
			$data = array(
				'topic_name' => $topic_name,
				'keyword' => $keyword,
				'desc' => $desc,
				'picture_img' => $picture_img,
				'scroll_img' => $scroll_img,
				'background_img' => $background_img,
				'background_color' => $background_color,
				'start_time' => getTimeTurnTimeStamp($start_time),
				'end_time' => getTimeTurnTimeStamp($end_time),
				'shop_id' => 0,
				'introduce' => $content,
				'status' => 0,
				'is_head' => $is_head,
				'is_foot' => $is_foot,
				'pc_topic_template' => $pc_topic_template,
				'wap_topic_template' => $wap_topic_template,
				'modify_time' => time(),
				'goods_id_array' => $goods_id_array,
				'range_type' => $range_type,
				'topic_id' => $topic_id
			);
			
			$retval = $discount->updatePromotionTopic($data);
			return AjaxReturn($retval);
		}
		$info = $this->getTopicDetail();
		if (!empty($info['goods_list'])) {
			foreach ($info['goods_list'] as $k => $v) {
				$goods_id_array[] = $v['goods_id'];
			}
		}
		//选择商品的id
		$info['goods_id_array'] = join(',', $goods_id_array);
		//包含折扣的选择商品数据
		$this->assign("info", $info);
		$template_url = array();
		$config = new ConfigService();
		$pc_template = $config->getUsePCTemplate($this->instance_id);
		$wap_template = $config->getUseWapTemplate($this->instance_id);
		$template_url["pc_template_url"] = "template/shop/" . $pc_template['value'] . '/Goods/';
		$template_url["wap_template_url"] = "template/wap/" . $wap_template['value'] . '/Goods/';
		$template_url['pc_file'] = 'promotionTopicGoods';
		$template_url['wap_file'] = 'promotionTopicGoods';
		$this->assign("template_url", $template_url);
		return view($this->style . "Promotion/updateTopic");
	}
	
	/**
	 * 获取专题活动详情
	 */
	public function getTopicDetail()
	{
		$topic_id = request()->get('topic_id', '');
		if (!is_numeric($topic_id)) {
			$this->error("没有获取到专题信息");
		}
		$topic = new PromotionService();
		$detail = $topic->getPromotionTopicDetail($topic_id);
		return $detail;
	}
	
	/**
	 * 关闭正在进行的专题活动
	 */
	public function closeTopic()
	{
		$topic_id = request()->post('topic_id', '');
		if (!is_numeric($topic_id)) {
			$this->error("没有获取到专题信息");
		}
		$discount = new PromotionService();
		$res = $discount->closePromotionTopic($topic_id);
		return AjaxReturn($res);
	}
	
	/**
	 * 删除专题活动
	 */
	public function delTopic()
	{
		$topic_id = request()->post('topic_id', '');
		if (empty($topic_id)) {
			$this->error("没有获取到专题信息");
		}
		$topic = new PromotionService();
		$res = $topic->deletePromotionTopic($topic_id);
		return AjaxReturn($res);
	}
	
	/**
	 * 海报设置
	 */
	public function posterSetting()
	{
		$config = new ConfigService();
		if (request()->isAjax()) {
			$value = request()->post('value', '');
			$res = $config->setPosterConfig($value);
			return AjaxReturn($res);
		}
		$info = $config->getPosterConfig();
		$this->assign('info', $info);
		return view($this->style . "Promotion/posterSetting");
	}
	
	/**
	 * 渠道营销
	 */
	public function channelPromotion(){
	    return view($this->style . "Promotion/channelPromotion");
	}
	
	/**
	 * 应用推荐
	 */
	public function appRecommend(){
	    $res = hook("getAppRecommendConfig", ['type' => 'all']);
	    $res = array_filter($res);
	    sort($res);
	    $this->assign('app_recommend', $res);
	    return view($this->style . "Promotion/appRecommend");
	}
}