<?php
/**
 * O2o.php
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

namespace addons\NsO2o\data\service;

use addons\NsO2o\data\model\NsO2oDistributionConfigModel;
use addons\NsO2o\data\model\NsO2oDistributionUserModel;
use addons\NsO2o\data\model\NsO2oDistributionAreaModel;
use data\service\OrderAction;
use think\Cache;
use addons\NsO2o\data\model\NsO2oOrderDeliveryModel;
use data\model\NsOrderGoodsModel;
use data\service\BaseService;

/**
 * o2o相关服务层
 */
class O2o extends BaseService
{
	/**
	 * 获取配送运费设置
	 */
	public function getDistributionConfig($store_id = 0)
	{
		$cache = Cache::tag('o2o')->get('getDistributionConfig' . $store_id);
		if (!empty($cache)) return $cache;
		
		$model = new NsO2oDistributionConfigModel();
		$distribution_config = $model->getInfo([ 'store_id' => $store_id, 'is_start' => 1 ], '*');
		if (!empty($distribution_config)) {
			$distribution_config['freight_query'] = $model->getQuery([ 'store_id' => $store_id, 'is_start' => 0 ]);
		}
		
		Cache::tag('o2o')->set('getDistributionConfig' . $store_id, $distribution_config);
		
		return $distribution_config;
	}
	
	/**
	 * 设置o2o运费
	 * @param unknown $query 数组形式  里面有（价格，运费，是否是起步）
	 */
	public function setDistributionConfig($query, $store_id = 0)
	{
		Cache::tag('o2o')->set('getDistributionConfig' . $store_id, null);
		
		if (!empty($query)) {
			$model = new NsO2oDistributionConfigModel();
			$model->destroy([ 'store_id' => $store_id ]);
			
			$query = json_decode($query, true);
			foreach ($query as $k => $v) {
				$model = new NsO2oDistributionConfigModel();
				$data = array(
					'order_money' => $v[0],
					'freight' => $v[1],
					'is_start' => $v[2],
				);
				$model->save($data);
			}
			return 1;
		} else {
			return 0;
		}
	}
	
	/**
	 * 配送人员列表
	 */
	public function getDistributionUserList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$cache = Cache::tag('o2o')->get('getDistributionUserList' . json_encode([ $page_index, $page_size, $condition, $order ]));
		if (!empty($cache)) return $cache;
		
		$model = new NsO2oDistributionUserModel();
		$list = $model->pageQuery($page_index, $page_size, $condition, $order, '*');
		Cache::tag('o2o')->set('getDistributionUserList' . json_encode([ $page_index, $page_size, $condition, $order ]), $list);
		return $list;
	}
	
	/**
	 * 配送人员详情
	 */
	public function getDistributionUserInfo($condition = [])
	{
		$cache = Cache::tag('o2o')->get('getDistributionUserInfo' . json_encode($condition));
		if (!empty($cache)) return $cache;
		
		$model = new NsO2oDistributionUserModel();
		$info = $model->getInfo($condition, '*');
		Cache::tag('o2o')->set('getDistributionUserInfo' . json_encode($condition), $info);
		
		return $info;
	}
	
	
	/**
	 * 添加配送人员
	 */
	public function addDistributionUser($name, $mobile, $remark)
	{
		$model = new NsO2oDistributionUserModel();
		$data = array(
			'name' => $name,
			'mobile' => $mobile,
			'remark' => $remark
		);
		$retval = $model->save($data);
		Cache::clear('o2o');
		return $retval;
	}
	
	/**
	 * 修改配送人员
	 */
	public function modifyDistributionUser($id, $name, $mobile, $remark)
	{
		$model = new NsO2oDistributionUserModel();
		$data = array(
			'name' => $name,
			'mobile' => $mobile,
			'remark' => $remark
		);
		$retval = $model->save($data, [ 'id' => $id ]);
		Cache::clear('o2o');
		return $retval;
	}
	
	/**
	 * 删除配送人员
	 */
	public function deleteDistributionUser($id)
	{
		Cache::clear('o2o');
		$model = new NsO2oDistributionUserModel();
		$condition ['id'] = array( 'in', $id );
		$distribution_user_return = $model->destroy($condition);
		
		if ($distribution_user_return > 0) {
			return 1;
		} else {
			return -1;
		}
	}
	
	/**
	 * 获取本地配送地区
	 */
	public function getDistributionAreaInfo($store_id = 0)
	{
		$cache = Cache::tag('o2o')->get('getDistributionAreaInfo' . $store_id);
		if (!empty($cache)) return $cache;
		
		$o2oDistributionArea = new NsO2oDistributionAreaModel();
		$res = $o2oDistributionArea->getInfo([
			'store_id' => $store_id
		], "province_id,city_id,district_id");
		Cache::tag('o2o')->set('getDistributionAreaInfo' . $store_id, $res);
		
		return $res;
	}
	
	/**
	 * 添加本地配送地区
	 */
	public function addOrUpdateDistributionArea($data)
	{
		Cache::clear('o2o');
		$o2oDistributionArea = new NsO2oDistributionAreaModel();
		$res = $this->getDistributionAreaInfo($data['store_id']);
		if ($res == '') {
			return $o2oDistributionArea->save($data);
		} else {
			return $o2oDistributionArea->save($data, [
				'store_id' => $data['store_id']
			]);
		}
	}
	
	/**
	 * o2o订单配送
	 * @param unknown $order_id 订单id
	 * @param unknown $order_delivery_user_id 配送人员id
	 * @param unknown $express_no 配送单号
	 * @param unknown $remark 备注
	 */
	public function O2oOrderDelivery($order_id, $order_delivery_user_id, $express_no, $remark)
	{
		$order_delivery = new NsO2oOrderDeliveryModel(); // 订单配送表
		$delivery_user = new NsO2oDistributionUserModel(); //本地人员配送表
		$order_goods = new NsOrderGoodsModel(); //订单项表
		
		$order_delivery->startTrans(); //开启事务
		
		try {
			$delivery_user_info = $delivery_user->getInfo([ "id" => $order_delivery_user_id ], "*");
			$data = array(
				"express_no" => $express_no,
				"order_id" => $order_id,
				"order_delivery_user_id" => $delivery_user_info['id'],
				"order_delivery_user_name" => $delivery_user_info['name'],
				"order_delivery_user_mobile" => $delivery_user_info['mobile'],
				"status" => 1,
				"remark" => $remark,
			);
			$order_delivery->save($data); //添加配送信息
			
			$order_goods_data = array(
				'shipping_status' => 1
			);
			$order_goods->save($order_goods_data, [ "order_id" => $order_id ]); // 订单项发货 o2o订单订单项发货
			
			$order_action = new OrderAction();
			$order_action->orderDoDelivery($order_id); //订单主表发货
			
			$order_delivery->commit();
			return array(
				"code" => 1,
				"message" => "发货成功"
			);
			
		} catch (\Exception $e) {
			$order_delivery->rollback(); // 事务回滚
			return array(
				"code" => 0,
				"message" => $e->getMessage()
			);
		}
		
	}
}