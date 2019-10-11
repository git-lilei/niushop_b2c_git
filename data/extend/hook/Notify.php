<?php
namespace data\extend\hook;

use addons\Nsfx\data\model\NfxCommissionDistributionModel;
use addons\Nsfx\data\model\NfxPromoterModel;
use addons\Nsfx\data\model\NfxUserCommissionWithdrawModel;
use data\model\NoticeTemplateTypeModel;
use data\model\NsMemberBalanceWithdrawModel;
use data\model\WebSiteModel;
use data\model\UserModel;
use data\model\ConfigModel;
use data\model\NoticeTemplateModel;
use data\model\NsOrderGoodsModel;
use data\model\NsOrderModel;
use data\model\NsOrderGoodsExpressModel;
use data\model\NsOrderPaymentModel;
use data\service\Member\MemberAccount;
use data\service\Notice;
use addons\NsPintuan\data\model\NsTuangouGroupModel;
use addons\NsBargain\data\model\NsPromotionBargainLaunchModel;
use data\model\NsGoodsModel;
use data\service\OrderQuery;
use data\service\User;
use data\service\Config;
use addons\NsAlisms\data\service\AlismsConfig;


class Notify
{
    public $result=array(
        "code"=>0,
        "message"=>"success",
        "param"=>""
    );
    /**
     * 邮件的配置信息
     * @var unknown
     */
    public $email_is_open=0;
    public $email_host="";
    public $email_port="";
    public $email_addr="";
    public $email_id="";
    public $email_pass="";
    public $email_is_security=false;
    public $shop_name;
    public $mobile_is_open = 1;
    public $ali_use_type = 1;//参数
    public $shop_notify_config;
    public $sms_config;
    public $email_config;
    public function sendMessage($tag, $params)
    {
        //查库查询对应的模板介绍类型
        $notice_template_type_model = new NoticeTemplateTypeModel();
        $type_info = $notice_template_type_model->getInfo(["template_code" => $tag]);
        $params["tag"] = $tag;
        if(empty($type_info["addon_name"]))
        {
            //判断当前类中是否包含某个函数
            if(is_callable(array($this,$type_info["function_name"]))) {
                $this->getShopNotifyInfo(0);//消息配置
                $function_name = $type_info["function_name"];
                $result = $this->$function_name($params);//调用函数
            }
        }else{
            //存在
            $result = hook("sysMessageSend",['addon_name'=> $type_info["addon_name"], 'tag' => $tag, 'param' => $params]);
        }
        return $result;
    }
    
    /**
     * 得到系统通知的配置信息
     * @param unknown $shop_id
     */
    private function getShopNotifyInfo($shop_id){
        
        $website_model=new WebSiteModel();
        $website_obj=$website_model->getInfo("1=1", "title");
        if(empty($website_obj)){
            $this->shop_name="NiuShop开源商城";
        }else{
            $this->shop_name=$website_obj["title"];
        }
        
        $config_model=new ConfigModel();
        #查看邮箱是否开启
        $email_info=$config_model->getInfo(["instance_id"=>$shop_id, "key"=>"EMAILMESSAGE"], "*");
        if(!empty($email_info)){
            $this->email_is_open=$email_info["is_use"];
            $value=$email_info["value"];
            if(!empty($value)){
                $email_array=json_decode($value, true);
                $this->email_host=$email_array["email_host"];
                $this->email_port=$email_array["email_port"];
                $this->email_addr=$email_array["email_addr"];
                $this->email_id=$email_array["email_id"];
                $this->email_pass=$email_array["email_pass"];
                $this->email_is_security=$email_array["email_is_security"];
            }
        }
        //商家消息设置
        $config_service = new Config();
        $shop_notift_info = $config_service->getShopNotifyConfig();
        $this->shop_notify_config = json_decode($shop_notift_info["value"], true);
        $ali_sms_config = new AlismsConfig();
        $sms_config = $ali_sms_config->getMobileMessage(0);
        $this->sms_config = $sms_config;
        $email_config = $config_service->getEmailMessage(0);
        $this->email_config = $email_config;
    }
    
    /**
     * 查询模板的信息
     * @param unknown $shop_id
     * @param unknown $template_code
     * @param unknown $type
     * @return unknown
     */
    private function getTemplateDetail($shop_id, $template_code, $type, $notify_type = "user"){
        $template_model=new NoticeTemplateModel();
        $template_obj=$template_model->getInfo(["instance_id"=>$shop_id, "template_type"=>$type, "template_code"=>$template_code, "notify_type" => $notify_type]);
        return $template_obj;
    }
    /**
     * 用户注册成功后
     * @param string $params
     */
    public function registAfter($params=null){
        
        $shop_id= isset($params["shop_id"]) ? $params["shop_id"] : 0;//店铺id
        #查询系统配置信息
        $this->getShopNotifyInfo(0);
        
        $user_id=$params["user_id"];//用户id
        
        $user_model=new UserModel();
        $user_obj=$user_model->get($user_id);
        $mobile="";
        $user_name="";
        $email="";
        if(empty($user_obj)){
            $user_name="用户";
        }else{
            $user_name = $user_obj["nick_name"];
            $mobile=$user_obj["user_tel"];
            $email=$user_obj["user_email"];
        }
        //短信验证
        if(!empty($mobile) && $this->mobile_is_open==1){
            
            $template_obj=$this->getTemplateDetail($shop_id, "after_register", "sms");
            if(!empty($template_obj) && $template_obj["is_enable"]==1){
                
                $sms_params=array(
                    "shopname"=>$this->shop_name,
                    "username"=>$user_name
                );
                $this->createNoticeSmsRecords($template_obj, $shop_id, $params["user_id"], $mobile, $sms_params, "注册成功短信通知", 17);
            }
        }
        //邮箱验证
        if(!empty($email) && $this->email_is_open==1){
            $template_obj=$this->getTemplateDetail($shop_id, "after_register", "email");
            if(!empty($template_obj) && $template_obj["is_enable"]==1){
                $content=$template_obj["template_content"];
                $content=str_replace("{商场名称}", $this->shop_name, $content);
                $content=str_replace("{用户名称}", $user_name, $content);
                $send_title=$template_obj["template_title"];
                $send_title=str_replace("{商场名称}", $this->shop_name, $send_title);
                $send_title=str_replace("{用户名称}", $user_name, $send_title);
                // $result=emailSend($this->email_host, $this->email_id, $this->email_pass, $this->email_port, $this->email_is_security, $this->email_addr, $email, $send_title, $content, $this->shop_name);
                $this->createNoticeEmailRecords($shop_id, $params["user_id"], $email, $send_title, $content, 5);
            }
        }
        //模板消息
        //         if(addon_is_exit("NsWxtemplatemsg"))
            //         {
            //             $keyword = array(
            //                 "keyword1" => $user_name,//会员昵称
            //                 "keyword2" => getTimeStampTurnTime($user_obj['reg_time'])//注册时间
            //             );
            //             $param = [
            //                 'uid'      => $user_id,
            //                 'template_no' => 'OPENTM203347141',
            //                 'url'         => '',
            //                 'keyword'    => $keyword,
            //                 "template_code" => $params["tag"]
            //             ];
            //             hook("sendWxTemplateMsg", $param);
            //         }
    }
    
    /**
     * 注册发送验证
     * @param $param
     */
    public function registValidation($param){
        if($param["type"] == "sms"){
            $result = $this->registSmsValidation($param);
        }else if($param["type"] == "email"){
            $result = $this->registEmailValidation($param);
        }
        return $result;
    }
    /**
     * 注册短信验证
     * @param string $params
     */
    public function registSmsValidation($params=null){
        $rand = rand(100000,999999);
        $mobile=$params["mobile"];
        $shop_id=$params["shop_id"];
        #查询系统配置信息
        $this->getShopNotifyInfo($shop_id);
        $result="";
        
        if(!empty($mobile) && $this->mobile_is_open==1){
            $template_obj=$this->getTemplateDetail($shop_id, "register_validate", "sms");
            if(!empty($template_obj) && $template_obj["is_enable"]==1){
                $sms_params=array(
                    "number"=>$rand.""
                );
                $result = hook('smssend', [
                    'signName' => $template_obj["sign_name"],
                    'smsParam' => json_encode($sms_params),
                    'mobile'   => $mobile,
                    'code'     => $template_obj["template_title"]
                ]);
                $result = arrayFilter($result);
                if(empty($result))
                {
                    $this->result["code"]= -1;
                    $this->result["message"]="店家没有开启短信验证";
                    $this->result["param"]=0;
                }else{
                    $result = $result[0];
                    if($result['param'] != 0)
                    {
                        $this->result["param"]=$rand;
                    }else{
                        
                        $this->result["param"]=0;
                    }
                    $this->result["code"]= $result['code'];
                    $this->result["message"]=$result['message'];
                }
                
            }else{
                $this->result["code"]=-1;
                $this->result["message"]="短信通知模板有误!";
                $this->result["param"]=0;
            }
        }else{
            $this->result["code"]=-1;
            $this->result["message"]="店家没有开启短信验证";
            $this->result["param"]=0;
        }
        
        $send_result = $this->result["code"] < 0 ? -1 : 1;
        $this->result['record_id'] = $this->createVerificationCodeRecords($template_obj, $shop_id, 0, 1, $mobile, 9, "用户注册短信验证码", json_encode($sms_params), $this->result["message"], $send_result);
        return $this->result;
    }
    
