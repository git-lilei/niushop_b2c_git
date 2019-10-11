<?php
/**
 * Article.php
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

use data\model\NcCmsArticleClassModel;
use data\model\NcCmsArticleModel;
use data\model\NcCmsArticleViewModel;
use data\model\NcCmsCommentModel;
use data\model\NcCmsCommentViewModel;
use data\model\NcCmsTopicModel;
use think\Cache;


/**
 * 文章服务层
 */
class Article extends BaseService
{
	
	/***********************************************************文章开始*********************************************************/
	
	/**
	 * 添加文章
	 */
	public function addArticle($data)
	{
		Cache::clear("article");
		$member = new Member();
		$user_info = $member->getUserInfoDetail($data["uid"]);
		$article = new NcCmsArticleModel();
		$data["publisher_name"] = $user_info["user_name"];
		$article->save($data);
		$data['article_id'] = $article->article_id;
		hook("articleSaveSuccess", $data);
		$retval = $article->article_id;
		return $retval;
	}
	
	/**
	 * 修改文章
	 */
	public function updateArticle($data)
	{
		Cache::clear("article");
		$member = new Member();
		$user_info = $member->getUserInfoDetail($this->uid);
		$data["publisher_name"] = $user_info["user_name"];
		$article = new NcCmsArticleModel();
		
		$retval = $article->save($data, [ 'article_id' => $data["article_id"] ]);
		$data['article_id'] = $data["article_id"];
		hook("articleSaveSuccess", $data);
		return $retval;
	}
	
	/**
	 * 修改文章排序
	 */
	public function modifyArticleSort($article_id, $sort)
	{
		Cache::clear("article");
		$article = new NcCmsArticleModel();
		$data = array(
			'sort' => $sort
		);
		$retval = $article->save($data, [ 'article_id' => $article_id ]);
		return $retval;
	}
	
	/**
	 * 增加文章点击量
	 */
	public function updateArticleClickNum($article_id)
	{
		Cache::clear("article");
		$article_class = new NcCmsArticleModel();
		$article_class->where([ "article_id" => $article_id ])->setInc("click", 1);
	}
	
	/**
	 * 删除文章
	 */
	public function deleteArticle($article_id)
	{
		Cache::clear("article");
		$article = new NcCmsArticleModel();
		$condition['article_id'] = [ 'in', $article_id ];
		$retval = $article->destroy($condition);
		return $retval;
	}
	
	/**
	 *  文章分类使用次数
	 */
	public function articleClassUseCount($class_id)
	{
		$article = new NcCmsArticleModel();
		$is_class_count = $article->viewCount($article, [ 'class_id' => $class_id ]);
		return $is_class_count;
	}
	
	/**
	 * 获取文章详情
	 */
	public function getArticleDetail($article_id)
	{
		$cache = Cache::tag("article")->get("getArticleDetail" . $article_id);
		if (empty($cache)) {
			$article = new NcCmsArticleModel();
			$data = $article->get($article_id);
			Cache::tag("article")->set("getArticleDetail" . $article_id, $data);
			return $data;
		} else {
			return $cache;
		}
		
	}
	
	/**
	 * 查询最*的一条
	 */
	public function getArticleFirst($condition, $order = "")
	{
		$article_class = new NcCmsArticleModel();
		$data = $article_class->getFirstData($condition, $order);
		return $data;
	}
	
	/**
	 * 获取文章列表
	 */
	public function getArticleList($page_index = 1, $page_size = 0, $condition = [], $order = '')
	{
		$data = array( $page_index, $page_size, $condition, $order );
		$data = json_encode($data);
		
		$cache = Cache::tag("article")->get("getArticleList" . $data);
		if (empty($cache)) {
			$articleview = new NcCmsArticleViewModel();
			//查询该分类以及子分类下的文章列表
			if (!empty($condition['nca.class_id'])) {
				$article_class = new NcCmsArticleClassModel();
				$article_class_array = $article_class->getQuery([
					"class_id|pid" => $condition['nca.class_id']
				], "class_id");
				$new_article_class_array = array();
				foreach ($article_class_array as $v) {
					$new_article_class_array[] = $v["class_id"];
				}
				$condition["nca.class_id"] = array( "in", $new_article_class_array );
			}
			$list = $articleview->getViewList($page_index, $page_size, $condition, $order);
			Cache::tag("article")->set("getArticleList" . $data, $list);
			return $list;
		} else {
			return $cache;
		}
	}
	
	/***********************************************************文章结束*********************************************************/
	
	
	/***********************************************************文章分类开始*********************************************************/
	
	/**
	 * 添加文章分类
	 */
	public function addAritcleClass($data)
	{
		Cache::clear("article");
		$article_class = new NcCmsArticleClassModel();
		$retval = $article_class->save($data);
		return $retval;
	}
	
	/**
	 * 修改文章分类
	 */
	public function updateArticleClass($data)
	{
		Cache::clear("article");
		$article_class = new NcCmsArticleClassModel();
		$retval = $article_class->save($data, [ 'class_id' => $data["class_id"] ]);
		return $retval;
	}
	
	/**
	 * 修改文章分类排序
	 */
	public function modifyArticleClassSort($class_id, $sort)
	{
		Cache::clear("article");
		$article_class = new NcCmsArticleClassModel();
		$data = array(
			'sort' => $sort
		);
		$retval = $article_class->save($data, [ 'class_id' => $class_id ]);
		return $retval;
	}
	
