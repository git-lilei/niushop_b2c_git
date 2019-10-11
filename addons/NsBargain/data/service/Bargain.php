<?php
/**
 * New.php
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
 * @date : 2017年9月18日
 * @version : v1.0.0.0
 */

namespace addons\NsBargain\data\service;

use addons\NsBargain\data\model\NsPromotionBargainGoodsModel;
use addons\NsBargain\data\model\NsPromotionBargainLaunchModel;
use addons\NsBargain\data\model\NsPromotionBargainModel;
use addons\NsBargain\data\model\NsPromotionBargainPartakeModel;
use data\model\NsGoodsModel;
use data\model\NsGoodsPromotionModel;
use data\model\NsGoodsSkuModel;
use data\model\NsGoodsViewModel;
use data\service\Address;
use data\service\Album;
use data\service\BaseService;
use data\service\Config;
use data\service\GoodsCalculate\GoodsCalculate;
use data\service\Member;
use data\service\OrderCreate;
use data\service\User;
use think\Log;
use data\service\Goods;

class Bargain extends BaseService
{
	private $config_key = 'Bargain';
	private $order_type = 7;
	
	/**
	 * 砍价配置信息
	 * @param unknown $is_use 是否启用
	 * @param unknown $activity_time 活动时间
	 * @param unknown $bargain_max_number 最大可砍次数
	 * @param unknown $cut_methods 刀法库
	 * @param unknown $launch_cut_method 发起者刀法名
	 * @param unknown $propaganda 宣传语
	 * @param unknown $rule 规格介绍
	 */
	public function setConfig($is_use, $activity_time, $bargain_max_number, $cut_methods, $launch_cut_method, $propaganda, $rule)
	{
		$value_array = array(
			'activity_time' => $activity_time,
			'bargain_max_number' => $bargain_max_number,
			'cut_methods' => $cut_methods,
			'launch_cut_method' => $launch_cut_method,
			'propaganda' => $propaganda,
			'rule' => $rule
		);
		
		$config_service = new Config();
		$data[0] = array(
			'is_use' => $is_use,
			'instance_id' => $this->instance_id,
			'key' => $this->config_key,
			'value' => $value_array,
			'desc' => '砍价配置信息'
		);
		
		$res = $config_service->setConfig($data);
		return $res;
	}
	
	/**
	 * 获取砍价配置信息
	 */
	public function getConfig()
	{
		$config_service = new Config();
		$config_info = $config_service->getConfig($this->instance_id, $this->config_key);
		
		$config_arr = json_decode($config_info['value'], true);
		$config_arr['is_use'] = $config_info['is_use'];
		
		return $config_arr;
	}
	
	/**
	 * 砍价设置
	 */
	public function setBargain($bargain_id, $bargain_name, $start_time, $end_time, $bargain_min_rate, $bargain_min_number, $one_min_rate, $one_max_rate, $goods_array, $remark = '')
	{
		if(empty($goods_array)) return ['code' => -1, 'message' => '请选择参加活动的商品'];
		$bargain_model = new NsPromotionBargainModel();
		$goods_promotion_model = new NsGoodsPromotionModel();
		$bargain_model->startTrans();

		try {
		    foreach ($goods_array as $goods_id) {
		        $condition = [
		            'goods_id' => $goods_id,
		            'start_time|end_time' => ['between', [getTimeTurnTimeStamp($start_time), getTimeTurnTimeStamp($end_time)]],
		            'is_goods_promotion' => 1
		        ]; 
		        if($bargain_id){
		            $condition['promotion_id'] = ['neq', $bargain_id];
		            $condition['promotion_addon'] = ['neq', 'NsBargain'];
		        }
		        $count = $goods_promotion_model->getCount($condition);
		        if($count){
		            $goods_model = new NsGoodsModel();
		            $goods_info = $goods_model->getInfo(['goods_id' => $goods_id], 'goods_name');
		            return ['code' => -1, 'message' => '商品【'.$goods_info['goods_name'].'】在该时间段内已参与其他活动'];
		        }
		    }
			
			$data = array(
				'bargain_name' => $bargain_name,
				'shop_id' => 0,
				'shop_name' => $this->instance_name,
				'start_time' => getTimeTurnTimeStamp($start_time),
				'end_time' => getTimeTurnTimeStamp($end_time),
				'bargain_min_rate' => $bargain_min_rate,
				'bargain_min_number' => $bargain_min_number,
				'one_min_rate' => $one_min_rate,
				'one_max_rate' => $one_max_rate,
				'remark' => $remark
			);
			
			$time = time();
			if ($time < getTimeTurnTimeStamp($start_time)) {
				$data["status"] = 0;
			} else {
				$data["status"] = 1;
			}
			
			if (empty($bargain_id)) {
				
				$data['create_time'] = time();
				$bargain_model->save($data);
				$result = $bargain_id = $bargain_model->bargain_id;
			} else {
				
				$data['modify_time'] = time();
				$result = $bargain_model->save($data, [ 'bargain_id' => $bargain_id, 'status' => 0 ]);
			}
			
			$this->addBargainGoods($bargain_id, $goods_array);
			
			$bargain_model->commit();
			return [
			    'code' => $result,
			    'message' => '操作成功'
			];
			
		} catch (\Exception $e) {
			$bargain_model->rollback();
			return [
			    'code' => -1,
			    'message' => $e->getMessage()
			];
		}
		
	}
	