    /**
     * 注册邮箱验证
     * @param string $params
     */
    public function registEmailValidation($params=null){
        $rand = rand(100000,999999);
        $email=$params["email"];
        $shop_id=$params["shop_id"];
        #查询系统配置信息
        $this->getShopNotifyInfo($shop_id);
        if(!empty($email) && $this->email_is_open==1){
            $template_obj=$this->getTemplateDetail($shop_id, "register_validate", "email");
            if(!empty($template_obj) && $template_obj["is_enable"]==1){
                $content=$template_obj["template_content"];
                $content=str_replace("{验证码}", $rand, $content);
                if(!empty($this->email_host) && !empty($this->email_id) && !empty($this->email_pass) && !empty($this->email_addr)){
                    $result=emailSend($this->email_host, $this->email_id, $this->email_pass, $this->email_port, $this->email_is_security, $this->email_addr, $email, $template_obj["template_title"], $content, $this->shop_name);
                    $this->result["param"]=$rand;
                    if($result){
                        $this->result["code"]=0;
                        $this->result["message"]="发送成功!";
                    }else{
                        $this->result["code"]=-1;
                        $this->result["message"]="发送失败!";
                    }
                }else{
                    $this->result["code"]=-1;
                    $this->result["message"]="邮箱配置信息有误!";
                }
            }else{
                $this->result["code"]=-1;
                $this->result["message"]="配置邮箱注册验证模板有误!";
            }
        }else{
            $this->result["code"]=-1;
            $this->result["message"]="店家没有开启邮箱验证";
        }
        $send_result = $this->result["code"] < 0 ? -1 : 1;
        $this->result['record_id'] = $this->createVerificationCodeRecords($template_obj, $shop_id, 0, 2, $email, 10, "用户注册邮箱验证码", json_encode(['number' => $rand]), $this->result["message"], $send_result);
        
        return $this->result;
        
    }
    /**
     * 订单发货
     * @param string $params
     */
    public function orderDelivery($params=null){
        #查询系统配置信息
        $this->getShopNotifyInfo(0);
        $order_goods_ids=$params["order_goods_ids"];
        $order_goods_str=explode(",", $order_goods_ids);
        $order_model=new NsOrderModel();
        $user_model = new UserModel();
        if(count($order_goods_str)>0){
            $order_goods_id=$order_goods_str[0];
            $order_goods_model=new NsOrderGoodsModel();
            $order_goods_obj=$order_goods_model->get($order_goods_id);
            $shop_id=$order_goods_obj["shop_id"];
            $order_id=$order_goods_obj["order_id"];
            $order_obj=$order_model->get($order_id);
            $buyer_id=$order_obj["buyer_id"];
            $user_obj = $user_model->get($buyer_id);
            $user_name=$user_obj["nick_name"];
            $goods_name=$order_goods_obj["goods_name"];
            $goods_sku=mb_substr($order_goods_obj["sku_name"],0,19,"utf-8");
            $goods_name = mb_substr($goods_name, 0, 19, "utf-8");
            $order_no=$order_obj["order_no"];
            $order_money=$order_obj["order_money"];
            $goods_money=$order_goods_obj["goods_money"];
            $mobile=$order_obj["receiver_mobile"];
            $goods_express_model=new NsOrderGoodsExpressModel();
            $express_obj=$goods_express_model->getInfo(["order_id"=>$order_id, "order_goods_id_array"=>$order_goods_ids], "*");
            $express_obj["express_name"] = $express_obj["express_name"] != null ? $express_obj["express_name"] : '';
            $express_obj["express_no"] = $express_obj["express_no"] != null ? $express_obj["express_no"] : '';
            $sms_params=array(
                "shopname"=>$this->shop_name,
                "username"=>$user_name,
                "goodsname"=>$goods_name,
                "goodssku"=>$goods_sku,
                "orderno"=>$order_no,
                "ordermoney"=>$order_money,
                "goodsmoney"=>$goods_money,
                "expresscompany"=>$express_obj["express_name"],
                "expressno"=>$express_obj["express_no"]
            );
            #短信发送
            if(!empty($mobile) && $this->mobile_is_open==1){
                $template_obj=$this->getTemplateDetail($shop_id, "order_deliver", "sms");
                if(!empty($template_obj) && $template_obj["is_enable"]==1){
                    $this->createNoticeSmsRecords($template_obj, $shop_id, $buyer_id, $mobile, $sms_params, "订单发货短信发送", 5);
                }
            }
            // 邮件发送
            if (!empty($user_obj)) {
                $email = $user_obj["user_email"];
                if (! empty($email) && $this->email_is_open == 1) {
                    $template_obj = $this->getTemplateDetail($shop_id, "order_deliver", "email");
                    if (! empty($template_obj) && $template_obj["is_enable"] == 1) {
                        $content = $template_obj["template_content"];
                        $content = str_replace("{商场名称}", $this->shop_name, $content);
                        $content = str_replace("{用户名称}", $user_name, $content);
                        $content = str_replace("{商品名称}", $goods_name, $content);
                        $content = str_replace("{商品规格}", $goods_sku, $content);
                        $content = str_replace("{主订单号}", $order_no, $content);
                        $content = str_replace("{订单金额}", $order_money, $content);
                        $content = str_replace("{商品金额}", $goods_money, $content);
                        $content = str_replace("{物流公司}", $express_obj["express_name"], $content);
                        $content = str_replace("{快递编号}", $express_obj["express_no"], $content);
                        
                        $send_title=$template_obj["template_title"];
                        $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
                        $send_title = str_replace("{用户名称}", $user_name, $send_title);
                        $send_title = str_replace("{商品名称}", $goods_name, $send_title);
                        $send_title = str_replace("{商品规格}", $goods_sku, $send_title);
                        $send_title = str_replace("{主订单号}", $order_no, $send_title);
                        $send_title = str_replace("{订单金额}", $order_money, $send_title);
                        $send_title = str_replace("{商品金额}", $goods_money, $send_title);
                        $send_title = str_replace("{物流公司}", $express_obj["express_name"], $send_title);
                        $send_title = str_replace("{快递编号}", $express_obj["express_no"], $send_title);
                        $this->createNoticeEmailRecords($shop_id, $buyer_id, $email, $send_title, $content, 5);
                    }
                }
            }
            
            //模板消息
            if(addon_is_exit("NsWxtemplatemsg"))
            {
                $keyword = array(
                    'keyword1'    => $order_obj['order_no'],
                    'keyword2'    => $express_obj["express_name"] != '' ? $express_obj["express_name"] : '无需物流',
                    'keyword3'    => $express_obj["express_no"] != '' ? $express_obj["express_no"] : '无快递单号',
                );
                $param = [
                    'uid'      => $buyer_id,
                    'template_no' => 'OPENTM201541214',
                    'url'         =>__URL(__URL__ . "/wap/order/detail?order_id=$order_id"),
                    'keyword'    => $keyword,
                    'first' => "",
                    "template_code" => $params["tag"]
                ];
                hook("sendWxTemplateMsg", $param);
            }
        }
    }
    /**
     * 订单完成
     * @param string $params
     */
    public function orderComplete($params=null){
        #查询系统配置信息
        $this->getShopNotifyInfo(0);
        $order_id=$params["order_id"];
        $order_model=new NsOrderModel();
        $order_obj=$order_model->getInfo(["order_id" => $order_id]);
        $shop_id=$order_obj["shop_id"];
        $buyer_id=$order_obj["buyer_id"];
        $user_name=$order_obj["receiver_name"];
        $order_no=$order_obj["order_no"];
        $order_money=$order_obj["order_money"];
        $mobile=$order_obj["receiver_mobile"];
        $sms_params = array(
            "shopname"=>$this->shop_name,
            "username"=>$user_name,
            "orderno"=>$order_no,
            "ordermoney"=>$order_money
        );
        // 短信发送
        if (! empty($mobile) && $this->mobile_is_open == 1) {
            $template_obj = $this->getTemplateDetail($shop_id, "order_complete", "sms");
            if (! empty($template_obj) && $template_obj["is_enable"] == 1) {
                $this->createNoticeSmsRecords($template_obj, $shop_id, $buyer_id, $mobile, $sms_params, "订单确认短信发送", 2);
            }
        }
        // 邮件发送
        $user_model = new UserModel();
        $user_obj = $user_model->get($buyer_id);
        if (! empty($user_obj)) {
            
            $email = $user_obj["user_email"];
            if (! empty($email) && $this->email_is_open == 1) {
                $template_obj = $this->getTemplateDetail($shop_id, "order_complete", "email");
                if (! empty($template_obj) && $template_obj["is_enable"] == 1) {
                    $content = $template_obj["template_content"];
                    $content = str_replace("{商场名称}", $this->shop_name, $content);
                    $content=str_replace("{用户名称}", $user_name, $content);
                    $content=str_replace("{主订单号}", $order_no, $content);
                    $content=str_replace("{订单金额}", $order_money, $content);
                    
                    $send_title = $template_obj["template_title"];
                    $send_title= str_replace("{商场名称}", $this->shop_name, $send_title);
                    $send_title=str_replace("{用户名称}", $user_name, $send_title);
                    $send_title=str_replace("{主订单号}", $order_no, $send_title);
                    $send_title=str_replace("{订单金额}", $order_money, $send_title);
                    $this->createNoticeEmailRecords($shop_id, $buyer_id, $email, $send_title, $content, 2);
                }
            }
        }
        //模板消息
        if(addon_is_exit("NsWxtemplatemsg"))
        {
            $uid = $order_obj['buyer_id'];
            $url = __URL(__URL__ . "/wap/order/detail?order_id=$order_id");
            $keyword = array();
            $keyword["keyword1"] = $order_obj["order_no"];//完成时间
            $keyword["keyword2"] = getTimeStampTurnTime($order_obj["finish_time"]);//订单编号
            $keyword["keyword3"] = "已完成";//完成状态
            $param = array(
                "uid" => $uid,
                "template_no" => 'OPENTM414529660',
                "first" => '',
                "url" => $url,
                "keyword" => $keyword,
                "template_code" => $params["tag"]
            );
            hook("sendWxTemplateMsg", $param);
        }
    }
    /**
     * 订单付款成功
     * @param string $params
     */
    public function orderPay($params=null){
        #查询系统配置信息
        $this->getShopNotifyInfo(0);
        
        $order_id=$params["order_id"];
        $order_model=new NsOrderModel();
        $order_obj=$order_model->get($order_id);
        $shop_id=$order_obj["shop_id"];
        $buyer_id=$order_obj["buyer_id"];
        $user_name=$order_obj["receiver_name"];
        $order_no=$order_obj["order_no"];
        $order_money=$order_obj["order_money"];
        $mobile=$order_obj["receiver_mobile"];
        $goods_money=$order_obj["goods_money"];
        $sms_params=array(
            "shopname"=>$this->shop_name,
            "username"=>$user_name,
            "orderno"=>$order_no,
            "ordermoney"=>$order_money,
            "goodsmoney"=>$goods_money
        );
        if(!empty($mobile) && $this->mobile_is_open == 1){
            $template_obj = $this->getTemplateDetail($shop_id, "pay_success", "sms");
            if(!empty($template_obj) && $template_obj["is_enable"]==1){
                $this->createNoticeSmsRecords($template_obj, $shop_id, $buyer_id, $mobile, $sms_params, "订单付款成功通知", 3);
            }
        }
        $user_model=new UserModel();
        $user_obj=$user_model->get($buyer_id);
        if(!empty($user_obj)){
            $email=$user_obj["user_email"];
            if(!empty($email) && $this->email_is_open==1){
                $template_obj=$this->getTemplateDetail($shop_id, "pay_success", "email");
                if(!empty($template_obj) && $template_obj["is_enable"]==1){
                    $content=$template_obj["template_content"];
                    $content=str_replace("{商场名称}", $this->shop_name, $content);
                    $content=str_replace("{用户名称}", $user_name, $content);
                    $content=str_replace("{主订单号}", $order_no, $content);
                    $content=str_replace("{订单金额}", $order_money, $content);
                    $content=str_replace("{商品金额}", $goods_money, $content);
                    $send_title=$template_obj["template_title"];
                    $send_title=str_replace("{商场名称}", $this->shop_name, $send_title);
                    $send_title=str_replace("{用户名称}", $user_name, $send_title);
                    $send_title=str_replace("{主订单号}", $order_no, $send_title);
                    $send_title=str_replace("{订单金额}", $order_money, $send_title);
                    $send_title=str_replace("{商品金额}", $goods_money, $send_title);
                    $this->createNoticeEmailRecords($shop_id, $buyer_id, $email, $send_title, $content, 3);
                }
            }
        }
        //模板消息
        if(addon_is_exit("NsWxtemplatemsg"))
        {
            $uid = $order_obj['buyer_id'];
            $url = __URL(__URL__ . "/wap/order/detail?order_id=$order_id");
            $keyword = array();
            $keyword["keyword1"] = $order_obj["order_no"];//付款时间
            $keyword["keyword2"] = getTimeStampTurnTime($order_obj["pay_time"]);//订单编号
            $keyword["keyword3"] = $order_obj["pay_money"];//支付金额
            $order_query = new OrderQuery();
            $pay_type_info = $order_query->getPayTypeInfo([ "pay_type" => $order_obj['payment_type'] ]);
            $pay_type_name = $pay_type_info["type_name"];
            $keyword["keyword4"] = $pay_type_name;//支付方式
            $param = array(
                "uid" => $uid,
                "template_no" => 'OPENTM200444326',
                "first" => '',
                "url" => $url,
                "keyword" => $keyword,
                "template_code" => $params["tag"]
            );
            hook("sendWxTemplateMsg", $param);
        }
    }
    /**
     * 订单创建成功
     * @param string $params
     */
    public function orderCreate($params=null){
        #查询系统配置信息
        $this->getShopNotifyInfo(0);
        $order_id=$params["order_id"];
        $order_model=new NsOrderModel();
        $order_obj=$order_model->get($order_id);
        $shop_id=$order_obj["shop_id"];
        $buyer_id=$order_obj["buyer_id"];
        $user_name=$order_obj["receiver_name"];
        $order_no=$order_obj["order_no"];
        $order_money=$order_obj["order_money"];
        $mobile=$order_obj["receiver_mobile"];
        $goods_money=$order_obj["goods_money"];
        $sms_params=array(
            "shopname"=>$this->shop_name,
            "username"=>$user_name,
            "orderno"=>$order_no,
            "ordermoney"=>$order_money,
            "goodsmoney"=>$goods_money
        );
        #短信发送
        if(!empty($mobile) && $this->mobile_is_open==1){
            $template_obj=$this->getTemplateDetail($shop_id, "create_order", "sms");
            if(!empty($template_obj) && $template_obj["is_enable"]==1){
                $this->createNoticeSmsRecords($template_obj, $shop_id, $buyer_id, $mobile, $sms_params, "订单创建成功通知", 4);
            }
        }
        // 邮件发送
        $user_model = new UserModel();
        $user_obj = $user_model->get($buyer_id);
        if (! empty($user_obj)) {
            $email = $user_obj["user_email"];
            if (! empty($email) && $this->email_is_open == 1) {
                $template_obj = $this->getTemplateDetail($shop_id, "create_order", "email");
                if (! empty($template_obj) && $template_obj["is_enable"] == 1) {
                    $content = $template_obj["template_content"];
                    $content = str_replace("{商场名称}", $this->shop_name, $content);
                    $content = str_replace("{用户名称}", $user_name, $content);
                    $content = str_replace("{主订单号}", $order_no, $content);
                    $content = str_replace("{订单金额}", $order_money, $content);
                    $content = str_replace("{商品金额}", $goods_money, $content);
                    $send_title=$template_obj["template_title"];
                    $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
                    $send_title = str_replace("{用户名称}", $user_name, $send_title);
                    $send_title = str_replace("{主订单号}", $order_no, $send_title);
                    $send_title = str_replace("{订单金额}", $order_money, $send_title);
                    $send_title = str_replace("{商品金额}", $goods_money, $send_title);
                    $this->createNoticeEmailRecords($shop_id, $buyer_id, $email, $send_title, $content, 4);
                }
            }
        }
        //模板消息
        if(addon_is_exit("NsWxtemplatemsg"))
        {
            $uid = $order_obj['buyer_id'];
            $url = __URL(__URL__ . "/wap/order/detail?order_id=$order_id");
            $keyword = array();
            $keyword["keyword1"] = $order_obj["order_no"];//付款时间
            $keyword["keyword2"] = getTimeStampTurnTime($order_obj["create_time"]);//订单编号
            $keyword["keyword3"] = $order_obj["order_money"];//支付金额
            $order_query = new OrderQuery();
            $pay_type_info = $order_query->getPayTypeInfo([ "pay_type" => $order_obj['payment_type'] ]);
            $pay_type_name = $pay_type_info["type_name"];
            $keyword["keyword4"] = $pay_type_name;//支付方式
            $param = array(
                "uid" => $uid,
                "template_no" => 'OPENTM200444240',
                "first" => '订单创建成功',
                "url" => $url,
                "keyword" => $keyword,
                "title" => "",
                "template_code" => $params["tag"]
            );
            hook("sendWxTemplateMsg", $param);
        }
    }
    /**
     * 找回密码
     * @param string $params
     * @return multitype:number string
     */
    public function forgotPassword($params=null){
        $send_type=$params["send_type"];
        $send_param=$params["send_param"];
        $shop_id=$params["shop_id"];
        $this->getShopNotifyInfo($shop_id);
        $rand = rand(100000,999999);
        $template_obj=$this->getTemplateDetail($shop_id, "forgot_password", $send_type);
        if($send_type=="email"){
            #邮箱验证
            if($this->email_is_open==1){
                if(!empty($template_obj) && $template_obj["is_enable"]==1){
                    #发送
                    $content=$template_obj["template_content"];
                    $content=str_replace("{验证码}", $rand, $content);
                    $result=emailSend($this->email_host, $this->email_id, $this->email_pass, $this->email_port, $this->email_is_security, $this->email_addr, $send_param, $template_obj["template_title"], $content, $this->shop_name);
                    $this->result["param"]=$rand;
                    if($result){
                        $this->result["code"]=0;
                        $this->result["message"]="发送成功!";
                    }else{
                        $this->result["code"]=-1;
                        $this->result["message"]="发送失败!";
                    }
                }else{
                    $this->result["code"]=-1;
                    $this->result["message"]="商家没有设置找回密码的模板!";
                }
            }else{
                $this->result["code"]=-1;
                $this->result["message"]="商家没开启邮箱验证!";
            }
            $send_result = $this->result["code"] < 0 ? -1 : 1;
            
            $this->result['record_id'] = $this->createVerificationCodeRecords($template_obj, $shop_id, 0, 2, $send_param, 11, "找回密码邮箱验证码", json_encode(['number' => $rand]), $this->result["message"], $send_result);
            
        }else{
            #短信验证
            if($this->mobile_is_open==1){
                if(!empty($template_obj) && $template_obj["is_enable"]==1){
                    #发送
                    $sms_params=array(
                        "number"=>$rand.""
                    );
                    
                    $result = hook('smssend', [
                        'signName' => $template_obj["sign_name"],
                        'smsParam' => json_encode($sms_params),
                        'mobile'   => $send_param,
                        'code'     => $template_obj["template_title"]
                    ]);
                    $result = arrayFilter($result);
                    if(empty($result))
                    {
                        $this->result["code"]= -1;
                        $this->result["message"]="店家没有开启短信验证";
                        $this->result["param"]=0;
                    }else{
                        $result = $result[0];
                        if($result['param'] != 0)
                        {
                            $this->result["param"]=$rand;
                        }else{
                            
                            $this->result["param"]=0;
                        }
                        $this->result["code"]= $result['code'];
                        $this->result["message"]=$result['message'];
                    }
                }else{
                    $this->result["code"]=-1;
                    $this->result["message"]="商家没有设置找回密码的短信模板!";
                }
            }else{
                $this->result["code"]=-1;
                $this->result["message"]="商家没开启短信验证!";
            }
            $send_result = $this->result["code"] < 0 ? -1 : 1;
            $this->result['record_id'] = $this->createVerificationCodeRecords($template_obj, $shop_id, 0, 1, $send_param, 12, "找回密码短信验证码", json_encode($sms_params), $this->result["message"], $send_result);
        }
        return $this->result;
    }
    /**
     * 用户绑定手机号
     * @param string $params
     */
    public function bindMobile($params=null){
        $rand = rand(100000,999999);
        $mobile=$params["mobile"];
        $shop_id=$params["shop_id"];
        $user_id=$params["user_id"];
        #查询系统配置信息
        $this->getShopNotifyInfo($shop_id);
        if(!empty($mobile) && $this->mobile_is_open==1){
            $template_obj=$this->getTemplateDetail($shop_id, "bind_mobile", "sms");
            if(!empty($template_obj) && $template_obj["is_enable"]==1){
                $sms_params=array(
                    "number"=>$rand."",
                );
                $this->result["param"]=$rand;
                
                $result = hook('smssend', [
                    'signName' => $template_obj["sign_name"],
                    'smsParam' => json_encode($sms_params),
                    'mobile'   => $mobile,
                    'code'     => $template_obj["template_title"]
                ]);
                $result = arrayFilter($result);
                if(empty($result))
                {
                    $this->result["code"]= -1;
                    $this->result["message"]="店家没有开启短信验证";
                    $this->result["param"]=0;
                }else{
                    $result = $result[0];
                    if($result['param'] != 0)
                    {
                        $this->result["param"]=$rand;
                    }else{
                        
                        $this->result["param"]=0;
                    }
                    $this->result["code"]= $result['code'];
                    $this->result["message"]=$result['message'];
                }
                
            }else{
                $this->result["code"]=-1;
                $this->result["message"]="短信通知模板有误!";
            }
        }else{
            $this->result["code"] = -1;
            $this->result["message"]="店家没有开启短信验证";
        }
        $send_result = $this->result["code"] < 0 ? -1 : 1;
        $this->result['record_id'] = $this->createVerificationCodeRecords($template_obj, $shop_id, 0, 1, $mobile, 13, "绑定手机号短信验证码", json_encode($sms_params), $this->result["message"], $send_result);
        
        return $this->result;
    }
    /**
     * 用户绑定邮箱
     * @param string $params
     */
    public function bindEmail($params=null){
        $rand = rand(100000,999999);
        $email=$params["email"];
        $shop_id=$params["shop_id"];
        $user_id=$params["user_id"];
        #查询系统配置信息
        $this->getShopNotifyInfo($shop_id);
        if(!empty($email) && $this->email_is_open==1){
            $template_obj=$this->getTemplateDetail($shop_id, "bind_email", "email");
            if(!empty($template_obj) && $template_obj["is_enable"]==1){
                $content=$template_obj["template_content"];
                $content=str_replace("{验证码}", $rand, $content);
                $this->result["param"]=$rand;
                if(!empty($this->email_host) && !empty($this->email_id) && !empty($this->email_pass) && !empty($this->email_addr)){
                    //$result=emailSend($this->email_host, $this->email_id, $this->email_pass, $this->email_port, $this->email_is_security, $this->email_addr, $email, $template_obj["template_title"], $content, $this->shop_name);
                    $result=emailSend($this->email_host, $this->email_id, $this->email_pass, $this->email_port, $this->email_is_security, $this->email_addr, $email, $template_obj["template_title"], $content, $this->shop_name);
                    if($result){
                        $this->result["code"]=0;
                        $this->result["message"]="发送成功!";
                    }else{
                        $this->result["code"]=-1;
                        $this->result["message"]="发送失败!";
                    }
                }else{
                    $this->result["code"]=-1;
                    $this->result["message"]="邮箱配置信息有误!";
                }
            }else{
                $this->result["code"]=-1;
                $this->result["message"]="邮箱通知模板有误!";
            }
        }else{
            $this->result["code"]=-1;
            $this->result["message"]="店家没有开启邮箱验证";
        }
        $send_result = $this->result["code"] < 0 ? -1 : 1;
        $this->result['record_id'] = $this->createVerificationCodeRecords($template_obj, $shop_id, 0, 2, $email, 14, "绑定邮箱邮箱验证码", json_encode(['number' => $rand]), $this->result["message"], $send_result);
        return $this->result;
    }
    
