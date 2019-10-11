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

namespace app\wap\controller;

\think\Loader::addNamespace('data', 'data/');

use data\extend\WchatOauth;
use data\service\Config;
use data\service\Weixin;

class Wchat extends BaseWap
{
	
	public $wchat;
	
	public $weixin_service;
	
	public function __construct()
	{
		parent::__construct();
		$this->wchat = new WchatOauth(); // 微信公众号相关类
		$this->weixin_service = new Weixin();
		$this->getMessage();
	}
	
	/**
	 * ************************************************************************微信公众号消息相关方法 开始******************************************************
	 */
	/**
	 * 关联公众号微信
	 */
	public function relateWeixin()
	{
		$sign = request()->get('signature', '');
		if (isset($sign)) {
			$signature = $sign;
			$timestamp = request()->get('timestamp');
			$nonce = request()->get('nonce');
			$token = "TOKEN";
			$config = new Config();
			$wchat_config = $config->getInstanceWchatConfig($this->instance_id);
			if (!empty($wchat_config["value"]["token"])) {
				$token = $wchat_config["value"]["token"];
			}
			
			$tmpArr = array(
				$token,
				$timestamp,
				$nonce
			);
			
			sort($tmpArr, SORT_STRING);
			$tmpStr = implode($tmpArr);
			$tmpStr = sha1($tmpStr);
			
			if ($tmpStr == $signature) {
				$echostr = request()->get('echostr', '');
				if (!empty($echostr)) {
					ob_clean();
					echo $echostr;
				}
				return 1;
			} else {
				return 0;
			}
		}
	}
	
	public function message()
	{
		$media_id = request()->get('media_id', 0);
		$weixin = new Weixin();
		$info = $weixin->getWeixinMediaDetailByMediaId($media_id);
		if (!empty($info["media_parent"])) {
			$this->assign("info", $info);
			$this->assign('title_before', $info["media_item"]["title"]);
			return $this->view($this->style . 'wechat/message');
		} else {
			echo "图文消息没有查询到";
		}
	}
	
