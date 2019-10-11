<?php
/**
 * Topic.php
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
 * 专题控制器
 */
class Topic extends BaseWeb
{
	/**
	 * 专题页
	 */
	public function detail()
	{
		$topic_id = request()->get('topic_id', 0);
		if (empty($topic_id)) {
			$this->error("专题不存在");
		}
		$this->assign("topic_id", $topic_id);
		$this->assign("title_before", "专题详情");
		return $this->view($this->style . 'topic/detail');
	}
}