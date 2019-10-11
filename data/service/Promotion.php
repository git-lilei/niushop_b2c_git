<?php
/**
 * Promotion.php
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
 * 营销
 */
use data\model\AlbumPictureModel;
use data\model\NsCouponGoodsModel;
use data\model\NsCouponModel;
use data\model\NsCouponTypeModel;
use data\model\NsGoodsModel;
use data\model\NsGoodsPromotionModel;
use data\model\NsGoodsSkuModel;
use data\model\NsMemberAccountModel;
use data\model\NsMemberLevelModel;
use data\model\NsOrderGoodsModel;
use data\model\NsPointConfigModel;
use data\model\NsPromotionDiscountGoodsModel;
use data\model\NsPromotionDiscountGoodsViewModel;
use data\model\NsPromotionDiscountModel;
use data\model\NsPromotionFullMailModel;
use data\model\NsPromotionGameRuleModel;
use data\model\NsPromotionGamesModel;
use data\model\NsPromotionGamesWinningRecordsModel;
use data\model\NsPromotionGameTypeModel;
use data\model\NsPromotionGiftGoodsModel;
use data\model\NSPromotionGiftGrantRecordsModel;
use data\model\NsPromotionGiftModel;
use data\model\NsPromotionGiftViewModel;
use data\model\NsPromotionMansongGoodsModel;
use data\model\NsPromotionMansongModel;
use data\model\NsPromotionMansongRuleModel;
use data\model\NsPromotionTopicGoodsModel;
use data\model\NsPromotionTopicModel;
use data\model\UserModel;
use data\service\Member\MemberAccount;
use data\service\Member\MemberCoupon;
use data\service\promotion\GoodsDiscount;
use data\service\promotion\GoodsMansong;
use think\Cache;
use think\Log;

class Promotion extends BaseService
{
	
	/**************************************************************商品活动整体*********************************************/
	
	/**
	 * 获取商品活动
	 */
	public function getGoodsPromotionQuery($condition, $field = "*", $order = "end_time desc")
	{
		$goods_promotion_model = new NsGoodsPromotionModel();
		$res = $goods_promotion_model->getQuery($condition, $field, $order);
		return $res;
	}
	
	/**********************************************************优惠券 begin******************************************************/
	