	/**
	 * 微信开放平台模式(需要对消息进行加密和解密)
	 * 微信获取消息以及返回接口
	 */
	public function getMessage()
	{
		$from_xml = file_get_contents('php://input');
		if (empty($from_xml)) {
			return;
		}
		$signature = request()->get('msg_signature', '');
		$signature = request()->get('timestamp', '');
		$nonce = request()->get('nonce', '');
		$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
		$ticket_xml = $from_xml;
		$postObj = simplexml_load_string($ticket_xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		
		if (!empty($postObj->MsgType)) {
			switch ($postObj->MsgType) {
				case "text":
					$weixin = new Weixin();
					// 用户发的消息 存入表中
					$params = [
						'openid' => $postObj->FromUserName,
						'content' => $postObj->Content,
						'msg_type' => $postObj->MsgType
					];
					$weixin->addUserMessage($params);
					$resultStr = $this->MsgTypeText($postObj);
					break;
				case "event":
					$resultStr = $this->MsgTypeEvent($postObj);
					break;
				default:
					$resultStr = "";
					break;
			}
		}
		if (!empty($resultStr)) {
			echo $resultStr;
		} else {
			echo '';
		}
		exit();
	}
	
	/**
	 * 文本消息回复格式
	 */
	private function MsgTypeText($postObj)
	{
		$wchat_replay = $this->weixin_service->getWhatReplay($this->instance_id, (string) $postObj->Content);
		// 判断用户输入text
		if (!empty($wchat_replay)) { // 关键词匹配回复
			$contentStr = $wchat_replay; // 构造media数据并返回
		} elseif ($postObj->Content == "uu") {
			$contentStr = "shopId：" . $this->instance_id;
		} elseif ($postObj->Content == "TESTCOMPONENT_MSG_TYPE_TEXT") {
			$contentStr = "TESTCOMPONENT_MSG_TYPE_TEXT_callback"; // 微店插件功能 关键词，预留口
		} elseif (strpos($postObj->Content, "QUERY_AUTH_CODE") !== false) {
			$get_str = str_replace("QUERY_AUTH_CODE:", "", $postObj->Content);
			$contentStr = $get_str . "_from_api"; // 微店插件功能 关键词，预留口
		} else {
			$content = $this->weixin_service->getDefaultReplay($this->instance_id);
			if (!empty($content)) {
				$contentStr = $content;
			} else {
				$contentStr = '';
			}
		}
		if (is_array($contentStr)) {
			$resultStr = $this->wchat->event_key_news($postObj, $contentStr);
		} elseif (!empty($contentStr)) {
			$resultStr = $this->wchat->event_key_text($postObj, $contentStr);
		} else {
			$resultStr = '';
		}
		return $resultStr;
	}
	
	/**
	 * 事件消息回复机制
	 */
	// 事件自动回复 MsgType = Event
	private function MsgTypeEvent($postObj)
	{
		$contentStr = "";
		switch ($postObj->Event) {
			case "subscribe": // 关注公众号
				$str = $this->wchat->get_fans_info($postObj->FromUserName);
				if (preg_match("/^qrscene_/", $postObj->EventKey)) {
					$source_uid = substr($postObj->EventKey, 8);
					$_SESSION['source_shop_id'] = $this->instance_id;
					$_SESSION['source_uid'] = $source_uid;
				} elseif (!empty($_SESSION['source_uid'])) {
					$source_uid = $_SESSION['source_uid'];
					$_SESSION['source_shop_id'] = $this->instance_id;
				} else {
					$source_uid = 0;
				}
				$Userstr = json_decode($str);
				$nickname = base64_encode($Userstr->nickname);
				$nickname_decode = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $Userstr->nickname);
				$headimgurl = $Userstr->headimgurl;
				$sex = $Userstr->sex;
				$language = $Userstr->language;
				$country = $Userstr->country;
				$province = $Userstr->province;
				$city = $Userstr->city;
				$district = "无";
				$openid = $Userstr->openid;
				if (!empty($Userstr->unionid)) {
					$unionid = $Userstr->unionid;
				} else {
					$unionid = '';
				}
				//$subscribe_date = date('Y/n/j G:i:s', (int) $postObj->CreateTime);
				$memo = $Userstr->remark;
				
				$data = array(
					'uid' => $this->uid,
					'source_uid' => $source_uid,
					'instance_id' => $this->instance_id,
					'nickname' => $nickname,
					'nickname_decode' => $nickname_decode,
					'headimgurl' => $headimgurl,
					'sex' => $sex,
					'language' => $language,
					'country' => $country,
					'province' => $province,
					'city' => $city,
					'district' => $district,
					'openid' => $openid,
					'groupid' => "",
					'is_subscribe' => 1,
					'update_date' => time(),
					'memo' => $memo,
					'unionid' => $unionid
				);
				$this->weixin_service->addWeixinFans($data); // 关注
				
				// 添加关注回复
				$content = $this->weixin_service->getSubscribeReplay($this->instance_id);
				if (!empty($content)) {
					$contentStr = $content;
				}
				// 构造media数据并返回 */
				break;
			case "unsubscribe": // 取消关注公众号
				$openid = $postObj->FromUserName;
				$this->weixin_service->WeixinUserUnsubscribe($openid);
				break;
			case "VIEW": // VIEW事件 - 点击菜单跳转链接时的事件推送
				/* $this->wchat->weichat_menu_hits_view($postObj->EventKey); //菜单计数 */
				$contentStr = "";
				break;
			case "SCAN": // SCAN事件 - 用户已关注时的事件推送 - 扫描带参数二维码事件
				$openid = $postObj->FromUserName;
				$data = $postObj->EventKey;
				$user_bound = $this->weixin_service->userBoundParent((string) $openid, $data);
				$content = $this->weixin_service->getSubscribeReplay($this->instance_id);
				if (!empty($content)) {
					$contentStr = $content;
				}
				break;
			case "CLICK": // CLICK事件 - 自定义菜单事件
				$menu_detail = $this->weixin_service->getWeixinMenuInfo($postObj->EventKey);
				$media_info = $this->weixin_service->getWeixinMediaDetail($menu_detail['media_id']);
				$contentStr = $this->weixin_service->getMediaWchatStruct($media_info); // 构造media数据并返回 */
				break;
		}
		// $contentStr = $postObj->Event."from_callback";//测试接口正式部署之后注释不要删除
		if (is_array($contentStr)) {
			$resultStr = $this->wchat->event_key_news($postObj, $contentStr);
		} else {
			$resultStr = $this->wchat->event_key_text($postObj, $contentStr);
		}
		return $resultStr;
	}
	
	/**
	 * ************************************************************************微信公众号消息相关方法 结束******************************************************
	 */
}