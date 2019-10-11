<?php
/**
 * WchatApplet.php
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

namespace app\api\controller;

class WchatApplet
{
	
	private $appid;
	
	private $sessionKey;
	
	/**
	 * error code 说明.
	 * <ul>
	 *
	 * <li>-41001: encodingAesKey 非法</li>
	 * <li>-41002: aes 解密失败</li>
	 * <li>-41003: 解密后得到的buffer非法</li>
	 * <li>-41014: base64解密失败</li>
	 * </ul>
	 */
	private $OK = 0;
	
	private $IllegalAesKey = -41001;
	
	private $IllegalIv = -41002;
	
	private $IllegalBuffer = -41003;
	
	private $DecodeBase64Error = -41004;
	
	/**
	 * 构造函数
	 *
	 * @param $sessionKey string
	 *            用户在小程序登录后获取的会话密钥
	 * @param $appid string
	 *            小程序的appid
	 */
	public function __construct($appid, $sessionKey)
	{
		$this->sessionKey = $sessionKey;
		$this->appid = $appid;
	}
	
	/**
	 * 检验数据的真实性，并且获取解密后的明文.
	 *
	 * @param $encryptedData string
	 *            加密的用户数据
	 * @param $iv string
	 *            与用户数据一同返回的初始向量
	 * @param $data string
	 *            解密后的原文
	 *
	 * @return int 成功0，失败返回对应的错误码
	 */
	public function decryptData($encryptedData, $iv, &$data)
	{
	    $this->sessionKey = stripslashes($this->sessionKey);
		if (strlen($this->sessionKey) != 24) {
			return $this->IllegalAesKey;
		}
		$aesKey = base64_decode($this->sessionKey);
		
		if (strlen($iv) != 24) {
			return $this->IllegalIv;
		}
		$aesIV = base64_decode($iv);
		
		$aesCipher = base64_decode($encryptedData);
		
		$result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
		
		$dataObj = json_decode($result);
		if ($dataObj == NULL) {
			return $this->IllegalBuffer;
		}
		if ($dataObj->watermark->appid != $this->appid) {
			return $this->IllegalBuffer;
		}
		$data = $result;
		return $this->OK;
	}
	
	/**
	 * 获取用户微信信息
	 */
	public function getWchatInfo($encryptedData, $iv, $data)
	{
		$errCode = $this->decryptData($encryptedData, $iv, $data);
		if ($errCode == 0) {
			return $data;
		} else {
			return $errCode;
		}
	}
}