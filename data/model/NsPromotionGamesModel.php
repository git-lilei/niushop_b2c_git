<?php
/**
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
namespace data\model;

use data\model\BaseModel as BaseModel;

/**
 * 营销游戏表
 */
class NsPromotionGamesModel extends BaseModel
{

    protected $table = 'ns_promotion_games';

    /**
     * 根据game_id和rule_id查询营销游戏奖励详情
     * 创建时间：2018年1月30日18:00:55
     *
     * @param unknown $game_id            
     * @return unknown
     */
    public function getPromotionGamesAwardInfo($game_id, $rule_id)
    {
        // 必须在活动范围之内（结束时间大于当前时间）
        $res = $this->alias('game')
            ->join('ns_promotion_game_rule game_rule', 'game_rule.game_id = game.game_id', 'left')
            ->field('game.game_id,game.name,game.game_type,game_rule.type,game_rule.points,game_rule.hongbao,game_rule.coupon_type_id,game_rule.gift_id,game_rule.rule_id,game.points')
            ->where("game.game_id=$game_id and game_rule.rule_id=$rule_id and game.end_time>" . time())
            ->find();
        return $res;
    }
}