    /**
     * 订单提醒
     * @param string $params
     */
    public function orderRemindBusiness($params=null){
        #查询系统配置信息
        $this->getShopNotifyInfo(0);
        $out_trade_no = $params["out_trade_no"];//订单号
        $shop_id = $params['shop_id'];
        $result="";
        $user_name="";
        if(!empty($out_trade_no)){
            //获取订单详情
            $ns_order = new NsOrderModel();
            $order_detial = $ns_order->getInfo(["out_trade_no"=>$out_trade_no]);
            $user_model = new UserModel();
            $user_obj = $user_model->get($order_detial['buyer_id']);
            //邮箱提醒
            $template_email_obj = $this->getTemplateDetail($shop_id, "order_remind", "email", "business");
            
            
            $email_array = explode(',', $this->shop_notify_config["email"]);//获取要提醒的人
            if(!empty($email_array[0]) && $template_email_obj["is_enable"] == 1){
                $content = $template_email_obj["template_content"];
                $content = str_replace("{商场名称}", $this->shop_name, $content);
                $content = str_replace("{用户名称}", $order_detial['user_name'], $content);
                $content = str_replace("{主订单号}", $order_detial['order_no'], $content);
                $content = str_replace("{订单金额}", $order_detial['order_money'], $content);
                $send_title = $template_email_obj["template_title"];
                $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
                $send_title = str_replace("{用户名称}", $order_detial['user_name'], $send_title);
                $send_title = str_replace("{主订单号}", $order_detial['order_no'], $send_title);
                $send_title = str_replace("{订单金额}", $order_detial['order_money'], $send_title);
                foreach ($email_array as $v){
                    $this->createNoticeEmailRecords($shop_id, 0, $v, $send_title, $content, 7);
                }
            }
            //短信提醒
            $template_sms_obj = $this->getTemplateDetail($shop_id, "order_remind", "sms", "business");
            $mobile_array = explode(',', $this->shop_notify_config["mobile"]);//获取要提醒的人
            if(!empty($mobile_array[0]) && $template_sms_obj["is_enable"] == 1){
                $sms_params=array(
                    "shopname"=>$this->shop_name,
                    "username"=>$order_detial['user_name'],
                    "orderno"=>$order_detial['order_no'],
                    "ordermoney"=>$order_detial['order_money'],
                    "shopname"=>$this->shop_name,
                    "username"=>$order_detial['user_name'],
                    "orderno"=>$order_detial['order_no'],
                    "ordermoney"=>$order_detial['order_money']
                );
                foreach ($mobile_array as $v){
                    $this->createNoticeSmsRecords($template_sms_obj, $shop_id, 0, $v, $sms_params, "订单提醒-商家通知", 7);
                }
            }
        }
    }
    
    /**
     * 订单退款提醒
     * @param string $params
     */
    public function orderRefundBusiness($params=null){
        #查询系统配置信息refund_order
        $this->getShopNotifyInfo(0);
        $order_id = $params["order_id"];//订单id
        $shop_id = $params['shop_id'];
        $result="";
        $user_name = "";
        if(!empty($order_id)){
            //获取订单详情
            $ns_order = new NsOrderModel();
            $order_detial = $ns_order->getInfo(["order_id"=>$order_id]);
            //邮箱提醒
            $template_email_obj = $this->getTemplateDetail($shop_id, "refund_order", "email", "business");
            
            $email_array = explode(',', $this->shop_notify_config["email"]);//获取要提醒的人
            if(!empty($email_array[0]) && $template_email_obj["is_enable"] == 1){
                $content = $template_email_obj["template_content"];
                $content = str_replace("{商场名称}", $this->shop_name, $content);
                $content = str_replace("{用户名称}", $order_detial['user_name'], $content);
                $content = str_replace("{主订单号}", $order_detial['order_no'], $content);
                $content = str_replace("{订单金额}", $order_detial['order_money'], $content);
                $send_title = $template_email_obj["template_title"];
                $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
                $send_title = str_replace("{用户名称}", $order_detial['user_name'], $send_title);
                $send_title = str_replace("{主订单号}", $order_detial['order_no'], $send_title);
                $send_title = str_replace("{订单金额}", $order_detial['order_money'], $send_title);
                foreach ($email_array as $v){
                    $this->createNoticeEmailRecords($shop_id, 0, $v, $send_title, $content, 6);
                }
            }
            //短信提醒
            $template_sms_obj = $this->getTemplateDetail($shop_id, "refund_order", "sms", "business");
            $mobile_array = explode(',', $this->shop_notify_config["mobile"]);//获取要提醒的人
            if(!empty($mobile_array[0]) && $template_sms_obj["is_enable"] == 1){
                $sms_params=array(
                    "shopname"=>$this->shop_name,
                    "username"=>$order_detial['user_name'],
                    "orderno"=>$order_detial['order_no'],
                    "ordermoney"=>$order_detial['order_money']
                );
                foreach ($mobile_array as $v){
                    $this->createNoticeSmsRecords($template_sms_obj, $shop_id, 0, $v, $sms_params, "订单退货提醒-商家通知", 6);
                }
            }
            
            //模板消息
            if(addon_is_exit("NsWxtemplatemsg"))
            {
                if($this->weixin_uid != null){
                    $uid = "bind_openid";
                    $url = '';
                    $keyword = array();
                    $keyword["keyword1"] = $order_detial['order_no'];
                    $keyword["keyword2"] = $order_detial['order_money'];
                    
                    $param = array(
                        "uid" => $uid,
                        "template_no" => 'OPENTM205986217',
                        "first" => '退款申请通知',
                        "url" => $url,
                        "keyword" => $keyword,
                        "remark" => "",
                        "template_code" => $params["tag"]
                    );
                    hook("sendWxTemplateMsg", $param);
                }
                
            }
        }
    }
    
    /**
     * 用户充值余额商家提醒
     */
    public function rechargeSuccessBusiness($params=null){
        #查询系统配置信息
        $this->getShopNotifyInfo(0);
        
        $out_trade_no = $params["out_trade_no"];//订单号
        $shop_id = $params['shop_id'];
        $user = new UserModel();
        $user_name = $user->getInfo(["uid"=>$params['uid']],"nick_name")["nick_name"];
        $result="";
        if(!empty($out_trade_no)){
            //获取支付详情
            $pay = new NsOrderPaymentModel();
            $order_payment = $pay->getInfo(["out_trade_no"=>$out_trade_no]);
            //邮箱提醒
            $template_email_obj = $this->getTemplateDetail($shop_id, "recharge_success_business", "email", "business");
            $email_array = explode(',', $this->shop_notify_config["email"]);//获取要提醒的人
            if(!empty($email_array[0]) && $template_email_obj["is_enable"] == 1){
                $content = $template_email_obj["template_content"];
                $content = str_replace("{商场名称}", $this->shop_name, $content);
                $content = str_replace("{用户名称}", $user_name, $content);
                $content = str_replace("{充值金额}", $order_payment['pay_money'], $content);
                $send_title=$template_email_obj["template_title"];
                $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
                $send_title = str_replace("{用户名称}", $user_name, $send_title);
                $send_title = str_replace("{充值金额}", $order_payment['pay_money'], $send_title);
                foreach ($email_array as $v){
                    $this->createNoticeEmailRecords($shop_id, 0, $v, $send_title, $content, 8);
                }
            }
            //短信提醒
            $template_sms_obj = $this->getTemplateDetail($shop_id, "recharge_success_business", "sms", "business");
            $mobile_array = explode(',', $this->shop_notify_config["mobile"]);//获取要提醒的人
            if(!empty($mobile_array[0]) && $template_sms_obj["is_enable"] == 1){
                $sms_params=array(
                    "shopname"=>$this->shop_name,
                    "username"=>$user_name,
                    "rechargemoney"=>$order_payment['pay_money']
                );
                foreach ($mobile_array as $v){
                    $this->createNoticeSmsRecords($template_sms_obj, $shop_id, 0, $v, $sms_params, "余额充值-商家通知", 8);
                }
            }
        }
    }
    
