<?php
/**
 * Platform.php
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

use data\model\AlbumPictureModel as AlbumPictureModel;
use data\model\NsGoodsGroupModel;
use data\model\NsGoodsViewModel;
use data\model\NsNoticeModel;
use data\model\NsPlatformAdvModel as NsPlatformAdvModel;
use data\model\NsPlatformAdvPositionModel as NsPlatformAdvPositionModel;
use data\model\NsPlatformAdvViewModel;
use data\model\NsPlatformGoodsRecommendModel;
use data\model\NsPlatformHelpClassModel;
use data\model\NsPlatformHelpDocumentModel;
use data\model\NsPlatformLinkModel;
use think\Cache;
use think\Log;

/**
 */
class Platform extends BaseService
{
    /**********************************************友情链接*****************************************************************/
    /**
     * 添加友情链接
     *
     * @param unknown $link_title
     * @param unknown $link_url
     * @param unknown $link_pic
     * @param unknown $link_sort
     */
//     public function addLink($link_title, $link_url, $link_pic, $link_sort, $is_blank, $is_show)
//     {
//         Cache::clear("niu_link");
//         $data = array(
//             'link_title' => $link_title,
//             'link_url' => $link_url,
//             'link_pic' => $link_pic,
//             'link_sort' => $link_sort,
//             'is_blank' => $is_blank,
//             'is_show' => $is_show
//         );
//         $link = new NsPlatformLinkModel();
//         $link->save($data);
//         return $link->link_id;
//         // TODO Auto-generated method stub
//     }
    
    /**
     * 修改友情链接
     *
     * @param unknown $link_id
     * @param unknown $link_title
     * @param unknown $link_url
     * @param unknown $link_pic
     * @param unknown $link_sort
     */
//     public function updateLink($link_id, $link_title, $link_url, $link_pic, $link_sort, $is_blank, $is_show)
//     {
//         Cache::clear("niu_link");
//         $data = array(
//             'link_title' => $link_title,
//             'link_url' => $link_url,
//             'link_pic' => $link_pic,
//             'link_sort' => $link_sort,
//             'is_blank' => $is_blank,
//             'is_show' => $is_show
//         );
//         $link = new NsPlatformLinkModel();
//         $retval = $link->save($data, [
//             'link_id' => $link_id
//         ]);
//         return $retval;
//         // TODO Auto-generated method stub
//     }
    
    /**
     * 删除友情链接
     *
     * @param unknown $link_id
     */
//     public function deleteLink($link_id)
//     {
//         Cache::clear("niu_link");
//         $link = new NsPlatformLinkModel();
//         $retval = $link->destroy($link_id);
//         return $retval;
//         // TODO Auto-generated method stub
//     }
    /**
     * 设置友情链接是否打开新窗口
     * @param unknown $link_id
     * @param unknown $is_show
     * @return boolean
     */
//     public function setPlatformLinklistIsblank($link_id, $is_show)
//     {
//         // 	    Cache::clear('niu_adv');
//         $platform_linklist = new NsPlatformLinkModel();
//         $data = array(
//             'is_blank' => $is_show
//         );
//         $res = $platform_linklist->save($data, [
//             'link_id' => $link_id
//         ]);
//         return $res;
//     }
    
    
    /**
     * 设置友情链接是否显示
     * @param unknown $link_id
     * @param unknown $is_use
     * @return boolean
     */
//     public function setPlatformLinklistIsshow($link_id, $is_use)
//     {
//         // 	    Cache::clear('niu_adv');
//         $platform_linklist = new NsPlatformLinkModel();
//         $data = array(
//             'is_show' => $is_use
//         );
//         $res = $platform_linklist->save($data, [
//             'link_id' => $link_id
//         ]);
//         return $res;
//     }
    
    /**
     * 获取友情链接详情
     *
     * @param unknown $link_id
     */
//     public function getLinkDetail($link_id)
//     {
//         $cache = Cache::tag("niu_link")->get("getLinkDetail" . $link_id);
//         if (!empty($cache)) return $cache;
    
//         $link = new NsPlatformLinkModel();
//         $info = $link->get($link_id);
//         Cache::tag("niu_link")->set("getLinkDetail" . $link_id, $info);
//         return $info;
//     }
    
