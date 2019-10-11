<?php
/**
 * Shop.php
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

namespace app\api\controller;

use data\service\Config as ConfigService;
use data\service\Express;
use data\service\OrderQuery;
use data\service\Shop as ShopService;

/**
 * 店铺相关接口
 */
class Shop extends BaseApi
{
	/**
	 * 导航列表
	 */
	public function shopNavigationList()
	{
		$type = isset($this->params['type']) ? $this->params['type'] : 1;
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$order = isset($this->params['order']) ? $this->params['order'] : 'sort desc';
		$shop_service = new ShopService();
		$condition = [ 'type' => $type, 'is_show' => 1 ];
		$navigation_list = $shop_service->shopNavigationList($page_index, $page_size, $condition, $order);
		
		return $this->outMessage("", $navigation_list);
	}
	
	/**
	 * 商城默认搜索关键字
	 */
	public function defaultKeyWords()
	{
		$config = new ConfigService();
		$instance_id = isset($this->params['instance_id']) ? $this->params['instance_id'] : $this->instance_id;
		$default_keywords = $config->getDefaultSearchConfig($instance_id);
		return $this->outMessage("", $default_keywords);
	}
	
	/**
	 * 友情链接
	 */
	public function shopLinkList()
	{
		$shop_service = new ShopService();
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$link_list = $shop_service->getLinkList($page_index, $page_size, [ "is_show" => 1 ], 'link_sort desc');
		return $this->outMessage("", $link_list);
	}
	
	/**
	 * 获取公告列表
	 */
	public function shopNoticeList()
	{
		$shop_service = new ShopService();
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$shop_id = isset($this->params['shop_id']) ? $this->params['shop_id'] : $this->instance_id;
		$condition = isset($this->params['condition']) ? $this->params['condition'] : [];
		$order = isset($this->params['order']) ? $this->params['order'] : "create_time desc";
        if (! empty($condition) && is_string($condition)) {
		    $condition = json_decode($condition, true);
        }
		$field = 'id,notice_title,create_time';
		$condition['shop_id'] = $shop_id;
		$notice = $shop_service->getNoticeList($page_index, $page_size, $condition, $order, $field);
		return $this->outMessage("公告列表", $notice);
	}
	
	/**
	 * 获取公告详情
	 */
	public function shopNoticeInfo()
	{
		$id = isset($this->params['id']) ? $this->params['id'] : "";
		$shop_service = new ShopService();
		$res = $shop_service->getNoticeDetail($id);
		return $this->outMessage("获取公告详情", $res);
	}
	
	/**
	 * 查询广告详情
	 */
	public function shopAdvPositionInfo()
	{
		$ap_id = isset($this->params['ap_id']) ? $this->params['ap_id'] : "";
		$res = "";
		if (!empty($ap_id)) {
			$shop_service = new ShopService();
			$res = $shop_service->getPlatformAdvPositionDetail($ap_id);
		}
		return $this->outMessage("", $res);
	}
	
	/**
	 * 广告位详情
	 */
	public function shopAdvPositionDetailByApKeyword()
	{
		$title = "广告位详情";
		$ap_keyword = isset($this->params['ap_keyword']) ? $this->params['ap_keyword'] : '';
		if (empty($ap_keyword)) {
			return $this->outMessage($title, "", '-1', "无法获取广告位信息");
		}
		$shop_service = new ShopService();
		$spelling_group_zone_adv = $shop_service->getPlatformAdvPositionDetailByApKeyword($ap_keyword);
		return $this->outMessage($title, $spelling_group_zone_adv);
	}
	
	
	/**
	 * 帮助内容列表
	 */
	public function helpList()
	{
		$shop_service = new ShopService();
		$document_id = isset($this->params['id']) ? $this->params['id'] : '';
		$class_id = isset($this->params['class_id']) ? $this->params['class_id'] : '';
		
		$platform_help_class = $shop_service->getPlatformHelpClassList(1, 0, '', 'sort desc');
		$data['platform_help_class'] = $platform_help_class['data'];// 帮助中心分类列表
		
		$platform_help_document = $shop_service->getPlatformHelpDocumentList(1, 0, 'is_visibility=1', 'sort desc');
		$data['platform_help_document'] = $platform_help_document['data'];// 帮助中心列表
		
		if (empty($document_id)) {
			$is_exit = false;
			foreach ($platform_help_class['data'] as $class) {
				if ($is_exit) {
					break;
				}
				foreach ($platform_help_document['data'] as $document) {
					if ($class['class_id'] == $document['class_id']) {
						$is_exit = true;
						$title = $document['title'];
						$content = $document['content'];
						break;
					}
				}
			}
			$help_document_info = array(
				'title' => $title,
				'content' => $content
			);
			$data['help_document_info'] = $help_document_info;// 帮助中心信息详情
		} else {
			$help_document_info = $shop_service->getPlatformHelpDocumentList(1, 0, [
				'id' => $document_id,
				'is_visibility' => 1
			], 'sort desc');
			$data['help_document_info'] = $help_document_info['data'][0];
		}
		return $this->outMessage("帮助中心列表", $data);
	}
	
