<?php

namespace data\service;

use data\service\BaseService as BaseService;
use data\model\SysAddonsModel;
use data\model\SysHooksModel;
use data\model\ModuleModel;
use think\Db;
use think\Log;
use data\model\BaseModel;
use think\Cache;

class Extend extends BaseService
{
    public function getAddonsList($page_index = 1, $page_size = PAGESIZE, $condition = '', $order = '', $field = '*')
    {
        $sys_addons = new SysAddonsModel();
        $addon_dir = ADDON_PATH;
        $dirs = array_map('basename', glob($addon_dir . '*', GLOB_ONLYDIR));
        Log::write($dirs);
        if ($dirs === FALSE || !file_exists($addon_dir)) {
            $this->error = '插件目录不可读或者不存在';
            return FALSE;
        }
        $addons = array();
        $where['name'] = array('in', $dirs);
        $list = $sys_addons->getQuery($where, '*', 'create_time desc');
        foreach ($list as $key => $value) {
            $list[$key] = $value->toArray();
        }
        foreach ($list as $addon) {
            $addon['uninstall'] = 0;
            $addons[$addon['name']] = $addon;
        }
        foreach ($dirs as $value) {
            if (!isset($addons[$value])) {
                $class = get_addon_class($value);
                if (!class_exists($class)) {
                    trace($class);
                    \think\Log::record('插件' . $value . '的入口文件不存在！');
                    continue;
                }
                $obj = new $class();
                $addons[$value] = $obj->info;
                if ($addons[$value]) {
                    $addons[$value]['uninstall'] = 1;
                    unset($addons[$value]['status']);
                }
            }
        }
        $addons = $this->list_sort_by($addons, 'uninstall', 'desc');
        $total_count = count($addons);
        $page_count = 1;
        $key_start = 0;
        $key_end = $total_count - 1;
        $new_array['data'] = $addons;
        $new_array['total_count'] = $total_count;
        $new_array['page_count'] = $page_count;
        return $new_array;
    }
    
    public function getAddons()
    {
        $cache = Cache::tag('addon')->get('addons_name');
        if (!empty($cache)) {
            return $cache;
        }
        $sys_addons = new SysAddonsModel();
        $addons = $sys_addons->getColumn([], 'name');
        Cache::tag('addon')->set('addons_name', $addons);
        return $addons;
    }
    
    public function addAddons($name, $title, $description, $status, $config, $author, $version, $has_adminlist, $has_addonslist, $config_hook, $content, $ico)
    {
        $sys_addons = new SysAddonsModel();
        $data = array('name' => $name, 'title' => $title, 'description' => $description, 'status' => $status, 'config' => $config, 'author' => $author, 'version' => $version, 'has_adminlist' => $has_adminlist, 'has_addonslist' => $has_addonslist, 'config_hook' => $config_hook, 'content' => $content, 'create_time' => time(), 'ico' => $ico);
        $res = $sys_addons->save($data);
        return $sys_addons->id;
    }
    
