<?php
/**
 * Promotion.php
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

namespace app\api\controller;

use data\service\Promotion as PromotionService;

/**
 * 营销控制器
 */
class Promotion extends BaseApi
{
	
	/**
	 * 获取营销游戏列表
	 */
	public function promotionGamesList()
	{
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : 0;
		$condition = isset($this->params['condition']) ? $this->params['condition'] : "";
		$order = isset($this->params['order']) ? $this->params['order'] : "";
		$promotion = new PromotionService();
		$res = $promotion->getPromotionGamesList($page_index, $page_size, $condition, $order);
		return $this->outMessage("获取营销游戏列表", $res);
	}
	
	/**
	 * 是否开启
	 */
	public function checkGameOpen()
	{
		$game_id = isset($this->params['game_id']) ? $this->params['game_id'] : "";
		if (empty($this->uid)) {
			$_SESSION['login_pre_url'] = __URL(\think\Config::get('view_replace_str.APP_MAIN') . "/Game/index?gid=" . $game_id);
			$redirect = __URL(__URL__ . "/wap/login");
			return $this->outMessage("判断是否开启", $redirect);
		} else {
			return $this->outMessage("判断是否开启", null, -1);
		}
	}
	
	/**
	 * 游戏检测
	 */
	public function checkGame()
	{
		$promotion = new PromotionService();
		
		$game_id = isset($this->params['game_id']) ? $this->params['game_id'] : "";
		
		$gameDetail = $promotion->getPromotionGameDetail($game_id);
		
		if (empty($gameDetail["game_id"])) {
			return $this->outMessage("未找到该活动信息！", "member/index", 0);
		}
		if ($gameDetail["start_time"] > time()) {
			return $this->outMessage("该活动尚未开始！", "member/index", 1);
		}
		if ($gameDetail["end_time"] < time()) {
			return $this->outMessage("该活动尚未开始！", "member/index", 2);
		}
		
		if ($gameDetail["member_level"] != 0) {
			if ($gameDetail["member_level"] != $gameDetail["member_level"]) {
				$error_message = "对不起,该活动只有" . $gameDetail["level_name"] . "才可以参与！";
				$this->error($error_message, "member/index");
				return $this->outMessage($error_message, "member/index", 3);
				
			}
		}
		return $this->outMessage("游戏详情", $gameDetail, 4);
	}
	
	/**
	 * 获奖列表
	 */
	public function gameWinningList()
	{
		$promotion = new PromotionService();
		$game_id = isset($this->params['game_id']) ? $this->params['game_id'] : "";
		$gameDetail = $promotion->getPromotionGameDetail($game_id);
		$condition = [
			"game_id" => $game_id,
			"shop_id" => $this->instance_id,
			"is_winning" => 1
		];
		$winningRecordsList = array();
		if ($gameDetail['winning_list_display'] == 1) {
			$winningRecordsList = $promotion->getPromotionGameWinningRecordsList(1, 15, $condition, "add_time desc", "*");
		}
		
		return $this->outMessage("获奖列表", $winningRecordsList['data']);
	}
	
	/**
	 * 活动参与限制
	 */
	public function participationRestriction()
	{
		$promotion = new PromotionService();
		$game_id = isset($this->params['game_id']) ? $this->params['game_id'] : "";
		$participationRestriction = $promotion->getPromotionParticipationRestriction($game_id, $this->uid);
		return $this->outMessage("活动限制", $participationRestriction);
	}
	
	/**
	 * 随机获取奖项
	 */
	public function randAward()
	{
		$title = "随机获取奖项";
		$promotion = new PromotionService();
		$game_id = isset($this->params['game_id']) ? $this->params['game_id'] : 0;
		$participationRestriction = $promotion->getPromotionParticipationRestriction($game_id, $this->uid);
		if (!empty($participationRestriction)) {
			$res = array(
				"is_winning" => -1,
				"message" => $participationRestriction
			);
			return $this->outMessage($title, $res);
		}
		$res = $promotion->getRandAward($game_id);
		if ($res['is_winning'] == -2) {
			return $this->outMessage($title, $res);
		}
		//添加中奖记录
		$data = array(
			"uid" => $this->uid,
			"game_id" => $game_id,
			"rule_id" => $res["winning_info"]["rule_id"],
		);
		$result = $promotion->addPromotionGamesWinningRecords($data);
		if ($result["code"] == 0) {
			$res = array(
				"is_winning" => 0,
				"no_winning_instruction" => $res["no_winning_instruction"]
			);
			return $this->outMessage($title, $res);
		} else if ($result["code"] == -1) {
			$res = array(
				"is_winning" => -1,
				"message" => $result["message"]
			);
			return $this->outMessage($title, $res);
		}
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 积分配置
	 */
	public function pointConfig()
	{
		$Promotion = new PromotionService();
		$pointconfiginfo = $Promotion->getPointConfig();
		return $this->outMessage("积分配置", $pointconfiginfo);
	}
	
	/**
	 * 获取赠品详情
	 */
	public function promotionGiftDetail()
	{
		if (empty($this->uid)) {
			return $this->outMessage('', "", '-9999', "无法获取会员登录信息");
		}
		$gift_id = isset($this->params['id']) ? $this->params['id'] : '';
		$promotion = new PromotionService();
		$giftDetail = $promotion->getPromotionGiftDetail($gift_id);
		return $this->outMessage('', $giftDetail);
	}
}