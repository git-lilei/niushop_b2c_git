<?php
/**
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
namespace data\model;
use data\model\BaseModel as BaseModel;
/**
 * 优惠券类型表
 * @author Administrator
 *
 */
class NsCouponTypeModel extends BaseModel {

    protected $table = 'ns_coupon_type';
    protected $rule = [
        'coupon_type_id'  =>  '',
    ];
    protected $msg = [
        'coupon_type_id'  =>  '',
    ];
    /**
     * 获取商品对应优惠券列表
     * @param unknown $goods_id
     * @param unknown $uid
     */
    public function getCouponTypeListByGoods($goods_id)
    {
    
        $list = $this->alias('nct')
        ->join('ns_coupon_goods ncg',' nct.coupon_type_id = ncg.coupon_type_id','left')
        ->field(' nct.coupon_type_id, nct.shop_id, nct.coupon_name, nct.money, nct.count, nct.max_fetch, nct.at_least, nct.need_user_level, nct.range_type, nct.is_show, nct.start_time, nct.end_time, nct.create_time, nct.update_time, nct.term_of_validity_type, nct.fixed_term')
        ->where('(ncg.goods_id = '.$goods_id.' OR nct.range_type = 1) AND nct.is_show = 1 AND (nct.start_time <= UNIX_TIMESTAMP(NOW()) AND nct.end_time >= UNIX_TIMESTAMP(NOW()) OR nct.term_of_validity_type = 1)')->select();
        return $list;
    }
    
    /**
     * 商品详情页所显示的优惠券
     * @param unknown $goods_id
     * @return unknown
     */  
    public function getCouponTypeListByGoodsdetail($goods_id)
    {
    
    	$list = $this->alias('nct')
    	->join('ns_coupon_goods ncg',' nct.coupon_type_id = ncg.coupon_type_id','left')
    	->field(' nct.coupon_type_id, nct.shop_id, nct.coupon_name, nct.money, nct.count, nct.max_fetch, nct.at_least, nct.need_user_level, nct.range_type, nct.is_show, nct.start_time, nct.end_time, nct.create_time, nct.update_time, nct.term_of_validity_type, nct.fixed_term')
    	->where('(ncg.goods_id = '.$goods_id.' OR nct.range_type = 1) AND (nct.start_time <= UNIX_TIMESTAMP(NOW()) AND nct.end_time >= UNIX_TIMESTAMP(NOW()) OR nct.term_of_validity_type = 1 AND is_end = 0 AND nct.is_show = 1)')->select();
    	return $list;
    }

}