    /**
     * 用户充值余额用户提醒
     */
    public function rechargeSuccessUser($params=null){
        $this->getShopNotifyInfo(0);
        $out_trade_no = $params["out_trade_no"];//订单号
        $shop_id = $params['shop_id'];
        $user = new UserModel();
        $user_info = $user->getInfo(["uid"=>$params['uid']],"*");
        $user_name = $user_info["nick_name"];
        $user_tel = $user_info["user_tel"];
        $user_email = $user_info["user_email"];
        
        $result = "";
        if(!empty($out_trade_no)){
            //获取支付详情
            $pay = new NsOrderPaymentModel();
            $order_payment = $pay->getInfo(["out_trade_no"=>$out_trade_no]);
            //邮箱提醒
            $template_email_obj = $this->getTemplateDetail($shop_id, "recharge_success", "email", "user");
            //             $email_array = explode(',', $this->shop_notify_config["email"]);//获取要提醒的人
            if(!empty($user_email) && $template_email_obj["is_enable"] == 1){
                $content = $template_email_obj["template_content"];
                $content = str_replace("{商场名称}", $this->shop_name, $content);
                $content = str_replace("{用户名称}", $user_name, $content);
                $content = str_replace("{充值金额}", $order_payment['pay_money'], $content);
                $send_title=$template_email_obj["template_title"];
                $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
                $send_title = str_replace("{用户名称}", $user_name, $send_title);
                $send_title = str_replace("{充值金额}", $order_payment['pay_money'], $send_title);
                $this->createNoticeEmailRecords($shop_id, $user_info["uid"], $user_email, $send_title, $content, 1);
            }
            //短信提醒
            $template_sms_obj = $this->getTemplateDetail($shop_id, "recharge_success", "sms", "user");
            //             $mobile_array = explode(',', $this->shop_notify_config["mobile"]);//获取要提醒的人
            if(!empty($user_tel) && $template_sms_obj["is_enable"] == 1){
                $sms_params=array(
                    "shopname"=>$this->shop_name,
                    "username"=>$user_name,
                    "rechargemoney"=>$order_payment['pay_money']
                );
                $this->createNoticeSmsRecords($template_sms_obj, $shop_id, $user_info["uid"], $user_tel, $sms_params, "用户余额充值", 1);
            }
        }
        
        //模板消息
        if(addon_is_exit("NsWxtemplatemsg"))
        {
            $uid = $params['uid'];
            
            $member_account = new MemberAccount();
            $member_balance = $member_account->getMemberBalance($uid);
            $url = '';
            $keyword = array();
            $keyword["keyword1"] = $order_payment['pay_money'] . '元';//充值金额
            $keyword["keyword2"] = getTimeStampTurnTime(time());//充值时间
            $keyword["keyword3"] = $member_balance . '元';//现在的余额
            
            $param = array(
                "uid" => $uid,
                "template_no" => 'OPENTM205041253',
                "first" => '',
                "url" => $url,
                "keyword" => $keyword,
                "template_code" => $params["tag"]
            );
            hook("sendWxTemplateMsg", $param);
        }
    }
    
    /**
     * 开团通知
     * @param array $params
     */
    public function openGroupNoticeUser($params = []){
        // 获取系统配置
        $this->getShopNotifyInfo(0);
        $shop_id = 0;
        $pintuan_info = $this->getPintuanInfo($params['pintuan_group_id']);
        if(!empty($pintuan_info)){
            $user_info = $this->getUserInfo($pintuan_info['group_uid']);
            $mobile = $user_info["user_tel"];
            $email = $user_info["user_email"];
        }
        if(!empty($pintuan_info) && !empty($user_info)){
            $pintuan_info['surplus_num'] -= 1;
            // 邮件提醒
            $template_email_obj = $this->getTemplateDetail($shop_id, "open_the_group", "email", "user");
            if(!empty($template_email_obj) && $template_email_obj["is_enable"] == 1 && !empty($user_info['user_email'])){
                // 内容
                $content = $template_email_obj["template_content"];
                $content = str_replace("{商场名称}", $this->shop_name, $content);
                $content = str_replace("{用户名称}", $pintuan_info['group_name'], $content);
                $content = str_replace("{商品名称}", $pintuan_info['goods_name'], $content);
                $content = str_replace("{主订单号}", $params['order_no'], $content);
                $content = str_replace("{拼团价}", $pintuan_info['tuangou_money'], $content);
                $content = str_replace("{剩余人数}", $pintuan_info['surplus_num'], $content);
                $content = str_replace("{团购人数}", $pintuan_info['tuangou_num'], $content);
                $content = str_replace("{发起时间}", date('Y-m-d H:i:s', $pintuan_info['create_time']), $content);
                $content = str_replace("{剩余时间}", $pintuan_info['surplus_time'], $content);
                $content = str_replace("{拼团类型}", $pintuan_info['tuangou_type_name'], $content);
                // 标题
                $send_title=$template_email_obj["template_title"];
                $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
                $send_title = str_replace("{用户名称}", $pintuan_info['group_name'], $send_title);
                $send_title = str_replace("{商品名称}", $pintuan_info['goods_name'], $send_title);
                $send_title = str_replace("{主订单号}", $params['order_no'], $send_title);
                $send_title = str_replace("{拼团价}", $pintuan_info['tuangou_money'], $send_title);
                $send_title = str_replace("{剩余人数}", $pintuan_info['surplus_num'], $send_title);
                $send_title = str_replace("{团购人数}", $pintuan_info['tuangou_num'], $send_title);
                $send_title = str_replace("{发起时间}", date('Y-m-d H:i:s', $pintuan_info['create_time']), $send_title);
                $send_title = str_replace("{剩余时间}", $pintuan_info['surplus_time'], $send_title);
                $send_title = str_replace("{拼团类型}", $pintuan_info['tuangou_type_name'], $send_title);
                // 添加到发送记录表
                $this->createNoticeEmailRecords($shop_id, $pintuan_info['group_uid'], $email, $send_title, $content, 0);
            }
            
            // 短信提醒
            $template_sms_obj = $this->getTemplateDetail($shop_id, "open_the_group", "sms", "user");
            if(!empty($template_sms_obj) && $template_sms_obj["is_enable"] == 1 && !empty($user_info['user_tel'])){
                $sms_params=array(
                    "username" => $pintuan_info['group_name'],
                    "shopname" => $this->shop_name,
                    "goodsname" => $pintuan_info['goods_name'],
                    "orderno" => $params['order_no'],
                    "pintuanmoney" => $pintuan_info['tuangou_money'],
                    "surplusnumber" => $pintuan_info['surplus_num'],
                    "totalnumber" => $pintuan_info['tuangou_num'],
                    "launchtime" => date('Y-m-d H:i:s', $pintuan_info['create_time']),
                    "surplustime" => $pintuan_info['surplus_time'],
                    "groupbookingtype" => $pintuan_info['tuangou_type_name']
                );
                $this->createNoticeSmsRecords($template_sms_obj, $shop_id, $pintuan_info['group_uid'], $user_info['user_tel'], $sms_params, "用户拼团发起通知", 0);
            }
            
            //模板消息
            if(addon_is_exit("NsWxtemplatemsg"))
            {
                $uid = $pintuan_info['group_uid'];
                $url = '';
                $keyword = array();
                $keyword["keyword1"] = $pintuan_info['goods_name'];
                $keyword["keyword2"] = $pintuan_info['tuangou_money'];
                $keyword["keyword3"] = $pintuan_info['tuangou_num'] . '人团';
                $keyword["keyword4"] = date("Y-m-d H:i:d", $pintuan_info['end_time']);
                
                $param = array(
                    "uid" => $uid,
                    "template_no" => 'OPENTM410729522',
                    "first" => '开团成功提醒',
                    "url" => $url,
                    "keyword" => $keyword,
                    "remark" => "",
                    "template_code" => $params["tag"]
                );
                hook("sendWxTemplateMsg", $param);
            }
        }
    }
    
    /**
     * 用户参团通知
     * @param array $params
     */
    public function addGroupNoticeUser($params = []){
        // 获取系统配置
        $this->getShopNotifyInfo(0);
        $shop_id = 0;
        $pintuan_info = $this->getPintuanInfo($params['pintuan_group_id']);
        if(!empty($pintuan_info)){
            $user_info = $this->getUserInfo($params['uid']);
        }
        if(!empty($pintuan_info) && !empty($user_info)){
            $pintuan_info['surplus_num'] -= 1;
            // 邮件提醒
            $template_email_obj = $this->getTemplateDetail($shop_id, "add_the_group", "email", "user");
            if(!empty($template_email_obj) && $template_email_obj["is_enable"] == 1 && !empty($user_info['user_email'])){
                // 内容
                $content = $template_email_obj["template_content"];
                $content = str_replace("{商场名称}", $this->shop_name, $content);
                $content = str_replace("{用户名称}", $pintuan_info['group_name'], $content);
                $content = str_replace("{商品名称}", $pintuan_info['goods_name'], $content);
                $content = str_replace("{主订单号}", $params['order_no'], $content);
                $content = str_replace("{拼团价}", $pintuan_info['tuangou_money'], $content);
                $content = str_replace("{剩余人数}", $pintuan_info['surplus_num'], $content);
                $content = str_replace("{团购人数}", $pintuan_info['tuangou_num'], $content);
                $content = str_replace("{发起时间}", date('Y-m-d H:i:s', $pintuan_info['create_time']), $content);
                $content = str_replace("{剩余时间}", $pintuan_info['surplus_time'], $content);
                $content = str_replace("{拼团类型}", $pintuan_info['tuangou_type_name'], $content);
                // 标题
                $send_title=$template_email_obj["template_title"];
                $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
                $send_title = str_replace("{用户名称}", $pintuan_info['group_name'], $send_title);
                $send_title = str_replace("{商品名称}", $pintuan_info['goods_name'], $send_title);
                $send_title = str_replace("{主订单号}", $params['order_no'], $send_title);
                $send_title = str_replace("{拼团价}", $pintuan_info['tuangou_money'], $send_title);
                $send_title = str_replace("{剩余人数}", $pintuan_info['surplus_num'], $send_title);
                $send_title = str_replace("{团购人数}", $pintuan_info['tuangou_num'], $send_title);
                $send_title = str_replace("{发起时间}", date('Y-m-d H:i:s', $pintuan_info['create_time']), $send_title);
                $send_title = str_replace("{剩余时间}", $pintuan_info['surplus_time'], $send_title);
                $send_title = str_replace("{拼团类型}", $pintuan_info['tuangou_type_name'], $send_title);
                // 添加到发送记录表
                $this->createNoticeEmailRecords($shop_id, $pintuan_info['group_uid'], $user_info['user_email'], $send_title, $content, 0);
            }
            
            // 短信提醒
            $template_sms_obj = $this->getTemplateDetail($shop_id, "add_the_group", "sms", "user");
            if(!empty($template_sms_obj) && $template_sms_obj["is_enable"] == 1 && !empty($user_info['user_tel'])){
                $sms_params=array(
                    "username" => $pintuan_info['group_name'],
                    "shopname" => $this->shop_name,
                    "goodsname" => $pintuan_info['goods_name'],
                    "orderno" => $params['order_no'],
                    "pintuanmoney" => $pintuan_info['tuangou_money'],
                    "surplusnumber" => $pintuan_info['surplus_num'],
                    "totalnumber" => $pintuan_info['tuangou_num'],
                    "launchtime" => date('Y-m-d H:i:s', $pintuan_info['create_time']),
                    "surplustime" => $pintuan_info['surplus_time'],
                    "groupbookingtype" => $pintuan_info['tuangou_type_name']
                );
                $this->createNoticeSmsRecords($template_sms_obj, $shop_id, $pintuan_info['group_uid'], $user_info['user_tel'], $sms_params, "用户参与拼团通知", 0);
            }
            
            
            
            //模板消息
            if(addon_is_exit("NsWxtemplatemsg"))
            {
                $uid = $params['uid'];
                $url = '';
                $keyword = array();
                $keyword["keyword1"] = $pintuan_info['goods_name'];
                $keyword["keyword2"] = $pintuan_info['tuangou_money'];
                $keyword["keyword3"] = $user_info['nick_name'];
                $keyword["keyword4"] =$pintuan_info['tuangou_num'] . '人团';
                
                $param = array(
                    "uid" => $uid,
                    "template_no" => 'OPENTM414066517',
                    "first" => '参团成功提醒',
                    "url" => $url,
                    "keyword" => $keyword,
                    "remark" => "",
                    "template_code" => $params["tag"]
                );
                hook("sendWxTemplateMsg", $param);
            }
        }
    }
    
