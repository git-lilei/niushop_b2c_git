<?php
/**
 * MemberAccount.php
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
 * @date : 2015.1.17
 * @version : v1.0.0.0
 */

namespace data\service\Member;

/**
 * 会员流水账户
 */
use data\model\ConfigModel;
use data\model\NsMemberAccountModel;
use data\model\NsMemberAccountRecordsModel;
use data\model\NsPointConfigModel;
use data\service\BaseService;
use data\service\Member;
use data\service\User;
use think\Log;

class MemberAccount extends BaseService
{
	/**
	 * 添加会员消费
	 */
	public function addMemberConsum($shop_id, $uid, $consum)
	{
		$account_statistics = new NsMemberAccountModel();
		$acount_info = $account_statistics->getInfo([
			'uid' => $uid,
			'shop_id' => $shop_id
		], '*');
		if (empty($acount_info)) {
			$insert_data = array(
				'uid' => $uid,
				'shop_id' => $shop_id
			);
			$account_statistics->save($insert_data);
			$acount_info = $account_statistics->getInfo([
				'uid' => $uid,
				'shop_id' => $shop_id
			], '*');
		}
		$data = array(
			'member_cunsum' => $acount_info['member_cunsum'] + $consum
		);
		$retval = $account_statistics->save($data, [
			'uid' => $uid,
			'shop_id' => $shop_id
		]);
		try {
			$user_service = new Member();
			$user_service->checkMemberLevel($shop_id, $uid);
		} catch (\Exception $e) {
			Log::write($e->getMessage());
		}
		return $retval;
	}
	
