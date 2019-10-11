<?php
/**
 * Wchat.php
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

namespace app\admin\controller;

use data\extend\WchatOauth;
use data\service\Config;
use data\service\Shop;
use data\service\Weixin;
use data\service\WeixinMessage;

/**
 * 微信管理
 */
class Wchat extends BaseController
{
	/**
	 * 微信账户设置
	 */
	public function config()
	{
		$config = new Config();
		$wchat_config = $config->getInstanceWchatConfig($this->instance_id);
		// 获取当前域名
		$domain_name = \think\Request::instance()->domain();
		$url = $domain_name . \think\Request::instance()->root();
		// 去除链接的http://头部
		if (strstr($url, 'http://')) {
			$url_top = substr($url, 7);
		} elseif (strstr($url, 'https://')) {
			$url_top = substr($url, 8);
		}
		// 去除链接的尾部index.php
		$url_top = str_replace('/index.php', '', $url_top);
		$call_back_url = __URL(__URL__ . '/wap/wchat/relateWeixin');
		// $call_back_url = str_replace('/index.php', '', $call_back_url);
		$this->assign("url", $url_top);
		$this->assign("call_back_url", $call_back_url);
		$this->assign('wchat_config', $wchat_config["value"]);
		return view($this->style . 'Wchat/config');
	}
	
	/**
	 * 修改微信配置
	 */
	public function setInstanceWchatConfig()
	{
		$config = new Config();
		$appid = str_replace(' ', '', request()->post('appid', ''));
		$appsecret = str_replace(' ', '', request()->post('appsecret', ''));
		$token = request()->post('token', '');
		$res = $config->setInstanceWchatConfig($this->instance_id, $appid, $appsecret, $token);
		return AjaxReturn($res);
	}
	
	/**
	 * 小程序账户设置
	 */
	public function appletConfig()
	{
		$config = new Config();
		// 获取当前域名
		$domain_name = \think\Request::instance()->domain();
		$this->assign("url", $domain_name);
		$applet_config = $config->getInstanceAppletConfig($this->instance_id);
		$this->assign('applet_config', $applet_config["value"]);
		return view($this->style . 'Wchat/appletConfig');
	}
	
	/**
	 * 修改小程序配置
	 */
	public function setInstanceAppletConfig()
	{
		$config = new Config();
		$appid = str_replace(' ', '', request()->post('appid', ''));
		$appsecret = str_replace(' ', '', request()->post('appsecret', ''));
		
		$res = $config->setInstanceAppletConfig($this->instance_id, $appid, $appsecret);
		return AjaxReturn($res);
	}
	
	/**
	 * 微信菜单
	 */
	public function menu()
	{
		$weixin = new Weixin();
		$menu_list = $weixin->getWchatMenuQuery($this->instance_id);
		$default_menu_info = array(); // 默认显示菜单
		$menu_list_count = count($menu_list);
		$class_index = count($menu_list);
		if ($class_index > 0) {
			if ($class_index == MAX_MENU_LENGTH) {
				$class_index = MAX_MENU_LENGTH - 1;
			}
		}
		if ($menu_list_count > 0) {
			$default_menu_info = $menu_list[ $menu_list_count - 1 ];
		} else {
			$default_menu_info["menu_name"] = "";
			$default_menu_info["menu_id"] = 0;
			$default_menu_info["child_count"] = 0;
			$default_menu_info["media_id"] = 0;
			$default_menu_info["menu_event_url"] = "";
			$default_menu_info["menu_event_type"] = 1;
		}
		$media_detail = array();
		if ($default_menu_info["media_id"]) {
			// 查询图文消息
			$media_detail = $weixin->getWeixinMediaDetail($default_menu_info["media_id"]);
			$media_detail["item_list_count"] = count($media_detail["item_list"]);
		} else {
			$media_detail["create_time"] = "";
			$media_detail["title"] = "";
			$media_detail["item_list_count"] = 0;
		}
		$default_menu_info["media_list"] = $media_detail;
		$this->assign("wx_name", $this->instance_name);
		$this->assign("menu_list", $menu_list);
		$this->assign("MAX_MENU_LENGTH", MAX_MENU_LENGTH); // 一级菜单数量
		$this->assign("MAX_SUB_MENU_LENGTH", MAX_SUB_MENU_LENGTH); // 二级菜单数量
		$this->assign("menu_list_count", $menu_list_count);
		$this->assign("default_menu_info", $default_menu_info);
		$this->assign("class_index", $class_index);
		return view($this->style . 'Wchat/wxMenu');
	}
	
