<?php
/**
 * Notice.php
 *
 * NiuShop商城系统 - 团队十年电商经验汇集巨献!
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

/**
 * 通知业务层
 */
use data\model\NoticeRecordsModel;
use data\model\NoticeTemplateItemModel;
use data\model\NoticeTemplateModel;
use data\model\NoticeTemplateTypeModel;

use think\Cache;
use think\Log;

class Notice extends BaseService
{
	/***********************************************************通知开始*********************************************************/
	
	/**
	 * 添加通知记录
	 */
	public function createNoticeRecords($shop_id, $uid, $send_type, $send_account, $notice_title, $notice_context, $records_type, $send_config, $is_send)
	{
		$notice_records_model = new NoticeRecordsModel();
		$condition = array(
			"shop_id" => $shop_id,
			"uid" => $uid,
			"send_type" => $send_type,
			"send_account" => $send_account,
			"send_config" => $send_config,
			"records_type" => $records_type,
			"notice_title" => $notice_title,
			"notice_context" => $notice_context,
			"is_send" => $is_send,
			"send_message" => "",
			"create_date" => time()
		);
		$insert_id = $notice_records_model->save($condition);
		Cache::clear('notice');
		return $insert_id;
	}
	
	/**
	 * 创建验证码发送记录
	 */
	public function createVerificationCodeRecords($shop_id, $uid, $send_type, $send_account, $send_config, $records_type, $notice_title, $notice_context, $send_message, $is_send)
	{
		$notice_records_model = new NoticeRecordsModel();
		$data = array(
			"shop_id" => $shop_id,
			"uid" => $uid,
			"send_type" => $send_type,
			"send_account" => $send_account,
			"send_config" => $send_config,
			"records_type" => $records_type,
			"notice_title" => $notice_title,
			"notice_context" => $notice_context,
			"is_send" => $is_send,
			"send_message" => $send_message,
			"create_date" => time(),
			"send_date" => time()
		);
		$insert_id = $notice_records_model->save($data);
		Cache::clear('notice');
		return $insert_id;
	}
	
	/**
	 * 发送通知
	 */
	public function sendNoticeRecords()
	{
		$notice_records_model = new NoticeRecordsModel();
		$condition = array( "is_send" => 0 );
		$notice_list = $notice_records_model->getQuery($condition);
		foreach ($notice_list as $notice_obj) {
			$send_type = $notice_obj["send_type"];
			if ($send_type == 1) {
				// 短信发送
				$this->noticeSmsSend($notice_obj["id"], $notice_obj["send_account"], $notice_obj["send_config"], $notice_obj["notice_context"]);
			} else {
				// 邮件发送
				$this->noticeEmailSend($notice_obj["id"], $notice_obj["send_account"], $notice_obj["send_config"], $notice_obj["notice_title"], $notice_obj["notice_context"]);
			}
		}
	}
	
	/**
	 * 发送短信
	 */
	private function noticeSmsSend($notice_id, $send_account, $send_config, $notice_params)
	{
		$send_config = json_decode($send_config, true);
		$appkey = $send_config["appkey"];
		$secret = $send_config["secret"];
		$signName = $send_config["signName"];
		$template_code = $send_config["template_code"];
		$sms_type = $send_config["sms_type"];
		
		$result = hook('smssend', [
			'signName' => $signName,
			'smsParam' => $notice_params,
			'mobile' => $send_account,
			'code' => $template_code
		]);
		$result = arrayFilter($result);
		$result = $result[0];
		if ($result["code"] == 0) {
			$status = 1;
		} else {
			$status = -1;
		}
		$send_message = empty($result["message"]) ? "" : $result["message"];
		$notice_records_model = new NoticeRecordsModel();
		$ret = $notice_records_model->save([
			"is_send" => $status,
			"send_message" => $send_message,
			"send_date" => time()
		], [
			"id" => $notice_id
		]);
		Cache::clear('notice');
		return $ret;
	}
	
