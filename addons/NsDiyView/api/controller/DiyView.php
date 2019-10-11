<?php
/**
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

namespace addons\NsDiyView\api\controller;

use addons\NsDiyView\data\service\Config;
use app\api\controller\BaseApi;

/**
 * 微页面接口
 */
class DiyView extends BaseApi
{
	/**
	 * 获取微页面
	 * @return string
	 */
	public function getDiyView()
	{
		$config = new Config();
		$template_type = isset($this->params['template_type']) ? $this->params['template_type'] : "";
		$type = $this->get('type', 1);
		$res = $config->getDiyView($template_type, $type);
		return $this->outMessage("获取微页面", $res);
	}
}