	/**
	 * 更新菜单到微信,保存并发布
	 */
	public function updateMenuToWeixin()
	{
		$weixin = new Weixin();
		$result = $weixin->updateInstanceMenuToWeixin($this->instance_id);
		$config = new Config();
		$auth_info = $config->getInstanceWchatConfig($this->instance_id);
		if (!empty($auth_info['value']['appid']) && !empty($auth_info['value']['appsecret'])) {
			$wchat_auth = new WchatOauth();
			$res = $wchat_auth->menu_create($result);
			if (!empty($res)) {
				$res = json_decode($res, true);
				if ($res['errcode'] == 0) {
					$retval = [
						"code" => 1,
						"message" => "保存成功"
					];
				} else {
					$retval = [
						"code" => 1,
						"message" => $res['errmsg']
					];
				}
			} else {
				$retval = [
					"code" => 0,
					"message" => "操作失败"
				];
			}
		} else {
			$retval = [
				"code" => 0,
				"message" => "当前未配置微信授权"
			];
		}
		return $retval;
	}
	
	/**
	 * 添加微信自定义菜单
	 */
	public function addWeixinMenu()
	{
		$menu = request()->post('menu', '');
		if (!empty($menu)) {
			$menu = json_decode($menu, true);
			$weixin = new Weixin();
			$data = [
				'instance_id' => $this->instance_id,
				'media_id' => $menu['media_id'],// '图文消息ID'
				'menu_name' => $menu["menu_name"],// 菜单名称
				'pid' => $menu["pid"],// 父级菜单（一级菜单）
				'ico' => '',// 菜图标单
				'menu_event_type' => $menu["menu_event_type"],// '1普通url 2 图文素材 3 功能'
				'menu_event_url' => $menu['menu_event_url'],// '菜单url'
				'sort' => $menu['sort'],// 排序
				'create_date' => time()
			];
			
			$res = $weixin->addWeixinMenu($data);
			return $res;
		}
		return -1;
	}
	
	/**
	 * 修改微信自定义菜单
	 */
	public function updateWeixinMenu()
	{
		$menu = request()->post('menu', '');
		if (!empty($menu)) {
			$weixin = new Weixin();
			$data = [
				'instance_id' => $this->instance_id,
				'menu_id' => $menu['menu_id'],
				'media_id' => $menu['media_id'],// '图文消息ID'
				'menu_name' => $menu["menu_name"],// 菜单名称
				'pid' => $menu["pid"],// 父级菜单（一级菜单）
				'ico' => '',// 菜图标单
				'menu_event_type' => $menu["menu_event_type"],// '1普通url 2 图文素材 3 功能'
				'menu_event_url' => $menu['menu_event_url'],// '菜单url'
				'sort' => $menu['sort'],// 排序
				'modify_date' => time()
			];
			$res = $weixin->updateWeixinMenu($data);
			return $res;
		}
		return -1;
	}
	
	/**
	 * 修改排序
	 */
	public function modifyWeixinMenuSort()
	{
		$menu_id_arr = request()->post('menu_id_arr', '');
		if (!empty($menu_id_arr)) {
			$menu_id_arr = explode(",", $menu_id_arr);
			$weixin = new Weixin();
			$res = $weixin->modifyWeixinMenuSort($menu_id_arr);
			return $res;
		}
		return -1;
	}
	
