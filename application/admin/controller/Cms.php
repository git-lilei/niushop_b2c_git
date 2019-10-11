<?php
/**
 * Cms.php
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

namespace app\admin\controller;

use data\service\Article;

/**
 * cms内容管理系统
 */
class Cms extends BaseController
{
	
	/**
	 * 文章列表
	 */
	public function articleList()
	{
		$child_menu_list = array(
			array(
				'url' => "cms/articlelist",
				'menu_name' => "文章列表",
				"active" => 1
			),
			array(
				'url' => "cms/articleclasslist",
				'menu_name' => "文章分类",
				"active" => 0
			)
		);
		
		$this->assign('child_menu_list', $child_menu_list);
		
		if (request()->isAjax()) {
			$article = new Article();
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$search_text = request()->post('search_text', '');
			$condition = array(
				'title|name' => array(
					'like',
					'%' . $search_text . '%'
				)
			);
			$result = $article->getArticleList($page_index, $page_size, $condition, 'nca.create_time desc');
			return $result;
		} else {
			return view($this->style . 'Cms/articleList');
		}
	}
	
	/**
	 * 添加文章
	 */
	public function addArticle()
	{
		$article = new Article();
		if (request()->isAjax()) {
			$title = request()->post('title', '');
			$class_id = request()->post('class_id', '');
			$short_title = request()->post('short_title', '');
			$source = request()->post('source', '');
			$url = request()->post('url', '');
			$author = request()->post('author', '');
			$summary = request()->post('summary', '');
			$content = request()->post('content', '');
			$image = request()->post('image', '');
			$keyword = request()->post('keyword', '');
			$article_id_array = request()->post('article_id_array', '');
			$click = request()->post('click', '');
			$sort = request()->post('sort', '');
			$commend_flag = request()->post('commend_flag', '');
			$comment_flag = request()->post('comment_flag', '');
			$attachment_path = request()->post('attachment_path', '');
			$tag = request()->post('tag', '');
			$comment_count = request()->post('comment_count', '');
			$share_count = request()->post('share_count', '');
			$status = 2;
			
			$data = array(
			    'title' => $title,
			    'class_id' => $class_id,
			    'short_title' => $short_title,
			    'source' => $source,
			    'url' => $url,
			    'author' => $author,
			    'summary' => $summary,
			    'content' => $content,
			    'image' => $image,
			    'keyword' => $keyword,
			    'article_id_array' => $article_id_array,
			    'click' => $click,
			    'sort' => $sort,
			    'commend_flag' => $commend_flag,
			    'comment_flag' => $comment_flag,
			    'status' => $status,
			    'attachment_path' => $attachment_path,
			    'tag' => $tag,
			    'comment_count' => $comment_count,
			    'share_count' => $share_count,
			    'uid' => $this->uid,
			    'public_time' => time(),
			    'create_time' => time()
			);
			$result = $article->addArticle($data);
			return AjaxReturn($result);
		} else {
			$articleClassList = $article->getArticleClass();
			$this->assign('articleClassList', $articleClassList);
			return view($this->style . 'Cms/addArticle');
		}
	}
	
	/**
	 * 修改文章
	 */
	public function updateArticle()
	{
		$article_id = request()->get('id', '');
		if (!is_numeric($article_id)) {
			$this->error('未获取到信息');
		}
		$article = new Article();
		$ArticleDetail = $article->getArticleDetail($article_id);
		$attachment_path = explode(",", $ArticleDetail['attachment_path']);
		$articleClassList = $article->getArticleClass();
		$this->assign('attachment_path', $attachment_path[0]);
		$this->assign('article_id', $article_id);
		$this->assign('articleClassList', $articleClassList);
		$this->assign('ArticleDetail', $ArticleDetail);
		return view($this->style . 'Cms/updateArticle');
	}
	
