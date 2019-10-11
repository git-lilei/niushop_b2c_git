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
 * App升级管理表
 *  
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  type varchar(255) NOT NULL COMMENT 'App类型，Android，IOS',
  version_number varchar(255) NOT NULL COMMENT '版本号',
  download_address varchar(255) NOT NULL COMMENT 'app下载地址',
  create_time int(11) NOT NULL COMMENT '创建时间',
  update_log varchar(255) DEFAULT '' COMMENT '更新日志',
  remark varchar(255) DEFAULT '' COMMENT '备注',
  PRIMARY KEY (id)
 * @author Administrator
 *
 */
class NsAppUpgradeModel extends BaseModel {

    protected $table = 'sys_app_upgrade';
    protected $rule = [
        'id'  =>  '',
    ];
    protected $msg = [
        'id'  =>  '',
    ];

}