	/**
	 * 修改微信菜单名称
	 */
	public function modifyWeixinMenuName()
	{
		$menu_name = request()->post('menu_name', '');
		$menu_id = request()->post('menu_id', '');
		if (!empty($menu_name)) {
			$weixin = new Weixin();
			$res = $weixin->modifyWeixinMenuName($menu_id, $menu_name);
			return $res;
		}
		return -1;
	}
	
	/**
	 * 修改跳转链接地址
	 */
	public function modifyWeixinMenuUrl()
	{
		$menu_event_url = request()->post('menu_event_url', '');
		$menu_id = request()->post('menu_id', '');
		if (!empty($menu_event_url)) {
			$weixin = new Weixin();
			$res = $weixin->modifyWeixinMenuUrl($menu_id, $menu_event_url);
			return $res;
		}
		return -1;
	}
	
	/**
	 * 修改菜单类型，1：文本，2：单图文，3：多图文
	 */
	public function modifyWeixinMenuEventType()
	{
		$menu_event_type = request()->post('menu_event_type', '');
		$menu_id = request()->post('menu_id', '');
		if (!empty($menu_event_type)) {
			$weixin = new Weixin();
			$res = $weixin->modifyWeixinMenuEventType($menu_id, $menu_event_type);
			return $res;
		}
		return -1;
	}
	
	/**
	 * 修改图文消息
	 */
	public function modifyWeiXinMenuMessage()
	{
		$menu_event_type = request()->post('menu_event_type', '');
		$menu_id = request()->post('menu_id', '');
		$media_id = request()->post('media_id', '');
		if (!empty($menu_event_type)) {
			$weixin = new Weixin();
			$res = $weixin->modifyWeiXinMenuMessage($menu_id, $media_id, $menu_event_type);
			return $res;
		}
		return -1;
	}
	
	/**
	 * 删除微信自定义菜单
	 */
	public function deleteWeixinMenu()
	{
		$menu_id = request()->post('menu_id', '');
		if (!empty($menu_id)) {
			$weixin = new Weixin();
			$res = $weixin->deleteWeixinMenu($menu_id);
			return $res;
		}
		return -1;
	}
	
	/**
	 * 获取图文素材
	 */
	public function getWeixinMediaDetail()
	{
		$media_id = request()->post('media_id', '');
		$weixin = new Weixin();
		$res = $weixin->getWeixinMediaDetail($media_id);
		return $res;
	}
	
	/**
	 * 回复设置
	 */
	public function replayConfig()
	{
		$type = request()->get('type', 1);
		$child_menu_list = array(
			array(
				'url' => "wchat/replayConfig?type=1",
				'menu_name' => "关注时回复",
				"active" => $type == 1 ? 1 : 0
			),
			array(
				'url' => "wchat/replayConfig?type=2",
				'menu_name' => "关键字回复",
				"active" => $type == 2 ? 1 : 0
			),
			array(
				'url' => "wchat/replayConfig?type=3",
				'menu_name' => "默认回复",
				"active" => $type == 3 ? 1 : 0
			)
		);
		$this->assign('child_menu_list', $child_menu_list);
		$this->assign('type', $type);
		if ($type == 1) {
			$weixin = new Weixin();
			$info = $weixin->getFollowReplayDetail([
				'instance_id' => $this->instance_id
			]);
			$this->assign('info', $info);
		} elseif ($type == 2) {
		} elseif ($type == 3) {
			$weixin = new Weixin();
			$info = $weixin->getDefaultReplayDetail([
				'instance_id' => $this->instance_id
			]);
			$this->assign('info', $info);
		}
		return view($this->style . 'Wchat/replayConfig');
	}
	
	/**
	 * 添加 或 修改 关注时回复
	 */
	public function addOrUpdateFollowReply()
	{
		$weixin = new Weixin();
		$id = request()->post('id', -1);
		$replay_media_id = request()->post('media_id', 0);
		$res = -1;
		$data = [
			'instance_id' => $this->instance_id,
			'reply_media_id' => $replay_media_id,
			'sort' => 0,
		];
		if ($id < 0) {
			$res = -1;
		} elseif ($id == 0) {
			if ($replay_media_id > 0) {
				$data['create_time'] = time();
				$res = $weixin->addFollowReplay($data);
			} else {
				$res = -1;
			}
		} elseif ($id > 0) {
			if ($replay_media_id > 0) {
				$data['modify_time'] = time();
				$data['id'] = $id;
				$res = $weixin->updateFollowReplay($data);
			} else {
				$res = -1;
			}
		}
		return AjaxReturn($res);
	}
	
