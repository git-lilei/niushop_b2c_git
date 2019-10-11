<?php
namespace data\extend\unionpay;
use data\extend\unionpay\sdk\AcpService;
use data\extend\unionpay\sdk\SDKConfig;
use data\extend\unionpay\sdk\LogUtil;
header ( 'Content-type:text/html;charset=utf-8' );
class unionpay {
    function __construct()
    {}
    
    public function frontConsume($merId, $orderId, $txnTime, $txnAmt){
        
        ini_set('date.timezone','Asia/Shanghai');

        $params = array(
            //以下信息非特殊情况不需要改动
            'version' => SDKConfig::getSDKConfig()->version,            //版本号                 
            'encoding' => 'utf-8',				  //编码方式
            'txnType' => '01',				      //交易类型
            'txnSubType' => '01',				  //交易子类
            'bizType' => '000201',				  //业务类型
            'frontUrl' =>  SDKConfig::getSDKConfig()->frontUrl,  //前台通知地址
            'backUrl' => SDKConfig::getSDKConfig()->backUrl,	  //后台通知地址
            'signMethod' => SDKConfig::getSDKConfig()->signMethod,	              //签名方法
            'channelType' => '08',	              //渠道类型，07-PC，08-手机
            'accessType' => '0',		          //接入类型
            'currencyCode' => '156',	          //交易币种，境内商户固定156
            
            //TODO 以下信息需要填写
            'merId' => $merId,		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
            'orderId' => $orderId,	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
            'txnTime' => $txnTime,	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
            'txnAmt' => $txnAmt,	//交易金额，单位分，此处默认取demo演示页面传递的参数
            
            // 订单超时时间。
            // 超过此时间后，除网银交易外，其他交易银联系统会拒绝受理，提示超时。 跳转银行网银交易如果超时后交易成功，会自动退款，大约5个工作日金额返还到持卡人账户。
            // 此时间建议取支付时的北京时间加15分钟。
            // 超过超时时间调查询接口应答origRespCode不是A6或者00的就可以判断为失败。
            'payTimeout' => date('YmdHis', strtotime('+15 minutes')),
            
            // 请求方保留域，
            // 透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据。
            // 出现部分特殊字符时可能影响解析，请按下面建议的方式填写：
            // 1. 如果能确定内容不会出现&={}[]"'等符号时，可以直接填写数据，建议的方法如下。
            //    'reqReserved' =>'透传信息1|透传信息2|透传信息3',
            // 2. 内容可能出现&={}[]"'符号时：
            // 1) 如果需要对账文件里能显示，可将字符替换成全角＆＝｛｝【】“‘字符（自己写代码，此处不演示）；
            // 2) 如果对账文件没有显示要求，可做一下base64（如下）。
            //    注意控制数据长度，实际传输的数据长度不能超过1024位。
            //    查询、通知等接口解析时使用base64_decode解base64后再对数据做后续解析。
            //    'reqReserved' => base64_encode('任意格式的信息都可以'),
            
            //TODO 其他特殊用法请查看 special_use_purchase.php
        );
        AcpService::sign($params);
        $uri = SDKConfig::getSDKConfig()->frontTransUrl;
        
        $html_form = AcpService::createAutoFormHtml( $params, $uri );
 
        echo $html_form;
        
    }
    
    /**
     * 后台通知
     */
    public function backReceive(){
        $logger = LogUtil::getLogger();
        
        $res = 0;
        
        if (isset ( $_POST ['signature'] )) {
        
//             $res = AcpService::validate ( $_POST ) ? '验签成功' : '验签失败';
            $is_signature = AcpService::validate ( $_POST ) ? 1 : 0;
            if($is_signature == 1){
                
                $orderId = $_POST ['orderId']; //其他字段也可用类似方式获取
                $respCode = $_POST ['respCode'];
                $merId = $_POST ['merId'];
                $txnTime = $_POST['txnTime'];
                
                //判断respCode=00、A6后，对涉及资金类的交易，请再发起查询接口查询，确定交易成功后更新数据库。
                if($respCode == 00 || $respCode == 'A6'){
                    return 1;
                }
            }else{
               $res = -1;
            }
        } else {
//             echo '签名为空';
        }
        
        return $res;
    }
    
    /**
     * 前台通知
     */
    public function frontReceive(){
        $logger = LogUtil::getLogger();
        if (isset ( $_POST ['signature'] )) {
        
            echo AcpService::validate ( $_POST ) ? '验签成功' : '验签失败';
            $orderId = $_POST ['orderId']; //其他字段也可用类似方式获取
            $respCode = $_POST ['respCode'];
            //判断respCode=00、A6后，对涉及资金类的交易，请再发起查询接口查询，确定交易成功后更新数据库。
        
        } else {
            echo '签名为空';
        }
    }
    
