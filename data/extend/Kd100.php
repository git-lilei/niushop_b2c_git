<?php
namespace data\extend;

use data\service\Config;

class Kd100
{
    private $key; // 授权key
    private $customer; // 快递100分配的公司编码
    private $http_type;
    
    public function __construct($shop_id){
        $config = new Config();
        $express_config = $config -> getOrderExpressMessageConfig($shop_id);
        if($express_config["is_use"]){
            $this->key = $express_config["value"]["appkey"];
            $this->customer = $express_config["value"]["customer"];
        }else{
            $this->key = "";
            $this->customer = "";
        }
        $this->http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    }
    
    /**
     * 查询物流轨迹免费版
     * @param unknown $express_no 物流公司编码
     * @param unknown $send_no 快递单号
     * @param string $order 排序
     */
    public function getExpressTracesFreeEdition($express_no, $send_no, $order = "asc"){
        
        $url = $this->http_type.'api.kuaidi100.com/api?id='.$this->key.'&com='.$express_no.'&nu='.$send_no.'&show=0&muti=1&order='.$order;
        $result = $this->sendRequest($url, 1);
        return $result;
    }
    
    /**
     * 查询物流轨迹企业版
     * @param unknown $express_no 物流公司编码
     * @param unknown $send_no 快递单号
     */
    public function getExpressTracesEnterpriseEdition($express_no, $send_no){
        $data = array();
        $data["customer"] = $this->customer;
        $data["param"] = '{"com" : "'.$express_no.'", "num" : "'.$send_no.'"}';
        $data["sign"] = md5($data["param"].$this->key.$data["customer"]);
        $data["sign"] = strtoupper($data["sign"]);
        // 测试地址仅可测试100单实际使用时使用“实际地址”
//         $url = $this->http_type."poll.kuaidi100.com/test/poll/query.do"; // 测试地址
        $url = $this->http_type."poll.kuaidi100.com/poll/query.do"; // 实际地址
        $data_url = "";
        foreach ($data as $k => $v)
        {
            $data_url .= "$k=".urlencode($v)."&";		//默认UTF-8编码格式
        }
        $data = substr($data_url, 0, -1);
        $result = $this->sendRequest($url, 2, $data);
        return $result;
    }
    
    
    /**
     * 发送请求
     * @param unknown $url
     * @param unknown $type 1免费版 2企业版
     * @param unknown $data
     */
    public function sendRequest($url, $type, $data = []){
        if (function_exists('curl_init') == 1){
            $curl = curl_init();
            curl_setopt ($curl, CURLOPT_URL, $url);
            curl_setopt ($curl, CURLOPT_HEADER,0);
            curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
            if(!empty($data)){
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            curl_setopt ($curl, CURLOPT_TIMEOUT,5);
            $result = curl_exec($curl);
            $result = json_decode($result, true);
            return $this->handleReturnResult($result, $type);
        }else{
            return $this->handleReturnResult(["status" => -1, "message" => "curl扩展未开启"]); 
        }
    }
    
    /**
     * 处理返回数据
     * @param unknown $data
     */
    public function handleReturnResult($data, $type = 0){
        $result = array();
        switch ($type) {
            case 0:
                // 处理默认返回数据
                if($data["status"] < 0){
                    $result["Success"] = false;
                    $result["Reason"] = $data["message"];
                }
            break;
            case 1:
                // 处理快递100免费版返回数据
                if($data["status"] == 1){
                    $result["Success"] = true;
                    $result["Reason"] = "";
                    $result["content"] = $data["data"];
                }else{
                    $result["Success"] = false;
                    $result["Reason"] = $data["message"];
                }
            break;
            case 2:
                // 处理100企业版返回数据
                if(isset($data["result"])){
                    $result["Success"] = false;
                    $result["Reason"] = $data["message"];
                }else{
                    $result["Success"] = true;
                    $result["Reason"] = "";
                    $result["content"] = array_reverse($data["data"]);
                }
            break;
        }
        return $result;
    }
}