	/**
	 * 添加 或 修改 关注时回复
	 */
	public function addOrUpdateDefaultReply()
	{
		$weixin = new Weixin();
		$id = request()->post('id', -1);
		$replay_media_id = request()->post('media_id', 0);
		$res = -1;
		$data = [
			'instance_id' => $this->instance_id,
			'reply_media_id' => $replay_media_id,
			'sort' => 0,
		];
		if ($id < 0) {
			$res = -1;
		} elseif ($id == 0) {
			if ($replay_media_id > 0) {
				$data['create_time'] = time();
				$res = $weixin->addDefaultReplay($data);
			} else {
				$res = -1;
			}
		} elseif ($id > 0) {
			if ($replay_media_id > 0) {
				$data['modify_time'] = time();
				$data['id'] = $id;
				$res = $weixin->updateDefaultReplay($data);
			} else {
				$res = -1;
			}
		}
		return AjaxReturn($res);
	}
	
	/**
	 * 删除图文消息
	 */
	public function deleteWeixinMedia()
	{
		$media_id = request()->post('media_id', '');
		$res = 0;
		if (!empty($media_id)) {
			$weixin = new Weixin();
			$res = $weixin->deleteWeixinMedia($media_id);
		}
		return $res;
	}
	
	/**
	 * 删除图文详情页列表
	 */
	public function deleteWeixinMediaDetail()
	{
		$id = request()->post('id', '');
		$res = 0;
		if (!empty($id)) {
			$weixin = new Weixin();
			$res = $weixin->deleteWeixinMediaDetail($id);
		}
		return $res;
	}
	
	public function materialMessage()
	{
		$type = request()->get('type', 1);
		$child_menu_list = array(
			/* array(
				'url' => "wchat/materialMessage",
				'menu_name' => "全部",
				"active" => $type == 0 ? 1 : 0
			), */
			array(
				'url' => "wchat/materialMessage?type=1",
				'menu_name' => "文本",
				"active" => $type == 1 ? 1 : 0
			),
			array(
				'url' => "wchat/materialMessage?type=2",
				'menu_name' => "单图文",
				"active" => $type == 2 ? 1 : 0
			),
			array(
				'url' => "wchat/materialMessage?type=3",
				'menu_name' => "多图文",
				"active" => $type == 3 ? 1 : 0
			)
		);
		$type_name_arr = [
			'1' => '文本',
			'2' => '单图文',
			'3' => '多图文',
		];
		if (request()->isAjax()) {
			$type = request()->post('type', 0);
			$search_text = request()->post('search_text', '');
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$weixin = new Weixin();
			$condition = array();
			if ($type != 0) {
				$condition['type'] = $type;
			}
			$condition['title'] = array(
				'like',
				'%' . $search_text . '%'
			);
			$condition = array_filter($condition);
			$list = $weixin->getWeixinMediaList($page_index, $page_size, $condition, 'create_time desc');
			return $list;
		}
		$this->assign('type', $type);
		$this->assign('type_name', $type_name_arr[ $type ]);
		$this->assign('child_menu_list', $child_menu_list);
		return view($this->style . 'Wchat/materialMessage');
	}

	/**
	 * 模板消息设置
	 */
	public function templateMessage()
	{
		return view($this->style . 'Wchat/templateMessage');
	}
	
	/**
	 * 添加 消息
	 */
	public function addMedia()
	{
		if (request()->isAjax()) {
			$type = request()->post('type', '');
			$title = request()->post('title', '');
			$content = request()->post('content', '');
			$sort = 0;
			$weixin = new Weixin();
			$data = [
				'title' => $title,
				'instance_id' => $this->instance_id,
				'type' => $type,
				'sort' => $sort,
				'create_time' => time(),
				'content' => $content
			];
			$res = $weixin->addWeixinMedia($data);
			return AjaxReturn($res);
		}
		$this->assign('type', request()->get('type'));
		return view($this->style . 'Wchat/addMedia');
	}
	
