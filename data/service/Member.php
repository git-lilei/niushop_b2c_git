<?php
/**
 * Member.php
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

namespace data\service;

use addons\Nsfx\data\service\NfxPromoter;
use data\model\AlbumPictureModel;
use data\model\NsCouponModel;
use data\model\NsCouponTypeModel;
use data\model\NsGoodsBrowseModel;
use data\model\NsGoodsModel;
use data\model\NsMemberAccountModel;
use data\model\NsMemberAccountRecordsModel;
use data\model\NsMemberAccountRecordsViewModel;
use data\model\NsMemberBalanceWithdrawModel;
use data\model\NsMemberBankAccountModel;
use data\model\NsMemberBehaviorRecordsModel;
use data\model\NsMemberExpressAddressModel;
use data\model\NsMemberFavoritesModel;
use data\model\NsMemberLabelModel;
use data\model\NsMemberLevelModel;
use data\model\NsMemberLevelRecordsModel;
use data\model\NsMemberModel;
use data\model\NsMemberRechargeModel;
use data\model\NsMemberViewModel;
use data\model\NsOrderModel;
use data\model\NsOrderPaymentModel;
use data\model\NsPointConfigModel;
use data\model\NsShopApplyModel;
use data\model\UserModel;
use data\service\Member\MemberAccount;
use data\service\Member\MemberCoupon;
use data\service\User as User;
use think\Cookie;
use think\Session;

class Member extends User
{
	
	/**
	 *注册会员
	 */
	public function registerMember($user_name, $password, $email, $mobile, $user_qq_id, $qq_info, $wx_openid, $wx_info, $wx_unionid = "")
	{
		if (!empty($user_qq_id) || !empty($wx_openid) || !empty($wx_unionid)) {
			$is_false = true;
		} else {
			//数据验证
			if (trim($user_name) != "") {
				$error_info = $this->verifyValue($user_name, $password, "plain");
				$is_false = $error_info[0];
			} else {
				if ($mobile != "" && $email == "") {
					if ($user_name == "") {
						$user_name = $mobile;
						$error_info = $this->verifyValue($user_name, $password, "mobile");
						$is_false = $error_info[0];
					}
				} else {
					$error_info = $this->verifyValue($user_name, $password, "email");
					$is_false = $error_info[0];
				}
			}
		}
		if (!$is_false) {
			return $error_info[1];
		}
		$res = parent::add($user_name, $password, $email, $mobile, 0, $user_qq_id, $qq_info, $wx_openid, $wx_info, $wx_unionid, 1);
		if ($res > 0) {
			// 获取默认会员等级id
			$member_level = new NsMemberLevelModel();
			$level_info = $member_level->getInfo([
				'is_default' => 1
			], 'level_id');
			$member_level = $level_info['level_id'];
			$member = new NsMemberModel();
			$user_info_obj = parent::getUserInfoByUid($res);
			$data = array(
				'uid' => $res,
				'member_name' => $user_info_obj["nick_name"],
				'member_level' => $member_level,
				'reg_time' => time()
			);
			$member->save($data);
			$data['shop_id'] = $this->instance_id;
			
			// 会员行为——注册会员送积分、优惠券
			$this->memberAction([ 'type' => 'NsMemberRegister', 'uid' => $res ]);
			
			// 注册成功后短信与邮箱提醒
			$params['shop_id'] = $this->instance_id;
			$params['user_id'] = $res;
//			runhook('Notify', 'registAfter', $params);
            message('after_register', $params);
			// 直接登录
			if (!empty($user_name)) {
				$this->bindWechatInfo($res);
				$this->login($user_name, $password);
			} elseif (!empty($mobile)) {
				$this->bindWechatInfo($res);
				$this->login($mobile, $password);
			} elseif (!empty($email)) {
				$this->login($email, $password);
			} elseif (!empty($user_qq_id)) {
				$this->bindWechatInfo($res);
				$this->qqLogin($user_qq_id);
			} elseif (!empty($wx_openid)) {
				$this->wchatLogin($wx_openid);
			} elseif (!empty($wx_unionid)) {
				$this->wchatUnionLogin($wx_unionid);
			}
			
			hook('memberRegisterSuccess', $data);
		}
		return $res;
	}
	
	/**
	 * 注册会员验证
	 */
	private function verifyValue($user_name, $password, $reg_type = "plain")
	{
		$config = new Config();
		$reg_config = $config->getRegisterAndVisitInfo($this->instance_id);// 验证注册
		
		if ($reg_config["is_register"] != 1) {
			return array(
				false,
				REGISTER_CONFIG_OFF
			);
		}
		if ($reg_type == "mobile") {
			if (stristr($reg_config["register_info"], "mobile") === false) {
				return array(
					false,
					REGISTER_MOBILE_CONFIG_OFF
				);
			}
		} else if ($reg_type == "email") {
			if (stristr($reg_config["register_info"], "email") === false) {
				return array(
					false,
					REGISTER_EMAIL_CONFIG_OFF
				);
			}
		} else {
			if (stristr($reg_config["register_info"], "plain") === false) {
				return array(
					false,
					REGISTER_PLAIN_CONFIG_OFF
				);
			}
			if (trim($user_name) == "") {
				return array(
					false,
					REGISTER_USERNAME_ERROR
				);
			}
			
			if (preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $user_name)) {
				return array(
					false,
					REGISTER_USERNAME_ERROR
				);
			}
			$usernme_verify_array = array();
			if (trim($reg_config["name_keyword"]) != "") {
				$usernme_verify_array = explode(",", $reg_config["name_keyword"]);
			}
			$usernme_verify_array[] = ",";
			foreach ($usernme_verify_array as $k => $v) {
				if (trim($v) != "") {
					if (stristr($user_name, $v) !== false) {
						return array(
							false,
							REGISTER_USERNAME_ERROR
						);
					}
				}
			}
		}
		// 密码最小长度
		$min_length = $reg_config['pwd_len'];
		$password_len = strlen(trim($password));
		if ($password_len == 0) {
			return array(
				false,
				REGISTER_PASSWORD_ERROR
			);
		}
		if ($min_length > $password_len) {
			return array(
				false,
				REGISTER_PASSWORD_ERROR
			);
		}
		if (preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $password)) {
			return array(
				false,
				REGISTER_PASSWORD_ERROR
			);
		}
		// 验证密码内容
		if (trim($reg_config['pwd_complexity']) != "") {
			if (stristr($reg_config['pwd_complexity'], "number") !== false) {
				if (!preg_match("/[0-9]/", $password)) {
					return array(
						false,
						REGISTER_PASSWORD_ERROR
					);
				}
			}
			if (stristr($reg_config['pwd_complexity'], "letter") !== false) {
				if (!preg_match("/[a-z]/", $password)) {
					return array(
						false,
						REGISTER_PASSWORD_ERROR
					);
				}
			}
			if (stristr($reg_config['pwd_complexity'], "upper_case") !== false) {
				if (!preg_match("/[A-Z]/", $password)) {
					return array(
						false,
						REGISTER_PASSWORD_ERROR
					);
				}
			}
			if (stristr($reg_config['pwd_complexity'], "symbol") !== false) {
				if (!preg_match("/[^A-Za-z0-9]/", $password)) {
					return array(
						false,
						REGISTER_PASSWORD_ERROR
					);
				}
			}
		}
		return array(
			true,
			''
		);
	}
	
	/**
	 * 添加前台会员（后台添加）
	 */
	public function addMember($data)
	{
		$res = parent::add($data['member_name'], $data['password'], $data['email'], $data['mobile'], 0, '', '', '', '', '', 1);
		if ($res > 0) {
			$member = new NsMemberModel();
			$data_member = array(
				'uid' => $res,
				'member_name' => $data['member_name'],
				'member_level' => $data['member_level'],
				'member_label' => $data['member_label'],
				'reg_time' => time()
			);
			$member->save($data_member);
			$user = new UserModel();
			$user->save([
				'user_status' => $data['status'],
				'sex' => $data['sex']
			], [
				'uid' => $res
			]);
			$data['shop_id'] = $this->instance_id;
			$data['uid'] = $res;
			hook('memberRegisterSuccess', $data);
			return $res;
		} else {
			return $res;
		}
	}
	
	/**
	 * 后台修改会员信息
	 */
	public function updateMemberByAdmin($data)
	{
		$retval = parent::updateUser($data['uid'], $data['user_name'], $data['email'], $data['sex'], $data['status'], $data['mobile'], $data['nick_name']);
		if ($retval < 0) {
			return $retval;
		} else {
			$member = new NsMemberModel();
			$member->save([
				'member_label' => $data['member_level']
			], [
				'uid' => $data['uid']
			]);
			$res = $this->updateMemberLevelAction([ "level_id" => $data['member_level'], "uid" => $data['uid'] ]);
			return $res;
		}
	}
	
	/**
	 * 通过用户id更新用户的昵称
	 */
	public function updateNickNameByUid($uid, $nickName)
	{
		$user = new UserModel();
		$result = $user->save([
			'nick_name' => $nickName,
			"current_login_time" => time()
		], [
			'uid' => $uid
		]);
		return $result;
	}
	
	/**
	 * 修改个人信息
	 */
	public function updateMemberInformation($user_name, $user_qq, $real_name, $sex, $birthday, $location, $user_headimg)
	{
		$useruser = new UserModel();
		$birthday = empty($birthday) ? '0000-00-00' : $birthday;
		$data = array(
			"nick_name" => $user_name,
			"user_qq" => $user_qq,
			"real_name" => $real_name,
			"sex" => $sex,
			"birthday" => getTimeTurnTimeStamp($birthday),
			"location" => $location
		);
		$data2 = array(
			"user_headimg" => $user_headimg
		);
		if ($user_headimg == "") {
			$result = $useruser->save($data, [
				'uid' => $this->uid
			]);
		} else {
			$result = $useruser->save($data2, [
				'uid' => $this->uid
			]);
		}
		return $result;
	}
	
	/**
	 * 修改会员标签
	 */
	public function updateMemberlebel($uid, $member_label)
	{
		$member_model = new NsMemberModel();
		$res = $member_model->save([
			'member_label' => $member_label
		], [ 'uid' => $uid ]);
		return $res;
	}
	
	/**
	 * 设置用户支付密码
	 */
	public function setUserPaymentPassword($uid, $payment_password)
	{
		$user = new UserModel();
		$retval = $user->save([
			'payment_password' => md5($payment_password),
			'is_set_payment_password' => 1
		], [
			'uid' => $uid
		]);
		return $retval;
	}
	
	/**
	 * 修改用户支付密码
	 */
	public function updateUserPaymentPassword($uid, $old_payment_password, $new_payment_password)
	{
		// 检测原密码是否正确
		$user = new UserModel();
		$res = $user->getInfo([
			"uid" => $uid,
			"payment_password" => md5($old_payment_password),
			"is_set_payment_password" => 1
		], "uid");
		// 修改支付密码
		if ($res['uid'] != '') {
			return $user->save([
				"payment_password" => md5($new_payment_password)
			], [
				"uid" => $uid
			]);
		} else {
			return -1;
		}
	}
	
	/**
	 * 删除会员
	 */
	public function deleteMember($uid)
	{
		$user = new UserModel();
		$user->startTrans();
		try {
			// 删除user信息
			$user->destroy($uid);
			$member = new NsMemberModel();
			// 删除member信息
			$member->destroy($uid);
			$member_account = new NsMemberAccountModel();
			// 删除会员账户信息
			$member_account->destroy([
				'uid' => array(
					'in',
					$uid
				)
			]);
			// 删除会员账户记录信息
			$member_account_records = new NsMemberAccountRecordsModel();
			$member_account_records->destroy([
				'uid' => array(
					'in',
					$uid
				)
			]);
			// 删除会员取现记录表
			$member_balance_withdraw = new NsMemberBalanceWithdrawModel();
			$member_balance_withdraw->destroy([
				'uid' => array(
					'in',
					$uid
				)
			]);
			// 删除会员银行账户表
			$member_bank_account = new NsMemberBankAccountModel();
			$member_bank_account->destroy([
				'uid' => array(
					'in',
					$uid
				)
			]);
			// 删除会员地址表
			$member_express_address = new NsMemberExpressAddressModel();
			$member_express_address->destroy([
				'uid' => array(
					'in',
					$uid
				)
			]);
			$user->commit();
			return 1;
		} catch (\Exception $e) {
			$user->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 获取会员基础信息
	 */
	public function getMemberInfo($condition = [], $field = '*')
	{
		if (empty($condition) && !empty($this->uid)) {
			$condition['uid'] = $this->uid;
		}
		// 获取当前会员基本信息
		$member = new NsMemberModel();
		$data = $member->getInfo($condition, $field);
		return $data;
	}
	
	/**
	 * 获取会员信息
	 */
	public function getMemberInfoByCondition($condition)
	{
		$res = null;
		if (!empty($condition)) {
			$member = new NsMemberModel();
			$res = $member->getInfo($condition);
		}
		return $res;
	}
	
	/**
	 * 获取会员详情
	 * $shop_id不传就为全部
	 */
	public function getMemberDetail($shop_id = '', $uid = '')
	{
		if (empty($uid)) {
			$uid = $this->uid;
		}
		// 获取基础信息
		if (!empty($uid)) {
			$member_info = $this->getMemberInfo([ 'uid' => $uid ]);
			if (empty($member_info)) {
				$member_info = array(
					'level_id' => 0
				);
			}
			// 获取user信息
			$user_info = $this->getUserDetail($uid);
			$member_info['user_info'] = $user_info;
			
			// 获取优惠券信息
			$member_account = new MemberAccount();
			$member_info['point'] = $member_account->getMemberPoint($uid, $shop_id);
			$member_info['balance'] = $member_account->getMemberBalance($uid);
			$member_info['coin'] = $member_account->getMemberCoin($uid);
			// 会员等级名称
			$member_level = new NsMemberLevelModel();
			$level_name = $member_level->getInfo([
				'level_id' => $member_info['member_level']
			], 'level_name');
			$member_info['level_name'] = $level_name['level_name'];
			// 会员标签
			$member_label = new NsMemberLabelModel();
			$label_name = $member_label->getInfo([ 'id' => $member_info['member_label'] ], 'label_name');
			$member_info['label_name'] = $label_name['label_name'];
			// 会员消费金额，消费次数，平均消费金额，最近消费时间
			$order = new NsOrderModel();
			$avg_order_money = 0;
			$order_money = $order->getSum([ 'buyer_id' => $user_info['uid'], 'pay_status' => 2, 'order_status' => [ [ 'neq', 4 ], [ 'neq', 5 ], [ 'neq', 0 ] ] ], 'pay_money');
			$order_count = $order->getCount([ 'buyer_id' => $user_info['uid'], 'pay_status' => 2, 'order_status' => [ [ 'neq', 4 ], [ 'neq', 5 ], [ 'neq', 0 ] ] ]);
			if ($order_count != 0) {
				$avg_order_money = sprintf("%.2f", ($order_money / $order_count));
			}
			$member_info['order_money'] = sprintf("%.2f", $order_money);
			$member_info['order_count'] = $order_count;
			$member_info['avg_order_money'] = $avg_order_money;
			
			// 找到最后一次成交的记录
			$last_order_info = $order->getFirstData([ 'buyer_id' => $user_info['uid'], 'pay_status' => 2, 'order_status' => [ [ 'neq', 4 ], [ 'neq', 5 ], [ 'neq', 0 ] ] ], 'order_id desc');
			if ($last_order_info) {
				$member_info['last_pay_time'] = $last_order_info['pay_time'];
			} else {
				$member_info['last_pay_time'] = 0;
			}
			
		} else {
			$member_info = '';
		}
		
		return $member_info;
	}
	
	/**
	 * 获取用户的手机号
	 */
	public function getUserTelephone()
	{
		if (!empty($this->uid)) {
			$user = new UserModel();
			$res = $user->getInfo([
				'uid' => $this->uid
			], 'user_tel');
			return $res['user_tel'];
		} else {
			return '';
		}
	}
	
	/**
	 * 获取会员头像
	 */
	public function getMemberImage($uid)
	{
		$user_model = new UserModel();
		$user_info = $user_model->getInfo([
			'uid' => $uid
		], '*');
		if (!empty($user_info['user_headimg'])) {
			$member_img = $user_info['user_headimg'];
		} elseif (!empty($user_info['qq_openid'])) {
			$qq_info_array = json_decode($user_info['qq_info'], true);
			$member_img = $qq_info_array['figureurl_qq_1'];
		} elseif (!empty($user_info['wx_openid'])) {
			$member_img = '0';
		} else {
			$member_img = '0';
		}
		return $member_img;
	}
	
	/**
	 * 获取一定条件下会员数据
	 */
	public function getMemberAll($condition)
	{
		$user = new UserModel();
		$user_data = $user->all($condition);
		return $user_data;
	}
	
	/**
	 * 获取一定条件下会员数量
	 */
	public function getMemberCount($condition)
	{
		$user = new UserModel();
		$user_sum = $user->where($condition)->count();
		return $user_sum;
	}
	
	/**
	 * 获取在一定时间之内的会员注册
	 */
	public function getMemberMonthCount($begin_date, $end_date)
	{
		// $begin = date('Y-m-01', strtotime(date("Y-m-d")));
		// $end = date('Y-m-d', strtotime("$begin +1 month -1 day"));
		$user = new UserModel();
		$condition["reg_time"] = [
			[
				">",
				$begin_date
			],
			[
				"<",
				$end_date
			]
		];
		// 一段时间内的注册用户
		$user_list = $user->all($condition);
		$begintime = strtotime($begin_date);
		$endtime = strtotime($end_date);
		
		$list = array();
		for ($start = $begintime; $start <= $endtime; $start += 24 * 3600) {
			$list[ date("Y-m-d", $start) ] = array();
			$user_num = 0;
			foreach ($user_list as $v) {
				if (date("Y-m-d", strtotime($v["reg_time"])) == date("Y-m-d", $start)) {
					$user_num = $user_num + 1;
				}
			}
			$list[ date("Y-m-d", $start) ] = $user_num;
		}
		return $list;
	}
	
	/**
	 * 获取会员登录后的初始化信息
	 */
	public function getMemberLoginInfo()
	{
		$this->init();
		if (!empty($this->uid)) {
			//生成token
//	         $config = new Config();
//	         $api_secure = $config->getApiSecureConfig();
			$token = array(
				'uid' => $this->uid,
				'request_time' => time()
			);
			$encode = $this->niuEncrypt(json_encode($token));
			//查库
			$data = [
				'code' => 1,
				'token' => $encode,
			];
			return $data;
			
		} else {
			return '';
		}
	}
	
	/**
	 * 查询会员的等级下是否有会员
	 */
	private function getMemberLevelUserCount($level_id)
	{
		$member_model = new NsMemberModel();
		$member_count = $member_model->getCount([
			'member_level' => $level_id
		]);
		return $member_count;
	}
	
	/**
	 * 查询会员的等级下是否有会员
	 */
	private function getMemberLabelUserCount($label_id)
	{
		$member_model = new NsMemberModel();
		$member_count = $member_model->getCount([
			'member_label' => $label_id
		]);
		return $member_count;
	}
	
	/**
	 * 判断用户名是否存在
	 */
	public function judgeUserNameIsExistence($user_name)
	{
		$user = new UserModel();
		$res = $user->getCount([
			"user_name" => $user_name
		]);
		if ($res > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 账号密码获取用户ID  --Applet
	 */
	public function getUidWidthApplet($user_name, $password)
	{
		$user = new UserModel();
		$res = $user->getInfo([
			"user_name" => $user_name,
			"user_password" => md5($password)
		], 'uid');
		if (!empty($res) && !empty($res['uid'])) {
			return $res['uid'];
		} else {
			return null;
		}
	}
	
	/**
	 * 会员列表
	 */
	public function getMemberList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '*')
	{
		$member_view = new NsMemberViewModel();
		$result = $member_view->getViewList($page_index, $page_size, $condition, $order);
		$member_account = new MemberAccount();
		foreach ($result['data'] as $k => $v) {
			$result['data'][ $k ]['point'] = $member_account->getMemberPoint($v['uid'], '');
			$result['data'][ $k ]['balance'] = $member_account->getMemberBalance($v['uid']);
			$result['data'][ $k ]['coin'] = $member_account->getMemberCoin($v['uid']);
			$result['data'][ $k ]['label_arr'] = $this->getMemberLabelList(1, 0, [ 'id' => [ 'in', $v['member_label'] ] ], '', 'label_name')['data'];
			
			if (NS_VERSION == NS_VER_B2C_FX) {
				$promoter = new NfxPromoter();
				$result['data'][ $k ]['member_promoter'] = $promoter->getShopMemberAssociation($v['uid']);
			}
		}
		return $result;
	}
	
	/**
	 * 用户退出
	 */
	public function Logout()
	{
		parent::Logout();
		$_SESSION['order_tag'] = ""; // 清空订单
	}
	
	/**
	 * 添加会员是否申请过该店铺
	 */
	public function getMemberIsApplyShop($uid)
	{
		if ($this->is_system == 1) {
			return 'is_system';
		} else {
			// 是否正在申请
			$shop_apply = new NsShopApplyModel();
			$apply = $shop_apply->get([
				'uid' => $uid
			]);
			if (!empty($apply)) {
				if ($apply['apply_state'] == -1) {
					// 已被拒绝
					return 'refuse_apply';
				} elseif ($apply['apply_state'] == 2) {
					// 已同意
					return 'is_system';
				} else {
					// 存在正在申请
					return 'is_apply';
				}
			} else {
				// 可以申请
				return 'apply';
			}
		}
	}
	
	/***********************************************************会员结束*********************************************************/
	
	
	/***********************************************************会员地址开始*********************************************************/
	
	/**
	 * 添加会员物流地址
	 */
	public function addMemberExpressAddress($data)
	{
		$express_address = new NsMemberExpressAddressModel();
		$express_address->save([
			'is_default' => 0
		], [
			'uid' => $this->uid
		]);
		$express_address = new NsMemberExpressAddressModel();
		$express_address->save($data);
		$this->updateAddressDefault($express_address->id);
		return $express_address->id;
	}
	
	/**
	 * 修改会员地址
	 */
	public function updateMemberExpressAddress($id, $data)
	{
		$express_address = new NsMemberExpressAddressModel();
		$express_address->save($data, [
			'id' => $id
		]);
		$retval = $this->updateAddressDefault($id);
		return $retval;
	}
	
	/**
	 * 修改会员收货地址默认
	 */
	public function updateAddressDefault($id)
	{
		$express_address = new NsMemberExpressAddressModel();
		$express_address->save([
			'is_default' => 0
		], [
			'uid' => $this->uid
		]);
		$res = $express_address->save([
			'is_default' => 1
		], [
			'id' => $id
		]);
		return $res;
	}
	
	/**
	 * 删除会员收货地址
	 */
	public function addressDelete($id)
	{
		$express_address = new NsMemberExpressAddressModel();
		$count = $express_address->where(array(
			"uid" => $this->uid
		))->count();
		if ($count == 1) {
			return USER_ADDRESS_DELETE_ERROR;
		} else {
			$express_address_info = $express_address->getInfo([
				'id' => $id,
				'uid' => $this->uid
			]);
			
			$res = $express_address->destroy($id);
			
			if ($express_address_info['is_default'] == 1) {
				$express_address_info = $express_address->where(array(
					"uid" => $this->uid
				))
					->order("id desc")
					->limit(0, 1)
					->select();
				$res = $this->updateAddressDefault($express_address_info[0]['id']);
			}
			
			return $res;
		}
	}
	
	/**
	 * 获取会员收货地址详情
	 */
	public function getMemberExpressAddressDetail($id)
	{
		$express_address = new NsMemberExpressAddressModel();
		$data = $express_address->get($id);
		if ($data['uid'] == $this->uid) {
			return $data;
		} else {
			return '';
		}
	}
	
	/**
	 * 获取会员默认地址
	 */
	public function getDefaultExpressAddress()
	{
		$express_address = new NsMemberExpressAddressModel();
		$data = $express_address->getInfo([
			'uid' => $this->uid,
			'is_default' => 1
		], '*');
		// 处理地址信息
		if (!empty($data)) {
			$address = new Address();
			$address_info = $address->getAddress($data['province'], $data['city'], $data['district']);
			$data['address_info'] = $address_info;
		}
		
		return $data;
	}
	
	/**
	 * 会员默认地址
	 */
	public function getMemberDefaultAddress($uid)
	{
		$express_address = new NsMemberExpressAddressModel();
		$data = $express_address->getInfo([ 'uid' => $uid, 'is_default' => 1 ], '*');
		// 处理地址信息
		if (!empty($data)) {
			$address = new Address();
			$address_info = $address->getAddress($data['province'], $data['city'], $data['district']);
			$data['address_info'] = $address_info . '&nbsp;' . $data['address'];
		}
		return $data;
	}
	
	/**
	 * 获取会员地址列表
	 */
	public function getMemberExpressAddressList($page_index = 1, $page_size = 0, $condition = [], $order = 'id desc')
	{
		$express_address = new NsMemberExpressAddressModel();
		$data = $express_address->pageQuery($page_index, $page_size, $condition, $order, '*');
		// 处理地址信息
		if (!empty($data)) {
			$address = new Address();
			foreach ($data['data'] as $key => $val) {
				$address_info = $address->getAddress($val['province'], $val['city'], $val['district']);
				$data['data'][ $key ]['address_info'] = $address_info;
			}
		}
		return $data;
	}
	
	/***********************************************************会员地址结束*********************************************************/
	
	
	/***********************************************************会员账户开始*********************************************************/
	
	/**
	 * 查询会员账户信息（对应商户 ）
	 */
	function getShopAccountListByUser($uid, $page_index, $page_size)
	{
		$userMessage = new NsMemberAccountModel();
		$data = array(
			'uid' => $uid
		);
		$result = $userMessage->pageQuery($page_index, $page_size, $data, 'id asc', 'shop_id,point,balance');
		return $result;
	}
	
	/**
	 * 获取会员在一个店铺的账户
	 */
	public function getMemberAccount($uid)
	{
		$member_account = new NsMemberAccountModel();
		$account_info = $member_account->getInfo([
			'uid' => $uid
		], '*');
		if (empty($account_info)) {
			$account_info["point"] = 0;
			$account_info['balance'] = 0;
		}
		// 购买次数
		$order_query = new OrderQuery();
		$account_info['order_num'] = $order_query->getOrderCount([ "buyer_id" => $uid, "pay_status" => 2 ]);
		return $account_info;
	}
	
	/**
	 * 会员积分转余额
	 */
	public function memberPointToBalance($uid, $shop_id, $point)
	{
		$member_account_model = new NsMemberAccountModel();
		$member_account_model->startTrans();
		try {
			$member_account_info = $this->getMemberAccount($uid);
			if ($point > $member_account_info['point']) {
				$member_account_model->commit();
				return LOW_POINT;
			} else {
				$point_config = new NsPointConfigModel();
				$point_info = $point_config->getInfo([
					'shop_id' => $shop_id
				], 'is_open, convert_rate');
				if ($point_info['is_open'] == 0 || empty($point_info)) {
					$member_account_model->rollback();
					return "积分兑换功能关闭";
				} else {
					$member_account = new MemberAccount();
					$exchange_balance = $member_account->pointToBalance($point, $shop_id);
					$retval = $member_account->addMemberAccountData($shop_id, 1, $uid, 0, $point * (-1), 3, 0, '积分兑换余额');
					if ($retval < 0) {
						$member_account_model->rollback();
						return $retval;
					}
					$retval = $member_account->addMemberAccountData($shop_id, 2, $uid, 1, $exchange_balance, 3, 0, '积分兑换余额');
					if ($retval < 0) {
						$member_account_model->rollback();
						return $retval;
					}
					$member_account_model->commit();
					return 1;
				}
			}
		} catch (\Exception $e) {
			$member_account_model->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 会员店铺积分账户总数
	 */
	public function memberShopPointCount($uid = 0, $shop_id = 0)
	{
		$member_account_model = new NsMemberAccountModel();
		$point_count = $member_account_model->getInfo([
			'shop_id' => $shop_id,
			'uid' => $uid
		], 'point');
		if (!empty($point_count)) {
			return $point_count['point'];
		} else {
			return 0;
		}
	}
	
	/**
	 * 获取会员店铺余额账户
	 */
	public function memberShopBalanceCount($uid = 0, $shop_id = 0)
	{
		$member_account_model = new NsMemberAccountModel();
		$balance_count = $member_account_model->getInfo([
			'shop_id' => $shop_id,
			'uid' => $uid
		], 'balance');
		if (!empty($balance_count)) {
			return $balance_count['balance'];
		} else {
			return 0;
		}
	}
	
	/**
	 * 添加会员账户数据
	 */
	public function addMemberAccount($shop_id, $type, $uid, $num, $text)
	{
		$member_account = new MemberAccount();
		$retval = $member_account->addMemberAccountData($shop_id, $type, $uid, 1, $num, 10, 0, $text);
		return $retval;
	}
	
	/**
	 * 添加会员充值
	 */
	public function createMemberRecharge($recharge_money, $uid, $out_trade_no)
	{
		$member_recharge = new NsMemberRechargeModel();
		$member_recharge->startTrans();
		try {
		    $pay = new UnifyPay();
		    $data = array(
		        'recharge_money' => $recharge_money,
		        'uid' => $uid,
		        'out_trade_no' => $pay->createOutTradeNo(),
		        'create_time' => time()
		    );
		    $res = $member_recharge->save($data);
		    $pay->createPayment($this->instance_id, $out_trade_no, '余额充值', '用户充值余额', $recharge_money, 4, $member_recharge->id);
		    $member_recharge->commit();
		    return $res;
		} catch (\Exception $e) {
		    $member_recharge->rollback();
		    return -1;
		}
	}
	
	/**
	 * 会员等级充值支付
	 */
	public function payMemberRecharge($out_trade_no, $pay_type)
	{
		$member_recharge_model = new NsMemberRechargeModel();
		$pay = new UnifyPay();
		$pay_info = $pay->getPayInfo($out_trade_no);
		if (!empty($pay_info)) {
			$type_alis_id = $pay_info["type_alis_id"];
			$pay_status = $pay_info["pay_status"];
			if ($pay_status == 1) {
				// 支付成功
				$racharge_obj = $member_recharge_model->get($type_alis_id);
				if (!empty($racharge_obj)) {
					$data = array(
						"is_pay" => 1,
						"status" => 1
					);
					$member_recharge_model->save($data, [
						"id" => $racharge_obj["id"]
					]);
					$member_account = new MemberAccount();
					if ($pay_type == 1) {
						$type_name = '微信充值';
					} elseif ($pay_type == 2) {
						$type_name = '支付宝充值';
					} else {
						$type_name = '余额充值';
					}
					$member_account->addMemberAccountData($pay_info["shop_id"], 2, $racharge_obj["uid"], 1, $racharge_obj["recharge_money"], 4, $racharge_obj["id"], $type_name);
//					runhook("Notify", "rechargeSuccessBusiness", [
//						"shop_id" => 0,
//						"out_trade_no" => $out_trade_no,
//						"uid" => $racharge_obj["uid"]
//					]); // 用户余额充值成功商家提醒
                    message("recharge_success_business", [
                        "shop_id" => 0,
                        "out_trade_no" => $out_trade_no,
                        "uid" => $racharge_obj["uid"]
                    ]);//用户余额充值成功商家提醒
//					runhook("Notify", "rechargeSuccessUser", [
//						"shop_id" => 0,
//						"out_trade_no" => $out_trade_no,
//						"uid" => $racharge_obj["uid"]
//					]); // 用户余额充值成功用户提醒
                    //wait del
                    message("recharge_success", [
                        "shop_id" => 0,
                        "out_trade_no" => $out_trade_no,
                        "uid" => $racharge_obj["uid"]
                    ]);//余额充值成功
//					hook('memberBalanceRechargeSuccess', [
//						'out_trade_no' => $out_trade_no,
//						'uid' => $racharge_obj["uid"],
//						'time' => time()
//					]);
				}
			}
		}
	}
	
	/**
	 * 添加会员提现
	 */
	public function addMemberBalanceWithdraw($shop_id, $withdraw_no, $uid, $bank_account_id, $cash)
	{
		// 得到本店的提线设置
		$config = new Config();
		$withdraw_info = $config->getBalanceWithdrawConfig($shop_id);
		// 判断是否余额提现设置是否为空 是否启用
		if (empty($withdraw_info) || $withdraw_info['is_use'] == 0) {
			return USER_WITHDRAW_NO_USE;
		}
		// 提现倍数判断
		if ($withdraw_info['value']["withdraw_multiple"] != 0) {
			$mod = $cash % $withdraw_info['value']["withdraw_multiple"];
			if ($mod != 0) {
				return USER_WITHDRAW_BEISHU;
			}
		}
		// 最小提现额判断
		if ($cash < $withdraw_info['value']["withdraw_cash_min"]) {
			return USER_WITHDRAW_MIN;
		}
		// 判断会员当前余额
		$member_account = new MemberAccount();
		$balance = $member_account->getMemberBalance($uid);
		if ($balance <= 0) {
			return ORDER_CREATE_LOW_PLATFORM_MONEY;
		}
		if ($balance < $cash || $cash <= 0) {
			return ORDER_CREATE_LOW_PLATFORM_MONEY;
		}
		// 获取 提现账户
		$member_bank_account = new NsMemberBankAccountModel();
		$bank_account_info = $member_bank_account->getInfo([
			'id' => $bank_account_id
		], '*');
		$brank_name = $bank_account_info['branch_bank_name'];
		
		// 提现方式如果不是银行卡，则显示账户类型名称
		if ($bank_account_info['account_type'] != 1) {
			$brank_name = $bank_account_info['account_type_name'];
		}
		
		// 添加提现记录
		$balance_withdraw = new NsMemberBalanceWithdrawModel();
		$data = array(
			'shop_id' => $shop_id,
			'withdraw_no' => $withdraw_no,
			'uid' => $uid,
			'bank_name' => $brank_name,
			'account_number' => $bank_account_info['account_number'],
			'realname' => $bank_account_info['realname'],
			'mobile' => $bank_account_info['mobile'],
			'cash' => $cash,
			'ask_for_date' => time(),
			'status' => 0,
			'modify_date' => time()
		);
		$balance_withdraw->save($data);
		// 添加账户流水
		$member_account->addMemberAccountData($shop_id, 2, $uid, 0, -$cash, 8, $balance_withdraw->id, "会员余额提现");
		if ($balance_withdraw->id) {
			$params['id'] = $balance_withdraw->id;
			$params['type'] = 'balance';
//			hook("memberWithdrawApplyCreateSuccess", $params);
            message("withdraw_apply", $params);
		}
		return $balance_withdraw->id;
	}
	
	/**
	 * 会员佣金提现拒绝
	 */
	public function userCommissionWithdrawRefuse($shop_id, $id, $status, $remark)
	{
		$member_balance_withdraw = new NsMemberBalanceWithdrawModel();
		$member_account = new MemberAccount();
		$retval = $member_balance_withdraw->where(array(
			"shop_id" => $shop_id,
			"id" => $id
		))->update(array(
			"status" => $status,
			"memo" => $remark,
		    "modify_date" => time()
		));
		$member_balance_withdraw = new NsMemberBalanceWithdrawModel();
		$member_balance_withdraw_info = $member_balance_withdraw->getInfo([
			'id' => $id
		], '*');
		if ($retval > 0 && $status == -1) {
			$member_account->addMemberAccountData($shop_id, 2, $member_balance_withdraw_info['uid'], 1, $member_balance_withdraw_info["cash"], 9, $id, "会员余额提现退回");
		}
		return $retval;
	}
	
	/**
	 * 会员提现审核同意
	 */
	public function memberBalanceWithdrawAudit($shop_id, $id, $status, $transfer_type, $transfer_name, $transfer_money, $transfer_remark, $transfer_no, $transfer_account_no, $type_id)
	{
		// 查询转账的信息
		$member_balance_withdraw = new NsMemberBalanceWithdrawModel();
		$member_balance_withdraw_info = $member_balance_withdraw->getInfo([ 'id' => $id ], '*');
		$transfer_status = 0;
		$transfer_result = "";
		if ($member_balance_withdraw_info['bank_name'] == '微信') {
			$type_id = 1;
		} elseif ($member_balance_withdraw_info['bank_name'] == '支付宝') {
			$type_id = 2;
		}
		
		$config = new Config();
		if ($member_balance_withdraw_info["transfer_status"] != 1 && $member_balance_withdraw_info["status"] != 1 && $status != -1) {
			if ($transfer_type == 1) {//线下转账
				$transfer_status = 1;
				$transfer_result = "会员提现, 线下转账成功";
			} else {//线上转账
				//提现公共参数
				$param = array(
					"account_number" => $member_balance_withdraw_info["account_number"],
					"desc" => $transfer_remark,
					"realname" => $member_balance_withdraw_info["realname"],
					"amount" => $transfer_money,
					"withdraw_no" => $member_balance_withdraw_info["withdraw_no"]
				);
				$addon_name = "";
				if ($type_id == 1) {
					// 线上微信转账
					$addon_name = "NsWeixinpay";
				} else {
					// 线上支付宝转账
					$addon_name = "NsAlipay";
				}
				
				$param["addon_name"] = $addon_name;
				$result = hook("transfer", $param);//根据所选方式 选择插件 进行转账
				$result = arrayFilter($result);
				$result = $result[0];
				if (empty($result)) {
					return array(
						"code" => -1,
						"message" => '无效的提现方式'
					);
				}
				if ($result["code"] <= 0) {
					return $result;
				}
				$transfer_result = $result["data"]["msg"];
				$transfer_status = $result["data"]["status"];
				
			}
		}
		if ($transfer_status != -1) {
			$member_balance_withdraw = new NsMemberBalanceWithdrawModel();
			$member_account = new MemberAccount();
			$member_update_data = array(
				"status" => $status,
				"transfer_type" => $transfer_type,
				"transfer_name" => $transfer_name,
				"transfer_money" => $transfer_money,
				"transfer_status" => $transfer_status,
				"transfer_remark" => $transfer_remark,
				"transfer_result" => $transfer_result,
				"transfer_account_no" => $transfer_account_no,
				"transfer_no" => $transfer_no,
			    "modify_date" => time()
			);
			$retval = $member_balance_withdraw->save($member_update_data, [ "shop_id" => 0, "id" => $id ]);
//            $retval = $member_balance_withdraw->where(array(
//                "shop_id" => $shop_id,
//                "id" => $id
//            ))->update(array(
//                "status" => $status,
//                "transfer_type" => $transfer_type,
//                "transfer_name" => $transfer_name,
//                "transfer_money" => $transfer_money,
//                "transfer_status" => $transfer_status,
//                "transfer_remark" => $transfer_remark,
//                "transfer_result" => $transfer_result,
//                "transfer_account_no" => $transfer_account_no,
//                "transfer_no" => $transfer_no
//            ));
			if ($retval > 0 && $status == -1) {
				$member_account->addMemberAccountData($shop_id, 2, $member_balance_withdraw_info['uid'], 1, $member_balance_withdraw_info["cash"], 9, $id, "会员余额提现退回");
			}
			if ($retval > 0 && $status == 1) {
				// 会员提现审核通过钩子
//				hook('memberWithdrawAuditAgree', [ 'id' => $id, 'type' => 'balance' ]);
				message("withdraw_result", [ 'id' => $id, 'type' => 'balance' ]);
			}
			return array(
				"code" => $retval,
				"message" => $transfer_result
			);
		} else {
			return array(
				"code" => $transfer_status,
				"message" => $transfer_result
			);
		}
	}
	
	/**
	 * 用户店铺消费
	 */
	public function getShopUserConsume($uid)
	{
		$member_account = new NsMemberAccountModel();
		$money = $member_account->getInfo([
			"shop_id" => 0,
			'uid' => $uid
		]);
		if (!empty($money)) {
			return $money['member_cunsum'];
		} else {
			return 0;
		}
	}
	/***********************************************************会员账户结束*********************************************************/
	
	
	/***********************************************************会员账户流水开始*********************************************************/
	
	/**
	 * 检测用户是否签到
	 */
	public function getIsMemberSign($uid, $shop_id)
	{
		$member_account_records = new NsMemberAccountRecordsModel();
		$day_begin_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$day_end_time = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
		$condition = array(
			'uid' => $uid,
			'shop_id' => $shop_id,
			'account_type' => 1,
			'from_type' => 5,
			'create_time' => array(
				'between',
				[
					$day_begin_time,
					$day_end_time
				]
			)
		);
		$count = $member_account_records->getCount($condition);
		return $count;
	}
	
	/**
	 * 检测用户是否分享
	 */
	public function getIsMemberShare($uid, $shop_id)
	{
		$member_account_records = new NsMemberAccountRecordsModel();
		$day_begin_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$day_end_time = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
		$condition = array(
			'uid' => $uid,
			'shop_id' => $shop_id,
			'account_type' => 1,
			'from_type' => 6,
			'create_time' => array(
				'between',
				[
					$day_begin_time,
					$day_end_time
				]
			)
		);
		$count = $member_account_records->getCount($condition);
		return $count;
	}
	
	/**
	 * 获取用户签到列表
	 */
	public function getPageMemberSignList($page_index, $page_size, $shop_id)
	{
		$member_account = new NsMemberAccountRecordsModel();
		$condition = array(
			'uid' => $this->uid,
			'account_type' => 1,
			'shop_id' => $shop_id,
			'from_type' => '5'
		);
		$list = $member_account->pageQuery($page_index, $page_size, $condition, 'create_time desc', 'sign,number,from_type,data_id,text,create_time');
		return $list;
	}
	
	/**
	 * 获取会员余额列表
	 */
	public function getMemberExtractionBalanceList($uid)
	{
		$member_account = new NsMemberAccountRecordsModel();
		$condition = array(
			'uid' => $uid,
			'account_type' => 2
		);
		$list = $member_account->getQuery($condition, 'sign,number,from_type,data_id,text,create_time', 'create_time desc');
		return $list;
	}
	
	/**
	 * 获取积分列表
	 */
	public function getPointList($page_index, $page_size, $condition, $order = 'nmar.create_time desc', $field = '*')
	{
		$member_account = new NsMemberAccountRecordsViewModel();
		$list = $member_account->getViewList($page_index, $page_size, $condition, $order);
		if (!empty($list['data'])) {
			foreach ($list['data'] as $k => $v) {
				$list['data'][ $k ]['type_name'] = MemberAccount::getMemberAccountRecordsName($v['from_type']);
				if ($list['data'][ $k ]['account_type'] == 1 && ($list['data'][ $k ]['from_type'] == 1 || $list['data'][ $k ]['from_type'] == 2)) {
					$ns_order = new NsOrderModel();
					$data_content = $ns_order->getInfo([ "order_id" => $list['data'][ $k ]['data_id'] ], "order_id,order_no");
					$list['data'][ $k ]['data_content'] = $data_content;
				}
			}
		}
		return $list;
	}
	
	/**
	 * 获取账户列表
	 * @param $page_index
	 * @param $page_size
	 * @param $condition
	 * @param string $order
	 * @param string $field
	 */
	public function getAccountList($page_index, $page_size, $condition = [], $order = 'nmar.create_time desc', $field = '*')
	{
		$member_account = new NsMemberAccountRecordsViewModel();
		$list = $member_account->getViewList($page_index, $page_size, $condition, $order);
		$model = $this->getRequestModel();
		if (!empty($list['data'])) {
			foreach ($list['data'] as $k => $v) {
				$list['data'][ $k ]['type_name'] = MemberAccount::getMemberAccountRecordsName($v['from_type']);
				$list['data'][ $k ]['account_type_name'] = MemberAccount::getMemberAccountRecordsTypeName($v['account_type']);
				if ($model != 'app') {
					if ($list['data'][ $k ]['account_type'] == 2 && ($list['data'][ $k ]['from_type'] == 1 || $list['data'][ $k ]['from_type'] == 2)) {
						$ns_order = new NsOrderModel();
						$data_content = $ns_order->getInfo([ "order_id" => $list['data'][ $k ]['data_id'] ], "order_id,order_no");
						$list['data'][ $k ]['data_content'] = $data_content;
					}
				}
				
			}
		}
		return $list;
	}
	
	/**
	 * 查询会员余额信息各项的总和
	 */
	public function getSelectBalanceSum()
	{
		$member_account_records_view = new NsMemberAccountRecordsViewModel();
		//商城订单支出
		$shancghen_sum = $member_account_records_view->getSum([ 'from_type' => 1 ], "number");
		//订单退还
		$order_sum = $member_account_records_view->getSum([ 'from_type' => 2 ], "number");
		//兑换
		$duihuan_sum = $member_account_records_view->getSum([ 'from_type' => 3 ], "number");
		//充值
		$chongzhi_sum = $member_account_records_view->getSum([ 'from_type' => 4 ], "number");
		//签到
		$qiandao_sum = $member_account_records_view->getSum([ 'from_type' => 5 ], "number");
		//分享
		$fenxiang_sum = $member_account_records_view->getSum([ 'from_type' => 6 ], "number");
		//注册
		$zhuce_sum = $member_account_records_view->getSum([ 'from_type' => 7 ], "number");
		//提现
		$tixian_sum = $member_account_records_view->getSum([ 'from_type' => 8 ], "number");
		//提现退还
		$titui_sum = $member_account_records_view->getSum([ 'from_type' => 9 ], "number");
		//调整
		$tiaozheng_sum = $member_account_records_view->getSum([ 'from_type' => 10 ], "number");
		$yue_zong = [
			"shancghen_sum" => $shancghen_sum,
			"order_sum" => $order_sum,
			"duihuan_sum" => $duihuan_sum,
			"chongzhi_sum" => $chongzhi_sum,
			"qiandao_sum" => $qiandao_sum,
			"fenxiang_sum" => $fenxiang_sum,
			"zhuce_sum" => $zhuce_sum,
			"tixian_sum" => $tixian_sum,
			"titui_sum" => $titui_sum,
			"tiaozheng_sum" => $tiaozheng_sum
		];
		return $yue_zong;
	}
	
	/**
	 * 获取订单支付流水号
	 */
	public function getOrderNumber($order_id)
	{
		$member_account = new NsOrderModel();
		$condition = array(
			"order_id" => array(
				"EQ",
				$order_id
			)
		);
		$data = $member_account->getInfo($condition, "out_trade_no");
		return $data;
	}
	
	/**
	 * 获取平台收入
	 */
	public function getShopMoneySum()
	{
		$order_payment_sum = new NsOrderPaymentModel();
		//微信收入
		$wx_money_sum = $order_payment_sum->getSum([ 'type' => 1, 'pay_type' => 1, 'pay_status' => 2 ], "pay_money");
		//支付宝收入
		$zfb_money_sum = $order_payment_sum->getSum([ 'type' => 1, 'pay_type' => 2, 'pay_status' => 2 ], "pay_money");
		$payMoneySum = [
			'wxmoneysum' => $wx_money_sum,
			'zfbmoneysum' => $zfb_money_sum
		];
		return $payMoneySum;
	}
	
	/***********************************************************会员账户流水结束*********************************************************/
	
	
	/***********************************************************会员收藏*********************************************************/
	
	/**
	 * 添加会员收藏
	 */
	public function addMemberFavouites($fav_id, $fav_type, $log_msg)
	{
		if (empty($this->uid)) {
			return 0;
		}
		$member_favorites = new NsMemberFavoritesModel();
		$count = $member_favorites->where(array(
			"fav_id" => $fav_id,
			"uid" => $this->uid,
			"fav_type" => $fav_type
		))->count("log_id");
		// 检查数据表中，防止用户重复收藏
		if ($count > 0) {
			return 0;
		}
		// 收藏商品
		$goods = new NsGoodsModel();
		$goods_info = $goods->getInfo([
			'goods_id' => $fav_id
		], 'goods_name,shop_id,price,picture,collects');
		// 查询商品图片信息
		$album = new AlbumPictureModel();
		$picture = $album->getInfo([
			'pic_id' => $goods_info['picture']
		], 'pic_cover_small');
		$shop_id = 0;
		$web_site = new WebSite();
		$web_info = $web_site->getWebSiteInfo();
		$shop_name = $web_info['title'];
		$shop_logo = $web_info['logo'];
		$data = array(
			'uid' => $this->uid,
			'fav_id' => $fav_id,
			'fav_type' => $fav_type,
			'fav_time' => time(),
			'shop_id' => $shop_id,
			'shop_name' => $shop_name,
			'shop_logo' => $shop_logo,
			'goods_name' => $goods_info['goods_name'],
			'goods_image' => $picture['pic_cover_small'],
			'log_price' => $goods_info['price'],
			'log_msg' => $log_msg
		);
		$retval = $member_favorites->save($data);
		$goods->save(array(
			"collects" => $goods_info["collects"] + 1
		), [
			"goods_id" => $fav_id
		]);
		return $retval;
	}
	
	/**
	 * 删除会员关注
	 */
	public function deleteMemberFavorites($fav_id, $fav_type)
	{
		$member_favorites = new NsMemberFavoritesModel();
		if (!empty($this->uid)) {
			// 收藏商品
			$goods = new NsGoodsModel();
			$goods_info = $goods->getInfo([
				'goods_id' => $fav_id
			], 'goods_name,shop_id,price,picture,collects');
			$condition = array(
				'fav_id' => $fav_id,
				'fav_type' => $fav_type,
				'uid' => $this->uid
			);
			$retval = $member_favorites->destroy($condition);
			$collect = empty($goods_info["collects"]) ? 0 : $goods_info["collects"];
			$collect--;
			if ($collect < 0) {
				$collect = 0;
			}
			$goods->save([
				"collects" => $collect
			], [
				"goods_id" => $fav_id
			]);
			return $retval;
		}
		
	}
	
	/**
	 * 获取信息是否会员关注(检测)
	 */
	public function getIsMemberFavorites($uid, $fav_id, $fav_type)
	{
		$member_favorites = new NsMemberFavoritesModel();
		$condition = array(
			'uid' => $uid,
			'fav_id' => $fav_id,
			'fav_type' => $fav_type
		);
		$res = $member_favorites->where($condition)->count();
		return $res;
	}
	
	/**
	 * 获取会员关注的商品列表
	 */
	public function getMemberGoodsFavoritesList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$fav = new NsMemberFavoritesModel();
		$list = $fav->getGoodsFavouitesViewList($page_index, $page_size, $condition, $order);
		return $list;
	}
	
	/**
	 * 获取店铺会员喜欢的关注列表
	 */
	public function getMemberShopsFavoritesList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$fav = new NsMemberFavoritesModel();
		$list = $fav->getShopsFavouitesViewList($page_index, $page_size, $condition, $order);
		return $list;
	}
	
	/***********************************************************会员关注结束*********************************************************/
	
	
	/***********************************************************会员浏览记录*********************************************************/
	
	/**
	 * 获取会员收藏历史
	 */
	public function getMemberViewHistory()
	{
		$has_history = Cookie::has('goodshistory');
		if ($has_history) {
			$goods_id_array = Cookie::get('goodshistory');
			$goods = new Goods();
			$list = $goods->getGoodsQueryLimit([
				'ng.goods_id' => array(
					'in',
					$goods_id_array
				),
				'ng.state' => 1
			], "ng.goods_id,ng.goods_name,ng_sap.pic_cover_mid,ng.price,ng.point_exchange_type,ng.point_exchange,ng.promotion_price");
			return $list;
		} else {
			return '';
		}
	}
	
	/**
	 * 获取用户商品收藏数量
	 */
	public function getMemberGoodsCollectionNum($uid)
	{
		$member_favorites = new NsMemberFavoritesModel();
		$count = $member_favorites->getCount([ 'fav_type' => 'goods', 'uid' => $uid ]);
		return $count;
	}
	
	/***********************************************************会员浏览结束*********************************************************/
	
	
	/***********************************************************会员浏览历史开始*********************************************************/
	
	/**
	 * 添加会员浏览历史
	 */
	public function addMemberViewHistory($goods_id)
	{
		$has_history = Cookie::has('goodshistory');
		if ($has_history) {
			$goods_id_array = Cookie::get('goodshistory');
			Cookie::set('goodshistory', $goods_id_array . ',' . $goods_id, 3600);
		} else {
			Cookie::set('goodshistory', $goods_id, 3600);
		}
		return 1;
	}
	
	/**
	 * 删除会员浏览历史
	 */
	public function deleteMemberViewHistory()
	{
		if (Cookie::has('goodshistory')) {
			Session::set('goodshistory', Cookie::get('goodshistory'));
		}
		Cookie::set('goodshistory', null);
	}
	
	/***********************************************************会员浏览历史结束*********************************************************/
	
	
	/***********************************************************会员提现账号开始*********************************************************/
	
	/**
	 * 添加会员银行账户
	 */
	public function addMemberBankAccount($data)
	{
		$member_bank_account = new NsMemberBankAccountModel();
		$member_bank_account->startTrans();
		try {
			$member_bank_account->save($data);
			$account_id = $member_bank_account->id;
			$this->setMemberBankAccountDefault($data['uid'], $account_id);
			$member_bank_account->commit();
			return $account_id;
		} catch (\Exception $e) {
			$member_bank_account->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 修改会员银行账户
	 */
	public function updateMemberBankAccount($data)
	{
		$member_bank_account = new NsMemberBankAccountModel();
		$member_bank_account->startTrans();
		try {
			$member_bank_account->save($data, [
				'id' => $data['id']
			]);
			$this->setMemberBankAccountDefault($this->uid, $data['id']);
			$member_bank_account->commit();
			return $data['id'];
		} catch (\Exception $e) {
			$member_bank_account->rollback();
			return $e->getMessage();
		}
	}
	
	/**
	 * 删除会员银行账户
	 */
	public function delMemberBankAccount($account_id)
	{
		$member_bank_account = new NsMemberBankAccountModel();
		$uid = $this->uid;
		$retval = $member_bank_account->destroy([
			'uid' => $uid,
			'id' => $account_id
		]);
		return $retval;
	}
	
	/**
	 * 设置员默认银行账户
	 */
	public function setMemberBankAccountDefault($uid, $account_id)
	{
		$member_bank_account = new NsMemberBankAccountModel();
		$member_bank_account->update([
			'is_default' => 0
		], [
			'uid' => $uid,
			'is_default' => 1
		]);
		$member_bank_account->update([
			'is_default' => 1
		], [
			'uid' => $uid,
			'id' => $account_id
		]);
		return $account_id;
	}
	
	/**
	 * 获取会员账户详情
	 */
	public function getMemberBankAccountDetail($id)
	{
		$member_bank_account = new NsMemberBankAccountModel();
		$bank_account_info = $member_bank_account->getInfo([
			'id' => $id,
			'uid' => $this->uid
		], '*');
		return $bank_account_info;
	}
	
	/**
	 * 获取会员银行账户
	 */
	public function getMemberBankAccount($is_default = 0)
	{
		$member_bank_account = new NsMemberBankAccountModel();
		$uid = $this->uid;
		$bank_account_list = '';
		if (!empty($uid)) {
			if (empty($is_default)) {
				$bank_account_list = $member_bank_account->getQuery([
					'uid' => $uid
				], '*', 'id desc');
			} else {
				$bank_account_list = $member_bank_account->getQuery([
					'uid' => $uid,
					'is_default' => 1
				]);
			}
		}
		
		return $bank_account_list;
	}
	
	/***********************************************************会员提现账号结束*********************************************************/
	
	
	/***********************************************************会员余额提现记录开始*********************************************************/
	
	/**
	 * 获取会员提现分页列表
	 */
	public function getMemberBalanceWithdraw($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$member_balance_withdraw = new NsMemberBalanceWithdrawModel();
		$list = $member_balance_withdraw->pageQuery($page_index, $page_size, $condition, $order, '*');
		if (!empty($list['data'])) {
			foreach ($list['data'] as $k => $v) {
				$user = new UserModel();
				$userinfo = $user->getInfo([
					'uid' => $v['uid']
				]);
				$list['data'][ $k ]['real_name'] = $userinfo["nick_name"];
			}
		}
		return $list;
	}
	
	/**
	 * 获取会员提现审核数量
	 */
	public function getMemberBalanceWithdrawCount($condition = [])
	{
		$member_balance_withdraw = new NsMemberBalanceWithdrawModel();
		$count = $member_balance_withdraw->getCount($condition);
		return $count;
	}
	
	/**
	 * 获取会员提现详情
	 */
	public function getMemberWithdrawalsDetails($id)
	{
		$member_balance_withdraw = new NsMemberBalanceWithdrawModel();
		$retval = $member_balance_withdraw->getInfo([
			'id' => $id
		], '*');
		if (!empty($retval)) {
			$user = new UserModel();
			$userinfo = $user->getInfo([
				'uid' => $retval['uid']
			]);
			$retval['real_name'] = $userinfo["nick_name"];
		}
		return $retval;
	}
	
	/**
	 * 查询会员提现信息各项的总和
	 */
	public function getSelectCashWithdrawalSum()
	{
		$member_balance_withdraw = new NsMemberBalanceWithdrawModel();
		/*提现*/
		//等待处理的
		$tx_status_0_money = $member_balance_withdraw->getSum([ 'status' => 0 ], "transfer_money");
		//已经同意的
		$tx_status_1_money = $member_balance_withdraw->getSum([ 'status' => 1 ], "transfer_money");
		//已经拒绝的
		$tx_status_2_money = $member_balance_withdraw->getSum([ 'status' => "-1" ], "transfer_money");
		//线下转账
		$xx_status_2_money = $member_balance_withdraw->getSum([ 'transfer_type' => 1 ], "transfer_money");
		//线上转账
		$xs_status_2_money = $member_balance_withdraw->getSum([ 'transfer_type' => 2 ], "transfer_money");
		//支付宝转账
		$zf_status_2_money = $member_balance_withdraw->getSum([ 'bank_name' => "支付宝" ], "transfer_money");
		//微信转账
		$wx_status_2_money = $member_balance_withdraw->getSum([ 'bank_name' => "微信" ], "transfer_money");
		
		$money_array = [
			'tx_status_0_money' => $tx_status_0_money,
			'tx_status_1_money' => $tx_status_1_money,
			'tx_status_2_money' => $tx_status_2_money,
			'xx_status_2_money' => $xx_status_2_money,
			'xs_status_2_money' => $xs_status_2_money,
			'zfb_status_money' => $zf_status_2_money,
			'wx_status_money' => $wx_status_2_money
		];
		return $money_array;
	}
	
	/***********************************************************会员余额提现记录结束*********************************************************/
	
	
	/***********************************************************会员优惠券开始*********************************************************/
	
	/**
	 * 会员获取优惠券
	 */
	public function memberGetCoupon($uid, $coupon_type_id, $get_type)
	{
		if ($get_type == 2) {
			$coupon = new NsCouponModel();
			$count = $coupon->getCount([
				'uid' => $uid,
				'coupon_type_id' => $coupon_type_id
			]);
			$coupon_type = new NsCouponTypeModel();
			$coupon_type_info = $coupon_type->getInfo([
				'coupon_type_id' => $coupon_type_id
			], 'max_fetch, start_time, end_time, term_of_validity_type');
			if (empty($coupon_type_info)) {
				return 0;
			}
			$time = time();
			/*  if($coupon_type_info['term_of_validity_type'] == 0){
				 if ($time < $coupon_type_info["start_time"] || $time > $coupon_type_info["end_time"]) {
					 return 0;
				 }
			 } */
			if ($time > $coupon_type_info["start_time"] && $time < $coupon_type_info["end_time"]) {
				if ($coupon_type_info['max_fetch'] != 0) {
					if ($count >= $coupon_type_info['max_fetch']) {
						return USER_HEAD_GET;
					}
				}
			}
		}
		
		$member_coupon = new MemberCoupon();
		$retval = $member_coupon->userAchieveCoupon($uid, $coupon_type_id, $get_type);
		return $retval;
	}
	
	/**
	 * 获取会员下面的优惠券列表
	 */
	public function getMemberCouponTypeList($shop_id, $uid)
	{
		// 查询可以发放的优惠券类型
		$coupon_type_model = new NsCouponTypeModel();
		$condition = array(
			'start_time' => array(
				'ELT',
				time()
			),
			'end_time' => array(
				'EGT',
				time()
			),
			'is_show' => 1,
			'shop_id' => $shop_id,
			'term_of_validity_type' => 0
		);
		$coupon_type_list = $coupon_type_model->getQuery($condition, '*', 'start_time desc');
		$coupon_type_list_two = $coupon_type_model->getQuery([ 'is_show' => 1, 'shop_id' => $shop_id, 'term_of_validity_type' => 1 ], '*', 'create_time desc');
		$coupon_type_list = array_merge($coupon_type_list, $coupon_type_list_two);
		
		if (!empty($coupon_type_list)) {
			$coupon = new NsCouponModel();
			foreach ($coupon_type_list as $k => $v) {
				$surplus_coupon = $coupon->getCount([
					'coupon_type_id' => $v['coupon_type_id'],
					'state' => 0
				]);
				if ($surplus_coupon == 0) {
					unset($coupon_type_list[ $k ]);
				} else {
					$received_num = 0;
					if (!empty($uid) && $v['max_fetch'] != 0) {
						$received_num = $coupon->getCount([
							'uid' => $uid,
							'coupon_type_id' => $v['coupon_type_id']
						]);
					}
					$coupon_type_list[ $k ]['received_num'] = $received_num;
				}
			}
		}
		
		return $coupon_type_list;
	}
	
	/**
	 * 获取会员优惠券列表
	 */
	public function getMemberCounponList($type, $shop_id = '')
	{
		$mebercoupon = new MemberCoupon();
		$list = $mebercoupon->getUserCouponList($type, $shop_id);
		return $list;
	}
	
	/**
	 * 获取会员单种优惠券数量
	 */
	public function getUserCouponCount($type, $shop_id)
	{
		$mebercoupon = new MemberCoupon();
		$count = $mebercoupon->getUserCouponCount($type, $shop_id);
		return $count;
	}
	
	/***********************************************************会员优惠券结束*********************************************************/
	
	
	/***********************************************************会员标签开始*********************************************************/
	
	/**
	 * 添加会员标签
	 */
	public function addMemberLabel($shop_id, $label_name, $desc)
	{
		$member_label = new NsMemberLabelModel();
		$data = array(
			'shop_id' => $shop_id,
			'label_name' => $label_name,
			'desc' => $desc,
			'create_time' => time()
		);
		$member_label->save($data);
		$data['id'] = $member_label->id;
		return $member_label->id;
	}
	
	/**
	 * 修改会员标签
	 */
	public function updateMemberLabel($id, $shop_id, $label_name, $desc)
	{
		$member_label = new NsMemberLabelModel();
		$data = array(
			'id' => $id,
			'shop_id' => $shop_id,
			'label_name' => $label_name,
			'desc' => $desc,
		);
		$res = $member_label->save($data, [
			'id' => $id
		]);
		
		$data['id'] = $id;
		if ($res == 0) {
			return 1;
		}
		return $res;
	}
	
	/**
	 * 获取会员标签详情
	 */
	public function getMemberLabelDetail($id)
	{
		$member_label = new NsMemberLabelModel();
		return $member_label->get($id);
	}
	
	/**
	 * 获取会员标签列表
	 */
	public function getMemberLabelList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '*')
	{
		$member_label = new NsMemberLabelModel();
		return $member_label->pageQuery($page_index, $page_size, $condition, $order, $field);
	}
	
	/***********************************************************会员标签结束*********************************************************/
	
	
	/***********************************************************会员等级开始*********************************************************/
	
	/**
	 * 添加会员等级
	 */
	public function addMemberLevel($data)
	{
		$member_level = new NsMemberLevelModel();
		$level_count = $member_level->getCount([ "level" => $data["level"] ]);
		if ($level_count == 0) {
			$member_level->save($data);
			$data['level_id'] = $member_level->level_id;
			hook("memberLevelSaveSuccess", $data);
			return $member_level->level_id;
		} else {
			return 0;
		}
		
	}
	
	/**
	 * 修改会员等级
	 */
	public function updateMemberLevel($data, $condition)
	{
		$result = $this->verifyMemberLevelExist($data, $condition);//检测等级条件是否可用
	    if($result["code"] < 0){
	        return $result;
	    }
		$member_level = new NsMemberLevelModel();
		$res = $member_level->save($data, $condition);
		$this->reorderMemberLevel();//重新对会员等级排序
		$data['level_id'] = $condition["level_id"];
		hook("memberLevelSaveSuccess", $data);
		if ($res == 0) {
			return success(1);
		}
		return success($res);
	}
	
	/**
	 * 修改会员等级单个字段
	 */
	public function modifyMemberLevelField($level_id, $field_name, $field_value)
	{
		$member_level = new NsMemberLevelModel();
		return $member_level->save([
			"$field_name" => $field_value
		], [
			'level_id' => $level_id
		]);
	}
	
	/**
	 * 删除会员等级
	 */
	public function deleteMemberLevel($level_id)
	{
		$member_level = new NsMemberLevelModel();
		$member_count = $this->getMemberLevelUserCount($level_id);
		$this->reorderMemberLevel();//重新对会员等级排序
		if ($member_count > 0) {
			return MEMBER_LEVEL_DELETE;
		} else {
			return $member_level->destroy($level_id);
		}
	}
	
	/**
	 * 删除会员等级
	 */
	public function deleteMemberLabel($id)
	{
		$member_label = new NsMemberLabelModel();
		$member_count = $this->getMemberLabelUserCount($id);
		if ($member_count > 0) {
			return MEMBER_LABEL_DELETE;
		} else {
			return $member_label->destroy($id);
		}
	}
	
	/**
	 * 获取会员等级详情
	 */
	public function getMemberLevelDetail($level_id)
	{
		$member_level = new NsMemberLevelModel();
		return $member_level->get($level_id);
	}
	
	/**
	 * 获取会员等级
	 */
	public function getMemberLevel()
	{
		$member_level = new NsMemberLevelModel();
		$list = $member_level->getQuery();
		return $list;
	}
	
	/**
	 * 获取会员等级列表
	 */
	public function getMemberLevelList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '*')
	{
		$member_level = new NsMemberLevelModel();
		return $member_level->pageQuery($page_index, $page_size, $condition, $order, $field);
	}
	
	/**
	 * 会员等级自动升级
	 */
	public function checkMemberLevel($shop_id, $user_id)
	{
		$member_model = new NsMemberModel();
		$member_level_model = new NsMemberLevelModel();
		
		$member_info = $member_model->getInfo([ "uid" => $user_id ]);
		if (empty($member_info))
			return;
		
		$level_info = $member_level_model->getInfo([ "level_id" => $member_info["member_level"] ]);
		
		if (empty($level_info)) {
			return;
		}
		//找到下一级
		$last_level_info = $member_level_model->getFirstData([ "level" => [ "gt", $level_info["level"] ] ], "level asc");
		if (empty($last_level_info))
			return;
		
		$config_service = new Config();
		$order_query = new OrderQuery();
		$config_info = $config_service->getMemberLevelConfig();
		
		$data = array(
			"level_id" => $last_level_info["level_id"],
			"uid" => $user_id
		);
		
		switch ($config_info["type"]) {
			case 1:
				//累计积分
				$member_account_model = new NsMemberAccountModel();
				$member_account_obj = $member_account_model->getInfo([ "uid" => $user_id ], "member_sum_point");
				if (empty($member_account_obj)) {
					return;
				}
				$member_sum_point = $member_account_obj["member_sum_point"];
				if ($member_sum_point >= $last_level_info["min_integral"]) {
					$this->updateMemberLevelAction($data);
				}
				break;
			case 2:
				//累计消费
				$money = $this->getShopUserConsume($user_id);
				if ($money >= $last_level_info["quota"]) {
					$this->updateMemberLevelAction($data);
				}
				break;
			case 3:
				//购买次数
				$order_num = $order_query->getOrderCount([ "buyer_id" => $user_id, "pay_status" => 2 ]);
				if ($order_num >= $last_level_info["order_num"]) {
					$this->updateMemberLevelAction($data);
				}
				break;
		}
	}
	
	/**
	 * 修改会员等级
	 */
	public function updateMemberLevelAction($data)
	{
		$level_id = $data["level_id"];
		$uid = $data["uid"];
		$member_model = new NsMemberModel();
		$member_model->save([ "member_level" => $level_id ], [ "uid" => $uid ]);
		$member_level_model = new NsMemberLevelModel();
		$level_info = $member_level_model->getInfo([ "level_id" => $level_id ]);
		
		if (empty($level_info)) {
			return 0;
		}
		//添加等级修改记录
		$member_level_records_model = new NsMemberLevelRecordsModel();
		$records_data = array(
			"level_id" => $level_info["level_id"],
			"level" => $level_info["level"],
			"level_name" => $level_info["level_name"],
			"create_time" => time()
		);
		$member_level_records_model->save($records_data);
		return 1;
		
	}
	
	/**
	 * 重新对会员等级排序
	 */
	public function reorderMemberLevel(){
	    $member_level_model = new NsMemberLevelModel();
	    $member_level_model->startTrans();
	    try {
	        $config_service = new Config();
	        $config_info = $config_service->getMemberLevelConfig();
	        $order = "";
	        switch ($config_info["type"]) {
	            case 1:
	                //累计积分
	                $order = "min_integral asc";
	                break;
	            case 2:
	                //累计消费
	                $order = "quota asc";
	                break;
	            case 3:
	                //购买次数
	                $order = "order_num asc";
	                break;
	        }
	        
	        $level_list = $member_level_model->getQuery([], "level_id", $order);
	        if(!empty($level_list)){
	            foreach($level_list as $k => $v){
	                $member_level_model = new NsMemberLevelModel();
	                $level = $k + 1;
	                $member_level_model->save(["level" => $level], ["level_id" => $v["level_id"]]);
	            }
	        }
	        $member_level_model->commit();
	        return 1;
	    } catch (\Exception $e) {
	        $member_level_model->rollback();
	        return $e->getMessage();
	    }
	}
	
	/***********************************************************会员等级结束*********************************************************/
	
	
	/***********************************************************用户操作开始*********************************************************/
	
	/**
	 * 添加用户操作记录
	 */
	public function addMemberBehaviorRecords($data = [])
	{
		$member_behavior_records_model = new NsMemberBehaviorRecordsModel();
		$res = $member_behavior_records_model->save($data);
		return $res;
	}
	
	/**
	 * 获取用户操作记录
	 */
	public function getMemberBehaviorRecordsInfo($condition)
	{
		$member_behavior_records_model = new NsMemberBehaviorRecordsModel();
		$info = $member_behavior_records_model->getInfo($condition);
		return $info;
	}
	
	/**
	 * 用户操作记录列表
	 */
	public function getMemberBehaviorRecordsQuery($condition, $field = "*", $order = "")
	{
		$member_behavior_records_model = new NsMemberBehaviorRecordsModel();
		$list = $member_behavior_records_model->getQuery($condition, $field, $order);
		return $list;
	}
	
	/***********************************************************用户操作结束*********************************************************/
	
	/**
	 * 猜你喜欢
	 */
	public function getGuessMemberLikes($page_index, $page_size)
	{
		$history = Cookie::has('goodshistory') ? Cookie::get('goodshistory') : Session::get('goodshistory');
		$goods_model = new NsGoodsModel();
		if (!empty($history)) {
			$history_array = explode(",", $history);
			$goods_id = $history_array[ count($history_array) - 1 ];
			$category_id = $goods_model->getInfo([
				'goods_id' => $goods_id
			], 'category_id');
		} else {
			$category_id['category_id'] = 0;
		}

		$goods_list = $goods_model->pageQuery($page_index, $page_size, ['category_id'=>$category_id['category_id'], "state"=>1], "", "goods_id,goods_name,price,point_exchange_type,point_exchange,promotion_price,picture");
		if(!empty($goods_list)){
			foreach ($goods_list['data'] as $key => $val){
				$album_model = new AlbumPictureModel();
				$album_info = $album_model->getInfo(['pic_id'=>$val['picture']], "pic_cover_mid");
				$goods_list['data'][$key]["pic_cover_mid"] = $album_info['pic_cover_mid'];
			}
		}
		
		return $goods_list;
	}
	
	/**
	 * 获取会员浏览记录
	 */
	public function getMemberBrowseRecord($page_index, $page_size, $condition, $order, $field = '*')
	{
		$goods_browse = new NsGoodsBrowseModel();
		$goods = new NsGoodsModel();
		$goods_browse_list = $goods_browse->pageQuery($page_index, $page_size, $condition, $order, $field);
		if (!empty($goods_browse_list)) {
			foreach ($goods_browse_list["data"] as $k => $v) {
				$goods_info = $goods->getInfo([
					"goods_id" => $v["goods_id"]
				], "goods_name, promotion_price, price, picture, clicks, point_exchange_type, point_exchange");
				
				$ablum_picture = new AlbumPictureModel();
				$picture_info = $ablum_picture->getInfo([
					"pic_id" => $goods_info["picture"]
				]);
				$goods_info["picture_info"] = $picture_info;
				$goods_browse_list["data"][ $k ]["goods_info"] = $goods_info;
			}
			return $goods_browse_list;
		}
	}
	
	/**
	 * 会员行为
	 * @param $params
	 */
	public function memberAction($params)
	{
		$res = hook("memberAction", $params);
		$res = array_filter($res);
		sort($res);
		return $res;
	}
	
	/**
	 * 获取会员行为
	 * @param $params
	 * @return array|void
	 */
	public function getMemberActionConfig($params)
	{
		$res = hook("getMemberActionConfig", $params);
		$res = array_filter($res);
		sort($res);
		return $res;
	}
	
}