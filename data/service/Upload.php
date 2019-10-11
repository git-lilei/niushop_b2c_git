<?php
/**
 * Upload.php
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
 * @date : 2015.4.24
 * @version : v1.0.0.0
 */

namespace data\service;

use data\extend\Upload as UploadExtend;
use data\service\Config as WebConfig;
use data\service\Upload\QiNiu;

/**
 * 上传服务层
 */
class Upload extends BaseService
{
	
	private $upload_type = 1;
	
	// 缩略类型
	private $thumb_type = 1;
	
	// 是否开启水印功能
	private $is_watermark = false;
	
	// 水印图片透明度
	private $transparency = 100;
	
	// 水印图片位置
	private $waterPosition = \think\Image::WATER_SOUTHEAST;
	
	// 水印图片，默认图
	private $imgWatermark = "public/static/images/show-water.png";
	
	// 文件路径
	private $upload_path = UPLOAD;
	
	public function __construct()
	{
		parent::__construct();
		$config = new WebConfig();
		$this->upload_type = $config->getUploadType($this->instance_id);
		$picture_info = $config->getPictureUploadSetting($this->instance_id);
		$this->thumb_type = $picture_info["thumb_type"];
		$config_water_info = $config->getWatermarkConfig($this->instance_id);
		
		//水印
		if (!empty($config_water_info)) {
			if (!empty($config_water_info['watermark']) && $config_water_info['watermark'] == "1") {
				$this->is_watermark = true;
				$this->imgWatermark = $config_water_info['imgWatermark'];
				if (!empty($config_water_info['transparency'])) {
					$this->transparency = $config_water_info['transparency'];
				}
				if (!empty($config_water_info['waterPosition'])) {
					$this->waterPosition = $config_water_info['waterPosition'];
				}
			}
		}
		
	}
	
	/**
	 * 普通上传
	 * @param $_FILE [''] $file  上传文件
	 * @param string $upload_path 存储路径
	 * @param string $size 文件大小  单位为B （1KB=1024B；1MB=1024KB=1024×1024B）
	 * @param string $thumb_type 缩略图类型  big,mid,small,thumb
	 */
	public function image($file, $file_upload_path, $thumb_type = '', $size = 30000000)
	{
		
		try {
			
			//上传到相册管理，生成四张大小不一的图 只有商品图才会生成
			$upload = new UploadExtend($file["tmp_name"]);//实例化上传类
			
			$rule = [ "type" => "image/png,image/jpeg,image/gif,image/bmp", "ext" => "gif,jpg,jpeg,bmp,png", "size" => $size ];//上传文件验证规则
			$old_name = $upload->getFileName($file["name"]);//文件原名
			$file_name = $upload->createNewFileName();//生成文件名
			
			$extend_name = $upload->getFileExt($file["name"]);//文件后缀名
			$upload_path = $this->upload_path . "/" . $file_upload_path;//拼装上传路径
			$upload_data = $upload->setValidate($rule)->setUploadInfo($file)->move($upload_path, $file_name . "." . $extend_name);//将图片移动到本地
			if ($upload_data !== false) {
				$filesize = $upload->getFileSize($upload_data);//获取文件大小
//				$filetype = $upload->getFileType($upload_data);//获取文件类型
				$size_data = $upload->getImageInfo($upload_data);//获取图片信息
				$thumb_res = $this->thumbTypeCreate($upload_path, $file_name, $extend_name, $thumb_type);//生成缩略图
				//判断图片处理成功与否（设计云存储）
				
				if ($thumb_res["code"] == 0) {
					return $thumb_res;
				} else {
					$thumb_data = $thumb_res["data"];
				}
				$file_res = $this->fileStore($upload_path, $file_name . "." . $extend_name);
				if ($file_res["code"] == 0) {
					return $file_res;
				}
				
				$data = array(
				    "path" => $file_res["path"],//图片云存储
				    "size" => $filesize,
				    "file_name" => $old_name,
				    "file_ext" => $extend_name,
				    "new_name" => $file_name,
				    "pic_spec" => $size_data["0"] . "*" . $size_data["1"],
				    "big_pic_path" => $thumb_data["big"]["thumb_name"],
				    "big_pic_spec" => $thumb_data["big"]["width"] . "*" . $thumb_data["big"]["height"],
				    "big_pic_size" => $thumb_data["big"]["size"],
				    "mid_pic_path" => $thumb_data["mid"]["thumb_name"],
				    "mid_pic_spec" => $thumb_data["mid"]["width"] . "*" . $thumb_data["mid"]["height"],
				    "mid_pic_size" => $thumb_data["mid"]["size"],
				    "small_pic_path" => $thumb_data["small"]["thumb_name"],
				    "small_pic_spec" => $thumb_data["small"]["width"] . "*" . $thumb_data["small"]["height"],
				    "small_pic_size" => $thumb_data["small"]["size"],
				    "thumb_pic_path" => $thumb_data["thumb"]["thumb_name"],
				    "thumb_pic_spec" => $thumb_data["thumb"]["width"] . "*" . $thumb_data["thumb"]["height"],
				    "thumb_pic_size" => $thumb_data["thumb"]["size"],
				    "upload_type" => $this->upload_type,
				    "domain" => $file_res["domain"],
				    "bucket" => $file_res["bucket"]
				);
				return [ "code" => 1, "data" => $data, "message" => "上传完毕" ];
			} else {
				return [ "code" => 0, "message" => $upload->error ];
			}
		} catch (\Exception $e) {
			return [ "code" => 0, "message" => $e->getMessage() ];
		}
	}
	
