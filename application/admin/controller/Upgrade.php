<?php
/**
 * Index.php
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

use data\service\Upgrade as UpgradeService;
use data\extend\upgrade\Upgrade as UpgradeExtend;
use data\service\DbQuery;

/**
 * 升级
 */
class Upgrade extends BaseController
{
	
	public $backup_code = 0;
	
	public $backup_message = "数据库备份成功!";
	
	public function onlineUpdate()
	{
		$upgrade = new UpgradeService();
		$back = request()->get('back', '');
		//查询授权信息
		$product_info = $upgrade->getVersionDevolution();
		$user_name = !empty($product_info[0]["devolution_username"]) ? $product_info[0]["devolution_username"] : "";
		$password = !empty($product_info[0]["devolution_password"]) ? $product_info[0]["devolution_password"] : "";
		$devolution_code = !empty($product_info[0]["devolution_code"]) ? $product_info[0]["devolution_code"] : "";
		
		// 绑定账号
		if (request()->isAjax()) {
			$authorization_code = request()->post("authorization_code", "");
			$result = $upgrade->getUserDevolutionByAuthorizationCode($authorization_code);
			$res = json_decode($result, true);
			
			if (!empty($res)) {
				$revel = json_decode(json_encode($res), true);
				if ($revel['code'] == 0) {
					$upgrade->addVersionDevolution($res["data"]["devolution_user_name"], $res["data"]["devolution_password"], $res["data"]["devolution_code"]);
				}
			} else {
				$revel = array(
					"code" => -1,
					"message" => "该授权码无效!"
				);
			}
			return $revel;
		}
		//服务器端版本
		$latestVersionRes = $upgrade->getLatestVersion();
		$latestVersion = json_decode($latestVersionRes, true);
		
		$child_menu_list = array(
			array(
				'url' => "upgrade/devolutioninfo",
				'menu_name' => "授权信息",
				"active" => 0
			),
			array(
				'url' => "upgrade/onlineupdate",
				'menu_name' => "在线更新",
				"active" => 1
			),
		
		);
		$this->assign('child_menu_list', $child_menu_list);
		
		$this->assign("latestVersion", $latestVersion);
		$this->assign('devolution_user_name', $user_name);
		$this->assign('devolution_password', $password);
		$this->assign('devolution_code', $devolution_code);
		
		$this->assign('niu_release', NIU_RELEASE);
		$this->assign('niu_version', NIU_VERSION);
		return view($this->style . "Upgrade/onlineUpdateList");
	}
	
	/**
	 * 授权信息
	 */
	public function devolutioninfo()
	{
		$upgrade = new UpgradeService();
		// 查询是否有授权信息
		$devolution_message = $upgrade->getVersionDevolution();
		$user_name = "";
		$password = "";
		$devolution_info = array(
			"code" => -1,
			"message" => "未授权"
		);
		if (!empty($devolution_message) && count($devolution_message) > 0) {
			$authorization_code = $devolution_message[0]['devolution_code'];
			$user_name = $devolution_message[0]['devolution_username'];
			$password = $devolution_message[0]['devolution_password'];
			$result = $upgrade->getUserDevolutionByAuthorizationCode($authorization_code);
			// $result = $upgrade->getUserDevolution($user_name, $password);
			$res = json_decode($result);
			$devolution_info = json_decode(json_encode($res), true);
			$this->assign('devolution_user_name', $user_name);
			$this->assign('devolution_code', $devolution_message[0]['devolution_code']);
		}
		
		$this->assign('result', $devolution_info);
		$this->assign('devolution_user_name', $user_name);
		$this->assign('devolution_password', $password);
		
		$child_menu_list = array(
			array(
				'url' => "upgrade/devolutioninfo",
				'menu_name' => "授权信息",
				"active" => 1
			),
			array(
				'url' => "upgrade/onlineupdate",
				'menu_name' => "在线更新",
				"active" => 0
			),
		
		);
		$this->assign('child_menu_list', $child_menu_list);
		
		//服务器端版本
		$latestVersionRes = $upgrade->getLatestVersion();
		$latestVersion = json_decode($latestVersionRes, true);
		$this->assign("latestVersion", $latestVersion);
		return view($this->style . "Upgrade/onlineUpdate");
	}
	