    /**
     * 查询交易记录
     */
    public function query($orderId, $merId, $txnTime){
        
        $params = array(
            //以下信息非特殊情况不需要改动
            'version' => SDKConfig::getSDKConfig()->version,		  //版本号
            'encoding' => 'utf-8',		  //编码方式
            'signMethod' => SDKConfig::getSDKConfig()->signMethod,		  //签名方法
            'txnType' => '00',		      //交易类型
            'txnSubType' => '00',		  //交易子类
            'bizType' => '000000',		  //业务类型
            'accessType' => '0',		  //接入类型
            'channelType' => '07',		  //渠道类型
        
            //TODO 以下信息需要填写
            'orderId' => $orderId,	//请修改被查询的交易的订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数
            'merId' => $merId,	    //商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
            'txnTime' => $txnTime,	//请修改被查询的交易的订单发送时间，格式为YYYYMMDDhhmmss，此处默认取demo演示页面传递的参数
        );
        
        AcpService::sign($params);
        $url = SDKConfig::getSDKConfig()->singleQueryUrl;
        
        $result_arr = AcpService::post ( $params, $url);
        if(count($result_arr)<=0) { //没收到200应答的情况
            return;
        }
        
        if(!AcpService::validate ($result_arr) ){
        	echo "应答报文验签失败<br>\n";
        	return;
        }
        if ($result_arr["respCode"] == "00"){
            if ($result_arr["origRespCode"] == "00"){
                //交易成功
                //TODO
                echo "交易成功。<br>\n";
                return $result_arr;
            } else if ($result_arr["origRespCode"] == "03"
                || $result_arr["origRespCode"] == "04"
                || $result_arr["origRespCode"] == "05"){
                //后续需发起交易状态查询交易确定交易状态
                //TODO
                echo "交易处理中，请稍微查询。<br>\n";
            } else {
                //其他应答码做以失败处理
                //TODO
                echo "交易失败：" . $result_arr["origRespMsg"] . "。<br>\n";
            }
        } else if ($result_arr["respCode"] == "03"
            || $result_arr["respCode"] == "04"
            || $result_arr["respCode"] == "05" ){
            //后续需发起交易状态查询交易确定交易状态
            //TODO
            echo "处理超时，请稍微查询。<br>\n";
        } else {
            //其他应答码做以失败处理
            //TODO
            echo "失败：" . $result_arr["respMsg"] . "。<br>\n";
        }
        
        return 0;
    }
    
    /**
     * 退货退款
     */
    public function refund($orderId, $merId, $origQryId, $txnTime, $txnAmt){
        $params = array(
        
            //以下信息非特殊情况不需要改动
            'version' => SDKConfig::getSDKConfig()->version,		      //版本号
            'encoding' => 'utf-8',		      //编码方式
            'signMethod' => SDKConfig::getSDKConfig()->signMethod,		      //签名方法
            'txnType' => '04',		          //交易类型
            'txnSubType' => '00',		      //交易子类
            'bizType' => '000201',		      //业务类型
            'accessType' => '0',		      //接入类型
            'channelType' => '07',		      //渠道类型
            'backUrl' => SDKConfig::getSDKConfig()->backUrl, //后台通知地址
        
            //TODO 以下信息需要填写
            'orderId' => $orderId,	    //商户订单号，8-32位数字字母，不能含“-”或“_”，可以自行定制规则，重新产生，不同于原消费，此处默认取demo演示页面传递的参数
            'merId' => $merId,	        //商户代码，请改成自己的测试商户号，此处默认取demo演示页面传递的参数
            'origQryId' => $origQryId, //原消费的queryId，可以从查询接口或者通知接口中获取，此处默认取demo演示页面传递的参数
            'txnTime' => $txnTime,	    //订单发送时间，格式为YYYYMMDDhhmmss，重新产生，不同于原消费，此处默认取demo演示页面传递的参数
            'txnAmt' => $txnAmt,       //交易金额，退货总金额需要小于等于原消费
        
            // 请求方保留域，
            // 透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据。
            // 出现部分特殊字符时可能影响解析，请按下面建议的方式填写：
            // 1. 如果能确定内容不会出现&={}[]"'等符号时，可以直接填写数据，建议的方法如下。
            //    'reqReserved' =>'透传信息1|透传信息2|透传信息3',
            // 2. 内容可能出现&={}[]"'符号时：
            // 1) 如果需要对账文件里能显示，可将字符替换成全角＆＝｛｝【】“‘字符（自己写代码，此处不演示）；
            // 2) 如果对账文件没有显示要求，可做一下base64（如下）。
            //    注意控制数据长度，实际传输的数据长度不能超过1024位。
            //    查询、通知等接口解析时使用base64_decode解base64后再对数据做后续解析。
            //    'reqReserved' => base64_encode('任意格式的信息都可以'),
        );
        AcpService::sign ( $params ); // 签名
        $url = SDKConfig::getSDKConfig()->backTransUrl;
        
        $result_arr = AcpService::post ( $params, $url);
        if(count($result_arr)<=0) { //没收到200应答的情况
//             printResult ( $url, $params, "" );
            return;
        }
        
        if (!AcpService::validate ($result_arr) ){
            echo "应答报文验签失败<br>\n";
            return;
        }
        
        if ($result_arr["respCode"] == "00"){
            //交易已受理，等待接收后台通知更新订单状态，如果通知长时间未收到也可发起交易状态查询
            //TODO
            echo "受理成功。<br>\n";
        } else if ($result_arr["respCode"] == "03"
            || $result_arr["respCode"] == "04"
            || $result_arr["respCode"] == "05" ){
            //后续需发起交易状态查询交易确定交易状态
            //TODO
            echo "处理超时，请稍微查询。<br>\n";
        } else {
            //其他应答码做以失败处理
            //TODO
            echo "失败：" . $result_arr["respMsg"] . "。<br>\n";
        }
    }
}