	/**
	 * 相册图片上传
	 */
	public function imageToAlbum($file, $file_upload_path, $thumb_type = '', $album_data = [], $size = 30000000)
	{
		
		try {
			//上传到相册管理，生成四张大小不一的图 只有商品图才会生成
			$upload = new UploadExtend($file["tmp_name"]);//实例化上传类
			
			$rule = [ "type" => "image/png,image/jpeg,image/gif,image/bmp", "ext" => "gif,jpg,jpeg,bmp,png", "size" => $size ];//上传文件验证规则
			$old_name = $upload->getFileName($file["name"]);//文件原名
			$file_name = $upload->createNewFileName();//生成文件名
			
			$extend_name = $upload->getFileExt($file["name"]);//文件后缀名
			$file_info = $upload->getImageInfo($file["tmp_name"]);
			
			$upload_path = $this->upload_path . "/" . $file_upload_path;//拼装上传路径
			
			$upload_data = $upload->setValidate($rule)->setUploadInfo($file)->move($upload_path, $file_name . "." . $extend_name);//将图片移动到本地
			if ($upload_data !== false) {
				
				$filesize = $upload->getFileSize($upload_data);//获取文件大小
//				$filetype = $upload->getFileType($upload_data);//获取文件类型
				$size_data = $upload->getImageInfo($upload_data);//获取图片信息
				$thumb_res = $this->thumbTypeCreate($upload_path, $file_name, $extend_name, $thumb_type);//生成缩略图
				//判断图片处理成功与否（设计云存储）
				if ($thumb_res["code"] == 0) {
					return $thumb_res;
				} else {
					$thumb_data = $thumb_res["data"];
				}
				
				$file_res = $this->fileStore($upload_path, $file_name . "." . $extend_name);
				if ($file_res["code"] == 0) {
					return $file_res;
				}
				
				$data = array(
					'type' => 'IMAGE',
					"path" => $file_res['path'],
					"size" => $filesize,
					"album_id" => $album_data["album_id"],
					"file_name" => $old_name,
					"file_ext" => $extend_name,
					"pic_spec" => $size_data["0"] . "*" . $size_data["1"],
					"big_pic_path" => $thumb_data["big"]["thumb_name"],
					"big_pic_spec" => $thumb_data["big"]["width"] . "*" . $thumb_data["big"]["height"],
					"big_pic_size" => $thumb_data["big"]["size"],
					"mid_pic_path" => $thumb_data["mid"]["thumb_name"],
					"mid_pic_spec" => $thumb_data["mid"]["width"] . "*" . $thumb_data["mid"]["height"],
					"mid_pic_size" => $thumb_data["mid"]["size"],
					"small_pic_path" => $thumb_data["small"]["thumb_name"],
					"small_pic_spec" => $thumb_data["small"]["width"] . "*" . $thumb_data["small"]["height"],
					"small_pic_size" => $thumb_data["small"]["size"],
					"thumb_pic_path" => $thumb_data["thumb"]["thumb_name"],
					"thumb_pic_spec" => $thumb_data["thumb"]["width"] . "*" . $thumb_data["thumb"]["height"],
					"thumb_pic_size" => $thumb_data["thumb"]["size"],
					"create_time" => time()
				);
				
				$data_picture = array(
					'shop_id' => $this->instance_id,
					'is_wide' => "0",
					'pic_name' => $file_name,
					'pic_tag' => $old_name,
					'pic_cover' => $data["path"],
					'pic_size' => $filesize,
					'pic_spec' => $file_info[0] . "," . $file_info[1],
					'pic_cover_big' => $thumb_data["big"]["thumb_name"],
					'pic_size_big' => $thumb_data["big"]["size"],
					'pic_spec_big' => $thumb_data["big"]["width"] . "*" . $thumb_data["big"]["height"],
					'pic_cover_mid' => $thumb_data["mid"]["thumb_name"],
					'pic_size_mid' => $thumb_data["mid"]["size"],
					'pic_spec_mid' => $thumb_data["mid"]["width"] . "*" . $thumb_data["mid"]["height"],
					'pic_cover_small' => $thumb_data["small"]["thumb_name"],
					'pic_size_small' => $thumb_data["small"]["size"],
					'pic_spec_small' => $thumb_data["small"]["width"] . "*" . $thumb_data["small"]["height"],
					'pic_cover_micro' => $thumb_data["thumb"]["thumb_name"],
					'pic_size_micro' => $thumb_data["thumb"]["size"],
					'pic_spec_micro' => $thumb_data["thumb"]["width"] . "*" . $thumb_data["thumb"]["height"],
					'upload_time' => time(),
					"upload_type" => $this->upload_type,
					"domain" => $file_res["domain"],
					"bucket" => $file_res["bucket"]
				);
				
				$album = new Album();
				
				if ($album_data["pic_id"] == 0) {
					
					//查询默认相册
					if ($album_data["album_id"] > 0) {
						$album_detail = $album->getAlbumClassDetail($album_data["album_id"]);
					} else {
						$album_detail = $album->getDefaultAlbumDetail();
					}
					
					if (empty($album_detail))
						return [ "code" => 0, "message" => "不存在的相册" ];
					
					$album_id = $album_detail["album_id"];//相册id
					
					$data_picture['album_id'] = $album_id;
					$retval = $album->addPicture($data_picture);
				} else {
					$data_picture['pic_id'] = $album_data['pic_id'];
					$retval = $album->modifyAlbumPicture($data_picture);
					if ($retval > 0) {
						$retval = $album_data['pic_id'];
					}
				}
				
				if ($retval > 0) {
					$data["pic_id"] = $retval;
					return [ "code" => 1, "data" => $data, "message" => "上传完毕" ];
				} else {
					return [ "code" => 0, "存入相册失败！" ];
				}
			} else {
				return [ "code" => 0, "message" => $upload->error ];
			}
		} catch (\Exception $e) {
			
			return [ "code" => 0, "message" => $e->getMessage() ];
		}
	}
	
