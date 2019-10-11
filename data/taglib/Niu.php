<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace data\taglib;

use data\service\Config;
use data\service\Member as MemberService;
use data\service\WebSite;
use think\template\TagLib;

class Niu extends TagLib
{
	/**
	 * 定义标签列表
	 */
	protected $tags = [
		// 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
		//专题
		'topicinfo' => [ 'attr' => 'id,cache,name', 'close' => 1 ],//专题详情
		//文章中心
		'articleclasslist' => [ 'attr' => 'page,num,where,order,field,cache', 'close' => 1 ],//分类列表
		'articlelist' => [ 'attr' => 'page,num,where,order,field,cache', 'close' => 1 ],//列表
		'articleinfo' => [ 'attr' => 'id,field,cache,name', 'close' => 1 ],//详情
		//帮助中心
		'helpclasslist' => [ 'attr' => 'page,num,where,order,field,cache', 'close' => 1 ],//帮助中心分类列表
		'helpdocumentlist' => [ 'attr' => 'page,num,where,order,field,cache', 'close' => 1 ],//帮助中心内容列表
		'helpdocumentinfo' => [ 'attr' => 'id,field,cache,name', 'close' => 1 ],//帮助中心内容详情
		//公告
		'noticelist' => [ 'attr' => 'page,num,where,order,field,cache', 'close' => 1 ],//公告列表
		'noticeinfo' => [ 'attr' => 'id,field,cache,name', 'close' => 1 ],//公告详情
		//网站基础信息
		'defaultsearch' => [ 'attr' => '', 'close' => 0 ],//默认搜索
		'hotsearch' => [ 'attr' => 'name', 'close' => 1 ],//热门搜索
		'webname' => [ 'attr' => '', 'close' => 0 ],//网站名称
		'weburl' => [ 'attr' => '', 'close' => 0 ],//官方网址
		'webaddress' => [ 'attr' => '', 'close' => 0 ],//联系地址
		'webqrcode' => [ 'attr' => '', 'close' => 0 ],//网站二维码
		'webdesc' => [ 'attr' => '', 'close' => 0 ],//网站描述
		'weblogo' => [ 'attr' => '', 'close' => 0 ],//网站logo
		'webwechatqrcode' => [ 'attr' => '', 'close' => 0 ],//网站公众号二维码
		'webkeywords' => [ 'attr' => '', 'close' => 0 ],//网站关键字
		'webphone' => [ 'attr' => '', 'close' => 0 ],//网站联系电话
		'webemail' => [ 'attr' => '', 'close' => 0 ],//网站邮箱
		'webqq' => [ 'attr' => '', 'close' => 0 ],//网站qq
		'webwechat' => [ 'attr' => '', 'close' => 0 ],//网站微信号
		'webicp' => [ 'attr' => '', 'close' => 0 ],//网站备案号
		'webclosereason' => [ 'attr' => '', 'close' => 0 ],//网站关闭原因
		'webcount' => [ 'attr' => '', 'close' => 0 ],//网站第三方统计代码
		'webwechatsharelogo' => [ 'attr' => '', 'close' => 0 ],//
		'goodslist' => [ 'attr' => 'page,num,where,order,field,cache,name', 'close' => 1 ],//
		'goodsviewlist' => [ 'attr' => 'page,num,where,order,field,cache,name', 'close' => 1 ],//直接查询商品列表
		'memberhistory' => [ 'attr' => 'cache,name', 'close' => 1 ],//会员浏览历史
		'memberlikes' => [ 'attr' => 'cache,name', 'close' => 1 ],//猜你喜欢
		
		
		'goodsinfo' => [ 'attr' => 'id,cache,name', 'close' => 1 ],//商品详情
		'categorylist' => [ 'attr' => 'page,num,where,order,field,cache', 'close' => 1 ],
		'categorytree' => [ 'attr' => 'page,num,where,order,field,cache', 'close' => 1 ],
		'brandlist' => [ 'attr' => 'page,num,where,order,field,cache', 'close' => 1 ],
		'brandinfo' => [ 'attr' => 'id,field,cache,name', 'close' => 1 ],
		
		
		'adv' => [ 'attr' => 'id,field,cache,name', 'close' => 1 ],
		'blocklist' => [ 'attr' => 'page,num,where,order,field,cache', 'close' => 1 ],//首页版块列表
		'blockinfo' => [ 'attr' => 'id,field,cache,name', 'close' => 1 ],//首页版块详情
		
		'linklist' => [ 'attr' => 'page,num,where,order,field,cache', 'close' => 1 ],//友情链接列表
		'categoryblock' => [ 'attr' => 'name,cache', 'close' => 1 ],//分类楼层
		'navigation' => [ 'attr' => 'page,num,where,order,field,cache', 'close' => 1 ],
		
		
		//商品列表筛选条件
		'categorypricegrades' => [ 'attr' => 'id,cache,name', 'close' => 1 ],//价格区间
		'categorybrands' => [ 'attr' => 'id,cache,name', 'close' => 1 ],//品牌
		
		
		//订单
		'orderlist' => [ 'attr' => 'page,num,where,order,cache', 'close' => 1 ],//订单列表
		'orderdetail' => [ 'attr' => 'id,cache,name', 'close' => 1 ],//订单详情
		'ordersum' => [ 'attr' => 'cache,name', 'close' => 1 ],//订单数量（待支付、待收货...）
		'shopconfig' => [ 'attr' => 'id,cache,name', 'close' => 1 ],//店铺设置
		'ordernumber' => [ 'attr' => 'id,cache,name', 'close' => 1 ],//订单号
		
		//购物车
		'cartlist' => [ 'attr' => 'uid,shop_id,cache', 'close' => 1 ],//获取购物车
		
		//首页活动商品
		'recommendgoodslist' => [ 'attr' => 'name,cache', 'close' => 1 ],//首页促销商品
		'discountgoodslist' => [ 'attr' => 'page,num,where,order,name,cache', 'close' => 1 ],//首页限时折扣商品列表
		
		//会员信息
		'addresslist' => [ 'attr' => 'page,num,where,order,name,cache', 'close' => 1 ],//收货地址
		'accountlist' => [ 'attr' => 'page,num,where,order,file,cache', 'close' => 1 ],//余额积分流水
		'memberaccount' => [ 'attr' => 'id,account_type,start_time,end_time,name,cache', 'close' => 1 ],//一段时间内会员账户（积分或余额）account_type：1.积分2.余额3.购物币
		'memberbankaccountlist' => [ 'attr' => 'is_default,name,cache', 'close' => 1 ],//会员提现账户列表
		
		
		'memberimg' => [ 'attr' => '', 'close' => 0 ],//会员头像
		'membernickname' => [ 'attr' => '', 'close' => 0 ],//会员昵称
		'memberrealname' => [ 'attr' => '', 'close' => 0 ],//会员真实姓名
		'memberbirthday' => [ 'attr' => '', 'close' => 0 ],//会员出生年月
		'membersex' => [ 'attr' => '', 'close' => 0 ],//会员性别
		'memberlocation' => [ 'attr' => '', 'close' => 0 ],//会员地址
		'memberqq' => [ 'attr' => '', 'close' => 0 ],//会员qq
		'memberinfo' => [ 'attr' => 'name,cache', 'close' => 1 ],//会员详情
		
		
		'membergoodsfavoriteslist' => [ 'attr' => 'page,num,where,order,name,cache', 'close' => 1 ],//会员收藏列表
		'membercounponlist' => [ 'attr' => 'type,name,cache', 'close' => 1 ],//会员优惠券 type:1已领取（未使用） 2已使用 3已过期
		
		
		'orderevaluatedatalist' => [ 'attr' => 'page,num,where,order,name,cache', 'close' => 1 ],// 评价信息 分页
		
		
		'convertrate' => [ 'attr' => 'id,name,cache', 'close' => 1 ],//获取兑换比例
		'withdrawlist' => [ 'attr' => 'page,num,where,name,order,cache', 'close' => 1 ],//余额提现记录
		
		'usernotice' => [ 'attr' => '', 'close' => 0 ],//获取用户通知
		'goodscoupon' => [ 'attr' => 'goods_id,uid,name,cache', 'close' => 1 ],//商品优惠券
		'categoryparentquery' => [ 'attr' => 'id,name,cache', 'close' => 1 ],//获取分类的父级分类
		'evaluatecount' => [ 'attr' => 'id,name,cache', 'close' => 1 ],//评价数量
		'consultlist' => [ 'attr' => 'page,num,where,order,name,cache', 'close' => 1 ],//购买咨询
		
		//=============================wap============================//
		'notice' => [ 'attr' => 'id,name,cache', 'close' => 1 ],//首页公告
		'couponlist' => [ 'attr' => 'shop_id,uid,name,cache', 'close' => 1 ],//优惠券列表
		'platformlist' => [ 'attr' => 'id,num,name,cache', 'close' => 1 ],//首页新品推荐列表
		'indexblocklist' => [ 'attr' => 'id,num,name,cache', 'close' => 1 ],//首页楼层版块
		'currenttime' => [ 'attr' => '', 'close' => 0 ],//当前时间戳
		'withdrawconfig' => [ 'arrt' => 'id,name,cache', 'close' => 1 ],//会员提现设置
		'pointconfig' => [ 'attr' => 'name,cache', 'close' => 1 ],//积分配置信息
		'cmstype' => [ 'attr' => 'name,cache', 'close' => 1 ],//cms分类
		'formatcategorylist' => [ 'attr' => 'name,cache', 'close' => 1 ],//获取格式化后的商品分类
		'isfavorites' => [ 'attr' => 'uid,goods_id,name,cache', 'close' => 1 ],//是否收藏了该商品
		'spotfabulous' => [ 'attr' => 'uid,goods_id,name,cache', 'close' => 1 ],//点赞
		'customserviceconfig' => [ 'attr' => 'id,name,cache', 'close' => 1 ],//美洽客服
		'ticket' => [ 'attr' => 'name,cache', 'colse' => 1 ],//获取分享相关票据
		'instancewchatconfig' => [ 'attr' => 'name,cache,id', 'close' => 1 ],//获取微信配置
		'checkuserissubscribeinstance' => [ 'attr' => 'name,cache,uid,shop_id', 'close' => 1 ],//检测用户是否关注了实例公众号
		'issubscribe' => [ 'attr' => 'name,uid,shop_id', 'close' => 1 ],//标识：是否显示顶部关注  0：[隐藏]，1：[显示]
		'userinfobyuid' => [ 'attr' => 'name,cache,id', 'close' => 1 ],//根据uid查询用户信息
		'integralconfig' => [ 'attr' => 'id,name,cache', 'close' => 1 ],//送积分配置  签到 注册 分享
		'ismembersign' => [ 'attr' => 'uid,shop_id,name,cache', 'close' => 1 ],//是否签到
		'ordernumbyorderstatu' => [ 'attr' => 'where,name,cache', 'close' => 1 ],//订单状态下的订单数目
		'memberexpressaddressdetail' => [ 'attr' => 'id,name,cache', 'close' => 1 ],//会员收货地址详情
		'shopaccountlistbyuser' => [ 'attr' => 'id,num,page,name,cache', 'close' => 1 ],//分页获取用户积分和余额
		'websiteinfo' => [ 'attr' => 'name,cache', 'close' => 1 ],//网站信息
		'shopinfo' => [ 'attr' => 'name,cache,id,file', 'close' => 1 ],//店铺详情
		'ordergoodsrefundinfo' => [ 'attr' => 'id,name,cache', 'close' => 1 ],//查询订单项退款信息
		'ordergoodsrefundmoney' => [ 'attr' => 'id,name,cache', 'close' => 1 ],//查询订单实际退金额
		'ordergoodsrefundbalance' => [ 'attr' => 'id,name,cache', 'close' => 1 ],//获取订单项实际可退款余额
		'defaultshopexpressaddress' => [ 'attr' => 'id,name,cache', 'close' => 1 ],//获取公司默认收货地址
		'shopreturnset' => [ 'attr' => 'id,name,cache', 'close' => 1 ],//查询店铺的退货设置
		
		
		//拼团
		'goodspellinglist' => [ 'attr' => 'name,page,num,where,field, order, cache', 'close' => 1 ],//商品拼单列表
		'gettuangougoodslist' => [ 'attr' => 'page,num,where,field,order,cache,name', 'close' => 1 ],//拼团商品列表
		'gettuangoudetail' => [ 'attr' => 'name,cache,where', 'close' => 1 ],//拼团商品列表
		
		'advkeyword' => [ 'attr' => 'keyword,field,cache,name', 'close' => 1 ]
	];
	
