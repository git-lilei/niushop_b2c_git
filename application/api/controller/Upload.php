<?php
/**
 * Upload.php
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

\think\Loader::addNamespace('data', 'data/');

use data\service\Upload as UploadService;

/**
 * 图片上传控制器
 */
class Upload extends BaseApi
{
	/**
	 * 上传图片（单图）
	 */
	public function uploadImage()
	{
		$return = array();
		$return['data'] = "";
		$return['code'] = -1;
		$return['message'] = "";
		
		$title = '上传图片';
		if (empty($this->uid)) {
			$return['message'] = "无法获取会员登录信息";
			return $this->outMessage($title, $return);
		}
		
		$file_path = isset($this->params['file_path']) ? $this->params['file_path'] : '';
		
		if ($file_path == "") {
			$return['message'] = "文件路径不能为空";
			return $this->outMessage($title, $return);
		}
		
		if (empty($_FILES)) {
			$return['message'] = "缺少文件";
			return $this->outMessage($title, $return);
		}
		
		//获取第一个键名
		reset($_FILES);
		$key = key($_FILES);
		
		$upload_service = new UploadService();
		$result = $upload_service->image($_FILES[ $key ], $file_path);
		$return['code'] = $result["code"];
		$return['message'] = $result["message"];
		$return['data'] = $result["data"]["path"];
		return $this->outMessage($title, $return);
		
	}
}