	/**
	 * 获取友情链接
	 *
	 * @param unknown $page_index
	 * @param number $page_size
	 * @param string $order
	 * @param string $where
	 */
// 	public function getLinkList($page_index = 1, $page_size = 0, $where = '', $order = '', $field = '*')
// 	{
// 		$cache = Cache::tag('niu_link')->get('getLinkList' . json_encode([ $page_index, $page_size, $where, $order, $field ]));
// 		if (!empty($cache)) return $cache;
		
// 		$link = new NsPlatformLinkModel();
// 		$list = $link->pageQuery($page_index, $page_size, $where, $order, $field);
// 		Cache::tag('niu_link')->set('getLinkList' . json_encode([ $page_index, $page_size, $where, $order, $field ]), $list);
// 		return $list;
// 	}
	/**********************************************友情链接结束*************************************************************/
	/**********************************************广告管理****************************************************************/
	/**
	 * 添加平台广告
	 * @param unknown $ap_id
	 * @param unknown $adv_title
	 * @param unknown $adv_url
	 * @param unknown $adv_image
	 * @param unknown $slide_sort
	 * @param unknown $background
	 * @param unknown $adv_code
	 * @return boolean
	 */
// 	public function addPlatformAdv($ap_id, $adv_title, $adv_url, $adv_image, $slide_sort, $background, $adv_code)
// 	{
// 	    Cache::clear("niu_adv");
// 	    $platform_adv = new NsPlatformAdvModel();
// 	    $data = array(
// 	        'ap_id' => $ap_id,
// 	        'adv_title' => $adv_title,
// 	        'adv_url' => $adv_url,
// 	        'adv_image' => $adv_image,
// 	        'slide_sort' => $slide_sort,
// 	        'background' => $background,
// 	        'adv_code' => $adv_code
// 	    );
// 	    $res = $platform_adv->save($data);
// 	    return $res;
// 	}
	
	/**
	 * 添加平台广告位
	 * @param unknown $instance_id
	 * @param unknown $ap_name
	 * @param unknown $ap_intro
	 * @param unknown $ap_class
	 * @param unknown $ap_display
	 * @param unknown $is_use
	 * @param unknown $ap_height
	 * @param unknown $ap_width
	 * @param unknown $default_content
	 * @param unknown $ap_background_color
	 * @param unknown $type
	 * @param unknown $ap_keyword
	 */
// 	public function addPlatformAdvPosition($instance_id, $ap_name, $ap_intro, $ap_class, $ap_display, $is_use, $ap_height, $ap_width, $default_content, $ap_background_color, $type, $ap_keyword)
// 	{
// 	    Cache::clear("niu_adv");
// 	    $platform_adv_position = new NsPlatformAdvPositionModel();
// 	    $data = array(
// 	        'instance_id' => $instance_id,
// 	        'ap_name' => $ap_name,
// 	        'ap_intro' => $ap_intro,
// 	        'ap_class' => $ap_class,
// 	        'ap_display' => $ap_display,
// 	        'is_use' => $is_use,
// 	        'ap_height' => $ap_height,
// 	        'ap_width' => $ap_width,
// 	        'default_content' => $default_content,
// 	        'ap_background_color' => $ap_background_color,
// 	        'type' => $type,
// 	        'ap_keyword' => $ap_keyword
// 	    );
// 	    $res = $platform_adv_position->save($data);
// 	    return $res;
// 	}
	
	/**
	 * 修改广告
	 * @param unknown $adv_id
	 * @param unknown $ap_id
	 * @param unknown $adv_title
	 * @param unknown $adv_url
	 * @param unknown $adv_image
	 * @param unknown $slide_sort
	 * @param unknown $background
	 * @param unknown $adv_code
	 * @return boolean
	 */
// 	public function updatePlatformAdv($adv_id, $ap_id, $adv_title, $adv_url, $adv_image, $slide_sort, $background, $adv_code)
// 	{
// 	    Cache::clear("niu_adv");
// 	    $platform_adv = new NsPlatformAdvModel();
// 	    $data = array(
// 	        'ap_id' => $ap_id,
// 	        'adv_title' => $adv_title,
// 	        'adv_url' => $adv_url,
// 	        'adv_image' => $adv_image,
// 	        'slide_sort' => $slide_sort,
// 	        'background' => $background,
// 	        'adv_code' => $adv_code
// 	    );
// 	    $res = $platform_adv->save($data, [
// 	        'adv_id' => $adv_id
// 	    ]);
// 	    return $res;
// 	}

	/**
	 * 修改平台广告位
	 * @param unknown $ap_id
	 * @param unknown $instance_id
	 * @param unknown $ap_name
	 * @param unknown $ap_intro
	 * @param unknown $ap_class
	 * @param unknown $ap_display
	 * @param unknown $is_use
	 * @param unknown $ap_height
	 * @param unknown $ap_width
	 * @param unknown $default_content
	 * @param unknown $ap_background_color
	 * @param unknown $type
	 * @param unknown $ap_keyword
	 */
// 	public function updatePlatformAdvPosition($ap_id, $instance_id, $ap_name, $ap_intro, $ap_class, $ap_display, $is_use, $ap_height, $ap_width, $default_content, $ap_background_color, $type, $ap_keyword)
// 	{
// 	    Cache::clear("niu_adv");
// 	    $platform_adv_position = new NsPlatformAdvPositionModel();
// 	    $data = array(
// 	        'ap_name' => $ap_name,
// 	        'instance_id' => $instance_id,
// 	        'ap_intro' => $ap_intro,
// 	        'ap_class' => $ap_class,
// 	        'ap_display' => $ap_display,
// 	        'is_use' => $is_use,
// 	        'ap_height' => $ap_height,
// 	        'ap_width' => $ap_width,
// 	        'default_content' => $default_content,
// 	        'ap_background_color' => $ap_background_color,
// 	        'type' => $type,
// 	        'ap_keyword' => $ap_keyword
// 	    );
// 	    $res = $platform_adv_position->save($data, [
// 	        'ap_id' => $ap_id
// 	    ]);
// 	    return $res;
// 	}
	

