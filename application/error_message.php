<?php
/**
 * 返回值格式
 */
// 定义返回值字母格式 基础1000-1999， 用户：2000-2999 商品：3000-3999， 订单：4000-4999 活动：5000-5999
/**
 * 基础变量定义
 */
define("VER_BC",                       1111); // 单店基础版
define("VER_BCFX",                     1222); // 单店分销版
define("VER_BBC",                      2111); // 平台基础版
define("VER_BBCFX",                    2222); // 平台分销版
define('SUCCESS',                      1);
define('ERROR',                        -1);
define('ADD_FAIL',                     -1000);
define('UPDATA_FAIL',                  -1001);
define('DELETE_FAIL',                  -1002);
define('SYSTEM_DELETE_FAIL',           -1003);
define('WEIXIN_AUTH_ERROR',            -1004);
define('NO_AITHORITY',                 -1005);
define('SYSTEM_ISUSED_DELETE_FAIL',    -1006);
/**
 * 用户变量定义
 */
define('LOGIN_FAIL',                   -2000);
define('USER_ERROR',                   -2001);
define('USER_LOCK',                    -2002);
define('USER_NBUND',                   -2003);
define('USER_REPEAT',                  -2004);
define('PASSWORD_ERROR',               -2005);
define('USER_WORDS_ERROR',             -2006);
define('USER_ADDRESS_DELETE_ERROR',    -2007);
define('USER_GROUP_ISUSE',             -2008);
define('NO_LOGIN',                     -2009);
define('USER_HEAD_GET',                -2010);
define('NO_COUPON',                    -2011);
define('USER_MOBILE_REPEAT',           -2012);
define('USER_EMAIL_REPEAT',            -2013);
define('USER_GROUP_REPEAT',            -2014);
define('USER_WITHDRAW_NO_USE',         -2015);
define('USER_WITHDRAW_BEISHU',         -2016);
define('USER_WITHDRAW_MIN',            -2017);
define('MEMBER_LEVEL_DELETE',          -2018);
define('FULL_MAX_FETCH',               -2019);
define('MEMBER_LABEL_DELETE',          -2020);
define('COUPON_NO_EXIST',              -2021);
// 注册错误提示
define('REGISTER_CONFIG_OFF',          -2051);
define('REGISTER_MOBILE_CONFIG_OFF',   -2052);
define('REGISTER_EMAIL_CONFIG_OFF',    -2053);
define('REGISTER_PLAIN_CONFIG_OFF',    -2054);
define('REGISTER_USERNAME_ERROR',      -2055);
define('REGISTER_PASSWORD_ERROR',      -2056);
define('REGISTER_MOBILE_ERROR',      -2057);
define('REGISTER_EMAIL_ERROR',      -2058);
/**
 * 订单定义变量
 */
define('ORDER_DELIVERY_ERROR',         -4002);
define('LOW_STOCKS',                   -4003);
define('LOW_POINT',                    -4004);
define('LOW_BALANCE',                  -4006);
define('ORDER_PAY',                    -4005);
define('ORDER_CREATE_LOW_POINT',       -4007);
define('ORDER_CREATE_LOW_PLATFORM_MONEY', -4008);
define('ORDER_CREATE_LOW_USER_MONEY',  -4009);
define('CLOSE_POINT',                  -4010);
define('LOW_COIN',                     -4011);
define('NULL_EXPRESS_FEE',             -4012);
define('NULL_EXPRESS',                 -4013);
define('ORDER_CASH_DELIVERY',          -4014);
define('ORDER_GOODS_ZERO',             -4015);
define('NO_OPEN_POINT_PAY',            -4016);

define('FULL_MAX_BUY_NUM',            -4017);
define('FULL_COUPON',            -4018);

define('AFTER_SALE_EXIST',            -4019);

define('VIRTUAL_NO_OPEN',            -4020);
define('EXPRESS_COMPANY_UNDEFINED',  -4021);

