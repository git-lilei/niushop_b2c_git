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
 * 专题活动表
  topic_id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  shop_id int(11) NOT NULL DEFAULT 1 COMMENT '店铺ID',
  shop_name varchar(50) NOT NULL DEFAULT '' COMMENT '店铺名称',
  topic_name varchar(255) NOT NULL DEFAULT '' COMMENT '活动名称',
  keyword varchar(255) NOT NULL DEFAULT '' COMMENT '专题关键字',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '专题描述',
  picture_img varchar(255) NOT NULL DEFAULT '' COMMENT '图像地址',
  scroll_img varchar(255) NOT NULL DEFAULT '' COMMENT '条幅图片',
  background_img varchar(255) NOT NULL DEFAULT '' COMMENT '背景图',
  background_color varchar(255) NOT NULL DEFAULT '' COMMENT '背景色',
  introduce text NOT NULL COMMENT '专题介绍',
  template_file varchar(255) NOT NULL DEFAULT '' COMMENT '专题模板文件',
  is_head tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否显示头部0.不显示1.显示',
  is_foot tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否显示底部',
  status tinyint(4) NOT NULL DEFAULT 0 COMMENT '活动状态(0-未发布/1-正常/3-关闭/4-结束)',
  start_time int(11) DEFAULT 0 COMMENT '开始时间',
  end_time int(11) DEFAULT 0 COMMENT '结束时间',
  create_time int(11) DEFAULT 0 COMMENT '创建时间',
  modify_time int(11) DEFAULT 0 COMMENT '修改时间',
 */
class NsPromotionTopicModel extends BaseModel {

    protected $table = 'ns_promotion_topic';
    protected $rule = [
        'topic_id'  =>  '',
    ];
    protected $msg = [
        'topic_id'  =>  '',
    ];

}