    /**
     * 拼团成功或失败通知（用户）
     * @param array $params
     */
    public function groupBookingSuccessOrFailUser($params = []){
        // 获取系统配置
        $this->getShopNotifyInfo(0);
        $shop_id = 0;
        $pintuan_info = $this->getPintuanInfo($params['pintuan_group_id']);
        $user_list = $this->getPintuanList($params['pintuan_group_id']);
        
        if(!empty($pintuan_info) && !empty($user_list)){
            // 邮件提醒
            if($params['type'] == "success"){
                $template_email_obj = $this->getTemplateDetail($shop_id, "group_booking_success", "email", "user");
            }elseif($params['type'] == "fail"){
                $template_email_obj = $this->getTemplateDetail($shop_id, "group_booking_fail", "email", "user");
            }
            
            if(!empty($template_email_obj) && $template_email_obj["is_enable"] == 1){
                // 内容
                $content = $template_email_obj["template_content"];
                $content = str_replace("{商场名称}", $this->shop_name, $content);
                $content = str_replace("{商品名称}", $pintuan_info['goods_name'], $content);
                $content = str_replace("{拼团价}", $pintuan_info['tuangou_money'], $content);
                $content = str_replace("{团购人数}", $pintuan_info['tuangou_num'], $content);
                $content = str_replace("{发起时间}", date('Y-m-d H:i:s', $pintuan_info['create_time']), $content);
                $content = str_replace("{拼团类型}", $pintuan_info['tuangou_type_name'], $content);
                // 标题
                $send_title=$template_email_obj["template_title"];
                $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
                $send_title = str_replace("{商品名称}", $pintuan_info['goods_name'], $send_title);
                $send_title = str_replace("{拼团价}", $pintuan_info['tuangou_money'], $send_title);
                $send_title = str_replace("{团购人数}", $pintuan_info['tuangou_num'], $send_title);
                $send_title = str_replace("{发起时间}", date('Y-m-d H:i:s', $pintuan_info['create_time']), $send_title);
                $send_title = str_replace("{拼团类型}", $pintuan_info['tuangou_type_name'], $send_title);
                
                foreach ($user_list as $item){
                    $content = str_replace("{用户名称}", $item['nick_name'], $content);
                    $content = str_replace("{主订单号}", $item['order_no'], $content);
                    $send_title = str_replace("{用户名称}", $item['nick_name'], $send_title);
                    $send_title = str_replace("{主订单号}", $item['order_no'], $send_title);
                    // 添加到发送记录表
                    $this->createNoticeEmailRecords($shop_id, $item['buyer_id'], $item['user_email'], $send_title, $content, 0);
                }
            }
            
            // 短信提醒
            if($params['type'] == "success"){
                $template_sms_obj = $this->getTemplateDetail($shop_id, "group_booking_success", "sms", "user");
            }elseif($params['type'] == "fail"){
                $template_sms_obj = $this->getTemplateDetail($shop_id, "group_booking_fail", "sms", "user");
            }
            if(!empty($template_sms_obj) && $template_sms_obj["is_enable"] == 1){
                $sms_params=array(
                    "shopname" => $this->shop_name,
                    "goodsname" => $pintuan_info['goods_name'],
                    "pintuanmoney" => $pintuan_info['tuangou_money'],
                    "totalnumber" => $pintuan_info['tuangou_num'],
                    "launchtime" => date('Y-m-d H:i:s', $pintuan_info['create_time']),
                    "groupbookingtype" => $pintuan_info['tuangou_type_name']
                );
                foreach ($user_list as $item){
                    if($params['type'] == "success"){
                        $sms_params['username'] = $item['nick_name'];
                        $sms_params['orderno'] = $item['order_no'];
                        $this->createNoticeSmsRecords($template_sms_obj, $shop_id, $item['buyer_id'], $item['user_tel'], $sms_params, "用户拼团成功通知", 0);
                    }elseif($params['type'] == "fail"){
                        $this->createNoticeSmsRecords($template_sms_obj, $shop_id, $item['buyer_id'], $item['user_tel'], $sms_params, "用户拼团失败通知", 0);
                    }
                }
            }
            //模板消息
            if(addon_is_exit("NsWxtemplatemsg"))
            {
                if (!empty($pintuan_info) && !empty($user_list)) {
                    foreach ($user_list as $order_item) {
                        $uid = $order_item['buyer_id'];
                        $url = '';
                        $keyword = array();
                        $keyword["keyword1"] = $order_item["order_no"];
                        $keyword["keyword2"] = $order_item['goods_name'];
                        $keyword["keyword3"] = $pintuan_info['tuangou_money'];
                        $keyword["keyword4"] = $pintuan_info['tuangou_num'] . '人团';
                        if($params['type'] == "success"){
                            $param = array(
                                "uid" => $uid,
                                "template_no" => 'OPENTM409367318',
                                "first" => '拼团成功通知',
                                "url" => $url,
                                "keyword" => $keyword,
                                "remark" => "",
                                "template_code" => $params["tag"]
                            );
                        } elseif ($params['type'] == "fail") {
                            $param = array(
                                "uid" => $uid,
                                "template_no" => 'OPENTM409367317',
                                "first" => '拼团失败通知',
                                "url" => $url,
                                "keyword" => $keyword,
                                "remark" => "",
                                "template_code" => $params["tag"]
                            );
                            
                        }
                        hook("sendWxTemplateMsg", $param);
                    }
                }
            }
            
        }
    }
    
    /**
     * 拼团发起通知（商家）
     * @param array $params
     */
    public function openGroupNoticeBusiness($params = []){
        // 获取系统配置
        $this->getShopNotifyInfo(0);
        $shop_id = 0;
        $pintuan_info = $this->getPintuanInfo($params['pintuan_group_id']);
        if(!empty($pintuan_info)){
            $user_info = $this->getUserInfo($pintuan_info['group_uid']);
        }
        if(!empty($pintuan_info) && !empty($user_info)){
            $pintuan_info['surplus_num'] -= 1;
            //邮箱提醒
            $template_email_obj = $this->getTemplateDetail($shop_id, "open_the_group_business", "email", "business");
            $email_array = explode(',', $this->shop_notify_config["email"]);//获取要提醒的人
            if(!empty($template_email_obj) && $template_email_obj["is_enable"] == 1 && !empty($email_array[0])){
                // 内容
                $content = $template_email_obj["template_content"];
                $content = str_replace("{商场名称}", $this->shop_name, $content);
                $content = str_replace("{用户名称}", $pintuan_info['group_name'], $content);
                $content = str_replace("{商品名称}", $pintuan_info['goods_name'], $content);
                $content = str_replace("{主订单号}", $params['order_no'], $content);
                $content = str_replace("{拼团价}", $pintuan_info['tuangou_money'], $content);
                $content = str_replace("{剩余人数}", $pintuan_info['surplus_num'], $content);
                $content = str_replace("{团购人数}", $pintuan_info['tuangou_num'], $content);
                $content = str_replace("{发起时间}", date('Y-m-d H:i:s', $pintuan_info['create_time']), $content);
                $content = str_replace("{剩余时间}", $pintuan_info['surplus_time'], $content);
                $content = str_replace("{拼团类型}", $pintuan_info['tuangou_type_name'], $content);
                // 标题
                $send_title=$template_email_obj["template_title"];
                $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
                $send_title = str_replace("{用户名称}", $pintuan_info['group_name'], $send_title);
                $send_title = str_replace("{商品名称}", $pintuan_info['goods_name'], $send_title);
                $send_title = str_replace("{主订单号}", $params['order_no'], $send_title);
                $send_title = str_replace("{拼团价}", $pintuan_info['tuangou_money'], $send_title);
                $send_title = str_replace("{剩余人数}", $pintuan_info['surplus_num'], $send_title);
                $send_title = str_replace("{团购人数}", $pintuan_info['tuangou_num'], $send_title);
                $send_title = str_replace("{发起时间}", date('Y-m-d H:i:s', $pintuan_info['create_time']), $send_title);
                $send_title = str_replace("{剩余时间}", $pintuan_info['surplus_time'], $send_title);
                $send_title = str_replace("{拼团类型}", $pintuan_info['tuangou_type_name'], $send_title);
                // 添加到发送记录表
                foreach ($email_array as $v){
                    $this->createNoticeEmailRecords($shop_id, $pintuan_info['group_uid'], $v, $send_title, $content, 0);
                }
            }
            
            // 短信提醒
            $template_sms_obj = $this->getTemplateDetail($shop_id, "open_the_group_business", "sms", "business");
            $mobile_array = explode(',', $this->shop_notify_config["mobile"]);//获取要提醒的人
            if(!empty($template_sms_obj) && $template_sms_obj["is_enable"] == 1 && !empty($mobile_array[0])){
                $sms_params=array(
                    "username" => $pintuan_info['group_name'],
                    "shopname" => $this->shop_name,
                    "goodsname" => $pintuan_info['goods_name'],
                    "orderno" => $params['order_no'],
                    "pintuanmoney" => $pintuan_info['tuangou_money'],
                    "surplusnumber" => $pintuan_info['surplus_num'],
                    "totalnumber" => $pintuan_info['tuangou_num'],
                    "launchtime" => date('Y-m-d H:i:s', $pintuan_info['create_time']),
                    "surplustime" => $pintuan_info['surplus_time'],
                    "groupbookingtype" => $pintuan_info['tuangou_type_name']
                );
                
                foreach ($mobile_array as $v){
                    $this->createNoticeSmsRecords($template_sms_obj, $shop_id, $pintuan_info['group_uid'], $v, $sms_params, "用户发起拼团商家通知", 0);
                }
            }
        }
    }
    
    /**
     * 拼团成功通知(商家)
     * @param array $params
     */
    public function groupBookingSuccessBusiness($params = []){
        // 获取系统配置
        $this->getShopNotifyInfo(0);
        $shop_id = 0;
        $pintuan_info = $this->getPintuanInfo($params['pintuan_group_id']);
        if(!empty($pintuan_info)){
            //邮箱提醒
            $template_email_obj = $this->getTemplateDetail($shop_id, "group_booking_success_business", "email", "business");
            $email_array = explode(',', $this->shop_notify_config["email"]);//获取要提醒的人
            if(!empty($template_email_obj) && $template_email_obj["is_enable"] == 1 && !empty($email_array[0])){
                // 内容
                $content = $template_email_obj["template_content"];
                $content = str_replace("{商场名称}", $this->shop_name, $content);
                $content = str_replace("{商品名称}", $pintuan_info['goods_name'], $content);
                $content = str_replace("{拼团价}", $pintuan_info['tuangou_money'], $content);
                $content = str_replace("{团购人数}", $pintuan_info['tuangou_num'], $content);
                $content = str_replace("{发起时间}", date('Y-m-d H:i:s', $pintuan_info['create_time']), $content);
                $content = str_replace("{拼团类型}", $pintuan_info['tuangou_type_name'], $content);
                $content = str_replace("{团长名称}", $pintuan_info['group_name'], $content);
                // 标题
                $send_title=$template_email_obj["template_title"];
                $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
                $send_title = str_replace("{商品名称}", $pintuan_info['goods_name'], $send_title);
                $send_title = str_replace("{拼团价}", $pintuan_info['tuangou_money'], $send_title);
                $send_title = str_replace("{团购人数}", $pintuan_info['tuangou_num'], $send_title);
                $send_title = str_replace("{发起时间}", date('Y-m-d H:i:s', $pintuan_info['create_time']), $send_title);
                $send_title = str_replace("{拼团类型}", $pintuan_info['tuangou_type_name'], $send_title);
                $send_title = str_replace("{团长名称}", $pintuan_info['group_name'], $send_title);
                // 添加到发送记录表
                foreach ($email_array as $v){
                    $this->createNoticeEmailRecords($shop_id, $pintuan_info['group_uid'], $v, $send_title, $content, 0);
                }
            }
            
            // 短信提醒
            $template_sms_obj = $this->getTemplateDetail($shop_id, "group_booking_success_business", "sms", "business");
            $mobile_array = explode(',', $this->shop_notify_config["mobile"]);//获取要提醒的人
            if(!empty($template_sms_obj) && $template_sms_obj["is_enable"] == 1 && !empty($mobile_array[0])){
                $sms_params=array(
                    "shopname" => $this->shop_name,
                    "goodsname" => $pintuan_info['goods_name'],
                    "pintuanmoney" => $pintuan_info['tuangou_money'],
                    "surplusnumber" => $pintuan_info['surplus_num'],
                    "totalnumber" => $pintuan_info['tuangou_num'],
                    "launchtime" => date('Y-m-d H:i:s', $pintuan_info['create_time']),
                    "surplustime" => $pintuan_info['surplus_time'],
                    "groupbookingtype" => $pintuan_info['tuangou_type_name'],
                    "headgroup" => $pintuan_info['group_name']
                );
                foreach ($mobile_array as $v){
                    $this->createNoticeSmsRecords($template_sms_obj, $shop_id, $pintuan_info['group_uid'], $v, $sms_params, "拼团成功商家通知", 0);
                }
            }
        }
    }
    
    /**
     * 获取拼团通知所需信息
     * @param unknown $pintuan_group_id
     */
    private function getPintuanInfo($pintuan_group_id){
        $tuangou_group = new NsTuangouGroupModel();
        $tuangou_group_info = $tuangou_group -> getInfo(['group_id' => $pintuan_group_id], 'group_uid,group_name,goods_name,tuangou_money,tuangou_type_name,tuangou_num,real_num,create_time,end_time');
        if(!empty($tuangou_group_info)){
            $tuangou_group_info['surplus_num'] = $tuangou_group_info['tuangou_num'] - $tuangou_group_info['real_num'];
            $day = floor(($tuangou_group_info['end_time'] - time()) / 86400);
            $hours = floor(($tuangou_group_info['end_time'] - time() - $day * 86400) / 3600);
            $tuangou_group_info['surplus_time'] = $day > 0 ? $day.'天' : '';
            $tuangou_group_info['surplus_time'] .= $hours > 0 ? $hours.'小时' : '';
        }
        return $tuangou_group_info;
    }
    
    /**
     * 获取用户的手机与邮箱
     * @param unknown $uid
     */
    private function getUserInfo($uid){
        $user = new UserModel();
        $user_info = $user -> getInfo(['uid' => $uid], "user_email,user_tel,nick_name");
        return $user_info;
    }
    
