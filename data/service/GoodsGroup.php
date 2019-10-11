<?php
/**
 * GoodsGroup.php
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

/**
 * 商品分组服务层
 */
use data\model\AlbumPictureModel;
use data\model\NsGoodsGroupModel;
use data\model\NsGoodsModel;
use think\Cache;

class GoodsGroup extends BaseService
{
	
	private $goods_group;
	
	function __construct()
	{
		parent:: __construct();
		$this->goods_group = new NsGoodsGroupModel();
	}
	
	/***********************************************************商品分组开始*********************************************************/
	
	/**
	 * 添加或修改商品分组
	 */
	public function editGoodsGroup($data)
	{
		Cache::clear("niu_goods_group");
		$data['level'] = 1; //目前仅为一级
//         $parent_level = $this->getGoodsGroupDetail($pid);
//     	if($pid == 0){
//     		$level = 1;
//     	}else{
//     		$level = $parent_level['level'] + 1;
//     	}
		if (empty($data['group_id'])) {
			$this->goods_group->save($data);
			$data['group_id'] = $this->goods_group->group_id;
			hook("goodsGroupSaveSuccess", $data);
			$res = $this->goods_group->group_id;
		} else {
			$res = $this->goods_group->save($data, [ 'group_id' => $data['group_id'] ]);
			hook("goodsGroupSaveSuccess", $data);
		}
		return $res;
	}
	
	/**
	 * 修改商品分组  单个字段
	 */
	public function modifyGoodsGroupField($group_id, $field_name, $field_value)
	{
		Cache::clear("niu_goods_group");
		$res = $this->goods_group->ModifyTableField('group_id', $group_id, $field_name, $field_value);
		return $res;
	}
	
	/**
	 * 删除商品分组
	 */
	public function deleteGoodsGroup($goods_group_id_array)
	{
		Cache::clear("niu_goods_group");
		$sub_list = $this->getGoodsGroupListByParentId($goods_group_id_array);
		//查询该标签是否已使用
		$goods = new Goods();
		$conditions = array(
			'group_id_array' => array( 'in', $goods_group_id_array )
		);
		$isuse_sub_list = $goods->getGoodsList(1, 0, $conditions, 'goods_id desc');
		if (!empty($sub_list)) {
			$res = SYSTEM_DELETE_FAIL;
		} else if (!empty($isuse_sub_list['data'])) {
			$res = SYSTEM_ISUSED_DELETE_FAIL;
		} else {
			$condition = array(
				'shop_id' => $this->instance_id,
				'group_id' => array( 'in', $goods_group_id_array )
			);
			$res = $this->goods_group->destroy($condition);
			hook("goodsGroupDeleteSuccess", [ 'group_id' => $goods_group_id_array ]);
		}
		return $res;
	}
	
	/**
	 * 商品分组详情
	 */
	public function getGoodsGroupDetail($group_id)
	{
		$cache = Cache::tag("niu_goods_group")->get("getGoodsGroupDetail" . '_' . $group_id);
		if (!empty($cache)) {
			return $cache;
		}
		$info = $this->goods_group->get($group_id);
		$picture = new AlbumPictureModel();
		$pic_info = array();
		$pic_info['pic_cover'] = '';
		if (!empty($info['group_pic'])) {
			$pic_info = $picture->get($info['group_pic']);
		}
		$info['picture'] = $pic_info;
		Cache::tag("niu_goods_group")->set("getGoodsGroupDetail" . '_' . $group_id, $info);
		return $info;
	}
	
	/**
	 * 返回 二级的列表
	 */
	public function getGoodsGroupQuery($shop_id)
	{
		//一级
		$cache = Cache::tag("niu_goods_group")->get("getGoodsGroupQuery" . $shop_id);
		if (empty($cache)) {
			$list = $this->getGoodsGroupListByParentId(0);
			foreach ($list as $k => $v) {
				$child_list = $this->getGoodsGroupListByParentId($v['group_id']);
				$v['child_list'] = $child_list;
			}
			Cache::tag("niu_goods_group")->set("getGoodsGroupQuery" . $shop_id, $list);
			return $list;
		} else {
			return $cache;
		}
		
	}
	
