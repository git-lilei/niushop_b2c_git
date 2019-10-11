<?php
/**
 * Help.php
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

namespace app\web\controller;

/**
 * 帮助中心
 */
class Help extends BaseWeb
{
	/**
	 * 首页
	 */
	public function index()
	{
		$id = request()->get("id", "");
		$class_id = request()->get("class_id", "");
		if ((!empty($id) && !is_numeric($id)) || (!empty($class_id) && !is_numeric($class_id))) {
			$redirect = __URL(__URL__ . '/index');
			$this->redirect($redirect);
		}
		
		$info = api('System.Shop.helpList', [ 'id' => $id, 'class_id' => $class_id ]);
		$info = $info['data'];
		$platform_help_class = $info['platform_help_class'];
		$platform_help_document = $info['platform_help_document'];
		$help_document_info = $info['help_document_info'];
		
		$this->assign('id', $id);
		$this->assign('class_id', $class_id);
		$this->assign("platform_help_class", $platform_help_class);
		$this->assign("platform_help_document", $platform_help_document);
		$this->assign("help_document_info", $help_document_info);
		$this->assign("title_before", $help_document_info['title']);
		return $this->view($this->style . 'help/index');
	}
}