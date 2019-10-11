<?php
/**
 * AuthGroup.php
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

namespace data\service;

use data\model\NsVirtualGoodsModel;
use data\model\NsVirtualGoodsTypeModel;
use data\service\BaseService as BaseService;
use data\model\NsVirtualGoodsViewModel;
use data\model\AlbumPictureModel;
use data\model\NsVirtualGoodsGroupModel;
use data\model\NsGoodsModel;
use data\model\NsGoodsSkuModel;
use think\Log;
use think\Cache;
use data\model\NsOrderGoodsModel;
use data\model\NsOrderModel;

class VirtualGoods extends BaseService
{
	
	public function __construct()
	{
		parent::__construct();
	}
	

	/**
	 * 添加虚拟商品
	 *
	 * @param 虚拟码 $virtual_code
	 * @param 虚拟商品名称 $virtual_goods_name
	 * @param 金额 $money
	 * @param 买家id $buyer_id
	 * @param 买家昵称 $buyer_nickname
	 * @param 关联订单项id $order_goods_id
	 * @param 订单编号 $order_no
	 * @param 有效期/天(0表示不限制) $validity_period
	 * @param 有效期开始时间 $start_time
	 * @param 有效期结束时间 $end_time
	 * @param 使用次数 $use_number
	 * @param 限制使用次数 $confine_use_number
	 * @param 使用状态 $use_status
	 * @param 商品名称 $goods_id
	 */
	public function addVirtualGoods($shop_id, $virtual_goods_name, $money, $buyer_id, $buyer_nickname, $order_goods_id, $order_no, $validity_period, $start_time, $end_time, $use_number, $confine_use_number, $use_status, $goods_id, $sku_id, $remark)
	{
	    $virtual_goods_model = new NsVirtualGoodsModel();
	
	    $data = array(
	        'virtual_code' => $this->generateVirtualCode($shop_id),
	        'virtual_goods_name' => $virtual_goods_name,
	        'money' => $money,
	        'buyer_id' => $buyer_id,
	        'buyer_nickname' => $buyer_nickname,
	        'order_goods_id' => $order_goods_id,
	        'order_no' => $order_no,
	        'validity_period' => $validity_period,
	        'start_time' => $start_time,
	        'end_time' => $end_time,
	        'use_number' => $use_number,
	        'confine_use_number' => $confine_use_number,
	        'use_status' => $use_status,
	        'shop_id' => $shop_id,
	        'create_time' => time(),
	        'goods_id' => $goods_id,
	        'sku_id'   => $sku_id,
	        'remark' => $remark
	    );
	
	    $res = $virtual_goods_model->save($data);
	    Cache::clear('niu_virtual_goods');
	    return $res;
	}
	
	/**
	 * 修改虚拟商品信息(用于点卡的发卡)
	 * @param unknown $virtual_goods_id
	 * @param unknown $virtual_goods_name
	 * @param unknown $money
	 * @param unknown $buyer_id
	 * @param unknown $buyer_nickname
	 * @param unknown $order_goods_id
	 * @param unknown $order_no
	 * @param unknown $start_time
	 * @param unknown $end_time
	 */
	public function updateVirtualGoods($virtual_goods_id, $virtual_goods_name, $money, $buyer_id, $buyer_nickname, $order_goods_id, $order_no, $start_time, $end_time)
	{
	    Cache::clear('niu_virtual_goods');
	    $virtual_goods_model = new NsVirtualGoodsModel();
	    $data = array(
	        'virtual_goods_name' => $virtual_goods_name,
	        'money' => $money,
	        'buyer_id' => $buyer_id,
	        'buyer_nickname' => $buyer_nickname,
	        'order_goods_id' => $order_goods_id,
	        'order_no' => $order_no,
	        'start_time' => $start_time,
	        'end_time' => $end_time,
	        'use_status' => 1,
	        'create_time' => time(),
	        'use_number' => 1,
	    );
	
	    $res = $virtual_goods_model->save($data, [
	        'virtual_goods_id' => $virtual_goods_id
	    ]);
	    return $res;
	}
	
	/**
	 * 编辑虚拟商品类型
	 *
	 * @param 虚拟商品类型id，0表示添加 $virtual_goods_type_id
	 * @param 关联虚拟商品分组id $virtual_goods_group_id
	 * @param 虚拟商品类型名称 $virtual_goods_type_name
	 * @param 有效期/天(0表示不限制) $validity_period
	 * @param 是否启用（禁用后要查询关联的虚拟商品给予弹出确认提示框） $is_enabled
	 * @param 金额 $money
	 * @param 配置信息(API接口、参数等) $config_info
	 * @param 限制使用次数 $confine_use_number
	 */
	public function editVirtualGoodsType($virtual_goods_type_id, $virtual_goods_group_id, $validity_period, $confine_use_number, $value_info, $goods_id)
	{
	    Cache::clear('niu_virtual_goods_category');
	    $virtual_goods_type_model = new NsVirtualGoodsTypeModel();
	    $res = 0;
	    if ($virtual_goods_type_id == 0) {
	        // 添加
	        $data = array(
	            'virtual_goods_group_id' => $virtual_goods_group_id,
	            'validity_period' => $validity_period,
	            'confine_use_number' => $confine_use_number,
	            'shop_id' => $this->instance_id,
	            'create_time' => time(),
	            'relate_goods_id' => $goods_id
	        );
	        	
	        // 如果不是点卡的话，添加配置信息
	        if ($virtual_goods_group_id != 3) {
	            $data['value_info'] = $value_info;
	        }
	        $res = $virtual_goods_type_model->save($data);
	    } else {
	        	
	        // 修改
	        $data = array(
	            'validity_period' => $validity_period,
	            'confine_use_number' => $confine_use_number,
	            'relate_goods_id' => $goods_id
	        );
	        	
	        // 如果不是点卡的话，添加配置信息
	        if ($virtual_goods_group_id != 3) {
	            $data['value_info'] = $value_info;
	        }
	        $res = $virtual_goods_type_model->save($data, [
	            'virtual_goods_type_id' => $virtual_goods_type_id
	        ]);
	    }
	
	    if ($virtual_goods_group_id == 3) {
	        	
	        if ($value_info != '') {
	            $value_array = json_decode($value_info, true);
	            foreach ($value_array as $item) {
	                $this->addVirtualGoods($this->instance_id, '', 0.00, '', '', 0, '', $validity_period, 0, 0, 0, $confine_use_number, -2, $goods_id, $item['remark']);
	            }
	
	            //更新库存
	            $this->setVirtualCardByGoodsStock($goods_id);
	        }
	    }
	    return $res;
	}
	

	/**
	 * 设置虚拟商品类型是否启用（禁用后要查询关联的虚拟商品给予弹出确认提示框，确认后将商品下架）
	 *
	 * @param 虚拟商品类型id $virtual_goods_type_id
	 * @param 是否启用0/1 $is_enabled
	 */
	public function setVirtualGoodsTypeIsEnabled($virtual_goods_type_id, $is_enabled)
	{
	    Cache::clear('niu_virtual_goods_category');
	    $virtual_goods_type_model = new NsVirtualGoodsTypeModel();
	    $data['is_enabled'] = $is_enabled;
	    $res = $virtual_goods_type_model->save($data, [
	        'virtual_goods_type_id' => $virtual_goods_type_id
	    ]);
	    return $res;
	}
	
	/**
	 * 根据id删除虚拟商品类型
	 *
	 * @param 虚拟商品类型id $virtual_goods_type_id
	 */
	public function deleteVirtualGoodsType($virtual_goods_type_id)
	{
	    Cache::clear('niu_virtual_goods_category');
	    $virtual_goods_type_model = new NsVirtualGoodsTypeModel();
	    $res = $virtual_goods_type_model->destroy([
	        'virtual_goods_type_id' => [
	            'in',
	            $virtual_goods_type_id
	        ]
	    ]);
	    return $res;
	}
	
	/**
	 * 根据id查询虚拟商品类型
	 *
	 * @param unknown $virtual_goods_type_id
	 */
	public function getVirtualGoodsTypeById($virtual_goods_type_id)
	{
	    $cache = Cache::tag('niu_virtual_goods_category')->get('getVirtualGoodsTypeById' . $virtual_goods_type_id);
	    if (!empty($cache)) return $cache;
	
	    $virtual_goods_type_model = new NsVirtualGoodsTypeModel();
	    $res = $virtual_goods_type_model->getInfo([
	        'virtual_goods_type_id' => $virtual_goods_type_id
	    ], "*");
	
	    Cache::tag('niu_virtual_goods_category')->set('getVirtualGoodsTypeById' . $virtual_goods_type_id, $res);
	    return $res;
	}
	
	/**
	 * 获取虚拟商品详情
	 * @param array $condition
	 */
	public function getVirtualGoodsTypeInfo($condition = [])
	{
	    $cache = Cache::tag('niu_virtual_goods_category')->get('getVirtualGoodsTypeInfo' . json_encode($condition));
	    if (!empty($cache)) return $cache;
	
	    $virtual_goods_type_model = new NsVirtualGoodsTypeModel();
	    $res = $virtual_goods_type_model->getInfo($condition, "*");
	
	    Cache::tag('niu_virtual_goods_category')->set('getVirtualGoodsTypeInfo' . json_encode($condition), $res);
	    return $res;
	}
	
	/**
	 * 获取虚拟商品类型列表
	 *
	 * @param 当前页 $page_index
	 * @param 显示页数 $page_size
	 * @param 条件 $condition
	 * @param 排序 $order
	 * @param 字段 $field
	 */
	public function getVirtualGoodsTypeList($page_index, $page_size = 0, $condition = array(), $order = "virtual_goods_type_id desc", $field = "*")
	{
		$cache = Cache::tag('niu_virtual_goods_category')->get('getVirtualGoodsTypeList' . json_encode([ $page_index, $page_size, $condition, $order, $field ]));
		if (!empty($cache)) return $cache;
		
		$virtual_goods_type_model = new NsVirtualGoodsTypeModel();
		$res = $virtual_goods_type_model->pageQuery($page_index, $page_size, $condition, $order, $field);
		
		Cache::tag('niu_virtual_goods_category')->set('getVirtualGoodsTypeList' . json_encode([ $page_index, $page_size, $condition, $order, $field ]), $res);
		return $res;
	}

	
	/**
	 * 生成虚拟码
	 */
	public function generateVirtualCode($shop_id)
	{
		$time_str = date('YmdHis');
		$rand_code = rand(0, 999999);
		
		$virtual_code = $time_str . $rand_code . $shop_id;
		$virtual_code = md5($virtual_code);
		$virtual_code = substr($virtual_code, 16, 32);
		return $virtual_code;
	}
	
	/**
	 * 根据主键id删除虚拟商品
	 * 创建时间：2018年3月6日16:33:57
	 *
	 * @param unknown $virtual_goods_id
	 */
	public function deleteVirtualGoodsById($virtual_goods_id)
	{
		Cache::clear('niu_virtual_goods');
		$virtual_goods_model = new NsVirtualGoodsModel();
		
		$data['virtual_goods_id'] = [
			'in',
			$virtual_goods_id
		];
		$res = $virtual_goods_model->destroy($data);
		return $res;
	}
	
	/**
	 * 根据订单编号查询虚拟商品列表
	 *
	 * @param unknown $order_no
	 */
	function getVirtualGoodsListByOrderNo($order_no)
	{
		$cache = Cache::tag('niu_virtual_goods')->get('getVirtualGoodsListByOrderNo' . $order_no);
		if (!empty($cache)) return $cache;
		
		$virtual_goods_model = new NsVirtualGoodsModel();
		$list = $virtual_goods_model->getQuery([
			"order_no" => $order_no
		], "*", "virtual_goods_id asc");
		if (!empty($list)) {
			
			foreach ($list as $k => $v) {
				if ($v['use_status'] == -1) {
					$list[ $k ]['use_status_msg'] = '已过期';
				} elseif ($v['use_status'] == 0) {
					$list[ $k ]['use_status_msg'] = '未使用';
				} elseif ($v['use_status'] == 1) {
					$list[ $k ]['use_status_msg'] = '已使用';
				}
				$path = $this->getVirtualQecode($v["virtual_goods_id"]);
				$list[ $k ]['path'] = $path;
			}
		}
		
		
		Cache::tag('niu_virtual_goods')->set('getVirtualGoodsListByOrderNo' . $order_no, $list);
		return $list;
	}
	
	/**
	 * 制作核销二维码
	 */
	function getVirtualQecode($virtual_goods_id)
	{
	    $title = '制作核销二维码';
	    if (empty($this->uid)) {
	        return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
	    }
	    $url = __URL(__URL__ . '/wap/Verification/VerificationGooodsToExamine?vg_id=' . $virtual_goods_id);
	    
	    // 查询并生成二维码
	    
	    $upload_path = "upload/qrcode/virtual_qrcode";
	    if (!file_exists($upload_path)) {
	        mkdir($upload_path, 0777, true);
	    }
	    $path = $upload_path . '/virtual_' . $virtual_goods_id . '.png';
	    getQRcode($url, $upload_path, "virtual_" . $virtual_goods_id);
	    return $path;
	}
	
	/**
	 * 获取虚拟商品列表
	 */
	function getVirtualGoodsList($page_index, $page_size, $condition, $order = "")
	{
		$cache = Cache::tag('niu_virtual_goods')->get('getVirtualGoodsList' . json_encode([ $page_index, $page_size, $condition, $order ]));
		if (!empty($cache)) return $cache;
		
		$ns_virtual_goods_view = new NsVirtualGoodsViewModel();
		$list = $ns_virtual_goods_view->getViewList($page_index, $page_size, $condition, $order);
		foreach ($list["data"] as $k => $v) {
			$album_picture = new AlbumPictureModel();
			$picture_info = $album_picture->getInfo([
				"pic_id" => $v["picture"]
			], "pic_cover_mid");
			$picture_info_src = '';
			if (empty($picture_info)) {
				$picture_info_src = '';
			} else {
				$picture_info_src = $picture_info["pic_cover_mid"];
			}
			$list["data"][ $k ]["picture_info"] = $picture_info_src;
			
			$path = $this->getVirtualQecode($v["virtual_goods_id"]);
			$list["data"][ $k ]['path'] = $path;
			
		}
		
		Cache::tag('niu_virtual_goods')->set('getVirtualGoodsList' . json_encode([ $page_index, $page_size, $condition, $order ]), $list);
		return $list;
	}
	
	/**
	 * 根据商品id查询点卡库存（虚拟商品列表）
	 *
	 * @param unknown $page_index
	 * @param unknown $page_size
	 * @param unknown $condition
	 * @param string $order
	 */
	public function getVirtualGoodsListByGoodsId($page_index, $page_size, $condition, $order = "")
	{
		$cache = Cache::tag('niu_virtual_goods')->get('getVirtualGoodsListByGoodsId' . json_encode([ $page_index, $page_size, $condition, $order ]));
		if (!empty($cache)) return $cache;
		
		$ns_virtual_goods_view = new NsVirtualGoodsViewModel();
		$list = $ns_virtual_goods_view->getViewList($page_index, $page_size, $condition, $order);
		
		Cache::tag('niu_virtual_goods')->set('getVirtualGoodsListByGoodsId' . json_encode([ $page_index, $page_size, $condition, $order ]), $list);
		return $list;
	}
	
	/**
	 * 根据商品id查询点卡库存数量
	 *
	 * @param unknown $goods_id
	 */
	public function getVirtualGoodsCountByGoodsId($goods_id)
	{
		$cache = Cache::tag('niu_virtual_goods')->get('getVirtualGoodsCountByGoodsId' . $goods_id);
		if (!empty($cache)) return $cache;
		
		$ns_virtual_goods_view = new NsVirtualGoodsViewModel();
		$res = $ns_virtual_goods_view->getCount([
			'goods_id' => $goods_id
		]);
		
		Cache::tag('niu_virtual_goods')->set('getVirtualGoodsCountByGoodsId' . $goods_id, $res);
		return $res;
	}
	
	/**
	 * 获取虚拟商品分组
	 * @param string $condition
	 */
	public function getVirtualGoodsGroup($condition = '1=1')
	{
		$cache = Cache::tag('niu_virtual_goods_group')->get('getVirtualGoodsGroup' . json_encode($condition));
		if (!empty($cache)) return $cache;
		
		$virtual_group_model = new NsVirtualGoodsGroupModel();
		$list = $virtual_group_model->getQuery($condition, '*', '');
		
		Cache::tag('niu_virtual_goods_group')->set('getVirtualGoodsGroup' . json_encode($condition), $list);
		return $list;
	}
	
	/**
	 * 获取虚拟商品分组详情
	 * @param unknown $virtual_goods_group_id
	 */
	public function getVirtualGoodsGroupInfo($virtual_goods_group_id)
	{
		$cache = Cache::tag('niu_virtual_goods_group')->get('getVirtualGoodsGroupInfo' . $virtual_goods_group_id);
		if (!empty($cache)) return $cache;
		
		$virtual_group_model = new NsVirtualGoodsGroupModel();
		$virtual_goods_group_info = $virtual_group_model->getInfo([ 'virtual_goods_group_id' => $virtual_goods_group_id ], '*');
		
		Cache::tag('niu_virtual_goods_group')->set('getVirtualGoodsGroupInfo' . $virtual_goods_group_id, $virtual_goods_group_info);
		return $virtual_goods_group_info;
	}
	
	/**
	 * 批量添加点卡库存
	 * @param unknown $virtual_goods_type_id
	 * @param unknown $goods_id
	 * @param unknown $virtual_card_json
	 */
	public function addBatchVirtualCard($virtual_goods_type_id, $goods_id, $virtual_card_json)
	{
		Cache::clear('niu_virtual_goods');
		
		$virtual_card_array = json_decode($virtual_card_json, true);
		$virtual_goods_type_info = $this->getVirtualGoodsTypeById($virtual_goods_type_id);
		foreach ($virtual_card_array as $item) {
			$this->addVirtualGoods($this->instance_id, '', 0.00, '', '', 0, '', $virtual_goods_type_info['validity_period'], 0, 0, 0, $virtual_goods_type_info['confine_use_number'], -2, $goods_id, $item['remark']);
		}
		
		//更新商品库存
		$this->setVirtualCardByGoodsStock($goods_id);
		return 1;
	}
	
	/**
	 * 根据点卡库存更新商品库存
	 * @param unknown $goods_id
	 * @param unknown $virtual_goods_type_id
	 */
	public function setVirtualCardByGoodsStock($goods_id)
	{
		Cache::clear('niu_virtual_goods');
		
		$virtual_goods_type_model = new NsVirtualGoodsTypeModel();
		$virtual_goods_type_info = $virtual_goods_type_model->getInfo([ 'relate_goods_id' => $goods_id ], '*');
		
		if ($virtual_goods_type_info['virtual_goods_group_id'] == 3) {
			$virtual_goods_model = new NsVirtualGoodsModel();
			$virtual_count = $virtual_goods_model->getCount([ 'use_status' => -2, 'goods_id' => $goods_id ]);
			
			$goods_model = new NsGoodsModel();
			$res = $goods_model->save([ 'stock' => $virtual_count ], [ 'goods_id' => $goods_id ]);
			
			$goods_sku_model = new NsGoodsSkuModel();
			$res = $goods_sku_model->save([ 'stock' => $virtual_count ], [ 'goods_id' => $goods_id ]);
		}
	}
	
	
	
	
	/**
	 * 虚拟订单，生成虚拟商品
	 * 1、根据订单id查询订单项(虚拟订单项只会有一条数据)
	 * 2、根据购买的商品获取虚拟商品类型信息
	 * 3、根据购买的商品数量添加相应的虚拟商品数量
	 * @param $order_id
	 * @param $buyer_nickname
	 * @param $order_no
	 * @return bool|int
	 */
	public function virtualOrderOperation($order_id, $buyer_nickname, $order_no)
	{
	    $order_goods_model = new NsOrderGoodsModel();
	    $order_goods_model->startTrans();
	    
	    try {
	        
	        // 查询订单项信息
	        $order_goods_items = $order_goods_model->getInfo([
	            'order_id' => $order_id
	        ], 'order_goods_id,goods_id,sku_id, goods_name,buyer_id,num,goods_money,price, shop_id');
	        //订单状态判断  如果状态不是待发货  就停止
	        $order_model = new NsOrderModel();
	        $order_info = $order_model->getInfo(["order_id" => $order_id], "order_status");
	        if($order_info["order_status"] != 1){
	            $order_goods_model->rollback();
	            return 1;
	        }
	        $res = 0;
	        if (! empty($order_goods_items)) {
	            
	            $goods_id = $order_goods_items['goods_id'];
	            $goods_model = new NsGoodsModel();
	            $goods_info = $goods_model->getInfo(['goods_id' => $goods_id], "production_date, shelf_life");
	            
	            // 生成虚拟商品
	            for ($i = 0; $i < $order_goods_items['num']; $i ++) {
	                
	                $validity_period = $goods_info['shelf_life']; // 有效期至
	                $start_time = time();
	                if ($validity_period == 0) {
	                    $end_time = 0;
	                } else {
	                    $end_time = strtotime("+$validity_period days");
	                }
	                $shop_id = $order_goods_items['shop_id'];
	                $data = array(
	                    'virtual_code'  => $this->generateVirtualCode($shop_id),
	                    'goods_id'      => $order_goods_items['goods_id'],
	                    'sku_id'        => $order_goods_items['sku_id'],
	                    'shop_id'       => $order_goods_items['shop_id'],
	                    'virtual_goods_name' => $order_goods_items['goods_name'],
	                    'money'         => $order_goods_items['price'],
	                    'buyer_id'      => $order_goods_items['buyer_id'],
	                    'buyer_nickname'=> $buyer_nickname,
	                    'order_goods_id'=> $order_goods_items['order_goods_id'],
	                    'order_no'      => $order_no,
	                    'validity_period' => $validity_period,
	                    'start_time'    => $start_time,
	                    'end_time'      => $end_time,
	                    'use_number'    => 0,
	                    'confine_use_number'    => 1,
	                    'use_status'    => 0,
	                    'remark'        => '',
	                    'create_time'   => time(),
	                );
	                
	                $virtual_goods_model = new NsVirtualGoodsModel();
	                $res = $virtual_goods_model->save($data);
	            }
	        }
	        
	        //店铺服务自动完成订单
	        $order_action = new OrderAction();
	        $order_action->orderComplete($order_id);
	        
	        $order_goods_model->commit();
	        return 1;
	    }catch (\Exception $e) {
	        $order_goods_model->rollback();
	        return $e->getMessage();
	    }
	    
	}
	
	
}