	/**
	 * 添加或编辑广告位
	 * @param unknown $data
	 */
// 	public function addOrEditAdvPosition($params){
// 	    $platform_adv_position = new NsPlatformAdvPositionModel();
// 	    $platform_adv = new NsPlatformAdvModel();
	     
// 	    $platform_adv_position->startTrans();
// 	    try {
// 	        $data = array(
// 	            'instance_id' => 0,
// 	            'ap_name' => $params['ap_name'],
// 	            'ap_intro' => $params['ap_intro'],
// 	            'ap_class' => 0,
// 	            'ap_display' => $params['ap_display'],
// 	            'is_use' => $params['is_use'],
// 	            'ap_height' => $params['ap_height'],
// 	            'ap_width' => $params['ap_width'],
// 	            'default_content' => '',
// 	            'ap_background_color' => '',
// 	            'type' => $params['type'],
// 	            'ap_keyword' => $params['ap_keyword'],
// 	            'layout' => $params['layout']
// 	        );
	        	
// 	        if(empty($params['ap_id'])){
// 	            $count = $platform_adv_position->getCount(['ap_keyword' => $params['ap_keyword']]);
// 	            if($count > 0) return ['code' => -1, 'message' => '该关键字已存在'];
	             
// 	            $platform_adv_position->save($data);
// 	            $ap_id = $platform_adv_position->ap_id;
// 	        }else{
// 	            $ap_id = $params['ap_id'];
// 	            $count = $platform_adv_position->getCount(['ap_keyword' => $params['ap_keyword'], 'ap_id' => ['<>', $ap_id]]);
// 	            if($count > 0) return ['code' => -1, 'message' => '该关键字已存在'];
	             
// 	            $platform_adv->destroy(['ap_id' => $ap_id]);
// 	            $platform_adv_position->save($data, ['ap_id' => $ap_id]);
// 	        }
	        	
// 	        $adv_data = [];
// 	        foreach ($params['imgs'] as $item){
// 	            $item_data = [
// 	                'ap_id' => $ap_id,
// 	                'adv_title' => '',
// 	                'adv_url' => $item['url'],
// 	                'adv_image' => $item['imgPath'],
// 	                'slide_sort' => $item['sort'],
// 	                'background' => $item['bgColor'],
// 	                'adv_code' => ''
// 	            ];
// 	            array_push($adv_data, $item_data);
// 	        }
// 	        $platform_adv->saveAll($adv_data);
// 	        $platform_adv_position->commit();
// 	        Cache::clear("niu_adv");
// 	        return [
// 	            'code' => 1,
// 	            'message' => '添加成功'
// 	        ];
// 	    } catch (\Exception $e) {
// 	        $platform_adv_position->rollback();
// 	        return [
// 	            'code' => -1,
// 	            'message' => $e->getMessage()
// 	        ];
// 	    }
// 	}
	
	/**
	 * 删除平台广告
	 * @param unknown $adv_id
	 */
// 	public function deletePlatformAdv($adv_id)
// 	{
// 	    Cache::clear("niu_adv");
// 	    $platform_adv = new NsPlatformAdvModel();
// 	    $res = $platform_adv->destroy($adv_id);
// 	    return $res;
// 	}
	/**
	 * 删除平台广告位
	 * @param unknown $ap_id
	 */
// 	public function delPlatfromAdvPosition($ap_id)
// 	{
// 	    Cache::clear('niu_adv');
// 	    $platform_adv = new NsPlatformAdvModel();
// 	    $platform_adv_position = new NsPlatformAdvPositionModel();
// 	    $platform_adv_position->startTrans();
// 	    try {
// 	        $position_detail = $this->getPlatformAdvPositionDetail($ap_id);
// 	        if (empty($position_detail['is_del'])) {
// 	            $platform_adv->destroy([
// 	                'ap_id' => $ap_id
// 	            ]);
// 	            $res = $platform_adv_position->destroy($ap_id);
// 	        } else {
// 	            $res = -1;
// 	        }
	        	
// 	        $platform_adv_position->commit();
// 	    } catch (\Exception $e) {
// 	        $platform_adv_position->rollback();
// 	        return $e->getMessage();
// 	    }
	
// 	    return $res;
// 	}
	