	/**
	 * 处理文章
	 */
	public function ajax_updateArticle()
	{
		$article_id = request()->post('article_id', '');
		$title = request()->post('title', '');
		$class_id = request()->post('class_id', '');
		$short_title = request()->post('short_title', '');
		$source = request()->post('source', '');
		$url = request()->post('url', '');
		$author = request()->post('author', '');
		$summary = request()->post('summary', '');
		$content = request()->post('content', '');
		$image = request()->post('image', '');
		$keyword = request()->post('keyword', '');
		$article_id_array = request()->post('article_id_array', '');
		$click = request()->post('click', '');
		$sort = request()->post('sort', '');
		$commend_flag = request()->post('commend_flag', '');
		$comment_flag = request()->post('comment_flag', '');
		$attachment_path = request()->post('attachment_path', '');
		$tag = request()->post('tag', '');
		$comment_count = request()->post('comment_count', '');
		$share_count = request()->post('share_count', '');
		$status = 2;
		$article = new Article();
		
		$data = array(
		    'title' => $title,
		    'class_id' => $class_id,
		    'short_title' => $short_title,
		    'source' => $source,
		    'url' => $url,
		    'author' => $author,
		    'summary' => $summary,
		    'content' => $content,
		    'image' => $image,
		    'keyword' => $keyword,
		    'article_id_array' => $article_id_array,
		    'click' => $click,
		    'sort' => $sort,
		    'commend_flag' => $commend_flag,
		    'comment_flag' => $comment_flag,
		    'status' => $status,
		    'attachment_path' => $attachment_path,
		    'tag' => $tag,
		    'comment_count' => $comment_count,
		    'share_count' => $share_count,
		    'uid' => $this->uid,
		    'modify_time' => time(),
		    'article_id' => $article_id
		);
		$result = $article->updateArticle($data);
		return AjaxReturn($result);
	}
	
	/**
	 * 文章分类列表
	 */
	public function articleClassList()
	{
		$child_menu_list = array(
			array(
				'url' => "cms/articlelist",
				'menu_name' => "文章列表",
				"active" => 0
			),
			array(
				'url' => "cms/articleclasslist",
				'menu_name' => "文章分类",
				"active" => 1
			)
		);
		
		$this->assign('child_menu_list', $child_menu_list);
		$article = new Article();
		$article_list = $article->getArticleClassQuery();
		$this->assign('list', $article_list["data"]);
		return view($this->style . 'Cms/articleClassList');
	}
	
	/**
	 * 根据文章id获取文章详情
	 */
	public function articleClassInfo()
	{
		if (request()->isAjax()) {
			$article = new Article();
			$class_id = request()->post('class_id', '');
			if (!is_numeric($class_id)) {
				$this->error('未获取到信息');
			}
			$infolist = $article->getArticleClassDetail($class_id);
			return $infolist;
		}
	}
	
	/**
	 * 文章分类添加修改
	 *
	 * @return multitype:unknown @class_id 大于0就是修改，小于等于0就是添加
	 */
	public function addUpdateAritcleClass()
	{
		if (request()->isAjax()) {
			$article = new Article();
			$class_id = request()->post('class_id', '');
			$name = request()->post('name', '');
			$sort = request()->post('sort', '');
			$pid = request()->post('pid', '');
			$data = array(
			    'name' => $name,
			    'pid' => $pid,
			    'sort' => $sort
			);
			if ($class_id > 0) {
			    $data["class_id"] = $class_id;
			    $retval = $article->updateArticleClass($data);
			} else {
			    $retval = $article->addAritcleClass($data);
			}
			return AjaxReturn($retval);
		}
	}
	
	/**
	 * 文章分类删除
	 */
	public function deleteArticleClass()
	{
		if (request()->isAjax()) {
			$article = new Article();
			$class_id = request()->post('class_id', '');
			/*
			 * if ($article->articleClassUseCount($class_id) > 0) {
			 * $retval = array(
			 * 'code' => '0',
			 * 'message' => '分类已被使用不可删除'
			 * );
			 * return $retval;
			 * } else {
			 * $retval = $article->deleteArticleClass($class_id);
			 * return AjaxReturn($retval);
			 * }
			 */
			if (!is_numeric($class_id)) {
				$this->error('未获取到信息');
			}
			$retval = $article->deleteArticleClass($class_id);
			return AjaxReturn($retval);
		}
	}
	
	/**
	 * 文章删除
	 */
	public function deleteArticle()
	{
		if (request()->isAjax()) {
			$article = new Article();
			$article_id = request()->post('article_id', '');
			/* if (! is_numeric($article_id)) {
				$this->error('未获取到信息');
			} */
			$retval = $article->deleteArticle($article_id);
			return AjaxReturn($retval);
		}
	}
	
	/**
	 * 评论列表
	 */
	public function commentArticle()
	{
		if (request()->isAjax()) {
			$article = new Article();
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$commentArticleList = $article->getCommentList($page_index, $page_size, '', 'comment_time desc');
			return $commentArticleList;
		} else {
			return view($this->style . 'Cms/commentArticle');
		}
	}
	