	/**
	 * 修改消息素材
	 */
	public function updateMedia()
	{
		$weixin = new Weixin();
		if (request()->isAjax()) {
			$media_id = request()->post('media_id', 0);
			$type = request()->post('type', '');
			$title = request()->post('title', '');
			$content = request()->post('content', '');
			$sort = 0;
			$data = [
				'media_id' => $media_id,
				'title' => $title,
				'instance_id' => $this->instance_id,
				'type' => $type,
				'sort' => $sort,
				'create_time' => time(),
				'content' => $content
			];
			$res = $weixin->updateWeixinMedia($data);
			return AjaxReturn($res);
		}
		$media_id = request()->get('media_id', 0);
		$info = $weixin->getWeixinMediaDetail($media_id);
		$this->assign('info', $info);
		return view($this->style . 'Wchat/updateMedia');
	}
	
	/**
	 * ajax 加载 选择素材 弹框数据
	 */
	public function onloadMaterial()
	{
		$type = request()->post('type', 0);
		$search_text = request()->post('search_text', '');
		$page_index = request()->post("page_index", 1);
		$page_size = request()->post("page_size", PAGESIZE);
		$weixin = new Weixin();
		$condition = array();
		if ($type != 0) {
			$condition['type'] = $type;
		}
		$condition['title'] = array(
			'like',
			'%' . $search_text . '%'
		);
		$condition = array_filter($condition);
		$list = $weixin->getWeixinMediaList($page_index, $page_size, $condition, 'create_time desc');
		return $list;
	}
	
	/**
	 * 删除 回复
	 */
	public function delReply()
	{
		$type = request()->post('type', '');
		if ($type == '') {
			return AjaxReturn(-1);
		} else {
			if ($type == 1) {
				// 删除 关注时回复
				$weixin = new Weixin();
				$res = $weixin->deleteFollowReplay($this->instance_id);
				return AjaxReturn($res);
			} elseif ($type == 3) {
				// 删除 关注时回复
				$weixin = new Weixin();
				$res = $weixin->deleteDefaultReplay($this->instance_id);
				return AjaxReturn($res);
			}
		}
	}
	
	/**
	 * 关键字 回复
	 */
	public function keyReplayList()
	{
		$weixin = new Weixin();
		$list = $weixin->getKeyReplayList(1, 0, [
			'instance_id' => $this->instance_id
		]);
		return $list;
	}
	
	/**
	 * 添加 或 修改 关键字 回复
	 */
	public function addOrUpdateKeyReplay()
	{
		$weixin = new Weixin();
		if (request()->isAjax()) {
			$id = request()->post('id', -1);
			$key = request()->post('key', '');
			$match_type = request()->post('match_type', 1);
			$replay_media_id = request()->post('media_id', 0);
			$sort = 0;
			$data = [
				'instance_id' => $this->instance_id,
				'key' => $key,
				'match_type' => $match_type,
				'reply_media_id' => $replay_media_id,
				'sort' => $sort,
			];
			if ($id > 0) {
				$data['id'] = $id;
				$data['modify_time'] = time();
				$res = $weixin->updateKeyReplay($data);
			} elseif ($id == 0) {
				$data['create_time'] = time();
				$res = $weixin->addKeyReplay($data);
			} elseif ($id < 0) {
				$res = -1;
			}
			return AjaxReturn($res);
		}
		$id = request()->get('id', 0);
		$this->assign('id', $id);
		$info = array(
			'key' => '',
			'match_type' => 1,
			'reply_media_id' => 0,
			'madie_info' => array()
		);
		if ($id > 0) {
			$info = $weixin->getKeyReplyDetail($id);
		}
		$secend_menu['module_name'] = "编辑回复";
		$child_menu_list = array(
			array(
				'url' => "Wchat/addOrUpdateKeyReplay.html?id=" . $id,
				'menu_name' => "编辑回复",
				"active" => 1
			)
		);
		
		if (!empty($id)) {
			$this->assign("secend_menu", $secend_menu);
			$this->assign('child_menu_list', $child_menu_list);
		}
		$this->assign('info', $info);
		return view($this->style . 'Wchat/addOrUpdateKeyReplay');
	}
	
