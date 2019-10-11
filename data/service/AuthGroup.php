<?php
/**
 * AuthGroup.php
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
use data\model\AuthGroupModel;
use think\Cache;

class AuthGroup extends BaseService
{
	private $authgroup;
	
	public function __construct()
	{
		parent::__construct();
		$this->authgroup = new AuthGroupModel();
	}
	
	/***********************************************************用户组开始*********************************************************/
	
	/**
	 * 添加系统用户组
	 */
	public function addSystemUserGroup($group_id, $group_name, $is_system, $module_id_array, $desc)
	{
		Cache::clear('authgroup');
		$count = $this->authgroup->getCount([ 'group_name' => $group_name ]);
		if ($count > 0) {
			return USER_GROUP_REPEAT;
		}
		$data = array(
			'group_name' => $group_name,
			'instance_id' => $this->instance_id,
			'is_system' => $is_system,
			'module_id_array' => $module_id_array,
			'desc' => $desc,
			'create_time' => time()
		);
		$res = $this->authgroup->save($data);
		return $res;
	}
	
	/**
	 * 修改系统用户组
	 */
	public function updateSystemUserGroup($group_id, $group_name, $group_status, $is_system, $module_id_array, $desc)
	{
		Cache::clear('authgroup');
		$group_info = $this->authgroup->getInfo([ 'group_id' => $group_id ], '*');
		if ($group_name != $group_info['group_name']) {
			$count = $this->authgroup->getCount([ 'group_name' => $group_name ]);
			if ($count > 0) {
				return USER_GROUP_REPEAT;
			}
		}
		$data = array(
			'group_name' => $group_name,
			'group_status' => $group_status,
			'is_system' => $is_system,
			'module_id_array' => $module_id_array,
			'desc' => $desc,
			'modify_time' => time()
		);
		$res = $this->authgroup->save($data, [ 'group_id' => $group_id ]);
		return $res;
	}
	
	/**
	 * 修改用户组的状态
	 */
	public function ModifyUserGroupStatus($group_id, $group_status)
	{
		Cache::clear('authgroup');
		$data = array(
			'group_status' => $group_status
		);
		$res = $this->authgroup->save($data, [ 'group_id' => $group_id ]);
		return $res;
	}
	
	/**
	 * 删除用户组
	 * @param int $group_id
	 */
	public function deleteSystemUserGroup($group_id)
	{
		Cache::clear('authgroup');
		$count = $this->getUserGroupIsUse($group_id);
		if ($count > 0) {
			return USER_GROUP_ISUSE;
		} else {
			$res = $this->authgroup->where('group_id', $group_id)->delete();
			return $res;
		}
		
	}
	
	/**
	 * 获取权限使用数量（0表示未使用）
	 */
	private function getUserGroupIsUse($group_id)
	{
		$user_admin = new AdminUserModel();
		$count = $user_admin->getCount([ 'group_id_array' => $group_id ]);
		return $count;
	}
	
	/**
	 * 获取用户组详情
	 * @param int $group_id
	 */
	public function getSystemUserGroupDetail($group_id)
	{
		$cache = Cache::tag("authgroup")->get("getSystemUserGroupDetail" . $group_id);
		if (!empty($cache)) {
			return $cache;
		}
		$data = $this->authgroup->get($group_id);
		Cache::tag("authgroup")->set("getSystemUserGroupDetail" . $group_id, $data);
		return $data;
	}
	
	/**
	 * 查询所有用户组
	 */
	public function getSystemUserGroupAll($where)
	{
		$cache = Cache::tag("authgroup")->get("getSystemUserGroupDetail" . json_encode($where));
		if (!empty($cache)) {
			return $cache;
		}
		$all = $this->authgroup->getQuery($where);
		Cache::tag("authgroup")->set("getSystemUserGroupAll" . json_encode($where), $all);
		return $all;
	}
	
	/**
	 * 获取系统用户组
	 */
	public function getSystemUserGroupList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = "*")
	{
		$data = array( $page_index, $page_size, $condition, $order, $field );
		$data = json_encode($data);
		$cache = Cache::tag("authgroup")->get("getSystemUserGroupList" . $data);
		if (!empty($cache)) {
			return $cache;
		}
		$list = $this->authgroup->pageQuery($page_index, $page_size, $condition, $order, $field);
		Cache::tag("authgroup")->set("getSystemUserGroupList" . $data, $list);
		return $list;
	}
	
	/***********************************************************用户组结束*********************************************************/
}