	/**
	 * 评论详情
	 */
	public function getCommentDetail()
	{
		if (request()->isAjax()) {
			$comment_id = request()->post('comment_id', '');
			$article = new Article();
			if (!is_numeric($comment_id)) {
				$this->error('未获取到信息');
			}
			$result = $article->getCommentDetail($comment_id);
			return $result;
		}
	}
	
	/**
	 * 删除单条评论
	 */
	public function deleteComment()
	{
		if (request()->isAjax()) {
			$comment_id = request()->post('comment_id', '');
			$article = new Article();
			if (!is_numeric($comment_id)) {
				$this->error('未获取到信息');
			}
			$result = $article->deleteComment($comment_id);
			return AjaxReturn($result);
		}
	}
	
	/**
	 * 修改分类排序
	 */
	public function modifyField()
	{
		if (request()->isAjax()) {
			$class_id = request()->post('fieldid', '');
			$sort = request()->post('sort', '');
			$article = new Article();
			if (!is_numeric($class_id)) {
				$this->error('未获取到信息');
			}
			$result = $article->modifyArticleClassSort($class_id, $sort);
			return AjaxReturn($result);
		}
	}
	
	/**
	 * 修改文章列表排序
	 */
	public function modifyArticleField()
	{
		if (request()->isAjax()) {
			$class_id = request()->post('fieldid', '');
			$sort = request()->post('sort', '');
			$article = new Article();
			if (!is_numeric($class_id)) {
				$this->error('未获取到信息');
			}
			$result = $article->modifyArticleSort($class_id, $sort);
			return AjaxReturn($result);
		}
	}
	
	/**
	 * 专题列表
	 */
	public function topicList()
	{
		if (request()->isAjax()) {
			$article = new Article();
			$page_index = request()->post('page_index', 1);
			$page_size = request()->post('page_size', PAGESIZE);
			$retval = $article->getTopicList($page_index, $page_size, '', 'create_time desc');
			return $retval;
		} else {
			return view($this->style . 'Cms/topicList');
		}
	}
	
	/**
	 * 添加专题
	 */
	public function addTopic()
	{
		if (request()->isAjax()) {
			$article = new Article();
			$title = request()->post('title', '');
			$image = request()->post('image', '');
			$content = request()->post('content', '');
			$status = request()->post('status', '');
			$data = array(
			    'instance_id' => $this->instance_id,
			    'title' => $title,
			    'image' => $image,
			    'content' => $content,
			    'status' => $status,
			    'create_time' => time()
			);
			
			$topic = $article->addTopic($data);
			return AjaxReturn($topic);
		} else {
			return view($this->style . 'Cms/addTopic');
		}
	}
	
	/**
	 * 修改专题
	 */
	public function updateTopic()
	{
		$article = new Article();
		if (request()->isAjax()) {
			$topic_id = request()->post('topic_id', '');
			$title = request()->post('title', '');
			$image = request()->post('image', '');
			$content = request()->post('content', '');
			$status = request()->post('status', '');
			
			$data = array(
			    'instance_id' => $this->instance_id,
			    'title' => $title,
			    'image' => $image,
			    'content' => $content,
			    'status' => $status,
			    'modify_time' => time(),
			    'topic_id' => $topic_id
			);
			
			$res = $article->updateTopic($data);
			return AjaxReturn($res);
		} else {
			$topic_id = request()->get('id', '');
			$info = $article->getTopicDetail($topic_id);
			$this->assign('info', $info);
			return view($this->style . 'Cms/updateTopic');
		}
	}
	
	/**
	 * 删除专题
	 */
	public function deleteTopic()
	{
		if (request()->isAjax()) {
			$article = new Article();
			$topic_id = request()->post('topic_id', '');
			$res = $article->deleteTopic($topic_id);
			return AjaxReturn($res);
		}
	}
	
	/**
	 * 修改单个字段
	 */
	public function cmsField()
	{
		$class_id = request()->post('fieldid', 0);
		$sort = request()->post('fieldname', '');
		$name = request()->post('fieldvalue', '');
		$article = new Article();
		if (!is_numeric($class_id)) {
			$this->error('未获取到信息');
		}
		$retval = $article->cmsField($class_id, $sort, $name);
		return AjaxReturn($retval);
	}
}