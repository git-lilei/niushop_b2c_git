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
 * 专题活动商品表
  discount_goods_id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  topic_id int(11) NOT NULL COMMENT '对应专题活动',
  goods_id int(11) NOT NULL COMMENT '商品ID',
  status tinyint(4) NOT NULL DEFAULT 0 COMMENT '活动状态',
  goods_name varchar(255) NOT NULL DEFAULT '' COMMENT '商品名称',
  goods_picture int(11) NOT NULL COMMENT '商品图片',
  start_time int(11) DEFAULT 0 COMMENT '开始时间',
  end_time int(11) DEFAULT 0 COMMENT '结束时间',
 */
class NsPromotionTopicGoodsModel extends BaseModel {

    protected $table = 'ns_promotion_topic_goods';
    protected $rule = [
        'topic_goods_id'  =>  '',
    ];
    protected $msg = [
        'topic_goods_id'  =>  '',
    ];

}