    /**
     * 获取参与拼团的用户列表
     * @param int $pintuan_group_id
     */
    private function getPintuanUserList($pintuan_group_id){
        $ns_order = new NsOrderModel();
        $order_goods_model = new NsOrderGoodsModel();
        $buyer_list = $ns_order -> getQuery(['tuangou_group_id' => $pintuan_group_id,'order_status' => 1], 'buyer_id,order_no, order_id', '');
        if(!empty($buyer_list)){
            $user_model = new UserModel();
            foreach ($buyer_list as $key => $item){
                $user_info = $user_model -> getInfo(['uid' => $item['buyer_id']], "user_email,user_tel,nick_name");
                $buyer_list[$key]['user_email'] = $user_info['user_email'];
                $buyer_list[$key]['user_tel'] = $user_info['user_tel'];
                $buyer_list[$key]['nick_name'] = $user_info['nick_name'];
                $order_item_info = $order_goods_model->getInfo(["order_id" => $item["order_id"]], "goods_name");
                $buyer_list[$key]['goods_name'] = $order_item_info["goods_name"];
                
            }
        }
        return $buyer_list;
    }
    /**
     * 获取参与拼团的用户列表
     * @param int $pintuan_group_id
     */
    private function getPintuanList($pintuan_group_id){
        $ns_order = new NsOrderModel();
        $order_goods_model = new NsOrderGoodsModel();
        $buyer_list = $ns_order -> getQuery(['tuangou_group_id' => $pintuan_group_id], 'buyer_id,order_no, order_id', '');
        if(!empty($buyer_list)){
            $user_model = new UserModel();
            foreach ($buyer_list as $key => $item){
                $user_info = $user_model -> getInfo(['uid' => $item['buyer_id']], "user_email,user_tel,nick_name");
                $buyer_list[$key]['user_email'] = $user_info['user_email'];
                $buyer_list[$key]['user_tel'] = $user_info['user_tel'];
                $buyer_list[$key]['nick_name'] = $user_info['nick_name'];
                $order_item_info = $order_goods_model->getInfo(["order_id" => $item["order_id"]], "goods_name");
                $buyer_list[$key]['goods_name'] = $order_item_info["goods_name"];
                
            }
        }
        return $buyer_list;
    }
    /**
     * 砍价发起（用户）
     * @param array $params
     */
    public function bargainLaunchUser($params = []){
        // 获取系统配置
        $this->getShopNotifyInfo(0);
        $shop_id = 0;
        $bargain_info = $this->getBargainInfo($params['launch_id']);
        if(!empty($bargain_info)){
            $user_info = $this-> getUserInfo($bargain_info['uid']);
        }
        
        if(!empty($bargain_info) && !empty($user_info)){
            // 邮件提醒
            $template_email_obj = $this->getTemplateDetail($shop_id, "bargain_launch", "email", "user");
            if(!empty($template_email_obj) && $template_email_obj["is_enable"] == 1 && !empty($user_info['user_email'])){
                // 内容
                $content = $template_email_obj["template_content"];
                $content = str_replace("{商场名称}", $this->shop_name, $content);
                $content = str_replace("{用户名称}", $user_info['nick_name'], $content);
                $content = str_replace("{商品名称}", $bargain_info['goods_name'], $content);
                $content = str_replace("{剩余时间}", $bargain_info['surplus_time'], $content);
                $content = str_replace("{砍价金额}", $bargain_info['bargain_min_money'], $content);
                // 标题
                $send_title=$template_email_obj["template_title"];
                $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
                $send_title = str_replace("{用户名称}", $user_info['nick_name'], $send_title);
                $send_title = str_replace("{商品名称}", $bargain_info['goods_name'], $send_title);
                $send_title = str_replace("{剩余时间}", $bargain_info['surplus_time'], $send_title);
                $send_title = str_replace("{砍价金额}", $bargain_info['bargain_min_money'], $send_title);
                // 添加到发送记录表
                $this->createNoticeEmailRecords($shop_id, $bargain_info['uid'], $user_info['user_email'], $send_title, $content, 0);
            }
            
            // 短信提醒
            $template_sms_obj = $this->getTemplateDetail($shop_id, "bargain_launch", "sms", "user");
            if(!empty($template_sms_obj) && $template_sms_obj["is_enable"] == 1 && !empty($user_info['user_tel'])){
                $sms_params = array(
                    "shopname" => $this->shop_name,
                    "username" => $user_info['nick_name'],
                    "goodsname" => $bargain_info['goods_name'],
                    "surplustime" => $bargain_info['surplus_time'],
                    "bargainminmoney" => $bargain_info['bargain_min_money']
                );
                $this->createNoticeSmsRecords($template_sms_obj, $shop_id, $bargain_info['uid'], $user_info['user_tel'], $sms_params, "用户砍价发起通知", 0);
            }
            //模板消息
            if(addon_is_exit("NsWxtemplatemsg"))
            {
                $uid = $bargain_info['uid'];
                $url = '';
                $keyword = array();
                $keyword["keyword1"] = $bargain_info['goods_name'];//商品名称
                $keyword["keyword2"] = round($bargain_info['goods_money'] - $bargain_info['bargain_money'], 2);//当前价
                $param = array(
                    "uid" => $uid,
                    "template_no" => 'OPENTM411530622',
                    "first" => '发起砍价',
                    "url" => $url,
                    "keyword" => $keyword,
                    "remark" => "",
                    "template_code" => $params["tag"]
                );
                hook("sendWxTemplateMsg", $param);
            }
            
            
        }
    }
    
    /**
     * 砍价成功或失败通知（用户）
     * @param array $params
     */
    public function bargainSuccessOrFailUser($params = []){
        $this->getShopNotifyInfo(0);
        $shop_id = 0;
        $bargain_info = $this->getBargainInfo($params['launch_id']);
        if(!empty($bargain_info)){
            $user_info = $this-> getUserInfo($bargain_info['uid']);
        }
        
        if(!empty($bargain_info) && !empty($user_info)){
            // 邮件提醒
            if($params['type'] == 'success'){
                $template_email_obj = $this->getTemplateDetail($shop_id, "bargain_success", "email", "user");
            }elseif($params['type'] == 'fail'){
                $template_email_obj = $this->getTemplateDetail($shop_id, "bargain_fail", "email", "user");
            }
            if(!empty($template_email_obj) && $template_email_obj["is_enable"] == 1 && !empty($user_info['user_email'])){
                // 内容
                $content = $template_email_obj["template_content"];
                $content = str_replace("{商场名称}", $this->shop_name, $content);
                $content = str_replace("{用户名称}", $user_info['nick_name'], $content);
                $content = str_replace("{商品名称}", $bargain_info['goods_name'], $content);
                $content = str_replace("{砍价金额}", $bargain_info['bargain_min_money'], $content);
                // 标题
                $send_title=$template_email_obj["template_title"];
                $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
                $send_title = str_replace("{用户名称}", $user_info['nick_name'], $send_title);
                $send_title = str_replace("{商品名称}", $bargain_info['goods_name'], $send_title);
                $send_title = str_replace("{砍价金额}", $bargain_info['bargain_min_money'], $send_title);
                
                if($params['type'] == 'success'){
                    $content = str_replace("{主订单号}", $params['order_no'], $content);
                    $send_title = str_replace("{主订单号}", $params['order_no'], $send_title);
                }
                // 添加到发送记录表
                $this->createNoticeEmailRecords($shop_id, $bargain_info['uid'], $user_info['user_email'], $send_title, $content, 0);
            }
            
            // 短信提醒
            if($params['type'] == 'success'){
                $template_sms_obj = $this->getTemplateDetail($shop_id, "bargain_success", "sms", "user");
            }elseif($params['type'] == 'fail'){
                $template_sms_obj = $this->getTemplateDetail($shop_id, "bargain_fail", "sms", "user");
            }
            if(!empty($template_sms_obj) && $template_sms_obj["is_enable"] == 1 && !empty($user_info['user_tel'])){
                $sms_params=array(
                    "shopname" => $this->shop_name,
                    "username" => $user_info['nick_name'],
                    "goodsname" => $bargain_info['goods_name'],
                    "bargainminmoney" => $bargain_info['bargain_min_money']
                );
                if($params['type'] == 'success'){
                    $sms_params['orderno'] = $params['order_no'];
                    $this->createNoticeSmsRecords($template_sms_obj, $shop_id, $bargain_info['uid'], $user_info['user_tel'], $sms_params, "用户砍价成功通知", 0);
                }elseif($params['type'] == 'fail'){
                    $this->createNoticeSmsRecords($template_sms_obj, $shop_id, $bargain_info['uid'], $user_info['user_tel'], $sms_params, "用户砍价失败通知", 0);
                }
            }
            
            
            //模板消息
            if(addon_is_exit("NsWxtemplatemsg"))
            {
                $uid = $bargain_info['uid'];
                $url = '';
                $keyword = array();
                $keyword["keyword1"] = $bargain_info['goods_name'];//商品名称
                $keyword["keyword2"] = $bargain_info['goods_money'] - $bargain_info['bargain_money'];//当前价
                if($params['type'] == 'success'){
                    $notice = "砍价成功通知";
                }elseif($params['type'] == 'fail'){
                    $notice = "砍价失败通知";
                }
                $param = array(
                    "uid" => $uid,
                    "template_no" => 'OPENTM411530622',
                    "first" => $notice,
                    "url" => $url,
                    "keyword" => $keyword,
                    "remark" => "",
                    "template_code" => $params["tag"]
                );
                hook("sendWxTemplateMsg", $param);
            }
        }
    }
    
    /**
     * 砍价发起通知（商家）
     * @param array $params
     */
    public function bargainLaunchBusiness($params = []){
        // 获取系统配置
        $this->getShopNotifyInfo(0);
        $shop_id = 0;
        $bargain_info = $this->getBargainInfo($params['launch_id']);
        if(!empty($bargain_info)){
            $user_info = $this-> getUserInfo($bargain_info['uid']);
        }
        if(!empty($bargain_info) && !empty($user_info)){
            // 邮件提醒
            $template_email_obj = $this->getTemplateDetail($shop_id, "bargain_launch_business", "email", "business");
            $email_array = explode(',', $this->shop_notify_config["email"]);//获取要提醒的人
            if(!empty($template_email_obj) && $template_email_obj["is_enable"] == 1 && !empty($email_array[0])){
                // 内容
                $content = $template_email_obj["template_content"];
                $content = str_replace("{商场名称}", $this->shop_name, $content);
                $content = str_replace("{用户名称}", $user_info['nick_name'], $content);
                $content = str_replace("{商品名称}", $bargain_info['goods_name'], $content);
                $content = str_replace("{剩余时间}", $bargain_info['surplus_time'], $content);
                $content = str_replace("{砍价金额}", $bargain_info['bargain_min_money'], $content);
                // 标题
                $send_title=$template_email_obj["template_title"];
                $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
                $send_title = str_replace("{用户名称}", $user_info['nick_name'], $send_title);
                $send_title = str_replace("{商品名称}", $bargain_info['goods_name'], $send_title);
                $send_title = str_replace("{剩余时间}", $bargain_info['surplus_time'], $send_title);
                $send_title = str_replace("{砍价金额}", $bargain_info['bargain_min_money'], $send_title);
                // 添加到发送记录表
                foreach ($email_array as $email){
                    $this->createNoticeEmailRecords($shop_id, $bargain_info['uid'], $email, $send_title, $content, 0);
                }
            }
            
            // 短信提醒
            $template_sms_obj = $this->getTemplateDetail($shop_id, "bargain_launch_business", "sms", "business");
            $mobile_array = explode(',', $this->shop_notify_config["mobile"]);//获取要提醒的人
            if(!empty($template_sms_obj) && $template_sms_obj["is_enable"] == 1 && !empty($mobile_array[0])){
                $sms_params=array(
                    "shopname" => $this->shop_name,
                    "username" => $user_info['nick_name'],
                    "goodsname" => $bargain_info['goods_name'],
                    "surplustime" => $bargain_info['surplus_time'],
                    "bargainminmoney" => $bargain_info['bargain_min_money']
                );
                foreach ($mobile_array as $mobile){
                    $this->createNoticeSmsRecords($template_sms_obj, $shop_id, $bargain_info['uid'], $mobile, $sms_params, "用户砍价发起商家通知", 0);
                }
            }
        }
    }
    
