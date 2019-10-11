<?php
/**
 * Shop.php
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
 * 店铺服务层
 */
use data\model\ConfigModel;
use data\model\NsGoodsFloorModel;
use data\model\NsMemberWithdrawSettingModel;
use data\model\NsNoticeModel;
use data\model\NsPickupPointModel;
use data\model\NsPlatformAdvModel;
use data\model\NsPlatformAdvPositionModel;
use data\model\NsPlatformAdvViewModel;
use data\model\NsPlatformHelpClassModel;
use data\model\NsPlatformHelpDocumentModel;
use data\model\NsPlatformLinkModel;
use data\model\NsShopAdModel as NsShopAdModel;
use data\model\NsShopNavigationModel;
use data\model\NsShopNavigationTemplateModel;
use data\model\NsShopRecommendModel;
use data\model\NsShopWeixinShareModel;
use think\Cache;

class Shop extends BaseService
{
	
	/*****************************************************店铺轮播**********************************************************/
	
	/**
	 * 添加店铺广告
	 */
	public function addShopAd($data)
	{
		$shop_ad = new NsShopAdModel();
		$shop_ad->save($data);
		$id = $shop_ad->id;
		return $id;
	}
	
	/**
	 * 修改店铺轮播图
	 */
	public function updateShopAd($data)
	{
		$shop_ad = new NsShopAdModel();
		$res = $shop_ad->save($data, [
			'id' => $data["id"]
		]);
		return $res;
	}
	
	/**
	 * 删除店铺轮播图
	 */
	public function deleteShopAd($id)
	{
		$shop_ad = new NsShopAdModel();
		$res = $shop_ad->destroy([
			'id' => $id,
			'shop_id' => 0
		]);
		return $res;
	}
	
	/**
	 * 获取店铺轮播图详情
	 */
	public function getShopAdDetail($id)
	{
		$shop_ad = new NsShopAdModel();
		$info = $shop_ad->get($id);
		return $info;
	}
	