	/**
	 * 视频
	 */
	public function video($file, $file_upload_path, $size = 3000000000)
	{
		try {
			$upload = new UploadExtend($file["tmp_name"]);//实例化上传类
			$rule = [ "type" => "video/mp4,video/x-msvideo,application/vnd.rn-realmedia,audio/x-pn-realaudio,audio/x-ms-wmv", "ext" => "mp4,avi,rm,rmvb,wmv", "size" => $size ];//上传文件验证规则
			$file_name = $upload->createNewFileName();
			$extend_name = $upload->getFileExt($file["name"]);
			$old_name = $upload->getFileName($file["name"]);//文件原名
			$upload_path = $this->upload_path . "/" . $file_upload_path;//拼装上传路径
			$upload_data = $upload->setValidate($rule)->setUploadInfo($file)->move($upload_path, $file_name . "." . $extend_name);
			if ($upload_data !== false) {
				$filesize = $upload->getFileSize($upload_data);//获取文件大小
				
				//判断图片处理成功与否（设计云存储）
				$file_res = $this->fileStore($upload_path, $file_name . "." . $extend_name);
				if ($file_res["code"] == 0) {
					return $file_res;
				}
				$data = array(
					'type' => 'VIDEO',
					'path' => $file_res["path"],
					'file_name' => $old_name,
					'file_ext' => $extend_name,
					'size' => $filesize,
					'create_time' => time()
				);
				return [ "code" => 1, "data" => $data, "message" => "上传完毕" ];
			} else {
				return [ "code" => 0, "message" => $upload->error ];
			}
		} catch (\Exception $e) {
			return [ "code" => 0, "message" => $e->getMessage() ];
		}
	}
	
