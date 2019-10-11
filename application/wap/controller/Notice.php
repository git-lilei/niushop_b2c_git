<?php
/**
 * Notice.php
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

/**
 * 公告控制器
 */
class Notice extends BaseWap
{
	
	/**
	 * 公告列表
	 */
	public function lists()
	{
		$this->assign('title', "公告列表");
		$this->assign('title_before', "公告列表");
		return $this->view($this->style . "notice/lists");
	}
	
	/**
	 * 公告详情
	 */
	public function detail()
	{
		$id = request()->get('id', '');
		if (empty($id)) {
			$this->error("公告不存在");
		}
		$this->assign("id", $id);
		
		$notice_info = api("System.Shop.shopNoticeInfo", [ 'id' => $id ]);
		$notice_info = $notice_info['data'];
		
		// 上一篇
		$prev_info = api("System.Shop.shopNoticeList", [ 'page_size' => 1, 'condition' => [ "id" => array( "gt", $id ) ], 'order' => 'create_time asc' ]);
		$prev_info = $prev_info['data']['data'];
		
		// 下一篇
		$next_info = api("System.Shop.shopNoticeList", [ 'page_size' => 1, 'condition' => [ "id" => array( "lt", $id ) ], 'order' => 'create_time desc' ]);
		$next_info = $next_info['data']['data'];
		
		$this->assign('notice_info', $notice_info);
		$this->assign('prev_info', $prev_info);
		$this->assign('next_info', $next_info);
		
		$this->assign('title', $notice_info['notice_title'] ? $notice_info['notice_title'] : "公告详情");
		$this->assign('title_before', $notice_info['notice_title'] ? $notice_info['notice_title'] : "公告详情");
		return $this->view($this->style . 'notice/detail');
	}
}