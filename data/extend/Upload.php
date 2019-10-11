<?php
/**
 * Upload.php
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

namespace data\extend;

/**
 * 上传控制器
 */

class Upload
{
	/**
	 * @var string 错误信息
	 */
	public $error;
	/**
	 * @var array 上传规则
	 */
	public $validate = [];
	/**
	 * @var array 上传文件信息
	 */
	public $info;
	/**
	 * @var string 当前完整文件名
	 */
	protected $filename;
	
	/**
	 * @var array 上传文件信息
	 */
	public function __construct($filename)
	{
		$this->filename = $filename;
	}
	
	/**
	 * 设置上传信息
	 * @param array $info
	 */
	public function setUploadInfo($info)
	{
		$this->info = $info;
		
		return $this;
	}
	
	/**
	 * 获取上传文件的信息
	 * @access public
	 * @param  string $name 信息名称
	 * @return array|string
	 */
	public function getInfo($name = '')
	{
		return isset($this->info[ $name ]) ? $this->info[ $name ] : $this->info;
	}
	
	/**
	 * 设置文件上传文件的验证规则
	 * @param array $rule
	 */
	public function setValidate(array $rule = [])
	{
		$this->validate = $rule;
		return $this;
	}
	
	/**
	 * 验证目录是否可写
	 * @param unknown $path
	 * @return boolean
	 */
	public function checkPath($path)
	{
		if (is_dir($path) || mkdir($path, 0755, true)) {
			return true;
		}
		$this->error = [ 'directory {:path} creation failed', [ 'path' => $path ] ];
		
		return false;
	}
	
	/**
	 * 检测是否合法的上传文件
	 * @access public
	 * @return bool
	 */
	public function isValid()
	{
		return $this->isTest ? is_file($this->filename) : is_uploaded_file($this->filename);
	}
	
	/**
	 * 检测上传文件
	 * @access public
	 * @param  array $rule 验证规则
	 * @return bool
	 */
	public function check($rule = [])
	{
		$rule = $this->validate;
		/* 检查文件大小 */
		if (isset($rule['size']) && !$this->checkSize($rule['size'])) {
			$this->error = 'filesize not match';
			return false;
		}
		
		/* 检查文件 Mime 类型 */
		if (isset($rule['type']) && !$this->checkMime($rule['type'])) {
			$this->error = 'mimetype to upload is not allowed';
			return false;
		}
		
		/* 检查文件后缀 */
		if (isset($rule['ext']) && !$this->checkExt($rule['ext'])) {
			$this->error = 'extensions to upload is not allowed';
			return false;
		}
		
		return true;
	}
	
	/**
	 * 检测上传文件大小
	 * @access public
	 * @param  integer $size 最大大小
	 * @return bool
	 */
	public function checkSize($size)
	{
		return $this->getFileSize($this->info["tmp_name"]) <= $size;
		
	}
	
	/**
	 * 检测上传文件类型
	 * @access public
	 * @param  array|string $mime 允许类型
	 * @return bool
	 */
	public function getFileSize($filename)
	{
		$filesize = filesize($filename);
		clearstatcache();
		return $filesize;
	}
	