define('REFUND_ERROR_VIRTUAL',  -4022);
define('REFUND_ERROR_GIFT',  -4023);
/**
 * 活动变量定义
 */
define('ACTIVE_REPRET',                -5001);
define('GOODS_HAVE_BEEN_GIFT',         -5002);
define('GIFT_NOT_NULL',         -5003);
define('GIFT_NOT_DELETE',         -5004);
define('GIFT_NOT_EXIST',         -5005);

// 发送邮件
define("EMAIL_SENDERROR",              -6001);

// 微信菜单
define("MAX_MENU_LENGTH",              3); // 一级菜单数量
define("MAX_SUB_MENU_LENGTH",          5); // 二级菜单数量


define('UPLOAD_FILE_ERROR',            -7001);
// 拼团错误
define('TUANGOU_PAY_ERROR',            -8001);
define('TUANGOU_EXIST',            -8005);
// 虚拟码错误信息
define('VIRIUAL_GOODS_TIME_ERROR',     -8004);
define('VIRIUAL_GOODS_ERROR',          -8002);
define('VIRIUAL_GOODS_MEMBER_ERROR',   -8003);
define('VIRIUAL_DOWNLOAD_ERROR',   -8010);
define('VIRIUAL_DOWNLOAD_NOT_MEMBER',   -8011);
define('VIRIUAL_DOWNLOAD_EXPIRE',   -8012);