	/**
	 * 获取商品分组的子分类(一级)
	 */
	public function getGoodsGroupListByParentId($pid)
	{
		$cache = Cache::tag("niu_goods_group")->get("getGoodsGroupListByParentId" . $this->instance_id . '_' . $pid);
		if (empty($cache)) {
			$condition = array(
				'shop_id' => $this->instance_id,
				'pid' => $pid
			);
			$list = $this->getGoodsGroupList(1, 0, $condition, 'pid,sort');
			foreach ($list['data'] as $k => $v) {
				$picture = new AlbumPictureModel();
				$pic_info = array();
				$pic_info['pic_cover'] = '';
				if (!empty($v['group_pic'])) {
					$pic_info = $picture->get($v['group_pic']);
				}
				$v['picture'] = $pic_info;
			}
			Cache::tag("niu_goods_group")->set("getGoodsGroupListByParentId" . $this->instance_id . '_' . $pid, $list['data']);
			return $list['data'];
		} else {
			return $cache;
		}
		
	}
	
	/**
	 * 查询分组商品列表数据结构
	 */
	public function getGroupGoodsTree($shop_id)
	{
		$cache = Cache::tag("niu_goods_group")->get("getGroupGoodsTree" . $shop_id);
		if (empty($cache)) {
			$list = $this->goods_group->getQuery([ 'shop_id' => $shop_id ]);
			$goods = new NsGoodsModel();
			$goods_list = $goods->getQuery([ 'shop_id' => $shop_id ]);
			foreach ($list as $k => $v) {
				$group_goods_list = array();
				foreach ($goods_list as $k_goods => $v_goods) {
					$group_id_array = explode(',', $v_goods['group_id_array']);
					if (in_array($v['group_id'], $group_id_array) || $v['group_id'] == 0) {
						$picture = new AlbumPictureModel();
						$picture_info = $picture->get($v_goods['picture']);
						$v_goods['picture_info'] = $picture_info;
						$group_goods_list[] = $v_goods;
					}
				}
				$list[ $k ]['goods_list'] = $group_goods_list;
				$list[ $k ]['goods_list_count'] = count($group_goods_list);
			}
			Cache::tag("niu_goods_group")->set("getGroupGoodsTree" . $shop_id, $list);
			return $list;
		} else {
			return $cache;
		}
		
	}
	
	/**
	 * 查询商品分组
	 */
	public function getGoodsGroupQueryList($condition)
	{
		$data = json_encode($condition);
		$cache = Cache::tag("niu_goods_group")->get("getGoodsGroupQueryList" . $data);
		if (empty($cache)) {
			$res = $this->goods_group->getQuery($condition, "*", "sort");
			Cache::tag("niu_goods_group")->set("getGoodsGroupQueryList" . $data, $res);
			return $res;
		} else {
			return $cache;
		}
		
	}
	
	/**
	 * 获取商品分组列表
	 */
	public function getGoodsGroupList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '*')
	{
		$data = array( $page_index, $page_size, $condition, $order, $field );
		$data = json_encode($data);
		$cache = Cache::tag("niu_goods_group")->get("getGoodsGroupList" . $data);
		if (empty($cache)) {
			$list = $this->goods_group->pageQuery($page_index, $page_size, $condition, $order, $field);
			foreach ($list['data'] as $k => $v) {
				$picture = new AlbumPictureModel();
				$pic_info = array();
				$pic_info['pic_cover'] = '';
				if (!empty($v['group_pic'])) {
					$pic_info = $picture->get($v['group_pic']);
				}
				$list['data'][ $k ]['picture'] = $pic_info;
			}
			
			Cache::tag("niu_goods_group")->set("getGoodsGroupList" . $data, $list);
			return $list;
		} else {
			return $cache;
		}
	}
	
	/***********************************************************商品分组结束*********************************************************/
}