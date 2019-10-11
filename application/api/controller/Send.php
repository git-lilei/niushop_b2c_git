<?php
/**
 * Send.php
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


use data\service\Notice;

/**
 * 短信、邮箱发送接口
 */
class Send extends BaseApi
{
	/**
	 * 发送短信 邮箱 动态码
	 */
	public function sendDynamicCode()
	{
		$title = '获取动态码';
		
		$name = isset($this->params['name']) ? $this->params['name'] : ''; // 发送钩子名称
		$params = isset($this->params['params']) ? $this->params['params'] : ''; // 所需参数
		
		if (empty($name)) return $this->outMessage($title, null, -1, '缺少必须参数name');
		if (empty($params)) return $this->outMessage($title, null, -1, '缺少必须参数params');
		$params = json_decode($params, true);
		
//		$res = runhook('Notify', $name, $params);
        $res = "";
		if ($res == 0) {
			return $this->outMessage($title, [ 'record_id' => $res['record_id'] ], -1, $res['message']);
		} else {
			return $this->outMessage($title, null, -1, $res['message']);
		}
	}
	
	/**
	 * 验证动态码
	 */
	public function checkDynamicCode()
	{
		$title = '动态码验证';
		
		$account = isset($this->params['account']) ? $this->params['account'] : ''; // 验证账号
		$record_id = isset($this->params['record_id']) ? $this->params['record_id'] : ''; // 记录id
		$code = isset($this->params['code']) ? $this->params['code'] : ''; // 动态码
		
		if (empty($account)) return $this->outMessage($title, null, -1, '缺少必须参数account');
		if (empty($record_id)) return $this->outMessage($title, null, -1, '缺少必须参数record_id');
		if (empty($code)) return $this->outMessage($title, null, -1, '缺少必须参数code');
		
		$notice = new Notice();
		$record_data = $notice->getNotifyRecordsDetail([ 'id' => $record_id ]);
		
		if (empty($record_data)) {
			return $this->outMessage($title, null, -1, '未获取到验证记录');
		} else {
			$send_content = json_decode($record_data['notice_context']);
			if ($account != $record_data['send_account']) return $this->outMessage($title, null, -1, '该账号与验证时账号不一致！');
			if ($code != $send_content['number']) {
				return $this->outMessage($title, null, -1, '该账号与验证时账号不一致！');
			} else {
				return $this->outMessage($title, null, 0, '验证通过');
			}
		}
	}
}