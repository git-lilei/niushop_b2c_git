<?php
/**
 * MemberCoupon.php
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

namespace data\service\Member;

/**
 * 会员流水账户
 */
use data\model\NsCouponModel as NsCouponModel;
use data\model\NsCouponTypeModel;
use data\service\BaseService;

class MemberCoupon extends BaseService
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * 使用优惠券
	 */
	public function useCoupon($uid, $coupon_id, $order_id)
	{
		$coupon = new NsCouponModel();
		$data = array(
			'use_order_id' => $order_id,
			'state' => 2,
			'use_time' => time()
		);
		$res = $coupon->save($data, [
			'coupon_id' => $coupon_id,
			'uid' => $uid
		]);
		return $res;
	}
	
	/**
	 * 会员领取优惠券
	 * @param unknown $uid
	 * @param unknown $coupon_type_id
	 * @param unknown $get_type 1.订单  2.首页领取  3.商品详情页领取 4.营销活动获取
	 */
	public function userAchieveCoupon($uid, $coupon_type_id, $get_type)
	{
		$coupon_type_model = new NsCouponTypeModel();
		$coupon_model = new NsCouponModel();
		
		$coupon_type_info = $coupon_type_model->getInfo([ 'coupon_type_id' => $coupon_type_id ], '*');
		if(empty($coupon_type_info)){
			return COUPON_NO_EXIST;
		}
		if ($coupon_type_info['is_end']){
			return NO_COUPON;
		}
		
		$fetched_count = $coupon_model->getCount([ 'coupon_type_id' => $coupon_type_id, 'uid' => $uid ]);
		$max_fetch = $coupon_type_info['max_fetch'];
		if ($max_fetch != 0 && ($fetched_count >= $max_fetch) && ($get_type != 1 && $get_type != 4)){
			return FULL_MAX_FETCH;
		}
		
		$coupon_type_model->startTrans();
		try {
			// 优惠券领取
			$data = array(
				'uid' => $uid,
				'state' => 1,
				'get_type' => $get_type,
				'fetch_time' => time()
			);
			if ($coupon_type_info['term_of_validity_type'] == 1) {
				$data['start_time'] = time();
				$data['end_time'] = time() + ($coupon_type_info['fixed_term'] * 24 * 60 * 60);
			}
			$coupon_model->where([ 'coupon_type_id' => $coupon_type_id, 'uid' => 0 ])->limit(1)->update($data);
			// 变更优惠券表领取数量
			$coupon_type_data = [ 'get_num' => ($coupon_type_info['get_num'] + 1) ];
			if ($coupon_type_data['get_num'] == $coupon_type_info['count']) {
				$coupon_type_data['is_end'] = 1;
			}
			$coupon_type_model->save($coupon_type_data, [ 'coupon_type_id' => $coupon_type_id ]);
			$coupon_type_model->commit();
			return 1;
		} catch (\Exception $e) {
			$coupon_type_model->rollback();
			return -1;
		}
	}
	
	/**
	 * 订单返还会员优惠券
	 */
	public function UserReturnCoupon($coupon_id)
	{
		$coupon = new NsCouponModel();
		$data = array(
			'state' => 1
		);
		$retval = $coupon->save($data, [
			'coupon_id' => $coupon_id
		]);
		return $retval;
	}
	
	/**
	 * 获取优惠券金额
	 */
	public function getCouponMoney($coupon_id)
	{
		$coupon = new NsCouponModel();
		$money = $coupon->getInfo([
			'coupon_id' => $coupon_id
		], 'money');
		if (!empty($money['money'])) {
			return $money['money'];
		} else {
			return 0;
		}
	}
	
	/**
	 * 查询当前会员优惠券列表 1已领用（未使用） 2已使用 3已过期
	 */
	public function getUserCouponList($type = '', $shop_id = '')
	{
		$time = time();
		$condition['nc.uid'] = $this->uid;
		switch ($type) {
			case 1:
				
				// 未使用，已领用,未过期
				// $condition['start_time'] = array('ELT', $time);
				$condition['nc.end_time'] = array(
					'GT',
					$time
				);
				$condition['nc.state'] = 1;
				break;
			case 2:
				
				// 已使用
				$condition['nc.state'] = 2;
				break;
			case 3:
				
				// $condition['end_time'] = array('ELT', $time);
				$condition['nc.state'] = 3;
				break;
		}
		if (!empty($shop_id)) {
			$condition['nc.shop_id'] = $shop_id;
		}
		$coupon = new NsCouponModel();
		$coupon_list = $coupon->alias('nc')
			->join('ns_coupon_type nct', 'nc.coupon_type_id = nct.coupon_type_id', 'inner')
			->field('nc.coupon_id, nc.coupon_code, nc.uid, nc.money, nc.state, nc.get_type, nc.fetch_time, nc.use_time, nc.start_time, nc.end_time, nct.coupon_name, nct.at_least, nct.range_type, nct.term_of_validity_type, nct.fixed_term')->where($condition)->order('nc.fetch_time desc')->select();
		
		return $coupon_list;
	}
	
	public function getUserCouponCount($type = '', $shop_id = '')
	{
		$time = time();
		$condition['uid'] = $this->uid;
		switch ($type) {
			case 1:
				// 未使用，已领用,未过期
				// $condition['start_time'] = array('ELT', $time);
				$condition['end_time'] = array(
					'GT',
					$time
				);
				$condition['state'] = 1;
				break;
			case 2:
				
				// 已使用
				$condition['state'] = 2;
				break;
			case 3:
				
				// $condition['end_time'] = array('ELT', $time);
				$condition['state'] = 3;
				break;
		}
		if (!empty($shop_id)) {
			$condition['shop_id'] = $shop_id;
		}
		$coupon = new NsCouponModel();
		$count = $coupon->getCount($condition);
		
		return $count;
	}
}