	/**
	 * 更新列表页面
	 */
	public function onlineUpdateList()
	{
		// 如果授权，进入更新页面
		$upgrade = new UpgradeService();
		if (request()->isAjax()) {
			
			$user_name = request()->post('user_name', '');
			$password = request()->post('password', '');
			$devolution_code = request()->post('devolution_code', '');
			$path_list = $upgrade->getVersionPatchList($user_name, $password, $devolution_code);
			
			$path_list = json_decode($path_list, true);
			
			foreach ($path_list['data'] as $key => $item) {
				if (count($path_list['data']) - 1 == $key) {
					
					$upgrade->updateVersionPatch($item, 1);
				} else {
					$upgrade->updateVersionPatch($item);
				}
			}
			
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$use_path_list = $upgrade->getProductPatchList($page_index, $page_size, '', 'patch_release desc');
			
			return $use_path_list;
		}
		return view($this->style . "Upgrade/onlineUpdateList");
	}
	
	/**
	 * 系统在线更新
	 * @return \think\response\View
	 */
	public function upgradePatch()
	{
		$upgrade_detail = request()->post("upgradePatch_detail", "");
		
		$upgrade_detail = json_decode($upgrade_detail, true);
		if ($upgrade_detail["from_version"] != NIU_VERSION) {
			$this->error("当前升级需要在版本v" . $upgrade_detail["from_version"] . "版本上升级，您当前版本不符合要求！");
		}
		
		if (empty($upgrade_detail)) {
			$this->error("更新信息数据异常, 请检查更新包信息！");
		}
		
		$dbquery = new DbQuery();
		$table_list = $dbquery->getDatabaseList();
		$table_name_list = array();
//         $table_name_list[]=$table_list[0]["Name"];
		foreach ($table_list as $table) {
			$table_name_list[] = $table["Name"];
		}
		$this->assign("tablenamelist", json_encode($table_name_list));
		$this->assign("upgradeDetail", $upgrade_detail);
		return view($this->style . "Upgrade/upgradeonline");
	}
	
	/**
	 * 判断当前升级的版本的，补丁编号是否是最小，并且未升级
	 * @return unknown
	 */
	public function getProductPatch()
	{
		if (request()->isAjax()) {
			$upgrade = new UpgradeService();
			// $patch_id = request()->post('patch_id', '');
			$patch_type = request()->post('patch_type', '');
			$is_up = request()->post('is_up', '');
			$patch_release = request()->post('patch_release', '');
			
			$revel = $upgrade->getProductPatch($patch_type, $is_up, $patch_release);
			return $revel;
		}
	}
	
	/**
	 * 升级补丁
	 */
	public function upVersionPatch()
	{
		if (request()->isAjax()) {
			$upgrade_type = request()->post('upgrade_type', '');
			if ($upgrade_type == 1) {
				// 一个一个升级
				$patch_release = request()->post('patch_release', '');
				// $devolution_code = request()->post('devolution_code', '');
				// $devolution_version = request()->post('devolution_version', '');
				$user_name = request()->post('user_name', '');
				$password = request()->post('password', '');
				$upgrade = new UpgradeExtend($patch_release, $user_name, $password);
				$revel = $upgrade->niushop_patch_upgrade();
				if ($revel['code'] == 0) {
					$upgrade = new UpgradeService();
					$upgrade->updateVersionPatchState($patch_release);
					
					
				}
				return $revel;
			} else {
				// 一键升级
				// $devolution_code = request()->post('devolution_code', '');
				$user_name = request()->post('user_name', '');
				$password = request()->post('password', '');
				$upgrade = new UpgradeService();
				$patch_list = $upgrade->getUpgradePatchList();
				$revel = array(
					"code" => 0,
					"message" => "升级成功"
				);
				if (count($patch_list) > 0) {
					foreach ($patch_list as $patch_obj) {
						$upgrade = new UpgradeExtend($patch_obj["patch_release"], $user_name, $password);
						$revel = $upgrade->niushop_patch_upgrade();
						if ($revel['code'] == 0) {
							$upgrade = new UpgradeService();
							$upgrade->updateVersionPatchState($patch_obj["patch_release"]);
						} else {
							return $revel;
						}
					}
					return $revel;
				} else {
					$revel["message"] = "当前没有可升级的补丁!";
					return $revel;
				}
			}
		}
	}
	
	/**
	 * 判断是否需要更新
	 */
	public function isNeedToUpgrade()
	{
		$upgrade = new UpgradeService();
		$res = $upgrade->devolutionUpdate();
		return json_decode($res, true);
	}
}