    /**
     * 砍价成功通知（商家）
     * @param array $params
     */
    public function bargainSuccessBusiness($params = []){
        // 获取系统配置
        $this->getShopNotifyInfo(0);
        $shop_id = 0;
        $bargain_info = $this->getBargainInfo($params['launch_id']);
        if(!empty($bargain_info)){
            $user_info = $this-> getUserInfo($bargain_info['uid']);
        }
        if(!empty($bargain_info) && !empty($user_info)){
            // 邮件提醒
            $template_email_obj = $this->getTemplateDetail($shop_id, "bargain_success_business", "email", "business");
            $email_array = explode(',', $this->shop_notify_config["email"]);//获取要提醒的人
            if(!empty($template_email_obj) && $template_email_obj["is_enable"] == 1 && !empty($email_array[0])){
                // 内容
                $content = $template_email_obj["template_content"];
                $content = str_replace("{商场名称}", $this->shop_name, $content);
                $content = str_replace("{用户名称}", $user_info['nick_name'], $content);
                $content = str_replace("{商品名称}", $bargain_info['goods_name'], $content);
                $content = str_replace("{砍价金额}", $bargain_info['bargain_min_money'], $content);
                $content = str_replace("{主订单号}", $params['order_no'], $content);
                // 标题
                $send_title=$template_email_obj["template_title"];
                $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
                $send_title = str_replace("{用户名称}", $user_info['nick_name'], $send_title);
                $send_title = str_replace("{商品名称}", $bargain_info['goods_name'], $send_title);
                $send_title = str_replace("{砍价金额}", $bargain_info['bargain_min_money'], $send_title);
                $send_title = str_replace("{主订单号}", $params['order_no'], $send_title);
                // 添加到发送记录表
                foreach ($email_array as $email){
                    $this->createNoticeEmailRecords($shop_id, $bargain_info['uid'], $email, $send_title, $content, 0);
                }
            }
            
            // 短信提醒
            $template_sms_obj = $this->getTemplateDetail($shop_id, "bargain_success_business", "sms", "business");
            $mobile_array = explode(',', $this->shop_notify_config["mobile"]);//获取要提醒的人
            if(!empty($template_sms_obj) && $template_sms_obj["is_enable"] == 1 && !empty($mobile_array[0])){
                $sms_params = array(
                    "shopname" => $this->shop_name,
                    "username" => $user_info['nick_name'],
                    "goodsname" => $bargain_info['goods_name'],
                    "bargainminmoney" => $bargain_info['bargain_min_money'],
                    "orderno" => $params['order_no']
                );
                foreach ($mobile_array as $mobile){
                    $this->createNoticeSmsRecords($template_sms_obj, $shop_id, $bargain_info['uid'], $mobile, $sms_params, "用户砍价成功商家通知", 0);
                }
            }
        }
    }
    
    /**
     * 获取砍价通知所需信息
     * @param unknown $bargain_id
     */
    private function getBargainInfo($launch_id){
        $bargain_model = new NsPromotionBargainLaunchModel();
        $bargain_info = $bargain_model -> getInfo(['launch_id'=>$launch_id], '*');
        if(!empty($bargain_info)){
            $day = floor(($bargain_info['end_time'] - time()) / 86400);
            $hours = floor(($bargain_info['end_time'] - time() - $day * 86400) / 3600);
            $bargain_info['surplus_time'] = $day > 0 ? $day.'天' : '';
            $bargain_info['surplus_time'] .= $hours > 0 ? $hours.'小时' : '';
            // 商品信息
            $ns_goods = new NsGoodsModel();
            $goods_info = $ns_goods -> getInfo(['goods_id' => $bargain_info['goods_id']], 'goods_name');
            if(!empty($goods_info)){
                $bargain_info['goods_name'] = $goods_info['goods_name'];
            }else{
                $bargain_info = null;
            }
        }
        return $bargain_info;
    }
    
    /**
     * 添加短信记录
     * @param unknown $template_obj
     * @param unknown $shop_id
     * @param unknown $buyer_id
     * @param unknown $mobile
     * @param unknown $sms_params
     * @param unknown $title
     * @param unknown $records_type
     */
    private function createNoticeSmsRecords($template_obj, $shop_id, $buyer_id, $mobile, $sms_params, $title, $records_type, $is_send = 0){
        if($this->sms_config["is_use"] != 1){
            return;
        }
        $notice_service=new Notice();
        $send_config=array(
            "appkey"=>'',
            "secret"=>'',
            "signName"=>$template_obj["sign_name"],
            "template_code"=>$template_obj["template_title"],
            "sms_type"=>$this->ali_use_type
        );
        $notice_service->createNoticeRecords($shop_id, $buyer_id, 1, $mobile, $title, json_encode($sms_params), $records_type, json_encode($send_config), $is_send);
    }
    
    /**
     * 添加邮箱记录
     * @param unknown $shop_id
     * @param unknown $buyer_id
     * @param unknown $mobile
     * @param unknown $send_title
     * @param unknown $content
     * @param unknown $records_type
     */
    private function createNoticeEmailRecords($shop_id, $buyer_id, $mobile, $send_title, $content, $records_type, $is_send = 0){
        
        if($this->email_config["is_use"] != 1){
            return;
        }
        $notice_service=new Notice();
        $send_config=array(
            "email_host"=>$this->email_host,
            "email_id"=>$this->email_id,
            "email_pass"=>$this->email_pass,
            "email_port"=>$this->email_port,
            "email_is_security"=>$this->email_is_security,
            "email_addr"=>$this->email_addr,
            "shopName"=>$this->shop_name,
        );
        $notice_service->createNoticeRecords($shop_id, $buyer_id, 2, $mobile, $send_title, $content, $records_type, json_encode($send_config), $is_send);
    }
    
    /**
     * 创建验证码发送记录
     * @param unknown $template_obj
     * @param unknown $shop_id
     * @param unknown $uid
     * @param unknown $send_type
     * @param unknown $send_account
     * @param unknown $records_type
     * @param unknown $notice_title
     * @param unknown $notice_context
     * @param unknown $send_message
     * @param unknown $is_send
     */
    private function createVerificationCodeRecords($template_obj, $shop_id, $uid, $send_type, $send_account, $records_type, $notice_title, $notice_context, $send_message, $is_send)
    {
        $notice_service = new Notice();
        if ($send_type == 1) {
            // 短信
            $send_config = array(
                "appkey" => '',
                "secret" => '',
                "signName" => $template_obj["sign_name"],
                "template_code" => $template_obj["template_title"],
                "sms_type" => $this->ali_use_type
            );
        } elseif ($send_type == 2) {
            // 邮箱
            $send_config = array(
                "email_host" => $this->email_host,
                "email_id" => $this->email_id,
                "email_pass" => $this->email_pass,
                "email_port" => $this->email_port,
                "email_is_security" => $this->email_is_security,
                "email_addr" => $this->email_addr,
                "shopName" => $this->shop_name,
            );
        }
        $res = $notice_service->createVerificationCodeRecords($shop_id, $uid, $send_type, $send_account, json_encode($send_config), $records_type, $notice_title, $notice_context, $send_message, $is_send);
        return $res;
    }
    
    
    /**
     * 用户申请退款
     * @param $param
     */
    public function applyRefund($params){
        $order_query = new OrderQuery();
        $order_goods_info = $order_query->getOrderGoodsInfo($params["order_goods_id"]);
        $order_info = $order_query->getOrderInfo(["order_id" => $params["order_id"]] );
        $user_model = new UserModel();
        $user_obj = $user_model->get($order_info["buyer_id"]);
        $mobile = $order_info["receiver_mobile"];
        $email = $user_obj["user_email"];
        if($order_goods_info["refund_status"] == 1 ){
            
            //邮箱提醒
            $template_email_obj = $this->getTemplateDetail(0, "refund_apply", "email", "user");
            //             $email_array = explode(',', $this->shop_notify_config["email"]);//获取要提醒的人
            if(!empty($email) && $template_email_obj["is_enable"] == 1){
                $content = $template_email_obj["template_content"];
                $content = str_replace("{商场名称}", $this->shop_name, $content);
                $content = str_replace("{用户名称}", $order_info["user_name"], $content);
                $content = str_replace("{退款金额}", $order_goods_info['refund_require_money'], $content);
                $content = str_replace("{主订单号}", $order_info['order_no'], $content);
                $content = str_replace("{商品名称}", $order_goods_info['goods_name'], $content);
                $send_title=$template_email_obj["template_title"];
                $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
                $send_title = str_replace("{用户名称}", $order_info["user_name"], $send_title);
                $send_title = str_replace("{退款金额}", $order_goods_info['refund_require_money'], $send_title);
                $send_title = str_replace("{主订单号}", $order_info['order_no'], $send_title);
                $send_title = str_replace("{商品名称}", $order_goods_info['goods_name'], $send_title);
                
                $this->createNoticeEmailRecords(0, $order_info["buyer_id"], $email, $send_title, $content, 0);
            }
            //短信提醒
            $template_sms_obj = $this->getTemplateDetail(0, "refund_apply", "sms", "user");
            //             $mobile_array = explode(',', $this->shop_notify_config["mobile"]);//获取要提醒的人
            if(!empty($mobile) && $template_sms_obj["is_enable"] == 1){
                $sms_params=array(
                    "shopname" => $this->shop_name,
                    "username" => $order_info["user_name"],
                    "goodsname" => $order_goods_info['goods_name'],
                    "orderno" => $order_info['order_no'],
                    "refundmoney" => $order_goods_info['refund_require_money']
                );
                $this->createNoticeSmsRecords($template_sms_obj, 0, $order_info["buyer_id"], $mobile, $sms_params, "申请退款-用户通知", 0);
                
            }
            //模板消息
            if(addon_is_exit("NsWxtemplatemsg"))
            {
                $uid = $order_info["buyer_id"];
                $url = __URL(__URL__ . "/wap/order/detail?order_id=".$params['order_id']);
                $keyword = array();
                $keyword["keyword1"] = $params['refund_require_money'];//退款金额
                $keyword["keyword2"] = $order_goods_info['goods_name'];//商品信息
                $keyword["keyword3"] = $order_info['order_no'];//订单编号
                $param = array(
                    "uid" => $uid,
                    "template_no" => 'OPENTM207103254',
                    "first" => '申请退款',
                    "url" => $url,
                    "keyword" => $keyword,
                    "remark" => "",
                    "template_code" => $params["tag"]
                );
                hook("sendWxTemplateMsg", $param);
            }
            
        }
    }
    
    /**
     * 退款通过
     * @param $param
     */
    public function refundResult($params){
        
        $order_id = $params['order_id'];
        $order_query = new OrderQuery();
        $order_info = $order_query->getOrderInfo([ "order_id" => $order_id ]);
        $uid = $order_info['buyer_id'];
        
        $user_model = new UserModel();
        $user_obj = $user_model->get($uid);
        $mobile = $order_info["receiver_mobile"];
        $email = $user_obj["user_email"];
        
        //邮箱提醒
        $template_email_obj = $this->getTemplateDetail(0, "refund_result", "email", "user");
        //         $email_array = explode(',', $this->shop_notify_config["email"]);//获取要提醒的人
        if(!empty($email) && $template_email_obj["is_enable"] == 1)
        {
            $content = $template_email_obj["template_content"];
            $content = str_replace("{商场名称}", $this->shop_name, $content);
            $content = str_replace("{用户名称}", $order_info["user_name"], $content);
            $content = str_replace("{主订单号}", $order_info['order_no'], $content);
            $send_title=$template_email_obj["template_title"];
            $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
            $send_title = str_replace("{用户名称}", $order_info["user_name"], $send_title);
            $send_title = str_replace("{主订单号}", $order_info['order_no'], $send_title);
            
            $this->createNoticeEmailRecords(0, $uid, $email, $send_title, $content, 0);
        }
        //短信提醒
        $template_sms_obj = $this->getTemplateDetail(0, "refund_result", "sms", "user");
        //         $mobile_array = explode(',', $this->shop_notify_config["mobile"]);//获取要提醒的人
        if(!empty($mobile) && $template_sms_obj["is_enable"] == 1){
            $sms_params=array(
                "shopname" => $this->shop_name,
                "username" => $order_info["user_name"],
                "orderno" => $order_info['order_no']
            );
            $this->createNoticeSmsRecords($template_sms_obj, 0, $uid, $mobile, $sms_params, "退款通过-用户通知", 0);
            
        }
        //模板消息
        if(addon_is_exit("NsWxtemplatemsg"))
        {
            $uid = $order_info["buyer_id"];
            $url = __URL(__URL__ . "/wap/order/detail?order_id=$order_id");
            $keyword = array();
            $keyword["keyword1"] = $order_info['order_no'];//退款金额
            $keyword["keyword2"] = $order_info['pay_money'];//商品信息
            $keyword["keyword3"] = $params['refund_real_money'];//订单编号
            $param = array(
                "uid" => $uid,
                "template_no" => 'OPENTM205986235',
                "first" => '退款已通过',
                "url" => $url,
                "keyword" => $keyword,
                "remark" => "",
                "template_code" => $params["tag"]
            );
            hook("sendWxTemplateMsg", $param);
        }
    }
    
    
    /**
     * 提现申请
     * @param $param
     */
    public function withdrawApply($params){
        
        $id = $params['id'];
        if ($params['type'] == 'balance') {
            $withdraw = new NsMemberBalanceWithdrawModel();
            $info = $withdraw->getInfo([
                'id' => $id
            ], '*');
            $url = __URL(__URL__ . "/wap/member/withdrawal");
        } elseif ($params['type'] == 'commission') {
            $withdraw = new NfxUserCommissionWithdrawModel();
            $info = $withdraw->getInfo([
                'id' => $id
            ], '*');
            $url = __URL(__URL__ . "/wap/Distribution/account");
        }
        
        $uid = $info['uid'];
        $user_service = new User();
        $user_info = $user_service->getUserInfoByUid($uid);
        
        
        $mobile = $user_info["user_tel"];
        $email = $user_info["user_email"];
        
        //邮箱提醒
        $template_email_obj = $this->getTemplateDetail(0, "withdraw_apply", "email", "user");
        //         $email_array = explode(',', $this->shop_notify_config["email"]);//获取要提醒的人
        if(!empty($email) && $template_email_obj["is_enable"] == 1)
        {
            $content = $template_email_obj["template_content"];
            $content = str_replace("{商场名称}", $this->shop_name, $content);
            $content = str_replace("{用户名称}", $user_info["nick_name"], $content);
            $content = str_replace("{提现金额}", $info['cash'], $content);
            $content = str_replace("{提现账户}", $info['account_number'], $content);
            $send_title=$template_email_obj["template_title"];
            $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
            $send_title = str_replace("{用户名称}", $user_info["nick_name"], $send_title);
            $send_title = str_replace("{提现金额}", $info['cash'], $send_title);
            $send_title = str_replace("{提现账户}", $info['account_number'], $send_title);
            
            $this->createNoticeEmailRecords(0, $uid, $email, $send_title, $content, 0);
            
        }
        //短信提醒
        $template_sms_obj = $this->getTemplateDetail(0, "withdraw_apply", "sms", "user");
        //         $mobile_array = explode(',', $this->shop_notify_config["mobile"]);//获取要提醒的人
        if(!empty($mobile) && $template_sms_obj["is_enable"] == 1){
            $sms_params=array(
                "shopname" => $this->shop_name,
                "username" => $user_info["user_name"],
                "withdrawmoney" => $info['cash'],
                "accountnumber" => $info['account_number']
            );
            
            $this->createNoticeSmsRecords($template_sms_obj, 0, $uid, $mobile, $sms_params, "提现申请提醒-用户通知", 0);
            
        }
        //模板消息
        if(addon_is_exit("NsWxtemplatemsg"))
        {
            $keyword = array();
            $keyword["keyword1"] = $info['cash'];// 本次提现金额
            $keyword["keyword2"] = $info['account_number'];// 提现账户
            $keyword["keyword3"] = getTimeStampTurnTime($info['ask_for_date']);// 申请时间
            $keyword["keyword4"] = getTimeStampTurnTime($info['ask_for_date'] + 3 * 24 * 3600);// 预计到账时间
            $param = array(
                "uid" => $uid,
                "template_no" => 'OPENTM207292959',
                "first" => '提现申请提醒',
                "url" => $url,
                "keyword" => $keyword,
                "remark" => "",
                "template_code" => $params["tag"]
            );
            hook("sendWxTemplateMsg", $param);
        }
    }
    
