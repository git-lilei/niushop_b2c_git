<?php
/**
 * Auth.php
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
use data\model\AuthGroupModel;
use data\model\NsMemberLevelModel;
use data\model\NsMemberModel;
use data\model\SysModel;
use data\model\UserLogModel;
use data\service\User as User;
use think\Cache;

class Auth extends User
{
	
	private $authgroup;
	
	private $module;
	
	private $admin_user;
	
	public function __construct()
	{
		parent::__construct();
		$this->authgroup = new AuthGroupModel();
		$this->module = new SysModel();
		$this->admin_user = new AdminUserModel();
	}
	
	/*******************************************************系统模块********************************************************/
	
	/**
	 * 添加系统模块
	 */
	public function addSytemModule($data)
	{
		Cache::clear('module');
		// 查询level
		if ($data['pid'] == 0) {
			$level = 1;
		} else {
			$level = $this->getSystemModuleInfo($data['pid'], $field = 'level')['level'] + 1;
		}
		$data['level'] = $level;
		$mod = new SysModel();
		$res = $mod->save($data);
		$this->updateUserModule();
		return $res;
	}
	
	/**
	 * 修改系统模块
	 */
	public function updateSystemModule($data)
	{
		Cache::clear('module');
		// 查询level
		if ($data['pid'] == 0) {
			$level = 1;
		} else {
			$level = $this->getSystemModuleInfo($data['pid'], $field = 'level')['level'] + 1;
		}
		$data['level'] = $level;
		$mod = new SysModel();
		$res = $mod->allowField(true)->save($data, [
			'module_id' => $data['module_id']
		]);
		$this->updateUserModule();
		return $res;
	}
	
	/**
	 * 删除系统模块
	 */
	public function deleteSystemModule($module_id_array)
	{
		Cache::clear('module');
		$sub_list = $this->getModuleListByParentId($module_id_array);
		if (!empty($sub_list)) {
			$res = SYSTEM_DELETE_FAIL;
		} else {
			$res = $this->module->destroy($module_id_array);
		}
		$this->updateUserModule();
		return $res;
	}
	
	/**
	 * 安装模块菜单
	 */
	public function installModule($module_name, $module, $controller, $method, $pid, $is_menu, $is_dev, $sort, $module_picture, $desc, $icon_class, $is_control_auth)
	{
		Cache::clear('module');
		// 查询level
		if ($pid == 0) {
			$level = 1;
		} else {
			$level = $this->getSystemModuleInfo($pid, $field = 'level')['level'] + 1;
		}
		$data = array(
			'module_name' => $module_name,
			'module' => $module,
			'controller' => $controller,
			'method' => $method,
			'pid' => $pid,
			'level' => $level,
			'url' => $controller . '/' . $method,
			'is_menu' => $is_menu,
			"is_control_auth" => $is_control_auth,
			'is_dev' => $is_dev,
			'sort' => $sort,
			'module_picture' => $module_picture,
			'desc' => $desc,
			'create_time' => time(),
			'icon_class' => $icon_class
		);
		$mod = new SysModel();
		$res = $mod->save($data);
		$this->updateUserModule();
		if ($res > 0) {
			return $mod->module_id;
		} else {
			return 0;
		}
	}
	
	/**
	 * 修改系统模块 单个字段
	 */
	public function ModifyModuleField($module_id, $field_name, $field_value)
	{
		Cache::clear('module');
		$res = $this->module->ModifyTableField('module_id', $module_id, $field_name, $field_value);
		$this->updateUserModule();
		return $res;
	}
	
	/**
	 * 清除菜单
	 */
	private function updateUserModule()
	{
		Cache::clear('module');
	}
	
	/**
	 * 获取系统模块
	 */
	public function getSystemModuleInfo($module_id, $field = '*')
	{
		$cache = Cache::tag('module')->get('getSystemModuleInfo' . $module_id . $field);
		if (!empty($cache)) return $cache;
		
		$res = $this->module->getInfo(array(
			'module_id' => $module_id
		), $field);
		Cache::tag('module')->set('getSystemModuleInfo' . $module_id . $field, $res);
		
		return $res;
	}
	
	/**
	 * 获取系统模块列表
	 */
	public function getSystemModuleList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '*')
	{
		$cache = Cache::tag('module')->get('getSystemModuleList' . json_encode([ $page_index, $page_size, $condition, $order, $field ]));
		if (!empty($cache)) return $cache;
		
		// 针对开发者模式处理
		if (!config('app_debug')) {
			if (is_array($condition)) {
				$condition = array_merge($condition, [
					'is_dev' => 0
				]);
			} else {
				if (!empty($condition)) {
					$condition = $condition . ' and is_dev=0 ';
				} else {
					$condition = 'is_dev=0';
				}
			}
		}
		$res = $this->module->pageQuery($page_index, $page_size, $condition, $order, $field);
		Cache::tag('module')->set('getSystemModuleList' . json_encode([ $page_index, $page_size, $condition, $order, $field ]), $res);
		return $res;
	}
	
	/**
	 * 根据当前实例查询权限列表
	 */
	public function getInstanceModuleQuery()
	{
		// 单用户查询全部
		$condition_module = array(
			'is_control_auth' => 1
		);
		$moduelList = $this->getSystemModuleList(1, 0, $condition_module, 'sort desc');
		return $moduelList['data'];
	}
	
	/**
	 * 获取系统模块列表
	 *
	 * @param string $tpye 0 debug模式 1 部署模式
	 */
	public function getModuleListTree($type = 0)
	{
		$cache = Cache::tag('module')->get('getModuleListTree');
		if (!empty($cache)) return $cache;
		
		$list = $this->module->order('pid,sort')->select();
		$new_list = $this->list_tree($list);
		Cache::tag('module')->set('getModuleListTree', $new_list);
		return $new_list;
	}
	
	/**
	 * 数组转化为树
	 */
	private function list_tree($list, $p_id = '0')
	{
		$tree = array();
		foreach ($list as $row) {
			if ($row['pid'] == $p_id) {
				$tmp = $this->list_tree($list, $row['module_id']);
				if ($tmp) {
					$row['sub_menu'] = $tmp;
				} else {
					$row['leaf'] = true;
				}
				$tree[] = $row;
			}
		}
		Return $tree;
	}
	
	/**
	 * 查询模块的子项列表
	 */
	public function getModuleListByParentId($pid)
	{
		$cache = Cache::tag('module')->get('getModuleListByParentId' . $pid);
		if (!empty($cache)) return $cache;
		
		$list = $this->getSystemModuleList(1, 0, 'pid=' . $pid);
		Cache::tag('module')->set('getModuleListByParentId' . $pid, $list['data']);
		return $list['data'];
	}
	
	/**
	 * 获取当前节点的根节点以及二级节点项
	 */
	public function getModuleRootAndSecondMenu($module_id)
	{
		$cache = Cache::tag('module')->get('getModuleRootAndSecondMenu' . $module_id);
		if (!empty($cache)) return $cache;
		
		$count = $this->module->where([
			'module_id' => $module_id
		])
			->count();
		if ($count == 0) {
			$result = array(
				0,
				0
			);
		}
		$info = $this->module->getInfo([
			'module_id' => $module_id,
			'pid' => array(
				'neq',
				0
			)
		], 'pid, level');
		if (empty($info)) {
			$result = array(
				$module_id,
				0
			);
		} else {
			if ($info['level'] == 2) {
				$result = array(
					$info['pid'],
					$module_id
				);
			} else {
				$pid = $info['pid'];
				while ($pid != 0) {
					$module = $this->module->getInfo([
						'module_id' => $pid,
						'pid' => array(
							'neq',
							0
						)
					], 'pid, module_id, level');
					if ($module['level'] == 2) {
						$pid = 0;
						$result = array(
							$module['pid'],
							$module['module_id']
						);
					} else {
						$pid = $module['pid'];
					}
				}
			}
		}
		
		Cache::tag('module')->set('getModuleRootAndSecondMenu' . $module_id, $result);
		return $result;
	}
	
	/**
	 * 查询当前模块的上级ID
	 */
	public function getModulePid($module_id)
	{
		$cache = Cache::tag('module')->get('getModulePid' . '_' . $module_id);
		if (!empty($cache)) return $cache;
		$sys_module = new SysModel();
		$pid = $sys_module->get($module_id);
		Cache::tag('module')->set('getModulePid' . '_' . $module_id, $pid['pid']);
		return $pid['pid'];
	}
	
	/**
	 * 通过模块方法查询权限id
	 */
	public function getModuleIdByModule($controller, $action)
	{
		$cache = Cache::tag('module')->get('getModuleIdByModule' . '_' . $controller . '_' . $action);
		if (!empty($cache)) return $cache;
		$condition = array(
			'controller' => $controller,
			'method' => $action,
		);
		$sys_module = new SysModel();
		$count = $sys_module->where($condition)->count('module_id');
		if ($count > 1) {
			$condition = array(
				'controller' => $controller,
				'method' => $action,
				'pid' => array(
					'<>',
					0
				)
			);
		}
		$res = $sys_module->getInfo($condition);
		Cache::tag('module')->set('getModuleIdByModule' . '_' . $controller . '_' . $action, $res);
		return $res;
	}
	
	/**
	 * 查询权限节点的根节点
	 */
	public function getModuleRoot($module_id)
	{
		$cache = Cache::tag('module')->get('getModuleRoot' . '_' . $module_id);
		if (!empty($cache)) return $cache;
		$root_id = $module_id;
		$sys_module = new SysModel();
		$pid = $sys_module->getInfo([
			'module_id' => $module_id
		], 'pid');
		$pid = $pid['pid'];
		if (empty($pid)) {
			return 0;
		}
		while ($pid != 0) {
			$module = $sys_module->getInfo([
				'module_id' => $pid
			], 'pid, module_id');
			$root_id = $module['module_id'];
			$pid = $module['pid'];
		}
		Cache::tag('module')->set('getModuleIdByModule' . '_' . $module_id, $root_id);
		return $root_id;
	}
	
	/**
	 * 通过权限id组查询权限列表
	 */
	public function getAuthList($pid)
	{
		$cache = Cache::tag('module')->get('getAuthList' . '_' . $pid);
		if (!empty($cache)) return $cache;
		$condition = array(
			'pid' => $pid,
			'is_menu' => 1
		);
		$sys_module = new SysModel();
		$list = $sys_module->where($condition)
			->order("sort")
			->column('module_id,module_name,module,controller,method,pid,url,is_menu,is_dev,icon_class,is_control_auth');
		Cache::tag('module')->set('getAuthList' . '_' . $pid, $list);
		return $list;
	}
	
	/**
	 * 通过权限id组查询权限列表
	 */
	public function getAuthListByModule($pid, $module_id_array)
	{
		$cache = Cache::tag('module')->get('getAuthListByModule' . '_' . $pid . '_' . $module_id_array);
		if (!empty($cache)) return $cache;
		$condition = array(
			'pid' => $pid,
			'module_id' => array( 'in', $module_id_array )
		);
		$sys_module = new SysModel();
		$list = $sys_module->where($condition)
			->order("sort")
			->column('module_id,module_name,module,controller,method,pid,url,is_menu,is_dev,icon_class,is_control_auth');
		Cache::tag('module')->set('getAuthListByModule' . '_' . $pid, $list);
		return $list;
	}
	
	/**
	 * 获取不控制权限模块组
	 */
	private function getNoControlAuth()
	{
		$cache = Cache::tag('module')->get('getNoControlAuth');
		if (!empty($cache)) return $cache;
		$moudle = new SysModel();
		$list = $moudle->getQuery([
			"is_control_auth" => 0
		], "module_id");
		$str = "";
		foreach ($list as $v) {
			$str .= $v["module_id"] . ",";
		}
		Cache::tag('module')->set('getNoControlAuth', $str);
		return $str;
	}
	/*********************************************************模块管理结束****************************************************/
	
	/**
	 * 查询权限组的id序列
	 */
	public function getAuthGroupModuleList($group_id)
	{
		$auth_group_info = $this->getSystemUserGroupDetail($group_id);
		$no_control_auth = $this->getNoControlAuth();
		return $auth_group_info['module_id_array'] . ',' . $no_control_auth;
	}
	
	/**
	 * 检测用户是否具有打开权限
	 */
	public function checkAuth($module_id)
	{
		;
		if ($this->is_admin) {
			return 1;
		} else {
			$module_list = $this->getAuthGroupModuleList($this->group_id);
			if (strstr($module_list . ',', $module_id . ',')) {
				return 1;
			} else {
				return 0;
			}
		}
	}
	
	/**
	 * 获取用户的权限子项
	 * @param int $moduleid（0标示根节点子项）
	 */
	public function getchildModuleQuery($moduleid)
	{
		$module_list = Cache::tag('module')->get('getchildModuleQuery' . '_' . $this->group_id . '_' . $moduleid);
		if (empty($module_list)) {
			if ($this->is_admin) {
				
				$list = $this->getAuthList($moduleid);
				$new_list = $list;
			} else {
				
				$list = $this->getAuthList($moduleid);
				$module_id_array = explode(',', $this->getAuthGroupModuleList($this->group_id));
				$new_list = array();
				if ($moduleid != 0) {
					
					foreach ($list as $k => $v) {
						if (in_array($list[ $k ]['module_id'], $module_id_array)) {
							$new_list[] = $list[ $k ];
						}
					}
				} else {
					
					foreach ($list as $k => $v) {
						$check_module_id = $this->getModuleIdByModule($v['controller'], $v['method']);
						$check_auth = $this->checkAuth($check_module_id);
						if ($check_auth == 0) {
							$sub_menu = $this->getchildModuleQuery($v['module_id']);
							if (!empty($sub_menu[0])) {
								$v['url'] = $sub_menu[0]['url'];
							}
						}
						if (in_array($list[ $k ]['module_id'], $module_id_array)) {
							$new_list[] = $v;
						}
					}
				}
			}
			$arrange_list = array();
			foreach ($new_list as $k => $v) {
				if ($v['is_dev'] == 0) {
					$arrange_list[] = $new_list[ $k ];
				}
			}
			Cache::tag('module')->set('getchildModuleQuery' . '_' . $this->group_id . '_' . $moduleid, $arrange_list);
			return $arrange_list;
			
		} else {
			return $module_list;
		}
	}
	
	/***********************************************************权限结束*********************************************************/
	
	/***********************************************************后台用户开始*********************************************************/
	
	/**
	 * 添加后台用户
	 */
	public function addAdminUser($data)
	{
		$uid = $this->add($data['admin_name'], $data['user_password'], '', '', 1, '', '', '', '', '', 1, 0);
		if ($uid <= 0) {
			return $uid;
		}
		$data_admin = array(
			'uid' => $uid,
			'admin_name' => $data['admin_name'],
			'group_id_array' => $data['group_id_array'],
			'admin_status' => 1,
			'desc' => $data['desc']
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
			'member_name' => $data['admin_name'],
			'reg_time' => time(),
			'member_level' => $level_id
		);
		$member->save($data_member);
		$this->admin_user->save($data_admin);
		$this->addUserLog($this->uid, 1, '用户', '添加用户', '添加用户：' . $data['admin_name']);
		$res = $member->uid;
		return $res;
	}
	
	/**
	 * 编辑后台用户
	 */
	public function editAdminUser($data)
	{
		$this->modifyUserName($data['uid'], $data['admin_name']);
		$data_admin = array(
			'admin_name' => $data['admin_name'],
			'group_id_array' => $data['group_id_array'],
			'admin_status' => 1,
			'desc' => $data['desc']
		);
		$res = $this->admin_user->save($data_admin, [
			"uid" => $data['uid']
		]);
		$this->addUserLog($this->uid, 1, '用户', '编辑用户', '编辑用户：' . $data['admin_name']);
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
		$admin_user_info = $this->admin_user->getInfo($condition, $field);
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
	/***********************************************************用户组开始*********************************************************/
	
	/**
	 * 添加系统用户组
	 */
	public function addSystemUserGroup($data)
	{
		Cache::clear('authgroup');
		$count = $this->authgroup->getCount([ 'group_name' => $data['group_name'] ]);
		if ($count > 0) {
			return USER_GROUP_REPEAT;
		}
		$res = $this->authgroup->save($data);
		return $res;
	}
	
	/**
	 * 修改系统用户组
	 */
	public function updateSystemUserGroup($data)
	{
		Cache::clear('authgroup');
		$group_info = $this->authgroup->getInfo([ 'group_id' => $data['group_id'] ], '*');
		if ($data['group_name'] != $group_info['group_name']) {
			$count = $this->authgroup->getCount([ 'group_name' => $data['group_name'] ]);
			if ($count > 0) {
				return USER_GROUP_REPEAT;
			}
		}
		$res = $this->authgroup->save($data, [ 'group_id' => $data['group_id'] ]);
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