	/**
	 * 帮助类型列表
	 */
	public function helpClassList()
	{
		$shop_service = new ShopService();
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : 5;
		$order = isset($this->params['order']) ? $this->params['order'] : "sort desc";
		$platform_help_class = $shop_service->getPlatformHelpClassList($page_index, $page_size, "", $order);
		return $this->outMessage("", $platform_help_class);
	}
	
	/**
	 * 帮助信息
	 */
	public function helpInfo()
	{
		$shop_service = new ShopService();
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : 0;
		$is_visibility = isset($this->params['is_visibility']) ? $this->params['is_visibility'] : 1;
		$class_id = isset($this->params['class_id']) ? $this->params['class_id'] : 0;
		$id = isset($this->params['id']) ? $this->params['id'] : '';
		$order = isset($this->params['order']) ? $this->params['order'] : "sort desc";
		$condition = [];
		$condition['is_visibility'] = $is_visibility;
		if (!empty($class_id)) {
			$condition['np.class_id'] = $class_id;
		}
		if (!empty($id)) {
			$condition['np.id'] = $id;
		}
		
		$platform_help_document = $shop_service->getPlatformHelpDocumentList($page_index, $page_size, $condition, $order);
		
		return $this->outMessage("", $platform_help_document);
	}
	
	/**
	 * 店铺默认物流地址
	 */
	public function defaultExpressAddress()
	{
		$express = new Express();
		$address = $express->getDefaultShopExpressAddress();
		return $this->outMessage("", $address);
	}
	
	/**
	 * 商家订单退款设置
	 */
	public function shopOrderReturnSet()
	{
		$order_query = new OrderQuery();
		// 查询商家地址
		$shop_info = $order_query->getShopReturnSet($this->instance_id);
		return $this->outMessage("", $shop_info);
	}
	
	/**
	 * 分享配置
	 */
	public function shareConfig()
	{
		$title = "分享配置";
		$shop = new ShopService();
		$config = $shop->getShopShareConfig();
		return $this->outMessage($title, $config);
	}
	
	/**
	 * 自提地址
	 */
	public function pickupPointList()
	{
		$shop = new ShopService();
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$condition = isset($this->params['condition']) ? $this->params['condition'] : [];
		$order = isset($this->params['order']) ? $this->params['order'] : "create_time desc";
		
		$list = $shop->getPickupPointList($page_index, $page_size, $condition, $order);
		return $this->outMessage("自提地址", $list);
	}
	
	/**
	 * 获取广告位详情
	 */
	public function advDetail()
	{
		$title = '广告位详情';
		$keyword = $this->get('ap_keyword', '');
		$export_type = $this->get('export_type', 'data'); // html:模板   data:数据
		$shop_service = new ShopService();
		$data = $shop_service->getAdvPositionDetail([ 'ap_keyword' => $keyword, 'is_use' => 1 ]);
		$rand_str = randomkeys(6);
		if (!empty($data)) {
			if ($export_type == 'data') {
				return $this->outMessage($title, $data);
			} else {
				if ($data['ap_display'] == 0) {
					$template = $this->fetch('public/static/advTemplate/tileTemplate.html', [ 'info' => $data, 'rand_str' => $rand_str ]);
				} elseif ($data['ap_display'] == 2) {
					$template = $this->fetch('public/static/advTemplate/slideTemplate.html', [ 'info' => $data, 'rand_str' => $rand_str ]);
				}
				return $this->outMessage($title, $template);
			}
		} else {
			return $this->outMessage($title, null);
		}
		
	}
	
	/**
	 * 获取PC端浮层详情
	 */
	public function webFloating()
	{
		$title = 'PC端首页浮层信息';
		$shop_service = new ShopService();
		$data = $shop_service->getWebFloatConfig();
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 获取WAP端浮层详情
	 */
	public function wapFloating()
	{
		$title = 'WAP端首页浮层信息';
		$shop_service = new ShopService();
		$data = $shop_service->getWapFloatConfig();
		return $this->outMessage($title, $data);
	}
    
    /**
     * 获取店铺配送方式
     */
	public function expressType()
    {
        $title = '获取店铺配送方式';
        $data = isset($this->params['data']) ? json_decode($this->params['data'], true) : [];//订单数据
        $condition = [
            'shop_type' => isset($data['express_type']) ? (int)$data['express_type'] : 0,
            'province_id' => isset($data['province']) ? (int)$data['province'] : 0,
            'city_id' => isset($data['city']) ? (int)$data['city'] : 0,
            'district_id' => isset($data['district']) ? (int)$data['district'] : 0,
        ];
        $shop_service = new ShopService();
        $data = $shop_service->getPickupPointQueryCS($condition);
        return $this->outMessage($title, $data);
    }
}