	/**
	 * 上传音频
	 * @param unknown $file
	 * @param unknown $file_upload_path
	 * @param number $size
	 */
	public function audio($file, $file_upload_path, $size = 3000000000){
	    try {
	        $upload = new UploadExtend($file["tmp_name"]);//实例化上传类
	        $rule = [ "type" => "audio/mpeg,audio/mpeg3,audio/x-mpeg-3,audio/wav,audio/x-wav", "ext" => "mp3,wav", "size" => $size ];//上传文件验证规则
	        $file_name = $upload->createNewFileName();
	        $extend_name = $upload->getFileExt($file["name"]);
	        $old_name = $upload->getFileName($file["name"]);//文件原名
	        $upload_path = $this->upload_path . "/" . $file_upload_path;//拼装上传路径
	        $upload_data = $upload->setValidate($rule)->setUploadInfo($file)->move($upload_path, $file_name . "." . $extend_name);
	        if ($upload_data !== false) {
	            $filesize = $upload->getFileSize($upload_data);//获取文件大小
	
	            $file_res = $this->fileStore($upload_path, $file_name . "." . $extend_name);
	            if ($file_res["code"] == 0) {
	                return $file_res;
	            }
	            $data = array(
	                'type' => 'AUDIO',
	                'path' => $file_res["path"],
	                'file_name' => $old_name,
	                'file_ext' => $extend_name,
	                'size' => $filesize,
	                'create_time' => time()
	            );
	            return [ "code" => 1, "data" => $data, "message" => "上传完毕" ];
	        } else {
	            return [ "code" => 0, "message" => $upload->error ];
	        }
	    } catch (\Exception $e) {
	        return [ "code" => 0, "message" => $e->getMessage() ];
	    }
	}
	
	/**
	 * 附件上传
	 */
	public function file($file, $file_upload_path, $size = 300000000)
	{
		try {
			$upload = new UploadExtend($file["tmp_name"]);//实例化上传类
			$old_name = $upload->getFileName($file["name"]);//文件原名
			$file_name = $upload->createNewFileName();//生成新的文件名
			$rule = [ "type" => "application/msword,application/msword", "ext" => "doc,xls", "size" => $size ];//上传文件验证规则
			$extend_name = $upload->getFileExt($file["name"]);
			$upload_path = $this->upload_path . "/" . $file_upload_path;//上传路径
			$upload_data = $upload->setValidate($rule)->setUploadInfo($file)->move($upload_path, $file_name . "." . $extend_name);
			if ($upload_data !== false) {
				$filesize = $upload->getFileSize($upload_data);//获取文件大小
				$file_res = $this->fileStore($upload_path, $file_name . "." . $extend_name);//判断图片处理成功与否（设计云存储）
				if ($file_res["code"] == 0) {
					return $file_res;
				}
				
				$data = array(
					"path" => $file_res['path'],
					"size" => $filesize,
					"file_name" => $old_name,
					"file_ext" => $extend_name,
				);
				return [ "code" => 1, "data" => $data, "message" => "上传完毕" ];
			} else {
				return [ "code" => 0, "message" => $upload->error ];
			}
		} catch (\Exception $e) {
			return [ "code" => 0, "message" => $e->getMessage() ];
		}
		
	}
	
	/**
	 * 压缩文件
	 */
	public function compressedFiles($file, $file_upload_path, $size = 300000000)
	{
		try {
			
			$upload = new UploadExtend($file["tmp_name"]);//实例化上传类
			
			$old_name = $upload->getFileName($file["name"]);//文件原名
			$file_name = $upload->createNewFileName();//生成新的文件名
			
			$rule = [ "type" => "application/zip,application/x-rar,application/x-zip-compressed", "ext" => "zip,rar,apk", "size" => $size ];//上传文件验证规则
			$extend_name = $upload->getFileExt($file["name"]);
			$upload_path = $this->upload_path . "/" . $file_upload_path;//上传路径
			
			$upload_data = $upload->setValidate($rule)->setUploadInfo($file)->move($upload_path, $file_name . "." . $extend_name);
			
			if ($upload_data !== false) {
				$filesize = $upload->getFileSize($upload_data);//获取文件大小
				$file_res = $this->fileStore($upload_path, $file_name . "." . $extend_name);//判断图片处理成功与否（设计云存储）
				if ($file_res["code"] == 0) {
					return $file_res;
				}
				
				$data = array(
					"path" => $file_res['path'],
					"size" => $filesize,
					"file_name" => $old_name,
					"file_ext" => $extend_name,
				);
				return [ "code" => 1, "data" => $data, "message" => "上传完毕" ];
			} else {
				return [ "code" => 0, "message" => $upload->error ];
			}
		} catch (\Exception $e) {
			return [ "code" => 0, "message" => $e->getMessage() ];
		}
	}
	
