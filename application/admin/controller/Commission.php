<?php
/**
 * Commission.php
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

use addons\Nsfx\data\service\NfxPartner;
use addons\Nsfx\data\service\NfxPromoter;
use addons\Nsfx\data\service\NfxRegionAgent;
use addons\Nsfx\data\service\NfxUser;
use data\service\Config as WebConfig;
use data\service\Member;
use data\service\Shop;

/**
 * 佣金控制器
 */
class Commission extends BaseController
{
	
	/**
	 * 三级分销佣金列表
	 */
	public function commissionDistributionList()
	{
		if (request()->isAjax()) {
			$pageindex = isset($_POST['pageIndex']) ? $_POST['pageIndex'] : 1;
			$start_date = request()->post('start_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('start_date'));
			$end_date = request()->post('end_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('end_date'));
			$user_name = isset($_POST['user_name']) ? $_POST['user_name'] : '';
			$order_no = isset($_POST['order_no']) ? $_POST['order_no'] : '';
			$order_status = isset($_POST['order_status']) ? $_POST['order_status'] : '';
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
			if (!empty($order_status)) {
				$condition['order_status'] = $order_status;
			}
			if (!empty($user_name)) {
				$condition['user_name'] = $user_name;
			}
			if (!empty($order_no)) {
				$condition['out_trade_no'] = $order_no;
			}
			$condition['shop_id'] = $this->instance_id;
			$promoter = new NfxPromoter();
			$order_list = $promoter->getCommissionDistributionList($pageindex, PAGESIZE, $condition, 'create_time desc');
			return $order_list;
		} else {
			
			return view($this->style . "Commission/commissionDistributionList");
		}
	}
	
	/**
	 * 股东分红佣金列表
	 */
	public function commissionPartnerList()
	{
		if (request()->isAjax()) {
			$pageindex = isset($_POST['pageIndex']) ? $_POST['pageIndex'] : 1;
			$start_date = request()->post('start_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('start_date'));
			$end_date = request()->post('end_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('end_date'));
			$user_name = isset($_POST['user_name']) ? $_POST['user_name'] : '';
			$order_no = isset($_POST['order_no']) ? $_POST['order_no'] : '';
			$order_status = isset($_POST['order_status']) ? $_POST['order_status'] : '';
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
			if (!empty($order_status)) {
				$condition['order_status'] = $order_status;
			}
			if (!empty($user_name)) {
				$condition['user_name'] = $user_name;
			}
			if (!empty($order_no)) {
				$condition['out_trade_no'] = $order_no;
			}
			$condition['shop_id'] = $this->instance_id;
			$partner = new NfxPartner();
			$order_list = $partner->getCommissionPartnerList($pageindex, PAGESIZE, $condition, 'create_time desc');
			return $order_list;
		} else {
			return view($this->style . "Commission/commissionPartnerList");
		}
	}
	
	/**
	 * 区域代理佣金
	 */
	public function commissionRegionAgentList()
	{
		if (request()->isAjax()) {
			
			$pageindex = isset($_POST['pageIndex']) ? $_POST['pageIndex'] : 1;
			$start_date = request()->post('start_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('start_date'));
			$end_date = request()->post('end_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('end_date'));
			$user_name = isset($_POST['user_name']) ? $_POST['user_name'] : '';
			$order_no = isset($_POST['order_no']) ? $_POST['order_no'] : '';
			$order_status = isset($_POST['order_status']) ? $_POST['order_status'] : '';
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
			if (!empty($order_status)) {
				$condition['order_status'] = $order_status;
			}
			if (!empty($user_name)) {
				$condition['user_name'] = $user_name;
			}
			if (!empty($order_no)) {
				$condition['out_trade_no'] = $order_no;
			}
			$condition['shop_id'] = $this->instance_id;
			$region_agent = new NfxRegionAgent();
			$order_list = $region_agent->getCommissionRegionAgentList($pageindex, PAGESIZE, $condition, 'create_time desc');
			return $order_list;
		} else {
			return view($this->style . "Commission/commissionRegionAgentList");
		}
	}
	
	/**
	 * 全球分红发放列表
	 */
	public function commissionPartnerGlobalList()
	{
		if (request()->isAjax()) {
			$partner = new NfxPartner();
			$pageindex = isset($_POST['pageIndex']) ? $_POST['pageIndex'] : '';
			$records_id = request()->post("records_id", 0);
			if ($records_id != 0) {
				$condition["records_id"] = $records_id;
			}
			$user_name = request()->post("user_name", '');
			$user_phone = request()->post("user_phone", '');
			$where = array();
			if ($user_name != "") {
				$where["user_name"] = array(
					"like",
					"%" . $user_name . "%"
				);
			}
			if ($user_phone != "") {
				$where["user_tel"] = $user_phone;
			}
			if (!empty($where)) {
				$uid_string = $this->getUserUids($where);
				if ($uid_string != "") {
					$condition["uid"] = array(
						"in",
						$uid_string
					);
				} else {
					$condition["uid"] = 0;
				}
			}
			$condition["shop_id"] = $this->instance_id;
			$list = $partner->getCommissionPartnerGlobalList($pageindex, PAGESIZE, $condition, '');
			return $list;
		} else {
			$records_id = isset($_GET["records_id"]) ? $_GET["records_id"] : 0;
			$this->assign("records_id", $records_id);
			return view($this->style . "Commission/commissionPartnerGlobalList");
		}
	}
	
	/**
	 * 分销商佣金列表
	 *
	 * @return Ambigous <multitype:number unknown , unknown>|Ambigous <\think\response\View, \think\response\$this, \think\response\View>
	 */
	public function userAccountList()
	{
		if (request()->isAjax()) {
			$user = new NfxUser();
			$pageindex = isset($_POST['pageIndex']) ? $_POST['pageIndex'] : '';
			$condition["shop_id"] = $this->instance_id;
			$where = array();
			if ($_POST["role"] == 1) {
				$search_string = $this->getPromoterUids(array(
					"shop_id" => $this->instance_id,
					"is_audit" => 1
				));
				if ($search_string != "") {
					$where["uid"] = [
						"in",
						$search_string
					];
				}
			} else
				if ($_POST["role"] == 2) {
					$search_string = $this->getPartnerUids(array(
						"shop_id" => $this->instance_id,
						"is_audit" => 1
					));
					if ($search_string != "") {
						$where["uid"] = [
							"in",
							$search_string
						];
					}
				} else
					if ($_POST["role"] == 3) {
						$search_string = $this->getPromoterRegionAgentUids(array(
							"shop_id" => $this->instance_id,
							"is_audit" => 1
						));
						if ($search_string != "") {
							$where["uid"] = [
								"in",
								$search_string
							];
						}
					}
			if ($_POST['user_name'] != "") {
				$where["user_name"] = array(
					"like",
					"%" . $_POST['user_name'] . "%"
				);
			}
			if ($_POST['user_phone'] != "") {
				$where["user_tel"] = $_POST['user_phone'];
			}
			if (!empty($where)) {
				$uid_string = $this->getUserUids($where);
				if ($uid_string != "") {
					$condition["uid"] = array(
						"in",
						$uid_string
					);
				} else {
					$condition["uid"] = 0;
				}
			}
			$list = $user->getShopUserAccountList($pageindex, PAGESIZE, $condition, '');
			return $list;
		} else {
			return view($this->style . "Commission/userAccountList");
		}
	}
	
	/**
	 * 会员提现列表
	 */
	public function userCommissionWithdrawList()
	{
		if (request()->isAjax()) {
			$user = new NfxUser();
			$pageindex = isset($_POST['pageIndex']) ? $_POST['pageIndex'] : '';
			$user_phone = isset($_POST['user_phone']) ? $_POST['user_phone'] : '';
			if ($user_phone != "") {
				$condition["mobile"] = $_POST['user_phone'];
			}
			$where = array();
			if ($_POST['user_name'] != "") {
				$where["user_name"] = array(
					"like",
					"%" . $_POST['user_name'] . "%"
				);
			}
			if (!empty($where)) {
				$uid_string = $this->getUserUids($where);
				if ($uid_string != "") {
					$condition["uid"] = array(
						"in",
						$uid_string
					);
				} else {
					$condition["uid"] = 0;
				}
			}
			$condition["shop_id"] = $this->instance_id;
			$list = $user->getUserCommissionWithdraw($pageindex, PAGESIZE, $condition, 'ask_for_date desc');
			return $list;
		} else {
			
			$config_service = new WebConfig();
			$data1 = $config_service->getTransferAccountsSetting($this->instance_id, 'wechat');
			$data2 = $config_service->getTransferAccountsSetting($this->instance_id, 'alipay');
			if (!empty($data1)) {
				$wechat = json_decode($data1['value'], true);
			}
			if (!empty($data2)) {
				$alipay = json_decode($data2['value'], true);
			}
			$this->assign("wechat", $wechat);
			$this->assign("alipay", $alipay);
			return view($this->style . "Commission/userCommissionWithdrawList");
		}
	}
	
	/**
	 * 用户提现审核
	 */
	public function userCommissionWithdrawAudit()
	{
		$id = request()->post('id', '');
		$status = request()->post('status', '');
		$transfer_type = request()->post('transfer_type', '');
		$transfer_name = request()->post('transfer_name', '');
		$transfer_money = request()->post('transfer_money', '');
		$transfer_remark = request()->post('transfer_remark', '');
		$transfer_no = request()->post('transfer_no', '');
		$transfer_account_no = request()->post('transfer_account_no', '');
		$type_id = request()->post('type_id', '');
		
		$user = new NfxUser();
		$retval = $user->UserCommissionWithdrawAudit($this->instance_id, $id, $status, $transfer_type, $transfer_name, $transfer_money, $transfer_remark, $transfer_no, $transfer_account_no, $type_id);
		return $retval;
	}
	
	/**
	 * 拒绝提现请求
	 */
	public function userCommissionWithdrawRefuse()
	{
		$id = request()->post('id', '');
		$status = request()->post('status', '');
		$remark = request()->post('remark', '');
		
		$user = new NfxUser();
		$retval = $user->userCommissionWithdrawRefuse($this->instance_id, $id, $status, $remark);
		return AjaxReturn($retval);
	}
	
	/**
	 * 获取提现详情
	 *
	 * @return unknown
	 */
	public function getWithdrawalsInfo()
	{
		$id = request()->post('id', '');
		$user = new NfxUser();
		$retval = $user->getMemberWithdrawalsDetails($id);
		return $retval;
	}
	
	/**
	 * 查寻符合条件的数据并返回id （多个以“,”隔开）
	 */
	public function getUserUids($condition)
	{
		$member = new Member();
		$list = $member->getMemberAll($condition);
		$uid_string = "";
		foreach ($list as $k => $v) {
			$uid_string = $uid_string . "," . $v["uid"];
		}
		if ($uid_string != "") {
			$uid_string = substr($uid_string, 1);
		}
		return $uid_string;
	}
	
	/**
	 * 查询 股东 返回id 已,隔开
	 */
	public function getPartnerUids($condition)
	{
		$partner = new NfxPartner();
		$list = $partner->getPartnerAll($condition);
		$uid_string = "";
		foreach ($list as $k => $v) {
			$uid_string = $uid_string . "," . $v["uid"];
		}
		if ($uid_string != "") {
			$uid_string = substr($uid_string, 1);
		}
		return $uid_string;
	}
	
	/**
	 * 查询 分销商 返回id 已,隔开
	 */
	public function getPromoterUids($condition)
	{
		$promoter = new NfxPromoter();
		$list = $promoter->getPromoterAll($condition);
		$uid_string = "";
		foreach ($list as $k => $v) {
			$uid_string = $uid_string . "," . $v["uid"];
		}
		if ($uid_string != "") {
			$uid_string = substr($uid_string, 1);
		}
		return $uid_string;
	}
	
	/**
	 * 查询 代理 返回id 已,隔开
	 */
	public function getPromoterRegionAgentUids($condition)
	{
		$region_agent = new NfxRegionAgent();
		$list = $region_agent->getPromoterRegionAgentAll($condition);
		$uid_string = "";
		foreach ($list as $k => $v) {
			$uid_string = $uid_string . "," . $v["uid"];
		}
		if ($uid_string != "") {
			$uid_string = substr($uid_string, 1);
		}
		return $uid_string;
	}
	
	/**
	 * 会员提现设置
	 */
	public function memberWithdrawSetting()
	{
		$shop = new Shop();
		if (request()->isAjax()) {
			$id = request()->post("id", '');
			if ($id == '') {
				$withdraw_cash_min = isset($_POST['cash_min']) ? $_POST['cash_min'] : '';
				$withdraw_multiple = isset($_POST['multiple']) ? $_POST['multiple'] : '';
				$withdraw_poundage = isset($_POST['poundage']) ? $_POST['poundage'] : '';
				$withdraw_message = isset($_POST['message']) ? $_POST['message'] : '';
				$data = array(
					"shop_id" => 0,
					"withdraw_cash_min" => $withdraw_cash_min,
					"withdraw_multiple" => $withdraw_multiple,
					"withdraw_poundage" => $withdraw_poundage,
					"withdraw_message" => $withdraw_message,
					"withdraw_account_type" => "银联卡",
					"create_time" => time()
				);
				$list = $shop->addMemberWithdrawSetting($data);
				return $list;
			} else {
				$withdraw_cash_min = isset($_POST['cash_min']) ? $_POST['cash_min'] : '';
				$withdraw_multiple = isset($_POST['multiple']) ? $_POST['multiple'] : '';
				$withdraw_poundage = isset($_POST['poundage']) ? $_POST['poundage'] : '';
				$withdraw_message = isset($_POST['message']) ? $_POST['message'] : '';
				
				$data = array(
					"withdraw_cash_min" => $withdraw_cash_min,
					"withdraw_multiple" => $withdraw_multiple,
					"withdraw_poundage" => $withdraw_poundage,
					"withdraw_message" => $withdraw_message,
					"withdraw_account_type" => "银联卡",
					"modify_time" => time(),
					"id" => $id
				);
				$list = $shop->updateMemberWithdrawSetting($data);
				return $list;
			}
		} else {
			$list = $shop->getWithdrawInfo();
			if (empty($list)) {
				$list['id'] = '';
				$list['withdraw_cash_min'] = '';
				$list['withdraw_multiple'] = '';
				$list['withdraw_poundage'] = '';
				$list['withdraw_message'] = '';
			}
			$this->assign("list", $list);
			return view($this->style . "Commission/memberWithdrawSetting");
		}
		
	}
	
	/**
	 * 具体项的佣金明细
	 */
	public function userAccountRecordsDetail()
	{
		$nfx_user = new NfxUser();
		if (request()->isAjax()) {
			$pageindex = request()->post('pageIndex', '');
			$condition['shop_id'] = $this->instance_id;
			$uid = request()->post('uid', '');
			if ($uid != "") {
				$condition['uid'] = $uid;
			}
			$type_id = request()->post('type_id', '');
			if (!empty($type_id)) {
				$condition['account_type'] = $type_id;
			}
			$account_records_detail = $nfx_user->getPcNfxUserAccountRecordsList($pageindex, PAGESIZE, $condition, 'create_time desc');
			return $account_records_detail;
		} else {
			$type_id = request()->get('type_id', 1);
			$uid = request()->get('uid', 0);
			switch ($type_id) {
				case 1:
					$type_name = '分销佣金';
					$view = 'Commission/userAccountRecordsDetail';
					break;
				case 2:
					$type_name = '代理佣金';
					$view = 'Commission/userRegionAgentDetail';
					break;
				case 4:
					$type_name = '股东分红';
					$view = 'Commission/userPartnerDetail';
					break;
				case 5:
					$type_name = '全球分红';
					$view = 'Commission/userPartnerGlobalDetail';
					break;
			}
			
			$this->assign('type_name', $type_name);
			$this->assign('type_id', $type_id);
			$this->assign('uid', $uid);
			
			return view($this->style . $view);
			
		}
		
	}
	
	/**
	 * 分销商账户详情
	 */
	public function promoterAccount()
	{
		
		$nfx_user = new NfxUser();
		if (request()->isAjax()) {
			$page_index = request()->post('page_index', '');
			$page_size = request()->post("page_size", PAGESIZE);
			
			$startDate = request()->post('startDate', 0);
			$endDate = request()->post('endDate', 0);
			$account_type = request()->post('account_type', '');
			
			$condition['shop_id'] = $this->instance_id;
			$uid = request()->post('uid', '');
			if (!empty($uid)) {
				$condition['uid'] = $uid;
			}
			
			$endDate = empty($endDate) ? time() : getTimeTurnTimeStamp($endDate);
			$startDate = empty($startDate) ? 0 : getTimeTurnTimeStamp($startDate);
			if (!empty($startDate) && !empty($endDate)) {
				
				$condition['create_time'] = array(
					'between time',
					array( $startDate, $endDate )
				);
			}
			
			if (!empty($account_type)) {
				$condition['account_type'] = $account_type;
			}
			
			$account_records_detail = $nfx_user->getPcNfxUserAccountRecordsList($page_index, $page_size, $condition, 'create_time desc');
			return $account_records_detail;
		}
		
		$uid = request()->get('uid', '');
		$this->assign('promoter_uid', $uid);
		
		//分销商基本信息
		$promoter = new NfxPromoter();
		$condition = array(
			'uid' => $uid
		);
		$promoter_info = $promoter->getPromoterList(1, 1, $condition, '')['data'][0];
		$this->assign('promoter_info', $promoter_info);
		
		//账号信息
		$user_account_info = $nfx_user->getShopUserAccountList(1, 1, $condition, '')['data'][0];
		$this->assign('user_account_info', $user_account_info);
		
		//分销账号类型列表
		$account_type = $nfx_user->getUserAccountTypeList();
		$this->assign('account_type', $account_type);
		return view($this->style . "Distribution/promoterAccount");
	}
}