	/**
	 * 邮件发送
	 */
	private function noticeEmailSend($notice_id, $send_account, $send_config, $notice_title, $notice_context)
	{
		$send_config = json_decode($send_config, true);
		$email_host = $send_config["email_host"];
		$email_id = $send_config["email_id"];
		$email_pass = $send_config["email_pass"];
		$email_port = $send_config["email_port"];
		$email_is_security = $send_config["email_is_security"];
		$email_addr = $send_config["email_addr"];
		$shopName = $send_config["shopName"];
		$result = emailSend($email_host, $email_id, $email_pass, $email_port, $email_is_security, $email_addr, $send_account, $notice_title, $notice_context, $shopName);
		if ($result) {
			$send_message = "发送成功";
			$status = 1;
		} else {
			$status = -1;
			$send_message = "发送失败";
		}

		$notice_records_model = new NoticeRecordsModel();
		$ret = $notice_records_model->save([
			"is_send" => $status,
			"send_message" => $send_message,
			"send_date" => time()
		], [
			"id" => $notice_id
		]);
		Cache::clear('notice');
		return $ret;
	}
	
	/**
	 * 获取通知记录详情
	 */
	public function getNotifyRecordsDetail($condition = array())
	{
		$cache = Cache::tag('notice')->get('getNotifyRecordsDetail' . json_encode($condition));
		
		if (!empty($cache)) return $cache;
		
		$notice_records_model = new NoticeRecordsModel();
		$detail = $notice_records_model->getInfo($condition);
		
		$user_service = new User();
		$user_info = $user_service->getUserInfoByUid($detail['uid']);
		$detail['user_name'] = $user_info['nick_name'];
		
		Cache::tag('notice')->set('getNotifyRecordsDetail' . json_encode($condition), $detail);
		
		return $detail;
	}
	
	/**
	 * 获取通知记录
	 */
	public function getNoticeRecordsList($page_index = 1, $page_size = 0, $condition = array(), $order = '', $field = "*")
	{
		$cache = Cache::tag('notice')->get('getNoticeRecordsList' . json_encode([ $page_index, $page_size, $condition, $order, $field ]));
		
		if (!empty($cache)) return $cache;
		
		$notice_records_model = new NoticeRecordsModel();
		$list = $notice_records_model->pageQuery($page_index, $page_size, $condition, $order, $field);
		
		foreach ($list['data'] as $k => $v) {
			$user_service = new User();
			$user_info = $user_service->getUserInfoByUid($v['uid']);
			$list['data'][ $k ]['user_name'] = $user_info['nick_name'];
		}
		
		Cache::tag('notice')->set('getNoticeRecordsList' . json_encode([ $page_index, $page_size, $condition, $order, $field ]), $list);
		
		return $list;
	}
	
	/**
	 * 检测动态码
	 */
	public function checkDynamicCode($record_id, $account, $code)
	{
		$record_data = $this->getNotifyRecordsDetail([ 'id' => $record_id ]);
		
		if (empty($record_data)) {
			return [ 'code' => -1, 'message' => '未获取到验证记录' ];
		} else {
			$send_content = json_decode($record_data['notice_context']);
			if ($account != $record_data['send_account']) return [ 'code' => -1, 'message' => '该账号与验证时账号不一致！' ];
			if ($code != $send_content['number']) {
				return [ 'code' => -1, 'message' => '动态码错误' ];
			} else {
				return [ 'code' => 1, 'message' => '验证通过' ];
			}
		}
	}
	
	/***********************************************************通知结束*********************************************************/
	
	/*************************************************通知系统设置**********************************************************/
	/**
	 * 获取通知模板列表
	 */
	public function getNoticeTemplateDetail($shop_id, $template_type, $notify_type)
	{
		$cache = Cache::tag('notice_template')->get("getNoticeTemplateDetail" . $shop_id . '_' . $template_type . '_' . $notify_type);
		if (!empty($cache)) {
			return $cache;
		}
		
		$notice_template_model = new NoticeTemplateModel();
		
		$condition['instance_id'] = $shop_id;
		if ($template_type) {
			$condition['template_type'] = $template_type;
		}
		if ($notify_type) {
			$condition['notify_type'] = $notify_type;
		}
		$template_list = $notice_template_model->getQuery($condition);
		Cache::tag('notice_template')->set("getNoticeTemplateDetail" . $shop_id . '_' . $template_type . '_' . $notify_type, $template_list);
		return $template_list;
	}
	