	/**
	 * 设置广告位是否使用
	 * @param unknown $ap_id
	 * @param unknown $is_use
	 * @return boolean
	 */
// 	public function setPlatformAdvPositionUse($ap_id, $is_use)
// 	{
// 	    Cache::clear('niu_adv');
// 	    $platform_adv_position = new NsPlatformAdvPositionModel();
// 	    $data = array(
// 	        'is_use' => $is_use
// 	    );
// 	    $res = $platform_adv_position->save($data, [
// 	        'ap_id' => $ap_id
// 	    ]);
// 	    return $res;
// 	}
	/**
	 * 修改广告排序
	 * @param unknown $adv_id
	 * @param unknown $slide_sort
	 */
// 	public function updateAdvSlideSort($adv_id, $slide_sort)
// 	{
// 	    Cache::clear("niu_adv");
// 	    $platform_adv = new NsPlatformAdvModel();
// 	    $data = array(
// 	        'adv_id' => $adv_id,
// 	        'slide_sort' => $slide_sort
// 	    );
// 	    $res = $platform_adv->save($data, [
// 	        'adv_id' => $adv_id
// 	    ]);
// 	    return $res;
// 	}
	
	/**
	 * 检测广告位关键字是否存在
	 *
	 * @param unknown $ap_keyword
	 * @return \data\model\unknown
	 */
// 	public function check_apKeyword_is_exists($ap_keyword, $ap_id = '')
// 	{
// 	    $platform_adv_position = new NsPlatformAdvPositionModel();
// 	    if(empty($ap_id)){
// 	        $is_exists = $platform_adv_position->getCount([
// 	            "ap_keyword" => $ap_keyword
// 	        ]);
// 	    }else{
// 	        $is_exists = $platform_adv_position->getCount([
// 	            "ap_keyword" => $ap_keyword,
// 	            "ap_id" => ['neq', $ap_id]
// 	        ]);
// 	    }
// 	    return $is_exists;
// 	}
	/**
	 * 获取平台广告位信息
	 * @param int $ap_id
	 * @return Ambigous <NULL, multitype:multitype:string unknown  >|mixed
	 */
// 	public function getPlatformAdvPositionDetail($ap_id)
// 	{
// 	    $cache = Cache::tag("niu_adv")->get("getPlatformAdvPositionDetail" . $ap_id);
// 	    if (empty($cache)) {
// 	        $platform_adv_position = new NsPlatformAdvPositionModel();
// 	        $info = $platform_adv_position->getInfo([
// 	            'ap_id' => $ap_id,
// 	            'is_use' => 1
// 	        ]);
	        	
// 	        if (!empty($info)) {
// 	            $platform_adv = new NsPlatformAdvModel();
// 	            $platform_adv_list = $platform_adv->getQuery([
// 	                'ap_id' => $info['ap_id']
// 	            ], '*', ' slide_sort ');
// 	            if (empty($platform_adv_list)) {
// 	                $platform_adv_list[0] = array(
// 	                    'adv_title' => $info['ap_name'] . '默认图',
// 	                    'adv_url' => '#',
// 	                    'adv_image' => $info['default_content'],
// 	                    'background' => '#FFFFFF',
// 	                    'adv_width' => $info['ap_width'],
// 	                    'adv_height' => $info['ap_height']
// 	                );
// 	            }
// 	            $info['adv_list'] = $platform_adv_list;
// 	        } else {
// 	            $info = null;
// 	        }
// 	        Cache::tag("niu_adv")->set("getPlatformAdvPositionDetail" . $ap_id, $info);
// 	        return $info;
// 	    } else {
// 	        return $cache;
// 	    }
// 	}
	

	/**
	 * 获取广告位详情
	 * @param unknown $condition
	 */
// 	public function getAdvPositionDetail($condition = []){
// 	    $cache = Cache::tag('niu_adv')->get('getAdvPositionDetail' . json_encode($condition));
// 	    if (!empty($cache)) return $cache;
	
// 	    $platform_adv_position = new NsPlatformAdvPositionModel();
// 	    $platform_adv = new NsPlatformAdvModel();
	
// 	    $info = $platform_adv_position->getInfo($condition);
// 	    if(!empty($info)){
// 	        $advs = $platform_adv->pageQuery(1, 0, ['ap_id' => $info['ap_id']], 'slide_sort asc', '*');
// 	        $info['advs'] = $advs['data'];
// 	    }
// 	    Cache::tag('niu_adv')->set('getAdvPositionDetail' . json_encode($condition), $info);
	
// 	    return $info;
// 	}
	
	/**
	 * 获取广告详情
	 * @param unknown $adv_id
	 */
// 	public function getPlatformAdDetail($adv_id)
// 	{
// 	    $cache = Cache::tag("niu_adv")->get("getPlatformAdDetail" . $adv_id);
// 	    if (!empty($cache)) {
// 	        return $cache;
// 	    }
// 	    $platform_adv = new NsPlatformAdvModel();
// 	    $info = $platform_adv->getInfo([
// 	        'adv_id' => $adv_id
// 	    ]);
// 	    if (!empty($info["adv_code"])) {
// 	        $info["adv_code"] = html_entity_decode($info["adv_code"]);
// 	    }
// 	    Cache::tag("niu_adv")->set("getPlatformAdDetail" . $adv_id, $info);
// 	    return $info;
// 	}
	