	/**
	 * 获取文件类型
	 * @param unknown $filename
	 * @return unknown
	 */
	public function getFileType($filename)
	{
//         filetype($filePath);
		if (extension_loaded('fileinfo')) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE); // 返回 mime 类型
			$filetype = finfo_file($finfo, $filename);
			finfo_close($finfo);
		}
		return $filetype;
	}
	
	/**
	 * 检测上传文件类型
	 * @access public
	 * @param  array|string $mime 允许类型
	 * @return bool
	 */
	public function checkMime($mime)
	{
		$mime = is_string($mime) ? explode(',', $mime) : $mime;
		
		return in_array(strtolower($this->getFileType($this->info["tmp_name"])), $mime);
	}
	
	/**
	 * 获取文件类型信息
	 * @access public
	 * @return string
	 */
	public function getMime()
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		
		return finfo_file($finfo, $this->filename);
	}
	
	/**
	 * 检测上传文件后缀
	 * @access public
	 * @param  array|string $ext 允许后缀
	 * @return bool
	 */
	public function checkExt($ext)
	{
		if (is_string($ext)) {
			$ext = explode(',', $ext);
		}
		
		$extension = strtolower($this->getFileExt($this->getInfo('name')));
		return in_array($extension, $ext);
	}
	
	/**
	 * 获取文件后缀
	 * @param unknown $filename
	 * @return mixed
	 */
	public function getFileExt($filename)
	{
		return pathinfo($filename, PATHINFO_EXTENSION);
	}
	
	/**
	 * 移动文件
	 * @param unknown $path
	 * @param string $savename
	 * @param string $replace
	 */
	public function move($path, $savename, $replace = true)
	{
		// 文件上传失败，捕获错误代码
		if (!empty($this->info['error'])) {
			$this->error($this->info['error']);
			return false;
		}
		
		// 验证上传
		if (!$this->check()) {
			return false;
		}
		$path = rtrim($path, "/") . "/";
		// 文件保存命名规则(有外部传入)
		$filename = $path . $savename;
		// 检测目录
		if (false === $this->checkPath(dirname($filename))) {
			return false;
		}
		
		// 不覆盖同名文件
		if (!$replace && is_file($filename)) {
			$this->error = [ 'has the same filename: {:filename}', [ 'filename' => $filename ] ];
			return false;
		}
		/* 移动文件 */
		if (!move_uploaded_file($this->filename, $filename)) {
			$this->error = 'upload write error';
			return false;
		}
		
		return $filename;
	}
	
	
	/**
	 * 检测图像文件
	 * @access public
	 * @return bool
	 */
	public function checkImg()
	{
		$extension = strtolower($this->getFileExt($this->getInfo('name')));
		
		// 如果上传的不是图片，或者是图片而且后缀确实符合图片类型则返回 true
		return !in_array($extension, [ 'gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf' ]) || in_array($this->getImageType($this->filename), [ 1, 2, 3, 4, 6, 13 ]);
	}
	
	/**
	 * 判断图像类型
	 * @access protected
	 * @param  string $image 图片名称
	 * @return bool|int
	 */
	protected function getImageType($image)
	{
		if (function_exists('exif_imagetype')) {
			return exif_imagetype($image);
		}
		
		try {
			$info = getimagesize($image);
			return $info ? $info[2] : false;
		} catch (\Exception $e) {
			return false;
		}
	}
	
	/**
	 * 得到图片mime信息
	 * @param unknown $image
	 * @return boolean
	 */
	public function getImageInfo($image)
	{
		try {
			$info = getimagesize($image);
			return $info;
		} catch (\Exception $e) {
			return false;
		}
	}
	
	/**
	 *获取一个新文件名
	 */
	public function createNewFileName()
	{
		$name = date('Ymdhis', time())
			. sprintf('%03d', microtime() * 1000)
			. sprintf('%02d', mt_rand(10, 99));
		return $name;
	}
	
	/**
	 * 这里$data为一个数组类型
	 * $data[0] 为图像的宽度
	 * $data[1] 为图像的高度
	 * $data[2] 为图像的格式，包括jpg、gif和png等
	 * $data[3] 为图像的宽度和高度，内容为 width="xxx" height="yyy"
	 */
	private function getFileSizeData($file_name)
	{
		$data = getimagesize($file_name); // 图片名称
		return $data;
	}
	
	private function thumbTypeCreate($file_name, $extend_name, $thumb_type)
	{
		$thumb_type_array = array(
			"big" => array(
				"size" => "BIG",
				"width" => "800",
				"height" => "800",
				"thumb_name" => ""
			),
			"mid" => array(
				"size" => "MID",
				"width" => "400",
				"height" => "400",
				"thumb_name" => ""
			),
			"small" => array(
				"size" => "SMALL",
				"width" => "100",
				"height" => "100",
				"thumb_name" => ""
			),
			"thumb" => array(
				"size" => "THUMB",
				"width" => "50",
				"height" => "50",
				"thumb_name" => ""
			)
		);
		foreach ($thumb_type_array as $k => $v) {
			if (strpos($thumb_type, $k) !== false || empty($thumb_type)) {
				$result = $this->thumbCreate($file_name . "." . $extend_name, $file_name . "_" . $v["size"] . "." . $extend_name, $v["width"], $v["height"]);
				if ($result["code"] > 0) {
					$thumb_type_array[ $k ]["thumb_name"] = $file_name . "_" . $v["size"] . "." . $extend_name;
				}
			}
		}
		return $thumb_type_array;
	}
	
	/**
	 * 缩略图
	 * @param unknown $file_name
	 * @param unknown $new_path
	 * @param unknown $width
	 * @param unknown $height
	 * @return multitype:boolean unknown |multitype:boolean
	 */
	public function thumbCreate($file_name, $thumb_name, $width, $height)
	{
		try {
			$image = \think\Image::open($file_name);
			$image->thumb($width, $height, 3);
			$image->save($thumb_name, null, 80);
			unset($image);
			$result = array(
				"code" => 1,
				"path" => $thumb_name
			);
			
			return $result;
		} catch (\Exception $e) {
			return array(
				"code" => 0
			);
		}
	}
	
	/**
	 * 获取错误信息（支持多语言）
	 * @access public
	 * @return string
	 */
	public function getError()
	{
		return $this->error;
	}
	
	/**
	 * 获取文件名称
	 * @param unknown $file_name
	 */
	public function getFileName($file_name)
	{
		return basename($file_name, "." . $this->getFileExt($file_name));
		
	}
	
	/**
	 * 获取错误代码信息
	 * @access private
	 * @param  int $errorNo 错误号
	 * @return $this
	 */
	private function error($errorNo)
	{
		switch ($errorNo) {
			case 1:
			case 2:
				$this->error = 'upload File size exceeds the maximum value';
				break;
			case 3:
				$this->error = 'only the portion of file is uploaded';
				break;
			case 4:
				$this->error = 'no file to uploaded';
				break;
			case 6:
				$this->error = 'upload temp dir not found';
				break;
			case 7:
				$this->error = 'file write error';
				break;
			default:
				$this->error = 'unknown upload error';
		}
		
		return $this;
	}
}