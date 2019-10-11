<?php
/**
 * Auth.php
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

namespace app\admin\controller;

/**
 * 用户权限控制器
 */
use think\Request;

class Auth extends BaseController
{
	
	/**
	 * 用户列表
	 */
	public function userList()
	{
		if (request()->isAjax()) {
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$search_text = request()->post('search_text', '');
			$condition = [];
			if (!empty($search_text)) {
				$condition = array(
					'sur.instance_id' => $this->instance_id,
					'sur.user_name|sua.uid' => array(
						'like',
						'%' . $search_text . '%'
					)
				);
			}
			$user_list = $this->auth->adminUserList($page_index, $page_size, $condition, "is_admin desc,uid desc");
			return $user_list;
		} else {
			$child_menu_list = array(
				array(
					'url' => "auth/userlist",
					'menu_name' => "用户列表",
					"active" => 1
				),
				array(
					'url' => "auth/authgrouplist",
					'menu_name' => "权限组",
					"active" => 0
				),
				array(
					'url' => "auth/authLog",
					'menu_name' => "操作日志",
					"active" => 0
				)
			);
			$this->assign("child_menu_list", $child_menu_list);
			return view($this->style . 'Auth/userList');
		}
	}
	
	/**
	 * 获取用户日志列表
	 */
	public function authLog()
	{
		if (request()->isAjax()) {
			$start_date = request()->post('start_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('start_date'));
			$end_date = request()->post('end_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('end_date'));
			$page_index = request()->post("page_index", 1);
			$page_size = request()->post("page_size", PAGESIZE);
			$user_name = request()->post("search_text", "");
			if ($start_date != 0 && $end_date != 0) {
				$condition["create_time"] = [
					[
						">",
						$start_date
					],
					[
						"<",
						$end_date
					]
				];
			} elseif ($start_date != 0 && $end_date == 0) {
				$condition["create_time"] = [
					[
						">",
						$start_date
					]
				];
			} elseif ($start_date == 0 && $end_date != 0) {
				$condition["create_time"] = [
					[
						"<",
						$end_date
					]
				];
			}
			if (!empty($user_name)) {
				$condition["user_name"] = [
					[
						"like",
						"%$user_name%"
					]
				];
			}
			
			$list = $this->auth->getUserLogList($page_index, $page_size, $condition, "create_time desc");
			return $list;
		}
		
		$child_menu_list = array(
			array(
				'url' => "auth/userlist",
				'menu_name' => "用户列表",
				"active" => 0
			),
			array(
				'url' => "auth/authgrouplist",
				'menu_name' => "权限组",
				"active" => 0
			),
			array(
				'url' => "auth/authLog",
				'menu_name' => "操作日志",
				"active" => 1
			)
		);
		$this->assign("child_menu_list", $child_menu_list);
		return view($this->style . "Auth/authLog");
	}
	
	/**
	 * 删除 操作记录
	 */
	public function deleteLog()
	{
		if (request()->isAjax()) {
			$id = request()->post("id", "");
			if (!empty($id)) {
				$res = $this->auth->deleteAuthLog($id);
			}
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 用户组列表
	 */
	public function authGroupList()
	{
		if (request()->isAjax()) {
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$list = $this->auth->getSystemUserGroupList($page_index, $page_size, [
				'instance_id' => $this->instance_id
			], "is_system desc,create_time desc");
			return $list;
		} else {
			$permissionList = $this->auth->getInstanceModuleQuery();
			$firstArray = array();
			$p = array();
			for ($i = 0; $i < count($permissionList); $i++) {
				$per = $permissionList[ $i ];
				if ($per["pid"] == 0 && $per["module_name"] != null) {
					$firstArray[] = $per;
				}
			}
			for ($i = 0; $i < count($firstArray); $i++) {
				$first_per = $firstArray[ $i ];
				$secondArray = array();
				for ($y = 0; $y < count($permissionList); $y++) {
					$childPer = $permissionList[ $y ];
					if ($childPer["pid"] == $first_per["module_id"]) {
						$secondArray[] = $childPer;
					}
				}
				$first_per['child'] = $secondArray;
				for ($j = 0; $j < count($secondArray); $j++) {
					$second_per = $secondArray[ $j ];
					$threeArray = array();
					for ($z = 0; $z < count($permissionList); $z++) {
						$three_per = $permissionList[ $z ];
						if ($three_per["pid"] == $second_per["module_id"]) {
							$threeArray[] = $three_per;
						}
					}
					$second_per['child'] = $threeArray;
				}
				$p[] = $first_per;
			}
			$this->assign("list", $p);
			$child_menu_list = array(
				array(
					'url' => "auth/userlist",
					'menu_name' => "用户列表",
					"active" => 0
				),
				array(
					'url' => "auth/authgrouplist",
					'menu_name' => "权限组",
					"active" => 1
				),
				array(
					'url' => "auth/authLog",
					'menu_name' => "操作日志",
					"active" => 0
				)
			);
			$this->assign("child_menu_list", $child_menu_list);
			return view($this->style . 'Auth/authGroupList');
		}
	}
	
	/**
	 * 添加或者编辑用户组
	 */
	public function addUserGroup()
	{
		$group_id = request()->post('roleId', 0);
		$module_id_array = request()->post('array', '');
		$role_name = request()->post('roleName', '');
		$data = array(
			'group_name' => $role_name,
			'instance_id' => $this->instance_id,
			'is_system' => 0,
			'group_status' => 1,
			'module_id_array' => $module_id_array,
			'desc' => '',
			'create_time' => time()
		);
		if ($group_id != 0) {
			$data['group_id'] = $group_id;
			$retval = $this->auth->updateSystemUserGroup($data);
		} else {
			$retval = $this->auth->addSystemUserGroup($data);
		}
		return AjaxReturn($retval);
	}
	
	/**
	 * 添加 后台用户
	 */
	public function addUser()
	{
		if (request()->isAjax()) {
			$admin_name = request()->post('admin_name', '');
			$group_id = request()->post('group_id', '');
			$user_password = request()->post('user_password', '123456');
			$desc = request()->post('desc', '');
			$data_admin = array(
				'admin_name' => $admin_name,
				'group_id_array' => $group_id,
				'admin_status' => 1,
				'user_password' => $user_password,
				'desc' => $desc
			);
			$retval = $this->auth->addAdminUser($data_admin);
			return AjaxReturn($retval);
		} else {
			$condition["instance_id"] = $this->instance_id;
			$list = $this->auth->getSystemUserGroupAll($condition);
			$this->assign('auth_group', $list);
			return view($this->style . 'Auth/addUser');
		}
	}
	
	/**
	 * 修改后台用户
	 */
	public function editUser()
	{
		if (request()->isAjax()) {
			$uid = request()->post('uid', '');
			$admin_name = request()->post('admin_name', '');
			$group_id = request()->post('group_id', '');
			$desc = request()->post('desc', '');
			if ($uid == '' || $admin_name == '' || $group_id == '') {
				$this->error('未获取到信息');
			}
			$data = array(
				'uid' => $uid,
				'admin_name' => $admin_name,
				'group_id_array' => $group_id,
				'admin_status' => 1,
				'desc' => $desc
			);
			$retval = $this->auth->editAdminUser($data);
			return AjaxReturn($retval);
		} else {
			$uid = request()->get('uid', 0);
			if ($uid == 0) {
				$this->error("没有获取到用户信息");
			}
			$ua_info = $this->auth->getAdminUserInfo("uid = " . $uid);
			$this->assign("ua_info", $ua_info);
			$condition["instance_id"] = $this->instance_id;
			$list = $this->auth->getSystemUserGroupAll($condition);
			$this->assign('auth_group', $list);
			return view($this->style . 'Auth/editUser');
		}
	}
	
	/**
	 * 删除系统用户组
	 */
	public function deleteSystemUserGroup()
	{
		$group_id = request()->post('group_id', '');
		if (!is_numeric($group_id)) {
			$this->error("请传入正确参数");
		}
		$retval = $this->auth->deleteSystemUserGroup($group_id);
		return AjaxReturn($retval);
	}
	
	/**
	 * 用户的 锁定
	 */
	public function userLock()
	{
		$uid = request()->post('uid', 0);
		if ($uid > 0) {
			$res = $this->auth->userLock($uid);
		}
		return AjaxReturn($res);
	}
	
	/**
	 * 解锁
	 */
	public function userUnlock()
	{
		$uid = request()->post('uid', 0);
		if ($uid > 0) {
			$res = $this->auth->userUnlock($uid);
		}
		return AjaxReturn($res);
	}
	
	/**
	 * 重置密码
	 */
	public function resetUserPassword()
	{
		$uid = request()->post('uid', 0);
		if ($uid > 0) {
			$res = $this->auth->resetUserPassword($uid);
		}
		return AjaxReturn($res);
	}
	
	/**
	 * 个人资料页面
	 */
	public function userDetail()
	{
		if (request()->isAjax()) {
			$user_headimg = request()->post('user_headimg', '');
			$user_qq = request()->post('user_qq', '');
			$this->auth->modifyUserHeadimg($this->uid, $user_headimg);
			$this->auth->modifyQQ($this->uid, $user_qq);
			return AjaxReturn(1);
		}
		$_SESSION['bind_pre_url'] = Request::instance()->domain() . $_SERVER['REQUEST_URI'];
		$info = $this->auth->getUserDetail();
		$this->assign('info', $info);
		$img_md5 = md5(time() . "niuku");
		session::set("niuku", $img_md5);
		$this->assign("niuku", $img_md5);
		return view($this->style . "Auth/userDetail");
	}
	
	/**
	 * 修改会员 用户名
	 */
	public function modifyUserName()
	{
		$user_name = request()->post('user_name', '');
		if (!empty($user_name)) {
			$res = $this->auth->modifyUserName($this->uid, $user_name);
		}
		return AjaxReturn($res);
	}
	
	/**
	 * 会员手机号绑定
	 */
	public function userTelBind()
	{
		$res = $this->auth->userTelBind($this->uid);
		return AjaxReturn($res);
	}
	
	/**
	 * 解除 会员手机号 绑定
	 */
	public function removeUserTelBind()
	{
		$res = $this->auth->removeUserTelBind($this->uid);
		return AjaxReturn($res);
	}
	
	/**
	 * 修改 会员 手机号
	 */
	public function modifyUserTel()
	{
		$user_tel = request()->post('user_tel', '');
		if (!empty($user_tel)) {
			$res = $this->auth->modifyMobile($this->uid, $user_tel);
		}
		return AjaxReturn($res);
	}
	
	/**
	 * 会员邮箱 绑定
	 */
	public function userEmailBind()
	{
		$res = $this->auth->userEmailBind($this->uid);
		return AjaxReturn($res);
	}
	
	/**
	 * 解除 会员邮箱 绑定
	 */
	public function removeUserEmailBind()
	{
		$res = $this->auth->removeUserEmailBind($this->uid);
		return AjaxReturn($res);
	}
	
	/**
	 * 修改 会员 邮箱
	 */
	public function modifyUserEmail()
	{
		$user_email = request()->post('user_email', '');
		if (!empty($user_email)) {
			$res = $this->auth->modifyEmail($this->uid, $user_email);
		}
		return AjaxReturn($res);
	}
	
	/**
	 * 解除 会员 qq 绑定
	 *
	 * @return array
	 */
	public function removeUserQQBind()
	{
		$res = $this->auth->removeBindQQ();
		return AjaxReturn($res);
	}
	
	/**
	 * 删除 后台会员
	 */
	public function deleteAdminUserAjax()
	{
		if (request()->isAjax()) {
			$uid = request()->post("uid", "");
			if (!empty($uid)) {
				$res = $this->auth->deleteAdminUser($uid);
			}
			return AjaxReturn($res);
		}
	}
}