	/**
	 * 通过广告位关键字获取广告位详情
	 * {@inheritDoc}
	 */
// 	public function getPlatformAdvPositionDetailByApKeyword($ap_keyword)
// 	{
// 	    $cache = Cache::tag("niu_adv")->get("getPlatformAdvPositionDetailByApKeyword" . '_' . $ap_keyword);
// 	    if (!empty($cache)) {
// 	        return $cache;
// 	    }
// 	    $platform_adv_position = new NsPlatformAdvPositionModel();
// 	    $info = $platform_adv_position->getInfo([
// 	        'ap_keyword' => $ap_keyword,
// 	        'is_use' => 1
// 	    ]);
	
// 	    $platform_adv_list = array();
// 	    if (!empty($info)) {
// 	        $platform_adv = new NsPlatformAdvModel();
// 	        $platform_adv_list = $platform_adv->getQuery([
// 	            'ap_id' => $info['ap_id']
// 	        ], '*', ' slide_sort ');
// 	        if (empty($platform_adv_list)) {
// 	            $platform_adv_list[0] = array(
// 	                'adv_title' => $info['ap_name'] . '默认图',
// 	                'adv_url' => '#',
// 	                'adv_image' => $info['default_content'],
// 	                'background' => '#FFFFFF',
// 	                'adv_width' => $info['ap_width'],
// 	                'adv_height' => $info['ap_height']
// 	            );
// 	        }
// 	        $info['adv_list'] = $platform_adv_list;
// 	    } else {
// 	        $info = null;
// 	    }
// 	    Cache::tag("niu_adv")->set("getPlatformAdvPositionDetailByApKeyword" . '_' . $ap_keyword, $info);
// 	    return $info;
// 	}
	
	/**
	 * 获取平台广告列表
	 * @param number $page_index
	 * @param number $page_size
	 * @param string $where
	 * @param string $order
	 * @param string $field
	 */
// 	public function getPlatformAdvList($page_index = 1, $page_size = 0, $where = '', $order = '', $field = '*')
// 	{
// 		$data = [ $page_index, $page_size, $where, $order, $field ];
// 		$data = json_encode($data);
// 		$cache = Cache::tag("niu_adv")->get("getPlatformAdvList" . $data);
// 		if (!empty($cache)) {
// 			return $cache;
// 		}
// 		$platform_adv = new NsPlatformAdvModel();
// 		$result = $platform_adv->pageQuery($page_index, $page_size, $where, $order, $field);
// 		foreach ($result['data'] as $k => $v) {
// 			$platform_adv_position = new NsPlatformAdvPositionModel();
// 			$result['data'][ $k ]['ap_info'] = $platform_adv_position->get($v['ap_id']);
// 		}
// 		Cache::tag("niu_adv")->set("getPlatformAdvList" . $data, $result);
// 		return $result;
// 	}
	
	/**
	 * 获取平台广告位列表
	 * @param number $page_index
	 * @param number $page_size
	 * @param string $where
	 * @param string $order
	 * @param string $field
	 */
// 	public function getPlatformAdvPositionList($page_index = 1, $page_size = 0, $where = '', $order = '', $field = '*')
// 	{
// 		$data = [ $page_index, $page_size, $where, $order, $field ];
// 		$data = json_encode($data);
// 		$cache = Cache::tag("niu_adv")->get("getPlatformAdvPositionList" . $data);
// 		if (!empty($cache)) {
// 			return $cache;
// 		}
// 		$platform_adv_position = new NsPlatformAdvPositionModel();
// 		$result = $platform_adv_position->pageQuery($page_index, $page_size, $where, $order, $field);
// 		foreach ($result['data'] as $k => $v) {
// 			if ($v['ap_class'] == 0) {
// 				$result['data'][ $k ]['ap_class_name'] = '图片';
// 			} else if ($v['ap_class'] == 1) {
// 				$result['data'][ $k ]['ap_class_name'] = '文字';
// 			} else if ($v['ap_class'] == 2) {
// 				$result['data'][ $k ]['ap_class_name'] = '幻灯';
// 			} else if ($v['ap_class'] == 3) {
// 				$result['data'][ $k ]['ap_class_name'] = 'flash';
// 			} else if ($v['ap_class'] == 4) {
// 				$result['data'][ $k ]['ap_class_name'] = '代码';
// 			} else {
// 				$result['data'][ $k ]['ap_class_name'] = '';
// 			}
// 			if ($v['ap_display'] == 0) {
// 				$result['data'][ $k ]['ap_display_name'] = '幻灯片';
// 			} else if ($v['ap_display'] == 1) {
// 				$result['data'][ $k ]['ap_display_name'] = '多广告展示';
// 			} else if ($v['ap_display'] == 2) {
// 				$result['data'][ $k ]['ap_display_name'] = '单广告展示';
// 			} else {
// 				$result['data'][ $k ]['ap_display_name'] = '';
// 			}
// 		}
// 		Cache::tag("niu_adv")->set("getPlatformAdvPositionList" . $data, $result);
// 		return $result;
// 	}