	/**
	 * 获取单项通知模板详情
	 */
	public function getNoticeTemplateOneDetail($shop_id, $template_type, $template_code, $notify_type)
	{
		$cache = Cache::tag('notice_template')->get("getNoticeTemplateOneDetail" . $shop_id . '_' . $template_type . '_' . $template_code);
		if (!empty($cache)) {
			return $cache;
		}
		$notice_template_model = new NoticeTemplateModel();
		$info = $notice_template_model->getInfo([
			'instance_id' => $shop_id,
			'template_type' => $template_type,
			'template_code' => $template_code,
			'notify_type' => $notify_type
		]);
		Cache::tag('notice_template')->set("getNoticeTemplateOneDetail" . $shop_id . '_' . $template_type . '_' . $template_code, $info);
		return $info;
	}
	
	/**
	 * 获取单项通知模板类型详情
	 */
	public function getNoticeTemplateTypeDetail($condition)
	{
		$notice_template_model = new NoticeTemplateTypeModel();
		$info = $notice_template_model->getInfo($condition);
		return $info;
	}
	
	/**
	 * 获取通知模版列表
	 */
	public function getNoticeTemplateList($condition)
	{
		$notice_template_model = new NoticeTemplateModel();
		$info = $notice_template_model->getQuery($condition);
		return $info;
	}
	
	/**
	 * 更新通知模板信息
	 */
	public function updateNoticeTemplate($shop_id, $template_type, $template_array, $notify_type)
	{
		Cache::clear('notice_template');
		$template_data = json_decode($template_array, true);
		foreach ($template_data as $template_obj) {
			$template_code = $template_obj["template_code"];
			$template_title = $template_obj["template_title"];
			$template_content = $template_obj["template_content"];
			$sign_name = $template_obj["sign_name"];
			$is_enable = $template_obj["is_enable"];
			$notification_mode = $template_obj["notification_mode"];
			$notice_template_model = new NoticeTemplateModel();
			$t_count = $notice_template_model->getCount([
				"instance_id" => $shop_id,
				"template_type" => $template_type,
				"template_code" => $template_code,
				"notify_type" => $notify_type
			]);
			
			if ($t_count > 0) {
				// 更新
				$data = array(
					"template_title" => $template_title,
					"template_content" => $template_content,
					"sign_name" => $sign_name,
					"is_enable" => $is_enable,
					"modify_time" => time(),
					"notification_mode" => $notification_mode
				);
				$res = $notice_template_model->save($data, [
					"instance_id" => $shop_id,
					"template_type" => $template_type,
					"template_code" => $template_code,
					"notify_type" => $notify_type
				]);
			} else {
				// 添加
				$data = array(
					"instance_id" => $shop_id,
					"template_type" => $template_type,
					"template_code" => $template_code,
					"template_title" => $template_title,
					"template_content" => $template_content,
					"sign_name" => $sign_name,
					"is_enable" => $is_enable,
					"modify_time" => time(),
					"notify_type" => $notify_type,
					"notification_mode" => $notification_mode
				);
				$res = $notice_template_model->save($data);
			}
		}
		return $res;
	}
	
	/**
	 * 获取店铺发送信息模板
	 */
	public function getNoticeTemplateItem($template_code)
	{
		$cache = Cache::tag('notice_template')->get("getNoticeTemplateItem" . '_' . $template_code);
		if (!empty($cache)) {
			return $cache;
		}
		$notice_model = new NoticeTemplateItemModel();
		$item_list = $notice_model->where("FIND_IN_SET('" . $template_code . "', type_ids)")->select();
		Cache::tag('notice_template')->set("getNoticeTemplateItem" . '_' . $template_code, $item_list);
		return $item_list;
	}
	
	/**
	 * 得到店铺模板的集合
	 */
	public function getNoticeTemplateType($template_type, $notify_type)
	{
		$cache = Cache::tag('notice_template')->get("getNoticeTemplateType" . '_' . $template_type . '_' . $notify_type);
		if (!empty($cache)) {
			return $cache;
		}
		$notice_type_model = new NoticeTemplateTypeModel();
		
		$where = ' 1 = 1 or template_type = "all" ';
		if ($template_type) {
			$where .= ' or template_type = "' . $template_type . '"';
		}
		if ($notify_type) {
			$where .= ' and notify_type = "' . $notify_type . '"';
		}
		
		$type_list = $notice_type_model->where($where)->select();
		Cache::tag('notice_template')->set("getNoticeTemplateType" . '_' . $template_type . '_' . $notify_type, $type_list);
		return $type_list;
	}
	
	/*************************************************通知系统设置结束*******************************************************/
	
	
}