	/**
	 * 根据拼团id查询详情
	 */
	public function tagGetTuangouDetail($tag, $content)
	{
		
		$group_id = isset($tag['group_id']) ? $tag['group_id'] : '';
		$goods_id = isset($tag['goods_id']) ? $tag['goods_id'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Pintuan";
		$function_array = [ 'getTuangouDetail', $group_id, $goods_id ];
		$function = 'getTuangouDetail("' . $group_id . '","' . $goods_id . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 查询拼团商品列表
	 */
	public function tagGetTuangouGoodsList($tag, $content)
	{
		
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Pintuan";
		$function_array = [ 'getTuangouGoodsList', $page, $num, $where, $order, $field ];
		$function = 'getTuangouGoodsList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '","' . $field . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 商品拼单列表
	 */
	public function tagGoodSpellingList($tag, $content)
	{
		
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Pintuan";
		$function_array = [ 'getGoodsPintuanStatusList', $page, $num, $where, $order, $field ];
		$function = 'getGoodsPintuanStatusList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '","' . $field . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	
	/**
	 * 查询店铺的退货设置
	 */
	public function tagShopReturnSet($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "OrderRefund";
		$function_array = [ 'getShopReturnSet', $id ];
		$function = 'getShopReturnSet("' . $id . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 获取公司默认收货地址
	 */
	public function tagDefaultshopexpressaddress($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Express";
		$function_array = [ 'getDefaultShopExpressAddress'];
		$function = 'getDefaultShopExpressAddress()';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 获取订单项实际可退款余额
	 */
	public function tagOrderGoodsRefundBalance($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "OrderQuery";
		$service_folder = "";
		$function_array = [ 'orderGoodsRefundBalance', $id ];
		$function = 'orderGoodsRefundBalance("' . $id . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array, $service_folder);
	}
	
	/**
	 * 查询订单项退款信息
	 */
	public function tagOrdergoodsrefundmoney($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Order";
		$function_array = [ 'orderGoodsRefundMoney', $id ];
		$function = 'orderGoodsRefundMoney("' . $id . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 查询订单项退款信息
	 */
	public function tagOrdergoodsrefundinfo($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "OrderQuery";
		$function_array = [ 'getOrderGoodsRefundInfo', $id ];
		$function = 'getOrderGoodsRefundInfo("' . $id . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * shopinfo
	 */
	public function tagShopinfo($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$file = isset($tag['file']) ? $tag['file'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Shop";
		$function_array = [ 'getShopInfo', $id, $file ];
		$function = 'getShopInfo("' . $id . '","' . $file . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 分页获取用户积分和余额
	 */
	public function tagWebsiteinfo($tag, $content)
	{
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "WebSite";
		$function_array = [ 'getWebSiteInfo' ];
		$function = 'getWebSiteInfo()';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 分页获取用户积分和余额
	 */
	public function tagShopaccountListbyuser($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$num = isset($tag['num']) ? $tag['num'] : '0';
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Member";
		$function_array = [ 'getShopAccountListByUser', $id, $num, $page ];
		$function = 'getShopAccountListByUser("' . $id . '","' . $page . '","' . $num . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 订单状态下的订单数目
	 */
	public function tagMemberexpressaddressdetail($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Member";
		$function_array = [ 'getMemberExpressAddressDetail', $id ];
		$function = 'getMemberExpressAddressDetail("' . $id . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 订单状态下的订单数目
	 */
	public function tagOrderNumbyorderstatu($tag, $content)
	{
		$where = isset($tag['where']) ? $tag['where'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "OrderQuery";
		$function_array = [ 'getOrderNumByOrderStatu', $where ];
		$function = 'getOrderNumByOrderStatu("' . $where . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 是否签到
	 */
	public function tagIsmembersign($tag, $content)
	{
		$shop_id = isset($tag['shop_id']) ? $tag['shop_id'] : '0';
		$uid = isset($tag['uid']) ? $tag['uid'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Member";
		$function_array = [ 'getIsMemberSign', $uid, $shop_id ];
		$function = 'getIsMemberSign("' . $uid . '","' . $shop_id . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 根据uid查询用户信息
	 */
	public function tagUserinfobyuid($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "User";
		$function_array = [ 'getUserInfoByUid', $id, ];
		$function = 'getUserInfoByUid("' . $id . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 检测用户是否关注了实例公众号
	 */
	public function tagCheckuserissubscribeinstance($tag, $content)
	{
		$uid = isset($tag['uid']) ? $tag['uid'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "User";
		$function_array = [ 'checkUserIsSubscribeInstance', $uid ];
		$function = 'checkUserIsSubscribeInstance("' . $uid . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 获取微信配置
	 */
	public function tagInstancewchatconfig($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '0';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Config";
		$function_array = [ 'getInstanceWchatConfig', $id ];
		$function = 'getInstanceWchatConfig("' . $id . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 购买咨询
	 */
	public function tagConsultList($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Goods";
		$function_array = [ 'getConsultList', $page, $num, $where, $order ];
		$function = 'getConsultList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 *美洽客服
	 */
	public function tagCustomserviceconfig($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '0';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Config";
		$function_array = [ 'getCustomServiceConfig', $id ];
		$function = 'getCustomServiceConfig("' . $id . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 *查询点赞记录表，获取详情再判断当天该店铺下该商品该会员是否已点赞
	 */
	public function tagSpotfabulous($tag, $content)
	{
		$uid = isset($tag['uid']) ? $tag['uid'] : '';
		$goods_id = isset($tag['goods_id']) ? $tag['goods_id'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Goods";
		$function_array = [ 'getGoodsSpotFabulous', $uid, $goods_id ];
		$function = 'getGoodsSpotFabulous("' . $uid . '","' . $goods_id . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 *是否收藏了该商品
	 */
	public function tagIsfavorites($tag, $content)
	{
		$uid = isset($tag['uid']) ? $tag['uid'] : '';
		$goods_id = isset($tag['goods_id']) ? $tag['goods_id'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Member";
		$function_array = [ 'getIsMemberFavorites', $uid, $goods_id ];
		$function = 'getIsMemberFavorites("' . $uid . '","' . $goods_id . '","goods")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 *获取格式化后的商品分类
	 */
	public function tagFormatcategorylist($tag, $content)
	{
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "GoodsCategory";
		$function_array = [ 'getFormatGoodsCategoryList' ];
		$function = 'getFormatGoodsCategoryList()';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 *cms分类
	 */
	public function tagCmstype($tag, $content)
	{
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Article";
		$function_array = [ 'getArticleClassQuery' ];
		$function = 'getArticleClassQuery()';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 *积分配置信息
	 */
	public function tagPointconfig($tag, $content)
	{
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Promotion";
		$function_array = [ 'getPointConfig' ];
		$function = 'getPointConfig()';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 *会员提现配置
	 */
	public function tagWithdrawconfig($tag, $content)
	{
		$shop_id = isset($tag['id']) ? $tag['id'] : '0';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Config";
		$function_array = [ 'getBalanceWithdrawConfig', $shop_id ];
		$function = 'getBalanceWithdrawConfig("' . $shop_id . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 *当前时间戳
	 */
	public function tagCurrenttime($tag, $content)
	{
		$time = time();
		return $time;
	}
	
	/**
	 *优惠券
	 */
	public function tagCouponlist($tag, $content)
	{
		$uid = isset($tag['uid']) ? $tag['uid'] : '';
		$shop_id = isset($tag['shop_id']) ? $tag['shop_id'] : '0';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Member";
		$function_array = [ 'getMemberCouponTypeList', $shop_id, $uid ];
		$function = 'getMemberCouponTypeList("' . $shop_id . '","' . $uid . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 *首页公告
	 */
	public function tagNotice($tag, $content)
	{
		$shop_id = isset($tag['id']) ? $tag['id'] : '0';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Config";
		$function_array = [ 'getNotice', $shop_id ];
		$function = 'getNotice("' . $shop_id . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 评价数量
	 * @evaluate_count总数量 @imgs_count带图的数量 @praise_count好评数量 @center_count中评数量 bad_count差评数量
	 */
	public function tagEvaluatecount($tag, $content)
	{
		$goods_id = isset($tag['id']) ? $tag['id'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Goods";
		$function_array = [ 'getGoodsEvaluateCount', $goods_id ];
		$function = 'getGoodsEvaluateCount("' . $goods_id . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 获取分类的父级分类
	 */
	public function tagCategoryparentquery($tag, $content)
	{
		$category_id = isset($tag['id']) ? $tag['id'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "GoodsCategory";
		$function_array = [ 'getCategoryParentQuery', $category_id ];
		$function = 'getCategoryParentQuery("' . $category_id . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 商品优惠券
	 */
	public function tagGoodscoupon($tag, $content)
	{
		$goodsid = isset($tag['goods_id']) ? $tag['goods_id'] : '1';
		$uid = isset($tag['uid']) ? $tag['uid'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Goods";
		$function_array = [ 'getGoodsCoupon', $goodsid, $uid ];
		$function = 'getGoodsCoupon("' . $goodsid . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 获取用户通知
	 */
	public function tagUsernotice($tag, $content)
	{
		$config = new Config();
		return $user_notice = $config->getUserNotice(0);
	}
	
	/**
	 * 余额提现记录
	 */
	public function tagWithdrawlist($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Member";
		$function_array = [ 'getMemberBalanceWithdraw', $page, $num, $where, $order ];
		$function = 'getMemberBalanceWithdraw("' . $page . '","' . $num . '", "' . $where . '","' . $order . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 会员详情
	 */
	public function tagMemberinfo($tag, $content)
	{
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Member";
		$function_array = [ 'getMemberDetail' ];
		$function = 'getMemberDetail(0)';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 *兑换比例
	 */
	public function tagConvertrate($tag, $content)
	{
		$shop_id = isset($tag['id']) ? $tag['id'] : 0;
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$service_folder = 'Member';
		$service_name = 'MemberAccount';
		$function_array = [ 'getConvertRate', $shop_id ];
		$function = 'getConvertRate("' . $shop_id . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array, $service_folder);
	}
	
	/**
	 * 订单号
	 */
	public function tagOrdernumber($tag, $content)
	{
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$order_id = isset($tag['id']) ? $tag['id'] : '';
		
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = 'Member';
		$function_array = [ 'getOrderNumber', $order_id ];
		$function = 'getOrderNumber("' . $order_id . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 获取店铺设置
	 */
	public function tagShopconfig($tag, $content)
	{
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$shop_id = isset($tag['id']) ? $tag['id'] : '0';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = 'Config';
		$function_array = [ 'getShopConfig', $shop_id ];
		$function = 'getShopConfig("' . $shop_id . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 订单数量
	 */
	public function tagOrdersum($tag, $content)
	{
		$where = isset($tag['where']) ? $tag['where'] : '';//传入值为array,须有买家id
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = 'Order';
		$function_array = [ 'getOrderStatusNum2' ];
		$function = 'getOrderStatusNum2()';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 评价信息 分页
	 */
	public function tagOrderevaluatedatalist($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "OrderQuery";
		$function_array = [ 'getOrderEvaluateDataList', $page, $num, $where, $order ];
		$function = 'getOrderEvaluateDataList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 会员优惠券
	 */
	public function tagMembercounponlist($tag, $content)
	{
		$type = isset($tag['type']) ? $tag['type'] : 1;
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Member";
		$function_array = [ 'getMemberCounponList', $type ];
		$function = 'getMemberCounponList("' . $type . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 会员收藏列表
	 */
	public function tagMembergoodsfavoriteslist($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Member";
		$function_array = [ 'getMemberGoodsFavoritesList', $page, $num, $where, $order ];
		$function = 'getMemberGoodsFavoritesList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 会员qq
	 */
	public function tagmemberqq($tag, $content)
	{
		$member = new MemberService();
		$member_info = $member->getMemberDetail();
		$memberqq = $member_info['user_info']['user_qq'];
		return $memberqq;
	}
	
	/**
	 * 会员地址
	 */
	public function tagmemberlocation($tag, $content)
	{
		$member = new MemberService();
		$member_info = $member->getMemberDetail();
		$memberlocation = $member_info['user_info']['location'];
		return $memberlocation;
	}
	
	/**
	 * 会员性别
	 */
	public function tagmembersex($tag, $content)
	{
		$member = new MemberService();
		$member_info = $member->getMemberDetail();
		$membersex = $member_info['user_info']['sex'];
		return $membersex;
	}
	
	/**
	 * 会员出生年月
	 */
	public function tagMemberbirthday($tag, $content)
	{
		$member = new MemberService();
		$member_info = $member->getMemberDetail();
		if ($member_info['user_info']['birthday'] == 0 || $member_info['user_info']['birthday'] == "") {
			$member_birthday = "";
		} else {
			$member_birthday = date('Y-m-d', $member_info['user_info']['birthday']);
		}
		return $member_birthday;
	}
	
	/**
	 * 会员真实姓名
	 */
	public function tagmemberrealname($tag, $content)
	{
		$member = new MemberService();
		$member_info = $member->getMemberDetail();
		$memberrealname = $member_info['user_info']['real_name'];
		return $memberrealname;
	}
	
	/**
	 * 会员昵称
	 */
	public function tagmembernickname($tag, $content)
	{
		$member = new MemberService();
		$member_info = $member->getMemberDetail();
		$membernickname = $member_info['user_info']['nick_name'];
		return $membernickname;
	}
	
	/**
	 * 会员头像
	 */
	public function tagMemberimg($tag, $content)
	{
		$member = new MemberService();
		$member_info = $member->getMemberDetail();
		if (!empty($member_info['user_info']['user_headimg'])) {
			$member_img = $member_info['user_info']['user_headimg'];
		} elseif (!empty($member_info['user_info']['qq_openid'])) {
			$member_img = $member_info['user_info']['qq_info_array']['figureurl_qq_1'];
		} elseif (!empty($member_info['user_info']['wx_openid'])) {
			$member_img = '0';
		} else {
			$member_img = '0';
		}
		return $member_img;
	}
	
	/**
	 *
	 * 会员提现账户列表
	 */
	public function tagMemberbankaccountlist($tag, $content)
	{
		
		$is_default = isset($tag['is_default']) ? $tag['is_default'] : '0';
		$name = isset($tag['name']) ? $tag['name'] : 'name';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$service_name = "Member";
		$function_array = [ 'getMemberBankAccount' ];
		$function = 'getMemberBankAccount("' . $is_default . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 *一段时间内会员账户（积分和余额）
	 */
	public function tagMemberaccount($tag, $content)
	{
		$uid = isset($tag['id']) ? $tag['id'] : '';
		$account_type = isset($tag['account_type']) ? $tag['account_type'] : '';
		$start_time = isset($tag['start_time']) ? $tag['start_time'] : '';
		$end_time = isset($tag['end_time']) ? $tag['end_time'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_folder = 'Member';
		$service_name = "MemberAccount";
		$function_array = [ 'getMemberAccount', 0, $uid, $account_type, $start_time, $end_time ];
		$function = 'getMemberAccount(0,"' . $uid . '","' . $account_type . '","' . $start_time . '","' . $end_time . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array, $service_folder);
//     	$member = new MemberAccount();
//     	$member_account = $member->getMemberAccount(0,$uid,$account_type,$start_time,$end_time);
//     	return $member_account;
	}
	
	/**
	 *会员余额积分流水
	 */
	public function tagAccountList($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Member";
		$function_array = [ 'getAccountList', $page, $num, $where, $order, $field ];
		$function = 'getAccountList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '","' . $field . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	
	/**
	 *收货地址
	 */
	public function tagAddresslist($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Member";
		$function_array = [ 'getMemberExpressAddressList', $page, $num, $where, $order ];
		$function = 'getMemberExpressAddressList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 *首页限时折扣商品
	 */
	public function tagDiscountgoodslist($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Goods";
		$function_array = [ 'getDiscountGoodsList', $page, $num, $where, $order ];
		$function = 'getDiscountGoodsList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 获取购物车
	 */
	public function tagCartlist($tag, $content)
	{
		$uid = isset($tag['uid']) ? $tag['uid'] : '';
		$shop_id = isset($tag['shop_id']) ? $tag['shop_id'] : '0';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = 'Goods';
		$function_array = [ 'getCart', $uid, $shop_id ];
		$function = 'getCart("' . $uid . '","' . $shop_id . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 *订单详情
	 */
	public function tagorderdetail($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = 'OrderQuery';
		$function_array = [ 'getOrderDetail', $id ];
		$function = 'getOrderDetail("' . $id . '")';
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 *订单列表
	 */
	public function tagOrderlist($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Order";
		$function_array = [ 'getOrderList', $page, $num, $where, $order ];
		$function = 'getOrderList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 筛选条件-价格区间标签
	 */
	public function tagCategorypricegrades($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : 0;
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "GoodsCategory";
		$function_array = [ 'getGoodsCategoryPriceGrades', $id ];
		$function = 'getGoodsCategoryPriceGrades("' . $id . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 筛选条件-品牌标签
	 */
	public function tagCategorybrands($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : 0;
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "GoodsCategory";
		$function_array = [ 'getGoodsCategoryBrands', $id ];
		$function = 'getGoodsCategoryBrands("' . $id . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	
	/**
	 * 导航标签
	 */
	public function tagNavigation($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Shop";
		$function_array = [ 'shopNavigationList', $page, $num, $where, $order ];
		$function = 'shopNavigationList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 商品详情标签
	 */
	public function tagGoodsinfo($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : 0;
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Goods";
		$function_array = [ 'getGoodsDetail', $id, ];
		$function = 'getGoodsDetail("' . $id . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 分类列表标签
	 */
	public function tagCategorylist($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "GoodsCategory";
		$function_array = [ 'getGoodsCategoryList', $page, $num, $where, $order, $field ];
		$function = 'getGoodsCategoryList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '","' . $field . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 分类树标签（最多3级）
	 */
	public function tagCategorytree($tag, $content)
	{
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "GoodsCategory";
		$function_array = [ 'getCategoryTreeUseInShopIndex' ];
		$function = 'getCategoryTreeUseInShopIndex()';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	
	/**
	 * 友情链接列表标签
	 */
	public function tagLinklist($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : 1;
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Shop";
		$function_array = [ 'getLinkList', $page, $num, $where, $order, $field ];
		$function = 'getLinkList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '","' . $field . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 广告标签
	 */
	public function tagAdv($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Shop";
		$function_array = [ 'getPlatformAdvPositionDetail', $id, $field ];
		$function = 'getPlatformAdvPositionDetail("' . $id . '", "' . $field . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 广告位标签（通过广告位关键字）
	 */
	public function tagAdvkeyword($tag, $content)
	{
		$keyword = isset($tag['keyword']) ? $tag['keyword'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Shop";
		$function_array = [ 'getPlatformAdvPositionDetailByApKeyword', $keyword, $field ];
		$function = 'getPlatformAdvPositionDetailByApKeyword("' . $keyword . '", "' . $field . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 网站名称标签
	 */
	public function tagWebname($tag)
	{
		$web_site = new WebSite();
		$data = $web_site->getWebSiteInfo();
		return $data['title'];
	}
	
	/**
	 * 官方网址标签
	 */
	public function tagWeburl($tag, $content)
	{
		$web_site = new WebSite();
		$data = $web_site->getWebSiteInfo();
		return $data['web_url'];
	}
	
	/**
	 * 联系地址标签
	 */
	public function tagWebaddress($tag, $content)
	{
		$web_site = new WebSite();
		$data = $web_site->getWebSiteInfo();
		return $data['web_address'];
	}
	
	/**
	 * 网站二维码标签
	 */
	public function tagWebqrcode($tag, $content)
	{
	
	}
	
	/**
	 * 网站描述标签
	 */
	public function tagWebdesc($tag, $content)
	{
		$web_site = new WebSite();
		$data = $web_site->getWebSiteInfo();
		return $data['web_desc'];
	}
	
	/**
	 * 网站logo标签
	 */
	public function tagWeblogo($tag, $content)
	{
		$web_site = new WebSite();
		$data = $web_site->getWebSiteInfo();
		return $data['logo'];
	}
	
	/**
	 * 网站公众号二维码标签
	 */
	public function tagWebwechatqrcode($tag, $content)
	{
		$web_site = new WebSite();
		$data = $web_site->getWebSiteInfo();
		return $data['web_qrcode'];
	}
	
	/**
	 * 网站关键字
	 */
	public function tagWebkeywords($tag, $content)
	{
		$web_site = new WebSite();
		$data = $web_site->getWebSiteInfo();
		return $data['key_words'];
	}
	
	/**
	 * 网站联系电话
	 */
	public function tagWebphone($tag, $content)
	{
		$web_site = new WebSite();
		$data = $web_site->getWebSiteInfo();
		return $data['web_phone'];
	}
	
	/**
	 * 网站邮箱
	 */
	public function tagWebemail($tag, $content)
	{
		$web_site = new WebSite();
		$data = $web_site->getWebSiteInfo();
		return $data['web_email'];
	}
	
	/**
	 * 网站qq
	 */
	public function tagWebqq($tag, $content)
	{
		$web_site = new WebSite();
		$data = $web_site->getWebSiteInfo();
		return $data['web_qq'];
	}
	
	/**
	 * 网站微信号
	 */
	public function tagWebwechat($tag, $content)
	{
		$web_site = new WebSite();
		$data = $web_site->getWebSiteInfo();
		return $data['web_weixin'];
	}
	
	/**
	 * 网站备案号
	 */
	public function tagWebicp($tag, $content)
	{
		$web_site = new WebSite();
		$data = $web_site->getWebSiteInfo();
		return $data['web_icp'];
	}
	
	/**
	 * 网站关闭原因
	 */
	public function tagWebclosereason($tag, $content)
	{
		$web_site = new WebSite();
		$data = $web_site->getWebSiteInfo();
		return $data['close_reason'];
	}
	
	/**
	 * 网站第三方统计代码
	 */
	public function tagWebcount($tag, $content)
	{
		$web_site = new WebSite();
		$data = $web_site->getWebSiteInfo();
		return $data['third_count'];
	}
	
	/**
	 *
	 */
	public function tagWebwechatsharelogo($tag, $content)
	{
		$web_site = new WebSite();
		$data = $web_site->getWebSiteInfo();
		return $data['web_wechat_share_logo'];
	}
	
	/**
	 * 默认搜索
	 */
	public function tagDefaultsearch($tag, $content)
	{
		$config = new Config();
		$default_keywords = $config->getDefaultSearchConfig(0);
		return $default_keywords;
	}
	
	/**
	 * 热门搜索
	 */
	public function tagHotsearch($tag, $content)
	{
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$config = new Config();
		$hot_keys = $config->getHotsearchConfig(0);
		return $this->loadContent($hot_keys, $name, $content);
	}
	
	/**
	 * 组装非封闭标签数据
	 * @param unknown $data
	 * @param unknown $name
	 * @param unknown $content
	 */
	protected function loadContent($data, $name, $content)
	{
		$parse = '<?php ';
		$parse .= "\${$name} ='" . json_encode($data) . "';";
		$parse .= "\${$name} =json_decode(\${$name}, true);";
		$parse .= '?>';
		$parse .= $content;
		return $parse;
	}
	
	/***************分界线*********分界线**********分界线******分界线********分界线****分界线******分界线*****分界线*********分界线**************************/
	
	/**
	 * 帮助中心分类列表标签
	 */
	public function tagHelpclasslist($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Shop";
		$function_array = [ 'getPlatformHelpClassList', $page, $num, $where, $order, $field ];
		$function = 'getPlatformHelpClassList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '", "' . $field . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 帮助中心内容列表标签
	 */
	public function tagHelpdocumentlist($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : 1;
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Shop";
		$function_array = [ 'getPlatformHelpDocumentList', $page, $num, $where, $order, $field ];
		$function = 'getPlatformHelpDocumentList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '", "' . $field . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 帮助中心详情标签
	 */
	public function tagHelpdocumentinfo($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Shop";
		$function_array = [ 'getPlatformDocumentDetail', $id, $field ];
		$function = 'getPlatformDocumentDetail("' . $id . '", "' . $field . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 公告列表标签
	 */
	public function tagNoticelist($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Shop";
		$function_array = [ 'getNoticeList', $page, $num, $where, $order, $field ];
		$function = 'getNoticeList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '", "' . $field . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 公告详情标签
	 */
	public function tagNoticeinfo($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Shop";
		$function_array = [ 'getNoticeDetail', $id, $field ];
		$function = 'getNoticeDetail("' . $id . '", "' . $field . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 文章分类列表标签
	 */
	public function tagArticleclasslist($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Article";
		$function_array = [ 'getArticleClass', $page, $num, $where, $order ];
		$function = 'getArticleClass("' . $page . '","' . $num . '", "' . $where . '","' . $order . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 文章列表标签
	 */
	public function tagArticlelist($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		$service_name = "Article";
		$function_array = [ 'getArticleList', $page, $num, $where, $order ];
		$function = 'getArticleList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 文章中心详情
	 */
	public function tagArticleinfo($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Article";
		$function_array = [ 'getArticleDetail', $id, $field ];
		$function = 'getArticleDetail("' . $id . '", "' . $field . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 专题详情
	 */
	public function tagTopicinfo($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Article";
		$function_array = [ 'getTopicDetail', $id, $field ];
		$function = 'getTopicDetail("' . $id . '", "' . $field . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 猜你喜欢标签
	 */
	public function tagmemberlikes($tag, $content)
	{
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Member";
		$function_array = [ 'getGuessMemberLikes' ];
		$function = 'getGuessMemberLikes()';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 浏览历史标签
	 */
	public function tagMemberhistory($tag, $content)
	{
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Member";
		$function_array = [ 'getMemberViewHistory' ];
		$function = 'getMemberViewHistory()';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 商品列表标签
	 */
	public function tagGoodslist($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "Goods";
		$function_array = [ 'getGoodsList', $page, $num, $where, $order ];
		$function = 'getGoodsList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 品牌列表标签
	 */
	public function tagBrandlist($tag, $content)
	{
		$page = isset($tag['page']) ? $tag['page'] : '1';
		$num = isset($tag['num']) ? $tag['num'] : PAGESIZE;
		$where = isset($tag['where']) ? $tag['where'] : '';
		$order = isset($tag['order']) ? $tag['order'] : '';
		$field = isset($tag['field']) ? $tag['field'] : '*';
		$cache = isset($tag['cache']) ? $tag['cache'] : '';
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "GoodsBrand";
		$function_array = [ 'getGoodsBrandList', $page, $num, $where, $order, $field ];
		$function = 'getGoodsBrandList("' . $page . '","' . $num . '", "' . $where . '","' . $order . '","' . $field . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 品牌详情标签
	 */
	public function tagBrandinfo($tag, $content)
	{
		$id = isset($tag['id']) ? $tag['id'] : '';
		$cache = isset($tag['cache']) ? $tag['cache'] : 0;
		$name = isset($tag['name']) ? $tag['name'] : 'data';
		
		$service_name = "GoodsBrand";
		$function_array = [ 'getGoodsBrandInfo', $id ];
		$function = 'getGoodsBrandInfo("' . $id . '")';
		
		return $this->loadPageListContent($name, $content, $cache, $service_name, $function, $function_array);
	}
	
	/**
	 * 组装返回代码
	 */
	protected function loadPageListContent($name, $content, $cache, $service_name, $function, $function_array, $service_folder = '')
	{
		$parse = '<?php ';
		if ($cache === '') {
			if ($service_folder == '') {
				$parse .= '$service = new data\service\\' . $service_name . ';';
			} else {
				$parse .= '$service = new data\service\\' . $service_folder . '\\' . $service_name . ';';
			}
			$parse .= '$' . $name . ' = $service->' . $function . ';';
			$parse .= '$' . $name . ' = json_encode($' . $name . ');';
			$parse .= '$' . $name . ' = json_decode($' . $name . ', true);';
		} else {
			$parse .= '$tag_md5 = json_encode(' . json_encode($function_array) . ');';
			$parse .= 'if(cache("TAG_".md5($tag_md5))):';
			$parse .= '$' . $name . ' = cache("TAG_".md5($tag_md5));';
			$parse .= 'else:';
			if ($service_folder == '') {
				$parse .= '$service = new data\service\\' . $service_name . ';';
			} else {
				$parse .= '$service = new data\service\\' . $service_folder . '\\' . $service_name . ';';
			}
			$parse .= '$' . $name . ' = $service->' . $function . ';';
			$parse .= '$' . $name . ' = json_encode($' . $name . ');';
			$parse .= '$' . $name . ' = json_decode($' . $name . ', true);';
			$parse .= 'cache("TAG_".md5($tag_md5), $' . $name . ', ' . $cache . ');';
			$parse .= 'endif;';
		}
		$parse .= ' ?>';
		$parse .= $content;
		return $parse;
	}
}