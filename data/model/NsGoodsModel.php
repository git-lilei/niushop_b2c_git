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
 * 商品表
 * @author Administrator
 *
 */
class NsGoodsModel extends BaseModel
{
	
	protected $table = 'ns_goods';
	protected $rule = [
		'goods_id' => '',
		'description' => 'no_html_parse',
		'goods_spec_format' => 'no_html_parse'
	];
	protected $msg = [
		'goods_id' => '',
		'description' => '',
		'goods_spec_format' => ''
	];
	
	/**
	 * @see \data\model\BaseModel::save()
	 */
	public function save($data = [], $where = [], $sequence = null)
	{
		$retval = parent::save($data, $where, $sequence);
		if ($retval) {
			//$this->addLog($data, $where, $sequence);
		}
		return $retval;
	}
	
	/**
	 * 添加日志(针对父类save方法)
	 * @param unknown $data
	 * @param unknown $where
	 * @param unknown $sequence
	 */
	public function addLog($data, $where, $sequence)
	{
		$user_log = new UserLogModel();
		if (empty($where)) {
			$user_log->addUserLog(1, "添加商品:" . json_encode($data));
		} else {
			$user_log->addUserLog(1, "修改商品:" . json_encode($data));
		}
	}
	
	
}