	/**
	 * 文章分类修改单个字符
	 */
	public function cmsField($class_id, $sort, $name)
	{
		Cache::clear("article");
		$article_class = new NcCmsArticleClassModel();
		$data = array(
			$sort => $name,
		);
		$retval = $article_class->save($data, [ 'class_id' => $class_id ]);
		return $retval;
	}
	
	/**
	 * 删除文章分类（如果文章分类已被使用那就不可删除）
	 */
	public function deleteArticleClass($class_id)
	{
		Cache::clear("article");
		$article_class = new NcCmsArticleClassModel();
		$retval = $article_class->destroy($class_id);
		return $retval;
	}
	
	/**
	 * 获取文章分类详情
	 */
	public function getArticleClassDetail($class_id, $field = "*")
	{
		$cache = Cache::tag("article")->get("getArticleClassDetail" . $class_id . $field);
		if (empty($cache)) {
			$article_class = new NcCmsArticleClassModel();
			$info = $article_class->getInfo([ 'class_id' => $class_id ], $field);
			Cache::tag("article")->set("getArticleClassDetail" . $class_id . $field, $info);
			return $info;
		} else {
			return $cache;
		}
	}
	
	/**
	 * 获取文章分类
	 */
	public function getArticleClassQuery()
	{
		$cache = Cache::tag("article")->get("getArticleClassQuery");
		if (empty($cache)) {
			$list = $this->getArticleClass(1, 0, 'pid=0', 'sort');
			foreach ($list["data"] as $k => $v) {
				$second_list = $this->getArticleClass(1, 0, 'pid=' . $v['class_id'], 'sort');
				$list["data"][ $k ]['child_list'] = $second_list['data'];
			}
			Cache::tag("article")->set("getArticleClassQuery", $list);
			return $list;
		} else {
			return $cache;
		}
		
	}
	
	/**
	 * 文章分类列表
	 */
	public function getArticleClass($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$data = array( $page_index, $page_size, $condition, $order );
		$data = json_encode($data);
		$cache = Cache::tag("article")->get("getArticleClass" . $data);
		if (empty($cache)) {
			$article_class = new NcCmsArticleClassModel();
			$list = $article_class->pageQuery($page_index, $page_size, $condition, $order, '*');
			Cache::tag("article")->set("getArticleClass" . $data, $list);
			return $list;
		} else {
			return $cache;
		}
		
	}
	
	/***********************************************************文章分类结束*********************************************************/
	
	
	/***********************************************************文章评论开始*********************************************************/
	
	/**
	 * 删除评论
	 */
	public function deleteComment($comment_id)
	{
		$comment = new NcCmsCommentModel();
		$retval = $comment->destroy($comment_id);
		return $retval;
	}
	
	/**
	 * 查看评论详情
	 */
	public function getCommentDetail($comment_id)
	{
		$comment = new NcCmsCommentModel();
		$data = $comment->get($comment_id);
		return $data;
	}
	
	/**
	 * 文章评论列表
	 */
	public function getCommentList($page_index = 1, $page_size = 0, $condition = '', $order = '')
	{
		$commentview = new NcCmsCommentViewModel();
		$list = $commentview->getViewList($page_index, $page_size, $condition, $order);
		return $list;
	}
	
	/***********************************************************文章评论结束*********************************************************/
	
	
	/***********************************************************专题开始*********************************************************/
	
	/**
	 * 添加专题
	 */
	public function addTopic($data)
	{
		Cache::clear("article");
		$topic = new NcCmsTopicModel();
		
		$retval = $topic->save($data);
		return $retval;
	}
	
	/**
	 * 修改专题
	 */
	public function updateTopic($data)
	{
		Cache::clear("article");
		$topic = new NcCmsTopicModel();
		$retval = $topic->save($data, [ 'topic_id' => $data["topic_id"] ]);
		return $retval;
	}
	
	/**
	 * 删除专题
	 */
	public function deleteTopic($topic_id)
	{
		Cache::clear("article");
		$topic = new NcCmsTopicModel();
		$retval = $topic->destroy($topic_id);
		return $retval;
	}
	
	/**
	 * 获取详情
	 */
	public function getTopicDetail($topic_id)
	{
		$cache = Cache::tag("article")->get("getTopicDetail" . $topic_id);
		if (!empty($cache)) {
			return $cache;
		}
		$topic = new NcCmsTopicModel();
		$list = $topic->get($topic_id);
		Cache::tag("article")->set("getTopicDetail" . $topic_id, $list);
		return $list;
	}
	
	/**
	 * 专题列表
	 */
	public function getTopicList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = '*')
	{
		$data = array( $page_index, $page_size, $condition, $order, $field );
		$data = json_encode($data);
		$cache = Cache::tag("article")->get("getTopicList" . $data);
		if (!empty($cache)) {
			return $cache;
		}
		$topic = new NcCmsTopicModel();
		$list = $topic->pageQuery($page_index, $page_size, $condition, $order, $field);
		Cache::tag("article")->set("getTopicList" . $data, $list);
		return $list;
	}
	
	/***********************************************************专题结束*********************************************************/
}