	/**
	 * 后台获取广告列表
	 *
	 * @param number $page_index
	 * @param number $page_size
	 * @param string $where
	 * @param string $order
	 * @param string $field
	 */
// 	public function adminGetAdvList($page_index = 1, $page_size = 0, $condition = '', $order = '')
// 	{
// 	    $ns_platform_adv = new NsPlatformAdvViewModel();
// 	    $list = $ns_platform_adv->getViewList($page_index, $page_size, $condition, $order);
// 	    return $list;
// 	}
	/**********************************************广告管理结束************************************************************/
	/**********************************************帮助中心***************************************************************/
	

	/**
	 * 添加帮助分类
	 * @param unknown $type
	 * @param unknown $class_name
	 * @param unknown $parent_class_id
	 * @param unknown $sort
	 */
// 	public function addPlatformHelpClass($type, $class_name, $parent_class_id, $sort)
// 	{
// 	    Cache::clear("niu_platform_help");
// 	    $data = array(
// 	        'type' => $type,
// 	        'class_name' => $class_name,
// 	        'parent_class_id' => $parent_class_id,
// 	        'sort' => $sort
// 	    );
// 	    $platform_class = new NsPlatformHelpClassModel();
// 	    $platform_class->save($data);
// 	    return $platform_class->class_id;
// 	    // TODO Auto-generated method stub
// 	}
	
	/**
	 * 修改帮助分类
	 * @param unknown $class_id
	 * @param unknown $type
	 * @param unknown $class_name
	 * @param unknown $parent_class_id
	 * @param unknown $sort
	 */
// 	public function updatePlatformClass($class_id, $type, $class_name, $parent_class_id, $sort)
// 	{
// 	    Cache::clear("niu_platform_help");
// 	    $data = array(
// 	        'type' => $type,
// 	        'class_name' => $class_name,
// 	        'parent_class_id' => $parent_class_id,
// 	        'sort' => $sort
// 	    );
// 	    $platform_class = new NsPlatformHelpClassModel();
// 	    $retval = $platform_class->save($data, [
// 	        'class_id' => $class_id
// 	    ]);
// 	    return $retval;
// 	    // TODO Auto-generated method stub
// 	}
	

	/**
	 * 添加帮助内容
	 * @param unknown $uid
	 * @param unknown $class_id
	 * @param unknown $title
	 * @param unknown $link_url
	 * @param unknown $is_visibility
	 * @param unknown $sort
	 * @param unknown $content
	 * @param unknown $image
	 */
// 	public function addPlatformDocument($uid, $class_id, $title, $link_url, $is_visibility, $sort, $content, $image)
// 	{
// 	    Cache::clear("niu_platform_help");
// 	    $data = array(
// 	        'uid' => $uid,
// 	        'class_id' => $class_id,
// 	        'title' => $title,
// 	        'link_url' => $link_url,
// 	        'is_visibility' => $is_visibility,
// 	        'sort' => $sort,
// 	        'content' => $content,
// 	        'image' => $image,
// 	        'create_time' => time(),
// 	        'modufy_time' => time()
// 	    );
// 	    $platform_document = new NsPlatformHelpDocumentModel();
// 	    $platform_document->save($data);
// 	    return $platform_document->id;
// 	    // TODO Auto-generated method stub
// 	}
	
	
	/**
	 * 修改帮助内容
	 * @param unknown $id
	 * @param unknown $uid
	 * @param unknown $class_id
	 * @param unknown $title
	 * @param unknown $link_url
	 * @param unknown $is_visibility
	 * @param unknown $sort
	 * @param unknown $content
	 * @param unknown $image
	 * @return boolean
	 */
// 	public function updatePlatformDocument($id, $uid, $class_id, $title, $link_url, $is_visibility, $sort, $content, $image)
// 	{
// 	    Cache::clear("niu_platform_help");
// 	    $data = array(
// 	        'uid' => $uid,
// 	        'class_id' => $class_id,
// 	        'title' => $title,
// 	        'link_url' => $link_url,
// 	        'sort' => $sort,
// 	        'is_visibility' => $is_visibility,
// 	        'content' => $content,
// 	        'image' => $image,
// 	        'modufy_time' => time()
// 	    );
// 	    $platform_document = new NsPlatformHelpDocumentModel();
// 	    $retval = $platform_document->save($data, [
// 	        'id' => $id
// 	    ]);
// 	    return $retval;
// 	    // TODO Auto-generated method stub
// 	}
	
	/**
	 * 删除帮助分类
	 *
	 * @param unknown $class_id
	 */
// 	public function deleteHelpClass($class_id)
// 	{
// 	    Cache::clear("niu_platform_help");
// 	    $platform_class = new NsPlatformHelpClassModel();
// 	    $platform_class->startTrans();
// 	    try {
// 	        $retval = $platform_class->destroy($class_id);
// 	        $this->deleteHelpClassTitle($class_id);
// 	        $platform_class->commit();
// 	        return 1;
// 	    } catch (\Exception $e) {
// 	        $platform_class->rollback();
// 	        return $e->getMessage();
// 	    }
	
// 	    return $retval;
// 	}
	