	/**
	 * 删除 回复
	 */
	public function delKeyReply()
	{
		$id = request()->post('id', '');
		if ($id == '') {
			return AjaxReturn(-1);
		} else {
			// 删除 关注时回复
			$weixin = new Weixin();
			$res = $weixin->deleteKeyReplay($id);
			return AjaxReturn($res);
		}
	}

	public function testSend()
	{
		$weixin_message = new WeixinMessage();
		$weixin = new Weixin();
		// $res = $weixin_message->sendWeixinOrderCreateMessage(1);
		
		$data = array(
			'msg_id' => 1,
			'replay_uid' => 1,
			'replay_type' => 'text',
			'content' => 'this is kefu replay message!',
			'replay_time' => time()
		);
		$weixin->addUserMessageReplay($data);
		$res = $weixin_message->sendMessageToUser('oXTarwCCbPb9eouZmwCr6CHtNI0I', 'text', 'this is kefu replay message!');
		var_dump($res);
	}
	
	public function keyConcernConfig()
	{
		if (request()->isPost()) {
			$is_show_follow = request()->post("is_show_follow", 1);
			$retval = $this->website->updateKeyConcernConfig($is_show_follow);
			return AjaxReturn($retval);
		} else {
			
			$website_info = $this->website->getWebSiteInfo();
			$this->assign("website_info", $website_info);
			return view($this->style . 'Wchat/keyConcernConfig');
		}
	}
	
	/**
	 * 粉丝留言管理
	 */
	public function fansMessageManage()
	{
		if (request()->post()) {
			$weixin = new Weixin();
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$list = $weixin->getUserMessageList($page_index, $page_size, '', 'msg_id desc');
			return $list;
			
		} else {
			$child_menu_list = $this->getMessageMenuList(1);
			//$this->assign('child_menu_list',$child_menu_list);
			return view($this->style . 'Wchat/fansMessageManage');
		}
	}
	
	/**
	 * 群发消息设置
	 */
	public function sendGroupMessage()
	{
		if (request()->post()) {
			$group = request()->post('group');
			$send_message = request()->post('send_message');
			return AjaxReturn(1);
		} else {
			$child_menu_list = $this->getMessageMenuList(2);
			$this->assign('child_menu_list', $child_menu_list);
			
			return view($this->style . 'Wchat/sendGroupMessage');
		}
		
	}
	
	/**
	 * 微信客服管理三级菜单
	 */
	public function getMessageMenuList($menu_id)
	{
		$child_menu_list = array(
			array(
				"menu_id" => 1,
				'url' => "Wchat/fansMessageManage",
				'menu_name' => "粉丝留言",
				"active" => 0
			),
			array(
				"menu_id" => 2,
				'url' => "Wchat/sendGroupMessage",
				'menu_name' => "群发消息",
				"active" => 0
			),
		);
		foreach ($child_menu_list as $k => $v) {
			if ($menu_id == $v['menu_id']) {
				$child_menu_list[ $k ]['active'] = 1;
			}
		}
		return $child_menu_list;
	}
	
	/**
	 * 修改微信菜单小程序配置
	 */
	public function modifyMenuSmallProgramConfig()
	{
		if (request()->isAjax()) {
			$type = request()->post("type", "");
			$val = request()->post("val", "");
			$menu_id = request()->post("menu_id", "");
			$weixin = new Weixin();
			$res = $weixin->modifyMenuSmallProgramConfig($menu_id, $type, $val);
			return $res;
		}
	}
	
	/**
	 * 微信推广
	 */
	public function wchatPromotion()
	{
		return view($this->style . 'Wchat/wchatPromotion');
	}
	
}