	/**
	 * 获取砍价活动信息(基础)
	 */
	public function getBargainInfo($bargain_id, $condition = [])
	{
		
		if (!empty($bargain_id)) $condition['bargain_id'] = $bargain_id;
		
		$bargain_model = new NsPromotionBargainModel();
		$bargain_info = $bargain_model->getInfo($condition, '*');
		
		return $bargain_info;
		
	}
	
	/**
	 * 获取砍价活动详情
	 */
	public function getBargainDetail($bargain_id, $condition = [], $order = '')
	{
		if (!empty($bargain_id)) $condition['bargain_id'] = $bargain_id;
		
		$bargain_model = new NsPromotionBargainModel();
		$bargain_info = $bargain_model->getInfo($condition, '*');
		
		$bargain_info['goods_list'] = $this->getBargainGoodsPage(1, 0, [ 'npbg.bargain_id' => $bargain_id ], $order)['data'];
		return $bargain_info;
	}
	
	/**
	 * 获取砍价活动列表
	 */
	public function getBargainList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '*')
	{
		$bargain_model = new NsPromotionBargainModel();
		$bargain_list = $bargain_model->pageQuery($page_index, $page_size, $condition, $order, $field);
		return $bargain_list;
	}
	
	/**
	 * 删除砍价活动（针对未开始）
	 * @param int $bargain_id
	 */
	public function delBargain($bargain_id)
	{
		$bargain_launch = new NsPromotionBargainLaunchModel();
		$bargain_model = new NsPromotionBargainModel();
		$bargain_goods = new NsPromotionBargainGoodsModel();
		$bargain_id_array = explode(',', $bargain_id);
		
		$list = $bargain_launch->pageQuery(1, 0, ['bargain_id' => ["in",$bargain_id], 'status' => 1 ], "", "*");
		if (!empty($list['data'])) {
			$bargain_info = $bargain_model->getInfo(['bargain_id' => ['in', $bargain_id]]);
			if ($bargain_info['end_time'] > time()) {
				return -1;
			}
		}
		
		$bargain_model->startTrans();
		
		try {
			foreach ($bargain_id_array as $k => $v) {
				if (!empty($v)) {
					$bargain = $bargain_model->getInfo([ 'bargain_id' => $v ]);
					if($bargain['status'] != 1){
						$bargain_model->destroy($v);
						$goods_promotion_model = new NsGoodsPromotionModel();
							
						$bargain_goods->destroy([ 'bargain_id' => $v ]);
							
						$goods_promotion_model->destroy([ 'promotion_id' => $v, 'promotion_addon' => 'NsBargain' ]);//删除公共活动表记录						
					}	
				}
				
			}
			$bargain_model->commit();
			return 1;
			
		} catch (\Exception $e) {
			$bargain_model->rollback();
			return $e->getMessage();
		}
		
	}
	
	/**
	 * 添加砍价活动商品
	 */
	public function addBargainGoods($bargain_id, $goods_array)
	{
		
		//删除此活动下的所有商品
		$bargain_goods_model = new NsPromotionBargainGoodsModel();
		$bargain_goods_model->destroy([ 'bargain_id' => $bargain_id ]);
		
		//获取活动的信息
		$bargain_info = $this->getBargainInfo($bargain_id);
		
		foreach ($goods_array as $item) {
			
			// 查询商品名称图片
			$goods_model = new NsGoodsModel();
			$goods_info = $goods_model->getInfo([ 'goods_id' => $item ], 'goods_name,picture');
			
			$bargain_goods_model = new NsPromotionBargainGoodsModel();
			$data = array(
				'bargain_id' => $bargain_id,
				'goods_id' => $item,
				'goods_name' => $goods_info['goods_name'],
				'goods_picture' => $goods_info['picture'],
				'start_time' => $bargain_info['start_time'],
				'end_time' => $bargain_info['end_time'],
				'bargain_min_rate' => $bargain_info['bargain_min_rate'],
				'one_min_rate' => $bargain_info['one_min_rate'],
				'one_max_rate' => $bargain_info['one_max_rate'],
				'bargain_min_number' => $bargain_info['bargain_min_number']
			);
			$time = time();
			if ($time < getTimeTurnTimeStamp($bargain_info['start_time'])) {
			    $data["status"] = 0;
			} else {
			    $data["status"] = 1;
			}
			
			$bargain_goods_model->save($data);
			$goods_promotion_model = new NsGoodsPromotionModel();
			$goods_promotion_model->destroy([ 'goods_id' => $item, 'promotion_addon' => 'NsBargain' ]);
			$data_goods_promotion = [
				'goods_id' => $item,
				'label' => '砍',
				'remark' => '',
				'status' => 1,
				'is_all' => 0,
				'promotion_addon' => 'NsBargain',
				'promotion_id' => $bargain_id,
				'start_time' => time(),
				'end_time' => 0
			];
			$goods_promotion_model->save($data_goods_promotion);
		}
		
		return 1;
	}
	
	/**
	 * 获取砍价活动商品列表
	 */
	public function getBargainGoodsList($bargain_id, $condition = [])
	{
		if (!empty($bargain_id)) $condition['bargain_id'] = $bargain_id;
		$bargain_goods_model = new NsPromotionBargainGoodsModel();
		$bargain_goods_list = $bargain_goods_model->getQuery($condition);
		
		return $bargain_goods_list;
	}
	
	/**
	 * 砍价商品分页
	 */
	public function getBargainGoodsPage($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '*')
	{
		
		$goods_view = new NsGoodsViewModel();
		$list = $goods_view->getBargainGoodsViewList($page_index, $page_size, $condition, $order);
		return $list;
	}
	
	/**
	 * 获取砍价商品信息
	 */
	public function getBargainGoodsInfo($bargain_id, $goods_id)
	{
		$bargain_goods_model = new NsPromotionBargainGoodsModel();
		$condition = array(
			'bargain_id' => $bargain_id,
			'goods_id' => $goods_id
		);
		
		$info = $bargain_goods_model->getInfo($condition, '*');
		
		$album_service = new Album();
		if (!empty($info)) {
			$info['pic'] = $album_service->getAlbumDetailInfo($info['goods_picture']);
			return $info;
		}
	}
	
	/**
	 * 发起砍价
	 */
	public function addBargainLaunch($params)
	{
		$bargain_launch_model = new NsPromotionBargainLaunchModel();
		$bargain_launch_model->startTrans();
		try {
			//计算发起失效时间
			$config_info = $this->getConfig();
			$activity_time = $config_info['activity_time'] * 60 * 60 * 24;
			$start_time = time();
			
			//获取地址信息
			$member_service = new Member();
			if ($params['distribution_type'] == "logistics") {
				$address_info = $member_service->getMemberExpressAddressDetail($params['address_id']);
				$shipping_type = 1;
				$pick_up_id = 0;
			} elseif ($params['distribution_type'] == "pickup") {
				$address_info = $member_service->getDefaultExpressAddress();
				$shipping_type = 2;
				$pick_up_id = $params['address_id'];
			} elseif ($params['distribution_type'] == "virtual") {
				//虚拟商品砍价不需要地址
				$address_info = [
					'mobile' => $params['receiver_mobile'],
					'province' => '',
					'city' => '',
					'district' => '',
					'address' => '',
					'zip_code' => '',
					'consigner' => ''
				];
				$shipping_type = 0;
				$pick_up_id = 0;
			}
			//获取选择该商品规格的信息
			$goods_sku_model = new NsGoodsSkuModel();
			$goods_sku_info = $goods_sku_model->getInfo([ 'sku_id' => $params['sku_id'] ], 'goods_id, price');
			
			//获取参与该活动的商品信息
			$bargain_goods_info = $this->getBargainGoodsInfo($params['bargain_id'], $goods_sku_info['goods_id']);
			
			//判断是否已经发起了该商品的砍价
			$bargain_launch_info = $this->getBargainLaunchInfo(0, [ 'uid' => $this->uid, 'bargain_id' => $params['bargain_id'], 'status' => 1, 'goods_id' => $goods_sku_info['goods_id'] ]);
			if (!empty($bargain_launch_info)) {
				
				$bargain_launch_model->commit();
				return $bargain_launch_info['launch_id'];
			}
			
			//生成该商品可砍到的最低价格
			
			$goods_money = $goods_sku_info['price'];
			$bargain_min_money = round($goods_money * $bargain_goods_info['bargain_min_rate'] / 100, 2);
			
			$data = array(
				'uid' => $this->uid,
				'bargain_id' => $params['bargain_id'],
				'start_time' => $start_time,
				'end_time' => ($start_time + $activity_time),
				'receiver_mobile' => $address_info['mobile'],
				'receiver_province' => $address_info['province'],
				'receiver_city' => $address_info['city'],
				'receiver_district' => $address_info['district'],
				'receiver_address' => $address_info['address'],
				'receiver_zip' => $address_info['zip_code'],
				'receiver_name' => $address_info['consigner'],
				'sku_id' => $params['sku_id'],
				'goods_money' => $goods_money,
				'bargain_min_number' => $bargain_goods_info['bargain_min_number'],
				'bargain_min_money' => $bargain_min_money,
				'goods_id' => $goods_sku_info['goods_id'],
				'shipping_type' => $shipping_type,
				'pick_up_id' => $pick_up_id
			);
			
			$bargain_launch_model->save($data);
			$launch_id = $bargain_launch_model->launch_id;
			//创建砍价减去商品库存
			$goods_calculate = new GoodsCalculate();
			$res = $goods_calculate->subGoodsStock($goods_sku_info['goods_id'], $params['sku_id'], 1, '');
			if ($res < 0) {
				$bargain_launch_model->rollback();
				return error([], LOW_STOCKS);
			}
			$one_min = ($goods_money - $bargain_min_money) * $bargain_goods_info['one_min_rate'] / 100;
			$one_max = ($goods_money - $bargain_min_money) * $bargain_goods_info['one_max_rate'] / 100;
			
			//自动砍第一刀
			$bargain_money = mt_rand($one_min * 100, $one_max * 100) / 100;
			$this->addBargainPartake($launch_id, $bargain_money);
			
			//更新多少人发起砍价
			$this->setBargainLaunchSales($params['bargain_id'], $goods_sku_info['goods_id']);
			
			$bargain_launch_model->commit();
			
			if ($launch_id) {
				// 砍价发起用户通知
//				runhook("Notify", "bargainLaunchUser", [
//					'launch_id' => $launch_id
//				]);
				message("bargain_launch", [
                    'launch_id' => $launch_id
                ]);
				// 砍价发起商家通知
//				runhook("Notify", "bargainLaunchBusiness", [
//					'launch_id' => $launch_id
//				]);
				message("bargain_launch_business", [
                    'launch_id' => $launch_id
                ]);
			}
			return $launch_id;
		} catch (\Exception $e) {
			
			$bargain_launch_model->rollback();
			Log::write('砍价创建出错' . $e->getMessage());
			return $e->getMessage();
		}
		
	}
	
	/**
	 * 更新多少人发起砍价
	 */
	public function setBargainLaunchSales($bargain_id, $goods_id)
	{
		
		$bargain_launch_model = new NsPromotionBargainLaunchModel();
		$condition = [ 'bargain_id' => $bargain_id, 'goods_id' => $goods_id ];
		$bargain_launch_count = $bargain_launch_model->getCount($condition);
		
		$bargain_model = new NsPromotionBargainGoodsModel();
		$result = $bargain_model->save([ 'sales' => $bargain_launch_count ], $condition);
		return $result;
		
	}
	
	/**
	 * 发起砍价详情
	 */
	public function getBargainLaunchInfo($launch_id, $condition = [])
	{
		
		if (!empty($launch_id)) $condition['launch_id'] = $launch_id;
		
		$launch_model = new NsPromotionBargainLaunchModel();
		$launch_info = $launch_model->getInfo($condition, '*');
		
		return $launch_info;
	}
	
	/**
	 * 添加砍价记录
	 */
	public function addBargainPartake($launch_id, $bargain_money = 0)
	{
		//根据砍价记录
		
		$partake_model = new NsPromotionBargainPartakeModel();
		$partake_model->startTrans();
		
		try {
			$launch_info = $this->getBargainLaunchInfo($launch_id);
			$surplus_money = $launch_info['goods_money'] - $launch_info['bargain_money'] - $launch_info['bargain_min_money'];
			$surplus_money = $surplus_money < 0 ? 0 : $surplus_money;
			
			if (empty($this->uid)) {
				
				$partake_model->rollback();
				return NO_LOGIN;
			}
			//砍价活动已结束的
			if ($launch_info['status'] != 1) {
				
				$partake_model->rollback();
				return BARGAIN_LAUNCH_ALREADY_CLOSE;
			}
			
			$config = $this->getConfig();
			$partake_count = $partake_model->getCount([ 'launch_id' => $launch_id, 'uid' => $this->uid ]);
			if ($partake_count >= $config['bargain_max_number']) {
				$partake_model->rollback();
				return BARGAIN_LAUNCH_MAX_PARTAKE;
			}
			
			//如果没有传入砍掉的金额就计算砍掉的金额
			if (empty($bargain_money)) {
				
				$first_partake = $partake_model->getFirstData([ 'launch_id' => $launch_id ], 'create_time asc');
				if (!empty($first_partake)) {
					$min_price = 0.01;
					$first_partake['bargain_money'] = empty($first_partake['bargain_money']) ? 0 : $first_partake['bargain_money'];
					$launch_info['bargain_min_number'] = $launch_info['bargain_min_number'] == 1 ? 2 : $launch_info['bargain_min_number'];
					$max_price = round(($launch_info['goods_money'] - $first_partake['bargain_money'] - $launch_info['bargain_min_money']) / ($launch_info['bargain_min_number'] - 1), 2);
					
					$bargain_money = mt_rand($min_price * 100, $max_price * 100) / 100;
					$bargain_money = sprintf("%.2f", $bargain_money);   //砍掉的金额
				} else {
					//商品价格太小，砍价金额计算为0，并且没有首次砍刀，则取商品价格
					$bargain_money = $launch_info['goods_money'];
				}
			}
			$bargain_money = ($surplus_money < $bargain_money) ? $surplus_money : $bargain_money;
			
			//随机获取刀法说明
			$config = $this->getConfig();
			
			if ($this->uid == $launch_info['uid']) {
				$remark = $config['launch_cut_method'];
			} else {
				$cut_method_array = explode('，', $config['cut_methods']);
				$cut_method_index = mt_rand(0, count($cut_method_array) - 1);
				$remark = $cut_method_array[ $cut_method_index ];
				$remark = trim($remark);
			}
			
			$data = array(
				'launch_id' => $launch_id,
				'uid' => $this->uid,
				'bargain_money' => $bargain_money,
				'create_time' => time(),
				'remark' => $remark
			);
			$result = $partake_model->save($data);
			$this->setBargainPartakeRecord($launch_id);
			$partake_model->commit();
			return $result;
		} catch (\Exception $e) {
			
			dump($e->getMessage());
			$partake_model->rollback();
			Log::write('wwwwwwwwwwwwwwwwwww' . $e->getMessage());
			return $e->getMessage();
		}
		
	}
	
	/**
	 * 更新发起该活动的砍价记录
	 */
	public function setBargainPartakeRecord($launch_id)
	{
		
		$partake_model = new NsPromotionBargainPartakeModel();
		$sum_bargain_money = $partake_model->getSum([ 'launch_id' => $launch_id ], 'bargain_money');
		$count_bargain_number = $partake_model->getCount([ 'launch_id' => $launch_id ]);
		
		$bargain_launch_model = new NsPromotionBargainLaunchModel();
		$data = array(
			'partake_number' => $count_bargain_number,
			'bargain_money' => $sum_bargain_money
		);
		
		$condition = [ 'launch_id' => $launch_id ];
		
		$bargain_launch_info = $bargain_launch_model->getInfo($condition, '*');
		
		$bargain_money = $bargain_launch_info['goods_money'] - $bargain_launch_info['bargain_min_money'];
		$bargain_money = round($bargain_money, 2);
		$sum_bargain_money = round($sum_bargain_money, 2);
		
		if ($sum_bargain_money >= $bargain_money) {
			
			$data['status'] = 2;
			$this->orderCreate($bargain_launch_info);
		}
		$result = $bargain_launch_model->save($data, $condition);
		return $result;
	}
	
	public function orderCreate($bargain_launch_info)
	{
		
		$goods_id = $bargain_launch_info["goods_id"];
		$goods_model = new NsGoodsModel();
		$goods_info = $goods_model->getInfo([ "goods_id" => $goods_id ], "is_virtual");
		$address_info = [];
		//创建订单
		$order_create = new OrderCreate();
		if ($goods_info["is_virtual"] == 1) {
			$user_telephone = $bargain_launch_info['receiver_mobile'];
		} else {
			//地址
			$address = new Address();
			$address_info = $address->getAddress($bargain_launch_info['receiver_province'], $bargain_launch_info['receiver_city'], $bargain_launch_info['receiver_district']);
			$bargain_launch_info['receiver_address'] = $address_info . '&nbsp;' . $bargain_launch_info['receiver_address'];
			
			$address_info = array();
			$address_info['mobile'] = $bargain_launch_info['receiver_mobile'];
			$address_info['province'] = $bargain_launch_info['receiver_province']; // '收货人所在省',
			$address_info['city'] = $bargain_launch_info['receiver_city']; // '收货人所在城市',
			$address_info['district'] = $bargain_launch_info['receiver_district']; // '收货人所在街道',
			$address_info['address_info'] = $bargain_launch_info['receiver_address']; // '收货人详细地址',
			$address_info['zip_code'] = $bargain_launch_info['receiver_zip']; // '收货人邮编',
			$address_info['consigner'] = $bargain_launch_info['receiver_name']; // '收货人姓名',
			$address_info['phone'] = '';
		}
		
		$bargain_info = array(
			"bargain_info" => array(
				"launch_id" => $bargain_launch_info["launch_id"],
				//	            "address_info" => $address_info
			)
		);
		$shipping_info = array(
			"pick_up_id" => $bargain_launch_info['pick_up_id'],
			"shipping_type" => $bargain_launch_info['shipping_type'],
			"shipping_company_id" => 0
		);
		$order_data = array(
			"order_type" => 1,
			"goods_sku_list" => $bargain_launch_info['sku_id'] . ":1",
			"shipping_info" => $shipping_info,
			"promotion_type" => 3,
			"pay_type" => 1,
			"promotion_info" => $bargain_info,
			"user_money" => 0,
			"buyer_ip" => "0.0.0.0",
			"platform_money" => 0,
			"buyer_invoice" => "",
			"buyer_message" => "",
			"coin" => 0,
			"coupon_id" => 0,
			"point" => 0,
			"address" => $address_info,
		    "buyer_id" => $bargain_launch_info['uid']
		);
		if ($goods_info["is_virtual"] == 1) {
			$order_data["user_telephone"] = $user_telephone;
		}
		$result = $order_create->orderCreate($order_data);
		$order_id = $result["data"]["order_id"];
		$out_trade_no = $result["data"]["out_trade_no"];
		$data['order_id'] = $order_id;
		
		if ($order_id > 0) {
		    //砍价成功  用户通知
			message("bargain_success",  [
                'launch_id' => $bargain_launch_info["launch_id"],
                'order_no' => $out_trade_no,
                'type' => 'success'
            ]);
			//砍价成功  商家通知
			message("bargain_success_business", [
                'launch_id' => $bargain_launch_info["launch_id"],
                'order_no' => $out_trade_no
            ]);
		}
		return $data;
		
	}
	
	/**
	 * 获取发起砍价的列表
	 */
	public function getBargainLaunchList($page_index = 1, $page_size = 0, $condition = '', $order = 'launch_id desc', $field = '*')
	{
		$bargain_launch_model = new NsPromotionBargainLaunchModel();
		$goods_service = new Goods();
		$list = $bargain_launch_model->pageQuery($page_index, $page_size, $condition, $order, $field);
		foreach ($list['data'] as $k => $v) {
			$condition = [ "sku_id" => $v['sku_id'] ];
			$goods_sku_info = $goods_service->getGoodsSkuDetail($condition);
			$list['data'][ $k ]['goods_info'] = $goods_sku_info;
		}
		$user = new User();
		$list['user_info'] = $user->getUserInfoByUid($this->uid);
		return $list;
	}
	
	/**
	 * 获取参与砍价的列表
	 */
	public function getBargainPartakeList($launch_id)
	{
		
		$bargain_partake_model = new NsPromotionBargainPartakeModel();
		$list = $bargain_partake_model->getQuery([ 'launch_id' => $launch_id ], '*', 'create_time desc');
		foreach ($list as $k => $v) {
			$user = new User();
			$list[ $k ]['user_info'] = $user->getUserInfoByUid($v['uid']);
		}
		return $list;
	}
	
	/**
	 * 修改发起砍价的状态 2活动结束 -1取消
	 */
	public function setBargainLaunchStatus($launch_id, $status)
	{
		
		$bargain_launch_model = new NsPromotionBargainLaunchModel();
		$result = $bargain_launch_model->save([ 'status' => $status ], [ 'launch_id' => $launch_id ]);
		return $result;
	}
	
	/**
	 * 通过商品sku获取砍价信息
	 */
	public function getBragainBySkuGoodsInfo($bargain_id, $sku_id)
	{
		$goods_sku_model = new NsGoodsSkuModel();
		$album_service = new Album();
		$goods_sku_info = $goods_sku_model->getInfo([ 'sku_id' => $sku_id ], 'goods_id, price');
		
		//获取参与该活动的商品信息
		$bargain_goods_info = $this->getBargainGoodsInfo($bargain_id, $goods_sku_info['goods_id']);
		
		$bargain_goods_info['pic'] = $album_service->getAlbumDetailInfo($bargain_goods_info['goods_picture']);
		
		$bargain_goods_info['price'] = $goods_sku_info['price'];
		return $bargain_goods_info;
		
	}
	
	/**
	 * 获取当前会员绑好友砍价是否已达最大次数
	 */
	public function getBragainLaunchIsPartakeMax($uid, $launch_id)
	{
		$config_info = $this->getConfig();
		$bargain_partake_model = new NsPromotionBargainPartakeModel();
		$partake_count = $bargain_partake_model->getCount([ 'launch_id' => $launch_id, 'uid' => $uid ]);
		if ($partake_count >= $config_info['bargain_max_number']) {
			return 1;
		} else {
			return 0;
		}
	}
	
	/**
	 * 关闭砍价
	 */
	public function closeBargain($bargain_id, $condition = [])
	{
		
		if (!empty($bargain_id)) $condition['bargain_id'] = $bargain_id;
		
		$bargain_model = new NsPromotionBargainModel();
		$bargain_goods = new NsPromotionBargainGoodsModel();
		$bargain_model->startTrans();
		try {
			$res = $bargain_model->save([ 'status' => 3 ], $condition);
			$goods_res = $bargain_goods->save([ 'status' => 3 ], $condition);
			
			$goods_promotion_model = new NsGoodsPromotionModel();
			
			$goods_promotion_model->destroy([ 'promotion_id' => $bargain_id, 'promotion_addon' => 'NsBargain' ]);
			
			if ($goods_res > 0) {
				$bargain_model->commit();
			} else {
				$bargain_model->rollback();
			}
			return $res;
		} catch (\Exception $e) {
			
			$bargain_model->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 获取订单状态
	 * @return array
	 */
	public function getOrderStatus()
	{
		$status = array(
			array(
				'status_id' => '0',
				'status_name' => '待付款',
				'is_refund' => 0, // 是否可以申请退款
				'operation' => array(
					'0' => array(
						'no' => 'pay',
						'name' => '线下支付',
						'color' => '#FF9800'
					),
					'1' => array(
						'no' => 'close',
						'color' => '#E61D1D',
						'name' => '交易关闭'
					),
					'2' => array(
						'no' => 'adjust_price',
						'color' => '#4CAF50',
						'name' => '修改价格'
					),
					'3' => array(
						'no' => 'seller_memo',
						'color' => '#666666',
						'name' => '备注'
					)
				),
				'member_operation' => array(
					'0' => array(
						'no' => 'pay',
						'name' => '去支付',
						'color' => '#F15050',
						'class_name' => 'ns-bg-color'
					),
					
					'1' => array(
						'no' => 'close',
						'name' => '关闭订单',
						'color' => '#999999',
						'class_name' => 'ns-bg-color-gray-shade-20'
					)
				)
			),
			array(
				'status_id' => '1',
				'status_name' => '待发货',
				'is_refund' => 1,
				'operation' => array(
					'0' => array(
						'no' => 'delivery',
						'color' => 'green',
						'name' => '发货'
					),
					'1' => array(
						'no' => 'seller_memo',
						'color' => '#666666',
						'name' => '备注'
					),
					'2' => array(
						'no' => 'update_address',
						'color' => '#51A351',
						'name' => '修改地址'
					)
				),
				'member_operation' => array()
			),
			array(
				'status_id' => '2',
				'status_name' => '已发货',
				'is_refund' => 1,
				'operation' => array(
					'0' => array(
						'no' => 'seller_memo',
						'color' => '#666666',
						'name' => '备注'
					),
					'1' => array(
						'no' => 'logistics',
						'color' => '#666666',
						'name' => '查看物流'
					),
					'2' => array(
						'no' => 'getdelivery',
						'name' => '确认收货',
						'color' => '#FF6600'
					)
				),
				
				'member_operation' => array(
					'0' => array(
						'no' => 'getdelivery',
						'name' => '确认收货',
						'color' => '#FF6600',
						'class_name' => 'ns-bg-color'
					),
					'1' => array(
						'no' => 'logistics',
						'color' => '#cccccc',
						'name' => '查看物流',
						'class_name' => 'ns-bg-color-gray-shade-20'
					)
				)
			),
			array(
				'status_id' => '3',
				'status_name' => '已收货',
				'is_refund' => 0,
				'operation' => array(
					'0' => array(
						'no' => 'seller_memo',
						'color' => '#666666',
						'name' => '备注'
					),
					'1' => array(
						'no' => 'logistics',
						'color' => '#666666',
						'name' => '查看物流'
					)
				),
				'member_operation' => array(
					'0' => array(
						'no' => 'logistics',
						'color' => '#cccccc',
						'name' => '查看物流',
						'class_name' => 'ns-bg-color-gray-shade-20'
					)
				)
			),
			array(
				'status_id' => '4',
				'status_name' => '已完成',
				'is_refund' => 0,
				'operation' => array(
					'0' => array(
						'no' => 'seller_memo',
						'color' => '#666666',
						'name' => '备注'
					),
					'1' => array(
						'no' => 'logistics',
						'color' => '#666666',
						'name' => '查看物流'
					)
				),
				'member_operation' => array(
					'0' => array(
						'no' => 'logistics',
						'color' => '#cccccc',
						'name' => '查看物流',
						'class_name' => 'ns-bg-color-gray-shade-20'
					)
				)
			),
			array(
				'status_id' => '5',
				'status_name' => '已关闭',
				'is_refund' => 0,
				'operation' => array(
					'0' => array(
						'no' => 'seller_memo',
						'color' => '#666666',
						'name' => '备注'
					),
					'1' => array(
						'no' => 'delete_order',
						'color' => '#ff0000',
						'name' => '删除订单'
					)
				),
				'member_operation' => array(
					'0' => array(
						'no' => 'delete_order',
						'color' => '#ff0000',
						'name' => '删除订单',
						'class_name' => 'ns-bg-color-gray-shade-20'
					)
				)
			),
			array(
				'status_id' => '-1',
				'status_name' => '退款中',
				'is_refund' => 1,
				'operation' => array(
					'0' => array(
						'no' => 'seller_memo',
						'color' => '#666666',
						'name' => '备注'
					)
				),
				'member_operation' => array()
			)
		);
		return $status;
	}
	
	/**
	 * 数据整理
	 */
	public function getOrderPromotionArray($data)
	{
		
		//砍价
		$partake_model = new NsPromotionBargainPartakeModel();
		$sum_bargain_money = $partake_model->getSum([ 'launch_id' => $data["promotion_info"]["bargain_info"]["launch_id"] ], 'bargain_money');
		
		$bargain_launch_model = new NsPromotionBargainLaunchModel();
		
		$bargain_launch_info = $bargain_launch_model->getInfo([ 'launch_id' => $data["promotion_info"]["bargain_info"]["launch_id"] ], '*');
		$bargain_money = $bargain_launch_info['goods_money'] - $bargain_launch_info['bargain_min_money'];
		$bargain_money = round($bargain_money, 2);
		$sum_bargain_money = round($sum_bargain_money, 2);
		//砍价是否打成
		
		//是否满足可以生成砍价订单的条件
//         if ($sum_bargain_money < $bargain_money || !($bargain_launch_info["end_time"] < time() && $bargain_launch_info["status"] == 1) || $bargain_launch_info["status"] == 2) {
// 	        return error();
// 	    }
		
		$temp_array = $data["goods_sku_array"][0];
		
		$temp_array["promotion_price"] = $sum_bargain_money;
		$temp_array["discount_money"] = $sum_bargain_money;
		$temp_array["promotion_id"] = $data["promotion_info"]["bargain_info"]["launch_id"];
		$temp_array["promotion_type"] = 'BARGAIN';
		$discount_List[] = $temp_array;
		
		$promotion_array[0] = array(
			'promotion_id' => $data["promotion_info"]["bargain_info"]["launch_id"],
			'promotion_type_id' => 4,
			'promotion_type' => 'BARGAIN',
			'promotion_condition' => '',
			'promotion_name' => '砍价活动',
			'discount_money' => $sum_bargain_money,
			'promotion_sku_list' => $discount_List,
			'free_shipping' => 1//是否免邮
		);
		
		return success($promotion_array);
	}
	
	/**
	 * 砍价操作
	 */
	public function bargainOperation()
	{
		$bargain = new NsPromotionBargainModel();
		$bargain->startTrans();
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
			$bargain->save([ 'status' => 4 ], $condition_close);
			
			$bargain_goods = new NsPromotionBargainGoodsModel();
			$bargain_goods->save([ 'status' => 4 ], $condition_close);
			
			//只有砍价配置是开启的状态下才能开启砍价活动
			$bargain_service = new Bargain();
			$config = $bargain_service->getConfig();
			if ($config['is_use'] == 1) {
				$bargain->save([ 'status' => 1 ], $condition_start);
				$bargain_goods->save([ 'status' => 1 ], $condition_start);
			}
			
			$bargain_launch = new NsPromotionBargainLaunchModel();
			
			$condition_close_launch = array(
				'end_time' => array( 'LT', $time ),
				'status' => 1
			);
			
			$list = $bargain_launch->getQuery($condition_close_launch, 'launch_id,end_time, bargain_money,sku_id, launch_id, receiver_mobile, receiver_province, receiver_city, receiver_district, receiver_address, receiver_zip, receiver_name, uid, shipping_type, pick_up_id', '');
			
			foreach ($list as $item) {
				//创建订单
				$res = $bargain_service->orderCreate($item);
				
				$bargain_launch = new NsPromotionBargainLaunchModel();
				$bargain_launch->save([ 'status' => 2, 'order_id' => $res['order_id'] ], [ 'launch_id' => $item['launch_id'] ]);
			}
			
			$bargain->commit();
			return 1;
		} catch (\Exception $e) {
			$bargain->rollback();
			return $e->getMessage();
		}
	}
}