	/**
	 * 添加账户流水
	 */
	public function addMemberAccountData($shop_id, $account_type, $uid, $sign, $number, $from_type, $data_id, $text)
	{
		if (empty($uid)) {
			return 1;
		}
		if ($number == 0) {
			return 1;
		}
		
		$member_account = new NsMemberAccountRecordsModel();
		$member_account->startTrans();
		try {
			// 前期检测
			$account_statistics = new NsMemberAccountModel();
			$all_info = $account_statistics->getInfo([
				'uid' => $uid,
				'shop_id' => $shop_id
			], '*');
			if (empty($all_info)) {
				$member_all_point = 0;
			} else {
				$member_all_point = $all_info['point'];
			}
			if (empty($all_info)) {
				$member_all_balance = 0;
			} else {
				$member_all_balance = $all_info['balance'];
			}
			if ($number < 0) {
				
				if ($account_type == 1) {
					
					if ($number + $member_all_point < 0) {
						return LOW_POINT;
					}
				} elseif ($account_type == 2) {
					
					if ($number + $member_all_balance < 0) {
						return LOW_BALANCE;
					}
				}
			}
			$data = array(
				'shop_id' => $shop_id,
				'account_type' => $account_type,
				'uid' => $uid,
				'sign' => $sign,
				'number' => $number,
				'from_type' => $from_type,
				'data_id' => $data_id,
				'text' => $text,
				'create_time' => time()
			);
			$member_account->save($data);
			// 更新对应会员账户
			if ($account_type == 1) {
				$count = $account_statistics->getCount([
					'uid' => $uid,
					'shop_id' => $shop_id
				]);
				if ($count == 0) {
					$account_statistics = new NsMemberAccountModel();
					$data = array(
						'uid' => $uid,
						'shop_id' => $shop_id,
						'point' => $number,
						'member_sum_point' => $number
					);
					$account_statistics->save($data);
				} else {
					$data_member = array(
						'point' => $member_all_point + $number
					);
					if ($number > 0) {
						// 计算会员累计积分
						$data_member['member_sum_point'] = $member_all_point + $number;
					}
					$account_statistics->save($data_member, [
						'uid' => $uid,
						'shop_id' => $shop_id
					]);
				}
				try {
					$user_service = new Member();
					$user_service->checkMemberLevel($shop_id, $uid);
				} catch (\Exception $e) {
					Log::write($e->getMessage());
				}
			}
			if ($account_type == 2) {
				
				$count = $account_statistics->where([
					'uid' => $uid,
					'shop_id' => 0
				])->count();
				if ($count == 0) {
					$data = array(
						'uid' => $uid,
						'shop_id' => 0,
						'balance' => $number
					);
					$account_statistics->save($data);
				} else {
					$data_member = array(
						'balance' => $member_all_balance + $number
					);
					$account_statistics->save($data_member, [
						'uid' => $uid,
						'shop_id' => 0
					]);
				}
			}
			$member_account->commit();
			return 1;
		} catch (\Exception $e) {
			$member_account->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 获取会员在一段时间之内的账户
	 */
	public function getMemberAccount($shop_id, $uid, $account_type, $start_time = '', $end_time = '')
	{
		$start_time = ($start_time == '') ? '2010-1-1' : $start_time;
		$end_time = ($end_time == '') ? 'now' : $end_time;
		$condition = array(
			'create_time' => array(
				'EGT',
				getTimeTurnTimeStamp($start_time)
			),
			'create_time' => array(
				'LT',
				getTimeTurnTimeStamp($end_time)
			),
			'account_type' => $account_type,
			'uid' => $uid,
			'shop_id' => $shop_id
		);
		$member_account = new NsMemberAccountRecordsModel();
		$account = $member_account->where($condition)->sum('number');
		if (empty($account)) {
			$account = 0;
		}
		return $account;
	}
	
	/**
	 * 获取在一段时间之内用户的账户流水
	 */
	public function getMemberAccountList($shop_id, $uid, $account_type, $start_time, $end_time)
	{
		$start_time = ($start_time == '') ? '2010-1-1' : $start_time;
		$end_time = ($end_time == '') ? 'now' : $end_time;
		$condition = array(
			'create_time' => array(
				'EGT',
				$start_time
			),
			'create_time' => array(
				'LT',
				$end_time
			),
			'account_type' => $account_type,
			'uid' => $uid,
			'shop_id' => $shop_id
		);
		$member_account = new NsMemberAccountRecordsModel();
		$list = $member_account->getQuery($condition, '*', 'create_time desc');
		return $list;
	}
	
	/**
	 * 积分转换成余额
	 *
	 * @param unknown $point
	 *            积分
	 * @param unknown $convert_rate
	 *            积分/余额
	 */
	public function pointToBalance($point, $shop_id)
	{
		$point_config = new NsPointConfigModel();
		$convert_rate = $point_config->getInfo([
			'shop_id' => $shop_id,
			'is_open' => 1
		], 'convert_rate');
		if (!$convert_rate || $convert_rate == '') {
			$convert_rate = 0;
		}
		$balance = $point * $convert_rate["convert_rate"];
		return $balance;
	}
	
	/**
	 * 获取兑换比例
	 */
	public function getConvertRate($shop_id)
	{
		$point_config = new NsPointConfigModel();
		$convert_rate = $point_config->getInfo([
			'shop_id' => $shop_id,
			'is_open' => 1
		], 'convert_rate');
		return $convert_rate;
	}
	
	/**
	 * 获取购物币余额转化关系
	 */
	public function getCoinConvertRate()
	{
		$config = new ConfigModel();
		$config_rate = $config->getInfo([
			'key' => 'COIN_CONFIG'
		], '*');
		if (empty($config_rate)) {
			return 1;
		} else {
			$rate = json_decode($config_rate['value'], true);
			return $rate['convert_rate'];
		}
	}
	
	/**
	 * 获取会员余额数
	 */
	public function getMemberBalance($uid)
	{
		$member_account = new NsMemberAccountModel();
		$balance = $member_account->getInfo([
			'uid' => $uid,
			'shop_id' => 0
		], 'balance');
		if (!empty($balance)) {
			return $balance['balance'];
		} else {
			return 0;
		}
	}
	
	/**
	 * 获取会员购物币
	 */
	public function getMemberCoin($uid)
	{
		$member_account = new NsMemberAccountModel();
		$coin = $member_account->getInfo([
			'uid' => $uid,
			'shop_id' => 0
		], 'coin');
		if (!empty($coin)) {
			return $coin['coin'];
		} else {
			return 0;
		}
	}
	
	public function getMemberPoint($uid, $shop_id = '')
	{
		$member_account = new NsMemberAccountModel();
		if ($shop_id == '') {
			// 查询全部积分
			$point = $member_account->where([
				'uid' => $uid
			])->sum('point');
			if (!empty($point)) {
				return $point;
			} else {
				return 0;
			}
		} else {
			$point = $member_account->getInfo([
				'uid' => $uid,
				'shop_id' => $shop_id
			], 'point');
			if (!empty($point)) {
				return $point['point'];
			} else {
				return 0;
			}
		}
	}
	
	/**
	 * 获取会员账户所有产生方式名称
	 */
	public static function getMemberAccountRecordsNameList()
	{
		$list = [
			[ 'type_id' => 1, 'type_name' => '商城订单' ],
			[ 'type_id' => 2, 'type_name' => '订单退还' ],
			[ 'type_id' => 3, 'type_name' => '兑换' ],
			[ 'type_id' => 4, 'type_name' => '充值' ],
			[ 'type_id' => 5, 'type_name' => '签到' ],
			[ 'type_id' => 6, 'type_name' => '分享' ],
			[ 'type_id' => 7, 'type_name' => '注册' ],
			[ 'type_id' => 8, 'type_name' => '提现' ],
			[ 'type_id' => 9, 'type_name' => '提现退还' ],
			[ 'type_id' => 10, 'type_name' => '调整' ],
			[ 'type_id' => 11, 'type_name' => '参与营销游戏消耗积分' ],
			[ 'type_id' => 19, 'type_name' => '点赞' ],
			[ 'type_id' => 20, 'type_name' => '评论' ],
		
		];
		return $list;
	}
	
	/**
	 * 获取会员账户产生方式名称
	 */
	public static function getMemberAccountRecordsName($from_type)
	{
		switch ($from_type) {
			
			case 1:
				$type_name = '商城订单';
				break;
			case 2:
				$type_name = '订单退还';
				break;
			case 3:
				$type_name = '兑换';
				break;
			case 4:
				$type_name = '充值';
				break;
			case 5:
				$type_name = '签到';
				break;
			
			case 6:
				$type_name = '分享';
				break;
			case 7:
				$type_name = '注册';
				break;
			case 8:
				$type_name = '提现';
				break;
			case 9:
				$type_name = '提现退还';
				break;
			case 10:
				$type_name = '调整';
				break;
			case 11:
				$type_name = '参与营销游戏';
				break;
			case 19:
				$type_name = '点赞';
				break;
			case 20:
				$type_name = '评论';
				break;
			default:
				$type_name = '';
				break;
		}
		return $type_name;
	}
	
	/**
	 * 获取会员账户所有记录类型名称
	 */
	public static function getMemberAccountRecordsTypeNameList()
	{
		$list = [
			[ 'type_id' => 1, 'type_name' => '积分' ],
			[ 'type_id' => 2, 'type_name' => '余额' ],
//             ['type_id'=>3,'type_name'=>'购物币'],
		];
		return $list;
	}
	
	/**
	 * 获取会员账户记录类型名称
	 */
	public static function getMemberAccountRecordsTypeName($account_type)
	{
		switch ($account_type) {
			
			case 1:
				$type_name = '积分';
				break;
			case 2:
				$type_name = '余额';
				break;
			case 3:
				$type_name = '购物币';
				break;
			default:
				$type_name = '';
				break;
		}
		return $type_name;
		
	}
	
	/**
	 * 获取会员账户
	 */
	public function getMemberAccountInfo($uid)
	{
		$member_account_model = new NsMemberAccountModel();
		$info = $member_account_model->getInfo([ "uid" => $uid ], "*");
		return $info;
	}
}