//砍价
define('BARGAIN_LAUNCH_ALREADY_CLOSE', -9001);
define('BARGAIN_LAUNCH_MAX_PARTAKE',   -9002);


    
    function getErrorInfo($error_code)
    {
        $zh_cn = [
            // 基础变量
            SUCCESS =>                             '操作成功',
            ADD_FAIL =>                            '添加失败',
            UPDATA_FAIL =>                         '修改失败',
            DELETE_FAIL =>                         '删除失败',
            SYSTEM_DELETE_FAIL =>                  '当前分类下存在子分类，不能删除!',
            NO_AITHORITY =>                        '当前用户无权限',
            SYSTEM_ISUSED_DELETE_FAIL =>           '当前分类已被使用，不能删除!',
        
            // 用户变量定义
            LOGIN_FAIL =>                          '登录失败',
            USER_ERROR =>                          '账号或者密码错误',
            USER_LOCK =>                           '用户被锁定',
            USER_NBUND =>                          '用户未绑定',
            USER_REPEAT =>                         '当前用户已存在',
            PASSWORD_ERROR =>                      '用户密码错误',
            USER_WORDS_ERROR =>                    '用户名只能是数字或者英文字母',
            USER_ADDRESS_DELETE_ERROR =>           '当前用户默认地址不能删除',
            USER_GROUP_ISUSE =>                    '当前用户组已被使用，不能删除',
            NO_LOGIN =>                            '当前用户未登录',
            USER_HEAD_GET =>                       '用户已领用过',
            NO_COUPON =>                           '来迟了，已领完',
            USER_MOBILE_REPEAT =>                  '用户手机重复',
            USER_EMAIL_REPEAT =>                   '用户邮箱重复',
            USER_GROUP_REPEAT =>                   '用户组名称重复',
            USER_WITHDRAW_NO_USE =>                '会员提现功能未启用',
            USER_WITHDRAW_BEISHU =>                '提现倍数不符合',
            USER_WITHDRAW_MIN =>                   '申请提现小于最低提现',
            MEMBER_LEVEL_DELETE =>                 '该等级正在使用中,不可删除',
            MEMBER_LABEL_DELETE =>                 '该标签正在使用中,不可删除',
            FULL_MAX_FETCH =>                      '领取已达到上限',
            COUPON_NO_EXIST =>                     '优惠券不存在',
        
            // 订单定义变量
            ORDER_DELIVERY_ERROR =>                '存在未发货订单',
            LOW_STOCKS =>                          '库存不足',
            LOW_POINT =>                           '用户积分不足',
            LOW_COIN =>                            '用户购物币不足',
            CLOSE_POINT =>                         '店铺积分功能未开启',
            ORDER_PAY =>                           '订单已支付',
            ORDER_CREATE_LOW_POINT =>              '当前用户积分不足',
            ORDER_CREATE_LOW_PLATFORM_MONEY =>     '当前用户余额不足',
            ORDER_CREATE_LOW_USER_MONEY =>         '当前用户店铺余额不足',
            ORDER_CASH_DELIVERY =>                 '当前地址不支持货到付款',
            NULL_EXPRESS_FEE =>                    '当前收货地址暂不支持配送！',
            NULL_EXPRESS =>                        '无货',
            NO_OPEN_POINT_PAY =>                   '积分支付未开启',
            FULL_MAX_BUY_NUM =>                     '存在商品达到最大购买量',
            FULL_COUPON =>                          '所选优惠券不可用',
            AFTER_SALE_EXIST =>                     '存在进行中的售后服务',
            VIRTUAL_NO_OPEN =>                      '虚拟商品未开启',
            EXPRESS_COMPANY_UNDEFINED =>            '商家未配置默认物流公司',
            REFUND_ERROR_VIRTUAL     =>             '虚拟商品只能申请退款,不能退货',
            REFUND_ERROR_GIFT     =>             '赠品不可以退款和售后',
            // 活动定义变量
        
            ACTIVE_REPRET =>                       '在同一时间段内存在相同商品的活动！',
            GOODS_HAVE_BEEN_GIFT =>                '该商品已经是赠品了！',
            GIFT_NOT_NULL         =>                '赠品活动名称不能为空',
            GIFT_NOT_DELETE        =>               '进行中的赠品不能删除',
            GIFT_NOT_EXIST        =>               '赠品不存在',
            // 注册错误提示
            REGISTER_CONFIG_OFF =>                 '抱歉,商城暂未开启用户注册！',
            REGISTER_MOBILE_CONFIG_OFF =>          '抱歉,商城暂未开启用户手机注册！',
            REGISTER_EMAIL_CONFIG_OFF =>           '抱歉,商城暂未开启用户邮箱注册！',
            REGISTER_PLAIN_CONFIG_OFF =>           '抱歉,商城暂未开启用户普通注册！',
            REGISTER_USERNAME_ERROR =>             '你所填的账号不符合注册规则！',
            REGISTER_PASSWORD_ERROR =>             '你所填的密码不符合注册规则！',
            REGISTER_MOBILE_ERROR =>                '手机号格式不合法',
            REGISTER_EMAIL_ERROR =>                '邮箱格式不合法',
        
            
            
            EMAIL_SENDERROR =>                     '请开启或启用sockets扩展 和  socket_connect函数！',
            UPLOAD_FILE_ERROR =>                   '文件权限不足！',
            TUANGOU_PAY_ERROR =>                   '该团已满员',
            VIRIUAL_GOODS_TIME_ERROR=>             '虚拟商品已过期或已使用！',
            VIRIUAL_GOODS_ERROR=>                  '虚拟商品已不能使用！',
            VIRIUAL_GOODS_MEMBER_ERROR=>           '操作人没有核销资格！',
            VIRIUAL_DOWNLOAD_ERROR =>              '没有找到可用的下载文件',
            VIRIUAL_DOWNLOAD_NOT_MEMBER =>         '下载文件与当前会员不符',
            VIRIUAL_DOWNLOAD_EXPIRE =>             '当前下载文件已过期',
            BARGAIN_LAUNCH_ALREADY_CLOSE =>        '当前砍价已结束',
            BARGAIN_LAUNCH_MAX_PARTAKE =>          '您已参加过当前砍价了',
            ERROR =>                               '操作失败',
            TUANGOU_EXIST =>                        '本商品存在进行中的拼团'
        ];
        $system_error_arr = $zh_cn;
        if (array_key_exists($error_code, $system_error_arr)) {
            return $system_error_arr[$error_code];
        } elseif ($error_code > 0) {
            return '操作成功';
        } else {
            return '操作失败';
        }
    }



