<?php
/**
 * Weixin.php
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

use addons\Nsfx\data\model\NfxShopMemberAssociationModel;
use data\extend\WchatOauth;
use data\model\UserModel;
use data\model\WeixinAuthModel;
use data\model\WeixinDefaultReplayModel;
use data\model\WeixinFansModel;
use data\model\WeixinFollowReplayModel;
use data\model\WeixinKeyReplayModel;
use data\model\WeixinMediaItemModel;
use data\model\WeixinMediaModel;
use data\model\WeixinMenuModel;
use data\model\WeixinOneKeySubscribeModel;
use data\model\WeixinQrcodeTemplateModel;
use data\model\WeixinUserMsgModel;
use data\model\WeixinUserMsgReplayModel;
use think\Cache;
use think\Request;

class Weixin extends BaseService
{
	
	/***************************************************微信菜单************************************************************/
	
	/**
	 * 添加微信菜单
	 */
	public function addWeixinMenu($data)
	{
		Cache::tag('weixin_menu')->set('getWeixinMenuList' . '_' . $data['instance_id'] . '_' . $data['pid'], null);
		$weixin_menu = new WeixinMenuModel();
		$weixin_menu->save($data);
		return $weixin_menu->menu_id;
	}
	
	/**
	 * 修改微信菜单
	 */
	public function updateWeixinMenu($data)
	{
		Cache::tag('weixin_menu')->set('getWeixinMenuList' . '_' . $data['instance_id'] . '_' . $data['pid'], null);
		$weixin_menu = new WeixinMenuModel();
		$retval = $weixin_menu->save($data, [
			"menu_id" => $data['menu_id']
		]);
		return $retval;
	}
	
	/**
	 * 修改菜单名称
	 */
	public function modifyWeixinMenuName($menu_id, $menu_name)
	{
		Cache::clear('weixin_menu');
		$weixin_menu = new WeixinMenuModel();
		$retval = $weixin_menu->save([
			"menu_name" => $menu_name
		], [
			"menu_id" => $menu_id
		]);
		return $retval;
	}
	
	/**
	 * 修改菜单排序
	 */
	public function modifyWeixinMenuSort($menu_id_arr)
	{
		Cache::clear('weixin_menu');
		$weixin_menu = new WeixinMenuModel();
		$retval = 0;
		foreach ($menu_id_arr as $k => $v) {
			$data = array(
				'sort' => $k + 1,
				'modify_date' => time()
			);
			$retval += $weixin_menu->save($data, [
				"menu_id" => $v
			]);
		}
		return $retval;
	}
	
	/**
	 * 修改跳转链接
	 */
	public function modifyWeixinMenuUrl($menu_id, $menu_event_url)
	{
		Cache::clear('weixin_menu');
		$weixin_menu = new WeixinMenuModel();
		$retval = $weixin_menu->save([
			"menu_event_url" => $menu_event_url
		], [
			"menu_id" => $menu_id
		]);
		return $retval;
	}
	
	/**
	 * 修改菜单类型，1：文本，2：单图文，3：多图文
	 */
	public function modifyWeixinMenuEventType($menu_id, $menu_event_type)
	{
		Cache::clear('weixin_menu');
		$weixin_menu = new WeixinMenuModel();
		$retval = $weixin_menu->save([
			"menu_event_type" => $menu_event_type
		], [
			"menu_id" => $menu_id
		]);
		return $retval;
	}
	
	/**
	 * 修改微信菜单图文
	 */
	public function modifyWeiXinMenuMessage($menu_id, $media_id, $menu_event_type)
	{
		Cache::clear('weixin_menu');
		$weixin_menu = new WeixinMenuModel();
		$retval = $weixin_menu->save([
			"media_id" => $media_id,
			"menu_event_type" => $menu_event_type
		], [
			"menu_id" => $menu_id
		]);
		return $retval;
	}
	
	/**
	 * 更新微信菜单
	 */
	public function updateInstanceMenuToWeixin($instance_id)
	{
		$menu = array();
		$menu_list = $this->getWchatMenuQuery($instance_id);
		if (!empty($menu_list)) {
			
			foreach ($menu_list as $k => $v) {
				if (!empty($v)) {
					$menu_item = array(
						'name' => ''
					);
					$menu_item['name'] = $v['menu_name'];
					
					// $menu_item['sub_menu'] = array();
					if (!empty($v['child'])) {
						
						foreach ($v['child'] as $k_child => $v_child) {
							if (!empty($v_child)) {
								$sub_menu = array();
								$sub_menu['name'] = $v_child['menu_name'];
								// $sub_menu['sub_menu'] = array();
								if ($v_child['menu_event_type'] == 1) {
									$sub_menu['type'] = 'view';
									$sub_menu['url'] = $v_child['menu_event_url'];
								} elseif ($v_child['menu_event_type'] == 4) {
									// 小程序
									$sub_menu['type'] = 'miniprogram';
									$sub_menu['appid'] = $v_child['appid'];
									$sub_menu['pagepath'] = $v_child['pagepath'];
									$sub_menu['url'] = $v_child['menu_event_url'];
								} else {
									$sub_menu['type'] = 'click';
									$sub_menu['key'] = $v_child['menu_id'];
								}
								
								$menu_item['sub_button'][] = $sub_menu;
							}
						}
					} else {
						if ($v['menu_event_type'] == 1) {
							$menu_item['type'] = 'view';
							$menu_item['url'] = $v['menu_event_url'];
						} elseif ($v['menu_event_type'] == 4) {
							// 小程序
							$menu_item['type'] = 'miniprogram';
							$menu_item['appid'] = $v['appid'];
							$menu_item['pagepath'] = $v['pagepath'];
							$menu_item['url'] = $v['menu_event_url'];
						} else {
							$menu_item['type'] = 'click';
							$menu_item['key'] = $v['menu_id'];
						}
					}
					$menu[] = $menu_item;
				}
			}
		}
		$menu_array = array();
		$menu_array['button'] = array();
		foreach ($menu as $k => $v) {
			$menu_array['button'][] = $v;
		}
		// 汉字不编码
		$menu_array = json_encode($menu_array);
		// 链接不转义
		//$menu_array = preg_replace_callback("/\\\u([0-9a-f]{4})/i", create_function('$matches', 'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'), $menu_array);
		$menu_array = preg_replace_callback(
			"/\\\u([0-9a-f]{4})/i",
			function ($matches) { return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE"); },
			$menu_array
		);
		return $menu_array;
	}
	
	/**
	 *小程序菜单设置
	 */
	public function modifyMenuSmallProgramConfig($menu_id, $type, $val)
	{
		Cache::clear('weixin_menu');
		$weixin_menu = new WeixinMenuModel();
		$data = array();
		if ($type == "appid") {
			$data["appid"] = $val;
		} elseif ($type == "pagepath") {
			$data["pagepath"] = $val;
		}
		$retval = $weixin_menu->save($data, [ "menu_id" => $menu_id ]);
		return $retval;
	}
	
	/**
	 * 删除微信自定义菜单
	 */
	public function deleteWeixinMenu($menu_id)
	{
		Cache::clear('weixin_menu');
		$weixin_menu = new WeixinMenuModel();
		$res = $weixin_menu->where("menu_id=$menu_id or pid=$menu_id")->delete();
		return $res;
	}
	
	/**
	 * 获取微信菜单详情
	 */
	public function getWeixinMenuInfo($menu_id)
	{
		$weixin_menu = new WeixinMenuModel();
		$data = $weixin_menu->get($menu_id);
		return $data;
	}
	
	/**
	 * 获取店铺微信菜单
	 */
	public function getWchatMenuQuery($instance_id)
	{
		$cache = Cache::tag('weixin_menu')->get('getWchatMenuQuery' . $instance_id);
		if (!empty($cache)) return $cache;
		
		$weixin_menu = new WeixinMenuModel();
		$foot_menu = $weixin_menu->getQuery([
			'instance_id' => $instance_id,
			'pid' => 0
		], '*', 'sort');
		if (!empty($foot_menu)) {
			foreach ($foot_menu as $k => $v) {
				$foot_menu[ $k ]['child'] = '';
				$second_menu = $weixin_menu->getQuery([
					'instance_id' => $instance_id,
					'pid' => $v['menu_id']
				], '*', 'sort');
				if (!empty($second_menu)) {
					$foot_menu[ $k ]['child'] = $second_menu;
					$foot_menu[ $k ]['child_count'] = count($second_menu);
				} else {
					$foot_menu[ $k ]['child_count'] = 0;
				}
			}
		}
		
		Cache::tag('weixin_menu')->set('getWchatMenuQuery' . $instance_id, $foot_menu);
		return $foot_menu;
	}
	
	/**
	 * 获取微信菜单列表，当pid=''查询全部
	 */
	public function getWeixinMenuList($instance_id, $pid = '')
	{
		$cache = Cache::tag('weixin_menu')->get('getWeixinMenuList' . '_' . $instance_id . '_' . $pid);
		if (!empty($cache)) return $cache;
		
		$weixin_menu = new WeixinMenuModel();
		if ($pid == '') {
			$list = $weixin_menu->pageQuery(1, 0, [
				'instance_id' => $instance_id
			], 'sort', '*');
		} else {
			$list = $weixin_menu->pageQuery(1, 0, [
				'instance_id' => $instance_id,
				'pid' => $pid
			], 'sort', '*');
		}
		Cache::tag('weixin_menu')->set('getWeixinMenuList' . '_' . $instance_id . '_' . $pid, $list['data']);
		return $list['data'];
	}
	
	/********************************************************微信菜单结束****************************************************/
	
	/***********************************************微信关注回复*************************************************************/
	/**
	 * 添加关注回复
	 */
	public function addFollowReplay($data)
	{
		Cache::clear('weixin');
		$weixin_follow_replay = new WeixinFollowReplayModel();
		$weixin_follow_replay->save($data);
		return $weixin_follow_replay->id;
	}
	
	/**
	 * 修改关注回复
	 */
	public function updateFollowReplay($data)
	{
		Cache::clear('weixin');
		$weixin_follow_replay = new WeixinFollowReplayModel();
		$retval = $weixin_follow_replay->save($data, [
			'id' => $data['id']
		]);
		return $retval;
	}
	
	/**
	 * 删除关注回复
	 */
	public function deleteFollowReplay($instance_id)
	{
		Cache::clear('weixin');
		$weixin_follow_replay = new WeixinFollowReplayModel();
		return $weixin_follow_replay->destroy([
			'instance_id' => $instance_id
		]);
	}
	
	/**
	 * 获取关注回复
	 */
	public function getSubscribeReplay($instance_id)
	{
		$cache = Cache::tag('weixin')->get('getSubscribeReplay' . $instance_id);
		if (!empty($cache)) return $cache;
		
		$weixin_flow_replay = new WeixinFollowReplayModel();
		$info = $weixin_flow_replay->getInfo([
			'instance_id' => $instance_id
		], '*');
		if (!empty($info)) {
			$media_detail = $this->getWeixinMediaDetail($info['reply_media_id']);
			$content = $this->getMediaWchatStruct($media_detail);
		} else {
			$content = '';
		}
		Cache::tag('weixin')->set('getSubscribeReplay' . $instance_id, $content);
		return $content;
	}
	
	/**
	 * 获取关注回复数据信息
	 */
	public function getFollowReplayDetail($condition)
	{
		$cache = Cache::tag('weixin')->get('getFollowReplayDetail' . json_encode($condition));
		if (!empty($cache)) return $cache;
		
		$weixin_follow_replay = new WeixinFollowReplayModel();
		$info = $weixin_follow_replay->getInfo($condition);
		if ($info['reply_media_id'] > 0) {
			$info['media_info'] = $this->getWeixinMediaDetail($info['reply_media_id']);
		}
		
		Cache::tag('weixin')->set('getFollowReplayDetail' . json_encode($condition), $info);
		return $info;
	}
	
	/**
	 * 获取关注时回复列表
	 */
	public function getFollowReplayList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$cache = Cache::tag('weixin')->get('getFollowReplayList' . json_encode([ $page_index, $page_size, $condition, $order ]));
		if (!empty($cache)) return $cache;
		
		$weixin_follow_replay = new WeixinFollowReplayModel();
		$list = $weixin_follow_replay->pageQuery($page_index, $page_size, $condition, $order, '*');
		Cache::tag('weixin')->set('getFollowReplayList' . json_encode([ $page_index, $page_size, $condition, $order ]), $list);
		
		return $list;
	}
	
	/******************************************************关注回复结束******************************************************/
	
	/******************************************************微信默认回复******************************************************/
	
	/**
	 * 添加默认回复
	 */
	public function addDefaultReplay($data)
	{
		Cache::clear('weixin');
		$weixin_default_replay = new WeixinDefaultReplayModel();
		$weixin_default_replay->save($data);
		return $weixin_default_replay->id;
	}
	
	/**
	 * 修改默认回复
	 */
	public function updateDefaultReplay($data)
	{
		Cache::clear('weixin');
		$weixin_default_replay = new WeixinDefaultReplayModel();
		$retval = $weixin_default_replay->save($data, [
			'id' => $data['id']
		]);
		return $retval;
	}
	
	/**
	 * 删除默认回复
	 */
	public function deleteDefaultReplay($instance_id)
	{
		Cache::clear('weixin');
		$weixin_default_replay = new WeixinDefaultReplayModel();
		return $weixin_default_replay->destroy([
			'instance_id' => $instance_id
		]);
	}
	
	/**
	 * 获取默认回复
	 */
	public function getDefaultReplay($instance_id)
	{
		$cache = Cache::tag('weixin')->get('getDefaultReplay' . $instance_id);
		if (!empty($cache)) return $cache;
		
		$weixin_default_replay = new WeixinDefaultReplayModel();
		$info = $weixin_default_replay->getInfo([
			'instance_id' => $instance_id
		], '*');
		if (!empty($info)) {
			$media_detail = $this->getWeixinMediaDetail($info['reply_media_id']);
			$content = $this->getMediaWchatStruct($media_detail);
		} else {
			$content = '';
		}
		Cache::tag('weixin')->set('getDefaultReplay' . $instance_id, $content);
		return $content;
	}
	
	/**
	 * 获取默认回复数据
	 */
	public function getDefaultReplayDetail($condition)
	{
		$cache = Cache::tag('weixin')->get('getDefaultReplayDetail' . json_encode($condition));
		if (!empty($cache)) return $cache;
		
		$weixin_default_replay = new WeixinDefaultReplayModel();
		$info = $weixin_default_replay->get($condition);
		if ($info['reply_media_id'] > 0) {
			$info['media_info'] = $this->getWeixinMediaDetail($info['reply_media_id']);
		}
		Cache::tag('weixin')->set('getDefaultReplayDetail' . json_encode($condition), $info);
		return $info;
	}
	
	/**
	 * 获取默认回复列表
	 */
	public function getDefaultReplayList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$cache = Cache::tag('weixin')->get('getDefaultReplayList' . json_encode([ $page_index, $page_size, $condition, $order ]));
		if (!empty($cache)) return $cache;
		
		$weixin_default_replay = new WeixinDefaultReplayModel();
		$list = $weixin_default_replay->pageQuery($page_index, $page_size, $condition, $order, '*');
		Cache::tag('weixin')->set('getDefaultReplayList' . json_encode([ $page_index, $page_size, $condition, $order ]), $list);
		
		return $list;
	}
	
	/******************************************************微信默认回复结束***************************************************/
	/******************************************************微信关键字回复****************************************************/
	
	/**
	 * 添加关键字回复
	 */
	public function addKeyReplay($data)
	{
		Cache::clear('weixin');
		$weixin_key_replay = new WeixinKeyReplayModel();
		$weixin_key_replay->save($data);
		return $weixin_key_replay->id;
	}
	
	/**
	 * 修改关键字回复
	 */
	public function updateKeyReplay($data)
	{
		Cache::clear('weixin');
		$weixin_key_replay = new WeixinKeyReplayModel();
		$retval = $weixin_key_replay->save($data, [
			'id' => $data['id']
		]);
		return $retval;
	}
	
	/**
	 * 删除关键字回复
	 */
	public function deleteKeyReplay($id)
	{
		Cache::clear('weixin');
		$weixin_key_replay = new WeixinKeyReplayModel();
		return $weixin_key_replay->destroy($id);
	}
	
	/**
	 * 获取关键字回复
	 */
	public function getWhatReplay($instance_id, $key_words)
	{
		$weixin_key_replay = new WeixinKeyReplayModel();
		// 全部匹配
		$condition = array(
			'instance_id' => $instance_id,
			'key' => $key_words,
			'match_type' => 2
		);
		$info = $weixin_key_replay->getInfo($condition, '*');
		if (empty($info)) {
			// 模糊匹配
			$condition = array(
				'instance_id' => $instance_id,
				'key' => array(
					'LIKE',
					'%' . $key_words . '%'
				),
				'match_type' => 1
			);
			$info = $weixin_key_replay->getInfo($condition, '*');
		}
		if (!empty($info)) {
			$media_detail = $this->getWeixinMediaDetail($info['reply_media_id']);
			$content = $this->getMediaWchatStruct($media_detail);
			return $content;
		} else {
			return '';
		}
	}
	
	/**
	 * 获取关键字回复信息
	 */
	public function getKeyReplyDetail($id)
	{
		$cache = Cache::tag('weixin')->get('getKeyReplyDetail' . $id);
		if (!empty($cache)) return $cache;
		
		$weixin_key_replay = new WeixinKeyReplayModel();
		$info = $weixin_key_replay->get($id);
		if ($info['reply_media_id'] > 0) {
			$info['media_info'] = $this->getWeixinMediaDetail($info['reply_media_id']);
		}
		Cache::tag('weixin')->set('getKeyReplyDetail' . $id, $info);
		return $info;
	}
	
	/**
	 * 获取关键词回复列表
	 */
	public function getKeyReplayList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$cache = Cache::tag('weixin')->get('getKeyReplayList' . json_encode([ $page_index, $page_size, $condition, $order ]));
		if (!empty($cache)) return $cache;
		
		$weixin_key_replay = new WeixinKeyReplayModel();
		$list = $weixin_key_replay->pageQuery($page_index, $page_size, $condition, $order, '*');
		Cache::tag('weixin')->set('getKeyReplayList' . json_encode([ $page_index, $page_size, $condition, $order ]), $list);
		return $list;
	}
	
	/******************************************************微信关键字回复结束*************************************************/
	
	/******************************************************微信图文消息管理**************************************************/
	
	/**
	 * 添加图文消息
	 */
	public function addWeixinMedia($data)
	{
		Cache::clear('weixin');
		$weixin_media = new WeixinMediaModel();
		$weixin_media->startTrans();
		try {
			$data_media = array(
				'title' => $data['title'],
				'instance_id' => $data['instance_id'],
				'type' => $data['type'],
				'sort' => $data['sort'],
				'create_time' => $data['time']
			);
			$weixin_media->save($data_media);
			$media_id = $weixin_media->media_id;
			if ($data['type'] == 1) {
				$this->addWeixinMediaItem($media_id, $data['title'], '', '', '', '', '', '', 0);
			} elseif ($data['type'] == 2) {
				$info = explode('`|`', $data['content']);
				$this->addWeixinMediaItem($media_id, $info[0], $info[1], $info[2], $info[3], $info[4], $info[5], $info[6], 0);
			} elseif ($data['type'] == 3) {
				$list = explode('`$`', $data['content']);
				foreach ($list as $k => $v) {
					$arr = explode('`|`', $v);
					$this->addWeixinMediaItem($media_id, $arr[0], $arr[1], $arr[2], $arr[3], $arr[4], $arr[5], $arr[6], 0);
				}
			}
			$weixin_media->commit();
			return 1;
		} catch (\Exception $e) {
			$weixin_media->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 添加图文消息内容
	 */
	private function addWeixinMediaItem($media_id, $title, $author, $cover, $show_cover_pic, $summary, $content, $content_source_url, $sort)
	{
		Cache::clear('weixin');
		$weixin_media_item = new WeixinMediaItemModel();
		$data = array(
			'media_id' => $media_id,
			'title' => $title,
			'author' => $author,
			'cover' => $cover,
			'show_cover_pic' => $show_cover_pic,
			'summary' => $summary,
			'content' => $content,
			'content_source_url' => $content_source_url,
			'sort' => $sort
		);
		$retval = $weixin_media_item->save($data);
		return $retval;
	}
	
	/**
	 * 修改图文消息
	 */
	public function updateWeixinMedia($data)
	{
		$weixin_media = new WeixinMediaModel();
		$weixin_media->startTrans();
		try {
			// 先修改 图文消息表
			$data_media = array(
				'title' => $data['title'],
				'instance_id' => $data['instance_id'],
				'type' => $data['type'],
				'sort' => $data['sort'],
				'create_time' => $data['time']
			);
			$weixin_media->save($data_media, [
				'media_id' => $data['media_id']
			]);
			// 修改 图文消息内容的时候 先删除了图文消息内容再添加一次
			$weixin_media_item = new WeixinMediaItemModel();
			$weixin_media_item->destroy([
				'media_id' => $data['media_id']
			]);
			if ($data['type'] == 1) {
				$this->addWeixinMediaItem($data['media_id'], $data['title'], '', '', '', '', '', '', 0);
			} elseif ($data['type'] == 2) {
				$info = explode('`|`', $data['content']);
				$this->addWeixinMediaItem($data['media_id'], $info[0], $info[1], $info[2], $info[3], $info[4], $info[5], $info[6], 0);
			} elseif ($data['type'] == 3) {
				$list = explode('`$`', $data['content']);
				foreach ($list as $k => $v) {
					$arr = explode('`|`', $v);
					$this->addWeixinMediaItem($data['media_id'], $arr[0], $arr[1], $arr[2], $arr[3], $arr[4], $arr[5], $arr[6], 0);
				}
			}
			$weixin_media->commit();
			Cache::clear('weixin');
			return 1;
		} catch (\Exception $e) {
			$weixin_media->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 删除图文消息
	 */
	public function deleteWeixinMedia($media_id)
	{
		Cache::clear('weixin');
		$weixin_media = new WeixinMediaModel();
		$res = $weixin_media->destroy([
			'media_id' => $media_id,
			'instance_id' => $this->instance_id
		]);
		if ($res) {
			$weixin_media_item = new WeixinMediaItemModel();
			$weixin_media_item->destroy([
				'media_id' => $media_id
			]);
		}
		
		return $res;
	}
	
	/**
	 * 删除图文消息详情下列表
	 */
	public function deleteWeixinMediaDetail($id)
	{
		Cache::clear('weixin');
		$weixin_media_item = new WeixinMediaItemModel();
		$res = $weixin_media_item->where("id=$id")->delete();
		return $res;
	}
	
	/**
	 * 获取图文消息详情，包括子
	 */
	public function getWeixinMediaDetail($media_id)
	{
		// 		$cache = Cache::tag('weixin')->get('getWeixinMediaDetail' . $media_id);
		// 		if (!empty($cache)) return $cache;
		
		$weixin_media = new WeixinMediaModel();
		$weixin_media_info = $weixin_media->get($media_id);
		if (!empty($weixin_media_info)) {
			$weixin_media_item = new WeixinMediaItemModel();
			$item_list = $weixin_media_item->getQuery([
				'media_id' => $media_id
			]);
			$weixin_media_info['item_list'] = $item_list;
		}
		// 		Cache::tag('weixin')->set('getWeixinMediaDetail' . $media_id, $weixin_media_info);
		return $weixin_media_info;
	}
	
	/**
	 * 根据图文消息id查询
	 */
	public function getWeixinMediaDetailByMediaId($media_id)
	{
		$cache = Cache::tag('weixin')->get('getWeixinMediaDetailByMediaId' . $media_id);
		if (!empty($cache)) return $cache;
		
		$weixin_media_item = new WeixinMediaItemModel();
		$item_list = $weixin_media_item->getInfo([
			'id' => $media_id
		], '*');
		
		if (!empty($item_list)) {
			
			// 主表
			$weixin_media = new WeixinMediaModel();
			$weixin_media_info["media_parent"] = $weixin_media->getInfo([
				"media_id" => $item_list["media_id"]
			], "*");
			
			// 微信配置
			$weixin_auth = new WeixinAuthModel();
			$weixin_media_info["weixin_auth"] = $weixin_auth->getInfo([
				"instance_id" => $weixin_media_info["media_parent"]["instance_id"]
			], "*");
			
			$weixin_media_info["media_item"] = $item_list;
			
			// 更新阅读次数
			$weixin_media_item->save([
				"hits" => ($item_list["hits"] + 1)
			], [
				"id" => $media_id
			]);
			Cache::tag('weixin')->set('getWeixinMediaDetailByMediaId' . $media_id, $weixin_media_info);
			return $weixin_media_info;
		}
		Cache::tag('weixin')->set('getWeixinMediaDetailByMediaId' . $media_id, null);
		return null;
	}
	
	/**
	 * 获取图文消息微信结构
	 */
	public function getMediaWchatStruct($media_info)
	{
		switch ($media_info['type']) {
			case "1":
				$contentStr = trim($media_info['title']);
				break;
			case "2":
				if (strstr($media_info['item_list'][0]['cover'], "http")) {
					$pic_url = $media_info['item_list'][0]['cover'];
				} else {
					$pic_url = Request::instance()->domain() . '/' . $media_info['item_list'][0]['cover'];
				}
				$contentStr[] = array(
					"Title" => $media_info['item_list'][0]['title'],
					"Description" => $media_info['item_list'][0]['summary'],
					"PicUrl" => $pic_url,
					"Url" => __URL(__URL__ . '/wap/wchat/message?media_id=' . $media_info['item_list'][0]['id'])
				);
				break;
			case "3":
				$contentStr = array();
				foreach ($media_info['item_list'] as $k => $v) {
					if (strstr($v['cover'], "http")) {
						$pic_url = $v['cover'];
					} else {
						$pic_url = Request::instance()->domain() . '/' . $v['cover'];
					}
					$contentStr[ $k ] = array(
						"Title" => $v['title'],
						"Description" => $v['summary'],
						"PicUrl" => $pic_url,
						"Url" => __URL(__URL__ . '/wap/wchat/message?media_id=' . $v['id'])
					);
				}
				break;
			default:
				$contentStr = "";
				break;
		}
		return $contentStr;
	}
	
	/**
	 * 获取微信图文消息列表
	 */
	public function getWeixinMediaList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$cache = Cache::tag('weixin')->get('getWeixinMediaList' . json_encode([ $page_index, $page_size, $condition, $order ]));
		if (!empty($cache)) return $cache;
		
		$weixin_media = new WeixinMediaModel();
		$weixin_media_item = new WeixinMediaItemModel();
		$list = $weixin_media->pageQuery($page_index, $page_size, $condition, $order, '*');
		if (!empty($list)) {
			foreach ($list['data'] as $k => $v) {
				$item_list = $weixin_media_item->getQuery([
					'media_id' => $v['media_id']
				], 'title');
				$list['data'][ $k ]['item_list'] = $item_list;
			}
		}
		Cache::tag('weixin')->set('getWeixinMediaList' . json_encode([ $page_index, $page_size, $condition, $order ]), $list);
		return $list;
	}
	/****************************************************图文消息结束********************************************************/
	
	/*********************************************************微信授权*****************************************************/
	/**
	 * 公众号授权
	 */
	public function addWeixinAuth($params)
	{
		Cache::clear('weixin');
		$weixin_auth = new WeixinAuthModel();
		$data = array(
			'instance_id' => $params['instance_id'],
			'authorizer_appid' => $params['authorizer_appid'],
			'authorizer_refresh_token' => $params['authorizer_refresh_token'],
			'authorizer_access_token' => $params['authorizer_access_token'],
			'func_info' => $params['func_info'],
			'nick_name' => $params['nick_name'],
			'head_img' => $params['head_img'],
			'user_name' => $params['user_name'],
			'alias' => $params['alias'],
			'qrcode_url' => $params['qrcode_url'],
			'auth_time' => time()
		);
		$count = $weixin_auth->where([
			'instance_id' => $params['instance_id']
		])->count();
		$weixin_auth = new WeixinAuthModel();
		if ($count == 0) {
			$retval = $weixin_auth->save($data);
		} else {
			$retval = $weixin_auth->save($data, [
				'instance_id' => $params['instance_id']
			]);
		}
		
		return $retval;
	}
	
	/**
	 * 获取微信授权列表
	 */
	public function getWeixinAuthList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$cache = Cache::tag('weixin')->get('getWeixinAuthList' . json_encode([ $page_index, $page_size, $condition, $order ]));
		if (!empty($cache)) return $cache;
		
		$weixin_auth = new WeixinAuthModel();
		$list = $weixin_auth->pageQuery($page_index, $page_size, $condition, $order, '*');
		Cache::tag('weixin')->set('getWeixinAuthList' . json_encode([ $page_index, $page_size, $condition, $order ]), $list);
		
		return $list;
	}
	
	/**
	 * 通过author_appid获取shopid
	 */
	public function getShopidByAuthorAppid($author_appid)
	{
		$cache = Cache::tag('weixin')->get('getShopidByAuthorAppid' . $author_appid);
		if (!empty($cache)) return $cache;
		
		$weixin_auth = new WeixinAuthModel();
		$instance_id = $weixin_auth->getInfo([
			'authorizer_appid' => $author_appid
		], 'instance_id');
		if (!empty($instance_id['instance_id'])) {
			$res = $instance_id['instance_id'];
		} else {
			$res = '';
		}
		
		Cache::tag('weixin')->set('getShopidByAuthorAppid' . $author_appid, $res);
		return $res;
	}
	
	/**
	 * 通过appid获取公众账号信息
	 */
	public function getWeixinInfoByAppid($author_appid)
	{
		$weixin_auth = new WeixinAuthModel();
		$info = $weixin_auth->getInfo([
			'authorizer_appid' => $author_appid
		], '*');
		return $info;
	}
	
	/**
	 * 获取店铺微信授权信息
	 */
	public function getWeixinAuthInfo($instance_id)
	{
		$cache = Cache::tag('weixin')->get('getWeixinAuthInfo' . $instance_id);
		if (!empty($cache)) return $cache;
		
		$weixin_auth = new WeixinAuthModel();
		$data = $weixin_auth->getInfo([
			'instance_id' => $instance_id
		], '*');
		
		Cache::tag('weixin')->set('getWeixinAuthInfo' . $instance_id, $data);
		return $data;
	}
	/****************************************************微信授权结束********************************************************/
	
	/****************************************************微信粉丝***********************************************************/
	
	/**
	 * 用户关注添加粉丝信息
	 */
	public function addWeixinFans($data)
	{
		Cache::clear('weixin');
		if (empty($data['openid'])) {
			return 1;
		}
		$weixin_fans = new WeixinFansModel();
		$count = $weixin_fans->where([
			'openid' => $data['openid']
		])->count();
		$weixin_fans = new WeixinFansModel();
		$data['subscribe_date'] = time();
		if ($count == 0) {
			$retval = $weixin_fans->save($data);
		} else {
			$retval = $weixin_fans->save($data, [
				'openid' => $data['openid']
			]);
		}
		return $retval;
	}
	
	/**
	 * 通过微信openID查询uid
	 */
	public function getWeixinUidByOpenid($openid)
	{
		$weixin_fans = new WeixinFansModel();
		$uid = $weixin_fans->getInfo([
			'openid' => $openid
		], 'uid');
		if (!empty($uid['uid'])) {
			return $uid['uid'];
		} else {
			return 0;
		}
	}
	
	/**
	 * 取消关注
	 */
	public function WeixinUserUnsubscribe($openid)
	{
		$weixin_fans = new WeixinFansModel();
		$data = array(
			'is_subscribe' => 0,
			'unsubscribe_date' => time()
		);
		
		$retval = $weixin_fans->save($data, [
			'openid' => $openid
		]);
		return $retval;
	}
	
	/**
	 * 获取一键关注
	 */
	public function getInstanceOneKeySubscribe($instance_id)
	{
		$cache = Cache::tag('weixin')->get('getInstanceOneKeySubscribe' . $instance_id);
		if (!empty($cache)) return $cache;
		
		$weixin_subscribe = new WeixinOneKeySubscribeModel();
		$info = $weixin_subscribe->get($instance_id);
		if (empty($info)) {
			$data = array(
				'instance_id' => $instance_id,
				'url' => ''
			);
			$weixin_subscribe->save($data);
			$info = $weixin_subscribe->get($instance_id);
		}
		Cache::tag('weixin')->set('getInstanceOneKeySubscribe' . $instance_id, $info);
		return $info;
	}
	
	/**
	 * 设置一键关注
	 */
	public function setInsanceOneKeySubscribe($instance_id, $url)
	{
		$weixin_subscribe = new WeixinOneKeySubscribeModel();
		$retval = $weixin_subscribe->save([
			'url' => $url
		], [
			'instance_id' => $instance_id
		]);
		return $retval;
	}
	
	/**
	 * 获取一定条件下粉丝数量
	 */
	public function getWeixinFansCount($condition)
	{
		$cache = Cache::tag('weixin')->get('getWeixinFansCount' . json_encode($condition));
		if (!empty($cache)) return $cache;
		
		$weixin_fans = new WeixinFansModel();
		$count = $weixin_fans->where($condition)->count();
		
		Cache::tag('weixin')->set('getWeixinFansCount' . json_encode($condition), $count);
		return $count;
	}
	
	/**
	 * 获取会员关注微信信息
	 */
	public function getUserWeixinSubscribeData($uid, $instance_id)
	{
		$cache = Cache::tag('weixin')->get('getUserWeixinSubscribeData_' . $instance_id . '_' . $uid);
		if (!empty($cache)) return $cache;
		
		// 查询会员信息
		$user = new UserModel();
		$user_info = $user->getInfo([
			'uid' => $uid
		], 'wx_openid,wx_unionid');
		$fans_info = '';
		// 通过openid查询信息
		$weixin_fans = new WeixinFansModel();
		if (!empty($user_info['wx_openid'])) {
			$fans_info = $weixin_fans->getInfo([
				'openid' => $user_info['wx_openid']
			]);
		}
		if (empty($fans_info) && !empty($user_info['wx_unionid'])) {
			$fans_info = $weixin_fans->getInfo([
				'unionid' => $user_info['wx_unionid']
			]);
		}
		Cache::tag('weixin')->set('getUserWeixinSubscribeData_' . $instance_id . '_' . $uid, $fans_info);
		return $fans_info;
	}
	
	/**
	 * 通过微信openid获取粉丝表信息
	 */
	public function getWeixinFansInfoByWxOpenid($wx_openid)
	{
		$cache = Cache::tag('weixin')->get('getWeixinFansInfoByWxOpenid' . $wx_openid);
		if (!empty($cache)) return $cache;
		
		$weixin_fans = new WeixinFansModel();
		$fans_info = $weixin_fans->getInfo([
			'openid' => $wx_openid
		]);
		
		Cache::tag('weixin')->set('getWeixinFansInfoByWxOpenid' . $wx_openid, $fans_info);
		return $fans_info;
	}
	
	/**
	 * 更新粉丝信息
	 */
	public function UpdateWchatFansList($openid_array)
	{
		Cache::clear('weixin');
		$wchatOauth = new WchatOauth();
		$fans_list_info = $wchatOauth->get_fans_info_list($openid_array);
		//获取微信粉丝列表
		if (isset($fans_list_info["errcode"]) && $fans_list_info["errcode"] < 0) {
			return $fans_list_info;
		} else {
			foreach ($fans_list_info['user_info_list'] as $info) {
				$province = filterStr($info["province"]);
				$city = filterStr($info["city"]);
				$nickname = filterStr($info['nickname']);
				$nickname_decode = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $info['nickname']);
				$data = array(
					'uid' => $this->uid,
					'source_uid' => 0,
					'instance_id' => $this->instance_id,
					'nickname' => $nickname,
					'nickname_decode' => $nickname_decode,
					'headimgurl' => $info["headimgurl"],
					'sex' => $info["sex"],
					'language' => $info["language"],
					'country' => $info["country"],
					'province' => $province,
					'city' => $city,
					'district' => "",
					'openid' => $info["openid"],
					'groupid' => $info["groupid"],
					'is_subscribe' => $info["subscribe"],
					'update_date' => time(),
					'memo' => $info["remark"],
					'unionid' => $info["unionid"]
				);
				$this->addWeixinFans($data);
			}
		}
		return array(
			'errcode' => '0',
			'errorMsg' => 'success'
		);
		
	}
	
	/**
	 * 获取微信所有openid
	 */
	public function getWeixinOpenidList()
	{
		$wchatOauth = new WchatOauth();
		$res = $wchatOauth->get_fans_list("");
		if (!empty($res['data'])) {
			$openid_list = $res['data']['openid'];
			$wchatOauth = new WchatOauth();
			while ($res['next_openid']) {
				$res = $wchatOauth->get_fans_list($res['next_openid']);
				if (!empty($res['data'])) {
					$openid_list = array_merge($openid_list, $res['data']['openid']);
				}
			}
			return array(
				'total' => $res['total'],
				'openid_list' => $openid_list,
				'errcode' => '0',
				'errorMsg' => ''
			);
			
		} else {
			if (!empty($res["errcode"])) {
				return array(
					'errcode' => $res['errcode'],
					'errorMsg' => $res['errmsg'],
					'total' => 0,
					'openid_list' => ''
				);
			} else {
				return array(
					'errcode' => '-400001',
					'errorMsg' => '当前无粉丝列表或者获取失败',
					'total' => 0,
					'openid_list' => ''
				);
			}
			
		}
		
	}
	
	public function userBoundParent($openid, $source_uid)
	{
		//判断当前扫码人是不是会员
		$user_model = new UserModel();
		$user_info = $user_model->getInfo([ 'wx_openid' => $openid ], '*');
		if (empty($user_info)) {
			//如果不是会员，检测fans表，如果没有上级，设定上级是对应的souce_uid,
			$fans_model = new WeixinFansModel();
			$fans_info = $fans_model->getInfo([ 'openid' => $openid ], '*');
			if (!empty($fans_info) && $fans_info['source_uid'] == 0) {
				$data = array(
					'source_uid' => $source_uid
				);
				$fans_model->save($data, [ 'openid' => $openid ]);
			}
			
		} else {
			//如果是会员，检测会员uid与souce_uid是不是相同，不相同的话，检测查询的会员上级是，promoter_id=0，查询对应souce_uid对应的promoter和股东，赋值
			if ($user_info['uid'] != $source_uid) {
				switch (NS_VERSION) {
					case NS_VER_B2C:
						break;
					case NS_VER_B2C_FX:
						$nfx_user = new NfxShopMemberAssociationModel();
						$nfx_user_info = $nfx_user->getInfo([ 'uid' => $user_info['uid'] ], '*');
						if ($nfx_user_info['promoter_id'] == 0) {
							$source_user_info = $nfx_user->getInfo([ 'uid' => $source_uid ], '*');
							if ($source_user_info['promoter_id'] != 0) {
								$data = array(
									'promoter_id' => $source_user_info['promoter_id'],
									'partner_id' => $source_user_info['partner_id']
								);
								$nfx_user->save($data, [ 'uid' => $user_info['uid'] ]);
							}
						}
						break;
				}
			}
		}
		return 1;
		
	}
	
	/**
	 * 获取微信粉丝列表
	 */
	public function getWeixinFansList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$cache = Cache::tag('weixin')->get('getWeixinFansList' . json_encode([ $page_index, $page_size, $condition, $order ]));
		if (!empty($cache)) return $cache;
		
		$weixin_fans = new WeixinFansModel();
		$list = $weixin_fans->pageQuery($page_index, $page_size, $condition, $order, '*');
		Cache::tag('weixin')->set('getWeixinFansList' . json_encode([ $page_index, $page_size, $condition, $order ]), $list);
		
		return $list;
	}
	/*************************************************************微信粉丝结束***********************************************/
	
	/*************************************************************微信推广**************************************************/
	
	/**
	 * 修改推广二维码配置
	 */
	public function updateWeixinQrcodeConfig($params)
	{
		Cache::clear('weixin');
		$weixin_qrcode = new WeixinQrcodeTemplateModel();
		$num = $weixin_qrcode->where([
			'instance_id' => $params['instance_id']
		])->count();
		$data = array(
			'background' => $params['background'],
			'nick_font_color' => $params['nick_font_color'],
			'nick_font_size' => $params['nick_font_size'],
			'is_logo_show' => $params['is_logo_show'],
			'header_left' => $params['header_left'] . 'px',
			'header_top' => $params['header_top'] . 'px',
			'name_left' => $params['name_left'] . 'px',
			'name_top' => $params['name_top'] . 'px',
			'logo_left' => $params['logo_left'] . 'px',
			'logo_top' => $params['logo_top'] . 'px',
			'code_left' => $params['code_left'] . 'px',
			'code_top' => $params['code_top'] . 'px'
		);
		if ($num > 0) {
			$res = $weixin_qrcode->save($data, [
				'instance_id' => $params['instance_id']
			]);
		} else {
			$data['instance_id'] = $params['instance_id'];
			$weixin_qrcode->save($data);
			$res = 1;
		}
		return $res;
	}
	
	/**
	 * 添加店铺推广二维码
	 */
	public function addWeixinQrcodeTemplate($data)
	{
		Cache::clear('weixin');
		$weixin_qrcode = new WeixinQrcodeTemplateModel();
		$weixin_query = $weixin_qrcode->getQuery([
			"instance_id" => $data['instance_id'],
			"is_check" => 1
		]);
		if (empty($weixin_query)) {
			$data["is_check"] = 1;
		}
		$weixin_qrcode->save($data);
		return $weixin_qrcode->id;
	}
	
	/**
	 * 更新店铺推广二维码
	 */
	public function updateWeixinQrcodeTemplate($data)
	{
		Cache::clear('weixin');
		$weixin_qrcode = new WeixinQrcodeTemplateModel();
		$res = $weixin_qrcode->save($data, [
			'id' => $data['id']
		]);
		return $res;
	}
	
	/**
	 * 将某个模板设置为最新默认模板
	 */
	public function modifyWeixinQrcodeTemplateCheck($shop_id, $id, $type)
	{
		Cache::clear('weixin');
		$weixin_qrcode_template = new WeixinQrcodeTemplateModel();
		$weixin_qrcode_template->where(array(
			"instance_id" => $shop_id,
			'qrcode_type' => $type
		))->update(array(
			"is_check" => 0
		));
		$retval = $weixin_qrcode_template->where(array(
			"instance_id" => $shop_id,
			"id" => $id,
			'qrcode_type' => $type
		))->update(array(
			"is_check" => 1
		));
		return $retval;
	}
	
	/**
	 * 用户更换 自己的推广二维码
	 */
	public function updateMemberQrcodeTemplate($shop_id, $uid)
	{
		Cache::clear('weixin');
		$user = new UserModel();
		$userinfo = $user->getInfo([
			"uid" => $uid
		], "qrcode_template_id");
		$qrcode_template_id = $userinfo["qrcode_template_id"];
		$qrcode_template = new WeixinQrcodeTemplateModel();
		if ($qrcode_template_id == 0 || $qrcode_template_id == null) {
			$template_obj = $qrcode_template->getInfo([
				"instance_id" => $shop_id,
				"is_remove" => 0
			], "*");
		} else {
			$condition["id"] = array(
				">",
				$qrcode_template_id
			);
			$condition["instance_id"] = $shop_id;
			$condition["is_remove"] = 0;
			$template_obj = $qrcode_template->getInfo($condition, "*");
			if (empty($template_obj)) {
				$template_obj = $qrcode_template->getInfo([
					"instance_id" => $shop_id,
					"is_remove" => 0
				], "*");
			}
		}
		if (!empty($template_obj)) {
			$user->where(array(
				"uid" => $uid
			))->update(array(
				"qrcode_template_id" => $template_obj["id"]
			));
		}
	}
	
	/**
	 * 删除模板
	 */
	public function deleteWeixinQrcodeTemplate($id, $instance_id)
	{
		Cache::clear('weixin');
		$weixin_qrcode_template = new WeixinQrcodeTemplateModel();
		$retval = $weixin_qrcode_template->where(array(
			"instance_id" => $instance_id,
			"id" => $id
		))->update(array(
			"is_remove" => 1
		));
		return $retval;
	}
	
	/**
	 * 获取会员 微信公众号二维码
	 */
	public function getUserWchatQrcode($uid)
	{
		$weixin_auth = new WchatOauth();
		$qrcode_url = $weixin_auth->ever_qrcode($uid);
		return $qrcode_url;
	}
	
	/**
	 * 获取推广二维码
	 */
	public function getWeixinQrcodeConfig($uid, $type = 1)
	{
		$user = new UserModel();
		$userinfo = $user->getInfo([
			"uid" => $uid
		]);
		$qrcode_template_id = $userinfo["qrcode_template_id"];
		$weixin_qrcode = new WeixinQrcodeTemplateModel();
		if ($qrcode_template_id == 0 || $qrcode_template_id == null) {
			$weixin_obj = $weixin_qrcode->getInfo([
				"instance_id" => $this->instance_id,
				"is_check" => 1,
				'qrcode_type' => $type
			], "*");
		} else {
			$weixin_obj = $weixin_qrcode->getInfo([
				"instance_id" => $this->instance_id,
				"id" => $qrcode_template_id,
				'qrcode_type' => $type
			], "*");
		}
		
		if (empty($weixin_obj)) {
			$weixin_obj = $weixin_qrcode->getInfo([
				"instance_id" => $this->instance_id,
				"is_remove" => 0,
				'qrcode_type' => $type
			], "*");
		}
		return $weixin_obj;
	}
	
	/**
	 * 得到店铺的推广二维码模板列表
	 */
	public function getWeixinQrcodeTemplate($shop_id, $type = 1)
	{
		$weixin_qrcode_template = new WeixinQrcodeTemplateModel();
		return $weixin_qrcode_template->all(array(
			"instance_id" => $shop_id,
			"is_remove" => 0,
			"qrcode_type" => $type
		));
	}
	
	/**
	 * 查询单个模板的具体信息
	 */
	public function getDetailWeixinQrcodeTemplate($id)
	{
		$cache = Cache::tag('weixin')->get('getDetailWeixinQrcodeTemplate' . $id);
		if (!empty($cache)) return $cache;
		
		if ($id == 0) {
			$template_obj = array(
				"background" => "",
				"nick_font_color" => "#2B2B2B",
				"nick_font_size" => "23",
				"is_logo_show" => 1,
				"header_left" => "59px",
				"header_top" => "15px",
				"name_left" => "150px",
				"name_top" => "120px",
				"logo_top" => "100px",
				"logo_left" => "120px",
				"code_left" => "70px",
				"code_top" => "300px"
			);
		} else {
			$weixin_qrcode_template = new WeixinQrcodeTemplateModel();
			$template_obj = $weixin_qrcode_template->get($id);
		}
		Cache::tag('weixin')->set('getDetailWeixinQrcodeTemplate' . $id, $template_obj);
		return $template_obj;
	}
	
	/********************************************************微信推广结束****************************************************/
	
	/********************************************************微信客服*******************************************************/
	/**
	 * 添加用户消息
	 */
	public function addUserMessage($params)
	{
		Cache::clear('weixin');
		$weixin_user_msg = new WeixinUserMsgModel();
		$fans_model = new WeixinFansModel();
		$fans_info = $fans_model->getInfo([ 'openid' => $params['openid'] ], 'nickname,headimgurl,uid');
		if (!empty($fans_info)) {
			$data = array(
				'uid' => $fans_info['uid'],
				'openid' => $params['openid'],
				'nickname' => $fans_info['nickname'],
				'headimgurl' => $fans_info['headimgurl'],
				'msg_type' => $params['msg_type'],
				'content' => $params['content'],
				'create_time' => time()
			);
			$weixin_user_msg->save($data);
			return $weixin_user_msg->msg_id;
		} else {
			return 0;
		}
	}
	
	/**
	 * 获取会员留言列表
	 */
	public function getUserMessageList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$cache = Cache::tag('weixin')->get('getUserMessageList' . json_encode([ $page_index, $page_size, $condition, $order ]));
		if (!empty($cache)) return $cache;
		
		$weixin_user_msg = new WeixinUserMsgModel();
		$list = $weixin_user_msg->pageQuery($page_index, $page_size, $condition, $order, '*');
		
		Cache::tag('weixin')->set('getUserMessageList' . json_encode([ $page_index, $page_size, $condition, $order ]), $list);
		return $list;
	}
	
	/**
	 * 添加微信客服消息
	 */
	public function addUserMessageReplay($data)
	{
		Cache::clear('weixin');
		$weixin_user_msg_replay = new WeixinUserMsgReplayModel();
		$weixin_user_msg_replay->save($data);
		return $weixin_user_msg_replay->replay_id;
	}
	
	/********************************************************微信客服结束****************************************************/
	
}