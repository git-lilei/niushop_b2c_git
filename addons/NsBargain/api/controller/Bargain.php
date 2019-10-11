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

namespace addons\NsBargain\api\controller;

use data\service\User;
use addons\NsBargain\data\service\Bargain as BargainService;
use app\api\controller\BaseApi;

/**
 * 砍价控制器
 */
class Bargain extends BaseApi
{
	
	/**
	 * 发起砍价
	 */
	public function addBargain()
	{
		$title = '发起砍价';
		$bargain = new BargainService();
		$bargain_id = isset($this->params['bargain_id']) ? $this->params['bargain_id'] : 0;
		$sku_id = isset($this->params['sku_id']) ? $this->params['sku_id'] : 0;
		$address_id = isset($this->params['address_id']) ? $this->params['address_id'] : 0;
		$distribution_type = isset($this->params['distribution_type']) ? $this->params['distribution_type'] : "";
		$receiver_mobile = isset($this->params['receiver_mobile']) ? $this->params['receiver_mobile'] : 0;
		$params = [
			'bargain_id' => $bargain_id,
			'sku_id' => $sku_id,
			'address_id' => $address_id,
			'distribution_type' => $distribution_type,
			'receiver_mobile' => $receiver_mobile
		];
		
		$launch_id = $bargain->addBargainLaunch($params);
		$data = array(
			'launch_id' => $launch_id
		);
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 砍价列表
	 */
	public function bargainList()
	{
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$title = '砍价专区';
		$bargain = new BargainService ();
		
		$condition = [
			'status' => 1
		];
		
		$list = $bargain->getBargainGoodsPage($page_index, $page_size, $condition, $order = 'create_time desc');
		
		return $this->outMessage($title, $list);
	}
	
	/**
	 * 砍价商品发起页面
	 */
	public function bargainDetail()
	{
		$title = '砍价活动页';
		
		$launch_id = isset($this->params['launch_id']) ? $this->params['launch_id'] : 0;
		
		if ($launch_id == 0)
			return $this->outMessage($title, '', '-50', "无法获取砍价活动信息");
		
		if ($this->uid == 0 || empty($this->uid))
			return $this->outMessage($title, '', '-9999', "无法获取会员登录信息");
		
		
		$bargain = new BargainService ();
		$user = new User ();
		
		$data = [];
		$launch_info = $bargain->getBargainLaunchInfo($launch_id);
		// 砍价主用户信息
		$user_info = $user->getDetail([ 'uid' => $launch_info['uid'] ], "user_headimg, user_name");
		
		$is_self = 1;
		if ($this->uid != $launch_info['uid']) {
			// 说明是分享出去的砍刀
			$is_self = 0;
		}
		$data ['is_self'] = $is_self;
		// 分享出去的需要手动砍刀
		
		// 砍价的商品信息
		$goods_info = $bargain->getBragainBySkuGoodsInfo($launch_info['bargain_id'], $launch_info ['sku_id']);
		
		$surplus = number_format($launch_info ['goods_money'] - $launch_info ['bargain_money'] - $launch_info ['bargain_min_money'], 2, ".", "");
		$surplus = $surplus == '-0.00' ? 0.00 : $surplus;
		$launch_info ['surplus'] = $surplus;
		
		// 参与该活动的商品详情
		$bargain_goods_info = $bargain->getBargainGoodsInfo($launch_info ['bargain_id'], $launch_info ['goods_id']);
		
		// 参团列表
		$partake_list = $bargain->getBargainPartakeList($launch_id);
		$is_max_partake = $bargain->getBragainLaunchIsPartakeMax($this->uid, $launch_id);
		
		$data ['surplus'] = $surplus;
		$data ['user_info'] = $user_info;
		$data ['launch_info'] = $launch_info;
		$data ['goods_info'] = $goods_info;
		$data ['launch_id'] = $launch_id;
		$data ['bargain_goods_info'] = $bargain_goods_info;
		$data ['partake_list'] = $partake_list;
		$data ['is_max_partake'] = $is_max_partake;
		$data ['current_time'] = time() * 1000;
		
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 砍价配置
	 */
	public function bargainConfig()
	{
		$bargain = new BargainService ();
		$config = $bargain->getConfig();
		return $this->outMessage("砍价配置", $config);
	}
	
	/**
	 * 帮助好友砍价
	 */
	public function helpBargain()
	{
		$title = '帮好友砍价接口';
		if (empty($this->uid))
			return $this->outMessage($title, '', -9999, '无法获取会员登录信息');
		
		$launch_id = isset($this->params['launch_id']) ? $this->params['launch_id'] : 0;
		if ($launch_id == 0)
			return $this->outMessage($title, [ 'data' => -9003 ]);
		
		$bargain = new BargainService();
		// 发起的活动信息
		$res = $bargain->addBargainPartake($launch_id);
		$data = [
			'data' => $res
		];
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 我的砍价
	 */
	public function myBargain()
	{
		$title = '我的砍价';
		if (empty($this->uid))
			return $this->outMessage($title, '', -9999, '无法获取会员登录信息');
		
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$condition = array();
		$condition["uid"] = $this->uid;
		
		// 还要考虑状态逻辑
		$bargain = new BargainService();
		$list = $bargain->getBargainLaunchList($page_index, PAGESIZE, $condition, 'start_time desc');
		$data = [
			'list' => $list,
			'current_time' => time() * 1000,
		];
		return $this->outMessage($title, $data);
		
	}
	
}