	/**
	 * 删除帮助主题
	 * @param unknown $id
	 */
// 	public function deleteHelpTitle($id)
// 	{
// 	    Cache::clear("niu_platform_help");
// 	    $platform_document = new NsPlatformHelpDocumentModel();
// 	    $retval = $platform_document->destroy($id);
// 	    return $retval;
// 	    // TODO Auto-generated method stub
// 	}
	
	
	
	/**
	 * 删除帮助内容
	 * @param unknown $class_id
	 * @return number
	 */
// 	public function deleteHelpClassTitle($class_id)
// 	{
// 	    Cache::clear("niu_platform_help");
// 	    $platform_document = new NsPlatformHelpDocumentModel();
// 	    $retval = $platform_document->destroy([
// 	        'class_id' => $class_id
// 	    ]);
// 	    return $retval;
// 	}

	/**
	 * 修改帮助中心内容的标题与排序
	 */
// 	public function updatePlatformDocumentTitleAndSort($id, $title, $sort)
// 	{
// 	    Cache::clear("niu_platform_help");
// 	    $data = array(
// 	        'title' => $title,
// 	        'sort' => $sort
// 	    );
// 	    $platform_document = new NsPlatformHelpDocumentModel();
// 	    $retval = $platform_document->save($data, [
// 	        'id' => $id
// 	    ]);
// 	    return $retval;
// 	}
	
	/**
	 * 获取帮助内容详情
	 * @param unknown $id
	 */
// 	public function getPlatformDocumentDetail($id)
// 	{
// 	    $cache = Cache::tag("niu_platform_help")->get("getPlatformDocumentDetail" . $id);
// 	    if (empty($cache)) {
// 	        $platform_document = new NsPlatformHelpDocumentModel();
// 	        $data = $platform_document->get($id);
// 	        Cache::tag("niu_platform_help")->set("getPlatformDocumentDetail" . $id, $data);
// 	        return $data;
// 	    } else {
// 	        return $cache;
// 	    }
// 	}
	
	/**
	 * 获取帮助分类详情
	 * @param unknown $class_id
	 * @return \think\static|mixed
	 */
// 	public function getPlatformClassDetail($class_id)
// 	{
// 	    $cache = Cache::tag("niu_platform_help")->get("getPlatformClassDetail" . $class_id);
// 	    if (empty($cache)) {
// 	        $platform_class = new NsPlatformHelpClassModel();
// 	        $data = $platform_class->get($class_id);
// 	        Cache::tag("niu_platform_help")->set("getPlatformClassDetail" . $class_id, $data);
// 	        return $data;
// 	    } else {
// 	        return $cache;
// 	    }
// 	}
	
	/**
	 * 获取帮助列表
	 * @param number $page_index
	 * @param number $page_size
	 * @param string $where
	 * @param string $order
	 * @param string $field
	 * @return multitype:number unknown |mixed
	 */
// 	public function getPlatformHelpClassList($page_index = 1, $page_size = 0, $where = '', $order = '', $field = '*')
// 	{
// 		$data = array(
// 			$page_index,
// 			$page_size,
// 			$where,
// 			$order,
// 			$field
// 		);
// 		$data = json_encode($data);
// 		$cache = Cache::tag("niu_platform_help")->get("getPlatformHelpClassList" . $data);
// 		if (empty($cache)) {
// 			$platform_class = new NsPlatformHelpClassModel();
// 			$list = $platform_class->pageQuery($page_index, $page_size, $where, $order, $field);
// 			Cache::tag("niu_platform_help")->set("getPlatformHelpClassList" . $data, $list);
// 			return $list;
// 		} else {
// 			return $cache;
// 		}
		
// 		// TODO Auto-generated method stub
// 	}
	
	/**
	 * 获取帮助内容列表
	 * @param number $page_index
	 * @param number $page_size
	 * @param string $where
	 * @param string $order
	 * @param string $field
	 */
// 	public function getPlatformHelpDocumentList($page_index = 1, $page_size = 0, $where = '', $order = '', $field = '*')
// 	{
// 		$data = array(
// 			$page_index,
// 			$page_size,
// 			$where,
// 			$order,
// 			$field
// 		);
// 		$data = json_encode($data);
// 		$cache = Cache::tag("niu_platform_help")->get("getPlatformHelpDocumentList" . $data);
// 		if (empty($cache)) {
// 			$platform_document = new NsPlatformHelpDocumentModel();
// 			$list = $platform_document->getPlatformHelpDocumentViewList($page_index, $page_size, $where, $order);
// 			Cache::tag("niu_platform_help")->set("getPlatformHelpDocumentList" . $data, $list);
// 			return $list;
// 		} else {
// 			return $cache;
// 		}
		
// 		// TODO Auto-generated method stub
// 	}
	
