<?php
/**
 * NoticeRecordsModel.php
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

namespace data\model;

use data\model\BaseModel as BaseModel;
/**
 * 发送通知的记录表
 */
class NoticeRecordsModel extends BaseModel {

    protected $table = 'sys_notice_records';
    protected $rule = [
        'id'  =>  '',
        "send_config"=>'no_html_parse',
        "notice_context"=>'no_html_parse',
        "send_message"=>'no_html_parse'
    ];
    protected $msg = [
        'id'  =>  '',
        "send_config"=>'',
        "notice_context"=>'',
        "send_message"=>""
    ];
}