	/**
	 * 缩略图生成
	 */
	private function thumbTypeCreate($upload_path, $file_name, $extend_name, $thumb_type = "")
	{
		
		$thumb_type_array = array(
			"big" => array(
				"size" => "BIG",
				"width" => 700,
				"height" => 700,
				"thumb_name" => ""
			),
			"mid" => array(
				"size" => "MID",
				"width" => 360,
				"height" => 360,
				"thumb_name" => ""
			),
			"small" => array(
				"size" => "SMALL",
				"width" => 240,
				"height" => 240,
				"thumb_name" => ""
			),
			"thumb" => array(
				"size" => "THUMB",
				"width" => 60,
				"height" => 60,
				"thumb_name" => ""
			)
		);
		foreach ($thumb_type_array as $k => $v) {
			if (strpos($thumb_type, $k) !== false) {
				$result = $this->thumbCreate($upload_path, $file_name . "." . $extend_name, $file_name . "_" . $v["size"] . "." . $extend_name, $v["width"], $v["height"]);
				//返回生成的缩略图路径
				if ($result["code"] > 0) {
					$thumb_type_array[ $k ]["thumb_name"] = $result["path"];
					$thumb_type_array[ $k ]["size"] = $result["size"];
				} else {
					return $result;
				}
			}
		}
		return [ "code" => 1, "data" => $thumb_type_array ];
	}
	
	/**
	 * 缩略图
	 */
	public function thumbCreate($upload_path, $file_name, $thumb_name, $width, $height)
	{
		try {
			$image = \think\Image::open($upload_path . "/" . $file_name);
			$image->thumb($width, $height, $this->thumb_type);
			
			$image->save($upload_path . "/" . $thumb_name, null, 100);
			unset($image);
			//是否添加水印
			if ($this->is_watermark && !empty($this->imgWatermark)) {
				$this->imageWater($upload_path . "/" . $thumb_name, $upload_path . "/" . $thumb_name);
			}
			
			$res = $this->fileStore($upload_path, $thumb_name);
			return $res;
		} catch (\Exception $e) {
			return [ "code" => 0, "message" => $e->getMessage() ];
		}
	}
	
	/**
	 * 添加水印
	 */
	public function imageWater($file_name, $warer_name)
	{
		$source = "";//水印图片
		$type = 1;//水印类型 1 为图片  2为  文字
		try {
			$image = \think\Image::open($file_name);
			if ($type == 1) {
				$locate = $this->waterPosition;//水印位置
				$alpha = $this->transparency;//水印透明度
				$image->water($this->imgWatermark, $locate, $alpha);
			} else {
				$text = "";   //添加的文字
				$font = "";   //字体路径
				$size = "";   //字号
				$color = "";  //文字颜色
				$locate = ""; //文字写入位置
				$offset = ""; //文字相对当前位置的偏移量
				$angle = "";  //文字倾斜角度
				$image->text($text, $font, $size, $color, $locate, $offset, $angle);
			}
			$image->save($warer_name, null, 80);
			unset($image);
			return [ "code" => 1, "data" => $warer_name ];
		} catch (\Exception $e) {
			return [ "code" => 0, "message" => $e->getMessage() ];
		}
	}
	
	/**
	 * 云存储调用
	 */
	public function fileStore($upload_path, $file_name)
	{
		$upload = new UploadExtend('');
		$file_size = $upload->getFileSize($upload_path . "/" . $file_name);
		if ($this->upload_type == 2) {
			$qiniu = new QiNiu();
			$result = $qiniu->setQiniuUplaod($upload_path . "/" . $file_name, $upload_path . "/" . $file_name);
			
			$delete_file_name = substr($upload_path . "/" . $file_name, strlen($this->upload_path . "/"));
			$this->deleteFile($delete_file_name);
			$result["size"] = $file_size;
			return $result;
		}
		
		return [ "code" => 1, 'path' => $upload_path . "/" . $file_name, "size" => $file_size ];
	}
	
	/**
	 * 删除文件
	 */
	public function deleteFile($file_name)
	{
		$file_path = $this->upload_path . "/" . $file_name;
		$res = @unlink($file_path);
		if ($res) {
			return [ "code" => 1, "message" => "文件删除完毕" ];
		} else {
			return [ "code" => 0, "message" => "文件删除失败" ];
		}
		
	}
	
}