	/**********************************************帮助中心结束*************************************************************/
	/**********************************************推荐商品列表*************************************************************/
	/**
	 * 获取店铺推荐商品列表
	 * @param unknown $shop_id
	 * @param number $show_num
	 * @return multitype:\data\model\unknown
	 */
// 	public function getRecommendGoodsList($shop_id, $show_num = 4)
// 	{
// 		// TODO Auto-generated method stub
// 		$group = new NsGoodsGroupModel();
// 		$goods = new NsGoodsViewModel();
		
// 		$group_list = $group->getQuery([
// 			"shop_id" => $shop_id
// 		], "*", "sort asc");
// 		foreach ($group_list as $k => $v) {
// 			$group_goods_list = array();
// 			$goods_list = $goods->getGoodsViewList(1, $show_num, "FIND_IN_SET(" . $v["group_id"] . ",ng.group_id_array) AND ng.state = 1", "ng.sort asc");
// 			// var_dump($goods_list);
// 			if (!empty($goods_list["data"])) {
// 				foreach ($goods_list["data"] as $t => $m) {
// 					$is_exist = true;
// 					foreach ($group_goods_list as $q => $w) {
// 						if ($w["goods_id"] == $m["goods_id"]) {
// 							$is_exist = false;
// 							break;
// 						}
// 					}
// 					if ($is_exist) {
// 						$group_goods_list[] = $m;
// 					}
// 				}
// 			}
// 			$group_list[ $k ]["goods_list"] = $group_goods_list;
// 		}
// 		return $group_list;
// 	}
	/**********************************************推荐商品列表*************************************************************/
	/**********************************************公告管理****************************************************************/
	/**
	 * 添加或修改公告
	 *
	 * @param unknown $notice_title
	 * @param unknown $notice_content
	 * @param unknown $shop_id
	 * @param unknown $sort
	 * @param unknown $id
	 */
// 	public function addOrModifyNotice($notice_title, $notice_content, $shop_id, $sort, $id)
// 	{
// 	    Cache::clear("niu_notice");
// 	    $data = array(
// 	        "notice_title" => $notice_title,
// 	        "notice_content" => $notice_content,
// 	        "shop_id" => $shop_id,
// 	        "sort" => $sort
// 	    );
// 	    $notice = new NsNoticeModel();
// 	    if ($id == 0) {
// 	        $data["create_time"] = time();
// 	        return $notice->save($data);
// 	    } else
// 	        if ($id > 0) {
// 	            $data["modify_time"] = time();
// 	            return $notice->save($data, [
// 	                "id" => $id
// 	            ]);
// 	        }
// 	}
	
	/**
	 * 删除公告
	 */
// 	public function deleteNotice($id)
// 	{
// 	    Cache::clear("niu_notice");
// 	    $notice = new NsNoticeModel();
// 	    $retval = $notice->destroy($id);
// 	    return $retval;
// 	}
	

	/**
	 * 更改公告排序
	 */
// 	public function updateNoticeSort($sort, $id)
// 	{
// 	    Cache::clear("niu_notice");
// 	    $notice = new NsNoticeModel();
// 	    $retval = $notice->save([
// 	        'sort' => $sort
// 	    ], [
// 	        'id' => $id
// 	    ]);
// 	    return $retval;
// 	}
	
	/**
	 * 获取公告详情
	 *
	 * @param int $id
	 */
// 	public function getNoticeDetail($id)
// 	{
// 	    $cache = Cache::tag("niu_notice")->get("getNoticeDetail" . $id);
// 	    if (empty($cache)) {
// 	        $notice = new NsNoticeModel();
// 	        $res = $notice->getInfo([
// 	            "id" => $id
// 	        ]);
// 	        Cache::tag("niu_notice")->set("getNoticeDetail" . $id, $res);
// 	        return $res;
// 	    } else {
// 	        return $cache;
// 	    }
// 	}
	
	/**
	 * 分页获取公告列表
	 *
	 * @param unknown $page_index
	 * @param unknown $page_size
	 * @param unknown $condition
	 * @param string $order
	 * @param string $field
	 * @return number[]|unknown[]
	 */
// 	public function getNoticeList($page_index, $page_size, $condition, $order = "", $field = "*")
// 	{
// 		$data = array(
// 			$page_index,
// 			$page_size,
// 			$condition,
// 			$order,
// 			$field
// 		);
// 		$data = json_encode($data);
		
// 		$cache = Cache::tag("niu_notice")->get("getNoticeList" . $data);
// 		if (empty($cache)) {
// 			$notice = new NsNoticeModel();
// 			$list = $notice->pageQuery($page_index, $page_size, $condition, $order, $field);
// 			Cache::tag("niu_notice")->set("getNoticeList" . $data, $list);
// 			return $list;
// 		} else {
// 			return $cache;
// 		}
// 	}
	
	/**********************************************************公告管理结束*************************************************/
	
}