    /**
     * 提现结果通知
     * @param $params
     */
    public function withdrawResult($params){
        
        $id = $params['id'];
        if ($params['type'] == 'balance') {
            $withdraw = new NsMemberBalanceWithdrawModel();
            $info = $withdraw->getInfo([
                'id' => $id
            ], '*');
            $url = __URL(__URL__ . "/wap/member/withdrawal");
        } elseif ($params['type'] == 'commission') {
            $withdraw = new NfxUserCommissionWithdrawModel();
            $info = $withdraw->getInfo([
                'id' => $id
            ], '*');
            $url = __URL(__URL__ . "/wap/Distribution/account");
        }
        
        $uid = $info['uid'];
        $user_service = new User();
        $user_info = $user_service->getUserInfoByUid($uid);
        $mobile = $user_info["user_tel"];
        $email = $user_info["user_email"];
        //邮箱提醒
        $template_email_obj = $this->getTemplateDetail(0, "withdraw_result", "email", "user");
        //         $email_array = explode(',', $this->shop_notify_config["email"]);//获取要提醒的人
        if(!empty($email) && $template_email_obj["is_enable"] == 1)
        {
            $content = $template_email_obj["template_content"];
            $content = str_replace("{商场名称}", $this->shop_name, $content);
            $content = str_replace("{用户名称}", $user_info["nick_name"], $content);
            $content = str_replace("{提现金额}", $info['cash'], $content);
            $content = str_replace("{提现账户}", $info['account_number'], $content);
            $send_title=$template_email_obj["template_title"];
            $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
            $send_title = str_replace("{用户名称}", $user_info["nick_name"], $send_title);
            $send_title = str_replace("{提现金额}", $info['cash'], $send_title);
            $send_title = str_replace("{提现账户}", $info['account_number'], $send_title);
            
            $this->createNoticeEmailRecords(0, $uid, $email, $send_title, $content, 0);
        }
        //短信提醒
        $template_sms_obj = $this->getTemplateDetail(0, "withdraw_result", "sms", "user");
        //         $mobile_array = explode(',', $this->shop_notify_config["mobile"]);//获取要提醒的人
        if(!empty($mobile) && $template_sms_obj["is_enable"] == 1){
            $sms_params=array(
                "shopname" => $this->shop_name,
                "username" => $user_info["user_name"],
                "withdrawmoney" => $info['cash'],
                "accountnumber" => $info['account_number']
            );
            $this->createNoticeSmsRecords($template_sms_obj, 0, $uid, $mobile, $sms_params, "提现审核结果-用户通知", 0);
        }
        //模板消息
        if(addon_is_exit("NsWxtemplatemsg"))
        {
            $keyword = array();
            $keyword["keyword1"] = $info['cash'];// 本次提现金额
            $keyword["keyword2"] = $info['account_number'];// 提现账户
            $keyword["keyword3"] = getTimeStampTurnTime($info['ask_for_date']);// 申请时间
            $keyword["keyword4"] = '已通过';//结果
            $keyword["keyword5"] = getTimeStampTurnTime($info['modify_date']);// 申请时间
            $param = array(
                "uid" => $uid,
                "template_no" => 'OPENTM400094285',
                "first" => '提现审核结果通知',
                "url" => $url,
                "keyword" => $keyword,
                "remark" => "",
                "template_code" => $params["tag"]
            );
            hook("sendWxTemplateMsg", $param);
        }
    }
    
    /**
     * 分销商申请
     * @param $params
     */
    public function promoterApply($params){
        
        $uid = $params['uid'];
        $url = '';
        $promoter_shop_name = $params['promoter_shop_name']; // 店铺名称
        $regidter_time =  ($params['regidter_time']);
        
        
        $user_service = new User();
        $user_info = $user_service->getUserInfoByUid($uid);
        //邮箱提醒
        $template_email_obj = $this->getTemplateDetail(0, "promoter_apply", "email", "user");
        //         $email_array = explode(',', $this->shop_notify_config["email"]);//获取要提醒的人
        $mobile = $user_info["user_tel"];
        $email = $user_info["user_email"];
        
        if(!empty($email) && $template_email_obj["is_enable"] == 1)
        {
            $content = $template_email_obj["template_content"];
            $content = str_replace("{商场名称}", $this->shop_name, $content);
            $content = str_replace("{用户名称}", $user_info["nick_name"], $content);
            $content = str_replace("{分销商店铺}", $promoter_shop_name, $content);
            $content = str_replace("{申请时间}", $regidter_time, $content);
            $send_title=$template_email_obj["template_title"];
            $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
            $send_title = str_replace("{用户名称}", $user_info["nick_name"], $send_title);
            $send_title = str_replace("{分销商店铺}", $promoter_shop_name, $send_title);
            $send_title = str_replace("{申请时间}",$regidter_time, $send_title);
            $this->createNoticeEmailRecords(0, $uid, $email, $send_title, $content, 0);
        }
        //短信提醒
        $template_sms_obj = $this->getTemplateDetail(0, "promoter_apply", "sms", "user");
        //         $mobile_array = explode(',', $this->shop_notify_config["mobile"]);//获取要提醒的人
        if(!empty($mobile) && $template_sms_obj["is_enable"] == 1){
            $sms_params=array(
                "shopname" => $this->shop_name,
                "username" => $user_info["user_name"],
                "promotershopname" => $promoter_shop_name,
                "applytime" => $regidter_time
            );
            $this->createNoticeSmsRecords($template_sms_obj, 0, $uid, $mobile, $sms_params, "分销商申请-用户通知", 0);
        }
        //模板消息
        if(addon_is_exit("NsWxtemplatemsg"))
        {
            $url = "";
            $keyword = array();
            $keyword["keyword1"] = $promoter_shop_name;// 分销商名称
            $keyword["keyword2"] = empty($mobile) ? "暂无" : $mobile;// 联系方式
            $keyword["keyword3"] = getTimeStampTurnTime($regidter_time);// 申请时间
            $param = array(
                "uid" => $uid,
                "template_no" => 'OPENTM405551941',
                "first" => '分销商申请提醒',
                "url" => $url,
                "keyword" => $keyword,
                "remark" => "",
                "template_code" => $params["tag"]
            );
            hook("sendWxTemplateMsg", $param);
        }
    }
    
    /**
     * 分销提成
     * @param $params
     */
    public function orderDistribution($params){
        
        $uid = $params['uid'];
        $url = '';
        $order_no = $params['order_no'];
        $order_money = $params['order_money'];
        $commission_money = 0;
        $nfx_promoter = new NfxPromoterModel();
        $nfx_commission_distribution = new NfxCommissionDistributionModel();
        
        $nfx_promoter_info = $nfx_promoter->getInfo([ "uid" => $params['uid'] ], "promoter_id");
        if (!empty($nfx_promoter_info)) {
            $nfx_commission_money_info = $nfx_commission_distribution->getInfo([ "order_id" => $params['order_id'], "promoter_id" => $nfx_promoter_info['promoter_id'] ], "commission_money");
            $commission_money = !empty($nfx_commission_money_info) ? sprintf("%.2f", $nfx_commission_money_info['commission_money']) : "0.00";
        }
        
        $user_service = new User();
        $user_info = $user_service->getUserInfoByUid($uid);
        $mobile = $user_info["user_tel"];
        $email = $user_info["user_email"];
        //邮箱提醒
        $template_email_obj = $this->getTemplateDetail(0, $params["tag"], "email", "user");
        //         $email_array = explode(',', $this->shop_notify_config["email"]);//获取要提醒的人
        if(!empty($email) && $template_email_obj["is_enable"] == 1)
        {
            $content = $template_email_obj["template_content"];
            $content = str_replace("{商场名称}", $this->shop_name, $content);
            $content = str_replace("{主订单号}", $order_no, $content);
            $content = str_replace("{订单金额}", $order_money, $content);
            $content = str_replace("{分成金额}", $commission_money, $content);
            $send_title=$template_email_obj["template_title"];
            $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
            $send_title = str_replace("{主订单号}", $order_no, $send_title);
            $send_title = str_replace("{订单金额}", $order_money, $send_title);
            $send_title = str_replace("{分成金额}", $commission_money, $send_title);
            $this->createNoticeEmailRecords(0, $uid, $email, $send_title, $content, 0);
        }
        //短信提醒
        $template_sms_obj = $this->getTemplateDetail(0,  $params["tag"], "sms", "user");
        //         $mobile_array = explode(',', $this->shop_notify_config["mobile"]);//获取要提醒的人
        if(!empty($mobile) && $template_sms_obj["is_enable"] == 1){
            $sms_params=array(
                "shopname" => $this->shop_name,
                "ordermoney" => $order_money,
                "commissionmoney" => $commission_money,
                "orderno" => $order_no
            );
            $this->createNoticeSmsRecords($template_sms_obj, 0, $uid, $mobile, $sms_params, "分销订单提成-用户通知", 0);
        }
        //模板消息
        if(addon_is_exit("NsWxtemplatemsg"))
        {
            $url = "";
            $keyword = array();
            $keyword["keyword1"] = $order_no;// 订单号
            $keyword["keyword2"] = $order_money;// 订单金额
            $keyword["keyword3"] = $commission_money;// 提成金额
            $keyword["keyword4"] = getTimeStampTurnTime($params['notice_time']);//时间
            $param = array(
                "uid" => $uid,
                "template_no" => 'OPENTM201010537',
                "first" => '分销订单提成通知',
                "url" => $url,
                "keyword" => $keyword,
                "remark" => "",
                "template_code" => $params["tag"]
            );
            hook("sendWxTemplateMsg", $param);
        }
    }
    
    /**
     * 分销商审核通知
     * @param $params
     */
    public function promoterAudit($params){
        
        $uid = $params['uid'];
        $url = '';
        $promoter_shop_name = $params['promoter_shop_name'];//分销商
        $audit_time = getTimeStampTurnTime($params['audit_time']);//审核时间
        $user_service = new User();
        $user_info = $user_service->getUserInfoByUid($uid);
        $mobile = $user_info["user_tel"];
        $email = $user_info["user_email"];
        //邮箱提醒
        $template_email_obj = $this->getTemplateDetail(0, $params["tag"], "email", "user");
        //         $email_array = explode(',', $this->shop_notify_config["email"]);//获取要提醒的人
        if(!empty($email) && $template_email_obj["is_enable"] == 1)
        {
            $content = $template_email_obj["template_content"];
            $content = str_replace("{商场名称}", $this->shop_name, $content);
            $content = str_replace("{分销商名称}", $promoter_shop_name, $content);
            $content = str_replace("{用户名称}", $user_info["nick_name"], $content);
            $send_title = $template_email_obj["template_title"];
            $send_title = str_replace("{商场名称}", $this->shop_name, $send_title);
            $send_title = str_replace("{分销商名称}", $promoter_shop_name, $send_title);
            $send_title = str_replace("{用户名称}", $user_info["nick_name"], $send_title);
            $this->createNoticeEmailRecords(0, $uid, $email, $send_title, $content, 0);
        }
        //短信提醒
        $template_sms_obj = $this->getTemplateDetail(0,  $params["tag"], "sms", "user");
        //         $mobile_array = explode(',', $this->shop_notify_config["mobile"]);//获取要提醒的人
        if(!empty($mobile) && $template_sms_obj["is_enable"] == 1){
            $sms_params=array(
                "shopname" => $this->shop_name,
                "promotershopname" => $promoter_shop_name,
                "username" => $user_info["nick_name"]
            );
            $this->createNoticeSmsRecords($template_sms_obj, 0, $uid, $mobile, $sms_params, $params['title'], 0);
        }
        //模板消息
        if(addon_is_exit("NsWxtemplatemsg"))
        {
            $url = "";
            $keyword = array();
            $keyword["keyword1"] = $promoter_shop_name;// 分销商
            $keyword["keyword2"] = $audit_time;// 通过时间
            $param = array(
                "uid" => $uid,
                "template_no" => 'OPENTM409846856',
                "first" => $params['title'],
                "url" => $url,
                "keyword" => $keyword,
                "remark" => "",
                "template_code" => $params["tag"]
            );
            hook("sendWxTemplateMsg", $param);
        }
    }
}

?>