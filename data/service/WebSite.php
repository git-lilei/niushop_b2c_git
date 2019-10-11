<?php
/**
 * WebSite.php
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

use data\model\SysModel;
use data\model\SysUrlRouteModel;
use data\model\WebSiteModel;
use data\model\WebStyleModel;
use think\Cache;

class WebSite extends BaseService
{
	
	private $website;
	
	private $module;
	
	public function __construct()
	{
		parent::__construct();
		$this->website = new WebSiteModel();
		$this->module = new SysModel();
	}
	
	/*************************************************************网站信息**************************************************/
	/**
	 * 修改网站信息
	 */
	function updateWebSite($data)
	{
		$this->website = new WebSiteModel();
		$res = $this->website->save($data, [
			"website_id" => 1
		]);
		if ($res) {
			Cache::tag('website')->set('getWebSiteInfo', null);
		}
		return $res;
	}
	
	/**
	 * 获取网站信息
	 */
	public function getWebSiteInfo()
	{
		$cache = Cache::tag('website')->get('getWebSiteInfo');
		if (!empty($cache)) return $cache;
		
		$info = $this->website->getInfo('');
		Cache::tag('website')->set('getWebSiteInfo', $info);
		
		return $info;
	}
	
	/**
	 * 获取网站样式
	 */
	public function getWebStyle()
	{
		$config_style = ''; // 根据用户实例从数据库中获取样式，以及项目
		$style = \think\Request::instance()->module() . '/' . $config_style;
		return $style;
	}
	
	
	/**
	 * 获取网站信息
	 */
	public function getWebDetail()
	{
		$cache = Cache::tag('module')->get('getWebDetail');
		if (!empty($cache)) return $cache;
		
		$web_info = $this->website->getInfo(array(
			"website_id" => 1
		));
		Cache::tag('module')->set('getWebDetail', $web_info);
		return $web_info;
	}
	
	/**
	 * 获取样式列表
	 */
	public function getWebStyleList($condition)
	{
		$webstyle = new WebStyleModel();
		$style_list = $webstyle->getQuery($condition);
		return $style_list;
	}
	/*******************************************************网站信息结束*****************************************************/
	
	/*****************************************************************路由规则**********************************************/
	/**
	 * 添加路由规则
	 */
	public function addUrlRoute($data)
	{
		Cache::clear('route');
		$url_route_model = new SysUrlRouteModel();
		$res = $url_route_model->save($data);
		return $res;
	}
	
	/**
	 * 修改路由规则
	 */
	public function updateUrlRoute($data)
	{
		Cache::clear('route');
		
		$url_route_model = new SysUrlRouteModel();
		$res = $url_route_model->save($data, [
			"routeid" => $data['routeid']
		]);
		return $res;
	}
	
	/**
	 * 删除路由规则
	 */
	public function deleteUrlRoute($routeid)
	{
		Cache::clear('route');
		$url_route_model = new SysUrlRouteModel();
		$res = $url_route_model->destroy([
			"routeid" => array(
				"in",
				$routeid
			),
			"is_system" => 0
		]);
		
		return $res;
	}
	
	/**
	 * 获取路由
	 */
	public function getUrlRoute()
	{
		$cache = Cache::tag('route')->get('getUrlRoute');
		if (!empty($cache)) return $cache;
		
		$url_route_model = new SysUrlRouteModel();
		$route_list = $url_route_model->pageQuery(1, 0, [
			'is_open' => 1
		], '', 'rule,route');
		
		Cache::tag('route')->set('getUrlRoute', $route_list);
		return $route_list;
	}
	
	/**
	 * 获取路由信息
	 */
	public function getUrlRouteDetail($routeid)
	{
		$cache = Cache::tag('route')->get('getUrlRouteDetail' . $routeid);
		
		if (!empty($cache)) return $cache;
		
		$url_route_model = new SysUrlRouteModel();
		$res = $url_route_model->get($routeid);
		Cache::tag('route')->set('getUrlRouteDetail' . $routeid, $res);
		return $res;
	}
	
	/**
	 * 获取路由规则列表
	 */
	public function getUrlRouteList($page_index = 1, $page_size = 0, $condition = '', $order = 'routeid desc')
	{
		$cache = Cache::tag('route')->get('getUrlRouteList' . json_encode([ $page_index, $page_size, $condition, $order ]));
		if (!empty($cache)) return $cache;
		
		$url_route_model = new SysUrlRouteModel();
		$route_list = $url_route_model->pageQuery($page_index, $page_size, $condition, $order, '*');
		
		Cache::tag('route')->set('getUrlRouteList' . json_encode([ $page_index, $page_size, $condition, $order ]), $route_list);
		return $route_list;
	}
	
	/**
	 * 检测路由规则是否存在
	 */
	public function urlRouteIsExists($type, $value)
	{
		$cache = Cache::tag('route')->get('urlRouteIsExists' . $type . $value);
		if (!empty($cache)) return $cache;
		
		$is_exists = false;
		$url_route_model = new SysUrlRouteModel();
		if ($type == "rule") {
			$count = $url_route_model->getCount([
				"rule" => trim($value)
			]);
			if ($count > 0) {
				$is_exists = true;
			}
		} else
			if ($type == "route") {
				$count = $url_route_model->getCount([
					"route" => trim($value)
				]);
				if ($count > 0) {
					$is_exists = true;
				}
			}
		Cache::tag('route')->set('urlRouteIsExists' . $type . $value, $is_exists);
		return $is_exists;
	}
	
	/********************************************************路由规则结束*****************************************************/
	
	/**
	 * 修改运营模式
	 */
	public function updateVisitWebSite($data)
	{
		$this->website = new WebSiteModel();
		$res = $this->website->save($data, [
			"website_id" => 1
		]);
		Cache::clear('website');
		return $res;
	}
	
	/**
	 * 修改一键关注设置
	 */
	public function updateKeyConcernConfig($is_show_follow)
	{
		$data = array(
			'modify_time' => time(),
			'is_show_follow' => $is_show_follow
		);
		$this->website = new WebSiteModel();
		$res = $this->website->save($data, [
			"website_id" => 1
		]);
		Cache::clear('website');
		return $res;
	}
}