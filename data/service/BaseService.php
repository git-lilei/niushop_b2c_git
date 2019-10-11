<?php
/**
 * BaseService.php
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

use data\model\UserLogModel;
use data\model\UserModel;
use think\facade\Cookie;
use think\Request;
use think\Session as Session;

class BaseService
{
	protected $uid;
	protected $user_name;
	protected $user_headimg;
	protected $instance_id = 0;  //店铺id
	protected $is_admin;
	protected $group_id;
	protected $instance_name;
	
	//加密后的uid
	public $token;
	
	//会员基础信息
	public $member_detail;
	
	public function __construct()
	{
		$this->init();
	}
	
	/**
	 * 初始化数据
	 */
	protected function init()
	{
		$model = $this->getRequestModel();
		if (!$model) {
		    $model = 'app';
        }
		$this->uid = Session::get($model . 'uid');
		$this->instance_id = 0;
		$this->is_admin = Session::get($model . 'is_admin');
		$this->group_id = Session::get($model . 'group_id');
		$this->instance_name = Session::get($model . 'instance_name');
		$this->is_member = Session::get($model . 'is_member');
		$this->is_system = Session::get($model . 'is_system');
		$this->user_name = Session::get($model . 'user_name');
		$this->user_headimg = Session::get($model . 'user_headimg');
        
//        $this->uid = Cookie::get($model . 'uid');
//        $this->instance_id = 0;
//        $this->is_admin = Cookie::get($model . 'is_admin');
//        $this->group_id = Cookie::get($model . 'group_id');
//        $this->instance_name = Cookie::get($model . 'instance_name');
//        $this->is_member = Cookie::get($model . 'is_member');
//        $this->is_system = Cookie::get($model . 'is_system');
//        $this->user_name = Cookie::get($model . 'user_name');
//        $this->user_headimg = Cookie::get($model . 'user_headimg');
	}
	
	/**
	 * 把返回的数据集转换成Tree
	 * @param array $list 要转换的数据集
	 * @param string $pid parent标记字段
	 * @param string $level level标记字段
	 * @return array
	 */
	function listToTree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
	{
		for ($k = 0; $k < count($list); $k++) {
			$list[ $k ][ $child ] = array();
		}
		// 创建Tree
		for ($i = count($list) - 1; $i >= 0; $i--) {
			for ($j = 0; $j < count($list); $j++) {
				if ($list[ $j ][ $pk ] == $list[ $i ][ $pid ]) {
					if (empty($list[ $j ][ $child ])) {
						$list[ $j ][ $child ][0] = $list[ $i ];
					} else {
						$list[ $j ][ $child ] = array_push($list[ $j ][ $child ], $list[ $i ]);
					}
					
					
				}
			}
			
		}
		return $list;
	}
	
	/**
	 * 缓存标签
	 */
	public function cacheTag()
	{
		$tag = [
			[ 'address', '地址相关' ],
			[ 'offpayarea', '货到付款区域相关' ],
			[ 'album', '相册图片相关' ],
			[ 'article', '文章相关' ],
			[ 'wap_custom_template', '手机端自定义模板' ],
			[ 'config', '配置信息' ],
			[ 'express', '物流配送' ],
			[ 'niu_goods_brand', '商品品牌' ],
			[ 'niu_goods_category', '商品分类' ],
			[ 'niu_goods_group', '商品分组' ],
			[ 'user', '会员相关' ],
			[ 'notice', '通知相关' ],
			[ 'o2o', '本地配送相关' ],
			[ 'weixin_message', '微信模板消息相关' ],
			[ 'weixin_menu', '微信菜单相关' ],
			[ 'weixin', '微信相关' ],
			[ 'website', '网站相关' ],
			[ 'module', '系统模块相关' ],
			[ 'instance', '系统实例相关' ],
			[ 'route', '路由相关' ],
			[ 'niu_virtual_goods_category', '虚拟商品分类' ],
			[ 'niu_virtual_goods_group', '虚拟商品分组' ],
			[ 'niu_virtual_goods', '虚拟商品' ],
			[ 'verification', '核销相关' ],
			[ 'sys_user', '用户相关' ],
			[ 'niu_supplier', '供货商相关' ],
			[ 'niu_link', '友情链接' ],
			[ 'niu_adv', '广告位以及广告' ],
			[ 'niu_platform_help', '帮助' ],
			[ 'niu_notice', '网站公告相关' ],
			[ 'niu_supplier', '供货商相关' ],
			[ 'niu_link', '友情链接' ],
			[ 'pintuan', '拼团' ],
			[ 'coupon', '优惠券' ],
			[ 'point_config', '积分设置' ],
			[ 'gift', '赠品相关' ],
			[ 'mansong', '满减送相关' ],
			[ 'discount', '限时折扣相关' ],
			[ 'combo_package', '组合套餐相关' ],
			[ 'promotion_game', '营销游戏相关' ],
			[ 'topic', '专题相关' ]
		];
		return $tag;
	}
	
	/**
	 * 获取model
	 */
	public function getRequestModel()
	{
		$model = Request::instance()->module();
		if ($model == 'web' || $model == 'wap') {
			$model = 'app';
		}
		return $model;
	}
	
	/**
	 * 添加日志
	 * @param int $uid
	 * @param string $is_system
	 * @param string $controller 控制器中文名
	 * @param string $method 方法中文名
	 * @param string $ip ip地址
	 * @param string $get_data 操作数据详情
	 * @return boolean
	 */
	public function addUserLog($uid, $is_system, $controller, $method, $get_data)
	{
		$url = Request::instance()->url(true);
		$ip = Request::instance()->ip();
		
		if ($uid == 0) {
			$user_name = '系统';
		} else {
			$user_model = new UserModel();
			$user_info = $user_model->getInfo([ 'uid' => $uid ], 'nick_name');
			if (!empty($user_info)) {
				$user_name = $user_info['nick_name'];
			} else {
				$user_name = '未知';
			}
			
		}
		$data = array(
			'uid' => $uid,
			'url' => $url,
			'user_name' => $user_name,
			'is_system' => $is_system,
			'controller' => $controller,
			'method' => $method,
			'ip' => $ip,
			'data' => $get_data,
			'create_time' => time()
		);
		$user_log = new UserLogModel();
		$res = $user_log->save($data);
		return $res;
	}
	
}