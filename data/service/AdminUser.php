<?php
/**
 * AdminUser.php
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

use data\model\AdminUserModel;
use data\model\AdminUserViewModel;
use data\model\NsMemberLevelModel;
use data\model\NsMemberModel;
use data\model\UserLogModel;
use data\service\User as User;

class AdminUser extends User
{
	
	function __construct()
	{
		parent::__construct();
		$this->admin_user = new AdminUserModel();
	}
	
	/***********************************************************后台用户开始*********************************************************/
	
	/**
	 * 添加后台用户
	 */
	public function addAdminUser($user_name, $group_id, $user_password, $desc, $instance_id = 0)
	{
		$uid = $this->add($user_name, $user_password, '', '', 1, '', '', '', '', '', 1, $instance_id);
		if ($uid <= 0) {
			return $uid;
		}
		$data_admin = array(
			'uid' => $uid,
			'admin_name' => $user_name,
			'group_id_array' => $group_id,
			'admin_status' => 1,
			'desc' => $desc
		);
		$member = new NsMemberModel();
		// 查询默认等级
		$member_level = new NsMemberLevelModel();
		$level_info = $member_level->getInfo([
			'is_default' => 1
		]);
		if (!empty($level_info)) {
			$level_id = $level_info['level_id'];
		} else {
			$level_id = 0;
		}
		$data_member = array(
			'uid' => $uid,
			'member_name' => $user_name,
			'reg_time' => time(),
			'member_level' => $level_id
		);
		$member->save($data_member);
		$this->admin_user->save($data_admin);
		$this->addUserLog($this->uid, 1, '用户', '添加用户', '添加用户：' . $user_name);
		$res = $member->uid;
		return $res;
	}
	
	/**
	 * 编辑后台用户
	 */
	public function editAdminUser($uid, $user_name, $group_id, $desc)
	{
		$res = $this->modifyUserName($uid, $user_name);
		if ($res) {
			$data = array(
				'admin_name' => $user_name,
				'group_id_array' => $group_id,
				'admin_status' => 1,
				'desc' => $desc
			);
			$res = $this->admin_user->save($data, [
				"uid" => $uid
			]);
			$this->addUserLog($this->uid, 1, '用户', '编辑用户', '编辑用户：' . $user_name);
		}
		
		return $res;
	}
	
	/**
	 * 删除单个用户
	 */
	public function deleteAdminUser($uid)
	{
		$admin_user_info = $this->admin_user->getInfo([
			'uid' => $uid
		]);
		$this->addUserLog($this->uid, 1, '用户', '删除用户', '删除用户：' . $admin_user_info['user_name']);
		if ($admin_user_info['is_admin'] == 0) {
			$retval = $this->admin_user->destroy($uid);
			if ($retval) {
				// 删除用户相关会员信息
				$member = new Member();
				$member->deleteMember($uid);
			}
			return $retval;
		} else {
			return 0;
		}
	}
	
	/**
	 * 获取单个用户后台信息
	 */
	public function getAdminUserInfo($condition, $field = "*")
	{
		$admin_user_info = $this->admin_user->getInfo($condition, $field = "*");
		return $admin_user_info;
	}
	
	/**
	 * 获取权限使用数量
	 */
	public function getAdminUserCountByGroupIdArray($condition)
	{
		$admin_user = new AdminUserViewModel();
		$num = $admin_user->getAdminUserViewCount($condition);
		return $num;
	}
	
	/**
	 * 后台操作用户列表
	 */
	public function adminUserList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$admin_user = new AdminUserViewModel();
		$res = $admin_user->getAdminUserViewList($page_index, $page_size, $condition, $order);
		return $res;
	}
	
	/***********************************************************后台用户结束*********************************************************/
	
	
	/***********************************************************日志开始*********************************************************/
	
	/**
	 * 根据主键id删除操作日志记录
	 */
	public function deleteAuthLog($ids)
	{
		$user_log = new UserLogModel();
		
		$data['id'] = [
			'in',
			$ids
		];
		$res = $user_log->destroy($data);
		return $res;
	}
	
	/**
	 * 用户日志列表
	 */
	public function getUserLogList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$user_log = new UserLogModel();
		$list = $user_log->pageQuery($page_index, $page_size, $condition, $order, '*');
		return $list;
	}
	
	/***********************************************************日志结束*********************************************************/
}