	/**
	 * 获取店铺轮播图列表
	 */
	public function getShopAdList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$shop_ad = new NsShopAdModel();
		$list = $shop_ad->pageQuery($page_index, $page_size, $condition, $order, '*');
		return $list;
	}
	/*************************************************************店铺轮播结束***********************************************/
	
	/*************************************************************店铺导航**************************************************/
	/**
	 * 店铺导航添加
	 */
	public function addShopNavigation($data)
	{
		Cache::clear("niu_shop_navigation");
		$shop_navigation = new NsShopNavigationModel();
		$shop_navigation->save($data);
		$retval = $shop_navigation->nav_id;
		return $retval;
	}
	
	/**
	 * 店铺导航修改
	 */
	public function updateShopNavigation($data)
	{
		Cache::clear("niu_shop_navigation");
		$shop_navigation = new NsShopNavigationModel();
		$shop_navigation->save($data, [
			'nav_id' => $data["nav_id"]
		]);
		return $data["nav_id"];
	}
	
	/**
	 * 店铺导航删除
	 */
	public function deleteShopNavigation($nav_id)
	{
		Cache::clear("niu_shop_navigation");
		$shop_navigation = new NsShopNavigationModel();
		$retval = $shop_navigation->destroy($nav_id);
		return $retval;
	}
	
	/**
	 * 修改导航排序
	 */
	public function modifyShopNavigationSort($nav_id, $sort)
	{
		Cache::clear("niu_shop_navigation");
		$shop_navigation = new NsShopNavigationModel();
		$retval = $shop_navigation->save([
			'sort' => $sort
		], [
			'nav_id' => $nav_id
		]);
		return $retval;
	}
	
	/**
	 * 查询店铺导航详情
	 */
	public function shopNavigationDetail($nav_id)
	{
		Cache::tag("niu_shop_navigation")->get("shopNavigationDetail" . $nav_id);
		// if(empty($cache))
		// {
		$shop_navigation = new NsShopNavigationModel();
		$info = $shop_navigation->get($nav_id);
		Cache::tag("niu_shop_navigation")->set("shopNavigationDetail" . $nav_id, $info);
		return $info;
		// }else{
		// return $info;
		// }
	}
	
	/**
	 * 导航列表
	 */
	public function shopNavigationList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$data = array(
			$page_index,
			$page_size,
			$condition,
			$order
		);
		$data = json_encode($data);
		$cache = Cache::tag("niu_shop_navigation")->get("ShopNavigationList" . $data);
		if (empty($cache)) {
			$shop_navigation = new NsShopNavigationModel();
			$list = $shop_navigation->pageQuery($page_index, $page_size, $condition, $order, '*');
			Cache::tag("niu_shop_navigation")->set("ShopNavigationList" . $data, $list);
			return $list;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 获取导航商城模块
	 */
	public function getShopNavigationTemplate($use_type)
	{
		$template_model = new NsShopNavigationTemplateModel();
		$template_list = $template_model->getQuery([
			"is_use" => 1,
			"use_type" => array( "in", $use_type )
		]);
		return $template_list;
	}
	
	/******************************************************店铺导航结束******************************************************/
	
	/******************************************************店铺分享设置******************************************************/
	
	/**
	 * 修改店铺分享设置
	 */
	public function updateShopShareConfig($data)
	{
		$shop_share = new NsShopWeixinShareModel();
		$retval = $shop_share->save($data, [
			'shop_id' => 0
		]);
		return $retval;
	}
	
	/**
	 * 获取店铺分享设置
	 */
	public function getShopShareConfig()
	{
		$shop_share = new NsShopWeixinShareModel();
		$info = $shop_share->getinfo([ 'shop_id' => 0 ]);
		if (empty($info)) {
			$data = array(
				'shop_id' => 0,
				'goods_param_1' => '优惠价：',
				'goods_param_2' => '全场正品',
				'shop_param_1' => '欢迎打开',
				'shop_param_2' => '分享赚佣金',
				'shop_param_3' => '',
				'qrcode_param_1' => '向您推荐',
				'qrcode_param_2' => '注册有优惠'
			);
			$shop_share->save($data);
			$info = $shop_share->getInfo([ 'shop_id' => 0 ]);
		}
		return $info;
	}
	
	/********************************************************店铺分享结束*****************************************************/
	
	
	/********************************************************店铺会员提现*****************************************************/
	
	/**
	 * 添加会员提现设置
	 */
	public function addMemberWithdrawSetting($data)
	{
		$member_withdraw_setting = new NsMemberWithdrawSettingModel();
		$member_withdraw_setting->save($data);
		return $member_withdraw_setting->id;
	}
	
	/**
	 * 修改会员提现设置
	 */
	public function updateMemberWithdrawSetting($data)
	{
		$member_withdraw_setting = new NsMemberWithdrawSettingModel();
		$retval = $member_withdraw_setting->save($data, array(
			"shop_id" => 0,
			"id" => $data["id"]
		));
		return $retval;
	}
	
	/**
	 * 获取提现设置信息
	 */
	public function getWithdrawInfo()
	{
		$member_withdraw_setting = new NsMemberWithdrawSettingModel();
		$info = $member_withdraw_setting->getInfo([
			"shop_id" => 0
		]);
		
		return $info;
	}
	
	/********************************************************店铺会员提现结束**************************************************/
	
	/********************************************************店铺自提点管理****************************************************/
	
	/**
	 * 自提点添加
	 */
	public function addPickupPoint($data)
	{
		$pickup_point_model = new NsPickupPointModel();
		$data["create_time"] = time();
		$pickup_point_model->save($data);
		return $pickup_point_model->id;
	}
	
	/**
	 * 自提点修改
	 */
	public function updatePickupPoint($data, $condition)
	{
		$pickup_point_model = new NsPickupPointModel();
		$retval = $pickup_point_model->save($data, $condition);
		return $retval;
	}
	
	/**
	 * 删除自提点
	 */
	public function deletePickupPoint($pickip_id)
	{
		$pickup_point_model = new NsPickupPointModel();
		$retval = $pickup_point_model->destroy($pickip_id);
		return $retval;
	}
	
	/**
	 * 获取自提点详情
	 */
	public function getPickupPointDetail($pickip_id)
	{
		$pickup_point_model = new NsPickupPointModel();
		$pickup_point_detail = $pickup_point_model->get($pickip_id);
		return $pickup_point_detail;
	}
	
	/**
	 * 自提点列表
	 */
	public function getPickupPointQuery($condition)
	{
		$pickup_point_model = new NsPickupPointModel();
		$list = $pickup_point_model->getQuery($condition);
		if (!empty($list)) {
			$address = new Address();
			foreach ($list as $k => $v) {
				$list[ $k ]['province_name'] = $address->getProvinceName($v['province_id']);
				$list[ $k ]['city_name'] = $address->getCityName($v['city_id']);
				$list[ $k ]['district_name'] = $address->getDistrictName($v['district_id']);
			}
		}
		return $list;
	}
    
    /**
     * 超商
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Collection
     */
	public function getPickupPointQueryCS($condition)
    {
        $key = implode('_', $condition);
        $list = Cache::tag("shop_express")->get($key);
        if ($list) {
            return $list;
        }
        $pickup_point_model = new NsPickupPointModel();
        $list = $pickup_point_model->getQuery($condition);
        if (!empty($list)) {
            $address = new Address();
            foreach ($list as $k => $v) {
                $list[ $k ]['province_name'] = $address->getProvinceName($v['province_id']);
                $list[ $k ]['city_name'] = $address->getCityName($v['city_id']);
                $list[ $k ]['district_name'] = $address->getDistrictName($v['district_id']);
            }
        }
        Cache::tag('shop_express')->set($key, $list);
        return $list;
    }
	
	/**
	 * 自提点列表
	 */
	public function getPickupPointList($page_index = 1, $page_size = 0, $where = '', $order = '')
	{
		$pickup_point_model = new NsPickupPointModel();
		$list = $pickup_point_model->pageQuery($page_index, $page_size, $where, $order, '*');
		if (!empty($list)) {
			$address = new Address();
			foreach ($list['data'] as $k => $v) {
				$list['data'][ $k ]['province_name'] = $address->getProvinceName($v['province_id']);
				$list['data'][ $k ]['city_name'] = $address->getCityName($v['city_id']);
				$list['data'][ $k ]['district_name'] = $address->getDistrictName($v['district_id']);
			}
		}
		return $list;
	}
	
	/*******************************************************自提点管理结束****************************************************/
	
	/*******************************************************手机端首页推荐****************************************************/
	
	/**
	 * 添加店铺商品推荐
	 */
	public function addGoodsRecommend($data)
	{
		Cache::clear("niu_goods_wap_block");
		$shop_recommend = new NsShopRecommendModel();
		$res = $shop_recommend->save($data);
		return $res;
	}
	
	/**
	 * 修改店铺商品推荐
	 */
	public function updateGoodsRecommend($data)
	{
		Cache::clear("niu_goods_wap_block");
		$shop_recommend = new NsShopRecommendModel();
		$res = $shop_recommend->save($data, [ 'id' => $data["id"] ]);
		return $res;
	}
	
	/**
	 * 商品推荐条幅
	 */
	public function modifyRecommendImg($id, $img)
	{
		Cache::clear("niu_goods_wap_block");
		$model = new NsShopRecommendModel();
		$res = $model->save([ 'img' => $img ], [ 'id' => $id ]);
		return $res;
	}
	
	/**
	 * 删除店铺商品推荐
	 */
	public function deleteGoodsRecommend($recommend_id)
	{
		Cache::clear("niu_goods_wap_block");
		if (empty($recommend_id)) return -1;
		$recommend = new NsShopRecommendModel();
		$res = $recommend->destroy([ 'id' => $recommend_id ]);
		return $res;
	}
	
	/**
	 * 获取店铺商品推荐
	 */
	public function getGoodsRecommend($page_index = 1, $page_size = 0, $condition = [], $order = '', $field = '*')
	{
		$list = Cache::tag("niu_goods_wap_block")->get("getGoodsRecommend");
		$recommend = new NsShopRecommendModel();
		if (empty($list)) {
			$list = $recommend->pageQuery($page_index, $page_size, $condition, $order, $field);
			Cache::tag("niu_goods_wap_block")->set("getGoodsRecommend", $list);
		}
		$goods = new Goods();
		$goods_group = new GoodsGroup();
		foreach ($list['data'] as $k => $v) {
			if ($v['type'] == 1) {
				//标签
				if (!empty($v['alis_id'])) {
					$info = $goods_group->getGoodsGroupDetail($v['alis_id']);
					$list['data'][ $k ]['type_name'] = $info['group_name'];
					$list['data'][ $k ]['name'] = '标签';
					$conditions = "concat(',', ng.group_id_array, ',') like '%," . $v['alis_id'] . ",%'";
					$list['data'][ $k ]['goods_list'] = $goods->getRecommendGoodsList(1, $v['show_num'], $conditions);
				} else {
					$list['data'][ $k ]['type_name'] = '';
					$list['data'][ $k ]['name'] = '';
					$list['data'][ $k ]['goods_list'] = [];
					
				}
			} else if ($v['type'] == 2) {
				//分类
				if (!empty($v['alis_id'])) {
					$goodsCategory = new GoodsCategory();
					$info = $goodsCategory->getParentCategory($v['alis_id']);
					$list['data'][ $k ]['type_name'] = $info;
					$list['data'][ $k ]['name'] = '分类';
					$list['data'][ $k ]['goods_list'] = $goods->getRecommendGoodsList(1, $v['show_num'], [ 'ng.category_id_1|ng.category_id_2|ng.category_id_3' => $v['alis_id'] ]);
				} else {
					$list['data'][ $k ]['type_name'] = '';
					$list['data'][ $k ]['name'] = '';
					$list['data'][ $k ]['goods_list'] = [];
					
				}
				
			} else if ($v['type'] == 3) {//品牌
				
				if (!empty($v['alis_id'])) {
					$goodsbrand = new GoodsBrand();
					$info = $goodsbrand->getGoodsBrandInfo($v['alis_id'], 'brand_name');
					$list['data'][ $k ]['type_name'] = $info['brand_name'];
					$list['data'][ $k ]['name'] = '品牌';
					$list['data'][ $k ]['goods_list'] = $goods->getRecommendGoodsList(1, $v['show_num'], [ 'ng.brand_id' => $v['alis_id'] ]);
				} else {
					$list['data'][ $k ]['type_name'] = '';
					$list['data'][ $k ]['name'] = '';
					$list['data'][ $k ]['goods_list'] = [];
				}
			} else if ($v['type'] == 4) {
				$list['data'][ $k ]['type_name'] = '新品';
				$list['data'][ $k ]['name'] = '推荐';
				$list['data'][ $k ]['goods_list'] = $goods->getRecommendGoodsList(1, $v['show_num'], [ 'ng.is_new' => 1 ]);
			} else if ($v['type'] == 5) {
				$list['data'][ $k ]['type_name'] = '精品';
				$list['data'][ $k ]['name'] = '推荐';
				$list['data'][ $k ]['goods_list'] = $goods->getRecommendGoodsList(1, $v['show_num'], [ 'ng.is_recommend' => 1 ]);
			} else if ($v['type'] == 6) {
				$list['data'][ $k ]['type_name'] = '热卖';
				$list['data'][ $k ]['name'] = '推荐';
				$list['data'][ $k ]['goods_list'] = $goods->getRecommendGoodsList(1, $v['show_num'], [ 'ng.is_hot' => 1 ]);
			} else if ($v['type'] == 7) {
				if (!empty($v['alis_id'])) {
					$list['data'][ $k ]['goods_list'] = $goods->getRecommendGoodsList(1, $v['show_num'], [ 'ng.goods_id' => [ 'in', $v['alis_id'] ] ]);
					$list['data'][ $k ]['name'] = '自定义';
					$list['data'][ $k ]['goods_id_array'] = trim($v['alis_id'], ',');
					$list['data'][ $k ]['type_name'] = '自定义';
				} else {
					$list['data'][ $k ]['goods_id_array'] = '';
					$list['data'][ $k ]['type_name'] = '';
					$list['data'][ $k ]['name'] = '';
					$list['data'][ $k ]['goods_list'] = [];
				}
			} else {
				$list['data'][ $k ]['type_name'] = '';
				$list['data'][ $k ]['name'] = '';
				$list['data'][ $k ]['goods_list'] = [];
			}
		}
		return $list;
		
	}
	
	/*******************************************************手机端首页推荐结束*************************************************/
	
	/*******************************************************pc端楼层管理*****************************************************/
	
	/**
	 * 楼层
	 */
	public function editGoodsFloor($data)
	{
		$model = new NsGoodsFloorModel();
		
		if ($data["id"] > 0) {
			$res = $model->save($data, [ "id" => $data["id"] ]);
		} else {
			$res = $model->save($data);
		}
		
		return $res;
	}
	
	/**
	 * 更改楼层排序
	 */
	public function modifyFloorSort($sort, $id)
	{
		$model = new NsGoodsFloorModel();
		$retval = $model->save([
			'sort' => $sort
		], [
			'id' => $id
		]);
		return $retval;
	}
	
	/**
	 * 删除楼层
	 */
	public function deleteFloor($id)
	{
		$model = new NsGoodsFloorModel();
		$retval = $model->destroy([ 'id' => [ 'in', $id ] ]);
		return $retval;
	}
	
	/**
	 * 获取板块信息
	 */
	public function getFloorInfo($condition, $field = '*')
	{
		$model = new NsGoodsFloorModel();
		$res = $model->getInfo($condition, $field);
		return $res;
	}
	
	/**
	 * 楼层列表（不关联）
	 */
	public function getGoodsFloorList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '*')
	{
		$model = new NsGoodsFloorModel();
		$list = $model->pageQuery($page_index, $page_size, $condition, $order, $field);
		return $list;
	}
	
	/**
	 * 处理板块模板数据结构
	 */
	public function formatBlockData($data)
	{
		if (!empty($data)) {
			$product_model = new Goods();
			$goodsCategoty = new GoodsCategory();
			$goodsBrand = new GoodsBrand();
			
			foreach ($data as $k => $v) {
				if ($k == "text") {
				} elseif ($k == "product_category") {
					
					//查询商品分类信息
					foreach ($v as $child_k => $child_v) {
						if (!empty($child_v)) {
							$condition = array();
							$condition['category_id'] = [ 'in', $child_v ];
							$list = $goodsCategoty->getGoodsCategoryList(1, 0, $condition, '', "*");
							if ($list['code'] == 0) {
								$data[ $k ][ $child_k ] = $list['data'];
							}
						}
					}
					
				} elseif ($k == "product") {
					
					foreach ($v as $child_k => $child_v) {
						if (!empty($child_v)) {
							$condition = array();
							$page_size = PAGESIZE;
							if (!empty($child_v['page_size'])) {
								$page_size = $child_v['page_size'];
							}
							if ($child_v['product_source'] == 'product_category') {
								$condition['ng.category_id'] = $child_v['source_value'];
							} elseif ($child_v['product_source'] == 'product_label') {
								$condition['ng.group_id_array'] = [ "like", "%" . $child_v['source_value'] . "%" ];
							} elseif ($child_v['product_source'] == 'product_brand') {
								$condition['ng.brand_id'] = $child_v['source_value'];
							} elseif ($child_v['product_source'] == 'product_recommend') {
								$condition[ $child_v['source_value'] ] = 1;
							} elseif ($child_v['product_source'] == 'product_diy') {
								$condition['ng.goods_id'] = [ 'in', $child_v['source_value']['goods_id'] ];
								$page_size = 0;//自选产品，数量由用户控制
							}
							$condition['ng.state'] = 1;
							$list = $product_model->getGoodsListNew(1, $page_size, $condition, 'ng.sort desc,ng.goods_id desc');
							$data[ $k ][ $child_k ] = $list['data'];
						}
					}
					
				} elseif ($k == "adv") {
				
				} elseif ($k == "brand") {
					
					//查询品牌
					foreach ($v as $child_k => $child_v) {
						if (!empty($child_v)) {
							$condition = array();
							$condition['brand_id'] = [ 'in', $child_v ];
							$list = $goodsBrand->getGoodsBrandList(1, 0, $condition, "sort desc", "*");
							if ($list['code'] == 0) {
								$data[ $k ][ $child_k ] = $list['data'];
							}
						}
					}
					
				}
			}
			return $data;
		}
	}
	
	/*********************************************************pc端首页楼层结束************************************************/
	
	/********************************************************友情链接 begin*******************************************************************/
	
	/**
	 * 添加友情链接
	 */
	public function addLink($data)
	{
		Cache::clear("niu_link");
		$link = new NsPlatformLinkModel();
		$link->save($data);
		return $link->link_id;
	}
	
	/**
	 * 修改友情链接
	 */
	public function updateLink($data)
	{
		Cache::clear("niu_link");
		$link = new NsPlatformLinkModel();
		$retval = $link->save($data, [
			'link_id' => $data["link_id"]
		]);
		return $retval;
	}
	
	/**
	 * 删除友情链接
	 */
	public function deleteLink($link_id)
	{
		Cache::clear("niu_link");
		$link = new NsPlatformLinkModel();
		$retval = $link->destroy($link_id);
		return $retval;
	}
	
	/**
	 * 设置友情链接是否打开新窗口
	 */
	public function modifyPlatformLinkListIsBlank($link_id, $is_blank)
	{
		Cache::clear("niu_link");
		$link_model = new NsPlatformLinkModel();
		$data = array(
			'is_blank' => $is_blank
		);
		$res = $link_model->save($data, [
			'link_id' => $link_id
		]);
		return $res;
	}
	
	/**
	 * 设置友情链接是否显示
	 */
	public function modifyPlatformLinkListIsShow($link_id, $is_show)
	{
		Cache::clear("niu_link");
		$platform_linklist = new NsPlatformLinkModel();
		$data = array(
			'is_show' => $is_show
		);
		$res = $platform_linklist->save($data, [
			'link_id' => $link_id
		]);
		return $res;
	}
	
	/**
	 * 获取友情链接详情
	 */
	public function getLinkDetail($link_id)
	{
		$cache = Cache::tag("niu_link")->get("getLinkDetail" . $link_id);
		if (!empty($cache)) return $cache;
		
		$link = new NsPlatformLinkModel();
		$info = $link->get($link_id);
		Cache::tag("niu_link")->set("getLinkDetail" . $link_id, $info);
		return $info;
	}
	
	/**
	 * 获取友情链接
	 */
	public function getLinkList($page_index = 1, $page_size = 0, $where = '', $order = '', $field = '*')
	{
		$cache = Cache::tag('niu_link')->get('getLinkList' . json_encode([ $page_index, $page_size, $where, $order, $field ]));
		if (!empty($cache)) return $cache;
		
		$link = new NsPlatformLinkModel();
		$list = $link->pageQuery($page_index, $page_size, $where, $order, $field);
		Cache::tag('niu_link')->set('getLinkList' . json_encode([ $page_index, $page_size, $where, $order, $field ]), $list);
		return $list;
	}
	/********************************************************友情链接 end*******************************************************************/
	
	
	/**********************************************广告管理 begin****************************************************************/
	/**
	 * 添加平台广告
	 */
	public function addPlatformAdv($data)
	{
		Cache::clear("niu_adv");
		$platform_adv = new NsPlatformAdvModel();
		$res = $platform_adv->save($data);
		return $res;
	}
	
	/**
	 * 修改广告
	 */
	public function updatePlatformAdv($data)
	{
		Cache::clear("niu_adv");
		$platform_adv = new NsPlatformAdvModel();
		$res = $platform_adv->save($data, [
			'adv_id' => $data["adv_id"]
		]);
		return $res;
	}
	
	/**
	 * 添加或编辑广告位
	 */
	public function editAdvPosition($params)
	{
		$platform_adv_position = new NsPlatformAdvPositionModel();
		$platform_adv = new NsPlatformAdvModel();
		$platform_adv_position->startTrans();
		try {
			$data = array(
				'instance_id' => 0,
				'ap_name' => $params['ap_name'],
				'ap_intro' => $params['ap_intro'],
				'ap_class' => 0,
				'ap_display' => $params['ap_display'],
				'is_use' => $params['is_use'],
				'ap_height' => $params['ap_height'],
				'ap_width' => $params['ap_width'],
				'default_content' => '',
				'ap_background_color' => '',
				'type' => $params['type'],
				'ap_keyword' => $params['ap_keyword'],
				'layout' => $params['layout']
			);
			
			if (empty($params['ap_id'])) {
				$count = $platform_adv_position->getCount([ 'ap_keyword' => $params['ap_keyword'] ]);
				if ($count > 0) return [ 'code' => -1, 'message' => '该关键字已存在' ];
				
				$platform_adv_position->save($data);
				$ap_id = $platform_adv_position->ap_id;
			} else {
				$ap_id = $params['ap_id'];
				$count = $platform_adv_position->getCount([ 'ap_keyword' => $params['ap_keyword'], 'ap_id' => [ '<>', $ap_id ] ]);
				if ($count > 0) return [ 'code' => -1, 'message' => '该关键字已存在' ];
				
				$platform_adv->destroy([ 'ap_id' => $ap_id ]);
				$platform_adv_position->save($data, [ 'ap_id' => $ap_id ]);
			}
			
			$adv_data = [];
			foreach ($params['imgs'] as $item) {
				$item_data = [
					'ap_id' => $ap_id,
					'adv_title' => '',
					'adv_url' => $item['url'],
					'adv_image' => $item['imgPath'],
					'slide_sort' => $item['sort'],
					'background' => $item['bgColor'],
					'adv_code' => ''
				];
				array_push($adv_data, $item_data);
			}
			$platform_adv->saveAll($adv_data);
			$platform_adv_position->commit();
			Cache::clear("niu_adv");
			return [
				'code' => 1,
				'message' => '添加成功'
			];
		} catch (\Exception $e) {
			$platform_adv_position->rollback();
			return [
				'code' => -1,
				'message' => $e->getMessage()
			];
		}
	}
	
	/**
	 * 删除平台广告
	 */
	public function deletePlatformAdv($adv_id)
	{
		Cache::clear("niu_adv");
		$platform_adv = new NsPlatformAdvModel();
		$res = $platform_adv->destroy($adv_id);
		return $res;
	}
	
	/**
	 * 删除平台广告位
	 */
	public function deletePlatfromAdvPosition($ap_id)
	{
		Cache::clear('niu_adv');
		$platform_adv = new NsPlatformAdvModel();
		$platform_adv_position = new NsPlatformAdvPositionModel();
		$platform_adv_position->startTrans();
		try {
			$position_detail = $this->getPlatformAdvPositionDetail($ap_id);
			if (empty($position_detail['is_del'])) {
				$platform_adv->destroy([
					'ap_id' => $ap_id
				]);
				$res = $platform_adv_position->destroy($ap_id);
			} else {
				$res = -1;
			}
			
			$platform_adv_position->commit();
			Cache::tag("niu_platform_adv_position")->set("getPlatformAdvPositionDetail" . $ap_id, '');
		} catch (\Exception $e) {
			$platform_adv_position->rollback();
			return $e->getMessage();
		}
		
		return $res;
	}
	
	/**
	 * 设置广告位是否使用
	 */
	public function modifyPlatformAdvPositionUse($ap_id, $is_use)
	{
		Cache::clear('niu_adv');
		$platform_adv_position = new NsPlatformAdvPositionModel();
		$data = array(
			'is_use' => $is_use
		);
		$res = $platform_adv_position->save($data, [
			'ap_id' => $ap_id
		]);
		return $res;
	}
	
	/**
	 * 修改广告排序
	 */
	public function modifyAdvSlideSort($adv_id, $slide_sort)
	{
		Cache::clear("niu_adv");
		$platform_adv = new NsPlatformAdvModel();
		$data = array(
			'adv_id' => $adv_id,
			'slide_sort' => $slide_sort
		);
		$res = $platform_adv->save($data, [
			'adv_id' => $adv_id
		]);
		return $res;
	}
	
	/**
	 * 检测广告位关键字是否存在
	 */
	public function checkApKeywordIsExists($ap_keyword, $ap_id = '')
	{
		$platform_adv_position = new NsPlatformAdvPositionModel();
		if (empty($ap_id)) {
			$is_exists = $platform_adv_position->getCount([
				"ap_keyword" => $ap_keyword
			]);
		} else {
			$is_exists = $platform_adv_position->getCount([
				"ap_keyword" => $ap_keyword,
				"ap_id" => [ 'neq', $ap_id ]
			]);
		}
		return $is_exists;
	}
	
	/**
	 * 获取平台广告位信息
	 */
	public function getPlatformAdvPositionDetail($ap_id)
	{
		$cache = Cache::tag("niu_adv")->get("getPlatformAdvPositionDetail" . $ap_id);
		if (empty($cache)) {
			$platform_adv_position = new NsPlatformAdvPositionModel();
			$info = $platform_adv_position->getInfo([
				'ap_id' => $ap_id,
				'is_use' => 1
			]);
			
			if (!empty($info)) {
				$platform_adv = new NsPlatformAdvModel();
				$platform_adv_list = $platform_adv->getQuery([
					'ap_id' => $info['ap_id']
				], '*', ' slide_sort ');
				if (empty($platform_adv_list)) {
					$platform_adv_list[0] = array(
						'adv_title' => $info['ap_name'] . '默认图',
						'adv_url' => '#',
						'adv_image' => $info['default_content'],
						'background' => '#FFFFFF',
						'adv_width' => $info['ap_width'],
						'adv_height' => $info['ap_height']
					);
				}
				$info['adv_list'] = $platform_adv_list;
			} else {
				$info = null;
			}
			Cache::tag("niu_adv")->set("getPlatformAdvPositionDetail" . $ap_id, $info);
			return $info;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 获取广告位详情
	 */
	public function getAdvPositionDetail($condition = [])
	{
		$cache = Cache::tag('niu_adv')->get('getAdvPositionDetail' . json_encode($condition));
		if (!empty($cache)) return $cache;
		
		$platform_adv_position = new NsPlatformAdvPositionModel();
		$platform_adv = new NsPlatformAdvModel();
		
		$info = $platform_adv_position->getInfo($condition);
		if (!empty($info)) {
			$advs = $platform_adv->pageQuery(1, 0, [ 'ap_id' => $info['ap_id'] ], 'slide_sort desc', '*');
			$info['advs'] = $advs['data'];
		}
		Cache::tag('niu_adv')->set('getAdvPositionDetail' . json_encode($condition), $info);
		
		return $info;
	}
	
	/**
	 * 获取广告详情
	 */
	public function getPlatformAdDetail($adv_id)
	{
		$cache = Cache::tag("niu_adv")->get("getPlatformAdDetail" . $adv_id);
		if (!empty($cache)) {
			return $cache;
		}
		$platform_adv = new NsPlatformAdvModel();
		$info = $platform_adv->getInfo([
			'adv_id' => $adv_id
		]);
		if (!empty($info["adv_code"])) {
			$info["adv_code"] = html_entity_decode($info["adv_code"]);
		}
		Cache::tag("niu_adv")->set("getPlatformAdDetail" . $adv_id, $info);
		return $info;
	}
	
	/**
	 * 通过广告位关键字获取广告位详情
	 */
	public function getPlatformAdvPositionDetailByApKeyword($ap_keyword)
	{
		$cache = Cache::tag("niu_adv")->get("getPlatformAdvPositionDetailByApKeyword" . '_' . $ap_keyword);
		if (!empty($cache)) {
			return $cache;
		}
		$platform_adv_position = new NsPlatformAdvPositionModel();
		$info = $platform_adv_position->getInfo([
			'ap_keyword' => $ap_keyword,
			'is_use' => 1
		]);
		
		if (!empty($info)) {
			$platform_adv = new NsPlatformAdvModel();
			$platform_adv_list = $platform_adv->getQuery([
				'ap_id' => $info['ap_id']
			], '*', ' slide_sort ');
			if (empty($platform_adv_list)) {
				$platform_adv_list[0] = array(
					'adv_title' => $info['ap_name'] . '默认图',
					'adv_url' => '#',
					'adv_image' => $info['default_content'],
					'background' => '#FFFFFF',
					'adv_width' => $info['ap_width'],
					'adv_height' => $info['ap_height']
				);
			}
			$info['adv_list'] = $platform_adv_list;
		} else {
			$info = null;
		}
		Cache::tag("niu_adv")->set("getPlatformAdvPositionDetailByApKeyword" . '_' . $ap_keyword, $info);
		return $info;
	}
	
	/**
	 * 获取平台广告位列表
	 */
	public function getPlatformAdvPositionList($page_index = 1, $page_size = 0, $where = '', $order = 'ap_id desc', $field = '*')
	{
		$data = [ $page_index, $page_size, $where, $order, $field ];
		$data = json_encode($data);
		$cache = Cache::tag("niu_adv")->get("getPlatformAdvPositionList" . $data);
		if (!empty($cache)) {
			return $cache;
		}
		$platform_adv_position = new NsPlatformAdvPositionModel();
		$result = $platform_adv_position->pageQuery($page_index, $page_size, $where, $order, $field);
		foreach ($result['data'] as $k => $v) {
			if ($v['ap_class'] == 0) {
				$result['data'][ $k ]['ap_class_name'] = '图片';
			} else if ($v['ap_class'] == 1) {
				$result['data'][ $k ]['ap_class_name'] = '文字';
			} else if ($v['ap_class'] == 2) {
				$result['data'][ $k ]['ap_class_name'] = '幻灯';
			} else if ($v['ap_class'] == 3) {
				$result['data'][ $k ]['ap_class_name'] = 'flash';
			} else if ($v['ap_class'] == 4) {
				$result['data'][ $k ]['ap_class_name'] = '代码';
			} else {
				$result['data'][ $k ]['ap_class_name'] = '';
			}
			if ($v['ap_display'] == 0) {
				$result['data'][ $k ]['ap_display_name'] = '幻灯片';
			} else if ($v['ap_display'] == 1) {
				$result['data'][ $k ]['ap_display_name'] = '多广告展示';
			} else if ($v['ap_display'] == 2) {
				$result['data'][ $k ]['ap_display_name'] = '单广告展示';
			} else {
				$result['data'][ $k ]['ap_display_name'] = '';
			}
		}
		Cache::tag("niu_adv")->set("getPlatformAdvPositionList" . $data, $result);
		return $result;
	}
	
	/**
	 * 后台获取广告列表
	 */
	public function adminGetAdvList($page_index = 1, $page_size = 0, $condition = 'npa.adv_id desc', $order = '')
	{
		$ns_platform_adv = new NsPlatformAdvViewModel();
		$list = $ns_platform_adv->getViewList($page_index, $page_size, $condition, $order);
		return $list;
	}
	/**********************************************广告管理 end****************************************************************/
	
	/**********************************************帮助 begin****************************************************************/
	
	/**
	 * 添加帮助分类
	 */
	public function addPlatformHelpClass($data)
	{
		Cache::clear("niu_platform_help");
		$platform_class = new NsPlatformHelpClassModel();
		$platform_class->save($data);
		return $platform_class->class_id;
	}
	
	/**
	 * 修改帮助分类
	 */
	public function updatePlatformClass($data)
	{
		Cache::clear("niu_platform_help");
		$platform_class = new NsPlatformHelpClassModel();
		$retval = $platform_class->save($data, [
			'class_id' => $data["class_id"]
		]);
		return $retval;
	}
	
	/**
	 * 删除帮助分类
	 */
	public function deleteHelpClass($class_id)
	{
		Cache::clear("niu_platform_help");
		$platform_class = new NsPlatformHelpClassModel();
		$platform_class->startTrans();
		try {
			$platform_class->destroy($class_id);
			$this->deleteHelpClassTitle($class_id);
			$platform_class->commit();
			return 1;
		} catch (\Exception $e) {
			$platform_class->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 添加帮助内容
	 */
	public function addPlatformDocument($data)
	{
		Cache::clear("niu_platform_help");
		$platform_document = new NsPlatformHelpDocumentModel();
		$platform_document->save($data);
		return $platform_document->id;
	}
	
	/**
	 * 修改帮助内容
	 */
	public function updatePlatformDocument($data)
	{
		Cache::clear("niu_platform_help");
		$platform_document = new NsPlatformHelpDocumentModel();
		$retval = $platform_document->save($data, [
			'id' => $data["id"]
		]);
		return $retval;
	}
	
	/**
	 * 修改帮助中心内容的标题与排序
	 */
	public function updatePlatformDocumentTitleAndSort($id, $title, $sort)
	{
		Cache::clear("niu_platform_help");
		$data = array(
			'title' => $title,
			'sort' => $sort
		);
		$platform_document = new NsPlatformHelpDocumentModel();
		$retval = $platform_document->save($data, [
			'id' => $id
		]);
		return $retval;
	}
	
	/**
	 * 删除帮助主题
	 */
	public function deleteHelpTitle($id)
	{
		Cache::clear("niu_platform_help");
		$platform_document = new NsPlatformHelpDocumentModel();
		$retval = $platform_document->destroy($id);
		return $retval;
	}
	
	/**
	 * 删除帮助内容
	 */
	public function deleteHelpClassTitle($class_id)
	{
		Cache::clear("niu_platform_help");
		$platform_document = new NsPlatformHelpDocumentModel();
		$retval = $platform_document->destroy([
			'class_id' => $class_id
		]);
		return $retval;
	}
	
	/**
	 * 获取帮助内容详情
	 */
	public function getPlatformDocumentDetail($id)
	{
		$cache = Cache::tag("niu_platform_help")->get("getPlatformDocumentDetail" . $id);
		if (empty($cache)) {
			$platform_document = new NsPlatformHelpDocumentModel();
			$data = $platform_document->get($id);
			Cache::tag("niu_platform_help")->set("getPlatformDocumentDetail" . $id, $data);
			return $data;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 获取帮助列表
	 */
	public function getPlatformHelpClassList($page_index = 1, $page_size = 0, $where = '', $order = '', $field = '*')
	{
		$data = array(
			$page_index,
			$page_size,
			$where,
			$order,
			$field
		);
		$data = json_encode($data);
		$cache = Cache::tag("niu_platform_help")->get("getPlatformHelpClassList" . $data);
		if (empty($cache)) {
			$platform_class = new NsPlatformHelpClassModel();
			$list = $platform_class->pageQuery($page_index, $page_size, $where, $order, $field);
			Cache::tag("niu_platform_help")->set("getPlatformHelpClassList" . $data, $list);
			return $list;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 获取帮助内容列表
	 */
	public function getPlatformHelpDocumentList($page_index = 1, $page_size = 0, $where = '', $order = '', $field = '*')
	{
		$data = array(
			$page_index,
			$page_size,
			$where,
			$order,
			$field
		);
		$data = json_encode($data);
		$cache = Cache::tag("niu_platform_help")->get("getPlatformHelpDocumentList" . $data);
		if (empty($cache)) {
			$platform_document = new NsPlatformHelpDocumentModel();
			$list = $platform_document->getPlatformHelpDocumentViewList($page_index, $page_size, $where, $order);
			Cache::tag("niu_platform_help")->set("getPlatformHelpDocumentList" . $data, $list);
			return $list;
		} else {
			return $cache;
		}
	}
	/**********************************************帮助 end****************************************************************/
	
	/**********************************************公告****************************************************************/
	/**
	 * 添加或修改公告
	 */
	public function editNotice($data)
	{
		Cache::clear("niu_notice");
		$notice = new NsNoticeModel();
		if ($data["id"] == 0) {
			$data["create_time"] = time();
			return $notice->save($data);
		} else if ($data["id"] > 0) {
			$data["modify_time"] = time();
			return $notice->save($data, [
				"id" => $data["id"]
			]);
		}
	}
	
	/**
	 * 更改公告排序
	 */
	public function modifyNoticeSort($sort, $id)
	{
		Cache::clear("niu_notice");
		$notice = new NsNoticeModel();
		$retval = $notice->save([
			'sort' => $sort
		], [
			'id' => $id
		]);
		return $retval;
	}
	
	/**
	 * 删除公告
	 */
	public function deleteNotice($id)
	{
		Cache::clear("niu_notice");
		$notice = new NsNoticeModel();
		$retval = $notice->destroy($id);
		return $retval;
	}
	
	/**
	 * 获取公告详情
	 */
	public function getNoticeDetail($id)
	{
		$cache = Cache::tag("niu_notice")->get("getNoticeDetail" . $id);
		if (empty($cache)) {
			$notice = new NsNoticeModel();
			$res = $notice->getInfo([
				"id" => $id
			]);
			Cache::tag("niu_notice")->set("getNoticeDetail" . $id, $res);
			return $res;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 分页获取公告列表
	 */
	public function getNoticeList($page_index, $page_size, $condition, $order = "", $field = "*")
	{
		$data = array(
			$page_index,
			$page_size,
			$condition,
			$order,
			$field
		);
		$data = json_encode($data);
		
		$cache = Cache::tag("niu_notice")->get("getNoticeList" . $data);
		if (empty($cache)) {
			$notice = new NsNoticeModel();
			$list = $notice->pageQuery($page_index, $page_size, $condition, $order, $field);
			Cache::tag("niu_notice")->set("getNoticeList" . $data, $list);
			return $list;
		} else {
			return $cache;
		}
	}
	
	/**********************************************公告 end****************************************************************/
	
	/**********************************************电脑端首页浮动****************************************************************/
	
	/**
	 * 设置首页浮动
	 */
	public function getWebFloatConfig()
	{
		$cache = Cache::tag('config')->get("getWebFloatConfig");
		if (empty($cache)) {
			$config_module = new ConfigModel();
			$info = $config_module->getInfo([
				'key' => 'WEB_FLOAT_CONFIG',
				'instance_id' => 0
			], 'value');
			if (empty($info)) {
				$value = [
					'is_open' => 0,
					'nav_icon' => '',
					'nav_url' => '',
					'nav_title' => ''
				];
				$data = array(
					'instance_id' => 0,
					'key' => 'WEB_FLOAT_CONFIG',
					'value' => json_encode($value),
					'is_use' => 0,
					'desc' => '设置首页浮动',
					'create_time' => time()
				);
				$res = $config_module->save($data);
				Cache::tag('config')->set("WEB_FLOAT_CONFIG", $data);
				return $value;
			} else {
				$data = json_decode($info['value'], true);
				Cache::tag('config')->set("getWebFloatConfig", $data);
				return $data;
			}
		} else {
			return $cache;
		}
	}
	
	/**
	 * 设置首页浮动
	 */
	public function setWebFloatConfig($data)
	{
		Cache::tag('config')->set("WEB_FLOAT_CONFIG", null);
		$value = $data;
		$config_module = new ConfigModel();
		$info = $config_module->getInfo([
			'key' => 'WEB_FLOAT_CONFIG',
			'instance_id' => 0
		], 'value');
		
		if (empty($info)) {
			$data = array(
				'instance_id' => 0,
				'key' => 'WEB_FLOAT_CONFIG',
				'value' => json_encode($value),
				'is_use' => 1,
				'desc' => '设置首页浮动',
				'create_time' => time()
			);
			$res = $config_module->save($data);
		} else {
			$config_module = new ConfigModel();
			$data = array(
				'value' => json_encode($value),
				'is_use' => 1,
				'modify_time' => time()
			);
			$res = $config_module->save($data, [
				'instance_id' => 0,
				'key' => 'WEB_FLOAT_CONFIG'
			]);
			Cache::tag('config')->set("getWebFloatConfig", $value);
		}
		return $res;
	}
	
	/**********************************************手机端首页浮动****************************************************************/
	
	/**
	 * 设置首页浮动
	 */
	public function getWapFloatConfig()
	{
		$cache = Cache::tag('config')->get("getWapFloatConfig");
		$config_module = new ConfigModel();
		if (empty($cache)) {
			$info = $config_module->getInfo([
				'key' => 'WAP_FLOAT_CONFIG',
				'instance_id' => 0
			], 'value');
			if (empty($info)) {
				$config_module = new ConfigModel();
				$value = [
					'is_open' => 0,
					'nav_icon' => '',
					'nav_url' => '',
					'nav_title' => ''
				];
				$data = array(
					'instance_id' => 0,
					'key' => 'WAP_FLOAT_CONFIG',
					'value' => json_encode($value),
					'is_use' => 0,
					'desc' => '设置首页浮动',
					'create_time' => time()
				);
				$config_module->save($data);
				Cache::tag('config')->set("WAP_FLOAT_CONFIG", $data);
				return $value;
			} else {
				$data = json_decode($info['value'], true);
				Cache::tag('config')->set("getWapFloatConfig", $data);
				return $data;
			}
		} else {
			return $cache;
		}
	}
	
	/**
	 * add，edd首页浮动
	 */
	public function setWapFloatConfig($data)
	{
		Cache::tag('config')->set("WAP_FLOAT_CONFIG", null);
		$value = $data;
		$config_module = new ConfigModel();
		$info = $config_module->getInfo([
			'key' => 'WAP_FLOAT_CONFIG',
			'instance_id' => 0
		], 'value');
		if (empty($info)) {
			$config_module = new ConfigModel();
			$data = array(
				'instance_id' => 0,
				'key' => 'WAP_FLOAT_CONFIG',
				'value' => json_encode($value),
				'is_use' => 1,
				'desc' => '设置首页浮动',
				'create_time' => time()
			);
			$res = $config_module->save($data);
			Cache::tag('config')->set("WAP_FLOAT_CONFIG", $data);
		} else {
			$config_module = new ConfigModel();
			$data = array(
				'value' => json_encode($value),
				'is_use' => 1,
				'modify_time' => time()
			);
			$res = $config_module->save($data, [
				'instance_id' => 0,
				'key' => 'WAP_FLOAT_CONFIG'
			]);
		}
		Cache::tag('config')->set("getWapFloatConfig", $value);
		return $res;
	}
}