    public function installAddon($addon_name)
    {
        Cache::clear('addon');
        Cache::clear('module');
        $sys_addons = new SysAddonsModel();
        $sys_addons->startTrans();
        try {
            $class = get_addon_class($addon_name);
            if (!class_exists($class)) {
                $sys_addons->rollback();
                return AjaxReturn("插件不存在");
            }
            $addons = new $class();
            $info = $addons->info;
            if (!$info) {
                $sys_addons->rollback();
                return AjaxReturn('插件信息缺失');
            }
            $check = $sys_addons->getInfo(['name' => $info['name']]);
            if (!empty($check)) {
                $sys_addons->rollback();
                return AjaxReturn('插件已存在');
            }
            session('addons_install_error', null);
            $config_class = get_addon_config($addon_name);
            if (!class_exists($class)) {
                $addons->menu_info = [];
            } else {
                $config = new $config_class();
                $addons->menu_info = $config->menu();
            }
            if (!empty($addons->menu_info)) {
                $menu = $addons->menu_info;
                $website = new Auth();
                $module_model = new ModuleModel();
                foreach ($menu as $k => $v) {
                    if (isset($v['parent'])) {
                        if (!empty($v['parent'])) {
                            $parent_module_condition = ['module' => $v['parent']['module'], 'controller' => $v['parent']['controller'], 'method' => $v['parent']['method']];
                            if (isset($v['parent']['level'])) {
                                $parent_module_condition['level'] = $v['parent']['level'];
                            }
                            $parent_module_info = $module_model->getInfo($parent_module_condition, '*');
                            Log::write("检测存在上级" . $parent_module_info['module_id']);
                        } else {
                            $parent_module_info = [];
                        }
                    } else {
                        $parent_module_info = [];
                    }
                    if (empty($parent_module_info)) {
                        $pid = 0;
                    } else {
                        $pid = $parent_module_info['module_id'];
                    }
                    $c_pid = $website->installModule($v['module_name'], $info['name'], $v['controller'], $v['method'], $pid, $v['is_menu'], $v['is_dev'], $v['sort'], $v['module_picture'], $v['desc'], $v['icon_class'], $v['is_control_auth']);
                    if (isset($v['child'])) {
                        $child_menu = $v['child'];
                        if (!empty($child_menu)) {
                            foreach ($child_menu as $k_c => $v_c) {
                                $g_pid = $website->installModule($v_c['module_name'], $info['name'], $v_c['controller'], $v_c['method'], $c_pid, $v_c['is_menu'], $v_c['is_dev'], $v_c['sort'], $v_c['module_picture'], $v_c['desc'], $v_c['icon_class'], $v_c['is_control_auth']);
                                if (isset($v_c['child'])) {
                                    $g_menu = $v_c['child'];
                                    foreach ($g_menu as $k_g => $v_g) {
                                        $website->installModule($v_g['module_name'], $info['name'], $v_g['controller'], $v_g['method'], $g_pid, $v_g['is_menu'], $v_g['is_dev'], $v_g['sort'], $v_g['module_picture'], $v_g['desc'], $v_g['icon_class'], $v_g['is_control_auth']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $res = $this->addAddons($info['name'], $info['title'], $info['description'], $info['status'], '', $info['author'], $info['version'], '', '', '', $info['content'], $info['ico']);
            Cache::clear('module');
            $addons->install();
            $hooks_update = $this->updateHooks($addon_name);
            if ($hooks_update) {
                cache('hooks', null);
                $sys_addons->commit();
                return AjaxReturn(1);
            } else {
                $this->extend->deleteAddons(['name' => $addon_name]);
                $sys_addons->rollback();
                return AjaxReturn('更新钩子处插件失败,请卸载后尝试重新安装');
            }
        } catch (\Exception $e) {
            Log::write("安装错误" . $e->getMessage());
            $sys_addons->rollback();
            return $e;
        }
    }
    
    public function updateHooks($addons_name)
    {
        $public = ['__construct', 'install', 'uninstall', 'redirect', 'fetch', 'display', 'show', 'assign', 'getError', 'checkInfo', 'getName', 'getAllConfig', 'getOneConfig'];
        $sys_hooks = new SysHooksModel();
        $addons_class = get_addon_class($addons_name);
        if (!class_exists($addons_class)) {
            return false;
        }
        $methods = get_class_methods($addons_class);
        $hooks = $sys_hooks->column('name');
        $methods_hooks = array_diff($methods, $public);
        foreach ($methods_hooks as $hook) {
            if (in_array($hook, $hooks)) {
                $flag = $this->updateAddons($hook, array($addons_name));
                if (false === $flag) {
                    $this->removeHooks($addons_name);
                    return false;
                }
            } else {
                $sys_hooks = new SysHooksModel();
                $flag = $sys_hooks->save(['name' => $hook, 'update_time' => time(), 'description' => '', 'addons' => $addons_name]);
                if (false === $flag) {
                    return false;
                }
            }
        }
        return true;
    }
    
    public function updateAddons($hook_name, $addons_name)
    {
        $sys_hooks = new SysHooksModel();
        $hooks_info = $sys_hooks->getInfo(['name' => $hook_name], 'addons');
        $o_addons = $hooks_info['addons'];
        if ($o_addons) {
            $o_addons = explode(',', $o_addons);
        }
        if ($o_addons) {
            $addons = array_merge($o_addons, $addons_name);
            $addons = array_unique($addons);
        } else {
            $addons = $addons_name;
        }
        $addons = implode(',', $addons);
        if ($o_addons) {
            $o_addons = implode(',', $o_addons);
        }
        $res = $sys_hooks->save(['addons' => $addons], ['name' => $hook_name]);
        if (false === $res) {
            $sys_hooks->save(['addons' => $o_addons], ['name' => $hook_name]);
        }
        return $res;
    }
    
    public function removeHooks($addons_name)
    {
        $sys_hooks = new SysHooksModel();
        $addons_class = get_addon_class($addons_name);
        if (!class_exists($addons_class)) {
            return false;
        }
        $methods = get_class_methods($addons_class);
        $hooks = $sys_hooks->column('name');
        $common = array_intersect($hooks, $methods);
        if ($common) {
            foreach ($common as $hook) {
                $flag = $this->removeAddons($hook, array($addons_name));
                if (false === $flag) {
                    return false;
                }
            }
        }
        return true;
    }
    
    public function removeAddons($hook_name, $addons_name)
    {
        $sys_hooks = new SysHooksModel();
        $hooks_info = $sys_hooks->getInfo(['name' => $hook_name], 'addons');
        $o_addons = explode(',', $hooks_info['addons']);
        if ($o_addons) {
            $addons = array_diff($o_addons, $addons_name);
        } else {
            return true;
        }
        $addons = implode(',', $addons);
        $o_addons = implode(',', $o_addons);
        $flag = $sys_hooks->save(['addons' => $addons], ['name' => $hook_name]);
        if (false === $flag) {
            $sys_hooks->save(['addons' => $o_addons], ['name' => $hook_name]);
        }
        return $flag;
    }
    
    public function removeMenu($addon)
    {
        $module = new ModuleModel();
        $res = $module->destroy(['module' => $addon]);
        return $res;
    }
    
    public function deleteAddons($condition)
    {
        $sys_addons = new SysAddonsModel();
        return $sys_addons->destroy($condition);
    }
    
    public function getAddonsInfo($condition, $field = '*')
    {
        $sys_addons = new SysAddonsModel();
        return $sys_addons->getInfo($condition, $field);
    }
    
    public function updateAddonsStatus($id, $status)
    {
        $sys_addons = new SysAddonsModel();
        return $sys_addons->save(['status' => $status], ['id' => $id]);
    }
    
    public function getPluginList($id)
    {
        $sys_addons = new SysAddonsModel();
        $addons_info = $sys_addons->getInfo(['id' => $id], 'name');
        $addon_name = $addons_info['name'];
        $addon_dir = ADDON_PATH . $addon_name . '/';
        $dirs = array_map('basename', glob($addon_dir . '*', GLOB_ONLYDIR));
        if ($dirs === FALSE || !file_exists($addon_dir)) {
            $this->error = '插件目录不可读或者不存在';
            return FALSE;
        }
        $addon_type_class = get_addon_class($addon_name);
        if (!class_exists($addon_type_class)) {
            trace($addon_type_class);
            \think\Log::record('插件' . $addon_type_class . '的入口文件不存在！');
            return false;
        }
        $obj = new $addon_type_class();
        $table = $obj->table;
        $addons = array();
        $where['name'] = array('in', $dirs);
        $list = Db::table("$table")->where($where)->select();
        foreach ($list as $addon) {
            $addon['uninstall'] = 0;
            $addons[$addon['name']] = $addon;
        }
        foreach ($dirs as $value) {
            if (!isset($addons[$value]) && ($value != 'core')) {
                $temp_arr = array();
                if (is_file($addon_dir . $value . '/config.php')) {
                    $temp_arr = include $addon_dir . $value . '/config.php';
                }
                $addons[$value] = $temp_arr;
            }
        }
        $addons = $this->list_sort_by($addons, 'id');
        return $addons;
    }
    
    public function getHooksList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '*')
    {
        $sys_hooks = new SysHooksModel();
        return $sys_hooks->pageQuery($page_index, $page_size, $condition, $order, $field);
    }
    
    public function getHoodsInfo($condition, $field = '*')
    {
        $sys_hooks = new SysHooksModel();
        $info = $sys_hooks->getInfo($condition, $field);
        return $info;
    }
    
    public function addHooks($name, $description, $type)
    {
        $sys_hooks = new SysHooksModel();
        $data = array('name' => $name, 'description' => $description, 'type' => $type, 'update_time' => time());
        $sys_hooks->save($data);
        return $sys_hooks->id;
    }
    
    public function editHooks($id, $name, $description, $type, $addons)
    {
        $sys_hooks = new SysHooksModel();
        $data = array('name' => $name, 'description' => $description, 'type' => $type, 'update_time' => time());
        $res = $sys_hooks->save($data, ['id' => $id]);
        return $res;
    }
    
    public function deleteHooks($id)
    {
        $sys_hooks = new SysHooksModel();
        return $sys_hooks->destroy($id);
    }
    
    public function updateAddonsConfig($condition, $config)
    {
        $sys_addons = new SysAddonsModel();
        return $sys_addons->save(['config' => $config], $condition);
    }
    
    protected function list_sort_by($list, $field, $sortby = 'asc')
    {
        if (is_array($list)) {
            $refer = $resultSet = array();
            foreach ($list as $i => $data) $refer[$i] = &$data[$field];
            switch ($sortby) {
                case 'asc':
                    asort($refer);
                    break;
                case 'desc':
                    arsort($refer);
                    break;
                case 'nat':
                    natcasesort($refer);
                    break;
            }
            foreach ($refer as $key => $val) $resultSet[] = &$list[$key];
            return $resultSet;
        }
        return false;
    }
}