	/**
	 * 添加优惠券类型
	 */
	public function addCouponType($param)
	{
		Cache::clear('coupon');
		$coupon_type = new NsCouponTypeModel();
		$coupon_type->startTrans();
		try {
			// 添加优惠券类型表
			$data = array(
				'shop_id' => 0,
				'coupon_name' => $param["coupon_name"],
				'money' => $param["money"],
				'count' => $param["count"],
				'max_fetch' => $param["max_fetch"],
				'at_least' => $param["at_least"],
				'need_user_level' => $param["need_user_level"],
				'range_type' => $param["range_type"],
				'start_time' => $param["start_time"],
				'end_time' => $param["end_time"],
				'is_show' => $param["is_show"],
				'create_time' => $param["create_time"],
				'term_of_validity_type' => $param["term_of_validity_type"],
				'fixed_term' => $param["fixed_term"]
			);
			if ($param["term_of_validity_type"] == 1) {
				$param['start_time'] = 0;
				$param['end_time'] = 0;
			}
			$coupon_type->save($data);
			$coupon_type_id = $coupon_type->coupon_type_id;
			$this->addUserLog($this->uid, 1, '营销', '添加优惠券类型', '添加优惠券类型:' . $param["coupon_name"]);
			// 添加类型商品表goods_list
			if ($param["range_type"] == 0 && !empty($param["goods_list"])) {
				$goods_list_array = explode(',', $param["goods_list"]);
				foreach ($goods_list_array as $k => $v) {
					$data_coupon_goods = array(
						'coupon_type_id' => $coupon_type_id,
						'goods_id' => $v
					);
					$coupon_goods = new NsCouponGoodsModel();
					$coupon_goods->save($data_coupon_goods);
				}
			}
			// 添加优惠券表
			if ($param["count"] > 0) {
				for ($i = 0; $i < $param["count"]; $i++) {
					$coupon_code = Cache::get('coupon_code' . time());
					$coupon_code = !empty($coupon_code) ? $coupon_code + 1 : 1;
					Cache::set('coupon_code' . time(), $coupon_code, 1);
					$data_coupon = array(
						'coupon_type_id' => $coupon_type_id,
						'shop_id' => 0,
						'coupon_code' => time() . sprintf("%03d", $coupon_code),
						'uid' => 0,
						'create_order_id' => 0,
						'money' => $param["money"],
						'state' => 0,
						"start_time" => $param["start_time"],
						"end_time" => $param["end_time"]
					);
					if ($param["term_of_validity_type"] == 1) {
						$data_coupon['start_time'] = 0;
						$data_coupon['end_time'] = 0;
					}
					$coupon = new NsCouponModel();
					$coupon->save($data_coupon);
				}
			}
			$coupon_type->commit();
			return 1;
		} catch (\Exception $e) {
			$coupon_type->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 修改优惠券类型
	 */
	public function updateCouponType($param)
	{
		Cache::clear('coupon');
		$coupon_type = new NsCouponTypeModel();
		$coupon_type->startTrans();
		try {
			// 更新优惠券类型表
			
			$data = array(
				'shop_id' => 0,
				'coupon_name' => $param["coupon_name"],
				'money' => $param["money"],
				'count' => $param["count"] + $param["repair_count"],
				'max_fetch' => $param["max_fetch"],
				'at_least' => $param["at_least"],
				'need_user_level' => $param["need_user_level"],
				'range_type' => $param["range_type"],
				'start_time' => $param["start_time"],
				'end_time' => $param["end_time"],
				'is_show' => $param["is_show"],
				'term_of_validity_type' => $param["term_of_validity_type"],
				'fixed_term' => $param["fixed_term"]
			);
			if ($param["term_of_validity_type"] == 1) {
				$data['start_time'] = 0;
				$data['end_time'] = 0;
			}
			$coupon_type->save($data, [
				'coupon_type_id' => $param["coupon_type_id"]
			]);
			$this->addUserLog($this->uid, 1, '营销', '修改优惠券类型', '修改优惠券类型:' . $param["coupon_name"]);
			// 更新优惠券商品表
			$coupon_goods = new NsCouponGoodsModel();
			$coupon_goods->destroy([
				'coupon_type_id' => $param["coupon_type_id"]
			]);
			if ($param["range_type"] == 0 && !empty($param["goods_list"])) {
				$goods_list_array = explode(',', $param["goods_list"]);
				foreach ($goods_list_array as $k => $v) {
					$data_coupon_goods = array(
						'coupon_type_id' => $param["coupon_type_id"],
						'goods_id' => $v
					);
					$coupon_goods = new NsCouponGoodsModel();
					$coupon_goods->save($data_coupon_goods);
				}
			}
			// 添加优惠券表
			if ($param["repair_count"] > 0) {
				for ($i = 0; $i < $param["repair_count"]; $i++) {
					$data_coupon = array(
						'coupon_type_id' => $param["coupon_type_id"],
						'shop_id' => 0,
						'coupon_code' => time() . rand(111, 999),
						'uid' => 0,
						'create_order_id' => 0,
						'money' => $param["money"],
						'state' => 0,
						'start_time' => $param["start_time"],
						'end_time' => $param["end_time"],
					);
					if ($param["term_of_validity_type"] == 1) {
						$data_coupon['start_time'] = 0;
						$data_coupon['end_time'] = 0;
					}
					$coupon = new NsCouponModel();
					$coupon->save($data_coupon);
				}
			}
			// 修改优惠券时，更新优惠券的使用状态，金额
			$coupon = new NsCouponModel();
			$coupon_condition['state'] = array( 'in', [ 0, 3 ] );
			
			// 未领用或者已过期的优惠券
			$coupon_condition['coupon_type_id'] = $param["coupon_type_id"];
			
			$coupon_update_data = array(
				'start_time' => $param["start_time"],
				'end_time' => $param["end_time"],
				'state' => 0,
				'money' => $param["money"]
			);
			if ($param["term_of_validity_type"] == 1) {
				$coupon_update_data['start_time'] = 0;
				$coupon_update_data['end_time'] = 0;
			}
			$coupon->save($coupon_update_data, $coupon_condition);
			$coupon_type->commit();
			return 1;
		} catch (\Exception $e) {
			$coupon_type->rollback();
			return 0;
		}
	}
	
	/**
	 * 删除优惠券
	 */
	public function deleteCoupontype($coupon_type_id)
	{
		Cache::clear('coupon');
		$coupon = new NsCouponModel();
		$coupon_type = new NsCouponTypeModel();
		$coupon_type->startTrans();
		try {
			$condition['coupon_type_id'] = $coupon_type_id;
			$condition['state'] = 1;
			$coupon_count = $coupon->getcount($condition);
			if ($coupon_count > 0) {
				$coupon_type->rollback();
				return -1;
			}
			$coupon_type->destroy($coupon_type_id);
			$coupon_type->commit();
			return 1;
		} catch (\Exception $e) {
			$coupon_type->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 获取优惠券类型详情
	 * @param int $coupon_type_id 类型主键
	 */
	public function getCouponTypeDetail($coupon_type_id)
	{
		$cache = Cache::tag('coupon')->get('getCouponTypeDetail' . $coupon_type_id);
		if (!empty($cache)) return $cache;
		
		$coupon_type = new NsCouponTypeModel();
		$data = $coupon_type->get($coupon_type_id);
		$coupon_goods = new NsCouponGoodsModel();
		$goods_list = $coupon_goods->getCouponTypeGoodsList($coupon_type_id);
		foreach ($goods_list as $k => $v) {
			$picture = new AlbumPictureModel();
			$pic_info = array();
			$pic_info['pic_cover'] = '';
			if (!empty($v['picture'])) {
				$pic_info = $picture->get($v['picture']);
			}
			$goods_list[ $k ]['picture_info'] = $pic_info;
		}
		$data['goods_list'] = $goods_list;
		Cache::tag('coupon')->set('getCouponTypeDetail' . $coupon_type_id, $data);
		return $data;
	}
	
	/**
	 * 获取活动名称
	 */
	public function getCouponTypeByName($coupon_type_id)
	{
		$coupon_type = new NsCouponTypeModel();
		$coupon_type_info = $coupon_type->getInfo([ 'coupon_type_id' => $coupon_type_id ], 'coupon_name');
		return $coupon_type_info['coupon_name'];
	}
	
	/**
	 * 获取优惠券类型列表
	 */
	public function getCouponTypeList($page_index = 1, $page_size = 0, $condition = '', $order = 'create_time asc')
	{
		$coupon_type = new NsCouponTypeModel();
		$coupon_type_list = $coupon_type->pageQuery($page_index, $page_size, $condition, $order, 'coupon_type_id, coupon_name, money, count, max_fetch, at_least, need_user_level, range_type, start_time, end_time, create_time, update_time,is_show, term_of_validity_type, fixed_term, get_num, is_end');
		return $coupon_type_list;
	}
	
	/**
	 * 获取优惠券类型分页列表
	 */
	public function getCouponTypeInfoList($page_index = 1, $page_size = 0, $condition = '', $order = '', $uid = 0)
	{
		$coupon_type = new NsCouponTypeModel();
		$coupon = new NsCouponModel();
		$coupon_type_list = $coupon_type->pageQuery($page_index, $page_size, $condition, $order, 'coupon_type_id, coupon_name, money, count, max_fetch, at_least, need_user_level, range_type, start_time, end_time, create_time, update_time,is_show,term_of_validity_type,fixed_term,get_num,is_end');
		foreach ($coupon_type_list['data'] as $k => $v) {
			// 剩余数量
			$surplus_num = $coupon->getCount([
				"coupon_type_id" => $v["coupon_type_id"],
				"state" => 0
			]);
			$coupon_type_list["data"][ $k ]["surplus_num"] = $surplus_num;
			// 当前用户已领取数量
			$received_num = 0;
			if (!empty($uid)) {
				$received_num = $coupon->getCount([
					"coupon_type_id" => $v["coupon_type_id"],
					"uid" => $uid
				]);
			}
			$coupon_type_list["data"][ $k ]["received_num"] = $received_num;
			// 计算优惠券未领取百分比
			$surplus_percentage = 0;
			if ($v["count"] > 0) {
				$surplus_percentage = floor($surplus_num / $v["count"] * 100);
			}
			$coupon_type_list["data"][ $k ]["surplus_percentage"] = $surplus_percentage;
		}
		return $coupon_type_list;
	}
	
	/**
	 * 优惠券获取记录
	 */
	public function getCouponGrantLogList($page_index = 1, $page_size = 0, $condition = '', $order = 'fetch_time asc')
	{
		$coupon = new NsCouponModel();
		$coupon_list = $coupon->pageQuery($page_index, $page_size, $condition, $order, '');
		$user = new User();
		foreach ($coupon_list['data'] as $k => $v) {
			$coupon_list['data'][ $k ]['coupon_name'] = $this->getCouponTypeByName($v['coupon_type_id']);
			//查询用户名称
			$coupon_list['data'][ $k ]['user_info'] = $user->getUserInfoByUid($v['uid']);
		}
		return $coupon_list;
	}
	
	/**
	 * 优惠券自动过期
	 */
	public function autoCouponClose()
	{
		$ns_coupon_model = new NsCouponModel();
		$ns_coupon_model->startTrans();
		try {
			$condition['end_time'] = array( [ 'LT', time() ], [ 'NEQ', 0 ] );
			$condition['state'] = array( 'NEQ', 2 );//排除已使用的优惠券
			$count = $ns_coupon_model->getCount($condition);
			if ($count) {
				$ns_coupon_model->save([ 'state' => 3 ], $condition);
			}
			$ns_coupon_model->commit();
			Cache::clear('coupon');
			return 1;
		} catch (\Exception $e) {
			$ns_coupon_model->rollback();
			return $e->getMessage();
		}
	}
	
	/**********************************************************优惠券结束***************************************************/
	/**********************************************************积分抵现设置*************************************************/
	
	/**
	 * 积分抵现设置
	 */
	public function setPointConfig($convert_rate, $is_open, $desc)
	{
		Cache::clear('point_config');
		$point_model = new NsPointConfigModel();
		$data = array(
			'convert_rate' => $convert_rate,
			'is_open' => $is_open,
			'desc' => $desc,
			'modify_time' => time()
		);
		$this->addUserLog($this->uid, 1, '营销', '积分设置', '积分设置：' . '转化比率' . $convert_rate . ',' . '启用设置：' . $is_open);
		$retval = $point_model->save($data, [
			'shop_id' => 0
		]);
		return $retval;
	}
	
	/**
	 * 获取积分抵现配置
	 * @return \think\static
	 */
	public function getPointConfig()
	{
		$cache = Cache::tag('point_config')->get('getPointConfig');
		if (!empty($cache)) return $cache;
		
		$point_model = new NsPointConfigModel();
		$info = $point_model->getInfo([ 'shop_id' => 0 ], "*");
		if (empty($info)) {
			$data = array(
				'shop_id' => 0,
				'is_open' => 0,
				'desc' => '',
				'create_time' => time()
			);
			$point_model = new NsPointConfigModel();
			$point_model->save($data);
			$info = $point_model->get([
				'shop_id' => 0
			]);
		}
		
		Cache::tag('point_config')->set('getPointConfig', $info);
		return $info;
	}
	/**********************************************************积分抵现设置结束**********************************************/
	/**********************************************************赠品管理****************************************************/
	
	/**
	 * 添加赠品活动
	 */
	public function addPromotionGift($param)
	{
		$promotion_gift = new NsPromotionGiftModel();
		$promotion_gift->startTrans();
		try {
			if (empty($param["gift_name"])) {
				return GIFT_NOT_NULL;
			}
			
			$data_gift = array(
				'gift_name' => $param["gift_name"],
				'shop_id' => 0,
				'start_time' => $param["start_time"],
				'end_time' => $param["end_time"],
				'days' => $param["days"],
				'max_num' => $param["max_num"],
				'create_time' => $param["create_time"]
			);
			$promotion_gift->save($data_gift);
			
			$gift_id = $promotion_gift->gift_id;
			$this->addUserLog($this->uid, 1, '营销', '赠品管理', '添加赠品：' . $param["gift_name"]);
			// 当前功能只能选择一种商品
			$promotion_gift_goods = new NsPromotionGiftGoodsModel();
			$promotion_gift_view = new NsPromotionGiftViewModel();
			if (!empty($goods_id)) {
				$count = $promotion_gift_view->getViewCount([
					"npgg.goods_id" => $param["goods_id"],
					"npg.end_time" => array( ">", time() )
				]);
				if ($count > 0) {
					return GOODS_HAVE_BEEN_GIFT;
				}
			}
			
			// 查询商品名称图片
			$goods = new NsGoodsModel();
			$goods_info = $goods->getInfo([
				'goods_id' => $param["goods_id"]
			], 'goods_name,picture');
			$data_goods = array(
				'gift_id' => $gift_id,
				'goods_id' => $param["goods_id"],
				'goods_name' => $goods_info['goods_name'],
				'goods_picture' => $goods_info['picture']
			);
			
			$promotion_gift_goods->save($data_goods);
			$promotion_gift->commit();
			
			return $gift_id;
		} catch (\Exception $e) {
			$promotion_gift->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 修改赠品活动
	 */
	public function updatePromotionGift($param)
	{
		$promotion_gift = new NsPromotionGiftModel();
		$promotion_gift_goods = new NsPromotionGiftGoodsModel();
		$promotion_gift_view = new NsPromotionGiftViewModel();
		
		$promotion_gift->startTrans();
		try {
			if (empty($param["gift_name"])) {
				return GIFT_NOT_NULL;
			}
			$data_gift = array(
				'gift_name' => $param["gift_name"],
				'shop_id' => $param["shop_id"],
				'start_time' => $param["start_time"],
				'end_time' => $param["end_time"],
				'days' => $param["days"],
				'max_num' => $param["max_num"],
				'modify_time' => time()
			);
			$promotion_gift->save($data_gift, [
				'gift_id' => $param["gift_id"]
			]);
			$this->addUserLog($this->uid, 1, '营销', '赠品管理', '修改赠品：' . $param["gift_name"]);
			// 当前功能只能选择一种商品
			if (!empty($goods_id)) {
				$count = $promotion_gift_view->getViewCount([
					"npgg.goods_id" => $param["goods_id"],
					"npg.end_time" => array( ">", time() ),
					"npgg.gift_id" => array( "<>", $param["gift_id"] )
				]);
				
				if ($count > 0) {
					return GOODS_HAVE_BEEN_GIFT;
				}
			}
			
			$promotion_gift_goods->destroy([
				'gift_id' => $param["gift_id"]
			]);
			// 查询商品名称图片
			$goods = new NsGoodsModel();
			$goods_info = $goods->getInfo([
				'goods_id' => $param["goods_id"]
			], 'goods_name,picture');
			$data_goods = array(
				'gift_id' => $param["gift_id"],
				'goods_id' => $param["goods_id"],
				'goods_name' => $goods_info['goods_name'],
				'goods_picture' => $goods_info['picture']
			);
			$promotion_gift_goods = new NsPromotionGiftGoodsModel();
			$promotion_gift_goods->save($data_goods);
			$promotion_gift->commit();
			return 1;
		} catch (\Exception $e) {
			$promotion_gift->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 删除赠品
	 */
	public function deletePromotionGift($gift_id)
	{
		$promotion_gift = new NsPromotionGiftModel();
		$promotion_list = $promotion_gift->getQuery([
			'gift_id' => array( "in", $gift_id )
		], "start_time,end_time,gift_id");
		// 开启事务
		$promotion_gift->startTrans();
		try {
			if (is_array($promotion_list) && count($promotion_list) > 0) {
				foreach ($promotion_list as $info) {
					// 未开始、已结束的赠品不能删除
					if ($info['start_time'] > time() || time() > $info['end_time']) {
						$promotion_gift_goods = new NsPromotionGiftGoodsModel();
						$promotion_gift->destroy([
							'gift_id' => $info["gift_id"]
						]);
						$promotion_gift_goods->destroy([
							'gift_id' => $info["gift_id"]
						]);
					} else {
						$promotion_gift->rollback();
						return GIFT_NOT_DELETE;
					}
				}
				$promotion_gift->commit();
				return 1;
			} else {
				$promotion_gift->rollback();
				return GIFT_NOT_EXIST;
			}
		} catch (\Exception $e) {
			$promotion_gift->rollback();
			return 0;
		}
	}
	
	/**
	 * 获取 赠品详情
	 */
	public function getPromotionGiftDetail($gift_id)
	{
		$promotion_gift = new NsPromotionGiftModel();
		$data = $promotion_gift->get($gift_id);
		$promotion_gift_goods = new NsPromotionGiftGoodsModel();
		
		$gift_goods = $promotion_gift_goods->get([
			'gift_id' => $gift_id
		]);
		
		$picture = new AlbumPictureModel();
		$goods = new NsGoodsModel();
		
		$goods_info = $goods->getInfo([
			'goods_id' => $gift_goods['goods_id']
		], 'goods_id, goods_name, price, stock, picture, is_virtual');
		
		$pic_info = array();
		$pic_info['pic_cover'] = '';
		if (!empty($goods_info['picture'])) {
			$pic_info = $picture->get($goods_info['picture']);
		}
		$goods_info['picture_info'] = $pic_info;
		$goods_info['gift_id'] = $gift_id;
		
		$data['gift_goods'] = $goods_info;
		return $data;
	}
	
	/**
	 * 根据赠品id，返回商品规格
	 */
	public function getGoodsSkuByGiftId($gift_id, $num)
	{
		$res = "";
		$promotion_gift_goods_model = new NsPromotionGiftGoodsModel();
		$gift_info = $promotion_gift_goods_model->getInfo([ 'gift_id' => $gift_id ], "goods_id");
		if (!empty($gift_info['goods_id'])) {
			$goods_sku_model = new NsGoodsSkuModel();
			$sku_id = $goods_sku_model->getInfo([
				'goods_id' => $gift_info['goods_id']
			], 'sku_id');
			if (!empty($sku_id['sku_id'])) {
				$res = $sku_id['sku_id'] . ":" . $num;
			}
		}
		return $res;
	}
	
	/**
	 * 通过赠品id获取商品信息
	 */
	public function getGoodsInfoByGiftId($gift_id)
	{
		$goods_info = array();
		$promotion_gift_goods_model = new NsPromotionGiftGoodsModel();
		$gift_info = $promotion_gift_goods_model->getInfo([ 'gift_id' => $gift_id ], "goods_id");
		if (!empty($gift_info)) {
			$goods_model = new NsGoodsModel();
			$goods_info = $goods_model->getInfo([ "goods_id" => $gift_info["goods_id"] ], "*");
		}
		return $goods_info;
	}
	
	/**
	 * 赠品列表
	 */
	public function getPromotionGiftList($page_index = 1, $page_size = 0, $condition = '', $order = 'create_time desc')
	{
		$promotion_gift = new NsPromotionGiftModel();
		$list = $promotion_gift->pageQuery($page_index, $page_size, $condition, $order, '*');
		if (!empty($list['data'])) {
			foreach ($list['data'] as $k => $v) {
				$start_time = $v['start_time'];
				$end_time = $v['end_time'];
				if ($end_time < time()) {
					$list['data'][ $k ]['type'] = 2;
					$list['data'][ $k ]['type_name'] = '已结束';
				} elseif ($start_time > time()) {
					$list['data'][ $k ]['type'] = 0;
					$list['data'][ $k ]['type_name'] = '未开始';
				} elseif ($start_time <= time() && time() <= $end_time) {
					$list['data'][ $k ]['type'] = 1;
					$list['data'][ $k ]['type_name'] = '进行中';
				}
			}
		}
		return $list;
	}
	
	/**
	 * 赠品列表
	 */
	public function getPromotionGiftQuery($condition = '', $order = 'create_time desc')
	{
		$promotion_gift = new NsPromotionGiftModel();
		$list = $promotion_gift->getQuery($condition, '*', $order);
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$start_time = $v['start_time'];
				$end_time = $v['end_time'];
				if ($end_time < time()) {
					$list[ $k ]['type'] = 2;
					$list[ $k ]['type_name'] = '已结束';
				} elseif ($start_time > time()) {
					$list[ $k ]['type'] = 0;
					$list[ $k ]['type_name'] = '未开始';
				} elseif ($start_time <= time() && time() <= $end_time) {
					$list[ $k ]['type'] = 1;
					$list[ $k ]['type_name'] = '进行中';
				}
			}
		}
		return $list;
	}
	
	/**
	 * 添加赠品发放记录
	 */
	public function addPromotionGiftGrantRecords($data)
	{
		$res = array();
		if (empty($data["nick_name"]) || empty($data["gift_name"]) || empty($data["goods_name"]) || empty($data["type_name"])) {
			$res['code'] = 0;
			$res['message'] = '缺少必要参数';
		} else {
			$model = new NSPromotionGiftGrantRecordsModel();
			$result = $model->save($data);
			$res['code'] = $model->id;
			$res['message'] = '添加赠品发放记录成功';
		}
		return $res;
	}
	
	/**
	 * 赠品发放数量
	 */
	public function getPromotionGiftGrantRecordsCount($condition, $where_sql = "")
	{
		$model = new NSPromotionGiftGrantRecordsModel();
		$viewObj = $model->alias('pgr');
		if (!empty($where_sql)) {
			$count = $model->viewCountNew($viewObj, $condition, $where_sql);
		} else {
			$count = $model->viewCount($viewObj, $condition);
		}
		return $count;
	}
	
	/**
	 * 获取赠品发放记录列表
	 */
	public function getPromotionGiftGrantRecordsList($page_index, $page_size, $condition, $order)
	{
		$condition_sql = "";
		$model = new NSPromotionGiftGrantRecordsModel();
		$viewObj = $model->alias("pgr")
			->join('sys_album_picture ng_sap', 'ng_sap.pic_id = pgr.goods_picture', 'left')
			->field("pgr.id,pgr.shop_id,pgr.uid,pgr.nick_name,pgr.gift_id,pgr.gift_name,pgr.goods_name,pgr.type,pgr.type_name,pgr.relate_id,pgr.remark,pgr.create_time,ng_sap.pic_cover_mid,ng_sap.pic_id,ng_sap.pic_cover_small");
		
		$queryList = $model->viewPageQueryNew($viewObj, $page_index, $page_size, $condition, $condition_sql, $order);
		
		$queryCount = $this->getPromotionGiftGrantRecordsCount($condition);
		
		$list = $model->setReturnList($queryList, $queryCount, $page_size);
		
		return $list;
	}
	
	/**
	 * 会员获取赠品
	 *
	 * @param 用户id $uid
	 * @param 赠品记录id $gift_records_id
	 * @param 收货人的手机号码 $receiver_mobile
	 * @param 收货人所在省 $receiver_province
	 * @param 收货人所在城市 $receiver_city
	 * @param 收货人所在街道 $receiver_district
	 * @param 收货人详细地址 $receiver_address
	 * @param 收货人邮编 $receiver_zip
	 * @param 收货人姓名 $receiver_name
	 * @param 买家附言 $buyer_message
	 * @param 固定电话 $fixed_telephone
	 */
	public function userAchieveGift($param)
	{
		
		$gift_records_model = new NSPromotionGiftGrantRecordsModel();
		$gift_records_info = $gift_records_model->getInfo([
			'id' => $param["gift_records_id"]
		], '*');
		
		if (empty($gift_records_info)) {
			return array(
				"code" => 0,
				"message" => '信息不存在'
			);
		} elseif ($gift_records_info['uid'] != $param["uid"]) {
			return array(
				"code" => 0,
				"message" => '领取人错误'
			);
		} elseif ($gift_records_info['relate_id'] != 0) {
			return array(
				"code" => 0,
				"message" => '赠品已领取, 不能重复领取'
			);
		}
		$gift_records_model->startTrans();
		try {
			$promotion_gift_goods_model = new NsPromotionGiftGoodsModel();
			$promotion_gift_goods_info = $promotion_gift_goods_model->getInfo([
				'gift_id' => $gift_records_info['gift_id']
			], "goods_id");
			if (!empty($promotion_gift_goods_info['goods_id'])) {
				$goods_model = new NsGoodsModel();
				$goods_info = $goods_model->getInfo([ 'goods_id' => $promotion_gift_goods_info['goods_id'] ], 'goods_id,goods_name,is_virtual');
				
				$goods_sku_model = new NsGoodsSkuModel();
				$sku_info = $goods_sku_model->getInfo([
					'goods_id' => $promotion_gift_goods_info['goods_id']
				], 'sku_id, sku_name,stock');
				
				if (empty($goods_info) || empty($sku_info)) {
					return array(
						"code" => 0,
						"message" => '商品信息丢失'
					);
				}
				
			} else {
				return array(
					"code" => 0,
					"message" => '商品信息丢失'
				);
			}
			
			//创建订单
			$order_create = new OrderCreate();
			
			$gift_info = array(
				"gift_info" => array(
					"gift_records_id" => $param["gift_records_id"]
				)
			);
			
			$shipping_info = array(
				"shipping_type" => 1,
				"shipping_company_id" => 0,
				"distribution_time_out" => "",
				"shipping_time" => ""
			);
			
			$order_data = array(
				"order_type" => 1,
				"goods_sku_list" => $sku_info['sku_id'] . ':1',
				"shipping_info" => $shipping_info,
				"promotion_type" => 5,
				"pay_type" => 1,
				"promotion_info" => $gift_info,
				"user_money" => 0,
				"buyer_ip" => "0.0.0.0",
				"platform_money" => 0,
				"buyer_invoice" => "",
				"buyer_message" => $param["buyer_message"],
				"coin" => 0,
				"coupon_id" => 0,
				"point" => 0,
				"is_virtual" => $goods_info['is_virtual'],
			);
			
			if ($goods_info['is_virtual'] == 1) {
				if (empty($param['mobile'])) {
					return array(
						"code" => 0,
						"message" => '请输入手机号'
					);
				}
				$order_data['user_telephone'] = $param['mobile'];
			}
			
			$member_service = new Member();
			$member_address = $member_service->getMemberDefaultAddress($this->uid);
			$order_data["address"] = $member_address;
			$result = $order_create->orderCreate($order_data);
			if ($result["code"] <= 0) {
				return array(
					"code" => 0,
					"message" => '赠品创建订单失败'
				);
			}
			$order_id = $result["data"]["order_id"];
			
			// 订单项
			$order_goods_module = new NsOrderGoodsModel();
			$order_goods_id = $order_goods_module->getInfo([ "order_id" => $order_id ], "order_goods_id")["order_goods_id"];
			
			if ($order_goods_id > 0) {
				// 订单赠品发放记录 关联订单项id
				$gift_records_model->save([ 'relate_id' => $order_goods_id ], [ 'id' => $param["gift_records_id"] ]);
				// 获奖记录表更新使用状态
				$ns_winning_records = new NsPromotionGamesWinningRecordsModel();
				$ns_winning_records->save([
					"is_use" => 1
				], [
					"associated_gift_record_id" => $param["gift_records_id"]
				]);
			} else {
				$gift_records_model->rollback();
				return array(
					"code" => 0,
					"message" => '赠品创建订单失败'
				);
			}
			
			$gift_records_model->commit();
			return array(
				"code" => 1,
				"message" => '奖品领取成功！请到我的订单中查看'
			);
		} catch (\Exception $e) {
			$gift_records_model->rollback();
			return $e->getMessage();
		}
	}
	
	/**********************************************************赠品管理结束*************************************************/
	
	/**********************************************************满减送管理***************************************************/
	
	/**
	 * 添加满减送
	 */
	public function addPromotionMansong($param)
	{
		$promot_mansong = new NsPromotionMansongModel();
		$goods_mansong = new GoodsMansong();
		$promot_mansong->startTrans();
		try {
			$err = 0;
			$count_quan = $goods_mansong->getQuanmansong($param["start_time"], $param["end_time"]);
			if ($count_quan > 0 && $param["range_type"] == 1) {
				$err = 1;
			}
			$shop_name = $this->instance_name;
			$time = time();
			if ($time < getTimeTurnTimeStamp($param["start_time"])) {
				$status = 0;
			} else {
				$status = 1;
			}
			$data = array(
				'mansong_name' => $param["mansong_name"],
				'start_time' => getTimeTurnTimeStamp($param["start_time"]),
				'end_time' => getTimeTurnTimeStamp($param["end_time"]),
				'shop_id' => 0,
				'shop_name' => $shop_name,
				'status' => $status, // 状态重新设置
				'remark' => $param["remark"],
				'type' => $param["type"],
				'range_type' => $param["range_type"],
				'create_time' => $param["create_time"]
			);
			$promot_mansong->save($data);
			$mansong_id = $promot_mansong->mansong_id;
			$this->addUserLog($this->uid, 1, '营销', '满减送管理', '添加满减送：' . $param["mansong_name"]);
			// 添加活动规则表
			$rule_array = explode(';', $param["rule"]);
			foreach ($rule_array as $k => $v) {
				$get_rule = explode(',', $v);
				$data_rule = array(
					'mansong_id' => $mansong_id,
					'price' => $get_rule[0],
					'discount' => $get_rule[1],
					'free_shipping' => $get_rule[2],
					'give_point' => $get_rule[3],
					'give_coupon' => $get_rule[4],
					'gift_id' => $get_rule[5]
				);
				$promot_mansong_rule = new NsPromotionMansongRuleModel();
				$promot_mansong_rule->save($data_rule);
			}
			
			// 满减送商品表
			if ($param["range_type"] == 0 && !empty($param["goods_id_array"])) {
				// 部分商品
				$goods_id_array = explode(',', $param["goods_id_array"]);
				foreach ($goods_id_array as $k => $v) {
					$promotion_mansong_goods = new NsPromotionMansongGoodsModel();
					// 查询商品名称图片
					$goods = new NsGoodsModel();
					$goods_info = $goods->getInfo([
						'goods_id' => $v
					], 'goods_name,picture');
					$data_goods = array(
						'mansong_id' => $mansong_id,
						'goods_id' => $v,
						'goods_name' => $goods_info['goods_name'],
						'goods_picture' => $goods_info['picture'],
						'status' => $status, // 状态重新设置
						'start_time' => getTimeTurnTimeStamp($param["start_time"]),
						'end_time' => getTimeTurnTimeStamp($param["end_time"]),
					);
					$count = $goods_mansong->getGoodsIsMansong($v, getTimeTurnTimeStamp($param["start_time"]), getTimeTurnTimeStamp($param["end_time"]));
					if ($count > 0) {
						$err = 1;
					}
					$promotion_mansong_goods->save($data_goods);
					$goods_promotion_model = new NsGoodsPromotionModel();
					$data_goods_promotion = [
						'goods_id' => $v,
						'label' => '满',
						'remark' => '',
						'status' => $status,
						'is_all' => 0,
						'promotion_addon' => 'MANJIAN',
						'promotion_id' => $mansong_id,
						'start_time' => getTimeTurnTimeStamp($param["start_time"]),
						'end_time' => getTimeTurnTimeStamp($param["end_time"]),
					];
					$goods_promotion_model->save($data_goods_promotion);
					
				}
			} else {
				$goods_promotion_model = new NsGoodsPromotionModel();
				$data_goods_promotion = [
					'goods_id' => 0,
					'label' => '满',
					'remark' => '',
					'status' => $status,
					'is_all' => 0,
					'promotion_addon' => 'MANJIAN',
					'promotion_id' => $mansong_id,
					'start_time' => getTimeTurnTimeStamp($param["start_time"]),
					'end_time' => getTimeTurnTimeStamp($param["end_time"]),
				];
				$goods_promotion_model->save($data_goods_promotion);
			}
			if ($err > 0) {
				$promot_mansong->rollback();
				return ACTIVE_REPRET;
			} else {
				$promot_mansong->commit();
				return $mansong_id;
			}
		} catch (\Exception $e) {
			$promot_mansong->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 修改满减送
	 */
	public function updatePromotionMansong($param)
	{
		$promot_mansong = new NsPromotionMansongModel();
		$promot_mansong->startTrans();
		try {
			$err = 0;
			$shop_name = $this->instance_name;
			$time = time();
			if ($time < getTimeTurnTimeStamp($param["start_time"])) {
				$status = 0;
			} else {
				$status = 1;
			}
			$data = array(
				'mansong_name' => $param["mansong_name"],
				'start_time' => getTimeTurnTimeStamp($param["start_time"]),
				'end_time' => getTimeTurnTimeStamp($param["end_time"]),
				'shop_id' => 0,
				'shop_name' => $shop_name,
				'status' => $status, // 状态重新设置
				'remark' => $param["remark"],
				'type' => $param["type"],
				'range_type' => $param["range_type"]
			);
			
			$promot_mansong->save($data, [
				'mansong_id' => $param["mansong_id"]
			]);
			$this->addUserLog($this->uid, 1, '营销', '满减送管理', '修改满减送：' . $param["mansong_name"]);
			// 添加活动规则表
			$promot_mansong_rule = new NsPromotionMansongRuleModel();
			$promot_mansong_rule->destroy([
				'mansong_id' => $param["mansong_id"]
			]);
			$rule_array = explode(';', $param["rule"]);
			foreach ($rule_array as $k => $v) {
				$promot_mansong_rule = new NsPromotionMansongRuleModel();
				$get_rule = explode(',', $v);
				$data_rule = array(
					'mansong_id' => $param["mansong_id"],
					'price' => $get_rule[0],
					'discount' => $get_rule[1],
					'free_shipping' => $get_rule[2],
					'give_point' => $get_rule[3],
					'give_coupon' => $get_rule[4],
					'gift_id' => $get_rule[5]
				);
				$promot_mansong_rule->save($data_rule);
			}
			
			// 满减送商品表
			if ($param["range_type"] == 0 && !empty($param["goods_id_array"])) {
				// 部分商品
				$goods_id_array = explode(',', $param["goods_id_array"]);
				$promotion_mansong_goods = new NsPromotionMansongGoodsModel();
				$promotion_mansong_goods->destroy([
					'mansong_id' => $param["mansong_id"]
				]);
				$goods_promotion_model = new NsGoodsPromotionModel();
				$goods_promotion_model->destroy([ 'promotion_id' => $param["mansong_id"], 'promotion_addon' => 'MANJIAN' ]);
				$goods_mansong = new GoodsMansong();
				foreach ($goods_id_array as $k => $v) {
					// 查询商品名称图片
					$count = $goods_mansong->getGoodsIsMansong($v, getTimeTurnTimeStamp($param["start_time"]), getTimeTurnTimeStamp($param["end_time"]));
					if ($count > 0) {
						$err = 1;
					}
					$promotion_mansong_goods = new NsPromotionMansongGoodsModel();
					$goods = new NsGoodsModel();
					$goods_info = $goods->getInfo([
						'goods_id' => $v
					], 'goods_name,picture');
					$data_goods = array(
						'mansong_id' => $param["mansong_id"],
						'goods_id' => $v,
						'goods_name' => $goods_info['goods_name'],
						'goods_picture' => $goods_info['picture'],
						'status' => $status, // 状态重新设置
						'start_time' => getTimeTurnTimeStamp($param["start_time"]),
						'end_time' => getTimeTurnTimeStamp($param["end_time"])
					);
					$promotion_mansong_goods->save($data_goods);
					$goods_promotion_model = new NsGoodsPromotionModel();
					$data_goods_promotion = [
						'goods_id' => $v,
						'label' => '满',
						'remark' => '',
						'status' => $status,
						'is_all' => 0,
						'promotion_addon' => 'MANJIAN',
						'promotion_id' => $param["mansong_id"],
						'start_time' => getTimeTurnTimeStamp($param["start_time"]),
						'end_time' => getTimeTurnTimeStamp($param["end_time"])
					];
					$goods_promotion_model->save($data_goods_promotion);
				}
			} else {
				$goods_promotion_model = new NsGoodsPromotionModel();
				$goods_promotion_model->destroy([ 'promotion_id' => $param["mansong_id"] ]);
				$data_goods_promotion = [
					'goods_id' => 0,
					'label' => '满',
					'remark' => '',
					'status' => $status,
					'is_all' => 0,
					'promotion_addon' => 'MANJIAN',
					'promotion_id' => $param["mansong_id"],
					'start_time' => getTimeTurnTimeStamp($param["start_time"]),
					'end_time' => getTimeTurnTimeStamp($param["end_time"])
				];
				$goods_promotion_model->save($data_goods_promotion);
			}
			if ($err > 0) {
				$promot_mansong->rollback();
				return ACTIVE_REPRET;
			} else {
				$promot_mansong->commit();
				return 1;
			}
		} catch (\Exception $e) {
			$promot_mansong->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 关闭满减送
	 */
	public function closePromotionMansong($mansong_id)
	{
		$goods_promotion_model = new NsGoodsPromotionModel();
		$goods_promotion_model->destroy([ 'promotion_id' => $mansong_id, 'promotion_addon' => 'MANJIAN' ]);
		$promotion_mansong = new NsPromotionMansongModel();
		$retval = $promotion_mansong->save([
			'status' => 3
		], [
			'mansong_id' => $mansong_id,
			'shop_id' => $this->instance_id
		]);
		if ($retval == 1) {
			$this->addUserLog($this->uid, 1, '营销', '满减送管理', '关闭满减送：id' . $mansong_id);
			$promotion_mansong_goods = new NsPromotionMansongGoodsModel();
			$retval = $promotion_mansong_goods->save([
				'status' => 3
			], [
				'mansong_id' => $mansong_id
			]);
		}
		return $retval;
	}
	
	/**
	 * 删除满减送
	 */
	public function deletePromotionMansong($mansong_id)
	{
		$goods_promotion_model = new NsGoodsPromotionModel();
		$goods_promotion_model->destroy([ 'promotion_id' => $mansong_id, 'promotion_addon' => 'MANJIAN' ]);
		$promotion_mansong = new NsPromotionMansongModel();
		$promotion_mansong_goods = new NsPromotionMansongGoodsModel();
		$promot_mansong_rule = new NsPromotionMansongRuleModel();
		$promotion_mansong->startTrans();
		try {
			$mansong_id_array = explode(',', $mansong_id);
			foreach ($mansong_id_array as $k => $v) {
				$status = $promotion_mansong->getInfo([
					'mansong_id' => $v
				], 'status');
				if ($status['status'] == 1) {
					$promotion_mansong->rollback();
					return -1;
				}
				$promotion_mansong->destroy($v);
				$promotion_mansong_goods->destroy([
					'mansong_id' => $v
				]);
				$promot_mansong_rule->destroy([
					'mansong_id' => $v
				]);
			}
			$promotion_mansong->commit();
			return 1;
		} catch (\Exception $e) {
			$promotion_mansong->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 获取满减送详情
	 */
	public function getPromotionMansongDetail($mansong_id)
	{
		$promotion_mansong = new NsPromotionMansongModel();
		$data = $promotion_mansong->get($mansong_id);
		$promot_mansong_rule = new NsPromotionMansongRuleModel();
		$rule_list = $promot_mansong_rule->pageQuery(1, 0, 'mansong_id = ' . $mansong_id, '', '*');
		foreach ($rule_list['data'] as $k => $v) {
			if ($v['free_shipping'] == 1) {
				$rule_list['data'][ $k ]['free_shipping_name'] = "是";
			} else {
				$rule_list['data'][ $k ]['free_shipping_name'] = "否";
			}
			if ($v['give_coupon'] == 0) {
				$rule_list['data'][ $k ]['coupon_name'] = '';
			} else {
				$coupon_type = new NsCouponTypeModel();
				$coupon_name = $coupon_type->getInfo([
					'coupon_type_id' => $v['give_coupon']
				], 'coupon_name');
				$rule_list['data'][ $k ]['coupon_name'] = $coupon_name['coupon_name'];
			}
			if ($v['gift_id'] == 0) {
				$rule_list['data'][ $k ]['gift_name'] = '';
			} else {
				$gift = new NsPromotionGiftModel();
				$gift_name = $gift->getInfo([
					'gift_id' => $v['gift_id']
				], 'gift_name');
				$rule_list['data'][ $k ]['gift_name'] = $gift_name['gift_name'];
			}
		}
		$list = array();
		$goods_id_array = array();
		$data['rule'] = $rule_list['data'];
		if ($data['range_type'] == 0) {
			$mansong_goods = new NsPromotionMansongGoodsModel();
			$list = $mansong_goods->getQuery([
				'mansong_id' => $mansong_id
			]);
			if (!empty($list)) {
				foreach ($list as $k => $v) {
					$goods = new NsGoodsModel();
					$goods_info = $goods->getInfo([
						'goods_id' => $v['goods_id']
					], 'price, stock');
					$picture = new AlbumPictureModel();
					$pic_info = array();
					$pic_info['pic_cover'] = '';
					if (!empty($v['goods_picture'])) {
						$pic_info = $picture->get($v['goods_picture']);
					}
					$v['picture_info'] = $pic_info;
					$v['price'] = $goods_info['price'];
					$v['stock'] = $goods_info['stock'];
				}
			}
			foreach ($list as $k => $v) {
				$goods_id_array[] = $v['goods_id'];
			}
		}
		$data['goods_list'] = $list;
		$data['goods_id_array'] = $goods_id_array;
		return $data;
	}
	
	/**
	 * 获取满减送活动列表
	 */
	public function getPromotionMansongList($page_index = 1, $page_size = 0, $condition = '', $order = 'create_time desc')
	{
		$promotion_mansong = new NsPromotionMansongModel();
		$list = $promotion_mansong->pageQuery($page_index, $page_size, $condition, $order, '*');
		if (!empty($list['data'])) {
			foreach ($list['data'] as $k => $v) {
				if ($v['status'] == 0) {
					$list['data'][ $k ]['status_name'] = '未开始';
				}
				if ($v['status'] == 1) {
					$list['data'][ $k ]['status_name'] = '进行中';
				}
				if ($v['status'] == 2) {
					$list['data'][ $k ]['status_name'] = '已取消';
				}
				if ($v['status'] == 3) {
					$list['data'][ $k ]['status_name'] = '已失效';
				}
				if ($v['status'] == 4) {
					$list['data'][ $k ]['status_name'] = '已结束';
				}
			}
		}
		return $list;
	}
	
	/**
	 * 满减送超过期限自动关闭, 进入时间自动开始
	 */
	public function mansongOperation()
	{
		$mansong = new NsPromotionMansongModel();
		$mansong->startTrans();
		try {
			$time = time();
			$condition_close = array(
				'end_time' => array( 'LT', $time ),
				'status' => array( 'NEQ', 3 )
			);
			$condition_start = array(
				'start_time' => array( 'ELT', $time ),
				'status' => 0
			);
			$mansong->save([ 'status' => 4 ], $condition_close);
			$mansong->save([ 'status' => 1 ], $condition_start);
			$mansong_goods = new NsPromotionMansongGoodsModel();
			$mansong_goods->save([ 'status' => 4 ], $condition_close);
			$mansong_goods->save([ 'status' => 1 ], $condition_start);
			$mansong->commit();
			Cache::clear('mansong');
			return 1;
		} catch (\Exception $e) {
			$mansong->rollback();
			return $e->getMessage();
		}
		
	}
	
	/***********************************************************满减送  end**************************************************************/
	
	/***********************************************************限时折扣  begin**************************************************************/
	/**
	 * 限时折扣自动开始以及自动关闭
	 */
	public function discountOperation()
	{
		$discount = new NsPromotionDiscountModel();
		$discount->startTrans();
		try {
			$time = time();
			$discount_goods = new NsPromotionDiscountGoodsModel();
			/************************************************************结束活动**************************************************************/
			$condition_close = array(
				'end_time' => array( 'LT', $time ),
				'status' => array( 'NEQ', 3 )
			);
			$discount_list = $discount->getQuery($condition_close, "*");//可以关闭的限时折扣
			$discount->save([ 'status' => 4 ], $condition_close);
			
			//删除相关限时折扣营销活动
			$goods_promotion_model = new NsGoodsPromotionModel();
			foreach ($discount_list as $discount_key => $discount_item) {
				$goods_promotion_model->destroy([ "promotion_id" => $discount_item["discount_id"], "promotion_addon" => "DISCOUNT" ]);
			}
			
			$discount_close_goods_list = $discount_goods->getQuery($condition_close);
			if (!empty($discount_close_goods_list)) {
				foreach ($discount_close_goods_list as $k => $discount_goods_item) {
					$goods_model = new NsGoodsModel();
					
					$data_goods = array(
						'promotion_type' => 2,
						'promote_id' => $discount_goods_item['discount_id']
					);
					$goods_id_list = $goods_model->getQuery($data_goods, 'goods_id');
					if (!empty($goods_id_list)) {
						foreach ($goods_id_list as $k => $goods_item) {
							$goods_info = $goods_model->getInfo([ 'goods_id' => $goods_item['goods_id'] ], 'promotion_type, price');
							//							$goods->save([ 'promotion_price' => $goods_info['price'] ], [ 'goods_id' => $goods_id['goods_id'] ]);
							$goods_model->save([ 'promotion_price' => $goods_info['price'] ], [ "goods_id" => $goods_item['goods_id'] ]);
// 						    Db::table('ns_goods')->where('goods_id', $goods_item['goods_id'])->update([ 'promotion_price' => $goods_info['price'] ]);
							$goods_sku = new NsGoodsSkuModel();
							$goods_sku_list = $goods_sku->getQuery([ 'goods_id' => $goods_item['goods_id'] ], 'price,sku_id');
							foreach ($goods_sku_list as $k_sku => $sku_item) {
								$goods_sku = new NsGoodsSkuModel();
								$data_goods_sku = array(
									'promote_price' => $sku_item['price']
								);
								$goods_sku->save($data_goods_sku, [ 'sku_id' => $sku_item['sku_id'] ]);
							}
							
							//清除商品详情缓存
							Cache::tag("niu_goods")->set("getBusinessGoodsInfo_" . $goods_item['goods_id'], null);
							
						}
						
					}
					$goods_model->save([ 'promotion_type' => 0, 'promote_id' => 0 ], $data_goods);
					
				}
			}
			$discount_goods->save([ 'status' => 4 ], $condition_close);
			/************************************************************结束活动**************************************************************/
			/************************************************************开始活动**************************************************************/
			$condition_start = array(
				'start_time' => array( 'ELT', $time ),
				'status' => 0
			);
			//查询待开始活动列表
			$discount_goods_list = $discount_goods->getQuery($condition_start);
			if (!empty($discount_goods_list)) {
				foreach ($discount_goods_list as $k => $discount_goods_item) {
					$this->discountUpdatePriceAction($discount_goods_item);
				}
			}
			$discount_goods->save([ 'status' => 1 ], $condition_start);
			$discount->save([ 'status' => 1 ], $condition_start);
			/************************************************************开始活动**************************************************************/
			Cache::clear('discount');
			$discount->commit();
			return 1;
		} catch (\Exception $e) {
			$discount->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 开启限时折扣更新商品价格
	 * goods_id、discount、decimal_reservation_number、discount_id
	 * @param $discount_goods_item
	 */
	private function discountUpdatePriceAction($discount_goods_item)
	{
		$goods = new NsGoodsModel();
		$goods_info = $goods->getInfo([ 'goods_id' => $discount_goods_item['goods_id'] ], 'promotion_type,price');
		
		$promotion_price = $goods_info['price'] * $discount_goods_item['discount'] / 10;
		if ($discount_goods_item['decimal_reservation_number'] >= 0) {
			$promotion_price = sprintf("%.2f", round($promotion_price, $discount_goods_item['decimal_reservation_number']));
		}
		
		$data_goods = array(
			'promotion_type' => 2,
			'promote_id' => $discount_goods_item['discount_id'],
			'promotion_price' => $promotion_price
		);
		
		$goods->save($data_goods, [ 'goods_id' => $discount_goods_item['goods_id'] ]);
		$goods_sku = new NsGoodsSkuModel();
		$goods_sku_list = $goods_sku->getQuery([ 'goods_id' => $discount_goods_item['goods_id'] ], 'price,sku_id');
		foreach ($goods_sku_list as $k_sku => $sku) {
			$goods_sku = new NsGoodsSkuModel();
			$promote_price = $sku['price'] * $discount_goods_item['discount'] / 10;
			if ($discount_goods_item['decimal_reservation_number'] >= 0) {
				$promote_price = sprintf("%.2f", round($promote_price, $discount_goods_item['decimal_reservation_number']));
			}
			$data_goods_sku = array(
				'promote_price' => $promote_price
			);
			$goods_sku->save($data_goods_sku, [ 'sku_id' => $sku['sku_id'] ]);
		}
		//清除商品详情缓存
		Cache::tag("niu_goods")->set("getBusinessGoodsInfo_" . $discount_goods_item['goods_id'], null);
	}
	
	/**
	 * 添加限时折扣
	 */
	public function addPromotiondiscount($param)
	{
		$promotion_discount = new NsPromotionDiscountModel();
		$promotion_discount->startTrans();
		try {
			$shop_name = $this->instance_name;
			
			$time = time();
			if ($time < $param["start_time"]) {
				$status = 0;
			} else {
				$status = 1;
			}
			$data = array(
				'discount_name' => $param["discount_name"],
				'start_time' => $param["start_time"],
				'end_time' => $param["end_time"],
				'shop_id' => 0,
				'shop_name' => $shop_name,
				'status' => $status,
				'remark' => $param["remark"],
				'create_time' => time(),
				'decimal_reservation_number' => $param["decimal_reservation_number"]
			);
			$promotion_discount->save($data);
			$discount_id = $promotion_discount->discount_id;
			$this->addUserLog($this->uid, 1, '营销', '限时折扣', '添加限时折扣：' . $param["discount_name"]);
			$goods_id_array = explode(',', $param["goods_id_array"]);
			$goods_promotion_model = new NsGoodsPromotionModel();
			$goods_promotion_model->destroy([ 'promotion_id' => $discount_id, 'promotion_addon' => 'DISCOUNT' ]);
			
			//判断是否存在其他单品活动
			$result = $this->getGoodsIdsPromotionIsExist($param["goods_id_array"], $param["start_time"], $param["end_time"]);//判断所选商品是否存在单品活动
			if ($result["code"] <= 0) {
				$promotion_discount->rollback();
				return $result;
			}
			foreach ($goods_id_array as $k => $v) {
				// 添加检测考虑商品在一个时间段内只能有一种活动
				$promotion_discount_goods = new NsPromotionDiscountGoodsModel();
				$discount_info = explode(':', $v);
				$goods_discount = new GoodsDiscount();
				$count = $goods_discount->getGoodsIsDiscount($discount_info[0], $param["start_time"], $param["end_time"]);
				
				if ($count > 0) {
					$promotion_discount->rollback();
					return error("", ACTIVE_REPRET);
				}
				$goods = new NsGoodsModel();
				$goods_info = $goods->getInfo([ 'goods_id' => $discount_info[0] ], 'goods_name,picture');
				$data_goods = array(
					'discount_id' => $discount_id,
					'goods_id' => $discount_info[0],
					'discount' => $discount_info[1],
					'status' => $status,
					'start_time' => $param["start_time"],
					'end_time' => $param["end_time"],
					'goods_name' => $goods_info['goods_name'],
					'goods_picture' => $goods_info['picture'],
					'decimal_reservation_number' => $param["decimal_reservation_number"]
				);
				$promotion_discount_goods->save($data_goods);
				
				$goods_promotion_model = new NsGoodsPromotionModel();
				$data_goods_promotion = [
					'goods_id' => $discount_info[0],
					'label' => '折',
					'remark' => '',
					'status' => $status,
					'is_all' => 0,
					'promotion_addon' => 'DISCOUNT',
					'is_goods_promotion' => 1,
					'promotion_id' => $discount_id,
					'start_time' => $param["start_time"],
					'end_time' => $param["end_time"],
				];
				$goods_promotion_model->save($data_goods_promotion);
				
				//针对已开始的限时折扣活动更新价格
				if ($status == 1) {
					$discount_goods_item = [
						'discount_id' => $discount_id,
						'goods_id' => $discount_info[0],
						'discount' => $discount_info[1],
						'decimal_reservation_number' => $param["decimal_reservation_number"]
					];
					$this->discountUpdatePriceAction($discount_goods_item);
				}
			}
			$promotion_discount->commit();
			return success($discount_id);
		} catch (\Exception $e) {
			$promotion_discount->rollback();
			return error($e->getMessage());
		}
	}
	
	/**
	 * 修改限时折扣
	 */
	public function updatePromotionDiscount($param)
	{
		$promotion_discount = new NsPromotionDiscountModel();
		$promotion_discount->startTrans();
		try {
			$this->closePromotionDiscount($param["discount_id"]);
			$shop_name = $this->instance_name;
			$time = time();
			if ($time < $param["start_time"]) {
				$status = 0;
			} else {
				$status = 1;
			}
			$data = array(
				'discount_name' => $param["discount_name"],
				'start_time' => $param["start_time"],
				'end_time' => $param["end_time"],
				'shop_id' => $this->instance_id,
				'shop_name' => $shop_name,
				'status' => $status,
				'remark' => $param["remark"],
				'decimal_reservation_number' => $param["decimal_reservation_number"]
			);
			$promotion_discount->save($data, [
				'discount_id' => $param["discount_id"]
			]);
			$this->addUserLog($this->uid, 1, '营销', '限时折扣', '修改限时折扣：' . $param["discount_name"]);
			$goods_id_array = explode(',', $param["goods_id_array"]);
			$promotion_discount_goods = new NsPromotionDiscountGoodsModel();
			$promotion_discount_goods->destroy([
				'discount_id' => $param["discount_id"]
			]);
			$goods_promotion_model = new NsGoodsPromotionModel();
			$goods_promotion_model->destroy([ 'promotion_id' => $param["discount_id"], 'promotion_addon' => 'DISCOUNT' ]);
			
			//判断是否存在其他单品活动
			$result = $this->getGoodsIdsPromotionIsExist($param["goods_id_array"], $param["start_time"], $param["end_time"]);//判断所选商品是否存在单品活动
			if ($result["code"] <= 0) {
				$promotion_discount->rollback();
				return $result;
			}
			foreach ($goods_id_array as $k => $v) {
				$promotion_discount_goods = new NsPromotionDiscountGoodsModel();
				$discount_info = explode(':', $v);
				$goods_discount = new GoodsDiscount();
				$count = $goods_discount->getGoodsIsDiscount($discount_info[0], $param["start_time"], $param["end_time"]);
				// 查询商品名称图片
				if ($count > 0) {
					$promotion_discount->rollback();
					return error("", ACTIVE_REPRET);
				}
				// 查询商品名称图片
				$goods = new NsGoodsModel();
				$goods_info = $goods->getInfo([
					'goods_id' => $discount_info[0]
				], 'goods_name,picture');
				$data_goods = array(
					'discount_id' => $param["discount_id"],
					'goods_id' => $discount_info[0],
					'discount' => $discount_info[1],
					'status' => $status,
					'start_time' => $param["start_time"],
					'end_time' => $param["end_time"],
					'goods_name' => $goods_info['goods_name'],
					'goods_picture' => $goods_info['picture'],
					'decimal_reservation_number' => $param["decimal_reservation_number"]
				);
				$promotion_discount_goods->save($data_goods);
				$goods_promotion_model = new NsGoodsPromotionModel();
				$data_goods_promotion = [
					'goods_id' => $discount_info[0],
					'label' => '折',
					'remark' => '',
					'status' => $status,
					'is_all' => 0,
					'promotion_addon' => 'DISCOUNT',
					'promotion_id' => $param["discount_id"],
					'is_goods_promotion' => 1,
					'start_time' => $param["start_time"],
					'end_time' => $param["end_time"],
				];
				$goods_promotion_model->save($data_goods_promotion);
				
				//针对已开始的限时折扣活动更新价格
				if ($status == 1) {
					$discount_goods_item = [
						'discount_id' => $param["discount_id"],
						'goods_id' => $discount_info[0],
						'discount' => $discount_info[1],
						'decimal_reservation_number' => $param["decimal_reservation_number"]
					];
					$this->discountUpdatePriceAction($discount_goods_item);
				}
			}
			$promotion_discount->commit();
			return success($param["discount_id"]);
		} catch (\Exception $e) {
			$promotion_discount->rollback();
			return error($e->getMessage());
		}
	}
	
	/**
	 * 删除限时折扣
	 */
	public function deletePromotionDiscount($discount_id)
	{
		$promotion_discount = new NsPromotionDiscountModel();
		$promotion_discount_goods = new NsPromotionDiscountGoodsModel();
		$goods_promotion_model = new NsGoodsPromotionModel();
		$goods_promotion_model->destroy([ 'promotion_id' => $discount_id, 'promotion_addon' => 'DISCOUNT' ]);
		$promotion_discount->startTrans();
		try {
			$discount_id_array = explode(',', $discount_id);
			foreach ($discount_id_array as $k => $v) {
				$promotion_detail = $promotion_discount->get($discount_id);
				if ($promotion_detail['status'] == 1) {
					$promotion_discount->rollback();
					return -1;
				}
				$promotion_discount->destroy($v);
				$promotion_discount_goods->destroy([
					'discount_id' => $v
				]);
			}
			$promotion_discount->commit();
			return 1;
		} catch (\Exception $e) {
			$promotion_discount->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 关闭限时折扣
	 */
	public function closePromotionDiscount($discount_id)
	{
		$promotion_discount = new NsPromotionDiscountModel();
		$promotion_discount->startTrans();
		try {
			$goods_promotion_model = new NsGoodsPromotionModel();
			$goods_promotion_model->destroy([ 'promotion_id' => $discount_id, 'promotion_addon' => 'DISCOUNT' ]);
			$retval = $promotion_discount->save([
				'status' => 3
			], [
				'discount_id' => $discount_id
			]);
			if ($retval == 1) {
				$goods = new NsGoodsModel();
				
				$data_goods = array(
					'promotion_type' => 2,
					'promote_id' => $discount_id
				);
				$goods_id_list = $goods->getQuery($data_goods, 'goods_id');
				if (!empty($goods_id_list)) {
					
					foreach ($goods_id_list as $k => $goods_id) {
						$goods_info = $goods->getInfo([
							'goods_id' => $goods_id['goods_id']
						], 'promotion_type,price');
						$goods->save([
							'promotion_price' => $goods_info['price']
						], [
							'goods_id' => $goods_id['goods_id']
						]);
						$goods_sku = new NsGoodsSkuModel();
						$goods_sku_list = $goods_sku->getQuery([
							'goods_id' => $goods_id['goods_id']
						], 'price,sku_id');
						foreach ($goods_sku_list as $k_sku => $sku) {
							$goods_sku = new NsGoodsSkuModel();
							$data_goods_sku = array(
								'promote_price' => $sku['price']
							);
							$goods_sku->save($data_goods_sku, [
								'sku_id' => $sku['sku_id']
							]);
						}
						
						//清除商品详情缓存
						Cache::tag("niu_goods")->set("getBusinessGoodsInfo_" . $goods_id['goods_id'], null);
					}
				}
				$goods->save([
					'promotion_type' => 0,
					'promote_id' => 0
				], $data_goods);
				$promotion_discount_goods = new NsPromotionDiscountGoodsModel();
				$retval = $promotion_discount_goods->save([
					'status' => 3
				], [
					'discount_id' => $discount_id
				]);
			}
			$promotion_discount->commit();
			return $retval;
		} catch (\Exception $e) {
			$promotion_discount->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 获取限时折扣详情
	 */
	public function getPromotionDiscountDetail($discount_id)
	{
		$promotion_discount = new NsPromotionDiscountModel();
		$promotion_detail = $promotion_discount->get($discount_id);
		$promotion_discount_goods = new NsPromotionDiscountGoodsViewModel();
		$promotion_goods_list = $promotion_discount_goods->getViewQuery(1, 0, [ 'npdg.discount_id' => $discount_id ], '');
		if (!empty($promotion_goods_list)) {
			foreach ($promotion_goods_list as $k => $v) {
				$picture = new AlbumPictureModel();
				if (!empty($v['picture'])) {
					$pic_info = $picture->get($v['picture']);
				}
				$v['picture_info'] = $pic_info;
			}
		}
		$promotion_detail['goods_list'] = $promotion_goods_list;
		return $promotion_detail;
	}
	
	/**
	 * 获取限时折扣列表
	 */
	public function getPromotionDiscountList($page_index = 1, $page_size = 0, $condition = '', $order = 'create_time desc', $field = '*')
	{
		$promotion_discount = new NsPromotionDiscountModel();
		$list = $promotion_discount->pageQuery($page_index, $page_size, $condition, $order, $field);
		return $list;
	}
	
	/**
	 * 获取最新的限时折扣活动
	 */
	public function getNewestDiscount()
	{
		$result = [];
		$promotion_discount = new NsPromotionDiscountModel();
		$info = $promotion_discount->getFirstData([ 'status' => 1 ], 'discount_id desc');
		if (!empty($info)) {
			$result = $info;
			$promotion_discount_goods = new NsPromotionDiscountGoodsViewModel();
			$promotion_goods_list = $promotion_discount_goods->getViewQuery(1, 0, [ 'npdg.discount_id' => $info['discount_id'] ], '');
			if (!empty($promotion_goods_list)) {
				foreach ($promotion_goods_list as $k => $v) {
					$picture = new AlbumPictureModel();
					if (!empty($v['picture'])) {
						$pic_info = $picture->get($v['picture']);
					}
					$v['picture_info'] = $pic_info;
				}
			}
			$result['goods_list'] = $promotion_goods_list;
			
		}
		return $result;
	}
	
	/***********************************************************限时折扣结束************************************************/
	
	/***********************************************************满额包邮***************************************************/
	/**
	 * 更新或添加满额包邮的信息
	 * @param $data
	 * @return int
	 */
	public function updatePromotionFullMail($data)
	{
		$full_mail_model = new NsPromotionFullMailModel();
		$full_mail_model->save($data, [
			"shop_id" => 0
		]);
		return 1;
	}
	
	/**
	 * 得到店铺的满额包邮信息
	 */
	public function getPromotionFullMail()
	{
		$promotion_fullmail = new NsPromotionFullMailModel();
		$mail_count = $promotion_fullmail->getCount([
			"shop_id" => 0
		]);
		if ($mail_count == 0) {
			$data = array(
				'shop_id' => 0,
				'is_open' => 0,
				'full_mail_money' => 0,
				'no_mail_province_id_array' => '',
				'no_mail_city_id_array' => '',
				'create_time' => time()
			);
			$promotion_fullmail->save($data);
		}
		$mail_obj = $promotion_fullmail->getInfo([
			"shop_id" => 0
		]);
		return $mail_obj;
	}
	/***********************************************************满额包邮结束************************************************/
	
	/***********************************************************营销游戏***************************************************/
	
	/**
	 * 添加营销游戏
	 */
	public function addUpdatePromotionGame($param)
	{
		$promotion_games = new NsPromotionGamesModel();
		$promotion_games->startTrans();
		
		try {
			$time = time();
			if ($time < $param["start_time"]) {
				$status = 0;
			} else {
				$status = 1;
			}
			$member_level_model = new NsMemberLevelModel();
			if ($param["member_level"] == 0) {
				$level_name = '所有用户';
			} else {
				$level_info = $member_level_model->getInfo([
					'level_id' => $param["member_level"]
				], 'level_name');
				$level_name = $level_info['level_name'];
			}
			
			$data = array(
				'shop_id' => 0,
				'name' => $param["name"],
				'game_type' => $param["game_type"],
				'member_level' => $param["member_level"],
				'level_name' => $level_name,
				'points' => $param["points"],
				'start_time' => $param["start_time"],
				'end_time' => $param["end_time"],
				'remark' => $param["remark"],
				'winning_rate' => $param["winning_rate"],
				'no_winning_des' => $param["no_winning_des"],
				'activity_images' => $param["activity_images"],
				"winning_list_display" => $param["winning_list_display"],
				"join_type" => $param["join_type"],
				"status" => $status,
				"join_frequency" => $param["join_frequency"],
				"winning_type" => $param["winning_type"],
				"winning_max" => $param["winning_max"]
			);
			if (empty($param["game_id"])) {
				$this->addUserLog($this->uid, 1, '营销', '营销游戏', '添加游戏：' . $param["name"]);
				$promotion_games->save($data);
// 				$info = $promotion_games->pageQuery(1, 0, "", "game_id desc limit 1", "*");
				$game_id = $promotion_games->game_id;
			} else {
				$this->addUserLog($this->uid, 1, '营销', '营销游戏', '修改游戏：' . $param["name"]);
				$promotion_games->save($data, [
					'game_id' => $param["game_id"]
				]);
				$game_id = $param["game_id"];
			}
			
			// 删除已有的规则
			$this->delPromotionGameRule($game_id);
			
			// 添加规则表
			$rule_array = json_decode($param["rule_json"], true);
			foreach ($rule_array as $item) {
				$rule_data = array(
					'game_id' => $game_id,
					'rule_name' => $item['rule_name'],
					'rule_num' => $item['rule_num'],
					'remaining_number' => $item['rule_num'], // 剩余奖品数量
					'type' => $item['type'],
					'remark' => '',
					'points' => $item['points'],
					'coupon_type_id' => $item['coupon_type_id'],
					'hongbao' => $item['hongbao'],
					'gift_id' => $item['gift_id'],
					'type_value' => $item['type_value'],
					'create_time' => time()
				);
				$this->addPromotionGameRule($rule_data);
			}
			$promotion_games->commit();
			
			return 1;
		} catch (\Exception $e) {
			$promotion_games->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 添加活动规则
	 */
	public function addPromotionGameRule($data)
	{
		$game_rule_model = new NsPromotionGameRuleModel();
		$res = $game_rule_model->save($data);
		return $res;
	}
	
	/**
	 * 删除活动规则
	 */
	public function delPromotionGameRule($game_id)
	{
		$game_rule_model = new NsPromotionGameRuleModel();
		$res = $game_rule_model->destroy([
			'game_id' => $game_id
		]);
		return $res;
	}
	
	/**
	 * 删除营销游戏
	 */
	public function deletePromotionGame($game_id)
	{
		$promotion_games = new NsPromotionGamesModel();
		$game_rule = new NsPromotionGameRuleModel();
		$promotion_games->startTrans();
		try {
			$condition = array(
				'game_id' => array( "in", $game_id )
			);
			$game_rule->destroy($condition);
			$res = $promotion_games->destroy($condition);
			$promotion_games->commit();
			return $res;
		} catch (\Exception $e) {
			$promotion_games->rollback();
			Log::write("营销游戏删除错误，错误原因：" . $e->getMessage());
		}
	}
	
	/**
	 * 根据主键关闭营销游戏
	 */
	public function closePromotionGame($game_id)
	{
		$promotion_games_model = new NsPromotionGamesModel();
		$res = $promotion_games_model->save([
			"status" => -2
		], [
			'game_id' => $game_id
		]);
		return $res;
	}
	
	/**
	 * 获取营销游戏详情
	 */
	public function getPromotionGameDetail($game_id)
	{
		$promotion_games = new NsPromotionGamesModel();
		$game_info = $promotion_games->getInfo([
			'game_id' => $game_id
		], '*');
		$promotion_games_rule = new NsPromotionGameRuleModel();
		$rule_list = $promotion_games_rule->getQuery([
			'game_id' => $game_id
		]);
		$game_info['rule'] = $rule_list;
		return $game_info;
	}
	
	/**
	 * 营销游戏信息
	 */
	public function getPromotionGameTypeInfo($game_type)
	{
		$game_type_model = new NsPromotionGameTypeModel();
		$info = $game_type_model->getInfo([
			'game_type' => $game_type
		], '*');
		return $info;
	}
	
	/**
	 * 获取营销游戏列表
	 */
	public function getPromotionGamesList($page_index = 1, $page_size = 0, $condition = '', $order = 'game_id desc')
	{
		$promotion_games = new NsPromotionGamesModel();
		$list = $promotion_games->pageQuery($page_index, $page_size, $condition, $order, '*');
		
		foreach ($list['data'] as $item) {
			
			$game_type_info = $this->getPromotionGameTypeInfo($item['game_type']);
			$item['game_type_name'] = $game_type_info['type_name'];
			
			switch ($item['status']) {
				case 0:
					$item['status_name'] = '未开始';
					break;
				case 1:
					$item['status_name'] = '已开始';
					break;
				case -1:
					$item['status_name'] = '已结束';
					break;
				case -2:
					$item['status_name'] = '已关闭';
					break;
				default:
					break;
			}
		}
		return $list;
	}
	
	/**
	 * 获取营销游戏类型列表
	 */
	public function getPromotionGameTypeList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '*')
	{
		$game_type_model = new NsPromotionGameTypeModel();
		$game_type_list = $game_type_model->pageQuery($page_index, $page_size, $condition, $order, $field);
		return $game_type_list;
	}
	
	/**
	 * 获取中奖记录表
	 */
	public function getPromotionGameWinningRecordsList($page_index, $page_size, $condition, $order, $field)
	{
		$WinningRecords = new NsPromotionGamesWinningRecordsModel();
		$list = $WinningRecords->pageQuery($page_index, $page_size, $condition, $order, $field);
		return $list;
	}
	
	/**
	 * 获取奖项
	 */
	public function getRandAward($game_id)
	{
		// 获取游戏详情
		$promotionGameDetail = $this->getPromotionGameDetail($game_id);
		
		if (!empty($promotionGameDetail)) {
			// 中奖概率 按百分比
			$winning_rate = round($promotionGameDetail["winning_rate"]);
			// 取一个 1 到 100 的随机数 如果这个数组小于概率则通过 第一步
			$rand_num = mt_rand(1, 100);
			
			if ($rand_num <= $winning_rate) {
				$rule_list = $promotionGameDetail["rule"];
				
				$retval = $this->getRandAwardRules($rule_list);
				if ($retval == -2) {
					return $res = array(
						"is_winning" => -2,
						"message" => "该游戏已结束"
					);
				} else if (count($retval) > 0) {
					return $result = array(
						"is_winning" => 1,
						"winning_info" => $retval,
						"no_winning_instruction" => $promotionGameDetail["no_winning_des"]
					);
				} else {
					
					return $result = array(
						"is_winning" => 0,
						"no_winning_instruction" => $promotionGameDetail["no_winning_des"],
						"winning_info" => [
							"rule_id" => 0
						]
					);
				}
			} else {
				return $result = array(
					"is_winning" => 0,
					"no_winning_instruction" => $promotionGameDetail["no_winning_des"],
					"winning_info" => [
						"rule_id" => 0
					]
				);
			}
		} else {
			return null;
		}
	}
	
	/**
	 * 获取随机奖项
	 */
	public function getRandAwardRules($rule_list)
	{
		$result = array();
		
		if (count($rule_list) > 0) {
			$roll_array = array(); //根据奖品数量生成权重区间
			$total_number = 0;
			foreach ($rule_list as $k => $v) {
				$roll_array[ $k ][0] = $total_number;
				if ($v["remaining_number"] > 0) {
					$total_number += $v["remaining_number"];
				}
				$roll_array[ $k ][1] = $total_number;
			}
			if ($total_number == 0) {
				return -2;
			}
			$rand_num = mt_rand(0, $total_number - 1);
			
			if (count($roll_array) > 0) {
				foreach ($roll_array as $k => $v) {
					if ($v[0] <= $rand_num && $rand_num < $v[1]) {
						$result = [
							"rule_id" => $rule_list[ $k ]["rule_id"],
							"type" => $rule_list[ $k ]["type"],
							"coupon_type_id" => $rule_list[ $k ]["coupon_type_id"],
							"points" => $rule_list[ $k ]["points"],
							"hongbao" => $rule_list[ $k ]["hongbao"],
							"gift_id" => $rule_list[ $k ]["gift_id"],
							"rule_name" => $rule_list[ $k ]["rule_name"],
							"type_value" => $rule_list[ $k ]["type_value"]
						];
						return $result;
					}
				}
			}
		}
		return $result;
	}
	
	/**
	 * 添加营销游戏获奖记录
	 * code码 -1：出错，0：未中奖，1：已中奖
	 */
	public function addPromotionGamesWinningRecords($param)
	{
		if (empty($param["uid"])) {
			return array(
				'code' => -1,
				'message' => '缺少参数uid'
			);
		}
		if (empty($param["game_id"])) {
			return array(
				'code' => -1,
				'message' => '缺少参数game_id'
			);
		}
		try {
			$game_winning_model = new NsPromotionGamesWinningRecordsModel(); // 获奖记录
			$games_model = new NsPromotionGamesModel(); // 营销游戏
			$game_rule_model = new NsPromotionGameRuleModel(); // 营销游戏规则
			$user = new UserModel();
			$member_account = new NsMemberAccountModel();
			$member_account_service = new MemberAccount();
			$member_coupon = new MemberCoupon();
			
			$game_winning_model->startTrans();
			
			$game_condition = array();
			$game_condition['game_id'] = $param["game_id"];
			$game_condition['end_time'] = [
				">",
				time()
			];
			$game_info = $games_model->getInfo($game_condition, "name,game_type,points");
			if (empty($game_info)) {
				return array(
					'code' => -1,
					'message' => '游戏不存在，或者已经结束啦'
				);
			}
			
			// 消耗积分
			$member_account_info = $member_account->getInfo([
				'uid' => $param["uid"]
			], "point");
			
			// 剩余积分
			$residual_integral = $residual_integral = $member_account_info['point'] - $game_info['points'];
			if ($residual_integral < 0) {
				return array(
					'code' => -1,
					'message' => '积分不足，无法参与活动'
				);
			}
			
			// 消耗积分
			$member_account_service->addMemberAccountData(0, 1, $param["uid"], 0, -$game_info['points'], 11, $param["game_id"], "参与【" . $game_info['name'] . "】营销游戏消耗" . $game_info['points'] . "积分");
			
			$nick_name = "";
			// 获取用户昵称
			$user_info = $user->getInfo([
				'uid' => $param["uid"]
			], 'nick_name');
			if (!empty($user_info)) {
				if (!empty($user_info['nick_name'])) {
					$nick_name = $user_info['nick_name'];
				}
			}
			
			$is_use = 0; // 使用状态 0：未使用，1：已使用（除了赠品，其余奖项直接已使用）
			$coupon_id = 0; // 赠送优惠券id
			$is_winning = 0; // 是否中奖，0：未中奖，1：已中奖
			$remark = "【" . $user_info['nick_name'] . "】未中奖";
			$associated_gift_record_id = 0; // 关联赠品记录id，后续领取赠品用，默认为0
			$data = array();
			
			// 检测是否中奖
			if ($param["rule_id"] > 0) {
				$is_winning = 1;
				$game_rule_info = $game_rule_model->getInfo([
					'rule_id' => $param["rule_id"],
					'game_id' => $param["game_id"]
				], "rule_id,rule_num,remaining_number,type,coupon_type_id,points,hongbao,gift_id,rule_name");
				if (empty($game_rule_info)) {
					$game_winning_model->rollback();
					return array(
						'code' => -1,
						'message' => '游戏奖项不存在'
					);
				}
				
				// 判断奖品有没有
				if ($game_rule_info['remaining_number'] <= 0) {
					$game_winning_model->rollback();
					return array(
						'code' => -1,
						'message' => '奖品已发放完啦'
					);
				}
				
				$reward_content = ""; // 奖励内容
				if ($game_rule_info['type'] == 1) {
					
					// 送积分
					$reward_content = "奖励" . $game_rule_info['points'] . "积分";
					$member_account_service->addMemberAccountData(0, 1, $param["uid"], 0, $game_rule_info['points'], 11, $param["game_id"], "参与【" . $game_info['name'] . "】营销游戏，获得【" . $game_rule_info['rule_name'] . "】作为奖励，赠送" . $game_rule_info['points'] . "积分");
					
					$is_use = 1;
				} elseif ($game_rule_info['type'] == 2) {
					
					// 送优惠券
					$member_coupon_res = $member_coupon->userAchieveCoupon($param["uid"], $game_rule_info['coupon_type_id'], 4);
					if ($member_coupon_res > 0) {
						$coupon_model = new NsCouponModel();
						$coupon_info = $coupon_model->getInfo([
							"coupon_id" => $member_coupon_res
						], "coupon_type_id");
						$coupon_type_model = new NsCouponTypeModel();
						$coupon_type_info = $coupon_type_model->getInfo([
							"coupon_type_id" => $coupon_info['coupon_type_id']
						], "coupon_name");
						$reward_content = "奖励【" . $coupon_type_info['coupon_name'] . "】";
						$coupon_id = $member_coupon_res;
						$is_use = 1; // 使用状态 0：未使用，1：已使用（除了赠品，其余奖项直接已使用）
					} else {
						$is_winning = 0;
					}
				} elseif ($game_rule_info['type'] == 3) {
					
					// 送红包（余额）
					$is_use = 1; // 使用状态 0：未使用，1：已使用（除了赠品，其余奖项直接已使用）
					$reward_content = "奖励￥" . $game_rule_info['hongbao'] . "余额红包";
					$member_account_service->addMemberAccountData(0, 2, $param["uid"], 0, $game_rule_info['hongbao'], 11, $param["game_id"], "参与【" . $game_info['name'] . "】营销游戏，获得【" . $game_rule_info['rule_name'] . "】作为奖励，赠送" . $game_rule_info['hongbao'] . "余额红包");
				} elseif ($game_rule_info['type'] == 4) {
					
					// 送赠品
					$promotion_gift_model = new NsPromotionGiftModel();
					$promotion_gift_condition = array();
					$promotion_gift_condition['gift_id'] = $game_rule_info['gift_id'];
					$promotion_gift_condition['end_time'] = [
						'>',
						time()
					];
					$promotion_gift_info = $promotion_gift_model->getInfo($promotion_gift_condition, "gift_id,gift_name");
					if (!empty($promotion_gift_info)) {
						
						$promotion_gift_goods_model = new NsPromotionGiftGoodsModel();
						$promotion_gift_goods_info = $promotion_gift_goods_model->getInfo([
							'gift_id' => $promotion_gift_info['gift_id']
						], "goods_name,goods_picture");
						
						// 判断要赠送的商品是否存在
						if (!empty($promotion_gift_goods_info)) {
							
							$type = 2; // 领取类型1.满减2.游戏
							$type_name = "游戏";
							$gift_records_remark = "参与营销游戏送赠品";
							$relate_id = 0; // 中奖之后先记录起来，在个人中心里的中奖记录中继续领取，生成订单
							$reward_content .= "奖励赠品【" . $promotion_gift_goods_info['goods_name'] . "】";
							
							$gift_data = array(
								"shop_id" => 0,
								"uid" => $param["uid"],
								"nick_name" => $nick_name,
								"gift_id" => $promotion_gift_info['gift_id'],
								"gift_name" => $promotion_gift_info['gift_name'],
								"goods_picture" => $promotion_gift_goods_info['goods_picture'],
								"goods_name" => $promotion_gift_goods_info['goods_name'],
								"type" => $type,
								"type_name" => $type_name,
								"relate_id" => $relate_id,
								"remark" => $gift_records_remark,
								"create_time" => time(),
							);
							
							$gift_grant_records_res = $this->addPromotionGiftGrantRecords($gift_data);
							
							if ($gift_grant_records_res['code'] > 0) {
								$associated_gift_record_id = $gift_grant_records_res['code'];
							} else {
								// 赠品发放记录添加异常，未中奖
								$is_winning = 0;
							}
						} else {
							// 要赠送的商品不存在，未中奖
							$is_winning = 0;
						}
					} else {
						
						// 赠品活动已结束，未中奖
						$is_winning = 0;
					}
				}
				// 游戏活动数量减少一次
				$remaining_number = $game_rule_info['remaining_number'] - 1;
				$game_rule_model->save([
					'remaining_number' => $remaining_number
				], [
					'rule_id' => $param["rule_id"],
					'game_id' => $param["game_id"]
				]);
				
				if ($is_winning == 1) {
					$remark = "【" . $nick_name . "】获得" . $game_rule_info['rule_name'] . "，" . $reward_content;
				}
				
				$data['uid'] = $param["uid"]; // 用户id
				$data['shop_id'] = 0; // 店铺id
				$data['is_use'] = $is_use; // 是否使用,除了赠品外，其余的都是已使用
				$data['game_id'] = $param["game_id"]; // 活动id
				$data['game_type'] = $game_info['game_type']; // 游戏类型1.大转盘2.刮刮乐3.九宫格
				$data['type'] = $game_rule_info['type']; // 奖励类型1.积分2.优惠券3.红包4.赠品...
				$data['points'] = $game_rule_info['points']; // 奖励积分
				$data['hongbao'] = $game_rule_info['hongbao']; // 红包数（余额）
				$data['coupon_id'] = $coupon_id; // 奖励优惠券
				$data['gift_id'] = $game_rule_info['gift_id']; // 赠品id
				$data['remark'] = $remark; // 说明
				$data['is_winning'] = $is_winning; // 该次是否中奖 0未中奖 1中奖
				$data['nick_name'] = $nick_name; // 会员昵称
				$data['add_time'] = time(); // 添加时间
				$data['rule_id'] = $game_rule_info['rule_id']; // 奖项id
				$data['associated_gift_record_id'] = $associated_gift_record_id; // 关联赠品记录id，后续领取赠品用，默认为0
			} else {
				
				$data['uid'] = $param["uid"]; // 用户id
				$data['shop_id'] = 0; // 店铺id
				$data['is_use'] = $is_use; // 是否使用,除了赠品外，其余的都是已使用
				$data['game_id'] = $param["game_id"]; // 活动id
				$data['game_type'] = $game_info['game_type']; // 游戏类型1.大转盘2.刮刮乐3.九宫格
				$data['type'] = 0; // 奖励类型1.积分2.优惠券3.红包4.赠品...
				$data['points'] = 0; // 奖励积分
				$data['hongbao'] = 0; // 红包数（余额）
				$data['coupon_id'] = $coupon_id; // 奖励优惠券
				$data['gift_id'] = 0; // 赠品id
				$data['remark'] = $remark; // 说明
				$data['is_winning'] = $is_winning; // 该次是否中奖 0未中奖 1中奖
				$data['nick_name'] = $nick_name; // 会员昵称
				$data['add_time'] = time(); // 添加时间
				$data['rule_id'] = 0; // 奖项id
				$data['associated_gift_record_id'] = $associated_gift_record_id; // 关联赠品记录id，后续领取赠品用，默认为0
			}
			$res = $game_winning_model->save($data);
			
			if ($res > 0) {
				
				$game_winning_model->commit();
				return array(
					'code' => $is_winning,
					'message' => '添加获奖记录成功'
				);
			} else {
				
				$game_winning_model->rollback();
				return array(
					'code' => $is_winning,
					'message' => '添加获奖记录失败'
				);
			}
		} catch (\Exception $e) {
			$game_winning_model->rollback();
		}
	}
	
	/**
	 * 获取用户的中奖记录
	 */
	public function getUserPromotionGamesWinningRecords($page_index, $page_size, $condition, $order = "np_pgwr.id desc")
	{
		$model = new NsPromotionGamesWinningRecordsModel();
		$res = $model->getUserPromotionGamesWinningRecordsViewList($page_index, $page_size, $condition, $order);
		return $res;
	}
	
	/**
	 *  获取活动限制判断 用户是否可以参与该活动
	 */
	public function getPromotionParticipationRestriction($game_id, $uid)
	{
		$gameDetail = $this->getPromotionGameDetail($game_id);
		$join_type = $gameDetail['join_type'];          //参与次数限制类型 0全过程 1每天
		$join_frequency = $gameDetail['join_frequency'];//参与次数
		$winning_type = $gameDetail['winning_type'];    //中奖次数限制类型 0全过程 1每天
		$winning_max = $gameDetail['winning_max'];      //中奖次数
		
		$winningRecords = new NsPromotionGamesWinningRecordsModel();
		$participation_num = $winningRecords->getCount([ "game_id" => $gameDetail['game_id'], "uid" => $uid ]); //该用户已参与次数
		$winning_num = $winningRecords->getCount([ "game_id" => $gameDetail['game_id'], "is_winning" => 1, "uid" => $uid ]); //该用户中奖次数
		$day_begin_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$day_end_time = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
		$today_participation_num = $winningRecords->getCount([ "game_id" => $gameDetail['game_id'], "uid" => $uid, "add_time" => array( "between", [ $day_begin_time, $day_end_time ] ) ]); //当天该用户已参与次数
		$today_winning_num = $winningRecords->getCount([ "game_id" => $gameDetail['game_id'], "is_winning" => 1, "uid" => $uid, "add_time" => array( "between", [ $day_begin_time, $day_end_time ] ) ]); //当天该用户中奖次数
		
		//如果参与次数有限制
		if ($join_frequency > 0) {
			if ($join_type == 0) {
				if ($participation_num >= $join_frequency) {
					return "您已参与过该活动了！去看看其他的吧。";
				} else {
					if ($winning_max > 0) {
						if ($winning_type == 0) {
							if ($winning_num >= $winning_max) {
								return "您已参与过该活动了！去看看其他的吧。";
							}
						} elseif ($winning_type == 1) {
							if ($today_winning_num >= $winning_max) {
								return "您今天已参与过了！明天再来吧。";
							}
						}
					}
				}
			} elseif ($join_type == 1) {
				if ($today_participation_num >= $join_frequency) {
					return "您今天已参与过了！明天再来吧。";
				} else {
					if ($winning_max > 0) {
						if ($winning_type == 0) {
							if ($winning_num >= $winning_max) {
								return "您已参与过该活动了！去看看其他的吧。";
							}
						} elseif ($winning_type == 1) {
							if ($today_winning_num >= $winning_max) {
								return "您今天已参与过了！明天再来吧。";
							}
						}
					}
				}
			}
		} elseif ($join_frequency == 0) {
			//如果参与次数没有限制
			if ($winning_max > 0) {
				if ($winning_type == 0) {
					if ($winning_num >= $winning_max) {
						return "您已参与过该活动了！去看看其他的吧。";
					}
				} elseif ($winning_type == 1) {
					if ($today_winning_num >= $winning_max) {
						return "您今天已参与过了！明天再来吧。";
					}
				}
			}
		}
		return null;
	}
	
	/**
	 * 营销游戏自动执行操作，改变活动状态
	 */
	public function autoPromotionGamesOperation()
	{
		$model = new NsPromotionGamesModel();
		$model->startTrans();
		try {
			$time = time();
			
			//活动开始条件：当前时间大于开始时间，并且活动状态等于0（未开始）
			$condition_start = array(
				'start_time' => array( 'ELT', $time ),
				'status' => 0
			);
			
			//活动结束条件：当前时间大于结束时间，并且活动状态不等于-1（已结束）
			$condition_close = array(
				'end_time' => array( 'LT', $time ),
				'status' => array( 'NEQ', -1 )
			);
			
			$start_count = $model->getCount($condition_start);
			$close_count = $model->getCount($condition_close);
			
			if ($start_count) {
				$model->save([ 'status' => 1 ], $condition_start);
			}
			
			if ($close_count) {
				$model->save([ 'status' => -1 ], $condition_close);
			}
			Cache::clear('promotion_game');
			$model->commit();
			return 1;
		} catch (\Exception $e) {
			$model->rollback();
			return $e->getMessage();
		}
	}
	/*************************************************************营销游戏结束**********************************************/
	
	/*************************************************************专题活动*************************************************/
	
	/**
	 * 添加专题活动
	 */
	public function addPromotionTopic($param)
	{
		$promotion_topic = new NsPromotionTopicModel();
		$promotion_topic->startTrans();
		try {
			$shop_name = $this->instance_name;
			$time = time();
			if ($time < $param["start_time"]) {
				$status = 0;
			} else {
				$status = 1;
			}
			$data = array(
				'topic_name' => $param["topic_name"],
				'keyword' => $param["keyword"],
				'desc' => $param["desc"],
				'picture_img' => $param["picture_img"],
				'scroll_img' => $param["scroll_img"],
				'background_img' => $param["background_img"],
				'background_color' => $param["background_color"],
				'start_time' => $param["start_time"],
				'end_time' => $param["end_time"],
				'shop_id' => 0,
				'shop_name' => $shop_name,
				'introduce' => $param["introduce"],
				'status' => $status,
				'is_head' => $param["is_head"],
				'is_foot' => $param["is_foot"],
				'pc_topic_template' => $param["pc_topic_template"],
				'wap_topic_template' => $param["wap_topic_template"],
				'create_time' => time()
			);
			$promotion_topic->save($data);
			$topic_id = $promotion_topic->topic_id;
			$this->addUserLog($this->uid, 1, '营销', '添加专题', '添加专题活动：' . $param["topic_name"]);
			$goods_id_array = explode(',', $param["goods_id_array"]);
			$promotion_topic_goods = new NsPromotionTopicGoodsModel();
			$promotion_topic_goods->destroy([
				'topic_id' => $topic_id
			]);
			foreach ($goods_id_array as $k => $v) {
				// 添加检测考虑商品在一个时间段内只能有一种活动
				$promotion_topic_goods = new NsPromotionTopicGoodsModel();
				$topic_info = explode(':', $v);
				// 查询商品名称图片
				$goods = new NsGoodsModel();
				$goods_info = $goods->getInfo([
					'goods_id' => $topic_info[0]
				], 'goods_name,picture');
				$data_goods = array(
					'topic_id' => $topic_id,
					'goods_id' => $topic_info[0],
					'goods_name' => $goods_info['goods_name'],
					'goods_picture' => $goods_info['picture']
				);
				$promotion_topic_goods->save($data_goods);
			}
			$promotion_topic->commit();
			return $topic_id;
		} catch (\Exception $e) {
			$promotion_topic->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 修改专题活动
	 */
	public function updatePromotionTopic($param)
	{
		$promotion_topic = new NsPromotionTopicModel();
		$promotion_topic->startTrans();
		try {
			
			$shop_name = $this->instance_name;
			$time = time();
			if ($time < $param["start_time"]) {
				$status = 0;
			} else {
				$status = 1;
			}
			$data = array(
				'topic_name' => $param["topic_name"],
				'keyword' => $param["keyword"],
				'desc' => $param["desc"],
				'picture_img' => $param["picture_img"],
				'scroll_img' => $param["scroll_img"],
				'background_img' => $param["background_img"],
				'background_color' => $param["background_color"],
				'start_time' => $param["start_time"],
				'end_time' => $param["end_time"],
				'shop_id' => 0,
				'shop_name' => $shop_name,
				'introduce' => $param["introduce"],
				'status' => $status,
				'is_head' => $param["is_head"],
				'is_foot' => $param["is_foot"],
				'pc_topic_template' => $param["pc_topic_template"],
				'wap_topic_template' => $param["wap_topic_template"],
				'modify_time' => time()
			);
			$promotion_topic->save($data, [ 'topic_id' => $param["topic_id"] ]);
			
			$this->addUserLog($this->uid, 1, '营销', '修改专题', '修改专题活动：' . $param["topic_name"]);
			$goods_id_array = explode(',', $param["goods_id_array"]);
			$promotion_topic_goods = new NsPromotionTopicGoodsModel();
			$promotion_topic_goods->destroy([
				'topic_id' => $param["topic_id"]
			]);
			foreach ($goods_id_array as $k => $v) {
				// 添加检测考虑商品在一个时间段内只能有一种活动
				$promotion_topic_goods = new NsPromotionTopicGoodsModel();
				$topic_info = explode(':', $v);
				$goods = new NsGoodsModel();
				$goods_info = $goods->getInfo([
					'goods_id' => $topic_info[0]
				], 'goods_name,picture');
				$data_goods = array(
					'topic_id' => $param["topic_id"],
					'goods_id' => $topic_info[0],
					'goods_name' => $goods_info['goods_name'],
					'goods_picture' => $goods_info['picture']
				);
				$promotion_topic_goods->save($data_goods);
			}
			$promotion_topic->commit();
			return $param["topic_id"];
		} catch (\Exception $e) {
			$promotion_topic->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 关闭专题活动
	 */
	public function closePromotionTopic($topic_id)
	{
		$promotion_topic = new NsPromotionTopicModel();
		$promotion_topic->startTrans();
		try {
			$retval = $promotion_topic->save([
				'status' => 3
			], [
				'topic_id' => $topic_id
			]);
			$promotion_topic->commit();
			return $retval;
		} catch (\Exception $e) {
			$promotion_topic->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 删除专题活动
	 */
	public function deletePromotionTopic($topic_id)
	{
		$promotion_topic = new NsPromotionTopicModel();
		$promotion_topic_goods = new NsPromotionTopicGoodsModel();
		$promotion_topic->startTrans();
		try {
			$topic_id_array = explode(',', $topic_id);
			foreach ($topic_id_array as $k => $v) {
				$promotion_detail = $promotion_topic->get($topic_id);
				if ($promotion_detail['status'] == 1) {
					$promotion_topic->rollback();
					return -1;
				}
				$promotion_topic->destroy($v);
				$promotion_topic_goods->destroy([
					'topic_id' => $v
				]);
			}
			$promotion_topic->commit();
			return 1;
		} catch (\Exception $e) {
			$promotion_topic->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 获取专题活动详情
	 */
	public function getPromotionTopicDetail($topic_id)
	{
		$promotion_topic = new NsPromotionTopicModel();
		$promotion_detail = $promotion_topic->get($topic_id);
		
		$promotion_topic_goods = new NsPromotionTopicGoodsModel();
		$promotion_goods_list = $promotion_topic_goods->getQuery([
			'topic_id' => $topic_id
		], '*', 'topic_goods_id desc');
		
		if ($promotion_detail['introduce'] != '') {
			$promotion_detail['introduce'] = htmlspecialchars_decode($promotion_detail['introduce']);
		}
		if (!empty($promotion_goods_list)) {
			foreach ($promotion_goods_list as $k => $v) {
				$goods = new NsGoodsModel();
				$goods_info = $goods->getInfo([
					'goods_id' => $v['goods_id']
				], 'price, stock, picture, point_exchange_type, point_exchange, promotion_price');
				$picture = new AlbumPictureModel();
				$pic_info = array();
				$pic_info['pic_cover'] = '';
				if (!empty($goods_info['picture'])) {
					$pic_info = $picture->get($goods_info['picture']);
				}
				$v['picture_info'] = $pic_info;
				$v['promotion_price'] = $goods_info['promotion_price'];
				$v['point_exchange_type'] = $goods_info['point_exchange_type'];
				$v['point_exchange'] = $goods_info['point_exchange'];
			}
		}
		$promotion_detail['goods_list'] = $promotion_goods_list;
		return $promotion_detail;
	}
	
	/**
	 * 获取专题活动列表
	 */
	public function getPromotionTopicList($page_index = 1, $page_size = 0, $condition = '', $order = 'create_time desc')
	{
		$promotion_topic = new NsPromotionTopicModel();
		$list = $promotion_topic->pageQuery($page_index, $page_size, $condition, $order, '*');
		foreach ($list['data'] as $v) {
			if ($v['introduce'] != '') {
				$v['introduce'] = htmlspecialchars_decode($v['introduce']);
			}
		}
		return $list;
	}
	
	/**
	 * 查询商品在某一时间段是否有专题活动
	 */
	public function getGoodsIsTopic($goods_id, $start_time, $end_time)
	{
		$topic_goods = new NsPromotionTopicGoodsModel();
		$condition_1 = array(
			'start_time' => array(
				'ELT',
				$end_time
			),
			'end_time' => array(
				'EGT',
				$end_time
			),
			'status' => array(
				'NEQ',
				3
			),
			'goods_id' => $goods_id
		);
		$condition_2 = array(
			'start_time' => array(
				'ELT',
				$start_time
			),
			'end_time' => array(
				'EGT',
				$start_time
			),
			'status' => array(
				'NEQ',
				3
			),
			'goods_id' => $goods_id
		);
		$condition_3 = array(
			'start_time' => array(
				'EGT',
				$start_time
			),
			'end_time' => array(
				'ELT',
				$end_time
			),
			'status' => array(
				'NEQ',
				3
			),
			'goods_id' => $goods_id
		);
		$count_1 = $topic_goods->where($condition_1)->count();
		$count_2 = $topic_goods->where($condition_2)->count();
		$count_3 = $topic_goods->where($condition_3)->count();
		$count = $count_1 + $count_2 + $count_3;
		return $count;
	}
	
	/**
	 * 专题活动自动状态
	 */
	public function autoTopicClose()
	{
		$model = new NsPromotionTopicModel();
		$model->startTrans();
		try {
			$time = time();
			
			//活动开始条件：当前时间大于开始时间，并且活动状态等于0（未开始）
			$condition_start = array(
				'start_time' => array( 'ELT', $time ),
				'status' => 0
			);
			
			//活动结束条件：当前时间大于结束时间，并且活动状态不等于4（已结束）
			$condition_close = array(
				'end_time' => array( 'LT', $time ),
				'status' => array( 'NEQ', 4 )
			);
			
			$start_count = $model->getCount($condition_start);
			$close_count = $model->getCount($condition_close);
			
			if ($start_count) {
				$model->save([ 'status' => 1 ], $condition_start);
			}
			
			if ($close_count) {
				$model->save([ 'status' => 4 ], $condition_close);
			}
			Cache::clear('topic');
			$model->commit();
			return 1;
		} catch (\Exception $e) {
			$model->rollback();
			return $e->getMessage();
		}
	}
	
	/**************************************************************专题活动结束*********************************************/
	
	/**
	 * 获取商品收藏详情
	 */
	public function getCollatingGoodsDetail($goods_id)
	{
		$goods = new Goods();
		$curr_goods = $goods->getGoodsDetail($goods_id);
		$default_gallery_img = $curr_goods["img_list"][0]["pic_cover_big"];
		$curr_goods['default_gallery_img'] = $default_gallery_img;
		$spec_list = $curr_goods["spec_list"];
		if (!empty($spec_list)) {
			$album = new Album();
			foreach ($spec_list as $k => $v) {
				foreach ($v["value"] as $t => $m) {
					if ($m["spec_show_type"] == 2) {
						if (is_numeric($m["spec_value_data"])) {
							$picture_detail = $album->getAlubmPictureDetail([
								"pic_id" => $m["spec_value_data"]
							]);
							
							if (!empty($picture_detail)) {
								$spec_list[ $k ]["value"][ $t ]["picture_id"] = $picture_detail['pic_id'];
								$spec_list[ $k ]["value"][ $t ]["spec_value_data"] = $picture_detail["pic_cover_micro"];
								$spec_list[ $k ]["value"][ $t ]["spec_value_data_big_src"] = $picture_detail["pic_cover_big"];
							} else {
								$spec_list[ $k ]["value"][ $t ]["spec_value_data"] = '';
								$spec_list[ $k ]["value"][ $t ]["spec_value_data_big_src"] = '';
								$spec_list[ $k ]["value"][ $t ]["picture_id"] = 0;
							}
						} else {
							$spec_list[ $k ]["value"][ $t ]["spec_value_data_big_src"] = $m["spec_value_data"];
							$spec_list[ $k ]["value"][ $t ]["picture_id"] = 0;
						}
					}
				}
			}
			$curr_goods['spec_list'] = $spec_list;
		}
		return $curr_goods;
	}
	
	/**
	 * 获取商品在一段时间内是否存在单品活动
	 * @param unknown $goods_ids
	 */
	public function getGoodsIdsPromotionIsExist($goods_ids, $start_time, $end_time)
	{
		$goods_promotion_model = new NsGoodsPromotionModel();
		
		$goods_id_array = explode(',', $goods_ids);
		foreach ($goods_id_array as $k => $v) {
			
			$goods = new NsGoodsModel();
			$goods_info = $goods->getInfo([ 'goods_id' => $v ], 'goods_name,is_open_presell,point_exchange_type');
			
			if ($goods_info["is_open_presell"] == 1 || $goods_info["point_exchange_type"] == 3) {
				return error("商品" . $goods_info["goods_name"] . "存在预售或积分兑换,不能再次设置单品活动");
			}
			
			// 添加检测考虑商品在一个时间段内只能有一种活动 单品活动
			$count = $goods_promotion_model->getCount([ "goods_id" => $v, "is_goods_promotion" => 1, "end_time" => [ ">", 0 ], "start_time" => [ ">", 0 ], [ "exp", "NOT ((end_time < " . $start_time . ") OR (start_time > " . $end_time . "))" ] ]);//判断时间段内是否存在交集
			if ($count > 0) {
				return error("商品" . $goods_info["goods_name"] . "存在其他单品活动");
			}
			// 添加检测考虑商品在一个时间段内只能有一种活动  启动性活动
			$count = $goods_promotion_model->getCount([ "goods_id" => $v, "status" => 1, "is_goods_promotion" => 1, "end_time" => 0 ]);//判断时间段内是否存在交集
			if ($count > 0) {
				return error("商品" . $goods_info["goods_name"] . "存在其他单品活动");
			}
		}
		return success();
	}
	
	/**
	 * 获取限时折扣商品列表
	 */
	public function getDialogGoodsList($goods_id_array, $discount_id, $type){
		$dialog_model0 = new NsPromotionDiscountGoodsModel();
		$ns_goods = new NsGoodsModel();
		if (!empty($goods_id_array)) {
			if ($type == "select") {
				$condition["goods_id"] = array(
						"not in",
						$goods_id_array
				);
			} elseif ($type == "selected") {
				$condition["goods_id"] = array(
						"in",
						$goods_id_array
				);
			}
		}
		$list = $ns_goods->pageQuery(1, 0, $condition, "create_time desc", "goods_id,goods_name,stock,promotion_price,price");
		if(!empty($list['data'])){
			foreach ($list['data'] as $key => $val){		
					
				$info = $dialog_model0->getInfo(["discount_id"=>$discount_id, "goods_id"=>$val['goods_id']], "discount");
				$list['data'][$key]['discount'] = $info['discount'];
			}		
		}
		return $list;
	}
}